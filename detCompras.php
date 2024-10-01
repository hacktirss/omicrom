<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$varg = true;
require "./services/ReportesVentasService.php";

$Titulo = "Reporte de compras aditivos del $FechaI al $FechaF";
$registros = utils\IConnection::getRowsFromQuery($SQLCompras);
//error_log($selectVales);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports.php'; ?> 
        <script type="text/javascript" src="js/export_.js"></script>
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
                $("#Status").val("<?= $Status ?>");
            });
            function ExportToExcel(type, fn, dl) {
                var elt = document.getElementById('tbl_exporttable_to_xls');
                var wb = XLSX.utils.table_to_book(elt, {sheet: "sheet1"});
                return dl ?
                        XLSX.write(wb, {bookType: type, bookSST: true, type: 'base64'}) :
                        XLSX.writeFile(wb, fn || ('ReporteGerencia.' + (type || 'xlsx')));
            }
        </script>
    </head>

    <body>
        <div id="tbl_exporttable_to_xls">
            <div id="container">
                <?php nuevoEncabezado($Titulo) ?>
                <div id="Reportes" style="padding-bottom: 10px">
                    <table aria-hidden="true">

                        <tbody>
                            <?php
                            $nRng = 1;
                            $Cli = 0;
                            foreach ($registros as $rg) {
                                if ($Cli < $rg["idPrv"]) {
                                    if ($CntInd > 0) {
                                        ?>
                                        <tr style="background-color: rgba(0, 221, 236,0.2);font-weight: bold;">
                                            <td colspan="3" style="border-top: 1px solid #00565B;text-align: right;">Total:</td>
                                            <td style="text-align: right;border-top: 1px solid #00565B;"><?= number_format($CntInd, 2) ?></td>
                                            <td style="text-align: right;border-top: 1px solid #00565B;"><?= number_format($ImpInd, 2) ?></td>
                                            <td style="text-align: right;border-top: 1px solid #00565B;"><?= number_format($IvaInd, 2) ?></td>
                                            <td style="text-align: right;border-top: 1px solid #00565B;"><?= number_format($TtInd, 2) ?></td>
                                        </tr>
                                        <?php
                                        $CntInd = 0;
                                        $ImpInd = 0;
                                        $TtInd = 0;
                                        $IvaInd = 0;
                                    }
                                    ?>  
                                    <tr style="height: 30px; background-color: rgba(0, 173, 168,0.8);">
                                        <td colspan="8" style="text-align: center;font-size: 14px;font-weight: bold;border: 1px solid black;"><?= $rg["nombre"] ?></td>
                                    </tr>
                                    <tr style="background: white;font-weight: bold;">
                                        <td style="border-bottom: 1px solid #013D3B">No.Compra</td>
                                        <td style="border-bottom: 1px solid #013D3B">Producto</td>
                                        <td style="border-bottom: 1px solid #013D3B">Costo</td>
                                        <td style="border-bottom: 1px solid #013D3B">Cantidad</td>
                                        <td style="border-bottom: 1px solid #013D3B">Importe</td>
                                        <td style="border-bottom: 1px solid #013D3B">Iva</td>
                                        <td style="border-bottom: 1px solid #013D3B">Total</td>
                                    </tr>
                                    <?php
                                    $Cli = $rg["idPrv"];
                                }
                                ?>
                                <tr>
                                    <td><?= $rg["id"] ?></td>
                                    <td><?= $rg["descripcion"] ?></td>
                                    <td class="numero"><?= number_format($rg["costo"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["cnt"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                                    <td class="numero"><?= number_format(($rg["importe"] * 1.16) - $rg["importe"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["importe"] * 1.16, 2) ?></td>
                                </tr>
                                <?php
                                $CntInd += $rg["cnt"];
                                $ImpInd += $rg["importe"];
                                $TtInd += $rg["importe"] * 1.16;
                                $IvaInd += ($rg["importe"] * 1.16) - $rg["importe"];
                                $nRng++;
                            }
                            ?>
                            <tr style="background-color: rgba(0, 221, 236,0.2);font-weight: bold">
                                <td colspan="3" style="border-top: 1px solid #00565B;text-align: right;">Total:</td>
                                <td style="text-align: right;border-top: 1px solid #00565B;"><?= number_format($CntInd, 2) ?></td>
                                <td style="text-align: right;border-top: 1px solid #00565B;"><?= number_format($ImpInd, 2) ?></td>
                                <td style="text-align: right;border-top: 1px solid #00565B;"><?= number_format($IvaInd, 2) ?></td>
                                <td style="text-align: right;border-top: 1px solid #00565B;"><?= number_format($TtInd, 2) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div> 
            </div>
        </div>
        <div id="footer">
            <div id="Controles">
                <table aria-hidden="true">
                    <tr style="height: 40px;">
                        <td align="left" style="vertical-align: top;width: 85%;">
                            <form name="formActions" method="post" action="" id="form" class="oculto">
                                <table aria-hidden="true" style="display: inline-block;width: 85%;">
                                    <tr>
                                        <td>F.inicial:</td>
                                        <td><input type="text" id="FechaI" name="FechaI"></td>
                                        <td class="calendario"><i id="cFechaI" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
                                        <td rowspan="2" style="vertical-align: bottom;padding-left: 5px;"><input type="submit" name="Boton" value="Enviar"></td>
                                    </tr>
                                    <tr>
                                        <td>F.final:</td>
                                        <td><input type="text" id="FechaF" name="FechaF"></td>
                                        <td class="calendario"><i id="cFechaF" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
                                    </tr>
                                </table>
                            </form>

                        </td>
                        <td>
                            <div style="display: inline-block;vertical-align: top;padding-top: 5px;">
                                <span><button onclick="ExportToExcel('xlsx')" style="height: 25px;"><i class="icon fa fa-lg fa-bold fa-file-excel-o" aria-hidden="true"></i></button></span>
                                <span><button onclick="print()" title="Imprimir reporte" style="height: 25px;"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
            <?php topePagina(); ?>
        </div>
    </body>
</html>