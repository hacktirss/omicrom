<?php

include_once ("libnvo/lib.php");
include_once ('data/DictamenDAO.php');

use com\softcoatl\cfdi\v33\schema\Comprobante as Comprobante;
use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$mysqli = iconnect();
$Fn = explode(".", $_FILES["file"]["name"][0]);
if (move_uploaded_file($_FILES["file"]["tmp_name"][0], "/home/omicrom/xml/Dictamen_" . $_REQUEST["IdDictamen"] . "." . $Fn[1])) {

    $nombreA = "/home/omicrom/xml/Dictamen_" . $_REQUEST["IdDictamen"] . "." . $Fn[1];
    $carga_xml = simplexml_load_file($nombreA); //Obtenemos los datos del xml agregados
    $ns = $carga_xml->getNamespaces(true);
    $carga_xml->registerXPathNamespace('c', $ns['Certificado']);
    $arra["RfcContribuyente"] = $RfcContribuyente = $carga_xml->RfcContribuyente;
    $array["RfcRepresentanteLegal"] = $RfcRepresentanteLegal = $carga_xml->RfcRepresentanteLegal;
    $array["RfcProveedorCertificado"] = $RfcProveedorCertificado = $carga_xml->RfcProveedorCertificado;
    $array["RfcRepresentanteLegalProveedor"] = $RfcRepresentanteLegalProveedor = $carga_xml->RfcRepresentanteLegalProveedor;
    foreach ($carga_xml->xpath('//Certificado//InformacionVerificacion') as $InformacionVerificacion) {
        $array["FechaEmisionCertificado"] = $FechaEmisionCertificado = $InformacionVerificacion->FechaEmisionCertificado;
        $array["NumeroFolioCertificado"] = $NumeroFolioCertificado = $InformacionVerificacion->NumeroFolioCertificado;
        $array["ResultadoCertificado"] = $ResultadoCertificado = $InformacionVerificacion->ResultadoCertificado;
        $array["RfcPersonal"] = $RfcPersonal = $InformacionVerificacion->RfcPersonal;
    }
} else {
    echo "Error en guardado";
}

$objectDAO = new DictamenDAO();
$objectVO = new DictamenVO();
$objectVO->setFechaEmision($FechaEmisionCertificado);
$objectVO->setResultado($ResultadoCertificado);
$objectVO->setNumeroFolio($NumeroFolioCertificado);
$objectVO->setEstado(0);
$objectVO->setNoCarga($_REQUEST["IdDictamen"]);
$objectVO->setProveedor(0);
error_log("________________________________________________________" . $objectVO->getNumeroFolio());
$objectDAO->create($objectVO);
