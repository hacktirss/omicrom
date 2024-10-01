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
$sigmes = $mes + 1;

    $selectVent = "select * FROM  ( SELECT rm.id,rm.inicio_venta,com.descripcion,fc.serie,fc.folio,fc.fecha,fc.uuid,
                    round(fcd.cantidad,2) cantidad,
                    round(fcd.cantidad*fcd.precio,2)subtotal,
                    round((fcd.cantidad*fcd.precio)*fcd.iva,2) importeIva,
                    round(fcd.cantidad * fcd.ieps,2) ieps,
                    round(fcd.importe,2) importe
                    ,rm.fecha_venta
                    FROM fc inner join fcd ON fc.id = fcd.id 
                            INNER JOIN rm on fcd.ticket = rm.id
                            inner join com on rm.producto = com.clavei
                            WHERE YEAR(fc.fecha_indice) = $anio 
                            AND MONTH(fc.fecha_indice) = $mes 
                            and fc.uuid != '-----' 
                            and status = 1 and fcd.producto < 5 
                            ) f where MONTH(fecha_venta) <> $mes";

//echo $selectFacturas;
$registros = utils\IConnection::getRowsFromQuery($selectVent);

?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <title><?= $Gcia ?></title>
        <script type="text/javascript" src="https://unpkg.com/xlsx@0.15.1/dist/xlsx.full.min.js"></script>
        <script>
        function ExportToExcel(type, fn, dl) {
       var elt = document.getElementById('Reportes');
       
       var wb = XLSX.utils.table_to_book(elt, { sheet: "vtaditivosV" });
       
       return dl ?
         XLSX.write(wb, { bookType: type, bookSST: true, type: 'base64' }):
         
         XLSX.writeFile(wb, fn || ('ReporteVentasOtroMes.' + (type || 'xlsx')));
    };
    </script>
    </head>

    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo); ?>
            <span><button onclick="ExportToExcel('xlsx')"><i class="icon fa fa-lg fa-bold fa-file-excel-o" aria-hidden="true"></i></button></span>
            <div id="Reportes">
                    <table aria-hidden="true">
                        <thead>
                            <tr class="titulo">
                                <td colspan="12">Detallado de ventas de otro mes facturas en el mes de <?= $mes ?>  </td>
                            </tr>
                            <tr>
                                <td>Ticket</td>
                                <td>Fecha Venta</td>
                                <td>Descripcion</td>
                                <td>Serie</td>
                                <td>Folio</td>
                                <td>Fecha factura</td>
                                <td>Uuid</td>
                                <td>volumen</td>
                                <td>subtotal</td>
                                <td>importeIva</td>
                                <td>ieps</td>
                                <td>importe</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $imp = $subt =$iva = $ieps= $vol= 0;
                            foreach ($registros as $rg) {
                                ?>
                                <tr>

                                    <td><?= $rg["id"] ?></td>
                                    <td><?= $rg["inicio_venta"] ?></td>
                                    <td><?= $rg["descripcion"] ?></td>
                                    <td><?= $rg["serie"] ?></td>
                                    <td><?= $rg["folio"] ?></td>
                                    <td><?= $rg["fecha"] ?></td>
                                    <td><?= $rg["uuid"] ?></td>
                                    <td><?= $rg["cantidad"] ?></td>
                                    <td><?= $rg["subtotal"] ?></td>
                                    <td><?= $rg["importeIva"] ?></td>
                                    <td><?= $rg["ieps"] ?></td>
                                    <td><?= $rg["importe"] ?></td>
                                </tr>
                                <?php
                                $imp += $rg["importe"];
                                $vol += $rg["cantidad"];
                                $subt += $rg["subtotal"];
                                $iva += $rg["importeIva"];
                                $ieps += $rg["ieps"];
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
                                <td><?= number_format($vol,0) ?></td>
                                <td><?= number_format($subt, 2) ?></td>
                                <td><?= number_format($iva,2) ?></td>
                                <td><?= number_format($ieps, 2) ?></td>
                                <td><?= number_format($imp,2) ?></td>
                                
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
                                <?php
                                $data = array("Nombre" => "Resumen mensual", "Reporte" => 203, "mes" => $mes, "Anio" => $anio);
//                                echo print_r($data,true);
                                ?>
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

