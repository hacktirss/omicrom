<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$serie = $request->getAttribute("serie");
$mes = $request->getAttribute("mes");
$anio = $request->getAttribute("Anio");
$piezas = $request->getAttribute("piezas");
$sigmes = $mes + 1;

$selectAditivos = "SELECT vta.id,vta.fecha,clave,descripcion,cantidad as piezas,total as importev
                        ,vta.uuid
    FROM vtaditivos vta LEFT JOIN cli ON vta.cliente=cli.id 
         WHERE year(DATE(vta.fecha)) = $anio and month(DATE(vta.fecha)) = $mes 
        AND vta.tm = 'C'
        order by vta.clave";

$selectAditivosFac = "SELECT fc.serie,fc.folio,fc.uuid,fc.fecha as fechaf,inv.id ,inv.descripcion,vta.fecha,round(fcd.cantidad,0) as cantidad,round(fcd.importe) as total
                                FROM fc inner join fcd
                                ON fc.id = fcd.id inner join inv
                                on fcd.producto = inv.id inner join vtaditivos vta
                                on vta.id = fcd.ticket
                                WHERE year(DATE(fc.fecha)) = $anio and month(DATE(fc.fecha)) = $mes
                                and fc.uuid != '-----' 
                                and status = 1
                                and producto > 5
                                and fcd.ticket  >= 0	
                                order by inv.id;";

//echo $selectFacturas;
$registros = utils\IConnection::getRowsFromQuery($selectAditivos);
$registros1 = utils\IConnection::getRowsFromQuery($selectAditivosFac);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <title><?= $Gcia ?></title>
        <script type="text/javascript" src="js/export_.js"></script>
        <script>
            function ExportToExcel(type, fn, dl) {
                var elt = document.getElementById('Reportes');

                var wb = XLSX.utils.table_to_book(elt, {sheet: "vtaditivosV"});

                return dl ?
                        XLSX.write(wb, {bookType: type, bookSST: true, type: 'base64'}) :
                        XLSX.writeFile(wb, fn || ('ReporteAditivos.' + (type || 'xlsx')));
            }
            ;
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
                            <td colspan="12">Detallado de ventas de aditivos durante el mes de <?php $me ?> </td>
                        </tr>
                        <tr>
                            <td>Id</td>
                            <td>Fecha</td>
                            <td>clave</td>
                            <td>Descripcion</td>
                            <td>UUID</td>
                            <td>Piezas</td>
                            <td>Importe</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $imp = $piezas = 0;
                        foreach ($registros as $rg) {
                            ?>
                            <tr>

                                <td><?= $rg["id"] ?></td>
                                <td><?= $rg["fecha"] ?></td>
                                <td><?= $rg["clave"] ?></td>
                                <td><?= $rg["descripcion"] ?></td>
                                <td><?= $rg["uuid"] ?></td>
                                <td><?= $rg["piezas"] ?></td>
                                <td><?= $rg["importev"] ?></td>
                            </tr>
                            <?php
                            $imp += $rg["importev"];
                            $piezas += $rg["piezas"];
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>Total</td>
                            <td><?= number_format($piezas, 0) ?></td>
                            <td><?= number_format($imp, 2) ?></td>

                        </tr>
                    </tfoot>
                </table>

                <table id="F" aria-hidden="true">
                    <thead>
                        <tr class="titulo">
                            <td colspan="12">Detallado de facturado de ventas de aditivos</td>
                        </tr>
                        <tr>
                            <td>Serie</td>
                            <td>Folio</td>
                            <td>UUID</td>
                            <td>Fecha_Factura</td>
                            <td>Descripcion</td>
                            <td>Fecha_venta</td>
                            <td>Cantidad</td>
                            <td>Total</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $imp = $cantidad = 0;
                        foreach ($registros1 as $rg) {
                            ?>
                            <tr>
                                <td><?= $rg["serie"] ?> </td>
                                <td><?= $rg["folio"] ?> </td>
                                <td><?= $rg["uuid"] ?> </td>
                                <td><?= $rg["fechaf"] ?> </td>
                                <td><?= $rg["descripcion"] ?> </td>
                                <td><?= $rg["fecha"] ?> </td>
                                <td><?= $rg["cantidad"] ?> </td>
                                <td><?= number_format($rg["total"], 2) ?> </td>    
                            </tr>
                            <?php
                            $imp += $rg["total"];
                            $cantidad += $rg["cantidad"];
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
                            <td>Total</td>
                            <td><?= number_format($cantidad, 2) ?></td>
                            <td><?= number_format($imp, 2) ?></td>
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

