<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require './services/ReportesVentasService.php';

$Titulo = "Venta de aditivos por dia del $FechaI al $FechaF ";

$registros = utils\IConnection::getRowsFromQuery($selectVentaAceitesP);
$VentaData = array();
$VentaAxisX = array();

foreach ($registros as $rg) {
    $VentaAxisX[] = ucwords(strtolower($rg["descripcion"]));

    $VentaData[0]["name"] = "Importe";
    $VentaData[0]["color"] = "IndianRed";
    $VentaData[0]["data"][] = number_format($rg["importe"], 2, ".", "");
    $VentaData[0]["tooltip"]["valuePrefix"] = "$ ";

    $VentaData[1]["name"] = "Unidades";
    $VentaData[1]["color"] = "Blue";
    $VentaData[1]["data"][] = number_format($rg["cantidad"], 0, ".", "");
}

$JsonAxisX = json_encode($VentaAxisX);
$JsonData = json_encode(array_values($VentaData), JSON_NUMERIC_CHECK);

//error_log($selectVentaAceitesP);
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
                        type: "column"
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
                                text: "<strong>Productos</strong>"
                            },
                            tickInterval: 1
                        }],
                    yAxis: {
                        min: 0,
                        tickInterval: 10,
                        useHTML: true,
                        title: {
                            text: "<strong>Importe por producto</strong>"
                        }
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
