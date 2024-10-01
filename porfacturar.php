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
select pf.ticket,com.descripcion,cli.nombre ,pf.uuid,pf.precio,
	 round(pf.volumen,2) volumen,
	round(((pf.importe-(pf.volumen * pf.ieps))/(1+pf.iva)),2) subtotal,
    round(((pf.importe-(pf.volumen * pf.ieps))/(1+pf.iva)) * pf.iva,2) importeIva,
	round((pf.volumen * pf.ieps),2) ieps,
    round(pf.importe,2) importe
 from (
		select id ticket,producto, precio,(importe/precio) volumen,importe,cliente,uuid,iva,ieps
		from rm where uuid = '-----' and fecha_venta like '".$anio."".$mes."%' and tipo_venta ='D' and importe > 0
    ) pf inner join cli on pf.cliente = cli.id
		inner join com on pf.producto = com.clavei";

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

$Titulo = "Detallado por facturar del mes: ".$mes." del año".$anio;

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
        <script type="text/javascript" src="https://unpkg.com/xlsx@0.15.1/dist/xlsx.full.min.js"></script>
        <script>
        function ExportToExcel(type, fn, dl) {
       var elt = document.getElementById('Reportes');
       
       var wb = XLSX.utils.table_to_book(elt, { sheet: "vtaditivosV" });
       
       return dl ?
         XLSX.write(wb, { bookType: type, bookSST: true, type: 'base64' }):
         
         XLSX.writeFile(wb, fn || ('ReporteAditivos.' + (type || 'xlsx')));
    };
    </script>
    </head>

    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo); ?>
            <div id="Reportes">
                 <table aria-hidden="true">
                    <thead>
                        <tr class="titulo">
                            <td colspan="10">Detallado de Facturas</td>
                        </tr>
                        <tr>
                            <td>Ticket</td>
                            <td>Producto</td>
                            <td>Nombre</td>
                            <td>UUID</td>
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
                        $vol = $imp = $iva= $ieps= $total= 0;
                        foreach ($registros as $rg) {
                            ?>
                            <tr>

                                <td><?= $rg["ticket"] ?></td>
                                <td><?= $rg["descripcion"]  ?></td>
                                <td><?= $rg["nombre"] ?></td>
                                <td><?= $rg["uuid"] ?></td>
                                <td><?= number_format($rg["precio"],2) ?></td>
                                <td><?= number_format($rg["volumen"],2) ?></td>
                                <td><?= number_format($rg["subtotal"], 2) ?></td>
                                <td><?= number_format($rg["importeIva"], 2) ?></td>
                                <td><?= number_format($rg["ieps"],2) ?></td>
                                <td><?= number_format($rg["importe"],2) ?></td>
                            </tr>
                            <?php
                            $vol += $rg["volumen"];
                            $imp += $rg["subtotal"];
                            $iva += $rg["importeIva"];
                            $ieps += $rg["ieps"];
                            $total += $rg["importe"];
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
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
                                <span><button onclick="ExportToExcel('xlsx')"><i class="icon fa fa-lg fa-bold fa-file-excel-o" aria-hidden="true"></i></button></span>
                               
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
            <?php topePagina(); ?>
        </div>
    </body>
</html>

