<?php

#Librerias
include_once ("data/VendedorDAO.php");
include_once ("data/BancosDAO.php");
include_once ("data/IslaDAO.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "vendedores.php?";

if ($request->hasAttribute("returnLink")) {
    $Return = $request->getAttribute("returnLink") . "?";
}

$islaDAO = new IslaDAO();
$bancosDAO = new BancosDAO();

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $vendedorDAO = new VendedorDAO();

    $vendedorVO = new VendedorVO();
    $vendedorVO->setId($sanitize->sanitizeInt("busca"));
    if (is_numeric($vendedorVO->getId())) {
        $vendedorVO = $vendedorDAO->retrieve($vendedorVO->getId());
    }
    $vendedorVO->setNombre($request->getAttribute("Nombre"));
    $vendedorVO->setAlias($request->getAttribute("Alias"));
    $vendedorVO->setNum_empleado($request->getAttribute("NumeroEmpleado"));
    $vendedorVO->setDireccion("-");
    $vendedorVO->setColonia("-");
    $vendedorVO->setMunicipio("-");
    $vendedorVO->setTelefono("-");
    $vendedorVO->setNcc($sanitize->sanitizeString("Ncc"));
    $vendedorVO->setNip($sanitize->sanitizeString("Nip"));
    $vendedorVO->setActivo($sanitize->sanitizeString("Activo"));
    //error_log(print_r($vendedorVO, TRUE));
    try {
        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            if (($id = $vendedorDAO->create($vendedorVO)) > 0) {
                $vendedorVO->setId($id);
                validaBancoVendedor($vendedorVO);
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
            if ($vendedorDAO->update($vendedorVO)) {
                validaBancoVendedor($vendedorVO);
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === "Guardar cambios") {
            error_log(print_r($request, TRUE));

            $DespachadorSig = $request->getAttribute("DespachadorSig");
            foreach ($request->getAttribute("Islas") as $key => $isla) {
                error_log("Isla_pos[$key]: " . $isla);
                if (count($DespachadorSig) > 0 && !empty($DespachadorSig[$key])) {
                    $Valor = $request->getAttribute("Despachador" . $isla);
                    $Siguiente = $DespachadorSig[$key];

                    $updateMan = "UPDATE man SET ";
                    $updateMan .= (!empty($Valor)) ? "despachador='$Valor'," : "";
                    $updateMan .= " despachadorsig='$Siguiente' WHERE isla_pos = '$isla'";

                    if ($mysqli->query($updateMan)) {
                        $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                    } else {
                        error_log($mysqli->error);
                        $Msj = utils\Messages::RESPONSE_ERROR;
                    }
                }
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en vendedores: " . $ex);
    } finally {
        header("Location: $Return");
    }
}

if ($request->hasAttribute("BotonReasigna")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $islaVO = $islaDAO->retrieve(1, "isla");
    $Corte = $islaVO->getCorte();

    try {
        //error_log(print_r($request, TRUE));

        $Isla = $request->getAttribute("BotonReasigna");
        $Vendedor = $request->getAttribute("Despachador" . $Isla);
        error_log("Cambiar venta de vendedor: " . $Vendedor);

        if (!empty($Vendedor)) {
            $update = true;
            $updateMan = "UPDATE man SET despachador = $Vendedor WHERE isla_pos = $Isla";

            if (!($mysqli->query($updateMan))) {
                error_log($mysqli->error);
                error_log($updateMan);
                $Msj = utils\Messages::RESPONSE_ERROR;
                $update = false;
            }

            if ($update) {
                $updateRm = "
                            UPDATE rm,man 
                            SET rm.vendedor = $Vendedor 
                            WHERE man.isla_pos = $Isla AND man.posicion = rm.posicion
                            AND rm.corte = $Corte";
                $updateVtaditivos = "
                            UPDATE vtaditivos,man 
                            SET vtaditivos.vendedor = $Vendedor 
                            WHERE vtaditivos.posicion = man.posicion AND man.isla_pos = $Isla 
                            AND vtaditivos.corte = $Corte AND vtaditivos.tm = 'C';";
                $updateCtdep = "
                            UPDATE ctdep,man 
                            SET ctdep.despachador = $Vendedor 
                            WHERE ctdep.posicion = man.posicion AND man.isla_pos = $Isla
                            AND ctdep.corte = $Corte";

                if (($mysqli->query($updateRm)) && ($mysqli->query($updateVtaditivos)) && ($mysqli->query($updateCtdep))) {
                    $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                } else {
                    error_log($mysqli->error);
                    error_log($updateRm);
                    rror_log($updateVtaditivos);
                    rror_log($updateCtdep);
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } else {
            $Msj = "Favor de seleccionar un vendedor valido";
        }
        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en vendedores: " . $ex);
    } finally {
        header("Location: $Return");
    }
}

if ($request->hasAttribute("BotonRevertir")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $islaVO = $islaDAO->retrieve(1, "isla");
    $Corte = $islaVO->getCorte();

    try {
        error_log(print_r($request, TRUE));

        $Isla = $request->getAttribute("BotonRevertir");

        $update = true;

        $updateMan = "UPDATE man SET despachador = posicion WHERE isla_pos = $Isla";

        if ($Corte == $islaVO->getCorte()) {
            if (!($mysqli->query($updateMan))) {
                error_log($mysqli->error);
                error_log($updateMan);
                $Msj = utils\Messages::RESPONSE_ERROR;
                $update = false;
            }
        }

        if ($update) {
            $updateRm = "
                        UPDATE rm,man 
                        SET rm.vendedor = rm.posicion
                        WHERE man.isla_pos = $Isla AND man.posicion = rm.posicion
                        AND rm.corte = $Corte";
            $updateVtaditivos = "
                        UPDATE vtaditivos,man 
                        SET vtaditivos.vendedor = vtaditivos.posicion 
                        WHERE vtaditivos.posicion = man.posicion AND man.isla_pos = $Isla 
                        AND vtaditivos.corte = $Corte AND vtaditivos.tm = 'C';";
            $updateCtdep = "
                        UPDATE ctdep,man 
                        SET ctdep.despachador = ctdep.posicion 
                        WHERE ctdep.posicion = man.posicion AND man.isla_pos = $Isla
                        AND ctdep.corte = $Corte";

            if (($mysqli->query($updateRm)) && ($mysqli->query($updateVtaditivos)) && ($mysqli->query($updateCtdep))) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
            } else {
                error_log($mysqli->error);
                error_log($updateRm);
                rror_log($updateVtaditivos);
                rror_log($updateCtdep);
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } else {
            $Msj = utils\Messages::RESPONSE_ERROR;
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en vendedores: " . $ex);
    } finally {
        header("Location: $Return");
    }
}

if ($request->hasAttribute("op")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $vendedorDAO = new VendedorDAO();
    $cId = $sanitize->sanitizeInt("cId");

    try {
        if ($request->getAttribute("op") === utils\Messages::OP_DELETE) {
            $ExiA = $mysqli->query("SELECT COUNT(*) exi FROM ctdep WHERE despachador='" . $cId . "'; ");
            $ExiCt = $ExiA->fetch_array();

            $ExiB = $mysqli->query("SELECT COUNT(*) exi FROM rm WHERE vendedor='" . $cId . "'; ");
            $ExiRm = $ExiB->fetch_array();

            if ($ExiCt['exi'] > 0) {
                $Msj = "No es posible eliminar el vendedor, existen colectas asociadas";
            } elseif ($ExiRm['exi'] > 0) {
                $Msj = "No es posible eliminar el vendedor, tiene ventas registradas";
            } else {
                if ($vendedorDAO->remove($cId)) {
                    $Msj = utils\Messages::RESPONSE_VALID_DELETE;
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en vendedores: " . $ex);
    } finally {
        header("Location: $Return");
    }
}

/**
 * 
 * @global BancosDAO $bancosDAO
 * @param VendedorVO $objectVO
 */
function validaBancoVendedor($objectVO) {
    global $bancosDAO;

    $selectBancoVendedore = "SELECT * FROM bancos WHERE "
            . "rubro = '" . RubroBanco::VENDEDORES . "' AND cuenta = '" . $objectVO->getId() . "'";

    $bancosVO = $bancosDAO->getAll($selectBancoVendedore);
    if (count($bancosVO) > 0 && $bancosVO[0] instanceof BancosVO) {
        $bancoVO = $bancosVO[0];
        $bancoVO->setBanco($objectVO->getNombre());
        $bancosDAO->update($bancoVO);
    } else {
        $bancoVO = new BancosVO();
        $bancoVO->setBanco($objectVO->getNombre());
        $bancoVO->setRubro(RubroBanco::VENDEDORES);
        $bancoVO->setCuenta($objectVO->getId());
        $bancoVO->setConcepto("FALTANTES");
        $bancoVO->setActivo(StatusBanco::ACTIVO);
        $bancosDAO->create($bancoVO);
    }
}
