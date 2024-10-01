<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;
require "./services/ReportesVentasService.php";


$request = utils\HTTPUtils::getRequest();

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

$registros = utils\IConnection::getRowsFromQuery($selectFacturasProct);
$registrosT = utils\IConnection::getRowsFromQuery($selectFacturasProctT);
$Titulo = "Factura por producto";

$Id = 205; 
$data = array("Nombre" => $Titulo, "Reporte" => $Id,
              "FechaI" =>$FechaI,"FechaF" =>$FechaF);

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
                            <td>Nombre</td>
                            <td>Fecha</td>
                            <td>Estado</td>
                            <td>Descripcion</td>
                            <td>Cantidad</td>
                            <td>Importe</td>
                            <td>Iva</td>
                            <td>Iesp</td>
                            <td>Total</td>
                            <td>RFC</td>
                            <td>uuid</td>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $vol = $imp = $iva= $ieps= $total= 0;
                        foreach ($registros as $rg) {
                            ?>
                            <tr>

                                <td><?= $rg["folio"] ?></td>
                                <td><?= $rg["nombre"]  ?></td>
                                <td><?= $rg["fecha"] ?></td>
                                <td><?= $rg["statusfc"] ?></td>
                                <td><?= $rg["descripcion"] ?></td>
                                <td><?= number_format($rg["cantidad"],2) ?></td>
                                <td><?= number_format($rg["importe"], 2) ?></td>
                                <td><?= number_format($rg["iva"],2) ?></td>
                                <td><?= number_format($rg["ieps"],2) ?></td>
                                <td><?= number_format($rg["total"],2) ?></td>
                                <td><?= $rg["rfc"] ?></td>
                                <td><?= $rg["uuid"] ?></td>
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
        <div class="sheet padding-5mm"> <!-- Abre hoja-->
            <div id="TablaDatosReporte"> 
                    <div style="width: 50%;padding-top: 10px;min-height: 200px;margin-left: auto;margin-right: auto;">
                        <div><h3> T O T A L E S </h3></div>
                    </div>
                    <table aria-hidden="true">
                         <thead>
                            <tr class="tableexport-ignore">
                                <td>Producto</td>
                                <td>Cantidad</td>
                                <td>Importe</td>
                                <td>Iva</td>
                                <td>Ieps</td>
                                <td>Total</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                    $merma = 0;
                                    $Cargas = $Litros = $Importe = $LitrosA = $LitrosN = $ImporteN = 0;
                                    foreach ($registrosT as $rg) {
                                        ?>
                                        <tr>                 
                                            <td><?= $rg["descripcion"] ?></td>
                                            <td class="numero"><?= number_format($rg["cantidad"], 0) ?></td>
                                            <td class="numero"><?= number_format($rg["importe"], 0) ?></td>
                                            <td class="numero"><?= number_format($rg["iva"], 0) ?></td>
                                            <td class="numero"><?= number_format($rg["ieps"], 0) ?></td>
                                            <td class="numero"><?= number_format($rg["total"], 0) ?></td>

                                        </tr>
                                        <?php
                                        $Cargas += $rg["cargas"];
                                        $Litros += $rg["volumenfac"];
                                        $Importe += $rg["importefac"];
                                        $LitrosA += $rg["incremento"];
                                        $LitrosN += $rg["neto"];
                                        $ImporteN += $rg["importeNet"];
                                        $merma += $rg["aumento_neto"];
                                    }
                                    ?>
                                </tbody>
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

