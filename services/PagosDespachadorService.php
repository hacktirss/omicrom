<?php

include_once ("data/PagosDespDAO.php");
#Librerias

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "pagosdesp.php?";

$pagosDespDAO = new PagosDespDAO();
$ciaDAO = new CiaDAO();

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    $objectVO = new PagosDespVO();
    $busca = $sanitize->sanitizeInt("busca");
    if (is_numeric($busca)) {
        $objectVO = $pagosDespDAO->retrieve($busca);
    }
    $objectVO->setVendedor($sanitize->sanitizeInt("Vendedor"));
    $objectVO->setDeposito($sanitize->sanitizeString("Fecha"));
    $objectVO->setConcepto($sanitize->sanitizeString("Concepto"));
    $objectVO->setImporte($sanitize->sanitizeFloat("Importe"));
    $objectVO->setStatus(StatusPagoDespachador::ABIERTO);

    //error_log(print_r($objectVO, TRUE));
    try {
        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            if (($id = $pagosDespDAO->create($objectVO)) > 0) {
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                $Return = "pagosdespd.php?cVarVal=$id";
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
            if ($pagosDespDAO->update($objectVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_CANCEL) {

            $ciaVO = $ciaDAO->retrieve(1);

            if ($ciaVO->getMaster() === $sanitize->sanitizeString("Password")) {
                $objectVO->setStatus(StatusPagoDespachador::CANCELADO);
                if ($pagosDespDAO->update($objectVO)) {
                    $updatePagosdespd = "UPDATE pagosdespd SET pago = -pago, referencia = -referencia WHERE pago = '$busca';";
                    $updateCxd = "UPDATE cxd SET recibo = -recibo,referencia = -referencia,vendedor = -vendedor WHERE recibo = '$busca' AND tm = 'H';";
                    if ($mysqli->query($updatePagosdespd) && $mysqli->query($updateCxd)) {
                        $Msj = utils\Messages::RESPONSE_VALID_CANCEL;
                    } else {
                        $Msj = utils\Messages::RESPONSE_ERROR;
                    }
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            } else {
                $Msj = utils\Messages::RESPONSE_PASSWORD_INCORRECT;
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error : " . $ex);
    } finally {
        header("Location: $Return");
    }
}