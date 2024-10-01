<?php

include_once ("../../softcoatl/SoftcoatlHTTP.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$connection = utils\IConnection::getConnection();
$jsonString = array();

$jsonString["Response"] = false;

$Cliente = $request->getAttribute("Cliente");
$IdPago = $request->getAttribute("IdPago");

$selectCliente = "SELECT * FROM unidades u LEFT join (SELECT idUnidad,SUM(importeDelPago) importeDelPago"
        . " FROM unidades_log WHERE noPago = " . $IdPago . " GROUP BY idUnidad) ul ON u.id = ul.idUnidad WHERE u.periodo='B' AND u.cliente = $Cliente;";

$rows_ = utils\IConnection::getRowsFromQuery($selectCliente, $connection);
foreach ($rows_ as $value) {
    $jsonString["rows"][] = $value;
}

$selectCliente = "SELECT idUnidad,SUM(importeDelPago) importeDelPago FROM unidades_log WHERE noPago = " . $IdPago . " GROUP BY idUnidad";
$rows_ = utils\IConnection::getRowsFromQuery($selectCliente, $connection);
foreach ($rows_ as $value) {
    $jsonString["listaLog"][] = $value;
}

$selectCliente = "SELECT aplicado FROM pagos WHERE id = " . $IdPago;
$rows_ = utils\IConnection::getRowsFromQuery($selectCliente, $connection);
foreach ($rows_ as $value) {
    $jsonString["PagoAplicado"][] = $value;
}


$jsonString["Response"] = true;

if (is_null($jsonString)) :
    error_log(json_last_error());
endif;

echo json_encode($jsonString);

