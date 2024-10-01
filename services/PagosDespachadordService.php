<?php

include_once ("data/PagosDespDAO.php");
include_once ("data/IslaDAO.php");
include_once ("data/CxdDAO.php");
#Librerias

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "pagosdespd.php?";
//error_log(print_r($request, TRUE));

$nameVariableSession = "CatalogoPagosDespachadorDetalle";

$pagosDespDAO = new PagosDespDAO();
$islaDAO = new IslaDAO();
$cxdDAO = new CxdDAO();

if ($request->hasAttribute("cVarVal")) {
    utils\HTTPUtils::setSessionBiValue($nameVariableSession, "cVarVal", $request->getAttribute("cVarVal"));
}

$cVarVal = utils\HTTPUtils::getSessionBiValue($nameVariableSession, "cVarVal");
$islaVO = $islaDAO->retrieve(1, "isla");

if ($request->hasAttribute("Faltante")) {
    try {
        if (cargarFaltante($sanitize->sanitizeInt("Faltante"))) {
            $Msj = utils\Messages::MESSAGE_DEFAULT;
        } else {
            $Msj = utils\Messages::RESPONSE_ERROR;
        }
    } catch (Exception $ex) {
        error_log("Error en pagos: " . $ex);
    } finally {
        if (!empty($Return) && !is_null($Return)) {
            $Return .= "&Msj=" . urlencode($Msj);
            header("Location: $Return");
        }
    }
}

if ($request->hasAttribute("Faltantes")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    try {
        $Faltantes = $request->getAttribute("Faltantes");
        foreach ($Faltantes as $key => $value) {
            error_log("Faltante: " . $value);
            cargarFaltante($value);
            $Msj = utils\Messages::MESSAGE_DEFAULT;
        }
    } catch (Exception $ex) {
        error_log("Error en pagos: " . $ex);
    } finally {
        if (!empty($Return) && !is_null($Return)) {
            $Return .= "&Msj=" . urlencode($Msj);
            header("Location: $Return");
        }
    }
}


if ($request->hasAttribute("op")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $cId = $sanitize->sanitizeInt("cId");

    try {
        if ($request->getAttribute("op") === utils\Messages::OP_DELETE) {
            $updatePagosdespd = "UPDATE pagosdespd SET pago = -pago, referencia = -referencia WHERE id = $cId LIMIT 1";
            $updateCxd = "UPDATE cxd SET recibo = -recibo,referencia = -referencia,vendedor = -vendedor WHERE recibo = '$cVarVal' AND tm = 'H'";

            if ($mysqli->query($updatePagosdespd) && $mysqli->query($updateCxd)) {
                $Msj = utils\Messages::RESPONSE_VALID_CANCEL;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("op") === utils\Messages::OP_CLOSE) {
            $objectVO = $pagosDespDAO->retrieve($cVarVal);
            $objectVO->setStatus(StatusPagoDespachador::CERRADO);
            if($pagosDespDAO->update($objectVO)){
                $Return = "pagosdesp.php?";
                $Msj = utils\Messages::MESSAGE_DEFAULT;
            } else{
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

/**
 * 
 * @global int $cVarVal
 * @global PagosDespDAO $pagosDespDAO
 * @global IslaVO $islaVO
 * @global CxdDAO $cxdDAO
 * @param int $Faltante
 * @return boolean
 */
function cargarFaltante($Faltante) {
    global $cVarVal, $pagosDespDAO, $islaVO, $cxdDAO;

    $Response = false;

    $mysqli = iconnect();

    $sqlPagos = "SELECT IFNULL(SUM(pagosdespd.importe),0) imp,pagosdesp.importe pago,
                IFNULL(ROUND( (pagosdesp.importe - IFNULL(SUM(pagosdespd.importe),0) ),2),0) diferencia
                FROM pagosdesp LEFT JOIN pagosdespd ON pagosdesp.id = pagosdespd.pago
                WHERE  pagosdesp.id = '$cVarVal'";

    $Tot = utils\IConnection::execSql($sqlPagos);

    if ($Tot['diferencia'] > 0) {
        $pagosDespVO = $pagosDespDAO->retrieve($cVarVal);

        $sqlEgr = "SELECT * FROM egr WHERE id = '" . $Faltante . "'";
        $Egr = utils\IConnection::execSql($sqlEgr);

        $Importe = getSaldoEgreso($Faltante, $pagosDespVO->getVendedor());
        $Total = $Tot['diferencia'];
        if ($Importe <= $Tot['diferencia'] || $Tot['diferencia'] == 0) {
            $Total = $Importe <= $Tot['pago'] ? $Importe : $Importe - ($Importe - $Tot['pago']);
        }

        $cxdVO = crearRegistroCxd($pagosDespVO, $Egr, $Faltante, $Total);

        $sqlUpdateCXD = "UPDATE cxd SET recibo = -recibo,referencia = -referencia,vendedor = -vendedor
                        WHERE vendedor = '" . $pagosDespVO->getVendedor() . "' AND tm = 'H' 
                        AND recibo = '" . $pagosDespVO->getId() . "' AND referencia = '" . $Faltante . "';";
        if ($mysqli->query($sqlUpdateCXD)) {
            if (($cxdDAO->create($cxdVO)) > 0) {
                $pagosDespDVO = new PagosDespdVO($pagosDespVO->getId(), $Faltante, $Total);
                $Response = ($pagosDespDAO->created($pagosDespDVO)) > 0 ? utils\Messages::MESSAGE_DEFAULT : utils\Messages::RESPONSE_ERROR;
            }
        } else {
            error_log($mysqli->error . "\n" . $sqlUpdateCXD);
        }
    } else {
        $Response = "Ya se ha cubierto el importe del pago, no es posible cargar mas facturas.";
    }

    return $Response;
}

/**
 * 
 * @param PagosDespVO $pagosDespVO
 * @param array() $Egr
 * @param int $Referencia
 * @param double $Total
 * @return \CxdVO
 */
function crearRegistroCxd($pagosDespVO, $Egr, $Referencia, $Total) {

    $cxdVO = new CxdVO();
    $cxdVO->setVendedor($pagosDespVO->getVendedor());
    $cxdVO->setReferencia($Referencia);
    $cxdVO->setFecha($pagosDespVO->getDeposito() . date(" H:i:s"));
    $cxdVO->setTm(TipoMovCxd::ABONO);
    $cxdVO->setConcepto("PAGO DE FALTANTES DEL CORTE " . $Egr["corte"]);
    $cxdVO->setImporte($Total);
    $cxdVO->setRecibo($pagosDespVO->getId());
    $cxdVO->setCorte($Egr["corte"]);

    return $cxdVO;
}

/**
 * 
 * @global int $cVarVal
 * @param int $Referencia
 * @param int $Vendedor
 * @return double
 */
function getSaldoEgreso($Referencia, $Vendedor) {
    global $cVarVal;
    $Importe = 0;
    $selectSaldo = "SELECT IFNULL(SUM(sub.importe),0) saldo 
                    FROM(
                            SELECT cxc.vendedor,cxc.tm,cxc.referencia,IFNULL(pagosdespd.referencia,0) ref,
                            ROUND(SUM(IF(cxc.tm = 'H',-cxc.importe,cxc.importe)),2) importe
                            FROM cxd cxc 
                            LEFT JOIN pagosdespd ON cxc.referencia = pagosdespd.referencia AND pagosdespd.pago = $cVarVal
                            WHERE cxc.vendedor = $Vendedor
                            GROUP BY cxc.referencia,cxc.tm
                            ORDER BY cxc.referencia DESC
                    ) sub 
                    WHERE sub.ref = 0 AND sub.referencia = $Referencia
                    GROUP BY sub.referencia,sub.vendedor
                    HAVING SUM(sub.importe) > 0 AND sub.referencia IS NOT NULL";

    $rg = utils\IConnection::execSql($selectSaldo);

    if (is_array($rg) && $rg["saldo"] > 0) {
        $Importe = $rg["saldo"];
    }

    return $Importe;
}

$selectHe = "SELECT pagosdesp.*, ven.nombre
            FROM pagosdesp,ven
            WHERE pagosdesp.vendedor = ven.id AND pagosdesp.id = '$cVarVal'";
$He = utils\IConnection::execSql($selectHe);


$selectHed = "SELECT SUM(importe) total FROM pagosdespd WHERE pago = '$cVarVal'";
$Hed = utils\IConnection::execSql($selectHed);
