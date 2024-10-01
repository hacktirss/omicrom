<?php

header("Cache-Control: no-cache,no-store");

$wsdl = 'http://localhost:9080/DetiPOS/detisa/services/DetiPOS?wsdl';

include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();

$cId =  $request->getAttribute("cId");
$cPrc = $request->getAttribute("cPrc");
$isla = $request->getAttribute("isla");

$jsonString = Array();
$jsonString["success"] = false;
$jsonString["message"] = "";

try {

    $mysqli = iconnect();

    $proceso = "";
    $Msj = "El turno ha sido cerrado y abierto con exito!";
    switch ($cPrc) {
        case 1: $proceso = "OPEN";
            $Msj = "El turno ha sido abierto con exito!";
            break;
        case 2: $proceso = "CLOSE";
            $Msj = "El turno ha sido cerrado con exito!";
            break;
        default : $proceso = "CLOSEANDOPEN";
            $Msj = "El turno ha sido cerrado y abierto con exito!";
            break;
    }

    $selectCt = "SELECT isla, id FROM ct WHERE id='" . $cId . "'";
    $Ct = utils\IConnection::execSql($selectCt);

    $isla_ = $Ct['isla'];
    if (is_numeric($isla)) {
        $isla_ = $isla;
    }

    error_log("Iniciar corte en isla: " . $isla_);

    $client = new nusoap_client($wsdl, true);
    $client->timeout = 180;
    $client->soap_defencoding = 'UTF-8';
    $client->namespaces = array("SOAP-ENV" => "http://schemas.xmlsoap.org/soap/envelope/");
    $parameters = array(
        "isla" => $isla_,
        "proceso" => $proceso
    );

    $result = $client->call("CorteOmicrom", $parameters);

    sleep(5);

    $flag = false;
    $selectMensaje = "SELECT mensaje FROM display";
    while (true) {

        $display = utils\IConnection::execSql($selectMensaje);
        error_log("Consultando mensaje");
        if ($display["mensaje"] === '-----') {
            sleep(10);
            break;
        }
        sleep(2);
        $flag = true;
    }

    if ($flag) {
        $jsonString["success"] = true;
    } else {
        $Msj = "Lo sentimos, no se ha generado ningun corte!";
    }
} catch (Exception $exc) {
    $Msj = "Error consultando servicio " . $exc->getMessage();
}
$jsonString["message"] = $Msj;

echo json_encode($jsonString);

