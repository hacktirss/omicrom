<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

$DetalleTexto = $Detallado === "Si" ? "detallado" : "";
$Titulo = "Ventas por $Desglose del $FechaI al $FechaF $DetalleTexto [Reporte Contable]";
$ProducidoN = "SELECT sum(volumen) - sum(volumenp) Producido,SUM(pesos) - SUM(pesosp) ProducidoP FROM com, rm, ct "
        . "WHERE TRUE AND com.clavei = rm.producto AND com.activo = 'Si' AND rm.corte = ct.id AND "
        . "rm.tipo_venta IN ('N') AND DATE(ct.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF') "
        . "ORDER BY rm.producto DESC;";

$registrosPrdN = utils\IConnection::execSql($ProducidoN);
$registros = utils\IConnection::getRowsFromQuery($selectByDia);

$Id = 32; /* Número de en el orden de la tabla submenus */
$data = array("Nombre" => $Titulo, "Reporte" => $Id,
    "FechaI" => $FechaI, "FechaF" => $FechaF,
    "Detallado" => $Detallado, "Desglose" => $Desglose,
    "Turno" => $Turno, "Textos" => "Subtotal", "Filtro" => "1");
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>

        <?php require "./config_omicrom_reports.php"; ?>         
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
                $("#Turno");
                comboTurno();

                $("#Detallado").change(function () {
                    comboTurno();
                });

                $("#Desglose").change(function () {
                    comboTurno();
                });

                $("#FechaI").focus();

                function comboTurno() {
                    if ($("#Detallado").val() === "Si" && $("#Desglose").val() === "Cortes") {
                        $("#Turno").val("<?= $Turno ?>");
                        $("#showTurno").show();
                    } else {
                        $("#showTurno").hide();
                    }
                }
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
            <div id="container">
                <div id="Reportes" style="min-height: 200px;"> 

                    <?php
                    if ($Detallado === "Si") {
                        if ($Turno === "No") {
                            ?>
                            <table aria-hidden="true" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <td> </td>
                                        <td>No.vtas</td>
                                        <td>Litros</td>
                                        <td>Subtotal</td>
                                        <td>IVA</td>
                                        <td>IEPS</td>
                                        <td>Total</td>
                                        <td>Descuento</td>
                                        <td>Litros C.</td>
                                        <td>Importe C.</td>
                                        <td>Aceites</td>
                                        <td>Total</td>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php
                                    $Vts = $impAce = 0;
                                    foreach ($registros as $rg) {
                                        $IvaTt = $imp = $vol = $volN = $impN = $ImpSnIva = $ieps = $ttDescuento = 0;
                                        echo "<tr><td colspan='12' style='text-align:center;font-weight: bold;font-size: 14px;'>" . $rg["fecha"] . " " . $rg["corte"] . "</td></tr>";
                                        foreach ($Productos as $producto) {
                                            $colImporte = "pesos" . $producto["id"];
                                            $colImpSIva = "impSinIva" . $producto["id"];
                                            $colVolumen = "volumen" . $producto["id"];
                                            $colIeps = "ieps" . $producto["id"];
                                            $colProducido = "producido" . $producto["id"];
                                            $colProducidop = "producidop" . $producto["id"];
                                            $colImporteN = "pesosN" . $producto["id"];
                                            $colDescuento = "descuento" . $producto["id"];
                                            $colVolumenN = "volumenN" . $producto["id"];
                                            $colNVentas = "cantidadVenta" . $producto["id"];
                                            $ImpIva = $rg[$colImpSIva] * 0.16;
                                            echo "<tr>";
                                            echo "<td><div style='height:12px;width:12px;background-color:" . $producto["color"] . ";display: inline-block;margin-right:15px;border-radius:3px;'></div>" . $producto["descripcion"] . "</td>";
                                            echo "<td class=\"numero\">" . $rg[$colNVentas] . "</td>";
                                            echo "<td class=\"numero\">" . number_format($rg[$colVolumen], 2) . "</td>";
                                            echo "<td class=\"numero\">" . number_format($rg[$colImpSIva], 2) . "</td>";
                                            echo "<td class=\"numero\">" . number_format($ImpIva, 2) . "</td>";
                                            echo "<td class=\"numero\">" . number_format($rg[$colIeps], 2) . "</td>";
                                            echo "<td class=\"numero\">" . number_format($rg[$colImporte], 2) . "</td>";
                                            echo "<td class=\"numero\">" . number_format($rg[$colDescuento], 2) . "</td>";
                                            echo "<td class=\"numero\">" . number_format($rg[$colVolumenN], 2) . "</td>";
                                            echo "<td class=\"numero\">" . number_format($rg[$colImporteN], 2) . "</td>";
                                            echo "<td></td>";
                                            echo "<td></td></tr>";
                                            $ttDescuento += $rg[$colDescuento];
                                            $SttDescuento += $rg[$colDescuento];
                                            $SttImp += $rg[$colImporte];
                                            $imp += $rg[$colImporte];
                                            $SttVol += $rg[$colVolumen];
                                            $vol += $rg[$colVolumen];
                                            $SttIeps += $rg[$colIeps];
                                            $ieps += $rg[$colIeps];
                                            $SttIva += $ImpIva;
                                            $IvaTt += $ImpIva;
                                            $SttImpN += $rg[$colImporteN];
                                            $impN += $rg[$colImporteN];
                                            $volN += $rg[$colVolumenN];
                                            $SttvolN += $rg[$colVolumenN];
                                            $SttSiva += $rg[$colImpSIva];
                                            $ImpSnIva += $rg[$colImpSIva];
                                            $nImp[$producto["id"]] += $rg[$colImporte];
                                            $nVol[$producto["id"]] += $rg[$colVolumen];

                                            $nImpN[$producto["id"]] += $rg[$colImporteN];
                                            $nVolN[$producto["id"]] += $rg[$colVolumenN];
                                        }
                                        echo "<tr>";
                                        echo "<td style='border-bottom: 3px solid #55514e;font-weight: bold;text-align:right;'>Totales -></td>";
                                        echo "<td style='border-bottom: 3px solid #55514e;' class=\"numero\">" . number_format($rg["ventas"], 0) . "</td>";
                                        echo "<td style='border-bottom: 3px solid #55514e;' class=\"numero\">" . number_format($vol, 2) . "</td>";
                                        echo "<td style='border-bottom: 3px solid #55514e;' class=\"numero\">" . number_format($ImpSnIva, 2) . "</td>";
                                        echo "<td style='border-bottom: 3px solid #55514e;' class=\"numero\">" . number_format($IvaTt, 2) . "</td>";
                                        echo "<td style='border-bottom: 3px solid #55514e;' class=\"numero\">" . number_format($ieps, 2) . "</td>";
                                        echo "<td style='border-bottom: 3px solid #55514e;' class=\"numero\">" . number_format($imp, 2) . "</td>";
                                        echo "<td style='border-bottom: 3px solid #55514e;' class=\"numero\">" . number_format($ttDescuento, 2) . "</td>";
                                        echo "<td style='border-bottom: 3px solid #55514e;' class=\"numero\">" . number_format($volN, 2) . "</td>";
                                        echo "<td style='border-bottom: 3px solid #55514e;' class=\"numero\">" . number_format($impN, 2) . "</td>";
                                        echo "<td style='border-bottom: 3px solid #55514e;' class=\"numero\">" . number_format($rg["pesos_ace"], 2) . "</td>";
                                        echo "<td style='border-bottom: 3px solid #55514e;' class=\"numero\">" . number_format($imp + $rg["pesos_ace"] - $ttDescuento, 2) . "</td></tr>";
                                        $SpTt += $imp + $rg["pesos_ace"];
                                        $Vts += $rg["ventas"];
                                        $impAce += $rg["pesos_ace"];
                                    }
                                    ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td>Total</td>
                                        <td><?= number_format($Vts, 0) ?></td>
                                        <td><?= number_format($SttVol, 2) ?></td>
                                        <td><?= number_format($SttSiva, 2) ?></td>
                                        <td class="moneda"><?= number_format($SttIva, 2) ?></td>
                                        <td class="moneda"><?= number_format($SttIeps, 2) ?></td>
                                        <td class="moneda"><?= number_format($SttImp, 2) ?></td>
                                        <td class="moneda"><?= number_format($SttDescuento, 2) ?></td>
                                        <td class="moneda"><?= number_format($SttvolN, 2) ?></td>
                                        <td class="moneda"><?= number_format($SttImpN, 2) ?></td>
                                        <td class="moneda"><?= number_format($impAce, 2) ?></td>
                                        <td class="moneda"><?= number_format($SpTt - $SttDescuento, 2) ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                            <?php
                        } else {
                            ?>
                            <table aria-hidden="true">
                                <thead>
                                    <tr>
                                        <td>Fecha</td>
                                        <td>Corte</td>
                                        <td>Producto</td>
                                        <td>No.de ventas</td>
                                        <td>Litros</td>
                                        <td>Importe</td>
                                        <td>Aceites</td>
                                        <td>Total</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $ct = 0;
                                    $nImportetCtAce = $nImportetAce = 0;
                                    foreach ($registros as $rg) {
                                        if ($ct != 0) {
                                            if ($ct != $rg["corte"]) {
                                                ?>
                                                <tr class="subtotal">
                                                    <td></td>
                                                    <td></td>
                                                    <td>Total</td>
                                                    <td><?= $nVentasCt ?></td>
                                                    <td><?= number_format($nCantidadtCt, 2) ?></td>
                                                    <td class="moneda"><?= number_format($nImportetCt, 2) ?></td>
                                                    <td class="moneda"><?= number_format($nImportetCtAce, 2) ?></td>
                                                    <td class="moneda"><?= number_format($nImportetCt + $nImportetCtAce, 2) ?></td>
                                                </tr>

                                                <?php
                                                $nImportetAce += $nImportetCtAce;
                                                $nVentasCt = 0;
                                                $nCantidadtCt = 0;
                                                $nImportetCt = 0;
                                            }
                                        }

                                        $ct = $rg["corte"];
                                        ?>

                                        <tr>
                                            <td><?= $rg["fecha"] ?></td>
                                            <td><?= $rg["corte"] ?></td>
                                            <td><?= $rg["producto"] ?></td>
                                            <td class="numero"><?= $rg["ventas"] ?></td>
                                            <td class="numero"><?= $rg["volumen"] ?></td>
                                            <td class="numero"><?= $rg["importe"] ?></td>
                                            <td></td>
                                            <td></td>
                                        </tr>

                                        <?php
                                        $nVentasCt += $rg["ventas"];
                                        $nCantidadtCt += $rg["volumen"];
                                        $nImportetCt += $rg["importe"];
                                        $nImportetCtAce = $rg["pesos_ace"];

                                        $nVentas += $rg["ventas"];
                                        $nCantidadt += $rg["volumen"];
                                        $nImportet += $rg["importe"];
                                    }
                                    ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td>Total</td>
                                        <td><?= $nVentasCt ?></td>
                                        <td><?= number_format($nCantidadtCt, 2) ?></td>
                                        <td class="moneda"><?= number_format($nImportetCt, 2) ?></td>
                                        <td class="moneda"><?= number_format($nImportetCtAce, 2) ?></td>
                                        <td class="moneda"><?= number_format($nImportetCt + $nImportetCtAce, 2) ?></td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td>Gran Total</td>
                                        <td><?= $nVentas ?></td>
                                        <td><?= number_format($nCantidadt, 2) ?></td>
                                        <td class="moneda"><?= number_format($nImportet, 2) ?></td>
                                        <td class="moneda"><?= number_format($nImportetAce + $nImportetCtAce, 2) ?></td>
                                        <td class="moneda"><?= number_format($nImportet + $nImportetAce + $nImportetCtAce, 2) ?></td>
                                    </tr>
                                </tfoot>
                            </table>

                            <?php
                        }
                    } else { /* No detallado */
                        ?>
                        <table aria-hidden="true">
                            <thead>
                                <tr>
                                    <td>Fecha</td>
                                    <td>No.de ventas</td>
                                    <td>Litros</td>
                                    <td>Importe</td>
                                    <td>Descuento</td>
                                    <td>Litros C.</td>
                                    <td>Importe C.</td>
                                    <td>Aceites</td>
                                    <td>Total</td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $nImpAce = $nVts = $nImp = $nLts = 0;
                                foreach ($registros as $rg) {
                                    ?>
                                    <tr>
                                        <td><?= $rg["fecha"] ?></td>
                                        <td class="numero"><?= number_format($rg["ventas"], 0) ?></td>
                                        <td class="numero"><?= number_format($rg["volumen"], 2) ?></td>
                                        <td class="numero"><?= number_format($rg["pesos"], 2) ?></td>
                                        <td class="numero"><?= number_format($rg["descuento"], 2) ?></td>
                                        <td class="numero"><?= number_format($rg["volumenN"], 2) ?></td>
                                        <td class="numero"><?= number_format($rg["pesosN"], 2) ?></td>
                                        <td class="numero"><?= number_format($rg["pesos_ace"], 2) ?></td>
                                        <td class="numero"><?= number_format($rg["pesos"] + $rg["pesos_ace"] - $rg["descuento"], 2) ?></td>
                                    </tr>
                                    <?php
                                    $nImpAce += $rg["pesos_ace"];
                                    $nVts += $rg["ventas"];
                                    $nImp += $rg["pesos"];
                                    $nLts += $rg["volumen"];
                                    $nImpN += $rg["pesosN"];
                                    $nLtsN += $rg["volumenN"];
                                    $nDesc += $rg["descuento"];
                                }
                                ?>
                              
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td>Total</td>
                                    <td><?= number_format($nVts, 0) ?></td>
                                    <td><?= number_format($nLts, 3) ?></td>
                                    <td class="moneda"><?= number_format($nImp, 2) ?></td>
                                    <td><?= $nDesc ?></td>
                                    <td><?= number_format($nLtsN, 3) ?></td>
                                    <td class="moneda"><?= number_format($nImpN, 2) ?></td>
                                    <td class="moneda"><?= number_format($nImpAce, 2) ?></td>
                                    <td class="moneda"><?= number_format($nImp + $nImpAce, 2) ?></td>
                                </tr>
                            </tfoot>
                        </table>

                    <?php } ?>

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
                            <td id="showTurno">
                                <table style="width: 100%" aria-hidden="true">
                                    <tr>
                                        <td>Por Turno:</td>
                                        <td style="text-align: left;">
                                            <select id="Turno" name="Turno">
                                                <option value="No">No</option>
                                                <option value="Si">Si</option>
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
                                                                                                                                                                                                                                                                                    <!--<span class="ButtonExcel"><a href="report_excel_reports.php?<?= http_build_query($data) ?>"><i class="icon fa fa-lg fa-bold fa-file-excel-o" aria-hidden="true"></i></a></span>-->
                                    <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                                    <span><button name="Reporte"><a href="reptransac.php"><i class="icon fa fa-address-card" aria-hidden="true"></i></a></button></span>
                                    <span><button onclick="ExportToExcel('xlsx')"><i class="icon fa fa-lg fa-bold fa-file-excel-o" aria-hidden="true">v2</i></button></span>
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
