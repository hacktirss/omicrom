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

if ($request->getAttribute("Pedido") === "PP") {
    $SQL = "SELECT p.id,fecha,volumen,name FROM pedidos p LEFT JOIN authuser ON p.id_user=authuser.id "
            . "WHERE p.status = 1 AND DATE(fecha) between DATE('" . $request->getAttribute("Ini") . "') AND DATE('" . $request->getAttribute("Fin") . "');";
} else if ($request->getAttribute("Pedido") === "PA") {
    $SQL = "SELECT p.id,fecha,volumen,name FROM pedidos p LEFT JOIN authuser ON p.id_user=authuser.id "
            . "WHERE p.status = 2 AND DATE(fecha) between DATE('" . $request->getAttribute("Ini") . "') AND DATE('" . $request->getAttribute("Fin") . "');";
} else if ($request->getAttribute("Pedido") === "PEP") {
    $SQL = "SELECT p.id,fecha,volumen,name FROM pedidos p LEFT JOIN authuser ON p.id_user=authuser.id "
            . "WHERE fecha < now() AND fechafin > now()  AND DATE(fecha) between DATE('" . $request->getAttribute("Ini") . "') AND DATE('" . $request->getAttribute("Fin") . "');";
} else if ($request->getAttribute("Pedido") === "PC") {
    $SQL = "SELECT p.id,fecha,volumen,name FROM pedidos p LEFT JOIN authuser ON p.id_user=authuser.id "
            . "WHERE p.status = 5 AND DATE(fecha) between DATE('" . $request->getAttribute("Ini") . "') AND DATE('" . $request->getAttribute("Fin") . "');";
}
error_log($SQL);
?>

<div id="scroll">
    <?php
    $Rs = utils\IConnection::getRowsFromQuery($SQL);
    foreach ($Rs as $rst) {
        ?>
        <div class="PedidosClass2" data-id-registro="<?= $rst["id"] ?>" id="<?= $rst["id"] ?>" onclick="LevelTwo()">
            Nombre : <?= $rst["name"] ?><br>
            Fecha : <?= $rst["fecha"] ?><br>
            Volumen: <?= $rst["volumen"] ?><br>
        </div>
        <?php
    }
    ?>
</div>