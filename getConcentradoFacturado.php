<?php
header("Cache-Control: no-cache,no-store");
$wsdl = 'http://localhost:9080/DetiPOS/detisa/services/DetiPOS?wsdl';

include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();

$dt = $request->getAttribute("Var");

$jsonString = Array();
$jsonString["success"] = false;
$jsonString["message"] = "";

$MesSig = "SELECT count(*) movimientos,ROUND(SUM(rm.importe),2) importe, rm.producto,fc.serie
            FROM rm left join fcd on fcd.ticket=rm.id left join fc on fcd.id=fc.id
            WHERE month(date(rm.fecha_venta)) = " . $request->getAttribute("MesNum")
        . " AND YEAR(date(rm.fecha_venta)) = " . $request->getAttribute("Anio") . "
            AND rm.uuid <> '-----' AND rm.tipo_venta='D' AND fc.status = 1 AND fcd.producto < 5 group by serie;";
//echo $MesSig;
$MesS = utils\IConnection::getRowsFromQuery($MesSig);
?>
<table style="width: 100%;color: #626262; font-family: sans-serif;font-size: 13px;margin-top: 25px;border: 1px solid  #999999;border-radius: 5px;"
       summary="Concentrado">
    <tr style="font-size: 15px;font-weight: bold;height: 25px;background-color: #B7C5D3;">
        <th >Serie</th>
        <th>Movimientos</th>
        <th>Importe</th>
    </tr>
    <?php
    foreach ($MesS as $Mr) {
        ?>
        <tr style="background-color: #EDBB99">
            <td style="text-align: left;"><?= $Mr["serie"] ?></td>
            <td><?= $Mr["movimientos"] ?></td>
            <td style="text-align: right;">$<?= number_format($Mr["importe"], 2) ?></td>
        </tr>
        <?php
        if ($request->getAttribute("Op") == 2) {
            $Tt = 0;
            $Vtas = "SELECT rm.id,rm.importe,rm.inicio_venta
            FROM rm left join fcd on fcd.ticket=rm.id left join fc on fcd.id=fc.id
            WHERE month(date(rm.fecha_venta)) = " . $request->getAttribute("MesNum")
                    . " AND YEAR(date(rm.fecha_venta)) = " . $request->getAttribute("Anio") . " AND serie= '" . $Mr["serie"] . "'
            AND rm.uuid <> '-----' AND rm.tipo_venta='D' AND fc.status = 1 AND fcd.producto < 5;";

            $vt = utils\IConnection::getRowsFromQuery($Vtas);
            foreach ($vt as $v) {
                ?>
                <tr>
                    <td>Ticket.-<?= $v["id"] ?></td>
                    <td><?= $v["inicio_venta"] ?></td>
                    <td style="text-align: right;"><?= number_format($v["importe"], 2) ?></td>
                </tr>
                <?php
                $Tt += $v["importe"];
            }
            ?>
            <tr>
                <td></td>
                <td></td>
                <td style="text-align: right;font-weight: bold;">Total <?= number_format($Tt, 2) ?></td>
            </tr>
            <?php
        }
    }
    ?>
</table>
