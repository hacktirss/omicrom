<?php

#Librerias
include_once ('data/ClientesDAO.php');
include_once ('data/TrasladosDAO.php');
include_once ('data/IngresosDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "traslados.php?";
$IngresosDAO = new IngresosDAO();
$TrasladoDAO = new TrasladosDAO();

if ($request->getAttribute("id") === "NUEVO") {
    $returnLink = urlencode("trasladose.php?Boton=Agregar");
    $backLink = urlencode("traslados.php?criteria=ini");
    header("Location: clientes.php?criteria=ini&Facturar=1&backLink=$backLink&returnLink=$returnLink&Op=" . utils\HTTPUtils::getSessionObject("Tipo"));
} else if ($request->getAttribute("Boton") === "Agregar" && $request->getAttribute("Cliente") >= 0) {
    if ($request->getAttribute("Op") == 1) {
        error_log("Iniciamos con Traslados");
        $TrasladoVO = new TrasladosVO();
        $Folio = "SELECT IFNULL(MAX(folio),0) + 1 numMax FROM traslados;";
        $FolioRs = utils\IConnection::execSql($Folio);
        $TrasladoVO->setFecha(date("Y-m-d H:i:s"));
        $TrasladoVO->setUsr($usuarioSesion->getNombre());
        $TrasladoVO->setClaveProductoServicio("000000");
        $TrasladoVO->setId_cli($request->getAttribute("Cliente"));
        $TrasladoVO->setFolio($FolioRs["numMax"]);
        $Ts = $TrasladoDAO->create($TrasladoVO);
        $Msj = utils\Messages::RESPONSE_VALID_CREATE;
    } else {
        error_log("Iniciamos con Ingresos");
        error_log(print_r($request, true));
        $IngresosVO = new IngresosVO();
        $Folio = "SELECT IFNULL(MAX(folio),0) + 1 numMax FROM ingresos;";
        $FolioRs = utils\IConnection::execSql($Folio);
        $IngresosVO->setFecha(date("Y-m-d H:i:s"));
        $IngresosVO->setUsr($usuarioSesion->getNombre());
        $IngresosVO->setClaveProdServ("000000");
        $IngresosVO->setId_cli($request->getAttribute("Cliente"));
        $IngresosVO->setFolio($FolioRs["numMax"]);
        $IngresosVO->setSerie("CPI");
        $IngresosVO->setMetodopago("PUE");
        $IngresosVO->setFormadepago("03");
        $IngresosVO->setUsocfdi("G03");
        $Ts = $IngresosDAO->create($IngresosVO);
        $Msj = utils\Messages::RESPONSE_VALID_CREATE;
    }
    header("Location: trasladosd.php?cVarVal=$Ts&busca=$Ts&Msj=$Msj");
} else if ($request->getAttribute("BotonA") === "Actualizar") {
    if (utils\HTTPUtils::getSessionObject("Tipo") != 2) {
        $TrasladoVO = new TrasladosVO();
        $TrasladoVO = $TrasladoDAO->retrieve($request->getAttribute("busca"));
        $TrasladoVO->setMetodoPago($request->getAttribute("Metododepago"));
        $TrasladoVO->setFormaPago($request->getAttribute("Formadepago"));
        $TrasladoVO->setObservaciones($request->getAttribute("Observaciones"));
        $TrasladoVO->setUsoCfdi($request->getAttribute("cuso"));
        $TrasladoDAO->update($TrasladoVO);
    } else {
        $IngresosVO = new IngresosVO();
        $IngresosVO = $IngresosDAO->retrieve($request->getAttribute("busca"));
        $IngresosVO->setMetodopago($request->getAttribute("Metododepago"));
        $IngresosVO->setFormadepago($request->getAttribute("Formadepago"));
        $IngresosVO->setObservaciones($request->getAttribute("Observaciones"));
        $IngresosVO->setUsocfdi($request->getAttribute("cuso"));
        $IngresosDAO->update($IngresosVO);
    }
}
