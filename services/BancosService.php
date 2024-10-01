<?php
#Librerias
include_once ('data/BancosDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "bancos.php?";

$objectDAO = new BancosDAO();

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    $objectVO = new BancosVO();
    $objectVO->setId($sanitize->sanitizeInt("busca"));
    $objectVO->setRubro($sanitize->sanitizeInt("Rubro"));
    $objectVO->setBanco($sanitize->sanitizeString("Banco"));
    $objectVO->setCuenta($sanitize->sanitizeString("Cuenta"));
    $objectVO->setConcepto($sanitize->sanitizeString("Concepto"));
    $objectVO->setNcc($sanitize->sanitizeString("Ncc"));
    $objectVO->setTipo_moneda($sanitize->sanitizeInt("Tipo_moneda"));
    $objectVO->setTipo_cambio($sanitize->sanitizeFloat("Tipo_cambio"));
    $objectVO->setActivo($sanitize->sanitizeFloat("Activo"));
    
    try {
        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            if ($objectDAO->create($objectVO) > 0) {
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
            if ($objectDAO->update($objectVO)) {                                            
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en bancos: " . $ex);
    } finally {
        header("Location: $Return");
    }
}


if ($request->hasAttribute("op")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $cId = $sanitize->sanitizeInt("cId");

    try {
        if ($request->getAttribute("op") === utils\Messages::OP_DELETE) {
            
            $ExiA = $mysqli->query("SELECT COUNT(*) exi FROM pagos WHERE banco = '" . $cId . "'; ");
            $ExiPagos = $ExiA->fetch_array();
            
            $ExiB = $mysqli->query("SELECT COUNT(*) exi FROM egr WHERE clave = '" . $cId . "'; ");
            $ExiEgr = $ExiB->fetch_array();
            
            if ($ExiPagos['exi'] > 0) {
                $Msj = "No se puede eliminar el banco ya que tiene pagos registrados";
            } elseif ($ExiEgr['exi'] > 0) {
                $Msj = "No se puede eliminar el banco ya que tiene registros en el cambio de turno";
            } else {
                if ($objectDAO->remove($cId)) {
                    $Msj = utils\Messages::RESPONSE_VALID_DELETE;
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en bancos: " . $ex);
    } finally {
        header("Location: $Return");
    }
}