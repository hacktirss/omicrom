<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("comboBoxes.php");

use com\softcoatl\utils as utils;

require './services/ReportesVentasService.php';

$Titulo = "Ventas por dia del $FechaI al $FechaF";

$registros = utils\IConnection::getRowsFromQuery($selectByDia);
$VentaData = array();
$VentaAxisX = array();

if ($Detallado === "Si") {
    foreach ($registros as $rg) {
        $VentaAxisX[] = getDayDate(str_replace('|','',$rg["fecha"]));
    }

    foreach ($Productos as $producto) {
        $VentaData[$producto["id"]]["name"] = $Tipo . ": " . $producto["descripcion"];
        $VentaData[$producto["id"]]["color"] = $producto["color"];

        foreach ($registros as $rg) {
            $VarAdd = $producto["id"] + 10;
            if ($Tipo === "Importe") {
                $VentaData[$producto["id"]]["data"][] = number_format($rg["pesos" . $producto["id"]], 2, ".", "");
            } else if ($Tipo === "Ventas") {
                $VentaData[$producto["id"]]["data"][] = number_format($rg["cantidadVenta" . $producto["id"]], 3, ".", "");
            } else {
                $VentaData[$producto["id"]]["data"][] = number_format($rg["volumen" . $producto["id"]], 3, ".", "");
            }
            $var = $producto["id"] + 10;
            $VentaData[$var]["data"][] = number_format($rg["cantidadVenta" . $producto["id"]], 3, ".", "");
            $VentaData[$var]["name"] = "Ventas: " . $producto["descripcion"];
            $VentaData[$var]["color"] = $producto["color"];
        }
    }
    $JsonData = json_encode(array_values($VentaData), JSON_NUMERIC_CHECK);
} else {
    $VentaData["name"] = $Tipo;
    $VentaData["color"] = $Tipo === "Importe" ? "IndianRed" : "Blue";
    foreach ($registros as $rg) {
        //echo print_r($rg,true);
        $VentaAxisX[] = getDayDate(str_replace('|','',$rg["fecha"]));
        if ($Tipo === "Importe") {
            $VentaData["data"][] = number_format($rg["pesos"], 2, ".", "");
        } else if ($Tipo === "Ventas") {
            $VentaData["data"][] = number_format($rg["ventas"], 0, ".", "");
        } else {
            $VentaData["data"][] = number_format($rg["volumen"], 3, ".", "");
        }
    }
    $JsonData = "[" . json_encode($VentaData, JSON_NUMERIC_CHECK) . "]";
}

$JsonAxisX = json_encode($VentaAxisX);

//error_log($selectByDia);
//error_log(print_r($JsonData, true));
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
<?php require "./config_omicrom_reports.php"; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                var Titulo = "<?= $Titulo ?>";
                var Tipo = "<?= $Tipo ?>";

                $("#FechaI").val("<?= $FechaI ?>").attr("size", "10");
                $("#FechaF").val("<?= $FechaF ?>").attr("size", "10");
                $("#Producto").val("<?= $Producto ?>");
                $("#cFechaI").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaI")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#cFechaF").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaF")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#Detallado").val("<?= $Detallado ?>");
                $("#Tipo").val("<?= $Tipo ?>");

                var myJsonAxisX = <?= $JsonAxisX ?>;
                var myJsonVenta = <?= $JsonData ?>;

                //console.log(myJsonAxisX);
                //console.log(myJsonVenta);

                var stringJson = JSON.stringify(myJsonVenta, null, 2);
                //console.log(stringJson);

                $("#containerChart").highcharts({
                    chart: {
                        type: "spline"
                    },
                    title: {
                        text: Titulo,
                        style: {
                            "color": "#00A0A3",
                            "font-size": "18px",
                            "font-family": "sans-serif"
                        },
                        useHTML: true
                    },
                    subtitle: {
                        text: ""
                    },
                    xAxis: {
                        categories: myJsonAxisX,
                        crosshair: true,
                        title: {
                            text: "<strong>Dias</strong>"
                        },
                        tickInterval: 1
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: Tipo
                        },
                        tickInterval: 5000,
                        useHTML: true
                    },
                    tooltip: {
                        headerFormat: '<div style="font-size:12px"><strong>{point.key}</strong>',
                        pointFormat: '<div style="color:{series.color};">{series.name}: <strong>{point.y:.1f} </strong></div>',
                        footerFormat: '</div>',
                        shared: true,
                        useHTML: true,
                        borderColor: "Black",
                        borderRadius: 10,
                        borderWidth: 1
                    },
                    plotOptions: {
                        column: {
                            pointPadding: 0.3,
                            borderWidth: 0,
                            shadow: true
                        },
                        line: {
                            dataLabels: {
                                enabled: true,
                                format: "{y:.2f}"
                            }
                        }
                    },
                    series: myJsonVenta
                });

            });
        </script>
    </head>

    <body>

        <div id="container">
<?php nuevoEncabezado($Titulo); ?>

            <div id="containerChart" style="min-width: 310px; height: 400px; margin: 0 auto;"></div>

        </div>
        <div id="footer">
            <form name="formActions" method="post" action="" id="form" class="oculto">
                <div id="Controles">
                    <table aria-hidden="true">
                        <tr style="height: 40px;">
                            <td style="width: 30%;">
                                <table aria-hidden="true">
                                    <tr>
                                        <td>F.inicial:</td>
                                        <td><input type="text" id="FechaI" name="FechaI"></td>
                                        <td class="calendario"><i id="cFechaI" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
                                    </tr>
                                    <tr>
                                        <td>F.final:</td>
                                        <td><input type="text" id="FechaF" name="FechaF"></td>
                                        <td class="calendario"><i id="cFechaF" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <table style="width: 100%" aria-hidden="true">
                                    <tr class="texto_tablas">
                                        <td style="text-align: right;padding-right: 5px">Detallado:</td>
                                        <td style="text-align: left;padding-left: 5px">
                                            <select id="Detallado" name="Detallado">
                                                <option value="Si">Si</option>
                                                <option value="No">No</option>
                                            </select>
                                        </td>
                                        <td>Producto:</td>
                                        <td>
<?= ComboboxCombustibles::generate("Producto", "140px", "", "SELECCIONE") ?>
                                        </td>
                                    </tr>
                                    <tr class="texto_tablas">
                                        <td style="text-align: right;padding-right: 5px">Desglose:</td>
                                        <td style="text-align: left;padding-left: 5px">
                                            <select id="Tipo" name="Tipo">
                                                <option value="Importe">Importe</option>
                                                <option value="Volumen">Volumen</option>
                                                <option value="Ventas">Ventas</option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <span><input type="submit" name="Boton" value="Enviar"></span>
                                <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                            </td>
                        </tr>
                    </table>
                </div>
                <input type="hidden" name="Desglose" value="Dia">
                <input type="hidden" name="Turno" value="No">
            </form>
<?php topePagina(); ?>
        </div>
    </body>
</html>
