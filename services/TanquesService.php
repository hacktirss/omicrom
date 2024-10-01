<?php

#Librerias
include_once ('data/TanqueDAO.php');
include_once ('data/CombustiblesDAO.php');
include_once ('/data/VariablesDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "tanquese.php?";

$tanqueDAO = new TanqueDAO();
$combustibleDAO = new CombustiblesDAO();
$ciaDAO = new CiaDAO();

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    $objectVO = new TanqueVO();
    $combustibleVO = $combustibleDAO->retrieve($sanitize->sanitizeString("Producto"), "clave");

    $objectVO->setId($sanitize->sanitizeInt("busca"));
    if (is_numeric($objectVO->getId())) {
        $objectVO = $tanqueDAO->retrieve($objectVO->getId());
    }
    $objectVO->setTanque($sanitize->sanitizeInt("Tanque"));
    $objectVO->setProducto($combustibleVO->getDescripcion());
    $objectVO->setClave_producto($combustibleVO->getClave());
    $objectVO->setEstado($sanitize->sanitizeInt("Estado"));
    $objectVO->setCapacidad_total($sanitize->sanitizeString("CapacidadTotal"));
    $objectVO->setVolumen_fondaje($sanitize->sanitizeFloat("Volumen_fondaje"));
    $objectVO->setVolumen_minimo($sanitize->sanitizeFloat("Volumen_minimo"));
    $objectVO->setVolumen_operativo($sanitize->sanitizeFloat("Volumen_operativo"));
    $objectVO->setDescripcion($sanitize->sanitizeString("Descripcion"));
    error_log(print_r($sanitize->sanitizeString("Descripcion"), true));

    try {
        error_log("BOTON" . $request->getAttribute("Boton"));
        if ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
            $Clave_admin = VariablesDAO::getVariable("clave_admin");
            if ($Clave_admin === md5($sanitize->sanitizeString("Clave_Admin"))) {
                if ($tanqueDAO->update($objectVO)) {
                    $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                    BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "ACTUALIZACION DE TANQUE " . $objectVO->getTanque());
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE . "SAT") {
            $objectVO = $tanqueDAO->retrieve($objectVO->getId());
            $objectVO->setPrefijo_sat($sanitize->sanitizeString("Prefijo_sat"));
            $objectVO->setSistema_medicion($sanitize->sanitizeString("Sistema_medicion"));
            $objectVO->setSensor($sanitize->sanitizeString("Sensor"));
            $objectVO->setCapacidad_total($sanitize->sanitizeString("CapacidadTotal"));
            $objectVO->setDescripcion($sanitize->sanitizeString("Descripcion"));
            $objectVO->setIdProveedor($sanitize->sanitizeString("Proveedor"));
            $objectVO->setIdProveedorSesor($sanitize->sanitizeString("ProveedorSensor"));
            $Insertidumbre = $sanitize->sanitizeString("Incertidumbre_sensor") / 100;
            $objectVO->setIncertidumbre_sensor($Insertidumbre);

            if ($tanqueDAO->update($objectVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "ACTUALIZACION DE TANQUE " . $objectVO->getTanque());
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === "Ajustar") {
            $objectVO = $tanqueDAO->retrieve($objectVO->getId());
            $objectVO->setVigencia_calibracion($sanitize->sanitizeString("Calibracion"));
            if ($tanqueDAO->update($objectVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "ACTUALIZACION DE VIGENCIA DE CALIBRACION DE TANQUE " . $objectVO->getTanque());
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en tanques: " . $ex);
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
        error_log("Error en tanques: " . $ex);
    } finally {
        header("Location: $Return");
    }
}