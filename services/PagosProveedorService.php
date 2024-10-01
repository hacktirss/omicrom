<?php

include_once ("data/PagosPrvDAO.php");
#Librerias

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "pagosprv.php?";

$ciaDAO = new CiaDAO();
$pagosPrvDAO = new PagosPrvDAO();


if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    $pagoPrvVO = new PagosPrvVO();
    $busca = $sanitize->sanitizeInt("busca");
    if (is_numeric($busca)) {
        $pagoPrvVO = $pagosPrvDAO->retrieve($busca);
    }
    $pagoPrvVO->setProveedor($sanitize->sanitizeInt("Proveedor"));
    $pagoPrvVO->setConcepto($sanitize->sanitizeString("Concepto"));
    $pagoPrvVO->setFecha($sanitize->sanitizeString("Fecha"));
    $pagoPrvVO->setImporte($sanitize->sanitizeFloat("Importe"));
    $pagoPrvVO->setReferencia("PAGO A CUENTA");
    $pagoPrvVO->setStatus(StatusPagoProveedor::ABIERTO);
    $pagoPrvVO->setAplicado(0);

    //error_log(print_r($pagoPrvVO, TRUE));
    try {
        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            if (($id = $pagosPrvDAO->create($pagoPrvVO)) > 0) {
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                $Return = "pagosprvd.php?cVarVal=$id";
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
            if ($pagosPrvDAO->update($pagoPrvVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en pagos proveedores: " . $ex);
    } finally {
        header("Location: $Return");
    }
}


if ($request->hasAttribute("op")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $busca = $sanitize->sanitizeInt("busca");

    try {
        if ($request->getAttribute("op") === utils\Messages::OP_CANCEL) {
            $ciaVO = $ciaDAO->retrieve(1);
            $pagoPrvVO = $pagosPrvDAO->retrieve($busca);

            if ($ciaVO->getMaster() === $sanitize->sanitizeString("Password")) {

                $pagoPrvVO->setImporte(0);
                $pagoPrvVO->setConcepto("Pago cancelado " . date("Y-m-d"));
                $pagoPrvVO->setStatus(StatusPagoProveedor::CANCELADO);

                if ($pagosPrvDAO->update($pagoPrvVO)) {
                    $Msj = utils\Messages::RESPONSE_VALID_CANCEL;

                    $Sql1 = "DELETE FROM cxp WHERE numpago='$busca' AND tm='H'";
                    $Ins = "INSERT INTO cxp SELECT * FROM cxph WHERE numpago = $busca AND tm='H'";
                    $Sql2 = "DELETE FROM cxph WHERE numpago='$busca' AND tm='H'";
                    
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            } else {
                $Msj = utils\Messages::RESPONSE_PASSWORD_INCORRECT;
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en pagos proveedores: " . $ex);
    } finally {
        header("Location: $Return");
    }
}