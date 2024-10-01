<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

$Titulo = "Vendido Vs Facturado [Aceites y Aditivos] del $FechaI al $FechaF";

$registros = utils\IConnection::getRowsFromQuery($selectVF_Aceites);
$registrosFAC = utils\IConnection::getRowsFromQuery($selectVF_AceitesFAC);
$registrosDIF = utils\IConnection::getRowsFromQuery($selectVF_AceitesDIF);
$registrosGRAL = utils\IConnection::getRowsFromQuery($selectVF_AceitesGRAL);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports.php'; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#FechaI").val("<?= $FechaI ?>").attr("size", "10");
                $("#FechaF").val("<?= $FechaF ?>").attr("size", "10");
                $("#cFechaI").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaI")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#cFechaF").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaF")[0], "yyyy-mm-dd", $(this)[0]);
                });
            });
        </script>
    </head>

    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo) ?>
            <div id="Reportes">
                 <table aria-hidden="true">
                    <thead>
                        <tr class="titulo">
                            <td colspan="100%">Venta total</td>
                        </tr>
                        <tr>
                            <td>Rubro</td>
                            <td>Producto</td>
                            <td>Cantidad</td>
                            <td>Importe</td>
                            <td>Iva</td>
                            <td>Total</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($registros as $rg) {
                            ?>
                            <tr>
                                <td><?= $rg["tipodepago"] ?></td>
                                <td class="overflow"><?= $rg["producto"] ?></td>
                                <td class="numero"><?= number_format($rg["cantidad"], 0) ?></td>
                                <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["iva"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["total"], 2) ?></td>
                            </tr>
                            <?php
                            $nCnt += $rg["cantidad"];
                            $nImp += $rg["importe"];
                            $nTotal += $rg["total"];
                            $nIva += $rg["iva"];
                        }
                        ?>

                        <tr class="subtotal">
                            <td></td>
                            <td>Total</td>
                            <td><?= number_format($nCnt, 0) ?></td>
                            <td><?= number_format($nImp, 2) ?></td>
                            <td><?= number_format($nIva, 2) ?></td>
                            <td><?= number_format($nTotal, 2) ?></td>
                        </tr>

                        <tr class="titulo">
                            <td colspan="100%">Venta facturada</td>
                        </tr>

                        <?php
                        $nCnt = $nImp = $nIeps = $nIva = 0;
                        foreach ($registrosFAC as $rg) {
                            ?>
                            <tr>
                                <td></td>
                                <td class="overflow"><?= $rg["descripcion"] ?></td>
                                <td class="numero"><?= number_format($rg["cantidad"], 0) ?></td>
                                <td class="numero"><?= number_format($rg["importe"] - $rg["iva"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["iva"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                            </tr>
                            <?php
                            $nFolios += $rg["folios"];
                            $nCnt += $rg["cantidad"];
                            $nImp += $rg["importe"];
                            $nIva += $rg["iva"];
                        }
                        ?>

                        <tr class="subtotal">
                            <td></td>
                            <td>Total</td>
                            <td><?= number_format($nCnt, 0) ?></td>
                            <td><?= number_format($nImp - $nIva, 2) ?></td>
                            <td><?= number_format($nIva, 2) ?></td>
                            <td><?= number_format($nImp, 2) ?></td>
                        </tr>
                        
                        <tr class="subtitulo">
                            <td colspan="100%">Numero de facturas realizadas a clientes: <?= number_format($nFolios, 0) ?></td>
                        </tr>

                        <tr class="titulo">
                            <td colspan="100%">Diferencias</td>
                        </tr>

                        <?php
                        $nCnt = $nImp = $nIeps = $nIva = 0;
                        foreach ($registrosDIF as $rg) {
                            ?>
                            <tr>
                                <td><?= $rg["tipodepago"] ?></td>
                                <td class="overflow"><?= $rg["producto"] ?></td>
                                <td class="numero"><?= number_format($rg["cantidad"], 0) ?></td>
                                <td class="numero"><?= number_format($rg["total"] - $rg["iva"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["iva"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["total"], 2) ?></td>
                            </tr>
                            <?php
                            $nCnt += $rg["cantidad"];
                            $nImp += $rg["total"];
                            $nIva += $rg["iva"];
                            $nImporte += $rg["total"];
                        }
                        ?>

                        <tr class="subtotal">
                            <td></td>
                            <td>Total</td>
                            <td><?= number_format($nCnt, 0) ?></td>
                            <td><?= number_format($nImp - $nIva, 2) ?></td>
                            <td><?= number_format($nIva, 2) ?></td>
                            <td><?= number_format($nImporte, 2) ?></td>
                        </tr>

                        <tr class="titulo">
                            <td colspan="100%">Factura realizada al Público en General</td>
                        </tr>

                        <?php
                        $nCnt = $nImp = $nIeps = $nIva = 0;
                        foreach ($registrosGRAL as $rg) {
                            $Iva = ($rg["importe"] - $rg["ieps"]) - (($rg["importe"] - $rg["ieps"]) / $PrmIva);
                            ?>
                            <tr>
                                <td></td>
                                <td class="overflow"><?= $rg["descripcion"] ?></td>
                                <td class="numero"><?= number_format($rg["cantidad"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["importe"] - $Iva, 2) ?></td>
                                <td class="numero"><?= number_format($Iva, 2) ?></td>
                                <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                            </tr>
                            <?php
                            $nCnt += $rg["cantidad"];
                            $nImp += $rg["importe"];
                            $nIeps += $rg["ieps"];
                            $nIva += $Iva;
                        }
                        ?>

                        <tr class="subtotal">
                            <td></td>
                            <td>Total</td>
                            <td class="numero"><?= number_format($nCnt, 0) ?></td>
                            <td class="numero"><?= number_format($nImp - $nIva, 2) ?></td>
                            <td class="numero"><?= number_format($nIva, 2) ?></td>
                            <td class="numero"><?= number_format($nImp, 2) ?></td>
                        </tr>
                    </tbody>
                </table>

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