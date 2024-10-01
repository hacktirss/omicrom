<?php

#Librerias
include_once ('data/CtDAO.php');
include_once ('data/IslaDAO.php');
include_once ('data/RmDAO.php');
include_once ('data/FcDAO.php');
include_once ('data/ClientesDAO.php');
include_once ('data/CxcDAO.php');
include_once ('data/ProductoDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "cambiotur.php?";

$rmDAO = new RmDAO();
$clienteDAO = new ClientesDAO();
$ctDAO = new CtDAO();
$cxcDAO = new CxcDAO();

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    if ($request->hasAttribute("Cliente")) {
        $Cliente = $sanitize->sanitizeInt("Cliente");
    }
    $Ticket = $sanitize->sanitizeInt("Ticket");
    $Corte = $sanitize->sanitizeInt("Corte");
    error_log("Agregando ticket: " . $cId);
    try {
        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            $Return = "impaceites.php?";

            if ($Cliente > 0) {
                $pagoReal = $sanitize->sanitizeFloat("Pagoreal");
                $rmVO = $rmDAO->retrieve($Ticket);
                $pesos = round($rmVO->getPesos(), 2);
                $clienteVO = $clienteDAO->retrieve($Cliente);
                $ctVO = $ctDAO->retrieve($Corte);

                error_log("pagoReal : $pagoReal" . "<=" . $pesos);
                if (is_numeric($pagoReal) && $pagoReal > 0 && ($pagoReal <= $pesos || $clienteVO->getTipodepago() === TiposCliente::TARJETA || $clienteVO->getTipodepago() === TiposCliente::VALES || $clienteVO->getTipodepago() === TiposCliente::MONEDERO)) {

                    if ($ctVO->getStatusctv() === StatusCorte::ABIERTO) {

                        $Concepto = "Venta de " . $clienteVO->getTipodepago();
                        $Placas = "";

                        $rmVO->setCliente($Cliente);
                        $rmVO->setPlacas($Placas);
                        $rmVO->setPagoreal($pagoReal);
                        $rmVO->setTipodepago($clienteVO->getTipodepago());
                        $rmVO->setEnviado(0);

                        if ($rmDAO->update($rmVO)) {
                            $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                            BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "CAMBIA VENTA [" . $Ticket . "] A CLIENTE: $Cliente [" . strtoupper($clienteVO->getTipodepago()) . "]");
                            $updateCxc = "UPDATE cxc SET cliente = -cliente, referencia = -referencia, corte = -corte "
                                    . "WHERE referencia='$Ticket' AND tm='C' AND producto = '" . $rmVO->getProducto() . "' LIMIT 1";
                            if (!( $mysqli->query($updateCxc))) {
                                error_log($mysqli->error);
                            }

                            $cxcVO = new CxcVO();
                            $cxcVO->setCliente($clienteVO->getId());
                            $cxcVO->setPlacas($Placas);
                            $cxcVO->setReferencia($Ticket);
                            $cxcVO->setFecha(date("Y-m-d", strtotime($rmVO->getFin_venta())));
                            $cxcVO->setHora(date("H:i:s", strtotime($rmVO->getFin_venta())));
                            $cxcVO->setTm("C");
                            $cxcVO->setConcepto($Concepto);
                            $cxcVO->setCantidad($rmVO->getVolumen());
                            $cxcVO->setImporte($pagoReal);
                            $cxcVO->setRecibo(0);
                            $cxcVO->setCorte($Corte);
                            $cxcVO->setRubro("-----");
                            $cxcVO->setProducto($rmVO->getProducto());

                            if (($cxcDAO->create($cxcVO)) < 0) {
                                $Msj = utils\Messages::RESPONSE_ERROR;
                            }
                        } else {
                            $Msj = utils\Messages::RESPONSE_ERROR;
                        }
                    } else {
                        $Msj = "Lo siento el corte actual ha sido cerrado, no es posible hacer ningun movimiento";
                    }
                } else {
                    $Msj = "El importe ingresado es incorrecto o es mayor que el importe del ticket";
                }
            } else {
                $Msj = "El cliente ingresado es invalido";
            }
        }
        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error: " . $ex);
    } finally {
        header("Location: $Return");
    }
}

if ($request->hasAttribute("op")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $cId = $sanitize->sanitizeInt("cId");
    error_log("Liberando ticket: " . $cId);
    try {
        if ($request->getAttribute("op") === utils\Messages::OP_DELETE) {
            $Return = "impaceites.php?";
            $rmVO = $rmDAO->retrieve($cId);
            $clienteVO = $clienteDAO->retrieve($rmVO->getCliente());

            $selectFcd = "SELECT fc.folio FROM fcd,fc WHERE fcd.id = fc.id AND fcd.ticket = $cId AND producto < 5;";
            $fcd = $mysqli->query($selectFcd)->fetch_array();
            $flagTarjetas = $sanitize->sanitizeInt("Tarjetas");

            if ($rmVO->getUuid() !== FcDAO::SIN_TIMBRAR && ($clienteVO->getTipodepago() === TiposCliente::CREDITO || $clienteVO->getTipodepago() === TiposCliente::PREPAGO)) {
                $Msj = "Error! el ticket [" . $cId . "] no se puede eliminar ya que tiene una factura asociada";
            } elseif (!empty($fcd["folio"]) && $fcd["folio"] > 0 && $flagTarjetas = 0) {
                $Msj = "Error! el ticket [" . $cId . "] no se puede eliminar ya que esta asociado a la factura " . $fcd["folio"];
            } else {
                $rmVO->setCliente(0);
                $rmVO->setPagoreal($rmVO->getPesos());
                $rmVO->setTipodepago(TiposCliente::CONTADO);
                $rmVO->setEnviado(0);
                $rmVO->setCodigo(0);
                if ($rmDAO->update($rmVO)) {
                    $Msj = utils\Messages::RESPONSE_VALID_CANCEL;
                    $updateCxc = "UPDATE cxc SET cliente = -cliente, referencia=-referencia, corte = -corte "
                            . "WHERE referencia='$cId' AND tm='C' AND producto = '" . $rmVO->getProducto() . "' LIMIT 1;";
                    $mysqli->query($updateCxc);
                    BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "CAMBIA VENTA [" . $cId . "] A CONTADO");
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            }
        }
        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error: " . $ex);
    } finally {
        header("Location: $Return");
    }
}