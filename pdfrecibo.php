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

$Titulo = "Recibo de pago";
$busca = $request->getAttribute("busca");

$sqlHe = "SELECT pagos.cliente,pagos.importe,pagos.concepto,
                DATE(pagos.fecha) aplicacion,pagos.fecha_deposito deposito,      
                cli.nombre,TIME(pagos.fecha) hora,pagos.formapago
                FROM pagos LEFT JOIN cli ON pagos.cliente = cli.id 
                WHERE pagos.id = '$busca'";

$He = utils\IConnection::execSql($sqlHe);

$selectPagos = "SELECT * FROM (
                    SELECT 'Factura' concepto,fc.fecha,fc.folio,fc.total importe,pagose.importe abono
                    FROM pagose 
                    LEFT JOIN fc ON pagose.factura = fc.id
                    WHERE pagose.id = '$busca' AND pagose.factura > 0
                    UNION
                    SELECT 'Nota de credito' concepto,nc.fecha,fc.folio,(nc.total * -1) importe,0 abono
                    FROM pagose,nc 
                    LEFT JOIN fc ON nc.factura = fc.id
                    WHERE pagose.factura = nc.factura AND pagose.id = '$busca' AND nc.status = 1  AND pagose.factura > 0
                    UNION
                    SELECT CONCAT('Consumo de ', com.descripcion) concepto,rm.fin_venta,rm.id folio,rm.pagoreal importe,pagose.importe abono
                    FROM pagose 
                    LEFT JOIN rm ON pagose.referencia = rm.id
                    LEFT JOIN com ON rm.producto = com.clavei AND com.activo = 'Si'
                    WHERE pagose.id = '$busca' AND pagose.referencia > 0 and pagose.tipo=1
                    UNION
                    SELECT vt.descripcion concepto,vt.fecha fin_venta,vt.id folio,vt.total importe,pagose.importe abono
                    FROM pagose 
                    LEFT JOIN vtaditivos vt ON pagose.referencia = vt.id
                    WHERE pagose.id = '2825' AND pagose.referencia > 0 and pagose.tipo=2
                ) 
                pagose ORDER BY pagose.folio ";
$registros = utils\IConnection::getRowsFromQuery($selectPagos);

$registrosLandscape = 25;
$registrosVertical = 37;
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
        <div id="TablaExcel">
            <!-- Each sheet element should have the class "sheet" -->
            <!-- "padding-**mm" is optional: you can set 10, 15, 20 or 25 -->
            <?php
            $nRng = 1;
            $close = false;
            $sheet = 0;
            if (count($registros) > 0) {
                $Gtotal = $GAbonos = 0;
                foreach ($registros as $registro) {
                    if (($nRng - 1) % $registrosVertical == 0) {
                        $close = false;
                        $sheet++;

                        $ignore = "";
                        if ($sheet > 1) {
                            $ignore = "tableexport-ignore";
                        }
                        ?>
                        <div class="sheet padding-10mm"> <!-- Abre hoja-->

                            <?php nuevoEncabezadoPrint($Titulo) ?>

                            <div id="TablaDatosHeader">
                                <table aria-hidden="true">
                                    <thead>
                                        <tr>
                                            <td>Recibo de pago:  <?= $busca ?></td>
                                            <td>No. Cuenta: <?= $He["cliente"] ?></td>
                                            <td>Nombre: <?= $He["nombre"] ?></td>
                                            <td>Forma de pago: <?= $He["formapago"] ?></td>
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

                            <div id="TablaDatosReporte"> <!-- Abre div estilos-->
                                <div style="padding-top: 10px;">

                                    <table aria-hidden="true"> <!-- Abre tabla 1-->
                                        <thead>
                                            <tr class="<?= $ignore ?>">
                                                <td>#</td>
                                                <td>Concepto</td>
                                                <td>Fecha del comprobante</td>
                                                <td>Folio</td>
                                                <td>Importe</td>
                                                <td>Abono</td>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php
                                        }
                                        ?>
                                        <tr>
                                            <td><?= $nRng ?></td>
                                            <td style="text-align: left"><?= $registro["concepto"] ?></td>
                                            <td><?= $registro["fecha"] ?></td>
                                            <td class="numero"><?= $registro["folio"] ?></td>
                                            <td class="numero"><?= number_format($registro["importe"], 2) ?></td>
                                            <td class="numero"><?= number_format($registro["abono"], 2) ?></td>
                                        </tr>

                                        <?php
                                        //error_log("Modulo $nRng: " . ($nRng % $registrosVertical));
                                        if ($nRng % $registrosVertical == 0) {
                                            if (($nRng - 1) == count($registros)) {
                                                
                                            } else {
                                                echo ''
                                                . '</tbody>'
                                                . '</table> <!-- Cierra tabla 1 si hay mas de 25 registros-->'
                                                . '</div>'
                                                . '</div> <!-- Cierra div estilos-->'
                                                . '</div> <!-- Cierra hoja si hay mas de 25 registros-->';
                                                $close = true;
                                            }
                                        }
                                        $Gtotal += $registro["importe"];
                                        $GAbonos += $registro["abono"];
                                        $nRng++;
                                    }
                                } else {

                                    echo '<div class="sheet padding-10mm"> <!-- Abre hoja--> ';
                                    nuevoEncabezadoPrint($Titulo);
                                    echo ' 
                                        <div id="TablaDatosHeader">
                                            <table aria-hidden="true">
                                                <thead>
                                                    <tr>
                                                        <td>Recibo de pago:  ', $busca . '</td>
                                                        <td>No. Cuenta: ', $He["cliente"] . '</td>
                                                        <td>Nombre: ', $He["nombre"] . '</td>
                                                        <td>Forma de pago: ', $He["formapago"] . '</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Fecha Aplicación: ', $He["aplicacion"] . '</td>
                                                        <td>Hora: ', $He["hora"] . '</td>
                                                        <td>Deposito: ', $He["deposito"] . '</td>
                                                        <td>Importe: <span class="moneda">', number_format($He["importe"], 2) . '</span></td>
                                                    </tr>
                                                </thead>                                     
                                            </table>
                                        </div>

                                        <div id="TablaDatosReporte"> <!-- Abre div estilos-->
                                            <div style="padding-top: 10px;">

                                                <table aria-hidden="true"> <!-- Abre tabla 1-->
                                                    <thead>
                                                        <tr class="', $ignore . '">
                                                            <td>#</td>
                                                            <td>Cancepto</td>
                                                            <td>Fecha del comprobante</td>
                                                            <td>Folio</td>
                                                            <td>Importe</td>
                                                            <td>Abono</td>
                                                        </tr>
                                                    </thead>

                                                    <tbody>';
                                }

                                if (!$close) {
                                    ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3"></td>
                                        <td>Total</td>
                                        <td class="moneda"><?= number_format($Gtotal, 2) ?></td>
                                        <td class="moneda"><?= number_format($GAbonos, 2) ?></td>
                                    </tr>
                                </tfoot>
                            </table> <!-- Cierra tabla 1 si hay menos de 25 registros-->

                            <p style="text-align: center"><strong><?= impletras($Gtotal, "pesos") ?></strong></p>

                            <div style="padding-top: 100px;text-align: center;width: 100%">
                                <div>Autorizado y/o rebido por:</div>
                                <div><br/><br/><hr width='40%' sytle='border: 5px;'></div>
                                <div><?= $He["nombre"] ?></div>
                            </div>

                        </div>
                    </div> <!-- Cierra div estilos-->
                </div> <!-- Cierra hoja si hay mas de 25 registros-->
                <?php
            }
            ?>
        </div>
    </body>
</html>     