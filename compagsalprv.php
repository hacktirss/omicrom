<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesProveedoresService.php";

$Titulo = "Relacion de compras,pagos y saldos por proveedor del $FechaI al $FechaF";

$registros = utils\IConnection::getRowsFromQuery($selectC_P_S);
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
            <?php nuevoEncabezado($Titulo); ?>

            <div id="Reportes">
                 <table aria-hidden="true">
                    <thead>
                        <tr>
                            <td>Cta</td>
                            <td>Proveedor</td>
                            <td>Saldo Inicial</td>
                            <td>Cargos</td>
                            <td>Abonos</td>
                            <td> Importe</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $cTipoPago = "";
                        foreach ($registros as $rg) {
                            ?>
                            <tr>
                                <td><?= $rg["proveedor"] ?></td>
                                <td><?= $rg["nombre"] ?></td>
                                <td class="numero"><?= number_format($rg["inicial"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["cargos"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["abonos"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                            </tr>
                            <?php
                            $nImp += $rg["inicial"];
                            $nCar += $rg["cargos"];
                            $nPag += $rg["abonos"];
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td> Total</td>
                            <td><?= number_format($nImp, 2) ?></td>
                            <td><?= number_format($nCar, 2) ?></td>
                            <td><?= number_format($nPag, 2) ?></td>
                            <td><?= number_format($nImp + $nCar - $nPag, 2) ?></td>
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