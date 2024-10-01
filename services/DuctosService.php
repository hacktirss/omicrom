<?php

#Librerias
include_once ('data/DuctosDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();

if ($request->hasAttribute("Boton")) {
    $ductosDAO = new DuctosDAO();
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $Return = "ductos.php?";

    try {
        if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {

            if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {

                $objectVO = new DuctosVO();
                $objectDAO = new DuctosDAO();

                if ($sanitize->sanitizeString("Clave_producto")) {
                    $objectVO->setClave_identificacion_ducto($sanitize->sanitizeString("Clave_ductos01"));
                } else {
                    $objectVO->setClave_identificacion_ducto($sanitize->sanitizeString("Clave_ductos00"));
                }

                $objectVO->setDescripcion_ducto($sanitize->sanitizeString("Descripcion"));
                $objectVO->setDiametro_ducto($sanitize->sanitizeFloat("Diametro"));
                $objectVO->setTipo_ducto($sanitize->sanitizeInt("Tpducto"));
                $objectVO->setDescripcion_tipo_ducto($sanitize->sanitizeString("Descripcion_tipo_ducto"));
                $objectVO->setCve_producto_sat_ducto($sanitize->sanitizeString("Clave_producto"));
                $objectVO->setAlmacenamiento_ducto($sanitize->sanitizeString("Almacenamiento"));
                $objectVO->setMedidor($sanitize->sanitizeString("Medidor"));
                $objectVO->setSistema_medicion($sanitize->sanitizeString("Sistema_medicion"));
                
                if (($id = $objectDAO->create($objectVO)) > 0) {
                    $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
                $objectVO = $ductosDAO->retrieve($sanitize->sanitizeInt("busca"));

                if ($objectVO->getTipo_ducto() == 0) {
                    $objectVO->setClave_identificacion_ducto($sanitize->sanitizeString("Clave_ductos00"));
                } else {
                    $objectVO->setClave_identificacion_ducto($sanitize->sanitizeString("Clave_ductos01"));
                }
                $objectVO->setDescripcion_ducto($sanitize->sanitizeString("Descripcion"));
                $objectVO->setDiametro_ducto($sanitize->sanitizeFloat("Diametro"));
                $objectVO->setDescripcion_tipo_ducto($sanitize->sanitizeString("Descripcion_tipo_ducto"));
                $objectVO->setCve_producto_sat_ducto($sanitize->sanitizeString("Clave_producto"));
                $objectVO->setAlmacenamiento_ducto($sanitize->sanitizeString("Almacenamiento"));
                $objectVO->setMedidor($sanitize->sanitizeString("Medidor"));
                $objectVO->setSistema_medicion($sanitize->sanitizeString("Sistema_medicion"));
                error_log(print_r($objectVO, true));
                if ($ductosDAO->update($objectVO)) {
                    $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE . "Fecha") {
                $objectVO = $ductosDAO->retrieve($sanitize->sanitizeInt("busca"));

                $objectVO->setVigencia_calibracion_ducto($sanitize->sanitizeString("Fecha_calibracion"));

                if ($ductosDAO->update($objectVO)) {
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
