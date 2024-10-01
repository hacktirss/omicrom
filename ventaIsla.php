<?php
session_start();
include_once ("libnvo/lib.php");
include_once ("data/IslaDAO.php");
include_once ("data/CtDAO.php");
include_once ("data/ClientesDAO.php");
define("IDTAREA", -100);

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$islaDAO = new IslaDAO();
$ctDAO = new CtDAO();

$islaVO = $islaDAO->retrieve(1, "isla");
$Corte = $islaVO->getCorte();
$ctVO = $ctDAO->retrieve($Corte);

$selectComandos = "SELECT posicion,ejecucion FROM comandos WHERE idtarea = " . IDTAREA . " AND ejecucion = 1 ORDER BY posicion";

if (($result = $mysqli->query($selectComandos))) {
    while ($Pos = $result->fetch_array()) {
        if ($Pos["ejecucion"] > 0) {
            $insert = "INSERT INTO ct_parcial (posicion, manguera, producto, v1, v2, v3, tv1, tv2, tv3, m1, m2, m3, tm1, tm2, tm3, dm1, dm2, dm3, dv1, dv2, dv3, ventas, vendedor, pesos, volumen)
                       SELECT 
                        ctd.posicion,
                        m.manguera,
                        com.descripcion producto,
                        ROUND(ctd.ivolumen1,2) v1,
                        ROUND(ctd.ivolumen2,2) v2,
                        ROUND(ctd.ivolumen3,2) v3,
                        ROUND((tot.volumen1),2) tv1,
                        ROUND((tot.volumen2),2) tv2,
                        ROUND((tot.volumen3),2) tv3,
                        ROUND(ctd.imonto1,2) m1,
                        ROUND(ctd.imonto2,2) m2,
                        ROUND(ctd.imonto3,2) m3,
                        ROUND((tot.monto1),2) tm1,
                        ROUND((tot.monto2),2) tm2,
                        ROUND((tot.monto3),2) tm3,
                        ROUND((tot.monto1 - ctd.imonto1),2) dm1,
                        ROUND((tot.monto2 - ctd.imonto2),2) dm2,
                        ROUND((tot.monto3 - ctd.imonto3),2) dm3,
                        ROUND((tot.volumen1 - ctd.ivolumen1),2) dv1,
                        ROUND((tot.volumen2 - ctd.ivolumen2),2) dv2,
                        ROUND((tot.volumen3 - ctd.ivolumen3),2) dv3,
                        COUNT(rm.id) ventas,
                        IFNULL(rm.vendedor,0) vendedor,
                        IFNULL(ROUND(SUM(rm.pesos),2),0) pesos,
                        IFNULL(ROUND(SUM(rm.volumen),2),0) volumen
                        FROM ctd,totalizadores tot,com,man_pro m 
                        LEFT JOIN rm ON m.posicion = rm.posicion AND m.manguera = rm.manguera AND rm.corte = (SELECT corte FROM islas WHERE activo='Si')
                        WHERE
                        ctd.id = (SELECT corte FROM islas WHERE activo='Si')
                        AND ctd.posicion = tot.posicion
                        AND ctd.posicion = m.posicion
                        AND m.producto = com.clavei
                        AND m.activo = 'Si'
                        AND tot.idtarea = " . IDTAREA . "
                        AND ctd.posicion = " . $Pos["posicion"] . " 
                        GROUP BY rm.posicion,rm.manguera";

            $sqlCParcial = "SELECT IFNULL(posicion,0) p FROM ct_parcial WHERE posicion = '" . $Pos["posicion"] . "';";
            $showPos = $mysqli->query($sqlCParcial)->fetch_array();

            if ($showPos["p"] == 0) {
                if (!($mysqli->query($insert))) {
                    error_log($mysqli->error);
                }
            }
        }
    }
}

$selectLecturas = " 
                SELECT man.isla_pos isla,ctp.posicion, ctp.manguera, ctp.producto combustible, ctp.v1,ctp.v2,ctp.v3,
                ctp.tv1,ctp.tv2,ctp.tv3, ctp.m1,ctp.dm2,ctp.dm3, ctp.dv1,ctp.dv2,ctp.dv3,
                ctp.ventas, ctp.vendedor, ctp.pesos importe, ctp.volumen litros
                FROM ct_parcial ctp, man
                WHERE man.activo = 'Si' AND ctp.posicion = man.posicion
                ORDER BY ctp.posicion, ctp.manguera ASC;";

$selectDepositos = "
                SELECT man.isla_pos,ctdep.id folio,ctdep.fecha,ven.alias nombre,ctdep.total importe
                FROM ct, ctdep, ven, man
                WHERE ct.id = ctdep.corte AND ctdep.despachador = ven.id 
                AND ven.id = man.despachador AND man.activo = 'Si' AND ctdep.total > 0
                AND ctdep.corte =  $Corte ";

$selectInventario = "
                SELECT producto, categoria, SUM(inicial) inicial, SUM(ventas) ventas, SUM(importe) importe, 
                SUM(entradas) entradas, SUM(total) total FROM (
                    SELECT inv.id producto, inv.categoria, man.isla_pos, inv.descripcion,
                    IFNULL(SUM(ini.cantidad),0) inicial,
                    IFNULL(vt.cantidad,0) ventas, IFNULL(vt.importe,0) importe, IFNULL(ets.cantidad,0) entradas,
                    (IFNULL(SUM(ini.cantidad),0) - IFNULL(vt.cantidad,0) + IFNULL(ets.cantidad,0)) total
                    FROM man
                    LEFT JOIN inv ON TRUE AND inv.activo = 'Si' AND inv.rubro = 'Aceites'
                    LEFT JOIN (
                            SELECT vt.clave producto, man.isla_pos, IFNULL(SUM(IF(vt.tm = 'C', -vt.cantidad, vt.cantidad)),0) cantidad 
                            FROM vtaditivos vt,man 
                            WHERE 1= 1
                            AND vt.posicion = man.posicion
                            AND vt.corte < $Corte
                            AND vt.posicion > 0 AND vt.cantidad > 0
                            GROUP BY vt.clave,man.isla_pos
                    ) ini ON inv.id = ini.producto AND ini.isla_pos = man.isla_pos
                    LEFT JOIN (
                            SELECT vt.clave producto, man.isla_pos, IFNULL(SUM(vt.cantidad),0) cantidad, IFNULL(SUM(vt.total),0) importe 
                            FROM vtaditivos vt,man 
                            WHERE 1= 1
                            AND vt.posicion = man.posicion
                            AND vt.corte = $Corte
                            AND vt.posicion > 0 AND vt.cantidad > 0
                            AND vt.tm = 'C'
                            GROUP BY vt.clave,man.isla_pos
                    ) vt ON inv.id = vt.producto AND vt.isla_pos = man.isla_pos
                    LEFT JOIN (
                            SELECT vt.clave producto, man.isla_pos, IFNULL(SUM(vt.cantidad),0) cantidad 
                            FROM vtaditivos vt ,man 
                            WHERE 1= 1
                            AND vt.posicion = man.posicion
                            AND vt.corte = $Corte
                            AND vt.posicion > 0 AND vt.cantidad > 0
                            AND vt.tm = 'H'
                            GROUP BY vt.clave,man.isla_pos
                    ) ets ON inv.id = ets.producto AND ets.isla_pos = man.isla_pos
                    WHERE 1= 1
                    AND man.activo = 'Si' AND man.inventario = 'Si'
                ";

$selectVentaClientes = "
                SELECT cli.tipodepago, COUNT(rm.id) ventas, 
                ROUND(SUM(rm.volumen), 2) volumen, ROUND(SUM(rm.pagoreal), 2) importe
                FROM man, rm, cli
                WHERE TRUE AND man.posicion = rm.posicion AND rm.cliente = cli.id
                AND man.activo = 'Si' AND rm.tipo_venta IN ('D','N')
                AND rm.corte = $Corte ";

$selectVentaJarreos = "
                SELECT COUNT(rm.id) ventas, 
                ROUND(SUM(rm.volumen), 2) volumen, ROUND(SUM(rm.pesos), 2) importe
                FROM man, rm
                WHERE TRUE AND man.posicion = rm.posicion
                AND man.activo = 'Si' AND rm.tipo_venta IN ('J','A')
                AND rm.corte = $Corte ";

$lectura = utils\IConnection::getRowsFromQuery($selectLecturas);

$IslaPosicion = "";
$auxiliar = "";
foreach ($lectura as $value) {
    if ($auxiliar !== $value["isla"]):
        $IslaPosicion = empty($IslaPosicion) ? $value["isla"] : ($IslaPosicion . "," . $value["isla"]);
    endif;
    $auxiliar = $value["isla"];
}

$selectInventario .= " 
                    AND man.isla_pos IN($IslaPosicion)
                    GROUP BY inv.id , man.isla_pos
                    ORDER BY inv.id ASC 
                ) SUB GROUP BY producto ;";

$selectDepositos .= "                
                AND man.isla_pos IN ($IslaPosicion)
                GROUP BY man.isla_pos, ctdep.id";

$selectVentaClientes .= "
                AND man.isla_pos IN ($IslaPosicion)
                GROUP BY cli.tipodepago
                ORDER BY cli.tipodepago;";

$selectVentaJarreos .= "
                AND man.isla_pos IN ($IslaPosicion);";

if (!empty($IslaPosicion)) {
    //error_log($selectInventario);
    $registros = utils\IConnection::getRowsFromQuery($selectInventario);

    $depositos = utils\IConnection::getRowsFromQuery($selectDepositos);

    $clientes = utils\IConnection::getRowsFromQuery($selectVentaClientes);

    $jarreos = utils\IConnection::getRowsFromQuery($selectVentaJarreos);
}
$registrosArray = array();
foreach ($registros as $value) {
    $registrosArray[$value["producto"]][$value[isla_pos]]["inicial"] = $value["inicial"];
    $registrosArray[$value["producto"]][$value[isla_pos]]["entradas"] = $value["entradas"];
    $registrosArray[$value["producto"]][$value[isla_pos]]["ventas"] = $value["ventas"];
    $registrosArray[$value["producto"]][$value[isla_pos]]["total"] = $value["total"];
}
//error_log(print_r($registrosArray, TRUE));
/* * *****************Bloque de codigo de programa************************** */
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">    
    <head>
        <title>Venta por Isla Ajax</title>
        <style>
            #Reportes{
                width: 100%;
                min-height: 280px;
                text-align: center;
                margin-left: auto;
                margin-right: auto;

            }
            #Reportes > table{
                width: 100%;
                border-collapse: separate;
                font-family: Arial, Helvetica, sans-serif;
                font-size: 11px;
                color: #55514e;
            }
            #Reportes > table > thead > tr > td{
                height: 25px;
                background-color: white;
                border-bottom: solid 2px gray;
                font-weight: bold;
                text-align: center;
            }
            #Reportes > table > thead > tr > td > a{
                text-decoration: none;
                color: #55514e;
                font-weight: bold;
            }
            #Reportes > table > thead > tr.titulo > td{
                background-color: var(--GrisClaro);
                border-bottom: solid 2px white;
            }
            #Reportes > table > tbody > tr > td{
                padding-left: 5px;
                padding-right: 5px;
                text-align: left;
            }
            #Reportes > table > tbody > tr > td.overflow{
                max-width: 200px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            #Reportes > table > tbody > tr:nth-child(odd) {
                background-color: #DADADA;
            }

            #Reportes > table > tbody > tr:nth-child(even) {
                background-color: white;
            }

            #Reportes > table > tbody > tr:nth-child(odd):hover {
                background-color: #ACECAA;
            }

            #Reportes > table > tbody > tr:nth-child(even):hover {
                background-color: #ACECAA;
            }
            #Reportes > table > tbody > tr.titulos > td{
                height: 25px;
                background-color: white;
                border-bottom: solid 2px gray;
                font-weight: bold;
                text-align: center;
            }
            #Reportes > table > tbody > tr.subtotal > td{
                height: 25px;
                background-color: white;
                border-top: solid 2px gray;
                font-weight: bold;
                text-align: right;
                padding-bottom: 10px;
            }
            #Reportes > table > tbody > tr.titulo > td{
                height: 25px;
                background-color: var(--GrisClaro);
                font-weight: bold;
                text-align: right;
                text-align: center;
            }
            #Reportes > table > tbody > tr.subtitulo > td{
                height: 25px;
                background-color: white;
                font-weight: bold;
                text-align: right;
                text-align: center;
            }
            #Reportes > table > tbody > tr > td.numero,.moneda{
                text-align: right;
            }
            #Reportes > table > tbody > tr > td.remarcar{
                background-color: #F7FF7C;
            }
            #Reportes > table > tbody > tr > td.moneda:before{
                content: "$ ";
            }
            #Reportes > table > tbody > tr > td.overflow{
                max-width: 100px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            #Reportes > table > tfoot > tr > td{
                height: 25px;
                background-color: white;
                font-weight: bold;
                text-align: right;
                padding-left: 5px;
                padding-right: 5px;
            }
            #Reportes > table > tfoot > tr:first-child > td{
                border-top: solid 2px gray;
                padding-bottom: 10px;
            }
            #Reportes > table > tfoot > tr > td.moneda:before{
                content: "$ ";
            }
        </style>
    </head>
    <body>
        <div style="width: 100%; text-align: left;font-size: 11px;">
            <div>Parcial del corte: <?= $Corte ?></div>
            <?php
            $countIslas = 0;
            $isla = $posicion = "";
            $importeCombustible = 0;
            foreach ($lectura as $value) :
                $manguera = $value["manguera"];
                ?>
                <?php if ($isla !== $value["isla"]): ?>
                    <div style="margin-top: 10px;">Isla: <?= $value["isla"] ?></div>
                    <div>Inicio de corte: <?= $ctVO->getFecha() ?></div>
                    <div>Fecha de consulta: <?= date("Y-m-d H:i:s") ?></div>
                    <?php
                    $posicion = "";
                endif;

                if ($posicion !== $value["posicion"]):
                    ?>
                    <div style="margin-top: 10px;">Posición de carga: <?= $value["posicion"] ?></div>
                <?php endif; ?>
                <div style="padding-top: 3px;"><?= $value["combustible"] ?> Ini: <?= $value["v" . $manguera] ?> Fin: <?= $value["tv" . $manguera] ?></div>
                <div>Lts. <?= $value["litros"] ?> $<?= $value["importe"] ?></div>
                <?php
                $isla = $value["isla"];
                $posicion = $value["posicion"];
                $importeCombustible += $value["importe"];
                ?>
            <?php endforeach; ?>

            <div style="margin-top: 10px;">Total de combustible: $<?= number_format($importeCombustible, 2) ?></div>

            <div><hr></div>

            <div  style="margin-top: 10px;">Inventario de aceites</div>

            <div style="margin-top: 10px;">Islas comprometidas: <?= $IslaPosicion ?></div>
            <div id="Reportes" style="min-height: 50px;">
                <table  aria-hidden="true">
                    <thead>
                        <tr>
                            <td>Producto</td>
                            <td>Inicial</td>
                            <td>Entrada</td>
                            <td>Vendido</td>
                            <td>Final</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $importeAceites = 0;
                        $aceites = $pzAceites = 0;
                        $aditivos = $pzAditivos = 0;
                        $Inicial = $Ventas = $Compras = $Total = 0;
                        foreach ($registros as $inv) :
                            ?>
                            <tr>
                                <td><?= $inv["producto"] ?></td>
                                <td class="numero"><?= $registrosArray[$inv["producto"]][$inv["isla_pos"]]["inicial"] ?></td>
                                <td class="numero"><?= $registrosArray[$inv["producto"]][$inv["isla_pos"]]["entradas"] ?></td>
                                <td class="numero"><?= $registrosArray[$inv["producto"]][$inv["isla_pos"]]["ventas"] ?></td>
                                <td class="numero"><?= $registrosArray[$inv["producto"]][$inv["isla_pos"]]["total"] ?></td>
                            </tr>
                            <?php
                            $Inicial += $registrosArray[$inv["producto"]][$inv["isla_pos"]]["inicial"];
                            $Compras += $registrosArray[$inv["producto"]][$inv["isla_pos"]]["entradas"];
                            $Ventas += $registrosArray[$inv["producto"]][$inv["isla_pos"]]["ventas"];
                            $Total += $registrosArray[$inv["producto"]][$inv["isla_pos"]]["total"];

                            if ($inv["categoria"] === "Aceites") {
                                $aceites += $inv["importe"];
                                $pzAceites += $registrosArray[$inv["producto"]][$inv["isla_pos"]]["ventas"];
                            } else {
                                $aditivos += $inv["importe"];
                                $pzAditivos += $registrosArray[$inv["producto"]][$inv["isla_pos"]]["ventas"];
                            }
                            $importeAceites += $inv["importe"];
                        endforeach;
                        ?>
                    </tbody>
                    <tfoot>
                        <tr style="border-top: 1px solid gray;">
                            <td>Total</td>
                            <td><?= $Inicial ?></td>
                            <td><?= $Compras ?></td>
                            <td><?= $Ventas ?></td>
                            <td><?= $Total ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div style="margin-top: 10px;">Aceites: <?= $pzAceites ?> pzas. / $<?= number_format($aceites, 2) ?></div>
            <div style="margin-top: 10px;">Aditivos: <?= $pzAditivos ?> pzas. / $<?= number_format($aditivos, 2) ?></div>
            <div style="margin-top: 10px;">Total: $<?= number_format($importeAceites, 2) ?></div>

            <div><hr></div>

            <div style="margin-top: 10px;">Gran Total: $<?= number_format($importeCombustible + $importeAceites, 2) ?></div>

            <div><hr></div>

            <div style="margin-top: 10px;">Depositos</div>

            <div id="Reportes" style="min-height: 50px;">
                <table  aria-hidden="true">
                    <thead>
                        <tr>
                            <td>Folio</td>
                            <td>Fecha</td>
                            <td>Vendedor</td>
                            <td>Importe</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $importeDepositos = 0;
                        foreach ($depositos as $dep) :
                            ?>
                            <tr>
                                <td><?= $dep["folio"] ?></td>
                                <td><?= $dep["fecha"] ?></td>
                                <td><?= $dep["nombre"] ?></td>
                                <td class="numero"><?= $dep["importe"] ?></td>
                            </tr>
                            <?php
                            $importeDepositos += $dep["importe"];
                        endforeach;
                        ?>
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 10px;">Total de depositos: $<?= number_format($importeDepositos, 2) ?></div>

            <div><hr></div>

            <div style="margin-top: 10px;">Venta por tipo de pago</div>

            <div id="Reportes" style="min-height: 50px;">
                <table  aria-hidden="true">
                    <thead>
                        <tr>
                            <td>Tipo</td>
                            <td>Ventas</td>
                            <td>Litros</td>
                            <td>Importe</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $importeClientes = 0;
                        $noEfectivo = 0;
                        foreach ($clientes as $cli) :
                            ?>
                            <tr>
                                <td><?= $cli["tipodepago"] ?></td>
                                <td class="numero"><?= $cli["ventas"] ?></td>
                                <td class="numero"><?= $cli["volumen"] ?></td>
                                <td class="numero"><?= $cli["importe"] ?></td>
                            </tr>
                            <?php
                            if ($cli["tipodepago"] === TiposCliente::CREDITO || $cli["tipodepago"] === TiposCliente::PREPAGO || $cli["tipodepago"] === TiposCliente::TARJETA || $cli["tipodepago"] === TiposCliente::MONEDERO) {
                                $noEfectivo += $cli["importe"];
                            }
                            $importeClientes += $cli["importe"];
                        endforeach;

                        $importeJarreos = is_array($jarreos) ? $jarreos[0]["importe"] : 0;
                        ?>
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 10px;">Total por tipo de cliente: $<?= number_format($importeClientes, 2) ?></div>

            <div><hr><p style="text-align: center">TOTALES</p><hr></div>

            <div style="margin-top: 10px;">Venta total: $<?= number_format($importeCombustible + $importeAceites, 2) ?></div>
            <div style="margin-top: 10px;">Venta a crédito: $<?= number_format($noEfectivo, 2) ?></div>
            <div style="margin-top: 10px;">Jarreos: $<?= number_format($importeJarreos, 2) ?></div>
            <div style="margin-top: 10px;">Depositos: $<?= number_format($importeDepositos, 2) ?></div>
            <div style="margin-top: 10px;">Efectivo: $<?= number_format(($importeCombustible + $importeAceites) - $importeDepositos - $noEfectivo - $importeJarreos, 2) ?></div>

            <div><hr></div>

            <div><p style="text-align: center">SISTEMA DE CONTROL VOLUMETRICO OMICROM</p></div>
        </div>
    </body>
</html>
<?php
mysqli_close($mysqli);
