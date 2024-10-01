<?php

set_time_limit(720);

include_once ("libnvo/lib.php");
include_once ('com/softcoatl/cfdi/ComprobanteResolver.php');

use com\softcoatl\utils as utils;
use com\softcoatl\cfdi\ComprobanteResolver;

$request = utils\Request::instance();
error_log(print_r($request, true));
$tipo = $request->get("type");
$id = $request->get("id");
$formato = $request->has("formato") ? $request->get("formato") : "0";

/*
            case 1: return DocType.FA;
            case 2: return DocType.CR;
            case 3: return DocType.RP;
            case 4: return DocType.AN;
            case 5: return DocType.TR;
            case 6: return DocType.CPI;
 */

$condition = is_numeric($id) ? "id = {$id}" : "uuid = '{$id}'";
$sql = <<< EOQ
    SELECT a.tabla, a.id, a.uuid, f.cfdi_xml xml
    FROM (
            SELECT 1 tabla, fc.id, fc.uuid FROM fc WHERE fc.{$condition}
            UNION ALL
            SELECT 2 tabla, nc.id, nc.uuid FROM nc WHERE nc.{$condition}
            UNION ALL
            SELECT 3 tabla, p.id, p.uuid FROM pagos p WHERE p.{$condition}
            UNION ALL
            SELECT 5 tabla, t.id, t.uuid FROM traslados t WHERE t.{$condition}
    ) a
    LEFT JOIN facturas f ON f.uuid = a.uuid
    WHERE f.{$condition};
EOQ;
error_log($sql);
$connection = utils\IConnection::getConnection();
$result = $connection->query($sql);
$myrowsel = $result->fetch_array();
$uuid = $myrowsel["uuid"];

if (empty($myrowsel["xml"])) { // Si no existe el registro en facturas

    // Carga los archivos de disco duro
    error_log("No existe el registro, carga archivo {$uuid}.xml de HD");
    if (!file_exists("/var/www/html/omicrom/fae/archivos/" . $uuid . ".xml")) {
        error_log("No existe archivo XML");
        echo "Error : No existe archivo XML";
        exit();
    }

    $xml = file_get_contents("/var/www/html/omicrom/fae/archivos/" . $uuid . ".xml");
    $comprobante = ComprobanteResolver::resolve($xml);
    $insert = "INSERT INTO facturas (id_fc_fk, version, cfdi_xml, fecha_emision, fecha_timbrado, clave_pac, emisor, receptor, uuid, tabla)"
            . " VALUES (?, ?, ?, ?, ?, 'SIFEI', ?, ?, ?, ?)";
    $stmt = $connection->prepare($insert);
    if ($stmt) {
        $stmt->bind_param("isssssssi",
                $myrowsel["id"],
                $comprobante->getVersion(),
                $xml,
                $comprobante->getFecha(),
                $comprobante->getTimbreFiscalDigital()->getFechaTimbrado(),
                $comprobante->getEmisor()->getRfc(),
                $comprobante->getReceptor()->getRfc(),
                $comprobante->getTimbreFiscalDigital()->getUUID(),
                $myrowsel["tabla"]);
        $connection->execute_query("SET GLOBAL max_allowed_packet=10000000000;");
        $stmt->execute();
        error_log($stmt->error);
    }
} else { // Existe y es de una versión anterior
    error_log("Existe el registro. Carga archivos de BD");
    $xml = $myrowsel['xml'];
}

if ($tipo === 'pdf') {

    $wsdl = FACTENDPOINT;
    $client = new nusoap_client($wsdl, true);
    $client->timeout = 720;
    $client->response_timeout = 720;
    $client->soap_defencoding = 'UTF-8';
    $client->namespaces = array("SOAP-ENV" => "http://schemas.xmlsoap.org/soap/envelope/");

    $formato = $formato == 1 ? "TC" : "A1";
    $params = array(
        "uuid" => $uuid,
        "formato" => $formato
    );

    $message = "generaPDFFile";
    try {
        error_log("Calling " . $wsdl);
        $response = $client->call($message, $params);
        if (empty($client->getError())) {
            $pdf = base64_decode($response["return"]);
            error_log("****************** Se ha generado el PDF con el nuevo método*****************");
        } else {
            throw new Exception("Sin acceso al servidor de facturación.");
        }
        error_log("Enviando PDF");
        header("Content-Description: File Transfer");
        header("Content-Type: application/pdf");
        header("Content-Disposition: inline; filename=" . $uuid . ".pdf");
        header("Content-Length: " . strlen(bin2hex($pdf)) / 2);
        header("Expires: 0");
        header("Cache-Control: must-revalidate");
        header("Pragma: public");
        echo $pdf;
        exit();
    } catch (Exception $e) {
        echo "Error : " . $e->getMessage();
        exit();
    }
} else {

    error_log("Enviando XML");
    header("Content-Description: File Transfer");
    header("Content-Type: application/xml");
    header("Content-Disposition: attachment; filename=" . $uuid . ".xml");
    header("Content-Length: " . strlen($xml));
    header("Expires: 0");
    header("Cache-Control: must-revalidate");
    header("Pragma: public");
    ob_clean();
    echo $xml;
    exit();
}

