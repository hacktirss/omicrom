<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

$Titulo = "Corte mensual al $Fecha ";

$selectTanquesH = " SELECT * FROM v_tanques_h WHERE  DATE(now) = DATE('$Fecha') ORDER BY producto DESC";
$selectInv = "SELECT * FROM v_inv WHERE DATE(now) = DATE('$Fecha')";
$selectInvd = "
                SELECT invd.id producto, invd.isla_pos, invd.existencia 
                FROM v_inv inv,v_invd invd
                WHERE 1 = 1 
                AND inv.id = invd.id
                AND inv.rubro = 'Aceites' AND inv.activo = 'Si'
                AND DATE(inv.now) = DATE('$Fecha') AND DATE(invd.now) = DATE('$Fecha')
                ORDER BY inv.id, invd.isla_pos";
$selectSaldos = " SELECT * FROM v_saldos WHERE  DATE(now) = DATE('$Fecha') ORDER BY orden,tipodepago,cliente;";
$selectCargosAbonos = " SELECT * FROM v_carabonos WHERE  DATE(now) = DATE('$Fecha') ORDER BY orden,tipodepago,cliente;";

$colspan = 6 + count($PosicionesInventario);

$registros1 = utils\IConnection::getRowsFromQuery($selectTanquesH);
$registros2 = utils\IConnection::getRowsFromQuery($selectInv);
$registrosIsla = utils\IConnection::getRowsFromQuery($selectInvd);
$registros3 = utils\IConnection::getRowsFromQuery($selectSaldos);
$registros4 = utils\IConnection::getRowsFromQuery($selectCargosAbonos);

foreach ($registrosIsla as $value) {
    $registrosArray[$value["producto"]][$value[isla_pos]] = $value["existencia"];
}
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#Fecha").val("<?= $Fecha ?>").attr("size", "10");
                $("#cFecha").css("cursor", "hand").click(function () {
                    displayCalendar($("#Fecha")[0], "yyyy-mm-dd", $(this)[0]);
                });
            });
        </script>
    </head>

    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo) ?>
            <div id="Reportes">
                <table aria-hidden="true">
                    <thead>
                        <tr class="titulo"><td colspan="5">Inventario de combustibles</td></tr>

                        <tr>
                            <td>Tanque</td>
                            <td>Producto</td>
                            <td>Fecha de lectura </td>
                            <td>Volumen actual</td>
                            <td>Temperatura</td>                    
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($registros1 as $rg) {
                            echo "<tr>";
                            echo "<td align='center'>" . $rg["tanque"] . "</td>";
                            echo "<td>" . ucwords(strtolower($rg["producto"])) . "</td>";
                            echo "<td>" . ucwords(strtolower($rg[fecha_hora_s])) . "</td>";
                            echo "<td class='numero'>" . number_format($rg[volumen_actual], 2) . "</td>";
                            echo "<td class='numero'>" . number_format($rg["temperatura"], 2) . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>

                <table aria-hidden="true">
                    <thead>
                        <tr class="titulo">
                            <td colspan="<?= $colspan ?>">Inventario de aceites</td>
                        </tr>
                        <tr>
                            <td colspan="4"></td>
                            <td colspan="<?= count($IslasPosicionInventario) ?>">Islas o Dispensarios</td>
                            <td colspan="2"> Totales</td>
                        </tr>
                        <tr>
                            <td>Producto </td>
                            <td>Descripcion</td>
                            <td>Costo </td>
                            <td>Almacen </td>
                            <?php foreach ($IslasPosicionInventario as $value) { ?>
                                <td><?= $value ?></td>
                            <?php } ?>
                            <td>Piezas </td>
                            <td>Importe </td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $Gtotal = $Almacen = $Total = 0;
                        $arrayP = null;
                        foreach ($registros2 as $rg) {
                            echo "<tr>";

                            echo "<td>" . $rg["id"] . "</td>";
                            echo "<td>" . $rg["descripcion"] . "</td>";
                            echo "<td class=\"numero\">" . number_format($rg["costo"], 2) . "</td>";
                            echo "<td class=\"numero\">" . number_format($rg["existencia"], 0) . "</td>";

                            $SubTotal = $rg["existencia"];
                            foreach ($IslasPosicionInventario as $value) {
                                echo '<td class="numero">' . $registrosArray[$rg["id"]][$value] . '</td>';
                                $SubTotal += $registrosArray[$rg["id"]][$value];
                                $arrayP[$value] += $registrosArray[$rg["id"]][$value];
                            }


                            echo "<td class=\"numero\">" . number_format($SubTotal, 0) . "</td>";
                            echo "<td class=\"numero\">" . number_format(($SubTotal * $rg["costo"]), 2) . "</td>";
                            echo "</tr>";

                            $Gtotal += $SubTotal;
                            $Almacen += $rg["existencia"];
                            $Total += ($SubTotal * $rg["costo"]);
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td><?= number_format($Almacen, 0) ?></td>
                            <?php
                            foreach ($arrayP as $rg) {
                                echo "<td>" . number_format($rg, 0) . "</td>";
                            }
                            ?>
                            <td><?= number_format($Gtotal, 0) ?></td>
                            <td><?= number_format($Total, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>

                <table aria-hidden="true">
                    <thead>
                        <tr class="titulo">
                            <td colspan="6">Saldos por cliente</td>
                        </tr>
                        <tr>
                            <td>No.Cta</td>
                            <td>Alias </td>
                            <td>Nombre </td>
                            <td>Tipo/cliente</td>
                            <td>Importe</td>
                            <td>Total</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $cTipoPago = '';
                        $SubTotal = 0;
                        foreach ($registros3 as $rg) {

                            if ($cTipoPago <> $registro["tipodepago"]) {
                                if (!empty($cTipoPago)) {
                                    $SubTotal += $nImporte;
                                    ?>
                                    <tr class="subtotal">
                                        <td></td>
                                        <td></td>
                                        <td>Subotal</td>
                                        <td></td>
                                        <td><?= number_format($nImporte, 2) ?></td>
                                        <td><?= number_format($SubTotal, 2) ?></td>
                                    </tr>
                                    <?php
                                }
                                $cTipoPago = $registro["tipodepago"];
                                $nImporteT += $nImporte;
                                $nImporte = 0;
                            }


                            echo "<tr>";
                            echo "<td align='center'> " . $rg["cliente"] . "</td>";
                            echo "<td>" . ucwords(strtolower($rg["alias"])) . "</td>";
                            echo "<td>" . ucwords(strtolower($rg["nombre"])) . "</td>";
                            echo "<td align='center'> " . $rg["tipodepago"] . "</td>";
                            echo "<td class='numero'> " . number_format($rg["importe"], 2) . "</td>";
                            echo "<td></td>";
                            echo "</tr>";

                            $nImporte += $rg["importe"];
                        }
                        $nImporteT += $nImporte;
                        $SubTotal += $nImporte;
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td></td>
                            <td>Subotal</td>
                            <td></td>
                            <td><?= number_format($nImporte, 2) ?></td>
                            <td><?= number_format($SubTotal, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>

                <table aria-hidden="true">
                    <thead>
                        <tr class="titulo">
                            <td colspan="6">Cargos, abonos y saldos</td>
                        </tr>
                        <tr>
                            <td>Cta</td>
                            <td>Nombre</td>
                            <td>Saldo Inicial</td>
                            <td>Cargos</td>
                            <td>Abonos</td>
                            <td>Importe</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($registros4 as $rg) {

                            echo "<tr>";
                            echo "<td>" . $rg["cliente"] . "</td>";
                            echo "<td>" . $rg["nombre"] . " </td>";
                            echo "<td class='numero''>" . number_format($rg["inicial"], 2) . "</td>";
                            echo "<td class='numero'>" . number_format($rg["cargos"], 2) . "</td>";
                            echo "<td class='numero'>" . number_format($rg["abonos"], 2) . "</td>";
                            echo "<td class='numero'>" . number_format($rg["importe"], 2) . "</td>";
                            echo "</tr>";

                            $nImp += $rg["inicial"];
                            $nCar += $rg["cargos"];
                            $nPag += $rg["abonos"];
                            $nSal += $rg["importe"];
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td>Total</td>
                            <td><?= number_format($nImp, 2) ?></td>
                            <td><?= number_format($nCar, 2) ?></td>
                            <td><?= number_format($nPag, 2) ?></td>
                            <td><?= number_format($nSal, 2) ?></td>
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
                            <td style="width: 70%;">
                                <table aria-hidden="true">
                                    <tr>
                                        <td>Fecha:</td>
                                        <td><input type="text" id="Fecha" name="Fecha"></td>
                                        <td class="calendario"><i id="cFecha" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
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
