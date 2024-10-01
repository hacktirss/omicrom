<?php

/*
 * ReciboPagoDAO Objeto DAO.
 * Recupera la información referente al pago con pagos.id = $folio
 * Crea un objeto de tipo Comprobante y los nodos requeridos.
 * La información vaciada en Comprobante se encuentra contenida en las tablas cia, cli, pagos, pagose.
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
require_once ('com/softcoatl/cfdi/v40/schema/Comprobante40.php');
require_once ('com/softcoatl/cfdi/v40/schema/Comprobante40/Addenda.php');
require_once ('com/softcoatl/cfdi/complemento/pagos/Pagos20.php');

//require_once ('cfdi33/Comprobante.php');
//require_once ('cfdi33/complemento/Pagos.php');
//require_once ('cfdi33/addenda/Observaciones.php');

use \com\softcoatl\cfdi as cfdi;
use \com\softcoatl\cfdi\v40\schema as cfdi40;
use com\softcoatl\cfdi\complemento as complemento;
use com\detisa\cfdi\factory\ComprobanteFactoryIface;
use com\detisa\cfdi\factory\ComprobanteFactory;

//use \com\softcoatl\cfdi\v33\schema as cfdi33;

class ReciboPagoDAO {

    private $folio;
    /* @var $comprobante cfdi40\Comprobante */
    private $comprobante;
    /* @var $mysqlConnection \mysqli */
    private $mysqlConnection;
    private $TotalRetencionesIVA = 0;
    private $TotalRetencionesISR = 0;
    private $TotalTrasladosBaseIVA16 = 0;
    private $TotalTrasladosImpuestoIVA16 = 0;
    private $Porcentaje = 0;

    function __construct($folio) {

        error_log("Cargando CFDI con folio " . $folio);
        $this->TotalRetencionesIVA = 0;
        $this->folio = $folio;
        $this->cfdiPago = new cfdi\complemento\pagos\Pagos20();
        $this->comprobante = new cfdi40\Comprobante40();
        $this->mysqlConnection = getConnection();

        $this->comprobante();
        $this->emisor();
        $this->receptor();
        $this->cfdiRelacionados();
        $this->conceptos();
        $this->pagos();
//$this->observaciones();
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

    function getTotalRetencionesIVA() {
        return $this->TotalRetencionesIVA;
    }

    function setTotalRetencionesIVA($TotalRetencionesIVA) {
        $this->TotalRetencionesIVA = $TotalRetencionesIVA;
    }

    function getTotalRetencionesISR() {
        return $this->TotalRetencionesISR;
    }

    function setTotalRetencionesISR($TotalRetencionesISR) {
        $this->TotalRetencionesISR = $TotalRetencionesISR;
    }

    function getTotalTrasladosBaseIVA16() {
        return $this->TotalTrasladosBaseIVA16;
    }

    function setTotalTrasladosBaseIVA16($TotalTrasladosBaseIVA16) {
        $this->TotalTrasladosBaseIVA16 = $TotalTrasladosBaseIVA16;
    }

    function getTotalTrasladosImpuestosIVA16() {
        return $this->TotalTrasladosImpuestoIVA16;
    }

    function setTotalTrasladosImpuestosIVA16($TotalTrasladosImpuestosIVA16) {
        $this->TotalTrasladosImpuestoIVA16 = $TotalTrasladosImpuestosIVA16;
    }

    function getPorcentaje() {
        return $this->Porcentaje;
    }

    function setPorcentaje($Porcentaje) {
        $this->Porcentaje = $Porcentaje;
    }

    /**
     * Recupera la información relativa al pago.
     * Crea el objeto Comprobante
     */
    private function comprobante() {

        /* @var $emisor cfdi40\Comprobante40 */
        $this->comprobante = new cfdi40\Comprobante40();
        $sql = "SELECT 
                    pagos.id Folio, 
                    DATE_FORMAT(now(), '%Y-%m-%dT%H:%i:%s') Fecha, 
                    TRIM( cia.codigo ) LugarExpedicion
              FROM pagos JOIN cia ON TRUE
              WHERE pagos.id = " . $this->folio;
        $Sql2 = "SELECT valor FROM variables_corporativo where llave like '%serie_credito%';";
        if (($query2 = $this->mysqlConnection->query($Sql2)) && ($rs2 = $query2->fetch_assoc())) {
            if (($query = $this->mysqlConnection->query($sql)) && ($rs = $query->fetch_assoc())) {
                $SERIE = $rs2["valor"] <> "" ? "RP-" . $rs2["valor"] : 'RP-MDEB';
                $this->comprobante->setFolio($rs['Folio']);
                $this->comprobante->setFecha($rs['Fecha']);
                $this->comprobante->setSerie($SERIE);
                $this->comprobante->setTipoDeComprobante("P");
                $this->comprobante->setVersion("4.0");
                $this->comprobante->setMoneda("XXX");
                $this->comprobante->setSubTotal('0');
                $this->comprobante->setTotal('0');
                $this->comprobante->setLugarExpedicion($rs['LugarExpedicion']);
                $this->comprobante->setExportacion("01");
            }//if
        }
    }

//comprobante

    /**
     * Recupera los datos de la estación de servicio.
     * Crea el nodo Emisor.
     */
    private function emisor() {

        /* @var $emisor cfdi40\Comprobante\Emisor */
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

        /* @var $emisor cfdi40\Comprobante40\Receptor */
        $receptor = new cfdi40\Comprobante40\Receptor();

        $receptor = new cfdi40\Comprobante40\Receptor();
        $Client = \ClientesDAO::getClientData($this->folio, "pagos");
        $receptor->setNombre($Client->getNombre());
        $receptor->setRfc($Client->getRfc());
        $receptor->setUsoCFDI($Client->getObservaciones());
        $receptor->setDomicilioFiscalReceptor($Client->getCodigo());
        $receptor->setRegimenFiscalReceptor($Client->getRegimenFiscal());
        $this->comprobante->setReceptor($receptor);
    }

//receptor

    /**
     * Recupera los CFDI relacionados.
     * En el caso del Recibo Electrónico de Pago, se agregan todas las facturas que ampara el pago.
     * Crea el nodo CfdiRelacionados.
     */
    private function cfdiRelacionados() {

        $cfdiRelacionados = new cfdi40\Comprobante40\CfdiRelacionados();

        $sql = "
            SELECT IFNULL( PES.uuid,  '' ) ruuid, IFNULL( PE.tiporelacion, '04' ) tiporelacion
            FROM pagos PE
            INNER JOIN pagos PES ON PE.relacioncfdi = PES.id
            WHERE PE.id = " . $this->folio;

        if (($query = $this->mysqlConnection->query($sql))) {
            while (($rs = $query->fetch_assoc())) {
                if (!empty($rs['ruuid'])) {
                    $cfdiRelacionados->setTipoRelacion('04');

                    $cfdiRelacionado = new cfdi40\Comprobante40\CfdiRelacionados\CfdiRelacionado();
                    $cfdiRelacionado->setUUID($rs['ruuid']);
                    $cfdiRelacionados->addCfdiRelacionado($cfdiRelacionado);
                    $cfdiRelacionados->setTipoRelacion($rs['tiporelacion']);
                    //$cfdiRelacionados->setTipoRelacion(04);
                }
                $this->comprobante->addCfdiRelacionados($cfdiRelacionados);
            }
        }
    }

//cfdiRelacionadosFactura

    /**
     * Crea el nodo Conceptos. 
     * De acuerdo a lo definido por el SAT, este tipo de comprobantes consta de un solo Concepto con valores fijos.
     */
    private function conceptos() {

        $conceptos = new cfdi40\Comprobante40\Conceptos();
        $concepto = new cfdi40\Comprobante40\Conceptos\Concepto();
        $concepto->setClaveProdServ('84111506');
        $concepto->setClaveUnidad('ACT');
        $concepto->setDescripcion('Pago');
        $concepto->setImporte('0');
        $concepto->setCantidad('1');
        $concepto->setValorUnitario('0');
        $concepto->setObjetoImp("01"); //noce si es obj de impuestos
        $conceptos->addConcepto($concepto);
        $this->comprobante->setConceptos($conceptos);
    }

//retrieveConceptosFactura

    /**
     * Crea el nodo DoctosRelacionados, requerido por el Complemento de Recepción de Pagos.
     * En este nodo se detallan las facturas que ampara el pago y los importes pagados de cada CFDI.
     * @return array
     */
    private function doctosRelacionados() {

        $doctosRelacionados = array();
        $sql = "
            SELECT 
                fc.folio Folio, 
                fc.uuid IdDocumento, 
                fc.cliente idCli,
                pagose.importe ImpPagado, 
                fc.total-IFNULL( cxc.importe, 0 )-IFNULL( nc.importe, 0 ) ImpSaldoAnt, 
                fc.total-IFNULL( cxc.importe, 0 )-IFNULL( nc.importe, 0 )-pagose.importe ImpSaldoInsoluto, 
                IFNULL( cxc.parcialidades, 0 )+1 NumParcialidad,
                fc.uuid,fc.total, round(pagose.importe/fc.total ,6) porcentaje 
            FROM pagose 
            JOIN fc ON fc.id = pagose.factura
            LEFT JOIN (
                SELECT factura, COUNT( * ) parcialidades, SUM( importe ) importe FROM (
                SELECT * FROM cxc WHERE tm = 'H' AND concepto LIKE '%factura%'
                UNION ALL
                SELECT * FROM cxch WHERE tm = 'H' AND concepto LIKE '%factura%') cxch
                WHERE recibo < " . $this->folio . " GROUP BY factura ) cxc ON fc.id = cxc.factura 
            LEFT JOIN (
                    SELECT factura, SUM( total ) importe FROM nc WHERE status = 1 GROUP BY factura ) nc ON pagose.factura = nc.factura
            WHERE pagose.id = " . $this->folio;
        error_log("DOCTO RELACIONADO " . $sql);
        $sumaBases = 0;
        if (($query = $this->mysqlConnection->query($sql))) {

            while (($rs = $query->fetch_assoc())) {

                $doctoRelacionado = new complemento\pagos\Pagos20\Pago\DoctoRelacionado();
                $doctoRelacionado->setFolio($rs['Folio']);
                $doctoRelacionado->setIdDocumento($rs['IdDocumento']);
                $doctoRelacionado->setImpPagado(number_format($rs['ImpPagado'], 2, '.', ''));
                $doctoRelacionado->setImpSaldoAnt(number_format($rs['ImpSaldoAnt'], 2, '.', ''));
                $doctoRelacionado->setImpSaldoInsoluto(number_format($rs['ImpSaldoInsoluto'], 2, '.', ''));
                $doctoRelacionado->setMonedaDR("MXN");
                $doctoRelacionado->setEquivalenciaDR(1);
                //$doctoRelacionado->setMetodoDePagoDR("PPD");
                $doctoRelacionado->setNumParcialidad($rs['NumParcialidad']);
                $doctoRelacionado->setObjetoImpDR("02");

                $doctoRelacionadoImpDR = new complemento\pagos\Pagos20\Pago\DoctoRelacionado\ImpuestosDR();
                $doctoRelacionadoRetsDR = new complemento\pagos\Pagos20\Pago\DoctoRelacionado\ImpuestosDR\RetencionesDR();
                $doctoRelacionadoTrasDR = new complemento\pagos\Pagos20\Pago\DoctoRelacionado\ImpuestosDR\TrasladosDR();
                $Sql = "SELECT 
                fcd.factoriva,
                fcd.factorieps,
                SUM( ROUND( fcd.cantidad * fcd.preciou, 2 ) ) base_iva,
                SUM( IF( cli.desgloseIEPS = 'S', ROUND( fcd.cantidad, 2 ), 0.00 ) ) base_ieps ,
                SUM( ROUND( fcd.cantidad * fcd.preciou , 2 ) * fcd.factoriva ) tax_iva,
                SUM( IF( cli.desgloseIEPS = 'S', ROUND( fcd.cantidad * fcd.factorieps, 4 ), 0.00 ) ) tax_ieps,
                SUM( ROUND( fcd.cantidad * fcd.preciou * fcd.factorisr_retenido, 2 ) ) tax_isr_retenido,
                SUM( ROUND( fcd.cantidad * fcd.preciou * fcd.factoriva_retenido, 2 ) ) tax_iva_retenido,
                fcd.factoriva_retenido,SUM(fcd.cantidad) cnt,fcd.preciou,fcd.factorisr_retenido,fcd.factoriva_retenido 
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
			   , fc.cliente
                       FROM fcd INNER JOIN fc ON fcd.id = fc.id 
			AND fc.uuid='" . $rs["uuid"] . "'
                ) fcd
                JOIN cli ON cli.id = fcd.cliente 
                GROUP BY fcd.factoriva, fcd.factorieps";
                //error_log($Sql);
                $porcentajeT = $rs['porcentaje'];
                $TaxIva = 0;
                $BaseDR = 0;
                if (($querys = $this->mysqlConnection->query($Sql))) {
                    while ($rss = $querys->fetch_assoc()) {
                        if ($rss["tax_isr_retenido"] > 0) {
                            $doctoRelacionadoRetDRisr = new complemento\pagos\Pagos20\Pago\DoctoRelacionado\ImpuestosDR\RetencionesDR\RetencionDR();
                            $RetDrIsr = array();
                            $doctoRelacionadoRetDRisr->setBaseDR(number_format($rss["base_iva"] * $porcentajeT, 2, '.', ''));
                            $doctoRelacionadoRetDRisr->setImporteDR($rss["tax_isr_retenido"] * $porcentajeT);
                            $doctoRelacionadoRetDRisr->setImpuestoDR('001');
                            $doctoRelacionadoRetDRisr->setTasaOCuotaDR($rss["factorisr_retenido"]);
                            $doctoRelacionadoRetDRisr->setTipoFactorDR('Tasa');
                            array_push($RetDrIsr, $doctoRelacionadoRetDRisr);
                            $doctoRelacionadoRetsDR->setRetencionDR($RetDrIsr);
                            $TTretISR = $this->getTotalRetencionesISR() + ($rss["tax_isr_retenido"] * $porcentajeT);
                            $this->setTotalRetencionesISR($TTretISR);
                        }
                        if ($rss["tax_iva_retenido"] > 0) {
                            $doctoRelacionadoRetDRiva = new complemento\pagos\Pagos20\Pago\DoctoRelacionado\ImpuestosDR\RetencionesDR\RetencionDR();
                            $RetDRiva = array();
                            $doctoRelacionadoRetDRiva->setBaseDR(number_format($rss["base_iva"], 2, '.', ''));
                            $doctoRelacionadoRetDRiva->setImpuestoDR("002");
                            $doctoRelacionadoRetDRiva->setTasaOCuotaDR($rss["factoriva_retenido"]);
                            $doctoRelacionadoRetDRiva->setTipoFactorDR("Tasa");
                            $doctoRelacionadoRetDRiva->setImporteDR($rss["tax_iva_retenido"]);
                            array_push($RetDRiva, $doctoRelacionadoRetDRiva);
                            $doctoRelacionadoRetsDR->setRetencionDR($RetDRiva);
                            $TTretIva = $this->getTotalRetencionesIVA() + ($rss["tax_iva_retenido"] );
                            $this->setTotalRetencionesIVA($TTretIva);
                        }

                        $sqlDesgloseIeps = "SELECT desgloseIEPS FROM cli WHERE id = " . $rs["idCli"];
                        if ($queryDes = $this->mysqlConnection->query($sqlDesgloseIeps)) {
                            $rs1 = $queryDes->fetch_assoc();
                            if (!($rs1["descloseIEPS"] === "N")) {
                                if ($rss['tax_ieps'] > 0) {
                                    error_log("ENTRAMOS CUANTAS VECES ? ???");
                                    $doctoRelacionadoTraDRieps = new complemento\pagos\Pagos20\Pago\DoctoRelacionado\ImpuestosDR\TrasladosDR\TrasladoDR();
                                    $TraDrieps = array();
                                    $doctoRelacionadoTraDRieps->setBaseDR(number_format($rss['base_ieps'] * $porcentajeT, 2, '.', ''));
                                    $sumaBases = $sumaBases + number_format($rss['base_ieps'] * $porcentajeT, 2, '.', '');
                                    $doctoRelacionadoTraDRieps->setImpuestoDR('003');
                                    $doctoRelacionadoTraDRieps->setTasaOCuotaDR($rss['factorieps']);
                                    $doctoRelacionadoTraDRieps->setTipoFactorDR('Cuota');
                                    $doctoRelacionadoTraDRieps->setImporteDR(number_format($doctoRelacionadoTraDRieps->getBaseDR() * $doctoRelacionadoTraDRieps->getTasaOCuotaDR(), 4, '.', ''));
//                                    array_push($TraDrieps, $doctoRelacionadoTraDRieps);
//                                    $doctoRelacionadoTrasDR->setTrasladoDR($TraDrieps);
                                    $doctoRelacionadoTrasDR->addTrasladoDR($doctoRelacionadoTraDRieps);
                                }
                            }
                        }
                        if ($rss["tax_iva"] > 0) {
                            $TaxIva += $rss["tax_iva"];
                            $BaseDR += number_format($rss['base_iva'] * $porcentajeT, 2, '.', '');
                        }
                    }
                }

                if ($TaxIva > 0) {
                    $doctoRelacionadoTraDRiva = new complemento\pagos\Pagos20\Pago\DoctoRelacionado\ImpuestosDR\TrasladosDR\TrasladoDR();
                    $TraDRiva = array();
                    $doctoRelacionadoTraDRiva->setBaseDR($BaseDR);
                    $doctoRelacionadoTraDRiva->setImpuestoDR('002');
                    $doctoRelacionadoTraDRiva->setTasaOCuotaDR(number_format(0.160000, 6));
                    $doctoRelacionadoTraDRiva->setTipoFactorDR('Tasa');
                    $doctoRelacionadoTraDRiva->setImporteDR(number_format($TaxIva * $porcentajeT, 2, '.', ''));
                    $doctoRelacionadoTrasDR->addTrasladoDR($doctoRelacionadoTraDRiva);
                    // error_log("BASE|".$BaseDR."|IVA|0.16"."|IMPORTE|".number_format($TaxIva * $porcentajeT, 2, '.', ''));


                    $this->setTotalTrasladosImpuestosIVA16($this->getTotalTrasladosImpuestosIVA16() + $doctoRelacionadoTraDRiva->getImporteDR());
                    $this->setTotalTrasladosBaseIVA16($this->getTotalTrasladosBaseIVA16() + number_format($BaseDR, 2, '.', ''));
                }
                $doctoRelacionadoImpDR->setRetencionesDR($doctoRelacionadoRetsDR);
                $doctoRelacionadoImpDR->setTrasladosDR($doctoRelacionadoTrasDR);

                $doctoRelacionado->setImpuestosDR($doctoRelacionadoImpDR);
                array_push($doctosRelacionados, $doctoRelacionado);
            }
            //error_log("Suma Bases IEPS: ".$sumaBases);
            return $doctosRelacionados;
        }
    }

//doctosRelacionados

    /**
     * Crea el nodo ImpuestosP, requerido por el Complemento de Recepción de Pagos.
     * En este nodo se detallan las facturas que ampara el pago y los importes pagados de cada CFDI.
     * @return array
     */
    private function ImpuestosP() {

        $PagoImpuestosP = new complemento\pagos\Pagos20\Pago\ImpuestosP();
        $PagoImportesoPRsP = new complemento\pagos\Pagos20\Pago\ImpuestosP\RetencionesP();
        $PagoImportesoPTsP = new complemento\pagos\Pagos20\Pago\ImpuestosP\TrasladosP();

        $ImpuestosP = array();
        $sqlIeps = "
            SELECT 
               fcd.factorieps,
               ROUND(SUM( IF( cli.desgloseIEPS = 'S', ROUND( fcd.cantidad, 2 )*porcentaje, 0.00 ) ),2) base_ieps ,
               ROUND(SUM( IF( cli.desgloseIEPS = 'S', ROUND( fcd.cantidad * fcd.factorieps, 4 )*porcentaje, 0.00 ) ),4) tax_ieps,
               SUM(fcd.cantidad) cnt,fcd.preciou,cli.id idCli
            FROM (
                SELECT 
                    fcd.id folio,
                    CAST( fcd.ieps AS DECIMAL( 10, 6 ) ) factorieps,
                    ROUND( (  fcd.preciob-fcd.ieps )/( 1+fcd.iva ), 3) preciou,
                    ROUND( ( fcd.importe/fcd.preciob ), 4) cantidad,
                    fcd.preciob,
                    round(pagose.importe/fc.total ,6) porcentaje
                FROM fcd INNER JOIN fc ON fcd.id = fc.id
                INNER JOIN pagose ON fc.id = pagose.factura	
                WHERE 
                pagose.id = " . $this->folio . "
                and fcd.ieps > 0
            ) fcd
            JOIN fc ON fcd.folio = fc.id 
            JOIN cli ON cli.id = fc.cliente
            GROUP BY fcd.factorieps;";
//error_log("QUERY IEPS");
//error_log($sqlIeps);
        $sqlIva = "SELECT 
                fcd.factoriva,
                fcd.factorieps,cli.id idCli,
                SUM( ROUND( fcd.cantidad * fcd.preciou, 2 ) ) base_iva,
                SUM( ROUND( fcd.cantidad * fcd.preciou, 2 ) * fcd.factoriva ) tax_iva,
                SUM( ROUND( fcd.cantidad * fcd.preciou / (1+fcd.factoriva), 2 ) ) base_sin_iva,
                SUM( ROUND( fcd.cantidad * fcd.preciou * fcd.factorisr_retenido, 2 ) ) tax_isr_retenido,
                SUM( ROUND( fcd.cantidad * fcd.preciou * fcd.factoriva_retenido, 2 ) ) tax_iva_retenido,
                fcd.factoriva_retenido,SUM(fcd.cantidad) cnt,fcd.preciou
             FROM (
                    SELECT 
                        fcd.id folio,
                        CAST( fcd.iva AS DECIMAL( 10, 6 ) ) factoriva,
                        CAST( fcd.ieps AS DECIMAL( 10, 6 ) ) factorieps,
                        ROUND( (  fcd.preciob-fcd.ieps )/( 1+fcd.iva ), 3) preciou,
                        SUM(ROUND( ( fcd.importe/fcd.preciob ), 3)) cantidad,
                        CAST( fcd.iva_retenido AS DECIMAL( 10, 6 ) ) factoriva_retenido,
                        fcd.preciob,
                        CAST( fcd.isr_retenido AS DECIMAL( 10, 6 ) ) factorisr_retenido
                    FROM fcd LEFT JOIN fc ON fcd.id = fc.id
                    WHERE fc.uuid in (
                        SELECT fc.uuid FROM pagos LEFT JOIN pagose ON pagos.id = pagose.id
                        LEFT JOIN fc ON fc.id = pagose.factura WHERE pagos.id = " . $this->folio . "
                    )
             ) fcd
             JOIN fc ON fcd.folio = fc.id 
             JOIN cli ON cli.id = fc.cliente
             GROUP BY fcd.factoriva;";

///////////////////////////////
        $totalIvaRetenido = 0.0;
        $sqlIvaRetenido = "SELECT 
		  iva_retenido
		, round(SUM(round(fcd.importe/1.16,2)),2) base
		, round(SUM(round(fcd.importe/1.16,2) *iva_retenido),2) monto_retenido
		FROM fcd LEFT JOIN pagose ON fcd.id = pagose.factura
		WHERE 
		pagose.id = " . $this->folio . "
		AND iva_retenido > 0
		GROUP by iva_retenido;";

        if ($queryIvaRetenido = $this->mysqlConnection->query($sqlIvaRetenido)) {
            while (($rs = $queryIvaRetenido->fetch_assoc())) {
                $totalIvaRetenido = $totalIvaRetenido + $rs['monto_retenido'];
                //$FactorIva = $rs["factoriva"];
                //$BaseTrasladoIva += $rs['base_iva'];
                //$TaxIvaRetenido += $rs["tax_iva_retenido"];
                //$TaxIsrRetenido += $rs["tax_isr_retenido"];
                //$TaxIva = $rs["tax_iva"];
            }
        }
        if ($this->getTotalRetencionesIVA() > 0) {
            $RetencionPIVA = new complemento\pagos\Pagos20\Pago\ImpuestosP\RetencionesP\RetencionP();
            $RetencionPIVA->setImporteP(number_format($totalIvaRetenido, 2, '.', ''));
            $RetencionPIVA->setImpuestoP('002');
            $PagoImportesoPRsP->addRetencionP($RetencionPIVA);
        }




//////////////////////
        $IdCli = 0;
        if ($queryIeps = $this->mysqlConnection->query($sqlIeps)) {
            $i = 0;
            while ($rs = $queryIeps->fetch_assoc()) {
                $IdCli = $rs["idCli"];
                $BaseTrasladoIeps[$i] = $rs['base_ieps'];
                error_log("BASE|" . $rs['base_ieps'] . "|FACTOR|" . $rs["factorieps"]);
                $Tax_Ieps[$i] = $rs["tax_ieps"];
                $FactorIepsIeps[$i] = $rs["factorieps"];
                $i++;
            }
        }



        if ($queryIva = $this->mysqlConnection->query($sqlIva)) {
            $BaseTrasladoIva = 0.00;
            $TaxIvaRetenido = 0.00;
            $TaxIsrRetenido = 0.00;
            while (($rs = $queryIva->fetch_assoc())) {
                $FactorIva = $rs["factoriva"];
                $BaseTrasladoIva += $rs['base_iva'];
                $TaxIvaRetenido += $rs["tax_iva_retenido"];
                $TaxIsrRetenido += $rs["tax_isr_retenido"];
                $TaxIva = $rs["tax_iva"];
            }
        }

        if ($this->getTotalRetencionesISR() > 0) {
            if ($this->getTotalRetencionesISR() > 0) {
                $PagoImportesoPRPisr = new complemento\pagos\Pagos20\Pago\ImpuestosP\RetencionesP\RetencionP();
                $PagoImportesoPRPisr->setImporteP(number_format($this->getTotalRetencionesISR(), 2), '.', '');
                $PagoImportesoPRPisr->setImpuestoP('001');
                $PagoImportesoPRsP->addRetencionP($PagoImportesoPRPisr);
            }
        }
        error_log("Agregamos las Retenciones....");
        $PagoImpuestosP->setRetencionesP($PagoImportesoPRsP);

        if ($BaseTrasladoIva > 0) {
            $PagoImportesoPTPiva = new complemento\pagos\Pagos20\Pago\ImpuestosP\TrasladosP\TrasladoP();
            $PagoImportesoPTPiva->setBaseP($this->getTotalTrasladosBaseIVA16());
            $PagoImportesoPTPiva->setImpuestoP('002');
            $PagoImportesoPTPiva->setImporteP(number_format($this->getTotalTrasladosImpuestosIVA16(), 2, '.', ''));
            $PagoImportesoPTPiva->setTipoFactorP('Tasa');
            $PagoImportesoPTPiva->setTasaOCuotaP(number_format($FactorIva, 6));
            $PagoImportesoPTsP->addTrasladoP($PagoImportesoPTPiva);
        }
        $sqlDesgloseIeps = "SELECT desgloseIEPS FROM cli WHERE id = " . $IdCli;
        if ($queryDes = $this->mysqlConnection->query($sqlDesgloseIeps)) {
            $rs1 = $queryDes->fetch_assoc();
            if (!($rs1["descloseIEPS"] === "N")) {
                for ($e = 0; $e < $i; $e++) {
                    if ($Tax_Ieps[$e] > 0) {
                        if ($BaseTrasladoIeps > 0) {
                            $PagoImportesoPTPieps = new complemento\pagos\Pagos20\Pago\ImpuestosP\TrasladosP\TrasladoP();
                            $PagoImportesoPTPieps->setBaseP(number_format($BaseTrasladoIeps[$e], 2, '.', ''));
                            $PagoImportesoPTPieps->setImpuestoP('003');
                            $PagoImportesoPTPieps->setImporteP(number_format($BaseTrasladoIeps[$e] * $FactorIepsIeps[$e], 2, '.', ''));
                            $PagoImportesoPTPieps->setTipoFactorP('Cuota');
                            $PagoImportesoPTPieps->setTasaOCuotaP($FactorIepsIeps[$e]);
                            $PagoImportesoPTsP->addTrasladoP($PagoImportesoPTPieps);
                            error_log("BASE|" . $BaseTrasladoIeps[$e] . "|MONTO|" . $Tax_Ieps[$e] . "|IMPORTE|" . number_format($Tax_Ieps[$e], 2, '.', ''));
                        }
                    }
                }
            }
        }
        if ($PagoImportesoPTsP !== "") {
            $PagoImpuestosP->setTrasladosP($PagoImportesoPTsP);
        }
        return $PagoImpuestosP;
    }

    /**
     * Recupera la información referente al pago.
     * Crea el nodo Pagos y el nodo Pago asociado al presente pago.
     * Por definición de Detisa y diseño del sistema Omicrom, sólo es posible timbrar un pago por cada Recibo Electrónico de Pagos.
     */
    private function pagos() {

        $pagos = new complemento\pagos\Pagos20();
        $totales = new complemento\pagos\Pagos20\Totales();
        $complemento = new cfdi40\Comprobante40\Complemento();

        $pagos->setVersion("2.0");
        $sumPagos = 0;

        $sql = "
            SELECT
                pagos.formapago FormaDePagoP,
                CONCAT( DATE_FORMAT( pagos.fecha_deposito, '%Y-%m-%d' ), 'T', CASE WHEN TIME( pagos.fecha_deposito ) = 0 THEN '12:00:00' ELSE DATE_FORMAT( pagos.fecha_deposito, '%H:%i:%s' ) END ) FechaPago, 
                pagos.importe Monto,
                pagos.numoperacion NumOperacion
            FROM pagos
            WHERE pagos.id = " . $this->folio;

        if (($query = $this->mysqlConnection->query($sql)) && ($rs = $query->fetch_assoc())) {
            $pago = new complemento\pagos\Pagos20\Pago();
            $Pago = array();
            $sumPagos += number_format($rs['Monto'], 2, '.', '');
            $pago->setMonto(number_format($rs['Monto'], 2, '.', ''));
            $pago->setMonedaP("MXN");
            $pago->setTipoCambioP(1);
            $pago->setFormaDePagoP($rs['FormaDePagoP']);
            $pago->setFechaPago($rs['FechaPago']);
            $pago->setNumOperacion($rs['NumOperacion']);
            $pago->setDoctoRelacionado($this->doctosRelacionados());
            $pago->setImpuestosP($this->ImpuestosP());

// Registrar Banco Beneficiario
// Registrar Banco Emisor
            array_push($Pago, $pago);
            $pagos->setPago($Pago);
            $sumPagos == 0 ? "" : $totales->setMontoTotalPagos(number_format($sumPagos, 2, '.', ''));
            $this->TotalRetencionesIVA == 0 ? "" : $totales->setTotalRetencionesIVA(number_format($this->TotalRetencionesIVA, 2, '.', ''));

            $totales->setTotalTrasladosBaseIVA16(number_format($this->getTotalTrasladosBaseIVA16(), 2, '.', ''));
            $totales->setTotalTrasladosImpuestoIVA16(number_format($this->getTotalTrasladosImpuestosIVA16(), 2, '.', ''));
            if ($this->getTotalRetencionesISR() > 0) {
                $totales->setTotalRetencionesISR(number_format($this->getTotalRetencionesISR(), 2, '.', ''));
            }
            $pagos->setTotales($totales);

            $complemento->addAny($pagos);
            $this->comprobante->setComplemento($complemento);
        }
    }

//pagos

    /**
     * Recupera el valor del campo concepto en pagos.
     * Si existe, crea la addenda Observaciones, definida por Detisa
     */
    private function observaciones() {

//        $observaciones = new cfdi33\Comprobante\addenda\Observaciones();
//        $sql = "
//            SELECT pagos.concepto Observacion
//            FROM pagos
//            WHERE pagos.id = " . $this->folio
//        ;
//
//        if (($query = $this->mysqlConnection->query($sql)) && ($rs = $query->fetch_assoc())) {
//            $observaciones->addObservaciones(new cfdi33\Comprobante\addenda\Observaciones\Observacion($observacion = $rs['Observacion']));
//            $this->comprobante->addAddenda($observaciones);
//        }
    }

//observaciones
}

//ReciboPagoDAO
