<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesClientesService.php";

$Titulo = "Consumos del $FechaI al $FechaF ";

$registros = utils\IConnection::getRowsFromQuery($selectConsumos);

$registrosT = utils\IConnection::getRowsFromQuery($selectConsumosTotalesByProducto);

$registrosAd = utils\IConnection::getRowsFromQuery($selectConsumosAditivos);

$registrosTC = utils\IConnection::getRowsFromQuery($selectConsumosTarjetasConcetrado);

$Id = 21; /* Número de en el orden de la tabla submenus */
$data = array("Nombre" => $Titulo, "Reporte" => $Id,
    "FechaI" => $FechaI, "FechaF" => $FechaF,
    "HoraI" => $HoraI, "HoraF" => $HoraF,
    "Desglose" => $Desglose, "Producto" => $Producto, "Turno" => $Turno,
    "TipoCliente" => $TipoCliente, "IslaPosicion" => $IslaPosicion,
    "Detallado" => "No", "Textos" => "Subtotal", "Filtro" => "1");
$nLts = 0;
$rImp = 0;
$nImp = 0;
$nLtsT = 0;
$nImpTR = 0;
$rImpAce = 0;
$nImpT = 0;
$nImpAce = 0;
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <script type="text/javascript" src="js/export_.js"></script>
        <title><?= $Gcia ?></title>
        <script>

            $(document).ready(function () {
                var cliente = "<?= html_entity_decode($SCliente) ?>";
                $("#autocomplete").val(cliente.replace("Array", ""))
                        .attr("placeholder", "* Favor de buscar al cliente *")
                        .click(function () {
                            this.select();
                        }).focus()
                        .activeComboBox(
                                $("[name=\"form1\"]"),
                                "SELECT id as data, CONCAT(id, ' | ', tipodepago, ' | ', nombre) value FROM cli " +
                                "WHERE cli.id >= 10 AND cli.tipodepago NOT REGEXP 'Contado'",
                                "nombre");
                $("#FechaI").val("<?= $FechaI ?>").attr("size", "10");
                $("#FechaF").val("<?= $FechaF ?>").attr("size", "10");
                $("#HoraI").val("<?= $HoraI ?>");
                $("#HoraF").val("<?= $HoraF ?>");
                $("#Turno").val("<?= $Turno ?>");
                $("#cFechaI").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaI")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#cFechaF").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaF")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#TipoCliente").val("<?= $TipoCliente ?>");
                $("#Desglose").val("<?= $Desglose ?>");
                $("#Producto").val("<?= $Producto ?>");
                $("#IslaPosicion").val("<?= $IslaPosicion ?>");

                if ($("#Desglose").val() === "Dia") {
                    $("#horasParam").show();
                    $("#turnosParam").hide();
                } else {
                    $("#horasParam").hide();
                    $("#turnosParam").show();
                }
                $("#autocomplete").focus();

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
        <?php nuevoEncabezado($Titulo); ?>

        <div id="tbl_exporttable_to_xls">
            <div id="Reportes" style="min-height: 200px;"> 
                <table aria-hidden="true">
                    <thead>
                        <tr>
                            <td></td>
                            <td>Isla/Disp.</td>
                            <td>Ticket</td>
                            <td>Corte</td>
                            <td>Codigo</td>
                            <td>Operador</td>
                            <td>Despachador</td>
                            <td style="width: 130px;">Fecha</td>
                            <td>Hora</td>
                            <td>No.placas</td>
                            <td>No.Eco.</td>
                            <td>Km.</td>
                            <td>Descripcion</td>
                            <td>Producto</td>
                            <td>Fac</td>
                            <td>Litros</td>
                            <td>Importe</td>
                            <td>IVA</td>
                            <td>IEPS</td>
                            <td>Total</td>
                            <td>Pago Real</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $nIva = $nIeps = $Unt = $rImp = $nImp = $nLts = $nUntT = $nIvaT = $nIepsT = $TgTT = $TgImp = $TgIva = $TgImpr = $TgImpn = $ValU = $Iva = $Ieps = $Pr = $Impu = $Ivau = 0;
                        $nRng = 0;
                        $cont = 1;
                        $contCliente = 1;
                        $contCodigo = 1;
                        $cCli = "";
                        $cCod = "";
                        $uptitles = true;
                        $nImpR = $nImpC = $nLtsC = 0;
                        $TotalesGenerales = Array();
                        foreach ($registros as $rg) {
                            //error_log("cCod: $cCod && $cCod !== $rg["codigo"] && ($cCli == $rg["cliente"] ||  $contCodigo > 1)");
                            $style = "";
                            if ($cCod !== trim($rg["codigo"]) && ($cCli === $rg["cliente"] || $contCodigo > 1)) {
                                ?>
                                <tr class="subtotal">
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>                                        
                                    <td></td>                 
                                    <td></td>                                              
                                    <td></td>                 
                                    <td></td>
                                    <td>Subtotal <?= $contCodigo ?></td>
                                    <td></td>  
                                    <td><?= number_format($nLtsC, 2) ?></td>
                                    <td><?= number_format($nImpR, 2) ?></td>
                                    <td><?= number_format($nImpC, 2) ?></td>
                                </tr>
                                <?php
                                $uptitles = false;
                                $nImpR = $nImpC = $nLtsC = 0;
                                $nRng = 0;
                                $contCodigo++;
                            }
                            $cCod = trim($rg["codigo"]);

                            if ($cCli !== $rg["cliente"]) {
                                if (!empty($cCli)) {
                                    ?>
                                    <tr class="subtotal">
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>                                        
                                        <td></td>
                                        <td>Total <?= $contCliente ?></td>
                                        <td></td>                                
                                        <td><?= number_format($nLts, 2) ?></td>                               
                                        <td><?= number_format($Unt, 2) ?></td>                               
                                        <td><?= number_format($nIva, 2) ?></td>
                                        <td><?= number_format($nIeps, 2) ?></td>
                                        <td><?= number_format($rImp, 2) ?></td>
                                        <td><?= number_format($nImp, 2) ?></td>
                                    </tr>
                                    <?php
                                    $TotalesGenerales[$cCli]["Nombre"] = $NomGuarda;
                                    $TotalesGenerales[$cCli]["Litros"] = $nLts;
                                    $TotalesGenerales[$cCli]["Importe"] = $Unt;
                                    $TotalesGenerales[$cCli]["Iva"] = $nIva;
                                    $TotalesGenerales[$cCli]["Ieps"] = $nIeps;
                                    $TotalesGenerales[$cCli]["Impr"] = $rImp;
                                    $TotalesGenerales[$cCli]["Impn"] = $nImp;

                                    $nIva = $nIeps = $Unt = $rImp = $nImp = $nLts = 0;
                                    $nImpR = $nImpC = $nLtsC = 0;
                                    $nRng = 0;

                                    $uptitles = true;
                                    $contCodigo = 1;
                                    $contCliente++;
                                }
                                ?>
                                <tr class="subtitulo"><td colspan="100%" class="tdCliente">***<?= $rg["cliente"] . " " . $rg["nombre"] ?> ***</td></tr>
                                <?php
                                $NomGuarda = $rg["nombre"];
                            }
                            $cCli = $rg["cliente"];
                            if (abs($rg["pagoreal"] - $rg["importe"]) > 0.5) {
                                $style = "style='background-color: #F7FF7C' title='El importe fue modificado'";
                            }
                            $Fcha = explode(" ", $rg["fecha"]);
                            ?>
                            <tr <?= $style ?>>
                                <td><?= $cont ?></td>
                                <td><?= $rg["isla_pos"] ?></td>
                                <td><?= $rg["ticket"] ?></td>
                                <td><?= $rg["corte"] ?></td>
                                <td><?= $rg["impreso"] ?></td>
                                <td><?= $rg["descUnidad"] ?></td>
                                <td><?= $rg["alias"] ?></td>
                                <td>|<?= $Fcha[0] ?>|</td>
                                <td>|<?= $Fcha[1] ?>|</td>
                                <td><?= $rg["placas"] ?></td>
                                <td><?= $rg["numeco"] ?></td>
                                <td><?= $rg["kilometraje"] ?></td>

                                <?php if ($rg["tipo"] === "0") { ?>
                                    <td class="overflow"><?= ucwords(strtolower($rg["descripcion"])) ?></td>
                                    <td><?= $rg["producto"] ?></td>
                                <?php } else { ?>
                                    <td class="overflow" colspan="2"><?= ucwords(strtolower($rg["descripcion"])) ?></td>
                                <?php } ?>

                                <?php if ($rg["uuid"] !== "-----") { ?>
                                    <td align="center" style="font-weight: bold;"><i class="fa fa-check-square-o" aria-hidden="true"></i></td>
                                <?php } else { ?>
                                    <td align="center"><i class="fa fa-square-o" aria-hidden="true"></i></td>
                                <?php } ?>

                                <td class="numero"><?= number_format($rg["volumen"], 2) ?></td>                               
                                <td class="numero"><?= number_format((($rg["importe"] - ($rg["ieps"] * $rg["volumen"]) ) / (1 + $rg["iva"])), 2) ?></td>                               
                                <td class="numero"><?= number_format((($rg["importe"] - ($rg["ieps"] * $rg["volumen"]) ) / (1 + $rg["iva"])) * $rg["iva"], 2) ?></td>                               
                                <td class="numero"><?= number_format(($rg["ieps"] * $rg["volumen"]), 2) ?></td>
                                <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["pagoreal"], 2) ?></td>
                                <?php ?>
                            </tr>
                            <?php
                            $nIva += (($rg["importe"] - ($rg["ieps"] * $rg["volumen"]) ) / (1 + $rg["iva"])) * $rg["iva"];
                            $nIeps += ($rg["ieps"] * $rg["volumen"]);
                            $Unt += (($rg["importe"] - ($rg["ieps"] * $rg["volumen"]) ) / (1 + $rg["iva"]));
                            $rImp += $rg["importe"];
                            $nImp += $rg["pagoreal"];
                            $nLts += $rg["volumen"];
                            $nImpR += $rg["importe"];
                            $nImpC += $rg["pagoreal"];
                            $nLtsC += $rg["volumen"];
                            $nImpTR += $rg["importe"];
                            $nImpT += $rg["pagoreal"];
                            $nLtsT += $rg["volumen"];
                            $nUntT += (($rg["importe"] - ($rg["ieps"] * $rg["volumen"]) ) / (1 + $rg["iva"]));
                            $nIvaT += (($rg["importe"] - ($rg["ieps"] * $rg["volumen"]) ) / (1 + $rg["iva"])) * $rg["iva"];
                            $nIepsT += ($rg["ieps"] * $rg["volumen"]);
                            $NomGuarda = $rg["nombre"];
                            $nRng++;
                            $cont++;
                        }
                        ?>

                        <?php if (!$uptitles) { ?>
                            <tr class="subtotal">
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>                                        
                                <td></td>
                                <td>Subtotal <?= $contCodigo ?></td>
                                <td></td>
                                <td><?= number_format($nLtsC, 2) ?></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td><?= number_format($nImpR, 2) ?></td>
                                <td><?= number_format($nImpC, 2) ?></td>
                            </tr>
                        <?php } ?>

                        <tr class="subtotal">
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td> 
                            <?php
                            $TotalesGenerales[$contCliente]["Nombre"] = $NomGuarda;
                            $TotalesGenerales[$contCliente]["Litros"] = $nLts;
                            $TotalesGenerales[$contCliente]["Importe"] = $Unt;
                            $TotalesGenerales[$contCliente]["Iva"] = $nIva;
                            $TotalesGenerales[$contCliente]["Ieps"] = $nIeps;
                            $TotalesGenerales[$contCliente]["Impr"] = $rImp;
                            $TotalesGenerales[$contCliente]["Impn"] = $nImp;
                            ?>
                            <td>Total <?= $contCliente == 1 ? "" : $contCliente ?></td>
                            <td></td>
                            <td></td>
                            <td><?= number_format($nLts, 2) ?></td>
                            <td><?= number_format($Unt, 2) ?></td>                               
                            <td><?= number_format($nIva, 2) ?></td>
                            <td><?= number_format($nIeps, 2) ?></td>
                            <td><?= number_format($rImp, 2) ?></td>
                            <td><?= number_format($nImp, 2) ?></td>
                        </tr>


                    </tbody>
                    <tfoot>

                        <tr style="border-top: 0">
                            <td colspan="9"></td>
                            <td>Gran Total</td>                                     
                            <td></td>
                            <td><?= number_format($nLtsT, 2) ?></td>        
                            <td><?= number_format($nUntT, 2) ?></td>        
                            <td><?= number_format($nIvaT, 2) ?></td>        
                            <td><?= number_format($nIepsT, 2) ?></td>
                            <td><?= number_format($nImpTR + $rImpAce, 2) ?></td>
                            <td><?= number_format($nImpT + $nImpAce, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
                <table style="margin-top: 15px;margin-bottom: 25px;">
                    <caption>Concentrado por cliente</caption>
                    <thead>
                        <tr class="titulo">
                            <th>Cliente</th>
                            <th>Litros</th>
                            <th>Importe</th>
                            <th>Iva</th>
                            <th>Total</th>
                            <th>Pago Real</th>
                        </tr>
                    </thead>
                    <?php
                    foreach ($TotalesGenerales as $Tg) {
                        ?>
                        <tr>
                            <td><?= $Tg["Nombre"] ?></td>
                            <td class="numero"><?= number_format($Tg["Litros"], 2) ?></td>
                            <td class="numero"><?= number_format($Tg["Importe"], 2) ?></td>
                            <td class="numero"><?= number_format($Tg["Iva"], 2) ?></td>
                            <td class="numero"><?= number_format($Tg["Impr"], 2) ?></td>
                            <td class="numero"><?= number_format($Tg["Impn"], 2) ?></td>
                        </tr>
                        <?php
                        $TgTT += $Tg["Litros"];
                        $TgImp += $Tg["Importe"];
                        $TgIva += $Tg["Iva"];
                        $TgImpr += $Tg["Impr"];
                        $TgImpn += $Tg["Impn"];
                    }
                    ?>
                    <tfoot>
                        <tr>
                            <td class="numero">Totales -></td>
                            <td class="numero"><?= number_format($TgTT, 2) ?></td>
                            <td class="numero"><?= number_format($TgImp, 2) ?></td>
                            <td class="numero"><?= number_format($TgIva, 2) ?></td>
                            <td class="numero"><?= number_format($TgImpr, 2) ?></td>
                            <td class="numero"><?= number_format($TgImpn, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div id="Reportes" style="width: 80%;min-height: 150px;"> 
                <table aria-hidden="true">
                    <thead>
                        <tr class="titulo"><td colspan="9">Totales por producto</td></tr>
                        <tr>
                            <td>Producto</td>
                            <td>Consumos</td>
                            <td>Litros</td>
                            <td>Importe</td>
                            <td>IVA</td>
                            <td>IEPS</td>
                            <td>Total</td>
                            <td>Pago Real</td>
                            <td>Tipo</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $Imp = $Lts = $Car = 0;
                        foreach ($registrosT as $rg) {
                            ?>
                            <tr>
                                <td><?= $rg["producto"] ?></td>
                                <td class="numero"><?= $rg["cargas"] ?></td>
                                <td class="numero"><?= number_format($rg["volumen"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["imp"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["iva2"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["ieps"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["pesos"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["pr"], 2) ?></td>
                                <td><?= $rg["tv"] ?></td>
                            </tr>
                            <?php
                            $ValU += $rg["imp"];
                            $Pr += $rg["pr"];
                            $Ieps += $rg["ieps"];
                            $Iva += $rg["iva2"];
                            $Imp += $rg["pesos"];
                            $Lts += $rg["volumen"];
                            $Car += $rg["cargas"];
                            $nRng++;
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>Total</td>
                            <td><?= $Car ?></td>
                            <td><?= number_format($Lts, 2) ?></td>
                            <td><?= number_format($ValU, 2) ?></td>
                            <td><?= number_format($Iva, 2) ?></td>
                            <td><?= number_format($Ieps, 2) ?></td>
                            <td><?= number_format($Imp, 2) ?></td>
                            <td><?= number_format($Pr, 2) ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div id="Reportes" style="width: 60%;min-height: 150px;"> 
            <table aria-hidden="true">
                <thead>
                    <tr class="titulo"><td colspan="5">Total de aditivos por cliente</td></tr>
                    <tr>
                        <td>Cliente</td>
                        <td>Nombre</td>
                        <td>Importe</td>
                        <td>IVA</td>
                        <td>Total</td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $Imp = $Lts = $Car = 0;
                    foreach ($registrosAd as $rg) {
                        ?>
                        <tr>
                            <td><?= $rg["cliente"] ?></td>
                            <td><?= $rg["nombre"] ?></td>
                            <td class="numero"><?= number_format(($rg["importe"] / (1 + $rg["iva"])), 2) ?></td>
                            <td class="numero"><?= number_format(($rg["importe"] / (1 + $rg["iva"])) * $rg["iva"], 2) ?></td>
                            <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                        </tr>
                        <?php
                        $Imp += $rg["importe"];
                        $Cnt += $rg["cantidad"];
                        $Impu += $rg["importe"] / (1 + $rg["iva"]);
                        $Ivau += ($rg["importe"] / (1 + $rg["iva"])) * $rg["iva"];
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2">Total</td>
                        <td><?= number_format($Impu, 2) ?></td>
                        <td><?= number_format($Ivau, 2) ?></td>
                        <td><?= number_format($Imp, 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <?php if ($ConcentrarVtasTarjeta == "S") { ?>
            <div id="Reportes" style="width: 50%;min-height: 150px;"> 
                <table aria-hidden="true">
                    <thead>
                        <tr class="titulo"><td colspan="3">Total de bancos</td></tr>
                        <tr>
                            <td>Banco</td>
                            <td>Nombre</td>
                            <td>Importe</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $Imp = 0;
                        error_log("error372:" . $VtaB);
                        while ($rg[$VtaB]) {
                            ?>
                            <tr>
                                <td><?= $rg["id"] ?></td>
                                <td><?= $rg["banco"] ?></td>
                                <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                            </tr>
                            <?php
                            $Imp += $rg["importe"];
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2">Total</td>
                            <td><strong><?= number_format($Imp, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        <?php } ?>

    </div>

    <div id="footer">
        <form name="formActions" method="post" action="" id="form" class="oculto">
            <div id="Controles">
                <table aria-hidden="true">
                    <tbody>
                        <tr style="height: 40px;">
                            <td align="left" colspan="3">
                                <div style="position: relative;">
                                    <input style="width: 100%;" type="search" id="autocomplete" name="ClienteS">
                                </div>
                                <div id="autocomplete-suggestions"></div>
                            </td>
                            <td colspan="2">Mostrar facturados: 
                                Si<input type="radio" name="Detallado" value="Si" <?= $Detallado === "Si" ? "checked" : "" ?>>
                                No<input type="radio" name="Detallado" value="No" <?= $Detallado === "No" ? "checked" : "" ?>>
                            </td>
                        </tr>
                        <tr>
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
                                        <td>Desglose:</td>
                                        <td style="text-align: left;">
                                            <select id="Desglose" name="Desglose">
                                                <option value="Dia">Día</option>
                                                <option value="Cortes">Cortes</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Producto:</td>
                                        <td style="text-align: left;">
                                            <select id="Producto" name="Producto">
                                                <option value="*">* Todos</option>
                                                <?php
                                                $ProA = $mysqli->query("SELECT * FROM com WHERE com.activo='Si' ORDER BY clavei");
                                                while ($Pro = $ProA->fetch_array()) {
                                                    ?>
                                                    <option value="<?= $Pro["clavei"] ?>"><?= $Pro["descripcion"] ?></option>
                                                <?php } ?>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <div id="horasParam">
                                    <table aria-hidden="true">
                                        <tr>
                                            <td>Hra.inicial:</td>
                                            <td>
                                                <select id="HoraI" name="HoraI">
                                                    <option value="**">**</option>
                                                    <?php for ($i = 0; $i <= 23; $i++) { ?>
                                                        <option value="<?= cZeros($i, 2) ?>"><?= cZeros($i, 2) ?></option>
                                                    <?php } ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Hra.final:</td>
                                            <td>
                                                <select id="HoraF" name="HoraF">
                                                    <option value="**">**</option>
                                                    <?php for ($i = 0; $i <= 23; $i++) { ?>
                                                        <option value="<?= cZeros($i, 2) ?>"><?= cZeros($i, 2) ?></option>
                                                    <?php } ?>
                                                </select>
                                            </td>
                                        </tr>
                                    </table>
                                </div>

                                <div id="turnosParam">
                                    No.turno:
                                    <select id="Turno" name="Turno">
                                        <option value="*">* Todos</option>
                                        <?php
                                        $TurA = $mysqli->query("SELECT turno FROM tur WHERE activo='Si' GROUP BY turno ORDER BY turno");
                                        while ($Tur = $TurA->fetch_array()) {
                                            ?>
                                            <option value="<?= $Tur[0] ?>"><?= $Tur[0] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </td>
                            <td>
                                <table aria-hidden="true">
                                    <tr>
                                        <td style="text-align: right;">Tipo:</td>
                                        <td style="text-align: left;">
                                            <select id="TipoCliente" name="TipoCliente">
                                                <?php
                                                foreach ($TiposClienteArray as $key => $value) {
                                                    echo "<option value='$key'>$value</option>";
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: right;">Isla o Dispensario</td>
                                        <td style="text-align: left;">
                                            <select id="IslaPosicion" name="IslaPosicion">
                                                <?php
                                                foreach ($IslasPosicion as $key => $value) {
                                                    echo "<option value='$key'>$value</option>";
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <span><input type="submit" name="Boton" value="Enviar"></span>
                                <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                                <?php if ($Cliente > 0) { ?>
                                    <span>
                                        <a class="pdf" href="javascript:wingral('consumospdf.php?<?= "ClienteS=$Cliente&FechaI=$FechaI&FechaF=$FechaF&Hora=$HoraI&Turno=$cTurno&Desglose=$Desglose&TipoCliente=$TipoCliente" ?>')">
                                            <i class="icon fa fa-lg fa-file-pdf-o" aria-hidden="true"></i>
                                        </a>
                                    </span>
                                <?php } ?>
                                <span class="ButtonExcel" title="Reporte agrupado por cliente"><a href="report_excel_reports.php?<?= http_build_query($data) ?>"><i class="icon fa fa-lg fa-bold fa-file-excel-o" aria-hidden="true"></i></a></span>
                                <span><button onclick="ExportToExcel('xlsx')"  title="Reporte agrupado por cliente seguido por unidad"><i class="icon fa fa-lg fa-bold fa-file-excel-o" aria-hidden="true">v2</i></button></span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </form>
        <script type="text/javascript">

            $("#Desglose").change(function () {
                if ($(this).val() === "Dia") {
                    $("#horasParam").show();
                    $("#turnosParam").hide();
                } else {
                    $("#horasParam").hide();
                    $("#turnosParam").show();
                }
            });

        </script>
        <?php topePagina(); ?>
    </div>
</body>
</html>