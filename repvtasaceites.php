<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

$Titulo = "Venta de aceites del $FechaI al $FechaF";

$registros = utils\IConnection::getRowsFromQuery($selectVentaAceites);

$registrosP = utils\IConnection::getRowsFromQuery($selectVentaAceitesP);
$registrosP2 = utils\IConnection::getRowsFromQuery($selectVentaAceitesNvo);

$cSql = $selectVentaAceites;
//error_log($cSql);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports.php'; ?> 
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css" type="text/css">
        <script type="text/javascript" src="https://unpkg.com/xlsx@0.15.1/dist/xlsx.full.min.js"></script>
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
                $("#Detallado").val("<?= $Detallado ?>");
                $("#Desglose").val("<?= $Desglose ?>");
                $('table.display').DataTable({
                    dom: 'Bfrtip',
                    paging: false,
                    ordering: false,
                    buttons: [
                        'excelHtml5',
                        'pdfHtml5'
                    ],
                    columnDefs: [{targets: 3, className: 'dt-body-right'}]
                });
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
        <div id="container">
            <?php nuevoEncabezado($Titulo); ?>

            <div id="tbl_exporttable_to_xls">
                <table style="width: 100%;" summary="Mostramos titulo del reporte"><tr><th><h3><?= $Titulo ?></h3></th></tr></table>
                <div id="Reportes">
                    <?php
                    if ($Detallado === "No") {
                        ?>
                        <table id="RpAceites" aria-hidden="true" class="display" style="width: 100%;">
                            <thead>
                                <tr style="font-weight: bold;">
                                    <td>Posicion</td>
                                    <td>Nombre</td>
                                    <td>Cantidad</td>
                                    <td>Importe</td>
                                </tr>
                            </thead>
                            <tbody>

                                <?php
                                $nCnt = $nImp = $nCos = 0;
                                foreach ($registros as $rg) {
                                    ?>
                                    <tr>
                                        <td><?= $rg["posicion"] ?></td>
                                        <td><?= $rg["alias"] ?></td>
                                        <td class="numero"><?= number_format($rg["cantidad"], 0) ?></td>
                                        <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                                    </tr>
                                    <?php
                                    $nCnt += $rg["cantidad"];
                                    $nImp += $rg["importe"];
                                    $nCos += $rg["costo"];
                                    $nRng++;
                                }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td></td>
                                    <td>Total</td>
                                    <td><?= number_format($nCnt, 0) ?></td>
                                    <td><?= number_format($nImp, 2) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                        <table id="RpAceites2" aria-hidden="true" class="display" style="width: 100%;">
                            <thead>
                                <tr>
                                    <td colspan="7" style="text-align: center;font-weight: bold;font-size: 20px;">Venta por producto</td>
                                </tr>
                                <tr style="font-weight: bold;">
                                    <td>Clave</td>
                                    <td>Descripcion</td>
                                    <td>Cantidad</td>
                                    <td>Precio Publico</td>
                                    <td>Costo X Cantidad</td>
                                    <td>Importe</td>
                                    <td>Diferencia</td></strong>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $nCnt = $nImp = $nCos = 0;
                                foreach ($registrosP as $rg) {
                                    ?>
                                    <tr>
                                        <td><?= $rg["clave_producto"] ?></td>
                                        <td><?= $rg["descripcion"] ?></td>
                                        <td class="numero"><?= number_format($rg["cantidad"], 0) ?></td>
                                        <td class="numero"><?= number_format($rg["unitario"], 2) ?></td>
                                        <td class="numero"><?= number_format($rg["costo"], 2) ?></td>
                                        <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                                        <td class="numero"><?= number_format($rg["importe"] - $rg["costo"], 2) ?></td>
                                    </tr>
                                    <?php
                                    $nCnt += $rg["cantidad"];
                                    $nUn += $rg["unitario"];
                                    $nCos += $rg["costo"];
                                    $nImp += $rg["importe"];
                                    $nDif += $rg["importe"] - $rg["costo"];
                                }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td></td>
                                    <td>Total</td>
                                    <td><?= number_format($nCnt, 0) ?></td>
                                    <td><?= number_format($nUn, 2) ?></td>
                                    <td><?= number_format($nCos, 2) ?></td>
                                    <td><?= number_format($nImp, 2) ?></td>
                                    <td><?= number_format($nDif, 2) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                        <table id="RpAceites3" aria-hidden="true" class="display" style="width: 100%;">
                            <thead>
                                <tr>
                                    <td colspan="4" style="text-align: center;font-weight: bold;font-size: 20px;">Venta por día</td>
                                </tr>
                                <tr style="font-weight: bold;">
                                    <td>Registros</td>
                                    <td>Fecha</td>
                                    <td>Cantidad</td>
                                    <td>Importe</td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $a = 1;
                                $nCnt = $nImp = $nCos = 0;
                                foreach ($registrosP2 as $rg) {
                                    ?>
                                    <tr>
                                        <td><?= $a ?></td>
                                        <td><?= $rg["fecha"] ?></td>
                                        <td class="numero"><?= number_format($rg["cantidad"], 0) ?></td>
                                        <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                                    </tr>
                                    <?php
                                    $a++;
                                    $nCnt += $rg["cantidad"];
                                    $nUn += $rg["unitario"];
                                    $nCos += $rg["costo"];
                                    $nImp += $rg["importe"];
                                    $nDif += $rg["importe"] - $rg["costo"];
                                }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td></td>
                                    <td>Total</td>
                                    <td class="numero"><?= number_format($nCnt, 0) ?></td>
                                    <td class="numero"><?= number_format($nImp, 2) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                        <?php
                    } else {
                        ?>
                        <table aria-hidden="true"  class="display" style="width: 100%;font-size: 12px;">
                            <thead>
                                <tr style="font-size: 18px;font-weight: bold;">
                                    <td>Clave</td>
                                    <td>Producto</td>
                                    <td>Cantidad</td>
                                    <td>Precio</td>
                                    <td>Importe</td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $Posicion = "";
                                foreach ($registros as $rg) {
                                    if ($rg["posicion"] <> $Posicion) {
                                        if (!empty($Posicion)) {
                                            ?>
                                            <tr>
                                                <td></td>
                                                <td>Total</td>
                                                <td><?= number_format($nCantidad, 0) ?></td>
                                                <td></td>
                                                <td style="text-align: right;padding-right: 10px;"><?= number_format($nImporte, 2) ?></td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                        <tr style="background-color: #B2BABB">
                                            <td></td>
                                            <td></td>
                                            <td style="text-align: left">Posicion: <?= $rg["posicion"] ?> <?= $rg["alias"] ?></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                        <?php
                                        $Posicion = $rg["posicion"];
                                        $nCantidad = 0;
                                        $nImporte = 0;
                                    }
                                    ?>
                                    <tr>
                                        <td><?= $rg["clave_producto"] ?></td>
                                        <td><?= $rg["descripcion"] ?></td>
                                        <td class="numero"><?= number_format($rg["cantidad"], 0) ?></td>
                                        <td class="numero" style="text-align: right;padding-right: 10px;"><?= number_format($rg["precio"], 2) ?></td>
                                        <td class="numero" style="text-align: right;padding-right: 10px;"><?= number_format($rg["importe"], 2) ?></td>
                                    </tr>
                                    <?php
                                    $nImpT += $rg["importe"];
                                    $nCantT += $rg["cantidad"];
                                    $nImporte += $rg["importe"];
                                    $nCantidad += $rg["cantidad"];
                                }
                                ?>
                            </tbody>
                        </table>
                        <?php
                    }
                    ?>
                </div>
            </div>

            <div id="footer">
                <form name="formActions" method="post" action="" id="form" class="oculto">
                    <div id="Controles">
                        <table aria-hidden="true">
                            <tr style="height: 40px;">
                                <td style="width: 30%;">
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
                                        <tr>
                                            <td>&nbsp;Desglose:</td>
                                            <td style="text-align: left;padding-left: 5px">
                                                <select id="Desglose" name="Desglose">
                                                    <option value="Cortes">Cortes</option>
                                                    <option value="Dia">Dia</option>
                                                </select>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td>
                                    <span><input type="submit" name="Boton" value="Enviar"></span>
                                    <span><button onclick="ExportToExcel('xlsx')"><i class="icon fa fa-lg fa-bold fa-file-excel-o" aria-hidden="true"></i></button></span>
                                    <?php
                                    if ($usuarioSesion->getTeam() !== "Operador") {
                                        ?>
                                        <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>                                
                                                <?php
                                            }
                                            ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </form>
                <?php topePagina(); ?>
            </div>
        </div>
    </body>
</html>