<?php

#Librerias
include_once ('data/ComprasDAO.php');
include_once ('data/ComprasdDAO.php');
include_once ('data/ProveedorDAO.php');
include_once ('data/IslaDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "comprasd.php?";

$comprasDAO = new ComprasDAO();
$comprasdDAO = new ComprasdDAO();
$proveedorDAO = new ProveedorDAO();
$islaDAO = new IslaDAO();

$nameVariableSession = "CatalogoComprasDetalle";

if ($request->hasAttribute("cVarVal")) {
    utils\HTTPUtils::setSessionBiValue($nameVariableSession, "cVarVal", $request->getAttribute("cVarVal"));
}

$cVarVal = utils\HTTPUtils::getSessionBiValue($nameVariableSession, "cVarVal");

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    //error_log(print_r($bancosVO, TRUE));
    try {
        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            $Descuento = 0;
            if ($request->hasAttribute("Desc")) {
                $Descuento = $sanitize->sanitizeInt("Desc") / 100;
            }
            $comprasdVO = new ComprasdVO();
            $comprasdVO->setId($cVarVal);
            $comprasdVO->setProducto($sanitize->sanitizeInt("Producto"));
            $comprasdVO->setCantidad($sanitize->sanitizeInt("Cantidad"));
            $costo = $sanitize->sanitizeInt("Tipo") == 1 ? $sanitize->sanitizeFloat("Costo") : $sanitize->sanitizeFloat("Costo") / $sanitize->sanitizeInt("Cantidad");
            $comprasdVO->setCosto($costo);
            $comprasdVO->setDescuento($Descuento);

            if (($id = $comprasdDAO->create($comprasdVO))) {
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                TotalizaCompraAceites($cVarVal);
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === "Aplicar") {
            if ($request->hasAttribute("Descuento") && !empty($request->getAttribute("Descuento"))) {
                if ($sanitize->sanitizeFloat("Descuento") >= 0) {
                    $Desc = $sanitize->sanitizeFloat("Descuento") / 100;
                    $updateEtd = "UPDATE etd SET descuento = '$Desc' WHERE id = '$cVarVal';";
                    if ($mysqli->query($updateEtd)) {
                        $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                        TotalizaCompraAceites($cVarVal);
                    } else {
                        $Msj = utils\Messages::RESPONSE_ERROR;
                        error_log($mysqli->error);
                    }
                }
            } elseif ($request->hasAttribute("DescuentoI")) {
                if ($sanitize->sanitizeFloat("DescuentoI") >= 0) {
                    error_log("Descuento: " . $sanitize->sanitizeFloat("DescuentoI"));
                    $selectSumEtd = "
                                    SELECT 
                                    ROUND(SUM(etd.cantidad * etd.costo), 2) importe_real
                                    FROM et,etd WHERE et.id = etd.id AND et.id = '$cVarVal'";

                    $sumEtd = $mysqli->query($selectSumEtd)->fetch_array();

                    $Desc = (($sanitize->sanitizeFloat("DescuentoI") * 100) / $sumEtd[importe_real]) / 100;
                    error_log("Descuento %: " . $Desc);
                    $updateEtd = "UPDATE etd SET descuento = '$Desc' WHERE id = '$cVarVal';";
                    if ($mysqli->query($updateEtd)) {
                        $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                        TotalizaCompraAceites($cVarVal);
                    } else {
                        $Msj = utils\Messages::RESPONSE_ERROR;
                        error_log($mysqli->error);
                    }
                }
            } else {
                $Msj = "Ingrese un porcentaje o importe valido";
            }
        } elseif ($request->getAttribute("Boton") === "Adicionar") {
            if ($request->hasAttribute("Adicional")) {
                if ($sanitize->sanitizeFloat("Adicional") >= 0) {
                    $Desc = $sanitize->sanitizeFloat("Adicional") / 100;
                    $lUp = "UPDATE etd SET adicional = '$Desc' WHERE id = '$cVarVal';";
                    if ($mysqli->query($lUp)) {
                        $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                        TotalizaCompraAceites($cVarVal);
                    } else {
                        $Msj = utils\Messages::RESPONSE_ERROR;
                        error_log($mysqli->error);
                    }
                }
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en pagos: " . $ex);
    } finally {
        header("Location: $Return");
    }
}


if ($request->hasAttribute("op")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $cId = $sanitize->sanitizeInt("cId");

    try {
        if ($request->getAttribute("op") === utils\Messages::OP_DELETE) {
            $comprasdVO = $comprasdDAO->retrieve($cId, "idnvo");
            $comprasdVO->setCantidad(0);
            $comprasdVO->setId(-$cVarVal);
            $comprasdVO->setProducto(-$comprasdVO->getProducto());

            if ($comprasdDAO->update($comprasdVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_CANCEL;
                TotalizaCompraAceites($cVarVal);
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("op") === "cr") {
            $islaVO = $islaDAO->retrieve(1, "isla");
            $comprasVO = $comprasDAO->retrieve($cVarVal);

            /**
             * *Entradas
              -Cuando se hace una entrada...
              se incrementa la existencia o almacen de cada producto
              se inserta un registro de venta como abono en isla_posicion 0 de cada producto
              -Tablas involucradas
              et, etd, inv, vtaditivos
             */
            $SqlSelect = "SELECT 
                                        etd.producto AS producto,
                                        SUM(etd.cantidad) AS total_cantidad,
                                        MAX(etd.costo) AS max_costo,
                                        MAX(etd.descuento) AS max_descuento,
                                        MAX(etd.adicional) AS max_adicional
                                FROM etd
                                WHERE etd.cantidad > 0 AND etd.id = '$cVarVal'
                                GROUP BY etd.producto";
            $VvRs = utils\IConnection::getRowsFromQuery($SqlSelect);

            foreach ($VvRs as $vv) {
                $updateInventario = "UPDATE inv SET inv.existencia = inv.existencia + " . $vv["total_cantidad"] . ",
                    inv.costo = (" . $vv["max_costo"] . " * (1- " . $vv["max_descuento"] . " ) * (1 - " . $vv["max_adicional"] . ")),
                    inv.costo_prom =( (inv.existencia * inv.costo_prom) + (" . $vv["total_cantidad"] . " * (" . $vv["max_costo"] . " * (1- " . $vv["max_descuento"] . ") * (1 - " . $vv["max_adicional"] . "))) ) / IF(" . $vv["total_cantidad"] . " + inv.existencia = 0, 1, " . $vv["total_cantidad"] . " + inv.existencia)
                    WHERE inv.id = '" . $vv["producto"] . "'";
                utils\IConnection::execSql($updateInventario);
            }
            $comprasVO->setStatus(StatusCompra::CERRADO);
            if ($comprasDAO->update($comprasVO)) {
                $importe = $comprasVO->getImporte() + $comprasVO->getIva();
                $insertCxp = "INSERT INTO cxp (proveedor,referencia,fecha,fechav,tm,concepto,cantidad,importe)
                                VALUES ('" . $comprasVO->getProveedor() . "','$cVarVal','" . $comprasVO->getFecha() . "',
                                DATE_ADD('" . $comprasVO->getFecha() . "',INTERVAL " . $comprasVO->getDias_credito() . " DAY),'C',
                                '" . $comprasVO->getConcepto() . "','" . $comprasVO->getCantidad() . "',
                                '" . $importe . "')";

                if ($mysqli->query($insertCxp)) {
                    $insertVtaditivos = "INSERT INTO vtaditivos (clave,cantidad,unitario,costo,total,corte,posicion,fecha,tm,descripcion,referencia) 
                                            SELECT etd.producto clave,etd.cantidad,
                                            ROUND(etd.costo*((1 - etd.descuento)*(1 - etd.adicional)),2) unitario,
                                            ROUND(etd.costo*((1 - etd.descuento)*(1 - etd.adicional)),2) costo,
                                            ROUND(etd.cantidad *etd.costo*((1 - etd.descuento)*(1 - etd.adicional)),2) total,
                                            " . $islaVO->getCorte() . " corte,0 posicion, NOW() fecha, 'H' tm, 'Entrada' descripcion, etd.id referencia
                                            FROM etd,inv 
                                            WHERE etd.id = '$cVarVal' and etd.producto = inv.id";
                    if ($mysqli->query($insertVtaditivos)) {
                        $Msj = utils\Messages::MESSAGE_CLOSE;
                        $Return = "compras.php?criteria=ini";
                    } else {
                        error_log($mysqli->error);
                        $Msj = utils\Messages::RESPONSE_ERROR;
                    }
                } else {
                    error_log($mysqli->error);
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            } else {
                error_log($mysqli->error);
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

function TotalizaCompraAceites($busca) {


    $connection = iconnect();
    $CiaA = $connection->query("SELECT iva/100 as iva FROM cia");
    $Cia = $CiaA->fetch_array();

    $sql = "SELECT 
        cantidad,
        SUM(importe) importe, 
        SUM(importe_real) importe_real,
        SUM(descuento) descuento,
        (SUM(importe_real) + SUM(descuento)) importeG
        FROM (
            SELECT etd.producto,
            SUM(etd.cantidad) cantidad,
            ROUND(SUM(etd.cantidad * (etd.costo * (1 - etd.descuento) * (1 - etd.adicional))), 2) importe, 
            ROUND(SUM(etd.cantidad * etd.costo), 2) importe_real,
            ROUND(SUM(etd.cantidad * etd.costo * ( 1 - ( 1 - etd.descuento ) * ( 1 - etd.adicional ) )) , 2 ) descuento 
            FROM etd WHERE etd.id = " . $busca . ") a";
    $DddA = $connection->query($sql);
    $Ddd = $DddA->fetch_array();

    if ($Ddd['cantidad'] == 0) {
        $Cnt = 0;
        $Importe = 0;
        $Iva = 0;
    } else {
        $Cnt = $Ddd['cantidad'];
        $Importe = $Ddd['importe_real'];
        //$Iva = $Ddd[1] * $Cia[iva];
        $Iva = ($Ddd['importe_real'] - $Ddd['descuento']) * $Cia['iva'];
    }

    $lUp = $connection->query("UPDATE et SET cantidad = " . $Cnt . ", importe = " . $Importe . " WHERE id = " . $busca);
    $Importe = $Ddd['importe_real'];

    if ($connection != null) {
        $connection->close();
    }
}
