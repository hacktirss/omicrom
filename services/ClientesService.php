<?php

#Librerias
include_once ('data/ClientesDAO.php');
include_once ('data/CiaDAO.php');
include_once ('data/UsuarioDAO.php');
include_once ('data/TarjetaDAO.php');
include_once ('data/DireccionDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
//error_log(print_r($request, TRUE));

$clienteDAO = new ClientesDAO();
$tarjetaDAO = new TarjetaDAO();

if ($request->hasAttribute("Tarjetas")) {
    $returnLink = urlencode("clientesd.php?op=Seleccionar");
    $backLink = urlencode("clientesd.php");
    header("Location: tarjetas.php?criteria=ini&backLink=$backLink&returnLink=$returnLink");
}

$nameVariableSession = "CatalogoCodigosClienteDetalle"; /* Utilizado en tarjetasService */

if ($request->hasAttribute("cVarVal")) {
    utils\HTTPUtils::setSessionBiValue($nameVariableSession, "cVarVal", $request->getAttribute("cVarVal"));
}

$cVarVal = utils\HTTPUtils::getSessionBiValue($nameVariableSession, "cVarVal");

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $Return = "clientes.php?";

    try {
        $busca = $sanitize->sanitizeInt("busca");
        $desgloseIEPS = $request->hasAttribute("DesgloseIEPS") ? $sanitize->sanitizeString("DesgloseIEPS") : "N";
        $nombreFactura = ($sanitize->sanitizeString("FAlias") === "1" && $sanitize->sanitizeString("FCuenta") === "1") ? "F" :
                ($sanitize->sanitizeString("FAlias") === "1" ? "A" : ($sanitize->sanitizeString("FCuenta") === "1" ? "C" : "N"));
        $TipoCliente = $sanitize->sanitizeString("Tipodepago");
        $clienteVO = new ClientesVO();
        $clienteVO->setUltimaModificacion(date("Y-m-d H:i:s"));
        $clienteVO->setId($sanitize->sanitizeInt("busca"));
        if (is_numeric($clienteVO->getId())) {
            if ($usuarioSesion->getLevel() < UsuarioDAO::LEVEL_MASTER) {
                $clienteVO = $clienteDAO->retrieve($clienteVO->getId());
                if ($clienteVO->getTipodepago() !== $TipoCliente) {
                    BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "INTENTO DE ACTUALIZACIÓN DE TIPO DE CLIENTE " . $clienteVO->getTipodepago() . " -> " . $TipoCliente);
                    $TipoCliente = $clienteVO->getTipodepago();
                }
            }
        }
        $clienteVO->setUltimaModificacion(date("Y-m-d H:i:s"));
        $clienteVO->setRfc(trim($request->getAttribute("Rfc")));
        $clienteVO->setNombre($request->getAttribute("Nombre"));
        $clienteVO->setAlias($request->getAttribute("Alias"));
        $clienteVO->setDireccion($sanitize->sanitizeString("Calle"));
        $clienteVO->setNumeroext($sanitize->sanitizeString("Numeroext"));
        $clienteVO->setNumeroint($sanitize->sanitizeString("Numeroint"));
        $clienteVO->setColonia($sanitize->sanitizeString("Colonia"));
        $clienteVO->setMunicipio($sanitize->sanitizeString("Municipio"));
        $clienteVO->setEstado($sanitize->sanitizeString("Estado"));
        $clienteVO->setCodigo($sanitize->sanitizeString("Codigo"));
        $clienteVO->setTelefono($sanitize->sanitizeString("Telefono"));
        $clienteVO->setFormadepago($sanitize->sanitizeString("Formadepago"));
        $clienteVO->setCuentaban($sanitize->sanitizeString("Cuentaban"));
        $clienteVO->setCorreo($sanitize->sanitizeEmail("Correo"));
        $clienteVO->setEnviarcorreo($sanitize->sanitizeString("Enviarcorreo"));
        $clienteVO->setDesgloseieps($desgloseIEPS);
        $clienteVO->setAutorizaCorporativo($sanitize->sanitizeInt("autorizaCorporativo"));
        $clienteVO->setNombrefactura($nombreFactura);
        $clienteVO->setDiasCredito($sanitize->sanitizeInt("DiasCredito"));
        $clienteVO->setCorreo2($sanitize->sanitizeString("ccCorreo"));
        $clienteVO->setFacturacion($sanitize->sanitizeInt("Facturacion"));
        $clienteVO->setRegimenFiscal($sanitize->sanitizeInt("RegimenFiscal"));
        $clienteVO->setPuntos($sanitize->sanitizeInt("PuntosPor"));
        $clienteVO->setTipoMonedero($sanitize->sanitizeString("Tipodepago") === "Monedero" ? $sanitize->sanitizeString("Tipodepago") : 0);
        if ($usuarioSesion->getTeam() !== PerfilesUsuarios::FACTURACION) {
            $clienteVO->setTipodepago($TipoCliente);
            $clienteVO->setLimite($sanitize->sanitizeString("Limite"));
            $clienteVO->setContacto($request->getAttribute("Contacto"));
            $clienteVO->setNcc($sanitize->sanitizeString("Ncc"));
//            $clienteVO->setPuntos($sanitize->sanitizeString("Puntos"));
            $clienteVO->setActivo($sanitize->sanitizeString("Activo"));
        }

//error_log(print_r($clienteVO, TRUE));

        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD || $request->getAttribute("Boton") === "Agregar como nuevo cliente") {
            if ($usuarioSesion->getTeam() !== PerfilesUsuarios::FACTURACION) {
                $clienteVO->setTipodepago($sanitize->sanitizeString("Tipodepago"));
            } else {
                $clienteVO->setTipodepago(TiposCliente::CONTADO);
            }
            $clienteVO->setPuntos(0);
            if (($id = $clienteDAO->create($clienteVO)) > 0) {
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "ALTA DE CLIENTE [$id] " . $clienteVO->getNombre());
                if ($request->hasAttribute("Facturar") && $request->getAttribute("Facturar") == 1) {
                    $Return = "facturase.php?Boton=Agregar&Cliente=" . $id . "";
                }
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
                if ($request->hasAttribute("Facturar") && $request->getAttribute("Facturar") == 1) {
                    $Return = "facturas.php?";
                }
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE || $request->getAttribute("Boton") === "Facturar") {
            $clienteVO->setPuntos($clienteVO->getPuntos() > 0 ? $clienteVO->getPuntos() : 0);
            if ($clienteDAO->update($clienteVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "ACTUALIZACION DE CLIENTE [$busca] " . $clienteVO->getNombre());
                if ($request->hasAttribute("Facturar") && $request->getAttribute("Facturar") == 1) {
                    $Return = "facturase.php?Boton=Agregar&Cliente=" . $clienteVO->getId() . "";
                }
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
                if ($request->hasAttribute("Facturar") && $request->getAttribute("Facturar") == 1) {
                    $Return = "facturas.php?";
                }
            }
        } elseif ($request->getAttribute("Boton") === "Dar acceso al sistema") {
            $usuarioDAO = new UsuarioDAO();

            $requestPassword = trim($request->getAttribute("Password"));
            $deletePwdUser = "DELETE FROM authuser_pwd WHERE id_user IN (SELECT id FROM authuser WHERE uname='$busca' AND level = 2)";
            if (!($mysqli->query($deletePwdUser))) {
                error_log($mysqli->error);
            }
            $deleteUser = "DELETE FROM authuser WHERE uname='$busca' AND level = 2";
            if (!($mysqli->query($deleteUser))) {
                error_log($mysqli->error);
            }

            $usuarioVO = new UsuarioVO();
            $usuarioVO->setNombre($clienteVO->getNombre());
            $usuarioVO->setUsername($busca);
            $usuarioVO->setPassword($requestPassword);
            $usuarioVO->setTeam("Cliente");
            $usuarioVO->setLevel(2);
            $usuarioVO->setRol(7);
            $usuarioVO->setStatus("active");
            $usuarioVO->setCreation(date("Y-m-d", strtotime(date("Y-m-d"))));
            $usuarioVO->setLastlogin("0000-00-00 00:00:00");
            $usuarioVO->setCount(0);

            if ($usuarioDAO->create($usuarioVO, false) > 0) {
                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "ACCESO DE CLIENTE [$busca] " . $clienteVO->getNombre());
                $Msj = "Se ha dado acceso al cliente. Usuario: [$busca] Contraseña: [$requestPassword]";
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en clientes: " . $ex);
    } finally {
        header("Location: $Return");
    }
}

if ($request->hasAttribute("Boton2") && $request->getAttribute("Boton2") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $Return = "clientes.php?";

    try {
        $busca = $sanitize->sanitizeInt("busca");
        $objectDAO = new DireccionDAO();
        $objectVO = new DireccionVO();

        $objectVO = $objectDAO->retrieve($busca, "id_origen", " AND tabla_origen = 'C'");
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
            $objectVO->setTabla_origen("C");
            $objectVO->setId_origen($busca);
            $objectDAO->create($objectVO);
        }
    } catch (Exception $ex) {
        error_log("Error en clientes: " . $ex);
    } finally {
        header("Location: $Return");
    }
}


if ($request->hasAttribute("op")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $Return = "clientes.php?";
    $cId = $sanitize->sanitizeInt("cId");
//error_log(print_r($request, TRUE));
    try {
        if ($request->getAttribute("op") === utils\Messages::OP_DELETE) {
            BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "BORRADO DE CLIENTE [$cId] ");
            $ExiA = $mysqli->query("SELECT COUNT(*) exi FROM fc WHERE cliente='" . $cId . "'; ");
            $ExiFc = $ExiA->fetch_array();

            $ExiB = $mysqli->query("SELECT COUNT(*) exi FROM rm WHERE cliente='" . $cId . "'; ");
            $ExiRm = $ExiB->fetch_array();

            $ExiC = $mysqli->query("SELECT COUNT(*) exi FROM cxc WHERE cliente='" . $cId . "'; ");
            $ExiCxc = $ExiC->fetch_array();

            if ($ExiFc['exi'] > 0) {
                $Msj = "No es posible eliminar el cliente, existen facturas registradas";
            } elseif ($ExiRm['exi'] > 0) {
                $Msj = "No es posible eliminar el cliente, tiene ventas registradas";
            } elseif ($ExiCxc['exi'] > 0) {
                $Msj = "No es posible eliminar el cliente, tiene movimientos en el estado de cuenta";
            } else {
                if ($clienteDAO->remove($cId)) {
                    $Msj = utils\Messages::RESPONSE_VALID_DELETE;
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            }
        }
        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en clientes: " . $ex);
    } finally {
        header("Location: $Return");
    }
}