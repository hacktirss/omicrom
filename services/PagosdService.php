<?php

#Librerias
set_time_limit(300);

include_once ('data/ClientesDAO.php');
include_once ('data/PagoDAO.php');
include_once ('data/CxcDAO.php');
include_once ('data/IslaDAO.php');
include_once ('data/PagoseDAO.php');
include_once ('data/GenbolDAO.php');
include_once ('data/CiaDAO.php');
include_once ('data/NcDAO.php');
include_once ('data/FcDAO.php');
include_once ('data/V_CorporativoDAO.php');

require_once ('data/ReciboElectronicoDePagoDetisa.php');
require_once ('data/ReciboElectronicoDeAnticipo.php');
include_once ('data/ProveedorPACDAO.php');
include_once ('data/NotaDeCreditoAnticipo.php');
include_once ('data/NotaDeCreditoDetisa.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
error_log(print_r($request, TRUE));

$pagoDAO = new PagoDAO();
$pagoseDAO = new PagoseDAO();
$clienteDAO = new ClientesDAO();
$islaDAO = new IslaDAO();
$cxcDAO = new CxcDAO();
$ciaDAO = new CiaDAO();
$variablesCorpDAO = new V_CorporativoDAO();

$pacDAO = new ProveedorPACDAO();
$ppac = $pacDAO->getActive();
$Return = "pagos.php?";

$nameVariableSession = "CatalogoPagosDetalle"; /* pagosd33 */

if ($request->hasAttribute("cVarVal")) {
    utils\HTTPUtils::setSessionBiValue($nameVariableSession, "cVarVal", $request->getAttribute("cVarVal"));
}

$cVarVal = utils\HTTPUtils::getSessionBiValue($nameVariableSession, "cVarVal");
$islaVO = $islaDAO->retrieve(1, "isla");
$variablesCorpVO = $variablesCorpDAO->retrieve(ListaLlaves::PAGOS_TICKETS);

if ($request->hasAttribute("Factura")) {
    $Return = "pagosd33.php?criteria=ini";
    try {
        if ($request->hasAttribute("cId")) {
            $pagoseDAO->removeLogic($sanitize->sanitizeInt("cId"));
        }
        if (cargarFactura($sanitize->sanitizeInt("Factura"))) {
            $Msj = utils\Messages::MESSAGE_DEFAULT;
        } else {
            $Msj = utils\Messages::RESPONSE_ERROR;
        }
    } catch (Exception $ex) {
        error_log("Error en pagos: " . $ex);
    } finally {
        if (!empty($Return) && !is_null($Return)) {
            $Return .= "&Msj=" . urlencode($Msj);
            header("Location: $Return");
        }
    }
}

if ($request->hasAttribute("Facturas")) {
    $Return = "pagosd33.php?criteria=ini";
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    try {
        $Facturas = $request->getAttribute("Facturas");
        foreach ($Facturas as $key => $value) {
            cargarFactura($value);
            $pagoseDAO->calculoPorcetajePagado($idPagose, $value);
            $Msj = utils\Messages::MESSAGE_DEFAULT;
        }
    } catch (Exception $ex) {
        error_log("Error en pagos: " . $ex);
    } finally {
        if (!empty($Return) && !is_null($Return)) {
            $Return .= "&Msj=" . urlencode($Msj);
            header("Location: $Return");
        }
    }
}

if ($request->hasAttribute("Consumos")) {
    $Return = "pagosd33.php?criteria=ini";
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    try {
        $Consumos = $request->getAttribute("Consumos");
        foreach ($Consumos as $key => $value) {
            error_log("Consumos: " . $value);
            $arrayConsumo = explode(DELIMITER, $value);
            if ($arrayConsumo[0] == TipoPagoDetalle::COMBUSTIBLE) {
                cargarConsumo($arrayConsumo[1]);
            } elseif ($arrayConsumo[0] == TipoPagoDetalle::ACEITES) {
                cargarConsumoAce($arrayConsumo[1]);
            }
            $Msj = utils\Messages::MESSAGE_DEFAULT;
        }
    } catch (Exception $ex) {
        error_log("Error en pagos: " . $ex);
    } finally {
        if (!empty($Return) && !is_null($Return)) {
            $Return .= "&Msj=" . urlencode($Msj);
            header("Location: $Return");
        }
    }
}


if ($request->hasAttribute("cId") && $request->hasAttribute("Abono")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $Return = "pagosd33.php?";
    $Imp = $sanitize->sanitizeFloat("Abono");
    $cId = $sanitize->sanitizeInt("cId");
    try {
        $pagoseVO = $pagoseDAO->retrieve($cId, "idnvo");
        $pagoseVO->setImporte($Imp);

        if ($pagoseDAO->update($pagoseVO)) {
            $cSql = "UPDATE cxc SET importe = '" . $Imp . "' "
                    . "WHERE tm = 'H' AND recibo = '" . $pagoseVO->getIdPago() . "' AND factura = '" . $pagoseVO->getFactura() . "';";
            if ($mysqli->query($cSql)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } else {
            $Msj = utils\Messages::RESPONSE_ERROR;
        }

        $Return .= "&Msj=" . $Msj;
    } catch (Exception $ex) {
        error_log("Error en pagos: " . $ex);
    } finally {
        header("Location: $Return");
    }
}

if ($request->hasAttribute("op")) {
    $pagoVO = $pagoDAO->retrieve($cVarVal);
    $clienteVO = $clienteDAO->retrieve($pagoVO->getCliente());
    $ciaVO = $ciaDAO->retrieve(1);
    $Return = "pagos.php?criteria=ini";
    $cId = $sanitize->sanitizeInt("cId");

    try {

        if ($request->getAttribute("op") === utils\Messages::OP_DELETE) {
            $pagoseVO = $pagoseDAO->retrieve($cId, "idnvo");

            if ($clienteVO->getTipodepago() !== TiposCliente::TARJETA && $clienteVO->getTipodepago() !== TiposCliente::MONEDERO) {
                $cSqlCxc = "UPDATE cxc SET recibo = -recibo,factura = -factura,cliente=-cliente, corte = -corte,referencia = -referencia  "
                        . "WHERE tm = 'H' AND recibo = '" . $pagoseVO->getIdPago() . "' AND factura = '" . $pagoseVO->getFactura() . "';";

                if ($mysqli->query($cSqlCxc)) {
                    if ($pagoseDAO->removeLogic($cId)) {
                        $Msj = utils\Messages::RESPONSE_VALID_DELETE;
                    } else {
                        $Msj = utils\Messages::RESPONSE_ERROR;
                    }
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            } else {
                $udpateRm = "UPDATE rm SET rm.pagado = 0 WHERE rm.id = '" . $pagoseVO->getReferencia() . "';";
                $udpateVt = "UPDATE vtaditivos vt SET vt.pagado = 0 WHERE vt.referencia = '" . $pagoseVO->getReferencia() . "';";
                $udpatePagose = "UPDATE pagose SET id = -id, referencia = -referencia WHERE idnvo = '$cId' AND tipo = '" . $pagoseVO->getTipo() . "';";

                if ($mysqli->query($udpateRm) && $mysqli->query($udpateVt)) {
                    if ($mysqli->query($udpatePagose)) {
                        $Msj = utils\Messages::MESSAGE_DEFAULT;
                    } else {
                        $Msj = utils\Messages::RESPONSE_ERROR;
                    }
                } else {
                    error_log($mysqli->error);
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            }
            $Return = "pagosd33.php?";
        } elseif ($request->getAttribute("op") === "reset") {

            $udpateRm = "UPDATE rm SET rm.pagado = 0 WHERE rm.pagado = '" . $pagoVO->getId() . "';";
            $udpatePagose = "UPDATE pagose SET id = -id, referencia = -referencia WHERE id = '" . $pagoVO->getId() . "';";

            if ($mysqli->query($udpateRm)) {
                if ($mysqli->query($udpatePagose)) {
                    $Msj = utils\Messages::MESSAGE_DEFAULT;
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
            $Return = "pagosd33.php?";
        } elseif ($request->getAttribute("op") === "CerrarTimbrar") {

            if ($clienteVO->getTipodepago() === TiposCliente::PREPAGO) {
                $genbolDAO = new GenbolDAO();
                $genbolVO = new GenbolVO();
                $genbolVO->setFecha(date("Y-m-d"));
                $genbolVO->setFechav(date('Y-m-d', strtotime('+1 MONTH', strtotime(date("Y-m-d")))));
                $genbolVO->setCliente($pagoVO->getCliente());
                $genbolVO->setCantidad(1);
                $genbolVO->setImporte($pagoVO->getImporte());
                $genbolVO->setStatus("Abierta");
                $genbolVO->setRecibe("");

                if (($genbolDAO->create($genbolVO)) < 0) {
                    error_log("No se pudo crear el registro para la captura de boletos");
                }
            }

            $pagoVO->setStatus(StatusPago::CERRADO);
            if ($pagoDAO->update($pagoVO)) {
                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getUsername(), "ADM", "TIMBRADO DE PAGO " . $pagoVO->getId());
                $Msj = utils\Messages::MESSAGE_RINGING;
                $lBd = TRUE;
                $Return = NULL;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("op") === "CerrarGenerar") {
            $pagoVO->setStatus(StatusPago::CERRADO);
            if ($pagoDAO->update($pagoVO)) {
                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getUsername(), "ADM", "TIMBRADO DE PAGO " . $pagoVO->getId());
                $Return = "facturase.php?Boton=Agregar&Cliente=" . $clienteVO->getId() . "&Pago=" . $cVarVal;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("op") === "Timbrar") {
            $lBd = TRUE;
            $Return = NULL;
        } elseif ($request->getAttribute("op") === "Genera") {
            if ($clienteVO->getTipodepago() === TiposCliente::CREDITO || utils\HTTPUtils::getSessionValue("ComplementoPago") == 1) {
                /* Aplica solo para clientes de Crédito o Clientes que esten ingresado al apartado de complementos de pago */
                $reciboElectronico = new com\detisa\omicrom\ReciboElectronicoDePago($cVarVal);

                $document = $reciboElectronico->getComprobante()->asXML();
                $a = $document->save("/home/omicrom/xml/prb.xml");
                $wsdl = FACTENDPOINT;
                $client = new nusoap_client($wsdl, true);
                $client->timeout = 720;
                $client->response_timeout = 720;
                $client->soap_defencoding = 'UTF-8';
                $client->namespaces = array("SOAP-ENV" => "http://schemas.xmlsoap.org/soap/envelope/");

                $params = array(
                    "cfdi" => $reciboElectronico->getComprobante()->asXML()->saveXML(),
                    "formato" => "A1",
                    "tipo" => "RP",
                    "idfc" => $cVarVal
                );

                try {

                    if ($ciaVO->getFacturacion() == 'Si') {
                        error_log("Enviamos la peticion al WS: ");
                        $result = $client->call("cfdiXml", $params);
                        error_log(print_r($result, true));
                    }

                    $facValida = $result["return"]["valid"];
                    $err = $client->getError();

                    if ($err || $facValida == 'false') {

                        $cError = utf8_encode($result["return"]["error"]);
                        $Msj = $cError;
                    } else {
                        error_log("Actualizando el status del pago...");
                        $pagoVO = $pagoDAO->retrieve($cVarVal);
                        $pagoVO->setStatus_pago(StatusPagoPrepago::CON_NOTA_CREDITO);
                        if ($pagoDAO->update($pagoVO)) {
                            $Msj = utils\Messages::MESSAGE_RINGING_SUCCESS;
                        } else {
                            $Msj = utils\Messages::RESPONSE_ERROR;
                        }
                    }
                } catch (Exception $e) {
                    error_log($e->getMessage());
                    $Msj = "Error : " . $e->getMessage();
                }
            } elseif ($clienteVO->getTipodepago() === TiposCliente::MONEDERO) {
                
            }
        } elseif ($request->getAttribute("op") === utils\Messages::OP_CLOSE) {

            if ($clienteVO->getTipodepago() == TiposCliente::PREPAGO) {
                $genbolDAO = new GenbolDAO();
                $genbolVO = new GenbolVO();
                $genbolVO->setFecha(date("Y-m-d"));
                $genbolVO->setFechav(date('Y-m-d', strtotime('+1 MONTH', strtotime(date("Y-m-d")))));
                $genbolVO->setCliente($pagoVO->getCliente());
                $genbolVO->setCantidad(1);
                $genbolVO->setImporte($pagoVO->getImporte());
                $genbolVO->setStatus("Abierta");
                $genbolVO->setRecibe("");

                if (($genbolDAO->create($genbolVO)) < 0) {
                    error_log("No se pudo crear el registro para la captura de boletos");
                }
            } if ($clienteVO->getTipodepago() == TiposCliente::TARJETA || $clienteVO->getTipodepago() == TiposCliente::MONEDERO) {
                $pagoVO->setStatus_pago(StatusPagoPrepago::CON_NOTA_CREDITO);
            }

            $pagoVO->setStatus(StatusPago::CERRADO);
            if ($pagoDAO->update($pagoVO)) {
                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getUsername(), "ADM", "CIERRE DE PAGO " . $pagoVO->getId());
                $Msj = utils\Messages::MESSAGES_PAGOS_CLOSE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("op") === "ac") {

            $sqlCxc = "UPDATE cxc SET cliente = -cliente, recibo = -recibo, factura = -factura, corte = -corte "
                    . "WHERE recibo = '" . $pagoVO->getId() . "' AND cliente='" . $pagoVO->getCliente() . "' AND tm = 'H'; ";
            if (!$mysqli->query($sqlCxc)) {
                error_log($mysqli->error);
            }

            $cxcVO = new CxcVO();
            $cxcVO->setCliente($pagoVO->getCliente());
            $cxcVO->setPlacas("Recibo");
            $cxcVO->setReferencia($pagoVO->getId());
            $cxcVO->setFecha($pagoVO->getFecha_deposito());
            $cxcVO->setHora(date("H:i:s"));
            $cxcVO->setTm("H");
            $cxcVO->setConcepto($pagoVO->getConcepto());
            $cxcVO->setCantidad(1);
            $cxcVO->setImporte($pagoVO->getImporte());
            $cxcVO->setRecibo($pagoVO->getId());
            $cxcVO->setCorte($islaVO->getCorte());
            $cxcVO->setRubro("-----");
            $cxcVO->setProducto("-");

            if (($cxcDAO->create($cxcVO)) > 0) {

                if ($clienteVO->getTipodepago() == TiposCliente::PREPAGO) {
                    $genbolDAO = new GenbolDAO();
                    $genbolVO = new GenbolVO();
                    $genbolVO->setFecha(date("Y-m-d"));
                    $genbolVO->setFechav(date('Y-m-d', strtotime('+1 MONTH', strtotime(date("Y-m-d")))));
                    $genbolVO->setCliente($pagoVO->getCliente());
                    $genbolVO->setCantidad(1);
                    $genbolVO->setImporte($pagoVO->getImporte());
                    $genbolVO->setStatus("Abierta");
                    $genbolVO->setRecibe("");

                    if (($genbolDAO->create($genbolVO)) < 0) {
                        error_log("No se pudo crear el registro para la captura de boletos");
                    }
                }
                if ($clienteVO->getTipodepago() == TiposCliente::PREPAGO) {
                    $pagoVO->setStatus_pago(StatusPagoPrepago::LIBERADO);
                } else {
                    $pagoVO->setStatus_pago(StatusPagoPrepago::CON_NOTA_CREDITO);
                }

                $pagoVO->setStatus(StatusPago::CERRADO);
                if ($pagoDAO->update($pagoVO)) {
                    BitacoraDAO::getInstance()->saveLog($usuarioSesion->getUsername(), "ADM", "ENVIA PAGO " . $pagoVO->getId() . " A CXC");
                    $Msj = utils\Messages::MESSAGES_PAGOS_PAY_FREE;
                    if ($clienteVO->getTipodepago() !== TiposCliente::PREPAGO) {
                        $Msj = utils\Messages::MESSAGES_PAGOS_CLOSE;
                    }
                    if ($clienteVO->getTipodepago() == TiposCliente::PREPAGO) {
                        $Return = "pagosd33.php?";
                    }
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("op") === "generaAnticipo") {
            $DateListo = date("Y-m-d H:i:s", strtotime($request->getAttribute("FechaAnticipo")));
            utils\HTTPUtils::setSessionValue("FechaAnticipoRg", $DateListo);
            $lBd_2 = TRUE;
            $Return = NULL;
        } elseif ($request->getAttribute("op") === "generaReciboAnticipo") {
            $Return = "pagosd33.php?";
            $reciboAnticipo = new com\detisa\omicrom\ReciboElectronicoDeAnticipo($cVarVal);

            $document = $reciboAnticipo->getComprobante()->asXML();

            $a = $document->save("/home/omicrom/xml/prb.xml");

            $wsdl = FACTENDPOINT;
            $client = new nusoap_client($wsdl, true);
            $client->timeout = 720;
            $client->response_timeout = 720;
            $client->soap_defencoding = 'UTF-8';
            $client->namespaces = array("SOAP-ENV" => "http://schemas.xmlsoap.org/soap/envelope/");

            $params = array(
                "cfdi" => $reciboAnticipo->getComprobante()->asXML()->saveXML(),
                "formato" => "A1",
                "tipo" => "AN",
                "idfc" => $cVarVal
            );
            error_log(print_r($params, true));
            try {

                if ($ciaVO->getFacturacion() == 'Si') {
                    error_log("Enviamos la peticion al WS: ");
                    $result = $client->call("cfdiXml", $params);
                    error_log(print_r($result, true));
                }

                $facValida = $result["return"]["valid"];
                $err = $client->getError();

                if ($err || $facValida == 'false') {

                    $cError = utf8_encode($result["return"]["error"]);
                    $Msj = "Ocurrio un error al timbrar, " . $cError;
                } else {
                    $pagoVO = $pagoDAO->retrieve($cVarVal);
                    $pagoVO->setStatus_pago(StatusPagoPrepago::CON_ANTICIPO);
                    if ($pagoDAO->update($pagoVO)) {
                        $Msj = utils\Messages::MESSAGE_RINGING_SUCCESS;
                    } else {
                        $Msj = utils\Messages::RESPONSE_ERROR;
                    }
                }
            } catch (Exception $e) {
                error_log($e->getMessage());
                $Msj = "Error : " . $e->getMessage();
            }
        } elseif ($request->getAttribute("op") === "generaNota") {
            $lBd_4 = TRUE;
            $Return = NULL;
            error_log("ENTRAMOS A GENERA NOTA");
            $DateListo = date("Y-m-d H:i:s", strtotime($request->getAttribute("FechaAnticipo")));
            utils\HTTPUtils::setSessionValue("FechaNCredito", $DateListo);
        } elseif ($request->getAttribute("op") === "generaNotaCredito") {
            $Return = "pagosd33.php?";
            error_log("ENTRAMOS A GENERA NOTA CREDITO");
            try {
                $selectFcRelacionado = "SELECT ROUND(total,2) total FROM fc WHERE relacioncfdi = " . $pagoVO->getId() . " AND tdoctorelacionado = 'ANT' AND uuid <> '-----' AND  status = " . StatusFactura::CERRADO;
                $fcResult = utils\IConnection::execSql($selectFcRelacionado);

                // Insertamos en nc
                $SqlCountNC = " SELECT COUNT(*) cuenta FROM nc WHERE formadepago = '30' AND factura = '$cVarVal' AND status < " . StatusNotaCredito::CANCELADO . " ";
                error_log($SqlCountNC);
                $query = $mysqli->query($SqlCountNC);
                $rs = $query->fetch_assoc();
                if ($request->getAttribute("UsoCfdi") === "Si") {
                    $UsoCfdi = "'G02' usocfdi";
                } else {
                    $UsoCfdi = "fc.usocfdi";
                }
                if (empty($rs['cuenta']) || $rs['cuenta'] == 0) {
//                    if ($fcResult["total"] >= $pagoVO->getImporte()) {
                    $Sql = "
                            INSERT INTO nc ( fecha, cliente, cantidad, importe, iva, ieps, total, observaciones, formadepago, metododepago, factura, usocfdi, tiporelacion, relacioncfdi, usr)
                            SELECT '" . utils\HTTPUtils::getSessionValue("FechaNCredito") . "', p.cliente , 1 cantidad , ROUND(p.importe/1.16,2) importe , (p.importe - ROUND(p.importe/1.16,2)) iva, 
                            0 ieps , p.importe total , CONCAT('Nota Credito relacionada al pago ', p.id) observaciones,
                            '30' formapago , 'PUE' metododepago , p.id factura ,$UsoCfdi , '07' tiporelacion, 
                            (SELECT fc.id FROM fc WHERE fc.relacioncfdi = p.id AND fc.tdoctorelacionado = 'ANT' AND fc.status = " . StatusFactura::CERRADO . " ) relacioncfdi,
                            '" . $usuarioSesion->getUsername() . "' usr
                            FROM pagos p, fc  WHERE p.id = fc.relacioncfdi AND p.id = '$cVarVal'";
//                    } else {
//                        $Sql = "
//                            INSERT INTO nc ( fecha, cliente, cantidad, importe, iva, ieps, total, observaciones, formadepago, metododepago, factura, usocfdi, tiporelacion, relacioncfdi, usr)
//                            SELECT NOW(), p.cliente , 1 cantidad , ROUND(fc.total/1.16,2) importe , (fc.total - ROUND(fc.total/1.16,2)) iva, 
//                            0 ieps , fc.total , CONCAT('Nota Credito relacionada al pago ', p.id) observaciones,
//                            '30' formapago , 'PUE' metododepago , p.id factura , $UsoCfdi ,'07' tiporelacion, fc.id relacioncfdi, '" . $usuarioSesion->getUsername() . "' usr
//                            FROM pagos p, fc 
//                            WHERE TRUE AND p.id = fc.relacioncfdi AND fc.status = " . StatusFactura::CERRADO . "
//                            AND p.id = '$cVarVal';";
//                    }
                    error_log($Sql);
                    if (!$mysqli->query($Sql)) {
                        error_log($mysqli->error);
                        error_log($Sql);
                    }
                }

                // Recuperamos el max id de nc
                $SqlNc = " SELECT id,relacioncfdi FROM nc WHERE formadepago = '30' AND factura = '$cVarVal' AND status = " . StatusNotaCredito::ABIERTO . " ";
                error_log($SqlNc);
                $queryNc = $mysqli->query($SqlNc);
                $rsNc = $queryNc->fetch_assoc();

                error_log("Retrive id nc: " . $rsNc['id']);

                // insertamos en ncd
                error_log("Creamos la Nota de Credito");
                $notaCreditoAnticipo = new com\detisa\omicrom\NotaCreditoAnticipoDAO($rsNc['id']);
                //error_log(json_encode(array("Comprobante" => $notaCreditoAnticipo->getComprobante()->asJsonArray())));
                $document = $notaCreditoAnticipo->getComprobante()->asXML();

                $a = $document->save("/home/omicrom/xml/prb.xml");
                if (count($notaCreditoAnticipo->getComprobante()->getConceptos()->getConcepto()) == 0) {
                    
                }
                //error_log(print_r($notaCreditoAnticipo, TRUE));

                $wsdl = FACTENDPOINT;
                $client = new nusoap_client($wsdl, true);
                $client->timeout = 720;
                $client->response_timeout = 720;
                $client->soap_defencoding = 'UTF-8';
                $client->namespaces = array("SOAP-ENV" => "http://schemas.xmlsoap.org/soap/envelope/");

                $params = array(
                    "cfdi" => $notaCreditoAnticipo->getComprobante()->asXML()->saveXML(),
                    "formato" => "A1",
                    "tipo" => "CR",
                    "idfc" => $rsNc['id']
                );

                if ($ciaVO->getFacturacion() == 'Si') {
                    error_log("Enviamos la peticion al WS: ");
                    $result = $client->call("cfdiXml", $params);
                }

                $facValida = $result["return"]["valid"];
                $err = $client->getError();

                if ($err || $facValida == 'false') {

                    $cError = utf8_encode($result["return"]["error"]);
                    $Msj = $cError;
                } else {
                    $pagoVO = $pagoDAO->retrieve($cVarVal);
                    $pagoVO->setStatus_pago(StatusPagoPrepago::CON_NOTA_CREDITO);
                    if ($pagoDAO->update($pagoVO)) {

                        $updateCxc = "UPDATE cxc SET factura = '$rsNc[relacioncfdi]' "
                                . "WHERE cxc.tm = 'H' "
                                . "AND cxc.cliente = '" . $pagoVO->getCliente() . "' "
                                . "AND cxc.referencia = '" . $pagoVO->getId() . "' "
                                . "AND cxc.recibo = '" . $pagoVO->getId() . "' LIMIT 1;";
                        error_log($updateCxc);
                        if (!($mysqli->query($updateCxc))) {
                            error_log($mysqli->error);
                        }

                        $Msj = utils\Messages::MESSAGE_RINGING_SUCCESS;
                    } else {
                        $Msj = utils\Messages::RESPONSE_ERROR;
                    }
                }
            } catch (Exception $e) {
                error_log($e->getMessage());
                $Msj = "Error : " . $e->getMessage();
            }
        } elseif ($request->getAttribute("op") === "generaComplemento") {
            $lBd_5 = TRUE;
            $Return = NULL;
            $importe = $request->getAttribute("total");
        } elseif ($request->getAttribute("op") === "generaComplementoCli") {
            $Return = "pagosd33.php?";
            try {
                $importe = $request->getAttribute("total");
                $pagoVO = $pagoDAO->retrieve($cVarVal);
                $pagoVO->setImporte($importe);
                $pagoVO->setAplicado($importe);
                $pagoVO->setUsr($usuarioSesion->getUsername());
                $pagoVO->setFecha(date("Y-m-d H:i:s"));
                $pagoVO->setUuid(PagoDAO::SIN_TIMBRAR);
                $pagoVO->setRelacion($cVarVal);
                $pagoVO->setStatus("Cerrada");

                if (($id = $pagoDAO->create($pagoVO)) > 0) {
                    $pagoVO->setId($id);
                    $Fc_update = "UPDATE pagos SET relacion = $id WHERE id = $cVarVal;";
                    if (!($mysqli->query($Fc_update))) {
                        error_log($mysqli->error);
                    } else {
                        $cSqlFC = "SELECT * FROM fc where relacioncfdi = " . $cVarVal . " AND tdoctorelacionado = 'ANT' AND uuid <> '-----' AND UPPER(status) NOT REGEXP 'CANCELADA|ABIERTA'";
                        $Fc = utils\IConnection::execSql($cSqlFC);

                        $cxcVO = crearRegistroCxc($pagoVO, $islaVO, $Fc, $Fc["id"], $pagoVO->getImporte());
                        if (($cxcDAO->create($cxcVO)) > 0) {
                            $pagoseVO = new PagoseVO();
                            $pagoseVO->setIdPago($pagoVO->getId());
                            $pagoseVO->setFactura($cxcVO->getFactura());
                            $pagoseVO->setImporte($cxcVO->getImporte());
                            $pagoseVO->setTipo(TipoPagoDetalle::FACTURA);
                            error_log("CREAMOS PAGOS NUEVO " . print_r($pagoseVO, true));

                            $Response = ($pagoseDAO->create($pagoseVO)) > 0;
                            if ($Response <= 0) {
                                error_log("Ocurrio un error al crear detalle del pago");
                            } else {
                                /* Aplica solo para clientes de Crédito */
                                $reciboElectronico = new com\detisa\omicrom\ReciboElectronicoDePago($id);

                                $wsdl = FACTENDPOINT;
                                $client = new nusoap_client($wsdl, true);
                                $client->timeout = 720;
                                $client->response_timeout = 720;
                                $client->soap_defencoding = 'UTF-8';
                                $client->namespaces = array("SOAP-ENV" => "http://schemas.xmlsoap.org/soap/envelope/");

                                $params = array(
                                    "cfdi" => $reciboElectronico->getComprobante()->asXML()->saveXML(),
                                    "formato" => "A1",
                                    "tipo" => "RP",
                                    "idfc" => $id
                                );

                                try {

                                    if ($ciaVO->getFacturacion() == 'Si') {
                                        error_log("Enviamos la peticion al WS: ");
                                        $result = $client->call("cfdiXml", $params);
                                    }

                                    $facValida = $result["return"]["valid"];
                                    $err = $client->getError();

                                    if ($err || $facValida == 'false') {

                                        $cError = utf8_encode($result["return"]["error"]);
                                        $Msj = $cError;
                                    } else {
                                        error_log("Actualizando el status del pago...");
                                        $pagoVO = $pagoDAO->retrieve($id);
                                        $pagoVO->setStatus_pago(StatusPagoPrepago::CON_NOTA_CREDITO);
                                        if ($pagoDAO->update($pagoVO)) {
                                            $Msj = utils\Messages::MESSAGE_RINGING_SUCCESS;
                                        } else {
                                            $Msj = utils\Messages::RESPONSE_ERROR;
                                        }
                                    }
                                } catch (Exception $e) {
                                    error_log($e->getMessage());
                                    $Msj = "Error : " . $e->getMessage();
                                }
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                error_log($e->getMessage());
                $Msj = "Error : " . $e->getMessage();
            }
        } else if ($request->getAttribute("op") === "ActualizaSaldo") {
            $pagoVO = $pagoDAO->retrieve($cVarVal);
            $BuscaCant = "SELECT SUM(importeDelPago) total FROM unidades_log WHERE noPago = " . $cVarVal;
            $CntTotal = utils\IConnection::execSql($BuscaCant);

            $BuscaUnidad = "SELECT importe FROM unidades WHERE id = " . $request->getAttribute("IdUnidad");
            $UnidadDet = utils\IConnection::execSql($BuscaUnidad);

            if ($request->getAttribute("SaldoSum") + $CntTotal["total"] <= $pagoVO->getAplicado()) {
                if (($CntTotal["total"] <= $pagoVO->getImporte() && !($CntTotal["total"] == $pagoVO->getImporte())) || $request->getAttribute("SaldoSum") < 0) {

                    $Result = $UnidadDet["importe"] + $request->getAttribute("SaldoSum");

                    $InsertLog = "INSERT INTO unidades_log (noPago,importeAnt,importe,importeDelPago,idUnidad,usr) VALUES "
                            . "($cVarVal," . $UnidadDet["importe"] . "," . $Result . "," . $request->getAttribute("SaldoSum") . ","
                            . "'" . $request->getAttribute("IdUnidad") . "','" . $usuarioSesion->getUsername() . "');";
                    if (!$mysqli->query($InsertLog)) {
                        error_log("ERROR EN QUERY " . $InsertLog);
                    }
                    $UpdateUnidad = "UPDATE unidades SET importe = importe + " . $request->getAttribute("SaldoSum") .
                            " WHERE id = " . $request->getAttribute("IdUnidad");
                    if (!$mysqli->query($UpdateUnidad)) {
                        error_log("ERROR EN QUERY " . $UpdateUnidad);
                    } else {
                        $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                    }

                    $Return = "pagosd33.php?busca=" . $request->getAttribute("IdPago");
                } else {
                    $Msj = "Error: Usado " . $CntTotal["total"] . " Importe del pago : " . $pagoVO->getImporte();
                }
            } else {
                $Msj = "Error el importe es mayor el monto del pago";
            }
            $Return = "pagosd33.php?busca=" . $request->getAttribute("IdPago");
        } elseif ($request->getAttribute("op") === "LibX") {
            $Update = "UPDATE pagos SET status_pago=2 WHERE id = " . $request->getAttribute("IdPago") . ";";
            utils\IConnection::execSql($Update);
            $Return = "pagosd33.php?";
        } else if ($request->getAttribute("op") === "AddDif") {
            $InsertPagoS2 = "INSERT INTO pagos (cliente,fecha,fecha_deposito,concepto,importe,aplicado,referencia,status,banco,formapago,numoperacion,uuid,statusCFDI,stCancelacion,"
                    . "motivoCan,fechar,usr,tiporelacion,relacioncfdi,status_pago,relacion,usocfdi,saldoFavor) SELECT cliente,now(),fecha_deposito,CONCAT(concepto, ' ', ' SALDO A FAVOR')," . $request->getAttribute("Dif") . ","
                    . "" . $request->getAttribute("Dif") . ",referencia,'Abierta',banco,formapago,numoperacion,'-----',0,0,null,now(),'" . $usuarioSesion->getNombre() . "',tiporelacion,relacioncfdi,"
                    . "2,relacion,usocfdi,1 FROM pagos WHERE id = " . $request->getAttribute("IdPago") . ";";
            $mysqli->query($InsertPagoS2);
            $idnvo = $mysqli->insert_id;
            $Update = "UPDATE pagos SET saldoFavor=$idnvo WHERE id = " . $request->getAttribute("IdPago") . ";";
            utils\IConnection::execSql($Update);
        } else if ($request->getAttribute("op") === "TimbraNcFc") {
            try {
                if ($request->getAttribute("UsoCfdi") === "Si") {
                    $UsoCfdi = "'G02' usocfdi";
                } else {
                    $UsoCfdi = "fc.usocfdi";
                }
                $DateListo = date("Y-m-d H:i:s", strtotime($request->getAttribute("FechaEmision")));
                $Sql = "INSERT INTO nc ( fecha, cliente, cantidad, importe, iva, ieps, total, observaciones, formadepago, metododepago, factura, usocfdi, tiporelacion, relacioncfdi, usr)
                            SELECT '" . $DateListo . "', p.cliente , 1 cantidad , ROUND(" . $request->getAttribute("TotalFc") . "/1.16,2) importe , "
                        . "(" . $request->getAttribute("TotalFc") . " - ROUND(" . $request->getAttribute("TotalFc") . "/1.16,2)) iva, 
                            0 ieps , " . $request->getAttribute("TotalFc") . " total , CONCAT('Nota Credito relacionada al pago ', p.id) observaciones,
                            '30' formapago , 'PUE' metododepago , p.id factura ,$UsoCfdi , '07' tiporelacion, 
                            '" . $request->getAttribute("IdFc") . "' relacioncfdi,
                            '" . $usuarioSesion->getUsername() . "' usr
                            FROM pagos p LEFT JOIN fc ON " . $request->getAttribute("IdFc") . " = fc.id WHERE p.id = '$cVarVal'";
                error_log($Sql);
                if (!$mysqli->query($Sql)) {
                    error_log($mysqli->error);
                    error_log($Sql);
                }


                // Recuperamos el max id de nc
                $SqlNc = " SELECT id,relacioncfdi FROM nc WHERE formadepago = '30' AND factura = '$cVarVal' AND status = " . StatusNotaCredito::ABIERTO . " ";
                error_log($SqlNc);
                $queryNc = $mysqli->query($SqlNc);
                $rsNc = $queryNc->fetch_assoc();

                error_log("Retrive id nc: " . $rsNc['id']);

                // insertamos en ncd
                error_log("Creamos la Nota de Credito");
                $notaCreditoAnticipo = new com\detisa\omicrom\NotaCreditoAnticipoDAO($rsNc['id']);
                //error_log(json_encode(array("Comprobante" => $notaCreditoAnticipo->getComprobante()->asJsonArray())));
                $document = $notaCreditoAnticipo->getComprobante()->asXML();

                $a = $document->save("/home/omicrom/xml/prb.xml");
                if (count($notaCreditoAnticipo->getComprobante()->getConceptos()->getConcepto()) == 0) {
                    
                }
                //error_log(print_r($notaCreditoAnticipo, TRUE));

                $wsdl = FACTENDPOINT;
                $client = new nusoap_client($wsdl, true);
                $client->timeout = 720;
                $client->response_timeout = 720;
                $client->soap_defencoding = 'UTF-8';
                $client->namespaces = array("SOAP-ENV" => "http://schemas.xmlsoap.org/soap/envelope/");

                $params = array(
                    "cfdi" => $notaCreditoAnticipo->getComprobante()->asXML()->saveXML(),
                    "formato" => "A1",
                    "tipo" => "CR",
                    "idfc" => $rsNc['id']
                );

                if ($ciaVO->getFacturacion() == 'Si') {
                    error_log("Enviamos la peticion al WS: ");
                    $result = $client->call("cfdiXml", $params);
                }

                $facValida = $result["return"]["valid"];
                $err = $client->getError();

                if ($err || $facValida == 'false') {

                    $cError = utf8_encode($result["return"]["error"]);
                    $Msj = $cError;
                } else {
                    $pagoVO = $pagoDAO->retrieve($cVarVal);
//                    $pagoVO->setStatus_pago(StatusPagoPrepago::CON_NOTA_CREDITO);
                    if ($pagoDAO->update($pagoVO)) {

                        $updateCxc = "UPDATE cxc SET factura = '$rsNc[relacioncfdi]' "
                                . "WHERE cxc.tm = 'H' "
                                . "AND cxc.cliente = '" . $pagoVO->getCliente() . "' "
                                . "AND cxc.referencia = '" . $pagoVO->getId() . "' "
                                . "AND cxc.recibo = '" . $pagoVO->getId() . "' LIMIT 1;";
                        error_log($updateCxc);
                        if (!($mysqli->query($updateCxc))) {
                            error_log($mysqli->error);
                        }

                        $Msj = utils\Messages::MESSAGE_RINGING_SUCCESS;
                    } else {
                        $Msj = utils\Messages::RESPONSE_ERROR;
                    }
                    $Return = "pagosd33.php?Msj=" . $Msj;
                }
            } catch (Exception $e) {
                error_log($e->getMessage());
                $Msj = "Error : " . $e->getMessage();
            }
        }

        if ($Return != NULL) {
            $Return .= "&Msj=" . urlencode($Msj);
        }
    } catch (Exception $ex) {
        error_log("Error en pagos: " . $ex);
    } finally {
        if ($Return != NULL) {
            header("Location: " . $Return);
        }
    }
}

/**
 * 
 * @global int $cVarVal
 * @global PagoDAO $pagoDAO
 * @global ClientesDAO $clienteDAO
 * @global IslaVO $islaVO
 * @global CxcDAO $cxcDAO
 * @global PagoseDAO $pagoseDAO
 * @param int $Factura
 * @return boolean
 */
function cargarFactura($Factura) {
    global $cVarVal, $pagoDAO, $islaVO, $cxcDAO, $pagoseDAO;

    $Response = false;

    $mysqli = iconnect();

    $sqlPagos = "SELECT IFNULL(SUM(pagose.importe),0) imp,pagos.importe pago,
                IFNULL(ROUND( (pagos.importe - IFNULL(SUM(pagose.importe),0) ),2),0) diferencia
                FROM pagos LEFT JOIN pagose ON pagos.id = pagose.id
                WHERE  pagos.id = '$cVarVal'";

    $Tot = utils\IConnection::execSql($sqlPagos);

    if ($Tot["diferencia"] > 0) {
        $pagoVO = $pagoDAO->retrieve($cVarVal);

        $sqlUpdateCXC = "UPDATE cxc SET recibo = -recibo,factura = -factura,cliente = -cliente, corte = -corte 
                        WHERE cliente = '" . $pagoVO->getCliente() . "' AND tm = 'H' 
                        AND recibo = '" . $pagoVO->getId() . "' AND factura = '" . $Factura . "';";
        if (!($mysqli->query($sqlUpdateCXC))) {
            error_log($mysqli->error . "\n" . $sqlUpdateCXC);
        }

        $sqlFc = "SELECT * FROM fc WHERE id = '" . $Factura . "'";
        $Fc = utils\IConnection::execSql($sqlFc);

        $Importe = getSaldoFactura($Factura, $pagoVO->getCliente());
        $Total = $Tot['diferencia'];
        if ($Importe <= $Tot['diferencia'] || $Tot['diferencia'] == 0) {
            $Total = $Importe <= $Tot['pago'] ? $Importe : $Importe - ($Importe - $Tot['pago']);
        }

        $cxcVO = crearRegistroCxc($pagoVO, $islaVO, $Fc, $Factura, $Total);

        if (($cxcDAO->create($cxcVO)) > 0) {
            $pagoseVO = new PagoseVO();
            $pagoseVO->setIdPago($pagoVO->getId());
            $pagoseVO->setFactura($cxcVO->getFactura());
            $pagoseVO->setImporte($cxcVO->getImporte());
            $pagoseVO->setTipo(TipoPagoDetalle::FACTURA);

            $Response = ($pagoseDAO->create($pagoseVO)) > 0;
            $pagoseDAO->calculoPorcetajePagado($pagoVO->getId(), $Factura);
        }
    } else {
        $Response = "Ya se ha cubierto el importe del pago, no es posible cargar mas facturas.";
    }

    return $Response;
}

/**
 * 
 * @global int $cVarVal
 * @global PagoDAO $pagoDAO
 * @global PagoseDAO $pagoseDAO
 * @param int $Consumo
 * @return string
 */
function cargarConsumo($Consumo) {
    global $cVarVal, $pagoDAO, $pagoseDAO;

    $Response = false;

    $mysqli = iconnect();

    $sqlPagos = "SELECT IFNULL(SUM(pagose.importe),0) imp,pagos.importe pago,
                IFNULL(ROUND( (pagos.importe - IFNULL(SUM(pagose.importe),0) ),2),0) diferencia
                FROM pagos LEFT JOIN pagose ON pagos.id = pagose.id
                WHERE  pagos.id = '$cVarVal'";

    $Tot = utils\IConnection::execSql($sqlPagos);

    if ($Tot["diferencia"] > 0) {
        $pagoVO = $pagoDAO->retrieve($cVarVal);

        $sqlUpdateRM = "UPDATE rm SET pagado = $cVarVal WHERE id = '" . $Consumo . "';";
        $sqlUpdateVt = "UPDATE vtaditivos SET pagado = $cVarVal WHERE referencia = '" . $Consumo . "' AND cliente = '" . $pagoVO->getCliente() . "';";
        if (!($mysqli->query($sqlUpdateRM)) || !($mysqli->query($sqlUpdateVt))) {
            error_log($mysqli->error . "\n" . $sqlUpdateRM);
        } else {

            $sqlRm = "SELECT (rm.pagoreal + IFNULL(SUM(vt.total), 0) ) total FROM rm LEFT JOIN vtaditivos vt ON vt.referencia = rm.id AND vt.tm = 'C' WHERE TRUE AND rm.id = '" . $Consumo . "'";
            $Rm = utils\IConnection::execSql($sqlRm);

            $pagoseVO = new PagoseVO();
            $pagoseVO->setIdPago($pagoVO->getId());
            $pagoseVO->setReferencia($Consumo);
            $pagoseVO->setImporte($Rm["total"]);
            $pagoseVO->setTipo(TipoPagoDetalle::COMBUSTIBLE);

            $Response = ($pagoseDAO->create($pagoseVO)) > 0;
        }
    } else {
        $Response = "Ya se ha cubierto el importe del pago, no es posible agregar mas consumos.";
    }

    return $Response;
}

/**
 * 
 * @global int $cVarVal
 * @global PagoDAO $pagoDAO
 * @global PagoseDAO $pagoseDAO
 * @param int $Consumo
 * @return string
 */
function cargarConsumoAce($Consumo) {
    global $cVarVal, $pagoDAO, $pagoseDAO;

    $Response = false;

    $mysqli = iconnect();

    $sqlPagos = "SELECT IFNULL(SUM(pagose.importe),0) imp,pagos.importe pago,
                IFNULL(ROUND( (pagos.importe - IFNULL(SUM(pagose.importe),0) ),2),0) diferencia
                FROM pagos LEFT JOIN pagose ON pagos.id = pagose.id
                WHERE  pagos.id = '$cVarVal'";

    $Tot = utils\IConnection::execSql($sqlPagos);

    if ($Tot["diferencia"] > 0) {
        $pagoVO = $pagoDAO->retrieve($cVarVal);

        $sqlUpdateRM = "UPDATE vtaditivos SET pagado =  $cVarVal WHERE id = '" . $Consumo . "';";
        if (!($mysqli->query($sqlUpdateRM))) {
            error_log($mysqli->error . "\n" . $sqlUpdateRM);
        } else {

            $sqlVt = "SELECT vt.total FROM vtaditivos vt WHERE vt.id = '" . $Consumo . "'";
            $Vt = utils\IConnection::execSql($sqlVt);

            $pagoseVO = new PagoseVO();
            $pagoseVO->setIdPago($pagoVO->getId());
            $pagoseVO->setReferencia($Consumo);
            $pagoseVO->setImporte($Vt["total"]);
            $pagoseVO->setTipo(TipoPagoDetalle::ACEITES);

            $Response = ($pagoseDAO->create($pagoseVO)) > 0;
        }
    } else {
        $Response = "Ya se ha cubierto el importe del pago, no es posible agregar mas consumos.";
    }

    return $Response;
}

/**
 * 
 * @global int $cVarVal
 * @param PagoVO $pagoVO
 * @param IslaVO $islaVO
 * @param array() $Fc
 * @param int $Factura
 * @param double $Total
 * @return \CxcVO
 */
function crearRegistroCxc($pagoVO, $islaVO, $Fc, $Factura, $Total) {
    global $cVarVal, $islaVO;

    $cxcVO = new CxcVO();
    $cxcVO->setCliente($pagoVO->getCliente());
    $cxcVO->setPlacas("Factura");
    $cxcVO->setReferencia($Factura);
    $cxcVO->setFecha($pagoVO->getFecha_deposito());
    $cxcVO->setHora(date("H:i:s"));
    $cxcVO->setTm("H");
    $cxcVO->setConcepto("Pago de Factura No. " . ( $Fc['serie'] == NULL || $Fc['serie'] == '' ? '' : ( $Fc['serie'] . '-' ) ) . $Fc['folio'] . " recibo " . $cVarVal);
    $cxcVO->setCantidad(1);
    $cxcVO->setImporte($Total);
    $cxcVO->setRecibo($pagoVO->getId());
    $cxcVO->setCorte($islaVO->getCorte());
    $cxcVO->setRubro("-----");
    $cxcVO->setProducto("-");
    $cxcVO->setFactura($Factura);

    return $cxcVO;
}

function getSaldoFactura($Factura, $Cliente) {
    global $cVarVal;
    $Importe = 0;
    $selectSaldo = "SELECT IFNULL(SUM(sub.importe),0) saldo 
                    FROM(
                        SELECT cxc.cliente,cxc.tm,cxc.factura,IFNULL(pagose.factura,0) ref,
                        ROUND(SUM(IF(cxc.tm = 'H',-cxc.importe,cxc.importe)),2) importe
                        FROM cxc 
                        LEFT JOIN pagose ON cxc.factura = pagose.factura AND pagose.id = $cVarVal
                        WHERE cxc.cliente = $Cliente
                        GROUP BY cxc.factura,cxc.tm
                        ORDER BY cxc.factura DESC
                    ) sub 
                    WHERE sub.ref = 0 AND sub.factura = $Factura
                    GROUP BY sub.factura,sub.cliente
                    HAVING SUM(sub.importe) > 0 AND sub.factura IS NOT NULL";

    $rg = utils\IConnection::execSql($selectSaldo);

    if (is_array($rg) && $rg["saldo"] > 0) {
        $Importe = $rg["saldo"];
    }

    return $Importe;
}
