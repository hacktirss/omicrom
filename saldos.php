<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesClientesService.php";

$Titulo = "Saldos por cliente al $Fecha ";

$saldosConsignaciones = "SELECT * FROM (" . $selectSaldos . "  WHERE tipodepago = 'Consignacion') V "
        . "LEFT JOIN (SELECT  SUM(volumen_devolucion) vd FROM me WHERE uuid <> '-----' AND volumen_devolucion > 0) v2 ON true";
if ($Detallado === "Si") {
    $selectSaldos .= "WHERE importe != 0 AND tipodepago != 'Consignacion'";
}

$selectSaldos .= "ORDER BY orden, tipodepago, cliente";

$registros = utils\IConnection::getRowsFromQuery($selectSaldos);
$registrosCn = utils\IConnection::getRowsFromQuery($saldosConsignaciones);
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
                $("#Detallado").val("<?= $Detallado ?>");
            });
        </script>
    </head>

    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo) ?>
            <div id="Reportes">
                <table aria-hidden="true">
                    <thead>
                        <tr>
                            <td>#</td>
                            <td>No.Cta</td>
                            <td>Nombre</td>
                            <td>Numero Cuenta C.</td>
                            <td>Tipo/cliente</td>
                            <td>Limite</td>
                            <td>Dias Credito</td>
                            <td>Importe</td>
                            <td>Saldo Disponible</td>
                            <td>Total</td>                           
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $cTipoPago = "";
                        $SubTotal = 0;
                        foreach ($registros as $registro) {

                            //error_log("-------------El valor de registro : " . print_r($registro,true));
                            if ($cTipoPago <> $registro["tipodepago"]) {
                                if (!empty($cTipoPago)) {
                                    $SubTotal += $nImporte;
                                    ?>
                                    <tr class="subtotal">
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td>Subtotal</td>
                                        <td class="moneda"><?= number_format($nImporte, 2) ?></td>
                                        <td></td>
                                        <td class="moneda"><?= number_format($SubTotal, 2) ?></td>
                                    </tr>
                                    <?php
                                }
                                $cTipoPago = $registro["tipodepago"];
                                $nImporteT += $nImporte;
                                $nImporte = 0;
                            }
                            ?>

                            <tr>
                                <td><?= number_format($nRng + 1) ?></td>
                                <td><?= $registro["cliente"] ?></td>                               
                                <td class="overflow"><?= ucwords(strtolower($registro["nombre"])) ?></td>
                                <td><?= $registro["ncc"] ?></td> 
                                <td><?= $registro["tipodepago"] ?></td>
                                <?php
                                $Disp = $registro["tipodepago"] === "Credito" ? number_format($registro["limite"] - $registro["importe"], 2) : 0;
                                ?>
                                <td class="numero"><?= number_format($registro["limite"], 2) ?></td>
                                <td class="numero"><?= $registro["diasCredito"] ?></td>
                                <td class="numero"> <?= number_format($registro["importe"], 2) ?></td>
                                <td class="numero"><?= $Disp ?></td>
                                <td></td>
                            </tr>
                            <?php
                            $nRng++;
                            $nImporte += $registro["importe"];
                        }
                        $nImporteT += $nImporte;
                        $SubTotal += $nImporte;
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>Subtotal</td>
                            <td class="moneda"><?= number_format($nImporte, 2) ?></td>
                            <td></td>
                            <td class="moneda"><?= number_format($SubTotal, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
                <table aria-hidden="true">
                    <thead>
                        <tr>
                            <td>#</td>
                            <td>Nombre</td>
                            <td>Volumen</td>
                            <td>Volumen Devolución</td>               
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($registrosCn as $registro) {
                            ?>
                            <tr>
                                <td><?= $registro["cliente"] ?></td>                               
                                <td><?= ucwords(strtolower($registro["nombre"])) ?></td>
                                <td class="numero"><?= number_format($registro["cntConsignacion"], 2) ?></td>
                                <td class="numero"> <?= number_format($registro["vd"], 2) ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="moneda"></td>
                        </tr>
                    </tfoot>
            </div>
        </div>

        <div id="footer">
            <form name="formActions" method="post" action="" class="oculto">
                <div id="Controles">
                    <table aria-hidden="true">
                        <tbody>
                            <tr>
                                <td style="width: 30%;">
                                    <table aria-hidden="true">
                                        <tr>
                                            <td>Fecha:</td>
                                            <td><input type="text" id="Fecha" name="Fecha"></td>
                                            <td class="calendario"><i id="cFecha" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
                                        </tr>
                                    </table>
                                </td>
                                <td>excluir clientes con saldo en ceros: 
                                    <select name="Detallado" id="Detallado">
                                        <option value="No">No</option>
                                        <option value="Si">Si</option>
                                    </select>
                                </td>
                                <td>
                                    <span><input type="submit" name="Boton" value="Enviar"></span>
                                    <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </form>
            <?php topePagina(); ?>
        </div>
    </body>
</html>
