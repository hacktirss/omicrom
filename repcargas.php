<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesClientesService.php";

$Titulo = "Relacion de cargas por cliente del $FechaI al $FechaF";

$registros = utils\IConnection::getRowsFromQuery($selectCargas);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports.php'; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $('#FechaI').val('<?= $FechaI ?>').attr('size', '10');
                $('#FechaF').val('<?= $FechaF ?>').attr('size', '10');
                $('#cFechaI').css('cursor', 'hand').click(function () {
                    displayCalendar($('#FechaI')[0], 'yyyy-mm-dd', $(this)[0]);
                });
                $('#cFechaF').css('cursor', 'hand').click(function () {
                    displayCalendar($('#FechaF')[0], 'yyyy-mm-dd', $(this)[0]);
                });
            });
        </script>

    </head>

    <body>
        <div id='container'>
            <?php nuevoEncabezado($Titulo); ?>

            <div id="Reportes" style="min-height: 200px;"> 
                <table aria-hidden="true">
                    <thead>
                        <tr>
                            <td>Fecha</td>
                            <td>No.cargas</td>
                            <td>Litros</td>
                            <td>Importe</td>
                            <td>Litros C.</td>
                            <td>Pago real</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $Cliente = "";
                        foreach ($registros as $rg) {

                            if ($rg["cliente"] != $Cliente) {
                                if (!empty($Cliente)) {
                                    ?>
                                    <tr class="subtotal">
                                        <td>Total</td>
                                        <td><?= number_format($nCargas, 0) ?></td>
                                        <td><?= number_format($nLitros, 2) ?></td>
                                        <td class="moneda"><?= number_format($nImporte, 2) ?></td>
                                        <td><?= number_format($nVolCalc, 2) ?></td>
                                        <td><?= number_format($nPagoReal, 2) ?></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                <tr class="subtitulo"><td colspan='4'>Cuenta: <?= $rg["cliente"] ?> | <?= $rg["nombre"] ?></td></tr>
                                <?php
                                $Cliente = $rg["cliente"];
                                $nCargas = 0;
                                $nLitros = 0;
                                $nVolCalc = 0;
                                $nPagoReal = 0;
                                $nImporte = 0;
                            }
                            ?>
                            <tr>
                                <td><?= $rg["fecha"] ?></td>
                                <td class="numero"><?= $rg["cargas"] ?></td>
                                <td class="numero"><?= number_format($rg["litros"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["pesos"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["volumencalcu"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["pagoreal"], 2) ?></td>
                            </tr>
                            <?php
                            $nImpT += $rg["pesos"];
                            $nCarT += $rg["cargas"];
                            $nLitT += $rg["litros"];
                            $nCargas += $rg["cargas"];
                            $nImporte += $rg["pesos"];
                            $nLitros += $rg["litros"];
                            $nVolCalc += $rg["volumencalcu"];
                            $nPagoReal += $rg["pagoreal"];
                            $nVolCalcT += $rg["volumencalcu"];
                            $nPagoRealT += $rg["pagoreal"];
                            $nRng++;
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>Total</td>
                            <td><?= number_format($nCargas, 0) ?></td>
                            <td><?= number_format($nLitros, 0) ?></td>
                            <td class="moneda"><?= number_format($nImporte, 2) ?></td>
                            <td><?= number_format($nVolCalc, 2) ?></td>
                            <td><?= number_format($nPagoReal, 2) ?></td>
                        </tr>

                        <tr>
                            <td>Gran total</td>
                            <td><?= number_format($nCarT, 0) ?></td>
                            <td><?= number_format($nLitT, 0) ?></td>
                            <td class="moneda"><?= number_format($nImpT, 2) ?></td>
                            <td><?= number_format($nVolCalcT, 2) ?></td>
                            <td><?= number_format($nPagoRealT, 2) ?></td>
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
                            <td>
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

