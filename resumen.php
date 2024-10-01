<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("data/VentaAditivosDAO.php");

use com\softcoatl\utils as utils;

require "./services/ReportesResumen.php";

$registros = utils\IConnection::getRowsFromQuery($selectByDia);

$Id = 32; /* Número de en el orden de la tabla submenus */
$data = array("Nombre" => "Resumen mensual", "Reporte" => $Id, "Fecha" => $Fecha, "FechaF" => $FechaF,
    "Detallado" => $Detallado, "Desglose" => $Desglose,
    "Turno" => $Turno, "Textos" => "Subtotal", "Filtro" => "1");

$Id1 = 205; /* NUMERO DE REPORTE POR FACTURA */
$data1 = array("Nombre" => "Ventas por Factura del mes" . $mes . "", "Reporte" => $Id1, "Fecha" => $Fecha,
    "mes" => $Mes, "anio" => $Anio);

$fechas = array("01" => "Enero", "02" => "Febrero", "03" => "Marzo", "04" => "Abril",
    "05" => "Mayo", "06" => "Junio", "07" => "Julio", "08" => "Agosto", "09" => "Septiembre",
    "10" => "Octubre", "11" => "Noviembre", "12" => "Diciembre");

$SerieF = array("A" => "Facturas mostrador", "T" => "TPV", "W" => "WEB", "H" => "Aceites",
    "B" => "Facturas Publico en general", "EDI" => "Facturas monederos", "E" => "Facturas Anticipos Débito", "CRE" => "Facturacion de credito",
    "D" => "Facturas de Prepago", "C" => "Facturas Contado", "F" => "Facturas Operativas", "ME" => "Facturas Monederos",
    "CA" => "Complementos de pago", "I" => "Rentas", "FE" => "Facturacion en Línea", "FP" => "Facturacion en Línea",
    "FEA" => "Facturacion en Línea", "FPA" => "Facturacion en Línea", "FPG-" => "Facturacion en Línea",
    "SC-DEB" => "Facturacion Debito", "FEC" => "Facturacion en Línea");
$Titulo = "Resumen mensual vendido y facturado | Mes : $fechas[$Mes] $Anio";

 $fechainicial = $Anio."".$Mes."01";
 $fechafinal = $Anio."".$Mes."31";

 $fechainicialSig = $Anio."".($Mes+1)."01";
 $fechafinalSig = $Anio."".($Mes+1)."31";
// error_log("Fecha sigiente ".$fechainicialSig );
// error_log("Fecha sigiente ".$fechafinalSig );
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $(".MesTxt").html("<?= $fechas[$Mes] ?>");
                $("#Mes").val("<?= $Mes ?>");
                $("#Detallado").val("<?= $Detallado ?>");
                $("#Desglose").val("<?= $Desglose ?>");
                $("#Mes").focus();
                $("#ShowTicket").hide();
                $("#ShowTicketOdo").hide();
                var i = 0
                $("#ShowDetalle").click(function () {
                    var e = i % 2;
                    if (e == 0) {
                        $("#ShowTicket").show();
                    } else {
                        $("#ShowTicket").hide();
                    }
                    i++;
                });
                $("#ShowDetalleOdo").click(function () {
                    var e = i % 2;
                    if (e == 0) {
                        $("#ShowTicketOdo").show();
                    } else {
                        $("#ShowTicketOdo").hide();
                    }
                    i++;
                });
            });
            function winieps(url) {
                window.open(url, 'miniwin', 'width=400,height=200,left=200,top=120,location=no');
            }
            function wingral2(url) {
                window.open(url, 'wingeneral1', 'width=700,height=900,left=200,top=120,location=no');
            }
        </script>
    </head>

    <body>

        <div id="container">
            <?php nuevoEncabezado($Titulo); ?>

            <?php
            $Com = "SELECT clave,clavei,descripcion,cve_producto_sat,cve_sub_producto_sat FROM com WHERE activo = 'Si';";
            $registros = utils\IConnection::getRowsFromQuery($Com);
            ?>
            <div style="width: 100%;">
                <table style="width: 100%;" summary="Reportes generales">
                    <tr><th scope="col" colspan="2"></th></tr>
                    <tr>
                        <td valign="top" style="width: 50%;">
                            <div style="width: 98%;height: 100%;display: inline-block;margin-right: 1%;margin-left: 1%;">
                                <div id="Reportes">
                                    <?php
                                    $InvSql = "SELECT id FROM inv WHERE descripcion = 'COMERCIALIZACION' AND rubro = 'Ent-pipas'";
                                    $ValorVariable = "SELECT valor FROM variables_corporativo WHERE llave = 'IdComercializacion';";
                                    $rsInv = utils\IConnection::execSql($InvSql);
                                    $rsVv = utils\IConnection::execSql($ValorVariable);

                                    $IdPipa = is_numeric($rsInv["id"]) ? $rsInv["id"] : $rsVv["valor"];
                                    $ticketsLibresMonederos = 0;
                                    foreach ($registros as $rg) {
                                        // DIESEL
                                        if ($rg["cve_producto_sat"] === "PR03") {
                                            $color = "#85929E";
                                        } else if ($rg["cve_producto_sat"] === "PR07" AND $rg["cve_sub_producto_sat"] === "SP16") {
                                            $color = "#82E0AA";
                                        } else if ($rg["cve_producto_sat"] === "PR07" AND $rg["cve_sub_producto_sat"] === "SP17") {
                                            $color = "#F1948A";
                                        }
                                        $MesNum2 = $MesNum == 12 ? 0 : $MesNum;
                                        if ($MesNum2 === 0) {
                                            $Anios = $Anio + 1;
                                        } else {
                                            $Anios = $Anio;
                                        }

                                        $MesNumAdd = $MesNum2 + 1;
                                        $cSqlM = "SELECT V.movimientos, V.importe vendido, V.volum
                            , IFNULL(F.importe,0.00) facturado
                            ,F.volumen volumenf
                            , cargas.numeroCargas nCargas
                            , cargas.aumento_bruto aumento
                            , cargas.aumento_neto  tcAumento
                            , round(V.importe - IFNULL(F.importe, 0.00) , 2) 'Porfacturar'
                            , round(V.volum - IFNULL(F.volumen, 0) , 2) 'PorfacturarV'
                            , TL.importe ImpTl
                            , SUM(ME.precioCompra) precioCompra
                            , sum(ME.vf) vf, sum(ME.ifc) ifc,TH.cantidad,rmPass.total TotalFP,rmPass.volS TotalFV
                            ,OtrasVFEsteMes.total totalVentasOtras
                            ,OtrasVFEsteMes.totalV totalVentasOtrasV
                            FROM 
                                (SELECT COUNT(*) movimientos,ROUND(SUM(if( rm.tipo_venta = 'D' , importe , 0)),2) importe, 
                                producto,ROUND(sum(importe/precio),2) volum
                                FROM rm 
                                WHERE rm.fecha_venta between $fechainicial AND $fechafinal 
                                AND rm.tipo_venta in ('D','N') 
                                AND producto='" . $rg["clavei"] . "'
                                ) V 
                            LEFT JOIN (
                                SELECT count(*) movimientos,ROUND(SUM(rm.importe),2) importe, producto,ROUND(SUM(importe/precio),2) volumen
                                FROM rm 
                                WHERE rm.fecha_venta between $fechainicial AND $fechafinal 
                                AND rm.uuid <> '-----' AND rm.tipo_venta in ('D','J') AND producto='" . $rg["clavei"] . "'
                            ) F ON F.producto = V.producto -- FACTURADO
                                  LEFT JOIN (
                                SELECT COUNT(*) movimientosL, ROUND(SUM(fcd.importe),2) importe ,
                                '" . $rg["clavei"] . "' prdcs
                                FROM fcd
                                LEFT JOIN fc ON fcd.id = fc.id 
                                LEFT JOIN cli ON fc.cliente = cli.id
                                WHERE fc.fecha_indice between $fechainicial AND $fechafinal AND (fcd.ticket = 0 OR fcd.ticket IS NULL)
                                AND (cli.rfc NOT LIKE 'XAXX010101000' AND cli.tipodepago = 'Monedero') 
                                AND fc.status = 1
                                 AND fcd.producto = 
                                (select inv.id from com LEFT JOIN inv ON com.descripcion = inv.descripcion 
                            WHERE clavei ='" . $rg["clavei"] . "'))
                            TL ON V.producto = TL.prdcs
                            LEFT JOIN (
                                SELECT 
                                       '" . $rg["clavei"] . "' producto 
                                       , SUM(importefac) ifc
                                       , ROUND( sum(me.volumenfac*1000) , 2 ) vf
                                       , count(*) numeroCargas
                                       , round(sum((select sum(precio*cantidad) preciounitario from med a where id = me.id and clave in (1,2,3,4,5,10))),2)  precioCompra 
                                       FROM me LEFT JOIN cargas c ON c.id = me.carga 
                                       WHERE 
                                       me.producto = '" . $rg["clave"] . "'
                                       AND c.tipo = 0 
                                       AND me.tipo <>  'Jarreo' 
                                       AND YEAR(DATE(me.fecha)) = $Anio
                                       AND MONTH(DATE(me.fecha)) =  $MesNum
                                       AND me.volumenfac > 0  
                                      GROUP BY me.producto
                            ) ME ON ME.producto = V.producto
                           LEFT JOIN 
                           (
                                SELECT
                                   '" . $rg["clavei"] . "' producto
                                  , IFNULL(SUM(CASE WHEN aumento_merma != 0 THEN aumento_merma
                                     ELSE c.tcaumento END 
                                 ),0) aumento_neto
                                 , sum(c.aumento) aumento_bruto
                                 , count(*)  numeroCargas
                                 FROM
                                 cargas c
                                 WHERE clave_producto = '" . $rg["clave"] . "'

                                 AND c.tipo = 0
                                 AND YEAR(DATE(c.fecha_fin)) = $Anio
                                 AND MONTH(DATE(c.fecha_fin)) =  $MesNum
                                 GROUP BY c.clave_producto
                           ) cargas ON  cargas.producto = V.producto
                           LEFT JOIN (
                                    SELECT '" . $rg["clavei"] . "' product , SUM(cantidad) cantidad FROM (
                                      SELECT * FROM (   SELECT IFNULL(    ROUND(volumen_actual , 3), 0) cantidad  
                                      ,DATE ( fecha_hora_s )    fecha,    tanque    
                                      FROM tanques_h   
                                       WHERE producto LIKE '%" . $rg["descripcion"] . "%'    
                                      AND DATE ( fecha_hora_s ) = DATE_ADD(DATE( '$Fecha' ),INTERVAL 1 MONTH)   
                                      ORDER BY fecha_hora_s DESC   ) t  
                                      GROUP BY DATE ( t.fecha ),t.tanque  
                                      ) t GROUP BY DATE ( fecha )                               
                           ) TH ON  TH.product = V.producto
                            LEFT JOIN ( 
                                SELECT count(1) cnt,sum(fcd.importe) total,sum(fcd.cantidad) volS,'" . $rg["clavei"] . "' prd FROM fc LEFT JOIN fcd on fc.id =fcd.id WHERE YEAR(fecha) = '$Anios' 
                            AND MONTH(fecha) = '$MesNumAdd' AND fcd.producto <= 5  AND uuid != '-----' AND fc.status=1  AND fcd.ticket in (SELECT id FROM rm WHERE 
                            rm.fecha_venta between $fechainicial AND $fechafinal AND uuid <> '-----' AND producto ='" . $rg["clavei"] . "')) rmPass
                            ON   rmPass.prd = V.producto 
                             LEFT JOIN ( 
                             select month(f.fecha_venta) mes_venta,round(sum(f.cantidad),2) totalV ,round(sum(f.importe),2) total,'" . $rg["clavei"] . "' prd 
                             FROM  (
                                 SELECT 
                                 fcd.importe
                                 ,fcd.cantidad 
                                 ,rm.fecha_venta
                                 FROM fc inner join fcd
                                 ON fc.id = fcd.id 
                                 INNER JOIN rm 
                                 on fcd.ticket = rm.id
                                 WHERE fc.fecha_indice between $fechainicial AND $fechafinal
                                 and fc.uuid != '-----' 
                                 and status = 1 and fcd.producto < 5 and rm.producto= '" . $rg["clavei"] . "'
                             ) f where MONTH(fecha_venta) <> $MesNum  ) OtrasVFEsteMes
                            ON   OtrasVFEsteMes.prd = V.producto ;";
                                       // echo($cSqlM);


                                        $FNF = utils\IConnection::execSql($cSqlM);
                                        $cantidad += $FNF["cantidad"];
                                        $vf += $FNF["vf"];
                                        $precioCompra += $FNF["precioCompra"];
                                        $ifc += $FNF["ifc"];
                                        $volum += $FNF["volum"];
                                        $volumen += $FNF["volumenf"];
                                        $vendido += $FNF["vendido"];
                                        $facturado += $FNF["facturado"];
                                        $ImpTl += $FNF["ImpTl"];
                                        $Porfacturar += $FNF["Porfacturar"];
                                        $PorfacturarV += $FNF["PorfacturarV"];
                                        $aumento += $FNF["aumento"];
                                        $tcAumento += $FNF["tcAumento"];
                                        $FacturadoMesSig += $FNF["TotalFP"];
                                        $FacturadoMesSigV += $FNF["TotalFV"];
                                        $VentaOtroMes += $FNF["totalVentasOtras"];
                                        $VentaOtroMesV += $FNF["totalVentasOtrasV"];
                                        ?>

                                        <table style="width: 100%;border: 1px solid #999;margin-bottom: 10px;border-radius: 5px;" summary="Reportes generales">
                                            <tr>
                                                <th scope="col" valign="top" style="font-size: 15px;font-weight: bold;height: 25px;background-color: <?= $color ?>;" colspan="3">
                                                    <?= $rg["descripcion"] ?>
                                                </th>
                                            </tr>
                                            <tr style="font-weight: bold;">
                                                <td style="width: 40%;" class="MesTxt"></td>
                                                <td style="text-align: center;">Litros</td>
                                                <td style="text-align: center;">ImporteTotal</td>
                                            </tr>
                                            <tr title="Ultima lectura tomada por el veeder">
                                                <td>Inventario final</td>
                                                <td style="text-align: right;"><?= number_format($FNF["cantidad"], 2) ?> Lts.</td>
                                                <td style="text-align: center;">&nbsp;</td>
                                            </tr>
                                            <tr title="Numero de cargas recibidas en periodo consultado">
                                                <td>Numero de Descargas Tanque</td>
                                                <td style="text-align: right;"><?= number_format($FNF["nCargas"], 0) ?> </td>
                                                <td style="text-align: center;">&nbsp;</td>
                                            </tr>
                                            <tr title="Recepcion litros compensado / temperatura (cargas)">
                                                <td>Recepciones neto (litros)</td>
                                                <td style="text-align: right;"><?= number_format($FNF["tcAumento"], 2) ?> Lts.</td>
                                                <td style="text-align: center;">&nbsp;</td>
                                            </tr>

                                            <tr title="Lectura de entradas tomado por el valor de la factura(s) (Volumen Facturado) (me) ">
                                                <td>Compras (litros)</td>
                                                <td style="text-align: right;"><?= number_format($FNF["vf"], 2) ?> Lts.</td>
                                                <td style="text-align: center;">&nbsp;</td>
                                            </tr>
                                            <tr title="Monto total de las compras de entradas (me)">
                                                <td>Compras total factura (pesos)</td>
                                                <td style="text-align: center;">&nbsp;</td>
                                                <td style="text-align: right;">$ <?= number_format($FNF["ifc"], 2) ?></td>
                                            </tr>
                                            <tr title="Lectura tomada por el valor de la factura(s) (med)">
                                                <td>Compras Neto (pesos)</td>
                                                <td style="text-align: center;">&nbsp;</td>
                                                <td style="text-align: right;">$ <?= number_format($FNF["precioCompra"], 2) ?></td>

                                            </tr>
    <!--                                        <tr title="Monto total vendido en litros (rm)">
                                                <td>Ventas (litros)</td>
                                                <td style="text-align: right;"><?= number_format($FNF["volum"], 2) ?></td>
                                                <td style="text-align: center;">Lts.&nbsp;</td>

                                            </tr>-->
                                            <tr title="Monto total vendido en pesos (rm)">
                                                <td>Ventas (pesos)</td>
                                                <td style="text-align: right;"><?= number_format($FNF["volum"], 2) ?> Lts.</td>
                                                <td style="text-align: right;">$ <?= number_format($FNF["vendido"], 2) ?></td>

                                            </tr>
                                            <tr title="Total de venta facturada por tickets (rm)">
                                                <td>Facturado (pesos)</td>
                                                <td style="text-align: right;"><?= number_format($FNF["volumenf"], 2) ?> Lts</td>
                                                <td style="text-align: right;">$ <?= number_format($FNF["facturado"], 2) ?></td>

                                            </tr>
                                            <tr title="Total de venta facturada por monederos mediante XML (fcd)">
                                                <td>Monederos (facturas)</td>
                                                <td style="text-align: right;">&nbsp;</td>
                                                <td style="text-align: right;">$ <?= number_format($FNF["ImpTl"], 2) ?></td>


                                            </tr>
                                            <tr title="Diferencia entre lo facturado y lo no facturado">
                                                <td>Por Facturar (pesos)</td>
                                                <td style="text-align: right;"><?= number_format($FNF["PorfacturarV"] - $FNF["ImpTl"], 2) ?> Lts</td>
                                                <td style="text-align: right;">$ <?= number_format($FNF["Porfacturar"] - $FNF["ImpTl"], 2) ?></td>

                                            </tr>
                                            <tr>
                                                <td >Facturado en el siguiente mes</td>
                                                <td style="text-align: right;color: red;"><?= number_format($FNF["TotalFV"], 2) ?> Lts</td>
                                                <td style="text-align: right;color: red;">$ <?= number_format($FNF["TotalFP"], 2) ?></td>

                                            </tr>          
                                            <tr>
                                                <td>Venta de otro mes facturado en <?= $fechas[$Mes] ?></td>
                                                <td style="text-align: right;"><?= number_format($FNF["totalVentasOtrasV"], 2) ?> Lts</td>
                                                <td style="text-align: right;">$ <?= number_format($FNF["totalVentasOtras"], 2) ?></td>

                                            </tr>
                                        </table>
                                        <?php
                                        $ticketsLibresMonederos += $FNF["ImpTl"];
                                    }
                                    ?>
                                </div>
                            </div>
                        </td>
                        <td valign="top" style="width: 50%;">
                            <div style="width: 98%;display: inline-block;">
                                <div id="Reportes">
                                    <table style="width: 100%;border: 1px solid #999999;border-radius: 5px;" summary="Reportes generales Resumen">
                                        <tr>
                                            <th  scope="col" style="font-size: 15px;font-weight: bold;height: 25px;background-color: #B7C5D3;" colspan="4">
                                                RESUMEN VENTAS GASOLINA
                                            </th>
                                        </tr>
                                        <tr style="font-weight: bold;">
                                            <td style="width: 30%;" class="MesTxt"></td>
                                            <td style="text-align: center;">Litros</td>
                                            <td style="text-align: center;">Pesos</td>
                                            <td style="text-align: center;">Detalle</td>

                                        </tr>
                                        <!--<tr title="Recepcion litros">
                                            <td>Recepciones bruto (litros)</td>
                                            <td style="text-align: right;"><?= number_format($aumento, 2) ?></td>
                                            <td style="text-align: center;">Lts.&nbsp;</td>
                                        </tr>-->
                                        <tr title="Recepcion litros compensado / temperatura">
                                            <td>Recepciones neto (litros)</td>
                                            <td style="text-align: right;"><?= number_format($tcAumento, 2) ?> Lts.</td>
                                            <td style="text-align: center;"></td>
                                            <td style="text-align: center;"></td>

                                        </tr>
                                        <tr title="Lectura que tomado por el valor de la factura(s)">
                                            <td>Compras (litros)</td>
                                            <td style="text-align: right;"><?= number_format($vf, 2) ?> Lts.</td>
                                            <td style="text-align: center;"></td>
                                            <td style="text-align: center;"></td>
                                        </tr>
                                        <tr title="Monto total de las compras">
                                            <td>Compras total factura (pesos)</td>
                                            <td style="text-align: center;">&nbsp;</td>
                                            <td style="text-align: right;">$ <?= number_format($ifc, 2) ?></td>
                                            <td style="text-align: center;"></td>

                                        </tr>
                                        <tr title="Lectura que tomado por el valor de la factura(s)">
                                            <td>Compras Neto (pesos)</td>
                                            <td style="text-align: right;">&nbsp;</td>
                                            <td style="text-align: right;">$ <?= number_format($precioCompra, 2) ?></td>
                                            <td style="text-align: center;"></td>

                                        </tr>
                                        <tr title="Monto total vendido en pesos">
                                            <td>Ventas</td>
                                            <td style="text-align: right;"><?= number_format($volum, 2) ?> Lts.</td>
                                            <td style="text-align: right;">$ <?= number_format($vendido, 2) ?></td>
                                            <td style="text-align: center;"></td>
                                        </tr>
                                        <tr title="Total de venta facturada por tickets (rm)">
                                            <td>Facturado</td>
                                            <td style="text-align: right;"><?= number_format($volumen, 2) ?> Lts.</td>
                                            <td style="text-align: right;">$ <?= number_format($facturado, 2) ?></td>
                                            <td style="text-align: center;"><i class="fa fa-list-alt fa-lg edit" aria-hidden="true" id="ShowDetalleOdo"></i></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" id="ShowTicketOdo">
                                                <table style="width: 100%;" summary="Reportes generales">
                                                    <?php
                                                    $MesSig = "SELECT count(*) movimientos,ROUND(SUM(rm.importe),2) importe, rm.producto,fc.serie
                                                    FROM fc left join fcd on fcd.id=fc.id left join rm on fcd.ticket=rm.id  
                                                    WHERE 
                                                     fc.fecha_indice between  $fechainicialSig AND $fechafinalSig  
                                                    and rm.fecha_venta between $fechainicial AND $fechafinal
                                                    AND rm.uuid <> '-----' AND rm.tipo_venta='D' AND fc.status = 1 AND fcd.producto < 5
                                                    group by serie";
//                                                    echo $MesSig;
                                                    $MesS = utils\IConnection::getRowsFromQuery($MesSig);
                                                    ?>
                                                    <tr style="font-weight: bold;">
                                                        <th scope="col">Serie</th>
                                                        <th scope="col">Importe </th>
                                                    </tr>
                                                    <?php
                                                    foreach ($MesS as $M) {
                                                        ?>
                                                        <tr>
                                                            <td><?= $M["serie"] ?></td>
                                                            <td style="text-align: right;">$<?= number_format($M["importe"], 2) ?></td>
                                                        </tr>
                                                        <?php
                                                        $ImpTot += $M["importe"];
                                                    }
                                                    ?>
                                                    <tr>
                                                        <td>
                                                            <?php
                                                            $data = array("Nombre" => "Resumen mensual", "Reporte" => 202, "mes" => $MesNum, "Anio" => $Anio);
                                                            ?>
                                                            <span class="ButtonExcel"><a href="report_excel_reports.php?<?= http_build_query($data) ?>"><i class="icon fa fa-lg fa-bold fa-file-excel-o" aria-hidden="true"> Excel </i></a></span></td>
                                                        <td style="font-weight: bold;font-size: 15px;text-align: right">Total: <?= number_format($ImpTot, 2) ?></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr title="Total de venta facturada por monederos mediante XML (fcd)">
                                            <td>Monederos (facturas)</td>
                                            <td style="text-align: right;">&nbsp;</td>
                                            <td style="text-align: right;">$ <?= number_format($ImpTl, 2) ?></td>
                                            <td style="text-align: center;"></td>
                                        </tr>
                                        <tr title="Diferencia entre lo facturado y lo no facturado">
                                            <td>Por Facturar (pesos)</td>
                                            <td style="text-align: right;"><?= number_format($PorfacturarV) ?> Lts.</td>
                                            <td style="text-align: right;">$ <?= number_format($Porfacturar - $FNF["ImpTl"] - $ImpTl, 2) ?></td>
                                            <td style="text-align: center;"><a href="javascript:window.winieps('porfacturar.php?criteria=ini&mesS=<?= $MesNumAdd ?>&anioS=<?= $Anios ?>&anio=<?= $Anio ?>&mes=<?= $MesNum ?>')" ><em class="fa fa-list-alt fa-lg edit"></em></a></td>

                                        </tr>
                                        <tr>
                                            <td>Facturado en el siguiente mes</td>
                                            <td style="text-align: right; color:red;"><?= number_format($FacturadoMesSigV, 2) ?> Lts.</td>
                                            <td style="text-align: right; color:red;">$ <?= number_format($FacturadoMesSig, 2) ?></td>
                                            <td style="text-align: center;"><a href="javascript:window.winieps('facsiguiente.php?criteria=ini&mesS=<?= $MesNumAdd ?>&anioS=<?= $Anios ?>&anio=<?= $Anio ?>&mes=<?= $MesNum ?>')" ><em class="fa fa-list-alt fa-lg edit"></em></a></td>
                                        </tr>
                                        <tr>
                                            <td>Venta de otro mes facturado <?= $fechas[$Mes] ?></td>
                                            <td style="text-align: right;"><?= number_format($VentaOtroMesV, 2) ?> Lts.</td>
                                            <td style="text-align: right;">$ <?= number_format($VentaOtroMes, 2) ?></td>
                                            <td style="text-align: center;"><a href="javascript:window.winieps('venOtrMes.php?criteria=ini&anio=<?= $Anio ?>&mes=<?= $MesNum ?>')" ><em class="fa fa-list-alt fa-lg edit"></em></a></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" id="ShowTicket">
                                                <table style="width: 100%;" summary="Reportes generales">
                                                    <?php
                                                    $MesSig = "SELECT  "
                                                            . "fc.folio,fc.serie,fc.fecha"
                                                            . ",fc.uuid,rm.id, rm.inicio_venta,fcd.cantidad volumenp,rm.importe importeTotal,"
                                                            . "fcd.cantidad*fcd.precio importe,(fcd.cantidad*fcd.precio)*fcd.iva importeIva,"
                                                            . "fcd.cantidad * fcd.ieps ieps "
                                                            . "FROM fc LEFT JOIN fcd on fc.id =fcd.id left join rm ON fcd.ticket=rm.id  "
                                                            . "WHERE YEAR(fc.fecha) = '$Anio' AND MONTH(fc.fecha) = '$MesNumAdd' and fc.uuid != '-----' "
                                                            . "AND  fcd.ticket in (SELECT id FROM rm WHERE rm.fecha_venta between $fechainicial AND $fechafinal AND fc.uuid <> '-----' ) AND fcd.producto <= 5;";
//                                                    echo $MesSig;
                                                       // error_log($MesSig);
                                                    $MesS = utils\IConnection::getRowsFromQuery($MesSig);
                                                    ?>
                                                    <tr style="font-size: 17px;font-weight: bold;">
                                                        <th colspan="3" scope="col">Factura</th>
                                                        <th colspan="3" scope="col">Tickets</th>
                                                    </tr>
                                                    <tr style="font-weight: bold;">
                                                        <th scope="col">Folio</th>
                                                        <th scope="col">Fecha</th>
                                                        <th scope="col">UUID</th>
                                                        <th scope="col">Ticket</th>
                                                        <th scope="col">Fecha</th>
                                                        <th scope="col">Volumen</th>
                                                        <th scope="col">Iva</th>
                                                        <th scope="col">Ieps</th>
                                                        <th scope="col">Importe </th>
                                                        <th scope="col">Total</th>
                                                    </tr>
                                                    <?php
                                                    foreach ($MesS as $M) {
                                                        ?>
                                                        <tr>
                                                            <td><?= $M["serie"] . "-" . $M["folio"] ?></td>
                                                            <td><?= $M["fecha"] ?></td>
                                                            <td><?= $M["uuid"] ?></td>
                                                            <td><?= $M["id"] ?></td>
                                                            <td><?= $M["inicio_venta"] ?></td>
                                                            <td><?= number_format($M["volumenp"], 2) ?></td>
                                                            <td style="text-align: right;">$<?= number_format($M["importeIva"], 3) ?></td>
                                                            <td style="text-align: right;">$<?= number_format($M["ieps"], 3) ?></td>
                                                            <td style="text-align: right;">$<?= number_format($M["importe"], 3) ?></td>
                                                            <td style="text-align: right;">$<?= number_format($M["importeTotal"], 3) ?></td>
                                                        </tr>
                                                        <?php
                                                        $ImpT += $M["importeTotal"];
                                                    }
                                                    ?>
                                                    <tr>
                                                        <td colspan="5"></td>
                                                        <td style="font-weight: bold;font-size: 15px;">Total: <?= $ImpT ?></td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                    <table style="width: 100%;margin-top: 10px;border: 1px solid #999999;border-radius: 5px;" summary="Control volumetrico">
                                        <?php
                                        $SqlCv = "SELECT logs.id, logs.fecha_generacion, logs.nombre_archivo archivo,fecha_informacion "
                                                . "FROM log_envios_sat logs   WHERE month(fecha_informacion) = '$MesNum' "
                                                . "AND year(fecha_informacion) = $Anio AND periodicidad = 'M' "
                                                . "ORDER BY logs.fecha_informacion ASC";
                                        //error_log($SqlCv);
                                        $SqlCvRs = utils\IConnection::execSql($SqlCv);
                                        $self = utils\HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");
                                        ?>
                                        <tr>
                                            <th style="font-size: 15px;font-weight: bold;height: 25px;background-color: #B7C5D3;" colspan="2"  scope="col">
                                                CONTROL VOLUMÉTRICO
                                            </th>
                                            <th style="text-align: right"  scope="col">
                                                <a href="#" onclick="wingral('imprep_envios_sat.php?FechaI=<?= $SqlCvRs["fecha_informacion"] ?>&FechaF=<?= $SqlCvRs["fecha_informacion"] ?>&return=resumen.php')" title="Resumen del archivo de control volumétrico">
                                                    <i class="icon fa fa-lg fa-list-alt" aria-hidden="true"></i>
                                                </a>
                                            </th>
                                        </tr>
                                        <tr>
                                            <td>Control Volumétrico</td>
                                            <td>
                                                <strong>Generación: </strong>
                                                <?= $SqlCvRs["fecha_generacion"] ?></td>
                                            <td style="text-align: center;">
                                                <?php
                                                if ($SqlCvRs["fecha_generacion"] <> "") {
                                                    ?>
                                                    <a href="<?= $self ?>?archivo=<?= $SqlCvRs["archivo"] ?>" title="Descargar Archivo <?= $SqlCvRs["archivo"] ?>">
                                                        <i class="icon fa fa-lg fa-download" aria-hidden="true" style="color: #2471A3"></i>
                                                    </a>
                                                    <?php
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php
                                        $SqlJ = "SELECT COUNT(*) movimientos,ROUND(SUM(importe),2) importe, producto,sum(volumen) volum
						                            FROM rm  WHERE  rm.fecha_venta between $fechainicial AND $fechafinal   AND rm.tipo_venta = 'J'  group by producto";
                                        //error_log($SqlJ);
                                        $RgJ = utils\IConnection::getRowsFromQuery($SqlJ);
                                        $FechaM1M = date("Y-m-d", strtotime($Fecha . "+ 1 month"));
                                        $FechaFin = date("Y-m-d", strtotime($FechaM1M . "- 1 days"));
                                        ?>
                                    </table>
                                    <table  style="width: 100%;border: 1px solid #999999;border-radius: 5px;margin-top: 10px;" summary="Jarreos totales">
                                        <tr>
                                            <th style="font-size: 15px;font-weight: bold;height: 25px;background-color: #B7C5D3;" colspan="3"  scope="col">
                                                JARREO
                                            </th>
                                            <th style="text-align: right;width: 100px;"  scope="col">
                                                <a title="Abrir detalle de jarreos" href=javascript:wingral("repjarreos.php?FechaI=<?= $Fecha ?>&FechaF=<?= $FechaFin ?>&return=resumen.php");>
                                                    <i class="fa fa-bars" aria-hidden="true"></i>
                                                </a>
                                            </th>
                                        </tr>
                                        <tr style="font-weight: bold;">
                                            <td colspan="2" style="text-align: right;padding-right: 20px;">Movimientos</td>
                                            <td style="text-align: center">Volumen</td>
                                            <td style="text-align: center">Importe</td>
                                        </tr>
                                        <?php
                                        foreach ($RgJ as $rgJ) {
                                            ?>
                                            <tr>
                                                <td style="font-weight: bold;"><?= $rgJ["producto"] ?></td>
                                                <td style="text-align: right;"><?= $rgJ["movimientos"] ?></td>
                                                <td style="text-align: right;"><?= number_format($rgJ["volum"], 2) ?> L.</td>
                                                <td style="text-align: right;">$ <?= number_format($rgJ["importe"], 2) ?></td>
                                            </tr>
                                            <?php
                                            $movimientosJ += $rgJ["movimientos"];
                                            $volumJ += $rgJ["volum"];
                                            $importeJ += $rgJ["importe"];
                                        }
                                        ?>
                                        <tr>
                                            <td style="font-weight: bold;"></td>
                                            <td style="text-align: right;"><?= $movimientosJ ?></td>
                                            <td style="text-align: right;"><?= number_format($volumJ, 2) ?> L.</td>
                                            <td style="text-align: right;">$ <?= number_format($importeJ, 2) ?></td>
                                        </tr>
                                    </table>
                                    <table style="width: 100%;margin-top: 10px;border: 1px solid #999999;border-radius: 5px;" summary="Prefijos y series de facturación">
                                        <tr>
                                            <th style="font-size: 15px;font-weight: bold;height: 25px;background-color: #B7C5D3;" colspan="5"  scope="col">
                                                FACTURAS EMITIDAS <?= strtoupper($fechas[$Mes]) ?><br>
                                                PREFIJOS Y SERIES DE FACTURACIÓN
                                            </th>
                                            <th  scope="col">
                                                <a href="javascript:window.wingral2('analisis.php?criteria=ini&Mes=<?= $MesNum ?>&Anio=<?= $Anio ?>')"><i class='fa fa-wpexplorer' aria-hidden='true'>Análisis</i></a>
                                            </th>
                                        </tr>
                                        <tr style="font-weight: bold;">
                                            <td>SERIE</td>
                                            <td>DETALLE</td>
                                            <td style="text-align: center;">TOTAL</td>
                                            <td style="text-align: center;">N.C.</td>
                                            <td>LITROS</td>
                                            <td>PIEZAS</td>
                                        </tr>
                                        <?php
                                        $SqlSF = "SELECT count(*) movimientos,fcImp.total importe,SUM(if(fcd.ticket < 0,fcd.importe,0)) notasCredito,
                                            fc.serie,SUM(if(fcd.producto < 5,fcd.cantidad,0)) volumen,SUM(if(fcd.producto > 5,fcd.cantidad,0)) piezas
                                    FROM fc LEFT JOIN fcd ON fc.id=fcd.id LEFT JOIN 
                                        (SELECT fc.serie , sum(fc.total) total
                                        FROM fc  
                                        WHERE fc.fecha_indice between $fechainicial AND $fechafinal and fc.uuid != '-----' and status=1
                                        group by fc.serie ) fcImp ON fcImp.serie=fc.serie WHERE fc.fecha_indice between $fechainicial AND $fechafinal and
                                      status = 1  group by fc.serie;";
                                      //error_log($SqlSF);
                                        $FSerie = utils\IConnection::getRowsFromQuery($SqlSF);
                                        foreach ($FSerie as $fs) {
                                            ?>
                                            <tr>
                                                <td><a href="javascript:window.winieps('resfactura.php?serie=<?= $fs["serie"] ?>&mes=<?= $MesNum ?>&anio=<?= $Anio ?>&piezas=<?= $fs["piezas"] ?>')" > <?= $fs["serie"] ?></a></td>
                                                <td><?= $SerieF[$fs["serie"]] ?></td>
                                                <td style="text-align: right;">$ <?= number_format($fs["importe"], 2) ?></td>
                                                <td style="text-align: right;">$ <?= number_format($fs["notasCredito"], 2) ?></td>
                                                <td style="text-align: right;"><?= number_format($fs["volumen"], 2) ?></td>
                                                <td style="text-align: right;"><?= number_format($fs["piezas"], 0) ?></td>
                                            </tr>
                                            <?php
                                            $Total += $fs["importe"] - $fs["notasCredito"];
                                            $Volumen += $fs["volumen"];
                                            $Piezas += $fs["piezas"];
                                        }
                                        if ($ticketsLibresMonederos > 0) {
                                            ?>
                                            <tr>
                                                <td>EDI</td>
                                                <td>Factura Monederos</td>
                                                <td style="text-align: right;">$ <?= number_format($ticketsLibresMonederos, 2) ?></td>
                                                <td style="text-align: right;"></td>
                                                <td style="text-align: right;"></td>
                                            </tr>
                                            <?php
                                            $Total += $ticketsLibresMonederos;
                                        }
                                        ?>
                                        <tr>
                                            <td colspan="2" style="text-align: right;font-weight: bold;">Total:</td>
                                            <td style="text-align: right;">$ <?= number_format($Total, 2) ?></td>
                                            <td></td>
                                            <td style="text-align: right;"><?= number_format($Volumen, 2) ?></td>
                                            <td style="text-align: right;"><?= number_format($Piezas, 0) ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>                            
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div id="Reportes">
                                <table style="width: 100%;border: 1px solid #999999;border-radius: 5px;" summary="Desglose de piezas aditivos">
                                    <tr>
                                        <th style="font-size: 15px;font-weight: bold;height: 25px;background-color: #B7C5D3;" colspan="6"  scope="col">
                                            Desgloce de piezas aditivos
                                        </th>
                                        <th  scope="col">
                                            <a href="javascript:window.wingral2('analisisAd.php?mes=<?= $MesNum ?>&Anio=<?= $Anio ?>')"><i class='fa fa-wpexplorer' aria-hidden='true'>Detalle</i></a>
                                        </th>
                                    </tr>
                                    <tr style="font-weight: bold;">
                                        <td>Producto</td>
                                        <td>Pz. vendida</td>
                                        <td>$ Vendido</td>
                                        <td>Pz. facturada</td>
                                        <td>$ Facturado</td>
                                        <td>Diferencia</td>
                                        <td>$ Diferencia</td>
                                    </tr>
                                    <?php
                                    $VAditivosDAO = new VentaAditivosDAO();
                                    //echo $Fecha . " Y " . $FechaFin;
                                    $Aditivos = $VAditivosDAO->getProductos($Fecha, $FechaFin);
                                    foreach ($Aditivos as $ad) {
                                        ?>
                                        <tr>
                                            <td><?= $ad["descripcion"] ?></td>
                                            <td><?= $ad["piezas"] ?></td>
                                            <td><?= number_format($ad["Importe_Vendido"], 2) ?></td>
                                            <td><?= $ad["piezas_fact"] ?></td>
                                            <td><?= number_format($ad["Importe_facturado"], 2) ?></td>
                                            <td><?= $ad["piezas"] - $ad["piezas_fact"] ?></td>
                                            <td><?= number_format($ad["Importe_Vendido"] - $ad["Importe_facturado"], 2) ?></td>
                                        </tr>
                                        <?php
                                        $piezasA += $ad["piezas"];
                                        $Importe_VendidoA += $ad["Importe_Vendido"];
                                        $piezas_factA += $ad["piezas_fact"];
                                        $Importe_facturadoA += $ad["Importe_facturado"];
                                        $piezasDifA += $ad["piezas"] - $ad["piezas_fact"];
                                        $importeDifA += $ad["Importe_Vendido"] - $ad["Importe_facturado"];
                                    }
                                    ?>
                                    <tr style="font-weight: bold;">
                                        <td style="text-align: right;"> Total: </td>
                                        <td><?= $piezasA ?></td>
                                        <td><?= number_format($Importe_VendidoA, 2) ?></td>
                                        <td><?= $piezas_factA ?></td>
                                        <td><?= number_format($Importe_facturadoA, 2) ?></td>
                                        <td><?= $piezasDifA ?></td>
                                        <td><?= number_format($importeDifA, 2) ?></td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div id="footer">
            <form name="formActions" method="post" action="" id="form" class="oculto">
                <div id="Controles">
                    <table aria-hidden="true" summary="Formulario de datos">
                        <tr style="height: 0px;"><th colspan="2"></th></tr>
                        <tr style="height: 40px;">
                            <td style="width: 30%;">
                                <table aria-hidden="true">
                                    <tr>
                                        <td>Año:</td>
                                        <td>
                                            <input type="number" name="FechaNum" id="FechaNum" min="2020" max="2060" value="<?= date("Y") ?>">
                                        </td>center
                                        <td>
                                            <select name="Mes" id="Mes">
                                                <?php
                                                foreach (getMonts() as $key => $value) {
                                                    echo "<option value='$key'>$value</option>";
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <span style="margin-left: 65%"><input type="submit" name="Boton" value="Enviar"></span>
                                <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                                <span class="ButtonExcel"><a href="report_excel_resumen.php?<?= http_build_query($data) ?>"><i class="icon fa fa-lg fa-bold fa-file-excel-o" aria-hidden="true"></i></a></span>
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
            <?php topePagina(); ?>
        </div>
    </body>
</html>
