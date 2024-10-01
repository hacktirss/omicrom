<?php

#Librerias
include_once ('data/ManDAO.php');
include_once ('data/ManProDAO.php');
include_once ('data/VariablesDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "dispensarios.php?";

$manDAO = new ManDAO();
$manProDAO = new ManProDAO();
$ciaDAO = new CiaDAO();

$nameVariableSession = "CatalogoDispensariosDetalle";
//error_log(print_r($request, true));
if ($request->hasAttribute("cVarVal")) {
    utils\HTTPUtils::setSessionBiValue($nameVariableSession, "cVarVal", $request->getAttribute("cVarVal"));
}

$cVarVal = utils\HTTPUtils::getSessionBiValue($nameVariableSession, "cVarVal");

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $Clave_admin = VariablesDAO::getVariable("clave_admin");
    $objectVO = new ManVO();

    $objectVO->setId($sanitize->sanitizeInt("busca"));
    if (is_numeric($objectVO->getId())) {
        $objectVO = $manDAO->retrieve($objectVO->getId(), "id", false);
    }

    $objectVO->setDispensario($sanitize->sanitizeInt("Dispensario"));
    $objectVO->setPosicion($sanitize->sanitizeInt("Posicion"));
    $objectVO->setProductos($sanitize->sanitizeInt("Productos"));
    $objectVO->setInventario($sanitize->sanitizeString("Inventario"));

    //error_log(print_r($request, TRUE));
    try {
        if ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
            if ($Clave_admin === md5($sanitize->sanitizeString("Clave_Admin"))) {
                if ($manDAO->update($objectVO)) {
                    $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                    BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "ACTUALIZACION DE DISPENSARIO " . $objectVO->getDispensario());
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            }
        } elseif ($request->getAttribute("Boton") === "Ajustar") {
            $objectVO = $manDAO->retrieve($objectVO->getId(), "id", false);
            $updateManpro = "UPDATE man_pro SET vigencia_calibracion = ? WHERE dispensario = ? AND posicion = ? AND activo = 'Si';";

            if (($ps = $mysqli->prepare($updateManpro))) {
                $ps->bind_param("sii",
                        $sanitize->sanitizeString("Calibracion"),
                        $objectVO->getDispensario(),
                        $objectVO->getPosicion()
                );
                if (!$ps->execute()) {
                    error_log($mysqli->error);
                } else {

                    $udpateMedidor = "UPDATE medidores SET vigencia_calibracion = ? WHERE num_dispensario = ? AND posicion = ?;";
                    if (($ps = $mysqli->prepare($udpateMedidor))) {
                        $ps->bind_param("sii",
                                $sanitize->sanitizeString("Calibracion"),
                                $objectVO->getDispensario(),
                                $objectVO->getPosicion()
                        );
                        if (!$ps->execute()) {
                            error_log($mysqli->error);
                        }
                    } else {
                        error_log($mysqli->error);
                    }
                    $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                    BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "ACTUALIZACION DE VIGENCIA DE CALIBRACION DE POSICION " . $objectVO->getPosicion());
                }
            } else {
                error_log($mysqli->error);
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en dispensarios: " . $ex);
    } finally {
        header("Location: $Return");
    }
}

if ($request->hasAttribute("BotonD") && $request->getAttribute("BotonD") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $Return = "dispensariosd.php?";
    $Clave_admin = VariablesDAO::getVariable("clave_admin");
    $objectVO = new ManProVO();

    $objectVO->setId($sanitize->sanitizeInt("busca"));
    if (is_numeric($objectVO->getId())) {
        $objectVO = $manProDAO->retrieve($objectVO->getId());
        $Return = "dispensariosde.php?busca=" . $objectVO->getId();
    }


    //error_log(print_r($objectVO, TRUE));
    try {
        if ($request->getAttribute("BotonD") === utils\Messages::OP_UPDATE) {
            if ($Clave_admin === md5($sanitize->sanitizeString("Clave_Admin"))) {
                $objectVO->setManguera($sanitize->sanitizeInt("Manguera"));
                $objectVO->setManf($sanitize->sanitizeInt("ManF"));
                $objectVO->setDis_mang($sanitize->sanitizeInt("Dis_mang"));
                $objectVO->setProducto($sanitize->sanitizeString("Producto"));
                $objectVO->setTanque($sanitize->sanitizeInt("Tanque"));

                if ($manProDAO->update($objectVO)) {
                    $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                    BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "ACTUALIZACION DE MANGUERA " . $objectVO->getManguera() . " POSICION " . $objectVO->getPosicion());
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            }
        } elseif ($request->getAttribute("BotonD") === "Ajustar") {
            $objectVO->setVigencia_calibracion($sanitize->sanitizeString("Calibracion"));

            if ($manProDAO->update($objectVO)) {
                $udpateMedidor = "UPDATE medidores SET vigencia_calibracion = ? WHERE num_dispensario = ? AND posicion = ? AND num_manguera = ?;";

                if (($ps = $mysqli->prepare($udpateMedidor))) {
                    $ps->bind_param("siii",
                            $sanitize->sanitizeString("Calibracion"),
                            $objectVO->getDispensario(),
                            $objectVO->getPosicion(),
                            $objectVO->getManguera()
                    );
                    if (!$ps->execute()) {
                        error_log($mysqli->error);
                    }
                } else {
                    error_log($mysqli->error);
                }
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "ACTUALIZACION DE VIGENCIA DE CALIBRACION DE MANGUERA " . $objectVO->getManguera() . " POSICION " . $objectVO->getPosicion());
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("BotonD") === utils\Messages::OP_UPDATE . " SAT") {
            $udpateMedidor = "UPDATE medidores SET num_medidor = ?, tipo_medidor = ?, modelo_medidor = ?, incertidumbre = ? WHERE num_dispensario = ? AND posicion = ? AND num_manguera = ?;";
            $Insertidumbre = $sanitize->sanitizeFloat("Incertidumbre") / 100;
            if (($ps = $mysqli->prepare($udpateMedidor))) {
                $ps->bind_param("sssdiii",
                        $sanitize->sanitizeString("Num_medidor"),
                        $sanitize->sanitizeString("Tipo_medidor"),
                        $sanitize->sanitizeString("Modelo_medidor"),
                        $Insertidumbre,
                        $objectVO->getDispensario(),
                        $objectVO->getPosicion(),
                        $objectVO->getManguera()
                );
                if ($ps->execute()) {
                    $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                    BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "ACTUALIZACION DE VIGENCIA DE CALIBRACION DE MANGUERA " . $objectVO->getManguera() . " POSICION " . $objectVO->getPosicion());
                } else {
                    error_log($mysqli->error);
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            } else {
                error_log($mysqli->error);
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        }


        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en dispensarios: " . $ex);
    } finally {
        header("Location: $Return");
    }
}

if ($request->hasAttribute("BotonE")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    try {
        if ($request->getAttribute("BotonE") === "Proveedor") {
            if ($request->getAttribute("Posiciones") === "Todos") {
                $Update = "UPDATE man SET id_proveedor = " . $request->getAttribute("Proveedores") . " WHERE activo='Si'";
                utils\IConnection::execSql($Update);
            } else {
                $Update = "UPDATE man SET id_proveedor = " . $request->getAttribute("Proveedores") . " WHERE id = " . $request->getAttribute("Posiciones");
                utils\IConnection::execSql($Update);
            }
            error_log($Update);
        }
        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en dispensarios: " . $ex);
    } finally {
        header("Location: $Return");
    }
}


if ($request->hasAttribute("BotonC") && $request->getAttribute("BotonC") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $objectVO = new ManProVO();

    try {
        if ($request->getAttribute("BotonC") === "Calibracion") {
            if ($sanitize->sanitizeString("Dispensario") === "*") {
                $updateManpro = "UPDATE man_pro SET vigencia_calibracion = ? WHERE activo = 'Si';";
                if (($ps = $mysqli->prepare($updateManpro))) {
                    $ps->bind_param("s",
                            $sanitize->sanitizeString("FechaC")
                    );
                    if (!$ps->execute()) {
                        error_log($mysqli->error);
                    } else {
                        $udpateMedidor = "UPDATE medidores SET vigencia_calibracion = ?;";
                        if (($ps = $mysqli->prepare($udpateMedidor))) {
                            $ps->bind_param("s",
                                    $sanitize->sanitizeString("FechaC")
                            );
                            if (!$ps->execute()) {
                                error_log($mysqli->error);
                            } else {
                                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "ACTUALIZACION DE VIGENCIA DE CALIBRACION DE POSICION " . $objectVO->getPosicion());
                            }
                        } else {
                            error_log($mysqli->error);
                        }
                    }
                } else {
                    error_log($mysqli->error);
                }
            } else {
                $updateManpro = "UPDATE man_pro SET vigencia_calibracion = ? WHERE dispensario = ? AND activo = 'Si';";
                if (($ps = $mysqli->prepare($updateManpro))) {
                    $ps->bind_param("ss",
                            $sanitize->sanitizeString("FechaC"),
                            $sanitize->sanitizeString("Dispensario")
                    );
                    if (!$ps->execute()) {
                        error_log($mysqli->error);
                    } else {
                        $udpateMedidor = "UPDATE medidores SET vigencia_calibracion = ? WHERE num_dispensario = ?;";
                        if (($ps = $mysqli->prepare($udpateMedidor))) {
                            $ps->bind_param("ss",
                                    $sanitize->sanitizeString("FechaC"),
                                    $sanitize->sanitizeString("Dispensario")
                            );
                            if (!$ps->execute()) {
                                error_log($mysqli->error);
                            } else {
                                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "ACTUALIZACION DE VIGENCIA DE CALIBRACION DE POSICION " . $objectVO->getPosicion());
                            }
                        } else {
                            error_log($mysqli->error);
                        }
                    }
                } else {
                    error_log($mysqli->error);
                }
            }
        } else if ($request->getAttribute("BotonE") === "Proveedor") {
            if ($request->getAttribute("Posiciones") === "Todos") {
                $Update = "UPDATE man SET id_proveedor = " . $request->getAttribute("Proveedores") . " WHERE activo='Si'";
                utils\IConnection::execSql($Update);
            } else {
                $Update = "UPDATE man SET id_proveedor = " . $request->getAttribute("Proveedores") . " WHERE id = " . $request->getAttribute("Posiciones");
                utils\IConnection::execSql($Update);
            }
            error_log($Update);
        }
        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en dispensarios: " . $ex);
    } finally {
        header("Location: $Return");
    }
}

if ($request->hasAttribute("op")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $cId = $sanitize->sanitizeInt("busca");

    try {
        if ($request->getAttribute("op") === utils\Messages::OP_DELETE) {
            
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en dispensarios: " . $ex);
    } finally {
        header("Location: $Return");
    }
}