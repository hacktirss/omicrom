<?php

set_time_limit(720);

#Librerias
include_once('data/CombustiblesDAO.php');
include_once('data/FcDAO.php');
include_once('data/FcdDAO.php');
include_once('data/ClientesDAO.php');
include_once('data/CxcDAO.php');
include_once('data/IslaDAO.php');
include_once('data/RmDAO.php');
include_once('data/VentaAditivosDAO.php');
include_once('data/ProductoDAO.php');
include_once('data/ProveedorPACDAO.php');
include_once('data/PagoDAO.php');
include_once('data/V_CorporativoDAO.php');

require_once('data/FacturaDetisa.php');
require_once('data/FacturaDetisaGeneral.php');
require_once('data/FacturaDetisaMonedero.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "facturasd.php?";

error_log("REQUEST " . print_r($request, true));
$fcDAO = new FcDAO();
$CombustiblesDAO = new CombustiblesDAO();
$fcdDAO = new FcdDAO();
$clientesDAO = new ClientesDAO();
$cxcDAO = new CxcDAO();
$islaDAO = new IslaDAO();
$ciaDAO = new CiaDAO();
$rmDAO = new RmDAO();
$ventaAditivosDAO = new VentaAditivosDAO();
$productoDAO = new ProductoDAO();
$pacDAO = new ProveedorPACDAO();
$pagoDAO = new PagoDAO();

$nameVariableSession = "CatalogoFacturasDetalle"; /* pagosd33 */

if ($request->hasAttribute("cVarVal")) {
    utils\HTTPUtils::setSessionBiValue($nameVariableSession, "cVarVal", $request->getAttribute("cVarVal"));
}

$cVarVal = utils\HTTPUtils::getSessionBiValue($nameVariableSession, "cVarVal");

$ciaVO = $ciaDAO->retrieve(1);
$islaVO = $islaDAO->retrieve(1, "isla");

$fcVO = new FcVO();
$clienteVO = new ClientesVO();
if (is_numeric($cVarVal)) {
    $fcVO = $fcDAO->retrieve($cVarVal);
    $clienteVO = $clientesDAO->retrieve($fcVO->getCliente());
}

$freeticket = utils\IConnection::execSql("SELECT valor FROM variables_corporativo WHERE llave = 'Fact_Ticket_Free';");

if ($clienteVO->getRfc() === ClientesDAO::GENERIC_RFC) {
    utils\HTTPUtils::setSessionValue("cVar", "A1");
}
error_log("VAL : " . $request->getAttribute("TipoCantidad"));
if ($request->hasAttribute("CambioCantidad")) {
    if ($request->getAttribute("CambioCantidad") === "EnTrue") {
        utils\HTTPUtils::setSessionValue("TipoCantidad", 1);
    } else if ($request->getAttribute("CambioCantidad") === "EnFalse") {
        utils\HTTPUtils::setSessionValue("TipoCantidad", 2);
    }
}
error_log("TIPO CANTIDAD " . utils\HTTPUtils::getSessionValue("TipoCantidad"));
if (!$request->hasAttribute("Boton")) {
// utils\HTTPUtils::setSessionValue("cGeneric", 0);
}

$lBd = false; /* Indica cuando inicia el proceso de timbrado */

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    try {
        $Cli626 = "SELECT regimenfiscal FROM cli WHERE id = " . $fcVO->getCliente();
        $RegimenFiscal = $mysqli->query($Cli626)->fetch_array();
        $Ieps_Retenido626 = $RegimenFiscal[0] == 62699 ? 0.0125 : 0;
        if ($request->getAttribute("Boton") === "Agregar ticket") {

            $Ticket = $sanitize->sanitizeInt("Ticket");
            error_log("ENTRAMOS EN AGREGA TICKET " . print_r($request, true));
            if (!empty($Ticket)) {
                $rmVO = $rmDAO->retrieve($Ticket);
                error_log("Agregando ticket [$Ticket] tipo operacion " . $request->getAttribute("Tipo"));
                $array = verificaTicketCxc($Ticket, $clienteVO, $request->getAttribute("Tipo"));
                $VC = "SELECT valor FROM variables_corporativo where llave = 'PermisoFactura';";
                $VC = utils\IConnection::execSql($VC);
                if ($VC["valor"] == 1) {
                    $VCfc = "SELECT valor FROM variables_corporativo where llave = 'FechaFactura';";
                    $VCf = utils\IConnection::execSql($VCfc);
                    $DateMin = date("Y-m") . "-" . $VCf["valor"] . " 00:00:01";
                }
                $MontRm = date("m", strtotime($rmVO->getFin_venta()));
                $MesAnterior = date("m", strtotime(date("Y-m-d") . "- 1 month"));

                if ($MontRm == date("m")) {
                    $Pass = true;
                } else {
                    if ($VC["valor"] == 1) {
                        if ($VCf["valor"] >= date("d")) {
                            $Pass = true;
                        } else {
                            $Pass = false;
                        }
                    } else {
                        $Pass = true;
                    }
                }

                if ($Pass || $VC["valor"] != 1 || $clienteVO->getTipodepago() !== "Contado") {
                    if ($array['fcd'] == FALSE && $array['siCxc'] == TRUE) {

                        $rmVO = $rmDAO->retrieve($Ticket);
                        $clienteRm = $clientesDAO->retrieve($rmVO->getCliente());

                        if ($rmVO->getTipodepago() === "Consignacion") {
                            $timestamp = strtotime($rmVO->getInicio_venta());
                            $DateComision = date("Y-m-d", $timestamp);
                            $SqlComisiones = "SELECT * FROM omicrom.comisiones WHERE vigencia >= '" . $DateComision . "';";
                            error_log($SqlComisiones);
                            $SqlCom = $mysqli->query($SqlComisiones);
                            $RsCom = $SqlCom->fetch_array();
                            $CombustiblesVO = $CombustiblesDAO->retrieve($rmVO->getProducto(), "clavei");
                            $productoVO = $productoDAO->retrieve($CombustiblesVO->getDescripcion(), "descripcion");
//                        $MontoComision = ($rmVO->getVolumen() * $RsCom["monto"]);
                            $MontoConIva = $RsCom["monto"] * 1.16;
                            $MontoComision = $MontoConIva * $rmVO->getVolumen();
                            $fcdVO = new FcdVO();
                            $fcdVO->setId($fcVO->getId());
                            $fcdVO->setProducto($productoVO->getId());
                            $fcdVO->setCantidad($rmVO->getVolumen());
                            $fcdVO->setPreciob($MontoConIva);
                            $fcdVO->setPrecio($RsCom["monto"]);
                            $fcdVO->setIva(0.16);
                            $fcdVO->setIeps(0);
                            $fcdVO->setImporte($MontoComision);
                            $fcdVO->setIva_retenido(0);
                            $fcdVO->setIsr_retenido(0);
                            $fcdVO->setTicket($Ticket);
                            $fcdVO->setTipoc("I");
                            if ($Id = $fcdDAO->create($fcdVO)) {
                                $Msj = "Registro creado con exito! " . $Id;
                            } else {
                                $Msj = "Error en sql " . $Id;
                            }
                        } else {
                            if ($request->getAttribute("Tipo") === TipoProductoFCD::COMBUSTIBLE) {
                                if ($clienteVO->getId() == $rmVO->getCliente() || $rmVO->getCliente() == 0 || $clienteRm->getTipodepago() === TiposCliente::TARJETA || $clienteRm->getTipodepago() === TiposCliente::PUNTOS) {
                                    if ($rmVO->getUuid() === "-----") {
                                        $Items = getRMItems("rm.id = '" . $rmVO->getId() . "'", "vtaditivos.referencia = '" . $rmVO->getId() . "'", $clienteVO->getTipodepago());
                                        while ($Item = $Items->fetch_array()) {
                                            if (($clienteVO->getTipodepago() != TiposCliente::CREDITO && $clienteVO->getTipodepago() != TiposCliente::PREPAGO) || $Item['cxc'] != NULL || validaTicketLibre($clienteVO)) {
                                                if ($Item['factura'] == 0) {
                                                    $Msj = fillFcd($cVarVal, $Item, $fcdDAO, $clienteVO);
                                                    if ($Msj === FcdDAO::RESPONSE_VALID) {
                                                        $Msj = "Se han cargado la venta correctamente.";
                                                        if ($sanitize->sanitizeString("EdoCuentaTicket") === "Si") {
                                                            $RmDAO = new RmDAO();
                                                            $RmVO = $RmDAO->retrieve($Ticket);
                                                            $cxcVO = new CxcVO();
                                                            $cxcVO->setCliente($fcVO->getCliente());
                                                            $cxcVO->setPlacas("----");
                                                            $cxcVO->setReferencia($Ticket);
                                                            $cxcVO->setFecha(date("Y-m-d"));
                                                            $cxcVO->setHora(date("h:i:s"));
                                                            $cxcVO->setTm("C");
                                                            $cxcVO->setConcepto("Desde detalle de Factura: " . $RmVO->getProducto());
                                                            $cxcVO->setCantidad($RmVO->getVolumen());
                                                            $cxcVO->setImporte($RmVO->getImporte());
                                                            $cxcVO->setRecibo($RmVO->getId());
                                                            $cxcVO->setCorte($RmVO->getCorte());
                                                            $cxcVO->setProducto($RmVO->getProducto());
                                                            $cxcVO->setRubro("-----");
                                                            $cxcVO->setFactura($fcVO->getId());
                                                            $cxcDAO = new CxcDAO();
                                                            $cxcDAO->create($cxcVO);
                                                        }
                                                    }
                                                } else {
                                                    $Msj = "La venta ha sido asociada al Folio: " . $Item['factura'];
                                                }
                                            } else {
                                                $Msj = "El ticket " . $Ticket . " no está cargado en el estado de cuenta del cliente.";
                                            }
                                        }
                                    } else {
                                        $Msj = "El ticket ya se encuentra facturado";
                                    }
                                } else {
                                    $Msj = "El ticket: $Ticket no pertenece al cliente o no existe";
                                }
                            } elseif ($request->getAttribute("Tipo") === TipoProductoFCD::ADITIVOS) {
                                $ventaAditivosVO = $ventaAditivosDAO->retrieve($Ticket);
                                if (!empty($ventaAditivosVO->getId())) {
                                    if ($ventaAditivosVO->getUuid() === "-----") {
                                        $productoVO = $productoDAO->retrieve($ventaAditivosVO->getProducto());

                                        $Iva = number_format($productoVO->getFactorIva() / 100, 2);
                                        $Ieps = 0.0000;
                                        $PrecioB = $ventaAditivosVO->getUnitario();

                                        $PrecioU = ROUND(($PrecioB - $Ieps) / (1 + $Iva), 6);
// En tipo 'cantidad' el valor del importe es calculado
                                        $Importe = ROUND($ventaAditivosVO->getCantidad() * $PrecioB, 6);

                                        if ($Importe > 0 && $PrecioU > 0) {

                                            if ($Item['factura'] == 0) {
                                                $fcdVO = new FcdVO();
                                                $fcdVO->setId($cVarVal);
                                                $fcdVO->setProducto($ventaAditivosVO->getProducto());
                                                $fcdVO->setCantidad($ventaAditivosVO->getCantidad());
                                                $fcdVO->setPrecio($PrecioU);
                                                $fcdVO->setIeps($Ieps);
                                                $fcdVO->setIva($Iva);
                                                $fcdVO->setImporte($Importe);
                                                $fcdVO->setTicket($Ticket);
                                                $fcdVO->setTipoc("C");
                                                $fcdVO->setPreciob($PrecioB);
                                                $fcdVO->setDescuento(0); //not use
                                                $fcdVO->setIva_retenido(0);
                                                $fcdVO->setIsr_retenido($Ieps_Retenido626);
                                                if (($id = $fcdDAO->create($fcdVO)) < 0) {
                                                    $Msj = utils\Messages::RESPONSE_ERROR;
                                                } else {
                                                    if ($Item['cxc'] != NULL && ($clienteVO->getTipodepago() === TiposCliente::CREDITO || $clienteVO->getTipodepago() === TiposCliente::PREPAGO)) {
                                                        $sql = "UPDATE cxc SET factura = '" . $cVarVal . "' "
                                                                . "WHERE tm = 'C' AND cliente = " . $clienteVO->getId() . " "
                                                                . "AND referencia = '" . $Ticket . "' AND producto = 'A'  LIMIT 1";
                                                        if (!$mysqli->query($sql)) {
                                                            $Msj = utils\Messages::RESPONSE_ERROR;
                                                        }
                                                    }
                                                    $Msj = "Se ha agregado el ticket correctamente";
                                                }
                                            } else {
                                                $Msj = "La venta ha sido asociada al Folio: " . $Item['factura'];
                                            }
                                        }
                                    } else {
                                        $Msj = "El ticket ya se encuentra facturado";
                                    }
                                } else {
                                    $Msj = "El ticket: $Ticket no existe";
                                }
                            }
                        }
                    } else {
                        $Msj = $array['mensaje'];
                    }
                } else {
                    $Msj = "Error: Ticket fuera de rengo del mes actual, fecha limite " . $VCf["valor"];
                }
            } else {
                $Msj = "El ticket ingresado es invalido";
            }
            TotalizaFactura($cVarVal, $fcDAO);
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            $isValid = TRUE;
            $Msj = utils\Messages::RESPONSE_VALID_CREATE;

            if ($request->getAttribute("Producto") <= 4) {
                $cSQL = "SELECT com.descripcion, com.precio, com.iva, com.ieps
                        FROM com JOIN inv ON inv.descripcion = com.descripcion
                        WHERE inv.id = '" . $request->getAttribute("Producto") . "'";

                if ($request->getAttribute("Importe") > 0) {
                    $Tipo = "I";
                    $Importe = ROUND($request->getAttribute("Importe"), 6);
                } else {
                    $Tipo = "C";
                    $Cnt = $request->getAttribute("Cantidad");
                }
            } else {
                $cSQL = "SELECT inv.descripcion, inv.precio, ROUND( cia.iva/100, 2 ) iva, 0.0000 ieps,inv.retiene_iva,inv.porcentaje,inv.precio
                        FROM inv JOIN cia ON 1=1
                        WHERE inv.id = '" . $request->getAttribute("Producto") . "'";

                if ($request->getAttribute("Importe") > 0) {
                    $Msj = "Para estos productos no es posible dar importe, favor de dar unicamente la <strong>Cantidad";
                    $sql = "SELECT inv.retiene_iva,inv.porcentaje FROM inv WHERE inv.id = '" . $request->getAttribute("Producto") . "'";
                    $retiene = $mysqli->query($sql)->fetch_array();
                    if ($retiene["retiene_iva"] === "Si") {
                        $Tipo = "I";
                        $isValid = TRUE;
                    } else {
                        $isValid = TRUE;
                    }
                }

                if ($request->getAttribute("Cantidad") > 0) {
                    $Tipo = "C";
                    $Cnt = ROUND($request->getAttribute("Cantidad"), 0);
                }
            }
            if ($request->getAttribute("AddTickets") === "BuscaTicket") {
                if ($Tipo === "I") {
                    $Concat = " AND importe <= " . $request->getAttribute("Importe") . " ";
                } else {
                    $Concat = " AND volumen <= " . $request->getAttribute("Cantidad") . " ";
                }

                $SelectTicketDisp = "SELECT rm.id,rm.ieps,rm.producto,rm.importe pesos,rm.tipo_venta,rm.fin_venta,rm.producto,rm.volumen,rm.precio,inv.id idInv "
                        . "FROM rm "
                        . "JOIN com ON rm.producto = com.clavei AND com.activo = 'Si' "
                        . "LEFT JOIN inv ON com.descripcion=inv.descripcion AND com.activo = 'Si' "
                        . "WHERE  fin_venta "
                        . "BETWEEN DATE_ADD(NOW(),INTERVAL -30 DAY) AND DATE_ADD(NOW(),INTERVAL -1 DAY) "
                        . "AND importe > 0 AND cliente = 0 $Concat AND completo = 1 AND tipo_venta = 'D' "
                        . "AND uuid = '-----' AND rm.producto = "
                        . "(SELECT clavei FROM `inv` LEFT JOIN com ON inv.descripcion=com.descripcion "
                        . "WHERE inv.id= '" . $request->getAttribute("Producto") . "') "
                        . "ORDER BY fin_venta ASC ,pesos DESC;";
//error_log($SelectTicketDisp);
                $WleDisp = $mysqli->query($SelectTicketDisp);
                $TotalaEliminar = $Tipo === "I" ? $request->getAttribute("Importe") : $request->getAttribute("Cantidad");
                $Restante = 100;
                while ($rgs = $WleDisp->fetch_array()) {
                    $fcd2VO = new FcdVO();
                    $fcd2VO = $fcdDAO->retrieve($rgs["id"], "tipoc = 'I' AND ticket");
                    if (0 == $fcd2VO->getTicket()) {
                        $PrecioU = ROUND(($rgs["precio"] - $rgs["ieps"]) / (1 + number_format($ciaVO->getIva() / 100, 2)), 6);
                        $fcdVO = new FcdVO();
                        $fcdVO->setId($cVarVal);
                        $fcdVO->setProducto($rgs["idInv"]);
                        $fcdVO->setCantidad($rgs["volumen"]);
                        $fcdVO->setPrecio($PrecioU);
                        $fcdVO->setIeps($rgs["ieps"]);
                        $fcdVO->setIva(number_format($ciaVO->getIva() / 100, 2));
                        $fcdVO->setImporte($rgs["pesos"]);
                        $fcdVO->setTicket($rgs["id"]);
                        $fcdVO->setTipoc($Tipo);
                        $fcdVO->setPreciob($rgs["precio"]);
                        $fcdVO->setDescuento(0); //not use
                        $fcdVO->setIva_retenido(0);
                        $fcdVO->setIsr_retenido(number_format($Ieps_Retenido626, 4));
                        if ($Tipo === "I") {
                            if ($TotalaEliminar < 0) {
                                $Restante = $TotalaEliminar + $Restante;
                            }
                            if ($TotalaEliminar >= $rgs["pesos"] || ($TotalaEliminar > -100 && $TotalaEliminar <= 3 && $rgs["pesos"] <= $Restante)) {
                                if ($fcdDAO->create($fcdVO)) {
                                    $TotalaEliminar = $TotalaEliminar - $rgs["pesos"];
                                }
                            }
                        } else {
                            if ($TotalaEliminar >= $rgs["volumen"]) {
                                if ($fcdDAO->create($fcdVO)) {
                                    $TotalaEliminar = $TotalaEliminar - $rgs["volumen"];
                                }
                            }
                        }
                    }
                }
            } else {
                if ($isValid && !empty($request->getAttribute("Producto"))) {
                    $Inv = $mysqli->query($cSQL)->fetch_array();
                    $Iva = $Inv['iva'];
                    $Ieps = $request->hasAttribute("IEPS") ? $request->getAttribute("IEPS") : $Inv['ieps'];
                    $PrecioB = $Inv['precio'];
                    $PrecioU = ROUND(($Inv['precio'] - $Ieps) / (1 + $Iva), 6);

                    if ($Inv["retiene_iva"] === "Si") {
                        $Importe = $Inv["precio"];
                        $RetencionIva = $Inv["porcentaje"] / 100;
                        $PrecioB = $Importe;
                        $PrecioU = $PrecioB * (1 - $Iva);
                        $Cnt = 1;
                    } else if ($request->getAttribute("Cantidad") === "" && $retiene["retiene_iva"] != "Si") {
                        $RetencionIva = 0;
                        $Importe = $request->getAttribute("Importe");
                        $sql = "SELECT precio FROM com WHERE descripcion in (SELECT descripcion FROM inv WHERE id = " . $request->getAttribute("Producto") . ")";
                        $cntGas = $mysqli->query($sql)->fetch_array();
                        $Cnt = ($cntGas["precio"] > 0) ? $Importe / $cntGas["precio"] : 1;
                        $Tipo = "I";
                    } else {
                        $RetencionIva = 0;
                    }
                    switch ($Tipo) {
                        case "C":
                            $Importe = ROUND($Cnt * $PrecioB, 6);
                            break;
                        case "I":
                            $Importe;
                            break;
                    }

                    if (($Importe > 0 && $PrecioU > 0) || ($request->getAttribute("Producto") > 4)) {
                        $ContieneIva = "SELECT factorIva FROM inv WHERE id = " . $request->getAttribute("Producto");
                        $CnIva = utils\IConnection::execSql($ContieneIva);
                        if ($CnIva["factorIva"] < 16) {
                            $PrecioU = $PrecioB / (1 + ($CnIva["factorIva"] / 100));
                            $Iva = ($CnIva["factorIva"] / 100);
                        }

                        $fcdVO = new FcdVO();
                        $fcdVO->setId($cVarVal);
                        $fcdVO->setProducto($request->getAttribute("Producto"));
                        $fcdVO->setCantidad($Cnt);
                        $fcdVO->setPrecio($PrecioU);
                        $fcdVO->setIeps($Ieps);
                        $fcdVO->setIva($Iva);
                        $fcdVO->setImporte($Importe);
                        $fcdVO->setTicket(0);
                        $fcdVO->setTipoc($Tipo);
                        $fcdVO->setPreciob($PrecioB);
                        $fcdVO->setDescuento(0); //not use
                        $fcdVO->setIva_retenido($RetencionIva);
                        $fcdVO->setIsr_retenido(number_format($Ieps_Retenido626, 4));

                        /* Asignamos a tickets que esten libres para el monto o cantidad */

                        if (($id = $fcdDAO->create($fcdVO)) > 0) {
                            if ($sanitize->sanitizeString("EdoCuenta") === "Si") {
                                $cxcVO = new CxcVO();
                                $cxcVO->setCliente($fcVO->getCliente());
                                $cxcVO->setPlacas("----");
                                $cxcVO->setReferencia($id);
                                $cxcVO->setFecha(date("Y-m-d"));
                                $cxcVO->setHora(date("h:i:s"));
                                $cxcVO->setTm("C");
                                $cxcVO->setConcepto("Producto manual : " . $fcdVO->getProducto());
                                $cxcVO->setCantidad($Cnt);
                                $cxcVO->setImporte($Importe);
                                $cxcVO->setRecibo(0);
                                $cxcVO->setCorte(0);
                                $cxcVO->setProducto($fcdVO->getProducto());
                                $cxcVO->setRubro("-----");
                                $cxcVO->setFactura($cVarVal);
                                $cxcDAO = new CxcDAO();
                                $cxcDAO->create($cxcVO);
                            }

                            $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                        } else {
                            $Msj = utils\Messages::RESPONSE_ERROR;
                        }
                    }
                } else {
                    $Msj = "Los parametros ingresados son invalidos";
                }
            }
            TotalizaFactura($cVarVal, $fcDAO);
        } elseif ($request->getAttribute("Boton") === "Agregar vtas") {
            $FechaI = date("Y-m-d", strtotime($request->getAttribute("FechaI")));
            $FechaF = date("Y-m-d", strtotime($request->getAttribute("FechaF")));

            updateObservaciones($cVarVal, $FechaI, $FechaF, "Combustible");
            insertFCP($FechaI, $FechaF, "T", $cVarVal);

            $sqlUpdateFcd = "UPDATE fcd SET id = -id, ticket = -ticket WHERE id = '" . $cVarVal . "'";
            if (!$mysqli->query($sqlUpdateFcd)) {
                error_log($mysqli->error);
            }

            $sqlUpdateCxc = "UPDATE cxc SET factura = null WHERE factura = " . $cVarVal . "";
            if (!$mysqli->query($sqlUpdateCxc)) {
                error_log($mysqli->error);
            }

            $productos = "";
            $combustibleR = $request->getAttribute("Combustible");
            if (!empty($combustibleR)) {
                $productos = "AND rm.producto = '" . $request->getAttribute("Combustible") . "'";
            }

            $FilterStatement = "rm.cliente = " . $fcVO->getCliente() . " AND rm.fecha_venta BETWEEN '" . str_replace("-", "", $FechaI) . "' AND '" . str_replace("-", "", $FechaF) . "'  " . $productos;
            $FilterStatement1 = "vtaditivos.cliente = " . $fcVO->getCliente() . " AND DATE( vtaditivos.fecha ) BETWEEN( '" . $FechaI . "' ) AND DATE( '" . $FechaF . "' )";

// Agrega los productos
            if ($clienteVO->getTipodepago() === TiposCliente::CREDITO || $clienteVO->getTipodepago() === TiposCliente::PREPAGO) {
                $Msj = getRMItems_CRE_PRE($cVarVal, $FilterStatement, $FilterStatement1);
                if ($Msj === FcdDAO::RESPONSE_VALID) {
                    $Msj = utils\Messages::MESSAGES_FACTURAS_LOAD_OK;
                }
            } else {
                if ($clienteVO->getTipodepago() === TiposCliente::CONSIGNACION) {
                    $Items = getRMItemsConsignaciones($FilterStatement, $FilterStatement1, $clienteVO->getTipodepago());
                    while ($Item = $Items->fetch_array()) {
                        $Msj = fillFcdConsignaciones($cVarVal, $Item, $fcdDAO, $clienteVO);
                    }
                    $InsertVentaG = "INSERT INTO fcd 
                        (id,producto,cantidad,preciob,precio,iva,iva_retenido,isr_retenido,ieps_retenido,ieps,importe)
                        select $cVarVal id,(select id FROM inv WHERE descripcion like '%Promoción De Negocios%') producto,
                        1 cantidad, ROUND((precio * SUM(cantidad))* (1 + iva),2) preciob,
                        ROUND(precio*sum(cantidad)) precio,0.16 iva,0 iva_retenido,0 isr_retenido,
                        0 ieps_retenido,0 ieps,((precio * SUM(cantidad))* (1 + iva)) importe from fcd where id = $cVarVal;";
                    if ($mysqli->query($InsertVentaG)) {
                        $DeleteRm = "DELETE FROM fcd WHERE id = $cVarVal AND producto < 4";
                        if ($mysqli->query($DeleteRm)) {
                            error_log($mysqli->error);
                        }
                    }
                    error_log($mysqli->error);
                } else {
                    $Items = getRMItems($FilterStatement, $FilterStatement1, $clienteVO->getTipodepago());
                    while ($Item = $Items->fetch_array()) {
                        $Msj = fillFcd($cVarVal, $Item, $fcdDAO, $clienteVO);
                    }
                }
                if ($Msj === utils\Messages::MESSAGES_FACTURAS_EXISTS) {
                    $Msj = "Una o mas ventas han sido asociadas a otros folios.";
                } elseif ($Msj === FcdDAO::RESPONSE_VALID) {
                    $Msj = utils\Messages::MESSAGES_FACTURAS_LOAD_OK;
                }
            }
            TotalizaFactura($cVarVal, $fcDAO);
        } elseif ($request->getAttribute("Boton") === "Agregar Periodo") {
            $Pass = false;
            if ($request->getAttribute("AnoPeriodo") === date("Y")) {
                if (date("m") === '01') {
                    if ($request->getAttribute("Meses") === "01" || $request->getAttribute("Meses") === "13") {
                        $Pass = true;
                    }
                } else if (date("m") === '02') {
                    if ($request->getAttribute("Meses") <= "02" || $request->getAttribute("Meses") === "13") {
                        $Pass = true;
                    }
                } else if (date("m") === '03') {
                    if ($request->getAttribute("Meses") <= "03" || $request->getAttribute("Meses") === "14") {
                        $Pass = true;
                    }
                } else if (date("m") === '04') {
                    if ($request->getAttribute("Meses") <= "04" || $request->getAttribute("Meses") === "14") {
                        $Pass = true;
                    }
                } else if (date("m") === '05') {
                    if ($request->getAttribute("Meses") <= "05" || $request->getAttribute("Meses") === "15") {
                        $Pass = true;
                    }
                } else if (date("m") === '06') {
                    if ($request->getAttribute("Meses") <= "06" || $request->getAttribute("Meses") === "15") {
                        $Pass = true;
                    }
                } else if (date("m") === '07') {
                    if ($request->getAttribute("Meses") <= "07" || $request->getAttribute("Meses") === "16") {
                        $Pass = true;
                    }
                } else if (date("m") === '08') {
                    if ($request->getAttribute("Meses") <= "08" || $request->getAttribute("Meses") === "16") {
                        $Pass = true;
                    }
                } else if (date("m") === '09') {
                    if ($request->getAttribute("Meses") <= "09" || $request->getAttribute("Meses") === "17") {
                        $Pass = true;
                    }
                } else if (date("m") === '10') {
                    if ($request->getAttribute("Meses") <= "10" || $request->getAttribute("Meses") === "17") {
                        $Pass = true;
                    }
                } else if (date("m") === '11') {
                    if ($request->getAttribute("Meses") <= "11" || $request->getAttribute("Meses") === "18") {
                        $Pass = true;
                    }
                } else if (date("m") === '12') {
                    if ($request->getAttribute("Meses") <= "12" || $request->getAttribute("Meses") === "18") {
                        $Pass = true;
                    }
                }
            } else {
                $Pass = true;
            }
            if ($Pass) {
                $fcVO = $fcDAO->retrieve($cVarVal);
                $fcVO->setPeriodo($request->getAttribute("Periodo_sat"));
                $fcVO->setMeses($request->getAttribute("Meses"));
                $fcVO->setAno($request->getAttribute("AnoPeriodo"));
                if ($fcDAO->update($fcVO)) {
                    $Msj = "Registro Actualizado con Exito!";
                }
            } else {
                $Msj = "Error: Las fechas superan a la fecha actual";
            }
        } elseif ($request->getAttribute("Boton") === "Guardar estos cambios") {
            $Return = "genfactura331.php?";
            $desgloseIEPS = $request->hasAttribute("DesgloseIEPS") ? "S" : "N";
            $nombreFacturaRequest = ($request->getAttribute("FAlias") == "1" && $request->getAttribute("FCuenta") == "1") ? "F" : ($request->getAttribute("FAlias") == "1" ? "A" : ($request->getAttribute("FCuenta") == "1" ? "C" : "N"));

            $clienteVO = $clientesDAO->retrieve($fcVO->getCliente());
            $clienteVO->setCorreo($request->getAttribute("Correo"));
            $clienteVO->setEnviarcorreo($request->getAttribute("Enviarcorreo"));
            $clienteVO->setDesgloseIEPS($desgloseIEPS);
            $clienteVO->setNombreFactura($nombreFacturaRequest);
//            $clienteVO->setFormadepago($request->getAttribute("Formadepago"));
            $clienteVO->setCodigo($request->getAttribute("CodigoPostal"));
            $clienteVO->setRegimenFiscal($request->getAttribute("RegimenFiscal"));
            if (!$clientesDAO->update($clienteVO)) {
                error_log("Error al actualizar Cliente");
            }
            if ($request->getAttribute("rfcGenerico") === "1") {
                utils\HTTPUtils::setSessionValue("cGeneric", 1);
                if ($request->getAttribute("Anio") > 2020) {
                    $fcVO->setPeriodo($request->getAttribute("Periodo_sat"));
                    $fcVO->setAno($request->getAttribute("Anio"));
                    $fcVO->setMeses($request->getAttribute("Meses"));
                }
            } else if ($request->getAttribute("rfcGenericoPersonal") === "1") {
                utils\HTTPUtils::setSessionValue("cGenericPerso", 1);
            } else if ($clienteVO->getRfc() === "XAXX010101000") {
                /* No se hace nada */
            } else {
                utils\HTTPUtils::setSessionValue("cGeneric", 0);
                utils\HTTPUtils::setSessionValue("cGenericPerso", 0);
                $fcVO->setPeriodo("00");
                $fcVO->setAno("0000");
                $fcVO->setMeses("00");
            }
            $fcVO->setObservaciones($request->getAttribute("Observaciones"));
            $fcVO->setFormadepago($request->getAttribute("Formadepago"));
            $fcVO->setMetododepago($request->getAttribute("Metododepago"));
            $fcVO->setDocumentoRelacion($request->getAttribute("TipoRelacion"));
            $fcVO->setRelacioncfdi(empty($request->getAttribute("Relacioncfdi")) ? 0 : $request->getAttribute("Relacioncfdi"));
            $fcVO->setTiporelacion($request->getAttribute("tiporelacion"));
            if ($usuarioSesion->getLevel() >= 7) {
                $fcVO->setUsocfdi($request->getAttribute("cuso"));
            }

            if ($fcDAO->update($fcVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === "Genera factura formato carta" || $request->getAttribute("Boton") === "Genera factura formato ticket") {
            if ($request->getAttribute("rfcGenerico") == "1") {
                utils\HTTPUtils::setSessionValue("cGeneric", 1);
            }
            if ($request->getAttribute("Boton") === "Genera factura formato ticket") {
                utils\HTTPUtils::setSessionValue("cVar", "TC");
            } else {
                utils\HTTPUtils::setSessionValue("cVar", "A1");
            }

            $Return = "genfactura331.php?";
            if ($clienteVO->getTipodepago() !== TiposCliente::MONEDERO) {
                if (utils\HTTPUtils::getSessionValue("cGeneric") == 1) {
                    $facturaDetisa = new \com\detisa\omicrom\FacturaDetisaGeneral($cVarVal);
                    utils\HTTPUtils::setSessionValue("cGeneric", 0);
                } else {
                    $facturaDetisa = $clienteVO->getRfc() === FcDAO::RFC_GENERIC ?
                            new \com\detisa\omicrom\FacturaDetisaGeneral($cVarVal) :
                            new com\detisa\omicrom\FacturaDetisa($cVarVal);
                    utils\HTTPUtils::setSessionValue("cGenericPerso", 0);
                }
            } else {
                $facturaDetisa = new \com\detisa\omicrom\FacturaDetisaMonedero($cVarVal);
                $facturaDetisa->getComprobante()->getReceptor()->setRfc(FcDAO::RFC_GENERIC);
            }

//error_log(print_r($facturaDetisa->getComprobante()->asXML(), TRUE));
            $document = $facturaDetisa->getComprobante()->asXML();
            $a = $document->save("/home/omicrom/xml/prb.xml");

            if (count($facturaDetisa->getComprobante()->getConceptos()->getConcepto()) == 0) {
                $Msj = "<strong>Error critico.</strong> El comprobante no tiene conceptos, no es posible timbrar un comprobante sin conceptos.";
                $Return = "facturasd.php?";
            } else {

                $wsdl = FACTENDPOINT;
                $client = new nusoap_client($wsdl, true);
                $client->timeout = 720;
                $client->response_timeout = 720;
                $client->soap_defencoding = 'UTF-8';
                $client->namespaces = array("SOAP-ENV" => "http://schemas.xmlsoap.org/soap/envelope/");

                $Fmt = utils\HTTPUtils::getSessionValue("cVar");        //Tipo de formato;

                $params = array(
                    "cfdi" => $facturaDetisa->getComprobante()->asXML()->saveXML(),
                    "formato" => $Fmt,
                    "tipo" => $clienteVO->getRfc() === FcDAO::RFC_GENERIC ? "FG" : "FA",
                    "idfc" => $cVarVal
                );
                try {
                    $result = $client->call("cfdiXml", $params);
                } catch (Exception $ex) {
                    error_log($ex);
                }

                $facValida = $result["return"]["valid"];

                $err = $client->getError();

                if ($err || $facValida == 'false') {
                    $cError = utf8_encode($result["return"]["error"]);
                    $Msj = $cError;
                    error_log("ENTRAMOS A ERROR" . $cError);
                    $Return = "facturas.php?criteria=ini";
                } else {
                    $Msj = utils\Messages::MESSAGE_RINGING_SUCCESS;
                    if ($Fmt === "TC") {
                        $Return = "facturas.php?pop=true&idp=$cVarVal&fmp=1";
                    } else {
                        $Return = "facturas.php?pop=true&idp=$cVarVal&fmp=0";
                    }
                    $FcDAO = new FcDAO();
                    $FcVO = new FcVO();
                    $FcVO = $FcDAO->retrieve($cVarVal);
                    $Updata = "UPDATE relacion_cfdi SET uuid = '" . $FcVO->getUuid() . "'  WHERE origen =1 AND id_fc = $cVarVal";
                    if (utils\IConnection::execSql($Updata)) {
                        
                    }

                    if ($clienteVO->getTipodepago() === TiposCliente::PREPAGO && $fcVO->getDocumentoRelacion() === "ANT") {
                        $pagoVO = $pagoDAO->retrieve($fcVO->getRelacioncfdi());
                        $pagoVO->setStatus_pago(StatusPagoPrepago::CON_FACTURA_CONSUMOS);
                        $pagoDAO->update($pagoVO);
                        $Return = "pagosd33.php?cVarVal=" . $pagoVO->getId();
                    } elseif ($clienteVO->getTipodepago() === TiposCliente::MONEDERO) {
                        $pagoVO = $pagoDAO->retrieve($fcVO->getRelacioncfdi());
                        $pagoVO->setStatus(StatusPago::CERRADO);
                        $pagoVO->setStatus_pago(StatusPagoPrepago::CON_NOTA_CREDITO);
                        $pagoVO->setStatusCFDI(1);
                        $pagoDAO->update($pagoVO);
                    }
                }
            }
        } elseif ($request->getAttribute("Boton") === "DeleteRelacion") {
            $Return = "genfactura331.php?";
            $DeleteRelacion = "DELETE FROM relacion_cfdi WHERE id = " . $request->getAttribute("idDt");
            utils\IConnection::execSql($DeleteRelacion);
        }
    } catch (Exception $ex) {
        error_log("Error en facturasd: " . $ex);
    } finally {
        if (!is_null($Return)) {
            $Return .= "&Msj=" . urlencode($Msj);
            header("Location: $Return");
        }
    }
}
if ($request->hasAttribute("General")) {
    error_log(print_r($request, TRUE));
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $foliosError = "";
    $FechaI = date("Ymd", strtotime($sanitize->sanitizeString("FechaI")));
    $FechaF = date("Ymd", strtotime($sanitize->sanitizeString("FechaF")));

    try {
        $selectTirillas = "
                        SELECT GROUP_CONCAT(DISTINCT rm.vdm ORDER BY rm.vdm ASC) tirillas
                        FROM fcd, rm, cli WHERE TRUE 
                        AND fcd.ticket = rm.id AND rm.cliente = cli.id
                        AND cli.tipodepago = 'Monedero'
                        AND fcd.id = $cVarVal;";

        if ($request->hasAttribute("Contado")) {

            if ($request->getAttribute("Contado") === utils\Messages::OP_ADD) {
                $Ticket = $sanitize->sanitizeInt("Ticket");

                if (!empty($Ticket)) {
                    BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "FACTURACIÓN. AGREGA TICKET MANUAL A PÚBLICO EN GENERAL[" . $Ticket . "] ");
                    $FilterStatement = " rm.id = '$Ticket' AND ( rm.cliente = '0' OR cli.tipodepago IN ('Contado','Puntos','Tarjeta','Monedero'))";
                    $FilterStatement1 = "";
                } else {
                    cleanDetail($cVarVal);

                    updateObservaciones($cVarVal, $request->getAttribute("FechaICn"), $request->getAttribute("FechaFCn"), "Combustible/Contado");

                    $FilterStatement = " rm.fecha_venta BETWEEN '" . date("Ymd", strtotime($sanitize->sanitizeString("FechaICn"))) . "' AND '" . date("Ymd", strtotime($sanitize->sanitizeString("FechaFCn"))) . "' AND ( rm.cliente = '0' OR cli.tipodepago IN ('Contado','Puntos'))";
                    $FilterStatement1 = "";
                    error_log();
                    insertFCP($request->getAttribute("Fecha"), $request->getAttribute("Fecha"), "C", $cVarVal);
                }
            }
        } elseif ($request->hasAttribute("Tarjeta")) {
            cleanDetail($cVarVal);
            if ($request->getAttribute("Tarjeta") === utils\Messages::OP_ADD) {

                updateObservaciones($cVarVal, $request->getAttribute("FechaI"), $request->getAttribute("FechaF"), "Combustible/Tarjeta");

                $FilterStatement = " rm.fecha_venta BETWEEN '$FechaI' AND '$FechaF' AND cli.tipodepago = 'Tarjeta' ";
                $FilterStatement1 = "";
                if (!empty($request->getAttribute("Cliente")) && is_numeric($request->getAttribute("Cliente"))) {
                    $ClienteVt = " AND cli.id = '" . $request->getAttribute("Cliente") . "' ";
                }
                $FilterStatement = $FilterStatement . $ClienteVt;
                insertFCP($request->getAttribute("FechaI"), $request->getAttribute("FechaF"), "T", $cVarVal);
            }
        } elseif ($request->hasAttribute("Aceites")) {
            cleanDetail($cVarVal);
            $FechaII = date("Y-m-d", strtotime($sanitize->sanitizeString("FechaII")));
            $FechaFF = date("Y-m-d", strtotime($sanitize->sanitizeString("FechaFF")));

            if ($request->getAttribute("Aceites") === utils\Messages::OP_ADD) {

                updateObservaciones($cVarVal, $FechaII, $FechaFF, "Aceites");

                $FilterStatement = "";
                $FilterStatement1 = " inv.rubro = '" . $request->getAttribute("RubroAditivo") . "' AND  "
                        . "DATE(vtaditivos.fecha) BETWEEN DATE('" . $FechaII . "') AND DATE('" . $FechaFF . "') "
                        . "AND cli.tipodepago IN ('Contado', 'Tarjeta', 'Puntos', 'Monedero')";

                insertFCP($FechaII, $FechaFF, "A", $cVarVal);
            }
        } elseif ($request->hasAttribute("Monedero")) {

            $Cliente = $sanitize->sanitizeInt("Cliente");
            $Pago = $sanitize->sanitizeInt("Pago");
            $Titilla = $sanitize->sanitizeString("Tirilla");
            error_log(print_r($request, true));
            error_log("BF " . $request->getAttribute("BotonFecha"));
            error_log("BP " . $request->getAttribute("BotonPago"));
            if ($request->hasAttribute("BotonFecha") && $request->getAttribute("BotonFecha") === utils\Messages::OP_ADD) {
                cleanDetail($cVarVal);
                updateObservaciones($cVarVal, $request->getAttribute("FechaI"), $request->getAttribute("FechaF"), "Combustible/Monederos");

                $FilterStatement = " rm.fecha_venta BETWEEN '$FechaI' AND '$FechaF' AND cli.tipodepago IN ('Monedero') AND cli.id = $Cliente";
                $FilterStatement1 = "DATE(vtaditivos.fecha) BETWEEN DATE('" . $request->getAttribute("FechaI") . "') AND DATE('" . $request->getAttribute("FechaF") . "') AND cli.tipodepago IN ('Monedero')  AND cli.id = $Cliente";

                insertFCP($request->getAttribute("FechaI"), $request->getAttribute("FechaF"), "M", $cVarVal);
            } elseif ($request->hasAttribute("BotonPago") && $request->getAttribute("BotonPago") === utils\Messages::OP_ADD) {

                if (!empty($Pago)) {
                    cleanDetail($cVarVal);

                    updateObservaciones($cVarVal, null, null, "Combustible/Monederos", null, $Pago);

                    $FilterStatement = " rm.pagado = $Pago AND cli.tipodepago IN ('Monedero') AND cli.id = $Cliente";
                    $FilterStatement1 = "vtaditivos.pagado = $Pago AND cli.tipodepago IN ('Monedero') AND cli.id = $Cliente";

                    insertFCP(null, null, "M", $cVarVal);
                } elseif (!empty($Titilla)) {
                    $tirillas = utils\IConnection::execSql($selectTirillas);
                    if ($tirillas["tirilla"] === "0") {
                        cleanDetail($cVarVal);
                    }
                    updateObservaciones($cVarVal, null, null, "Combustible/Monederos con tirilla: $Titilla");

                    $FilterStatement = " rm.vdm = '$Titilla' AND cli.tipodepago IN ('Monedero') AND cli.id = $Cliente";
                    $FilterStatement1 = "";

                    insertFCP(null, null, "M", $cVarVal);
                }
            } else {
                error_log($request->getAttribute("Boton") . " BOTON ");
                if (!($request->getAttribute("Boton") === "Agregar ticket")) {
                    if (!empty($Titilla)) {
                        $tirillas = utils\IConnection::execSql($selectTirillas);
                        if ($tirillas["tirilla"] === "0") {
                            cleanDetail($cVarVal);
                        }
                        updateObservaciones($cVarVal, null, null, "Combustible/Monederos con tirilla: $Titilla");

                        $FilterStatement = " rm.vdm = '$Titilla' AND cli.tipodepago IN ('Monedero') ";
                        $FilterStatement1 = "";

                        insertFCP(null, null, "M", $cVarVal);
                    } else {
                        $FechaI = date("Y-m-d", strtotime($sanitize->sanitizeString("FechaI")));
                        $FechaF = date("Y-m-d", strtotime($sanitize->sanitizeString("FechaF")));

                        cleanDetail($cVarVal);
                        updateObservaciones($cVarVal, $FechaI, $FechaF, "Combustible/Monederos");

                        $FilterStatement = " rm.fecha_venta BETWEEN '" . str_replace("-", "", $FechaI) . "' AND '" . str_replace("-", "", $FechaF) . "' AND cli.tipodepago IN ('Monedero') ";
                        $FilterStatement1 = "DATE(vtaditivos.fecha) BETWEEN DATE('" . $FechaI . "') AND DATE('" . $FechaF . "') AND cli.tipodepago IN ('Monedero') ";

                        insertFCP($FechaI, $FechaF, "M", $cVarVal);
                    }
                } else {
                    $Msj = utils\Messages::MESSAGE_DEFAULT;
                }
            }
        }
        if (!($request->getAttribute("Boton") === "Agregar ticket")) {
// Agrega los productos 
            $Items = getRMItemsGeneral($cVarVal, $FilterStatement, $FilterStatement1);
            if ($Items < 0) {
                $Msj = utils\Messages::RESPONSE_ERROR;
            } elseif ($Items > 0) {
                $Msj = utils\Messages::MESSAGES_FACTURAS_LOAD_OK;
                if (!empty($sanitize->sanitizeString("Tirilla"))) {
                    $tirillas = utils\IConnection::execSql($selectTirillas);
                    updateObservaciones($cVarVal, null, null, "Combustible/Monederos con tirilla: " . $tirillas["tirillas"]);
                }
            }

            TotalizaFactura($cVarVal, $fcDAO);
        }
        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en pagos: " . $ex);
    } finally {
        header("Location: $Return");
    }
}

if ($request->getAttribute("Calcula") === "Si") {
    $fcVO = $fcDAO->retrieve($request->getAttribute("busca"));
    $SqlAddT = "SELECT inv.descripcion,SUM(fcd.cantidad) cnt,SUM(fcd.importe) imp "
            . "FROM fcd LEFT JOIN inv ON fcd.producto=inv.id WHERE fcd.id=" . $request->getAttribute("busca") . " GROUP BY producto;";
    $Tadd = utils\IConnection::getRowsFromQuery($SqlAddT);
    foreach ($Tadd as $Ta) {
        $fcVO->setObservaciones($fcVO->getObservaciones() . " | " . $Ta["descripcion"] . " Cnt:" . number_format($Ta["cnt"], 1) . " Imp:" . number_format($Ta["imp"], 1) . " ");
        if (!$fcDAO->update($fcVO)) {
            error_log("Ha ocurrido un error");
        }
    }
}

if ($request->hasAttribute("op")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $cId = $sanitize->sanitizeInt("cId");

    try {
        $fcdVO = $fcdDAO->retrieve($cId, "idnvo");

        if ($request->getAttribute('action') === "devolucion") {
            $fcVO = $fcDAO->retrieve($fcdVO->getId());
            $updateFcd = "UPDATE fcd LEFT JOIN rm ON rm.id = fcd.ticket AND fcd.producto <= 10 LEFT JOIN vtaditivos vta ON vta.id = fcd.ticket AND fcd.producto > 10 SET fcd.ticket = -fcd.ticket, rm.uuid = '-----', vta.uuid = '-----' WHERE idnvo = " . $fcdVO->getIdnvo();
            $mysqli->query($updateFcd);
            $exists = "SELECT id FROM nc WHERE factura = " . $fcVO->getId() . " AND status = " . StatusFactura::ABIERTO . " LIMIT 1";
            $qry = $mysqli->query($exists);
            if ($qry->num_rows > 0) {
                $rs = $qry->fetch_array();
                $NotaCredito = $rs['id'];
            } else {
                $insertNc = "INSERT INTO nc (cliente, fecha, status, factura, formadepago, relacioncfdi, usr) "
                        . "VALUES ('" . $fcVO->getCliente() . "', NOW(), " . StatusFactura::ABIERTO . ", '" . $fcVO->getId() . "', '" . $fcVO->getFormadepago() . "', '" . $fcVO->getId() . "', '" . $usuarioSesion->getUsername() . "')";
                $mysqli->query($insertNc);
                $NotaCredito = $mysqli->insert_id;
            }
            $insertNcd = "
                    INSERT INTO ncd (id, producto, cantidad, precio, ieps, iva, importe, tipoc, preciob, id_ticket)
                    VALUES
                    ("
                    . $NotaCredito . ", '" . $fcdVO->getProducto() . "', "
                    . $fcdVO->getCantidad() . ", " . $fcdVO->getPrecio() . ", "
                    . $fcdVO->getIeps() . ", " . $fcdVO->getIva() . ", "
                    . $fcdVO->getImporte() . ", '" . $fcdVO->getTipoc() . "', "
                    . $fcdVO->getPreciob() . ",'" . $fcdVO->getTicket() . "')";
            $mysqli->query($insertNcd);
            TotalizaNotaCredito($NotaCredito);
            $Msj = "El ticket " . $fcdVO->getTicket() . " fue liberado. Timbre la Nota de Crédito de Devolución " . $NotaCredito . "  antes de generar la factura de consumo a fin de evitar duplicidad en su contabilidad.";
        } else if ($request->getAttribute("op") === utils\Messages::OP_DELETE) {

            if ($fcdVO->getTicket() > 0) {
                if ($clienteVO->getTipodepago() === TiposCliente::CREDITO || $clienteVO->getTipodepago() === TiposCliente::PREPAGO || $clienteVO->getTipodepago() === TiposCliente::TARJETA) {
                    $sql = "UPDATE cxc SET factura = null "
                            . "WHERE tm = 'C' AND referencia = '" . $fcdVO->getTicket() . "' AND cliente = '" . $fcVO->getCliente() . "' ";
                    if ($fcdVO->getProducto() <= 4) {
                        $sql .= " AND producto = '" . $fcdVO->getClavei() . "' LIMIT 1;";
                    } else {
                        $sql .= " AND producto = 'A' LIMIT 1;";
                    }
                    if (!$mysqli->query($sql)) {
                        error_log($sql);
                    }
                }
            } else if ($fcdVO->getTicket() == 0) {
                $sql = "UPDATE cxc SET cliente = -cliente, referencia = - referencia, cantidad = -cantidad,"
                        . "importe = -importe, factura = -factura "
                        . "WHERE tm = 'C' AND referencia = '" . $fcdVO->getIdnvo() . "' "
                        . "AND cliente = '" . $fcVO->getCliente() . "' AND concepto LIKE '%manual%' LIMIT 1";

                if (!$mysqli->query($sql)) {
                    error_log($sql);
                }
            }
            $fcdVO->setId("-" . $fcdVO->getId());
            $fcdVO->setTicket("-" . $fcdVO->getTicket());
            if ($fcdDAO->update($fcdVO)) {
                $id = -1 * $fcdVO->getTicket();
                $CxcVO = $cxcDAO->retrieve($id, "concepto LIKE '%Desde detalle de Factura%' AND referencia");
                if (is_numeric($CxcVO->getId())) {
                    $cxcDAO->remove($CxcVO->getId());
                }
                TotalizaFactura($cVarVal, $fcDAO);
                $Msj = utils\Messages::RESPONSE_VALID_DELETE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } else if ($request->getAttribute("op") === "Limpiar") {
            cleanDetail($cVarVal);
            TotalizaFactura($cVarVal, $fcDAO);
            updateObservaciones($cVarVal, null, null, "");
            $Msj = utils\Messages::MESSAGE_DEFAULT;
        } else if ($request->getAttribute("op") === "ReduceProducto") {
            $Update = "UPDATE fcd LEFT JOIN inv ON fcd.producto = inv.id  SET fcd.cantidad = fcd.cantidad - (" . $request->getAttribute("Importe") . " / fcd.preciob), "
                    . "fcd.importe = fcd.importe - " . $request->getAttribute("Importe") . " WHERE fcd.id = $cVarVal AND inv.descripcion='" . $request->getAttribute("Producto") . "'";
            utils\IConnection::execSql($Update);
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
 * @param int $factura
 */
function cleanDetail($factura) {
    $mysqli = iconnect();

    $sqlUpdateFcd = "UPDATE fcd SET id = -id, ticket = -ticket WHERE id = '" . $factura . "'";
    if (!$mysqli->query($sqlUpdateFcd)) {
        error_log($mysqli->error);
    }
}

/**
 * 
 * @param int $factura
 * @param array $Item
 * @param FcdDAO $fcdDAO
 * @param ClientesVO $clienteVO
 * @return string
 */
function fillFcd($factura, $Item, $fcdDAO, $clienteVO) {

    $mysqli = iconnect();

    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    if (is_array($Item) && count($Item) > 0) {
        if ($Item['factura'] === "0") {
            $fcdVO = new FcdVO();
            $fcdVO->setId($factura);
            $fcdVO->setProducto($Item['idProducto']);
            $fcdVO->setCantidad($Item['volumen']);
            $fcdVO->setPrecio($Item['precioUnitario']);
            $fcdVO->setIeps($Item['IEPS']);
            $fcdVO->setIva($Item['IVA']);
            $fcdVO->setImporte($Item['pesos']);
            $fcdVO->setTicket($Item['id']);
            $fcdVO->setTipoc($Item['quantifier']);
            $fcdVO->setPreciob($Item['precio']);
            $fcdVO->setDescuento($Item['descuento']); //not use
            $fcdVO->setIva_retenido(0);
            error_log("OBJ " . print_r($fcdVO), true);
            if (($id = $fcdDAO->create($fcdVO)) < 0) {
                $Msj = utils\Messages::RESPONSE_ERROR;
            } else {
                if (($clienteVO->getTipodepago() == TiposCliente::CREDITO || $clienteVO->getTipodepago() == TiposCliente::PREPAGO) && $Item['cxc'] > 0) {
                    $sql = "UPDATE cxc SET factura = '" . $factura . "' "
                            . "WHERE tm = 'C' AND cliente = " . $clienteVO->getId() . " "
                            . "AND referencia = '" . $fcdVO->getTicket() . "'  LIMIT 1";
                    if (!$mysqli->query($sql)) {
                        $Msj = utils\Messages::RESPONSE_ERROR;
                    }
                }
                $Msj = $fcdDAO::RESPONSE_VALID;
            }
        } else {
            $Msj = utils\Messages::MESSAGES_FACTURAS_EXISTS;
        }
    }
    return $Msj;
}

/**
 * 
 * @param int $factura
 * @param array $Item
 * @param FcdDAO $fcdDAO
 * @param ClientesVO $clienteVO
 * @return string
 */
function fillFcdConsignaciones($factura, $Item, $fcdDAO, $clienteVO) {
    global $DateComision;
    $mysqli = iconnect();
    $SqlComisiones = "SELECT * FROM omicrom.comisiones WHERE vigencia >= '" . $DateComision . "';";
    error_log($SqlComisiones);
    $SqlCom = $mysqli->query($SqlComisiones);
    $RsCom = $SqlCom->fetch_array();

    $MontoConIva = $RsCom["monto"] * 1.16;
    $MontoComision = $MontoConIva * $Item['volumen'];

    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    if (is_array($Item) && count($Item) > 0) {
        if ($Item['factura'] === "0") {
            $fcdVO = new FcdVO();
            $fcdVO->setId($factura);
            $fcdVO->setProducto($Item['idProducto']);
            $fcdVO->setCantidad($Item['volumen']);
            $fcdVO->setPrecio($RsCom["monto"]);
            $fcdVO->setIeps(0);
            $fcdVO->setIva($Item['IVA']);
            $fcdVO->setImporte($MontoComision);
            $fcdVO->setTicket($Item['id']);
            $fcdVO->setTipoc($Item['quantifier']);
            $fcdVO->setPreciob($MontoConIva);
            $fcdVO->setDescuento(0); //not use
            $fcdVO->setIva_retenido(0);
            if (($id = $fcdDAO->create($fcdVO)) < 0) {
                $Msj = utils\Messages::RESPONSE_ERROR;
            } else {
                if (($clienteVO->getTipodepago() == TiposCliente::CREDITO || $clienteVO->getTipodepago() == TiposCliente::PREPAGO) && $Item['cxc'] > 0) {
                    $sql = "UPDATE cxc SET factura = '" . $factura . "' "
                            . "WHERE tm = 'C' AND cliente = " . $clienteVO->getId() . " "
                            . "AND referencia = '" . $fcdVO->getTicket() . "'  LIMIT 1";
                    if (!$mysqli->query($sql)) {
                        $Msj = utils\Messages::RESPONSE_ERROR;
                    }
                }
                $Msj = $fcdDAO::RESPONSE_VALID;
            }
        } else {
            $Msj = utils\Messages::MESSAGES_FACTURAS_EXISTS;
        }
    }
    return $Msj;
}

/**
 * 
 * @param int $factura
 * @param FcVO $fcVO
 * @param FcDAO $fcDAO
 */
function TotalizaFactura($factura, $fcDAO) {

    $mysqli = iconnect();
    $cSQL = "
        SELECT 
        cantidad, total, importe, iva, total-importe-iva ieps, descuento 
        FROM (
        SELECT 
           ROUND( sum( cantidad ), 3) cantidad,
           ROUND( sum( total ) - sum(retenido), 2) total,
           ROUND( sum( cantidad * ( preciob - factorieps ) / (1 + factoriva) ), 2) importe,
           ROUND( sum(fiva), 2) iva,
           ROUND(SUM( descuento),2) descuento
        FROM (
           SELECT 
              iva factoriva,
              ieps factorieps,
              cantidad,
              importe total,
              preciob,
              descuento,
              (cantidad*(preciob - ieps)/(1+iva))*iva fiva,
              if(iva_retenido * importe / (1 + iva)=0,0,iva_retenido * importe / (1 + iva)) retenido
           FROM fcd WHERE id = '$factura') as SUB
        ) SUBQ
    ";

    $Ddd = $mysqli->query($cSQL)->fetch_array();

    $Cnt = 0;
    $Importe = 0;
    $Iva = 0;
    $Ieps = 0;
    $Total = 0;

    if ($Ddd[0] != 0) {
        $Cnt = $Ddd[0];
        $Importe = $Ddd['importe'];
        $Iva = $Ddd['iva'];
        $Ieps = $Ddd['ieps'];
        $Total = $Ddd['total'];
        $Descuento = $Ddd['descuento'];
    }
    $fcVO = $fcDAO->retrieve($factura);
    $fcVO->setCantidad($Cnt);
    $fcVO->setImporte($Importe);
    $fcVO->setIva($Iva);
    $fcVO->setIeps($Ieps);
    $fcVO->setTotal($Total);
    $fcVO->setDescuento($Descuento);
    if (!$fcDAO->update($fcVO)) {
        error_log("Ha ocurrido un error");
    }
}

function TotalizaNotaCredito($id) {

    $mysqli = iconnect();
    $cSQL = "UPDATE nc "
            . "JOIN ( "
            . "SELECT "
            . "id, "
            . "ROUND( SUM( cantidad ), 3 ) cantidad, "
            . "ROUND( SUM( importe ), 2 ) importe, "
            . "ROUND( SUM( importe * iva ), 2 ) iva, "
            . "ROUND( SUM( cantidad * ieps ), 2 ) ieps "
            . "FROM ncd WHERE id = " . $id . " GROUP BY id) ncd ON nc.id = ncd.id "
            . "SET nc.cantidad = ncd.cantidad, "
            . "nc.total = ncd.importe, "
            . "nc.importe = ncd.importe - ncd.iva - ncd.ieps, "
            . "nc.iva = ncd.iva, "
            . "nc.ieps = ncd.ieps "
            . "WHERE nc.id = " . $id;
    $mysqli->query($cSQL);
}

/**
 * 
 * @param string $filterRM
 * @param string $filterAdt
 * @param string $tipoDePago
 * @return array
 */
function getRMItems($filterRM, $filterAdt, $tipoDePago) {

    $mysqli = iconnect();

    $AndClauseRM = trim($filterRM) == "" ? "" : $filterRM;
    $AndClauseADT = trim($filterAdt) == "" ? "" : " AND " . $filterAdt;

    $cSQL = "SELECT
                TOT.factura,
                TOT.id," .
            ($tipoDePago === TiposCliente::CREDITO || $tipoDePago === TiposCliente::PREPAGO ? "cxc.id cxc" : "0 cxc") . ", 
                TOT.clave,
                TOT.idProducto,
                TOT.descripcion,
                TOT.precio,
                CASE WHEN TOT.iva > 1 THEN ROUND(TOT.iva/100, 2) ELSE ROUND(TOT.iva, 2) END IVA,
                TOT.ieps IEPS,
                TOT.unidad,
                TOT.volumen,
                TOT.descuento,
                TOT.pesos,
                TOT.precioUnitario,
                TOT.importe,
                (TOT.importe + TOT.taxIva + TOT.taxIeps) total,
                TOT.taxIva,
                TOT.taxIeps,
                TOT.quantifier
                
            FROM(
                    SELECT
                        SUBQ.id,
                        SUBQ.cliente,
                        SUBQ.clave,
                        SUBQ.idProducto,
                        SUBQ.descripcion,
                        SUBQ.precio,
                        SUBQ.iva,
                        SUBQ.ieps,
                        SUBQ.unidad,
                        SUBQ.volumen,
                        SUBQ.descuento,
                        SUBQ.quantifier,
                        ROUND(SUBQ.pesos, 4) pesos,
                        ROUND(SUBQ.preciouu, 4) AS precioUnitario,
                        ROUND(ROUND(SUBQ.volumen, 3)*ROUND(SUBQ.preciouu, 4), 6) importe,
                        ROUND(ROUND(SUBQ.volumen, 3)*ROUND(SUBQ.preciouu, 4) * ROUND(SUBQ.iva, 4), 4) AS taxIva,
                        ROUND(ROUND(SUBQ.volumen, 3)*ROUND(SUBQ.ieps, 4), 4) AS taxIeps,
                        IFNULL(fcd.id, 0) factura
                        FROM(
                            SELECT
                                   rm.id,
                                   rm.cliente,
                                   rm.precio,
                                   round((rm.precio-rm.ieps)/(1+rm.iva),3) preciouu,
                                   ROUND(IF('$tipoDePago' = 'Contado' AND rm.comprobante = 0,rm.importe/rm.precio,rm.volumen), 3) volumen,
                                   ROUND(IF('$tipoDePago' = 'Contado' AND rm.comprobante = 0,rm.importe,rm.pesos), 4) pesos,
                                   rm.iva,
                                   ROUND(rm.ieps, 6) ieps,
                                   com.clave,
                                   com.descripcion,
                                   inv.id idProducto,
                                   descuento,
                                   CASE WHEN com.clavei LIKE '%D' THEN '01' ELSE 'Lts.' END unidad,
                                   'C' quantifier
                            FROM rm
                            JOIN com ON rm.producto = com.clavei
                            JOIN inv ON com.descripcion = inv.descripcion AND inv.activo = 'Si' AND inv.rubro = 'Combustible'
                            WHERE $AndClauseRM AND rm.uuid = '-----'
                            AND rm.pesos > 0 AND rm.importe > 0
                            AND rm.tipo_venta = 'D' 
                            AND inv.rubro='Combustible'
                            UNION ALL
                            SELECT
                                vtaditivos.id,
                                vtaditivos.cliente,
                                vtaditivos.unitario precio,
                                ROUND(vtaditivos.unitario / (1+cia.iva/100), 6) preciouu,
                                ROUND(vtaditivos.cantidad, 3) volumen,
                                ROUND(vtaditivos.total, 4) pesos,
                                vtaditivos.iva,
                                ROUND(0.0000, 4) ieps,
                                vtaditivos.clave,
                                vtaditivos.descripcion,
                                vtaditivos.clave idProducto,
                                0,
                                'PZA' unidad,
                                'A' quantifier
                            FROM vtaditivos
                            JOIN cia ON TRUE
                            WHERE vtaditivos.uuid = '-----' AND vtaditivos.tm = 'C'
                            $AndClauseADT 
                        ) SUBQ
                        LEFT JOIN fcd ON SUBQ.id = fcd.ticket AND SUBQ.idProducto = fcd.producto
                ) TOT ";

    if ($tipoDePago === TiposCliente::CREDITO || $tipoDePago === TiposCliente::PREPAGO) {
        $cSQL .= " LEFT JOIN cxc ON cxc.referencia = TOT.id AND cxc.tm = 'C' AND cxc.cliente = TOT.cliente";
    }

    $cSQL .= " WHERE TOT.factura = 0";
    error_log("_________________________________________");
    error_log($cSQL);
    $rs = $mysqli->query($cSQL);
    error_log($cSQL);
    if (!$rs) {
        error_log($cSQL);
        error_log($mysqli->error);
    }
    return $rs;
}

/**
 * 
 * @param string $filterRM
 * @param string $filterAdt
 * @param string $tipoDePago
 * @return array
 */
function getRMItemsConsignaciones($filterRM, $filterAdt, $tipoDePago) {

    $mysqli = iconnect();

    $AndClauseRM = trim($filterRM) == "" ? "" : $filterRM;
    $AndClauseADT = trim($filterAdt) == "" ? "" : " AND " . $filterAdt;

    $cSQL = "SELECT
                TOT.factura,
                TOT.id," .
            ($tipoDePago === TiposCliente::CREDITO || $tipoDePago === TiposCliente::PREPAGO ? "cxc.id cxc" : "0 cxc") . ", 
                TOT.clave,
                TOT.idProducto,
                TOT.descripcion,
                TOT.precio,
                CASE WHEN TOT.iva > 1 THEN ROUND(TOT.iva/100, 2) ELSE ROUND(TOT.iva, 2) END IVA,
                TOT.ieps IEPS,
                TOT.unidad,
                TOT.volumen,
                TOT.pesos,
                TOT.precioUnitario,
                TOT.importe,
                (TOT.importe + TOT.taxIva + TOT.taxIeps) total,
                TOT.taxIva,
                TOT.taxIeps,
                TOT.quantifier
                
            FROM(
                    SELECT
                        SUBQ.id,
                        SUBQ.cliente,
                        SUBQ.clave,
                        SUBQ.idProducto,
                        SUBQ.descripcion,
                        SUBQ.precio,
                        SUBQ.iva,
                        SUBQ.ieps,
                        SUBQ.unidad,
                        SUBQ.volumen,
                        SUBQ.quantifier,
                        ROUND(SUBQ.pesos, 4) pesos,
                        ROUND(SUBQ.preciouu, 4) AS precioUnitario,
                        ROUND(ROUND(SUBQ.volumen, 3)*ROUND(SUBQ.preciouu, 4), 6) importe,
                        ROUND(ROUND(SUBQ.volumen, 3)*ROUND(SUBQ.preciouu, 4) * ROUND(SUBQ.iva, 4), 4) AS taxIva,
                        ROUND(ROUND(SUBQ.volumen, 3)*ROUND(SUBQ.ieps, 4), 4) AS taxIeps,
                        IFNULL(fcd.id, 0) factura
                        FROM(
                            SELECT
                                   rm.id,
                                   rm.cliente,
                                   rm.precio,
                                   round((rm.precio-rm.ieps)/(1+rm.iva),3) preciouu,
                                   ROUND(IF('$tipoDePago' = 'Contado' AND rm.comprobante = 0,rm.importe/rm.precio,rm.volumen), 3) volumen,
                                   ROUND(IF('$tipoDePago' = 'Contado' AND rm.comprobante = 0,rm.importe,rm.pesos), 4) pesos,
                                   rm.iva,
                                   ROUND(rm.ieps, 6) ieps,
                                   com.clave,
                                   com.descripcion,
                                   inv.id idProducto,
                                   CASE WHEN com.clavei LIKE '%D' THEN '01' ELSE 'Lts.' END unidad,
                                   'C' quantifier
                            FROM rm
                            JOIN com ON rm.producto = com.clavei
                            JOIN inv ON com.descripcion = inv.descripcion AND inv.activo = 'Si' AND inv.rubro = 'Combustible'
                            WHERE $AndClauseRM AND rm.uuid = '-----'
                            AND rm.pesos > 0 AND rm.importe > 0
                            AND rm.tipo_venta = 'N' 
                            UNION ALL
                            SELECT
                                vtaditivos.id,
                                vtaditivos.cliente,
                                vtaditivos.unitario precio,
                                ROUND(vtaditivos.unitario / (1+cia.iva/100), 6) preciouu,
                                ROUND(vtaditivos.cantidad, 3) volumen,
                                ROUND(vtaditivos.total, 4) pesos,
                                vtaditivos.iva,
                                ROUND(0.0000, 4) ieps,
                                vtaditivos.clave,
                                vtaditivos.descripcion,
                                vtaditivos.clave idProducto,
                                'PZA' unidad,
                                'A' quantifier
                            FROM vtaditivos
                            JOIN cia ON TRUE
                            WHERE vtaditivos.uuid = '-----' AND vtaditivos.tm = 'C'
                            $AndClauseADT 
                        ) SUBQ
                        LEFT JOIN fcd ON SUBQ.id = fcd.ticket AND SUBQ.idProducto = fcd.producto
                ) TOT ";

    if ($tipoDePago === TiposCliente::CREDITO || $tipoDePago === TiposCliente::PREPAGO) {
        $cSQL .= " LEFT JOIN cxc ON cxc.referencia = TOT.id AND cxc.tm = 'C' AND cxc.cliente = TOT.cliente";
    }

    $cSQL .= " WHERE TOT.factura = 0";
    $rs = $mysqli->query($cSQL);
    error_log($cSQL);
    if (!$rs) {
        error_log($cSQL);
        error_log($mysqli->error);
    }
    return $rs;
}

/**
 * 
 * @param type $factura
 * @param type $filterRM
 * @param type $filterAdt
 * @return type
 */
function getRMItems_CRE_PRE($factura, $filterRM, $filterAdt) {

    $mysqli = iconnect();

    $AndClauseRM = trim($filterRM) == "" ? "" : $filterRM . " AND ";
    $AndClauseADT = trim($filterAdt) == "" ? "" : " AND " . $filterAdt;

    $initSQL = "SELECT
                    SUBQ.id,
                    SUBQ.cliente,
                    SUBQ.clave,
                    SUBQ.clavei,
                    SUBQ.idProducto,
                    SUBQ.descripcion,
                    SUBQ.precio,
                    SUBQ.iva,
                    SUBQ.ieps,
                    SUBQ.unidad,
                    SUBQ.volumen,
                    SUBQ.descuento,
                    SUBQ.quantifier,
                    ROUND(SUBQ.pesos, 4) pesos,
                    ROUND(SUBQ.preciouu, 4) AS precioUnitario,
                    ROUND(ROUND(SUBQ.volumen, 3)*ROUND(SUBQ.preciouu, 4), 6) importe,
                    ROUND(ROUND(SUBQ.volumen, 3)*ROUND(SUBQ.preciouu, 4) * ROUND(SUBQ.iva, 4), 4) AS taxIva,
                    ROUND(ROUND(SUBQ.volumen, 3)*ROUND(SUBQ.ieps, 4), 4) AS taxIeps,
                    IFNULL(fcd.id, 0) factura
                    FROM(
                        SELECT
                               rm.id,
                               rm.cliente,
                               rm.precio,
                               (rm.precio-rm.ieps)/(1+rm.iva) preciouu,
                               ROUND(rm.volumen, 3) volumen,
                               ROUND(rm.pesos, 4) pesos,
                               rm.iva,
                               ROUND(rm.ieps, 6) ieps,
                               com.clave,
                               com.descripcion,
                               inv.id idProducto,
                               CASE WHEN com.clavei LIKE '%D' THEN '01' ELSE 'Lts.' END unidad,
                               'C' quantifier,
                               rm.producto clavei,
                               rm.descuento
                        FROM rm
                        JOIN com ON rm.producto = com.clavei
                        JOIN inv ON com.descripcion = inv.descripcion AND inv.activo = 'Si'
                        WHERE $AndClauseRM rm.uuid = '-----'
                        AND rm.pesos > 0
                        AND rm.tipo_venta = 'D' 
                        AND inv.rubro='Combustible'
                        UNION ALL
                        SELECT
                            vtaditivos.id,
                            vtaditivos.cliente,
                            vtaditivos.unitario precio,
                            ROUND(vtaditivos.unitario / (1+cia.iva/100), 6) preciouu,
                            ROUND(vtaditivos.cantidad, 3) volumen,
                            ROUND(vtaditivos.total, 4) pesos,
                            vtaditivos.iva,
                            ROUND(0.0000, 4) ieps,
                            vtaditivos.clave,
                            vtaditivos.descripcion,
                            vtaditivos.clave idProducto,
                            'PZA' unidad,
                            'A' quantifier,
                            'A' clavei,
                            0 descuento
                        FROM vtaditivos
                        JOIN cia ON 1 = 1
                        WHERE vtaditivos.uuid = '-----' AND vtaditivos.tm = 'C' $AndClauseADT 
                    ) SUBQ
                    LEFT JOIN fcd ON SUBQ.id = fcd.ticket AND SUBQ.idProducto = fcd.producto
                    ";

    $cSQL = "INSERT INTO fcd (id,producto,cantidad,precio,iva,ieps,importe,ticket,tipoc,preciob,descuento)
             SELECT
                '$factura' factura,
		TOT.idProducto producto,
                TOT.volumen cantidad,
                TOT.precioUnitario precio,
                CASE WHEN TOT.iva > 1 THEN ROUND(TOT.iva/100, 2) ELSE ROUND(TOT.iva, 2) END iva,
                TOT.ieps,
                TOT.pesos importe,
                TOT.id ticket,
		TOT.quantifier tipoc,
		TOT.precio preciob,
		TOT.descuento
                FROM($initSQL) TOT 
                LEFT JOIN cxc ON cxc.referencia = TOT.id AND cxc.tm = 'C' AND cxc.cliente = TOT.cliente 
                WHERE TOT.factura = 0";
    error_log("______________________");
    error_log($cSQL);
    if (($mysqli->query($cSQL))) {
        if ($mysqli->affected_rows > 0) {
            $updateCxc = "UPDATE cxc,(
                            SELECT fcd.*,IFNULL(rm.producto , 'A') clavei 
                            FROM fcd 
                            LEFT JOIN inv ON fcd.producto = inv.id
                            LEFT JOIN rm ON fcd.ticket = rm.id AND fcd.producto < 5
                            WHERE fcd.id = '$factura') SUB
                            SET cxc.factura = '$factura'
                            WHERE cxc.referencia = SUB.ticket AND cxc.producto = SUB.clavei AND cxc.tm = 'C';";
            if ($mysqli->query($updateCxc)) {
                error_log("ROWS CXC: " . $mysqli->affected_rows);
                return FcDAO::RESPONSE_VALID;
            }
        }
    } else {
        error_log($cSQL);
        error_log($mysqli->error);
    }
    return null;
}

/**
 * 
 * @param DateTime $inicial
 * @param DateTime $final
 * @param string $tipo
 * @param int $factura
 */
function insertFCP($inicial, $final, $tipo, $factura) {

    global $mysqli;

    $query = "SELECT * FROM fcp WHERE factura = '$factura';";
    $result = $mysqli->query($query);
    $rows = $result->fetch_array();
    if (!empty($rows)) {
        $query = "UPDATE fcp SET inicial = '$inicial',final = '$final',tipo = '$tipo' WHERE factura = '$factura'";
    } else {
        $query = "INSERT INTO fcp (factura,inicial,final,tipo) VALUES ('$factura','$inicial','$final','$tipo')";
    }
    if (!($mysqli->query($query))) {
        error_log($mysqli->error);
    }
}

/**
 * 
 * @param type $factura
 * @param type $F_I
 * @param type $F_F
 * @param type $Concepto
 * @param type $Fecha
 */
function updateObservaciones($factura, $F_I = null, $F_F = null, $Concepto = "Combustible", $Fecha = NULL, $Pago = 0) {

    $mysqli = iconnect();

    if ($Concepto === "Combustible/Tarjeta") {
        $tp = "04";
    } else if ($Concepto === "Combustible/Monederos") {
        $tp = "05";
    } else {
        $tp = "01";
    }
    $SUuid = "SELECT cli.rfc FROM fc LEFT JOIN cli ON cli.id=fc.cliente WHERE fc.id = " . $factura;
    $sr = $mysqli->query($SUuid)->fetch_array();
    if ($sr["rfc"] === "XAXX010101000") {
        $UsoCfdi = "S01";
    } else {
        $UsoCfdi = "G03";
    }
    if (!is_null($Fecha)) {
        $query = "UPDATE fc SET observaciones = 'Factura de $Concepto correspondiente al $Fecha' , formadepago='$tp', usocfdi='$UsoCfdi' WHERE id = '$factura'";
    } elseif (!empty($Pago)) {
        $query = "UPDATE fc SET observaciones = 'Factura de $Concepto correspondiente al pago $Pago', formadepago='$tp', usocfdi='$UsoCfdi' WHERE id = '$factura'";
    } elseif (!is_null($F_I) && !is_null($F_F)) {
        $query = "UPDATE fc SET observaciones = 'Factura de $Concepto correspondiente del $F_I al $F_F', formadepago='$tp', usocfdi='$UsoCfdi' WHERE id = '$factura'";
    } else {
        $query = "UPDATE fc SET observaciones = 'Factura de $Concepto', formadepago='$tp', usocfdi='$UsoCfdi' WHERE id = '$factura'";
    }
    if (!($mysqli->query($query))) {
        error_log($mysqli->error);
    }
}

function getRMItemsGeneral($factura, $filterRM, $filterAdt) {

    $mysqli = iconnect();

    $AndClauseRM = trim($filterRM) == "" ? "" : $filterRM;
    $AndClauseADT = trim($filterAdt) == "" ? "" : " AND (" . $filterAdt . ")";

    if (!empty($AndClauseRM) && !empty($AndClauseADT)) {
        $complement = " SELECT
                            rm.id,
                            rm.cliente,
                            rm.precio,
                            (rm.precio-rm.ieps)/(1+rm.iva) preciouu,
                            ROUND(rm.importe/rm.precio, 3) volumen,
                            ROUND(rm.importe, 4) pesos,
                            rm.iva,
                            ROUND(rm.ieps, 6) ieps,
                            com.clave,
                            com.descripcion,
                            inv.id idProducto,
                            CASE WHEN com.clave = '34006'  THEN '01' ELSE 'Lts.' END unidad,
                            'C' quantifier,
                            IF(rm.importe > rm.descuento,rm.descuento, 0) descuento
                        FROM rm
                        JOIN cli ON rm.cliente = cli.id 
                        JOIN com ON rm.producto = com.clavei
                        JOIN inv ON com.descripcion = inv.descripcion AND inv.activo = 'Si'
                        WHERE $AndClauseRM AND rm.uuid = '-----'
                        AND rm.importe > 0.5
                        AND rm.pesos > 0
                        AND rm.volumen > 0
                        AND rm.tipo_venta = 'D' 
                        AND inv.rubro='Combustible'
                        UNION ALL  
                        
                        SELECT
                            vtaditivos.id,
                            vtaditivos.cliente,
                            vtaditivos.unitario precio,
                            ROUND(vtaditivos.unitario / (1+cia.iva/100), 6) preciouu,
                            ROUND(vtaditivos.cantidad, 3) volumen,
                            ROUND(vtaditivos.total, 4) pesos,
                            vtaditivos.iva,
                            ROUND(0.0000, 6) ieps,
                            vtaditivos.clave,
                            vtaditivos.descripcion,
                            vtaditivos.clave idProducto,
                            'PZA' unidad,
                            'A' quantifier,
                            0 descuento
                        FROM vtaditivos
                        JOIN cia ON TRUE
                        LEFT JOIN cli ON vtaditivos.cliente = cli.id
                        WHERE vtaditivos.cantidad > 0 AND vtaditivos.tm = 'C'
                        AND vtaditivos.uuid = '-----'  $AndClauseADT";
//error_log($complement);
    } elseif (!empty($AndClauseRM)) {
        $complement = " SELECT
                            rm.id,
                            rm.cliente,
                            rm.precio,
                            (rm.precio-rm.ieps)/(1+rm.iva) preciouu,
                            ROUND(rm.importe/rm.precio, 3) volumen,
                            ROUND(rm.importe, 4) pesos,
                            rm.iva,
                            ROUND(rm.ieps, 6) ieps,
                            com.clave,
                            com.descripcion,
                            inv.id idProducto,
                            CASE WHEN com.clave = '34006'  THEN '01' ELSE 'Lts.' END unidad,
                            'C' quantifier,
                            IF(rm.importe > rm.descuento,rm.descuento, 0) descuento
                        FROM rm
                        JOIN cli ON rm.cliente = cli.id 
                        JOIN com ON rm.producto = com.clavei
                        JOIN inv ON com.descripcion = inv.descripcion AND inv.activo = 'Si'
                        WHERE $AndClauseRM AND rm.uuid = '-----'
                        AND rm.importe > 0.5
                        AND rm.pesos > 0
                        AND rm.volumen > 0
                        AND rm.tipo_venta = 'D'";
    } elseif (!empty($AndClauseADT)) {
        $complement = " SELECT
                            vtaditivos.id,
                            vtaditivos.cliente,
                            vtaditivos.unitario precio,
                            ROUND(vtaditivos.unitario / (1+cia.iva/100), 6) preciouu,
                            ROUND(vtaditivos.cantidad, 3) volumen,
                            ROUND(vtaditivos.total, 4) pesos,
                            vtaditivos.iva,
                            ROUND(0.0000, 6) ieps,
                            vtaditivos.clave,
                            vtaditivos.descripcion,
                            vtaditivos.clave idProducto,
                            'PZA' unidad,
                            'A' quantifier,
                            0 descuento
                        FROM vtaditivos
                        JOIN cia ON TRUE
                        LEFT JOIN cli ON vtaditivos.cliente = cli.id
                        LEFT JOIN inv ON inv.id = vtaditivos.clave
                        WHERE vtaditivos.cantidad > 0 AND vtaditivos.tm = 'C'
                        AND vtaditivos.uuid = '-----'  $AndClauseADT";
    }

    error_log($complement);

    $cSQL = "
            INSERT INTO fcd (id,producto,cantidad,precio,iva,ieps,importe,ticket,tipoc,preciob,descuento)
            SELECT
                $factura factura,
		TOT.idProducto producto,
                TOT.volumen cantidad,
                TOT.precioUnitario precio,
                CASE WHEN TOT.iva > 1 THEN ROUND(TOT.iva/100, 2) ELSE ROUND(TOT.iva, 2) END iva,
                TOT.ieps,
                TOT.pesos importe,
                TOT.id ticket,
		TOT.quantifier tipoc,
		TOT.precio preciob,
		TOT.descuento descuento
            FROM(
                SELECT
                    SUBQ.id,
                    SUBQ.cliente,
                    SUBQ.clave,
                    SUBQ.idProducto,
                    SUBQ.descripcion,
                    SUBQ.precio,
                    SUBQ.iva,
                    SUBQ.ieps,
                    SUBQ.unidad,
                    SUBQ.volumen,
                    SUBQ.quantifier,
                    ROUND(SUBQ.pesos, 4) pesos,
                    ROUND(SUBQ.preciouu, 4) AS precioUnitario,
                    ROUND(ROUND(SUBQ.volumen, 3)*ROUND(SUBQ.preciouu, 4), 6) importe,
                    ROUND(ROUND(SUBQ.volumen, 3)*ROUND(SUBQ.preciouu, 4) * ROUND(SUBQ.iva, 4), 4) AS taxIva,
                    ROUND(ROUND(SUBQ.volumen, 3)*ROUND(SUBQ.ieps, 4), 4) AS taxIeps,
                    IFNULL(fcd.id, 0) factura,
                    IF(SUBQ.descuento>0,SUBQ.descuento,0.00) descuento
                FROM (
                    $complement        
                ) SUBQ
                LEFT JOIN fcd ON SUBQ.id = fcd.ticket AND SUBQ.idProducto = fcd.producto
        ) TOT WHERE TOT.factura = 0";

    error_log($cSQL);
    if ($mysqli->query($cSQL)) {
        return $mysqli->affected_rows;
    } else {
        error_log($mysqli->error);
        error_log($cSQL);
        return -1;
    }
}

/**
 * 
 * @param int $ticket
 * @param ClientesVO $clienteVO
 * @param string $tipo
 * @return array()
 */
function verificaTicketCxc($ticket, $clienteVO, $tipo = "C") {
    $array = array();
    $mysqli = iconnect();

    $array['tipodepago'] = $clienteVO->getTipodepago();
    $array['siCxc'] = FALSE;
    $array['mensaje'] = NULL;

    if ($clienteVO->getTipodepago() === TiposCliente::CREDITO || $clienteVO->getTipodepago() === TiposCliente::PREPAGO || $clienteVO->getTipodepago() === TiposCliente::TARJETA) {
        if ($tipo === "C") {
            $sqlC = "SELECT IFNULL(referencia,0) referencia,IFNULL(cliente,0) cliente FROM cxc WHERE referencia = '$ticket' AND tm = 'C' AND producto <> 'A' LIMIT 1;";
        } else {
            $sqlC = "SELECT IFNULL(referencia,0) referencia,IFNULL(cliente,0) cliente FROM cxc WHERE referencia = '$ticket' AND tm = 'C' AND producto = 'A' LIMIT 1;";
        }
//error_log($sqlC);
        $cxc = $mysqli->query($sqlC)->fetch_array();
        if (($cxc['referencia'] == $ticket && $cxc['cliente'] == $clienteVO->getId()) || validaTicketLibre($clienteVO)) {
            error_log("VALida de W ");
            $array['siCxc'] = TRUE;
        } elseif (($cxc['referencia'] == 0 || empty($cxc['referencia']))) {
            $array['mensaje'] = "El ticket no se encuetra cargado en el estado de cuenta del cliente. Favor de asignalo en el corte correspondiente.";
        } elseif ($cxc['cliente'] != $clienteVO->getId() || empty($cxc['cliente'])) {
            $array['mensaje'] = "El ticket no pertenece al cliente, favor de verificarlo";
        }
    } else {
        $array['siCxc'] = TRUE;
    }

    $sql_fcd = "SELECT cli.rfc,fc.serie,fc.folio,fc.origen,fcd.ticket 
                FROM cli,fc,fcd 
                WHERE 1=1 AND cli.id = fc.cliente AND fc.id = fcd.id AND fcd.ticket = '$ticket'";
    if ($tipo === "C") {
        $sql_fcd .= " AND fcd.producto <= 4";
    } else {
        $sql_fcd .= " AND fcd.producto > 4";
    }

    $fcd = $mysqli->query($sql_fcd)->fetch_array();

    $array['fcd'] = FALSE;
    if (!empty($fcd['ticket']) && $fcd['ticket'] == $ticket) {
        $array['fcd'] = TRUE;
        $array['mensaje'] = "El ticket [$ticket] se encuentra cargado en el Folio: " . (!empty($fcd['serie']) ? $fcd['serie'] . "-" : "") . $fcd['folio'];
        if ($fcd['rfc'] === FcDAO::RFC_GENERIC) {
            $array['mensaje'] = $array['mensaje'] . ". Módulo de Facturas al público en general";
        } else {
            if ($fcd['origen'] === "3") {
                $array['mensaje'] = $array['mensaje'] . ". Módulo de Facturas en Linea";
            }
        }
    }
    if ($mysqli != null) {
        $mysqli->close();
    }
    return $array;
}

/**
 * 
 * @param ClientesVO $clienteVO
 * @return bool
 */
function validaTicketLibre($clienteVO) {
    global $freeticket;

    error_log(print_r($freeticket, true));

    return (($clienteVO->getTipodepago() == TiposCliente::CREDITO || $clienteVO->getTipodepago() == TiposCliente::PREPAGO) && $freeticket['valor'] == '1' );
}

$Tipo = "SELECT cli.tipodepago FROM fc LEFT JOIN cli ON cli.id=fc.cliente WHERE fc.id= " . $cVarVal . ";";
$Fp = utils\IConnection::execSql($Tipo);
if ($Fp["tipodepago"] === "Monedero") {
    $sql = "SELECT "
            . "CONCAT( cia.cre, '-', fcd.ticket ) NoIdentificacion, "
            . "inv.inv_cunidad ClaveUnidad, "
            . "inv.inv_cproducto ClaveProdServ, "
            . "CONCAT( inv.descripcion, IF( fcd.ticket = 0, ' Captura Manual', CONCAT( ' Ticket no: ' , fcd.ticket ) ) ) Descripcion, "
            . "fcd.factoriva, "
            . "fcd.factorieps, "
            . "fcd.factorivaretenido,"
            . "fcd.factorisrretenido,"
            . "fcd.tax_isr_retenido,"
            . "fcd.preciop,"
            . "fcd.clave_producto,"
            . "fcd.idnvo,"
            . "fcd.preciou + ROUND( IF( cli.desgloseIEPS = 'S', 0.0000, fcd.factorieps ) , 4 ) ValorUnitario, "
            . "fcd.cantidad base_ieps, "
            . "fcd.subtotal base_iva, "
            . "fcd.folio, "
            . "ROUND( fcd.subtotal + diferencia + IF( cli.desgloseIEPS = 'S', 0.0000, fcd.tax_ieps ), 4 ) Importe, "
            . "fcd.cantidad Cantidad,fc.observaciones obsrfc, "
            . "fcd.tax_iva, "
            . "IF( cli.desgloseIEPS = 'S', fcd.tax_ieps, 0.00 ) tax_ieps "
            . "FROM ( "
            . "SELECT "
            . "id folio,"
            . "idnvo, "
            . "ticket, "
            . "clave_producto, "
            . "factoriva, "
            . "factorieps, "
            . "factorivaretenido,"
            . "factorisrretenido,"
            . "A.preciop, "
            . "preciou, "
            . "cantidad, "
            . "ROUND( cantidad * preciou * factorisrretenido, 2 ) tax_isr_retenido,"
            . "ROUND( cantidad * preciou, 2 ) subtotal, "
            . "ROUND( cantidad * preciou * factoriva, 2 ) tax_iva, "
            . "ROUND( cantidad * factorieps, 2 ) tax_ieps, "
            . "total, "
            . "total - ROUND( cantidad * preciou, 2 ) - ROUND( cantidad * preciou * factoriva, 2 ) - ROUND( cantidad * factorieps, 2 ) diferencia "
            . "FROM ( "
            . "SELECT "
            . "fcd.id, "
            . "fcd.idnvo,"
            . "fcd.ticket ticket, "
            . "fcd.producto clave_producto, "
            . "CAST( fcd.iva AS DECIMAL( 10, 6 ) ) factoriva, "
            . "CAST( fcd.ieps AS DECIMAL( 10, 6 ) ) factorieps, "
            . "CAST( fcd.iva_retenido AS DECIMAL( 10, 6 ) ) factorivaretenido, "
            . "CAST( fcd.isr_retenido AS DECIMAL( 10, 6 ) ) factorisrretenido, "
            . "fcd.preciob preciop, "
            . "ROUND( ( fcd.preciob-fcd.ieps )/( 1+fcd.iva ), 4 ) preciou, "
            . "ROUND( fcd.importe/fcd.preciob, 4 ) cantidad, "
            . "ROUND( fcd.importe, 2 ) total "
            . "FROM fcd "
            . "WHERE fcd.id = " . $cVarVal . " "
            . ") A "
            . ") fcd "
            . "JOIN ( SELECT IFNULL( ( SELECT permiso valor FROM permisos_cre WHERE catalogo = 'VARIABLES_EMPRESA' AND llave = 'PERMISO_CRE' ), '' ) cre ) cia ON TRUE "
            . "JOIN inv ON inv.id = fcd.clave_producto "
            . "JOIN fc ON fcd.folio = fc.id "
            . "JOIN cli ON cli.id = fc.cliente "
            . "ORDER BY NoIdentificacion, ValorUnitario, factorieps";
    $Vvl = utils\IConnection::getRowsFromQuery($sql);
    foreach ($Vvl as $rs) {
        $numDecPrecio = 4;
        $numDecCant = 2;
        $num2 = pow(10, -$numDecCant) / 2;
        $num = pow(10, -$numDecPrecio) / 2;
        $LimitInferior = (number_format($rs['Cantidad'], 3, '.', '') - $num2) * (number_format($rs['ValorUnitario'], 4, '.', '') - $num);
        $LimitSuperior = (number_format($rs['Cantidad'], 3, '.', '') + $num) * (number_format($rs['ValorUnitario'], 4, '.', '') + $num);
        if ($rs['Importe'] > $LimitSuperior || $rs['Importe'] < $LimitInferior) {
            error_log($rs["clave_producto"] . " Ticket no. " . $rs["NoIdentificacion"] . " IdNvo : " . $rs['idnvo'] . "  Limite Superior " . $LimitSuperior . " E Inferior  " . $LimitInferior . " Importe : " . $rs['Importe']);
        }
        $Update = "";
        if ($LimitInferior > $rs['Importe']) {
            $Update = "UPDATE fcd SET preciob = preciob + 0.01 WHERE idnvo = " . $rs['idnvo'];
            utils\IConnection::execSql($Update);
        } elseif ($LimitSuperior < $rs['Importe']) {
            $Update = "UPDATE fcd SET preciob = preciob - 0.01 WHERE idnvo = " . $rs['idnvo'];
            utils\IConnection::execSql($Update);
        }
    }
}    
