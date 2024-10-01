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

if ($request->getAttribute("Origen") === "Estado") {
    $Sql = "SELECT localidad as id,localidad, descripcion FROM cp_localidad WHERE estado = '" . $dt . "'";
    $Sql2 = "SELECT localidad clave, descripcion FROM cp_municipio WHERE estado = '" . $dt . "'";
    $display = utils\IConnection::getRowsFromQuery($Sql);
    $display2 = utils\IConnection::getRowsFromQuery($Sql2);
    $jsonString = [$display, $display2];
} else if ($request->getAttribute("Origen") === "CodigoPostal") {
    $Sql = "SELECT colonia,codigo_postal, nombre FROM cp_colonia WHERE codigo_postal = '" . $dt . "'";
    $jsonString = utils\IConnection::getRowsFromQuery($Sql);
} else if ($request->getAttribute("Origen") === "IdOrigen") {
    if ($dt === 'V') {
        $Sql = "SELECT id, placa nombre FROM catalogo_vehiculos";
    } elseif ($dt === "O") {
        $Sql = "SELECT id, nombre FROM catalogo_operadores";
    } elseif ($dt === "C") {
        $Sql = "SELECT id, nombre FROM cli WHERE activo = 'Si'";
    }elseif ($dt === "P") {
        $Sql = "SELECT id, nombre FROM prv WHERE activo = 'Si'";
    }
    $jsonString = utils\IConnection::getRowsFromQuery($Sql);
}


echo json_encode($jsonString);
