<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$arrayFilter = array("Fecha" => date('Y-m-d'), "FechaFinal" => date("Y-m-d"), "Usuario" => "TODOS", "TipoAlarma" => "TODOS", "Aceptados" => "No");
$nameSession = "Alarmas detalle";
$session = new OmicromSession("a.id_alarma", "a.id_alarma", $nameSession, $arrayFilter, "Filtros");
foreach ($arrayFilter as $key => $value) {
    ${$key} = utils\HTTPUtils::getSessionBiValue($nameSession, $key);
}
$busca = $session->getSessionAttribute("criteria");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));
$usuarioSesion = getSessionUsuario();
if (!empty($Fecha)) {
    $conditions .= " AND a.fecha_alarma between '" . str_replace("-", "", $Fecha) . "' AND '" . str_replace("-", "", $FechaFinal) . "' ";
}
if ($Usuario !== "TODOS") {
    $conditions .= " AND a.componente_alarma = '$Usuario' ";
}
if ($TipoAlarma !== "TODOS") {
    $conditions .= " AND a.tipo_alarma = '$TipoAlarma' ";
}
if ($Aceptados === "Si") {
    $Cc2 = "a.revision_alarma = '0'";
} else {
    $Cc2 = "a.revision_alarma = '1'";
}

$Id = 107;
$Titulo = "Alarmas del sistema";

$paginador = new Paginador($Id,
        "",
        "",
        "",
        "$Cc2 " . $conditions,
        $session->getSessionAttribute("sortField"),
        $session->getSessionAttribute("criteriaField"),
        utils\Utils::split($session->getSessionAttribute("criteria"), "|"),
        strtoupper($session->getSessionAttribute("sortType")),
        $session->getSessionAttribute("page"),
        "REGEXP",
        "");

$self = utils\HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");

$cLink = substr($self, 0, strrpos($self, ".")) . 'e.php';
$cLinkd = substr($self, 0, strrpos($self, ".")) . 'd.php';

if ($request->hasAttribute("op")) {
    $cId = $request->getAttribute("cId");
    $alarmasDAO = new AlarmasDAO();
    if ($request->getAttribute("op") === "Si") {
        $alarmaVO = new AlarmasVO();
        $alarmaVO->setIdAlarma($cId);
        $alarmaVO->setRevisionAlarma(0);
        if ($alarmasDAO->update($alarmaVO)) {
            BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "ALARMA [" . $cId . "] HA SIDO ATENDIDA");
            $Msj = "Registro actualizado con EXITO!";
        }
    } elseif ($request->getAttribute("op") == "Todo") {
        if ($alarmasDAO->updateAll(1)) {
            BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "ALARMAS HAN SIDO ATENDIDAS");
            $Msj = "Registros actualizados con EXITO!";
        }
    }
    header("Location: $self?Msj=$Msj");
}
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#autocomplete").focus();
                $("#Aceptados").val("<?= $Aceptados ?>");
                $("#cFecha").css("cursor", "hand").click(function () {
                    displayCalendar($("#Fecha")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#cFechaF").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaFinal")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#Fecha").val("<?= $Fecha ?>");
                $("#FechaFinal").val("<?= $FechaFinal ?>");
                $("#Usuario").val("<?= $Usuario ?>");
                $("#TipoAlarma").val("<?= $TipoAlarma ?>");
                $(".textosCualli").click(function () {
                    var idAlarma = this.dataset.idalarma;
                    var descripcion = this.dataset.descripcion;
                    var componente = this.dataset.componente;
                    alertTextValidation(idAlarma + "  -  " + componente + " <br>" + descripcion, "textarea", "Guardar", "Solución.", true, "", "", "", "", idAlarma, 256, "Descripcion de solucion");
                });
            });
            function getResultado(val_Json) {
                if (val_Json.Sucess) {
                    jQuery.ajax({
                        type: "POST",
                        url: "getByAjax.php",
                        dataType: "json",
                        cache: false,
                        data: {"Origen": "AgregaObservacion", "IdAlarma": val_Json.IdOrigen, "DescripcionEvento": val_Json.Value, "Usr": "<?= $usuarioSesion->getId() ?>"},
                        success: function (data) {
                            var locationAceptar = "alarmas.php?cId=" + val_Json.IdOrigen + "&op=Si";
                            alertTextValidation(data.Msj, "", "", "", false, data.Icon, "1000", false);
                            $(location).attr('href', locationAceptar);
                        }
                    });
                } else {
                    console.log("Cancelado");
                }
            }
        </script>
        <?php $paginador->script(); ?>
    </head>


    <body>

        <?php BordeSuperior(); ?>

        <div id="TablaDatos">
            <table class="paginador" aria-hidden="true">
                <?php
                echo $paginador->headers(array(), array(""));
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr>
                        <?php echo $paginador->formatRow(); ?>
                        <td style="text-align: center">
                            <?php
                            if ($usuarioSesion->getTeam() === "Administrador") {
                                ?>
                                <a class='textosCualli' data-idAlarma='<?= $row["id_alarma"] ?>' data-descripcion='<?= $row["descripcion_alarma"] ?>' data-componente='<?= $row["componente_alarma"] ?>' href="#">aceptar</a>
                                <?php
                            }
                            ?>
                        </td>                   
                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>
        <?php
        if ($usuarioSesion->getTeam() === "Administrador") {
            $nLink = array("<i class=\"icon fa fa-lg fa-check-circle\" aria-hidden=\"true\"></i>Aceptar todo" => "$self?op=Todo");
        }
        echo $paginador->footer(false, $nLink, true, true);
        echo $paginador->filter();
        echo "<div class='mensajes'>$Msj</div>";
        ?>

        <form name="form1" id="form1" method="post" action="">
            <table class="texto_tablas" style="width: 100%;border-collapse: collapse;background-color: #f1f1f1; border: 1px solid #066;margin-top: 5px;" aria-hidden="true">
                <tr>
                    <td style="text-align: right;padding-top: 10px;"> &nbsp;
                        Usuario:
                        <?= Usuarios::comboUsuarios("Usuario", "TODOS", ' style="margin-right: 55px;"'); ?>
                    </td>
                    <td style="padding-top: 10px;">
                        Tipo de Alarma:  
                        <?php
                        $SqlTipo = "SELECT lv.llave_lista_valor,lv.valor_lista_valor FROM listas l,listas_valor lv
                                        WHERE l.id_lista = lv.id_lista_lista_valor 
                                        AND l.nombre_lista = 'NUMEROS DE EVENTOS CV' 
                                        AND l.estado_lista = 1 AND  lv.alarma_lista_valor";
                        $RsTipo = utils\IConnection::getRowsFromQuery($SqlTipo);
                        ?>
                        <select class='texto_tablas' name="TipoAlarma" id="TipoAlarma" style="margin-right: 30px; width: 150px;">
                            <option value="TODOS">TODOS</option>
                            <?php
                            foreach ($RsTipo as $rst) {
                                ?>
                                <option value="<?= $rst["llave_lista_valor"] ?>"><?= $rst["llave_lista_valor"] ?> - <?= $rst["valor_lista_valor"] ?></option>
                                <?php
                            }
                            ?>
                        </select>
                    </td>
                    <td style="padding-top: 10px;">
                        Aceptados :
                        <select  class='texto_tablas' name="Aceptados" id="Aceptados">
                            <option value="Si">Si</option>
                            <option value="No">No</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: right;">     
                        Fecha Inicial: 
                        <input type="text" id="Fecha" name="Fecha" class='texto_tablas'  style="width: 80px;"> 
                        <img id="cFecha" src="libnvo/calendar.png" alt="Calendario" style="margin-right: 55px;">
                    </td>
                    <td>
                        Fecha Final: 
                        <input type="text" id="FechaFinal" name="FechaFinal" class='texto_tablas'  style="width: 80px;"> 
                        <img id="cFechaF" src="libnvo/calendar.png" alt="Calendario" style="margin-right: 55px;">
                    </td>
                    <td>
                        <input class='nombre_cliente' type='submit' name='Filtros' id="Boton" value='Enviar' style="margin-right: 35px;">
                    </td>
                </tr>
                <tr><td colspan="3" style="height: 5px;"></td></tr>
            </table>
            <input type="hidden" name="busca" id="busca">
        </form>
        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>
