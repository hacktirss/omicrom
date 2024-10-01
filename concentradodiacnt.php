<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ('data/RmDAO.php');

use com\softcoatl\utils as utils;

$Contable = true;

require "./services/ReportesVentasService.php";

$DetalleTexto = $Detallado === "Si" ? "detallado" : "";
$DetalleAceite = $Detalle === "Si" ? "detalle" : "";
$Titulo = "Concentrado diario por $Desglose del $FechaI al $FechaF $DetalleTexto [Reporte Contable]";

$varD = getResumenVentas($mysqli, "'D'", $FechaI, $FechaF, "volumenp", "pesosp", utils\HTTPUtils::getSessionValue("Desglose"));
$varN = getResumenVentas($mysqli, "'N'", $FechaI, $FechaF, "volumen", "pesos", utils\HTTPUtils::getSessionValue("Desglose"));

//$registrosGRAL = utils\IConnection::getRowsFromQuery($selectConcentradoGRAL);
//$registrosGRALN = utils\IConnection::getRowsFromQuery($selectConcentradoGRALN);

$registrosAceGRAL = utils\IConnection::execSql($selectConcentradoAceGRAL);

$registrosGRAL_Jarreos = utils\IConnection::getRowsFromQuery($selectConcentradoJar);

$registrosCLI = utils\IConnection::getRowsFromQuery($selectConcentradoCli);

$registrosTAR = utils\IConnection::getRowsFromQuery($selectConcentradoTar);

$registrosACE = utils\IConnection::getRowsFromQuery($selectConcentradoAce);         //Aqui se obtiene el  array()
//echo print_r($selectConcentradoAce, true);
$registrosGAS = utils\IConnection::getRowsFromQuery($selectConcentradoGastos);

$registrosING1 = utils\IConnection::getRowsFromQuery($selectConcentradoIngresos1);

$registrosING2 = utils\IConnection::getRowsFromQuery($selectConcentradoIngresos2);

$registrosMonXConsumo = utils\IConnection::getRowsFromQuery($SqlDescuentosAplicadosTarjetaMonederoXConsumos);
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports.php'; ?> 
        <script type="text/javascript" src="js/export_.js"></script>
        <title><?= $Gcia ?></title>
        <style>
            #Concentrado{
                width: 100%;
                border-collapse: separate;
                font-family: Arial, Helvetica, sans-serif;
                font-size: 12px;
                color: #55514e;
            }
            #Concentrado > thead > tr > td{
                height: 25px;
                background-color: white;
                border-bottom: solid 2px gray;
                font-weight: bold;
                text-align: center;
            }
            #Concentrado > thead > tr > td > a{
                text-decoration: none;
                color: #55514e;
                font-weight: bold;
            }
            #Concentrado > thead > tr.titulo > td{
                background-color: var(--GrisClaro);
                border-bottom: solid 2px white;
            }
            #Concentrado > tbody > tr > td{
                padding-left: 5px;
                padding-right: 5px;
                text-align: left;
            }
            #Concentrado > tbody > tr > td.overflow{
                max-width: 200px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            #Concentrado > tbody > tr > td.overflow:hover{
                overflow: visible;
                white-space: normal;
            }
            #Concentrado > tbody > tr:nth-child(odd) {
                background-color: var(--GrisClaro);
            }

            #Concentrado > tbody > tr:nth-child(even) {
                background-color: white;
            }

            #Concentrado > tbody > tr:nth-child(odd):hover {
                background-color: var(--VerdeHover);
            }

            #Concentrado > tbody > tr:nth-child(even):hover {
                background-color: var(--VerdeHover);
            }
            #Concentrado > tbody > tr.titulos > td{
                height: 25px;
                background-color: white;
                border-bottom: solid 2px gray;
                font-weight: bold;
                text-align: center;
            }
            #Concentrado > tbody > tr.subtotal > td{
                height: 25px;
                background-color: white;
                border-top: solid 2px gray;
                font-weight: bold;
                text-align: right;
                padding-bottom: 10px;
            }
            #Concentrado > tbody > tr.titulo > td{
                height: 25px;
                background-color: var(--GrisClaro);
                font-weight: bold;
                text-align: right;
                text-align: center;
            }
            #Concentrado > tbody > tr.subtitulo > td{
                height: 25px;
                background-color: white;
                font-weight: bold;
                text-align: right;
                text-align: center;
            }
            #Concentrado > tbody > tr > td.numero,.moneda{
                text-align: right;
            }
            #Concentrado > tbody > tr > td.remarcar{
                background-color: #F7FF7C;
            }
            #Concentrado > tbody > tr > td.moneda:before{
                content: "$ ";
            }
            #Concentrado > tfoot > tr > td{
                height: 25px;
                background-color: white;
                /*    border-top: solid 2px gray;*/
                font-weight: bold;
                text-align: right;
                padding-left: 5px;
                padding-right: 5px;
            }
            #Concentrado > tfoot > tr:first-child > td{
                border-top: solid 2px gray;
                padding-bottom: 10px;
            }
            #Concentrado > tfoot > tr > td.moneda:before{
                content: "$ ";
            }
        </style>
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
                $("#Detalle").val("<?= $Detalle ?>");
                $("#Desglose").val("<?= $Desglose ?>");
                //$("#Turno").val("<?= $Turno ?>");
                if ($("#TotalVentasN").val() == 0) {
                    $("#ConcentradoConsignaciones").hide();
                }
            });
            function ExportToExcel(type, fn, dl) {
                var elt = document.getElementById('tbl_exporttable_to_xls');
                var wb = XLSX.utils.table_to_book(elt, {sheet: "sheet1"});
                return dl ?
                        XLSX.write(wb, {bookType: type, bookSST: true, type: 'base64'}) :
                        XLSX.writeFile(wb, fn || ('ReporteGerencia.' + (type || 'xlsx')));
            }
            ;
        </script>
    </head>

    <body>
        <div id="container">
            <div id="tbl_exporttable_to_xls">
                <?php nuevoEncabezado($Titulo); ?>
                <div id="Reportes" style="min-height: 200px;"> 
                    <table style="width: 100%;" summary="Concentrado diario">
                        <tr>
                            <th>
                                <h3><?= $Titulo ?></h3>
                            </th>
                        </tr>
                    </table>
                    <table summary="Detalle del concentrado diario">
                        <tr>
                            <th style="width:12%;">Producto <em><br><sub><strong>Ventas</strong></sub></em></th>
                            <th style="width:7%;">Ventas</th>
                            <th style="width:9%;">Litros</th>
                            <th style="width:9%;">Precio</th>
                            <th style="width:9%;">Importe</th>
                            <th style="width:9%;">Iva</th>
                            <th style="width:9%;">Ieps</th>
                            <th style="width:12%;">Total</th>
                            <th style="width:12%;">Descuento</th>
                            <th style="width:12%;">Total D.</th>
                        </tr>
                        <?php
                        foreach ($varD as $rst) {
                            ?>
                            <tr>
                                <td><?= $rst["producto"] ?></td>
                                <td class="numero"><?= $rst["ventas"] ?></td>
                                <td class="numero"><?= number_format($rst["volumen"], 3) ?></td>
                                <td class="numero"><?= number_format($rst["precio"], 2) ?></td>
                                <td class="numero"><?= number_format($rst["pesos"] - $rst["ieps"] - (($rst["pesos"] - $rst["ieps"]) / (1 + $rst["iva"])) * $rst["iva"], 2) ?></td>
                                <td class="numero"><?= number_format((($rst["pesos"] - $rst["ieps"]) / (1 + $rst["iva"])) * $rst["iva"], 2) ?></td>
                                <td class="numero"><?= number_format($rst["ieps"], 2) ?></td>
                                <td class="numero"><?= number_format($rst["pesos"], 2) ?></td>
                                <td class="numero"><?= number_format($rst["descuento"], 2) ?></td>
                                <td class="numero"><?= number_format($rst["pesos"] - $rst["descuento"], 2) ?></td>
                            </tr>
                            <?php
                            $VentasTotal += $rst["ventas"];
                            $VolumenTotal += $rst["volumen"];
                            $ImporteTotalCombustible += $rst["pesos"];
                            $ImporteTotal += $rst["pesos"] - $rst["ieps"] - (($rst["pesos"] - $rst["ieps"]) / (1 + $rst["iva"])) * $rst["iva"];
                            $IepsCombustible += $rst["ieps"];
                            $totalIva += (($rst["pesos"] - $rst["ieps"]) / (1 + $rst["iva"])) * $rst["iva"];
                            $totalDescuento += $rst["descuento"];
                        }
                        $ImporteTotalCombustibleCnD = $ImporteTotalCombustible - $totalDescuento;
                        ?>
                        <tr class="subtotal">
                            <td>Sub total</td>
                            <td><?= $VentasTotal ?></td>
                            <td><?= number_format($VolumenTotal, 2) ?></td>
                            <td></td>
                            <td><?= number_format($ImporteTotal, 2) ?></td>
                            <td><?= number_format($totalIva, 2) ?></td>
                            <td><?= number_format($IepsCombustible, 2) ?></td>
                            <td><?= number_format($ImporteTotalCombustible, 2) ?></td>
                            <td><?= number_format($totalDescuento, 2) ?></td>
                            <td><?= number_format($ImporteTotalCombustibleCnD, 2) ?></td>
                        </tr>
                        <?php $ImporteTotalCombustible = $ImporteTotalCombustible; ?>
                    </table>
                    <table id="Concentrado"  summary="Detalle del concentrado diario">
                        <thead>
                            <tr>
                                <th colspan="8" id="EspacioBlanco" style="height: 12px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th style="width:12%;" scope="col">Producto <em><sub><br><strong>Consignaciones</strong></sub></em></th>
                                <th style="width:7%;" scope="col">Ventas</th>
                                <th style="width:34%;" scope="col">Litros</th>
                                <th style="width:10%;" scope="col">Precio</th>
                                <th style="width:10%;" scope="col">Importe</th>
                                <th style="width:10%;" scope="col">Iva</th>
                                <th style="width:10%;" scope="col">Ieps</th>
                                <th style="width:15%;" scope="col">Total</th>
                            </tr>
                            <?php
                            foreach ($varN as $rst) {
                                ?>
                                <tr>
                                    <td><?= $rst["producto"] ?></td>
                                    <td class="numero"><?= $rst["ventas"] ?></td>
                                    <td class="numero"><?= number_format($rst["volumen"], 3) ?></td>
                                    <td class="numero"><?= number_format(0, 2) ?></td>
                                    <td class="numero"><?= number_format(0, 2) ?></td>
                                    <td class="numero"><?= number_format(0, 2) ?></td>
                                    <td class="numero"><?= number_format(0, 2) ?></td>
                                    <td class="numero"><?= number_format(0, 2) ?></td>
                                </tr>
                                <?php
                                $VentasTotalN += $rst["ventas"];
                                $VolumenTotalN += $rst["volumen"];
                            }
                            ?>
                            <tr class="subtotal">
                                <td>Sub total</td>
                                <td><?= $VentasTotalN ?></td>
                                <td><?= number_format($VolumenTotalN, 2) ?></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        <input type="hidden" name="TotalVentasN" id="TotalVentasN" value="<?= $VentasTotalN ?>">
                        <tr>
                            <th style="width:12%;" scope="col">Aceites</i></th>
                            <th style="width:7%;" scope="col">Ventas</th>
                            <th style="width:34%;" scope="col"></th>
                            <th style="width:10%;" scope="col"></th>
                            <th style="width:10%;" scope="col">Importe</th>
                            <th style="width:10%;" scope="col">Iva</th>
                            <th style="width:10%;" scope="col"></th>
                            <th style="width:15%;" scope="col">Total</th>
                        </tr>
                        <tr>
                            <td>Aceites</td>                    
                            <td class="numero"><?= number_format($registrosAceGRAL["cantidad"]) ?></td>
                            <td></td>
                            <td></td>
                            <td class="numero"><?= number_format($registrosAceGRAL["importe"] / 1.16, 2) ?></td>
                            <td class="numero"><?= number_format(($registrosAceGRAL["importe"] / 1.16) * 0.16, 2) ?></td>
                            <td></td>
                            <td class="numero"><?= number_format($registrosAceGRAL["importe"], 2) ?></td>
                        </tr>
                        <tr class="subtotal">
                            <td>Gran total</td>
                            <td></td>
                            <td><?= number_format($VolumenTotal + $VolumenTotalN, 2) ?></td>
                            <td></td>
                            <td class="numero"><?= number_format($ImporteTotal + ($registrosAceGRAL["importe"] / 1.16), 2) ?></td>
                            <td class="numero"><?= number_format($totalIva + ($registrosAceGRAL["importe"] / 1.16) * 0.16, 2) ?></td>
                            <td class="numero"><?= number_format($IepsCombustible, 2) ?></td>
                            <td class="numero"><?= number_format($ImporteTotalCombustible - $totalDescuento + $registrosAceGRAL["importe"], 2) ?></td>
                        </tr>
                        <tr class="subtitulo">
                            <td colspan="7">***Jarreos (informativo)***</td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                        </tr>

                        <?php
                        $VolumenJarreos = $PesosJarreos = $IepsJarreos = 0;
                        foreach ($registrosGRAL_Jarreos as $rg) {
                            ?>
                            <tr>
                                <td><?= $rg["producto"] ?></td>
                                <td class="numero"><?= $rg["ventas"] ?></td>
                                <td class="numero"><?= number_format($rg["volumen"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["pesos"] / $rg["volumen"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["pesos"] - $rg["ieps"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["ieps"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["pesos"], 2) ?></td>
                                <td></td>
                            </tr>

                            <?php
                            $VolumenJarreos += $rg["volumen"];
                            $PesosJarreos += $rg["pesos"];
                            $IepsJarreos += $rg["ieps"];
                        }
                        ?>
                        <tr class="subtotal">
                            <td>Total</td>
                            <td></td>
                            <td><?= number_format($VolumenJarreos, 2) ?> </td>
                            <td></td>
                            <td><?= number_format($PesosJarreos - $IepsJarreos, 2) ?></td>
                            <td><?= number_format($IepsJarreos, 2) ?> </td>
                            <td><?= number_format($PesosJarreos, 2) ?> </td>
                            <td></td>
                        </tr>
                        <tr class="titulo">
                            <td colspan="8">***Venta de combustible por tipo de pago***</td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                        </tr>
                        <tr>
                            <td>Tipo</td>
                            <td>Cuenta</td>
                            <td>Nombre</td>
                            <td>No.Ticket</td>
                            <td>Fecha</td>
                            <td>Litros</td>
                            <td>Importe</td>
                            <td>SubTotal</td>
                        </tr>

                        <?php
                        $nGtotal = 0;
                        $cPago = "";
                        $nSubImp = $nSubLit = $nGtotal = 0;
                        $cliente = 0;
                        $nSubImpCli = $nSubLitCli = 0;

                        foreach ($registrosCLI as $rg) {
                            if ($cliente > 0 && $Detallado === "Si") {
                                if ($cliente !== $rg["cliente"]) {
                                    ?>
                                    <tr class="subtotal">
                                        <td></td>
                                        <td></td>
                                        <td>Subtotal</td>
                                        <td></td>
                                        <td></td>
                                        <td><?= number_format($nSubLitCli, 2) ?></td>
                                        <td><?= number_format($nSubImpCli, 2) ?></td>
                                        <td></td>
                                    </tr>
                                    <?php
                                    $nSubImpCli = $nSubLitCli = 0;
                                } else {
                                    $upTitlesCli = false;
                                }
                            }

                            if ($rg["tipodepago"] <> $cPago) {
                                if (!empty($cPago)) {
                                    ?>
                                    <tr class="subtotal">
                                        <td></td>
                                        <td></td>
                                        <td>Total <?= $cPago ?></td>
                                        <td></td>
                                        <td></td>
                                        <td><?= number_format($nSubLit, 2) ?></td>
                                        <td><?= number_format($nSubImp, 2) ?></td>
                                        <td><?= number_format($nGtotal, 2) ?></td>
                                    </tr>
                                    <?php
                                }
                                $nSubImp = $nSubLit = 0;
                                $cPago = $rg["tipodepago"];
                            }
                            ?>

                            <tr>
                                <td><?= $rg["tipodepago"] ?></td>
                                <td><?= $rg["cliente"] ?></td>
                                <td><?= ucwords(strtolower($rg["nombre"])) ?></td>
                                <td class="numero"><?= $Detallado === "Si" ? $rg["id"] : "" ?></td>
                                <td><?= $rg["inicio_venta"] ?></td>
                                <td class="numero"><?= number_format($rg["cantidad"], 2) ?></td>
                                <td class="numero"><?= $rg["tipodepago"] === "Consignacion" ? number_format(0, 2) : number_format($rg["importe"], 2) ?></td>
                                <td></td>
                            </tr>

                            <?php
                            $cliente = $rg["cliente"];
                            if ($rg["tipodepago"] !== "Consignacion") {
                                $nSubImpCli += $rg["importe"];
                                $nSubImp += $rg["importe"];
                                $nGtotal += $rg["importe"];
                            }
                            $nSubLitCli += $rg["cantidad"];
                            $nSubLit += $rg["cantidad"];
                        }
                        ?>

                        <?php if ($Detallado === "Si") { ?>
                            <tr class="subtotal">
                                <td></td>
                                <td></td>
                                <td>Subtotal</td>
                                <td></td>                                
                                <td></td>
                                <td><?= number_format($nSubLitCli, 2) ?></td>
                                <td><?= number_format($nSubImpCli, 2) ?></td>
                                <td></td>
                            </tr>
                        <?php } ?>

                        <tr class="subtotal">
                            <td></td>
                            <td></td>
                            <td>Total <?= $cPago ?></td>
                            <td></td>
                            <td></td>
                            <td><?= number_format($nSubLit, 2) ?></td>
                            <td><?= number_format($nSubImp, 2) ?></td>
                            <td><?= number_format($nGtotal, 2) ?></td>
                        </tr>

                        <?php
                        $nSubImp = $nSubLit = 0;
                        foreach ($registrosTAR as $rg) {
                            ?>
                            <tr>
                                <td>Tarjeta bancaria</td>
                                <td><?= $rg["id"] ?></td>
                                <td><?= ucwords(strtolower($rg["cpto"])) ?></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td><?= number_format($rg["importe"], 2) ?></td>
                                <td></td>
                            </tr>
                            <?php
                            $nSubImp += $rg["importe"];
                            $nSubLit += $rg["cantidad"];

                            $nGtotal += $rg["importe"];
                        }
                        ?>

                        <?php if (count($registrosTAR) > 0) { ?>

                            <tr class="subtotal">
                                <td></td>
                                <td></td>
                                <td>Subtotal Tarjeta bancaria</td>
                                <td></td>
                                <td></td>
                                <td><?= number_format($nSubLit, 2) ?></td>
                                <td><?= number_format($nSubImp, 2) ?></td>
                                <td><?= number_format($nGtotal, 2) ?></td>
                            </tr>
                        <?php } ?>

                        <tr class="subtitulo">
                            <td colspan="7">***Gastos***</td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                        </tr>

                        <tr class="titulos">
                            <td>Corte</td>
                            <td></td>
                            <td>Concepto</td>
                            <td></td>
                            <td></td>
                            <td>Importe</td>
                            <td></td>
                        </tr>

                        <?php
                        $gastosT = 0;
                        foreach ($registrosGAS as $rg) {
                            ?>
                            <tr>                        
                                <td><?= $rg["corte"] ?></td>
                                <td></td>
                                <td><?= $rg["concepto"] ?></td>
                                <td></td>
                                <td></td>
                                <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                                <td></td>
                            </tr>
                            <?php
                            $gastosT += $rg["importe"];
                        }
                        ?>

                        <tr class="subtotal">
                            <td></td>
                            <td></td>
                            <td>Total</td>
                            <td></td>
                            <td></td>
                            <td><?= number_format($gastosT, 2) ?></td>
                            <td></td>
                        </tr>        

                        <tr class="subtitulo">
                            <td colspan="7">***Venta de aceites y aditivos por tipo de pago***</td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                        </tr>        

                        <?php
                        foreach ($registrosACE as $rg) {
                            ?>
                            <tr>
                                <td><?= $rg["tipodepago"] ?></td>
                                <td><?= $rg["cliente"] ?></td>
                                <td><?= $Detallado === "Si" || $Detalle === "Si" ? $rg["descripcion"] : ucwords(strtolower($rg["nombre"])) ?></td>
                                <td><?= $rg["id"] ?></td>
                                <td>| <?= $rg["fecha"] ?> |</td>
                                <td class="numero"><?= number_format($rg["cantidad"], 0) ?></td>
                                <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                                <td></td>
                            </tr>
                            <?php
                            $nAceLts += $rg["cantidad"];
                            $nAceImp += $rg["importe"];
                            if ($rg["tipodepago"] === TiposCliente::CONTADO || $rg["tipodepago"] === TiposCliente::PUNTOS) {
                                $nAceLtsCnt += $rg["cantidad"];
                                $nAceImpCnt += $rg["importe"];
                            }
                        }
                        ?>

                        <tr class="subtotal">
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td colspan="5">Subtotal Aceites</td>
                            <td><?= number_format($nAceLts, 0) ?></td>
                            <td><?= number_format($nAceImp, 2) ?></td>
                            <td><?= number_format($nGtotal + $nAceImp - $nAceImpCnt, 2) ?></td>
                        </tr>

                        <tr>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td colspan="4">Efectivo Aceites</td>
                            <td><?= number_format($nAceLtsCnt, 0) ?></td>
                            <td><?= number_format($nAceImpCnt, 2) ?></td>
                            <td></td>
                        </tr>

                        <tr>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td colspan="4">Efectivo Combustible</td>
                            <td></td>
                            <td><?= number_format($ImporteTotalCombustible - $nGtotal - $totalDescuento, 2) ?></td>
                            <td></td>
                        </tr>

                        <tr>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td colspan="4">Total Efectivo (*sin gastos)</td>
                            <td></td>
                            <td><?= number_format($ImporteTotalCombustible - $nGtotal - $gastosT + $nAceImpCnt - $totalDescuento, 2) ?></td>
                            <td></td>
                        </tr>

                        <tr>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td colspan="4">Gran Total</td>
                            <td></td>
                            <td></td>
                            <td><?= number_format($ImporteTotalCombustible + $registrosAceGRAL["importe"] - $totalDescuento, 2) ?></td>
                        </tr>

                        <tr class="titulo">
                            <td colspan="7">Depositos bancarios</td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                            <td style="display:none"></td>
                        </tr>
                        <tr>
                            <td>Banco</td>
                            <td>Cuenta</td>
                            <td>Descripcion</td>
                            <td colspan="2">No.plomo/No.Ficha</td>
                            <td style="display:none"></td>
                            <td colspan="2">Importe</td>
                            <td style="display:none"></td>
                        </tr>
                        <?php foreach ($registrosING1 as $rg) { ?>
                            <tr>
                                <td><?= ucwords(strtolower($rg["banco"])) ?></td>
                                <td><?= $rg["cuenta"] ?></td>
                                <td><?= ucwords(strtolower($rg["concepto"])) ?></td>
                                <td colspan="2"><?= strtoupper($rg["des"]) ?></td>
                                <td style="display:none"></td>
                                <td class="numero" colspan="2"><?= number_format($rg["importe"], 2) ?></td>
                                <td style="display:none"></td>
                            </tr>
                            <?php
                            $nImpA += $rg["importe"];
                        }
                        ?>
                        <tr class="subtotal">
                            <td></td>
                            <td></td>
                            <td></td>
                            <td colspan="2">Subtotal en fichas</td>
                            <td style="display:none"></td>
                            <td colspan="2"><?= number_format($nImpA, 2) ?></td>
                            <td style="display:none"></td>
                        </tr>

                        <?php foreach ($registrosING2 as $rg) { ?>
                            <tr>
                                <td><?= ucwords(strtolower($rg["banco"])) ?></td>
                                <td><?= $rg["cuenta"] ?></td>
                                <td><?= ucwords(strtolower($rg["concepto"])) ?></td>
                                <td colspan="2"><?= ucwords(strtolower($rg["des"])) ?></td>
                                <td style="display:none"></td>
                                <td class="numero" colspan="2"><?= number_format($rg["importe"], 2) ?></td>
                                <td style="display:none"></td>
                            </tr>
                            <?php
                            $nImpA += $rg["importe"];
                        }
                        ?>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td colspan="2">Gran total</td>
                            <td style="display:none"></td>
                            <td colspan="2"><?= number_format($nImpA, 2) ?></td>
                            <td style="display:none"></td>
                        </tr>
                        </tbody>
                    </table>
                    <table title="Mostramos totales consumidos. Numero de ventas, volumen, importe y descuento total">
                        <caption>Mostramos totales consumidos. Numero de ventas, volumen, importe y descuento total</caption>
                        <thead>
                            <tr>
                                <th colspan="8" id="Titulo_Concentrado_Descuentos">*** Concentrado de descuentos ***</th>
                            </tr>
                            <tr>
                                <th id="Nombre_Cliente">Nombre</th>
                                <th id="Cuenta_Unidad">Cuenta</th>
                                <th id="Clave_Unidad">Unidad</th>
                                <th id="Cantidad_Total">Ventas</th>
                                <th id="Volumen_Total">Volumen</th>
                                <th id="Importe_Total">Importe</th>
                                <th id="Descuento_Total">Descuento</th>
                                <th id="Fecha_Consumos">Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($registrosMonXConsumo as $rmc) {
                                ?>
                                <tr>
                                    <td><?= $rmc["nombre"] ?></td>
                                    <td><?= $rmc["id"] ?></td>
                                    <td><?= $rmc["codigo"] ?></td>
                                    <td style="text-align: right;padding-right: 5px;"><?= number_format($rmc["cnt"], 0) ?></td>
                                    <td style="text-align: right;padding-right: 5px;"><?= number_format($rmc["volumen"], 2) ?></td>
                                    <td style="text-align: right;padding-right: 5px;"><?= number_format($rmc["importe"], 2) ?></td>
                                    <td style="text-align: right;padding-right: 5px;"><?= number_format($rmc["descuento"], 2) ?></td>
                                    <td><?= $rmc["fecha"] ?></td>
                                </tr>
                                <?php
                                $VentasT += $rmc["cnt"];
                                $VolumenT += $rmc["volumen"];
                                $ImporteT += $rmc["importe"];
                                $DescuentoT += $rmc["descuento"];
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td style="text-align: right;" colspan="3">Total</td>
                                <td style="text-align: right;padding-right: 5px;"><?= number_format($VentasT, 0) ?></td>
                                <td style="text-align: right;padding-right: 5px;"><?= number_format($VolumenT, 2) ?></td>
                                <td style="text-align: right;padding-right: 5px;"><?= number_format($ImporteT, 2) ?></td>
                                <td style="text-align: right;padding-right: 5px;"><?= number_format($DescuentoT, 2) ?></td>
                                <td></td>
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
                            <td style="width: 30%;">
                                <table aria-hidden="true">
                                    <tr>
                                        <td>F.inicial:</td>
                                        <td><input type="text" id="FechaI" name="FechaI"></td>
                                        <td class="calendario"><em id="cFechaI" class="fa fa-2x fa-calendar" aria-hidden="true"></em></td>
                                    </tr>
                                    <tr>
                                        <td>F.final:</td>
                                        <td><input type="text" id="FechaF" name="FechaF"></td>
                                        <td class="calendario"><em id="cFechaF" class="fa fa-2x fa-calendar" aria-hidden="true"></em></td>
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
                                        <td>&nbsp;Detalle Aceites:</td>
                                        <td style="text-align: left;padding-left: 5px">
                                            <select id="Detalle" name="Detalle">
                                                <option value="Si">Si</option>
                                                <option value="No">No</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>&nbsp;Desglose:</td>
                                        <td style="text-align: left;padding-left: 5px">
                                            <select id="Desglose" name="Desglose">
                                                <?php
                                                $TDesglose = utils\IConnection::execSql("SELECT valor FROM variables_corporativo WHERE llave='Rep_gvc_visual'");
                                                if ($TDesglose["valor"] == 0) {
                                                    ?>
                                                    <option value="Cortes">Cortes</option>
                                                    <?php
                                                } elseif ($TDesglose["valor"] == 1) {
                                                    ?>
                                                    <option value="Dia">Dia</option>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <option value="Cortes">Cortes</option>
                                                    <option value="Dia">Dia</option>
                                                    <?php
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <span><input type="submit" name="Boton" value="Enviar"></span>
                                <?php
                                if ($usuarioSesion->getTeam() !== "Operador") {
                                    ?>
                                    <span><button onclick="print()" title="Imprimir reporte"><em class="icon fa fa-lg fa-print" aria-hidden="true"></em></button></span>
                                    <span><button onclick="ExportToExcel('xlsx')"><em class="icon fa fa-lg fa-bold fa-file-excel-o" aria-hidden="true"></em></button></span>
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
    </body>
</html>
