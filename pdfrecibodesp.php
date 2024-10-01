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

$sqlHe = "SELECT pagosdesp.vendedor,pagosdesp.importe,pagosdesp.concepto, pagosdesp.deposito,
            DATE(pagosdesp.fecha) aplicacion, TIME(pagosdesp.fecha) hora, ven.nombre
            FROM pagosdesp LEFT JOIN ven ON pagosdesp.vendedor = ven.id 
            WHERE pagosdesp.id = '$busca';";

$He = $mysqli->query($sqlHe)->fetch_array();

$selectPagos = "SELECT * FROM (
                    SELECT cxd.referencia, cxd.concepto, pagosdesp.deposito fecha, cxd.importe, pagosdespd.importe abono
                    FROM pagosdesp, pagosdespd, cxd
                    WHERE TRUE 
                    AND pagosdesp.id = pagosdespd.pago
                    AND pagosdespd.referencia = cxd.referencia
                    AND cxd.tm = 'C'
                    AND pagosdesp.id = '$busca'
                ) 
                pagosdespd 
                ORDER BY pagosdespd.referencia ";
$result = $mysqli->query($selectPagos);

$Titulo = "Recibo de pago";
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
                            <td>Recibo de pago:  <?= $busca ?></td>
                            <td>No. Cuenta: <?= $He["vendedor"] ?></td>
                            <td colspan="2">Nombre: <?= $He["nombre"] ?></td>
                        </tr>
                        <tr>
                            <td>Fecha Aplicación: <?= $He["aplicacion"] ?></td>
                            <td>Hora: <?= $He["hora"] ?></td>
                            <td>Deposito: <?= $He["deposito"] ?></td>
                            <td>Importe: <span class="moneda"><?= number_format($He["importe"], 2) ?></span></td>
                        </tr>
                    </thead>                                     
                </table>
            </div>

            <div id="TablaDatosReporte">
                <div style="padding-top: 10px;">
                     <table aria-hidden="true">
                        <thead>
                            <tr>
                                <td>Cancepto</td>
                                <td>Fecha del deposito</td>
                                <td>Referencia</td>
                                <td>Importe</td>
                                <td>Abono</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $Gtotal = $GAbonos = 0;
                            while ($rg = $result->fetch_array()) {
                                ?>
                                <tr>
                                    <td> <?= $rg["concepto"] ?></td>
                                    <td><?= $rg["fecha"] ?></td>
                                    <td class="numero"><?= $rg["referencia"] ?></td>
                                    <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["abono"], 2) ?></td>
                                </tr>
                                <?php
                                $Gtotal += $rg["importe"];
                                $GAbonos += $rg["abono"];
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2"></td>
                                <td>Total</td>
                                <td class="moneda"><?= number_format($Gtotal, 2) ?></td>
                                <td class="moneda"><?= number_format($GAbonos, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>

                    <p style="text-align: center"><strong><?= impletras($GAbonos, "pesos") ?></strong></p>
                </div>

                <div style="padding-top: 100px;text-align: center;width: 100%">
                    <div>Autorizado y/o rebido por:</div>
                    <div><br/><br/><hr width='40%' sytle='border: 5px;'></div>
                    <div><?= $He["nombre"] ?></div>
                </div>
            </div>
        </div>
    </body>
</html>     
