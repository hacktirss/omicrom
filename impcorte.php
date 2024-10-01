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
$usuarioSesion = getSessionUsuario();

if ($request->hasAttribute("busca")) {
    header("location: impcorte.php?Corte=" . $request->getAttribute("busca"));
}

$ctDAO = new CtDAO();
$ctVO = $ctDAO->retrieve($Corte);
$Isla = $ctVO->getIsla();

$Titulo = " Corte de turno: Id[$Corte]  Fecha: " . $ctVO->getFecha() . " Isla: " . $Isla . " Turno: " . $ctVO->getTurno();

$Precios = utils\IConnection::getRowsFromQuery($selectPreciosByCorte);

if (!($mysqli->query($selectVentaByCorteAbierto))) {
    error_log($mysqli->error);
}
$SqlTipoDispensario = "SELECT marca FROM omicrom.man where activo='Si' limit 1;";
$TD = utils\IConnection::execSql($SqlTipoDispensario);
if ($TD["marca"] === "T") {
    $MarcaDis = false;
} else {
    $MarcaDis = true;
}

$registros = utils\IConnection::getRowsFromQuery("SELECT * FROM corte_tmp", $mysqli);

$registrosD = utils\IConnection::getRowsFromQuery($selectVentaByCorteAbiertoDetalle, $mysqli);

$registrosT = utils\IConnection::getRowsFromQuery($selectVentaByCorteAbiertoT, $mysqli);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
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

            <table style="width: 100%;" aria-hidden="true">
                <tr>
                    <td width="30%" valign="top">
                        <div class="texto_tablas">Hora inicial: <?= $ctVO->getHora() ?></div>
                        <div class="texto_tablas">Hora final:   <?= date("H:i:s", strtotime($ctVO->getFechaf())) ?></div>
                    </td>
                    <td width="40%">
                        &nbsp;
                    </td>
                    <td width="30%" align="right">
                        <table style="width: 100%;" aria-hidden="true">
                            <?php
                            foreach ($Precios as $rg) {
                                ?>
                                <tr class="texto_tablas">
                                    <td align="right"><?= ucwords(strtolower($rg["descripcion"])) . ": " ?></td>
                                    <td align="right" style="background-color: #CACACA"><?= "$ " . $rg["precio"] ?></td>
                                </tr>
                                <?php
                            }
                            ?>
                        </table>
                    </td>
                </tr>
            </table>

            <div id="Reportes" style="min-height: 200px;">

                <div id="Reportes">
                    <table aria-hidden="true">
                        <thead>
                            <tr class="titulo">
                                <td colspan="5"></td>
                                <td colspan="6">Litros</td>
                                <?php
                                if ($MarcaDis) {
                                    ?>
                                    <td colspan="6">Importes</td>
                                    <?php
                                }
                                ?>
                                <td></td>
                                <td></td>
                            </tr>

                            <tr>
                                <td>Isla</td>
                                <td>Pos</td>
                                <td>Mang</td>
                                <td>Producto</td>
                                <td>#Vtas</td>

                                <td>Lect.Ini</td>
                                <td>Lect.Fin</td>
                                <td>Venta</td>
                                <td>Jarreo</td>
                                <td>Consig.</td>
                                <td>Total</td>
                                <?php
                                if ($MarcaDis) {
                                    ?>
                                    <td>Lect.Ini</td>
                                    <td>Lect.Fin</td>
                                    <td>Venta</td>
                                    <td>Jarreo</td>
                                    <td>Consig.</td>
                                    <td>Total</td>
                                    <?php
                                }
                                ?>
                                <td   class="DescuentoT">Descuento</td>
                                <td>Ven</td>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                            $Disp = "";
                            $DispAG = $groupcorte["valor"] == '1' ? "posicion" : "isla_pos";
                            $CountV = 0;
                            foreach ($registros as $rg) {
                                if ($Disp <> $rg["$DispAG"]) {
                                    if (!empty($Disp)) {
                                        ?>
                                        <tr class="subtotal">
                                            <td colspan="4">Total:</td>
                                            <td><?= $CountV ?></td>
                                            <td></td>
                                            <td></td>
                                            <td><?= number_format($TotL, 3) ?></td>
                                            <td><?= number_format($JarL, 3) ?></td>
                                            <td><?= number_format($ConsigL, 3) ?></td>
                                            <td><?= number_format($TotL - $JarL - $ConsigL, 3) ?></td>
                                            <?php
                                            if ($MarcaDis) {
                                                ?>
                                                <td></td>
                                                <td></td>
                                                <td><?= number_format($TotI, 2) ?></td> 
                                                <td><?= number_format($JarI, 2) ?></td>
                                                <td><?= number_format($ConsigI, 2) ?></td>
                                                <td><?= number_format($TotI - $JarI - $ConsigI, 2) ?></td>   
                                                <?php
                                            }
                                            ?>
                                            <td  class="DescuentoT DescuentoTxt"><?= number_format($Descuento, 2) ?></td>
                                            <td></td>
                                        </tr>
                                        <?php
                                        $CountV = 0;
                                    }
                                    $Disp = $rg["$DispAG"];
                                    $TotL = $TotI = $JarI = $JarL = $ConsigL = $ConsigI = $Descuento = 0;
                                }
                                ?>

                                <tr>
                                    <td class="numero"><?= $rg["isla_pos"] ?></td>
                                    <td class="numero"><?= cZeros($rg["posicion"], 2) ?></td>
                                    <td class="numero"><?= $rg["manguera"] ?></td>
                                    <td><?= $rg["producto"] ?></td>
                                    <td class="numero"><?= number_format($rg["ventas_d"] + $rg["ventas_j"], 0) ?></td>
                                    <?php
                                    $i_vol = "i_vol" . $rg["manguera"];
                                    $f_vol = "f_vol" . $rg["manguera"];
                                    $vol = "vol" . $rg["manguera"];

                                    $i_mon = "i_mon" . $rg["manguera"];
                                    $f_mon = "f_mon" . $rg["manguera"];
                                    $mon = "mon" . $rg["manguera"];
                                    ?>
                                    <td class="numero"><?= number_format($rg[$i_vol], 3) ?></td>
                                    <td class="numero"><?= number_format($rg[$f_vol], 3) ?></td>
                                    <td class="numero"><?= number_format($rg[$vol], 3) ?></td>
                                    <td class="numero"><?= number_format($rg["volumen_j"], 3) ?></td>
                                    <td class="numero"><?= number_format($rg["volumen_n"], 3) ?></td>
                                    <td class="numero"><?= number_format($rg[$vol] - $rg["volumen_j"] - $rg["volumen_n"], 3) ?></td>
                                    <?php
                                    $Array[cZeros($rg["posicion"], 2)] += $rg[$mon] - $rg["pesos_j"] - $rg["pesos_n"];
                                    if ($MarcaDis) {
                                        ?>
                                        <td class="numero"><?= number_format($rg[$i_mon], 2) ?></td>
                                        <td class="numero"><?= number_format($rg[$f_mon], 2) ?></td>
                                        <td class="numero"><?= number_format($rg[$mon], 2) ?></td>
                                        <td class="numero"><?= number_format($rg["pesos_j"], 2) ?></td>
                                        <td class="numero"><?= number_format($rg["pesos_n"], 2) ?></td>
                                        <td class="numero"><?= number_format($rg[$mon] - $rg["pesos_j"] - $rg["pesos_n"], 2) ?></td>
                                        <?php
                                    }
                                    ?>
                                    <td class="numero DescuentoT DescuentoTxt"><?= number_format($rg["descuento"], 2) ?></td>
                                    <td><?= $rg["ven"] ?></td>
                                </tr>
                                <?php
                                $CountV += $rg["ventas_d"] + $rg["ventas_j"];
                                /* Totales a desplegar por isla posicion */

                                $Vtas += $rg["ventas_d"] + $rg["ventas_j"];

                                $TotI += $rg[$mon];  //Totalizadores
                                $TotL += $rg[$vol]; //Totalizadores

                                $JarL += $rg["volumen_j"];
                                $JarI += $rg["pesos_j"];

                                $ConsigLT += $rg["volumen_n"];
                                $ConsigIT += $rg["pesos_n"];
                                $ConsigL += $rg["volumen_n"];
                                $ConsigI += $rg["pesos_n"];
                                $Descuento += $rg["descuento"];
                                /* Totales por isla_posicion */

                                $Imp += $rg[$mon];
                                $Lts += $rg[$vol];
                                $DescuentoT += $rg["descuento"];
                                $VImpj += $rg["pesos_j"];
                                $VLtsj += $rg["volumen_j"];
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4">Total Isla: <?= $Disp ?></td>
                                <td><?= $CountV ?></td>
                                <td></td>
                                <td></td>
                                <td><?= number_format($TotL, 3) ?></td>
                                <td><?= number_format($JarL, 3) ?></td>
                                <td><?= number_format($ConsigL, 3) ?></td>
                                <td><?= number_format($TotL - $JarL, 3) ?></td>
                                <?php
                                if ($MarcaDis) {
                                    ?>
                                    <td></td>
                                    <td></td>
                                    <td><?= number_format($TotI, 2) ?></td> 
                                    <td><?= number_format($JarI, 2) ?></td>
                                    <td><?= number_format($ConsigI, 2) ?></td>
                                    <td><?= number_format($TotI - $JarI, 2) ?></td>   
                                    <?php
                                }
                                ?>
                                <td  class="DescuentoT DescuentoTxt"><?= number_format($Descuento, 2) ?></td>
                                <td></td>
                            </tr>

                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>     
                                <td>Totales </td>        
                                <td><?= $Vtas ?></td>  
                                <td></td>
                                <td></td>
                                <td><?= number_format($Lts, 3) ?></td>
                                <td><?= number_format($VLtsj, 3) ?></td>
                                <td><?= number_format($ConsigLT, 3) ?></td>
                                <td><?= number_format($Lts - $VLtsj - $ConsigLT, 3) ?></td>
                                <?php
                                if ($MarcaDis) {
                                    ?>
                                    <td></td>
                                    <td></td>
                                    <td><?= number_format($Imp, 2) ?></td>
                                    <td><?= number_format($VImpj, 2) ?></td>
                                    <td><?= number_format($ConsigIT, 2) ?></td>
                                    <td><?= number_format($Imp - $VImpj - $ConsigIT, 2) ?></td>
                                    <?php
                                }
                                ?>
                                <td  class="DescuentoT"><?= number_format($DescuentoT, 2) ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div id="Reportes">
                    <table aria-hidden="true">
                        <thead>
                            <tr class="titulo">
                                <td colspan="10">C O M B U S T I B L E S</td>
                                <td colspan="3">A C E I T E S</td>
                                <td colspan="4">GRAN TOTAL</td>
                            </tr>

                            <tr>
                                <td>Isla</td>
                                <td>Pos</td>
                                <td>Vtas</td>
                                <td>Litros</td>
                                <td>Efec</td>
                                <td>Cred.y prep</td>
                                <td>Vales</td>
                                <td>Tarjeta</td>
                                <td>Monederos</td>
                                <td>Total</td>
                                <td>Efectivo</td>
                                <td>Cred/Tar</td>
                                <td>Total</td>
                                <td>Efectivo</td>
                                <td>Total</td>
                                <td class="DescuentoT">Descuento</td>
                                <td class="DescuentoT"></td>
                                <?php
                                if ($MarcaDis) {
                                    ?>
                                    <td>Dif</td>
                                    <?php
                                }
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $nReg = 0;
                            $nVtas = $nLts = $nEfe = $nCre = $nVal = $nTar = $nMon = $nVenta = $nAceE = $nAceC = $nVenta_a = 0;
                            $subVtas = $subLts = $subEfe = $subCre = $subVal = $subTar = $subMon = $subVenta = $subAceE = $subAceC = $subVenta_a = $descuento = 0;
                            $e = 0;
                            foreach ($registrosD as $rg) {
                                $dif = 0;
                                $Clr = "";
                                if ($e == 0) {
                                    $Tt = $rg["venta"] - $rg["descuento"];
                                } else {
                                    $Tt = $rg["venta"];
                                }
                                if ($Array[cZeros($rg["posicion"], 2)] <> $Tt) {
                                    $dif = $Array[cZeros($rg["posicion"], 2)] - $Tt - $rg["descuento"];
                                    error_log("Dif.Posicion: " . $dif . " ,Tt:" . $Tt . " ,VariablePosicion: " . $rg["posicion"]);
                                    if ($dif > 0.02 || $dif < -0.02) {
                                        $Clr = "style='color:red'";
                                    }
                                }
                                ?>
                                <tr>
                                    <td><?= $rg["isla_pos"] ?></td>
                                    <td><?= cZeros($rg["posicion"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["ventas"], 0) ?></td>
                                    <td class="numero"><?= number_format($rg["volumen"], 3) ?></td>
                                    <td class="numero"><?= number_format($rg["efectivo"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["credito"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["vales"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["tarjeta"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["monederos"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["venta"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["efectivo_a"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["credito_a"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["venta_a"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["efectivo"] + $rg["efectivo_a"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["venta"] + $rg["venta_a"], 2) ?></td>
                                    <td class="numero DescuentoT DescuentoTxt"><?= number_format($rg["descuento"], 2) ?></td>
                                    <td class="numero DescuentoT"><?= number_format($rg["venta"] + $rg["venta_a"] - $rg["descuento"], 2) ?></td>
                                    <?php
                                    if ($MarcaDis) {
                                        ?>
                                        <td <?= $Clr ?> class="numero"><?= number_format($dif, 1) ?></td>
                                        <?php
                                    }
                                    ?>
                                </tr>
                                <?php
                                $descuento += $rg["descuento"];
                                $subVtas += $rg["ventas"];
                                $subLts += $rg["volumen"];
                                $subEfe += $rg["efectivo"];
                                $subCre += $rg["credito"];
                                $subVal += $rg["vales"];
                                $subTar += $rg["tarjeta"];
                                $subMon += $rg["monederos"];
                                $subVenta += $rg["venta"];
                                $subDesc += $rg["descuento"];
                                $subAceE += $rg["efectivo_a"];
                                $subAceC += $rg["credito_a"];
                                $subVenta_a += $rg["venta_a"];
                                if ($MarcaDis) {
                                    $Diferencia += $dif;
                                }
                                if ($registrosD[$nReg + 1]["isla_pos"] !== $rg["isla_pos"]) {
                                    ?>
                                    <tr class="subtotal">
                                        <td colspan="2">Isla: <?= $rg["isla_pos"] ?></td>                                        
                                        <td><?= number_format($subVtas, 0) ?></td>
                                        <td><?= number_format($subLts, 3) ?></td>
                                        <td><?= number_format($subEfe, 2) ?></td>
                                        <td><?= number_format($subCre, 2) ?></td>
                                        <td><?= number_format($subVal, 2) ?></td>
                                        <td><?= number_format($subTar, 2) ?></td> 
                                        <td><?= number_format($subMon, 2) ?></td>
                                        <td><?= number_format($subVenta, 2) ?></td> 
                                        <td><?= number_format($subAceE, 2) ?></td>
                                        <td><?= number_format($subAceC, 2) ?></td> 
                                        <td><?= number_format($subVenta_a, 2) ?></td>
                                        <td><?= number_format($subEfe + $subAceE, 2) ?></td> 
                                        <td><?= number_format($subVenta + $subVenta_a, 2) ?></td>
                                        <td  class="DescuentoT DescuentoTxt"><?= number_format($descuento, 2) ?></td>
                                        <td   class="DescuentoT"><?= number_format($subVenta + $subVenta_a - $descuento, 2) ?></td>
                                        <?php
                                        if ($MarcaDis) {
                                            ?>
                                            <td><?= number_format($Diferencia, 1) ?></td>
                                            <?php
                                        }
                                        ?>
                                    </tr>
                                    <?php
                                    $subVtas = $subLts = $subEfe = $subCre = $subVal = $subTar = $subMon = $subVenta = $subAceE = $subAceC = $subVenta_a = $descuento = $Diferencia = 0;
                                }


                                $nVtas += $rg["ventas"];
                                $nLts += $rg["volumen"];
                                $nEfe += $rg["efectivo"];
                                $nCre += $rg["credito"];
                                $nVal += $rg["vales"];
                                $nTar += $rg["tarjeta"];
                                $nMon += $rg["monederos"];
                                $nVenta += $rg["venta"];
                                $nAceE += $rg["efectivo_a"];
                                $nAceC += $rg["credito_a"];
                                $nVenta_a += $rg["venta_a"];

                                if ($MarcaDis) {
                                    $nDiferencia += $dif;
                                }
                                $nReg++;
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2">Totales</td>
                                <td><?= number_format($nVtas, 0) ?></td>    
                                <td><?= number_format($nLts, 3) ?></td>     
                                <td><?= number_format($nEfe, 2) ?></td>     
                                <td><?= number_format($nCre, 2) ?></td>   
                                <td><?= number_format($nVal, 2) ?></td> 
                                <td><?= number_format($nTar, 2) ?></td>     
                                <td><?= number_format($nMon, 2) ?></td>     
                                <td><?= number_format($nVenta, 2) ?></td>     
                                <td><?= number_format($nAceE, 2) ?></td>     
                                <td><?= number_format($nAceC, 2) ?></td>     
                                <td><?= number_format($nVenta_a, 2) ?></td>     
                                <td><?= number_format($nEfe + $nAceE, 2) ?></td>    
                                <td><?= number_format($nVenta + $nVenta_a, 2) ?></td>     
                                <td  class="DescuentoT DescuentoTxt"><?= number_format($subDesc, 2) ?></td>
                                <td  class="DescuentoT"><?= number_format($nVenta + $nVenta_a - $subDesc, 2) ?></td>   
                                <?php
                                if ($MarcaDis) {
                                    ?>
                                    <td><?= number_format($nDiferencia, 1) ?></td>
                                    <?php
                                }
                                ?>
                            </tr>
                        </tfoot>
                    </table>
                    <?php
                    if (($nDiferencia > 10 || $nDiferencia < -10) && $TD["marca"] !== "T") {
                        ?>
                        <div style="padding-top: 5px;width: 26%;margin-left: 37%;border: 2px solid #F5B041;border-radius: 30px;height: 25px;background-color: #E74C3C;color: white;font-weight: bold; font-family: sans-serif">
                            Diferencia total de <?= number_format($nDiferencia, 1) ?>
                        </div>
                        <?php
                    }
                    ?>
                    <br/>

                    <table aria-hidden="true">
                        <thead>
                            <tr class="titulo">
                                <td></td>
                                <td></td>
                                <td colspan="4">L i t r o s</td>
                                <td colspan="5">I m p o r t e</td>
                            </tr>

                            <tr>
                                <td></td>
                                <td>#Vtas</td>

                                <td>Venta</td>
                                <td>Jarreo</td>
                                <td>Consig.</td>
                                <td>Venta real</td>
                                <td>Venta</td>
                                <td>Jarreo</td>
                                <td>Consig.</td>
                                <td  class="DescuentoT">Descuento</td>
                                <td>Venta real</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($registrosT as $rg) {
                                ?>
                                <tr>
                                    <td> <?= $rg["producto"] ?> </td>
                                    <td class="numero"><?= number_format($rg["ventas_d"], 0) ?></td>
                                    <td class="numero"><?= number_format($rg["volumen_d"] + $rg["volumen_j"], 3) ?></td>
                                    <td class="numero"><?= number_format($rg["volumen_j"], 3) ?></td>
                                    <td class="numero"><?= number_format($rg["volumen_n"], 3) ?></td>
                                    <td class="numero"><?= number_format($rg["volumen_d"], 3) ?></td>
                                    <td class="numero"><?= number_format($rg["pesos_d"] + $rg["pesos_j"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["pesos_j"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["pesos_n"], 2) ?></td>
                                    <td class="numero DescuentoT DescuentoTxt" ><?= number_format($rg["descuento_n"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["pesos_d"] - $rg["descuento_n"], 2) ?></td>

                                </tr>
                                <?php
                                $nVta += $rg["ventas_d"];
                                $nLtT += $rg["volumen_d"] + $rg["volumen_j"];
                                $nLtJ += $rg["volumen_j"];
                                $nLtN += $rg["volumen_n"];
                                $nLt += $rg["volumen_d"];
                                $nImT += $rg["pesos_d"] + $rg["pesos_j"];
                                $nImJ += $rg["pesos_j"];
                                $nImN += $rg["pesos_n"];
                                $nIm += $rg["pesos_d"];
                                $desc += $rg["descuento_n"];
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>Gran total</td>
                                <td><?= number_format($nVta, 0) ?> </td>
                                <td><?= number_format($nLtT, 3) ?></td>
                                <td><?= number_format($nLtJ, 3) ?></td>
                                <td><?= number_format($nLtN, 3) ?></td>
                                <td><?= number_format($nLt, 3) ?></td>
                                <td><?= number_format($nImT, 2) ?></td>
                                <td><?= number_format($nImJ, 2) ?></td>
                                <td><?= number_format($nImN, 2) ?></td>
                                <td class="DescuentoT DescuentoTxt"><?= number_format($desc, 2) ?></td>
                                <td><?= number_format($nIm - $desc, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            $(document).ready(function () {
                var i = "<?= $desc ?>";
                console.log(i);
                if (i === "0") {
                    console.log("ENTRA");
                    $(".DescuentoT").hide();
                } else {
                    $(".DescuentoT").show();
                }
            });
        </script>
        <style type="text/css">
            .DescuentoTxt {
                color: #E74C3C;
            }
        </style>
        <div id="footer">
            <form name="formActions" method="post" action="" id="form" class="oculto">
                <div id="Controles">
                    <table aria-hidden="true">
                        <tr style="height: 40px;">
                            <td>
                                <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                            </td>
                            <td>
                                <a href="iepscontable.php?Corte=<?= $Corte ?>">Detallado por impuestos</a>
                            </td>
                        </tr>
                    </table>
                </div>
                <input type="hidden" name="Corte" id="Corte">
            </form>
<?php topePagina(); ?>
        </div>
    </body>
</html>

