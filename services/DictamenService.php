<?php

#Librerias
include_once ('data/DictamenDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "dictamenes.php?";
$Return = $request->hasAttribute("return") ? $request->getAttribute("return") . "?criteria=ini" : $Return;
$objectDAO = new DictamenDAO();

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $busca = $sanitize->sanitizeInt("busca");

    try {
        $objectVO = new DictamenVO();
        if (is_numeric($busca)) {
            $objectVO = $objectDAO->retrieve($busca);
        }

        $objectVO->setProveedor($sanitize->sanitizeInt("Proveedor"));
        $objectVO->setLote($sanitize->sanitizeString("Lote"));
        $objectVO->setNumerofolio($sanitize->sanitizeString("NumeroFolio"));
        $objectVO->setFechaemision($sanitize->sanitizeString("FechaEmision"));
        $objectVO->setResultado($request->getAttribute("Resultado"));
        if ($request->getAttribute("Carga") > 0) {
            $objectVO->setNoCarga($request->getAttribute("Carga"));
        } else {
            $objectVO->setNoCarga(0);
        }
        error_log("La carga bien con " . $objectVO->getNoCarga());

        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            $objectVO->setEstado(0);
            if (($id = $objectDAO->create($objectVO)) > 0) {
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                if ($request->hasAttribute("return")) {
                    $Return = $request->getAttribute("return");
                } else {
                    $Return = (utils\HTTPUtils::getSessionObject("Tipo") == 1) ? "dictamenesd.php?criteria = ini&cVarVal = $id" : "dictamenes.php?criteria = ini&Msj = $Msj&cVarVal = $id";
                }
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

        $Return .= "&Msj = " . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error: " . $ex);
    } finally {
        header("Location: $Return");
    }
}

if ($request->hasAttribute("Boton2") && $request->getAttribute("Boton2") !== utils\Messages::OP_NO_OPERATION_VALID) {
    if ($request->getAttribute("Boton2") === "Cerrar Dictamen") {
        $UPD = "UPDATE dictamen SET estado = 1 WHERE id = $busca";
        utils\IConnection::execSql($UPD);
    }
}

if ($request->hasAttribute("cVarVal")) {
    utils\HTTPUtils::setSessionBiValue($nameVariableSession, "cVarVal", $request->getAttribute("cVarVal"));
}

$cVarVal = utils\HTTPUtils::getSessionBiValue($nameVariableSession, "cVarVal");

if ($request->hasAttribute("BotonD") && $request->getAttribute("BotonD") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Return = "dictamenesd.php?";
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $busca = $sanitize->sanitizeInt("busca");

    try {
        if ($request->getAttribute("BotonD") === utils\Messages::OP_UPDATE) {

            $objectVO = $objectDAO->retrieveD($busca, "dictamend.idnvo", "com.cve_producto_sat, dictamend.tanque, dictamend.idnvo, dictamend.comp_azufre, dictamend.fraccion_molar, dictamend.poder_calorifico, "
                    . "dictamend.comp_octanaje, contiene_fosil, dictamend.comp_etanol, dictamend.gravedad_especifica, dictamend.comp_fosil, dictamend.comp_propano, cia.clave_instalacion, "
                    . "dictamend.comp_butano ");
            $Cv_Producto = $objectVO->getCve_producto_sat();
            if ($Cv_Producto === "PR03") {
                $objectVO->setComp_fosil($sanitize->sanitizeFloat("Composicion_fosil"));
            } elseif ($Cv_Producto === "PR07") {
                $objectVO->setComp_fosil($sanitize->sanitizeFloat("Composicion_fosil"));
                $objectVO->setComp_octanaje($sanitize->sanitizeFloat("Comp_octanaje"));
            } elseif ($Cv_Producto === "PR08") {
                $objectVO->setComp_azufre($sanitize->sanitizeFloat("Comp_azufre"));
                $objectVO->setGravedad_especifica($sanitize->sanitizeFloat("Gravedad_especifica"));
            } elseif ($Cv_Producto === "PR09") {
                $objectVO->setFraccion_molar($sanitize->sanitizeFloat("Fraccion_molar"));
                $objectVO->setPoder_calorifico($sanitize->sanitizeFloat("Poder_calorifico"));
            } elseif ($Cv_Producto === "PR11") {
                $objectVO->setComp_fosil($sanitize->sanitizeFloat("Composicion_fosil"));
            } elseif ($Cv_Producto === "PR12") {
                $objectVO->setComp_propano($sanitize->sanitizeFloat("Composicion_propano"));
                $objectVO->setComp_butano($sanitize->sanitizeFloat("Composicion_butano"));
            }
            error_log(print_r($sanitize, true));
            $objectVO->setContiene_fosil($sanitize->sanitizeString("Contiene_fosil"));
            if ($objectDAO->updateD($objectVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        }

        $Return .= "&Msj = " . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error: " . $ex);
    } finally {
        header("Location: $Return");
    }
}

if ($request->hasAttribute("op")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $busca = $sanitize->sanitizeInt("busca");
    $cId = $sanitize->sanitizeInt("cId");

    try {

        if ($request->getAttribute("op") === utils\Messages::OP_CLOSE) {

            $objectVO = $objectDAO->retrieve($cVarVal);
            $objectVO->setEstado(1);

            if ($objectDAO->update($objectVO)) {
                $Msj = utils\Messages::MESSAGE_DEFAULT;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("op") === utils\Messages::OP_DELETE) {
            $Return = "dictamenesd.php?";
            if ($objectDAO->removeD($cId, "idnvo")) {
                $Msj = utils\Messages::RESPONSE_VALID_DELETE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } else if ($request->getAttribute("op") === "Download") {
            $fileName = $request->getAttribute("Name");
            $filePath = '/home/omicrom/xml/' . $fileName;
            error_log($filePath);
            if (!empty($fileName) && file_exists($filePath)) {
                // Define headers
                header("Content-disposition: attachment; filename=" . $fileName . ".pdf");
                header("Content-type: MIME");
                readfile("$filePath");
                echo $myrowsel["pdf_format"];
                exit();
            } else {
                echo 'The file does not exist.';
            }
        }

        $Return .= "&Msj = " . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error: " . $ex);
    } finally {
        header("Location: $Return");
    }
}