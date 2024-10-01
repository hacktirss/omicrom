<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

$request = utils\HTTPUtils::getRequest();
$mysqli = iconnect();

$incluir = 0;
$nFac = 1;
$Titulo = "ID del $FechaI al $FechaF en litros";
$valid = false;

if ($request->hasAttribute("Incluir")) {
    $incluir = 1;
}

$result = $mysqli->query("SELECT valor FROM variables_corporativo WHERE llave = 'balance';");
$balance = $result->fetch_array();
$VtaExtra = 0;
$registros = [];

if ($mysqli->query($selectBalanceCreate)) {
    $registros = utils\IConnection::getRowsFromQuery($selectBalance, $mysqli);
} else {
    error_log($mysqli->error);
}
$Fanquicia = "SELECT valor FROM omicrom.variables_corporativo WHERE llave ='Franquicia'";
$Fnc = utils\IConnection::execSql($Fanquicia);
$TipoFanquicia = $Fnc["valor"];
$Id = 204; /* Número de en el orden de la tabla submenus */
$data = array("Nombre" => $Titulo, "Reporte" => $Id, "busca" => $busca,
    "FechaI" => $FechaI, "FechaF" => $FechaF, "Informacion" => $Informacion,
    "Detallado" => "No", "Textos" => "Subtotal", "Filtro" => "2");
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <script type="text/javascript" src="js/export_.js"></script>
        <script type="text/javascript" src="js/balanceProductos.js"></script>
        <title><?= $Gcia ?></title>
        <script>

            $(document).ready(function () {
                let value = "<?= $busca ?>";
                let litros = "<?= $nFac ?>";
                let incluir = "<?= $incluir ?>";

                $("#FechaI").val("<?= $FechaI ?>").attr("size", "10");
                $("#FechaF").val("<?= $FechaF ?>").attr("size", "10");
                $("#cFechaI").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaI")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#cFechaF").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaF")[0], "yyyy-mm-dd", $(this)[0]);
                });
                var i = 0;
                setInterval(function () {
                    console.log(i % 2);
                    if (i % 2 == 0) {
                        $(".Alerta").css({"background-color": "#E74C3C", "color": "white"});
                    } else {
                        $(".Alerta").css({"background-color": "#F8C471", "color": "#2C3E50"});
                    }
                    i++;
                }, 1000);
                $("#Informacion").val("<?= $Informacion ?>");
                $("input[name=busca][value=" + value + "]").attr("checked", true);
                if (litros === "1") {
                    $("#Litros").attr("checked", true);
                }
                if (incluir === "1") {
                    $("#Incluir").attr("checked", true);
                }

                $("#Descargar").click(function () {
                    var instance = new TableExport($("#TablaExcel"), {
                        formats: ["xlsx"],
                        ignoreCSS: ".tableexport-ignore",
                        trimWhitespace: true,
                        filename: "<?= $Tiulo ?>",
                        RTL: false,
                        bootstrap: true,
                        exportButtons: false
                    });
                    var exportData = instance.getExportData()["TablaExcel"]["xlsx"];
                    instance.export2file(exportData.data, exportData.mimeType, exportData.filename, exportData.fileExtension);
                });
            });
            function ExportToExcel(type, fn, dl) {
                var elt = document.getElementById('tbl_exporttable_to_xls');
                var wb = XLSX.utils.table_to_book(elt, {sheet: "sheet1"});
                return dl ?
                        XLSX.write(wb, {bookType: type, bookSST: true, type: 'base64'}) :
                        XLSX.writeFile(wb, fn || ('BalanceProductos.' + (type || 'xlsx')));
            }
        </script>

    </head>

    <body>
        <div id="container">

            <div id="tbl_exporttable_to_xls">
                <?php nuevoEncabezado($Titulo) ?>
                <div id="TablaExcel">
                    <div id="Reportes">
                        <table summary="Opciones para la visualización con volumen bruto y compensado">           
                            <tr class="titulos" >
                                <th title="Mostramos valores con volumen bruto" style="color: white;width: 50%;" class="BotonTipoVolumen ini" data-tipovolumen="Bruto">Volumenes Bruto</th>
                                <th title="Mostramos valores con volumen compensado" style="border-right: 1px solid #566573;color: white;" class="BotonTipoVolumen" data-tipovolumen="Compensado">Volumenes Compensado por Temperatura</th>
                            </tr>
                        </table>
                        <table aria-hidden="true">
                            <tbody>
                                <?php
                                $clave = "";
                                $InventarioI = $InventarioF = 0;
                                $X = 0;
                                $Vdc = 0;
                                foreach ($registros as $rg) {
                                    $X++;
                                    if (!empty($clave) && $clave !== $rg["clavei"]) {
                                        if ($Informacion == TipoInformacion::OMICROM || $Informacion == TipoInformacion::COMPARATIVO) {
                                            ?>
                                            <tr class="subtotal">
                                                <td colspan="2">Resumen</td>
                                                <td class="SelectBruto"><?= number_format($InventarioI / $nFac, 3) ?></td>
                                                <td class="SelectBruto"><?= number_format($Ventas / $nFac, 3) ?></td> 
                                                <td class="SelectBruto"><?= number_format($Venta_consignacion / $nFac, 3) ?></td> 
                                                <?php if ($TipoFanquicia === "PEMEX") { ?>
                                                    <td class="SelectBruto"><?= number_format($VolumenDevuelto, 3) ?></td>
                                                <?php } ?>
                                                <?php if ($incluir) { ?>
                                                    <td class="SelectBruto"><?= number_format($VtaExtra / $nFac, 3) ?></td> 
                                                <?php } ?>
                                                <td class="SelectBruto"><?= number_format($Cargas / $nFac, 3) ?></td> 
                                                <td class="SelectBruto"><?= number_format($Jarreos / $nFac, 3) ?></td>
                                                <td class="SelectBruto"><?= number_format(($InventarioI - $Ventas + $VtaExtra - $Venta_consignacion + $Cargas + $VolumenDevuelto) / $nFac, 3) ?></td>
                                                <td class="SelectBruto"><?= number_format($InventarioF / $nFac, 3) ?></td>
                                                <td class="numero SelectBruto"><?= number_format($DiferenciaBruto, 3) ?></td>
                                                <td class="SelectBruto"><?= number_format(($DiferenciaBruto * 100) / ($InventarioF / $nFac), 2) ?></td>
                                                <td class="SelectCompensado"><?= number_format($InicialCompensado / $nFac, 3) ?></td>
                                                <td class="SelectCompensado"><?= number_format($VentaCompensada / $nFac, 3) ?></td>
                                                <td class="SelectCompensado"><?= number_format($VentaCompensadaConsig / $nFac, 3) ?></td>
                                                <?php if ($TipoFanquicia === "PEMEX") { ?>
                                                    <td class="SelectCompensado"><?= number_format($VolumenDevuelto, 3) ?></td>
                                                <?php } ?>
                                                <?php if ($balance["valor"] == 1 && $Informacion === TipoInformacion::OMICROM) { ?>
                                                    <td class="SelectCompensado"><?= number_format($Bruto / $nFac, 3) ?></td>
                                                    <td class="SelectCompensado"><?= number_format($Diferencia / $nFac, 3) ?></td>
                                                <?php } ?>
                                                <?php if ($Informacion == TipoInformacion::COMPARATIVO) { ?>
                                                    <td class="SelectCompensado"><?= number_format($VentasCV / $nFac, 3) ?></td>
                                                <?php } ?>
                                                <?php
                                                if ($incluir) {
                                                    echo $busca == 1 ? '<td class="SelectCompensado">' . number_format($VtaExtra / $nFac, 3) . '</td>' : ' <td class="SelectCompensado">' . number_format($Cargas / $nFac, 3) . '</td>';
                                                }
                                                ?>
                                                <td class="SelectCompensado"><?= number_format($Ccomp / $nFac, 3) ?></td>
                                                <?php if ($Informacion == TipoInformacion::COMPARATIVO) { ?>
                                                    <td><?= number_format($CargasCV / $nFac, 3) ?></td> 
                                                <?php } ?>
                                                <?php $Ccomp = $busca == 1 ? $Ccomp : $Cargas ?>
                                                <td class="SelectCompensado"><?= number_format(($InicialCompensado - $VentaCompensada + $VtaExtra - $VentaCompensadaConsig + $Ccomp + $VolumenDevuelto) / $nFac, 2) ?></td>
                                                <td class="SelectCompensado"><?= number_format($InvFinalComp / $nFac, 3) ?></td>
                                                <td class="SelectCompensado"><?= number_format($DiferenciaCompensado, 3) ?></td>
                                                <td class="SelectCompensado"><?= number_format(($DiferenciaCompensado * 100) / ($InvFinalComp / $nFac), 2) ?></td>
                                            </tr>
                                            <?php
                                            $PorcentajeAcumuladoComp = $VolumenDevuelto = $PorcentajeAcumulado = $Ccomp = $InvTeorico = $Ventas = $DiferenciaCompensado = $Venta_consignacion = $DiferenciaBruto = $Cargas = $VentaCompensadaConsig = $VentaCompensada = $VtaExtra = $InvFinal = $VentasCV = $CargasCV = $Bruto = $Diferencia = $Jarreos = $InicialCompensado = $InvFinalComp = 0;
                                        } else {
                                            ?>
                                            <tr class="subtotal">
                                                <td colspan="2">Resumen</td>
                                                <td class="SelectBruto"><?= number_format(0, 3) ?></td>
                                                <td class="SelectBruto"><?= number_format($InicialCompensado / $nFac, 3) ?></td>
                                                <td class="SelectBruto"><?= number_format($Ventas / $nFac, 3) ?></td>  
                                                <td class="SelectBruto"><?= number_format(0, 3) ?></td>
                                                <td class="SelectBruto"><?= number_format($Cargas / $nFac, 3) ?></td> 
                                                <td class="SelectCompensado"><?= number_format(0, 3) ?></td>
                                                <td class="SelectCompensado"><?= number_format(0, 3) ?></td>
                                                <td class="SelectCompensado"></td>
                                                <td class="SelectCompensado"><?= number_format(0, 3) ?></td>
                                                <td class="SelectCompensado"></td>
                                            </tr>
                                            <?php
                                            $InvTeorico = $Ventas = $Venta_consignacion = $Cargas = $InvFinal = $VentasCV = $CargasCV = $InicialCompensadoF = $InventarioFComp = $Venta_consignacion = 0;
                                        }
                                    }

                                    if (empty($clave) || $clave !== $rg["clavei"]) {
                                        $Clspan = $Informacion == TipoInformacion::COMPARATIVO ? 21 : 19;
                                        $Clspan = $incluir ? $Clspan + 1 : $Clspan;
                                        $ColorBack = "SELECT color FROM com WHERE descripcion = '" . $rg["descripcion"] . "';";
                                        $Clr = utils\IConnection::execSql($ColorBack);
                                        ?>
                                        <tr class="titulo">
                                            <td style="color:white;background-color:  <?= $Clr["color"] ?>;border-left: 1px solid black;border-right: 1px solid black;" colspan="<?= $Clspan ?>"><?= $rg["clave"] ?> &nbsp; <?= $rg["descripcion"] ?> &nbsp; <?= $rg["um"] ?></td>
                                        </tr>
                                        <tr class="titulos">
                                            <td style="border-left: 1px solid #566573;"></td>
                                            <td width="100px;">Fecha</td>
                                            <td class="SelectBruto">Inventario<br>Inicial</td>
                                            <td class="SelectBruto">Ventas</td>
                                            <td class="SelectBruto">Ventas<br> Consignación</td>
                                            <?php if ($TipoFanquicia === "PEMEX") { ?>
                                                <td class="SelectBruto">Volumen<br> Devolución</td>
                                            <?php } ?>
                                            <?php if ($incluir) { ?>
                                                <td class="SelectBruto">Ventas en<br>Descarga</td>
                                            <?php } ?>
                                            <td class="SelectBruto">Compras</td>
                                            <td class="SelectBruto">Jarreos</td>
                                            <td class="SelectBruto">Inventario<br>Teorico</td>
                                            <td class="SelectBruto">Inventario<br>Final</td>
                                            <td class="SelectBruto">Diferencia</td>
                                            <td class="SelectBruto" style="width: 3%;">%</td>
                                            <td class="SelectCompensado">Inventario<br> Inicial <br></td>
                                            <td class="SelectCompensado">Ventas <br></td>
                                            <td class="SelectCompensado">Ventas <br> Consignación</td>
                                            <?php if ($TipoFanquicia === "PEMEX") { ?>
                                                <td class="SelectCompensado">Volumen<br> Devolución</td>
                                            <?php } ?>
                                            <?php if ($incluir) { ?>
                                                <td class="SelectCompensado">Ventas en<br>Descarga</td>
                                            <?php } ?>
                                            <?php if ($balance["valor"] == 1 && $Informacion === TipoInformacion::OMICROM) { ?>
                                                <td class="SelectCompensado">Bruto</td>
                                                <td class="SelectCompensado">Dif.</td>
                                            <?php } ?>
                                            <?php if ($Informacion == TipoInformacion::COMPARATIVO) { ?>
                                                <td class="SelectCompensado">Ventas CV</td>
                                            <?php } ?>
                                            <td class="SelectCompensado">Compras <br></td>
                                            <?php if ($Informacion == TipoInformacion::COMPARATIVO) { ?>
                                                <td class="SelectCompensado">Compras CV</td>
                                            <?php } ?>
                                            <td class="SelectCompensado">Inventario<br>Teorico </td>
                                            <td class="SelectCompensado">Inventario<br>Final <br></td>
                                            <td class="SelectCompensado">Diferencia</td>
                                            <td class="SelectCompensado" style="width: 3%;border-right: 1px solid #566573;">%</td>
                                        </tr>
                                        <?php
                                        $InventarioI = $rg["inicial"];
                                        $InicialCompensado = $rg["inicial_compensado"];
                                    }

                                    $clave = $rg["clavei"];

                                    if ($Informacion == TipoInformacion::OMICROM || $Informacion == TipoInformacion::COMPARATIVO) {
                                        if ($request->getAttribute("Incluir") === "on") {
                                            $selectRmEntreDescarga = "select getVolumenDescarga('" . $rg["clave"] . "', '" . $rg["fecha"] . "' , '" . $rg["fecha"] . "') cantidad;";
                                            $Rmd = utils\IConnection::execSql($selectRmEntreDescarga);
                                        }
                                        $FechaLF = "DATE('" . $rg["fecha"] . "') ORDER BY fecha_hora_s  DESC LIMIT " . $rg["limite"] . "";
                                        if ($rg["fecha"] !== date("Y-m-d")) {
                                            $FechaLF = "DATE_ADD('" . $rg["fecha"] . "',INTERVAL 1 DAY) ORDER BY fecha_hora_s  ASC LIMIT " . $rg["limite"] . "";
                                        }

                                        $selectLecturaFinal = " 
                                    SELECT SUM(cantidad) cantidad,fecha,fecha_hora_s,cantidadcm 
                                    FROM (
                                        SELECT IFNULL(volumen_actual, 0) cantidad,IFNULL(volumen_compensado, 0) cantidadcm,DATE (fecha_hora_s) fecha, fecha_hora_s
                                        FROM tanques_h
                                        WHERE TRUE AND tanque IN (" . $rg["tanques"] . ") AND DATE( fecha_hora_s ) = $FechaLF
                                    ) t ";

                                        $Ifin = utils\IConnection::execSql($selectLecturaFinal);

                                        $Iinicial = $rg["inicial"];
                                        $Ifinal = $Ifin["cantidad"];

                                        $Ifinalcm = $Ifin["cantidadcm"];
                                        $Compras = $busca === "1" ? $rg["compras"] : $rg["volumen_docto"] + $rg["volumenDevolucion"];

                                        if ($Informacion == TipoInformacion::COMPARATIVO) {
                                            $data = leer_archivo_zip_to_xml($rg["nombrearchivo"], $rg["claveProducto"], $rg["claveSubProducto"]);
                                        }
                                        $InvTeorico = $Iinicial - ($rg["venta"] + $rg["venta_consignacion"]) + $Compras + ($incluir ? $Rmd["cantidad"] : 0);
                                        $RsCmp = $busca == 1 ? $rg["compras_compensado"] : $Compras;
                                        $InvTeoricoCompensado = ($rg["inicial_compensado"] - $rg["venta_compensada"] - $rg["venta_consignacion_compensada"] + ($incluir ? $Rmd["cantidad"] : 0) + $RsCmp ) / $nFac;
                                        $date1 = new DateTime($rg["fecha"] . " 23:59:59");
                                        $date2 = new DateTime($Ifin["fecha_hora_s"]);
                                        $diff = $date1->diff($date2);
                                        $difereciaFechas = ( ($diff->days * 24 ) * 60 ) + ( $diff->i ) . " minutos";
                                        $style = "";
                                        if ($diff->i > 5) {
                                            $style = "background-color: #F7FF7C";
                                        }
                                        $TitleDif = $clssAdd = "";
                                        if (ABS((((($Ifinal / $nFac) - ($InvTeorico / $nFac))) * 100) / ($Ifinal / $nFac)) > 0.5) {
                                            $clssAdd = "Alerta";
                                            $TitleDif = "title='Diferencia mayor a 0.5'";
                                        }
                                        ?>
                                        <tr style="<?= $style ?>" title="Fin de muestra: <?= $Ifin["fecha_hora_s"] ?> Dif: <?= $difereciaFechas ?>">
                                            <td style="border-left: 1px solid #566573;"><?= $X ?></td>
                                            <td style="width: 100px;"><?= $rg["fecha"] ?></td>
                                            <td class="numero SelectBruto"><?= number_format($Iinicial / $nFac, 3) ?></td>
                                            <td class="numero SelectBruto"><?= number_format($rg["venta"] / $nFac, 3) ?></td>
                                            <td class="numero SelectBruto"><?= number_format($rg["venta_consignacion"] / $nFac, 3) ?></td>
                                            <?php if ($TipoFanquicia === "PEMEX") { ?>
                                                <td class="numero SelectBruto"><?= number_format($rg["volumenDevolucion"] / $nFac, 3) ?></td>
                                            <?php } ?>
                                            <?php if ($incluir) { ?>
                                                <td class="numero SelectBruto"><?= number_format($Rmd["cantidad"] / $nFac, 3) ?></td>
                                            <?php } ?>
                                            <td class="numero SelectBruto"><?= number_format($Compras / $nFac, 3) ?></td>
                                            <td class="numero SelectBruto"><?= number_format($rg["jarreos"] / $nFac, 3) ?></td>
                                            <td class="numero SelectBruto"><?= number_format($InvTeorico / $nFac, 3) ?></td>
                                            <td class="numero SelectBruto"><?= number_format($Ifinal / $nFac, 3) ?></td>
                                            <td class="numero SelectBruto <?= $clssAdd ?>"><?= number_format(($Ifinal / $nFac) - ($InvTeorico / $nFac), 3) ?></td>
                                            <td class="numero SelectBruto"><?= number_format((((($Ifinal / $nFac) - ($InvTeorico / $nFac))) * 100) / ($Ifinal / $nFac), 2) ?></td>
                                            <td class="numero SelectCompensado"><?= number_format($rg["inicial_compensado"] / $nFac, 3) ?></td>
                                            <td class="numero SelectCompensado"><?= number_format($rg["venta_compensada"] / $nFac, 3) ?></td>
                                            <td class="numero SelectCompensado"><?= number_format($rg["venta_consignacion_compensada"] / $nFac, 3) ?></td>
                                            <?php if ($TipoFanquicia === "PEMEX") { ?>
                                                <td class="numero SelectCompensado"><?= number_format($rg["volumenDevolucion"] / $nFac, 3) ?></td>
                                                <?php
                                            }
                                            if ($incluir) {
                                                ?>
                                                <td class="numero SelectCompensado"><?= number_format($Rmd["cantidad"] / $nFac, 3) ?></td>
                                            <?php } ?>
                                            <?php if ($balance["valor"] == 1 && $Informacion === TipoInformacion::OMICROM) { ?>
                                                <td class="numero SelectCompensado"><?= number_format($rg["bruto"] / $nFacc, 3) ?></td>
                                                <td class="numero  SelectCompensado"><?= number_format($rg["diferencia"] / $nFac, 3) ?></td>
                                            <?php } ?>
                                            <?php if ($Informacion == TipoInformacion::COMPARATIVO) { ?>
                                                <td class="numero SelectCompensado"><?= number_format($data["venta"] / $nFac, 3) ?></td>
                                            <?php } ?>
                                            <?= $busca == 1 ? '<td class="numero SelectCompensado">' . number_format($rg["compras_compensado"] / $nFac, 3) . '</td>' : '<td class="numero SelectCompensado">' . number_format($Compras / $nFac, 3) . '</td>' ?>
                                            <?php if ($Informacion == TipoInformacion::COMPARATIVO) { ?>
                                                <td class="numero SelectCompensado"><?= number_format($data["compras"] / $nFac, 3) ?></td>
                                            <?php } ?>
                                            <td class="numero SelectCompensado"><?= number_format($InvTeoricoCompensado + $rg["volumenDevolucion"], 3) ?></td>
                                            <td class="numero SelectCompensado"><?= number_format($rg["final_compensado"] / $nFac, 3) ?></td>
                                            <td class="SelectCompensado numero <?= $clssAdd ?>" <?= $TitleDif ?>><?= number_format((($rg["final_compensado"] / $nFac) - $InvTeoricoCompensado), 3) ?></td>
                                            <td style="border-right: 1px solid #566573;" class="SelectCompensado"><?= number_format((((($rg["final_compensado"] / $nFac) - $InvTeoricoCompensado)) * 100) / ($rg["final_compensado"] / $nFac), 2) ?></td>
                                        </tr>
                                        <?php
                                        $PorcentajeAcumulado += ((($Ifinal / $nFac) - ($InvTeorico / $nFac)) * 100) / ($Ifinal / $nFac);
                                        $PorcentajeAcumuladoComp += (((($rg["final_compensado"] / $nFac) - $InvTeoricoCompensado)) * 100) / ($rg["final_compensado"] / $nFac);
                                        $Ventas += $rg["venta"];
                                        $Venta_consignacion += $rg["venta_consignacion"];
                                        $Jarreos += $rg["jarreos"];
                                        $Cargas += $Compras;
                                        $Ccomp += $busca == 1 ? $rg["compras_compensado"] : $Compras;
                                        $VtaExtra += ($incluir ? $Rmd["cantidad"] : 0);
                                        $Bruto += $rg["bruto"];
                                        $Diferencia += $rg["diferencia"];
                                        $DiferenciaCompensado += (($rg["final_compensado"] / $nFac) - $InvTeoricoCompensado);
                                        $InvFinal = $Ifinal;
                                        $InvFinalN = $rg["final"];
                                        $VentaCompensada += $rg["venta_compensada"];
                                        $VentaCompensadaConsig += $rg["venta_consignacion_compensada"];
                                        $InventarioF = $Ifinal;
                                        $DiferenciaBruto += ($Ifinal / $nFac) - ($InvTeorico / $nFac);
                                        $InvFinalComp = $Ifinalcm;
                                        $InvFinalNC = $rg["final_compensado"];
                                        $InventarioFComp = $Ifinalcm;
                                        $InicialCompensadoF += $rg["inicial_compensado"];
                                        $Tot_Bruto += $rg["bruto"];
                                        $Tot_Dif += $rg["diferencia"];
                                        $Ccompensada += $rg["compras_compensado"];
                                        $T_Ventas += $rg["venta"];
                                        $T_Ventas_Consignacion += $rg["venta_consignacion"];
                                        $T_Jarreos += $rg["jarreos"];
                                        $T_Cargas += $Compras;
                                        $T_VtaExtra += ($incluir ? $Rmd["cantidad"] : 0);
                                        $TVentaFinalCompensado += $rg["venta_consignacion_compensada"];
                                        $TComprasCompesada += $rg["compras_compensado"];
                                        $VentaCompensado += $rg["venta_compensada"];
                                        $VolumenDevuelto += $rg["volumenDevolucion"] / $nFac;
                                        if ($Informacion == TipoInformacion::COMPARATIVO) {
                                            $VentasCV += $data["venta"];
                                            $CargasCV += $data["compras"];

                                            $T_VentasCV += $data["venta"];
                                            $T_CargasCV += $data["compras"];
                                        }
                                    } elseif ($Informacion == TipoInformacion::ARCHIVOS) {

                                        $data = leer_archivo_zip_to_xml($rg["nombrearchivo"], $rg["claveProducto"], $rg["claveSubProducto"]);

                                        $Iinicial = $data["disponible"] + $data["extraccion"] - $data["compras"];
                                        $Ifinal = $data["disponible"];
                                        $Teorico = $Iinicial - $data["venta"] + $data["compras"];
                                        /**
                                         * Se toma la lectura final que arroja el sensor de tanques
                                         */
                                        ?>
                                        <tr>
                                            <td><?= $X ?></td>
                                            <td class="numero"><?= $rg["fecha"] ?></td>
                                            <td class="numeroSelectBruto"><?= number_format($Iinicial / $nFac, 3) ?></td>
                                            <td class="SelectBruto"></td>
                                            <td class="numero SelectBruto"><?= number_format($data["venta"] / $nFac, 3) ?></td>
                                            <td class="numero SelectBruto"><?= number_format(0, 3) ?></td>
                                            <td class="numero SelectBruto"><?= number_format($data["compras"] / $nFac, 3) ?></td>
                                            <td class="SelectCompensado"></td>
                                            <td class="numero SelectCompensado"><?= number_format($Teorico / $nFac, 3) ?></td>
                                            <td class="numero SelectCompensado"><?= number_format($Ifinal / $nFac, 3) ?></td>
                                            <td class="SelectCompensado"></td>
                                            <td class="numero SelectCompensado"><?= number_format(($Ifinal - $Teorico) / $nFac, 3) ?></td>
                                            <td class="SelectCompensado"></td>
                                        </tr>
                                        <?php
                                        $InvTeorico = $Iinicial;
                                        $Ventas += $data["venta"];
                                        $Cargas += $data["compras"];
                                        $InvFinal = $Ifinal;

                                        $T_Ventas += $data["venta"];
                                        $T_Cargas += $data["compras"];
                                        $DiferenciaBrutoFinal = $DiferenciaBruto;
                                    }
                                }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr class="subtotal">
                                    <td></td>
                                    <td colslpan="2">Resumen</td>
                                    <td class="SelectBruto"><?= number_format($InventarioI / $nFac, 3) ?></td>
                                    <td class="SelectBruto"><?= number_format($Ventas / $nFac, 3) ?></td>  
                                    <td class="SelectBruto"><?= number_format($Venta_consignacion / $nFac, 3) ?></td> 
                                    <?php if ($TipoFanquicia === "PEMEX") { ?>
                                        <td class="SelectBruto"><?= number_format($VolumenDevuelto, 3) ?></td>
                                    <?php } ?>
                                    <?php if ($incluir) { ?>
                                        <td class="SelectBruto"><?= number_format($VtaExtra / $nFac, 3) ?></td> 
                                    <?php } ?>
                                    <td class="SelectBruto"><?= number_format($Cargas / $nFac, 3) ?></td> 
                                    <td class="SelectBruto"><?= number_format($Jarreos / $nFac, 3) ?></td>
                                    <td class="SelectBruto"><?= number_format(($InventarioI - $Ventas - $Venta_consignacion + $Cargas + $VtaExtra + $VolumenDevuelto) / $nFac, 3) ?></td>
                                    <td class="SelectBruto"><?= number_format($Ifinal / $nFac, 3) ?></td>
                                    <td class="numero SelectBruto"><?= number_format($DiferenciaBruto, 3) ?></td>
                                    <td class="numero SelectBruto"><?= number_format(($DiferenciaBruto * 100) / ($Ifinal / $nFac), 2) ?></td>
                                    <td class="SelectCompensado"><?= number_format($InicialCompensado / $nFac, 3) ?></td>
                                    <td class="SelectCompensado"><?= number_format($VentaCompensada / $nFac, 3) ?></td>
                                    <td class="SelectCompensado"><?= number_format($VentaCompensadaConsig / $nFac, 3) ?></td>
                                    <?php if ($TipoFanquicia === "PEMEX") { ?>
                                        <td class="SelectCompensado"><?= number_format($VolumenDevuelto, 3) ?></td>
                                    <?php } ?>
                                    <?php if ($incluir) { ?>
                                        <td class="SelectCompensado"><?= number_format($VtaExtra / $nFac, 3) ?></td> 
                                    <?php } ?>
                                    <?php if ($balance["valor"] == 1 && $Informacion === TipoInformacion::OMICROM) { ?>
                                        <td class="SelectCompensado"><?= number_format($Bruto / $nFac, 3) ?></td>
                                        <td class="SelectCompensado"><?= number_format($Diferencia / $nFac, 3) ?></td>
                                    <?php } ?>
                                    <?php if ($Informacion == TipoInformacion::COMPARATIVO) { ?>
                                        <td class="SelectCompensado"><?= number_format($VentasCV / $nFac, 3) ?></td>
                                    <?php } ?>
                                    <td class="SelectCompensado"><?= number_format($Ccomp / $nFac, 3) ?></td>

                                    <?php if ($Informacion == TipoInformacion::COMPARATIVO) { ?>
                                        <td class="SelectCompensado"><?= number_format($CargasCV / $nFac, 3) ?></td> 
                                    <?php } ?>
                                    <td class="SelectCompensado"><?= number_format(($InicialCompensado - $VentaCompensada - $VentaCompensadaConsig + $Ccomp + $VtaExtra + $VolumenDevuelto) / $nFac, 2) ?></td>
                                    <td class="SelectCompensado"><?= number_format($rg["final_compensado"] / $nFac, 3) ?></td>
                                    <td class="SelectCompensado"><?= number_format($DiferenciaCompensado, 3) ?></td>
                                    <td class="SelectCompensado"><?= number_format((($DiferenciaCompensado) * 100) / ($rg["final_compensado"] / $nFac), 2) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div id="footer">
                <form name="formActions" method="post" action="" id="form" class="oculto">
                    <div id="Controles">
                        <table aria-hidden="true">
                            <tr style="height: 40px;">
                                <td style="width: 20%;">
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
                                    <table aria-hidden="true">
                                        <tr>
                                            <td style="text-align: right">Tipo: &nbsp;</td>
                                            <td>
                                                <select name="Informacion" id="Informacion">
                                                    <option value="1">Sistema Omicrom</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: center">Incluir venta durante la descarga: </td>
                                            <td style="text-align: left"><input type="checkbox" name="Incluir" id="Incluir"/></td>
                                        </tr>
                                    </table>
                                </td>
                                <td>
                                    <table aria-hidden="true">
                                        <tr>
                                            <td style="text-align: right">Vol. de recepcion: &nbsp;</td>
                                            <td style="text-align: left"><input type="radio" name="busca" value="1"></td>
                                        </tr>
                                        <tr>
                                            <td style="text-align: right">Vol. documentado: &nbsp;</td>
                                            <td style="text-align: left"><input type="radio" name="busca" value="2"></td>
                                        </tr>
                                    </table>
                                </td>
                                <td>
                                    <span><input type="submit" name="Boton" value="Enviar"></span>
                                    <?php
                                    if ($usuarioSesion->getTeam() !== "Operador") {
                                        ?>
                                        <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                                        <span class="ButtonExcel"><a href="report_excel_reports.php?<?= http_build_query($data) ?>"><i class="icon fa fa-lg fa-bold fa-file-excel-o" aria-hidden="true"></i></a></span>
                                        <span><button onclick="ExportToExcel('xlsx')"><i class="icon fa fa-lg fa-bold fa-file-excel-o" aria-hidden="true">v2</i></button></span>

                                        <?php
                                    }
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </form>
                <?php topePagina() ?>
            </div>
        </div>
    </body>
</html>

