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

$sqlHe = "  SELECT pagosprv.proveedor,pagosprv.importe,pagosprv.concepto,DATE(pagosprv.fecha) fecha,      
            prv.nombre,TIME(fecha) hora,prv.proveedorde
            FROM pagosprv LEFT JOIN prv ON pagosprv.proveedor = prv.id 
            WHERE pagosprv.id = '$busca'";

$He = $mysqli->query($sqlHe)->fetch_array();

if ($He["proveedorde"] === "Combustibles") {
    $selectPagos = "
            SELECT pagosprvd.factura compra,me.fecha,'Compra de combustible' concepto,me.importefac total,
            pagosprvd.importe abono,pagosprvd.idnvo,(sub.pagado - me.importefac) saldo,me.foliofac documento
            FROM me,pagosprvd,
            (
                SELECT SUM(importe) pagado,factura compra 
                FROM pagosprvd 
                GROUP BY factura
            ) sub
            WHERE pagosprvd.factura = me.id AND pagosprvd.factura = sub.compra 
            AND pagosprvd.id = '$busca' ";
} else {
    $selectPagos = "
            SELECT pagosprvd.factura compra,et.fecha,et.concepto,et.importe total,
            pagosprvd.importe abono,pagosprvd.idnvo,(sub.pagado - et.importe) saldo,et.documento
            FROM et,pagosprvd,
            (
                SELECT SUM(importe) pagado,factura compra 
                FROM pagosprvd 
                GROUP BY factura
            ) sub
            WHERE pagosprvd.factura=et.id AND pagosprvd.factura = sub.compra 
            AND pagosprvd.id = '$busca' ";
}
if(!($result = $mysqli->query($selectPagos))){
    error_log($mysqli->error);
    error_log($selectPagos);
}

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
                            <td>No. Cuenta: <?= $He["proveedor"] ?></td>
                            <td>Proveedor: <?= $He["nombre"] ?></td>
                            <td>Concepto del pago: <?= $He["concepto"] ?></td>
                        </tr>
                        <tr>
                            <td colspan="2"> Fecha Aplicación: <?= $He["fecha"] ?></td>
                            <td>Hora: <?= $He["hora"] ?></td>
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
                                <td>No. Compra</td>
                                <td>Fecha compra</td>
                                <td>Documento</td>
                                <td>Concepto</td>
                                <td>Importe</td>
                                <td>Abono</td>
                                <td>Saldo</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $Gtotal = $GAbonos = 0;
                            while ($rg = $result->fetch_array()) {
                                ?>
                                <tr>
                                    <td> <?= $rg["compra"] ?></td>
                                    <td><?= $rg["fecha"] ?></td>
                                    <td><?= $rg["documento"] ?></td>
                                    <td><?= $rg["concepto"] ?></td>
                                    <td class="numero"><?= number_format($rg["total"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["abono"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["saldo"], 2) ?></td>
                                </tr>
                                <?php
                                $Gtotal += $rg["total"];
                                $GAbonos += $rg["abono"];
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>Total</td>
                                <td class="moneda"><?= number_format($Gtotal, 2) ?></td>
                                <td class="moneda"><?= number_format($GAbonos, 2) ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>

                    <p style="text-align: center"><strong><?= impletras($Gtotal, 'pesos') ?></strong></p>
                </div>

            </div>
        </div>
    </body>
</html>     
