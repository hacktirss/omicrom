<?php

#Librerias
include_once ('data/ComprasoeDAO.php');
include_once ('data/ProveedorDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "compraso.php?";

$comprasoeDAO = new ComprasoeDAO();
$proveedorDAO = new ProveedorDAO();

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    try {
        $comprasoeVO = new ComprasoeVO();
        $comprasoeVO->setId($sanitize->sanitizeInt("busca"));
        if (is_numeric($comprasoeVO->getId())) {
            $comprasoeVO = $comprasoeDAO->retrieve($comprasoeVO->getId());
        }
        $comprasoeVO->setFechav($sanitize->sanitizeString("Fechav"));
        $comprasoeVO->setProveedor($sanitize->sanitizeInt("Proveedor"));
        $comprasoeVO->setDocumento($sanitize->sanitizeString("Documento"));
        $comprasoeVO->setConcepto($sanitize->sanitizeString("Concepto"));
        $comprasoeVO->setImporte($sanitize->sanitizeFloat("Importe"));
        //error_log(print_r($request, TRUE));

        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            if (($id = $comprasoeDAO->create($comprasoeVO)) > 0) {
                $Return = "comprasod.php?cVarVal=" . $id;
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
            if ($comprasoeDAO->update($comprasoeVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en compras: " . $ex);
    } finally {
        header("Location: $Return");
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
        error_log("Error en compras: " . $ex);
    } finally {
        header("Location: $Return");
    }
}