<?php

include_once ("data/PagosPrvDAO.php");
include_once ("data/CxpDAO.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();

$Return = "pagosprvd.php?";

$nameVariableSession = "CatalogoPagosProveedorDetalle";

$ciaDAO = new CiaDAO();
$pagosPrvDAO = new PagosPrvDAO();
$cxpDAO = new CxpDAO();

if ($request->hasAttribute("cVarVal")) {
    utils\HTTPUtils::setSessionBiValue($nameVariableSession, "cVarVal", $request->getAttribute("cVarVal"));
}

$cVarVal = utils\HTTPUtils::getSessionBiValue($nameVariableSession, "cVarVal");

$selectPagoPrv = "SELECT pagosprv.*,prv.proveedorde FROM pagosprv,prv WHERE pagosprv.proveedor = prv.id AND pagosprv.id = '$cVarVal'";
$PagoPrv = utils\IConnection::execSql($selectPagoPrv);

if ($request->hasAttribute("Entrada") && $request->hasAttribute("Imp")) {
    try {
        $Importe = $sanitize->sanitizeFloat("Imp");
        $Entrada = $sanitize->sanitizeInt("Entrada");

        $selectTotal = "SELECT IFNULL(SUM(pagosprvd.importe),0) imp,pagosprv.importe pago,
                    IFNULL(ROUND((pagosprv.importe - SUM(pagosprvd.importe)),2),0) dif
                    FROM pagosprv 
                    LEFT JOIN pagosprvd ON pagosprv.id = pagosprvd.id 
                    WHERE pagosprv.id = '$cVarVal'";
        $Tot = utils\IConnection::execSql($selectTotal);

        if ($Importe <= $Tot[dif] || $Tot[dif] == 0) {
            if ($Importe <= $Tot[pago]) {
                $Total = $Importe;
            } else {
                $Total = $Importe - ($Importe - $Tot[pago]);
            }
        } else {
            $Total = $Tot[dif];
        }

        $insertRegistro = "INSERT INTO pagosprvd (id,factura,importe) 
                        VALUES ('$cVarVal','$Entrada','$Total');";
        if (!($mysqli->query($insertRegistro))) {
            error_log($mysqli->error);
            error_log($insertRegistro);
        }

        $cxpVO = new CxpVO();
        $cxpVO->setProveedor($PagoPrv["proveedor"]);
        $cxpVO->setReferencia($Entrada);
        $cxpVO->setFecha($PagoPrv[fecha]);
        $cxpVO->setFechav($PagoPrv[fecha]);
        $cxpVO->setTm("H");
        $cxpVO->setConcepto("Pago de Compra No. $Entrada recibo $cVarVal");
        $cxpVO->setCantidad(0);
        $cxpVO->setImporte($Total);
        $cxpVO->setNumpago($cVarVal);

        if (($id = $cxpDAO->create($cxpVO)) > 0) {
            $Msj = utils\Messages::RESPONSE_VALID_CREATE;
        } else {
            $Msj = utils\Messages::RESPONSE_ERROR;
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en pagos proveedores: " . $ex);
    } finally {
        header("Location: $Return");
    }
}
if ($request->hasAttribute("op")) {

    $Return = "pagosprvd.php?";
    $cId = $sanitize->sanitizeInt("cId");

    try {
        if ($request->getAttribute("op") === utils\Messages::OP_DELETE) {
            $selectCompra = "SELECT factura FROM pagosprvd WHERE idnvo = '$cId'";
            $Referencia = utils\IConnection::execSql($selectCompra);

            $deleteRegistro = "UPDATE pagosprvd SET id = -id,factura = -factura WHERE idnvo = '$cId'";

            $deleteCxp = "UPDATE cxp SET numpago = -numpago,referencia = -referencia,proveedor = -proveedor 
                          WHERE numpago = '$cVarVal' AND referencia = '$Referencia[factura]' AND tm='H';";

            if (($mysqli->query($deleteCxp)) && ($mysqli->query($deleteRegistro))) {
                $Msj = utils\Messages::RESPONSE_VALID_DELETE;
            } else {
                error_log($mysqli->error);
                error_log($deleteCxp);
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("op") === utils\Messages::OP_CLOSE) {
            $Return = "pagosprv.php?";
            $pagoPrvVO = $pagosPrvDAO->retrieve($cVarVal);
            $pagoPrvVO->setStatus(StatusPagoProveedor::CERRADO);
            if ($pagosPrvDAO->update($pagoPrvVO)) {
                $Msj = utils\Messages::MESSAGE_DEFAULT;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        }

        if ($Return != NULL) {
            $Return .= "&Msj=" . urlencode($Msj);
        }
    } catch (Exception $ex) {
        error_log("Error en pagos proveedores: " . $ex);
    } finally {
        if ($Return != NULL) {
            header("Location: " . $Return);
        }
    }
}