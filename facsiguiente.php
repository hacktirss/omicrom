<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$mes = $request->getAttribute("mes");
$anio = $request->getAttribute("anio");
$mesS = $request->getAttribute("mesS");
$anioS = $request->getAttribute("anioS");
$sigmes = $mes+1;
//var_dump($request);
//var_dump($mes);
//var_dump($anio);
//var_dump($mesS);
//var_dump($aniS);
$selectFacturas = "
SELECT  fc.folio,fc.serie,fc.fecha
,fc.uuid,rm.id, rm.inicio_venta,fcd.cantidad volumenp,rm.importe importeTotal,
fcd.cantidad*fcd.precio importe,(fcd.cantidad*fcd.precio)*fcd.iva importeIva,
fcd.cantidad * fcd.ieps ieps, inv.descripcion
FROM fc LEFT JOIN fcd on fc.id =fcd.id left join rm ON fcd.ticket=rm.id
LEFT join inv ON fcd.producto = inv.id
WHERE YEAR(fc.fecha) = '$anioS' AND MONTH(fc.fecha) = '$mesS' and fc.uuid != '-----' 
AND fc.status=1 AND  fcd.ticket in (SELECT id FROM rm WHERE YEAR(DATE(fecha_venta)) = '$anio' 
AND  MONTH(DATE(fecha_venta)) = '$mes' AND fc.uuid <> '-----' ) AND fcd.producto <= 5
order by inv.descripcion";

/*$selectFacturas = "
SELECT fc.folio,fc.serie,fc.fecha,fc.uuid,rm.id, rm.inicio_venta,fcd.cantidad volumenp,rm.importe importeTotal,
fcd.cantidad*fcd.precio importe,(fcd.cantidad*fcd.precio)*fcd.iva importeIva,
fcd.cantidad * fcd.ieps ieps
FROM fc LEFT JOIN fcd on fc.id =fcd.id 
	inner join rm on rm.id = fcd.ticket 
WHERE YEAR(fecha) = '$anioS' AND MONTH(fecha) = '$mesS' AND fcd.producto <= 5 AND fc.uuid != '-----' 
AND fc.status=1 AND fcd.ticket in 
    (SELECT id FROM rm WHERE YEAR(DATE(fecha_venta)) = '$anio' and MONTH(DATE(fecha_venta)) = '$mes'
     AND uuid <> '-----' AND producto  in ('GS','GP','GD'));
";*/

//var_dump($selectFacturas);

$registros = utils\IConnection::getRowsFromQuery($selectFacturas);

$Titulo = "Detallado en el siguiente mes del mes: ".$mes." del año".$anio;

$Id = 200; 
$data = array("Nombre" => $Titulo, "Reporte" => $Id,
    "mes" => $mes, "anio" => $anio,"mesS" => $mesS, "anioS" => $anioS
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
                            <td colspan="12">Detallado de Facturas</td>
                        </tr>
                        <tr>
                            <td>Folio</td>
                            <td>Serie</td>
                            <td>Fecha</td>
                            <td>UUID</td>
                            <td>Producto</td>
                            <td>Ticket</td>
                            <td>Fecha ticket</td>
                            <td>Volumen</td>
                            <td>Importe</td>
                            <td>Iva</td>
                            <td>Ieps</td>
                            <td>Total</td>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $vol = $imp = $iva= $ieps= $total= 0;
                        foreach ($registros as $rg) {
                            ?>
                            <tr>

                                <td><?= $rg["folio"] ?></td>
                                <td><?= $rg["serie"]  ?></td>
                                <td><?= $rg["fecha"] ?></td>
                                <td><?= $rg["uuid"] ?></td>
                                <td><?= $rg["descripcion"] ?></td>
                                <td><?= $rg["id"] ?></td>
                                <td><?= $rg["inicio_venta"] ?></td>
                                <td><?= number_format($rg["volumenp"],2) ?></td>
                                <td><?= number_format($rg["importe"], 2) ?></td>
                                <td><?= number_format($rg["importeIva"],2) ?></td>
                                <td><?= number_format($rg["ieps"],2) ?></td>
                                <td><?= number_format($rg["importeTotal"],2) ?></td>
                            </tr>
                            <?php
                            $vol += $rg["volumenp"];
                            $imp += $rg["importe"];
                            $iva += $rg["importeIva"];
                            $ieps += $rg["ieps"];
                            $total += $rg["importeTotal"];
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>Total</td>
                            <td><?= number_format($vol , 2) ?></td>
                            <td><?= number_format($imp , 2) ?></td>
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

