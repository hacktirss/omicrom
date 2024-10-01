<?php

#Librerias
include_once ('data/TarjetaDAO.php');
include_once ('data/ClientesDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "tarjetas.php?";

$tarjetaDAO = new TarjetaDAO();
$clienteDAO = new ClientesDAO();

$nameVariableSession = "CatalogoCodigosClienteDetalle"; /* Utilizado en clientesService */

if ($request->hasAttribute("cVarVal")) {
    utils\HTTPUtils::setSessionBiValue($nameVariableSession, "cVarVal", $request->getAttribute("cVarVal"));
}

$cVarVal = utils\HTTPUtils::getSessionBiValue($nameVariableSession, "cVarVal");

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    $tarjetaVO = new TarjetaVO();
    $tarjetaVO->setId($sanitize->sanitizeInt("busca"));
    if (is_numeric($tarjetaVO->getId())) {
        $tarjetaVO = $tarjetaDAO->retrieve($tarjetaVO->getId());
    }

    //error_log(print_r($tarjetaVO, TRUE));
    try {
        $tarjetaVO->setCodigo($sanitize->sanitizeString("Codigo"));
        $tarjetaVO->setImpreso($sanitize->sanitizeString("Impreso"));

        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            $tarjetaVOFind = $tarjetaDAO->retrieve($sanitize->sanitizeString("Codigo"), "codigo");
            if ($tarjetaVOFind->getCodigo() === $tarjetaVO->getCodigo()) {
                $Msj = str_replace("?", "codigo", utils\Messages::REGISTER_DUPLICATE);
            } else {
                if ($tarjetaDAO->create($tarjetaVO) > 0) {
                    $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
            $tarjetaVOFind = $tarjetaDAO->retrieve($sanitize->sanitizeString("Codigo"), "codigo", $tarjetaVO->getId());
            if ($tarjetaVOFind->getCodigo() === $tarjetaVO->getCodigo()) {
                $Msj = str_replace("?", "codigo", utils\Messages::REGISTER_DUPLICATE);
            } else {
                if ($tarjetaDAO->update($tarjetaVO)) {
                    $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en unidades: " . $ex);
    } finally {
        header("Location: $Return");
    }
}

if ($request->hasAttribute("BotonD") && $request->getAttribute("BotonD") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    $tarjetaVO = new TarjetaVO();
    $tarjetaVOOLD = new TarjetaVO();
    $tarjetaVO->setId($sanitize->sanitizeInt("busca"));
    if (is_numeric($tarjetaVO->getId())) {
        $tarjetaVO = $tarjetaDAO->retrieve($tarjetaVO->getId());
        $tarjetaVOOLD = $tarjetaDAO->retrieve($tarjetaVO->getId());
    }
    //error_log(print_r($tarjetaVO, TRUE));
    try {
        if ($request->getAttribute("BotonD") === utils\Messages::OP_UPDATE) {
            $Return = "clientesd.php?";

            $ComA = $mysqli->query("SELECT id,descripcion FROM com WHERE activo='Si' ORDER BY id");
            while ($rg = $ComA->fetch_array()) {
                $Comb = 'c' . $rg['id'];
                if ($request->getAttribute($Comb)) {
                    $cNumero = $cNumero . $rg['id'];
                }
            }
            $tarjetaVO->setDescripcion($sanitize->sanitizeString("Descripcion"));
            $tarjetaVO->setPlacas($sanitize->sanitizeString("Placas"));
            $tarjetaVO->setCombustible($cNumero);
            $tarjetaVO->setNumeco($request->getAttribute("Nomeco"));
            $tarjetaVO->setLitros($sanitize->sanitizeString("Litros"));
            if ($tarjetaVO->getPeriodo() !== "B") {
                $ImpB = $sanitize->sanitizeString("Periodo") === "B" ? 0 : $sanitize->sanitizeString("Importe");
                $tarjetaVO->setImporte($ImpB);
            }
            $tarjetaVO->setEstado($sanitize->sanitizeString("Estado"));
            $tarjetaVO->setNip($sanitize->sanitizeString("Nip"));
            if (!($tarjetaVO->getPeriodo() === "B")) {
                $tarjetaVO->setPeriodo($sanitize->sanitizeString("Periodo"));
            }
            $tarjetaVO->setSimultaneo($sanitize->sanitizeString("Simultaneo"));

            $tarjetaVO->setDomi($sanitize->sanitizeString("DomI"));
            $tarjetaVO->setDomf($sanitize->sanitizeString("DomF"));
            $tarjetaVO->setLuni($sanitize->sanitizeString("LunI"));
            $tarjetaVO->setLunf($sanitize->sanitizeString("LunF"));
            $tarjetaVO->setMari($sanitize->sanitizeString("MarI"));
            $tarjetaVO->setMarf($sanitize->sanitizeString("MarF"));
            $tarjetaVO->setMiei($sanitize->sanitizeString("MieI"));
            $tarjetaVO->setMief($sanitize->sanitizeString("MieF"));
            $tarjetaVO->setJuei($sanitize->sanitizeString("JueI"));
            $tarjetaVO->setJuef($sanitize->sanitizeString("JueF"));
            $tarjetaVO->setViei($sanitize->sanitizeString("VieI"));
            $tarjetaVO->setVief($sanitize->sanitizeString("VieF"));
            $tarjetaVO->setSabi($sanitize->sanitizeString("SabI"));
            $tarjetaVO->setSabf($sanitize->sanitizeString("SabF"));

            if ($tarjetaDAO->update($tarjetaVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "EDITA DETALLE DE UNIDAD " . $tarjetaVO->getId() . " CLIENTE [" . $tarjetaVO->getCliente() . "] Valores Anteriores :" . $tarjetaVOOLD->__toString() . " | Valores actuales :" . $tarjetaVO->__toString());
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en unidades: " . $ex);
    } finally {
        header("Location: $Return");
    }
}


if ($request->hasAttribute("op")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $cId = $sanitize->sanitizeInt("cId");

    try {
        if ($request->getAttribute("op") === utils\Messages::OP_DELETE) {
            $ExiA = $mysqli->query("SELECT cliente FROM unidades WHERE id = '" . $cId . "'; ");
            $Exi = $ExiA->fetch_array();

            if ($Exi['cliente'] > 0) {
                $Msj = "No se puede borrar ya que tiene un cliente asociado";
            } else {
                if ($tarjetaDAO->remove($cId)) {
                    $Msj = utils\Messages::RESPONSE_VALID_DELETE;
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            }
        } elseif ($request->getAttribute("op") === utils\Messages::OP_FREE) {
            $Return = "clientesd.php?";

            $tarjetaVO = $tarjetaDAO->retrieve($cId);
            $tarjetaVO->setCliente(0);
            $tarjetaVO->setDescripcion("-");
            $tarjetaVO->setPlacas(0);
            $tarjetaVO->setEstado("d");
            $tarjetaVO->setLitros(0);
            $tarjetaVO->setImporte(0);

            if ($tarjetaDAO->update($tarjetaVO)) {
                $Msj = "La unidad o código ha quedo liberado y puede ser reasignado";
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("op") === utils\Messages::OP_SELECT) {
            $Return = "clientesd.php?";

            $tarjetaVO = $tarjetaDAO->retrieve($cId);
            $tarjetaVO->setCliente($cVarVal);

            if ($tarjetaDAO->update($tarjetaVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en unidades: " . $ex);
    } finally {
        header("Location: $Return");
    }
}