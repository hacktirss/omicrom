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

if ($op == 1) {
    $Titulo = "Facturas generadas de manera manual del $Fecha";
} else {
    $Titulo = "Facturas generadas de manera manual del $FechaI al $FechaF";
}


$selectFacturas = "
        SELECT DISTINCT fc.folio,date(fecha) fecha,fc.cantidad,fc.cliente,
        fc.total, cli.id, cli.nombre
        FROM fc
        JOIN fcd ON fcd.id = fc.id
        JOIN cli ON cli.id = fc.cliente AND (cli.rfc NOT LIKE 'XAXX010101000' OR cli.tipodepago = 'Monedero') 
        WHERE (fcd.ticket =0 OR fcd.ticket IS NULL) AND fc.status=". StatusFactura::CERRADO .  "";


if ($tpo == 1) {
    if ($op == 1) {
        $selectFacturas .= " AND DATE( fc.fecha ) = DATE('$FechaDia') AND cli.tipodepago='Contado' ";
    } else {
        $selectFacturas .= " AND DATE( fc.fecha ) BETWEEN DATE('$FechaI') AND DATE('$FechaF') AND cli.tipodepago='Contado' ";
    }
} elseif ($tpo == 2) {
    if ($op == 1) {
        $selectFacturas .= " AND DATE( fc.fecha ) = DATE('$FechaDia') AND cli.tipodepago='Credito' ";
    } else {
        $selectFacturas .= " AND DATE( fc.fecha ) BETWEEN DATE('$FechaI') AND DATE('$FechaF') AND cli.tipodepago='Credito' ";
    }
} elseif ($tpo == 3) {
    if ($op == 1) {
        $selectFacturas .= " AND DATE( fc.fecha ) = DATE('$FechaDia') AND cli.tipodepago='Prepago' ";
    } else {
        $selectFacturas .= " AND DATE( fc.fecha ) BETWEEN DATE('$FechaI') AND DATE('$FechaF') AND cli.tipodepago='Prepago' ";
    }
} elseif ($tpo == 4) {
    if ($op == 1) {
        $selectFacturas .= " AND DATE( fc.fecha ) = DATE('$FechaDia') AND cli.tipodepago='Tarjeta' ";
    } else {
        $selectFacturas .= " AND DATE( fc.fecha ) BETWEEN DATE('$FechaI') AND DATE('$FechaF') AND cli.tipodepago='Tarjeta' ";
    }
}

$registros = utils\IConnection::getRowsFromQuery($selectFacturas);
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
                            <td colspan="5">Desgloce por dia natural y tipo de pago</td>
                        </tr>
                        <tr>
                            <td>Folio</td>
                            <td>Fecha</td>
                            <td>Cliente</td>
                            <td>Nombre</td>
                            <td>Importe</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($registros as $rg) {
                            ?>
                            <tr>

                                <td><?= $rg["folio"] ?></td>
                                <td><?= $rg["fecha"] ?></td>
                                <td><?= $rg["cliente"] ?></td>
                                <td><?= $rg["nombre"] ?></td>
                                <td><?= number_format($rg["total"], 2) ?></td>
                            </tr>
                            <?php
                            $nImp += $rg["total"];
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>Total</td>
                            <td><?= number_format($nImp, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </body>
</html>

