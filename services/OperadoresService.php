<?php

#Librerias
include_once ('data/OperadorDAO.php');
include_once ('data/DireccionDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "operadores.php?";

$operadorDAO = new OperadorDAO();

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID || $request->hasAttribute("Boton2") && $request->getAttribute("Boton2") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    $operadorVO = new OperadorVO();
    $operadorVO->setId($sanitize->sanitizeInt("busca"));
    if (is_numeric($operadorVO->getId())) {
        $operadorVO = $operadorDAO->retrieve($operadorVO->getId());
    }

    $operadorVO->setId($sanitize->sanitizeInt("busca"));
    if (is_numeric($operadorVO->getId())) {
        $operadorVO = $operadorDAO->retrieve($operadorVO->getId());
    }


    $operadorVO->setRfc_operador($sanitize->sanitizeString("RFC"));
    $operadorVO->setNombre($sanitize->sanitizeString("Nombre"));
    $operadorVO->setNum_licencia($sanitize->sanitizeString("Licencia"));

    error_log(print_r($request, TRUE));
    try {
        $DireccionVO = new DireccionVO();
        $DireccionDAO = new DireccionDAO();
        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            error_log("Pulsamos en ADD");
            if ($operadorDAO->create($operadorVO) > 0) {
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
            if ($operadorDAO->update($operadorVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
            if ($request->hasAttribute("ReturnD")) {
                $Return = "IngresosCartaPorte.php?";
                $Add = "&ReturnD=" . $request->getAttribute("ReturnD");
            }
        } else if ($request->getAttribute("Boton2") === utils\Messages::OP_UPDATE . " Direccion") {
            $DireccionVO = $DireccionDAO->retrieve($request->getAttribute("IdDireccion"));
            $DireccionVO->setCalle($request->getAttribute("Calle"));
            $DireccionVO->setNum_exterior($request->getAttribute("Ext"));
            $DireccionVO->setNum_interior($request->getAttribute("Int"));
            $DireccionVO->setEstado($request->getAttribute("Estado"));
            $DireccionVO->setMunicipio($request->getAttribute("Municipio"));
            $DireccionVO->setLocalidad($request->getAttribute("Localidad"));
            $DireccionVO->setCodigo_postal($request->getAttribute("CodigoPostal"));
            $DireccionVO->setColonia($request->getAttribute("Colonia"));
            if ($DireccionDAO->update($DireccionVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
            if ($request->hasAttribute("ReturnD")) {
                $Add = "&ReturnD=" . $request->getAttribute("ReturnD");
            }
            $Return = "operadorese.php?busca=" . $request->getAttribute("busca2") . $Add;
        } else if ($request->getAttribute("Boton2") === utils\Messages::OP_ADD . " Direccion") {
            $DireccionVO->setCalle($request->getAttribute("Calle"));
            $DireccionVO->setNum_exterior($request->getAttribute("Ext"));
            $DireccionVO->setNum_interior($request->getAttribute("Int"));
            $DireccionVO->setEstado($request->getAttribute("Estado"));
            $DireccionVO->setMunicipio($request->getAttribute("Municipio"));
            $DireccionVO->setLocalidad($request->getAttribute("Localidad"));
            $DireccionVO->setCodigo_postal($request->getAttribute("CodigoPostal"));
            $DireccionVO->setColonia($request->getAttribute("Colonia"));
            $DireccionVO->setTabla_origen("O");
            $DireccionVO->setId_origen($request->getAttribute("idCliente"));
            if ($DireccionDAO->create($DireccionVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
            $Return = "operadorese.php?busca=" . $request->getAttribute("busca2");
        }

        $Return .= "&Msj=" . urlencode($Msj) . $Add;
    } catch (Exception $ex) {
        error_log("Error en pagos: " . $ex);
    } finally {
        header("Location: $Return");
    }
}


if ($request->hasAttribute("op")) {
    error_log("Estamos en OP ");
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $operadorDAO = new OperadorDAO();
    $cId = $sanitize->sanitizeInt("cId");

    try {
        if ($request->getAttribute("op") === utils\Messages::OP_DELETE) {

            if ($operadorDAO->remove($cId)) {
                $Msj = utils\Messages::RESPONSE_VALID_DELETE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en Operadores: " . $ex);
    } finally {
        header("Location: $Return");
    }
}