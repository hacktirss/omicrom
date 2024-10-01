<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

if ($request->hasAttribute("criteria")) {
    utils\HTTPUtils::setSessionValue("MasVentas", "");
    utils\HTTPUtils::setSessionValue("MasLitro", "");
    utils\HTTPUtils::setSessionValue("MasDescuento", "");
}
if ($request->hasAttribute("MasVentas")) {
    utils\HTTPUtils::setSessionValue("MasVentas", true);
    utils\HTTPUtils::setSessionValue("MasLitro", false);
    utils\HTTPUtils::setSessionValue("MasDescuento", false);
}
if ($request->hasAttribute("MasLitro")) {
    utils\HTTPUtils::setSessionValue("MasLitro", true);
    utils\HTTPUtils::setSessionValue("MasVentas", false);
    utils\HTTPUtils::setSessionValue("MasDescuento", false);
}
if ($request->hasAttribute("MasDescuento")) {
    utils\HTTPUtils::setSessionValue("MasDescuento", true);
    utils\HTTPUtils::setSessionValue("MasVentas", false);
    utils\HTTPUtils::setSessionValue("MasLitro", false);
}
$MasVentas = utils\HTTPUtils::getSessionValue("MasVentas");
$MasLitro = utils\HTTPUtils::getSessionValue("MasLitro");
$MasDescuento = utils\HTTPUtils::getSessionValue("MasDescuento");

$Titulo = "Beneficios del $FechaI al $FechaF";
$TipoGroup = $Detallado == "No" ? "GROUP BY rm.fecha_venta" : "GROUP BY b.id";
$NameGroup = $Detallado == "No" ? "Dia" : "Ticket";

$Group .= $MasVentas ? "ORDER BY cliOrdn.cntG DESC" : "";
$Group .= $MasLitro ? "ORDER BY cliOrdn.volumenG DESC" : "";
$Group .= $MasDescuento ? "ORDER BY cliOrdn.puntos DESC" : "";
if (!$MasVentas && !$MasDescuento && !$MasLitro) {
    $Group = "ORDER BY cli.id,u.id,rm.id DESC";
}
$SqlDescuentosAplicadosTarjetaMonederoXConsumos = "SELECT SUM(b.puntos) puntos ,SUM(b.consumido) consumido , cli.nombre,u.codigo,rm.id,SUM(rm.importe) importe
,SUM(rm.volumen) volumen,rm.fecha_venta,DATE_FORMAT(STR_TO_DATE(fecha_venta, '%Y%m%d'), '%Y-%m-%d') fecha
FROM beneficios b LEFT JOIN rm ON rm.id = id_consumo LEFT JOIN unidades u ON u.id = b.id_unidad 
LEFT JOIN cli ON cli.id = u.cliente
LEFT JOIN ( SELECT COUNT(1) cntG ,SUM(volumen) volumenG,SUM(b.puntos) puntos,u.cliente clienteG FROM beneficios b
    LEFT JOIN rm ON b.id_consumo=rm.id 
    LEFT JOIN unidades u ON b.id_unidad = u.id	
    WHERE rm.fecha_venta BETWEEN " . str_replace("-", "", $FechaI) . " AND " . str_replace("-", "", $FechaF) . "  
GROUP BY u.cliente) cliOrdn ON cliOrdn.clienteG=cli.id
WHERE tipo = 'P' AND tipo_consumo = 'C' AND
rm.fecha_venta BETWEEN " . str_replace("-", "", $FechaI) . "  AND " . str_replace("-", "", $FechaF) . " $TipoGroup  $Group;";

$registros = utils\IConnection::getRowsFromQuery($SqlDescuentosAplicadosTarjetaMonederoXConsumos);

$Gtt = "SELECT descripcion,IF(tipo_concepto='V','Litros generan','Pesos generan') Tipo ,monto_promocion,tipo_periodo,producto_promocion,factores_producto FROM periodo_puntos WHERE activo = 1;";
$RsGt = utils\IConnection::execSql($Gtt);
$Names = explode(",", $RsGt["producto_promocion"]);
$Values = explode(",", $RsGt["factores_producto"]);
for ($e = 0; $e <= 3; $e++) {
    $Vaals[$Names[$e]] = $Values[$e];
}
$Rcompenza = "SELECT * FROM (
                    SELECT SUM(cb.puntos) puntos,cb.fecha,0 importe,cb.id,0 descuento,cb.tm,cb.id idCb ,cb.id_ticket_beneficio itb,u.codigo,cli.nombre,
                    inv.descripcion FROM cobranza_beneficios cb 
                    LEFT JOIN beneficios b ON cb.id_beneficio = b.id 
                    LEFT JOIN unidades u ON u.id = b.id_unidad 
                    LEFT JOIN cli ON u.cliente = cli.id  
                    LEFT JOIN inv ON id_ticket_beneficio = inv.id
                    WHERE cb.tm='A' 
                    GROUP BY cb.id_ticket_beneficio,cb.fecha 
                    UNION ALL 
                    SELECT SUM(cb.puntos) puntos,fecha,rm.importe,rm.id,rm.descuento,cb.tm,cb.id idCb ,cb.id_ticket_beneficio itb,u.codigo,cli.nombre,com.descripcion
                    FROM cobranza_beneficios cb 
                    LEFT JOIN beneficios b ON cb.id_beneficio = b.id 
                    LEFT JOIN unidades u ON u.id = b.id_unidad
                    LEFT JOIN cli ON u.cliente = cli.id
                    LEFT JOIN rm ON cb.id_ticket_beneficio=rm.id 
                        LEFT JOIN com ON com.clavei=rm.producto
                    WHERE cb.tm='C' 
                    GROUP BY id_ticket_beneficio ORDER BY fecha DESC
                ) cn WHERE fecha BETWEEN " . str_replace("-", "", $FechaI) . "  AND " . str_replace("-", "", $FechaF) . "  ORDER BY codigo;";
$Rc = utils\IConnection::getRowsFromQuery($Rcompenza);
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
                $(".Clasificaciones").click(function () {
                    $(".Clasificaciones").prop('checked', false);
                    $(this).prop('checked', true);
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
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 20%;"></td>
                        <td>
                            <table style="background-color: #DADADA; border: 1px solid black;width: 100%;font-weight: bold;">
                                <tr>
                                    <td style=" border: 1px solid black;padding: 8px;text-align: left;">
                                        <?= number_format($RsGt["monto_promocion"], 3) ?> <?= $RsGt["Tipo"] ?>
                                    </td>
                                    <?php
                                    foreach ($Vaals as $Key => $Val) {
                                        ?>
                                        <td style=" border: 1px solid black;padding: 8px;text-align: left;">
                                            <?php
                                            $SqlCom = "SELECT descripcion FROM com WHERE clavei = '$Key'";
                                            $Dec = utils\IConnection::execSql($SqlCom);
                                            ?>
                                            <?= $Dec["descripcion"] !== null ? $Dec["descripcion"] : "ADITIVOS" ?>.- $<?= number_format($Val, 3) ?>
                                        </td>
                                        <?php
                                    }
                                    ?>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <h2>Consumos relacionados a puntos</h2>
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
                                <td>Puntos</td>
                                <td>Consumidos</td>
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
                                            <td>No. <?= $t ?></td>
                                            <td  style="text-align: right;">SubTotal</td>
                                            <td class="numero"><?= number_format($VolumenTCli, 2) ?></td>
                                            <td class="numero"><?= number_format($SubTCli, 2) ?></td>
                                            <td class="numero"><?= number_format($Cps, 2) ?></td>
                                            <td class="numero"><?= number_format($Consumido, 2) ?></td>
                                            <td class="numero"><?= number_format($SubGranTCli, 2) ?></td>
                                        </tr>
                                        <?php
                                    }
                                    $e++;
                                    $SubTCli = $VolumenTCli = $Consumido = $SubGranTCli = $Cps = 0;
                                    ?>
                                    <tr>
                                        <td colspan="8" style="font-weight: bold;">
                                            <?= $rg["nombre"] ?> .-  <?= $rg["codigo"] ?>
                                        </td>
                                    </tr>
                                    <?php
                                    $t = 0;
                                }
                                $SubTCli += $rg["importe"];
                                $VolumenTCli += $rg["volumen"];
                                $DescuentoTCli += $rg["descuento"];
                                $SubGranTCli += $rg["importe"] - $rg["descuento"];
                                ?>
                                <tr>
                                    <td><?= $TipoGroup ?></td>
                                    <td><?= $rg["fecha"] ?></td>
                                    <td><?= $rg["codigo"] ?></td>
                                    <td class="numero"><?= number_format($rg["volumen"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["puntos"], 0) ?></td>
                                    <td class="numero"><?= number_format($rg["consumido"], 0) ?></td>
                                    <td class="numero"><?= number_format($rg["importe"] - $rg["descuento"], 2) ?></td>
                                </tr>
                                <?php
                                $Cps += $rg["puntos"];
                                $Cpsgt += $rg["puntos"];
                                $nCnt += $rg["volumen"];
                                $nImp += $rg["importe"];
                                $nDesc += $rg["descuento"];
                                $Consumido += $rg["consumido"];
                                $tot += $rg["importe"] - $rg["descuento"];
                                $nRng++;
                                $t++;
                                $NombreActual = $rg["nombre"];
                            }
                            ?>
                            <tr style="font-weight: bold;">
                                <td></td>
                                <td>No. <?= $t ?></td>
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
                                <td></td>
                                <td>Gran Total</td>
                                <td><?= number_format($nCnt, 2) ?></td>
                                <td><?= number_format($nImp, 2) ?></td>
                                <td><?= number_format($nDesc, 2) ?></td>
                                <td><?= number_format($tot, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                    <h2>Beneficios consumidos</h2>
                    <table aria-hidden="true" class="display" style="width: 100%;">
                        <thead>
                            <tr style="font-weight: bold;">
                                <td>Fecha</td>
                                <td>Producto</td>
                                <td>Importe</td>
                                <td>Puntos</td>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                            $nCnt = $nImp = $nCos = 0;
                            $NombreActual = "";
                            $e = 0;
                            foreach ($Rc as $rg) {
                                if ($rg["nombre"] !== $NombreActual) {
                                    if ($e > 0) {
                                        ?>
                                        <tr style="font-weight: bold;">
                                            <td>No. <?= $t ?></td>
                                            <td  style="text-align: right;">SubTotal</td>
                                            <td class="numero"><?= number_format($SubTCli, 2) ?></td>
                                            <td class="numero"><?= number_format($Cps, 2) ?></td>
                                        </tr>
                                        <?php
                                    }
                                    $e++;
                                    $SubTCli = $VolumenTCli = $DescuentoTCli = $Cps = 0;
                                    ?>
                                    <tr>
                                        <td colspan="4" style="font-weight: bold;">
                                            <?= $rg["nombre"] ?> .- <?= $rg["codigo"] ?>
                                        </td>
                                    </tr>
                                    <?php
                                    $t = 0;
                                }
                                $SubTCli += $rg["importe"];
                                $VolumenTCli += $rg["volumen"];
                                $DescuentoTCli += $rg["descuento"];
                                $SubGranTCli += $rg["importe"] - $rg["descuento"];
                                ?>
                                <tr>
                                    <td><?= $rg["fecha"] ?></td>
                                    <td><?= $rg["descripcion"] ?> - <?= $rg["itb"] ?></td>
                                    <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["puntos"], 0) ?></td>
                                </tr>
                                <?php
                                $Cps += $rg["puntos"];
                                $Cpsgt += $rg["puntos"];
                                $nImp += $rg["importe"];
                                $nDesc += $rg["descuento"];
                                $nRng++;
                                $t++;
                                $NombreActual = $rg["nombre"];
                            }
                            ?>
                            <tr style="font-weight: bold;">
                                <td>No. <?= $t ?></td>
                                <td></td>
                                <td style="text-align: right;">SubTotal</td>
                                <td class="numero"><?= number_format($SubTCli, 2) ?></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td></td>
                                <td></td>
                                <td>Gran Total</td>
                                <td><?= number_format($nImp, 2) ?></td>
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
                                                <input type="checkbox" name="MasVentas" id="MasVentas" <?= $MasVentas ? "CHECKED" : "" ?> class="Clasificaciones">No. Ventas
                                            </td>
                                            <td>
                                                <input type="checkbox" name="MasLitro" id="MasLitros" <?= $MasLitro ? "CHECKED" : "" ?> class="Clasificaciones">No. Litros
                                            </td>
                                            <td>
                                                <input type="checkbox" name="MasDescuento" id="MasDescuento"  <?= $MasDescuento ? "CHECKED" : "" ?> class="Clasificaciones">Consumido
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