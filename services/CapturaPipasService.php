<?php

#Librerias
include_once('data/MeDAO.php');
include_once('data/MeTmpDAO.php');
include_once('data/MedDAO.php');
include_once('data/MedTmpDAO.php');
include_once('data/CargasDAO.php');
include_once('data/ProveedorDAO.php');
include_once('data/CombustiblesDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "entradased.php?";

$meDAO = new MeDAO();
$meTmpDAO = new MeTmpDAO();
$medDAO = new MedDAO();
$medTmpDAO = new MedTmpDAO();
$cargasDAO = new CargasDAO();
$proveedorDAO = new ProveedorDAO();

if ($request->hasAttribute("carga")) {
    utils\HTTPUtils::setSessionValue("carga", $sanitize->sanitizeInt("carga"));
}

if ($request->hasAttribute("step")) {
    utils\HTTPUtils::setSessionValue("step", $sanitize->sanitizeInt("step"));
}

$carga = utils\HTTPUtils::getSessionValue("carga");
$step = utils\HTTPUtils::getSessionValue("step");
$busca = $sanitize->sanitizeString("busca");

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    $cargasVO = $cargasDAO->retrieve($carga);
    $meTmpVO = $meTmpDAO->retrieve($busca);
    error_log(print_r($request, TRUE));
    try {
        if ($request->getAttribute("Boton") === utils\Messages::OP_SAVE) {
            $volumenFacturado = $sanitize->sanitizeFloat("Volumenfac");
            $Unidad = $sanitize->sanitizeInt("Unidad");
            $Conversion = $sanitize->sanitizeInt("Conversion");
            $precioUnitario = $sanitize->sanitizeFloat("Preciou");
            $importeFactura = $sanitize->sanitizeFloat("Importefac");

            $Guardar = false;
            if ($Unidad == UnidadMedida::M3) {
                if (is_numeric($volumenFacturado) && $volumenFacturado < MeDAO::MAX_VOLUMEN) {
                    $Guardar = true;
                } else {
                    $Msj = "Volumen en metros cubicos (m3) ingresado no valido, rango valido de [1 - " . MeDAO::MAX_VOLUMEN . "] .";
                }
            } elseif ($Unidad == UnidadMedida::LTS) {
                if (is_numeric($volumenFacturado) && $volumenFacturado > 100) {
                    $volumenFacturado = $volumenFacturado / 1000;
                    $Guardar = true;
                } else {
                    $Msj = "Volumen (lts.) ingresado no valido.";
                }
            }

            if ($Unidad == UnidadMedida::M3 && $Conversion == Conversion::PRECIOUNITARIO) {
                /* No hace nada */
            } elseif ($Unidad == UnidadMedida::M3 && $Conversion == Conversion::IMPORTE) {
                $precioUnitario = number_format($precioUnitario / $volumenFacturado, 6, ".", "");
            } elseif ($Unidad == UnidadMedida::LTS && $Conversion == Conversion::PRECIOUNITARIO) {
                $precioUnitario = number_format($precioUnitario * 1000, 6, ".", "");
            } else {
                $precioUnitario = number_format($precioUnitario / $volumenFacturado, 6, ".", "");
            }

            //error_log($precioUnitario . "<" . $importeFactura);

            if (($precioUnitario < $importeFactura || ($precioUnitario * $volumenFacturado) < $importeFactura) || ($sanitize->sanitizeString("TipoCarga") === TipoCarga::CONSIGNACION)) {
                if ($Guardar) {
                    $folioFactura = $sanitize->sanitizeString("Foliofac");
                    $uuid = $sanitize->sanitizeString("UUID");

                    if ($sanitize->sanitizeString("TipoCarga") === TipoCarga::CONSIGNACION || ($sanitize->sanitizeString("TipoCarga") === TipoCarga::NORMAL && preg_match("/^\{?[0-9A-F]{8}\-?[0-9A-F]{4}\-?[0-9A-F]{4}\-?[0-9A-F]{4}\-?[0-9A-F]{12}\}?$/i", $uuid))) {

                        $MeSql = "SELECT id,foliofac FROM me WHERE foliofac = '$folioFactura' AND proveedor='" . $meTmpVO->getProveedor() . "' AND carga > 0";
                        $Me = $mysqli->query($MeSql)->fetch_array();

                        if (count($Me) == 0) {
                            $MeTmpSql = "SELECT id,foliofac FROM me_tmp WHERE foliofac = '$folioFactura' ";
                            $MeTmp = $mysqli->query($MeSql)->fetch_array();

                            if (count($MeTmp) <= 1) {

                                //$meTmpVO->setTipoConversion($Unidad);
                                $meTmpVO->setFoliofac(trim($folioFactura));
                                $meTmpVO->setId($sanitize->sanitizeString("busca"));
                                $meTmpVO->setUuid(trim($uuid));
                                $meTmpVO->setVolumenfac($volumenFacturado);
                                $meTmpVO->setPreciou($precioUnitario);
                                $meTmpVO->setImportefac($importeFactura);
                                $meTmpVO->setTipo($sanitize->sanitizeString("TipoCarga"));
                                $meTmpVO->setVolumen_devolucion($sanitize->sanitizeString("Volumen_devolucion"));
                                $meTmpVO->setTipocomprobante($sanitize->sanitizeString("TipoComprobante"));
                                if ($meTmpDAO->update($meTmpVO)) {
                                    $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                                    $MedTmpSql = "SELECT * FROM med_tmp WHERE id = '" . $meTmpVO->getId() . "' AND clave = '" . $cargasVO->getClave() . "'";
                                    //error_log($MedTmpSql);
                                    $MedTmp = $mysqli->query($MedTmpSql)->fetch_array();

                                    if (!empty($MedTmp["precio"]) && $MedTmp["precio"] > 0) {
                                        $updateMedTmp = "UPDATE med_tmp  
                                                SET cantidad = '$volumenFacturado',precio = '" . $precioUnitario . "'  
                                                WHERE id = '" . $meTmpVO->getId() . "' AND clave = '" . $cargasVO->getClave() . "'";
                                        if (!($mysqli->query($updateMedTmp))) {
                                            error_log($mysqli->error);
                                            $Msj = utils\Messages::RESPONSE_ERROR;
                                        }
                                    } else {
                                        $medVO = new MedVO();
                                        $medVO->setId($meTmpVO->getId());
                                        $medVO->setClave($cargasVO->getClave());
                                        $medVO->setCantidad($volumenFacturado);
                                        $medVO->setPrecio($precioUnitario);
                                        if (($id = $medTmpDAO->create($medVO)) < 0) {
                                            $Msj = utils\Messages::RESPONSE_ERROR;
                                        }
                                    }
                                    TotalizaEntrada($busca);
                                } else {
                                    $Msj = utils\Messages::RESPONSE_ERROR;
                                }
                            } else {
                                $Msj = "Factura duplicada ERROR";
                            }
                        } else {
                            $Msj = "La folio de la factura ingresado ya esta en uso, Pipa capturada [" . $Me["id"] . "]";
                        }
                    } else {
                        $Msj = "El UUID no contiene el formato correcto";
                    }
                }
            } else {
                $Msj = "Verifique los datos capturados. Use correcatente las conversiones";
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            $busca = $sanitize->sanitizeString("busca");
            $producto = $sanitize->sanitizeInt("Producto");

            $MedTmpSql = "SELECT * FROM med_tmp WHERE id = '$busca' AND clave = '$producto'";
            $MedTmp = $mysqli->query($MedTmpSql)->fetch_array();
            //error_log(print_r($MedTmp, TRUE));
            if (count($MedTmp) == 0) {
                $tipo = $sanitize->sanitizeInt("Tipo");
                $importe = $sanitize->sanitizeFloat("Importe");

                $medVO = new MedVO();
                $medVO->setId($sanitize->sanitizeString("busca"));
                $medVO->setClave($sanitize->sanitizeInt("Producto"));
                $medVO->setCantidad($meTmpVO->getVolumenfac());
                $precio = $tipo == 1 ? $importe : $importe / $meTmpVO->getVolumenfac();
                $medVO->setPrecio($precio);

                if (($id = $medTmpDAO->create($medVO)) > 0) {
                    $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                } else {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }

                TotalizaEntrada($busca);
            } else {
                $Msj = "Ya se ha ingresado el rubro";
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {


            $Return = "entradas.php?";
            $comDAO = new CombustiblesDAO();
            $comVO = $comDAO->retrieve($sanitize->sanitizeInt("Tanque"));
            $cargasVO = $cargasDAO->retrieve($sanitize->sanitizeInt("Carga"));

            $meVO = $meDAO->retrieve($busca);
            $meVO->setTanque($sanitize->sanitizeInt("Tanque"));
            $meVO->setProveedor($sanitize->sanitizeInt("Proveedor"));
            $meVO->setProveedorTransporte($sanitize->sanitizeString("Transporte"));
            $meVO->setProducto($comVO->getClave());
            $meVO->setFechafac($sanitize->sanitizeString("Fechafac"));
            $meVO->setTerminal($sanitize->sanitizeInt("Terminal"));
            $meVO->setClavevehiculo($sanitize->sanitizeString("Clavevehiculo"));
            $meVO->setUuid($sanitize->sanitizeString("FolioFiscal"));
            $meVO->setDocumento($sanitize->sanitizeString("Documento"));
            $meVO->setTipo($sanitize->sanitizeString("Tipo"));
            $meVO->setFoliofac($sanitize->sanitizeString("Foliofac"));
            $meVO->setVolumenfac($sanitize->sanitizeFloat("Volumenfac"));
            $meVO->setPreciou($sanitize->sanitizeFloat("Preciou"));
            $meVO->setImportefac($sanitize->sanitizeFloat("Importefac"));
            $meVO->setCarga($cargasVO->getId());
            //error_log(print_r($meVO, TRUE));

            if ($meDAO->update($meVO)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
                if ($meVO->getTipo() == TipoCarga::JARREO) {
                    $cargasVO->setTipo(1);
                } else {
                    $cargasVO->setTipo(0);
                }
                if (!($cargasDAO->update($cargasVO))) {
                    $Msj = utils\Messages::RESPONSE_ERROR;
                }
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === "Consolidar entrada") {
            $selectEntradas = "SELECT * FROM me_tmp WHERE carga='$carga'";
            $result = $mysqli->query($selectEntradas);

            while ($rg = $result->fetch_array()) {
                $IdAnt = $rg[id];

                $ActualizaPrecioU = "UPDATE me_tmp JOIN "
                        . "(SELECT ROUND( SUM( precio ), 6 ) costo, "
                        . "id "
                        . "FROM med_tmp "
                        . "WHERE med_tmp.clave NOT IN ( 6, 8, 9 ) GROUP BY id ) med "
                        . "ON med.id = me_tmp.id SET me_tmp.preciou = med.costo WHERE me_tmp.id = " . $IdAnt;
                error_log($ActualizaPrecioU);
                $mysqli->query($ActualizaPrecioU);

                $meTmpVO = $meTmpDAO->retrieve($IdAnt);

                if ((!empty($meTmpVO->getFoliofac()) && $meTmpVO->getImportefac() > 0) || $rg["tipo"] === "Consignacion") {

                    if (($id = $meDAO->create($meTmpVO)) > 0) {
                        $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                        $selectMed = "  INSERT INTO med (id,clave,cantidad,precio)
                                    SELECT $id,clave,cantidad,precio 
                                    FROM med_tmp WHERE id = '$IdAnt'";
                        if (!($mysqli->query($selectMed))) {
                            error_log($mysqli->error);
                            $Msj = utils\Messages::RESPONSE_ERROR;
                        }

                        if ($meTmpVO->getTipo() === TipoCarga::NORMAL) {
                            $proveedorVO = $proveedorDAO->retrieve($meTmpVO->getProveedor());
                            if ($proveedorVO->getProveedorde() === "Combustibles" && $proveedorVO->getTipodepago() === "Credito") {
                                $insertCpx = "INSERT INTO cxp (proveedor,referencia,fecha,fechav,tm,concepto,cantidad,importe)
                                    VALUES ( "
                                        . "'" . $proveedorVO->getId() . "',"
                                        . "'$id',"
                                        . "'" . $meTmpVO->getFechae() . "',"
                                        . "DATE_ADD(DATE('" . $meTmpVO->getFechafac() . "'),INTERVAL " . $proveedorVO->getDias_credito() . " DAY),"
                                        . "'C',"
                                        . "'Entrada de pipa',"
                                        . "'" . $meTmpVO->getVolumenfac() . "',"
                                        . "'" . $meTmpVO->getImportefac() . "'"
                                        . ")";
                                if (!($mysqli->query($insertCpx))) {
                                    error_log($mysqli->error);
                                }
                            }
                        }
                        $cargasVO->setEntrada($id);
                        if (!($cargasDAO->update($cargasVO))) {
                            $Msj = utils\Messages::RESPONSE_ERROR;
                        }
                        $Return = "pipaspendientes.php?criteria=ini";

                        $medTmpDAO->remove($IdAnt);
                    } else {
                        $Msj = utils\Messages::RESPONSE_ERROR;
                    }
                } else {
                    $Msj = "Falta ingresar el folio y el importe de de la factura";
                }
            }
            $meTmpDAO->remove($usuarioSesion->getId(), "usuario");
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
        if ($request->getAttribute("op") == 2) {
            $cargasVO = $cargasDAO->retrieve($carga);
            //error_log(print_r($cargasVO, true));
            if (is_numeric($sanitize->sanitizeInt("Terminal"))) {
                $Facturas = $sanitize->sanitizeInt("Facturas");

                $medTmpDAO->removeByUsuario($usuarioSesion->getId());
                $meTmpDAO->remove($usuarioSesion->getId(), "usuario");

                $Count = 1;
                $diafac = date("Y-m-d", strtotime($sanitize->sanitizeString("FechaFac")));
                $mysqli->query("TRUNCATE TABLE me_tmp;");
                while ($Count <= $Facturas) {
                    $meVO = new MeVO();
                    $meVO->setUsuario($usuarioSesion->getId());
                    $meVO->setTanque($cargasVO->getTanque());
                    $meVO->setFechae($cargasVO->getFecha_insercion());
                    $meVO->setProveedor($sanitize->sanitizeString("Proveedor"));
                    $meVO->setProducto($cargasVO->getClave_producto());
                    $meVO->setStatus("Cerrada");
                    $meVO->setVol_inicial($cargasVO->getVol_inicial());
                    $meVO->setVol_final($cargasVO->getVol_final());
                    $meVO->setTerminal($sanitize->sanitizeInt("Terminal"));
                    $meVO->setClavevehiculo($sanitize->sanitizeString("Clavevehiculo"));
                    $meVO->setDocumento($sanitize->sanitizeString("Documento"));
                    $meVO->setCarga($cargasVO->getId());
                    $meVO->setFechafac($diafac);
                    //$meVO->setTipo($sanitize->sanitizeString("Tipo"));
                    $meVO->setTipo($sanitize->sanitizeString("TipoCarga"));
                    $meVO->setT_final($cargasVO->getT_final());
                    $meVO->setIncremento($cargasVO->getAumento());
                    $meVO->setHoraincremento($cargasVO->getFecha_fin());
                    $meVO->setProveedorTransporte($sanitize->sanitizeString("Transporte"));
                    $meVO->setTipoConversion(UnidadMedida::M3);
                    $meVO->setPunto_exportacion($sanitize->sanitizeString("Punto_exportacion"));
                    $meVO->setPunto_internacion($sanitize->sanitizeString("Punto_internacion"));
                    $meVO->setPais_destino($sanitize->sanitizeString("Pais_destino"));
                    $meVO->setPais_origen($sanitize->sanitizeString("Pais_origen"));
                    $meVO->setMedio_transporte_entrada($sanitize->sanitizeString("Medio_entrada"));
                    $meVO->setMedio_transporte_salida($sanitize->sanitizeString("Medio_salida"));
                    $meVO->setIncoterms($sanitize->sanitizeString("Incoterms"));

                    if (($id = $meTmpDAO->create($meVO)) > 0) {
                        $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                    } else {
                        $Msj = utils\Messages::RESPONSE_ERROR;
                        break;
                    }
                    $Count++;
                }
            } else {
                $Msj = "La Terminal debe ser un valor NUMERICO, favor de reintentar";
            }
        } elseif ($request->getAttribute("op") == 9) {
            $Return = "pipaspendientes.php?";

            $cargasVO = $cargasDAO->retrieve($carga);

            $FechaActual = date("m");
            $SS = explode(" ", $cargasVO->getFecha_inicio());
            $DateTime = date("m", strtotime($SS[0]));
            if ($DateTime < $FechaActual) {
                $DateTot = DateTime::createFromFormat('Y-m-d', $SS[0]);
                $FinMes = $DateTot->format("Y-m-t");
                $Insert = "INSERT INTO  resumen_reporte_sat (fecha,reporte,etiqueta,valor,producto) "
                        . "VALUES ('$FinMes','M','Se manda carga a jarreos " . $cargasVO->getId() . "',"
                        . "'" . $cargasVO->getAumento() . "','" . $cargasVO->getProducto() . "')";
                utils\IConnection::execSql($Insert);
            }

            $meVO = new MeVO();
            $meVO->setUsuario($usuarioSesion->getId());
            $meVO->setTanque($cargasVO->getTanque());
            $meVO->setFechae($cargasVO->getFecha_insercion());
            $meVO->setProveedor(1);
            $meVO->setProducto($cargasVO->getClave_producto());
            $meVO->setStatus("Cerrada");
            $meVO->setVol_inicial($cargasVO->getVol_inicial());
            $meVO->setVol_final($cargasVO->getVol_final());
            $meVO->setTerminal(0);
            $meVO->setClavevehiculo("");
            $meVO->setDocumento(TipoCarga::JARREO);
            $meVO->setCarga(0);
            $meVO->setFechafac(date("Y-m-d"));
            $meVO->setTipo(TipoCarga::JARREO);
            $meVO->setT_final($cargasVO->getT_final());
            $meVO->setCuadrada(1);
            $meVO->setIncremento($cargasVO->getAumento());
            $meVO->setHoraincremento($cargasVO->getFecha_fin());
            $meVO->setProveedorTransporte(0);

            if (($id = $meDAO->create($meVO)) > 0) {
                //$Msj = utils\Messages::RESPONSE_VALID_CREATE;
                $cargasVO->setEntrada($id);
                $cargasVO->setTipo(1);
                $cargasDAO->update($cargasVO);
                $Msj = "La pipa fue capturada como Jarreo";
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("op") === utils\Messages::OP_DELETE) {
            if ($medTmpDAO->remove($cId, "idnvo")) {
                $Msj = utils\Messages::RESPONSE_VALID_DELETE;
                TotalizaEntrada($busca);
            } else {
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

function TotalizaEntrada($Entrada) {
    $mysqli = iconnect();

    $Me_tmpA = $mysqli->query("SELECT importefac FROM me_tmp WHERE id = '$Entrada'");
    $Me_tmp = $Me_tmpA->fetch_array();

    $DddA = $mysqli->query("SELECT TRUNCATE( ROUND( IFNULL( SUM( cantidad*precio ), 0.000 ), 3 ), 2 ) importe FROM med_tmp WHERE id = '$Entrada'");
    $Ddd = $DddA->fetch_array();

    if ($Me_tmp['importefac'] > 0 && $Ddd["importe"] > 0) {
        $updateMe_tmp = "UPDATE me_tmp SET cuadrada = " . (abs($Me_tmp['importefac'] - $Ddd["importe"]) < 1 ? 1 : 0) . " WHERE id = '$Entrada'";
        error_log($updateMe_tmp);
        if (!($mysqli->query($updateMe_tmp))) {
            error_log($mysqli->error);
        }
    }

    if ($mysqli != null) {
        $mysqli->close();
    }
}

$Id = 41;
$paginador = new Paginador($Id, "", "", "", "", "cargas.id", "cargas.id", "cargas.id", strtoupper("asc"), 0, "REGEXP", "entradase.php");
