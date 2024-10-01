<?php

require_once ('softcoatl/config.php');

use com\softcoatl\utils\Configuration;

$sql = "SELECT uuid, cfdi_xml FROM facturas WHERE ExtractValue( cfdi_xml, 'count(/cfdi:Comprobante/cfdi:Addenda)' ) > 1";

$connection = getConnection();
$result = $connection->query($sql);

error_log("Eliminando addendas duplicadas ");

while ($rs = $result->fetch_array()) {
    
    $xml = $rs["cfdi_xml"];
    $uuid = $rs["uuid"];
    $document = new \DOMDocument("1.0","UTF-8");
    $document->loadXML($xml);
    $comprobante = $document->documentElement;
    $addendas = $comprobante->getElementsByTagNameNS("https://www.sat.gob.mx/cfd/4", "Addenda");
    for ($i = 0; $i < $addendas->count(); $i++) {
        $addenda = $addendas->item($i);
        if ($i > 0) {
            error_log("Eliminando addenda");
            $comprobante->removeChild($addenda);
        }
    }
    $cleanXML = $document->saveXML();
    updateXML($uuid, $cleanXML);
}

function updateXML($uuid, $xml) {
    
    $sql = "UPDATE facturas SET cfdi_xml = ? WHERE uuid = ?";
    $connection = getConnection();
    error_log("Updating " . $uuid . " as " . $xml);
    if (($ps = $connection->prepare($sql))) {
        $ps->bind_param("ss", $xml, $uuid);
        $ps->execute();
        error_log($connection->error);
    }
}

function getConnection() {

    $dbc = Configuration::get();

    $dbConn = new \mysqli($dbc->host, $dbc->username, $dbc->pass, $dbc->database);

    if ($dbConn->connect_errno > 0) {
        if ($dbConn->connect_errno) {
            throw new \Exception("Error conectando con base de datos <br/>" . urldecode($dbConn->error));
        }
    }
    if (!$dbConn->query("SET lc_time_names = 'es_MX'")) {
        if ($dbConn->error) {
            throw new \Exception("Error configurando base de datos <br/>" . urldecode($dbConn->error));
        }
    }
    if (property_exists($dbc, "charset") && !$dbConn->set_charset($dbc->charset)) {
        if ($dbConn->error) {
            throw new \Exception("Error configurando base de datos <br/>" . urldecode($dbConn->error));
        }
    }
    return $dbConn;
}
