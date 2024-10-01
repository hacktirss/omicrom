<?php

#Librerias
include_once ('data/ProveedorDAO.php');
include_once ('data/CiaDAO.php');
include_once ('data/DireccionDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "proveedores.php?";

$proveedorDAO = new ProveedorDAO();

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    $proveedorVO = new ProveedorVO();
    $proveedorVO->setId($sanitize->sanitizeInt("busca"));
    if (is_numeric($proveedorVO->getId())) {
        $proveedorVO = $proveedorDAO->retrieve($proveedorVO->getId());
    }
    $proveedorVO->setNombre($sanitize->sanitizeString("Nombre"));
    $proveedorVO->setAlias($sanitize->sanitizeString("Alias"));
    $proveedorVO->setRfc($sanitize->sanitizeString("Rfc"));
    $proveedorVO->setDireccion($sanitize->sanitizeString("Direccion"));
    $proveedorVO->setNumeroext($sanitize->sanitizeString("Numeroext"));
    $proveedorVO->setNumeroint($sanitize->sanitizeString("Numeroint"));
    $proveedorVO->setColonia($sanitize->sanitizeString("Colonia"));
    $proveedorVO->setMunicipio($sanitize->sanitizeString("Municipio"));
    $proveedorVO->setTelefono($sanitize->sanitizeString("Telefono"));
    $proveedorVO->setCodigo($sanitize->sanitizeString("Codigo"));
    $proveedorVO->setCorreo($sanitize->sanitizeEmail("Correo"));
    $proveedorVO->setContacto($sanitize->sanitizeString("Contacto"));
    $proveedorVO->setNcc($sanitize->sanitizeString("Ncc"));
    $proveedorVO->setBanco($sanitize->sanitizeString("Banco"));
    $proveedorVO->setCuenta($sanitize->sanitizeString("Cuenta"));
    $proveedorVO->setClabe($sanitize->sanitizeString("Clabe"));
    $proveedorVO->setPermisoCRE($sanitize->sanitizeString("PermisoCRE"));
    $proveedorVO->setTipodepago($sanitize->sanitizeString("Tipodepago"));
    $proveedorVO->setDias_credito($sanitize->sanitizeInt("Dias_credito"));
    $proveedorVO->setProveedorde($sanitize->sanitizeString("Proveedorde"));
    $proveedorVO->setTipoProveedor($sanitize->sanitizeString("TipoProveedor"));

    $proveedorVO->setLimite(0); // not use
    $proveedorVO->setObservaciones(""); //not use
    $proveedorVO->setCuentaban($sanitize->sanitizeString("Cuenta")); //not use
    //error_log(print_r($proveedorVO, TRUE));
    try {
        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            if ($proveedorDAO->create($proveedorVO) > 0) {
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
            if ($proveedorDAO->update($proveedorVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en pagos: " . $ex);
    } finally {
        header("Location: $Return");
    }
}
if ($request->hasAttribute("Boton2") && $request->getAttribute("Boton2") !== utils\Messages::OP_NO_OPERATION_VALID) {
    try {
        $busca = $sanitize->sanitizeInt("busca");
        $objectDAO = new DireccionDAO();
        $objectVO = new DireccionVO();

        $objectVO = $objectDAO->retrieve($busca, "id_origen", " AND tabla_origen = 'P'");
        $objectVO->setDescripcion($request->getAttribute("DescripcionCP"));
        $objectVO->setCalle($request->getAttribute("CalleCP"));
        $objectVO->setNum_exterior($request->getAttribute("ExtCP"));
        $objectVO->setNum_interior($request->getAttribute("IntCP"));
        $objectVO->setEstado($request->getAttribute("EstadoCP"));
        $objectVO->setMunicipio($request->getAttribute("MunicipioCP"));
        $objectVO->setLocalidad($request->getAttribute("LocalidadCP"));
        $objectVO->setCodigo_postal($request->getAttribute("CodigoPostalCP"));
        $objectVO->setColonia($request->getAttribute("ColoniaCP"));
        if ($objectVO->getId() > 0) {
            $objectDAO->update($objectVO);
        } else {
            $objectVO->setTabla_origen("P");
            $objectVO->setId_origen($busca);
            $objectDAO->create($objectVO);
        }
    } catch (Exception $ex) {
        error_log("Error en pagos: " . $ex);
    } finally {
        header("Location: $Return");
    }
}

if ($request->hasAttribute("op")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $proveedorDAO = new ProveedorDAO();
    $cId = $sanitize->sanitizeInt("cId");

    try {
        if ($request->getAttribute("op") === utils\Messages::OP_DELETE) {

            $ExiMe = $mysqli->query("SELECT COUNT(*) exi FROM me WHERE proveedor = '" . $cId . "'; ")->fetch_array();
            $ExiEt = $mysqli->query("SELECT COUNT(*) exi FROM et WHERE proveedor = '" . $cId . "'; ")->fetch_array();
            $ExiEto = $mysqli->query("SELECT COUNT(*) exi FROM eto WHERE proveedor = '" . $cId . "'; ")->fetch_array();

            if ($ExiMe['exi'] > 0) {
                $Msj = "No se puede borrar el proveedor ya que tiene entrada de pipas asociadas";
            } elseif ($ExiEt['exi'] > 0 || $ExiEto['exi'] > 0) {
                $Msj = "No se puede borrar el proveedor ya que tiene compras asociadas";
            } else {
                if ($proveedorDAO->remove($cId)) {
                    $Msj = utils\Messages::RESPONSE_VALID_DELETE;
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en proveedores: " . $ex);
    } finally {
        header("Location: $Return");
    }
}