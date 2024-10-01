<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

$Titulo = "Venta de aceites del $FechaI al $FechaF";

$registros = utils\IConnection::getRowsFromQuery($selectVentaAceites);

$registrosP = utils\IConnection::getRowsFromQuery($selectVentaAceitesP);

$cSql = $selectVentaAceites;
//error_log($cSql);
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
                $("#Detallado").val("<?= $Detallado ?>");
                $("#Desglose").val("<?= $Desglose ?>");
            });
        </script>
    </head>

    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo); ?>

            <div id="Reportes">
                <?php
                if ($Detallado === "No") {
                    ?>
                     <table aria-hidden="true">
                        <thead>
                            <tr>
                                <td>Posicion</td>
                                <td>Nombre</td>
                                <td>Cantidad</td>
                                <td>Importe</td>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                            $nCnt = $nImp = $nCos = 0;
                            foreach ($registros as $rg) {
                                ?>
                                <tr>
                                    <td><?= $rg["posicion"] ?></td>
                                    <td><?= $rg["alias"] ?></td>
                                    <td class="numero"><?= number_format($rg["cantidad"], 0) ?></td>
                                    <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                                </tr>
                                <?php
                                $nCnt += $rg["cantidad"];
                                $nImp += $rg["importe"];
                                $nCos += $rg["costo"];
                                $nRng++;
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td></td>
                                <td>Total</td>
                                <td><?= number_format($nCnt, 0) ?></td>
                                <td><?= number_format($nImp, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>

                     <table aria-hidden="true">
                        <thead>
                            <tr class="titulo">
                                <td colspan="6">Venta por producto</td>
                            </tr>
                            <tr>
                                <td>Clave</td>
                                <td>Descripcion</td>
                                <td>Cantidad</td>
                                <td>Precio Publico</td>
                                <td>Importe</td>
                                <td>Diferencia</td></strong>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $nCnt = $nImp = $nCos = 0;
                            error_log("----------El valor de registrosP es : " . print_r($registrosP,true));
                            foreach ($registrosP as $rg) {
                                ?>
                                <tr>
                                    <td><?= $rg["clave"] ?></td>
                                    <td><?= $rg["descripcion"] ?></td>
                                    <td class="numero"><?= number_format($rg["cantidad"], 0) ?></td>
                                    <td class="numero"><?= number_format($rg["unitario"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["importe"] - $rg["costo"], 2) ?></td>
                                </tr>
                                <?php
                                $nCnt += $rg["cantidad"];
                                $nImp += $rg["importe"];
                                $nCos += $rg["costo"];
                                $nDif += $rg["importe"] - $rg["costo"];
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td></td>
                                <td>Total</td>
                                <td><?= number_format($nCnt, 0) ?></td>
                                <td><?= number_format($nCos, 2) ?></td>
                                <td><?= number_format($nImp, 2) ?></td>
                                <td><?= number_format($nDif, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                    <?php
                } else {
                    ?>
                     <table aria-hidden="true">
                        <thead>
                            <tr>
                                <td>Clave</td>
                                <td>Producto</td>
                                <td>Cantidad</td>
                                <td>Precio</td>
                                <td>Importe</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $Posicion = "";
                            foreach ($registros as $rg) {
                                if ($rg["posicion"] <> $Posicion) {
                                    if (!empty($Posicion)) {
                                        ?>
                                        <tr class="subtotal">
                                            <td></td>
                                            <td>Total</td>
                                            <td><?= number_format($nCantidad, 0) ?></td>
                                            <td></td>
                                            <td><?= number_format($nImporte, 2) ?></td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                    <tr class="subtitulo">
                                        <td colspan="5">Posicion: <?= $rg["posicion"] ?> <?= $rg["alias"] ?></td>
                                    </tr>
                                    <?php
                                    $Posicion = $rg["posicion"];
                                    $nCantidad = 0;
                                    $nImporte = 0;
                                }
                                ?>
                                <tr>
                                    <td><?= $rg["clave"] ?></td>
                                    <td><?= $rg["descripcion"] ?></td>
                                    <td class="numero"><?= number_format($rg["cantidad"], 0) ?></td>
                                    <td class="numero"><?= number_format($rg["precio"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                                </tr>
                                <?php
                                $nImpT += $rg["importe"];
                                $nCantT += $rg["cantidad"];
                                $nImporte += $rg["importe"];
                                $nCantidad += $rg["cantidad"];
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td></td>
                                <td>Total</td>
                                <td><?= number_format($nCantidad, 0) ?></td>
                                <td></td>
                                <td><?= number_format($nImporte, 2) ?></td>
                            </tr>

                            <tr>
                                <td></td>
                                <td>Gran total</td>
                                <td><?= number_format($nCantT, 0) ?></td>
                                <td></td>
                                <td><?= number_format($nImpT, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                    <?php
                }
                ?>
            </div>
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
                                    <tr>
                                        <td>&nbsp;Detallado:</td>
                                        <td style="text-align: left;padding-left: 5px">
                                            <select id="Detallado" name="Detallado">
                                                <option value="Si">Si</option>
                                                <option value="No">No</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;Desglose:</td>
                                        <td style="text-align: left;padding-left: 5px">
                                            <select id="Desglose" name="Desglose">
                                                <option value="Cortes">Cortes</option>
                                                <option value="Dia">Dia</option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <span><input type="submit" name="Boton" value="Enviar"></span>
                                <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                                <span class="ButtonExcel"><a href="bajarep.php?cSql=<?= urlencode($cSql)?>"><i class="icon fa fa-lg fa-bold fa-file-excel-o" aria-hidden="true"></i></a></span>
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
            <?php topePagina(); ?>
        </div>
    </body>
</html>