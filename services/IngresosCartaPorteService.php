<?php

#Librerias
//include_once ('data/CartaPorteDAO.php');
include_once ('data/CartaPorteIngresoDAO.php');
include_once ('data/CartaPorteDestinosDAO.php');
include_once ('data/Ingresos_detalleDAO.php');
include_once ('data/IngresosDAO.php');
require_once ('data/CartaPorteDetisa.php');
include_once ('data/ClientesDAO.php');
include_once ('data/CxcDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();

$CpDAO = new \com\detisa\omicrom\CartaPorteIngresoDAO($busca, 'CPI');
$CpVO = new \com\detisa\omicrom\CartaPorteVO();

$ciaDAO = new CiaDAO();
$ciaVO = $ciaDAO->retrieve(1);

$ingresosDAO = new IngresosDAO();
$ingresosVO = new IngresosVO();

$CpVO = $CpDAO->retrieve($busca, "origen = 'CPI' AND  id_origen");

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
        $CpVO->setOrigen("CPI");
        $CpVO->setTranspInternac("No");
        if ($idcp = $CpDAO->create($CpVO)) {
            $ingresosVO = $ingresosDAO->retrieve($busca);
            $ingresosVO->setClaveProdServ($request->getAttribute("ClaveCP"));
            $ingresosDAO->update($ingresosVO);
            $Precio = $request->getAttribute("CostoServicio") / 1.16;
            $ingresosDetalleDAO = new Ingresos_detalleDAO();
            $ingresosDetalleVO = new Ingresos_detalleVO();
            $ingresosDetalleVO->setId($busca);
            $ingresosDetalleVO->setProducto(0);
            $ingresosDetalleVO->setCantidad(1);
            $ingresosDetalleVO->setPrecio($Precio);
            $ingresosDetalleVO->setIeps($sanitize->sanitizeString("RetencionServicio"));
            $ingresosDetalleVO->setIva(0.16);
            $ingresosDetalleVO->setImporte($sanitize->sanitizeString("CostoServicio"));
            $ingresosDetalleVO->setPreciob($sanitize->sanitizeString("CostoServicio"));

            if (($id = $ingresosDetalleDAO->create($ingresosDetalleVO)) > 0) {
                $CxcDAO = new CxcDAO();
                $CxcVO = new CxcVO();
                $CxcVO->setCliente($ingresosVO->getId_cli());
                $CxcVO->setPlacas("-----");
                $CxcVO->setReferencia($ingresosVO->getId());
                $CxcVO->setFecha(date("Y-m-d"));
                $CxcVO->setHora(date("H:i:s"));
                $CxcVO->setTm("C");
                $CxcVO->setConcepto("Costo de envio del ingreso " . $ingresosVO->getId());
                $CxcVO->setCantidad(1);
                $CxcVO->setImporte($sanitize->sanitizeString("CostoServicio") - ($Precio * ($sanitize->sanitizeString("RetencionServicio") / 100)));
                $CxcVO->setRecibo($id);
                $CxcVO->setCorte(1);
                $CxcVO->setProducto(0);
                $CxcVO->setRubro("CPI");
                $CxcVO->setFactura($idcp);
                $CxcDAO->create($CxcVO);
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        }
    } elseif ($request->getAttribute("Conceptos") === utils\Messages::OP_UPDATE) {
        $CpVO = $CpDAO->retrieve($busca, "origen = 'CPI' AND  id_origen");
        $CpVO->setClaveUnidad($sanitize->sanitizeString("CUnidad"));
        $CpVO->setDescripcion($sanitize->sanitizeString("Descripcion"));
        $CpVO->setBienesTransp($sanitize->sanitizeString("BienesTransportes"));
        $Msj = utils\Messages::RESPONSE_ERROR;
        if ($CpDAO->update($CpVO)) {
            $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
        }
    } else if ($request->getAttribute("Comprobante") === utils\Messages::OP_UPDATE) {
        $CpVO = $CpDAO->retrieve($busca, "origen = 'CPI' AND  id_origen");
        $CpVO->setId_operador($sanitize->sanitizeInt("Operador"));
        $CpVO->setId_vehiculo($sanitize->sanitizeInt("Vehiculo"));
        $CpVO->setEmbalaje($sanitize->sanitizeString("Embalaje"));
        if ($CpDAO->update($CpVO)) {
            $ingresosVO = $ingresosDAO->retrieve($busca);
            $ingresosVO->setClaveProdServ($sanitize->sanitizeString("ClaveCP"));
            $ingresosDAO->update($ingresosVO);
            $Dte = "DELETE FROM  ingresos_detalle WHERE id = $busca AND producto = 0";
            utils\IConnection::execSql($Dte);
            $Dtecxc = "DELETE FROM cxc WHERE referencia = " . $ingresosVO->getId() . " AND rubro = 'CPI' LIMIT 1;";
            utils\IConnection::execSql($Dtecxc);
            $Precio = $sanitize->sanitizeFloat("CostoServicio") / 1.16;
            $ingresosDetalleDAO = new Ingresos_detalleDAO();
            $ingresosDetalleVO = new Ingresos_DetalleVO();
            $ingresosDetalleVO->setId($busca);
            $ingresosDetalleVO->setProducto(0);
            $ingresosDetalleVO->setCantidad(1);
            $ingresosDetalleVO->setPrecio($Precio);
            $ingresosDetalleVO->setIeps($sanitize->sanitizeFloat("RetencionServicio"));
            $ingresosDetalleVO->setIva(0.16);
            $ingresosDetalleVO->setImporte($sanitize->sanitizeFloat("CostoServicio"));
            $ingresosDetalleVO->setPreciob($sanitize->sanitizeFloat("CostoServicio"));

            if (($id = $ingresosDetalleDAO->create($ingresosDetalleVO)) > 0) {
                $CxcDAO = new CxcDAO();
                $CxcVO = new CxcVO();
                $CxcVO->setCliente($ingresosVO->getId_cli());
                $CxcVO->setPlacas("-----");
                $CxcVO->setReferencia($ingresosVO->getId());
                $CxcVO->setFecha(date("Y-m-d"));
                $CxcVO->setHora(date("H:i:s"));
                $CxcVO->setTm("C");
                $CxcVO->setConcepto("Costo de envio del ingreso " . $ingresosVO->getId());
                $CxcVO->setCantidad(1);
                $CxcVO->setImporte(ROUND($sanitize->sanitizeString("CostoServicio") - ($Precio * ($sanitize->sanitizeString("RetencionServicio") / 100)), 2));
                $CxcVO->setRecibo($id);
                $CxcVO->setCorte(1);
                $CxcVO->setProducto(0);
                $CxcVO->setRubro("CPI");
                $CxcVO->setFactura($CpVO->getId());
                $CxcDAO->create($CxcVO);
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        }
    } else if ($request->getAttribute("Direcciones") === utils\Messages::OP_UPDATE) {
        $InsertDireccion = "INSERT INTO carta_porte_destino (id_destino_fk,id_carta_porte_fk,fecha,distancia,tipo,origen) "
                . "VALUES ('" . $request->getAttribute("Direccion") . "','" . $CpVO->getId() . "','"
                . $request->getAttribute("HoraLlegada") . "','" . $request->getAttribute("Distancia") . "','" . $request->getAttribute("TipoT") . "','ING')";
        error_log($InsertDireccion);
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
    } else if ($request->getAttribute("BotonFc") === "Actualizar") {
        $ingresosVO = new IngresosVO();
        $ingresosVO = $ingresosDAO->retrieve($request->getAttribute("BuscaCp"));
        $ingresosVO->setMetodopago($request->getAttribute("Metododepago"));
        $ingresosVO->setFormadepago($request->getAttribute("Formadepago"));
        $ingresosVO->setObservaciones($request->getAttribute("Observaciones"));
        $ingresosVO->setUsocfdi($request->getAttribute("cuso"));
        $ingresosDAO->update($ingresosVO);
    }
} catch (Exception $ex) {
    
}
$CpVO = $CpDAO->retrieve($busca, "origen = 'CPI' AND  id_origen");
$SelectDestinos = "SELECT * FROM carta_porte_destinos WHERE id_carta_porte_fk = " . $CpVO->getId() . " AND tipo = 'TRA'";
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
    $cartaPorteDetisa = new \com\detisa\omicrom\CartaPorteDetisa($busca, "CPI");
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
        "tipo" => "CPI",
        "idfc" => $busca
    );

    $result = $client->call("cfdiXml", $params);
    $facValida = $result["return"]["valid"];
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
    if (strlen($result["return"]["uuid"]) > 6) {
        $UpdateRmUuid = "UPDATE rm SET uuid = '" . $result["return"]["uuid"] . "' WHERE id IN (select id_rm from ingresos_detalle WHERE id=$busca AND id_rm > 0);";
        utils\IConnection::execSql($UpdateRmUuid);
        $InsertFc = "INSERT INTO fc (serie,folio,fecha,cliente,cantidad,importe,iva,ieps,total,status,uuid,ticket,observaciones,usr,origen,stCancelacion,
    motivoCan,tiporelacion,relacioncfdi,tdoctorelacionado,usocfdi,formadepago,metododepago,periodo,meses,ano,descuento,cancelacion,fecha_indice) 
    SELECT i.serie,i.folio,i.fecha,i.id_cli,id.cantidad,id.precio,ROUND(((id.precio*id.iva) - (id.precio * (id.ieps/100))),2) iva,0,
    ROUND((id.importe - (id.precio * (id.ieps/100))),2) importe,i.status,i.uuid,i.id,CONCAT('Creación desde ingresos ',i.id),i.usr,1,0,
    null,null,null,i.id,i.usocfdi,i.formadepago,i.metodopago,00,00,0000,0,0, DATE_FORMAT(i.fecha,'%Y%m%d') 
    FROM ingresos i LEFT JOIN ingresos_detalle id ON i.id=id.id WHERE i.id = $busca AND id.producto=0;";
        $mysqli->query($InsertFc);
        $UpdateCxc = "UPDATE cxc SET factura = " . $mysqli->insert_id . " WHERE factura = (SELECT id FROM carta_porte WHERE origen = 'CPI' and id_origen = $busca) AND rubro  = 'CPI';";
        error_log($UpdateCxc);
        $IdRmMax = "SELECT MAX(id_rm) idRm,producto FROM ingresos_detalle WHERE id= $busca  AND id_rm > 0 LIMIT 1;";
        $UtilsIdMax = utils\IConnection::execSql($IdRmMax);
        $InsertFcd = "INSERT INTO fcd (id,producto,cantidad,preciob,precio,iva,iva_retenido,isr_retenido,ieps_retenido,ieps,importe,ticket,tipoc,descuento) 
    SELECT " . $mysqli->insert_id . ",'" . $UtilsIdMax["producto"] . "',cantidad,preciob,precio,iva,ieps/100,0,0,0,importe," . $UtilsIdMax["idRm"] . ",'C',0 FROM ingresos_detalle WHERE id= $busca AND producto = 0;";
        utils\IConnection::execSql($InsertFcd);
        utils\IConnection::execSql($UpdateCxc);
    }
    header("Location: $Return");
}