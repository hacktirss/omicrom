<?php

#Librerias
session_start();

include_once ("libnvo/lib.php");
include "data/FcDAO.php";
require "./services/ReportesVentasService.php";

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$zip = null;
$NombreZip = "$FechaI-$FechaF.zip";

error_log(print_r($request, TRUE));

if ($request->hasAttribute("Boton")) {
    if (!empty($Cliente)) {
        $NombreZip = "Cliente-$Cliente-$FechaI-$FechaF.zip";
    }
    $parameters = array(
        "cliente" => empty($Cliente) ? -1 : $Cliente,
        "inicio" => $FechaI . "T00:00:00",
        "fin" => $FechaF . "T23:59:59"
    );
    generaDescarga($parameters, $NombreZip);
}

if ($request->hasAttribute("BotonT")) {
    $NombreZip = "Facturas-$Fecha.zip";
    if ($request->hasAttribute("General")) {
        $parameters = array("inicio" => $Fecha, "fin" => $Fecha);
        generaDescargaXmls($parameters, $NombreZip);
    } elseif ($request->getAttribute("FormaPago") !== "*") {
        $NombreZip = "Facturas-FormaPago-" . $request->getAttribute("FormaPago") . "-$Fecha.zip";
        $parameters = array("inicio" => $Fecha, "fin" => $Fecha, "formaPago" => $request->getAttribute("FormaPago"));
        generaDescargaXmls($parameters, $NombreZip);
    } elseif ($request->getAttribute("TipoCliente") !== "*") {
        $NombreZip = "Facturas-" . $request->getAttribute("TipoCliente") . "-$Fecha.zip";
        $parameters = array("inicio" => $Fecha, "fin" => $Fecha, "tipoCliente" => $request->getAttribute("TipoCliente"));
        generaDescargaXmls($parameters, $NombreZip);
    } else {
        $parameters = array(
            "cliente" => 0,
            "inicio" => $Fecha . "T00:00:00",
            "fin" => $Fecha . "T23:59:59"
        );
        generaDescarga($parameters, $NombreZip);
    }
}

if ($request->hasAttribute("BotonG")) {
    $NombreZip = "Facturas-$Fecha.zip";
    $parameters = array("inicio" => $Fecha, "fin" => $FechaF, "tipoCliente" => $request->getAttribute("TipoCliente"), "formaPago" => $request->getAttribute("FormaPago"));
    generaDescargaXmlsByVenta($parameters, $NombreZip);
}

