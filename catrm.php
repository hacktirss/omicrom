<?php
#Librerias
session_start();
set_time_limit(300);

include_once ("auth.php");
include_once ("authconfig.php");
include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$mysqli = iconnect();

$arrayFilter = array("Fecha" => "", "Corte" => "", "page" => 1);
$nameSession = "catalogoRmPagos";
$session = new OmicromSession("rm.id", "rm.id", $nameSession, $arrayFilter, "Filtros");

foreach ($arrayFilter as $key => $value) {
    ${$key} = utils\HTTPUtils::getSessionBiValue($nameSession, $key);
}

require_once "services/PagosdService.php";

$cVarVal = utils\HTTPUtils::getSessionBiValue($nameVariableSession, "cVarVal");
$Id = 122;

$pagoDAO = new PagoDAO();
$pagoVO = $pagoDAO->retrieve($cVarVal);

$Titulo = "Despachos del cliente " . $pagoVO->getCliente();

$conditions = "rm.cliente = " . $pagoVO->getCliente() . "";
if (!empty($Fecha)) {
    $conditions .= " AND DATE(rm.fecha) = '" . str_replace("-", "", $Fecha) . "'";
} elseif (!empty($Corte) && $Corte > 0) {
    $conditions .= " AND rm.corte = $Corte";
}

$from = "(SELECT 
            1 tipo, man.isla_pos, rm.corte, rm.id, rm.posicion, com.descripcion producto, 
            rm.fin_venta fecha, rm.cliente, cli.alias, rm.volumen, rm.pagoreal, rm.pagado, rm.uuid
            FROM rm
            LEFT JOIN com ON rm.producto = com.clavei AND com.activo = 'Si'
            LEFT JOIN cli ON rm.cliente = cli.id 
            LEFT JOIN man ON man.posicion = rm.posicion AND man.activo = 'Si' 
            WHERE TRUE 
            AND cli.tipodepago IN ('Tarjeta', 'Monedero') 
            AND rm.pagado = 0
            UNION ALL
            SELECT 2 tipo, man.isla_pos, vt.corte, vt.id, vt.posicion, vt.descripcion producto, 
            vt.fecha, vt.cliente, cli.alias, vt.cantidad volumen, vt.total pagoreal, vt.pagado, vt.uuid
            FROM vtaditivos vt
            LEFT JOIN cli ON vt.cliente = cli.id 
            LEFT JOIN man ON man.posicion = vt.posicion AND man.activo = 'Si' 
            WHERE TRUE 
            AND cli.tipodepago IN ('Tarjeta', 'Monedero') 
            AND vt.pagado = 0 AND vt.referencia = 0 AND vt.tm = 'C'
        ) rm";

$paginador = new Paginador($Id,
        "rm.tipo, rm.uuid, IFNULL(GROUP_CONCAT(vt.id),'') aceites_ligados",
        "LEFT JOIN vtaditivos vt ON vt.referencia = rm.id AND vt.tm = 'C' AND rm.tipo = 1",
        "GROUP BY rm.id, rm.tipo",
        $conditions,
        $session->getSessionAttribute("sortField"),
        $session->getSessionAttribute("criteriaField"),
        utils\Utils::split($session->getSessionAttribute("criteria"), "|"),
        strtoupper($session->getSessionAttribute("sortType")),
        $session->getSessionAttribute("page"),
        "REGEXP",
        "pagosd33.php",
        $from);
//error_log($paginador->getQueryPage());
$self = utils\HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom.php"; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#Wait").hide();
                $("#Fecha").val("<?= $Fecha ?>").attr("size", "8").addClass("texto_tablas");
                $("#cFecha").css("cursor", "hand").click(function () {
                    displayCalendar($("#Fecha")[0], "yyyy-mm-dd", $(this)[0]);
                    $("#Corte").val("");
                });

                $("#Corte").val("<?= $Corte ?>").addClass("texto_tablas");

                $("#Fecha").focus(function () {
                    $("#Corte").val("");
                });
                $("#Corte").focus(function () {
                    $("#Fecha").val("");
                });

                $("#autocomplete").focus();

                $("#form1").submit(function (event) {
                    if ($("#Fecha").val() !== "" || $("#Corte").val() !== "") {
                        return;
                    }

                    if ($("#Fecha").val() === "" && $("#Corte").val() === "") {
                        $("#message").text("Seleccione un corte o asigne una fecha!").show().fadeOut(3000);
                    }

                    event.preventDefault();
                });

                $("#checkall").change(function () {
                    $("input:checkbox").prop("checked", $(this).prop("checked"));
                    calcular();
                });

                $(".micheckbox").change(function () {
                    calcular();
                });

                function calcular() {
                    var importe = 0;
                    $('.micheckbox:checked').each(
                            function () {
                                importe += $(this).data("importe");
                            }
                    );
                    $("#Boton").html("Agregar consumos seleccionados por $" + importe.toFixed(2));
                }

                $("#Boton").on("click", function () {
                    $("#Wait").show();
                    $("#ButtonAction").hide();
                    //$("#TablaDatos").hide();
                });
            });
        </script>
        <style>
            .thisButton{
                color: #006633;
            }
        </style>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <form name="form1" method="post" action="pagosd33.php">
            <div id="TablaDatos">
                <table class="paginador" aria-hidden="true">
                    <?php
                    echo $paginador->headers(array("PDF"), array("Seleccionar <input type='checkbox' id='checkall'>"));
                    while ($paginador->next()) {
                        $row = $paginador->getDataRow();
                        $title = $row["tipo"] == 1 && !empty($row["aceites_ligados"]) ? "Ligado a los aceites y/o aditivos (" . $row["aceites_ligados"] . ")" : "";
                        ?>
                        <tr title="<?= $title ?>">
                            <td style="text-align: center;">
                                <?php if (!empty($row["uuid"]) && $row["uuid"] !== PagoDAO::SIN_TIMBRAR) { ?>
                                    <a style="color: red;" href=javascript:winuni("enviafile.php?id=<?= $row["uuid"] ?>&type=pdf&formato=0")><i class="icon fa fa-lg fa-file-pdf-o" aria-hidden="true"></i></a>
                                <?php } ?>
                            </td>
                            <?php echo $paginador->formatRow(); ?>
                            <td class="alignCenter">
                                <input type="checkbox" name="Consumos[]" value="<?= $row["tipo"] . DELIMITER . $row["id"] ?>" data-importe="<?= $row["total"] ?>" class="micheckbox"/>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>
            </div>
            <div id="ButtonAction" style="text-align: right;"><button id="Boton" class="thisButton">Agregar Seleccionados</button></div>
            <div id="Wait" style="text-align: center;" class="thisButton">
                <i class="fa fa-spinner fa-pulse fa-2x" aria-hidden="true"></i>
                <span class="sr-only">Loading...</span>
                <p>Espere un momento...</p>
            </div>
        </form>

        <?php
        echo $paginador->footer(false, null, false, true);
        echo $paginador->filter();
        ?> 
        <form name="form2" id="form1" method="post" action="">
            <table class="texto_tablas" style="width: 100%;border-collapse: collapse; border: 1px solid #066;margin-top: 5px;" aria-hidden="true">
                <tr>
                    <td style="background-color: #f1f1f1"> &nbsp;
                        Fecha: 
                        <input type="text" id="Fecha" name="Fecha"> 
                        <em id="cFecha" class="icon fa fa-lg fa-calendar"></em>
                    </td>
                    <td>
                        &nbsp;&nbsp Corte: 
                        <input type="number" name="Corte" class="nombre_cliente"  min="1" max="10000" id="Corte"> 
                    </td>
                    <td>
                        <input class="nombre_cliente" type="submit" name="Filtros" id="Filtros" value="Buscar">
                    </td>
                </tr>
            </table>
            <span id="message" style="text-align: center;color: red;font-weight: bold"></span>
            <input type="hidden" name="pagina" value="1">

        </form>

        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>
