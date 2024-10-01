<?php

set_time_limit(720);

include_once ('data/ClientesDAO.php');
include_once ('data/FcDAO.php');
include_once ('data/NcDAO.php');
include_once ('data/NcdDAO.php');
include_once ('data/ProveedorPACDAO.php');
require_once ('data/NotaDeCreditoDetisa.php');
require_once ('data/NotaCreditoDevolucion.php');
include_once ('data/CxcDAO.php');
include_once ('data/IslaDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "notascred.php?";

$nameVariableSession = "CatalogoNotasCreditoDetalle";

if ($request->hasAttribute("cVarVal")) {
    utils\HTTPUtils::setSessionBiValue($nameVariableSession, "cVarVal", $request->getAttribute("cVarVal"));
}

$cVarVal = utils\HTTPUtils::getSessionBiValue($nameVariableSession, "cVarVal");

$lBd = false;

$ciaDAO = new CiaDAO();
$clienteDAO = new ClientesDAO();
$ncDAO = new NcDAO();
$ncdDAO = new NcdDAO();
$cxcDAO = new CxcDAO();
$islaDAO = new IslaDAO();

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    $ciaVO = $ciaDAO->retrieve(1);
    $ncVO = $ncDAO->retrieve($cVarVal);
    $clienteVO = $clienteDAO->retrieve($ncVO->getCliente());

    try {
        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            if (AgregaDetalle($cVarVal, $sanitize->sanitizeInt("Producto"), $sanitize->sanitizeFloat("Importe"), $sanitize->sanitizeFloat("Cantidad"))) {
                $Msj = $request->getAttribute("Msj") . " " . utils\Messages::RESPONSE_VALID_CREATE;
                Totaliza($cVarVal);
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === "Guardar estos cambios") {
            $ncVO = $ncDAO->retrieve($cVarVal);
            $ncVO->setUsocfdi($sanitize->sanitizeString("cuso"));
            $ncVO->setObservaciones($sanitize->sanitizeString("Observaciones"));
            $ncVO->setFormadepago($sanitize->sanitizeString("Formadepago"));
            $ncVO->setTiporelacion($sanitize->sanitizeString("tiporelacion"));
            if ($ncDAO->update($ncVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
            $Return = "gennotacredito331.php?";
        } elseif ($request->getAttribute("Boton") === "Timbra nota de credito") {
            $Return = null;
            $lBd = true;
            $Msj = utils\Messages::MESSAGE_RINGING;
        } elseif ($request->getAttribute("Boton") === "Genera") {
            error_log("********** NotasCreditodServices         Iniciando servicio " );

            $ncVO = $ncDAO->retrieve($cVarVal);
            $clienteVO = $clienteDAO->retrieve($ncVO->getCliente());

            error_log("********** NotasCreditodServices         Iniciando servicio2 " );
            
            //LLamado a NotaCreditoDevolucion ->  NotaCreditoDevolucionDAO
            $notaCreditoDetisa = $clienteVO->getRfc() === FcDAO::RFC_GENERIC ?
                    new com\detisa\omicrom\NotaCreditoDevolucion($cVarVal) :
                    new com\detisa\omicrom\NotaDeCreditoDetisa($cVarVal);
            error_log("********** NotasCreditodServices         Iniciando servicio3 " );
            

            if (count($notaCreditoDetisa->getComprobante()->getConceptos()->getConcepto()) == 0) {
                //error_log("********** NotasCreditodServices         Iniciando servicio if " );
                $Msj = "<strong>Error critico.</strong> El comprobante no tiene conceptos, no es posible timbrar un comprobante sin conceptos.";
                $Return = "notascred.php?";
            } else {
                //error_log("********** NotasCreditodServices         Iniciando servicio else " );
                $wsdl = FACTENDPOINT;
                $client = new nusoap_client($wsdl, true);
                $client->timeout = 720;
                $client->response_timeout = 720;
                $client->soap_defencoding = 'UTF-8';
                $client->namespaces = array("SOAP-ENV" => "http://schemas.xmlsoap.org/soap/envelope/");

                $Fmt = 0;
                $document = $notaCreditoDetisa->getComprobante()->asXML();
                
                $a = $document->save("/home/omicrom/xml/prb.xml");

                $params = array(
                    "cfdi" => $notaCreditoDetisa->getComprobante()->asXML()->saveXML(),
                    "formato" => "A1",
                    "tipo" => "CR",
                    "idfc" => $cVarVal
                );

                error_log("invocando servicio " . FACTENDPOINT);
                $result = $client->call("cfdiXml", $params);
                error_log(print_r($result, TRUE));
                $facValida = $result["return"]["valid"];
                $err = $client->getError();

                $Return = "notascre.php?criteria=ini";
                if ($err || $facValida == 'false') {
                    $cError = utf8_encode($result["return"]["error"]);
                    $Msj = $cError;
                } else {
                    $Msj = utils\Messages::MESSAGE_RINGING_SUCCESS;

                    error_log("Insertando en cxc...");
                    $sql = "SELECT * FROM cxc WHERE tm = 'C' AND factura='" . $ncVO->getRelacioncfdi() . "' AND cliente='" . $ncVO->getCliente() . "' LIMIT 1";
                    $Fac = $mysqli->query($sql)->fetch_array();

                    if ($Fac[factura] == $ncVO->getRelacioncfdi()) {
                        $islaVO = $islaDAO->retrieve(1, "isla");

                        $cxcVO = new CxcVO();
                        $cxcVO->setCliente($ncVO->getCliente());
                        $cxcVO->setPlacas("Nota_cre");
                        $cxcVO->setReferencia($ncVO->getId());
                        $cxcVO->setFecha($ncVO->getFecha());
                        $cxcVO->setHora(date("H:i:s"));
                        $cxcVO->setTm("H");
                        $cxcVO->setConcepto("Nota Credito No. $cVarVal");
                        $cxcVO->setCantidad($ncVO->getCantidad());
                        $cxcVO->setImporte($ncVO->getTotal());
                        $cxcVO->setCorte($islaVO->getCorte());
                        $cxcVO->setFactura($ncVO->getFactura());

                        if (($id = $cxcDAO->create($cxcVO)) < 1) {
                            $Msj = utils\Messages::RESPONSE_ERROR;
                        }
                    }
                }
            }
        }
    } catch (Exception $ex) {
        error_log($ex);
    } finally {
        if (!is_null($Return)) {
            $Return .= "&Msj=" . urlencode($Msj);
            header("Location: $Return");
        }
    }
}

if ($request->hasAttribute("op")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $cId = $sanitize->sanitizeInt("cId");

    try {

        if ($request->getAttribute("op") === utils\Messages::OP_DELETE) {
            if ($ncdDAO->remove($cId, "idnvo")) {
                $Msj = utils\Messages::RESPONSE_VALID_DELETE;
                Totaliza($cVarVal);
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en productos: " . $ex);
    } finally {
        if (!is_null($Return)) {
            header("Location: $Return");
        }
    }
}

function AgregaDetalle($NotaCredito, $ProductoD, $ImporteD, $CantidadD) {

    global $mysqli;

    $PrmA = $mysqli->query("SELECT iva FROM cia");
    $Prm = $PrmA->fetch_array();

    if ($ProductoD <= 4) {
        $PsoA = $mysqli->query("SELECT descripcion FROM inv WHERE id='$ProductoD'");
        $Pso = $PsoA->fetch_array();

        $InvA = $mysqli->query("SELECT precio,iva,ieps FROM com WHERE descripcion='$Pso[descripcion]'");
        $Inv = $InvA->fetch_array();

        $Iva = $Inv[iva];
        $PrecioB = $Inv[precio];
        $Ieps = $Inv["ieps"];

        $Tipo = 'I';

        if ($ImporteD > 0) {  // Captura Importe
            $Importe = $ImporteD;
            $Cnt = round($ImporteD / $PrecioB, 6);
        } else {                 // Captura Volumen
            $Tipo = 'C';
            $Cnt = $CantidadD;
            $Importe = round($Cnt * $PrecioB, 6);
        }

        $PrecioU = ($PrecioB - $Inv[ieps]) / (1 + $Inv[iva]);
        $Pos = strrpos($PrecioU, ".");
        if ($Pos > 0) {
            $PrecioU = ((int) ($PrecioU) . '.' . substr($PrecioU, $Pos + 1, 4)) * 1;    //Trunco a 4 digitos
        }
    } else {

        $InvA = $mysqli->query("SELECT precio,rubro FROM inv WHERE id='$ProductoD'");
        $Inv = $InvA->fetch_array();

        if (!empty($ImporteD) && $ImporteD > 0 && $Inv[rubro] === "Aceites") {
            return false;
        }
        error_log("rubro: " . $Inv[rubro] . " cantidad: " . $CantidadD . " importe: " . $ImporteD);
        if (!empty($CantidadD) && $CantidadD > 0 && $Inv[rubro] === "Aceites") {
            $Tipo = 'C';
            $Cnt = $CantidadD;
            $PrecioB = $Inv[precio];

            $Importe = round($Cnt * $PrecioB, 6);

            $PrecioU = $PrecioB / (1 + $Prm[iva] / 100);
            $Pos = strrpos($PrecioU, ".");
            if ($Pos > 0) {
                $PrecioU = ((int) ($PrecioU) . '.' . substr($PrecioU, $Pos + 1, 4)) * 1;    //Trunco a 4 digitos
            }
        } elseif (!empty($ImporteD) && $ImporteD > 0 && $Inv[rubro] === "Otros") {
            $Tipo = 'I';
            $Cnt = 1;
            $PrecioB = $ImporteD;

            $Importe = round($Cnt * $PrecioB, 6);

            $PrecioU = $PrecioB / (1 + $Prm[iva] / 100);
            $Pos = strrpos($PrecioU, ".");
            if ($Pos > 0) {
                $PrecioU = ((int) ($PrecioU) . '.' . substr($PrecioU, $Pos + 1, 4)) * 1;    //Trunco a 4 digitos
            }
        } else {
            error_log("Criterios invalidos");
        }
        $Iva = $Prm[iva] / 100;
        $Ieps = 0;
    }


    if ($Importe > 0 && $PrecioU > 0) {

        $cSql = "
                INSERT INTO ncd (id, producto, cantidad, precio, ieps, iva, importe, tipoc, preciob, id_ticket)
                VALUES
                ('$NotaCredito', '$ProductoD', '$Cnt', '$PrecioU', '$Ieps', '$Iva', '$Importe', '$Tipo', '$PrecioB',0)";

        if ($mysqli->query($cSql)) {
            return true;
        } else {
            error_log($mysqli->error);
        }
    }

    return false;
}

function Totaliza($NotaCredito) {

    global $mysqli;

    $DddA = $mysqli->query("
      SELECT 
         round( sum( cantidad ), 2) AS cantidad,
         round( sum( total ), 2) AS total,
         round( sum( importe ), 2) AS importe,
         round( sum( iva ), 2) AS iva,
         round( sum( ieps ), 2) AS ieps
      FROM (
         SELECT 
            CASE
               WHEN tipoc = 'I' THEN
                  ( importe / preciob )
               ELSE
                  ( cantidad )
            END AS cantidad,
            CASE
               WHEN tipoc = 'I' THEN
                  ( importe )
               ELSE
                  ( cantidad * preciob )
            END AS total,
            CASE
               WHEN tipoc = 'I' THEN
                  ( importe - ( ( importe / preciob ) * ieps ) ) / ( 1 + iva )
               ELSE
                  ( cantidad * preciob * ( 1 - ieps ) ) / ( 1 + iva )
            END AS importe,
            CASE
               WHEN tipoc = 'I' THEN
                  ( ( importe - ( ( importe / preciob ) * ieps ) ) / ( 1 + iva ) ) * iva
               ELSE
                  ( ( cantidad * preciob * ( 1 - ieps ) )  / ( 1 + iva ) ) * iva
            END AS iva,
            CASE
               WHEN tipoc = 'I' THEN
                  ( ( importe/preciob ) * ieps )
               ELSE
                  ( cantidad * ieps )
            END AS ieps
         FROM ncd WHERE id = $NotaCredito) as SUB
   ");

    $Ddd = $DddA->fetch_array();

    if ($Ddd[0] == 0) {
        $Cnt = 0;
        $Iva = 0;
        $Ieps = 0;
        $Total = 0;
    } else {
        $Cnt = $Ddd[0];
        $Iva = $Ddd[iva];
        $Ieps = $Ddd[ieps];
        $Total = $Ddd[total];
    }

    $nImporte = $Ddd[total] - ($Iva + $Ieps);

    $sqlUpdate = "UPDATE nc SET cantidad=$Cnt,importe = $nImporte, iva=$Iva, ieps=$Ieps, total= $Total WHERE id=$NotaCredito";
    if (!($mysqli->query($sqlUpdate))) {
        error_log($mysqli->error);
    }
}
