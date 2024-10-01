<?php

#Librerias
include_once ('data/ClientesDAO.php');
include_once ('data/PagoDAO.php');
include_once ('data/CxcDAO.php');
include_once ('data/IslaDAO.php');
include_once ('data/PagoseDAO.php');
include_once ('data/CiaDAO.php');
include_once ('data/FcDAO.php');
include_once ('data/V_CorporativoDAO.php');
include_once ('data/CancelacionDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
//error_log(print_r($request, TRUE));

$pagoDAO = new PagoDAO();
$clientesDAO = new ClientesDAO();
$islaDAO = new IslaDAO();
$cxcDAO = new CxcDAO();
$ciaDAO = new CiaDAO();
$variablesCorpDAO = new V_CorporativoDAO();

if ($request->hasAttribute("id")) {
    $returnLink = urlencode("pagose33.php?");
    $backLink = urlencode("pagos.php?criteria=ini");
    header("Location: clientes.php?criteria=ini&Facturar=2&backLink=$backLink&returnLink=$returnLink");
}

$nameVariableSession = "CatalogoPagos"; /* pagos */

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {

    try {
        //$mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

        $Msj = utils\Messages::MESSAGE_NO_OPERATION;
        $Return = "pagos.php?";

        $pagoVO = new PagoVO();
        $pagoVO->setCliente($sanitize->sanitizeInt("Cliente"));
        if (is_numeric($sanitize->sanitizeInt("busca"))) {
            $pagoVO = $pagoDAO->retrieve($sanitize->sanitizeInt("busca"));
        }

        $clienteVO = $clientesDAO->retrieve($pagoVO->getCliente());

        $Deposito = $sanitize->sanitizeString("FechaDeposito") . " " . $sanitize->sanitizeString("HoraDeposito");
        $pagoVO->setFecha_deposito($Deposito);
        $pagoVO->setBanco($sanitize->sanitizeInt("Banco"));
        $pagoVO->setConcepto($sanitize->sanitizeString("Concepto"));
        $pagoVO->setNumoperacion($sanitize->sanitizeString("NumOper"));
        if ($pagoVO->getStatus_pago() < 2) {
            $pagoVO->setImporte($sanitize->sanitizeFloat("Importe"));
        }
        if ($request->hasAttribute("Fecha_Ini") && $request->hasAttribute("Fecha_Fin")) {
            $pagoVO->setFecha_ini($request->getAttribute("Fecha_Ini"));
            $pagoVO->setFecha_fin($request->getAttribute("Fecha_Fin"));
            $pagoVO->setMontonoreconocido($sanitize->sanitizeString("Montonoreconocido"));
        } else {
            $pagoVO->setFecha_ini(date("Y-m-d"));
            $pagoVO->setFecha_fin(date("Y-m-d"));
            $pagoVO->setMontonoreconocido(0);
        }
        $pagoVO->setFormapago($sanitize->sanitizeString("Formapago"));
        $pagoVO->setTiporelacion($sanitize->sanitizeString("tiporelacion"));
        $pagoVO->setRelacioncfdi($sanitize->sanitizeString("Relacioncfdi"));

        if ($clienteVO->getTipodepago() === TiposCliente::MONEDERO) {
            $pagoVO->setUuid($sanitize->sanitizeString("UUID"));
        }

        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {

            $pagoVO->setReferencia("PAGO A CUENTA");
            $pagoVO->setStatus(StatusPago::ABIERTO);
            $pagoVO->setUsr($usuarioSesion->getNombre());
            if ($clienteVO->getTipodepago() === "Prepago") {
                $pagoVO->setUsocfdi($sanitize->sanitizeString("UsoCfdi"));
            } else {
                $pagoVO->setUsocfdi("P01");
            }
            if (($id = $pagoDAO->create($pagoVO)) > 0) {
                if ($clienteVO->getTipodepago() === "Credito") {
                    $Sql2 = "SELECT valor FROM variables_corporativo where llave like '%series_anticipos_credito%';";
                } else {
                    $Sql2 = "SELECT valor FROM variables_corporativo where llave like '%series_anticipos%';";
                }
                $Srie = utils\IConnection::execSql($Sql2);
                if ($Srie["valor"] == "" || $Srie["valor"] == null) {
                    $Sql2 = "SELECT valor FROM variables_corporativo where llave like '%series_anticipos%';";
                    $Srie = utils\IConnection::execSql($Sql2);
                }
                error_log("SERIEEEEEE" . $Srie["valor"]);
                $Upd = "UPDATE pagos SET serie = '" . $Srie["valor"] . "' WHERE id=" . $id;
                utils\IConnection::execSql($Upd);

                $Return = "pagosd33.php?criteria=ini&cVarVal=" . $id;

                $islaVO = $islaDAO->retrieve(1, "isla");

                $cxcVO = new CxcVO();
                $cxcVO->setCliente($pagoVO->getCliente());
                $cxcVO->setPlacas($clienteVO->getTipodepago());
                $cxcVO->setReferencia($id);
                $cxcVO->setFecha($pagoVO->getFecha_deposito());
                $cxcVO->setHora(date("H:i:s"));
                $cxcVO->setTm("H");
                $cxcVO->setConcepto($pagoVO->getReferencia() . " Recibo " . $id);
                $cxcVO->setCantidad(1);
                $cxcVO->setImporte($pagoVO->getImporte());
                $cxcVO->setRecibo($id);
                $cxcVO->setCorte($islaVO->getCorte());
                $cxcVO->setRubro($clienteVO->getTipodepago());
                $cxcVO->setProducto("-");
                //error_log(print_r($cxcVO, true));

                $variablesCorpVO = $variablesCorpDAO->retrieve(ListaLlaves::PAGOS_TICKETS);

                if ($clienteVO->getTipodepago() === TiposCliente::TARJETA) {

                    if ($cxcDAO->create($cxcVO) > 0) {
                        $pagoVO = $pagoDAO->retrieve($id);
                        if ($variablesCorpVO->getValor() == 0) {
                            $pagoVO->setStatus(StatusPago::CERRADO);
                            $pagoVO->setStatus_pago(StatusPagoPrepago::CON_NOTA_CREDITO);
                            $Return = "pagos.php?";
                        }

                        if ($pagoDAO->update($pagoVO)) {
                            $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                        } else {
                            $Msj = utils\Messages::RESPONSE_ERROR;
                        }
                    } else {
                        $Msj = utils\Messages::RESPONSE_ERROR;
                    }
                } elseif ($clienteVO->getTipodepago() === TiposCliente::VALES) {

                    if ($cxcDAO->create($cxcVO) > 0) {
                        $pagoVO = $pagoDAO->retrieve($id);
                        $pagoVO->setStatus(StatusPago::CERRADO);
                        $pagoVO->setStatus_pago(StatusPagoPrepago::CON_NOTA_CREDITO);

                        if ($pagoDAO->update($pagoVO)) {
                            $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                        } else {
                            $Msj = utils\Messages::RESPONSE_ERROR;
                        }
                        $Return = "pagos.php?";
                    } else {
                        $Msj = utils\Messages::RESPONSE_ERROR;
                    }
                } elseif ($clienteVO->getTipodepago() === TiposCliente::MONEDERO) {
                    if ($cxcDAO->create($cxcVO) > 0) {
                        $pagoVO = $pagoDAO->retrieve($id);
                        //$pagoVO->setStatus(StatusPago::CERRADO);

                        if ($pagoDAO->update($pagoVO)) {
                            $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                        } else {
                            $Msj = utils\Messages::RESPONSE_ERROR;
                        }
                        if ($variablesCorpVO->getValor() == 0) {
                            $Return = "facturase.php?Boton=Agregar&Cliente=" . $clienteVO->getId() . "&Pago=" . $id;
                        }
                    } else {
                        $Msj = utils\Messages::RESPONSE_ERROR;
                    }
                } else {
                    $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                }
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
            if ($clienteVO->getTipodepago() === "Prepago") {
                $AddMessaje = "De uso : " . $pagoVO->getUsocfdi() . " a uso" . $sanitize->sanitizeString("UsoCfdi");
                $pagoVO->setUsocfdi($sanitize->sanitizeString("UsoCfdi"));
            } else if ($clienteVO->getTipodepago() === "Tarjeta") {
                $AddMessaje = "De importe : " . $pagoVO->getImporte() . " aplicado" . $pagoVO->getAplicado() . " A i y a " . $sanitize->sanitizeString("Importe");
                $pagoVO->setImporte($sanitize->sanitizeString("Importe"));
                $pagoVO->setAplicado($sanitize->sanitizeString("Importe"));
            }
            if ($pagoDAO->update($pagoVO)) {
                if ($clienteVO->getTipodepago() === TiposCliente::TARJETA || $clienteVO->getTipodepago() === TiposCliente::MONEDERO) {
                    $selectAbono = "SELECT * FROM cxc WHERE TRUE AND cxc.tm = 'H' AND cxc.recibo = " . $pagoVO->getId();
                    $cxc = utils\IConnection::execSql($selectAbono);
                    $cxcVO = $cxcDAO->retrieve($cxc["id"]);
                    $cxcVO->setImporte($pagoVO->getImporte());
                    $cxcDAO->update($cxcVO);
                }
                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "ACTUALIZACION DE PAGO " . $pagoVO->getId() . " = " . $AddMessaje);
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_CANCEL) {

            $ciaVO = $ciaDAO->retrieve(1);

            if ($ciaVO->getMaster() === $sanitize->sanitizeString("Password")) {
                $pagoVO = $pagoDAO->retrieve($sanitize->sanitizeInt("busca"));

                $selectFolios = "SELECT factura FROM pagose WHERE id = " . $pagoVO->getId() . "";

                /* Inicia proceso con cxc */
                $sqlRetriveHistoric = "INSERT INTO cxc (cliente,placas,referencia,fecha,hora,tm,concepto,cantidad,importe,recibo,corte,producto,rubro,factura)
                                        SELECT cliente,placas,referencia,fecha,hora,tm,concepto,cantidad,importe,recibo,corte,producto,rubro,factura 
                                        FROM cxch WHERE factura IN ($selectFolios);";
                //error_log($sqlRetriveHistoric);
                if (!$mysqli->query($sqlRetriveHistoric)) {
                    error_log($mysqli->error);
                }

                if ($mysqli->affected_rows > 0) {
                    $folios = $mysqli->query($selectFolios);
                    while ($rg = $folios->fetch_array()) {
                        $sqlHistoric = "DELETE FROM cxch WHERE factura IN ($rg[factura]);";
                        error_log($sqlHistoric);
                        if (!$mysqli->query($sqlHistoric)) {
                            error_log($mysqli->error);
                        }
                    }
                }

                //$sqlCxc = "DELETE FROM cxc WHERE recibo='$busca' AND tm='H'";
                $sqlCxc = "UPDATE cxc SET cliente = -cliente, recibo = -recibo, factura = -factura, corte = -corte, referencia= -referencia "
                        . "WHERE recibo = " . $pagoVO->getId() . " AND tm = 'H' AND cliente > 0 "
                        . "AND recibo > 0; ";
                //error_log($sqlCxc);
                if (!$mysqli->query($sqlCxc)) {
                    error_log($mysqli->error);
                }
                /* Finaliza proceso con cxc */

                $pagoVO->setImporte(0);
                $pagoVO->setConcepto("Pago cancelado " . date("Y-m-d H:i:s"));
                $pagoVO->setStatus(StatusPago::CANCELADO);
                if ($pagoVO->getStatusCFDI() == StatusPagoCFDI::ABIERTO) {
                    $pagoVO->setStatusCFDI(StatusPagoCFDI::CANCELADO_ST);
                }
                if ($pagoDAO->update($pagoVO)) {
                    BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "CANCELACION DE PAGO " . $pagoVO->getId());
                }

                $sqlPagose = "UPDATE pagose SET factura = -factura, id = -id WHERE id = '" . $pagoVO->getId() . "' ;";
                if (!$mysqli->query($sqlPagose)) {
                    error_log($mysqli->error);
                }

                $Msj = utils\Messages::RESPONSE_VALID_CANCEL;
            } else {
                $Msj = utils\Messages::RESPONSE_PASSWORD_INCORRECT;
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_SEND_EMAIL) {
            $pagoVO = $pagoDAO->retrieve($sanitize->sanitizeInt("busca"));
            if (!empty($sanitize->sanitizeEmail("Correo"))) {
                $clienteVO = $clientesDAO->retrieve($pagoVO->getCliente());
                $Msj = enviarCorreo($pagoVO->getUuid(), $sanitize->sanitizeEmail("Correo"), $clienteVO->getCorreo2());
            } else {
                $Msj = "El correo ingresado es invalido!";
            }
        } elseif ($request->getAttribute("Boton") === "Cancelar recibo") {
            $ciaVO = $ciaDAO->retrieve(1);
            $busca = $sanitize->sanitizeInt("busca");

            if ($ciaVO->getMaster() === $sanitize->sanitizeString("Password")) {
                $UpdateMotivo = "UPDATE pagos SET motivoCan = '" . $sanitize->sanitizeString("TipoCancelacion") . "' WHERE id = " . $pagoVO->getId();
                $mysqli->query($UpdateMotivo);
                $pagoVO = $pagoDAO->retrieve($busca);
                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "CANCELACION DE PAGO TIMBRADO " . $pagoVO->getId());

                $wsdl = FACTENDPOINT;
                $client = new nusoap_client($wsdl, true);
                $client->timeout = 180;
                $client->soap_defencoding = 'UTF-8';
                $client->namespaces = array("SOAP-ENV" => "http://schemas.xmlsoap.org/soap/envelope/");
                $relacion = $pagoVO->getRelacioncfdi() == null ? "" : $pagoVO->getRelacioncfdi();
                $parm = "|" . $pagoVO->getUuid() . "|02||";
                error_log($parm);
                $params = array(
                    "rfc" => $ciaVO->getRfc(),
                    "uuid" => array($parm)
                );

                $result = $client->call("cancelacion", $params, false, '', '');

                if ($client->fault) {
                    error_log(print_r($result, TRUE));
                    $Msj = utils\Messages::RESPONSE_ERROR;
                } else {

                    if ($result['return']['canceled'] == "true") {
                        $UpdateMotivo = "UPDATE pagos SET status_pago=2 WHERE id = " . $pagoVO->getId();
                        $mysqli->query($UpdateMotivo);
                        $Msj = utils\Messages::RESPONSE_VALID_CANCEL;
                    } else {
                        $cError = utf8_encode($result["return"]["error"]);
                        $Msj = utils\Messages::RESPONSE_ERROR . " " . $cError;
                    }
                }
            } else {
                $Msj = utils\Messages::RESPONSE_PASSWORD_INCORRECT;
            }
        } elseif ($request->getAttribute("Boton") === "Guardar Motivo") {
            $CancelacionDAO = new CancelacionDAO();
            $CancelacionVO = new CancelacionVO();
            $CancelacionVO->setTabla("pagos");
            $CancelacionVO->setId_origen(utils\HTTPUtils::getSessionValue("busca"));
            $CancelacionVO->setDescripcion_evento($request->getAttribute("MotivoCancelacion"));
            $CancelacionDAO->create($CancelacionVO);
            $Msj = utils\Messages::MESSAGE_DEFAULT;
            $Return = "cantimbrepago.php?busca=" . utils\HTTPUtils::getSessionValue("busca");
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en pagos: " . $ex);
    } finally {
        header("Location: $Return");
    }
}


