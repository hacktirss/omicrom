<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
include_once ("services/ReportesVentasService.php");
$serie = $request->getAttribute("serie");
$mes = $Mes;
$anio = $Anio;
$piezas = $request->getAttribute("piezas");
$sigmes = $mes + 1;
//var_dump($request);
//var_dump($sigmes);
if ($request->getAttribute("TipoS") === "Cnt") {
    $selectFacturas = "SELECT rm.importe importe, rm.producto,rm.volumen,rm.importe,fc.serie,rm.id
                            FROM rm left join fcd on fcd.ticket=rm.id left join fc on fcd.id=fc.id
                            WHERE month(date(rm.fecha_venta)) = $mes AND YEAR(date(rm.fecha_venta)) = $anio
                            AND rm.uuid <> '-----' AND rm.tipo_venta='D' AND fc.status = 1 AND fcd.producto < 5 order by serie;";
} else {
    $selectFacturas = "select * from (SELECT count(*) movimientos,ROUND(rm.importe,2) importe,rm.id,fc.serie,fc.folio FROM rm left join fcd on "
            . "fcd.ticket=rm.id left join fc on fcd.id=fc.id WHERE month(date(rm.fecha_venta)) = $mes AND YEAR(date(rm.fecha_venta)) = $anio "
            . "AND rm.uuid <> '-----' AND rm.tipo_venta='D' AND fc.status = 1 and fcd.producto < 5 group by fcd.ticket) a WHERE movimientos > 1";
}
$fechas = array("01" => "Enero", "02" => "Febrero", "03" => "Marzo", "04" => "Abril",
    "05" => "Mayo", "06" => "Junio", "07" => "Julio", "08" => "Agosto", "09" => "Septiembre",
    "10" => "Octubre", "11" => "Noviembre", "12" => "Diciembre");
//echo $selectFacturas;
$registros = utils\IConnection::getRowsFromQuery($selectFacturas);

$Titulo = "Detallado en el siguiente mes del mes: " . $mes . " del año" . $anio;

$Id = 200;
$data = array("Nombre" => $Titulo, "Reporte" => $Id,
    "mes" => $mes, "anio" => $anio, "mesig" => $sigmes
);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <title><?= $Gcia ?></title>
    </head>
    <script>
        $(document).ready(function () {
            $("#Anio").val("<?= $anio ?>");
            $("#Mes").val("<?= $mes ?>");
        });
    </script>
    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo); ?>
            <div id="Reportes">
                <?php
                if ($request->getAttribute("TipoS") === "Cnt") {
                    ?>
                    <table aria-hidden="true">
                        <thead>
                            <tr class="titulo">
                                <td colspan="12">Detallado de Facturas</td>
                            </tr>
                            <tr>
                                <td>id</td>
                                <td>volumen</td>
                                <td>importe</td>
                                <td>Serie</td>  
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $imp = 0;
                            foreach ($registros as $rg) {
                                ?>
                                <tr>

                                    <td><?= $rg["id"] ?></td>
                                    <td><?= $rg["volumen"] ?></td>
                                    <td><?= $rg["importe"] ?></td>
                                    <td><?= $rg["serie"] ?></td>
                                </tr>
                                <?php
                                $imp += $rg["importe"];
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>Total</td>
                                <td><?= number_format($imp, 2) ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    <?php
                } else {
                    ?>
                    <table aria-hidden="true">
                        <thead>
                            <tr class="titulo">
                                <td colspan="12">Detallado de tickets facturados dos veces</td>
                            </tr>
                            <tr>
                                <td>Folio</td>
                                <td>Serie</td>
                                <td>Id Ticket</td>
                                <td>Importe</td>
                                <td>Movimientos</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $imp = 0;
                            foreach ($registros as $rg) {
                                ?>
                                <tr>
                                    <td style="text-align: right"><?= $rg["folio"] ?></td>
                                    <td><?= $rg["serie"] ?></td>
                                    <td style="text-align: right"><?= $rg["id"] ?></td>
                                    <td style="text-align: right;"><?= number_format($rg["importe"],2) ?></td>
                                    <td style="text-align: right"><?= $rg["movimientos"] ?></td>
                                </tr>
                                <?php
                                $imp += $rg["importe"];
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3">Total</td>
                                <td><?= number_format($imp, 2) ?></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    <?php
                }
                $MesNum = $mes == 12 ? 0 : $mes;
                $MesNumAdd = $MesNum + 1;
                $SqlDiscrep = "SELECT fc.id,CONCAT(fc.serie,' - ',fc.folio) folio,fc.total,IFNULL(sum(fcd.importe),0) fcd
                    , (fc.total - IFNULL(sum(fcd.importe),0)) diferencia,IFNULL((SELECT total FROM nc WHERE id = fc.relacioncfdi),0) totalNc
                    FROM fc LEFT JOIN fcd ON fc.id=fcd.id 
                    WHERE  YEAR(fc.fecha) = '$anio' 
                    AND MONTH(fc.fecha) = '$mes' AND fc.uuid != '-----' AND fc.status=1  GROUP BY fc.id
                    having ABS((fc.total - IFNULL(sum(fcd.importe),0))) > 1";
                $utilDisc = utils\IConnection::getRowsFromQuery($SqlDiscrep);
                ?>

                <table aria-hidden="true">
                    <thead>
                        <tr class="titulo">
                            <td colspan="12">Discrepancias entre factura y su detalle</td>
                        </tr>
                        <tr>
                            <td>Id</td>
                            <td>Folio</td>
                            <td>Factura</td>
                            <td>Detalle</td>
                            <td>Diferencia</td>
                            <td>N.C.</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $Dif = 0;
                        foreach ($utilDisc as $rg) {
                            ?>
                            <tr>
                                <td><?= $rg["id"] ?></td>
                                <td><?= $rg["folio"] ?></td>
                                <td style="text-align: right;"><?= number_format($rg["total"], 2) ?></td>
                                <td style="text-align: right;"><?= number_format($rg["fcd"], 2) ?></td>
                                <td style="text-align: right;"><?= number_format($rg["diferencia"], 2) ?></td>
                                <td style="text-align: right;"><?= number_format($rg["totalNc"], 2) ?></td>
                            </tr>
                            <?php
                            $Dif += $rg["diferencia"];
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4">Total</td>
                            <td><?= number_format($Dif, 2) ?></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>

                <?php
                
                $SqlDirmfc = "SELECT fc.serie,fc.folio,fc.fecha fecha_factura ,date(rm.fecha_venta) fecha_venta, 
                                inv.descripcion producto,fcd.ticket,fcd.importe importe_facturado,rm.id,rm.importe importe_ticket,
                                round((fcd.importe - rm.importe),2) diferencia
                                FROM fc left join fcd on fc.id = fcd.id
                                inner join rm on rm.id = fcd.ticket
                                left join inv on fcd.producto= inv.id
                                WHERE YEAR(fc.fecha) = '$anio' AND MONTH(fc.fecha) = '$mes' and fc.uuid != '-----'   and status = 1 
                                and fcd.producto < 5
                                and round((fcd.importe - rm.importe),2) <> 0 order by round((fcd.importe - rm.importe),2)";
                $utilDifrmfc = utils\IConnection::getRowsFromQuery($SqlDirmfc);
                ?>

                <table aria-hidden="true">
                    <thead>
                        <tr class="titulo">
                            <td colspan="12">Diferencia entre importe facturado e importe de ticket </td>
                        </tr>
                        <tr>
                            <td>Serie</td>
                            <td>Folio</td>
                            <td>Fecha Factura</td>
                            <td>Fecha Venta</td>
                            <td>Producto</td>
                            <td>Ticket</td>
                            <td>Importe Factura</td>
                            <td>Importe Ticket</td>
                            <td>Diferecia</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $Dif = 0;
                        foreach ($utilDifrmfc as $rg) {
                            ?>
                            <tr>
                                <td><?= $rg["serie"] ?></td>
                                <td><?= $rg["folio"] ?></td>
                                <td><?= $rg["fecha_factura"] ?></td>
                                <td><?= $rg["fecha_venta"] ?></td>
                                <td><?= $rg["producto"] ?></td>
                                <td><?= $rg["ticket"] ?></td>
                                <td style="text-align: right;"><?= number_format($rg["importe_facturado"], 2) ?></td>
                                <td style="text-align: right;"><?= number_format($rg["importe_ticket"], 2) ?></td>
                                <td style="text-align: right;"><?= number_format($rg["diferencia"], 2) ?></td>
                            </tr>
                            <?php
                            $Dif += $rg["diferencia"];
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="8">Total</td>
                            <td><?= number_format($Dif, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
                <?php
                $AnalisisxMes = "select
                    f.serie
                    , month(f.fecha_venta) mes_venta
                    , descripcion
                    , round(sum(f.cantidad),2) volumen
                    , ROUND( SUM(ROUND(cantidad * precio,3)), 2 ) subtotal 
                    , ROUND( SUM(ROUND(cantidad * precio * iva,3)), 2 ) tax_iva 
                    , ROUND( SUM(round(cantidad * ieps,4)), 2 ) tax_ieps 
                    , round(sum(f.importe),2) importe
                    , 'COMBUSTIBLE'  tps
                    FROM  (
                    SELECT 
                    fc.id
                    , fc.serie
                    , fc.folio
                    , fcd.cantidad
                    , fcd.precio    
                    , fcd.iva
                    , fcd.ieps
                    , fcd.importe
                    , rm.fecha_venta
                    , inv.descripcion
                    FROM fc inner join fcd
                    ON fc.id = fcd.id 
                    INNER JOIN rm 
                    on fcd.ticket = rm.id
                    inner join inv
                    on fcd.producto = inv.id
                    WHERE YEAR(fc.fecha) = $anio
                    AND MONTH(fc.fecha) = $mes 
                    and fc.uuid != '-----' 
                    and status = 1
                    and fcd.producto < 5
                    ) f
                    group by f.serie , month(f.fecha_venta), descripcion
                    union all
                    select
                    f.serie
                    , month(f.fecha_venta) mes_venta
                    , descripcion
                    , round(sum(f.cantidad),2) volumen
                    , ROUND( SUM(ROUND(cantidad * preciob/(1+iva),3)), 2 ) subtotal 
                    , ROUND( SUM(ROUND(cantidad * preciob/(1+iva),3)*iva), 2 )  tax_iva 
                    , 0 tax_ieps 
                    , round(sum(f.importe),2) importe
                    , 'ADITIVOS'
                    FROM  (
                    SELECT 
                    fc.id
                    , fc.serie
                    , fc.folio
                    , fcd.cantidad
                    , fcd.preciob
                    , fcd.iva
                    , fcd.ieps
                    , fcd.importe
                    , date(vtaditivos.fecha) fecha_venta
                    , descripcion
                    FROM fc inner join fcd
                    ON fc.id = fcd.id 
                    INNER JOIN vtaditivos 
                    on fcd.ticket = vtaditivos.id
                    WHERE YEAR(fc.fecha) = $anio 
                    AND MONTH(fc.fecha) = $mes
                    and fc.uuid != '-----' 
                    and status = 1
                    and producto > 5
                    ) f
                    group by f.serie , month(f.fecha_venta), descripcion
                    union all
                    select
                    f.serie
                    , month(f.fecha_venta) mes_venta
                    , descripcion
                    , round(sum(f.cantidad),2) volumen
                    , ROUND( SUM(ROUND(cantidad * preciob/(1+iva),3)), 2 ) subtotal 
                    , ROUND( SUM(ROUND(cantidad * preciob/(1+iva),3)*iva), 2 )  tax_iva 
                    , 0 tax_ieps 
                    , round(sum(f.importe),2) importe
                    , 'ADITIVOS_MANUEALES'
                    FROM  (
                    SELECT 
                    fc.id
                    , fc.serie
                    , fc.folio
                    , fcd.cantidad
                    , fcd.preciob
                    , fcd.iva
                    , fcd.ieps
                    , fcd.importe
                    , date(fc.fecha) fecha_venta
                    , inv.descripcion
                    FROM fc inner join fcd
                    ON fc.id = fcd.id inner join inv
                    on fcd.producto = inv.id
                    WHERE YEAR(fc.fecha) = $anio 
                    AND MONTH(fc.fecha) = $mes
                    and fc.uuid != '-----' 
                    and status = 1
                    and producto > 5
                    and fcd.ticket = 0
                    ) f
                    group by f.serie , month(f.fecha_venta),descripcion
                    ";
//                echo $AnalisisxMes;
                $registros1 = utils\IConnection::getRowsFromQuery($AnalisisxMes);
                ?>

                <table aria-hidden="true">
                    <thead>
                        <tr class="titulo">
                            <td colspan="12">Detalle</td>
                        </tr>
                        <tr>
                            <td>Serie</td>
                            <td>Mes</td>
                            <td>Descripción</td>
                            <td>Litros</td>
                            <td>Importe</td>
                            <td>Iva</td>
                            <td>IEPS</td>
                            <td>Total</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $imp = 0;
                        foreach ($registros1 as $rg) {
                            ?>
                            <tr>

                                <td><?= $rg["serie"] ?> -<?= $rg["tps"] ?></td>
                                <td><?= $fechas[$rg["mes_venta"]] ?></td>
                                <td><?= $rg["descripcion"] ?></td>
                                <td><?= $rg["volumen"] ?></td>
                                <td><?= $rg["subtotal"] ?></td>
                                <td><?= $rg["tax_iva"] ?></td>
                                <td><?= $rg["tax_ieps"] ?></td>
                                <td><?= $rg["importe"] ?></td>
                            </tr>
                            <?php
                            $subT += $rg["subtotal"];
                            $imp += $rg["importe"];
                            $tax_ieps += $rg["tax_ieps"];
                            $tax_iva += $rg["tax_iva"];
                            $cnt += $rg["volumen"];
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>Total</td>
                            <td></td>
                            <td></td>
                            <td><?= number_format($cnt, 2) ?></td>
                            <td><?= number_format($subT, 2) ?></td>
                            <td><?= number_format($tax_iva, 2) ?></td>
                            <td><?= number_format($tax_ieps, 2) ?></td>
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
                                Periodo Año: 
                                <select name="Anio" id="Anio">
                                    <?php
                                    for ($e = 2018; $e <= date("Y"); $e++) {
                                        ?>
                                        <option value="<?= $e ?>"><?= $e ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                                Meses:
                                <select name="Mes" id="Mes">
                                    <option value="01">Enero</option>
                                    <option value="02">Febrero</option>
                                    <option value="03">Marzo</option>
                                    <option value="04">Abril</option>
                                    <option value="05">Mayo</option>
                                    <option value="06">Junio</option>
                                    <option value="07">Julio</option>
                                    <option value="08">Agosto</option>
                                    <option value="09">Septiembre</option>
                                    <option value="10">Octubre</option>
                                    <option value="11">Noviembre</option>
                                    <option value="12">Diciembre</option>
                                </select>
                            </td>
                            <td>
                                <input type="submit" name="Enviar" value="Enviar">
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

