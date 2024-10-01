<?php

set_time_limit(300);
/*
 * Generar las consultas que se utilizaran en los reportes de ventas
 */
include_once ("libnvo/lib.php");
include_once ("data/FcDAO.php");
include_once ("data/PagoDAO.php");
include_once ("data/NcDAO.php");
include_once ("data/ClientesDAO.php");
include_once ('data/V_CorporativoDAO.php');

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$mysqli = iconnect();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();

//error_log(print_r($request, true));
if ($request->hasAttribute("Mes")) {
    $dateRp = $request->getAttribute("FechaNum") . "-" . $request->getAttribute("Mes") . "-01";
    utils\HTTPUtils::setSessionValue("Fecha", $dateRp);
}

$ciaDAO = new CiaDAO();
$variablesCorpDAO = new V_CorporativoDAO();

$ciaVO = $ciaDAO->retrieve(1);
$variablesCorpDepositos = $variablesCorpDAO->retrieve(ListaLlaves::DESGLOSE_DEPOSITOS);

$ConcentrarVtasTarjeta = $ciaVO->getVentastarxticket();

if ($request->hasAttribute("criteria")) {
    utils\HTTPUtils::setSessionValue("Anio", date("Y"));
    utils\HTTPUtils::setSessionValue("Mes", date("m"));
    utils\HTTPUtils::setSessionValue("Fecha", date("Y-m-") . "01");
}
if ($request->hasAttribute("Mes")) {
    utils\HTTPUtils::setSessionValue("MesNum", $request->getAttribute("Mes"));
}

//if ($request->hasAttribute("Fecha")) {
//    utils\HTTPUtils::setSessionValue("Fecha", $sanitize->sanitizeString("Fecha"));
//}
if ($request->hasAttribute("FechaNum")) {
    utils\HTTPUtils::setSessionValue("Anio", $sanitize->sanitizeString("FechaNum"));
}
if ($request->hasAttribute("Mes")) {
    utils\HTTPUtils::setSessionValue("Mes", $sanitize->sanitizeString("Mes"));
}
$MesNum = utils\HTTPUtils::getSessionValue("MesNum");
$Anio = utils\HTTPUtils::getSessionValue("Anio");
$Mes = utils\HTTPUtils::getSessionValue("Mes");
$Fecha = utils\HTTPUtils::getSessionValue("Fecha");

$Productos = array();


if ($request->hasAttribute("archivo")) {
    $archivo = $request->getAttribute("archivo");

    $file = "/controlvolumetrico/sat/$archivo";
    if (!empty($archivo) && file_exists($file)) {
        header("Content-Description: File Transfer");
        header("Content-Type: application/zip");
        header("Content-Disposition: attachment; filename=$archivo");
        header("Expires: 0");
        header("Cache-Control: must-revalidate");
        header("Pragma: public");
        readfile($file);
        exit;
    } else {
        header("Location: resumen.php?Msj=El archivo [$archivo] no fue encontrado!");
    }
}