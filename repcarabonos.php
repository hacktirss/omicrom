<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesClientesService.php";

$Titulo = "Relacion de cargos,abonos y saldos por cliente del $FechaI al $FechaF";

$registros = utils\IConnection::getRowsFromQuery($selectC_A_S);
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
            });
        </script>
    </head>

    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo) ?>

            <div id="Reportes">
                 <table aria-hidden="true">
                    <thead>
                        <tr>
                            <td>#</td>
                            <td>Cta</td>
                            <td>Nombre</td>
                            <td>Tipo/Pago</td>
                            <td>Saldo Inicial</td>
                            <td>Cargos</td>
                            <td>Abonos</td>
                            <td>Importe</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $cTipoPago = "";
                        $nRng = 0;
                        foreach ($registros as $rg) {
                            if ($cTipoPago <> $rg["tipodepago"]) {
                                if (!empty($cTipoPago)) {
                                    ?>
                                    <tr class="subtotal">
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td>Subtotal <?=$cTipoPago?> </td>
                                        <td class="moneda"><?= number_format($nImpS, 2) ?></td>
                                        <td class="moneda"><?= number_format($nPagS, 2) ?></td>
                                        <td class="moneda"><?= number_format($nCarS, 2) ?></td>
                                        <td class="moneda"><?= number_format($nImporte, 2) ?></td>
                                    </tr>
                                    <?php
                                }
                                $cTipoPago = $rg["tipodepago"];
                                $nImporte = 0;
                                $nImpS =0;
                                $nCarS =0;
                                $nPagS =0;
                            }
                            ?>
                            <tr>
                                <td><?= ++$nRng; ?></td>
                                <td><?= $rg["cliente"] ?></td>
                                <td class="overflow"><?= $rg["nombre"] ?></td>
                                <td><?= $rg["tipodepago"] ?></td>
                                <td class="numero"><?= number_format($rg["inicial"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["cargos"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["abonos"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                            </tr>
                            <?php
                            $nImporte += $rg["importe"];
                            $nImpS += $rg["inicial"];
                            $nPagS += $rg["cargos"];
                            $nCarS += $rg["abonos"];

                            $nImp += $rg["inicial"];
                            $nCar += $rg["cargos"];
                            $nPag += $rg["abonos"];
                            $nSal += $rg["importe"];
                        }
                        ?>

                    </tbody>
                    <tfoot>
                        <tr class="subtotal">
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>Subtotal <?=$rg["tipodepago"]?> </td>
                            <td class="moneda"><?= number_format($nImpS, 2) ?></td>
                            <td class="moneda"><?= number_format($nPagS, 2) ?></td>
                            <td class="moneda"><?= number_format($nCarS, 2) ?></td>
                            <td class="moneda"><?= number_format($nImporte, 2) ?></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>Total</td>
                            <td class="moneda"><?= number_format($nImp, 2) ?></td>
                            <td class="moneda"><?= number_format($nCar, 2) ?></td>
                            <td class="moneda"><?= number_format($nPag, 2) ?></td>
                            <td class="moneda"><?= number_format($nSal, 2) ?></td>
                        </tr>
                    </tfoot>
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
                                excluir clientes con saldo en ceros: 
                                <select name="Detallado" id="Detallado">
                                    <option value="No">No</option>
                                    <option value="Si">Si</option>
                                </select>
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

