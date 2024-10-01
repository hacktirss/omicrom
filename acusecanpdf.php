<?php

#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ('pdf/PDFTransformerAC.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();

$busca = $request->getAttribute("busca");
$table = $request->getAttribute("table");

$cSQL = "SELECT facturas.uuid, facturas.acuse_cancelacion acuse "
        . "FROM " . $table . " "
        . "JOIN facturas ON "
        . "facturas.id_fc_fk = " . $table . ".id "
        . "AND facturas.uuid = " . $table . ".uuid "
        . "AND " . $table . ".id = $busca";
$cRS = $mysqli->query($cSQL)->fetch_array();

$cUid = $cRS['uuid'];
$cAcuse = $cRS['acuse'];

error_log($cSQL);
if (!isset($cAcuse)) {
    try {
        $wsdl = 'http://localhost:9190/GeneradorCFDIsWEB/Facturador?wsdl';

        $client = new nusoap_client($wsdl, true);

        $client->timeout = 180;
        $client->soap_defencoding = 'UTF-8';
        $client->namespaces = array("SOAP-ENV" => "http://schemas.xmlsoap.org/soap/envelope/");

        $params = array(
            "user" => "WS0DDT0026",
            "password" => "e16875b942",
            "uuid" => $cUid);
        $result = $client->call("obtenerAcuseCancelacion", $params, false, '', '');
    } catch (Exception $e) {
        error_log($e->getMessage());
    }
}

$cSQL = "SELECT "
        . "IF (facturas.version != '3.2', ExtractValue(cfdi_xml, '/cfdi:Comprobante/cfdi:Emisor/@Nombre')  , ExtractValue(cfdi_xml, '/cfdi:Comprobante/cfdi:Emisor/@nombre')) nombre, "
        . "IF (facturas.version != '3.2', ExtractValue(cfdi_xml, '/cfdi:Comprobante/cfdi:Emisor/@Rfc')     , ExtractValue(cfdi_xml, '/cfdi:Comprobante/cfdi:Emisor/@rfc')) rfc, "
        . "IF (facturas.version != '3.2', cia.direccion                                                    , ExtractValue(cfdi_xml, '/cfdi:Comprobante/cfdi:Emisor/cfdi:DomicilioFiscal/@calle')) calle, "
        . "IF (facturas.version != '3.2', cia.numeroext                                                    , ExtractValue(cfdi_xml, '/cfdi:Comprobante/cfdi:Emisor/cfdi:DomicilioFiscal/@noExterior')) noExterior, "
        . "IF (facturas.version != '3.2', cia.colonia                                                      , ExtractValue(cfdi_xml, '/cfdi:Comprobante/cfdi:Emisor/cfdi:DomicilioFiscal/@colonia')) colonia, "
        . "IF (facturas.version != '3.2', cia.ciudad                                                       , ExtractValue(cfdi_xml, '/cfdi:Comprobante/cfdi:Emisor/cfdi:DomicilioFiscal/@municipio')) municipio, "
        . "IF (facturas.version != '3.2', cia.estado                                                       , ExtractValue(cfdi_xml, '/cfdi:Comprobante/cfdi:Emisor/cfdi:DomicilioFiscal/@estado')) estado, "
        . "IF (facturas.version != '3.2', 'MÉXICO'                                                         , ExtractValue(cfdi_xml, '/cfdi:Comprobante/cfdi:Emisor/cfdi:DomicilioFiscal/@pais')) pais, "
        . "IF (facturas.version != '3.2', cia.codigo                                                       , ExtractValue(cfdi_xml, '/cfdi:Comprobante/cfdi:Emisor/cfdi:DomicilioFiscal/@codigoPostal')) codigoPostal, "
        . "IF (facturas.version != '3.2', ExtractValue(cfdi_xml, '/cfdi:Comprobante/cfdi:Receptor/@Nombre'), ExtractValue(cfdi_xml, '/cfdi:Comprobante/cfdi:Receptor/@nombre')) rnombre, "
        . "IF (facturas.version != '3.2', ExtractValue(cfdi_xml, '/cfdi:Comprobante/cfdi:Receptor/@Rfc')   , ExtractValue(cfdi_xml, '/cfdi:Comprobante/cfdi:Receptor/@rfc')) rrfc, "
        . "IF (facturas.version != '3.2', ExtractValue(cfdi_xml, '/cfdi:Comprobante/@Serie')               , ExtractValue(cfdi_xml, '/cfdi:Comprobante/@serie')) serie, "
        . "IF (facturas.version != '3.2', ExtractValue(cfdi_xml, '/cfdi:Comprobante/@Folio')               , ExtractValue(cfdi_xml, '/cfdi:Comprobante/@folio')) folio, "
        . "ExtractValue(cfdi_xml, '/cfdi:Comprobante/cfdi:Complemento/tfd:TimbreFiscalDigital/@UUID') UUID, "
        . "ExtractValue(acuse_cancelacion, '/Acuse/Signature/SignatureValue') SelloAcuse, "
        . "ExtractValue(acuse_cancelacion, '/Acuse/@Fecha')  FechaCancelacion "
        . "FROM " . $table . " JOIN facturas ON facturas.id_fc_fk = " . $table . ".id AND facturas.uuid = " . $table . ".uuid  JOIN cia "
        . "WHERE acuse_cancelacion IS NOT NULL "
        . "AND TRIM( acuse_cancelacion ) <>  '' "
        . "AND " . $table . ".id = " . $busca . " "
        . "UNION ALL "
        . "SELECT "
        . "cia.cia nombre, cia.rfc rfc, cia.direccion calle, cia.numeroext noExterior, cia.colonia colonia, "
        . "cia.ciudad municipio, cia.estado estado, 'MÉXICO' pais, cia.codigo codigoPostal, cli.nombre rnombre, "
        . "$table.id folio, null serie, cli.rfc rrfc, $table.uuid UUID, 'NO DISPONIBLE' SelloAcuse, 'NO DISPONIBLE' FechaCancelacion "
        . "FROM cia, " . $table . " LEFT JOIN cli ON " . $table . ".cliente = cli.id "
        . "WHERE FOUND_ROWS() = 0 AND " . $table . ".id = $busca";

$selectFactura = $mysqli->query($cSQL);

$acuse = new AcuseVO();

while ($rg = $selectFactura->fetch_array()) {

    $acuse->setNombreEmisor($rg['nombre']);
    $acuse->setRfcEmisor($rg['rfc']);
    $direccion = $rg['calle'] . " " . $rg['numero'] . " " . $rg['colonia'] . "<br />" . $rg['municipio'] . ", " . $rg['estado'] . " C.P. " . $rg['codigoPostal'];
    $acuse->setNombreReceptor($rg['rnombre']);
    $acuse->setRfcReceptor($rg['rrfc']);
    $acuse->setFolio(( empty($rg['serie']) ? "" : ( $rg['serie'] . " - " ) ) . $rg['folio']);
    $acuse->setUUID($rg['UUID']);
    $acuse->setSello($rg['SelloAcuse']);
    $acuse->setFecha($rg['FechaCancelacion']);
}

header("Content-Type: application/pdf");
header("Content-Disposition: inline; filename='" . $myrowsel['uuid'] . ".pdf'");
echo PDFTransformerAC::getPDF($acuse, $direccion, "S", file_get_contents("libnvo/logo.png"));
