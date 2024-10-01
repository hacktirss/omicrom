<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require './services/ReportesVentasService.php';

$Titulo = "Ventas por hora del $FechaI al $FechaF";

$registros = utils\IConnection::getRowsFromQuery($selectByHora);
$VentaData = array();
$VentaAxisX = array();

foreach ($registros as $rg) {
    $VentaAxisX[] = $rg["hora"];

    $VentaData[0]["name"] = "Ventas";
    $VentaData[0]["type"] = "spline";
    $VentaData[0]["color"] = "Black";
    $VentaData[0]["yAxis"] = 0;
    $VentaData[0]["data"][] = number_format($rg["ventas"], 0);

    $VentaData[1]["name"] = "Importe";
    $VentaData[1]["type"] = "column";
    $VentaData[1]["color"] = "IndianRed";
    $VentaData[1]["yAxis"] = 1;
    $VentaData[1]["data"][] = number_format($rg["importe"], 2, ".", "");
    $VentaData[1]["tooltip"]["valuePrefix"] = "$ ";

    $VentaData[2]["name"] = "Volumen";
    $VentaData[2]["type"] = "spline";
    $VentaData[2]["color"] = "Blue";
    $VentaData[2]["yAxis"] = 2;
    $VentaData[2]["data"][] = number_format($rg["volumen"], 3, ".", "");
    $VentaData[2]["dashStyle"] = "shortdot";
    $VentaData[2]["tooltip"]["valueSuffix"] = " Lts";
}

$JsonAxisX = json_encode($VentaAxisX);
$JsonData = json_encode(array_values($VentaData), JSON_NUMERIC_CHECK);
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports.php'; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                var Titulo = "<?= $Titulo ?>";

                $("#FechaI").val("<?= $FechaI ?>").attr("size", "10");
                $("#FechaF").val("<?= $FechaF ?>").attr("size", "10");
                $("#cFechaI").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaI")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#cFechaF").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaF")[0], "yyyy-mm-dd", $(this)[0]);
                });

                var myJsonAxisX = <?= $JsonAxisX ?>;
                var myJsonVenta = <?= $JsonData ?>;

                //console.log(myJsonAxisX);
                //console.log(myJsonVenta);
                
                var stringJson = JSON.stringify(myJsonVenta, null, 2);
                //console.log(stringJson);

                $("#containerChart").highcharts({
                    chart: {
                        zoomType: "xy"
                    }, title: {
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
                    credits: {
                        enabled: false
                    },
                    xAxis: [{
                            categories: myJsonAxisX,
                            crosshair: true,
                            title: {
                                text: "Dimensiones a 24 Hrs."
                            }
                        }],
                    yAxis: [
                        {
                            title: {
                                text: "Numero de despachos",
                                style: {
                                    color: "Black"
                                }
                            },
                            labels: {
                                format: "{value}",
                                style: {
                                    color: "Black"
                                }
                            },
                            opposite: true
                        },
                        {
                            title: {
                                text: "Importe",
                                style: {
                                    color: "IndianRed"
                                }
                            },
                            labels: {
                                format: "$ {value}",
                                style: {
                                    color: "IndianRed"
                                }
                            },
                            gridLineWidth: 0
                        },
                        {
                            title: {
                                text: "Volumen",
                                style: {
                                    color: "Blue"
                                }
                            },
                            labels: {
                                format: "{value} Lts",
                                style: {
                                    color: "Blue"
                                }
                            },
                            opposite: true,
                            gridLineWidth: 0
                        }],
                    tooltip: {
                        shared: true
                    },
                    legend: {
                        layout: "vertical",
                        align: "left",
                        x: 80,
                        verticalAlign: "top",
                        y: 55,
                        floating: true,
                        backgroundColor: "white"
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
                                <span><input type="submit" name="Boton" value="Enviar"></span>
                                <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
            <?php topePagina(); ?>
        </div>
    </body>
</html>
