<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

$Titulo = "Pagos Realizados del $FechaI al $FechaF";

$selectPagos = "
select cli.id no_cliente, cli.nombre,p.id pago,p.serie,p.id, p.fecha,p.concepto,p.importe, 
	case 
    WHEN p.status_pago = 1 THEN 
    'REGISTRO PAGO'
    WHEN p.status_pago = 2 THEN     
    'SALDO LIBERADO' 
    WHEN p.status_pago = 3 THEN     
    'RECIBO ANTICIPO' 
    WHEN p.status_pago = 4 THEN     
    'FACTURA CONSUMOS'  
    WHEN p.status_pago = 5 THEN     
    'ANTICIPO CERRADO' 
    END
    statusP,
    p.uuid uuid_pago,
    ifnull(fc.id,0) factura,ifnull(fc.serie,0) serie,ifnull(fc.folio,0)folio,ifnull(fc.cantidad,0) cantidad,ifnull(pe.imp ,0) subtotalf,
    ifnull(pe.iva,0) ivaf,ifnull(pe.ieps,0) iepsf,
    ifnull(pe.importe,0)abonofact,ifnull(fc.total ,0) totalfact,fc.relacioncfdi,
    case
    WHEN fc.status = 0 THEN 'Fact Abierta'
    WHEN fc.status = 1 THEN 'Fact Timbrada'
    WHEN fc.status = 2 THEN 'Fact Cancelada'
    WHEN fc.status = 3 THEN 'Fact Cancelada'
    END statusfc,
    ifnull(fc.uuid,'-----') uuid_factura,ifnull(fc.relacioncfdi,0) relacioncfdifact,ifnull(fc.fecha,'0000-00-00')fechaf,
    ifnull(fc.stCancelacion,0) stCancelacion,
    ifnull(nc.id,0) nota,ifnull(nc.cliente,0) cliente,ifnull(nc.total,0) totalnota,ifnull(nc.uuid,'') uuid_nota ,ifnull(nc.observaciones,'')observaciones,
    ifnull(nc.relacioncfdi,0) relacioncfdinc
    from pagos p
    left join pagose pe on p.id = pe.id
	left join fc on pe.factura = fc.id
    left join nc on nc.factura = p.id 
    inner join cli on p.cliente = cli.id
    where p.statusCFDI < 3 and date(p.fecha) BETWEEN " . str_replace("-", "", $FechaI) . " and " . str_replace("-", "", $FechaF) . "  and
    cli.tipodepago = 'Credito'
union all
	select cli.id no_cliente, cli.nombre,p.id pago,p.serie,p.id, p.fecha,p.concepto,p.importe,
    case 
    WHEN p.status_pago = 1 THEN 
    'REGISTRO PAGO'
    WHEN p.status_pago = 2 THEN     
    'SALDO LIBERADO' 
    WHEN p.status_pago = 3 THEN     
    'RECIBO ANTICIPO' 
    WHEN p.status_pago = 4 THEN     
    'FACTURA CONSUMOS'  
    WHEN p.status_pago = 5 THEN     
    'ANTICIPO CERRADO' 
    END
    statusP,
    p.uuid uuid_pago,
    ifnull(fc.id,0) factura,ifnull(fc.serie,0) serie,ifnull(fc.folio,0)folio,ifnull(fc.cantidad,0) cantidad,ifnull(fc.importe ,0) subtotalf,
    ifnull(fc.iva ,0) ivaf,ifnull(fc.total ,0) iepsf,
    ifnull(fc.importe,0)abonofact,ifnull(fc.total ,0) totalfact,fc.relacioncfdi,
    case
    WHEN fc.status = 0 THEN 'Fact Abierta'
    WHEN fc.status = 1 THEN 'Fact Timbrada'
    WHEN fc.status = 2 THEN 'Fact Cancelada'
    WHEN fc.status = 3 THEN 'Fact Cancelada'
    END statusfc,
    ifnull(fc.uuid,'-----') uuid_factura,ifnull(fc.relacioncfdi,0) relacioncfdifact,ifnull(fc.fecha,'0000-00-00')fechaf,
    ifnull(fc.stCancelacion,0) stCancelacion,
    ifnull(nc.id,0) nota,ifnull(nc.cliente,0) cliente,ifnull(nc.total,0) totalnota,ifnull(nc.uuid,'') uuid_nota ,ifnull(nc.observaciones,'')observaciones,
    ifnull(nc.relacioncfdi,0) relacioncfdinc
    from pagos p
	left join fc on fc.relacioncfdi = p.id
    left join nc on nc.factura = p.id 
    inner join cli on p.cliente = cli.id
    where p.statusCFDI < 3 and date(p.fecha) BETWEEN " . str_replace("-", "", $FechaI) . " and " . str_replace("-", "", $FechaF) . "  and cli.tipodepago = 'Prepago';       
    ";
//echo $selectPagos;
$registros = utils\IConnection::getRowsFromQuery($selectPagos);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css" type="text/css">
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css" type="text/css">
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#FechaI").val("<?= $FechaI ?>").attr("size", "10");
                $("#FechaF").val("<?= $FechaF ?>").attr("size", "10");
                $("#cFechaI").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaI")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#cFechaF").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaF")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $('#RpAceites').DataTable({
                    dom: 'Bfrtip',
                    paging: false,
                    buttons: [
                        'copyHtml5',
                        'excelHtml5',
                        'csvHtml5',
                        'pdfHtml5'
                    ]
                });
            });
        </script>
    </head>

    <body>

        <div id="container">
            <?php nuevoEncabezado($Titulo); ?>

            <div id="Reportes">
                    <table id="RpAceites" aria-hidden="true" class="display" style="width: 100%;">
                        <thead>
                            <tr class = "titulo">
                                <td colspan="9" style="text-align: center;">Pago</td>
                                <td colspan="12" style="text-align: center;">Factura</td>
                                <td colspan="6" style="text-align: center;">Nota de Credito</td>
                            </tr>
                            <tr>
                                <td>No.cliente </td>
                                <td>Nombre </td>
                                <td>Serie Pago</td>
                                <td>Folio Pago</td>  
                                <td>UUID_pago </td>  
                                <td>Fecha</td>
                                <td>Concepto </td>
                                <td>Importe </td>
                                <td>Status </td>

                                <td>Factura </td>
                                <td>Serie Factura</td>
                                <td>Folio Factura</td>
                                <td>Fecha </td>
                                <td>Subtotal </td>
                                <td>Iva </td>
                                <td>Ieps </td>
                                <td>Total </td>
                                <td>Status </td>
                                <td>UUID factura </td>
                                <td>Relacioncfdi</td>

                                <td>Nota </td>
                                <td>Cliente </td>
                                <td>Total </td>
                                <td>UUID </td>
                                <td>Observaciones </td>
                                <td>Relacioncfdi </td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($registros as $rg) {
                                echo "<tr>";
                                echo "<td>" . $rg["no_cliente"] . "</td>";
                                echo "<td>" . $rg["nombre"] . "</td>";
                                echo "<td>" . $rg["serie"] . "</td>";
                                echo "<td>" . $rg["id"] . "</td>";
                                echo "<td>" . $rg["uuid_pago"] . "</td>";
                                echo "<td>" . $rg["fecha"] . "</td>";
                                echo "<td>" . $rg["concepto"] . "</td>";
                                echo "<td>" . $rg["importe"] . "</td>";
                                echo "<td>" . $rg["statusP"] . "</td>";
                                echo "<td>" . $rg["factura"] . "</td>";
                                echo "<td>" . $rg["serie"] . "</td>";
                                echo "<td>" . $rg["folio"] . "</td>";
                                echo "<td>" . $rg["fechaf"] . "</td>";
                                echo "<td>" . $rg["subtotalf"] . "</td>";
                                echo "<td>" . $rg["ivaf"] . "</td>";
                                echo "<td>" . $rg["iepsf"] . "</td>";
                                echo "<td>" . $rg["abonofact"] . "</td>";
                                echo "<td>" . $rg["statusfc"] . "</td>";
                                echo "<td>" . $rg["uuid_factura"] . "</td>";
                                echo "<td>" . $rg["relacioncfdifact"] . "</td>";
                                echo "<td>" . $rg["nota"] . "</td>";
                                echo "<td>" . $rg["cliente"] . "</td>";
                                echo "<td>" . $rg["totalnota"] . "</td>";
                                echo "<td>" . $rg["uuid_nota"] . "</td>";
                                echo "<td>" . $rg["observaciones"] . "</td>";
                                echo "<td>" . $rg["relacioncfdinc"] . "</td>";
                            }
                            ?>
                        </tbody>
                    </table>
            </div> 
        </div>
    </body>
</html>

