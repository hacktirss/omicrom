<?php

#Librerias
include_once ('data/FcDAO.php');
include_once ('data/ClientesDAO.php');
include_once ('data/NcDAO.php');
include_once ('data/PagoDAO.php');
include_once ('data/CancelacionDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "notascre.php?";

if ($request->hasAttribute("id")) {
    $returnLink = urlencode("notascree.php?Boton=Agregar&");
    $backLink = urlencode("notascre.php?criteria=ini");
    header("Location: facturas.php?criteria=ini&backLink=$backLink&returnLink=$returnLink");
}

$ciaDAO = new CiaDAO();
$clientesDAO = new ClientesDAO();
$ncDAO = new NcDAO();
$pagoDAO = new PagoDAO();

$ciaVO = $ciaDAO->retrieve(1);

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    $ncVO = new NcVO();
    if (is_numeric($sanitize->sanitizeInt("busca"))) {
        $ncVO = $ncDAO->retrieve($sanitize->sanitizeInt("busca"));
    }
    //error_log(print_r($ncVO, TRUE));
    try {
        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {

            $Factura = $sanitize->sanitizeInt("Factura");
            $sql = "SELECT fc.*, "
                    . "IF( EXTRACTVALUE( cfdi_xml,  '/cfdi:Comprobante/@FormaPago' ) <>  '', "
                    . "EXTRACTVALUE( cfdi_xml,  '/cfdi:Comprobante/@FormaPago' ) , "
                    . "EXTRACTVALUE( cfdi_xml,  '/cfdi:Comprobante/@metodoDePago' ) ) formadepago "
                    . "FROM fc JOIN facturas ON fc.id = facturas.id_fc_fk AND fc.uuid = facturas.uuid "
                    . "WHERE id = '$Factura'";

            $fc = $mysqli->query($sql)->fetch_array();

            $clienteVO = $clientesDAO->retrieve($fc["cliente"]);

            if ($clienteVO->getFacturacion() === "1") {

                $insertNc = "INSERT INTO nc (cliente, fecha, status, factura, formadepago, relacioncfdi, usr) "
                        . "VALUES ('$fc[cliente]', NOW(), " . StatusNotaCredito::ABIERTO . ", '$fc[id]', '$fc[formadepago]', '$fc[id]', '" . $usuarioSesion->getUsername() . "')";

                if ($mysqli->query($insertNc)) {
                    $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                    $id = $mysqli->insert_id;
                    $Return = "notascred.php?criteria=ini&cVarVal=" . $id;
                } else {
                    error_log($mysqli->error);
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            } else {
                $Msj = "Error: el cliente [" . $clienteVO->getNombre() . "] no tiene permisos para facturar.";
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_SEND_EMAIL) {
            if (!empty($sanitize->sanitizeEmail("Correo"))) {
                $clienteVO = $clientesDAO->retrieve($ncVO->getCliente());
                $Msj = enviarCorreo($ncVO->getUuid(), $sanitize->sanitizeEmail("Correo"), $clienteVO->getCorreo2());
            } else {
                $Msj = "El correo ingresado es invalido!";
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_CANCEL) {
            $busca = $sanitize->sanitizeInt("busca");

            if ($ciaVO->getMaster() === $sanitize->sanitizeString("Password")) {
                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "CANCELACION DE NC " . $ncVO->getId());
                $clienteVO = $clientesDAO->retrieve($ncVO->getCliente());

                if (empty($ncVO->getUuid()) || $ncVO->getUuid() === NcDAO::SIN_TIMBRAR) {
                    $ncVO->setCantidad(0);
                    $ncVO->setImporte(0);
                    $ncVO->setIva(0);
                    $ncVO->setIeps(0);
                    $ncVO->setTotal(0);
                    $ncVO->setStatus(StatusNotaCredito::CANCELADO);

                    if ($ncDAO->update($ncVO)) {
                        $Msj = utils\Messages::RESPONSE_VALID_CANCEL;
                    } else {
                        $Msj = utils\Messages::RESPONSE_ERROR;
                    }
                } else {

                    $udpateCxc = "UPDATE cxc SET referencia = -referencia, cliente = -cliente 
                            WHERE cxc.tm = 'H' 
                            AND cxc.placas = 'Nota_cre' 
                            AND cxc.referencia = '$busca' 
                            AND cxc.cliente = '" . $ncVO->getCliente() . "' LIMIT 1";

                    if (!($mysqli->query($udpateCxc))) {
                        error_log($mysqli->error);
                    }

                    if ($clienteVO->getTipodepago() === TiposCliente::PREPAGO) {
                        $pagoVO = $pagoDAO->retrieve($ncVO->getFactura());
                        $pagoVO->setStatus_pago(StatusPagoPrepago::CON_FACTURA_CONSUMOS);
                        if (!($pagoDAO->update($pagoVO))) {
                            error_log("Ocurrio un error al actualizar el pago " . $pagoVO->getId());
                        } else {
                            $udpateCxc = "UPDATE cxc SET factura = null
                                        WHERE cxc.tm = 'H' 
                                        AND cxc.recibo = '" . $ncVO->getFactura() . "' 
                                        AND cxc.factura = '" . $ncVO->getRelacioncfdi() . "' 
                                        AND cxc.cliente = '" . $ncVO->getCliente() . "' LIMIT 1";

                            if (!($mysqli->query($udpateCxc))) {
                                error_log($mysqli->error);
                            }
                        }
                    }

                    $udpateNc = "UPDATE nc SET motivoCan = '" . $sanitize->sanitizeString("TipoCancelacion") . "'
                                        WHERE id=$busca";

                    if (!($mysqli->query($udpateNc))) {
                        error_log($mysqli->error);
                    }
                    $relacion = $ncVO->getRelacioncfdi() == null ? "" : $ncVO->getRelacioncfdi();
                    $fcDAO = new FcDAO();
                    $fcVO = $fcDAO->retrieve($relacion);

                    $wsdl = 'http://localhost:9190/GeneradorCFDIsWEB/Facturador?wsdl';

                    $client = new nusoap_client($wsdl, true);
                    $client->timeout = 180;
                    $client->soap_defencoding = 'UTF-8';
                    $client->namespaces = array("SOAP-ENV" => "http://schemas.xmlsoap.org/soap/envelope/");
                    //$parm = "|" . $ncVO->getUuid() . "|" . $sanitize->sanitizeString("TipoCancelacion") . "|" . $ncVO->getRelacioncfdi() . "|";
                    $parm = "|" . $ncVO->getUuid() . "|02||";
                    $params = array(
                        "rfc" => $ciaVO->getRfc(),
                        "uuid" => [$parm]
                    );
                    error_log("Parametros:");
                    error_log(print_r($params, true));
                    $result = $client->call("cancelacion", $params, false, '', '');

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

                                $ncVO->setCantidad(0);
                                $ncVO->setImporte(0);
                                $ncVO->setIva(0);
                                $ncVO->setIeps(0);
                                $ncVO->setTotal(0);
                                $ncVO->setStatus(StatusNotaCredito::CANCELADO);
                                $ncVO->setFactura(-$ncVO->getFactura());

                                if ($ncDAO->update($ncVO)) {
                                    $Msj = utils\Messages::RESPONSE_VALID_CANCEL;
                                }
                            } else {
                                $cError = utf8_encode($result["return"]["error"]);
                                $Msj = utils\Messages::RESPONSE_ERROR . " " . $cError;
                            }
                        }
                    }
                }
            } else {
                $Msj = utils\Messages::RESPONSE_PASSWORD_INCORRECT;
            }
        } else if ($request->getAttribute("Boton") === "Guardar Motivo") {
            $CancelacionDAO = new CancelacionDAO();
            $CancelacionVO = new CancelacionVO();
            $CancelacionVO->setTabla("nc");
            $CancelacionVO->setId_origen(utils\HTTPUtils::getSessionValue("busca"));
            $CancelacionVO->setDescripcion_evento($request->getAttribute("MotivoCancelacion"));
            $CancelacionDAO->create($CancelacionVO);
            $Msj = utils\Messages::MESSAGE_DEFAULT;
            $Return = "cannotascre.php?busca=" . utils\HTTPUtils::getSessionValue("busca");
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log($ex);
    } finally {
        header("Location: $Return");
    }
}


if ($request->hasAttribute("op")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    //$bancosDAO = new BancosDAO();
    $cId = $sanitize->sanitizeInt("cId");

    try {
        if ($request->getAttribute("op") === utils\Messages::OP_DELETE) {
            
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log($ex);
    } finally {
        header("Location: $Return");
    }
}
