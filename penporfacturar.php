<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();

require "./services/ReportesVentasService.php";

$op = $request->getAttribute("op");
$tpo = $request->getAttribute("tpo");  //1.contado, 2.credito, 3.prepago, 4.tarjeta
if ($tpo == 1){
    $tipo = "contado"; 
}elseif($tpo == 4){
    $tipo = "tarjeta";
}

if ($op == 1) {
    $Titulo = "Facturas pendientes por Facturar del $Fecha";
} else {
    $Titulo = "Facturas pendientes por Facturar del $FechaI al $FechaF";
}


$selectFacturas = "
SELECT rm.id ticket,com.descripcion ,cli.nombre ,rm.uuid,rm.precio, 
round(rm.volumen,2) volumen, 
round(((rm.importe-(rm.volumen * rm.ieps))/(1+rm.iva)),2) subtotal,
round(((rm.importe-(rm.volumen * rm.ieps))/(1+rm.iva)) * rm.iva,2) importeIva, 
round((rm.volumen * rm.ieps),2) ieps, round(rm.importe,2)  importe 
    FROM rm LEFT JOIN cli ON rm.cliente=cli.id
    INNER JOIN com ON rm.producto = com.clavei 
        WHERE rm.fecha_venta   BETWEEN " . str_replace("-", "", $FechaI) . " AND " . str_replace("-", "", $FechaF) . "  
            AND rm.uuid = '-----' AND rm.tipo_venta='D' 
            AND cli.tipodepago = '".$tipo."'
            AND rm.importe > 0 ";
$ticketsFactura = "SELECT 
fc.serie,fc.folio,
CASE 
	WHEN fc.status = 0 THEN 'Abierta'
    WHEN fc.status = 1 THEN 'Timbrar'
    WHEN fc.status = 2 THEN 'En procesos de cancelacion'
    WHEN fc.status = 3 THEN 'Cancelada'
   ELSE 
   'ERROR'
END AS status
,fcd.ticket,rm.importe
FROM
    rm
   RIGHT join fcd on rm.id = fcd.ticket
	INNER JOIN fc on fcd.id = fc.id
WHERE
    fecha_venta BETWEEN " . str_replace("-", "", $FechaI) . " AND " . str_replace("-", "", $FechaF) . " 
       AND rm.cliente in(select id from cli where tipodepago = '".$tipo."')
        AND rm.uuid = '-----'
        AND rm.importe > 0
        AND rm.pesos != 0
        and fcd.producto < 4
GROUP BY fcd.id";
$registros = utils\IConnection::getRowsFromQuery($selectFacturas);
$registros1 = utils\IConnection::getRowsFromQuery($ticketsFactura);
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
                            <td colspan="9">Tickets asignados a factura sin Timbrar</td>
                        </tr>
                        <tr>
                            <td>Serie</td>
                            <td>Folio</td>
                            <td>Status</td>
                            <td>Tickets</td>
                            <td>Importe</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($registros1 as $rg) {
                            ?>
                            <tr>

                                <td><?= $rg["serie"] ?></td>
                                <td><?= $rg["folio"] ?></td>
                                <td><?= $rg["status"] ?></td>
                                <td><?= $rg["ticket"] ?></td>
                                <td><?= number_format($rg["importe"], 2) ?></td>
                            </tr>
                            <?php
                            $nVol += $rg["importe"];

                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>Total</td>
                            <td><?= number_format($nVol, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
                 <table aria-hidden="true">
                    <thead>
                        <tr class="titulo">
                            <td colspan="9">Tickets pendientes por factura </td>
                        </tr>
                        <tr>
                            <td>Ticket</td>
                            <td>Producto</td>
                            <td>Cliente</td>
                            <td>Precio</td>
                            <td>Volumen</td>
                            <td>Subtotal</td>
                            <td>Iva</td>
                            <td>Ieps</td>
                            <td>Total</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($registros as $rg) {
                            ?>
                            <tr>

                                <td><?= $rg["ticket"] ?></td>
                                <td><?= $rg["descripcion"] ?></td>
                                <td><?= $rg["nombre"] ?></td>
                                <td><?= number_format($rg["precio"], 2) ?></td>
                                <td><?= number_format($rg["volumen"], 2) ?></td>
                                <td><?= number_format($rg["subtotal"], 2) ?></td>
                                <td><?= number_format($rg["importeIva"], 2) ?></td>
                                <td><?= number_format($rg["ieps"], 2) ?></td>
                                <td><?= number_format($rg["importe"], 2) ?></td>
                            </tr>
                            <?php
                            $nVol += $rg["volumen"];
                            $nStotal += $rg["subtotal"];
                            $nIva += $rg["importeIva"];
                            $nIesp += $rg["ieps"];
                            $nTotal += $rg["importe"];

                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>Total</td>
                            <td><?= number_format($nVol, 2) ?></td>
                            <td><?= number_format($nStotal, 2) ?></td>
                            <td><?= number_format($nIva, 2) ?></td>
                            <td><?= number_format($nIesp, 2) ?></td>
                            <td><?= number_format($nTotal, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </body>
</html>

