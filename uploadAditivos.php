<?php

include_once ('data/CombustiblesDAO.php');
include_once ('data/ComprasDAO.php');
include_once ('data/ComprasdDAO.php');
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
        $SubTotal = $cfdiComprobante['SubTotal'];
    }
    error_log("Mi subtotal " . $SubTotal);
    $i = 0;
    $Unidad[] = array();
    $Cantidad[] = array();
    $ValorUnitario[] = array();
    $ImporteC[] = array();
    $Descuento[] = array();

    foreach ($carga_xml->xpath('//cfdi:Comprobante//cfdi:Emisor') as $Emisor) {
        $ProveedorName = $Emisor["Nombre"];
        $ProveedorRfc = $Emisor["Rfc"];
    }
    $IdPrv = "SELECT * FROM prv WHERE nombre = '$ProveedorName' OR rfc = '$ProveedorRfc';";
    $Proveedor = utils\IConnection::execSql($IdPrv);
    $Pid = $Proveedor["id"];
    $Pname = $Proveedor["nombre"];

    error_log("Proveedor  " . $Pid . " NAME " . $Pname);
    foreach ($carga_xml->xpath('//cfdi:Comprobante//cfdi:Conceptos//cfdi:Concepto') as $Concepto) {
        $Clave_producto_servicio[$i] = $Concepto["ClaveProdServ"];
        $Cantidad[$i] = $Concepto['Cantidad']; /* Se utliza ******************** */
        $ImporteC[$i] = $Concepto["Importe"];
        $ValorUnitario[$i] = $Concepto["ValorUnitario"]; /* Valor por  unidad de cada producto  ************** */
        $Descuento[$i] = $Concepto["Descuento"];
        $Descripcion[$i] = $Concepto["Descripcion"]; /* Con este buscamos */
        $CantidadTotal += $Concepto['Cantidad'];
        $ImporteTotal += $ImporteC[$i];
        $i++;
    }
    error_log("Cantidad Total : " . $CantidadTotal);
    for ($r = 0; $r <= $i; $r++) {
        error_log("Producto ; " . $Descripcion[$r] . " Cantidad " . $Cantidad[$r] . " Valor unitario :  " . $ValorUnitario[$r]);
    }
    $u = 0;
    foreach ($carga_xml->xpath('//cfdi:Comprobante//cfdi:Conceptos//cfdi:Concepto//cfdi:Impuestos//cfdi:Retenciones//cfdi:Retencion') as $Retenciones) {
        $u++;
        $BaseRetencion[$u] = $Retenciones["Base"];
        $ImporteRetencion[$u] = -$Retenciones["Importe"];
    }

    $ValorUnitario[0] = $ImporteC[0] / $Cantidad[0];
    foreach ($carga_xml->xpath('//cfdi:Comprobante//cfdi:Impuestos') as $Impuestos) {
        $ImpuestosT = $Impuestos["TotalImpuestosTrasladados"];
    }
    error_log("Total de impuesto ; " . $ImpuestosT);
    foreach ($carga_xml->xpath('//t:TimbreFiscalDigital') as $tfd) {
        $Uuid = strtoupper($tfd['UUID']);
    }

    $BuscaUuid = "SELECT id FROM et WHERE observaciones like '%" . $_FILES["file"]["name"][0] . "%' AND status != 'Cancelado';";
    $Existeid = utils\IConnection::execSql($BuscaUuid);
    if (!($Existeid["id"] > 0)) {
        $EtDAO = new ComprasDAO();
        $EtVO = new ComprasVO();
        $ImpuestosT = $ImpuestosT > 0 ? $ImpuestosT : 0;
        $EtVO->setFecha(date("Y-m-d H:i:s"));
        $EtVO->setProveedor($_REQUEST["Proveedor"]);
        $EtVO->setConcepto("Carga con xml");
        $EtVO->setDocumento($Folio);
        $EtVO->setCantidad($CantidadTotal);
        $EtVO->setImporte($ImporteTotal);
        $EtVO->setImportesin($ImporteTotal);
        $EtVO->setIva($ImpuestosT);
        $EtVO->setStatus("Abierta");
        $EtVO->setUuid($Uuid);
        $EtVO->setObservaciones("Archivo : " . $_FILES["file"]["name"][0]);
        $IdE = $EtDAO->create($EtVO);
        $EtdDAO = new ComprasdDAO();
        $EtdVO = new ComprasdVO();
        for ($r = 0; $r <= $i; $r++) {
            error_log($Descripcion[$r]);
            $SqlInv = "SELECT id FROM inv WHERE REPLACE(descripcion,' ','') = REPLACE('" . $Descripcion[$r] . "',' ','');";
            error_log($SqlInv);
            $IdInvS = utils\IConnection::execSql($SqlInv);
            $EtdVO->setId($IdE);
            $EtdVO->setProducto($IdInvS["id"]);
            $EtdVO->setCantidad($Cantidad[$r]);
            $EtdVO->setCosto($ValorUnitario[$r]);
            $EtdVO->setDescuento(0);
            $EtdVO->setAdicional(0);
            $EtdDAO->create($EtdVO);
        }
        $DetalleVolumetrico = "Exito al registrar su xml, Id : $IdE";
        SetExternalMessage($DetalleVolumetrico);
    } else {
        $DetalleVolumetrico = "ERROR: El uuid ya se encuentra registrado en el sistema; Id: " . $Existeid["id"];
        SetExternalMessage($DetalleVolumetrico);
    }
}
?>