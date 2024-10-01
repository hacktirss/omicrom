<?php

#Librerias
include_once ('data/TurnoDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "turnos.php?";

$turnoDAO = new TurnoDAO();

if ($request->hasAttribute("Boton")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    $turno = $turnoDAO->retrieve($sanitize->sanitizeInt("Turno"), "turno");

    $turnoVO = new TurnoVO();
    if (is_numeric($sanitize->sanitizeInt("busca"))) {
        $turnoVO = $turnoDAO->retrieve($sanitize->sanitizeInt("busca"));
    }
    $turnoVO->setIsla($sanitize->sanitizeInt("Isla"));
    $turnoVO->setTurno($sanitize->sanitizeInt("Turno"));
    $turnoVO->setDescripcion($sanitize->sanitizeString("Descripcion"));
    $turnoVO->setHorai($sanitize->sanitizeString("Horai"));
    $turnoVO->setHoraf($sanitize->sanitizeString("Horaf"));
    $turnoVO->setActivo($sanitize->sanitizeString("Activo"));
    $turnoVO->setCortea($sanitize->sanitizeString("CorteA"));

    //error_log(print_r($turnoVO, TRUE));
    try {
        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            if ($turno->getId() > 0) {
                $Msj = "EL turno que deseas agregar ya existe, favor de modificar el mismo";
            } else {
                if ($turnoDAO->create($turnoVO) > 0) {
                    $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                    BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(),"ADM","CREACIÓN DE TURNO " . $turnoVO->getTurno());
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
            if ($turno->getId() > 0 && $turno->getId() != $turnoVO->getId()) {
                $Msj = "Turno duplicado, favor de verificarlo";
            } else {
                if ($turnoDAO->update($turnoVO)) {
                    $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                    BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(),"ADM","ACTUALIZACIÓN DE TURNO " . $turnoVO->getTurno());
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            }
        }
    } catch (Exception $ex) {
        error_log("Error en turno: " . $ex);
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
            
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en turno: " . $ex);
    } finally {
        header("Location: $Return");
    }
}
