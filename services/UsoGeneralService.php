<?php

#Librerias
include_once ('data/PermisoCreDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "uso_general.php?";


$nameVariableSession = "CatalogoUniversalDetalle";
if ($request->hasAttribute("cVarVal")) {
    utils\HTTPUtils::setSessionBiValue($nameVariableSession, "cVarVal", $request->getAttribute("cVarVal"));
}
$cVarVal = utils\HTTPUtils::getSessionBiValue($nameVariableSession, "cVarVal");


if ($request->hasAttribute("Boton")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $Return = "uso_generald.php?";

    $objectDAO = new PermisoCreDAO();
    $objectFather = $objectDAO->retrieve($cVarVal);
    $objectVO = new PermisoCreVO();
    $objectVO->setPadre($objectFather->getId());
    $objectVO->setCatalogo($objectFather->getCatalogo());
    if (is_numeric($sanitize->sanitizeInt("busca"))) {
        $objectVO = $objectDAO->retrieve($sanitize->sanitizeInt("busca"));
    }

    $objectVO->setLlave($sanitize->sanitizeString("Llave"));
    $objectVO->setPermiso($sanitize->sanitizeString("Permiso"));
    $objectVO->setDescripcion($sanitize->sanitizeString("Descripcion"));
    $objectVO->setEstado($sanitize->sanitizeString("Estado"));

    //error_log(print_r($objectVO, TRUE));
    try {
        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {

            $sql = "SELECT * FROM permisos_cre WHERE padre = '" . $cVarVal . "' "
                    . "AND llave = '" . $objectVO->getLlave() . "' LIMIT 1;";
            $rows = $mysqli->query($sql)->fetch_array();
            if (count($rows) > 0) {
                $Msj = "Lo siento, ya existe la clave [" . $objectVO->getLlave() . "] para este catálogo.";
            } else {
                if ($objectDAO->create($objectVO) > 0) {
                    $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                    BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "CREACION DE PERMISO " . $objectVO->getLlave());
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
            $sql = "SELECT * FROM permisos_cre WHERE padre = '" . $cVarVal . "' "
                    . "AND llave = '" . $objectVO->getLlave() . "' AND id <> '" . $objectVO->getId() . "' LIMIT 1;";
            $rows = $mysqli->query($sql)->fetch_array();
            if (count($rows) > 0) {
                $Msj = "Lo siento, ya existe la calve [" . $objectVO->getLlave() . "] para este catálogo.";
            } else {
                if ($objectDAO->update($objectVO)) {
                    BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "ACTUALIZACION DE PERMISO " . $objectVO->getLlave());
                    $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            }
        }
    } catch (Exception $ex) {
        error_log("Error en usoGeneral: " . $ex);
    } finally {
        if ($mysqli->errno > 0) {
            error_log($mysqli->error);
        }
        if ($Return != null) {
            $Return .= "&Msj=" . urlencode($Msj);
            header("Location: $Return");
        }
    }
}
