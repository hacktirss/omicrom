<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

$Titulo = "Venta por $Desglose del $FechaI al $FechaF [Reporte Contable]";

$registros = utils\IConnection::getRowsFromQuery($selectByProducto);

$Id = 33; /* Número de en el orden de la tabla submenus */
$data = array("Nombre" => $Titulo, "Reporte" => $Id,
    "FechaI" => $FechaI, "FechaF" => $FechaF, "Desglose" => $Desglose);
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
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
                $("#Desglose").val("<?= $Desglose ?>");
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
                            <td colspan="2">Concepto</td>
                            <td colspan="3">Litros</td>
                            <td colspan="4">Importe</td>
                        </tr>
                        <tr>
                            <td>Producto</td>
                            <td>#Ventas</td>
                            <td>Normal</td>
                            <td>Consignacion</td>
                            <td>Total</td>
                            <td>Normal</td>
                            <td>Descuento</td>
                            <td>Consignacion</td>
                            <td>Total</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $cPrd = "";
                        foreach ($registros as $rg) {
                            if ($cPrd <> $rg["producto"]) {
                                if (!empty($cPrd)) {
                                    ?>
                                    <tr>
                                        <td><?= $cPrd ?></td>
                                        <td class="numero"><?= number_format($nVts, 0) ?></td>
                                        <td class="numero"><?= number_format($nLtsNor, 2) ?></td>
                                        <td class="numero"><?= number_format($nLtsCon, 2) ?></td>
                                        <td class="numero"><?= number_format($nLtsNor + $nLtsCon, 2) ?></td>
                                        <td class="numero"><?= number_format($nImpNor, 2) ?></td>
                                        <td class="numero"><?= number_format($nDesc, 2) ?></td>
                                        <td class="numero"><?= number_format($nImpCon, 2) ?></td>
                                        <td class="numero"><?= number_format($nImpNor + $nImpCon - $nDesc, 2) ?></td>
                                    </tr>
                                    <?php
                                }

                                $cPrd = $rg["producto"];

                                $nImpNorT += $nImpNor;
                                $nImpConT += $nImpCon;
                                $nLtsNorT += $nLtsNor;
                                $nLtsConT += $nLtsCon;
                                $nVtsT += $nVts;
                                $nDescT += $nDesc;
                                $nVts = $nImpNor = $nImpCon = $nLtsNor = $nLtsCon = $nDesc = 0;
                            }

                            $nImpCon += $rg["pesosN"];
                            $nLtsCon += $rg["volumenN"];
                            $nVts += $rg["ventas"];
                            $nImpNor += $rg["pesos"];
                            $nLtsNor += $rg["volumen"];
                            $nDesc += $rg["descuento"];
                        }
                        $nDescT += $nDesc;
                        ?>

                        <tr>
                            <td><?= $cPrd ?></td>
                            <td class="numero"><?= number_format($nVts, 0) ?></td>
                            <td class="numero"><?= number_format($nLtsNor, 2) ?></td>
                            <td class="numero"><?= number_format($nLtsCon, 2) ?></td>
                            <td class="numero"><?= number_format($nLtsNor + $nLtsCon, 2) ?></td>
                            <td class="numero"><?= number_format($nImpNor, 2) ?></td>
                            <td class="numero"><?= number_format($nDesc, 2) ?></td>
                            <td class="numero"><?= number_format($nImpCon, 2) ?></td>
                            <td class="numero"><?= number_format($nImpNor + $nImpCon - $nDesc, 2) ?></td>
                        </tr>
                    </tbody>

                    <tfoot>
                        <?php
                        $nImpNorT += $nImpNor;
                        $nImpConT += $nImpCon;
                        $nLtsNorT += $nLtsNor;
                        $nLtsConT += $nLtsCon;
                        $nVtsT += $nVts;
                        ?>
                        <tr>
                            <td>Total</td>
                            <td><?= number_format($nVtsT, 0) ?></td>
                            <td><?= number_format($nLtsNorT, 2) ?></td>
                            <td><?= number_format($nLtsConT, 2) ?></td>
                            <td><?= number_format($nLtsNorT + $nLtsConT, 2) ?></td>
                            <td class="moneda"><?= number_format($nImpNorT, 2) ?></td>
                            <td class="moneda"><?= number_format($nDescT, 2) ?></td>
                            <td class="moneda"><?= number_format($nImpConT, 2) ?></td>
                            <td class="moneda"><?= number_format($nImpNorT + $nImpConT - $nDescT, 2) ?></td>
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
                                <table style="width: 100%" aria-hidden="true">
                                    <tr>
                                        <td>&nbsp;Desglose:</td>
                                        <td style="text-align: left;padding-left: 5px">
                                            <select id="Desglose" name="Desglose">
                                                <?php
                                                $TDesglose = utils\IConnection::execSql("SELECT valor FROM variables_corporativo WHERE llave='Rep_gvc_visual'");
                                                if ($TDesglose["valor"] == 0) {
                                                    ?>
                                                    <option value="Cortes">Cortes</option>
                                                    <?php
                                                } elseif ($TDesglose["valor"] == 1) {
                                                    ?>
                                                    <option value="Dia">Dia</option>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <option value="Cortes">Cortes</option>
                                                    <option value="Dia">Dia</option>
                                                    <?php
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <span><input type="submit" name="Boton" value="Enviar"></span>
                                <?php
                                if ($usuarioSesion->getTeam() !== "Operador") {
                                    ?>
                                    <span class="ButtonExcel"><a href="report_excel_reports.php?<?= http_build_query($data) ?>"><i class="icon fa fa-lg fa-bold fa-file-excel-o" aria-hidden="true"></i></a></span>
                                    <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                                            <?php
                                        }
                                        ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
            <?php topePagina(); ?>
        </div>
    </body>
</html>

