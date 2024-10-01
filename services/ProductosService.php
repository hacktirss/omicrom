<?php

#Librerias
include_once ('data/ProductoDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "productos.php?";

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $productoDAO = new ProductoDAO();

    $productoVO = new ProductoVO();
    $productoVOLD = new ProductoVO();
    $productoVO->setId($sanitize->sanitizeInt("busca"));
    if (is_numeric($productoVO->getId())) {
        $productoVO = $productoDAO->retrieve($productoVO->getId());
        $productoVOLD = $productoDAO->retrieve($productoVO->getId());
    }
    $productoVO->setDescripcion($sanitize->sanitizeString("Descripcion"));
    $productoVO->setUmedida($sanitize->sanitizeString("cumedida"));
    $productoVO->setActivo($sanitize->sanitizeString("Activo"));
    $productoVO->setRubro($sanitize->sanitizeString("cRubro"));
    $productoVO->setFactorIva($sanitize->sanitizeString("FactorIva"));
    $productoVO->setCategoria($sanitize->sanitizeString("Categoria"));
    if ($usuarioSesion->getTeam() === UsuarioPerfilDAO::PERFIL_ADMIN) {
        $productoVO->setExistencia($sanitize->sanitizeInt("Existencia"));
    }
    $productoVO->setMinimo($sanitize->sanitizeInt("Minimo"));
    $productoVO->setMaximo($sanitize->sanitizeInt("Maximo"));
    $productoVO->setPrecio($sanitize->sanitizeFloat("Precio"));
    $productoVO->setCodigo($sanitize->sanitizeString("Codigo"));
    $productoVO->setNcc_vt($sanitize->sanitizeString("Ncc_vt"));
    $productoVO->setNcc_cv($sanitize->sanitizeString("Ncc_cv"));
    $productoVO->setNcc_al($sanitize->sanitizeString("Ncc_al"));
    $productoVO->setInv_cunidad($sanitize->sanitizeString("cumedida"));
    $productoVO->setInv_cproducto($sanitize->sanitizeString("common_claveps"));
    $productoVO->setDlls(0);
    $productoVO->setClave_producto($sanitize->sanitizeString("Clave_producto"));
    $productoVO->setRetiene_iva($sanitize->sanitizeString("Retiene_iva"));
    $productoVO->setPorcentaje($sanitize->sanitizeInt("Porcentaje"));

    $productoVO->setCosto($sanitize->sanitizeFloat("UltimoCosto"));

    error_log(print_r($productoVO, TRUE));
    try {
        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            if (($id = $productoDAO->create($productoVO)) > 0) {
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "Se agrega el producto " . $productoVO->getDescripcion() . " ID: " . $id);
                modificarDetalle($id);
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
            BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "Actualiza producto de " . $productoVOLD->__toString() . " a " . $productoVO->__toString());
            if ($productoDAO->update($productoVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                modificarDetalle($productoVO->getId());
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error: " . $ex);
    } finally {
        header("Location: $Return");
    }
}

if ($request->hasAttribute("BotonD") && $request->getAttribute("BotonD") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Return = "productose.php?";
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    $cId = $sanitize->sanitizeInt("InvIslaPos");
    $minimo = $sanitize->sanitizeInt("Minimo");
    $maximo = $sanitize->sanitizeInt("Maximo");

    $updateInvd = "UPDATE invd SET minimo = $minimo, maximo = $maximo WHERE idnvo = $cId LIMIT 1;";

    try {
        if ($request->getAttribute("BotonD") === utils\Messages::OP_UPDATE) {
            if ($mysqli->query($updateInvd)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error: " . $ex);
    } finally {
        header("Location: $Return");
    }
}

if ($request->hasAttribute("op")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $productoDAO = new ProductoDAO();
    $cId = $sanitize->sanitizeInt("cId");

    try {
        if ($request->getAttribute("op") === utils\Messages::OP_DELETE) {

            $ExiA = $mysqli->query("SELECT COUNT(*) exi FROM vtaditivos WHERE clave = '" . $cId . "'; ");
            $Exi = $ExiA->fetch_array();

            if ($Exi['exi'] > 0) {
                $Msj = "No se puede borrar el producto ya que tiene ventas asociadas";
            } else {
                if ($productoDAO->remove($cId)) {
                    $mysqli->query("DELETE FROM invd WHERE id = '" . $cId . "'; ");
                    $Msj = utils\Messages::RESPONSE_VALID_DELETE;
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en productos: " . $ex);
    } finally {
        header("Location: $Return");
    }
}

/**
 * 
 * @param int $producto
 * @return boolean
 */
function modificarDetalle($producto) {
    $mysqli = iconnect();

    $query = "
            INSERT INTO invd (id,isla_pos,modificacion)
            SELECT inv.id,man.dispensario,NOW() fecha
            FROM man 
            LEFT JOIN inv ON TRUE AND inv.rubro = 'Aceites' 
            AND inv.activo = 'Si' AND inv.id = $producto
            WHERE man.activo = 'Si' AND man.inventario = 'Si'
            ORDER BY man.dispensario
            ON DUPLICATE KEY UPDATE 
            modificacion = NOW();";

    if ($mysqli->query($query)) {
        error_log("Afected rows: " . $mysqli->affected_rows);
        return true;
    }
    error_log($mysqli->error);
    return false;
}
