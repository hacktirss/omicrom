<?php

#Librerias
include_once ('data/ComisionesDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "comisiones.php?";

$ComisionesDAO = new ComisionesDAO();

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    $objectVO = new ComisionesVO();

    $objectVO->setId($sanitize->sanitizeInt("busca"));
    if (is_numeric($objectVO->getId())) {
        $objectVO = $ComisionesDAO->retrieve($objectVO->getId());
    }
    error_log(print_r($objectVO, TRUE));
    error_log(print_r($request, TRUE));
    try {
        $objectVO->setId_com($sanitize->sanitizeInt("Combustible"));
        $objectVO->setId_prv($sanitize->sanitizeInt("Proveedor"));
        $objectVO->setMonto($sanitize->sanitizeString("Monto"));
        $objectVO->setVigencia($sanitize->sanitizeString("Fecha"));
        if ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
            if ($ComisionesDAO->update($objectVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "ACTUALIZACION DE COMISION");
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            $BuscaUltimo = "select id from comisiones order by id desc limit 1;";
            error_log("ENBTRA1");
            if ($ant = utils\IConnection::execSql($BuscaUltimo)) {
                $ComisionesVOant = $ComisionesDAO->retrieve($ant["id"]);
                $ComisionesVOant->setVigenciafin(date("Y-m-d"));
                error_log("ENTRA2");
                if ($ComisionesDAO->update($ComisionesVOant)) {
                    if ($ComisionesDAO->create($objectVO)) {
                        $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                        error_log("ENTRA3");
                        BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "AGREGA REGISTRO DE COMISION");
                    } else {
                        $Msj = utils\Messages::RESPONSE_ERROR;
                    }
                }
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