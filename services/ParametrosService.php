<?php

#Librerias
include_once ('data/CiaDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();

if ($request->hasAttribute("Boton")) {
    $ciaDAO = new CiaDAO();
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $Return = "parametros.php?";

    try {
        if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {

            if ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
                $objectVO = $ciaDAO->retrieve(1);

                $objectVO->setCia($sanitize->sanitizeString("Cia"));
                $objectVO->setRfc($sanitize->sanitizeString("Rfc"));
                $objectVO->setRepresentante_legal($sanitize->sanitizeString("RepLegal"));
                $objectVO->setRfc_representante_legal($sanitize->sanitizeString("RfcRepLegal"));
                $objectVO->setDireccion($sanitize->sanitizeString("Direccion"));
                $objectVO->setNumeroext($sanitize->sanitizeString("Numeroext"));
                $objectVO->setNumeroint($sanitize->sanitizeString("Numeroint"));
                $objectVO->setColonia($sanitize->sanitizeString("Colonia"));
                $objectVO->setCiudad($sanitize->sanitizeString("Ciudad"));
                $objectVO->setEstado($sanitize->sanitizeString("Estado"));
                $objectVO->setTelefono($sanitize->sanitizeString("Telefono"));
                $objectVO->setCodigo($sanitize->sanitizeString("Codigo"));
                $objectVO->setMaster($sanitize->sanitizeString("Master"));
                $objectVO->setDescripcion($sanitize->sanitizeString("Descripcion"));
                $objectVO->setDireccionexp($sanitize->sanitizeString("Direccionexp"));
                $objectVO->setNumeroextexp($sanitize->sanitizeString("Numeroextexp"));
                $objectVO->setNumerointexp($sanitize->sanitizeString("Numerointexp"));
                $objectVO->setColoniaexp($sanitize->sanitizeString("Coloniaexp"));
                $objectVO->setCiudadexp($sanitize->sanitizeString("Ciudadexp"));
                $objectVO->setEstadoexp($sanitize->sanitizeString("Estadoexp"));
                $objectVO->setCodigoexp($sanitize->sanitizeString("Codigoexp"));

                if ($ciaDAO->update($objectVO)) {
                    $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE . " Localizacion") {
                $objectVO = $ciaDAO->retrieve(1);
                $objectVO->setLatitud($sanitize->sanitizeFloat("Latitud"));
                $objectVO->setLongitud($sanitize->sanitizeFloat("Longitud"));

                if ($ciaDAO->update($objectVO)) {
                    $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE . " SAT") {
                $objectVO = $ciaDAO->retrieve(1);
                $objectVO->setCaracter_sat($sanitize->sanitizeString("Caracter_sat"));
                $objectVO->setClave_instalacion($sanitize->sanitizeString("Clave_instalacion"));
                $objectVO->setModalidad_permiso($sanitize->sanitizeString("Modalidad_permiso"));

                if ($ciaDAO->update($objectVO)) {
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
