<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$serie = $request->getAttribute("serie");
$mes = $request->getAttribute("mes");
$anio = $request->getAttribute("anio");
$piezas = $request->getAttribute("piezas");

$selectFacturas = "
SELECT fc.serie,inv.descripcion,
		sum(if(producto>5,fcd.cantidad,0)) piezas,
        sum(if(producto<5,fcd.cantidad,0)) volumen,
        sum((fcd.cantidad * fcd.precio)) importe,
        sum((fcd.cantidad * fcd.precio) * fcd.iva) iva,
        sum((fcd.cantidad * fcd.ieps)) ieps,
        sum(fcd.importe) total
        FROM fc left join fcd on fc.id = fcd.id
        left join inv on fcd.producto = inv.id
        WHERE YEAR(fc.fecha) = '$anio' AND MONTH(fc.fecha) = '$mes' and fc.uuid != '-----'  and fc.serie = '$serie' and status = 1 group by producto;
";

$registros = utils\IConnection::getRowsFromQuery($selectFacturas);

$Titulo = "Detallado de facturas por Serie: ".$serie." del mes: ".$mes." del año".$anio;

$Id = 201; 
$data = array("Nombre" => $Titulo, "Reporte" => $Id,
    "mes" => $mes, "anio" => $anio,"serie" => $serie
    );
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <title><?= $Gcia ?></title>
    </head>

    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo); ?>
            <div id="Reportes">
                 <table aria-hidden="true">
                    <thead>
                        <tr class="titulo">
                            <td colspan="8">Detallado de Facturas</td>
                        </tr>
                        <tr>
                            <td>Serie</td>
                            <td>Descripción</td>
                            <td>Piezas</td>
                            <td>Litros</td>
                            <td>Importe</td>
                            <td>Iva</td>
                            <td>Ieps</td>
                            <td>Total</td>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $vol = $total = $piezas = $importe = $iva = $ieps= 0;
                        foreach ($registros as $rg) {
                            ?>
                            <tr>

                                <td><?= $rg["serie"] ?></td>
                                <td><?= $rg["descripcion"] ?></td>
                                <td><?= number_format($rg["piezas"] , 0) ?></td>
                                <td><?= number_format($rg["volumen"] , 2)?></td>
                                <td><?= number_format($rg["importe"] , 2) ?></td>
                                <td><?= number_format($rg["iva"] , 2)?></td>
                                <td><?= number_format($rg["ieps"] , 2)?></td>
                                <td><?= number_format($rg["total"] , 2)?></td>
                            </tr>
                            <?php
                            $vol += $rg["volumen"];
                            $piezas += $rg["piezas"];
                            $importe += $rg["importe"];
                            $iva += $rg["iva"];
                            $ieps += $rg["ieps"];
                            $total += $rg["total"];
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td>Total</td>
                            <td><?= number_format($piezas , 0) ?></td>
                            <td><?= number_format($vol , 2) ?></td>
                            <td><?= number_format($importe , 2) ?></td>
                            <td><?= number_format($iva , 2) ?></td>
                            <td><?= number_format($ieps , 2) ?></td>
                            <td><?= number_format($total , 2) ?></td>
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
                            <td>
                                <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                                <span class="ButtonExcel"><a href="report_excel_reports.php?<?= http_build_query($data) ?>"><i class="icon fa fa-lg fa-bold fa-file-excel-o" aria-hidden="true"></i></a></span>
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
            <?php topePagina(); ?>
        </div>
    </body>
</html>

