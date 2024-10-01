<?php

include_once ('data/PagoDAO.php');
include_once ('data/CargasDAO.php');
include_once ('data/CxcDAO.php');
include_once ('data/FcDAO.php');
include_once ('data/FcdDAO.php');
include_once ('data/CiaDAO.php');
include_once ('data/CombustiblesDAO.php');
include_once ("libnvo/lib.php");
include_once ('data/V_CorporativoDAO.php');

use com\softcoatl\cfdi\v33\schema\Comprobante as Comprobante;
use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();

$CalculoOXml = "SELECT valor FROM omicrom.variables_corporativo WHERE llave = 'MonederoCalculado'";
$CoX = utils\IConnection::execSql($CalculoOXml);
if ($CoX["valor"] == 1) {
    $IepsMagna = "SELECT ieps FROM omicrom.com WHERE clave = 32011;";
    $IepsM = utils\IConnection::execSql($IepsMagna);
    $IepsPremium = "SELECT ieps FROM omicrom.com WHERE clave = 32012;";
    $IepsP = utils\IConnection::execSql($IepsPremium);
    $IepsDiesel = "SELECT ieps FROM omicrom.com WHERE clave = 34006;";
    $IepsD = utils\IConnection::execSql($IepsDiesel);
}
$Usuario = $_REQUEST["Usr"];
$ClienteNvo = $_REQUEST["Cliente"];
$ciaDAO = new CiaDAO();
$ciaVO = new CiaVO();
$ComDAO = new CombustiblesDAO();
$ciaVO = $ciaDAO->retrieve(1);
$vCorporativoDAO = new V_CorporativoDAO();
$NameFile = $_FILES["file"]["name"][0]; //Se sube con el sistema y es lo que cacha del monedero
//$NameFile = "CTC000000733093FACTURA.xml"; //En caso de subir el archivo a mano, renombrar el nombre del documento y con eso se cargara
$BuscaUuid = "SELECT * FROM fc WHERE observaciones LIKE '%" . $NameFile . "%'";
$Dt = $mysqli->query($BuscaUuid)->fetch_array();
if (!($Dt["id"] > 0)) {
    if (move_uploaded_file($_FILES["file"]["tmp_name"][0], "/home/omicrom/xml/" . $NameFile)) {

        $PagoDAO = new PagoDAO();
        $PagoVO = new PagoVO();
        $FcDAO = new FcDAO();
        $FcVO = new FcVO();
        $CxcDAO = new CxcDAO();
        $CxcVO = new CxcVO();
        $nombreA = "/home/omicrom/xml/" . $NameFile;

        $carga_xml = simplexml_load_file($nombreA, 'SimpleXMLElement', 0, 'cfdi', true); //Obtenemos los datos del xml agregados

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
        $Comprobante = array();
        foreach ($carga_xml->xpath('//cfdi:Comprobante//cfdi:Complemento//consumodecombustibles11:ConsumoDeCombustibles') as $comp) {
            $Comprobante['SubTotal'] = $comp['subTotal'];
            $Comprobante['Total'] = $comp['total'];
            error_log(print_r($Comprobante, true));
        }
        $carga_xml->registerXPathNamespace('c', $ns['cfdi']);
        $carga_xml->registerXPathNamespace('t', $ns['tfd']);

        foreach ($carga_xml->xpath('//cfdi:Comprobante') as $cfdiComprobante) {
            $Comprobante['Folio'] = $cfdiComprobante['Folio'][0];
            $Comprobante['Fecha'] = $cfdiComprobante['Fecha'][0];
            $Comprobante['Moneda'] = $cfdiComprobante['Moneda'][0];
            $Comprobante['MetodoPago'] = $cfdiComprobante['MetodoPago'][0];
            $Comprobante['FormaPago'] = $cfdiComprobante['FormaPago'][0];
        }
        foreach ($carga_xml->xpath('//cfdi:Comprobante//cfdi:Receptor') as $cfdiReceptor) {
            $Comprobante['UsoCFDI'] = $cfdiComprobante['UsoCFDI'][0];
        }
        foreach ($carga_xml->xpath('//cfdi:Comprobante//cfdi:Emisor') as $cfdiEmisor) {
            $Comprobante['RfcEmisor'] = $cfdiEmisor['Rfc'][0];
        }

        foreach ($carga_xml->xpath('//t:TimbreFiscalDigital') as $tfd) {
            $Uuid = strtoupper($tfd['UUID']);
        }
        $PagoVO->setImporte($Comprobante['Total']);
        $PagoVO->setUuid($Uuid);
        $PagoVO->setCliente($ClienteNvo);
        $PagoVO->setFecha_deposito(date("Y-m-d H:i:s"));
        $PagoVO->setStatus("Abierta");
        $PagoVO->setFormapago($Comprobante['FormaPago']);
        $PagoVO->setConcepto("Carga XML " . $NameFile . " | ");
        if ($IdMc = $PagoDAO->create($PagoVO)) {
            $CxcVO->setCliente($ClienteNvo);
            $CxcVO->setReferencia($IdMc);
            $CxcVO->setFecha(date("Y-m-d"));
            $CxcVO->setHora(date("H:m:s"));
            $CxcVO->setTm("H");
            $CxcVO->setConcepto("PAGO A CUENTA Recibo " . $IdMc);
            $CxcVO->setCantidad(1);
            $CxcVO->setImporte($Comprobante['Total']);
            $CxcVO->setRecibo($IdMc);
            $CxcVO->setProducto("-");
            $CxcVO->setRubro("Monedero");
            $CxcVO->setCorte(1);
            if (!$CxcDAO->create($CxcVO)) {
                error_log("REGISTRO CREADO CON error");
            }

            $i = 0;
            $Unidad[] = array();
            $Cantidad[] = array();
            $ValorUnitario[] = array();
            $ImporteC[] = array();
            $Descuento[] = array();
            $CantidadMag = 0.00;
            $ImporteMag = 0.00;
            $DescuentoMag = 0.00;
            $CantidadPrem = 0.00;
            $ImportePrem = 0.00;
            $DescuentoPrem = 0.00;
            $CantidadDie = 0.00;
            $ImporteDie = 0.00;
            $DescuentoDie = 0.00;
            $array = array();

            $e = 0;
            foreach ($carga_xml->xpath('//cfdi:Comprobante//cfdi:Complemento//consumodecombustibles11:ConsumoDeCombustibles//consumodecombustibles11:Conceptos//consumodecombustibles11:ConceptoConsumoDeCombustibles//consumodecombustibles11:Determinados//consumodecombustibles11:Determinado') as $Traslado) {
                $array[$e] = $_REQUEST["MontoNeto"] == 1 ? $Traslado["importe"] : 0;
                $e++;
            }
            $C = 0;
            foreach ($carga_xml->xpath('//cfdi:Comprobante//cfdi:Complemento//consumodecombustibles11:ConsumoDeCombustibles') as $rsult) {
                $C++;
            }

            $e = 0;
            foreach ($carga_xml->xpath('//cfdi:Comprobante//cfdi:Complemento//consumodecombustibles11:ConsumoDeCombustibles//consumodecombustibles11:Conceptos//consumodecombustibles11:ConceptoConsumoDeCombustibles') as $ConcComDeCom) {
                (double) $Impuesto = 0.00;
                // error_log("Concepto " . print_r($ConcComDeCom, true));

                $Impuesto = (double) $array[$e];
                if ($ConcComDeCom["tipoCombustible"] == 1) {
                    (double) $CnMg = (double) $ConcComDeCom['cantidad'];
                    if ($CoX["valor"] == 1) {
                        $TTIeps = $IepsM["ieps"] * (double) $ConcComDeCom['cantidad'];  /* Calculamos Ieps total */
                        $IvaMgna = ($ConcComDeCom['importe'] - $TTIeps) * 0.16; /* IVA Magna */
                        (double) $ImpMg = (double) $ConcComDeCom['importe'] + $IvaMgna; /* Calculo de iva con respectoa ieps interno omicrom */
                    } else {
                        (double) $ImpMg = (double) $ConcComDeCom['importe'] + $Impuesto; /* Impuestos otorgados por el xml */
                    }
                    (double) $DesMg = (double) $ConcComDeCom["descuento"];

                    $Clave_producto_servicioMag = 1;

                    $CantidadMag += $CnMg;
                    $ImporteMag += $ImpMg;
                    $DescuentoMag += $DesMg;
                } else if ($ConcComDeCom["tipoCombustible"] == 2) {
                    (double) $CnPm = (double) $ConcComDeCom['cantidad'];
                    if ($CoX["valor"] == 1) {
                        $TTIeps = $IepsP["ieps"] * (double) $ConcComDeCom['cantidad'];  /* Calculamos Ieps total */
                        $IvaPrm = ($ConcComDeCom['importe'] - $TTIeps) * 0.16; /* IVA Magna */
                        (double) $IpPm = (double) $ConcComDeCom['importe'] + $IvaPrm;   /* Calculo de iva con respectoa ieps interno omicrom */
                    } else {
                        (double) $IpPm = (double) $ConcComDeCom['importe'] + $Impuesto; /* Impuestos otorgados por el xml */
                    }
                    (double) $DsPm = (double) $ConcComDeCom['descuento'];

                    $Clave_producto_servicioPrem = 2;

                    $CantidadPrem += $CnPm;
                    $ImportePrem += $IpPm;
                    $DescuentoPrem += $DsPm;
                } else if ($ConcComDeCom["tipoCombustible"] == 3 || $ConcComDeCom["tipoCombustible"] == 4) {
                    (double) $CnDs = (double) $ConcComDeCom['cantidad'];
                    if ($CoX["valor"] == 1) {
                        $TTIeps = $IepsD["ieps"] * (double) $ConcComDeCom['cantidad'];  /* Calculamos Ieps total */
                        $IvaDsl = ($ConcComDeCom['importe'] - $TTIeps) * 0.16; /* IVA Magna */
                        (double) $IpDs = (double) $ConcComDeCom['importe'] + $IvaDsl; /* Calculo de iva con respectoa ieps interno omicrom */
                    } else {
                        (double) $IpDs = (double) $ConcComDeCom['importe'] + $Impuesto; /* Impuestos otorgados por el xml */
                    }
                    (double) $DsDs = (double) $ConcComDeCom['descuento'];

                    $Clave_producto_servicioDie = 3;

                    $CantidadDie += $CnDs;
                    $ImporteDie += $IpDs;
                    $DescuentoDie += $DsDs;
                }
                $i++;
                $e++;
            }
            error_log("TOTAL DE REGISTROS : " . $e);
            $vCorporativoVO = $vCorporativoDAO->retrieve("serie_monederos_xml");

            if (!empty($vCorporativoVO->getValor())) {
                $serie = $vCorporativoVO->getValor();
            }

            $FSql = "SELECT IFNULL( MAX( fc.folio ), 0 ) + 1 folio FROM fc WHERE fc.serie = '" . $serie . "'";
            $folio = $mysqli->query($FSql)->fetch_array();

            $FcVO->setCliente($PagoVO->getCliente());
            $FcVO->setFolio($folio["folio"]);
            $FcVO->setFecha(date("Y-m-d H:i:s"));
            $FcVO->setSerie($serie);
            $FcVO->setStatus(0);
            $FcVO->setUuid("-----");
            $FcVO->setTicket(0);
            $FcVO->setObservaciones("");
            $FcVO->setUsr($Usuario);
            $FcVO->setOrigen(OrigenFactura::OMICROM);
            $FcVO->setStCancelacion(0);
            $FcVO->setRelacioncfdi($IdMc);
            $FcVO->setMetododepago($Comprobante['MetodoPago']);
            $FcVO->setUsocfdi($Comprobante['UsoCFDI']);
            $FcVO->setDocumentoRelacion("FAC");
            $FcVO->setFormadepago($Comprobante['FormaPago']);
            if ($Idfc = $FcDAO->create($FcVO)) {
                $Fc1VO = new FcVO();
                $Fc1VO = $FcDAO->retrieve($Idfc);
                $noEstacion = explode("E", $ciaVO->getNumestacion());
                $Observaciones = "Factura: " . $NameFile . " | " . $noEstacion[1] . "  " . $Comprobante['Folio'] . " " . $Comprobante['RfcEmisor'];
                $Fc1VO->setObservaciones("$Observaciones");
                if ($FcDAO->update($Fc1VO)) {
                    //error_log("EXITO ACTUALIZANDO " . print_r($Fc1VO, true));
                }
                $Observaciond = "" . $Comprobante['Folio'] . "";
                $fcdDAO = new FcdDAO();
                $fcdVO = new FcdVO();
                error_log("Clave producto servicio M " . $Clave_producto_servicioMag);
                error_log("Clave producto servicio P " . $Clave_producto_servicioPrem);
                error_log("Clave producto servicio D " . $Clave_producto_servicioDie);
                if (is_numeric($Clave_producto_servicioMag)) {
                    $ComVO = new CombustiblesVO();
                    $ComVO = $ComDAO->retrieve(32011, "clave");
                    $Sql = "SELECT inv.id, com.ieps FROM inv LEFT JOIN com ON inv.descripcion = com.descripcion "
                            . "WHERE com.claveSubProducto = $Clave_producto_servicioMag;";
                    $SqlR = $mysqli->query($Sql)->fetch_array();
                    $Precio = round($ImporteMag / $CantidadMag, 2);
                    $fcdVO->setId($Idfc);
                    $fcdVO->setProducto($SqlR["id"]);
                    $fcdVO->setCantidad($CantidadMag);
                    $fcdVO->setPreciob($Precio);
                    $Precio = ROUND(($Precio - $SqlR["ieps"]) / (1 + number_format($ciaVO->getIva() / 100, 2)), 6);
                    $fcdVO->setPrecio($Precio);
                    $fcdVO->setIva(0.16);
                    $fcdVO->setIeps($SqlR["ieps"]);
                    $fcdVO->setImporte($ImporteMag);
                    $fcdVO->setIva_retenido(0);
                    $fcdVO->setIsr_retenido(0);
                    $fcdVO->setTicket(0);
                    $fcdVO->setTipoc("C");
                    if ($Id = $fcdDAO->create($fcdVO)) {
                        $Msj = "Registro creado con exito!" . $Id;
                    } else {
                        $Msj = "Error en sql " . $Id;
                    }
                }
                if (is_numeric($Clave_producto_servicioPrem)) {
                    $ComVO = new CombustiblesVO();
                    $ComVO = $ComDAO->retrieve(32012, "clave");
                    $Sql = "SELECT inv.id, com.ieps FROM inv LEFT JOIN com ON inv.descripcion = com.descripcion "
                            . "WHERE com.claveSubProducto = $Clave_producto_servicioPrem;";
                    $SqlR = $mysqli->query($Sql)->fetch_array();
                    $Precio = round($ImportePrem / $CantidadPrem, 2);
                    $fcdVO->setId($Idfc);
                    $fcdVO->setProducto($SqlR["id"]);
                    $fcdVO->setCantidad($CantidadPrem);
                    $fcdVO->setPreciob($Precio);
                    $Precio = ROUND(($Precio - $SqlR["ieps"]) / (1 + number_format($ciaVO->getIva() / 100, 2)), 6);
                    $fcdVO->setPrecio($Precio);
                    $fcdVO->setIva(0.16);
                    $fcdVO->setIeps($SqlR["ieps"]);
                    $fcdVO->setImporte($ImportePrem);
                    $fcdVO->setIva_retenido(0);
                    $fcdVO->setIsr_retenido(0);
                    $fcdVO->setTicket(0);
                    $fcdVO->setTipoc("C");
                    if ($Id = $fcdDAO->create($fcdVO)) {
                        $Msj = "Registro creado con exito!" . $Id;
                    } else {
                        $Msj = "Error en sql " . $Id;
                    }
                }
                if (is_numeric($Clave_producto_servicioDie)) {
                    $ComVO = new CombustiblesVO();
                    $ComVO = $ComDAO->retrieve(34006, "clave");
                    $Sql = "SELECT inv.id, com.ieps FROM inv LEFT JOIN com ON inv.descripcion = com.descripcion "
                            . "WHERE com.claveSubProducto = $Clave_producto_servicioDie;";
                    $SqlR = $mysqli->query($Sql)->fetch_array();
                    $Precio = round($ImporteDie / $CantidadDie, 2);
                    $fcdVO->setId($Idfc);
                    $fcdVO->setProducto($SqlR["id"]);
                    $fcdVO->setCantidad($CantidadDie);
                    $fcdVO->setPreciob($Precio);
                    $Precio = ROUND(($Precio - $SqlR["ieps"]) / (1 + number_format($ciaVO->getIva() / 100, 2)), 6);
                    $fcdVO->setPrecio($Precio);
                    $fcdVO->setIva(0.16);
                    $fcdVO->setIeps($SqlR["ieps"]);
                    $fcdVO->setImporte($ImporteDie);
                    $fcdVO->setIva_retenido(0);
                    $fcdVO->setIsr_retenido(0);
                    $fcdVO->setTicket(0);
                    $fcdVO->setTipoc("C");
                    if ($Id = $fcdDAO->create($fcdVO)) {
                        $Msj = "Registro creado con exito!" . $Id;
                    } else {
                        $Msj = "Error en sql " . $Id;
                    }
                }

                TotalizaFactura($Idfc, $FcDAO);
            }
        }
        $Sql = "SELECT total  FROM fc WHERE id = " . $Idfc;
        $SqlRt = $mysqli->query($Sql)->fetch_array();

        $FcVO = $FcDAO->retrieve($Idfc);
        if ($PagoVO->getImporte() > $SqlRt["total"]) {
            $dif = $PagoVO->getImporte() - $SqlRt["total"];
            if ($dif > 1) {
                
            } else {
                $FcVO->setImporte($FcVO->getImporte() + $dif);
                $FcVO->setTotal($FcVO->getTotal() + $dif);
            }
            $FcDAO->update($FcVO);
        } else if ($PagoVO->getImporte() < $SqlRt["total"]) {
            $dif = $SqlRt["total"] - $PagoVO->getImporte();
            if ($dif > 1) {
                $SinIva = $dif / 1.16;
                $DIva = $dif - $SinIva;
                $FcVO->setImporte($FcVO->getImporte() - $SinIva);
                $FcVO->setIva($FcVO->getIva() - $DIva);
                $FcVO->setTotal($FcVO->getTotal() - $dif);
            } else {
                $FcVO->setImporte($FcVO->getImporte() - $dif);
                $FcVO->setTotal($FcVO->getTotal() - $dif);
            }
            $FcDAO->update($FcVO);
            $FcVO = $FcDAO->retrieve($Idfc);
            if ($FcVO->getTotal() <> $PagoVO->getImporte()) {
                $df = $FcVO->getTotal() - $PagoVO->getImporte();
                error_log("HAY DIFERENCIAS " . $df);
                $FcVO->setImporte($FcVO->getImporte() - $df);
                $FcVO->setTotal($FcVO->getTotal() - $df);
            }
            $FcDAO->update($FcVO);
        }
    } else {
        echo "Error en guardado";
    }
    echo "Exito";
} else {
    echo "Error";
}

if ($C > 1) {
    $SqlCorreccionGeneral = "SELECT SUM(fcd.importe) total,((SUM(fcd.importe) - fc.ieps)/1.16)*0.16 iva,
            SUM(fcd.importe) - fc.ieps- (((SUM(fcd.importe)- fc.ieps)/1.16)*0.16) importe,
            fc.ieps,fc.total,pagos.id, fc.ieps iepsT
            FROM fcd LEFT JOIN fc ON fc.id=fcd.id LEFT JOIN pagos ON fc.relacioncfdi=pagos.id where fc.id = " . $Idfc;
    $rsC = utils\IConnection::execSql($SqlCorreccionGeneral);
    $SumFcd = $rsC["importe"] + $rsC["iva"] + $rsC["iepsT"];
    $SqlCorreccionGeneral = "UPDATE pagos SET importe = " . $SumFcd . " WHERE id = " . $rsC["id"];
    utils\IConnection::execSql($SqlCorreccionGeneral);
    $SqlCorreccionGeneral = "UPDATE fc SET importe = " . $rsC["importe"] . ",iva = " . $rsC["iva"] . ",total = " . $SumFcd . " WHERE id= " . $Idfc;
    utils\IConnection::execSql($SqlCorreccionGeneral);
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
                cantidad, total, importe, iva, total-importe-iva ieps
                FROM (
                SELECT
                ROUND( sum( cantidad ), 3) cantidad,
                ROUND( sum( total ) - sum(retenido), 2) total,
                ROUND( sum( cantidad * ( preciob - factorieps ) / (1 + factoriva) ), 2) importe,
                ROUND( sum( cantidad * ( preciob - factorieps ) / (1 + factoriva) ) * factoriva, 2) iva
                FROM (
                SELECT
                iva factoriva,
                ieps factorieps,
                cantidad,
                importe total,
                preciob,
                if(iva_retenido * importe / (1 + iva) = 0, 0, iva_retenido * importe / (1 + iva)) retenido
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
    }
    $fcVO = $fcDAO->retrieve($factura);
    $fcVO->setCantidad($Cnt);
    $fcVO->setImporte($Importe);
    $fcVO->setIva($Iva);
    $fcVO->setIeps($Ieps);
    $fcVO->setTotal($Total);

    if (!$fcDAO->update($fcVO)) {
        error_log("Ha ocurrido un error");
    }
}

?>