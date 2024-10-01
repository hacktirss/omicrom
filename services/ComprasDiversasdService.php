<?php

#Librerias
include_once ('data/ComprasoeDAO.php');
include_once ('data/ProveedorDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "comprasod.php?";

$comprasoeDAO = new ComprasoeDAO();
$proveedorDAO = new ProveedorDAO();
$ciaDAO = new CiaDAO();

$ciaVO = $ciaDAO->retrieve(1);

$nameVariableSession = "CatalogoComprasDiversasDetalle";
if ($request->hasAttribute("cVarVal")) {
    utils\HTTPUtils::setSessionBiValue($nameVariableSession, "cVarVal", $request->getAttribute("cVarVal"));
}
$cVarVal = utils\HTTPUtils::getSessionBiValue($nameVariableSession, "cVarVal");

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    try {
        $clave = $sanitize->sanitizeString("Clave");
        $concepto = $sanitize->sanitizeString("Concepto");
        $costo = $sanitize->sanitizeFloat("Costo");

        //error_log(print_r($request, TRUE));

        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            $insertEtod = "INSERT INTO etod (id,clave,concepto,costo) 
                        VALUES ('$cVarVal','$clave','$concepto','$costo')";

            if ($mysqli->query($insertEtod)) {
                Totaliza($cVarVal, $ciaVO);
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
    $cId = $sanitize->sanitizeInt("cId");

    try {
        if ($request->getAttribute("op") === utils\Messages::OP_DELETE) {
            $deleteEtod = "DELETE FROM etod WHERE idnvo='$cId' LIMIT 1";
            if ($mysqli->query($deleteEtod)) {
                Totaliza($cVarVal, $ciaVO);
                $Msj = utils\Messages::RESPONSE_VALID_DELETE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("op") === utils\Messages::OP_CLOSE) {

            $comprasoeVO = new ComprasoeVO();
            if (is_numeric($cVarVal)) {
                $comprasoeVO = $comprasoeDAO->retrieve($cVarVal);
            }
            $comprasoeVO->setStatus("Cerrada");
            if ($comprasoeDAO->update($comprasoeVO)) {

                $insertCxp = "INSERT INTO cxp (proveedor,referencia,fecha,fechav,tm,concepto,cantidad,importe) VALUES
                       ('" . $comprasoeVO->getProveedor() . "','$cVarVal',DATE('" . $comprasoeVO->getFecha() . "'),'" . $comprasoeVO->getFechav() . "',
                       'C','" . $comprasoeVO->getConcepto() . "','" . $comprasoeVO->getCantidad() . "','" . $comprasoeVO->getImporte() . "')";
                error_log($insertCxp);
                if ($mysqli->query($insertCxp)) {
                    $Return = "compraso.php?";
                    $Msj = utils\Messages::MESSAGE_CLOSE;
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
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

/**
 * 
 * @param int $busca
 * @param CiaVO $ciaVO
 */
function Totaliza($busca, $ciaVO) {
    global $mysqli;
    $ivaCia = $ciaVO->getIva() / 100;

    $selectSum = "SELECT SUM(costo) costo FROM etd WHERE id=$busca";
    $Ddd = utils\IConnection::execSql($selectSum);

    $Cnt = 0;
    if ($Ddd[0] == 0) {
        $Importe = 0;
        $Iva = 0;
    } else {
        $Importe = $Ddd[0];
        $Iva = $Ddd[0] * $ivaCia;
    }

    $updateEto = "UPDATE eto SET cantidad=$Cnt,iva = ROUND($Iva,2) WHERE id = $busca";

    if (!($mysqli->query($updateEto))) {
        error_log($mysqli->error);
        error_log($updateEto);
    }
}
