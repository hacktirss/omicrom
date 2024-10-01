<?php

#Librerias
include_once ('data/CtDAO.php');
include_once ('data/IslaDAO.php');
include_once ('data/RmDAO.php');
include_once ('data/FcDAO.php');
include_once ('data/ClientesDAO.php');
include_once ('data/CxcDAO.php');
include_once ('data/ProductoDAO.php');
include_once ('data/VentaAditivosDAO.php');
include_once ('data/BancosDAO.php');
include_once ('data/ManDAO.php');
include_once ('data/EgrDAO.php');
include_once ('data/CxdDAO.php');
include_once ('data/VendedorDAO.php');
include_once('data/BitacoraDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "cambiotur.php?";

$nameVariableReturn = "returnLink";
if ($request->hasAttribute($nameVariableReturn)) {
    $Return = $request->getAttribute($nameVariableReturn) . "?";
}

$ctDAO = new CtDAO();
$islaDAO = new IslaDAO();
$rmDAO = new RmDAO();
$clienteDAO = new ClientesDAO();
$cxcDAO = new CxcDAO();
$ciaDAO = new CiaDAO();
$productoDAO = new ProductoDAO();
$vtAditivosDAO = new VentaAditivosDAO();
$bancosDAO = new BancosDAO();
$manDAO = new ManDAO();
$egrDAO = new EgrDAO();
$cxdDAO = new CxdDAO();
$vendedorDAO = new VendedorDAO();
$BitacoraDAO = new BitacoraDAO();

$nameVariableSession = "CambioDeTurnoDetalle";

$selectmachaca = "SELECT valor_variable('LIMITE_MACHACA') valor";
$mach = $mysqli->query($selectmachaca)->fetch_array();
$machaca = $mach['valor'];

if ($request->hasAttribute("criteria") && $request->getAttribute("criteria") === "ini") {
    if (!($request->hasAttribute("Corte"))) {
        $islaVO = $islaDAO->retrieve(1, "isla");
        utils\HTTPUtils::setSessionBiValue($nameVariableSession, "cVarVal", $islaVO->getCorte());
    }
}

if ($request->hasAttribute("Corte")) {
    utils\HTTPUtils::setSessionBiValue($nameVariableSession, "cVarVal", $sanitize->sanitizeInt("Corte"));
}

if ($request->hasAttribute("Limpia")) {
    utils\HTTPUtils::setSessionBiValue($nameVariableSession, "ClienteCorte", "");
}

if ($request->hasAttribute("Cliente")) {
    utils\HTTPUtils::setSessionBiValue($nameVariableSession, "ClienteCorte", $request->getAttribute("Cliente"));
}

$SCliente = utils\HTTPUtils::getSessionBiValue($nameVariableSession, "ClienteCorte");
$Corte = utils\HTTPUtils::getSessionBiValue($nameVariableSession, "cVarVal");
$ciaVO = $ciaDAO->retrieve(1);

$ConcentrarVtasTarjeta = $ciaVO->getVentastarxticket();
$cttarjetas = $mysqli->query("SELECT COUNT( * ) items FROM cttarjetas WHERE id = '$Corte'")->fetch_array();
if ($cttarjetas['items'] > 0) {
    $ConcentrarVtasTarjeta = "S";
}

$islaVO = $islaDAO->retrieve(1, "isla");

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $ctVO = $ctDAO->retrieve($Corte);

    //$Cliente = $sanitize->sanitizeInt("Cliente");
    $Cliente = strpos($SCliente, "|") > 0 ? trim(substr($SCliente, 0, strpos($SCliente, "|"))) : trim($SCliente);
    $Ticket = $sanitize->sanitizeInt("Ticket");
    try {
        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            $Ticket = $sanitize->sanitizeInt("Ticket");
            if ($ConcentrarVtasTarjeta === ConcentrarTarjetasCorte::SI && $request->hasAttribute("Vendedor")) {
                $importe = $sanitize->sanitizeFloat("Importe");
                $vendedor = $sanitize->sanitizeInt("Vendedor");
                $vaucher = $sanitize->sanitizeString("Vc");

                $insertCtTarjetas = "INSERT INTO cttarjetas
                        (id,banco,importe,vendedor,vaucher)
                        VALUES
                        ('$Corte','$Cliente','$importe','$vendedor','$vaucher')";

                if (($mysqli->query($insertCtTarjetas))) {
                    $Ticket = $mysqli->insert_id;
                    $Msj = utils\Messages::RESPONSE_VALID_CREATE;

                    $updateCxc = "UPDATE cxc SET cliente = -cliente, referencia = -referencia, corte = -corte "
                            . "WHERE referencia='$Ticket' AND tm='C' AND corte = '$Corte' LIMIT 1";
                    if (!( $mysqli->query($updateCxc))) {
                        error_log($mysqli->error);
                    }
                    $rmVO = $rmDAO->retrieve($Ticket);
                    $cxcVO = new CxcVO();
                    $cxcVO->setCliente($Cliente);
                    $cxcVO->setPlacas("");
                    $cxcVO->setReferencia($Ticket);
                    $cxcVO->setFecha(date("Y-m-d", strtotime($rmVO->getFin_venta())));
                    $cxcVO->setHora(date("H:i:s", strtotime($rmVO->getFin_venta())));
                    $cxcVO->setTm("C");
                    $cxcVO->setConcepto("Venta del corte $Corte Vendedor: $vendedor");
                    $cxcVO->setCantidad(1);
                    $cxcVO->setImporte($importe);
                    $cxcVO->setRecibo(0);
                    $cxcVO->setCorte($Corte);
                    $cxcVO->setRubro("-----");
                    $cxcVO->setProducto("-");

                    if (($cxcDAO->create($cxcVO)) < 0) {
                        $Msj = utils\Messages::RESPONSE_ERROR;
                    }
                } else {
                    error_log($mysqli->error);
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            } elseif ($Ticket > 0 && $request->hasAttribute("Dolares")) {
                $rmVO = $rmDAO->retrieve($Ticket);
                $dolares = $sanitize->sanitizeFloat("Dolares");

                $selectDivisa = "SELECT tipo_de_cambio FROM divisas WHERE clave = 'USD' LIMIT 1;";
                $divisas = $mysqli->query($selectDivisa)->fetch_array();

                if (is_array($divisas) && $divisas["tipo_de_cambio"] > 0) {
                    $real = $dolares * $divisas["tipo_de_cambio"];

                    $deleteFormas = "DELETE FROM formas_de_pago WHERE id = '$Ticket';";
                    if (!($mysqli->query($deleteFormas))) {
                        error_log($mysqli->error);
                    }

                    if ($real == $rmVO->getPesos()) {
                        $result = $rmVO->getPesos() / $divisas["tipo_de_cambio"];
                    } else {
                        $result = $real / $divisas["tipo_de_cambio"];
                    }

                    $insertFormas = "INSERT INTO formas_de_pago VALUES ('$Ticket','01','USD','" . $divisas["tipo_de_cambio"] . "','$result')";
                    if (($mysqli->query($insertFormas))) {
                        $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                    } else {
                        $Msj = "Monto ingresado invalido!";
                    }
                } else {
                    $Msj = "No existen divisas disponibles";
                }
            } elseif ($Ticket > 0) {
                $Permiso = "SELECT IF((pesos - 1)>importe || (pesos + 1) < importe , 0, 1) prm FROM rm WHERE id = $Ticket ORDER BY id DESC;";
                $Tper = utils\IConnection::execSql($Permiso);
                if ($Tper["prm"] == 1) {
                    $Msj = validarConsumo($Ticket, $Corte);
                    if (is_numeric($Msj)) {
                        $pagoReal = $sanitize->sanitizeFloat("Pagoreal");
                        $rmVO = $rmDAO->retrieve($Ticket);
                        $clienteVO = $clienteDAO->retrieve($Cliente);

                        if (!is_null($clienteVO) && $clienteVO->getId() > 0 && $clienteVO->getActivo() === "Si") {
                            if (is_numeric($pagoReal) && $pagoReal > 0 && ($pagoReal - $machaca) <= $rmVO->getImporte() && (($pagoReal - $machaca) <= $rmVO->getImporte() || $clienteVO->getTipodepago() === TiposCliente::TARJETA || $clienteVO->getTipodepago() === TiposCliente::VALES || $clienteVO->getTipodepago() === TiposCliente::MONEDERO)) {
                                if ($ctVO->getStatusctv() === StatusCorte::ABIERTO) {
                                    $Concepto = "Venta de " . $clienteVO->getTipodepago();
                                    $Placas = empty($sanitize->sanitizeString("Placas")) ? $rmVO->getPlacas() : $sanitize->sanitizeString("Placas");
                                    $Vdm = empty($sanitize->sanitizeString("Vdm")) ? $rmVO->getVdm() : $sanitize->sanitizeString("Vdm");
                                    $Kilometraje = empty($sanitize->sanitizeString("Kilometraje")) ? $rmVO->getKilometraje() : $sanitize->sanitizeInt("Kilometraje");
                                    $clienteVO = $clienteDAO->retrieve($Cliente);
//                                $rmVO->setImporte($rmVO->getPesos());
                                    $rmVO->setCliente($Cliente);
                                    $rmVO->setPlacas($Placas);
                                    $rmVO->setVdm($Vdm);
                                    $rmVO->setKilometraje($Kilometraje);
                                    $rmVO->setPagoreal($pagoReal);
                                    $rmVO->setPuntos($clienteVO->getPuntos());
                                    $rmVO->setTipodepago($clienteVO->getTipodepago());
                                    $rmVO->setEnviado(0);
                                    if ($clienteVO->getTipodepago() === "Consignacion") {
                                        $rmVO->setTipo_venta("N");
                                    }


                                    if ($rmDAO->update($rmVO)) {
                                        $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                                        BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "CAMBIA VENTA [" . $Ticket . "] A CLIENTE: $Cliente [" . strtoupper($clienteVO->getTipodepago()) . "]");
                                        $updateCxc = "UPDATE cxc SET cliente = -cliente, referencia = -referencia, corte = -corte "
                                                . "WHERE referencia='$Ticket' AND tm='C' AND producto = '" . $rmVO->getProducto() . "' LIMIT 1";
                                        if (!( $mysqli->query($updateCxc))) {
                                            error_log($mysqli->error);
                                        }
                                        $DescuentoTicket = "SELECT descuento FROM rm WHERE id = " . $rmVO->getId();
                                        $DsTkt = utils\IConnection::execSql($DescuentoTicket);
                                        error_log("Agregando descuento " . $DsTkt["descuento"] . " Total :  " . $importe);

                                        $cxcVO = new CxcVO();
                                        $cxcVO->setCliente($clienteVO->getId());
                                        $cxcVO->setPlacas($Placas);
                                        $cxcVO->setReferencia($Ticket);
                                        $cxcVO->setFecha(date("Y-m-d", strtotime($rmVO->getFin_venta())));
                                        $cxcVO->setHora(date("H:i:s", strtotime($rmVO->getFin_venta())));
                                        $cxcVO->setConcepto($Concepto);
                                        $cxcVO->setCantidad($rmVO->getVolumen());
                                        $cxcVO->setImporte($pagoReal - $DsTkt["descuento"]);
                                        $cxcVO->setCorte($Corte);
                                        $cxcVO->setProducto($rmVO->getProducto());

                                        if (($cxcDAO->create($cxcVO)) < 0) {
                                            $Msj = utils\Messages::RESPONSE_ERROR;
                                        } else {
                                            buscarVentasAceites($Ticket, $clienteVO->getId());
                                        }
                                    } else {
                                        $Msj = utils\Messages::RESPONSE_ERROR;
                                    }
                                } else {
                                    $Msj = "Lo sentimos!, el corte actual ha sido cerrado, no es posible hacer ningun movimiento";
                                }
                            } else {
                                $Msj = "El valor ingresado  excede el limite establecido";
                            }
                        } else {
                            $Msj = "Lo sentimos, el cliente [$Cliente] no existe o esta inactivo!";
                        }
                    }
                } else {
                    $Msj = "Lo sentimos, el ticket tiene algún proceso ejecutado, favor de comunicarse con soporte";
                }
            } elseif ($request->hasAttribute("Concepto") && $request->hasAttribute("Importe")) {
                $concepto = $sanitize->sanitizeString("Concepto");
                $importe = $sanitize->sanitizeFloat("Importe");

                $insertCtpagos = "INSERT INTO ctpagos (corte,cliente,fecha,concepto,importe)
                        VALUES ('$Corte',0,NOW(),'$concepto',$importe)";
                if (($mysqli->query($insertCtpagos))) {
                    $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            } elseif ($request->hasAttribute("Banco") && $request->hasAttribute("Importe")) {
                $bancoVO = $bancosDAO->retrieve($sanitize->sanitizeInt("Banco"));
                $importe = $sanitize->sanitizeFloat("Importe");
                $plomo = $sanitize->sanitizeString("Plomo");

                $BitacoraDAO->saveLog($usuarioSesion->getUsername(), "ADM", "Agrega abono a bancos corte $Corte e importe : " . $sanitize->sanitizeFloat("Importe"));
                if ($bancoVO->getTipo_cambio() != 1) {
                    $Importe = $importe * $bancoVO->getTipo_cambio();
                } else {
                    $Importe = $importe;
                }

                $concepto = strtoupper(trim($sanitize->sanitizeString("Descripcion")));

                $egrVO = new EgrVO();
                $egrVO->setClave($bancoVO->getId());
                $egrVO->setCorte($Corte);
                $egrVO->setConcepto($concepto);
                $egrVO->setImporte($Importe);
                $egrVO->setPlomo($plomo);
                $egrVO->setTipo_cambio($bancoVO->getTipo_cambio());

                if (($id = $egrDAO->create($egrVO)) > 0) {
                    if ($bancoVO->getRubro() == RubroBanco::VENDEDORES) {
                        $cxdVO = new CxdVO();
                        $cxdVO->setVendedor($bancoVO->getCuenta());
                        $cxdVO->setReferencia($id);
                        $cxdVO->setCorte($egrVO->getCorte());
                        $cxdVO->setFecha(date("Y-m-d H:i:s"));
                        $cxdVO->setTm(TipoMovCxd::CARGO);
                        $cxdVO->setConcepto($egrVO->getConcepto());
                        $cxdVO->setImporte($egrVO->getImporte());

                        if (($id = $cxdDAO->create($cxdVO)) <= 0) {
                            error_log("Ocurrio un error al agregar el cargo a despachador");
                        }
                    }
                    $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                    $ctVO->setEnviado(0);
                    $ctDAO->update($ctVO);
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            } else if ($request->getAttribute("AddTicketContado") === "ok") {
                $BuscaTicket = "SELECT id FROM rm WHERE id = '" . $request->getAttribute("TicketEfectivo") . "' AND cliente = 0 ";
                $Tkt = $mysqli->query($BuscaTicket)->fetch_array();
                if (is_numeric($Tkt["id"])) {
                    $IdCli = explode("|", $request->getAttribute("ClienteEfectivo"));
                    $CliSql = "SELECT puntos FROM cli WHERE id = " . $IdCli[0];
                    $PtsX = utils\IConnection::execSql($CliSql);
                    $UpdateTicket = "UPDATE rm SET cliente = '" . $IdCli[0] . "', puntos = '" . $PtsX["puntos"] . "' WHERE id = '" . $Tkt["id"] . "';";
                    if ($mysqli->query($UpdateTicket)) {
                        $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                    }
                } else {
                    $Msj = utils\Messages::REGISTER_DUPLICATE;
                }
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_SEEK) {
            $rmVO = $rmDAO->retrieve($Ticket);
            if (is_numeric($rmVO->getId())) {
                $clienteVO = $clienteDAO->retrieve($rmVO->getCliente());
                $flagTarjetas = $sanitize->sanitizeInt("Tarjetas");
                $selectFcd = "SELECT fc.folio FROM fcd,fc WHERE fcd.id = fc.id AND fcd.ticket = $Ticket AND producto < 5;";
                $fcd = $mysqli->query($selectFcd)->fetch_array();
                if ($rmVO->getTipo_venta() !== TipoVenta::NORMAL) {
                    $Msj = "No se puede cargar tickets marcados como jarreo";
                } elseif ($rmVO->getPesos() <= 0) {
                    $Msj = "Error! el ticket: [" . $Ticket . "] esta en ceros, favor de verificar";
                } elseif ($rmVO->getCorte() != $Corte) {
                    $Msj = "Error! el ticket: [" . $Ticket . "] corresponde a otro corte [" . $rmVO->getCorte() . "]";
                } elseif ($rmVO->getUuid() !== FcDAO::SIN_TIMBRAR && $flagTarjetas == 0) {
                    $selectFc = "SELECT fc.folio,cli.* 
                            FROM  fc, cli
                            WHERE fc.cliente = cli.id
                            AND fc.uuid =  '" . $rmVO->getUuid() . "'";
                    $Cli = $mysqli->query($selectFc)->fetch_array();
                    $Msj = "Error! el ticket: " . $rmVO->getId() . " ya ha sido facturado. Folio: " . $Cli[folio] . ", Cliente: " . $Cli[id] . " " . $Cli[nombre] . " [" . $Cli[tipodepago] . "]";
                } elseif ($rmVO->getCliente() > 0 && ($clienteVO->getTipodepago() === TiposCliente::CREDITO || $clienteVO->getTipodepago() === TiposCliente::PREPAGO || $clienteVO->getTipodepago() === TiposCliente::TARJETA)) {
                    $Msj = "El ticket ya ha sido asignado al cliente " . $clienteVO->getNombre() . " [" . $clienteVO->getTipodepago() . "] Corte: " . $rmVO->getCorte();
                } else {
                    if ($flagTarjetas == 0) {
                        if (!empty($fcd["folio"]) && $fcd["folio"] > 0) {
                            $Msj = "Error! el ticket: [" . $Ticket . "] esta asociado a la factura " . $fcd["folio"];
                        } else {
                            $Return .= "Ticket=" . $Ticket;
                            $Msj = "";
                        }
                    } else {
                        $Return .= "Ticket=" . $Ticket;
                        $Msj = "";
                    }
                }
            } else {
                $Msj = "Error! el ticket: [" . $Ticket . "] no existe";
            }
        } elseif ($request->getAttribute("Boton") === "Agregar como contado") {

            if ($ctVO->getStatusctv() === StatusCorte::ABIERTO) {

                $producto = $sanitize->sanitizeInt("Clave");
                $Isla_pos = $sanitize->sanitizeInt("Isla");
                $cantidad = $sanitize->sanitizeInt("Cantidad");

                $manVO = $manDAO->retrieve($Isla_pos, "isla_pos", true);
                $productoVO = $productoDAO->retrieve($producto);

                $vtAdtitivosVO = new VentaAditivosVO();
                $vtAdtitivosVO->setProducto($producto);
                $vtAdtitivosVO->setCantidad($cantidad);
                $vtAdtitivosVO->setUnitario($productoVO->getPrecio());
                $vtAdtitivosVO->setTotal($productoVO->getPrecio() * $cantidad);
                $vtAdtitivosVO->setCorte($Corte);
                $vtAdtitivosVO->setPosicion($manVO->getPosicion());
                $vtAdtitivosVO->setFecha($ctVO->getFecha());
                $vtAdtitivosVO->setDescripcion($productoVO->getDescripcion());
                $vtAdtitivosVO->setCliente(0);
                $vtAdtitivosVO->setReferencia(0);
                $vtAdtitivosVO->setCosto($productoVO->getCosto());
                $vtAdtitivosVO->setIva(($productoVO->getFactorIva() / 100));
                $vtAdtitivosVO->setVendedor($manVO->getDespachador());
                $vtAdtitivosVO->setTm("C");

                if (($id = $vtAditivosDAO->create($vtAdtitivosVO)) > 0) {
                    BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "ALTA DE VENTA DE ACEITE Y/O ADITIVO CON NUMERO DE TICKET[" . $id . "] ");
                    $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                    $updateInvd = "UPDATE invd SET existencia = existencia - $cantidad, modificacion = NOW() WHERE isla_pos = $Isla_pos AND id = $producto LIMIT 1";
                    if (!($mysqli->query($updateInvd))) {
                        error_log($mysqli->error);
                    }
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            } else {
                $Msj = "Lo siento el corte actual ha sido cerrado, no es posible hacer ningun movimiento";
            }
        } elseif ($request->getAttribute("Boton") === "Reasignar a cliente") {
            $vtAdtitivosVO = $vtAditivosDAO->retrieve($Ticket);  //----
            if (is_numeric($vtAdtitivosVO->getId()) && $vtAdtitivosVO->getCorte() == $Corte) {
                $clienteVO = $clienteDAO->retrieve($vtAdtitivosVO->getCliente());

                if ($vtAdtitivosVO->getUuid() !== FcDAO::SIN_TIMBRAR && ($clienteVO->getTipodepago() === TiposCliente::CREDITO || $clienteVO->getTipodepago() === TiposCliente::PREPAGO)) {
                    $Msj = "El ticket ya esta facturado y no es posible asignarlo a otro cliente [" . $clienteVO->getTipodepago() . "].";
                } elseif ($vtAdtitivosVO->getCliente() != 0 && ($clienteVO->getTipodepago() === TiposCliente::CREDITO || $clienteVO->getTipodepago() === TiposCliente::PREPAGO)) {
                    $Msj = "El ticket pertenece a otro cliente [" . $clienteVO->getTipodepago() . "].";
                } elseif ($vtAdtitivosVO->getCantidad() == 0) {
                    $Msj = "El ticket ya ha sido dado de baja previamente.";
                } else {
                    $clienteVO = $clienteDAO->retrieve($Cliente);
                    if ($clienteVO->getTipodepago() === TiposCliente::CREDITO || $clienteVO->getTipodepago() === TiposCliente::PREPAGO || $clienteVO->getTipodepago() === TiposCliente::TARJETA || $clienteVO->getTipodepago() === TiposCliente::MONEDERO || $clienteVO->getTipodepago() === TiposCliente::VALES || $clienteVO->getTipodepago() === TiposCliente::AUTOCONSUMO || $clienteVO->getTipodepago() === TiposCliente::CORTESIA) {
                        $vtAdtitivosVO->setCliente($Cliente);
                        $Codigo = explode("|", $sanitize->sanitizeString("Codigo"));
                        $vtAdtitivosVO->setCodigo(trim($Codigo[0]));
                        if ($vtAditivosDAO->update($vtAdtitivosVO)) {
                            BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "CAMBIA VENTA DE ACEITE Y/O ADITIVO NUMERO DE TICKET [" . $Ticket . "] A CLIENTE: $Cliente [" . strtoupper($clienteVO->getTipodepago()) . "]");
                            $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                            $updateCxc = "UPDATE cxc SET cliente = -cliente, referencia = -referencia, corte = -corte "
                                    . "WHERE referencia='$Ticket' AND tm='C' AND producto = 'A' LIMIT 1";
                            if (!( $mysqli->query($updateCxc))) {
                                error_log($mysqli->error);
                            }

                            $cxcVO = new CxcVO();
                            $cxcVO->setCliente($Cliente);
                            $cxcVO->setPlacas("");
                            $cxcVO->setReferencia($Ticket);
                            $cxcVO->setFecha(date("Y-m-d", strtotime($vtAdtitivosVO->getFecha())));
                            $cxcVO->setHora(date("H:i:s", strtotime($vtAdtitivosVO->getFecha())));
                            $cxcVO->setConcepto($vtAdtitivosVO->getDescripcion());
                            $cxcVO->setCantidad($vtAdtitivosVO->getCantidad());
                            $cxcVO->setImporte($vtAdtitivosVO->getTotal());
                            $cxcVO->setCorte($Corte);
                            $cxcVO->setProducto("A");

                            if (($cxcDAO->create($cxcVO)) < 0) {
                                $Msj = utils\Messages::RESPONSE_ERROR;
                            }
                        } else {
                            $Msj = utils\Messages::RESPONSE_ERROR;
                        }
                    }
                }
            } else {
                $Msj = "Ticket no existe o no pertenece al corte.";
            }
        } elseif ($request->getAttribute("Boton") === "Abrir turno") {
            $Return = "servicio.php?cPrc=1&isla=" . $islaVO->getIsla();
            $Msj = "Generando corte en isla " . $islaVO->getIsla();
        }
        if (!is_null($Return)) {
            $Return .= "&Msj=" . urlencode($Msj);
        }
    } catch (Exception $ex) {
        error_log("Error en cambio de turno: " . $ex);
    } finally {
        if ($mysqli->errno > 0) {
            error_log($mysqli->error);
        }
        if (!is_null($Return)) {
            header("Location: $Return");
        }
    }
}

if ($request->hasAttribute("BotonColectas") && $request->getAttribute("BotonColectas") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Return = "mdepositos.php?";

    try {
        if ($request->getAttribute("BotonColectas") === utils\Messages::OP_ADD || $request->getAttribute("BotonColectas") === "Agregar ieps") {
            $ieps = 1;
            if ($request->getAttribute("BotonColectas") === "Agregar ieps") {
                $ieps = -1;
            }
            $TpoCambio = 1;
            $TipoMoneda = $sanitize->sanitizeInt("Tipo_moneda");
            $Desp = $sanitize->sanitizeInt("Despachador");
            $DespAct = $sanitize->sanitizeInt("DespachadorAct");
            $Despachador = is_numeric($Desp) ? $Desp : $DespAct;
            $Posicion = 0;
            if ($Despachador < 50) {
                $Posicion = $Despachador;
            }
            if ($TipoMoneda == 2) {
                $bancoVO = $bancosDAO->retrieve(2, "tipo_moneda");
                if ($bancoVO->getId() > 0) {
                    $TpoCambio = $bancoVO->getTipo_cambio();
                }
            }
            $M0050c = $sanitize->sanitizeInt("M0050c") * $ieps;
            $M0001p = $sanitize->sanitizeInt("M0001p") * $ieps;
            $M0002p = $sanitize->sanitizeInt("M0002p") * $ieps;
            $M0005p = $sanitize->sanitizeInt("M0005p") * $ieps;
            $M0010p = $sanitize->sanitizeInt("M0010p") * $ieps;
            $M0020p = $sanitize->sanitizeInt("M0020p") * $ieps;
            $M0050p = $sanitize->sanitizeInt("M0050p") * $ieps;
            $M0100p = $sanitize->sanitizeInt("M0100p") * $ieps;
            $M0200p = $sanitize->sanitizeInt("M0200p") * $ieps;
            $M0500p = $sanitize->sanitizeInt("M0500p") * $ieps;
            $M1000p = $sanitize->sanitizeInt("M1000p") * $ieps;

            $Total = (($M0050c * .5) + $M0001p + ( $M0002p * 2) + ($M0005p * 5) + ($M0010p * 10) + ($M0020p * 20) + ($M0050p * 50) + ($M0100p * 100) + ($M0200p * 200) + ($M0500p * 500) + ($M1000p * 1000)) * $TpoCambio;

            if ($Total > 0) {
                $insertCtdep = "INSERT INTO ctdep
                       (fecha,corte,despachador,cincuentac,peso,dos,cinco,diez,veinte,
                       cincuenta,cien,doscientos,quinientos,mil,total,posicion,tipo_cambio)
                       VALUES
                       (NOW(),'$Corte','$Despachador','$M0050c','$M0001p','$M0002p','$M0005p','$M0010p','$M0020p',
                       '$M0050p','$M0100p','$M0200p','$M0500p','$M1000p','$Total',$Posicion,'$TpoCambio')";

                if (!($mysqli->query($insertCtdep))) {
                    $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                }
            }
        } elseif ($request->getAttribute("BotonColectas") === "Reasignar") {

            $Return = "mvendedores.php?";
            $update = true;

            $Isla = $request->getAttribute("Isla");
            $Vendedor = $request->getAttribute("Despachador");
            error_log("Cambiar venta de vendedor: " . $Vendedor);

            $updateMan = "UPDATE man SET despachador = $Vendedor WHERE isla_pos = $Isla";

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
        } elseif ($request->getAttribute("BotonColectas") === "Revertir") {

            $Return = "mvendedores.php?";
            $update = true;

            $Isla = $request->getAttribute("Isla");
            error_log("Revertir: " . $Isla);

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
        }
    } catch (Exception $ex) {
        error_log("Error en cambio de turno: " . $ex);
    } finally {
        if ($mysqli->errno > 0) {
            error_log($mysqli->error);
        }
        if (!is_null($Return)) {
            header("Location: $Return");
        }
    }
}

if ($request->hasAttribute("op")) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $cId = $sanitize->sanitizeInt("cId");
    $Tipo = $sanitize->sanitizeString("tipo");

    try {
        if ($request->getAttribute("op") === utils\Messages::OP_DELETE) {
            if ($Tipo === RmDAO::TIPO) {
                $rmVO = $rmDAO->retrieve($cId);
                $clienteVO = $clienteDAO->retrieve($rmVO->getCliente());

                if ($ConcentrarVtasTarjeta === ConcentrarTarjetasCorte::NO || (is_numeric($rmVO->getId()) && $rmVO->getCorte() == $Corte)) {

                    $selectFcd = "SELECT fc.folio FROM fcd,fc WHERE fcd.id = fc.id AND fcd.ticket = $cId AND producto < 5;";
                    $fcd = $mysqli->query($selectFcd)->fetch_array();
                    $flagTarjetas = $sanitize->sanitizeInt("Tarjetas");

                    if ($rmVO->getUuid() !== FcDAO::SIN_TIMBRAR && ($clienteVO->getTipodepago() === TiposCliente::CREDITO || $clienteVO->getTipodepago() === TiposCliente::PREPAGO)) {
                        $Msj = "Error! el ticket [" . $cId . "] no se puede eliminar ya que tiene una factura asociada";
                    } elseif (!empty($fcd["folio"]) && $fcd["folio"] > 0 && $flagTarjetas == 0) {
                        $Msj = "Error! el ticket [" . $cId . "] no se puede eliminar ya que esta asociado a la factura " . $fcd["folio"];
                    } elseif ($rmVO->getPagado() > 0) {
                        $Msj = "Error! el ticket [" . $cId . "] no se puede eliminar ya que esta asociado al pago " . $rmVO->getPagado();
                    } else {
                        $rmVO->setCliente(0);
                        $rmVO->setPagoreal($rmVO->getPesos());
                        $rmVO->setTipodepago(TiposCliente::CONTADO);
                        $rmVO->setEnviado(0);
                        $rmVO->setCodigo(0);
                        $rmVO->setTipo_venta("D");
                        if ($rmDAO->update($rmVO)) {
                            $Msj = utils\Messages::RESPONSE_VALID_CANCEL;
                            $updateCxc = "UPDATE cxc SET cliente = -cliente, referencia=-referencia, corte = -corte "
                                    . "WHERE referencia='$cId' AND tm='C' AND producto = '" . $rmVO->getProducto() . "' LIMIT 1;";
                            $mysqli->query($updateCxc);
                            desasignarVentasAceites($cId);
                            BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "CAMBIA VENTA [" . $cId . "] A CONTADO, CLIENTE PREVIO " . $clienteVO->getId() . " - " . $clienteVO->getNombre() . "[" . $clienteVO->getTipodepago() . "]");
                        } else {
                            $Msj = utils\Messages::RESPONSE_ERROR;
                        }
                    }
                } else {
                    $selectCttarjetas = $mysqli->query("SELECT * FROM cttarjetas WHERE idnvo = '$cId' LIMIT 1")->fetch_array();
                    $Cliente = $selectCttarjetas[banco];

                    $updateCxc = "UPDATE cxc SET cliente = -cliente,referencia = -referencia, corte = -corte "
                            . "WHERE referencia = '$cId' AND cliente = '$Cliente' LIMIT 1";
                    $updateCt = "UPDATE cttarjetas SET id = -id WHERE idnvo = '$cId' LIMIT 1";

                    if ($mysqli->query($updateCxc) && $mysqli->query($updateCt)) {
                        $Msj = utils\Messages::RESPONSE_VALID_CANCEL;
                    }
                }
            } elseif ($Tipo === VentaAditivosDAO::TIPO) {
                $vtAdtitivosVO = $vtAditivosDAO->retrieve($cId);
                $clienteVO = $clienteDAO->retrieve($vtAdtitivosVO->getCliente());

                if ($vtAdtitivosVO->getUuid() !== FcDAO::SIN_TIMBRAR) {
                    $Msj = "El registro no se puede eliminar ya que tiene una factura asociada";
                } elseif (is_numeric($vtAdtitivosVO->getReferencia()) && $vtAdtitivosVO->getReferencia() > 0) {
                    $Msj = "El registro no se puede eliminar ya que tiene un ticket asociado $Cpo[referencia]";
                } elseif ($vtAdtitivosVO->getPagado() > 0) {
                    $Msj = "Error! el ticket [" . $cId . "] no se puede eliminar ya que esta asociado al pago " . $vtAdtitivosVO->getPagado();
                } else {
                    error_log("Iniciando cancelacion de aceite o aditivo");
                    $manVO = $manDAO->retrieve($vtAdtitivosVO->getPosicion(), "posicion", true);
                    $Cantidad = $vtAdtitivosVO->getCantidad();
                    $Isla_pos = $manVO->getIsla_pos();

                    $vtAdtitivosVO->setCantidad(0);
                    $vtAdtitivosVO->setTotal(0);
                    $vtAdtitivosVO->setEnviado(0);
                    $vtAdtitivosVO->setReferencia(0);

                    if ($vtAditivosDAO->update($vtAdtitivosVO)) {
                        $Msj = utils\Messages::RESPONSE_VALID_CANCEL;
                        BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "CANCELA VENTA ACEITES [" . $cId . "]");
                        $updateInv = "UPDATE invd SET existencia = existencia + $Cantidad, modificacion = NOW() WHERE id = '" . $vtAdtitivosVO->getProducto() . "' AND isla_pos = $Isla_pos LIMIT 1";
                        if (!($mysqli->query($updateInv))) {
                            error_log($mysqli->error);
                        }
                    } else {
                        $Msj = utils\Messages::RESPONSE_ERROR;
                    }
                }
            }
        } elseif ($request->getAttribute("op") === utils\Messages::OP_FREE) {
            $vtAdtitivosVO = $vtAditivosDAO->retrieve($cId);
            $clienteVO = $clienteDAO->retrieve($vtAdtitivosVO->getCliente());

            if ($vtAdtitivosVO->getUuid() !== FcDAO::SIN_TIMBRAR && ($clienteVO->getTipodepago() !== TiposCliente::TARJETA && $clienteVO->getTipodepago() !== TiposCliente::MONEDERO)) {
                $Msj = "El registro no se puede eliminar ya que tiene una factura asociada";
            } elseif ($vtAdtitivosVO->getPagado() > 0) {
                $Msj = "Error! el ticket [" . $cId . "] no se puede eliminar ya que esta asociado al pago " . $vtAdtitivosVO->getPagado();
            } else {
                $vtAdtitivosVO->setCliente(0);
                $vtAdtitivosVO->setEnviado(0);
                if ($vtAditivosDAO->update($vtAdtitivosVO)) {
                    $Msj = utils\Messages::RESPONSE_VALID_CANCEL;
                    BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "CAMBIA VENTA ACEITES [" . $cId . "] A CONTADO, CLIENTE PREVIO " . $clienteVO->getId() . " - " . $clienteVO->getNombre() . "[" . $clienteVO->getTipodepago() . "]");
                    $updateCxc = "UPDATE cxc SET cliente = -cliente, referencia=-referencia, corte = -corte "
                            . "WHERE referencia='$cId' AND tm='C' AND producto = 'A' LIMIT 1;";
                    if (!($mysqli->query($updateCxc))) {
                        error_log($mysqli->error);
                    }
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            }
        } elseif ($request->getAttribute("op") === "Dolares") {
            $deleteFormas = "DELETE FROM formas_de_pago WHERE id = '$cId';";
            if ($mysqli->query($deleteFormas)) {
                $Msj = utils\Messages::RESPONSE_VALID_CANCEL;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("op") === "Gastos") {
            $deleteCtpagos = "DELETE FROM ctpagos WHERE idnvo = '$cId'  LIMIT 1";
            if ($mysqli->query($deleteCtpagos)) {
                $Msj = utils\Messages::RESPONSE_VALID_CANCEL;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("op") === "Bancos") {
            $ctVO = $ctDAO->retrieve($Corte);
            $ctVO->setEnviado(0);
            $ctDAO->update($ctVO);

            $BitacoraDAO->saveLog($usuarioSesion->getUsername(), "ADM", "Borra abono a bancos corte $Corte, detalle egr id " . $cId);
            $selectPagosDespachador = "SELECT * FROM pagosdespd WHERE referencia = '$cId'";
            $pagos = $mysqli->query($selectPagosDespachador)->fetch_array();

            if (empty($pagos) && count($pagos) == 0) {
                $updateEgresos = "UPDATE egr SET corte = -corte WHERE id = '$cId' LIMIT 1";
                $updateCxd = "UPDATE cxd SET corte = -corte, vendedor = -vendedor, referencia = -referencia WHERE referencia = '$cId' AND tm = 'C'";

                if ($mysqli->query($updateEgresos) && $mysqli->query($updateCxd)) {
                    $Msj = utils\Messages::RESPONSE_VALID_CANCEL;
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            } else {
                $Msj = "El registro no se puede eliminar ya que tiene un pago asociado al despachdor. Pago [$pagos[pago]]";
            }
        } elseif ($request->getAttribute("op") === "cr") {
            $ctVO = $ctDAO->retrieve($Corte);
            $ctVO->setStatusctv(StatusCorte::CERRADO);
            $ctVO->setEnviado(0);
            if ($ctDAO->update($ctVO)) {
                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "APLICA CIERRE DE CORTE #$Corte");
                $Msj = utils\Messages::RESPONSE_TURN_CLOSE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("op") === "Crear") {
            $DispensarioSql = "SELECT Dispensarios FROM variables";
            $DispensarioFetch = $mysqli->query($DispensarioSql)->fetch_array();
            $Dispensario = $DispensarioFetch["Dispensarios"];

            if ($Dispensario == "LC") {
                $Msj = crearCorte($request->getAttribute("Corte"));
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("op") === "Depositos") {
            $Return = "mdepositos.php?";
            $deleteCtdep = "DELETE FROM ctdep WHERE id='$cId' LIMIT 1";

            if ($mysqli->query($deleteCtdep)) {
                $Msj = utils\Messages::RESPONSE_VALID_DELETE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("op") === "Lb") {
            $UpdateTicket = "UPDATE rm SET cliente = 0 WHERE id = " . $request->getAttribute("IdT");
            if ($mysqli->query($UpdateTicket)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en cambio de turno: " . $ex);
    } finally {
        header("Location: $Return");
    }
}

/**
 * 
 * @global SanitizeUtil::getInstance() $sanitize
 * @global RmDAO $rmDAO
 * @global ClientesDAO $clienteDAO
 * @param int $Ticket
 * @param int $Corte
 * @return string
 */
function validarConsumo($Ticket, $Corte) {
    global $sanitize, $rmDAO, $clienteDAO;

    $mysqli = iconnect();
    $rmVO = $rmDAO->retrieve($Ticket);
    if (is_numeric($rmVO->getId())) {
        $clienteVO = $clienteDAO->retrieve($rmVO->getCliente());
        $flagTarjetas = $sanitize->sanitizeInt("Tarjetas");
        $flagMonederos = $sanitize->sanitizeInt("Monederos");
        $selectFcd = "SELECT fc.folio FROM fcd,fc WHERE fcd.id = fc.id AND fcd.ticket = $Ticket AND producto < 5;";
        $fcd = $mysqli->query($selectFcd)->fetch_array();
        if ($rmVO->getTipo_venta() === TipoVenta::JARREO) {
            $Msj = "No se puede cargar tickets marcados como jarreo";
        } elseif ($rmVO->getPesos() <= 0) {
            $Msj = "Error! el ticket: [" . $Ticket . "] esta en ceros, favor de verificar";
        } elseif ($rmVO->getCorte() != $Corte) {
            $Msj = "Error! el ticket: [" . $Ticket . "] corresponde a otro corte [" . $rmVO->getCorte() . "]";
        } elseif ($rmVO->getUuid() !== FcDAO::SIN_TIMBRAR && ($flagTarjetas == 0 || $flagMonederos == 1)) {
            $selectFc = "SELECT fc.folio,cli.* 
                            FROM  fc, cli
                            WHERE fc.cliente = cli.id
                            AND fc.uuid =  '" . $rmVO->getUuid() . "'";
            $Cli = $mysqli->query($selectFc)->fetch_array();
            $Msj = "Error! el ticket: " . $rmVO->getId() . " ya ha sido facturado. Folio: " . $Cli[folio] . ", Cliente: " . $Cli[id] . " " . $Cli[nombre] . " [" . $Cli[tipodepago] . "]";
        } elseif ($rmVO->getCliente() > 0 && ($clienteVO->getTipodepago() === TiposCliente::CREDITO || $clienteVO->getTipodepago() === TiposCliente::PREPAGO || $clienteVO->getTipodepago() === TiposCliente::TARJETA)) {
            $Msj = "El ticket ya ha sido asignado al cliente " . $clienteVO->getNombre() . " [" . $clienteVO->getTipodepago() . "] Corte: " . $rmVO->getCorte();
        } else {
            if ($flagTarjetas == 0) {
                if (!empty($fcd["folio"]) && $fcd["folio"] > 0) {
                    $Msj = "Error! el ticket: [" . $Ticket . "] esta asociado a la factura " . $fcd["folio"];
                } else {
                    $Msj = $Ticket;
                }
            } else {
                $Msj = $Ticket;
            }
        }
    } else {
        $Msj = "Error! el ticket: [" . $Ticket . "] no existe";
    }
    return $Msj;
}

function buscarVentasAceites($Ticket, $Cliente) {
    $mysqli = iconnect();

    $insertVtasAceites = "
            INSERT INTO cxc (cliente, placas, referencia, fecha, hora, tm, concepto, cantidad, importe, recibo, corte, producto, rubro)
            SELECT rm.cliente,IFNULL(rm.placas, '') placas,vt.id referencia, DATE(vt.fecha) fecha, TIME(vt.fecha) hora, 'C' tm,
            vt.descripcion concepto, vt.cantidad, vt.total, 0 recibo, vt.corte, 'A' producto, '-----' rubro 
            FROM vtaditivos vt, rm, cli
            WHERE TRUE
            AND vt.referencia = rm.id
            AND rm.cliente = cli.id
            AND vt.cliente = 0 AND vt.uuid = '-----' AND vt.referencia = $Ticket
            AND vt.cantidad > 0 AND vt.total > 0 AND vt.tm = 'C'
            AND cli.tipodepago IN ('Credito', 'Prepago', 'Tarjeta', 'Monedero', 'Consignacion');";

    $updateVtasAceites = " 
            UPDATE vtaditivos vt SET vt.cliente = $Cliente 
            WHERE vt.cliente = 0 AND vt.uuid = '-----' AND vt.referencia = $Ticket
            AND vt.cantidad > 0 AND vt.total > 0 AND vt.tm = 'C'";
    if (!$mysqli->query($insertVtasAceites) || !$mysqli->query($updateVtasAceites)) {
        error_log($mysqli->error);
    }
}

function desasignarVentasAceites($Ticket) {
    $mysqli = iconnect();

    $updateVtasAceites = "
            UPDATE cxc, vtaditivos vt 
            SET cxc.referencia = -cxc.referencia,
            cxc.cliente = -cxc.cliente,
            cxc.corte = -cxc.corte,
            vt.cliente = 0
            WHERE TRUE
            AND vt.id = cxc.referencia
            AND cxc.tm = 'C' AND cxc.producto = 'A' 
            AND vt.uuid = '-----' AND vt.referencia = $Ticket;";

    if (!$mysqli->query($updateVtasAceites)) {
        error_log($mysqli->error);
    }
}

$Id = 27;
$paginador = new Paginador($Id, "", "", "", "", "ct.id", "ct.id", "ct.id", strtoupper("asc"), 0, "REGEXP", "cambiotur.php");
