<?php

#Librerias
include_once ('data/DireccionDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "direcciones.php?";

$direccionDAO = new DireccionDAO();

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    $direccionVO = new DireccionVO();
    $direccionVO->setId($sanitize->sanitizeInt("busca"));
    if (is_numeric($direccionVO->getId())) {
        $direccionVO = $direccionDAO->retrieve($direccionVO->getId());
    }
    $direccionVO->setDescripcion($sanitize->sanitizeString("Descripcion"));
    $direccionVO->setCalle($sanitize->sanitizeString("Calle"));
    $direccionVO->setNum_exterior($sanitize->sanitizeString("Ext"));
    $direccionVO->setNum_interior($sanitize->sanitizeString("Int"));
    $direccionVO->setColonia($sanitize->sanitizeString("Colonia"));
    $direccionVO->setLocalidad($sanitize->sanitizeString("Localidad"));
    $direccionVO->setMunicipio($sanitize->sanitizeString("Municipio"));
    $direccionVO->setEstado($sanitize->sanitizeString("Estado"));
    $direccionVO->setCodigo_postal($sanitize->sanitizeString("CodigoPostal"));
    $direccionVO->setTabla_origen($sanitize->sanitizeString("TOrigen"));
    $direccionVO->setId_origen($sanitize->sanitizeString("IdOrigen"));
    //error_log(print_r($direccionVO, TRUE));
    $direccionVO->setTabla_origen('D');
    $direccionVO->setId_origen(0);
    try {
        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            if ($direccionDAO->create($direccionVO) > 0) {
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
            if ($direccionDAO->update($direccionVO)) {
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
    $direccionDAO = new DireccionDAO();
    $cId = $sanitize->sanitizeInt("cId");

    try {
        if ($request->getAttribute("op") === utils\Messages::OP_DELETE) {
            error_log("Estamos en delete");
            if ($direccionDAO->remove($cId)) {
                $Msj = utils\Messages::RESPONSE_VALID_DELETE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en direcciones: " . $ex);
    } finally {
        header("Location: $Return");
    }
}