<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("comboBoxes.php");

use com\softcoatl\utils as utils;

require './services/ReportesVentasService.php';

$Titulo = "Ventas por dia del $FechaI al $FechaF";
$fecha_original = "2024-03-25";
$fecha_ini = date("Ymd", strtotime($FechaI));
$fecha_fin = date("Ymd", strtotime($FechaF));
$ordn = $request->hasAttribute("PeorCli") ? "ASC" : "DESC";
$Vproducto = $Producto <> "*" ? "rm.producto = '$Producto' AND" : "";
$SqlPnts = "
SELECT nombre,volumen FROM (    
SELECT 
    count(1) cnt,
    cli.id,
    CONCAT(cli.id, '.- ', cli.nombre) nombre,
    SUM(volumen) volumen,
    u.codigo,
    rm.fecha_venta,
    DATE_FORMAT(STR_TO_DATE(fecha_venta, '%Y%m%d'),'%Y-%m-%d') fecha
FROM
    beneficios b
        LEFT JOIN
    rm ON rm.id = b.id_consumo
        LEFT JOIN
    unidades u ON u.id = b.id_unidad
        LEFT JOIN
    cli ON cli.id = u.cliente
        LEFT JOIN
    (SELECT 
        COUNT(1) cntG,
            SUM(volumen) volumenG,
            SUM(descuento) descuentoG,
            u.cliente clienteG
    FROM
        beneficios b
    LEFT JOIN rm ON b.id_consumo = rm.id
    LEFT JOIN unidades u ON b.id_unidad = u.id
    WHERE $Vproducto
        rm.fecha_venta BETWEEN $fecha_ini AND $fecha_fin
    GROUP BY u.cliente) ttCli ON ttCli.clienteG = cli.id
WHERE cli.id > 0 AND $Vproducto
    rm.descuento > 0
        AND rm.fecha_venta BETWEEN $fecha_ini AND  $fecha_fin
        AND b.tipo = 'I'
GROUP BY cli.id) Vv  ORDER BY volumen $ordn LIMIT $SCliente;";

$registros = utils\IConnection::getRowsFromQuery($SqlPnts);
$VentaData = array();
$VentaAxisX = array();
$numMax = 0;
foreach ($registros as $rg) {
    //echo print_r($rg,true);
    $numMax = $rg["volumen"] > $numMax ? $rg["volumen"] : $numMax;
    $VentaAxisX[] = str_replace('|', '', $rg["nombre"]);
    $VentaData["data"][] = number_format($rg["volumen"], 2, ".", "");
}
$numMax += 50;
$numMax = number_format($numMax / 7, 2);
$JsonData = "[" . json_encode($VentaData, JSON_NUMERIC_CHECK) . "]";
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
                $("#Cliente").val("<?= $SCliente ?>");
                $("#Producto").val("<?= $Producto ?>");
                $("#cFechaI").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaI")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#cFechaF").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaF")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $('input[name="MejorCli"]').prop('checked', true);
                $(".MejorPeor").click(function () {
                    $(".MejorPeor").prop('checked', false);
                    $(this).prop('checked', true);
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
                            text: "<strong>Clientes</strong>"
                        },
                        tickInterval: 1
                    },
                    yAxis: {
                        min: 0,
                        title: {
                            text: "<strong>Volumen</strong>"
                        },
                        tickInterval: <?= $numMax ?>,
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
                                <table>
                                    <tr class="texto_tablas">
                                        <td>No. Clientes: </td>
                                        <td><input type="number" name="Cliente" id="Cliente"></td>
                                        <td>
                                            <input type="checkbox" id="MejorCli" name="MejorCli" class="MejorPeor"> Mayores consumos
                                            <input type="checkbox" id="PeorCli" name="PeorCli" class="MejorPeor"> Menores consumos
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <table style="width: 100%" aria-hidden="true">
                                    <tr class="texto_tablas">
                                        <td>Producto:</td>
                                        <td>
                                            <?= ComboboxCombustibles::generate("Producto", "140px", "", "SELECCIONE") ?>
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
