<?php

#Librerias
include_once ('data/PozosDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();

if ($request->hasAttribute("Boton")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $Return = "pozos.php?";
    $objectDAO = new PozosDAO();
    $objectVO = new PozosVO();
    try {
        if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
            error_log("REQUEST ".print_r($request, true));
            if ($request->getAttribute("busca") !== "NUEVO") {
                $objectVO = $objectDAO->retrieve($request->getAttribute("busca"));
                $objectVO->setId($request->getAttribute("busca"));
            }
            $objectVO->setDescripcion($request->getAttribute("Descripcion"));
            $objectVO->setDescripcion_sistema_medicion($request->getAttribute("Descripcion_sistema_medicion"));
            $objectVO->setVigencia_sistema_medicion($request->getAttribute("Vigencia_sistema_medicion"));
            $objectVO->setClave_sistema_medicion($request->getAttribute("Clave_sistema_medicion"));
            $objectVO->setIncertidumbre_sistema_medicion($request->getAttribute("Incertidumbre_sistema_medicion"));
            if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
                if (($id = $objectDAO->create($objectVO)) > 0) {
                    $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
                error_log(print_r($objectVO, true));
                if ($objectDAO->update($objectVO)) {
                    $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en parametros: " . $ex);
    } finally {
        header("Location: $Return");
    }
}
