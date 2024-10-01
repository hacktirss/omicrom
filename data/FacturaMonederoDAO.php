<?php

/*
 * FacturaDAO Objeto DAO.
 * Recupera la información referente a la factura con fc.id = $folio
 * Crea un objeto de tipo Comprobante y los nodos requeridos.
 * La información vaciada en Comprobante se encuentra contenida en las tablas cia, cli, fc, fcd, rm, vtaditivos.
 * Este módulo está escrito de acuerdo a la estructura de base de datos, reglas y definiciones del sistema Omicrom®, Sistema de Control Volumétrico,
 * y cumple con las especificaciones definidas por la autoridad tributaria SAT.
 * 
 * omicrom®
 * © 2017, Detisa 
 * http://www.detisa.com.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since jul 2017
 */

namespace com\detisa\omicrom;

require_once ('mysqlUtils.php');
//require_once ('cfdi33/Comprobante.php');
require_once ('com/softcoatl/cfdi/v40/schema/Comprobante40.php');
require_once ('com/softcoatl/cfdi/addenda/detisa/Observaciones.php');
//require_once ('cfdi33/addenda/Observaciones.php');
require_once ('pdf/PDFTransformer.php');

//use \com\softcoatl\cfdi\v33\schema as cfdi33;
use com\softcoatl\cfdi\v40\schema as cfdi40;
use com\softcoatl\cfdi\addenda as Adenda;

class FacturaMonederoDAO {

    private $folio;
    /* @var $comprobante cfdi40Comprobante */
    private $comprobante;
    /* @var $mysqlConnection \mysqli */
    private $mysqlConnection;

    function __construct($folio) {

        error_log("CFDI33 para Monedero. Cargando CFDI con folio " . $folio);

        $this->folio = $folio;
        $this->comprobante = new cfdi40\Comprobante40();
        $this->mysqlConnection = getConnection();

        $this->comprobante();
        $this->InformacionGlobal();
        $this->emisor();
        $this->receptor();
//$this->cfdiRelacionados();
        $this->conceptos();
        $this->impuestos();
        $this->observaciones();
//error_log(print_r($this->getComprobante(), TRUE));
    }

//constructor

    public function __destruct() {
        $this->mysqlConnection->close();
    }

    function getComprobante() {
        return $this->comprobante;
    }

    function getFolio() {
        return $this->folio;
    }

    function setFolio($folio) {
        $this->folio = $folio;
    }

    /**
     * Recupera la información relativa a la factura.
     * Crea el objeto Comprobante
     */
    private function comprobante() {
        $sql = "
                SELECT 
                    fc.folio Folio, 
                    fc.serie Serie, 
                    DATE_FORMAT(fc.fecha, '%Y-%m-%dT%H:%i:%s') Fecha, 
                    fc.formadepago FormaPago, 
                    fc.metododepago MetodoPago, 
                    fc.total Total, 
                    TRIM( cia.codigo ) LugarExpedicion 
                FROM fc JOIN cia ON TRUE 
                WHERE fc.id = " . $this->folio;

        if (($query = $this->mysqlConnection->query($sql)) && ($rs = $query->fetch_assoc())) {

            $this->comprobante->setFolio($rs['Folio']);
            $this->comprobante->setSerie($rs['Serie']);
            $this->comprobante->setFecha($rs['Fecha']);
            $this->comprobante->setTipoDeComprobante("I");
            $this->comprobante->setVersion("4.0");
            $this->comprobante->setFormaPago($rs['FormaPago']);
            $this->comprobante->setMetodoPago("PUE");
            $this->comprobante->setMoneda("MXN");
            $this->comprobante->setTipoCambio(1);
            $this->comprobante->setTotal(number_format($rs['Total'], 2, '.', ''));
            $this->comprobante->setLugarExpedicion($rs['LugarExpedicion']);
            $this->comprobante->setExportacion("01");
        }//if
    }

//comprobante

    private function InformacionGlobal() {
        $InfGlobal = new cfdi40\Comprobante40\InformacionGlobal();

        $sql = "SELECT periodo,meses,ano FROM fc JOIN cia ON TRUE WHERE fc.id = " . $this->folio;
        if (($query = $this->mysqlConnection->query($sql)) && ($rs = $query->fetch_assoc())) {
            $InfGlobal->setAnio($rs["ano"]);
            $InfGlobal->setMeses($rs["meses"]);
            $InfGlobal->setPeriodicidad($rs["periodo"]);
            $this->comprobante->setInformacionGlobal($InfGlobal);
        }
    }

    /**
     * Recupera los datos de la estación de servicio.
     * Crea el nodo Emisor.
     */
    private function emisor() {

        /* @var $emisor cfdi40\Comprobante40\Emisor */
        $emisor = new cfdi40\Comprobante40\Emisor();
        $Cia = \ClientesDAO::getEmisor();
        $emisor->setNombre($Cia->getNombre());
        $emisor->setRfc($Cia->getRfc());
        $emisor->setRegimenFiscal($Cia->getRegimenFiscal());
        $this->comprobante->setEmisor($emisor);
    }

//emisor

    /**
     * Recupera los datos del receptor del CFDI.
     * Crea el nodo Receptor.
     */
    private function receptor() {

        $sqlExp = "SELECT TRIM( cia.codigo ) LugarExpedicion 
                FROM fc JOIN cia ON TRUE 
                WHERE fc.id = " . $this->folio;

        $queryExp = $this->mysqlConnection->query($sqlExp);
        $rsExp = $queryExp->fetch_assoc();

        /* @var $receptor cfdi40\Comprobante40\Receptor */
        $receptor = new cfdi40\Comprobante40\Receptor();
        $receptor->setNombre("PUBLICO EN GENERAL");
        $receptor->setDomicilioFiscalReceptor($rsExp["LugarExpedicion"]);
        $receptor->setRegimenFiscalReceptor("616");
        $receptor->setRfc("XAXX010101000");
        $receptor->setUsoCFDI("S01");
        $this->comprobante->setReceptor($receptor);
    }

//receptor

    /**
     * Recupera el CFDI relacionado. Por definición de Detisa, solo se soporta un CFDI relacionado.
     * Crea el nodo CfdiRelacionados si es necesario.
     */
    private function cfdiRelacionados() {

        $cfdiRelacionados = new cfdi40\Comprobante40\CfdiRelacionados();
        $cfdiRelacionado = new cfdi40\Comprobante40\CfdiRelacionados\CfdiRelacionado();

        $sql = "
            SELECT IFNULL(F.tiporelacion,  '') TipoRelacion, IFNULL(R.uuid,  '') ruuid UUID
            FROM fc F
            LEFT JOIN fc R ON R.id = F.relacioncfdi
            WHERE F.id = " . $this->folio;

        if (($query = $this->mysqlConnection->query($sql)) && ($rs = $query->fetch_assoc())) {

            if (!empty($rs['UUID'])) {

                $cfdiRelacionado->setUUID($rs['UUID']);
                $cfdiRelacionados->addCfdiRelacionado($cfdiRelacionado);
                $cfdiRelacionados->setTipoRelacion($rs['TipoRelacion']);
                $this->comprobante->setCfdiRelacionados($cfdiRelacionados);
            }
        }
    }

//cfdiRelacionados

    /**
     * Recupera los conceptos asociados a la factura.
     * Crea el nodo Conceptos, el arreglo de nodos Concepto y los nodos de Impuesto asociados a cada Concepto.
     */
    private function conceptos() {

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
//. "ROUND( ( fcd.subtotal + diferencia + ROUND( IF( cli.desgloseIEPS = 'S', 0.0000, fcd.tax_ieps ), 4 ) ) / ( fcd.preciou + ROUND( IF( cli.desgloseIEPS = 'S', 0.0000, fcd.factorieps ), 4 ) ), 4 ) Cantidad, "
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
                . "WHERE fcd.id = " . $this->folio . " "
                . ") A "
                . ") fcd "
                . "JOIN ( SELECT IFNULL( ( SELECT permiso valor FROM permisos_cre WHERE catalogo = 'VARIABLES_EMPRESA' AND llave = 'PERMISO_CRE' ), '' ) cre ) cia ON TRUE "
                . "JOIN inv ON inv.id = fcd.clave_producto "
                . "JOIN fc ON fcd.folio = fc.id "
                . "JOIN cli ON cli.id = fc.cliente "
                . "ORDER BY NoIdentificacion, ValorUnitario, factorieps";

        $conceptos = new cfdi40\Comprobante40\Conceptos();
        $subTotal = 0.00;
        $total = 0.00;

        if (($query = $this->mysqlConnection->query($sql))) {

            while (($rs = $query->fetch_assoc())) {
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
                    $this->mysqlConnection->query($Update);
                } elseif ($LimitSuperior < $rs['Importe']) {
                    $Update = "UPDATE fcd SET preciob = preciob - 0.01 WHERE idnvo = " . $rs['idnvo'];
                    $this->mysqlConnection->query($Update);
                }

                $rgst = explode("|", $rs["obsrfc"]);
                $Desc = is_string($rgst[1]) ? $rgst[1] : $rs['Descripcion'];
                $concepto = new cfdi40\Comprobante40\Conceptos\Concepto();
                $concepto->setClaveProdServ($rs['ClaveProdServ']);
                $concepto->setClaveUnidad($rs['ClaveUnidad']);
                $concepto->setDescripcion($Desc);
                $concepto->setImporte(number_format($rs['Importe'], 2, '.', ''));
                $concepto->setCantidad(number_format($rs['Cantidad'], 2, '.', ''));
                $concepto->setNoIdentificacion($rs['NoIdentificacion']);
                $concepto->setValorUnitario(number_format($rs['ValorUnitario'], 4, '.', ''));
                $concepto->setObjetoImp("02");

                $subTotal += $rs['Importe'];

                $traslados = new cfdi40\Comprobante40\Conceptos\Concepto\Impuestos\Traslados();

                $iva = new cfdi40\Comprobante40\Conceptos\Concepto\Impuestos\Traslados\Traslado();
                $iva->setBase(number_format($rs['base_iva'], 2, '.', ''));
                $iva->setImpuesto('002');
                $iva->setTasaOCuota($rs['factoriva']);
                $iva->setTipoFactor('Tasa');
                $iva->setImporte(number_format($rs['tax_iva'], 2, '.', ''));

                $traslados->addTraslado($iva);

                if ($rs['tax_ieps'] > 0) {

                    $ieps = new cfdi40\Comprobante40\Conceptos\Concepto\Impuestos\Traslados\Traslado();
                    $ieps->setBase(number_format($rs['base_ieps'], 2, '.', ''));
                    $ieps->setImpuesto('003');
                    $ieps->setTasaOCuota($rs['factorieps']);
                    $ieps->setTipoFactor('Cuota');
                    $ieps->setImporte(number_format($rs['tax_ieps'], 2, '.', ''));

                    $traslados->addTraslado($ieps);
                }

                $total += $rs['Importe'] + $rs['tax_iva'] + $rs['tax_ieps'];

                $impuestos = new cfdi40\Comprobante40\Conceptos\Concepto\Impuestos();
                $impuestos->setTraslados($traslados);
                $concepto->setImpuestos($impuestos);
                $conceptos->addConcepto($concepto);
            }//while

            error_log("CFDI33 TOTAL : " . $total);
            error_log("CFDI33 TOTAL : " . $this->getComprobante()->getTotal());
            if ($total !== $this->comprobante->getTotal()) {
                $difference = round($this->comprobante->getTotal() - $total, 2);
                error_log("CFDI33 There is a difference " . $difference);

                $subTotal += $difference;
                $arreglo = $conceptos->getConcepto();
                $importe = $arreglo[0]->getImporte() + $difference;
                $cantidad = round($importe / $arreglo[0]->getValorUnitario(), 4);
                $arreglo[0]->setImporte(number_format($importe, 2, '.', ''));
                $arreglo[0]->setCantidad($cantidad);
            }

            $this->comprobante->setSubTotal(number_format($subTotal, 2, '.', ''));
            $this->comprobante->setConceptos($conceptos);
        }
    }

//conceptos

    /**
     * Recupera el sumarizado de impuestos asociados a la factura.
     * Crea el nodo Impuestos y el nodo Traslados. En el caso de Omicrom, el nodo de Retenciones no existe.
     */
    private function impuestos() {

        $impuestos = new cfdi40\Comprobante40\Impuestos();
        $traslados = new cfdi40\Comprobante40\Impuestos\Traslados();
        $retens = new cfdi40\Comprobante40\Impuestos\Retenciones();

        $sql = "
            SELECT 
               fcd.factoriva,
               fcd.factorieps,
                SUM( ROUND( fcd.cantidad * fcd.preciou, 2 ) ) base_iva,
                SUM( IF( cli.desgloseIEPS = 'S', ROUND( fcd.cantidad, 2 ), 0.00 ) ) base_ieps ,
               SUM( ROUND( fcd.cantidad * fcd.preciou * fcd.factoriva, 2 ) ) tax_iva,
               SUM( IF( cli.desgloseIEPS = 'S', ROUND( fcd.cantidad * fcd.factorieps, 2 ), 0.00 ) ) tax_ieps,
               SUM( ROUND( fcd.cantidad * fcd.preciou * fcd.factorisr_retenido, 2 ) ) tax_isr_retenido,
               SUM( ROUND( fcd.cantidad * fcd.preciou * fcd.factoriva_retenido, 2 ) ) tax_iva_retenido,
               fcd.factoriva_retenido,SUM(fcd.cantidad) cnt,fcd.preciou
            FROM (
                   SELECT 
                       fcd.id folio,
                       CAST( fcd.iva AS DECIMAL( 10, 6 ) ) factoriva,
                       CAST( fcd.ieps AS DECIMAL( 10, 6 ) ) factorieps,
                       ROUND( (  fcd.preciob-fcd.ieps )/( 1+fcd.iva ), 4 ) preciou,
                       ROUND( ( fcd.importe/fcd.preciob ), 4 ) cantidad,
                       CAST( fcd.iva_retenido AS DECIMAL( 10, 6 ) ) factoriva_retenido,
                       fcd.preciob,
                       CAST( fcd.isr_retenido AS DECIMAL( 10, 6 ) ) factorisr_retenido
                   FROM fcd 
                   WHERE fcd.id = " . $this->folio . "
            ) fcd
            JOIN fc ON fcd.folio = fc.id 
            JOIN cli ON cli.id = fc.cliente
            GROUP BY fcd.factoriva, fcd.factorieps";

        $importe_iva = 0.00;
        $total_traslado = 0.00;
        $factor_iva = 0.000000;
        $importe = 0.00;
        $importe_Retenido = 0.00;
        $BaseIva = 0.00;
        if (($query = $this->mysqlConnection->query($sql))) {
            while (($rs = $query->fetch_assoc())) {

                $total_traslado += $rs['tax_iva'] + $rs['tax_ieps'];
                $importe_iva += $rs['tax_iva'];
                $factor_iva = $rs['factoriva'];
                $factor_ieps = $rs['factorieps'];

                if ($rs['tax_ieps'] > 0) {
                    $ieps = new cfdi40\Comprobante40\Impuestos\Traslados\Traslado();

                    $ieps->setImporte(number_format($rs['tax_ieps'], 2, '.', ''));
                    $ieps->setImpuesto('003');
                    $ieps->setTasaOCuota($rs['factorieps']);
                    $ieps->setTipoFactor('Cuota');
                    $ieps->setBase($rs["base_ieps"]);
                    $traslados->addTraslado($ieps);
                }

                $tt = $rs["tax_iva_retenido"] > 0 ? $rs["tax_iva_retenido"] : 0;
                $importe_Retenido += $rs["tax_isr_retenido"] + $tt;
                $BaseIva += $rs["base_iva"];
            }

            if ($importe_Retenido > 0) {
                $R_impuesto = new cfdi40\Comprobante40\Impuestos\Retenciones\Retencion();
                $R_impuesto->setImpuesto('001');
                $R_impuesto->setImporte(number_format($importe_Retenido, 2, '.', ''));
                $retens->addRetencion($R_impuesto);
//$importe_Retenido = $importe_Retenido + $rs["tax_isr_retenido"];
            }

            $iva = new cfdi40\Comprobante40\Impuestos\Traslados\Traslado();
            $iva->setImporte(number_format($importe_iva, 2, '.', ''));
            $iva->setImpuesto('002');
            $iva->setTasaOCuota($factor_iva);
            $iva->setTipoFactor('Tasa');
            $iva->setBase($BaseIva);
            $traslados->addTraslado($iva);

            $impuestos->setTraslados($traslados);
            if ($importe_Retenido > 0) {
                $impuestos->setTotalImpuestosRetenidos(number_format($importe_Retenido, 2, '.', ''));
                $impuestos->setRetenciones($retens);
                $TotalF = $this->comprobante->getTotal();
                $TotalF -= number_format($importe_Retenido, 2, '.', '');
                $this->comprobante->setTotal($TotalF);
            }
            $impuestos->setTotalImpuestosTrasladados(number_format($total_traslado, 2, '.', ''));

            $this->comprobante->setImpuestos($impuestos);
        }
    }

//impuestos

    /**
     * Recupera el valor del campo observaciones en fc.
     * Si existe, crea la addenda Observaciones, definida por Detisa
     */
    private function observaciones() {

        $complemento = new cfdi40\Comprobante40\Addenda();
        $sql = "
            SELECT fc.observaciones Observacion
            FROM fc 
            WHERE fc.id = " . $this->folio;

        $observacion = '';
        $i = 0;
        if (($query = $this->mysqlConnection->query($sql))) {
            while (($rs = $query->fetch_assoc())) {
                $observacion = $rs['Observacion'];
            }

            if (!empty($observacion)) {
                $Observaciones = new Adenda\detisa\Observaciones();
                $Observacion = new Adenda\detisa\Observaciones\Observacion($observacion);
                $Observaciones->addObservaciones($Observacion);
                $complemento->addAny($Observaciones);
                $this->comprobante->setAddenda($complemento);
            }
        }
    }

//observaciones

    /**
     * updateFC Actualiza el UUID del registro principal de la factura en la tabla fc
     * @param String $id id del registro en la tabla fc
     * @param String $uuid UUID del CFDI relacionado
     * @return boolean
     */
    public function updateFC($id, $uuid) {

        $sql = "UPDATE fc SET uuid = '" . $uuid . "', status = 'Cerrada' WHERE fc.id = " . $id;
        return $this->mysqlConnection->query($sql);
    }

//updateFC

    /**
     * updateRM Actualiza el UUID de cada registro en rm asociado a la factura con fc.id = $id
     * @param String $id id del registro en fc
     * @param String $uuid UUID de la factura relacionada
     * @return boolean
     */
    public function updateRM($id, $uuid) {

        $sql = "UPDATE rm SET uuid = '" . $uuid . "', comprobante = comprobante+50 "
                . "WHERE id IN (SELECT ticket FROM fcd JOIN inv ON inv.id = fcd.producto AND rubro = 'Combustible' WHERE fcd.id = " . $id . " AND ticket <> 0)";
        return $this->mysqlConnection->query($sql);
    }

//updateRM

    /**
     * 
     * updateVTA Actualiza el UUID de cada registro en vtaditicos asociado a la factura con fc.id = $id
     * @param String $uuid UUID de la factura relacionada
     * @return boolean
     */
    public function updateVTA($id, $uuid) {

        $sql = "UPDATE vtaditivos SET uuid = '" . $uuid . "' "
                . "WHERE id IN (SELECT ticket FROM fcd JOIN inv ON inv.id = fcd.producto AND rubro = 'Aceites' WHERE fcd.id = " . $id . " AND ticket <> 0)";
        return $this->mysqlConnection->query($sql);
    }

//updateVTA

    /**
     * insertFactura Crea el registro en facturas.
     * @param cfdi33\Comprobante $Comprobante Objeto Comprobante
     * @param String $clavePAC Clave del PAC usado para certificar el CFDI
     * @return boolean
     */
    public function insertFactura($Comprobante, $clavePAC) {

        $sql = "INSERT INTO facturas (id_fc_fk, cfdi_xml, pdf_format, fecha_emision, fecha_timbrado, clave_pac, emisor, receptor, uuid)"
                . " VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $pdf = \PDFTransformer::getPDF($Comprobante, 'S');
        $DOM = $Comprobante->asXML();
        $xml = $DOM->saveXML();
        $stmt = $this->mysqlConnection->prepare($sql);

        if ($stmt) {

            $stmt->bind_param("sssssssss",
                    $Comprobante->getFolio(),
                    $xml,
                    $pdf,
                    $Comprobante->getFecha(),
                    $Comprobante->getTimbreFiscalDigital()->getFechaTimbrado(),
                    $clavePAC,
                    $Comprobante->getEmisor()->getRfc(),
                    $Comprobante->getReceptor()->getRfc(),
                    $Comprobante->getTimbreFiscalDigital()->getUUID());

            if ($stmt->execute()) {
                return TRUE;
            }
            error_log($stmt->error);
        } else {
            error_log("Error insertando factura " . $this->mysqlConnection->error);
        }

        return FALSE;
    }

//insertFactura
}

//FacturaDAO
