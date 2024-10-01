<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

require_once ("com/softcoatl/cfdi/utils/NumericalCurrencyConverter.php");
require_once ("com/softcoatl/cfdi/utils/Currency.php");
require_once ("com/softcoatl/cfdi/utils/SpanishNumbers.php");

use com\softcoatl\utils as utils;
use com\softcoatl\cfdi\utils\NumericalCurrencyConverter;
use com\softcoatl\cfdi\utils\SpanishNumbers;
use com\softcoatl\cfdi\utils\Currency;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$usuarioSesion = getSessionUsuario();

$busca = $request->getAttribute("busca");

$sqlHe = "
        SELECT genbol.id,genbol.fecha,cli.nombre,genbol.cantidad,genbol.importe,
        genbol.status,genbol.fechav,UPPER(genbol.recibe) recibe
        FROM genbol 
        LEFT JOIN cli ON genbol.cliente = cli.id 
        WHERE TRUE AND genbol.id = '$busca'";

$He = $mysqli->query($sqlHe)->fetch_array();

$selectVales = "
        SELECT sub.nominacion, SUM(sub.cantidad) cantidad, SUM(sub.total) total, sub.minimo, sub.maximo
        FROM
        (
                SELECT genbold.precio nominacion, genbold.boletos cantidad,
                (genbold.precio * genbold.boletos) total,
                MIN(boletos.secuencia) minimo, MAX(boletos.secuencia) maximo
                FROM genbol, genbold, boletos
                WHERE TRUE 
                AND genbol.id = genbold.id 
                AND genbold.id = boletos.id AND genbold.precio = boletos.importe
                AND genbol.id = $busca
                GROUP BY genbold.idnvo 
                ORDER BY boletos.secuencia
        ) sub
        WHERE TRUE
        GROUP BY sub.nominacion
        ORDER BY sub.nominacion ASC;";
$result = $mysqli->query($selectVales);

$Titulo = "Acuse de compra de vales";
$converter = new NumericalCurrencyConverter(new SpanishNumbers(), new Currency('PESOS', 'PESO'));
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
                            <td>Cliente: <?= $He["nombre"] ?></td>
                            <td>No.de boletos: <?= number_format($He["cantidad"], 0) ?></td>
                        </tr>
                        <tr>
                            <td colspan="2">Fecha de compra: <?= $He["fecha"] ?> Vigencia: <?= $He["fechav"] ?></td>
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
                                <td>Folios</td>
                                <td>Nominacion</td>
                                <td>Cantidad</td>
                                <td>Importe</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($rg = $result->fetch_array()) { ?>
                                <tr>
                                    <td> <?= $rg["minimo"] . " - " . $rg["maximo"] ?></td>
                                    <td class="numero"><?= number_format($rg["nominacion"], 2) ?></td>
                                    <td class="numero"><?= $rg["cantidad"] ?></td>
                                    <td class="numero"><?= number_format($rg["total"], 2) ?></td>
                                </tr>
                                <?php
                                $Gtotal += $rg["total"];
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan='2'></td>
                                <td>Total</td>
                                <td class="moneda"><?= number_format($Gtotal, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>

                    <p style="text-align: center"><strong><?= $converter->convert($Gtotal) ?></strong></p>
                </div>

                <div style="padding-top: 100px;text-align: center;width: 100%">
                    <div>Autorizado y/o rebido por:</div>
                    <div><div style="height: 50px;width: 30%;margin-left: 35%;border-bottom: 1px solid #2C3E50;"></div></div>
                    <div><?= empty($He["recibe"]) ? $He["nombre"] : $He["recibe"]; ?></div>
                </div>
            </div>
        </div>
    </body>
</html>     

