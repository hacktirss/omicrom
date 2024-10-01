<?php

#Librerias
include_once ('data/VentaAditivosDAO.php');
include_once ('data/ClientesDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "ventaace.php?";

$objectDAO = new VentaAditivosDAO();
$clientesDAO = new ClientesDAO();

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    $objectVO = new VentaAditivosVO();
    if (is_numeric($sanitize->sanitizeInt("busca"))) {
        $objectVO = $objectDAO->retrieve($sanitize->sanitizeInt("busca"));
    }

    try {
        if ($request->getAttribute("Boton") === utils\Messages::OP_SAVE) {
            $Codigo = explode("|", $sanitize->sanitizeString("Codigo"));
            $objectVO->setCodigo(trim($Codigo[0]));
            if (!empty($objectVO->getCodigo())) {
                if ($objectDAO->update($objectVO)) {
                    $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            } else{
                $Msj .= ". Favor de seleccionar un codigo valido!";
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error: " . $ex);
    } finally {
        header("Location: $Return");
    }
}
