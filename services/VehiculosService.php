<?php

#Librerias
include_once ('data/VehiculoDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "vehiculos.php?";

$vehiculoDAO = new VehiculoDAO();

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    $vehiculoVO = new VehiculoVO();
    $vehiculoVO->setId($sanitize->sanitizeInt("busca"));
    if (is_numeric($vehiculoVO->getId())) {
        $vehiculoVO = $vehiculoDAO->retrieve($vehiculoVO->getId());
    }
    $vehiculoVO->setDescripcion($sanitize->sanitizeString("Descripcion"));
    $vehiculoVO->setConf_vehicular($sanitize->sanitizeString("Conf"));
    $vehiculoVO->setPlaca($sanitize->sanitizeString("Placa"));
    $vehiculoVO->setAnio_modelo($sanitize->sanitizeString("Anio"));
    $vehiculoVO->setSubtipo_remolque($sanitize->sanitizeString("Remolque"));
    $vehiculoVO->setPlaca_remolque($sanitize->sanitizeString("PRemolque"));
    $vehiculoVO->setPermiso_sct($sanitize->sanitizeString("Permiso"));
    $vehiculoVO->setNumero_sct($sanitize->sanitizeString("NumeroSCT"));
    $vehiculoVO->setNombre_aseguradora($sanitize->sanitizeString("Aseguradora"));
    $vehiculoVO->setNumero_seguro($sanitize->sanitizeString("Seguro"));
    $vehiculoVO->setTipo_figura($sanitize->sanitizeString("Figura"));
    error_log(print_r($vehiculoVO, TRUE));
    try {
        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            error_log("Estamos en add");
            if ($vehiculoDAO->create($vehiculoVO) > 0) {
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
            if ($request->hasAttribute("ReturnD")) {
                $Return = "IngresosCartaPorte.php?&ReturnD=" . $request->getAttribute("ReturnD");
            }
            if ($vehiculoDAO->update($vehiculoVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
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
    $vehiculoDAO = new VehiculoDAO();
    $cId = $sanitize->sanitizeInt("cId");

    try {
        if ($request->getAttribute("op") === utils\Messages::OP_DELETE) {

            if ($vehiculoDAO->remove($cId)) {
                $Msj = utils\Messages::RESPONSE_VALID_DELETE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en vehiculos: " . $ex);
    } finally {
        header("Location: $Return");
    }
}