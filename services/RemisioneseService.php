<?php

#Librerias
include_once ('data/RmDAO.php');
include_once ('data/ClientesDAO.php');
include_once ('data/CombustiblesDAO.php');
include_once ('data/TarjetaDAO.php');
include_once ('data/CxcDAO.php');
include_once ('data/IslaDAO.php');
include_once ('data/FcDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "remisionesCP.php?";

$rmDAO = new RmDAO();
$clientesDAO = new ClientesDAO();
$comDAO = new CombustiblesDAO();
$tarjetaDAO = new TarjetaDAO();
$cxcDAO = new CxcDAO();
$islaDAO = new IslaDAO();

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    $rmVO = new RmVO();
    $rmVO->setId($sanitize->sanitizeInt("busca"));
    if (is_numeric($rmVO->getId())) {
        $rmVO = $rmDAO->retrieve($rmVO->getId());
    }
    $clienteVO = $clientesDAO->retrieve($rmVO->getCliente());

    //error_log(print_r($request, TRUE));
    try {
        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {

            $Posicion = $sanitize->sanitizeInt("Posicion");
            $Producto = $sanitize->sanitizeString("Producto");
            $Importe = $sanitize->sanitizeFloat("Importe");
            $Volumen = $sanitize->sanitizeFloat("Volumen");

            $islaVO = $islaDAO->retrieve(1, "isla");
            $comVO = $comDAO->retrieve($Producto, "clavei");

            $man_sql = "SELECT m.dispensario,m.manguera,m.dis_mang,m.isla,m.posicion,m.factor,m.enable,man.despachador 
                        FROM man_pro m,man
                        WHERE m.posicion = man.posicion AND m.posicion='$Posicion' AND m.producto = '$Producto' AND m.activo = 'Si'";

            $Man = utils\IConnection::execSql($man_sql);

            if ($Importe > 0) {
                $Volumen = round($Importe / $comVO->getPrecio(), 4);
            } elseif ($Volumen > 0) {
                $Importe = round($Volumen * $comVO->getPrecio(), 4);
            }

            $VolumenBase = 50000;

            do {
                if ($Volumen > $VolumenBase) {
                    $auxV = $VolumenBase;
                    $Volumen -= $auxV;
                } else {
                    $auxV = $Volumen;
                    $Volumen = 0;
                }

                $auxI = round($auxV * $comVO->getPrecio(), 4);

                $Importep = $auxI / ( 1 + $Man["factor"] * $Man["enable"] / 100 );
                $Volumenp = $auxV / ( 1 + $Man["factor"] * $Man["enable"] / 100 );

                $rmVO->setDispensario($Man["dispensario"]);
                $rmVO->setPosicion($Posicion);
                $rmVO->setManguera($Man["manguera"]);
                $rmVO->setDis_mang($Man["dis_mang"]);
                $rmVO->setProducto($comVO->getClavei());
                $rmVO->setPrecio($comVO->getPrecio());
                $rmVO->setInicio_venta(date("Y-m-d H:i:s"));
                $rmVO->setFin_venta(date("Y-m-d H:i:s"));
                $rmVO->setPesos($auxI);
                $rmVO->setVolumen($auxV);
                $rmVO->setPesosp($Importep);
                $rmVO->setVolumenp($Volumenp);
                $rmVO->setTurno($islaVO->getTurno());
                $rmVO->setCorte($islaVO->getCorte());
                $rmVO->setVendedor($Man["despachador"]);
                $rmVO->setIva($comVO->getIva());
                $rmVO->setIeps($comVO->getIeps());
                $rmVO->setFactor($Man["factor"]);
                //error_log(print_r($rmVO, TRUE));
                if ($rmDAO->create($rmVO) > 0) {
                    $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                    BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "VENTA MANUAL POR [" . $auxI . "]");
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            } while ($Volumen > 0);
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {

            if ($rmDAO->update($rmVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === "Cambiar tipo de despacho") {
            if (date("Y-m-d", strtotime($rmVO->getFin_venta())) == date("Y-m-d") || date("Y-m-d", strtotime($rmVO->getFin_venta())) == date("Y-m-d", strtotime(date("Y-m-d") . " -1 day")) && ($rmVO->getImporte() + 1 > $rmVO->getPesos() && $rmVO->getImporte() - 1 < $rmVO->getPesos())) {
                $rmVO->setEnviado(0);
                $rmVO->setTipo_venta($sanitize->sanitizeString("Tipo_venta"));
                if ($rmDAO->update($rmVO)) {
                    $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                    BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "CAMBIA VENTA " . $rmVO->getId() . " A [" . $rmVO->getTipo_venta() . "]");
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            } else {
                $Msj = "Lo siento tu transaccion ha sido procesada, por lo tanto no fue posible realizar el cambio";
            }
        } elseif ($request->getAttribute("Boton") === "Guardar") {
            $clienteVO_ = $clientesDAO->retrieve($sanitize->sanitizeInt("Cliente"));
            $Codigo = explode("|", $sanitize->sanitizeString("Codigo"));
            $BuscaUnidadB = "SELECT *,count(1) no FROM unidades WHERE cliente = " . $clienteVO_->getId()
                    . " AND periodo='B' AND impreso='" . trim($Codigo[1]) . "'";
            $rsUB = utils\IConnection::execSql($BuscaUnidadB);
            /* Cuando el periodo de la unidad es Balance 
              agregamos bitacora el descuento tabla unidades log */
            if ($rsUB["periodo"] === "B") {
                $Residuo = $rsUB["importe"] - $rmVO->getImporte();
                $Insrt = "INSERT INTO unidades_log (noPago,importeAnt,importe,importeDelPago,idUnidad,usr) 
                                VALUES ('" . $rmVO->getId() . "',"
                        . "'" . $rsUB["importe"] . "',"
                        . "'" . $Residuo . "',"
                        . "'" . -$rmVO->getImporte() . "',"
                        . "'" . $rsUB["id"] . "',"
                        . "'" . $usuarioSesion->getNombre() . "');";
                utils\IConnection::execSql($Insrt);
            }
            if ($rsUB["no"] == 0 || $rsUB["no"] == null) {
                if ($clienteVO_->getActivo() === "Si") {
                    if (!empty($sanitize->sanitizeString("Codigo"))) {
                        if (empty($rmVO->getCodigo()) || $clienteVO->getTipodepago() === TiposCliente::CONTADO || $clienteVO->getTipodepago() === TiposCliente::PUNTOS) {
                            if (($clienteVO->getTipodepago() === TiposCliente::CONTADO || $clienteVO->getTipodepago() === TiposCliente::PUNTOS) && $rmVO->getUuid() === "-----") {

                                $Codigo = explode("|", $sanitize->sanitizeString("Codigo"));
                                $tarjetaVO = $tarjetaDAO->retrieve(trim($Codigo[0]), "id");
                                //error_log(print_r($tarjetaVO, true));
                                if (strtolower($tarjetaVO->getEstado()) === StatusUnidad::ACTIVA) {
                                    $Kilometraje = empty($sanitize->sanitizeString("Kilometraje")) ? $rmVO->getKilometraje() : $sanitize->sanitizeInt("Kilometraje");

                                    $rmVO->setCliente($sanitize->sanitizeInt("Cliente"));
                                    if ($request->hasAttribute("Placas") && !empty($request->getAttribute("Placas"))) {
                                        $rmVO->setPlacas($request->getAttribute("Placas"));
                                    } else {
                                        $rmVO->setPlacas($tarjetaVO->getPlacas());
                                    }
                                    $rmVO->setCodigo($tarjetaVO->getCodigo());
                                    $rmVO->setKilometraje($Kilometraje);
                                    if ($clienteVO_->getTipodepago() === "Consignacion") {
                                        $rmVO->setTipo_venta("N");
                                    }
                                    if ($rmDAO->update($rmVO)) {
                                        $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                                        BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "REASIGNA VENTA " . $rmVO->getId() . " A CLIENTE " . $rmVO->getCliente());
                                        error_log("TIPO CLIENTE " . $clienteVO->getTipodepago());
                                        if ($clienteVO->getTipodepago() === TiposCliente::CREDITO || $clienteVO->getTipodepago() === TiposCliente::PREPAGO || $clienteVO->getTipodepago() === TiposCliente::TARJETA) {
                                            $sqlUpdateCxcLogic = "UPDATE cxc SET placas = '" . $tarjetaVO->getPlacas() . "' 
                                      WHERE cliente =  " . $rmVO->getCliente() . " AND referencia = '" . $rmVO->getId() . "' AND producto = '" . $rmVO->getProducto() . "' AND tm = 'C' LIMIT 1;";
                                            error_log($sqlUpdateCxcLogic);
                                            if (!$mysqli->query($sqlUpdateCxcLogic)) {
                                                error_log($mysqli->error);
                                                $Msj = utils\Messages::RESPONSE_ERROR;
                                            }
                                        } else {
                                            error_log("ENTRA EN ELSE ");
                                            $clienteVO = $clientesDAO->retrieve($rmVO->getCliente());
                                            //error_log(print_r($clientevO, true));
                                            if ($clienteVO->getTipodepago() === TiposCliente::CREDITO || $clienteVO->getTipodepago() === TiposCliente::PREPAGO || $clienteVO->getTipodepago() === TiposCliente::TARJETA) {
                                                $cxcVO = new CxcVO();
                                                $IdCxc = "SELECT id FROM cxc WHERE referencia = " . $rmVO->getId() . " AND cliente = " . $rmVO->getCliente() . " AND tm='C'";
                                                error_log($IdCxc);
                                                $Idcxc = utils\IConnection::execSql($IdCxc);
                                                if ($Idcxc["id"] > 0) {
                                                    $cxcVO = $cxcDAO->retrieve($Idcxc["id"]);
                                                    $cxcVO->setPlacas($rmVO->getPlacas());
                                                    if ($cxcDAO->update($cxcVO) < 0) {
                                                        $Msj = utils\Messages::RESPONSE_ERROR;
                                                    } else {
                                                        $rmVO = $rmDAO->retrieve($rmVO->getId());
                                                        $rmVO->setEnviado(0);
                                                        $rmDAO->update($rmVO);
                                                    }
                                                } else {
                                                    $cxcVO->setCliente($rmVO->getCliente());
                                                    $cxcVO->setPlacas($tarjetaVO->getPlacas());
                                                    $cxcVO->setReferencia($rmVO->getId());
                                                    $cxcVO->setFecha($rmVO->getFin_venta());
                                                    $cxcVO->setHora($rmVO->getFin_venta());
                                                    $cxcVO->setTm("C");
                                                    $cxcVO->setConcepto("Venta de combustible");
                                                    $cxcVO->setCantidad($rmVO->getVolumen());
                                                    $cxcVO->setImporte($rmVO->getPesos());
                                                    $cxcVO->setRecibo(0);
                                                    $cxcVO->setCorte($rmVO->getCorte());
                                                    $cxcVO->setRubro("-----");
                                                    $cxcVO->setProducto($rmVO->getProducto());
                                                    error_log(print_r($cxcVO, true));
                                                    if ($cxcDAO->create($cxcVO) < 0) {
                                                        error_log($mysqli->error);
                                                        $Msj = utils\Messages::RESPONSE_ERROR;
                                                    } else {
                                                        $rmVO = $rmDAO->retrieve($rmVO->getId());
                                                        $rmVO->setEnviado(0);
                                                        $rmDAO->update($rmVO);
                                                    }
                                                }
                                            }
                                        }
                                    } else {
                                        $Msj = utils\Messages::RESPONSE_ERROR;
                                    }
                                } else {
                                    $Msj = "La unidad esta desactivada, favor de verificarlo.";
                                }
                            } else {
                                $Msj = "No es posible realizar esta operacion, el ticket ya esta facturado.";
                            }
                        } else {
                            $Msj = "La venta ya tiene un código asignado";
                        }
                    } else {
                        $Msj = "El código ingresado es invalido";
                    }
                } else {
                    $Msj = "No es posible realizar esta operacion, el cliente [" . $clienteVO_->getNombre() . "] esta inactivo";
                }
            } else {
                $UpdateTarjeta = "UPDATE unidades SET importe = importe - " . $rmVO->getImporte() . " WHERE cliente = " . $clienteVO_->getId()
                        . " AND periodo = 'B' AND impreso = '" . trim($Codigo[1]) . "'";
                $tarjetaVO = $tarjetaDAO->retrieve(trim($Codigo[0]), "id");
                if (!$mysqli->query($UpdateTarjeta)) {
                    error_log($mysqli->error);
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
                $Kilometraje = empty($sanitize->sanitizeString("Kilometraje")) ? $rmVO->getKilometraje() : $sanitize->sanitizeInt("Kilometraje");
                $rmVO->setCliente($sanitize->sanitizeInt("Cliente"));
                if ($request->hasAttribute("Placas") && !empty($request->getAttribute("Placas"))) {
                    $rmVO->setPlacas($request->getAttribute("Placas"));
                } else {
                    $rmVO->setPlacas($tarjetaVO->getPlacas());
                }
                $rmVO->setCodigo($tarjetaVO->getCodigo());
                $rmVO->setKilometraje($Kilometraje);
                if ($rmDAO->update($rmVO)) {
                    $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                }
                $cxcVO = new CxcVO();
                if ($rmVO->getCliente() > 0) {
                    $IdCxc = "select id from cxc WHERE referencia = " . $rmVO->getId() . " AND cliente = " . $rmVO->getCliente() . " AND tm='C'";
                    error_log($IdCxc);
                    $Idcxc = utils\IConnection::execSql($IdCxc);
                    $cxcVO = $cxcDAO->retrieve($Idcxc["id"]);
                    $cxcVO->setPlacas($rmVO->getPlacas());
                    if ($cxcDAO->update($cxcVO) < 0) {
                        $Msj = utils\Messages::RESPONSE_ERROR;
                    } else {
                        $rmVO = $rmDAO->retrieve($rmVO->getId());
                        $rmVO->setEnviado(0);
                        $rmDAO->update($rmVO);
                    }
                } else {
                    $cxcVO->setCliente($rmVO->getCliente());
                    $cxcVO->setReferencia($rmVO->getId());
                    $cxcVO->setFecha($rmVO->getFin_venta());
                    $cxcVO->setHora($rmVO->getFin_venta());
                    $cxcVO->setTm("C");
                    $cxcVO->setConcepto("Venta de combustible");
                    $cxcVO->setCantidad($rmVO->getVolumen());
                    $cxcVO->setImporte($rmVO->getPesos());
                    $cxcVO->setRecibo(0);
                    $cxcVO->setCorte($rmVO->getCorte());
                    $cxcVO->setRubro("-----");
                    $cxcVO->setProducto($rmVO->getProducto());
                    error_log(print_r($cxcVO, true));
                    if ($cxcDAO->create($cxcVO) < 0) {
                        error_log($mysqli->error);
                        $Msj = utils\Messages::RESPONSE_ERROR;
                    } else {
                        $rmVO = $rmDAO->retrieve($rmVO->getId());
                        $rmVO->setEnviado(0);
                        $rmDAO->update($rmVO);
                    }
                }
            }
        } elseif ($request->getAttribute("Boton") === "Modificar volumen") {
            BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "CAMBIA VOLUMEN " . $busca . " A [" . $request->getAttribute("VolumenV") . "]");
            $Update = "UPDATE rm SET totalizadorvi = volumen, volumen = " . $request->getAttribute("VolumenV") . " ,volumenp = " . $request->getAttribute("VolumenV") . ",totalizadorvf='" . $request->getAttribute("VolumenV") . "' WHERE id = $busca";
            utils\IConnection::execSql($Update);
            $Return = "remisionesCPe.php?busca=$busca";
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en pagos: " . $ex);
    } finally {
        header("Location: $Return");
    }
}


if ($request->hasAttribute("op")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $cId = $sanitize->sanitizeInt("cId");

    try {
        if ($request->getAttribute("op") === utils\Messages::OP_DELETE) {
            $Msj = utils\Messages::RESPONSE_ERROR;
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en pagos: " . $ex);
    } finally {
        header("Location: $Return");
    }
}