<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("importeletras.php");
include_once("data/VentaAditivosDAO.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$usuarioSesion = getSessionUsuario();

require "./services/ReportesVentasService.php";
$Rgeneral = new VentaAditivosDAO();

if ($request->hasAttribute("Boton")) {
    if ($SCliente !== "") {
        $Cc = explode("|", $SCliente);
        $ClienteBusca = $Cc[0];
        $NombreCli = $Cc[2];
    }
    if ($request->hasAttribute("Fecha")) {

        $Titulo = "Vendido y facturado del $Fecha " . $NombreCli;
        $op = 1;
        $cSql = consulta($Fecha, $Fecha, $ClienteBusca);
        $cSqlFAC = factvta($FechaI, $FechaF, $ClienteBusca);
        $Resumen = $Rgeneral->getProductos($Fecha, $Fecha, $ClienteBusca);
    } elseif ($request->hasAttribute("FechaI")) {


        $Titulo = "Vendido y facturado del $FechaI al  $FechaF   " . $NombreCli;
        $op = 2;
        $cSql = consulta($FechaI, $FechaF, $ClienteBusca);
        $cSqlAF = aditivoAsig($FechaI, $FechaF, $ClienteBusca);
        $cSqlFAC = factvta($FechaI, $FechaF, $ClienteBusca);
        $cSqlAl = aditivoAsig($FechaI, $FechaF, $ClienteBusca);
        $Resumen = $Rgeneral->getProductos($FechaI, $FechaF, $ClienteBusca);
    }
}

function consulta($FechaI, $FechaF, $Cli = 0) {
    $SqlAddRm = $SqlAddVt = "";
    if ($Cli > 0) {
        $SqlAddRm = "AND rm.cliente = $Cli ";
        $SqlAddVt = " AND vta.cliente = $Cli ";
    }
    $cSql = "SELECT * from (
        Select p.clave,p.producto,0 as piezas,
          round(sum(p.importe),2) importe,
          round(SUM((p.volumen)*(p.preciouu)),2) AS monto,
          round(sum((p.volumen)*(p.preciouu) * (p.iva)),2) AS iva,
          round(sum((p.volumen)*(p.ieps)),2) AS ieps
          from 
           (
            select com.clave,com.descripcion as producto,(rm.importe)/(rm.precio) volumen,rm.ieps,rm.iva,(rm.precio-rm.ieps)/(1+rm.iva) preciouu,rm.importe
           FROM rm LEFT JOIN cli ON rm.cliente=cli.id
                   LEFT JOIN com ON com.clavei = rm.producto 
           WHERE rm.fecha_venta BETWEEN " . str_replace("-", "", $FechaI) . " AND  " . str_replace("-", "", $FechaF) . " AND rm.tipo_venta='D' $SqlAddRm
           ) p 
           GROUP BY p.producto
           
       union all 
       
        Select a.clave,a.descripcion as producto,a.piezas,a.cantidad as importe,(a.cantidad/(1+a.iva)) as monto, (a.cantidad/(1+a.iva)*a.iva) as iva , 0
           from (SELECT vta.clave, descripcion, sum(total) as cantidad, iva,SUM(cantidad) as piezas
           FROM vtaditivos vta LEFT JOIN cli ON vta.cliente=cli.id 
           WHERE DATE(vta.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')  
           AND vta.tm = 'C' $SqlAddVt
           group by vta.descripcion
           )a order by cast(clave as char )
       ) vengeneral
        LEFT JOIN (
            Select p.clave, p.producto,0 as piezas, 
		    round(sum(p.importe),2) importe,
          round(SUM((p.volumen)*(p.preciouu)),2) AS monto,
          round(sum((p.volumen)*(p.preciouu) * (p.iva)),2) AS iva,
          round(sum((p.volumen)*(p.ieps)),2) AS ieps
          from 
           (
           select com.clave,com.descripcion as producto,(rm.importe)/(rm.precio) volumen,rm.ieps,rm.iva,(rm.precio-rm.ieps)/(1+rm.iva) preciouu,rm.importe 
           FROM rm LEFT JOIN cli ON rm.cliente=cli.id
                   LEFT JOIN com ON com.clavei = rm.producto
           WHERE rm.fecha_venta BETWEEN " . str_replace("-", "", $FechaI) . " AND  " . str_replace("-", "", $FechaF) . "  AND rm.uuid <> '-----' AND rm.tipo_venta='D' $SqlAddRm
           
           )p
           GROUP BY p.producto
           
           union all
           
           Select a.clave, a.descripcion as producto,a.piezas,IFNULL(a.cantidad,0.00) as importe,(a.cantidad/(1+a.iva)) as monto, (a.cantidad/(1+a.iva)*a.iva) as iva,0 
           from (SELECT vta.clave, descripcion, IFNULL(sum(total),0) as cantidad,iva,SUM(cantidad) as piezas
           FROM vtaditivos vta LEFT JOIN cli ON vta.cliente=cli.id 
           WHERE DATE(vta.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')  
           AND vta.uuid <> '-----' AND vta.tm = 'C' $SqlAddVt
           group by vta.descripcion
           )a order by cast(clave as char )
       ) facturado on facturado.producto = vengeneral.producto order by cast(vengeneral.clave as decimal) asc;
    ";
    return $cSql;
}

function factvta($FechaI, $FechaF, $Cliente = 0) {
    if ($Cliente > 0) {
        $SqlAddVt = " AND vta.cliente = $Cliente ";
    }
    $cSqlA = "
    SELECT  vta.clave, vta.descripcion,vta.cantidad,abs(inv.precio) precio,vta.costo,sum(IF(vta.uuid != '-----',vta.total,0)) totalFacturado,sum(vta.cantidad) Piezas,
    ifnull(sum(case when vta.uuid != '-----' and vta.cliente = 0 then vta.cantidad end),0) factContado,
    ifnull(sum(case when vta.uuid != '-----' and vta.cliente > 0 then vta.cantidad end),0) factCliente,
     ifnull(sum(case when vta.uuid = '-----'  then vta.cantidad end),0)  por_facturar,sum(IF(vta.uuid = '-----',vta.total,0)) totalsinFacturado ,sum(vta.total) Grantotal 
      FROM vtaditivos vta LEFT JOIN cli ON vta.cliente=cli.id
           inner join inv on vta.clave = inv.id
      WHERE DATE(vta.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')   $SqlAddVt
      AND vta.tm = 'C' AND vta.cantidad > 0
      group by descripcion  order by cast(clave as decimal ) asc
                ";
    return $cSqlA;
}

function aditivoAsig($FechaI, $FechaF, $Cliente = 0) {
    if ($Cliente > 0) {
        $SqlAddVt = " AND vta.cliente = $Cliente ";
    }
    $cSqlA = "SELECT vta.id,fecha,vta.uuid,descripcion,cantidad,cli.nombre,cli.tipodepago
    FROM vtaditivos vta LEFT JOIN cli ON vta.cliente=cli.id 
        WHERE DATE(vta.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')   $SqlAddVt
        AND vta.tm = 'C' AND cantidad > 0 AND cliente != 'Contado' order by cli.tipodepago,fecha,nombre;
                ";
    return $cSqlA;
}

$nTran = 0;
$nFac = 0;
$nTran = 0;
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#Fecha").attr("size", "10");
                $("#FechaF").attr("size", "10");
                $("#FechaI").attr("size", "10");
                $("#Detallado").val("<?= $Detallado ?>");

                if ("<?= $FechaI ?>" !== "") {
                    $("#Fecha").prop("disabled", true);
                    $("#FechaF").val("<?= $FechaF ?>");
                    $("#FechaI").val("<?= $FechaI ?>");
                } else if ("<?= $Fecha ?>" !== "") {
                    $("#Fecha").prop("disabled", false);
                    $("#FechaI").prop("disabled", true);
                    $("#FechaF").prop("disabled", true);
                    $("#Fecha").val("<?= $Fecha ?>");
                }

                $("#Fecha").focus(function () {
                    $("#FechaI").val("");
                    $("#FechaF").val("");
                });

                $("#cFechaI").css("cursor", "hand").click(function () {
                    $("#FechaI").val("<?= $FechaI ?>");
                    $("#FechaF").val("<?= $FechaF ?>");
                    $("#FechaI").prop("disabled", false);
                    $("#FechaF").prop("disabled", false);
                    displayCalendar($("#FechaI")[0], "yyyy-mm-dd", $(this)[0]);
                    $("#Fecha").val("");
                    $("#Fecha").prop("disabled", true);
                });

                $("#cFechaF").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaF")[0], "yyyy-mm-dd", $(this)[0]);
                });

                $("#cFecha").css("cursor", "hand").click(function () {
                    $("#Fecha").val("<?= $Fecha ?>");
                    $("#Fecha").prop("disabled", false);
                    displayCalendar($("#Fecha")[0], "yyyy-mm-dd", $(this)[0]);
                    $("#FechaI").val("");
                    $("#FechaF").val("");
                    $("#FechaI").prop("disabled", true);
                    $("#FechaF").prop("disabled", true);
                });
                $("#autocomplete").val("<?= $SCliente ?>");
                $("#autocomplete").activeComboBox(
                        $("[name='form1']"),
                        "SELECT data, value FROM (SELECT id as data, CONCAT(id, ' | ', tipodepago, ' | ', nombre) value FROM cli " +
                        "WHERE TRUE AND cli.tipodepago NOT REGEXP 'Contado|Puntos') sub WHERE TRUE",
                        "value"
                        );
                $('#autocomplete').focus();
            });

            function winieps(url) {
                window.open(url, 'miniwin', 'width=400,height=200,left=200,top=120,location=no');
            }
        </script>
    </head>

    <body>
        <div id="container">
            <?php
            nuevoEncabezado($Titulo);
            $impv = 0;
            $monv = 0;
            $ivav = 0;
            $iepsv = 0;
            $impfac = 0;
            $monfac = 0;
            $ivafac = 0;
            $iepsfac = 0;
            $Pvendidad = 0;
            $Pfacturada = 0;
            $PDiferencia = 0;
            ?>
            <div id="Reportes" style="min-height: 150px;">
                <table aria-hidden="true">
                    <thead>
                        <tr class="titulo">
                            <td colspan="12">Desgloce por dia natural y produto</td>
                        </tr>
                        <tr class="titulo">
                            <td colspan="2">Productos</td>
                            <td colspan="5">Vendido</td>
                            <td colspan="5">Facturado</td>
                        </tr>
                        <tr>
                            <td>Clave</td>
                            <td>Producto</td>
                            <td>Piezas</td>
                            <td>Importe</td>
                            <td>Subtotal</td>
                            <td>IVA</td>
                            <td>IEPS</td>
                            <td>Piezas</td>
                            <td>Importe</td>
                            <td>Subtotal</td>
                            <td>IVA</td>
                            <td>IEPS</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        error_log("La consulta para primer reporte " . $cSql);
                        if (($Vta2 = $mysqli->query($cSql))) {
                            while ($rg = $Vta2->fetch_array()) {

                                echo "<tr>";
                                echo "<td class=\"numero\">" . $rg[0] . "</td>";
                                echo "<td>" . $rg[1] . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg[2], 0) . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg[3], 2) . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg[4], 2) . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg[5], 2) . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg[6], 2) . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg[9], 0) . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg[10], 2) . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg[11], 2) . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg[12], 2) . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg[13], 2) . "</td>";

                                echo "</tr>";
                                $Pvendidad += $rg[2];
                                $impv += $rg[3];
                                $monv += $rg[4];
                                $ivav += $rg[5];
                                $iepsv += $rg[6];
                                $Pfacturada += $rg[9];
                                $impfac += $rg[10];
                                $monfac += $rg[11];
                                $ivafac += $rg[12];
                                $iepsfac += $rg[13];
                            }
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td>Total</td>
                            <td><?= number_format($Pvendidad, 0) ?></td>
                            <td><?= number_format($impv, 0) ?></td>
                            <td><?= number_format($monv, 2) ?></td>
                            <td><?= number_format($ivav, 2) ?></td>
                            <td><?= number_format($iepsv, 2) ?></td>
                            <td><?= number_format($Pfacturada, 0) ?></td>
                            <td><?= number_format($impfac, 2) ?></td>
                            <td><?= number_format($monfac, 2) ?></td>
                            <td><?= number_format($ivafac, 2) ?></td>
                            <td><?= number_format($iepsfac, 2) ?></td>

                        </tr>
                    </tfoot>
                </table>
            </div>

            <div id="Reportes" style="min-height: 150px;">
                <table aria-hidden="true">
                    <thead>
                        <tr class="titulo">
                            <td colspan="9">Desgloce de Piezas de aditivos</td>
                        </tr>
                        <tr>
                            <td>Producto</td>
                            <td>Piezas vendidas</td>
                            <td>Importe vendido</td>
                            <td>Piezas Facturadas</td>
                            <td>Importe Facturado</td>
                            <td>Diferencia Piezas</td>
                            <td>Diferencia Importe</td>

                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $Pvendidad = $Pfacturada = $impov = $impfac = 0;
                        $IDiferencia = 0;
                        foreach ($Resumen as $rg) {
                            echo "<tr>";
                            echo "<td>" . $rg["descripcion"] . "</td>";
                            echo "<td class=\"numero\">" . number_format($rg["piezas"], 0) . "</td>";
                            echo "<td class=\"numero\">" . number_format($rg["Importe_Vendido"], 2) . "</td>";
                            echo "<td class=\"numero\">" . number_format($rg["piezas_fact"], 0) . "</td>";
                            echo "<td class=\"numero\">" . number_format($rg["Importe_facturado"], 2) . "</td>";
                            echo "<td class=\"numero\">" . number_format($rg["piezas"] - $rg["piezas_fact"], 0) . "</td>";
                            echo "<td class=\"numero\">" . number_format($rg["Importe_Vendido"] - $rg["Importe_facturado"], 0) . "</td>";
                            echo "</tr>";
                            $Pvendidad += $rg["piezas"];
                            $Pfacturada += $rg["piezas_fact"];
                            $PDiferencia += $rg["piezas"] - $rg["piezas_fact"];
                            $impov += $rg["Importe_Vendido"];
                            $impfac += $rg["Importe_facturado"];
                            $IDiferencia += $rg["Importe_Vendido"] - $rg["Importe_facturado"];
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>Total</td>
                            <td><?= number_format($Pvendidad, 0) ?></td>
                            <td><?= number_format($impov, 2) ?></td>
                            <td><?= number_format($Pfacturada, 0) ?></td>
                            <td><?= number_format($impfac, 2) ?></td>
                            <td><?= number_format($PDiferencia, 0) ?></td>
                            <td><?= number_format($IDiferencia, 2) ?></td>


                        </tr>
                    </tfoot>
                </table>
            </div>

            <div id="Reportes" style="min-height: 150px;">
                <table aria-hidden="true">
                    <thead>
                        <tr class="titulo">
                            <td colspan="11">Detallado de piezas Facturadas </td>
                        </tr>
                        <tr>
                            <td>Clave</td>
                            <td>Producto</td>
                            <td>Precio</td>
                            <td>Costo</td>
                            <td>Piezas</td>
                            <td>Total</td>
                            <td>Pza.Fact.Contados</td>
                            <td>Pza.Fact.Clientes</td>
                            <td>Facturado</td>
                            <td>Pza.Por Factura</td>
                            <td>Pendiente por facturar</td>

                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $PT = $PFC = $PFCL = $PF = $IMPT = 0;
                        if (($VtAF = $mysqli->query($cSqlFAC))) {
                            while ($rg = $VtAF->fetch_array()) {

                                echo "<tr>";
                                echo "<td class=\"numero\">" . number_format($rg[0], 0) . "</td>";
                                echo "<td>" . $rg[1] . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg[3], 2) . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg[4], 2) . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg[6], 0) . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg["Grantotal"], 0) . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg[7], 0) . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg[8], 0) . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg[5], 2) . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg[9], 0) . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg["totalsinFacturado"], 2) . "</td>";
                                $PT += $rg[6];
                                $PFC += $rg[7];
                                $PFCL += $rg[8];
                                $PF += $rg[9];
                                $IMPT += $rg[5];
                                $Gt += $rg["Grantotal"];
                                $IMPTg += $rg["totalsinFacturado"];
                            }
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>Total</td>
                            <td><?= number_format($PT, 0) ?></td>
                            <td><?= number_format($Gt, 0) ?></td>
                            <td><?= number_format($PFC, 0) ?></td>
                            <td><?= number_format($PFCL, 0) ?></td>
                            <td><?= number_format($IMPT, 2) ?></td>
                            <td><?= number_format($PF, 0) ?></td>
                            <td><?= number_format($IMPTg, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>



            <div id="Reportes" style="min-height: 150px;">
                <table aria-hidden="true">
                    <thead>
                        <tr class="titulo">
                            <td colspan="11">Ventas de Aditivos Asignado a clientes </td>
                        </tr>
                        <tr>
                            <td>Ticket</td>
                            <td>Fecha Compra</td>
                            <td>UUID</td>
                            <td>Producto</td>
                            <td>Cantidad</td>
                            <td>Cliente</td>
                            <td>Tipo de Cliente</td>

                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $Pvendidad = 0;
                        if (($VtAF = $mysqli->query($cSqlAF))) {
                            while ($rg = $VtAF->fetch_array()) {

                                echo "<tr>";
                                echo "<td class=\"numero\">" . number_format($rg[0], 0) . "</td>";
                                echo "<td>" . $rg[1] . "</td>";
                                echo "<td>" . $rg[2] . "</td>";
                                echo "<td>" . $rg[3] . "</td>";
                                echo "<td>" . $rg[4] . "</td>";
                                echo "<td>" . $rg[5] . "</td>";
                                echo "<td>" . $rg[6] . "</td>";
                                echo "<td>" . $rg[7] . "</td>";
                                $Pvendidad += $rg[4];
                            }
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>Total</td>
                            <td><?= number_format($Pvendidad, 0) ?></td>
                            <td></td>
                            <td></td>
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
                            <td style="width: 30%;">
                                Por Periodo
                                <table aria-hidden="true">
                                    <tr>
                                        <td>F.inicial:</td>
                                        <td><input type="text" id="FechaI" name="FechaI"></td>
                                        <td class="calendario"><i id="cFechaI" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
                                    </tr>
                                    <tr>
                                        <td>F.final:</td>
                                        <td><input type="text" id="FechaF" name="FechaF"></td>
                                        <td class="calendario"><i id="cFechaF" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                Por Día
                                <table aria-hidden="true">
                                    <tr>
                                        <td>Fecha:</td>
                                        <td><input type="text" id="Fecha" name="Fecha"></td>
                                        <td class="calendario"><i id="cFecha" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                Cliente
                                <div style="position: relative;">
                                    <input type="search" style="width: 100%" class="texto_tablas" name="ClienteS" id="autocomplete" placeholder="Buscar cliente">
                                </div>
                                <div id="autocomplete-suggestions"></div>
                            </td>
                            <td>
                                <span><input type="submit" name="Boton" value="Enviar"></span>
                                <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                            </td>

                        </tr>
                    </table>
                </div>
            </form>

            <?php topePagina() ?>
        </div>

    </body>

</html>

