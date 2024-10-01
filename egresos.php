<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

$Titulo = "Reporte de gastos del " . $FechaI . "al" . $FechaF;

$selectPagos = "
        SELECT ctpagos.*,DATE(ct.fecha) fechaCorte 
        FROM ct,ctpagos
        WHERE ct.id = ctpagos.corte AND DATE(ct.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
        ORDER BY ct.id ";

$registros = utils\IConnection::getRowsFromQuery($selectPagos);
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
            <div  id="Reportes" style="min-height: 200px;">

                 <table aria-hidden="true">
                    <thead>
                        <tr>
                            <td>Corte</td>
                            <td>Fecha</td>
                            <td>Fecha de captura</td>
                            <td>Concepto</td>
                            <td>Importe</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $nImpT = 0;
                        foreach ($registros as $rg) {
                            ?>
                            <tr>
                                <td><?= $rg["corte"] ?></td>
                                <td><?= $rg["fechaCorte"] ?></td>
                                <td><?= $rg["fecha"] ?></td>
                                <td><?= ucwords(strtolower($rg["concepto"])) ?></td>
                                <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                            </tr>
                            <?php
                            $nImpT += $rg["importe"];
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>Gran total</td>
                            <td><?= number_format($nImpT, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>

            </div>

            <div id="footer">
                <form name="form1" class="oculto" method="get" action="">
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
                                <td align="center">
                                    <a class="nombre_cliente" href="ingresos.php">Depositos</a> 
                                </td>
                            </tr>
                        </table>
                    </div>
                </form>
                <?php topePagina(); ?>
            </div>
    </body>
