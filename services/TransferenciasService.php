<?php

#Librerias
include_once ('data/IslaDAO.php');
include_once ('data/ProductoDAO.php');
include_once ('data/ManDAO.php');
include_once ('data/TransferenciaDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "transferencias.php?";

$islaDAO = new IslaDAO();
$ciaDAO = new CiaDAO();
$productoDAO = new ProductoDAO();
$manDAO = new ManDAO();
$trasfDAO = new TransferenciaDAO();

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $islaVO = $islaDAO->retrieve(1, "isla");
    $tarea = date("YmdHis");

    try {
        /**
         * * Salidas
          -Cuando se hace una salida...
          se registra la salida o transferencia de cada producto
          se disminuye la existencia o almacen de cada producto
          se incrementa la existencia en la isla_posicion y producto especificado
          se inserta un registro de venta como abono en la isla_posicion especificada de cada producto
          -Tablas involucradas
          transf, inv, invd, vtaditivos
         */
        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {

            $Producto = $sanitize->sanitizeInt("Producto");
            $Isla_pos = $sanitize->sanitizeInt("Isla");
            $Cantidad = $sanitize->sanitizeInt("Cantidad");

            $manVO = $manDAO->retrieve($Isla_pos, "isla_pos", true);

            $productoVO = $productoDAO->retrieve($Producto);

            if ($productoVO->getExistencia() > 0) {
                if ($Cantidad <= $productoVO->getExistencia()) {

                    $transfVO = new TransferenciaVO();
                    $transfVO->setTarea($tarea);
                    $transfVO->setCorte($islaVO->getCorte());
                    $transfVO->setIsla_pos($Isla_pos);
                    $transfVO->setPosicion($manVO->getPosicion());
                    $transfVO->setProducto($Producto);
                    $transfVO->setCantidad($Cantidad);

                    if (($id = $trasfDAO->create($transfVO)) > 0) {
                        $productoVO->setExistencia($productoVO->getExistencia() - $Cantidad);

                        if ($productoDAO->update($productoVO)) {

                            $updateInvd = "UPDATE invd SET existencia = existencia + $Cantidad, modificacion = NOW() WHERE id = $Producto AND isla_pos = $Isla_pos";

                            $insertVtaditivos = "   
                                    INSERT INTO vtaditivos (clave,cantidad,unitario,costo,total,corte,posicion,fecha,tm,descripcion,referencia) 
                                    SELECT t.producto,t.cantidad, inv.precio, inv.costo, 
                                    (t.cantidad * inv.precio) total, 
                                    " . $islaVO->getCorte() . " corte,
                                    " . $manVO->getPosicion() . " posicion, NOW() fecha, 'H' tm, 'Salida' descripcion, $id id_transf_ref 
                                    FROM transf t, inv
                                    WHERE t.producto = inv.id    
                                    AND t.isla_pos = $Isla_pos AND t.tarea = '$tarea' ";
                            if (($mysqli->query($updateInvd)) && ($mysqli->query($insertVtaditivos))) {
                                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "SALIDA DE ADITIVO " . $Producto);
                            } else {
                                error_log("Error al actualizar e insertar registros");
                                error_log($mysqli->error);
                                error_log($updateInvd);
                                error_log($insertVtaditivos);
                                $Msj = utils\Messages::RESPONSE_ERROR;
                            }
                        } else {
                            $Msj = utils\Messages::RESPONSE_ERROR;
                            error_log("Error al actualizar inventario");
                        }
                    } else {
                        $Msj = utils\Messages::RESPONSE_ERROR;
                        error_log("Error al insertar transferencia");
                    }
                } else {
                    $Msj = "La cantidad debe ser menor o igual a la cantidad de piezas registradas en almacén";
                }
            } else {
                $Msj = "El almacén esta vacío, no es posible darle salida a este producto";
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_CANCEL) {
            $ciaVO = $ciaDAO->retrieve(1);

            if ($request->getAttribute("Password") === $ciaVO->getMaster()) {
                error_log("Iniciando cancelacion de transferencia");
                $busca = $request->getAttribute("busca");

                $transfVO = $trasfDAO->retrieve($busca);

                $Cantidad = $transfVO->getCantidad();
                $Producto = $transfVO->getProducto();
                $Isla_pos = $transfVO->getIsla_pos();

                $productoVO = $productoDAO->retrieve($Producto);

                $transfVO->setPosicion(0);
                $transfVO->setCantidad(0);
                if ($trasfDAO->update($transfVO)) {
                    $productoVO->setExistencia($productoVO->getExistencia() + $Cantidad);
                    if ($productoDAO->update($productoVO)) {
                        $updateInvd = " UPDATE invd SET existencia = existencia - $Cantidad, modificacion = NOW() 
                                        WHERE id = $Producto AND isla_pos = $Isla_pos LIMIT 1";
                        $updateVtaditivos = "
                                        UPDATE vtaditivos SET cantidad = 0, posicion = 0, total = 0, enviado = 0 
                                        WHERE referencia = $busca AND tm = 'H' AND clave = $Producto LIMIT 1";

                        if (($mysqli->query($updateInvd)) && ($mysqli->query($updateVtaditivos))) {
                            $Msj = utils\Messages::RESPONSE_VALID_CANCEL;
                            BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "CANCELACION DE SALIDA " . $busca);
                        } else {
                            error_log("Error al actualizar registros");
                            error_log($mysqli->error);
                            error_log($updateInvd);
                            error_log($updateVtaditivos);
                            $Msj = utils\Messages::RESPONSE_ERROR;
                        }
                    } else {
                        error_log("Error al actualizar inventario");
                        $Msj = utils\Messages::RESPONSE_ERROR;
                    }
                } else {
                    error_log("Error al actualizar transferencia");
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            } else {
                $Msj = utils\Messages::MESSAGE_NO_PASSWORD_VALID;
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en transferencias: " . $ex);
    } finally {
        header("Location: $Return");
        exit();
    }
}


if ($request->hasAttribute("Boton1") && $request->getAttribute("Boton1") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $islaVO = $islaDAO->retrieve(1, "isla");
    $tarea = date("YmdHis");

    try {
        $Return = "transferenciasd1.php?op=";
        $registrosArray = array();
        $decrementInv = false;

        error_log("Make exist...");
        $selectInv = "
                        SELECT inv.id producto, inv.descripcion,inv.minimo, inv.maximo, inv.existencia almacen
                        FROM inv WHERE inv.rubro = 'Aceites' AND inv.activo = 'Si'";
        $rows = utils\IConnection::getRowsFromQuery($selectInv);

        $selectInvd = "
                        SELECT invd.id producto, invd.isla_pos, invd.existencia 
                        FROM inv,invd
                        WHERE 1 = 1 
                        AND inv.id = invd.id
                        AND inv.rubro = 'Aceites' AND inv.activo = 'Si'
                        ORDER BY inv.id, invd.isla_pos";

        $registros = utils\IConnection::getRowsFromQuery($selectInvd);

        foreach ($registros as $value) {
            $registrosArray[$value[producto]][$value[isla_pos]] = $value[existencia];
        }

        foreach ($rows as $inv) {
            $productoVO = $productoDAO->retrieve($inv["producto"]);

            $ExistenciaVariable = $inv["almacen"];
            foreach ($IslasPosicionInventario as $value) {
                $porLlenar = 0;
                $existencia = $registrosArray[$inv["producto"]][$value];
                if ($ExistenciaVariable > 0 && $existencia < $inv["minimo"]) {
                    if ($ExistenciaVariable >= ($inv["maximo"] - $existencia)) {
                        $porLlenar = ($inv["maximo"] - $existencia);
                        $ExistenciaVariable -= $porLlenar;
                    } else {
                        $porLlenar = $ExistenciaVariable;
                        $ExistenciaVariable = 0;
                    }

                    $Isla_pos = $value;
                    $Producto = $inv["producto"];
                    $Cantidad = $porLlenar;

                    $manVO = $manDAO->retrieve($Isla_pos, "isla_pos", true);

                    $transfVO = new TransferenciaVO();
                    $transfVO->setTarea($tarea);
                    $transfVO->setCorte($islaVO->getCorte());
                    $transfVO->setIsla_pos($Isla_pos);
                    $transfVO->setPosicion($manVO->getPosicion());
                    $transfVO->setProducto($Producto);
                    $transfVO->setCantidad($Cantidad);

                    if (($id = $trasfDAO->create($transfVO)) > 0) {
                        $productoVO->setExistencia($productoVO->getExistencia() - $Cantidad);

                        if ($productoDAO->update($productoVO)) {

                            $updateInvd = "UPDATE invd SET existencia = existencia + $Cantidad, modificacion = NOW() WHERE id = $Producto AND isla_pos = $Isla_pos";

                            $insertVtaditivos = "   
                                    INSERT INTO vtaditivos (clave,cantidad,unitario,costo,total,corte,posicion,fecha,tm,descripcion,referencia) 
                                    SELECT t.producto,t.cantidad, inv.precio, inv.costo, 
                                    (t.cantidad * inv.precio) total, 
                                    " . $islaVO->getCorte() . " corte,
                                    " . $manVO->getPosicion() . " posicion, NOW() fecha, 'H' tm, 'Salida' descripcion, $id id_transf_ref 
                                    FROM transf t, inv
                                    WHERE t.producto = inv.id AND t.producto = $Producto
                                    AND t.isla_pos = $Isla_pos AND t.tarea = '$tarea' ";
                            if (($mysqli->query($updateInvd)) && ($mysqli->query($insertVtaditivos))) {
                                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "SALIDA DE ADITIVO " . $Producto);
                                $decrementInv = true;
                            } else {
                                error_log("Error al actualizar e insertar registros");
                                error_log($mysqli->error);
                                error_log($updateInvd);
                                error_log($insertVtaditivos);
                                $Msj = utils\Messages::RESPONSE_ERROR;
                            }
                        } else {
                            $Msj = utils\Messages::RESPONSE_ERROR;
                            error_log("Error al actualizar inventario");
                        }
                    } else {
                        $Msj = utils\Messages::RESPONSE_ERROR;
                        error_log("Error al insertar transferencia");
                    }
                    if (!is_null($transfVO)) {
                        $transfVO = null;
                    }
                }
            }
        }
        if ($decrementInv) {
            $Return .= 1;
            $Return .= "&tarea=" . $tarea;
        } else {
            $Return .= 0;
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en transferencias: " . $ex);
    } finally {
        header("Location: $Return");
        exit();
    }
}

if ($request->hasAttribute("Boton2") && $request->getAttribute("Boton2") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $islaVO = $islaDAO->retrieve(1, "isla");
    $tarea = date("YmdHis");

    try {
        $Return = "transferenciasd2.php?op=";
        $registrosArray = array();
        $decrementInv = false;

        error_log("Make exist...");
        $selectInv = "
                        SELECT inv.id producto, inv.descripcion,inv.minimo, inv.maximo, inv.existencia almacen
                        FROM inv WHERE inv.rubro = 'Aceites' AND inv.activo = 'Si'";
        $rows = utils\IConnection::getRowsFromQuery($selectInv);

        $selectInvd = "
                        SELECT invd.* 
                        FROM inv,invd
                        WHERE 1 = 1 
                        AND inv.id = invd.id
                        AND inv.rubro = 'Aceites' AND inv.activo = 'Si'
                        ORDER BY inv.id, invd.isla_pos";

        $registros = utils\IConnection::getRowsFromQuery($selectInvd);

        foreach ($registros as $value) {
            $registrosArray[$value[id]][$value[isla_pos]]["existencia"] = $value[existencia];
            $registrosArray[$value[id]][$value[isla_pos]]["minimo"] = $value[minimo];
            $registrosArray[$value[id]][$value[isla_pos]]["maximo"] = $value[maximo];
        }

        foreach ($rows as $inv) {
            $productoVO = $productoDAO->retrieve($inv["producto"]);

            $ExistenciaVariable = $inv["almacen"];
            foreach ($IslasPosicionInventario as $value) {
                $porLlenar = 0;
                $existencia = $registrosArray[$inv["producto"]][$value]["existencia"];
                $minimo = $registrosArray[$inv["producto"]][$value]["minimo"];
                $maximo = $registrosArray[$inv["producto"]][$value]["maximo"];

                if ($ExistenciaVariable > 0 && $existencia < $minimo) {
                    if ($ExistenciaVariable >= ($maximo - $existencia)) {
                        $porLlenar = ($maximo - $existencia);
                        $ExistenciaVariable -= $porLlenar;
                    } else {
                        $porLlenar = $ExistenciaVariable;
                        $ExistenciaVariable = 0;
                    }

                    $Isla_pos = $value;
                    $Producto = $inv["producto"];
                    $Cantidad = $porLlenar;

                    $manVO = $manDAO->retrieve($Isla_pos, "isla_pos", true);

                    $transfVO = new TransferenciaVO();
                    $transfVO->setTarea($tarea);
                    $transfVO->setCorte($islaVO->getCorte());
                    $transfVO->setIsla_pos($Isla_pos);
                    $transfVO->setPosicion($manVO->getPosicion());
                    $transfVO->setProducto($Producto);
                    $transfVO->setCantidad($Cantidad);

                    if (($id = $trasfDAO->create($transfVO)) > 0) {
                        $productoVO->setExistencia($productoVO->getExistencia() - $Cantidad);

                        if ($productoDAO->update($productoVO)) {

                            $updateInvd = "UPDATE invd SET existencia = existencia + $Cantidad, modificacion = NOW() WHERE id = $Producto AND isla_pos = $Isla_pos";

                            $insertVtaditivos = "   
                                    INSERT INTO vtaditivos (clave,cantidad,unitario,costo,total,corte,posicion,fecha,tm,descripcion,referencia) 
                                    SELECT t.producto,t.cantidad, inv.precio, inv.costo, 
                                    (t.cantidad * inv.precio) total, 
                                    " . $islaVO->getCorte() . " corte,
                                    " . $manVO->getPosicion() . " posicion, NOW() fecha, 'H' tm, 'Salida' descripcion, $id id_transf_ref 
                                    FROM transf t, inv
                                    WHERE t.producto = inv.id AND t.producto = $Producto
                                    AND t.isla_pos = $Isla_pos AND t.tarea = '$tarea' ";
                            if (($mysqli->query($updateInvd)) && ($mysqli->query($insertVtaditivos))) {
                                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "SALIDA DE ADITIVO " . $Producto);
                                $decrementInv = true;
                                error_log("Accept existence");
                            } else {
                                error_log("Error al actualizar e insertar registros");
                                error_log($mysqli->error);
                                error_log($updateInvd);
                                error_log($insertVtaditivos);
                                $Msj = utils\Messages::RESPONSE_ERROR;
                            }
                        } else {
                            $Msj = utils\Messages::RESPONSE_ERROR;
                            error_log("Error al actualizar inventario");
                        }
                    } else {
                        $Msj = utils\Messages::RESPONSE_ERROR;
                        error_log("Error al insertar transferencia");
                    }
                    if (!is_null($transfVO)) {
                        $transfVO = null;
                    }
                }
            }
        }
        if ($decrementInv) {
            $Return .= 1;
            $Return .= "&tarea=" . $tarea;
        } else {
            $Return .= 0;
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en transferencias: " . $ex);
    } finally {
        header("Location: $Return");
        exit();
    }
}
