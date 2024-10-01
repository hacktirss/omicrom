<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

$Titulo = "Beneficios consumidos del $FechaI al $FechaF";
$TipoGroup = $Detallado == "No" ? "rm.fecha_venta" : "rm.id";
$NameGroup = $Detallado == "No" ? "Dia" : "Ticket";

$Group .= $request->hasAttribute("MasVentas") ? "ORDER BY ttCli.cntG DESC" : "";
$Group .= $request->hasAttribute("MasLitro") ? "ORDER BY ttCli.volumenG DESC" : "";
$Group .= $request->hasAttribute("MasDescuento") ? "ORDER BY ttCli.descuentoG DESC" : "";
$SqlDescuentosAplicadosTarjetaMonederoXConsumos = "SELECT count(1) cnt,cli.id,CONCAT(cli.id,'.- ',cli.nombre) nombre,
    SUM(volumen) volumen,SUM(rm.importe) importe,SUM(rm.descuento) descuento,
    u.codigo,rm.fecha_venta,DATE_FORMAT(STR_TO_DATE(fecha_venta, '%Y%m%d'), '%Y-%m-%d') fecha,
    rm.id,rm.inicio_venta,rm.producto prdrm
        FROM omicrom.beneficios b 
        LEFT JOIN rm ON rm.id=b.id_consumo 
        LEFT JOIN unidades u ON u.id=b.id_unidad
        LEFT JOIN cli ON cli.id=u.cliente
        LEFT JOIN (
            SELECT COUNT(1) cntG ,SUM(volumen) volumenG,SUM(descuento) descuentoG,u.cliente clienteG FROM beneficios b
            LEFT JOIN rm ON b.id_consumo=rm.id 
            LEFT JOIN unidades u ON b.id_unidad = u.id
            WHERE rm.fecha_venta BETWEEN " . str_replace("-", "", $FechaI) . " AND " . str_replace("-", "", $FechaF) . " 
            GROUP BY u.cliente
        ) ttCli ON ttCli.clienteG=cli.id
    WHERE rm.descuento > 0 AND rm.fecha_venta BETWEEN " . str_replace("-", "", $FechaI) . " AND " . str_replace("-", "", $FechaF) . " AND b.tipo='I'
    GROUP BY cli.id,$TipoGroup $Group;";

$registros = utils\IConnection::getRowsFromQuery($SqlDescuentosAplicadosTarjetaMonederoXConsumos);

$Gtt = "SELECT descripcion,IF(tipo_concepto='V','Litros generan','Pesos generan') Tipo ,monto_promocion,tipo_periodo,producto_promocion,factores_producto FROM periodo_puntos WHERE activo = 1;";
$RsGt = utils\IConnection::execSql($Gtt);
$Names = explode(",", $RsGt["producto_promocion"]);
$Values = explode(",", $RsGt["factores_producto"]);
for ($e = 0; $e <= 3; $e++) {
    $Vaals[$Names[$e]] = $Values[$e];
}

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
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 20%;"></td>
                        <td>
                            <table style="background-color: #DADADA; border: 1px solid black;width: 100%;font-weight: bold;">
                                <tr>
                                    <td style=" border: 1px solid black;padding: 8px;text-align: left;">
                                        <?= $RsGt["monto_promocion"] ?> <?= $RsGt["Tipo"] ?>
                                    </td>
                                    <?php
                                    foreach ($Vaals as $Key => $Val) {
                                        ?>
                                        <td style=" border: 1px solid black;padding: 8px;text-align: left;">
                                            <?php
                                            $SqlCom = "SELECT descripcion FROM com WHERE clavei = '$Key'";
                                            $Dec = utils\IConnection::execSql($SqlCom);
                                            ?>
                                            <?= $Dec["descripcion"] !== null ? $Dec["descripcion"] : "ADITIVOS" ?>.- $<?= $Val ?>
                                        </td>
                                        <?php
                                    }
                                    ?>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <table style="width: 100%;" summary="Mostramos titulo del reporte"><tr><th><h3><?= $Titulo ?></h3></th></tr></table>
                <div id="Reportes">
                    <table aria-hidden="true" class="display" style="width: 100%;">
                        <thead>
                            <tr style="font-weight: bold;">
                                <td><?= $NameGroup ?></td>
                                <td>Fecha</td>
                                <td>Unidad</td>
                                <td>Volumen</td>
                                <td>Importe</td>
                                <td>Descuento</td>
                                <td>Total</td>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                            $nCnt = $nImp = $nCos = 0;
                            $NombreActual = "";
                            $e = 0;
                            foreach ($registros as $rg) {
                                $TipoGroup = $NameGroup === "Dia" ? $rg["fecha"] : $rg["id"];
                                if ($rg["nombre"] !== $NombreActual) {
                                    if ($e > 0) {
                                        ?>
                                        <tr style="font-weight: bold;">
                                            <td></td>
                                            <td></td>
                                            <td  style="text-align: right;">SubTotal</td>
                                            <td class="numero"><?= number_format($VolumenTCli, 2) ?></td>
                                            <td class="numero"><?= number_format($SubTCli, 2) ?></td>
                                            <td class="numero"><?= number_format($DescuentoTCli, 2) ?></td>
                                            <td class="numero"><?= number_format($SubGranTCli, 2) ?></td>
                                        </tr>
                                        <?php
                                    }
                                    $e++;
                                    $SubTCli = $VolumenTCli = $DescuentoTCli = $SubGranTCli = 0;
                                    ?>
                                    <tr>
                                        <td colspan="7" style="font-weight: bold;">
                                            <?= $rg["nombre"] ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                $SubTCli += $rg["importe"];
                                $VolumenTCli += $rg["volumen"];
                                $DescuentoTCli += $rg["descuento"];
                                $SubGranTCli += $rg["importe"] - $rg["descuento"];
                                ?>
                                <tr>
                                    <td><?= $TipoGroup ?></td>
                                    <td><?= $rg["inicio_venta"] ?></td>
                                    <td><?= $rg["codigo"] ?></td>
                                    <td class="numero"><?= number_format($rg["volumen"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["descuento"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["importe"] - $rg["descuento"], 2) ?></td>
                                </tr>
                                <?php
                                $nCnt += $rg["volumen"];
                                $nImp += $rg["importe"];
                                $nDesc += $rg["descuento"];
                                $tot += $rg["importe"] - $rg["descuento"];
                                $nRng++;
                                $NombreActual = $rg["nombre"];
                            }
                            ?>
                            <tr style="font-weight: bold;">
                                <td></td>
                                <td></td>
                                <td style="text-align: right;">SubTotal</td>
                                <td class="numero"><?= number_format($VolumenTCli, 2) ?></td>
                                <td class="numero"><?= number_format($SubTCli, 2) ?></td>
                                <td class="numero"><?= number_format($DescuentoTCli, 2) ?></td>
                                <td class="numero"><?= number_format($SubGranTCli, 2) ?></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td></td>
                                <td></td>
                                <td>Gran Total</td>
                                <td><?= number_format($nCnt, 2) ?></td>
                                <td><?= number_format($nImp, 2) ?></td>
                                <td><?= number_format($nDesc, 2) ?></td>
                                <td><?= number_format($tot, 2) ?></td>
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
                                    </table>
                                </td>
                                <td>
                                    <table style="width: 100%" aria-hidden="true">
                                        <tr>
                                            <td colspan="3">Orden</td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: left;padding-left: 5px">
                                                <input type="checkbox" name="MasVentas" id="MasVentas">No. Ventas
                                            </td>
                                            <td>
                                                <input type="checkbox" name="MasLitro" id="MasLitros">No. Litros
                                            </td>
                                            <td>
                                                <input type="checkbox" name="MasDescuento" id="MasDescuento">Descuento
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