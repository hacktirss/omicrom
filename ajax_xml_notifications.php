<?php
error_reporting(0);
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;
$connection = iconnect();

$arrayJson = Array();
$request = utils\HTTPUtils::getRequest();

if ($request->hasAttribute("key")) {
    $key = $request->getAttribute("key");
    
    $varCategory = "category";
    $varId = "id";
    $varMessage = "message";
    
    if ($key === "pipes") {
        $sqlCargas = "SELECT * FROM cargas WHERE cargas.entrada = 0 ORDER BY fecha_fin ASC;";

        $qryCargas = $connection->query($sqlCargas);

        while ($fetchCargas = $qryCargas->fetch_array()) {
            $category = "pipes";
            $id = $fetchCargas['id'];
            $message = "Carga de " . $fetchCargas['producto'] . " ingresada el: " . $fetchCargas['fecha_fin'];
            $item = array($varCategory => $category, $varId => $id, $varMessage => $message);
            array_push($arrayJson, $item);
        }
    } elseif ($key === "alerts") {
        $sqlAlertas = "SELECT COUNT(id_alarma) num_alarmas,'Revisar alertas'descripcion_alarma FROM alarmas "
                . "WHERE alarmas.revision_alarma = 1 ORDER BY alarmas.fecha_alarma ASC;";

        $qryAlertas = $connection->query($sqlAlertas);

        while ($fetchAlertas = $qryAlertas->fetch_array()) {
            $category = "alerts";
            $id = $fetchAlertas['num_alarmas'];
            $message = $fetchAlertas['descripcion_alarma'];
            $item = array($varCategory => $category, $varId => $id, $varMessage => $message);
            array_push($arrayJson, $item);
        }
    } elseif ($key === "messages") {
        $sqlMensajes = "SELECT * FROM msj WHERE tipo = 'R';";

        $qryMensajes = $connection->query($sqlMensajes);

        while ($fetchMensajes = $qryMensajes->fetch_array()) {
            $category = "messages";
            $id = $fetchMensajes['id'];
            $message = $fetchMensajes['titulo'];
            $item = array($varCategory => $category, $varId => $id, $varMessage => $message);
            array_push($arrayJson, $item);
        }
    }
}
echo json_encode($arrayJson);


