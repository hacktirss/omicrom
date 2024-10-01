<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("data/CtDAO.php");
include_once ("data/VariablesDAO.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();

require './services/ReportesVentasService.php';

$ctDAO = new CtDAO();
$ctVO = $ctDAO->retrieve($Corte);

$Titulo = " Inventario de aceites por isla [Piso de venta] Corte: $Corte  / " . $ctVO->getFecha() . " Turno: " . $ctVO->getTurno() . "";

$registrosArray = array();
if ($Detallado === "Si") {
    $registros = utils\IConnection::getRowsFromQuery($selectInv3rad);

    foreach ($registros as $value) {
        $registrosArray[$value["producto"]][$value[isla_pos]]["inicial"] = $value["inicial"];
        $registrosArray[$value["producto"]][$value[isla_pos]]["entradas"] = $value["entradas"];
        $registrosArray[$value["producto"]][$value[isla_pos]]["ventas"] = $value["ventas"];
        $registrosArray[$value["producto"]][$value[isla_pos]]["total"] = $value["total"];
    }
    //error_log(print_r($registrosArray[11], TRUE));
}

$rows = utils\IConnection::getRowsFromQuery($selectInv3ra);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports.php'; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#Detallado").val("<?= $Detallado ?>");
                $("#Corte").val("<?= $Corte ?>");
                $("#IslaPosicion").val("<?= $IslaPosicion ?>");
                $("#Isla").hide();
                if ($("#Detallado").val() === "Si") {
                    $("#Isla").show();
                }
                $("#Detallado").change(function () {
                    if ($("#Detallado").val() === "Si") {
                        $("#Isla").show();
                    } else {
                        $("#Isla").hide();
                    }
                });
                $("#Detallado").focus();
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
                            <?php if ($Detallado === "Si") { ?>
                                <td>Isla</td>
                            <?php } ?>                                
                            <td>Producto</td>
                            <td>Descripcion</td>
                            <td>Inv. Inicial</td>
                            <td>Entradas</td>
                            <td>Ventas</td>
                            <td>Inv. Final</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $Inicial = $Ventas = $Compras = $Total = 0;
                        $SubInicial = $SubVentas = $SubCompras = $SubTotal = 0;
                        if ($Detallado === "Si") {
                            $Producto = 0;
                            $Isla_pos = 0;

                            foreach ($rows as $inv) {
                                if (!empty($Isla_pos) && $Isla_pos != $inv[isla_pos] && (empty($IslaPosicion) || $IslaPosicion === "*")) {
                                    ?>
                                    <tr class="subtotal">
                                        <td colspan="3">Subtotal</td>
                                        <td class="numero"><?= $SubInicial ?></td>
                                        <td class="numero"><?= $SubCompras ?></td>
                                        <td class="numero"><?= $SubVentas ?></td>
                                        <td class="numero"><?= $SubTotal ?></td>
                                    </tr>
                                    <?php
                                    $SubInicial = $SubVentas = $SubCompras = $SubTotal = 0;
                                }
                                ?>
                                <tr>
                                    <td><?= $inv["isla_pos"] ?></td>
                                    <td><?= $inv["producto"] ?></td>
                                    <td><?= $inv["descripcion"] ?></td>
                                    <td class="numero"><?= $registrosArray[$inv["producto"]][$inv["isla_pos"]]["inicial"] ?></td>
                                    <td class="numero"><?= $registrosArray[$inv["producto"]][$inv["isla_pos"]]["entradas"] ?></td>
                                    <td class="numero"><?= $registrosArray[$inv["producto"]][$inv["isla_pos"]]["ventas"] ?></td>
                                    <td class="numero"><?= $registrosArray[$inv["producto"]][$inv["isla_pos"]]["total"] ?></td>
                                </tr>
                                <?php
                                $Producto = $inv["producto"];
                                $Isla_pos = $inv[isla_pos];

                                $SubInicial += $registrosArray[$inv["producto"]][$inv["isla_pos"]]["inicial"];
                                $SubVentas += $registrosArray[$inv["producto"]][$inv["isla_pos"]]["ventas"];
                                $SubCompras += $registrosArray[$inv["producto"]][$inv["isla_pos"]]["entradas"];
                                $SubTotal += $registrosArray[$inv["producto"]][$inv["isla_pos"]]["total"];

                                $Inicial += $registrosArray[$inv["producto"]][$inv["isla_pos"]]["inicial"];
                                $Ventas += $registrosArray[$inv["producto"]][$inv["isla_pos"]]["ventas"];
                                $Compras += $registrosArray[$inv["producto"]][$inv["isla_pos"]]["entradas"];
                                $Total += $registrosArray[$inv["producto"]][$inv["isla_pos"]]["total"];
                            }
                        } else {
                            foreach ($rows as $inv) {
                                ?>
                                <tr>
                                    <td style="text-align: center"><?= $inv["idClave"] ?></td>
                                    <td><?= $inv["descripcion"] ?></td>
                                    <td class="numero"><?= $inv["inicial"] ?></td>
                                    <td class="numero"><?= $inv["entradas"] ?></td>
                                    <td class="numero"><?= $inv["ventas"] ?></td>
                                    <td class="numero"><?= $inv["total"] ?></td>
                                </tr>
                                <?php
                                $Inicial += $inv["inicial"];
                                $Ventas += $inv["ventas"];
                                $Compras += $inv["entradas"];
                                $Total += $inv["total"];
                            }
                            ?>
                        <?php } ?>
                    </tbody>
                    <tfoot>
                        <?php
                        if ($Detallado === "Si") {
                            if (empty($IslaPosicion) || $IslaPosicion === "*") {
                                ?>
                                <tr class="subtotal">
                                    <td colspan="3">Subtotal</td>
                                    <td class="numero"><?= $SubInicial ?></td>
                                    <td class="numero"><?= $SubCompras ?></td>
                                    <td class="numero"><?= $SubVentas ?></td>
                                    <td class="numero"><?= $SubTotal ?></td>
                                </tr>
                                <?php
                            }
                            ?>
                            <tr>
                                <td colspan="3">Total</td>
                                <td class="numero"><?= $Inicial ?></td>
                                <td class="numero"><?= $Compras ?></td>
                                <td class="numero"><?= $Ventas ?></td>
                                <td class="numero"><?= $Total ?></td>
                            </tr>
                        <?php } else { ?>
                            <tr>
                                <td colspan="2">Total</td>
                                <td class="numero"><?= $Inicial ?></td>
                                <td class="numero"><?= $Compras ?></td>
                                <td class="numero"><?= $Ventas ?></td>
                                <td class="numero"><?= $Total ?></td>
                            </tr>
                        <?php } ?>
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
                            <td id="Isla">
                                <table style="width: 100%" aria-hidden="true">
                                    <tr>
                                        <td style="text-align: right;">Isla o Dispensario</td>
                                        <td style="text-align: left; padding-left: 5px">
                                            <select id="IslaPosicion" name="IslaPosicion">
                                                <?php
                                                foreach ($IslasPosicion as $key => $value) {
                                                    echo "<option value='$key'>$value</option>";
                                                }
                                                ?>
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
                    <input type="hidden" name="Corte" id="Corte">
                </div>
            </form>
            <?php topePagina() ?>
        </div>
    </body>
</html>

