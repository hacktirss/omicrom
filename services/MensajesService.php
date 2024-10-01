<?php

#Librerias
include_once ('data/MensajesDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "mensajes.php?";

$mensajeDAO = new MensajesDAO();
$ciaDAO = new CiaDAO();


if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    $mensajeVO = new MensajeVO();

    $mensajeVO->setId($sanitize->sanitizeInt("busca"));
    if (is_numeric($mensajeVO->getId())) {
        $mensajeVO = $mensajeDAO->retrieve($mensajeVO->getId());
    }
    $mensajeVO->setDe($sanitize->sanitizeString("De"));
    $mensajeVO->setPara($sanitize->sanitizeString("Para"));
    $mensajeVO->setTitulo($sanitize->sanitizeString("Titulo"));
    $mensajeVO->setNota($sanitize->sanitizeString("Nota"));
    $mensajeVO->setVigencia($sanitize->sanitizeString("Vigencia"));
    $mensajeVO->setTipo($sanitize->sanitizeString("Tipo"));

    error_log(print_r($mensajeVO, TRUE));
    try {
        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            $mensajeVO->setTipo(TipoMensaje::SIN_LEER);
            if ($mensajeDAO->create($mensajeVO) > 0) {
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
            if ($mensajeDAO->update($mensajeVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en mensajes: " . $ex);
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
        error_log("Error en pagos: " . $ex);
    } finally {
        header("Location: $Return");
    }
}