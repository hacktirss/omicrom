<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesProveedoresService.php";

$Titulo = "Relacion de pagos por proveedor del $FechaI al $FechaF";

$registros = utils\IConnection::getRowsFromQuery($selectRPagos);
error_log($selectRPagos);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports.php'; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $('#FechaI').val('<?= $FechaI ?>').attr('size', '10').addClass('texto_tablas');
                $('#FechaF').val('<?= $FechaF ?>').attr('size', '10').addClass('texto_tablas');
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

        <div id="container">
            <?php nuevoEncabezado($Titulo); ?>
            <div id="Reportes" style="min-height: 200px;"> 
                 <table aria-hidden="true">
                    <thead
                        <tr>
                            <td># Pago</td>
                            <td>Fecha</td>
                            <td>Concepto</td>
                            <td>Importe</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $Proveedor = "";
                        foreach ($registros as $rg) {
                            if ($rg["proveedor"] !== $Proveedor) {
                                if ($Proveedor !== '') {
                                    ?>
                                    <tr class="subtotal">
                                        <td colspan="3">Saldo</td>
                                        <td class="moneda"><?= number_format(abs($nImporte), 2) ?></td>
                                    </tr>
                                <?php } ?>
                                <tr class="subtitulo">
                                    <td colspan="4">Cuenta: <?= $rg["proveedor"] . " | " . $rg["nombre"] ?></td>
                                </tr>
                                <?php
                                $Proveedor = $rg["proveedor"];
                                $nImporte = 0;
                            }
                            ?>
                            <tr>
                                <td class="numero"><?= $rg["pago"] ?></td>
                                <td><?= $rg["fecha"] ?></td>
                                <td><?= $rg["concepto"] ?></td>
                                <td class="numero"><?= number_format(abs($rg["importe"]), 2) ?></td>
                            </tr>
                            <?php
                            $nImpT += $rg["importe"];
                            $nImporte += $rg["importe"];
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3">Saldo</td>
                            <td class="moneda"><?= number_format(abs($nImporte), 2) ?></td>
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