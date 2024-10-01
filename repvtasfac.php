<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("importeletras.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$usuarioSesion = getSessionUsuario();

require "./services/ReportesVentasService.php";

if ($request->hasAttribute("Boton")) {

    if ($request->hasAttribute("Fecha")) {

        $Titulo = "Vendido y facturado del $Fecha";
        $op = 1;

        $cSql_ = "SELECT UPPER(tipodepago) tipodepago,SUM(total) importe
                    FROM(
                    SELECT 
                    DISTINCT cli.tipodepago,fc.*
                    FROM cli,fc,fcd 
                    WHERE cli.id = fc.cliente AND fc.id = fcd.id
                    AND  cli.rfc NOT LIKE 'XAXX010101000' 
                    AND (fcd.ticket = 0 OR fcd.ticket IS NULL) 
                    AND fc.status = " . StatusFactura::CERRADO . " 
                    AND DATE( fc.fecha ) =DATE('$Fecha') ) f
                    GROUP BY tipodepago;";

        $cSql = "SELECT 
                        V.cliente, V.alias, UPPER(V.tipodepago) tipodepago, V.movimientos, V.importe vendido, 
                        IFNULL(F.importe,0.00) facturado, 
                        round(V.importe - IFNULL(F.importe, 0.00), 2) 'Porfacturar' 
                        FROM 
                            (SELECT COUNT(*) movimientos, cliente, cli.alias, cli.tipodepago, 
                            ROUND(SUM(importe),2) importe 
                            FROM rm LEFT JOIN cli ON rm.cliente=cli.id 
                            WHERE DATE(rm.fin_venta) = DATE('$Fecha') AND rm.tipo_venta = 'D'
                            GROUP BY cli.tipodepago) V
                        LEFT JOIN (
                            SELECT count(*) movimientos,cliente,cli.alias,cli.tipodepago,
                            ROUND(SUM(rm.importe),2) importe 
                            FROM rm LEFT JOIN cli ON rm.cliente=cli.id 
                            WHERE DATE(rm.fin_venta) = DATE('$Fecha') 
                            AND rm.uuid <> '-----' AND rm.tipo_venta='D' 
                            GROUP BY cli.tipodepago
                        ) F ON F.tipodepago = V.tipodepago";

        $cSqlD = "SELECT V.tipodepago,V.producto,V.movimientos,V.vendido,
                            F.facturado,(V.vendido-F.facturado) PorFacturar,
                            (V.movimientos-F.movimientos) DespPorFacturar  
                            FROM 
                            (
                                SELECT COUNT(*) movimientos,upper(cli.tipodepago) tipodepago,com.descripcion producto,
                                ROUND(SUM(rm.importe),2) vendido
                                FROM com,rm LEFT JOIN cli ON rm.cliente=cli.id 
                                WHERE com.clavei = rm.producto AND DATE(rm.fin_venta) = DATE('$Fecha') AND rm.tipo_venta='D'
                                GROUP BY cli.tipodepago,rm.producto
                                ) V
                            LEFT JOIN 
                            (
                                SELECT  COUNT(*) movimientos,upper(cli.tipodepago) tipodepago,com.descripcion producto,
                                ROUND(SUM(rm.importe),2) facturado
                                FROM com,rm LEFT JOIN cli ON rm.cliente=cli.id
                                WHERE com.clavei = rm.producto AND  DATE(rm.fin_venta) = DATE('$Fecha') AND rm.uuid <> '-----' 
                                AND rm.tipo_venta='D'
                                GROUP BY cli.tipodepago,rm.producto) F 
                            ON V.producto = F.producto AND V.tipodepago = F.tipodepago";

        $cSqlGralClientes = "SELECT rm.cliente,cliRm.nombre,cliRm.tipodepago,
                            COUNT(*) ventas,
                            ROUND(SUM(fcd.importe),2) total,
                            ROUND(SUM(rm.pesos),2) importe_rm,
                            fc.status
                            FROM rm,fcd,fc,cli,cli cliRm 
                            WHERE 
                            rm.id = fcd.ticket AND fcd.id = fc.id AND fc.cliente = cli.id AND rm.cliente = cliRm.id AND
                            DATE(rm.fin_venta) = DATE('$Fecha') AND cli.rfc NOT LIKE '%XAXX010101000%'
                            AND rm.tipo_venta = 'D' AND fc.status = " . StatusFactura::CERRADO . "
                            GROUP BY cliRm.tipodepago";

        $cSqlGral = "
                    SELECT cliente,nombre,tipodepago,ROUND(SUM(ventas),2) ventas,ROUND(SUM(total),2) total FROM (
                    SELECT fc.id,fc.folio,fc.cliente,cli.nombre,fc.fecha,fc.uuid,fc.formadepago,cliRm.tipodepago,
                    COUNT(*) ventas,
                    ROUND(SUM(fcd.importe),2) importe_fcd,
                    ROUND(SUM(rm.importe),2) importe_rm,
                    IF(fc.status = " . StatusFactura::CERRADO . ",fc.total,0) total,fc.status
                    FROM rm,fcd,fc,cli,cli cliRm 
                    WHERE 
                    rm.id = fcd.ticket AND fcd.id = fc.id AND fc.cliente = cli.id AND rm.cliente = cliRm.id AND
                    DATE(rm.fin_venta) = DATE('$Fecha') AND cli.rfc LIKE '%XAXX010101000%'
                    AND rm.tipo_venta = 'D' 
                    GROUP BY fc.id,cliRm.tipodepago
                    ) sub 
                    GROUP BY tipodepago";
    } elseif ($request->hasAttribute("FechaI")) {


        $Titulo = "Vendido y facturado del $FechaI al  $FechaF ";
        $op = 2;

        $cSql_ = "SELECT UPPER(tipodepago) tipodepago,SUM(total) importe
                    FROM(
                    SELECT 
                    DISTINCT cli.tipodepago,fc.*
                    FROM cli,fc,fcd 
                    WHERE cli.id = fc.cliente AND fc.id = fcd.id
                    AND (cli.rfc NOT LIKE 'XAXX010101000' OR cli.tipodepago = 'Monedero') 
                    AND (fcd.ticket = 0 OR fcd.ticket IS NULL) 
                    AND fc.status = " . StatusFactura::CERRADO . " 
                    AND DATE( fc.fecha ) BETWEEN DATE('$FechaI') AND DATE('$FechaF') ) f
                    GROUP BY tipodepago;";

        $cSql = "SELECT 
                V.cliente, V.alias, UPPER(V.tipodepago) tipodepago, V.movimientos, V.importe vendido, 
                IFNULL(F.importe,0.00) facturado, 
                ROUND(V.importe - IFNULL(F.importe, 0.00), 2) 'Porfacturar' 
                FROM 
                (
                    SELECT COUNT(*) movimientos, cliente, cli.alias, cli.tipodepago, 
                    ROUND(SUM(rm.importe),2) importe 
                    FROM rm LEFT JOIN cli ON rm.cliente=cli.id 
                    WHERE rm.fecha_venta BETWEEN " . str_replace("-", "", $FechaI) . " AND " . str_replace("-", "", $FechaF) . " 
                    AND rm.tipo_venta = 'D'
                    GROUP BY cli.tipodepago) V
                LEFT JOIN (
                    SELECT COUNT(*) movimientos,cliente,cli.alias,cli.tipodepago,
                    ROUND(SUM(rm.importe),2) importe 
                    FROM rm LEFT JOIN cli ON rm.cliente=cli.id 
                    WHERE rm.fecha_venta BETWEEN " . str_replace("-", "", $FechaI) . " AND " . str_replace("-", "", $FechaF) . " 
                    AND rm.uuid <> '-----' AND rm.tipo_venta='D' 
                    GROUP BY cli.tipodepago) F 
                ON F.tipodepago = V.tipodepago";

        $cSql_VtAditivos = "SELECT 
                V.cliente, V.alias, UPPER(V.tipodepago) tipodepago, V.movimientos, V.importe vendido, 
                IFNULL(F.importe,0.00) facturado, 
                ROUND(V.importe - IFNULL(F.importe, 0.00), 2) 'Porfacturar' 
                FROM 
                (
                    SELECT COUNT(*) movimientos, cliente, cli.alias, cli.tipodepago, 
                    ROUND(SUM(vta.total),2) importe 
                    FROM vtaditivos vta LEFT JOIN cli ON vta.cliente=cli.id 
                    WHERE DATE(vta.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')  
                    AND vta.tm = 'C'
                    GROUP BY cli.tipodepago) V
                LEFT JOIN (
                    SELECT COUNT(*) movimientos,cliente,cli.alias,cli.tipodepago,
                    ROUND(SUM(vta.total),2) importe 
                    FROM vtaditivos vta LEFT JOIN cli ON vta.cliente=cli.id 
                    WHERE  DATE(vta.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF') 
                    AND vta.uuid <> '-----' AND vta.tm='C' 
                    GROUP BY cli.tipodepago) F 
                ON F.tipodepago = V.tipodepago";

        $cSqlD = "SELECT V.tipodepago,V.producto,V.movimientos,V.vendido,
                F.facturado,(V.vendido-F.facturado) PorFacturar,
                (V.movimientos-F.movimientos) DespPorFacturar 
                FROM 
                (
                    SELECT COUNT(*) movimientos,upper(cli.tipodepago) tipodepago,com.descripcion producto,
                    ROUND(SUM(rm.importe),2) vendido
                    FROM com,rm LEFT JOIN cli ON rm.cliente=cli.id 
                    WHERE com.clavei = rm.producto AND  rm.fecha_venta BETWEEN " . str_replace("-", "", $FechaI) . " AND " . str_replace("-", "", $FechaF) . " 
                    AND rm.tipo_venta='D'
                    GROUP BY cli.tipodepago,rm.producto) V
                LEFT JOIN 
                (
                    SELECT  COUNT(*) movimientos,upper(cli.tipodepago) tipodepago,com.descripcion producto,
                    ROUND(SUM(rm.importe),2) facturado
                    FROM com,rm LEFT JOIN cli ON rm.cliente=cli.id
                    WHERE com.clavei = rm.producto AND rm.fecha_venta BETWEEN " . str_replace("-", "", $FechaI) . " AND " . str_replace("-", "", $FechaF) . " 
                    AND rm.uuid <> '-----' AND rm.tipo_venta='D'
                    GROUP BY cli.tipodepago,rm.producto) F 
                ON V.producto = F.producto AND V.tipodepago = F.tipodepago ";
        $cSqlGralClientes = "SELECT rm.tipodepago,COUNT(*) ventas,SUM(rm.importe)total,SUM(fcd.importe) total2,1 status FROM rm LEFT JOIN fcd ON fcd.ticket=rm.id
                            LEFT JOIN fc ON fc.id=fcd.id LEFT JOIN cli ON cli.id=fc.cliente WHERE fc.status=1 AND  cli.rfc  NOT LIKE '%XAXX010101000%' AND
                            rm.fecha_venta BETWEEN " . str_replace("-", "", $FechaI) . " AND " . str_replace("-", "", $FechaF) . "  AND rm.tipo_venta in ('D','N') AND rm.uuid!='-----'
                            GROUP BY rm.tipodepago;";
        $cSqlGral = "
                    SELECT tipodepago,ROUND(SUM(ventas),2) ventas,ROUND(SUM(total),2) total, 1 status 
                    FROM (
                        SELECT rm.tipodepago,COUNT(*) ventas,SUM(rm.importe)total,SUM(fcd.importe) total2 FROM rm LEFT JOIN fcd ON fcd.ticket=rm.id
                        LEFT JOIN fc ON fc.id=fcd.id LEFT JOIN cli ON cli.id=fc.cliente WHERE fc.status=1 AND  cli.rfc  LIKE '%XAXX010101000%' AND
                        rm.fecha_venta BETWEEN " . str_replace("-", "", $FechaI) . " AND " . str_replace("-", "", $FechaF) . "  AND cli.rfc LIKE '%XAXX010101000%'
                        AND rm.tipo_venta = 'D'  AND fc.status = " . StatusFactura::CERRADO . " AND rm.uuid!='-----'
                        GROUP BY rm.tipodepago
                    ) sub 
                    GROUP BY tipodepago";
        if ($Detallado === "Si") {
            $cSqlGral = "
                    SELECT fc.id,fc.folio,fc.cliente,cli.nombre,fc.fecha,fc.uuid,fc.formadepago,cliRm.tipodepago,
                    COUNT(*) ventas,DATE(rm.fin_venta) fecha_venta,
                    ROUND(SUM(fcd.importe),2) importe_fcd,
                    ROUND(SUM(rm.importe),2) importe_rm,
                    IF(fc.status = " . StatusFactura::CERRADO . ",fc.total,0) total,fc.status
                    FROM rm,fcd,fc,cli,cli cliRm 
                    WHERE 
                    rm.id = fcd.ticket AND fcd.id = fc.id AND fc.cliente = cli.id AND rm.cliente = cliRm.id AND
                    rm.fecha_venta BETWEEN " . str_replace("-", "", $FechaI) . " AND " . str_replace("-", "", $FechaF) . "  AND cli.rfc LIKE '%XAXX010101000%'
                    AND rm.tipo_venta = 'D' 
                    GROUP BY fc.id,cliRm.tipodepago
                    ORDER BY cliRm.tipodepago,fc.id";
        }



        $cSqlGralCero = "SELECT rm.cliente,cli.nombre,
                        COUNT(*) ventas,
                        ROUND(SUM(rm.pesos),2) pesos_rm,
                        ROUND(SUM(rm.importe),2) importe_rm,
                        'Cerrada' status
                        FROM rm,cli
                        WHERE 
                        rm.cliente = cli.id AND 
                        DATE(rm.fecha_venta) BETWEEN " . str_replace("-", "", $FechaI) . " AND " . str_replace("-", "", $FechaF) . "  AND rm.uuid = '-----'
                        AND rm.tipo_venta = 'D'
                        GROUP BY rm.cliente";
    }



    $aImporteFac = array(0, 0, 0, 0, 0);    //Contado, Credito, Tarjeta
    $Vta = $mysqli->query($cSql_);
    while ($rg = $Vta->fetch_array()) {

        if ($rg["tipodepago"] == "CONTADO") {
            $aImporteFac[0] = $rg["importe"];
        } elseif ($rg["tipodepago"] == "CREDITO") {
            $aImporteFac[1] = $rg["importe"];
        } elseif ($rg["tipodepago"] == "TARJETA") {
            $aImporteFac [2] = $rg["importe"];
        } elseif ($rg["tipodepago"] == "PREPAGO") {
            $aImporteFac [3] = $rg["importe"];
        } elseif ($rg["tipodepago"] == "MONEDERO") {
            $aImporteFac[4] = $rg["importe"];
        }
    }
}
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
            });

            function winieps(url) {
                window.open(url, 'miniwin', 'width=400,height=200,left=200,top=120,location=no');
            }
        </script>
    </head>

    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo) ?>
            <div id="Reportes" style="min-height: 150px;">
                <table aria-hidden="true">
                    <thead>
                        <tr class="titulo">
                            <td colspan="6">Desgloce por dia natural y tipo de pago</td>
                        </tr>
                        <tr>
                            <td>Tpo.venta</td>
                            <td>Despachos</td>
                            <td>Importe</td>
                            <td>Facturado</td>
                            <td>Por facturar</td>
                            <td>Fac.manuales</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (($Vta2 = $mysqli->query($cSql))) {
                            while ($rg = $Vta2->fetch_array()) {

                                echo "<tr>";

                                echo "<td>" . $rg["tipodepago"] . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg["movimientos"], 0) . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg["vendido"], 2) . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg ["facturado"], 2) . "</td>";
                                if ($rg["tipodepago"] == "CONTADO") {
                                    $nValor = $aImporteFac [0];
                                    echo "<td class=\"numero\">" . number_format($rg["Porfacturar"], 2) . "</td>";
                                    echo "<td class=\"numero\"><a class='textosCualli' href=javascript:window.winieps('facmanuales.php?tpo=1&op=$op&FechaI=$FechaI&FechaF=$FechaF&Fecha=$Fecha')>" . number_format($nValor, 2) . "</a></td>";
                                } elseif ($rg["tipodepago"] == "CREDITO") {
                                    $nValor = $aImporteFac[1];
                                    echo "<td class=\"numero\"> dato informativo </td>";
                                    echo "<td class=\"numero\"><a class='textosCualli' href=javascript:window.winieps('facmanuales.php?tpo=2&op=$op&FechaI=$FechaI&FechaF=$FechaF&Fecha=$Fecha')>" . number_format($nValor, 2) . "</a></td>";
                                } elseif ($rg["tipodepago"] == "TARJETA") {
                                    $nValor = $aImporteFac[2];
                                    echo "<td class=\"numero\">" . number_format($rg["Porfacturar"], 2) . "</td>";
                                    echo "<td class=\"numero\"><a class='textosCualli' href=javascript:window.winieps('facmanuales.php?tpo=4&op= $op&FechaI=$FechaI&FechaF=$FechaF&Fecha=$Fecha')>" . number_format($nValor, 2) . "</a></td>";
                                } elseif ($rg["tipodepago"] == "PREPAGO") {
                                    $nValor = $aImporteFac[3];
                                    echo "<td class=\"numero\"> dato informativo </td>";
                                    echo "<td class=\"numero\"><a class='textosCualli' href=javascript:window.winieps('facmanuales.php?tpo=3&op=$op&FechaI=$FechaI&FechaF=$FechaF&Fecha=$Fecha')>" . number_format($nValor, 2) . "</a></td>";
                                } elseif ($rg["tipodepago"] == "MONEDERO") {
                                    $nValor = $aImporteFac[4];
                                    echo "<td class=\"numero\"> dato informativo </td>";
                                    echo "<td class=\"numero\"><a class='textosCualli' href=javascript:window.winieps('facmanuales.php?tpo=3&op=$op&FechaI=$FechaI&FechaF=$FechaF&Fecha=$Fecha')>" . number_format($nValor, 2) . "</a></td>";
                                } else {
                                    echo "<td></td>";
                                    echo "<td></td>";
                                }
                                echo "</tr>";
                                $nTran += $rg["movimientos"];
                                $nFac += $rg["facturado"];
                                $Vendido += $rg["vendido"];
                            }
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>Total</td>
                            <td><?= number_format($nTran, 0) ?></td>
                            <td><?= number_format($Vendido, 2) ?></td>
                            <td><?= number_format($nFac, 2) ?></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div id="Reportes" style="min-height: 150px;">
                <table aria-hidden="true">
                    <thead>
                        <tr class="titulo">
                            <td colspan="7">Facturas de Público General</td>
                        </tr>
                        <tr>
                            <td>#</td>
                            <td>Folio</td>
                            <td>Fecha emisión</td>
                            <td>UUID</td>
                            <td>Ventas</td>
                            <td>Total</td>
                            <td>Status</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (($VtaGral = $mysqli->query($cSqlGral))) {
                            $GTotal = $GTotalVtas = $GTotalSub = $GTotalVtasSub = 0;
                            $cli = "";
                            $tipo = "";
                            $nRng = 1;
                            while ($rg = $VtaGral->fetch_array()) {

                                if ($cli <> $rg["cliente"]) {
                                    ?>
                                    <tr class="subtitulo">
                                        <td colspan="7">*** <?= $rg["cliente"] ?> | <?= $rg["nombre"] ?>***</td>
                                    </tr>
                                    <?php
                                }
                                $cli = $rg["cliente"];

                                if ($Detallado === "Si") {
                                    if ($tipo <> "") {
                                        if ($tipo !== $rg["tipodepago"]) {
                                            ?>
                                            <tr class="subtotal">
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td>Subtotal <?= $tipo ?></td>
                                                <td><?= number_format($GTotalVtasSub, 0) ?></td>
                                                <td><?= number_format($GTotalSub, 2) ?></td>
                                                <td></td>
                                            </tr>
                                            <?php
                                            $GTotalSub = $GTotalVtasSub = 0;
                                        }
                                    }
                                    $tipo = $rg["tipodepago"];

                                    echo "<tr title='$rg[fecha_venta]'>";
                                    echo "<td>" . number_format($nRng, 0) . "</td>";
                                    echo "<td>" . $rg["folio"] . "</td>";
                                    echo "<td>" . $rg["fecha"] . "</td>";
                                    echo "<td>" . $rg["uuid"] . "</td>";
                                    echo "<td class=\"numero\">" . number_format($rg["ventas"], 0) . "</td>";
                                    echo "<td class=\"numero\">" . number_format($rg["total"], 2) . "</td>";
                                    echo "<td>" . statusCFDI($rg["status"]) . "</td>";
                                    echo "</tr>";
                                } else {
                                    echo "<tr>";
                                    echo "<td>" . number_format($nRng, 0) . "</td>";
                                    echo "<td></td>";
                                    echo "<td>$FechaI al $FechaF</td>";
                                    echo "<td>" . $rg["tipodepago"] . "</td>";
                                    echo "<td class=\"numero\">" . number_format($rg["ventas"], 0) . "</td>";
                                    echo "<td class=\"numero\">" . number_format($rg["total"], 2) . "</td>";
                                    echo "<td>" . statusCFDI($rg["status"]) . "</td>";
                                    echo "</tr>";
                                }
                                $GTotalVtasSub += $rg["ventas"];
                                $GTotalSub += $rg["total"];
                                $GTotalVtas += $rg["ventas"];
                                $GTotal += $rg["total"];
                                $nRng++;
                            }
                        }
                        ?>

                        <tr class="subtotal">
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>Subtotal <?= $tipo ?></td>
                            <td><?= number_format($GTotalVtasSub, 0) ?></td>
                            <td><?= number_format($GTotalSub, 2) ?></td>
                            <td></td>
                        </tr>

                        <tr class="subtitulo">
                            <td colspan="7">*** 0 | Clientes varios ***</td>
                        </tr>

                        <?php
                        if (($VtaGralCliente = $mysqli->query($cSqlGralClientes))) {
                            $GTotalVtasSub = $GTotalSub = 0;
                            while ($rg = $VtaGralCliente->fetch_array()) {

                                echo "<tr>";
                                echo "<td>" . number_format($nRng, 0) . "</td>";
                                echo "<td></td>";
                                echo "<td></td>";
                                echo "<td>" . $rg["tipodepago"] . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg["ventas"], 0) . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg["total"], 2) . "</td>";
                                echo "<td>" . statusCFDI($rg["status"]) . "</td>";
                                echo "</tr>";

                                $GTotalVtasSub += $rg["ventas"];
                                $GTotalSub += $rg["total"];
                                $GTotalVtas += $rg["ventas"];
                                $GTotal += $rg["total"];
                                $nRng++;
                            }
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>Subtotal</td>
                            <td><?= number_format($GTotalVtasSub, 0) ?></td>
                            <td><?= number_format($GTotalSub, 2) ?></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>Total</td>
                            <td><?= number_format($GTotalVtas + $rgClientes["ventas"], 0) ?></td>
                            <td><?= number_format($GTotal + $rgClientes["importe_rm"], 2) ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>


            <div id="Reportes" style="min-height: 150px;">
                <table aria-hidden="true">
                    <thead>
                        <tr class="titulo"><td colspan="7">Desgloce por tipo de pago y producto</td></tr>
                        <tr>
                            <td> Tpo.venta</td>
                            <td> Producto</td>
                            <td> Despachos</td>
                            <td> Vendido</td>
                            <td> Facturado</td>
                            <td> Por facturar</td>
                            <td> Despachos</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (($Vta = $mysqli->query($cSqlD))) {

                            $TpoV = "";
                            while ($rg = $Vta->fetch_array()) {

                                if ($TpoV <> $rg["tipodepago"]) {
                                    if (!empty($TpoV)) {
                                        ?>
                                        <tr class="subtotal">
                                            <td colspan="2">Sub-total</td>
                                            <td><?= number_format($nTran, 0) ?></td>
                                            <td><?= number_format($nVen, 2) ?></td>
                                            <td><?= number_format($nFac, 2) ?></td>
                                            <td colspan="2"></td>
                                        </tr>
                                        <?php
                                    }
                                    $nTran = $nFac = $nVen = 0;
                                    $TpoV = $rg["tipodepago"];
                                }

                                $Producto = $rg["producto"];

                                echo "<tr>";
                                echo "<td>" . $rg["tipodepago"] . "</td>";
                                echo "<td>" . $Producto . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg["movimientos"], 0) . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg["vendido"], 2) . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg["facturado"], 2) . "</td>";
                                if ($rg["tipodepago"] == "CREDITO" || $rg["tipodepago"] == "PREPAGO") {
                                    echo "<td>dato informativo</td>";
                                } else {
                                    echo "<td>" . number_format($rg["PorFacturar"], 2) . "</td>";
                                }
                                echo "<td class=\"numero\">" . number_format($rg["DespPorFacturar"], 0) . "</td>";
                                echo "</tr>";
                                $nTranT += $rg["movimientos"];
                                $nTran += $rg["movimientos"];
                                $nVen += $rg["vendido"];
                                $nFac += $rg["facturado"];
                                $nFacT += $rg["facturado"];
                                $nRng++;
                            }
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2">Sub-total: </td>
                            <td><?= number_format($nTran, 0) ?></td>
                            <td><?= number_format($nVen, 2) ?></td>
                            <td><?= number_format($nFac, 2) ?></td>
                            <td colspan="2"></td>
                        </tr>
                        <tr>

                            <td></td>
                            <td>Total</td>
                            <td><?= number_format($nTranT, 0) ?></td>
                            <td><?= number_format($Vendido, 2) ?></td>
                            <td><?= number_format($nFacT, 2) ?></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div id="Reportes" style="min-height: 150px;">
            <table aria-hidden="true">
                <thead>
                    <tr class="titulo">
                        <td colspan="6">Desgloce por dia natura venta de aditivos</td>
                    </tr>
                    <tr>
                        <td>Tpo.venta</td>
                        <td>Despachos</td>
                        <td>Importe</td>
                        <td>Facturado</td>
                        <td>Por facturar</td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (($Vta2 = $mysqli->query($cSql_VtAditivos))) {
                        while ($rg = $Vta2->fetch_array()) {

                            echo "<tr>";

                            echo "<td>" . $rg["tipodepago"] . "</td>";
                            echo "<td class=\"numero\">" . number_format($rg["movimientos"], 0) . "</td>";
                            echo "<td class=\"numero\">" . number_format($rg["vendido"], 2) . "</td>";
                            echo "<td class=\"numero\">" . number_format($rg [facturado], 2) . "</td>";
                            if ($rg["tipodepago"] == "CONTADO") {
                                $nValor = $aImporteFac [0];
                                echo "<td class=\"numero\">" . number_format($rg["Porfacturar"], 2) . "</td>";
                            } elseif ($rg["tipodepago"] == "CREDITO") {
                                $nValor = $aImporteFac[1];
                                echo "<td class=\"numero\"> dato informativo </td>";
                            } elseif ($rg["tipodepago"] == "TARJETA") {
                                $nValor = $aImporteFac[2];
                                echo "<td class=\"numero\">" . number_format($rg["Porfacturar"], 2) . "</td>";
                            } elseif ($rg["tipodepago"] == "PREPAGO") {
                                $nValor = $aImporteFac[3];
                                echo "<td class=\"numero\"> dato informativo </td>";
                            } elseif ($rg["tipodepago"] == "MONEDERO") {
                                $nValor = $aImporteFac[4];
                                echo "<td class=\"numero\"> dato informativo </td>";
                            } else {
                                echo "<td></td>";
                            }
                            echo "</tr>";
                            $nTranAd += $rg["movimientos"];
                            $nFacAd += $rg["facturado"];
                            $VendidoAd += $rg["vendido"];
                        }
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td><?= number_format($nTranAd, 0) ?></td>
                        <td><?= number_format($VendidoAd, 2) ?></td>
                        <td><?= number_format($nFacAd, 2) ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
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
                                <table aria-hidden="true">
                                    <tr>
                                        <td style="white-space: nowrap">
                                            Detallado: 
                                            <select name="Detallado" id="Detallado">
                                                <option value="Si">Si</option>
                                                <option value="No">No</option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <span><input type="submit" name="Boton" value="Enviar"></span>
                                <?php
                                if ($usuarioSesion->getTeam() !== "Operador") {
                                    ?>
                                    <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                                            <?php
                                        }
                                        ?>
                            </td>
                            <td>
                                <a href="repvtasfacd.php?" title="Facturación pendiente por mes">Pendiente</a> 
                            </td>
                        </tr>
                    </table>
                </div>
            </form>

            <?php topePagina() ?>
        </div>

    </body>

</html>

