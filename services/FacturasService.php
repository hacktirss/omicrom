<?php

#Librerias
include_once ('data/FcDAO.php');
include_once ('data/ClientesDAO.php');
include_once ('data/CxcDAO.php');
include_once ('data/IslaDAO.php');
include_once ('data/CancelacionDAO.php');
include_once ('data/PagoDAO.php');
include_once ('data/NcDAO.php');
include_once ('data/CiaDAO.php');
include_once ('data/V_CorporativoDAO.php');
include_once ("data/RelacionCfdiDAO.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "facturas.php?";

$fcDAO = new FcDAO();
$clientesDAO = new ClientesDAO();
$cxcDAO = new CxcDAO();
$islaDAO = new IslaDAO();
$ciaDAO = new CiaDAO();
$vCorporativoDAO = new V_CorporativoDAO();

$ciaVO = $ciaDAO->retrieve(1);
$islaVO = $islaDAO->retrieve(1, "isla");

if ($request->hasAttribute("id")) {
    $returnLink = urlencode("facturase.php?Boton=Agregar&");
    $backLink = urlencode("facturas.php?criteria=ini");
    header("Location: clientes.php?criteria=ini&Facturar=1&backLink=$backLink&returnLink=$returnLink");
}

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {

    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    $fcVO = new FcVO();
    $fcVO->setId($sanitize->sanitizeInt("busca"));
    if (is_numeric($fcVO->getId())) {
        $fcVO = $fcDAO->retrieve($fcVO->getId());
    }
    try {
        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {

            $serie = $ciaVO->getSerie();
            $fcVO->setCliente($sanitize->sanitizeInt("Cliente"));
            $clienteVO = $clientesDAO->retrieve($fcVO->getCliente());

            if ($clienteVO->getFacturacion() === "1") {
                if ($clienteVO->getRfc() === ClientesDAO::GENERIC_RFC) {
                    $vCorporativoVO = $vCorporativoDAO->retrieve(Series::GENERAL);
                } elseIf ($clienteVO->getTipodepago() === TiposCliente::CREDITO) {
                    $vCorporativoVO = $vCorporativoDAO->retrieve(Series::CREDITO);
                } elseIf ($clienteVO->getTipodepago() === TiposCliente::PREPAGO) {
                    $vCorporativoVO = $vCorporativoDAO->retrieve(Series::DEBITO);
                } elseif ($clienteVO->getTipodepago() === TiposCliente::MONEDERO) {
                    $vCorporativoVO = $vCorporativoDAO->retrieve(Series::MONEDERO);
                } else {
                    $vCorporativoVO = $vCorporativoDAO->retrieve(Series::CONTADO);
                }

                if (!empty($vCorporativoVO->getValor())) {
                    $serie = $vCorporativoVO->getValor();
                }

                $folio = utils\IConnection::execSql("SELECT IFNULL( MAX( fc.folio ), 0 ) + 1 folio FROM fc WHERE fc.serie = '$serie'");

                $fcVO->setSerie($serie);
                $fcVO->setFolio($folio["folio"]);
                $fcVO->setStatus(StatusFactura::ABIERTO);
                $fcVO->setUsr($usuarioSesion->getUsername());
                $fcVO->setOrigen(OrigenFactura::OMICROM);
                $fcVO->setFormadepago($clienteVO->getFormadepago());

                if ($request->hasAttribute("Anticipo") && !empty($sanitize->sanitizeInt("Anticipo"))) {
                    $anticipo = $sanitize->sanitizeInt("Anticipo");
                    $fcVO->setDocumentoRelacion(TipoDocumento::ANTICIPO);
                    $fcVO->setTiporelacion("07");
                    $fcVO->setRelacioncfdi($anticipo);

                    $sql_ant = "SELECT id, folio, relacioncfdi FROM fc WHERE relacioncfdi = '" . $anticipo . "' AND tdoctorelacionado = 'ANT' AND status < '" . StatusFactura::CANCELADO . "'";
                    $ant = utils\IConnection::execSql($sql_ant);

                    utils\HTTPUtils::setSessionBiValue("catalogoFacturas", "fmt", 0);

                    if (utils\HTTPUtils::getSessionValue("NvaSerieP") !== "" && utils\HTTPUtils::getSessionValue("NvaSerieP") !== null) {
                        $fcVO->setSerie(utils\HTTPUtils::getSessionValue("NvaSerieP"));
                        utils\HTTPUtils::setSessionValue("NvaSerieP", "");
                    }
                    if ($ant['relacioncfdi'] == $anticipo) {
                        //$Msj = "La factura " . $ant["folio"] . " a sido asociada previamente al recibo " . $anticipo . "!";
                        $Return = "facturasd.php?criteria=ini&cVarVal=" . $ant["id"];
                    } elseif (($id = $fcDAO->create($fcVO)) > 0) {
                        $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                        $Return = "facturasd.php?criteria=ini&cVarVal=" . $id;
                    } else {
                        $Msj = utils\Messages::RESPONSE_ERROR;
                    }
                } elseif ($request->hasAttribute("Pago") && !empty($sanitize->sanitizeInt("Pago"))) {
                    $pago = $sanitize->sanitizeInt("Pago");
                    $fcVO->setDocumentoRelacion(TipoDocumento::FACTURA);
                    $fcVO->setRelacioncfdi($pago);
                    $fcVO->setUsocfdi("P01");
                    $fcVO->setMetododepago("PPD");

                    $sql_ant = "SELECT id, folio, relacioncfdi FROM fc WHERE relacioncfdi = '" . $pago . "' AND status < '" . StatusFactura::CANCELADO . "'";
                    $ant = utils\IConnection::execSql($sql_ant);
                    if (utils\HTTPUtils::getSessionValue("NvaSerieP") !== "" && utils\HTTPUtils::getSessionValue("NvaSerieP") !== null) {
                        $fcVO->setSerie(utils\HTTPUtils::getSessionValue("NvaSerieP"));
                        utils\HTTPUtils::setSessionValue("NvaSerieP", "");
                    }
                    if ($ant['relacioncfdi'] == $pago) {
                        //$Msj = "La factura " . $ant["folio"] . " a sido asociada previamente al recibo " . $pago . "!";
                        $Return = "facturasd.php?criteria=ini&cVarVal=" . $ant["id"];
                    } elseif (($id = $fcDAO->create($fcVO)) > 0) {
                        $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                        $Return = "facturasd.php?criteria=ini&cVarVal=" . $id;
                    } else {
                        $Msj = utils\Messages::RESPONSE_ERROR;
                    }
                } else {
                    $sqlPrev = "SELECT id FROM fc WHERE cliente = '" . $clienteVO->getId() . "' "
                            . "AND fecha > DATE_ADD( NOW(), INTERVAL - 20 SECOND ) AND status = '" . StatusFactura::ABIERTO . "'";
                    $Prev = $mysqli->query($sqlPrev)->fetch_array();
                    if (empty($Prev["id"])) {
                        $fcVO->setDocumentoRelacion(TipoDocumento::FACTURA);
                        if ($request->hasAttribute("Pago")) {
                            $fcVO->setRelacioncfdi($sanitize->sanitizeInt("Pago"));
                        }
                        if (utils\HTTPUtils::getSessionValue("NvaSerieP") !== "" && utils\HTTPUtils::getSessionValue("NvaSerieP") !== null) {
                            $fcVO->setSerie(utils\HTTPUtils::getSessionValue("NvaSerieP"));
                            utils\HTTPUtils::setSessionValue("NvaSerieP", "");
                        }
                        if (($id = $fcDAO->create($fcVO)) > 0) {
                            $clienteVO = $clientesDAO->retrieve($fcVO->getCliente());
                            if ($clienteVO->getTipodepago() === "Credito") {
                                $fcVO = $fcDAO->retrieve($id);
                                $fcVO->setFormadepago(99);
                                $fcVO->setMetododepago("PPD");
                                $fcDAO->update($fcVO);
                            }
                            $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                            $Return = "facturasd.php?criteria=ini&cVarVal=" . $id;
                        } else {
                            $Msj = utils\Messages::RESPONSE_ERROR;
                        }
                    } else {
                        $Return = "facturasd.php?criteria=ini&cVarVal=" . $Prev["id"];
                    }
                }
            } else {
                $Msj = "Error: el cliente [" . $clienteVO->getNombre() . "] no tiene permisos para facturar.";
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
            if ($fcDAO->update($fcVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_SEND_EMAIL) {
            if (!empty($sanitize->sanitizeEmail("Correo"))) {
                $clienteVO = $clientesDAO->retrieve($fcVO->getCliente());
                $Msj = enviarCorreo($fcVO->getUuid(), $sanitize->sanitizeEmail("Correo"), $clienteVO->getCorreo2());
            } else {
                $Msj = "El correo ingresado es invalido!";
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_CANCEL) {
            $busca = $sanitize->sanitizeInt("busca");
            $pagoDAO = new PagoDAO();
            $UpdateMotivo = "UPDATE fc SET motivoCan = '" . $sanitize->sanitizeString("TipoCancelacion") . "' WHERE id = " . $fcVO->getId();
            $fcVO = $fcDAO->retrieve($fcVO->getId());
            if ($ciaVO->getMaster() === $sanitize->sanitizeString("Password")) {
                $SqlCia = "SELECT tipo_permiso FROM cia;";
                $CiaTp = utils\IConnection::execSql($SqlCia);
                if ($CiaTp["tipo_permiso"] === "TRA") {
                    $SqlRmAJ = "SELECT fc.folio,fcd.ticket FROM fc LEFT JOIN fcd ON fcd.id=fc.id WHERE fc.id= $busca AND fcd.ticket > 0";
                    $AJarreo = utils\IConnection::execSql($SqlRmAJ);
                    $Upd = "UPDATE rm SET tipo_venta ='J' WHERE id = " . $AJarreo["ticket"] . " LIMIT 1";
                    utils\IConnection::execSql($Upd);
                    $UpdStatus = "UPDATE ingresos SET status = 3 WHERE id in (SELECT id FROM ingresos WHERE folio = " . $AJarreo["folio"] . ") LIMIT 1;";
                    utils\IConnection::execSql($UpdStatus);
                }
                $mysqli->query($UpdateMotivo);
                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "CANCELACION DE FACTURA,  FOLIO: " . $fcVO->getFolio());

                $updateCxc = "UPDATE cxc SET factura = null WHERE factura = '$busca'  AND tm = 'C' AND cliente = '" . $fcVO->getCliente() . "';";
                $updateFcd = "UPDATE fcd SET ticket = -ticket WHERE id = '$busca';";
                $fcVO->setCancelacion($usuarioSesion->getNombre());
                if ($fcVO->getStatus() == StatusFactura::ABIERTO && $fcVO->getUuid() === FcDAO::SIN_TIMBRAR) {
                    $fcVO->setCantidad(0);
                    $fcVO->setImporte(0);
                    $fcVO->setIva(0);
                    $fcVO->setIeps(0);
                    $fcVO->setTotal(0);
                    $fcVO->setStatus(StatusFactura::CANCELADO_ST);
                    $fcVO->setStCancelacion(StatusCancelacionFactura::CANCELADA_CONFIRMADA);
                } else {
                    $fcVO->setRelacioncfdi(-$fcVO->getRelacioncfdi());
                    $fcVO->setStatus(StatusFactura::CANCELADO);
                    $fcVO->setStCancelacion(StatusCancelacionFactura::PENDIENTE_CANCELAR);
                }

                if ($fcDAO->update($fcVO)) {

                    if (empty($fcVO->getUuid()) || $fcVO->getUuid() === FcDAO::SIN_TIMBRAR) {
                        if (!($mysqli->query($updateCxc)) || !($mysqli->query($updateFcd))) {
                            error_log($mysqli->error);
                        }
                    } else {
                        if (!($mysqli->query($updateCxc))) {
                            error_log($mysqli->error);
                        }
                    }

                    if ($fcVO->getTiporelacion() === "07" && $fcVO->getDocumentoRelacion() === "ANT") {
                        $pagoVO = $pagoDAO->retrieve(abs($fcVO->getRelacioncfdi()));
                        $pagoVO->setStatus_pago(StatusPagoPrepago::CON_ANTICIPO);
                        if (!$pagoDAO->update($pagoVO)) {
                            error_log($mysqli->error);
                        }
                    }

                    if (empty($fcVO->getUuid()) || $fcVO->getUuid() === FcDAO::SIN_TIMBRAR) {
                        $Msj = $Msj = utils\Messages::RESPONSE_VALID_CANCEL;
                    } else {
                        $updateRm = "UPDATE rm SET rm.uuid = '-----' WHERE rm.uuid = '" . $fcVO->getUuid() . "';";
                        $updateVtaditivos = "UPDATE vtaditivos SET vtaditivos.uuid = '-----' WHERE vtaditivos.uuid = '" . $fcVO->getUuid() . "';";
                        $UpdateIngresos = "UPDATE  ingresos SET status = 3 WHERE uuid = '" . $fcVO->getUuid() . "';";
                        utils\IConnection::execSql($UpdateIngresos);
                        $UpdateRmCanceldos = "UPDATE rm SET tipo_venta='J' WHERE id in ( SELECT rm.id FROM omicrom.ingresos ing LEFT JOIN ingresos_detalle ingd ON ing.id=ingd.id 
                        LEFT JOIN rm ON rm.id = ingd.id_rm WHERE ing.uuid = '" . $fcVO->getUuid() . "' AND ingd.id_rm > 0); ";
                        utils\IConnection::execSql($UpdateRmCanceldos);
                        if (!($mysqli->query($updateRm)) || !($mysqli->query($updateVtaditivos))) {
                            error_log($mysqli->error);
                        }

                        $wsdl = FACTENDPOINT;
                        error_log($wsdl);
                        $client = new nusoap_client($wsdl, true);
                        $client->timeout = 180;
                        $client->soap_defencoding = 'UTF-8';
                        $client->namespaces = array("SOAP-ENV" => "http://schemas.xmlsoap.org/soap/envelope/");
                        $fcVO = $fcDAO->retrieve($fcVO->getId());
                        $relacion = $fcVO->getRelacioncfdi() == 0 ? "" : -$fcVO->getRelacioncfdi();
                        $clienteVO = $clientesDAO->retrieve($fcVO->getCliente());

                        $ParamTc = $clienteVO->getRfc() === "XAXX010101000" ? "04" : "02";

                        $parm = "|" . $fcVO->getUuid() . "|$ParamTc||";

                        $params = array(
                            "uuid" => array($parm)
                        );
                        error_log("WS Parámetros de Cancelación" . print_r($params, true));
                        $result = $client->call("cancelacion", $params, false, '', '');
                        $RelacionCFDI = new RelacionCfdiDAO();
                        $RelacionCFDI->liberaUuid($fcVO->getUuid());
                        if ($client->fault) {
                            error_log(print_r($result, TRUE));
                            $Msj = utils\Messages::RESPONSE_ERROR;
                        } else {
                            $err = $client->getError();
                            if ($err) {
                                error_log(print_r($err, TRUE));
                                $Msj = utils\Messages::RESPONSE_ERROR;
                            } else {
                                if ($result['return']['canceled'] == "true") {
                                    $Msj = "Comprobante Cancelado Exitosamente";
                                } else {
                                    $Msj = "Error Cancelando el Comprobante " . $result['return']['error'];
                                }
                            }
                        }
                    }
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            } else {
                $Msj = utils\Messages::RESPONSE_PASSWORD_INCORRECT;
            }
        } else if ($request->getAttribute("Boton") === "Guardar Motivo") {
            $CancelacionDAO = new CancelacionDAO();
            $CancelacionVO = new CancelacionVO();
            $CancelacionVO->setTabla("fc");
            $CancelacionVO->setId_origen(utils\HTTPUtils::getSessionValue("busca"));
            $CancelacionVO->setDescripcion_evento($request->getAttribute("MotivoCancelacion"));
            $CancelacionDAO->create($CancelacionVO);
            $Msj = utils\Messages::MESSAGE_DEFAULT;
            $Return = "canfactura.php?busca=" . utils\HTTPUtils::getSessionValue("busca");
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en facturas: " . $ex);
    } finally {
        header("Location: $Return");
    }
}


if ($request->hasAttribute("op")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $busca = $sanitize->sanitizeInt("busca");

    try {
        $fcVO = $fcDAO->retrieve($busca);
        $clienteVO = $clientesDAO->retrieve($fcVO->getCliente());

        if ($request->getAttribute("op") === "Asentar") {

            $sqlUpdateFactura = "UPDATE cxc SET factura = null WHERE factura = '" . $fcVO->getId() . "' AND cliente =  " . $fcVO->getCliente() . " AND tm = 'C'";
            if (!$mysqli->query($sqlUpdateFactura)) {
                error_log($mysqli->error);
            }

            if ($clienteVO->getTipodepago() === TiposCliente::CREDITO || $clienteVO->getTipodepago() === TiposCliente::PREPAGO) {

                $Msj = "Consolidacion de factura " . $fcVO->getFolio() . " exitosa.";

                $sqlUpdateTicketsCombustible = "UPDATE cxc LEFT JOIN(
                        SELECT fcd.ticket,fc.cliente
                        FROM fc,fcd,inv
                        WHERE 
                        fc.id = fcd.id 
                        AND fcd.producto = inv.id
                        AND inv.rubro = 'Combustible'
                        AND fc.id = '" . $fcVO->getId() . "'
                        AND fcd.ticket <> 0
                    ) fcd 
                    ON cxc.referencia = fcd.ticket 
                    SET cxc.factura = '" . $fcVO->getId() . "' 
                    WHERE cxc.tm = 'C' AND cxc.referencia = fcd.ticket AND cxc.cliente = fcd.cliente";

                if (!$mysqli->query($sqlUpdateTicketsCombustible)) {
                    error_log($mysqli->error);
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }

                $sqlUpdateTicketsAceites = "UPDATE cxc LEFT JOIN(
                        SELECT fcd.ticket,fc.cliente
                        FROM fc,fcd,inv
                        WHERE 
                        fc.id = fcd.id 
                        AND fcd.producto = inv.id
                        AND inv.rubro = 'Aceites'
                        AND fc.id = '" . $fcVO->getId() . "'
                        AND fcd.ticket <> 0) fcd 
                    ON cxc.referencia = fcd.ticket 
                    SET cxc.factura = '" . $fcVO->getId() . "' 
                    WHERE 
                    cxc.tm = 'C' AND cxc.referencia = fcd.ticket AND cxc.cliente = fcd.cliente";

                if (!$mysqli->query($sqlUpdateTicketsAceites)) {
                    error_log($mysqli->error);
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            }
        } elseif ($request->getAttribute("op") === "Acentar0") {
            if ($clienteVO->getTipodepago() === TiposCliente::CREDITO || $clienteVO->getTipodepago() === TiposCliente::PREPAGO) {

                $Msj = "Se han cargado los movimientos manuales de la factura " . $fcVO->getFolio();

                $sqlUpdateCxcLogic = "UPDATE cxc SET cliente = -cliente, factura = -factura 
                                      WHERE factura = '" . $fcVO->getId() . "' AND cliente =  " . $fcVO->getCliente() . " AND tm = 'C' AND referencia = 0;";
                if (!$mysqli->query($sqlUpdateCxcLogic)) {
                    error_log($mysqli->error);
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }

                $sqlInsertTickets = "INSERT INTO cxc (cliente,referencia,fecha,hora,tm,concepto,cantidad,importe,corte,producto,factura)
                                    SELECT fc.cliente,fcd.ticket referencia,DATE(fc.fecha) fecha,TIME(fc.fecha) hora,'C' tm,'Venta de combustible' concepto,
                                    fcd.cantidad,(fcd.importe / ( 1 + fcd.iva_retenido )),'" . $islaVO->getCorte() . "' corte,IFNULL(com.clavei,'A') producto,fc.id factura 
                                    FROM cli,fc,fcd 
                                    LEFT JOIN com ON fcd.producto = com.id 
                                    WHERE fc.id = fcd.id  AND fc.cliente = cli.id 
                                    AND fcd.id = '" . $fcVO->getId() . "' AND fcd.ticket = 0;";
                if (!$mysqli->query($sqlInsertTickets)) {
                    error_log($mysqli->error);
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            }
        } elseif ($request->getAttribute("op") === "AcentarC") {
            if ($clienteVO->getTipodepago() === TiposCliente::CREDITO || $clienteVO->getTipodepago() === TiposCliente::PREPAGO) {

                $Msj = "Se han cargado los movimientos de la factura " . $fcVO->getFolio();

                $sqlUpdateCxcLogic = "UPDATE cxc SET cliente = -cliente, factura = -factura 
                                      WHERE factura = '" . $fcVO->getId() . "' AND cliente =  " . $fcVO->getCliente() . " AND tm = 'C' AND referencia > 0;";
                if (!$mysqli->query($sqlUpdateCxcLogic)) {
                    error_log($mysqli->error);
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }

                $sqlInsertTickets = "INSERT INTO cxc (cliente,referencia,fecha,hora,tm,concepto,cantidad,importe,corte,producto,factura)
                                    SELECT fc.cliente,fcd.ticket referencia,DATE(rm.fin_venta) fecha,TIME(rm.fin_venta) hora,'C' tm,IF(fcd.producto <= 4,'Venta de combustible',vtaditivos.descripcion) concepto,fcd.cantidad,
                                    fcd.importe,IF(fcd.producto <= 4,rm.corte,vtaditivos.corte) corte,IF(fcd.producto <= 4,rm.producto,'A') producto,fc.id factura 
                                    FROM fc,fcd
                                    LEFT JOIN rm ON fcd.ticket = rm.id 
                                    LEFT JOIN vtaditivos ON fcd.ticket = vtaditivos.id
                                    WHERE fc.id = fcd.id
                                    AND fcd.id = '" . $fcVO->getId() . "';";
                if (!$mysqli->query($sqlInsertTickets)) {
                    error_log($mysqli->error);
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en productos: " . $ex);
    } finally {
        header("Location: $Return");
    }
}