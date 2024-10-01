<?php

include_once ('data/MeDAO.php');
include_once ('data/MeTmpDAO.php');
include_once ('data/MedDAO.php');
include_once ('data/MedTmpDAO.php');
include_once ('data/CargasDAO.php');
include_once ('data/ProveedorDAO.php');
include_once ('data/CombustiblesDAO.php');
include_once ("libnvo/lib.php");

use com\softcoatl\cfdi\v33\schema\Comprobante as Comprobante;
use com\softcoatl\utils as utils;

$mysqli = iconnect();

if (move_uploaded_file($_FILES["file"]["tmp_name"][0], "/home/omicrom/xml/" . $_FILES["file"]["name"][0])) {

    $nombreA = "/home/omicrom/xml/" . $_FILES["file"]["name"][0];

    $carga_xml = simplexml_load_file($nombreA); //Obtenemos los datos del xml agregados

    if (!$carga_xml) {
        $location = "/home/omicrom/xml/archivo.xml";
        error_log("XML INCORRECTO POR ALGUN CARACTER ESPECIAL SE DA NOMBRE DE archivo.xml");
        unlink($location);
        $fh = fopen($nombreA, 'r+') or die("Ocurrio un error al abrir el archivo");
        $texto = fgets($fh);
        $archivo = fopen($location, 'a');
        $string = mb_substr($texto, 0, 15);
        $cadena = utf8_decode($texto);
        fputs($archivo, $cadena);
        fclose($archivo);
        $carga_xml = simplexml_load_file($location);
    }

    $ns = $carga_xml->getNamespaces(true);
    $carga_xml->registerXPathNamespace('c', $ns['cfdi']);
    $carga_xml->registerXPathNamespace('t', $ns['tfd']);

    foreach ($carga_xml->xpath('//cfdi:Comprobante') as $cfdiComprobante) {
        $Folio = $cfdiComprobante['Folio'];
        $Importe = $cfdiComprobante['Total'];
        $FechaXml = $cfdiComprobante['Fecha'];
        $TipoDeComprobante = $cfdiComprobante['TipoDeComprobante'];
    }
    $i = 0;
    $Unidad[] = array();
    $Cantidad[] = array();
    $ValorUnitario[] = array();
    $ImporteC[] = array();
    $Descuento[] = array();

    foreach ($carga_xml->xpath('//cfdi:Comprobante//cfdi:Conceptos//cfdi:Concepto') as $Concepto) {
        error_log("Concenptos " . print_r($Concepto, true));
        $Clave_producto_servicio[$i] = $Concepto["ClaveProdServ"];
        $Unidad[$i] = $Concepto['ClaveUnidad'];
        $Cantidad[$i] = $Concepto['Cantidad'];
        $ImporteC[$i] = $Concepto["Importe"];
        $ValorUnitario[$i] = $Concepto["ValorUnitario"];
        $Descuento[$i] = $Concepto["Descuento"];
        $Descripcion[$i] = $Concepto["Descripcion"];
        $i++;
    }
    $u = 0;
    foreach ($carga_xml->xpath('//cfdi:Comprobante//cfdi:Conceptos//cfdi:Concepto//cfdi:Impuestos//cfdi:Retenciones//cfdi:Retencion') as $Retenciones) {
        error_log("RETENCIONES " . print_r($Retenciones, true));
        $u++;
        $BaseRetencion[$u] = $Retenciones["Base"];
        $ImpuestoRetencion[$u] = $Retenciones['Impuesto'];
        $TasaRetencion[$u] = $Retenciones['TasaOCuota'];
        $ImporteRetencion[$u] = -$Retenciones["Importe"];
    }

    $ValorUnitario[0] = $ImporteC[0] / $Cantidad[0];
    foreach ($carga_xml->xpath('//cfdi:Comprobante//cfdi:Impuestos') as $Impuestos) {
        $ImpuestosT = $Impuestos["TotalImpuestosTrasladados"];
        $ImpuestosR = $Impuestos["TotalImpuestosRetenidos"];
    }
    foreach ($carga_xml->xpath('//t:TimbreFiscalDigital') as $tfd) {
        $Uuid = strtoupper($tfd['UUID']);
    }
    $BuscaUuid = "SELECT id FROM me WHERE uuid = '$Uuid'";
    $uuidEx = utils\IConnection::execSql($BuscaUuid);
    if ($uuidEx["id"] > 0) {
        $Msj = "YA EXISTE EL UUID " . $uuidEx["id"] . " EN LA TABLA ME";
        SetExternalMessage($Msj);
        return false;
    }
    $medTmpDAO = new MedTmpDAO();
    $meTmpDAO = new MeTmpDAO();
    $cargasDAO = new CargasDAO();

    $meTmpVO = $meTmpDAO->retrieve($_REQUEST["Cliente"], "uuid='-----' AND usuario");
    $cargasVO = $cargasDAO->retrieve($_REQUEST["busca"]);
    $SS = explode("T", $FechaXml);
    $MesXml = DateTime::createFromFormat('Y-m-d', $SS[0]);
    $MesRegistro = DateTime::createFromFormat('Y-m-d H:i:s', $meTmpVO->getFechae());
    $DetalleVolumetrico = "";
    if ($MesRegistro->format("m") < $MesXml->format("m")) {
        $UltimoDiaMes = $MesRegistro->format("Y-m-t");
        $Insert = "INSERT INTO  resumen_reporte_sat (fecha,reporte,etiqueta,valor,producto) "
                . "VALUES ('$UltimoDiaMes','M','Se ingresa carga de mes anterior. Registro : "
                . $MesRegistro->format("Y-m-d") . " Xml : " . $MesXml->format("Y-m-d") . "',"
                . "'" . $Cantidad[0] . "','" . $Clave_producto_servicio[0] . "')";
        $DetalleVolumetrico = "Aviso, la fecha de la carga es del mes anterior a el archivo XML ingresado. ";
        SetExternalMessage($DetalleVolumetrico);
        $mysqli->query($Insert);
    }
    $meTmpVO->setFoliofac($Folio);
    $meTmpVO->setImportefac($Importe);
    $meTmpVO->setTipocomprobante($TipoDeComprobante);
    $Unidad[0] == "LTR" ? $CantidadConvertida = (float) $Cantidad[0] / 1000 : $Unidad = $Cantidad[0];
    if ($_REQUEST["Location"] == "Si") {
        $meTmpVO->setVolumenfac(0);
    } else {
        $meTmpVO->setVolumenfac($CantidadConvertida);
    }
    $Unidad[0] == "LTR" ? $CantidadConvertida = (float) $ValorUnitario[0] * 1000 : $Unidad = $ValorUnitario[0];
    $meTmpVO->setPreciou($CantidadConvertida);
    $meTmpVO->setUuid($Uuid);

    $Unidad[0] == "LTR" ? $Unidad = 2 : $Unidad = 1;
    $BuscaProductoMatch = "SELECT i.inv_cproducto ip FROM cargas c "
            . "LEFT JOIN inv i ON c.producto=i.descripcion WHERE c.id=" . $_REQUEST['busca'];
    $PdtMatch = $mysqli->query($BuscaProductoMatch)->fetch_array();
    $rsMatch = $PdtMatch["ip"] == $Clave_producto_servicio[0] ? true : false;

    if ($rsMatch || $Clave_producto_servicio[0] = "78101800" || $Clave_producto_servicio[0] = "80141623") {
        if ($meTmpDAO->update($meTmpVO)) {
            $volumenFacturado = $Cantidad[0];
            $precioUnitario = $ValorUnitario[0];

            $Guardar = false;

            //Comparamos que unidad de medida tiene
            if ($Unidad == UnidadMedida::M3) {
                if ($volumenFacturado < MeDAO::MAX_VOLUMEN) {
                    $Guardar = true;
                } else {
                    $Msj = "Volumen en metros cubicos (m3) ingresado no valido, rango valido de [1 - " . MeDAO::MAX_VOLUMEN . "] .";
                }
            } elseif ($Unidad == UnidadMedida::LTS) {
                if ($volumenFacturado > 100) {
                    $volumenFacturado = $volumenFacturado / 1000;
                    $Guardar = true;
                } else {
                    $Msj = "Volumen (lts.) ingresado no valido.";
                }
            }

            if ($Unidad == UnidadMedida::LTS && !($_REQUEST["Location"] == "Si")) {
                $precioUnitario = number_format((float) $precioUnitario * 1000, 6, ".", "");
            } else {
                //return null;
                //$precioUnitario = number_format((float) $precioUnitario * 1000, 6, ".", "");
                $GuardarLogistica = true;
            }

            if ($precioUnitario < $meTmpVO->getImportefac() || ($precioUnitario * $volumenFacturado) < $meTmpVO->getImportefac()) {
                if ($Guardar || $GuardarLogistica) {
                    $MeSql = "SELECT id,foliofac FROM me WHERE foliofac = '" . $meTmpVO->getFoliofac() . "' AND proveedor='" . $meTmpVO->getProveedor() . "' AND carga > 0";
                    $Me = $mysqli->query($MeSql)->fetch_array();
                    if (count($Me) == 0) {
                        $MeTmpSql = "SELECT id,foliofac FROM me_tmp WHERE foliofac = '" . $meTmpVO->getFoliofac() . "' ";
                        $MeTmp = $mysqli->query($MeSql)->fetch_array();
                        if (count($MeTmp) <= 1) {
                            if ($u > 0) {
                                $medVO = new MedVO();
                                $medVO->setId($meTmpVO->getId());
                                $medVO->setClave(1007);
                                $medVO->setCantidad(1);
                                $medVO->setPrecio($ImporteRetencion[$u]);
                                if (($id = $medTmpDAO->create($medVO)) < 0) {
                                    $Msj = utils\Messages::RESPONSE_ERROR;
                                }
                            }
                            $MedTmpSql = "SELECT * FROM med_tmp WHERE id = '" . $meTmpVO->getId() . "' AND clave = '" . $cargasVO->getClave() . "'";
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
                                if ($GuardarLogistica) {
                                    $InvSql = "SELECT id FROM inv WHERE descripcion LIKE '%LOGISTICA%'";
                                    $InvLog = $mysqli->query($InvSql)->fetch_array();
                                    $medVO->setClave($InvLog[0]);
                                } else {
                                    $medVO->setClave($cargasVO->getClave());
                                }
                                $medVO->setCantidad($volumenFacturado);
                                $medVO->setPrecio($precioUnitario);
                                if (($id = $medTmpDAO->create($medVO)) < 0) {
                                    $Msj = utils\Messages::RESPONSE_ERROR;
                                }
                            }
                            TotalizaEntrada($meTmpVO->getId());
                            //Agreagmos los diferentes conceptos que pueda tener todos con una misma clave
                            if ($i >= 2) {
                                for ($h = 1; $h <= $i - 1; $h++) {
                                    $medVO->setId($meTmpVO->getId());
                                    $InvSql = "SELECT id FROM inv WHERE descripcion = 'COMERCIALIZACION' AND rubro = 'Ent-pipas'";
                                    $Inv = $mysqli->query($InvSql)->fetch_array();
                                    $ValorVariable = "SELECT valor FROM variables_corporativo WHERE llave = 'IdComercializacion';";
                                    $Vv = $mysqli->query($ValorVariable)->fetch_array();

                                    $Clave = is_numeric($Inv["id"]) ? $Inv["id"] : $Vv["valor"];
                                    $medVO->setClave($Clave);
                                    $medVO->setCantidad($volumenFacturado);
                                    $medVO->setPrecio((float) $ImporteC[$h] / $volumenFacturado);

                                    if (($id = $medTmpDAO->create($medVO)) > 0) {
                                        $Msj = utils\Messages::RESPONSE_VALID_CREATE;
                                    } else {
                                        $Msj = utils\Messages::RESPONSE_ERROR;
                                    }
                                    TotalizaEntrada($meTmpVO->getId());
                                }
                            }

                            //En caso de tener descuento agregamos el concepto
                            if ($Descuento[0] > 0) {

                                $medVO->setId($meTmpVO->getId());
                                $medVO->setClave(10);
                                $medVO->setCantidad($volumenFacturado);
                                $Precio = - $Descuento[0] / $volumenFacturado;
                                $medVO->setPrecio((float) $Precio);

                                if (($id = $medTmpDAO->create($medVO)) < 0) {
                                    $Msj = utils\Messages::RESPONSE_ERROR;
                                }
                                TotalizaEntrada($meTmpVO->getId());
                            }

                            //Agregamos los datos de los impuestos
                            $medVO->setId($meTmpVO->getId());
                            $medVO->setClave(6);
                            $medVO->setCantidad($volumenFacturado);
                            error_log("AQUI TENEMOS IMPUESTO R " . $ImpuestosR . " Y TENEMOS IMPUESTOT " . $ImpuestosT);

                            if ($ImpuestosR == "") {
                                $medVO->setPrecio((float) $ImpuestosT / $volumenFacturado);
                            } else {
                                $Ttl = ($ImpuestosT) / $medVO->getCantidad();
                                $medVO->setPrecio((float) $Ttl);
                            }
                            if (($id = $medTmpDAO->create($medVO)) < 0) {
                                $Msj = utils\Messages::RESPONSE_ERROR;
                            }

                            TotalizaEntrada($meTmpVO->getId());
                        }
                    } else {
                        $Msj = "ERROR 1001 : El folio de la factura ya se encuentra registrada en la entrada no. " . $Me["id"] . ", favor de verificar";
                    }
                }
            }
            $Msj = strstr($Msj, "ERROR") ? $Msj : "¡Factura cuadrada!";
            SetExternalMessage($Msj . $DetalleVolumetrico);
        } else {
            $Msj = utils\Messages::RESPONSE_ERROR;
        }
    } else {
        $Msj = "ERROR: En producto del XML favor de verificar";
        SetExternalMessage($Msj);
    }
} else {
    echo "Error en guardado";
}

function TotalizaEntrada($Entrada) {
    $mysqli = iconnect();

    $Me_tmpA = $mysqli->query("SELECT importefac FROM me_tmp WHERE id = '$Entrada'");
    $Me_tmp = $Me_tmpA->fetch_array();

    $DddA = $mysqli->query("SELECT TRUNCATE( ROUND( IFNULL( SUM( cantidad*precio ), 0.000 ), 3 ), 2 ) importe FROM med_tmp WHERE id = '$Entrada'");
    $Ddd = $DddA->fetch_array();

    if ($Me_tmp['importefac'] > 0 && $Ddd["importe"] > 0) {

        $Cuadrada = abs($Me_tmp['importefac'] - $Ddd["importe"]);
        $Cuadrada = number_format($Cuadrada, 2);

        $updateMe_tmp = "UPDATE me_tmp SET cuadrada = " . ($Cuadrada < 1.3 ? 1 : 0 ) . " WHERE id = '$Entrada'";

        if (!($mysqli->query($updateMe_tmp))) {
            error_log($mysqli->error);
        }
    }

    if ($mysqli != null) {
        $mysqli->close();
    }
}

?>