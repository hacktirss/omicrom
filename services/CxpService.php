<?php

#Librerias
include_once ('data/CxpDAO.php');
include_once ('data/ProveedorDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "cxp.php?";

$cxpDAO = new CxpDAO();
$ciaDAO = new CiaDAO();
$proveedorDAO = new ProveedorDAO();

$ciaVO = $ciaDAO->retrieve(1);

if ($request->hasAttribute("Boton")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;


    $cxpVO = new CxpVO();

    $cxpVO->setId($sanitize->sanitizeInt("busca"));


    //error_log(print_r($cxpVO, TRUE));
    try {
        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            if ($cxpDAO->create($cxpVO) > 0) {
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
            if ($cxpDAO->update($cxpVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === "Enviar a historico") {
            $Proveedor = $sanitize->sanitizeString("Proveedor");
            $Return = "cxp.php?ProveedorS=$Proveedor";

            $insertCxph = " INSERT INTO cxph
                            SELECT cxp.* FROM cxp, (
                                SELECT referencia, ROUND(SUM( IF( tm =  'C', importe , - importe ) ),2) importe
                                FROM cxp
                                WHERE proveedor = $Proveedor AND referencia > 0
                                GROUP BY referencia
                                HAVING SUM( IF( tm =  'C', importe , - importe ) ) < 1
                                ORDER BY referencia
                            ) sub
                            WHERE cxp.referencia = sub.referencia;";

            $updateCxp = "UPDATE cxp,(
                            SELECT referencia, ROUND(SUM( IF( tm =  'C', importe , - importe ) ),2) importe
                                FROM cxp
                                WHERE proveedor = $Proveedor AND referencia > 0
                                GROUP BY referencia
                                HAVING SUM( IF( tm =  'C', importe , - importe ) ) < 1
                                ORDER BY referencia
                        ) sub 
                        SET cxp.proveedor = -cxp.proveedor,cxp.referencia = -cxc.referencia  
                        WHERE cxp.proveedor = $Proveedor AND  cxp.referencia = sub.referencia";

            if (($mysqli->query($insertCxph)) && ($mysqli->query($updateCxp))) {
                error_log("Last query, affected_rows: " . $mysqli->affected_rows);
                $Msj = utils\Messages::MESSAGE_DEFAULT;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === "Determinar saldo") {
            error_log(print_r($request, TRUE));
            $Proveedor = $sanitize->sanitizeString("Proveedor");

            $FechaI = $sanitize->sanitizeString("FechaI");
            $FechaF = $sanitize->sanitizeString("FechaF");
            $Fecha = $sanitize->sanitizeString("Fecha");

            $Return = "cxp.php?ProveedorS=$Proveedor&FechaI=$FechaI&FechaF=$FechaF";

            if ($ciaVO->getMaster() === $sanitize->sanitizeString("Password")) {

                $copiarCxpToCxph = "INSERT INTO cxph SELECT * FROM cxp WHERE proveedor = $Proveedor AND fecha <= DATE('$Fecha');";

                if (($mysqli->query($copiarCxpToCxph))) {

                    $insertCxp = "INSERT INTO cxp (proveedor, referencia, fecha, fechav, tm, concepto, cantidad, importe) 
                                SELECT proveedor, DATE_FORMAT(CURRENT_DATE(),'%Y%m%d') referencia, CURRENT_DATE() fecha, CURRENT_DATE() fechav,
                                IF(SUM(importe) > 0,'C','H') tm,'SALDO AL $Fecha' concepto,1 cantidad,ROUND(SUM(importe),2) importe
                                FROM 
                                (
                                SELECT proveedor,tm,SUM(IF(tm = 'C',importe,-importe)) importe 
                                FROM cxp 
                                WHERE proveedor = $Proveedor AND fecha <= DATE('$Fecha')
                                GROUP BY tm
                                ) AS SUB;";

                    $updateCxp = "UPDATE cxp SET proveedor = -proveedor, referencia = -referencia
                                  WHERE proveedor = $Proveedor AND fecha <= DATE('$Fecha') AND referencia <> DATE_FORMAT(CURRENT_DATE(),'%Y%m%d');";

                    if (($mysqli->query($insertCxp)) && ($mysqli->query($updateCxp))) {
                        error_log("Last query, affected_rows: " . $mysqli->affected_rows);
                        $Msj = utils\Messages::MESSAGE_DEFAULT;
                    } else {
                        error_log($mysqli->error);
                        $Msj = utils\Messages::RESPONSE_ERROR;
                    }
                } else {
                    error_log($mysqli->error);
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            } else {
                $Msj = utils\Messages::RESPONSE_PASSWORD_INCORRECT;
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_NO_OPERATION_VALID) {
            $Return = null;
            if ($request->hasAttribute("Abono") && $sanitize->sanitizeFloat("Abono") > 0) {
                $Proveedor = $sanitize->sanitizeString("Proveedor");
                $Abono = $sanitize->sanitizeFloat("Abono");

                $insertPagos = "INSERT INTO pagosprv (proveedor,concepto,importe,fecha,referencia,status)
                                    VALUES ('$Proveedor','Pago a cuenta de consumos','$Abono',NOW(),0,'Cerrada')";
                if (($mysqli->query($insertPagos))) {
                    $id = $mysqli->insert_id;
                    $Msj = utils\Messages::MESSAGE_DEFAULT;

                    $insertCxp = "INSERT INTO cxp (proveedor,referencia,tm,fecha,fechav,importe,concepto,numpago)
                      VALUES ('$Proveedor','$id','H',CURRENT_DATE(),CURRENT_DATE(),'$Abono','Abono a cuenta recibo No. $id','$id')";
                    if (!($mysqli->query($insertCxp))) {
                        error_log($mysqli->error);
                        $Msj = utils\Messages::RESPONSE_ERROR;
                    }
                } else {
                    error_log($mysqli->error);
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            }
        }
    } catch (Exception $ex) {
        error_log("Error en cxp: " . $ex);
    } finally {
        if ($Return != null) {
            $Return .= "&Msj=" . urlencode($Msj);
            header("Location: $Return");
        }
    }
}


if ($request->hasAttribute("op")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $cId = $sanitize->sanitizeInt("cId");
    $Return = "editacxc.php?";

    try {
        if ($request->getAttribute("op") === utils\Messages::OP_DELETE) {
            if ($ciaVO->getMaster() === $sanitize->sanitizeString("Password")) {
                $cxpVO = $cxpDAO->retrieve($cId);
                if ($cxpVO->getFactura() > 0) {
                    $Msj = "No se puede eliminar el movimiento ya que se tiene una factura asociada";
                } else {
                    $cxpVO->setCliente(-$cxpVO->getCliente());
                    $cxpVO->setReferencia(-$cxpVO->getReferencia());

                    if ($cxpDAO->update($cxpVO)) {
                        $Msj = utils\Messages::RESPONSE_VALID_CANCEL;
                        BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "CANCELA MOVIMIENTO CXC [" . $cId . "]");
                    } else {
                        $Msj = utils\Messages::RESPONSE_ERROR;
                    }
                }
            } else {
                $Msj = "Clave es invalida, intente de nuevo";
            }
        } else {
            $Msj = utils\Messages::RESPONSE_PASSWORD_INCORRECT;
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en pagos: " . $ex);
    } finally {
        header("Location: $Return");
    }
}
