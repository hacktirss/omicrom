<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("importeletras.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$usuarioSesion = getSessionUsuario();

$busca = $request->getAttribute("busca");

$sqlHe = "SELECT et.fecha,et.concepto,et.cantidad,et.iva,et.proveedor,prv.alias,
        et.status,et.importe,et.importesin,et.documento            
        FROM et LEFT JOIN prv ON et.proveedor=prv.id 
        WHERE et.id='$busca'";

$He = $mysqli->query($sqlHe)->fetch_array();

$selectVales = "
        SELECT etd.producto,inv.descripcion,etd.cantidad,etd.costo,
        (etd.descuento * 100) descuento,
        (etd.adicional * 100) adicional,
        (etd.cantidad * etd.costo) importe,
        (etd.costo * (1 - etd.descuento) * (1 - etd.adicional)) c_real,
        etd.cantidad * etd.costo * (1 - etd.descuento) * (1 - etd.adicional) importe_r,
        etd.idnvo 
        FROM etd LEFT JOIN inv ON etd.producto=inv.id 
        WHERE etd.id='$busca' AND etd.cantidad > 0";
$result = $mysqli->query($selectVales);

$Titulo = "Entrada de aceites No:[$busca]";
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports_print.php'; ?> 
        <title><?= $Gcia ?></title>
        <style>
            @page { 
                size: A4 /*landscape*/; 
            }
        </style>
        <script type="text/javascript">
            $(document).ready(function () {

            });
        </script>

    </head>

    <!-- Set "A5", "A4" or "A3" for class name -->
    <!-- Set also "landscape" if you need -->
    <body class="A4">
        <div class="iconos">
             <table aria-hidden="true">
                <tr>
                    <td style="text-align: left"><?= $Titulo ?></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="text-align: center"><i onclick="print();" title="Imprimir" class='icon fa fa-lg fa-print' aria-hidden="true"></i></td>
                </tr>
            </table>
        </div>
        <!-- Each sheet element should have the class "sheet" -->
        <!-- "padding-**mm" is optional: you can set 10, 15, 20 or 25 -->
        <div class="sheet padding-10mm">

            <?php nuevoEncabezadoPrint($Titulo) ?>

            <div id="TablaDatosHeader">
                 <table aria-hidden="true">
                    <thead>
                        <tr>
                            <td>Id:  <?= $busca ?></td>
                            <td>No.entrada: <?= $busca ?></td>
                            <td>Fecha: <?= $He["fecha"] ?></td>
                            <td>Docto: <?= $He["documento"] ?></td>
                        </tr>
                        <tr>
                            <td>Proveedor: <?= $He["alias"] ?></td>
                            <td colspan="2">Concepto: <?= $He["concepto"] ?></td>
                            <td>Total: <?= number_format($He["importe"] + $He["iva"], 2) ?> </td>
                        </tr>
                    </thead>                                     
                </table>
            </div>

            <div id="TablaDatosReporte">
                <div style="padding-top: 10px;">
                     <table aria-hidden="true">
                        <thead>
                            <tr>
                                <td>Clave</td>
                                <td>Descripcion</td>
                                <td>Cantidad</td>
                                <td>Costo</td>
                                <td>Descuento</td>
                                <td>Costo real</td>
                                <td>Importe</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($rg = $result->fetch_array()) {
                                ?>
                                <tr>
                                    <td><?= $rg["producto"] ?></td>
                                    <td><?= $rg["descripcion"] ?></td>
                                    <td class="numero"><?= number_format($rg["cantidad"]) ?></td>
                                    <td class="numero"><?= number_format($rg["costo"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["descuento"], 2) . ($rg["adicional"] > 0 ? " + " . number_format($rg["adicional"], 2) : "" ) ?></td>
                                    <td class="numero"><?= number_format($rg[c_real], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                                </tr>
                                <?php
                                $nCnt += $rg["cantidad"];
                                $nDes += ($rg["importe"] - $rg[importe_r]);
                                $nImp += $rg["importe"];
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td></td>
                                <td>Descuento: <?= number_format($nDes, 2) ?></td>
                                <td><?= number_format($nCnt) ?></td>
                                <td></td>
                                <td colspan="2">Subtotal</td>
                                <td><?= number_format($He["importesin"], 2) ?></td>
                            </tr>

                            <tr>
                                <td></td>
                                <td></td>
                                <td colspan="2"></td>
                                <td colspan="2">Iva</td>
                                <td><?= number_format($He["iva"], 2) ?></td>
                            </tr>

                            <tr>
                                <td></td>
                                <td></td>
                                <td colspan="2"></td>
                                <td colspan="2">Total</td>
                                <td><?= number_format($He["importesin"] + $He["iva"], 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
    </body>
</html>     

