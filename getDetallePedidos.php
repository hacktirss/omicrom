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

$Sql = "SELECT p.id,name nombre,com.descripcion,fecha,fechafin,volumen,producto,pc.descripcion terminal,p.status "
        . "FROM pedidos p LEFT JOIN authuser a ON p.id_user=a.id LEFT JOIN com ON p.producto=com.clavei "
        . "LEFT JOIN permisos_cre pc ON p.terminal_almacenamiento=pc.id "
        . "WHERE p.id = " . $request->getAttribute("IdPedido");
error_log($Sql);
$rst = utils\IConnection::execSql($Sql);
if ($rst["status"] == 1) {
    $sts = "Nueva";
} else if ($rst["status"] == 2) {
    $sts = "Aceptada";
} else if ($rst["status"] == 5) {
    $sts = "Cancelada";
}
?>
<div class="PedidosClass3">
    No. Pedido <?= $rst["id"] ?><br>
    Nombre : <?= $rst["nombre"] ?><br>
    Producto : <?= $rst["descripcion"] ?><br>
    Volumen : <?= $rst["volumen"] ?><br>
    Fecha Salida : <?= $rst["fecha"] ?><br>
    Fecha Llegada : <?= $rst["fechafin"] ?><br>
    Terminal : <?= $rst["terminal"] ?><br>
    Status : <?= $sts?>
</div>