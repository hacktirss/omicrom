<?php

#Librerias
include_once ('data/CartaPorteDAO.php');
include_once ('data/CartaPorteDestinosDAO.php');
include_once ('data/TrasladosDetalleDAO.php');
include_once ('data/TrasladosDAO.php');
require_once ('data/CartaPorteDetisa.php');
include_once ('data/ClientesDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();

$CpDAO = new \com\detisa\omicrom\CartaPorteDAO($busca, 'TCP');
$CpVO = new \com\detisa\omicrom\CartaPorteVO();

$ciaDAO = new CiaDAO();
$ciaVO = $ciaDAO->retrieve(1);

$trasladosDAO = new TrasladosDAO();
$trasladosVO = new TrasladosVO();

$CpVO = $CpDAO->retrieve($busca, "origen = 'TCP' AND  id_origen");
error_log(print_r($request, true));
try {
    if ($request->getAttribute("Comprobante") === "Nuevo") {
        $CpVO = new \com\detisa\omicrom\CartaPorteVO();
        $CpVO->setFechaHoraSalidaLlegada($sanitize->sanitizeString("HoraSalida"));
        $CpVO->setMoneda($sanitize->sanitizeString("Moneda"));
        $CpVO->setRfcRemitenteDestinatario($ciaVO->getRfc());
        $CpVO->setId_origen($busca);
        $CpVO->setId_operador(1000);
        $CpVO->setId_vehiculo(1000);
        $CpVO->setId_operador($sanitize->sanitizeInt("Operador"));
        $CpVO->setId_vehiculo($sanitize->sanitizeInt("Vehiculo"));
        $CpVO->setEmbalaje($sanitize->sanitizeString("Embalaje"));
        $CpVO->setOrigen("TCP");
        $CpVO->setTranspInternac("No");
        if ($id = $CpDAO->create($CpVO)) {
            $trasladosVO = $trasladosDAO->retrieve($busca);
            $trasladosVO->setClaveProductoServicio($request->getAttribute("ClaveCP"));
            $trasladosDAO->update($trasladosVO);

//            $Valida = "SELECT count(*) n FROM ingresos_detalle WHERE id = $id AND producto = 0;";
//            $SqlValida = $connection->query($Valida);
//            $RsVal = $SqlValida->fetch_array();
            $Precio = $request->getAttribute("CostoServicio") / 1.16;
            $trasladosDetalleDAO = new TrasladosDetalleDAO();
            $trasladoDetalleVO = new TrasladosDetalleVO();
            $trasladoDetalleVO->setId($busca);
            $trasladoDetalleVO->setProducto(0);
            $trasladoDetalleVO->setCantidad(1);
            $trasladoDetalleVO->setPrecio($Precio);
            $trasladoDetalleVO->setIeps($sanitize->sanitizeString("RetencionServicio"));
            $trasladoDetalleVO->setIva(0.16);
            $trasladoDetalleVO->setImporte($sanitize->sanitizeString("CostoServicio"));
            $trasladoDetalleVO->setPreciob($sanitize->sanitizeString("CostoServicio"));

            if (($id = $trasladosDetalleDAO->create($trasladoDetalleVO)) > 0) {
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        }
    } elseif ($request->getAttribute("Conceptos") === utils\Messages::OP_UPDATE) {
        $CpVO = $CpDAO->retrieve($busca, "origen = 'TCP' AND  id_origen");
        $CpVO->setClaveUnidad($sanitize->sanitizeString("CUnidad"));
        $CpVO->setDescripcion($sanitize->sanitizeString("Descripcion"));
        $CpVO->setBienesTransp($sanitize->sanitizeString("BienesTransportes"));
        $Msj = utils\Messages::RESPONSE_ERROR;
        if ($CpDAO->update($CpVO)) {
            $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
        }
    } else if ($request->getAttribute("Comprobante") === utils\Messages::OP_UPDATE) {
        $CpVO = $CpDAO->retrieve($busca, "origen = 'TCP' AND  id_origen");
        $CpVO->setId_operador($sanitize->sanitizeInt("Operador"));
        $CpVO->setId_vehiculo($sanitize->sanitizeInt("Vehiculo"));
        $CpVO->setEmbalaje($sanitize->sanitizeString("Embalaje"));
        if ($CpDAO->update($CpVO)) {
            $trasladosVO = $trasladosDAO->retrieve($busca);
            $trasladosVO->setClaveProductoServicio($sanitize->sanitizeString("ClaveCP"));
            $trasladosDAO->update($trasladosVO);
            $Dte = "DELETE FROM  traslados_detalle WHERE id = $busca AND producto = 0";
            utils\IConnection::execSql($Dte);
            $Precio = $sanitize->sanitizeFloat("CostoServicio") / 1.16;
            $trasladosDetalleDAO = new TrasladosDetalleDAO();
            $trasladoDetalleVO = new TrasladosDetalleVO();
            $trasladoDetalleVO->setId($busca);
            $trasladoDetalleVO->setProducto(0);
            $trasladoDetalleVO->setCantidad(1);
            $trasladoDetalleVO->setPrecio($Precio);
            $trasladoDetalleVO->setIeps($sanitize->sanitizeFloat("RetencionServicio"));
            $trasladoDetalleVO->setIva(0.16);
            $trasladoDetalleVO->setImporte($sanitize->sanitizeFloat("CostoServicio"));
            $trasladoDetalleVO->setPreciob($sanitize->sanitizeFloat("CostoServicio"));

            if (($id = $trasladosDetalleDAO->create($trasladoDetalleVO)) > 0) {
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        }
    } else if ($request->getAttribute("Direcciones") === utils\Messages::OP_UPDATE) {
        $InsertDireccion = "INSERT INTO carta_porte_destino (id_destino_fk,id_carta_porte_fk,fecha,distancia,tipo,origen) "
                . "VALUES ('" . $request->getAttribute("Direccion") . "','" . $CpVO->getId() . "','"
                . $request->getAttribute("HoraLlegada") . "','" . $request->getAttribute("Distancia") . "','" . $request->getAttribute("TipoT") . "','TRA')";
        if ($mysqli->query($InsertDireccion)) {
            $Msj = utils\Messages::RESPONSE_VALID_CREATE;
        }
    } else if ($request->getAttribute("Remolque") === "Agregar Remolque") {
        $InsertRemolque = "INSERT INTO carta_porte_remolques (id_carta_porte_fk,SubTipoRem,placas) "
                . "VALUES ('" . $CpVO->getId() . "','" . $request->getAttribute("RemolqueCve") . "','" . $request->getAttribute("Placa") . "');";
        utils\IConnection::execSql($InsertRemolque);
    } else if ($request->getAttribute("opDR") === "Si") {
        $Delete = "DELETE FROM  carta_porte_remolques WHERE id = " . $request->getAttribute("nvoId");
        utils\IConnection::execSql($Delete);
    } else if ($request->getAttribute("Guardar") === "Nuevo Domicilio") {

        $CartaPorteDestinosDAO = new CartaPorteDestinosDAO();
        $CartaPorteDestinosVO = new CartaPorteDestinosVO();
        $CartaPorteDestinosVO->setEstado($request->getAttribute("Estado"));
        $CartaPorteDestinosVO->setRfcDestinatario("XAXXO1O1O1OOO");
        $CartaPorteDestinosVO->setCodigo_postal($request->getAttribute("CodigoPostal"));
        $CartaPorteDestinosVO->setCalle($request->getAttribute("Calle"));
        $CartaPorteDestinosVO->setNo_int($request->getAttribute("NumI"));
        $CartaPorteDestinosVO->setNo_ext($request->getAttribute("NumE"));
        $CartaPorteDestinosVO->setColonia($request->getAttribute("Colonia"));
        $CartaPorteDestinosVO->setLocalidad($request->getAttribute("Localidad"));
        $CartaPorteDestinosVO->setReferencia($request->getAttribute("Referencia"));
        $CartaPorteDestinosVO->setMunicipio($request->getAttribute("Municipio"));
        $CartaPorteDestinosVO->setOrigenDestino("-");
        $CartaPorteDestinosDAO->create($CartaPorteDestinosVO);
        $Msj = utils\Messages::RESPONSE_VALID_CREATE;
    } else if ($request->getAttribute("op") === "Si") {
        $CartaPorteDestinosDAO = new CartaPorteDestinosDAO();
        $CartaPorteDestinosDAO->remove($request->getAttribute("nvoId"));
    }
} catch (Exception $ex) {
    
}

$CpVO = $CpDAO->retrieve($busca, "id_origen");
$SelectDestinos = "SELECT * FROM carta_porte_destinos WHERE id_carta_porte_fk = " . $CpVO->getId();
$destinos = array();
$i = 1;
if ($folios = $mysqli->query($SelectDestinos)) {
    while ($rg = $folios->fetch_array()) {
        $destinosRFC[$i] = $rg["rfcDestinatario"];
        $destinosTiempo[$i] = $rg["horaDeSalida"];
        $idUbicaion[$i] = $rg["codigopostal"];
        $i++;
    }
}

if ($request->getAttribute("Op") === "Clean") {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $Sql = "DELETE FROM carta_porte WHERE id = " . $request->getAttribute("id") . " LIMIT 1;";
    if ($mysqli->query($Sql)) {
        $Msj = utils\Messages::RESPONSE_VALID_DELETE;
        $Sql = "DELETE FROM carta_porte_destinos WHERE id_carta_porte_fk = " . $request->getAttribute("id");
        if ($mysqli->query($Sql)) {
            $Msj = utils\Messages::RESPONSE_VALID_DELETE;
        } else {
            $Msj = utils\Messages::RESPONSE_ERROR;
        }
    }
} else if ($request->getAttribute("opD") === "Si") {
    $Sql = "DELETE FROM carta_porte_destino WHERE id = " . $request->getAttribute("nvoId") . " LIMIT 1;";
    if ($mysqli->query($Sql)) {
        $Msj = utils\Messages::RESPONSE_VALID_DELETE;
    }
} else if ($request->getAttribute("op") === "Timbra") {
    $cartaPorteDetisa = new \com\detisa\omicrom\CartaPorteDetisa($busca, "TCP");
    //error_log(print_r($cartaPorteDetisa, true));
    $wsdl = FACTENDPOINT;
    $client = new nusoap_client($wsdl, true);
    $client->timeout = 720;
    $client->response_timeout = 720;
    $client->soap_defencoding = 'UTF-8';
    $client->namespaces = array("SOAP-ENV" => "http://schemas.xmlsoap.org/soap/envelope/");

    $Fmt = utils\HTTPUtils::getSessionValue("cVar");        //Tipo de formato;
    $document = $cartaPorteDetisa->getComprobante()->asXML();

    $a = $document->save("/home/omicrom/xml/prb.xml");
    $params = array(
        "cfdi" => $cartaPorteDetisa->getComprobante()->asXML()->saveXML(),
        "formato" => "A1",
        "tipo" => "TR",
        "idfc" => $busca
    );

    $result = $client->call("cfdiXml", $params);
    $facValida = $result["return"]["valid"];
    error_log("______________________________________" . print_r($result, true));
    $err = $client->getError();

    if ($err || $facValida == 'false') {
        $cError = utf8_encode($result["return"]["error"]);
        $Msj = $cError;
        error_log("Mensaje de Error : " . $Msj);
        $Return = "traslados.php?criteria=ini&Msj=" . urlencode($Msj);
    } else {
        $Msj = utils\Messages::MESSAGE_RINGING_SUCCESS;
        $Return = "traslados.php?pop=true&idp=$cVarVal&fmp=1&Msj=" . urlencode($Msj);
    }
    header("Location: $Return");
}