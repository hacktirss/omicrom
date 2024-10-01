<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

$Titulo = "Venta de aceites y gasolina por despachador del $FechaI al $FechaF";

$registros = utils\IConnection::getRowsFromQuery($selectVentaAceitesDespachador);
$registrosG = utils\IConnection::getRowsFromQuery($selectVentaGasolinaDespachador);
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
                $("#Corte").attr("size", "10");
            });
        </script>
    </head>

    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo); ?>

            <div id="Reportes">

                <table aria-hidden="true">
                    <?php
                    if ($Detallado === "No") {
                        ?>
                        <thead>
                            <tr>
                                <td>Vendedor</td>
                                <td>Nombre</td>
                                <td>Categoria</td>
                                <td>Cantidad</td>
                                <td>Importe</td>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                            $nCnt = $nImp = 0;
                            $subCnt = $subImp = 0;
                            foreach ($registros as $rg) {
                                ?>
                                <tr>
                                    <td><?= $rg["vendedor"] ?></td>
                                    <td><?= $rg["nombre"] ?></td>
                                    <td><?= $rg["categoria"] ?></td>
                                    <td class="numero"><?= number_format($rg["cantidad"], 0) ?></td>
                                    <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                                </tr>
                                <?php
                                $subCnt += $rg["cantidad"];
                                $subImp += $rg["importe"];
                                $nRng++;
                                if ($registros[$nRng]["vendedor"] != $rg["vendedor"]) {
                                    ?>
                                    <tr class="subtotal">
                                        <td colspan="3">Subtotal</td>
                                        <td class="numero"><?= number_format($subCnt, 0) ?></td>
                                        <td class="numero"><?= number_format($subImp, 2) ?></td>
                                    </tr>
                                    <?php
                                    $subCnt = $subImp = 0;
                                }

                                $nCnt += $rg["cantidad"];
                                $nImp += $rg["importe"];
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td></td>
                                <td></td>
                                <td>Total</td>
                                <td><?= number_format($nCnt, 0) ?></td>
                                <td><?= number_format($nImp, 2) ?></td>
                            </tr>
                        </tfoot>
                        <?php
                    } else {
                        ?>
                        <thead>
                            <tr>
                                <td>Fecha</td>
                                <td>Isla/Disp.</td>
                                <td>Producto</td>
                                <td>Descripcion</td>
                                <td>Categoria</td>
                                <td>Cantidad</td>
                                <td>Importe</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $Vendedor = 0;
                            $Categoria = "";
                            $flag = false;
                            $nCantidad = $nImporte = 0;
                            $ncCantidad = $ncImporte = 0;
                            $i = 1;
                            foreach ($registros as $rg) {
                                $row = $registros[$i++];

                                if ($rg["vendedor"] != $Vendedor) {
                                    if (!empty($Vendedor)) {
                                        ?>
                                        <tr class="subtotal">
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td>Total</td>
                                            <td><?= number_format($nCantidad, 0) ?></td>
                                            <td><?= number_format($nImporte, 2) ?></td>
                                        </tr>
                                        <?php
                                        $ncCantidad = $ncImporte = 0;
                                        $Categoria = "";
                                        $flag = false;
                                    }
                                    ?>
                                    <tr class="subtitulo">
                                        <td colspan="100%">Vendedor: <?= $rg["vendedor"] ?> | <?= $rg["nombre"] ?></td>
                                    </tr>
                                    <?php
                                    $nCantidad = $nImporte = 0;
                                }

                                $Vendedor = $rg["vendedor"];
                                $Categoria = $rg["categoria"];
                                ?>
                                <tr>
                                    <td><?= $rg["fecha"] ?></td>
                                    <td><?= $rg["isla_pos"] ?></td>
                                    <td><?= $rg["clave_producto"] ?></td>
                                    <td><?= $rg["descripcion"] ?></td>
                                    <td><?= $rg["categoria"] ?></td>
                                    <td class="numero"><?= number_format($rg["cantidad"], 0) ?></td>
                                    <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                                </tr>
                                <?php
                                $nImpT += $rg["importe"];
                                $nCantT += $rg["cantidad"];
                                $nImporte += $rg["importe"];
                                $nCantidad += $rg["cantidad"];
                                $ncImporte += $rg["importe"];
                                $ncCantidad += $rg["cantidad"];

                                if (($row["categoria"] != $Categoria && $row["vendedor"] == $Vendedor) || ($row["vendedor"] != $Vendedor && $flag)) {
                                    ?>
                                    <tr class="subtotal">
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td>Subtotal</td>
                                        <td><?= number_format($ncCantidad, 0) ?></td>
                                        <td><?= number_format($ncImporte, 2) ?></td>
                                    </tr>
                                    <?php
                                    $ncCantidad = $ncImporte = 0;
                                    if ($row["categoria"] != $Categoria && $row["vendedor"] == $Vendedor) {
                                        $flag = true;
                                    }
                                }
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>Total</td>
                                <td><?= number_format($nCantidad, 0) ?></td>
                                <td><?= number_format($nImporte, 2) ?></td>
                            </tr>

                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>Gran total</td>
                                <td><?= number_format($nCantT, 0) ?></td>
                                <td><?= number_format($nImpT, 2) ?></td>
                            </tr>
                        </tfoot>

                        <?php
                    }
                    ?>
                </table>
                <table  summary="Reporte de ventas por despachador">
                    <thead>
                        <tr><th scope="col" colspan="4">Gasolina</th></tr>
                        <tr>
                            <th scope="col">Despachador</th>
                            <th scope="col">Ventas</th>
                            <th scope="col">Volumen</th>
                            <th scope="col">Importe</th>
                        </tr>
                    </thead>
                    <?php
                    foreach ($registrosG as $rgG) {
                        ?>
                        <tr>
                            <td><?= $rgG["nombre"] ?></td>
                            <td><?= number_format($rgG["Cnt"], 0) ?></td>
                            <td style="text-align: right;padding-right: 10px;"><?= number_format($rgG["volumen"], 2) ?></td>
                            <td style="text-align: right;padding-right: 10px;"><?= number_format($rgG["importe"], 2) ?></td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>
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
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
            <?php topePagina(); ?>
        </div>
    </body>
</html>