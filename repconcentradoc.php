<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("data/CtDAO.php");

use com\softcoatl\utils as utils;

require './services/ReportesVentasService.php';

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();

$ctDAO = new CtDAO();
$ctVO = $ctDAO->retrieve($Corte);

$Titulo = "Corte: $Corte  / " . $ctVO->getFecha() . " Turno: " . $ctVO->getTurno() . "";

$registros = utils\IConnection::getRowsFromQuery($selectVentaCorteDiferencia);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports.php'; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#Corte").val("<?= $Corte ?>");
            });
        </script>
    </head>

    <body>
        <div id="container">  
            <?php nuevoEncabezado($Titulo); ?>
            <div id="Reportes">
                 <table aria-hidden="true">
                    <thead>
                        <tr class="titulo">
                            <td></td>
                            <td colspan="6">Venta</td>
                            <td colspan="6">Venta contable</td>
                            <td colspan="2">Ieps</td>
                            <td></td>
                        </tr>
                        <tr class="titulo">
                        <td ></td>
                        <td colspan="2">Venta Normal</td>
                        <td colspan="2">Venta Consignacion</td>
                        <td colspan="2">Gran Total</td>
                        <td colspan="2">Venta Normal</td>
                        <td colspan="2">Venta Consignacion</td>
                        <td colspan="2">Gran Total</td>
                        </tr>
                        <tr>
                            <td>Producto</td>
                            <td>Litros</td>
                            <td>Importe</td>
                            <td>Litros</td>
                            <td>Importe</td>
                            <td>Litros</td>
                            <td>Importe</td>
                            <td>Litros</td>
                            <td>Importe</td>
                            <td>Litros</td>
                            <td>Importe</td>
                            <td>Litros</td>
                            <td>Importe</td>
                            <td>Litros</td>
                            <td>Importe</td>
                            <td></td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($registros as $rg) {
                            ?>
                            <tr>
                                <td><?= $rg["producto"] ?></td>
                                <td class="numero"><?= number_format($rg["volumenD"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["pesosD"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["volumenN"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["pesosN"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["volumenT"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["pesosT"], 2) ?></td>

                                <td class="numero"><?= number_format($rg["volumenpD"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["pesospD"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["volumenpN"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["pesospN"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["volumenpT"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["pesospT"], 2) ?></td>

                                <td class="numero"><?= number_format(($rg["volumenD"] + $rg["volumenN"])- ($rg["volumenpD"] + $rg["volumenpN"] ), 2) ?></td>

                                <td class="numero"><?= number_format((($rg["volumenD"] + $rg["volumenN"])- ($rg["volumenpD"] + $rg["volumenpN"] )) * $rg["precio"], 2) ?></td>

                                <?php
                                if ($rg["pesospD"] != 0) {
                                    ?>
                                    <td class="numero"><?= number_format(( ($rg["pesosD"] + $rg["pesosN"]) / ($rg["pesospD"] + $rg["pesospN"]) - 1 ) * 100, 2) ?></td>
                                    <?php
                                } else {
                                    ?>
                                    <td class="numero">0</td>
                                    <?php
                                }
                                ?>
                            </tr>
                            <?php
                            $nVol += $rg["volumenD"];
                            $nPes += $rg["pesosD"];
                            $nVolN += $rg["volumenN"];
                            $nPesN += $rg["pesosN"];

                            $nVolp += $rg["volumenpD"];
                            $nPesp += $rg["pesospD"];
                            $nVolpN += $rg["volumenpN"];
                            $nPespN += $rg["pesospN"];

                            $nVolT += $rg["volumenT"];
                            $nPesT += $rg["pesosT"];
                            $nVolpT += $rg["volumenpT"];
                            $nPespT += $rg["pesospT"];

                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>Total</td>
                            <td><?= number_format($nVol, 2) ?></td>
                            <td><?= number_format($nPes, 2) ?></td>
                            <td><?= number_format($nVolN, 2) ?></td>
                            <td><?= number_format($nPesN, 2) ?></td>

                            <td><?= number_format($nVolT, 2) ?></td>
                            <td><?= number_format($nPesT, 2) ?></td>

                            <td><?= number_format($nVolp, 2) ?></td>
                            <td><?= number_format($nPesp, 2) ?></td>
                            <td><?= number_format($nVolpN, 2) ?></td>
                            <td><?= number_format($nPespN, 2) ?></td>
                            <td><?= number_format($nVolpT, 2) ?></td>
                            <td><?= number_format($nPespT, 2) ?></td>

                            <td><?= number_format(($nVol + $nVolN)- ($nVolp + $nVolpN), 2) ?></td>
                            <td><?= number_format(($nPes - $nPesp)+ ($nPesN - $nPespN), 2) ?></td>
                            <td><?= number_format((((($nPes + $nPesN)) / ($nPesp + $nPespN) ) - 1 ) * 100, 2) ?></td>
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
                                <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                            </td>
                        </tr>
                    </table>
                    <input type="hidden" name="Corte" id="Corte">
                </div>
            </form>
            <?php topePagina() ?>
        </div>
    </body>
</html>

