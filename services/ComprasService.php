<?php

#Librerias
include_once ('data/ComprasDAO.php');
include_once ('data/ComprasdDAO.php');
include_once ('data/ProveedorDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "compras.php?";

$comprasDAO = new ComprasDAO();
$comprasdDAO = new ComprasdDAO();
$proveedorDAO = new ProveedorDAO();

if ($request->hasAttribute("id")) {
    $returnLink = urlencode("comprase.php?");
    $backLink = urlencode("compras.php?criteria=ini");
    header("Location: proveedores.php?criteria=ini&backLink=$backLink&returnLink=$returnLink");
}

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $busca = $sanitize->sanitizeInt("busca");
    //error_log(print_r($bancosVO, TRUE));
    try {
        $comprasVO = new ComprasVO();
        if (is_numeric($busca)) {
            $comprasVO = $comprasDAO->retrieve($busca);
        }
        $comprasVO->setFecha($sanitize->sanitizeString("Fecha"));
        $comprasVO->setConcepto($sanitize->sanitizeString("Concepto"));
        $comprasVO->setDocumento($sanitize->sanitizeString("Documento"));
        $comprasVO->setProveedor($sanitize->sanitizeInt("Proveedor"));
        $comprasVO->setImportesin($sanitize->sanitizeFloat("Importesin"));
        $comprasVO->setIva($sanitize->sanitizeFloat("Iva"));
        $comprasVO->setUuid($sanitize->sanitizeString("Uuid"));
        $comprasVO->setObservaciones($sanitize->sanitizeString("Observaciones"));

        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {

            if (($id = $comprasDAO->create($comprasVO)) > 0) {
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                $Return = "comprasd.php?criteria=ini&cVarVal=" . $id;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {

            if (($comprasDAO->update($comprasVO))) {
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
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
    //$bancosDAO = new BancosDAO();
    $cId = $sanitize->sanitizeInt("cId");

    try {
        if ($request->getAttribute("op") === utils\Messages::OP_DELETE) {

            $comprasVO = $comprasDAO->retrieve($cId);
            if ($comprasVO->getStatus() === StatusCompra::CERRADO) {
                $comprasVO->setCantidad(0);
                $comprasVO->setImporte(0);
                $comprasVO->setImportesin(0);
                $comprasVO->setIva(0);
                $comprasVO->setStatus(StatusCompra::CANCELADO);

                if ($comprasDAO->update($comprasVO)) {

                    $updateInv = "UPDATE inv,etd  
                                SET inv.existencia = inv.existencia - etd.cantidad
                                WHERE inv.id = etd.producto AND etd.id = $cId AND etd.cantidad > 0";
                    if (!($mysqli->query($updateInv))) {
                        error_log($mysqli->error);
                    }

                    $updateEtd = "UPDATE etd SET id = -id, cantidad = 0 WHERE id = $cId";
                    $updateVtaditivos = "UPDATE vtaditivos SET cantidad = 0,enviado = 0, total = 0 WHERE referencia = $cId AND tm = 'H'";

                    if (($mysqli->query($updateEtd)) && ($mysqli->query($updateVtaditivos))) {
                        $Msj = utils\Messages::RESPONSE_VALID_CANCEL;
                        BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "CANCELACION DE COMPRA " . $cId);
                    } else {
                        $Msj = utils\Messages::RESPONSE_ERROR;
                    }
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            } else if ($comprasVO->getStatus() === StatusCompra::ABIERTO) {
                $comprasVO->setCantidad(0);
                $comprasVO->setImporte(0);
                $comprasVO->setImportesin(0);
                $comprasVO->setIva(0);
                $comprasVO->setStatus(StatusCompra::CANCELADO);

                if ($comprasDAO->update($comprasVO)) {

                    $updateEtd = "UPDATE etd SET id = -id, cantidad = 0 WHERE id='$cId'";

                    if (($mysqli->query($updateEtd))) {
                        $Msj = utils\Messages::RESPONSE_VALID_CANCEL;
                    } else {
                        $Msj = utils\Messages::RESPONSE_ERROR;
                    }
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en pagos: " . $ex);
    } finally {
        header("Location: $Return");
    }
}