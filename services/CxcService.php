<?php

#Librerias
include_once ('data/CxcDAO.php');
include_once ('data/IslaDAO.php');
include_once ('data/ClientesDAO.php');
include_once ('data/PagoDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "editacxc.php?";

$cxcDAO = new CxcDAO();
$islaDAO = new IslaDAO();
$ciaDAO = new CiaDAO();
$clientesDAO = new ClientesDAO();
$pagoDAO = new PagoDAO();

$ciaVO = $ciaDAO->retrieve(1);

if ($request->hasAttribute("Boton")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;


    $cxcVO = new CxcVO();

    $islaVO = $islaDAO->retrieve(1, "isla");
    $cxcVO->setId($sanitize->sanitizeInt("busca"));

    $SCliente = explode("|", strpos($sanitize->sanitizeString("ClienteS"), "Array") ? "" : $sanitize->sanitizeString("ClienteS"));
    $Cliente = (int) trim($SCliente[0]);
    $cxcVO->setCliente($Cliente);

    $cxcVO->setPlacas($sanitize->sanitizeString("Placas"));
    $cxcVO->setReferencia($sanitize->sanitizeString("Referencia"));
    $cxcVO->setFecha($sanitize->sanitizeString("Fecha"));
    $cxcVO->setHora(date("H:i:s"));
    $cxcVO->setTm($sanitize->sanitizeString("Tm"));
    $cxcVO->setConcepto($sanitize->sanitizeString("Concepto"));
    $cxcVO->setCantidad(1);
    $cxcVO->setImporte($sanitize->sanitizeString("Importe"));
    $cxcVO->setRecibo(0);
    $cxcVO->setCorte($islaVO->getCorte());
    $cxcVO->setFactura($sanitize->sanitizeInt("Factura"));
    $cxcVO->setProducto("-");
    $cxcVO->setRubro("-----");

    try {
        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            if (($id = $cxcDAO->create($cxcVO)) > 0) {
                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "AGREGA MIVIMIENTO CXC, REG.:" . $id);
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
            if ($cxcDAO->update($cxcVO)) {
                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "MODIFICA MIVIMIENTO CXC, REG.:" . $id);
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === "Enviar a historico") {
            $Return = "cxc.php?";
            BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "ENVIA MIVIMIENTOS A HISTORICO CXC, CLIENTE.:" . $Cliente);
            $insertCxch = " INSERT INTO cxch (cliente,placas,referencia,fecha,hora,tm,concepto,cantidad,importe,recibo,corte,producto,rubro,factura)
                            SELECT cxc.cliente, IFNULL(cxc.placas, '') placas, cxc.referencia, cxc.fecha, cxc.hora, cxc.tm, cxc.concepto, 
                            cxc.cantidad, cxc.importe, cxc.recibo, cxc.corte, cxc.producto, cxc.rubro, cxc.factura 
                            FROM cxc, (
                                SELECT factura, ROUND(SUM( IF( tm =  'C', importe , - importe ) ),2) importe
                                FROM cxc
                                WHERE cliente = $Cliente AND factura IS NOT NULL
                                GROUP BY factura
                                HAVING SUM( IF( tm =  'C', importe , - importe ) ) <= 0.01
                                ORDER BY factura
                            ) sub
                            WHERE cxc.factura = sub.factura;";

            $updateCxc = "UPDATE cxc,(
                            SELECT factura, ROUND(SUM( IF( tm =  'C', importe , - importe ) ),2) importe
                            FROM cxc
                            WHERE cliente = $Cliente AND factura IS NOT NULL
                            GROUP BY factura
                            HAVING SUM( IF( tm =  'C', importe , - importe ) ) <= 0.01
                            ORDER BY factura
                        ) sub 
                        SET cxc.cliente = -cxc.cliente,cxc.factura = -cxc.factura  
                        WHERE cxc.cliente = $Cliente AND  cxc.factura = sub.factura";

            if (($mysqli->query($insertCxch)) && ($mysqli->query($updateCxc))) {
                error_log("Last query, affected_rows: " . $mysqli->affected_rows);
                $Msj = utils\Messages::MESSAGE_DEFAULT;
            } else {
                 error_log($mysqli->error);
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === "Determinar saldo") {

            $FechaI = $sanitize->sanitizeString("FechaI");
            $FechaF = $sanitize->sanitizeString("FechaF");
            $Fecha = $sanitize->sanitizeString("Fecha");

            $Return = "cxc.php?";
            
            if ($ciaVO->getMaster() === $sanitize->sanitizeString("Password")) {
                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "DETERMINA SALDO AL $Fecha, CLIENTE.:" . $Cliente);
                $copiarCxcToCxch = "INSERT INTO cxch (cliente,placas,referencia,fecha,hora,tm,concepto,cantidad,importe,recibo,corte,producto,rubro,factura)
                                    SELECT cxc.cliente, IFNULL(cxc.placas, '') placas, cxc.referencia, cxc.fecha, cxc.hora, cxc.tm, cxc.concepto, 
                                    cxc.cantidad, cxc.importe, cxc.recibo, cxc.corte, cxc.producto, cxc.rubro, cxc.factura  
                                    FROM cxc WHERE cliente = $Cliente AND fecha <= DATE('$Fecha');";

                if (($mysqli->query($copiarCxcToCxch))) {

                    $insertCxc = "INSERT INTO cxc (cliente, placas, referencia, fecha, hora, tm, concepto, cantidad, importe, recibo, corte, producto, rubro) 
                                SELECT cliente, '' placas, DATE_FORMAT('$Fecha','%Y%m%d') referencia, '$Fecha' fecha,CURRENT_TIME() hora, 
                                IF(SUM(importe) > 0,'C','H') tm,'SALDO AL $Fecha' concepto,1 cantidad,ROUND(SUM(importe),2) importe,0 recibo, 0 corte,'-' producto, '-----' rubro
                                FROM 
                                (
                                SELECT cliente,tm,SUM(IF(tm = 'C',importe,-importe)) importe 
                                FROM cxc 
                                WHERE cliente = $Cliente AND fecha <= DATE('$Fecha')
                                GROUP BY tm
                                ) AS SUB;";

                    $updateCxc = "UPDATE cxc SET cliente = -cliente, referencia = -referencia, factura = -factura 
                                  WHERE cliente = $Cliente AND fecha <= DATE('$Fecha') AND referencia <> DATE_FORMAT(CURRENT_DATE(),'%Y%m%d');";

                    if (($mysqli->query($insertCxc)) && ($mysqli->query($updateCxc))) {
                        error_log("Last query, affected_rows: " . $mysqli->affected_rows);
                        $Msj = utils\Messages::MESSAGE_DEFAULT;
                    } else {
                        error_log($mysqli->error);
                        $Msj = utils\Messages::RESPONSE_ERROR;
                    }
                } else {
                    error_log($mysqli->error);
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            } else {
                $Msj = utils\Messages::RESPONSE_PASSWORD_INCORRECT;
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_NO_OPERATION_VALID) {
            $Return = null;
            if ($request->hasAttribute("Abono") && $sanitize->sanitizeFloat("Abono") > 0) {
                $Cliente = $sanitize->sanitizeInt("Cliente");
                $Abono = $sanitize->sanitizeFloat("Abono");

                $clienteVO = $clientesDAO->retrieve($Cliente);

                if ($clienteVO->getId() > 0 && ($clienteVO->getTipodepago() === TiposCliente::CREDITO || $clienteVO->getTipodepago() === TiposCliente::PREPAGO || $clienteVO->getTipodepago() === TiposCliente::TARJETA)) {

                    $pagoVO = new PagoVO();
                    $pagoVO->setCliente($clienteVO->getId());
                    $pagoVO->setFecha(date("Y-m-d H:i:s"));
                    $Deposito = date("Y-m-d") . " " . "12:00:00";
                    $pagoVO->setFecha_deposito($Deposito);
                    $pagoVO->setFechar(date("Y-m-d H:i:s"));
                    $pagoVO->setConcepto("ABONO A CUENTA DE CONSUMOS");
                    $pagoVO->setImporte($Abono);
                    $pagoVO->setAplicado(0);
                    $pagoVO->setReferencia("ABONO A CUENTA");
                    $pagoVO->setBanco(1);
                    $pagoVO->setFormapago("01");
                    $pagoVO->setNumoperacion(0);
                    $pagoVO->setUsr($usuarioSesion->getNombre());

                    if (($id = $pagoDAO->create($pagoVO)) > 0) {

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

                        if (($idCxc = $cxcDAO->create($cxcVO)) > 0) {
                            BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "AGREGA MIVIMIENTO CXC, REG.:" . $idCxc);
                            $pagoVO = $pagoDAO->retrieve($id);
                            $pagoVO->setStatus(StatusPago::CERRADO);
                            $pagoVO->setStatus_pago(StatusPagoPrepago::CON_NOTA_CREDITO);

                            if ($pagoDAO->update($pagoVO)) {
                                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                            } else {
                                $Msj = utils\Messages::RESPONSE_ERROR;
                            }
                        } else {
                            $Msj = utils\Messages::RESPONSE_ERROR;
                        }
                    } else {
                        error_log($mysqli->error);
                        $Msj = utils\Messages::RESPONSE_ERROR;
                    }
                }
            }
        }
    } catch (Exception $ex) {
        error_log("Error en cxc: " . $ex);
    } finally {
        if ($Return != null) {
            $Return .= "&Msj=" . urlencode($Msj);
            header("Location: $Return");
        }
    }
}


if ($request->hasAttribute("op")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $cId = $sanitize->sanitizeInt("cId");
    $Return = "editacxc.php?";

    try {
        if ($request->getAttribute("op") === utils\Messages::OP_DELETE) {
            if ($ciaVO->getMaster() === $sanitize->sanitizeString("Password")) {
                $cxcVO = $cxcDAO->retrieve($cId);
                if ($cxcVO->getFactura() > 0) {
                    $Msj = "No se puede eliminar el movimiento ya que se tiene una factura asociada";
                } else {
                    $cxcVO->setCliente(-$cxcVO->getCliente());
                    $cxcVO->setReferencia(-$cxcVO->getReferencia());

                    if ($cxcDAO->update($cxcVO)) {
                        $Msj = utils\Messages::RESPONSE_VALID_CANCEL;
                        BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "CANCELA MOVIMIENTO CXC [" . $cId . "]");
                    } else {
                        $Msj = utils\Messages::RESPONSE_ERROR;
                    }
                }
            } else {
                $Msj = "Clave es invalida, intente de nuevo";
            }
        } else {
            $Msj = utils\Messages::RESPONSE_PASSWORD_INCORRECT;
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en pagos: " . $ex);
    } finally {
        header("Location: $Return");
    }
}
