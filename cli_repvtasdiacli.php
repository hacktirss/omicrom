<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$Cliente = utils\HTTPUtils::getSessionValue("Cuenta");

require "./services/ReportesVentasService.php";

$DetalleTexto = $Detallado === "Si" ? "detallado" : "";
$Titulo = "Ventas por $Desglose del $FechaI al $FechaF $DetalleTexto ";

$registros = utils\IConnection::getRowsFromQuery($selectByDiaCli);
//error_log($selectByDiaCli);
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
                $("#Detallado").val("<?= $Detallado ?>");
                $("#ClienteS").val("<?= $Cliente ?>");
            });
        </script>
    </head>

    <body>

        <div id="container">
            <?php nuevoEncabezado($Titulo); ?>
            <div id="Reportes" style="min-height: 200px;"> 

                <?php if ($Detallado === "Si") { ?>

                    <table aria-hidden="true">
                        <thead>
                            <tr class="titulo">
                                <td></td>
                                <?php
                                foreach ($Productos as $producto) {
                                    echo "<td colspan=\"2\"> " . $producto["descripcion"] . "</td>";
                                }
                                ?>
                                <td colspan="5">Totales</td>
                            </tr>

                            <tr>
                                <td> Fecha</td>
                                <?php
                                foreach ($Productos as $producto) {
                                    echo "<td>Litros</td>";
                                    echo "<td>Importe</td>";
                                }
                                ?>
                                <td>No.vtas</td>
                                <td>Litros</td>
                                <td>Importe</td>
                                <td>Aceites</td>
                                <td>Total</td>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            $Vts = $impAce = 0;
                            foreach ($registros as $rg) {
                                echo "<tr>";
                                echo "<td>" . $rg["fecha"] . "</td>";
                                $imp = $vol = 0;
                                foreach ($Productos as $producto) {
                                    $colImporte = "pesos" . $producto["id"];
                                    $colVolumen = "volumen" . $producto["id"];
                                    echo "<td class=\"numero\">" . number_format($rg[$colVolumen], 2) . "</td>";
                                    echo "<td class=\"numero\">" . number_format($rg[$colImporte], 2) . "</td>";
                                    $imp += $rg[$colImporte];
                                    $vol += $rg[$colVolumen];
                                    $nImp[$producto["id"]] += $rg[$colImporte];
                                    $nVol[$producto["id"]] += $rg[$colVolumen];
                                }
                                echo "<td class=\"numero\">" . number_format($rg["ventas"], 0) . "</td>";
                                echo "<td class=\"numero\">" . number_format($vol, 2) . "</td>";
                                echo "<td class=\"numero\">" . number_format($imp, 2) . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg["pesosA"], 2) . "</td>";
                                echo "<td class=\"numero\">" . number_format($imp + $rg["pesosA"], 2) . "</td>";

                                $Vts += $rg["ventas"];
                                $impAce += $rg["pesosA"];
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>Total</td>
                                <?php
                                foreach ($Productos as $producto) {
                                    echo "<td>" . number_format($nVol[$producto["id"]], 2) . "</td>";
                                    echo "<td class=\"moneda\">" . number_format($nImp[$producto["id"]], 2) . "</td>";
                                    $tVol += $nVol[$producto["id"]];
                                    $tImp += $nImp[$producto["id"]];
                                }
                                ?>
                                <td><?= number_format($Vts, 0) ?></td>
                                <td><?= number_format($tVol, 2) ?></td>
                                <td class="moneda"><?= number_format($tImp, 2) ?></td>
                                <td class="moneda"><?= number_format($impAce, 2) ?></td>
                                <td class="moneda"><?= number_format($tImp + $impAce, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>

                <?php } else { /* No detallado */ ?>
                    <table aria-hidden="true">
                        <thead>
                            <tr>
                                <td> Fecha</td>
                                <td> No.de ventas</td>
                                <td> Litros</td>
                                <td> Importe</td>
                                <td> Aceites</td>
                                <td> Total</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $nImpAce = $nVts = $nImp = $nLts = 0;
                            foreach ($registros as $rg) {
                                ?>
                                <tr>
                                    <td><?= $rg["fecha"] ?></td>
                                    <td class="numero"><?= number_format($rg["ventas"], 0) ?></td>
                                    <td class="numero"><?= number_format($rg["volumen"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["pesos"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["pesosA"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["pesos"] + $rg["pesosA"], 2) ?></td>
                                </tr>
                                <?php
                                $nImpAce += $rg["pesosA"];
                                $nVts += $rg["ventas"];
                                $nImp += $rg["pesos"];
                                $nLts += $rg["volumen"];
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>Total</td>
                                <td><?= number_format($nVts, 0) ?></td>
                                <td><?= number_format($nLts, 3) ?></td>
                                <td class="moneda"><?= number_format($nImp, 2) ?></td>
                                <td class="moneda"><?= number_format($nImpAce, 2) ?></td>
                                <td class="moneda"><?= number_format($nImp + $nImpAce, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>

                <?php } ?>

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
                                        <td><input type="text" id="FechaI" name="FechaI" alt="Calendario"></td>
                                        <td class="calendario"><i id="cFechaI" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
                                    </tr>
                                    <tr>
                                        <td>F.final:</td>
                                        <td><input type="text" id="FechaF" name="FechaF" alt="Calendario"></td>
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
                                </table>
                            </td>
                            <td>
                                <span><input type="submit" name="Boton" value="Enviar"></span>
                                <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                            </td>
                        </tr>
                    </table>
                </div>
                <input type="hidden" name="ClienteS" id="ClienteS">
            </form>
            <?php topePagina(); ?>
        </div>
    </body>
</html>
