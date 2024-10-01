<?php
#Librerias
session_start();

include_once("./check_report.php");
include_once("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

if (empty($Corte)) {
    $Titulo = "Reporte de ieps del $FechaI al $FechaF";
} else {
    $Titulo = "Desgloce de impuestos del corte $Corte";
}

$registros = utils\IConnection::getRowsFromQuery($selectIeps);

$registrosT = utils\IConnection::getRowsFromQuery($selectIepsT);

$data = array(
    "Nombre" => $Titulo, "Reporte" => 121,
    "FechaI" => $FechaI, "FechaF" => $FechaF, "Consig" => "No",
    "Detallado" => "Si", "Filtro" => "fecha", "Textos" => "Subtotal"
);
$nLink = "report_excel.php?" . http_build_query($data);
$pVol = 0;
$pImp = 0;
$pIeps = 0;
$pIva = 0;
$pTot = 0;
$Tot = 0;
$Vol = 0;
$Imp = 0;
$Ieps = 0;
$Iva = 0;
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">

    <head>
        <?php require './config_omicrom_reports.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $('#FechaI').val('<?= $FechaI ?>').attr('size', '10').addClass('texto_tablas');
                $('#FechaF').val('<?= $FechaF ?>').attr('size', '10').addClass('texto_tablas');
                $('#cFechaI').css('cursor', 'hand').click(function () {
                    displayCalendar($('#FechaI')[0], 'yyyy-mm-dd', $(this)[0]);
                });
                $('#cFechaF').css('cursor', 'hand').click(function () {
                    displayCalendar($('#FechaF')[0], 'yyyy-mm-dd', $(this)[0]);
                });
                $('#Corte').val('<?= $Corte ?>');
                $("#Desglose").val("<?= $Desglose ?>");
                $("#Consig").val("<?= $Consig ?>");
            });
        </script>
    </head>

    <body>
        <div id='container'>
            <?php nuevoEncabezado($Titulo) ?>
            <div id="Reportes" style="min-height: 200px;">
                <?php if (empty($Corte)) { ?>
                    <table aria-hidden="true">
                        <thead>
                            <tr>
                                <td>Fecha</td>
                                <td>Producto</td>
                                <td>Ventas</td>
                                <td>Fac.ieps</td>
                                <td>Precio</td>
                                <td>Litros</td>
                                <td>Importe</td>
                                <td>Ieps</td>
                                <td>Iva</td>
                                <td>Descuento</td>
                                <td>Total</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $fecha = "";
                            foreach ($registros as $rg) {
                                if ($rg["total"] > 0) {
                                    if ($fecha != $rg["fecha"] && $fecha != "") {
                                        if ($fecha != "") {
                                            ?>

                                            <tr class="subtotal">
                                                <td colspan="5">Total</td>
                                                <td class="numero"><?= number_format($Vol, 2) ?></td>
                                                <td class="moneda"><?= number_format($Imp, 2) ?></td>
                                                <td class="moneda"><?= number_format($Ieps, 6) ?></td>
                                                <td class="moneda"><?= number_format($Iva, 2) ?></td>
                                                <td class="moneda"><?= number_format($desc, 2) ?></td>
                                                <td class="moneda"><?= number_format($Total, 2) ?></td>
                                            </tr>
                                            <?php
                                        }
                                        $Vol = $Imp = $Ieps = $Iva = $Tot = $desc = 0;
                                    }
                                    ?>
                                    <tr>
                                        <td><?= $rg["fecha"] ?></td>
                                        <td><?= $rg["descripcion"] ?></td>
                                        <td class="numero"><?= number_format($rg["ventas"], 0) ?></td>
                                        <td class="numero"><?= number_format($rg["ieps"], 6) ?></td>
                                        <td class="numero"><?= number_format($rg["precio"], 2) ?></td>
                                        <td class="numero"><?= number_format($rg["volumen"], 2) ?></td>
                                        <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                                        <td class="numero"><?= number_format($rg["iepsCuota"], 2) ?></td>
                                        <td class="numero"><?= number_format($rg["iva"], 2) ?></td>
                                        <td class="numero"><?= number_format($rg["descuento"], 2) ?></td>
                                        <td class="numero"><?= number_format($rg["total"] - $rg["descuento"], 2) ?></td>
                                    </tr>
                                    <?php
                                    $Vol += $rg["volumen"];
                                    $Imp += $rg["importe"];
                                    $Ieps += $rg["iepsCuota"];
                                    $Iva += $rg["iva"];
                                    $nVol += $rg["volumen"];
                                    $nImp += $rg["importe"];
                                    $nIeps += $rg["iepsCuota"];
                                    $nIva += $rg["iva"];
                                    $nTot += $rg["total"] - $rg["descuento"];
                                    $Total += $rg["total"] - $rg["descuento"];
                                    $fecha = $rg["fecha"];
                                    $desc += $rg["descuento"];
                                    $descT += $rg["descuento"];
                                    $nRng++;
                                }
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5">Total</td>
                                <td class="numero"><?= number_format($Vol, 2) ?></td>
                                <td class="moneda"><?= number_format($Imp, 2) ?></td>
                                <td class="moneda"><?= number_format($Ieps, 6) ?></td>
                                <td class="moneda"><?= number_format($Iva, 2) ?></td>
                                <td class="moneda"><?= number_format($desc, 2) ?></td>
                                <td class="moneda"><?= number_format($Total, 2) ?></td>
                            </tr>

                            <tr>
                                <td colspan="5">Gran Total</td>
                                <td class="numero"><?= number_format($nVol, 2) ?></td>
                                <td class="moneda"><?= number_format($nImp, 2) ?></td>
                                <td class="moneda"><?= number_format($nIeps, 6) ?></td>
                                <td class="moneda"><?= number_format($nIva, 2) ?></td>
                                <td class="moneda"><?= number_format($descT, 2) ?></td>
                                <td class="moneda"><?= number_format($nTot, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                    <?php
                } else {
                    ?>
                    <table aria-hidden="true">
                        <thead>
                            <tr>
                                <td>Posicion.</td>
                                <td>Manguera.</td>
                                <td>Producto</td>
                                <td>Ieps</td>
                                <td>Precio</td>
                                <td>Ventas</td>
                                <td>Volumen</td>
                                <td>Importe</td>
                                <td>IepsCuota</td>
                                <td>Iva</td>
                                <td>Total</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $dispensario = $pos = "";
                            $nVen = 0;
                            foreach ($registros as $rg) {

                                if ($pos != $rg["posicion"] && $pos != "") {
                                    if ($pos != "") {
                                        ?>
                                        <tr class="subtotal">
                                            <td class="numero" colspan="5">Pos: <?= $pos ?></td>
                                            <td class="numero"><?= number_format($pVen, 0) ?></td>
                                            <td class="numero"><?= number_format($pVol, 2) ?></td>
                                            <td class="numero"><?= number_format($pImp, 2) ?></td>
                                            <td class="numero"><?= number_format($pIeps, 6) ?></td>
                                            <td class="numero"><?= number_format($pIva, 2) ?></td>
                                            <td class="numero"><?= number_format($pTot, 2) ?></td>
                                        </tr>
                                        <?php
                                    }
                                    $pVol = $pImp = $pIeps = $pIva = $pTot = $pVen = 0;
                                }
                                if ($dispensario != $rg["dispensario"] && $dispensario != "") {
                                    if ($dispensario != "") {
                                        ?>
                                        <tr class="subtotal">
                                            <td class="numero" colspan="5">Total Disp:<?= $dispensario ?></td>
                                            <td class="numero"><?= number_format($Ven, 0) ?></td>
                                            <td class="numero"><?= number_format($Vol, 2) ?></td>
                                            <td class="numero"><?= number_format($Imp, 2) ?></td>
                                            <td class="numero"><?= number_format($Ieps, 6) ?></td>
                                            <td class="numero"><?= number_format($Iva, 2) ?></td>
                                            <td class="numero"><?= number_format($Tot, 2) ?></td>
                                        </tr>
                                        <?php
                                    }
                                    $Vol = $Imp = $Ieps = $Iva = $Tot = $Ven = 0;
                                }
                                ?>
                                <tr>
                                    <td class="numero"><?= $rg["posicion"] ?></td>
                                    <td class="numero"><?= number_format($rg["manguera"], 0) ?></td>
                                    <td><?= $rg["descripcion"] ?></td>
                                    <td class="numero"><?= number_format($rg["ieps"], 6) ?></td>
                                    <td class="numero"><?= number_format($rg["precio"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["ventas"], 0) ?></td>
                                    <td class="numero"><?= number_format($rg["volumen"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["iepsCuota"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["iva"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["total"], 2) ?></td>
                                </tr>
                                <?php
                                $pVol += $rg["volumen"];
                                $pImp += $rg["importe"];
                                $pIeps += $rg["iepsCuota"];
                                $pIva += $rg["iva"];
                                $pTot += $rg["total"];

                                $Vol += $rg["volumen"];
                                $Imp += $rg["importe"];
                                $Ieps += $rg["iepsCuota"];
                                $Iva += $rg["iva"];
                                $Tot += $rg["total"];

                                $nVol += $rg["volumen"];
                                $nImp += $rg["importe"];
                                $nIeps += $rg["iepsCuota"];
                                $nIva += $rg["iva"];
                                $nTot += $rg["total"];

                                $dispensario = $rg["dispensario"];
                                $pos = $rg["posicion"];
                                $nRng++;

                                $pVen += $rg["ventas"];
                                $Ven += $rg["ventas"];
                                $nVen += $rg["ventas"];
                            }
                            ?>
                        </tbody>
                        <tfoot>

                            <tr>
                                <td colspan="5">Pos:<?= $pos ?></td>
                                <td class="numero"><?= number_format($pVen, 0) ?></td>
                                <td class="numero"><?= number_format($pVol, 2) ?></td>
                                <td class="moneda"><?= number_format($pImp, 2) ?></td>
                                <td class="moneda"><?= number_format($pIeps, 6) ?></td>
                                <td class="moneda"><?= number_format($pIva, 2) ?></td>
                                <td class="moneda"><?= number_format($pTot, 2) ?></td>
                            </tr>

                            <tr>
                                <td colspan="5">Total Disp: <?= $dispensario ?></td>
                                <td class="numero"><?= number_format($Ven, 0) ?></td>
                                <td class="numero"><?= number_format($Vol, 2) ?></td>
                                <td class="moneda"><?= number_format($Imp, 2) ?></td>
                                <td class="moneda"><?= number_format($Ieps, 6) ?></td>
                                <td class="moneda"><?= number_format($Iva, 2) ?></td>
                                <td class="moneda"><?= number_format($Tot, 2) ?></td>
                            </tr>

                            <tr>

                                <td colspan="5">Gran Total</td>
                                <td class="numero"><?= number_format($nVen, 0) ?></td>
                                <td class="numero"><?= number_format($nVol, 2) ?></td>
                                <td class="moneda"><?= number_format($nImp, 2) ?></td>
                                <td class="moneda"><?= number_format($nIeps, 6) ?></td>
                                <td class="moneda"><?= number_format($nIva, 2) ?></td>
                                <td class="moneda"><?= number_format($nTot, 2) ?></td>
                        </tfoot>
                    </table>

                    <br />

                    <table aria-hidden="true">
                        <thead>
                            <tr class="titulo">
                                <td></td>
                                <td colspan="4">VENTA TOTAL POR PRECIO</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>#Vtas</td>
                                <td>Precio</td>
                                <td>Volumen</td>
                                <td>Importe</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($registrosT as $rg) {
                                ?>
                                <tr>
                                    <td> <?= $rg["producto"] ?> </td>
                                    <td class="numero"><?= number_format($rg["ventas"], 0) ?></td>
                                    <td class="numero"><?= number_format($rg["precio"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["volumen"], 3) ?></td>
                                    <td class="numero"><?= number_format($rg["pesos"], 2) ?></td>
                                </tr>
                                <?php
                                $nVta += $rg["ventas"];
                                $nLt += $rg["volumen"];
                                $nIm += $rg["pesos"];
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>Gran total</td>
                                <td><?= number_format($nVta, 0) ?> </td>
                                <td></td>
                                <td><?= number_format($nLt, 3) ?></td>
                                <td><?= number_format($nIm, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                <?php } ?>
            </div>
        </div>

        <div id="footer">
            <form name="formActions" method="post" action="" id="form" class="oculto">
                <div id="Controles">
                    <table aria-hidden="true">
                        <tr style="height: 40px;">
                            <?php
                            $Return = "javascript:window.close();";
                            if (!empty($Corte)) {
                                if (is_numeric($Corte)) {
                                    $Return = "impcorte.php?busca=" . $Corte;
                                }
                                ?>
                                <td class="td1" style="width: 50%">
                                    <span><a href="<?= $Return ?>" class="enlaceboton textosCualli"><i class="icon fa fa-lg fa-arrow-left" aria-hidden="true"></i> Regresar</a></span>
                                </td>
                                <td>
                                    <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                                </td>
                            <input type="hidden" name="Corte" id="Corte">
                            <?php
                        } else {
                            ?>
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
                                        <td>&nbsp;Desglose:</td>
                                        <td style="text-align: left;padding-left: 5px">
                                            <select id="Desglose" name="Desglose">
                                                <option value="Cortes">Cortes</option>
                                                <option value="Dia">Dia</option>
                                            </select>
                                        </td>
                                    <tr>
                                        <td>&nbsp;Incluir consignaciones:</td>
                                        <td style="text-align: left;padding-left: 5px">
                                            <select id="Consig" name="Consig">
                                                <option value="No">No</option>
                                                <option value="Si">Si</option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </td>

                            <td>
                                <span><input type="submit" name="Boton" value="Enviar"></span>
                                <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                                <span class="ButtonExcel"><a href="<?= $nLink ?>"><i class="icon fa fa-lg fa-bold fa-file-excel-o" aria-hidden="true"></i></a></span>
                            </td>

                            </tr>
                            <?php
                        }
                        ?>
                    </table>
                </div>
            </form>
            <?php topePagina(); ?>
        </div>
    </body>

</html>