<?php

#Librerias
include_once ('data/TerminalPosDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "terminales.php?";

$terminalPosDAO = new TerminalPosDAO();

if ($request->hasAttribute("Boton")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    $terminalPosVO = new TerminalPosVO();
    if(is_numeric($sanitize->sanitizeInt("busca"))){
        $terminalPosVO = $terminalPosDAO->retrieve($sanitize->sanitizeInt("busca"), "pos_id");
    }
    $terminalPosVO->setSerial($sanitize->sanitizeString("Serial"));
    $terminalPosVO->setPrinted_serial($sanitize->sanitizeString("PrintedSN"));
    $terminalPosVO->setModel($sanitize->sanitizeString("Modelo"));
    $terminalPosVO->setIp($sanitize->sanitizeString("Ip"));
    $terminalPosVO->setStatus($sanitize->sanitizeString("Status"));
    $terminalPosVO->setDispositivo($sanitize->sanitizeString("Dispositivo"));
    try {
        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            if ($terminalPosDAO->create($terminalPosVO) > 0) {
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(),"ADM","CREACION DE TERMINAL " . $terminalPosVO->getSerial());
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
            if ($terminalPosDAO->update($terminalPosVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(),"ADM","ACTUALIZACION DE TERMINAL " . $terminalPosVO->getSerial());
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }

        }
    } catch (Exception $ex) {
        error_log("Error en terminales: " . $ex);
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

    try {
        if ($request->getAttribute("op") === utils\Messages::OP_DELETE) {
            
        } else {
            $Msj = utils\Messages::RESPONSE_PASSWORD_INCORRECT;
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en terminales: " . $ex);
    } finally {
        header("Location: $Return");
    }
}
