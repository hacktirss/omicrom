<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesProveedoresService.php";

$Titulo = "Compras del $FechaI al $FechaF ";

$registros = utils\IConnection::getRowsFromQuery($selectProveedoresG);

?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                var Proveedor = "<?= $ProveedorS ?>";
                $("#autocomplete").val(Proveedor.replace("Array", ""))
                        .attr("placeholder", "* Favor de buscar al proveedor *")
                        .click(function () {
                            this.select();
                        }).focus()
                        .activeComboBox(
                                $('[name=\'form1\']'),
                                'SELECT id as data, CONCAT(id, \' | \', mid(nombre,1,50)) value FROM prv WHERE id>0',
                                'nombre');

                $("#FechaI").val("<?= $FechaI ?>").attr("size", "10");
                $("#FechaF").val("<?= $FechaF ?>").attr("size", "10");
                $("#cFechaI").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaI")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#cFechaF").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaF")[0], "yyyy-mm-dd", $(this)[0]);
                });
            });
        </script>
    </head>
    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo) ?>
            <div id="Reportes" style="min-height: 200px;">
                 <table aria-hidden="true">
                    <thead>
                        <tr>
                            <td>No.compra</td>
                            <td>Fecha</td>
                            <td>Proveedor</td>
                            <td>Concepto</td>
                            <td>Documento</td>
                            <td>Cantidad</td>
                            <td>Importe</td>
                            <td>Iva</td>
                            <td>Total</td>
                        </tr>
                    </thead> 
                    <tbody>
                        <?php
                       foreach ($registros as $rg) {
                           ?>
                            <tr>
                                <td class="numero"><?= $rg["compra"] ?></td>
                                <td class="numero"><?= $rg["fecha"] ?></td>
                                <td><?= ucwords(strtolower($rg["proveedor"])) ?></td>
                                <td><?= $rg["concepto"] ?></td>
                                <td><?= $rg["documento"] ?></td>
                                <td class="numero"><?= number_format($rg["cantidad"], 0) ?></td>
                                <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["iva"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["total"], 2) ?></td>
                            </tr>
                            <?php
                            $nImp += $rg["importe"];
                            $nCnt += $rg["cantidad"];
                            $nIva += $rg["iva"];
                            $nTotal += $rg["total"];
                            $Gtotal += $rg["total"];
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5">Total</td>
                            <td><?= number_format($nCnt, 2) ?></td>
                            <td class="moneda"><?= number_format($nImp, 2) ?></td>
                            <td class="moneda"><?= number_format($nIva, 2) ?></td>
                            <td class="moneda"><?= number_format($nTotal, 2) ?></td>
                        </tr>

                        <tr>
                            <td colspan="8">Gran total</td>
                            <td><?= number_format($Gtotal, 2) ?></td>
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
                            <td align="left" colspan="2">
                                <div style="position: relative;">
                                    <input style="width: 100%;" type="search" id="autocomplete" name="ProveedorS">
                                </div>
                                <div id="autocomplete-suggestions"></div>
                            </td>
                        </tr>
                        <tr style="height: 40px;">
                            <td>
                                 <table aria-hidden="true">
                                    <tr>
                                        <td>F.inicial:</td>
                                        <td><input type="text" id="FechaI" name="FechaI"></td>
                                        <td class="calendario"><i id="cFechaI" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
                                    </tr>
                                    <tr>
                                        <td>F.final:</td>
                                        <td><input type="text" id="FechaF" name="FechaF"></td>
                                        <td class="calendario"><i id="cFechaF" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
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