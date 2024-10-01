<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$arrayFilter = array("Fecha" => date('Y-m-d'),  "FechaFinal" => date("Y-m-d"),"Usuario" => "TODOS", "Evento" => "TODOS");
$nameSession = "BitacoraEventos";
$session = new OmicromSession("id_bitacora", "id_bitacora", $nameSession, $arrayFilter, "Filtros");
$usuarioSesion = getSessionUsuario();
foreach ($arrayFilter as $key => $value) {
    ${$key} = utils\HTTPUtils::getSessionBiValue($nameSession, $key);
}

$busca = $session->getSessionAttribute("criteria");
if ($request->hasAttribute("criteria") && $request->getAttribute("criteria") === "ini") {
    BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "OP_COT", "CONSULTA DE BITACORAS");
}
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Titulo = "Bitacora de eventos";
$Id = 109;

$conditios .= " fecha_evento between '" . str_replace("-", "", $Fecha) . "' AND '" . str_replace("-", "", $FechaFinal) . "'";

if ($Usuario !== "TODOS") {
    $conditios .= " AND usuario = '" . $Usuario . "'";
}
if ($Evento !== "TODOS") {
    $conditios .= " AND tipo_evento = '" . $Evento . "'";
}

$paginador = new Paginador($Id,
        "mac",
        "",
        "",
        "$conditios",
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
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {

                $("#Usuario").val("<?= $Usuario ?>");
                $("#Evento").val("<?= $Evento ?>");

                $('#Fecha').val('<?= $Fecha ?>').attr('size', '8').addClass('texto_tablas');
                $("#FechaFinal").val("<?= $FechaFinal ?>").attr('size', '8').addClass('texto_tablas');
                $('#cFecha').css('cursor', 'hand').click(function () {
                    displayCalendar($('#Fecha')[0], 'yyyy-mm-dd', $(this)[0]);
                });
                $("#cFechaF").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaFinal")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $(".textosCualliAlert").click(function () {
                    var IdAlarma = this.dataset.idalarma;
                    jQuery.ajax({
                        type: "POST",
                        url: "getByAjax.php",
                        dataType: "json",
                        cache: false,
                        data: {"Origen": "BuscaBitacora", "IdAlarma": IdAlarma},
                        success: function (data) {
                            alertTextValidation("Solucion de evento No. " + IdAlarma + ":", "textarea", "Guardar", "", true, "", "", "", data.Solucion, IdAlarma, 256);
                        }
                    });
                });
            });
            function getResultado(val_Json) {
                if (val_Json.Sucess) {
                    jQuery.ajax({
                        type: "POST",
                        url: "getByAjax.php",
                        dataType: "json",
                        cache: false,
                        data: {"Origen": "AgregaObservacionBitacora", "IdAlarma": val_Json.IdOrigen, "DescripcionEvento": val_Json.Value, "Usr": "<?= $usuarioSesion->getId() ?>"},
                        success: function (data) {
                            if (data.Error) {
                                alertTextValidation("Error: " + data.Msj + "!", "", "", "", false, "error", "4000", false);
                            } else {
                                alertTextValidation("<?= utils\Messages::MESSAGE_DEFAULT ?>", "", "", "", false, "success", "1000", false);
                            }
                        }
                    });
                } else {
                    console.log("Cancelamos");
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
                echo $paginador->headers(array(), array("MAC", "Obs."));
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr>
                        <?php
                        echo $paginador->formatRow();
                        ?>
                        <td><?= $row["mac"] ?></td>
                        <td class="textosCualli" style="text-align: center;"><a class='textosCualliAlert' data-idAlarma='<?= $row["id_bitacora"] ?>'  href="#"><em class="fa-regular fa-message"></em></a></td>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>
        <?php
        $nLink = ['<span><i class="icon fa fa-lg fa-file-text"></i> Consulta</span' => "javascript:winuni('bitacoraEventosRep.php?criteria=ini')"];
        echo $paginador->footer(($usuarioSesion->getLevel() >= 7 && $usuarioSesion->getTeam() !== PerfilesUsuarios::AUDITOR) || $usuarioSesion->getTeam() === PerfilesUsuarios::SUPERVISOR, $nLink, true, true);
        echo $paginador->filter();
        echo "<div class='mensajes'>$Msj</div>";
        ?>
        <form name="form1" id="form1" method="post" action="">
            <table class="texto_tablas" style="width: 100%;border-collapse: collapse; border: 1px solid #066;margin-top: 5px;" aria-hidden="true">
                <tr>
                    <td style="background-color: #f1f1f1"> &nbsp;
                        Fecha Inicial: 
                        <input type="text" id="Fecha" name="Fecha"> 
                        <img id="cFecha" src="libnvo/calendar.png" alt="Calendario">
                        Fecha Final: 
                        <input type="text" id="FechaFinal" name="FechaFinal" class='texto_tablas'  style="width: 80px;"> 
                        <img id="cFechaF" src="libnvo/calendar.png" alt="Calendario" style="margin-right: 55px;">

                        &nbsp;&nbsp;Usuario:
                        <?= Usuarios::comboUsuarios("Usuario", "TODOS") ?>

                        &nbsp;&nbsp;Evento: 
                        <?php
                        echo $ListC = ListasCatalogo::listaNombreCatalogo("Evento", "BITACORA DE EVENTOS", "TODOS");
                        ?>
                        &nbsp;&nbsp;
                        <input class='nombre_cliente' type='submit' name='Filtros' id="Boton" value='Enviar'>
                    </td>
                </tr>
            </table>
            <input type="hidden" name="busca" id="busca">
        </form>

        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>
