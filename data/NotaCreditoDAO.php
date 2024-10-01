<?php

/*
 * NotaCreditoDAO Objeto DAO.
 * Recupera la información referente a la nota de crédito con nc.id = $folio
 * Crea un objeto de tipo Comprobante y los nodos requeridos.
 * La información vaciada en Comprobante se encuentra contenida en las tablas cia, cli, nc, ncd.
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
require_once ('com/softcoatl/cfdi/complemento/ine/INE.php');

use com\softcoatl\cfdi\v40\schema as cfdi40;
use com\softcoatl\cfdi\complemento as complemento;
use com\softcoatl\cfdi\v40\addenda as addenda;

class NotaCreditoDAO {

    private $folio;
    /* @var $comprobante cfdi40\Comprobante40 */
    private $comprobante;
    /* @var $mysqlConnection \mysqli */
    private $mysqlConnection;
    private $baseGlobal;
    private $baseImpo;

    function __construct($folio) {

        error_log("Cargando CFDI con folio " . $folio);

        $this->folio = $folio;
        $this->comprobante = new cfdi40\Comprobante40();
        $this->mysqlConnection = getConnection();

        $this->comprobante();
        $this->emisor();
        $this->receptor();
        $this->cfdiRelacionados();
        $this->conceptos();
        $this->impuestos();
        //$this->observaciones();
    }

    function getMysqlConnection() {
        return $this->mysqlConnection;
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

    function getBaseGlobal() {
        return $this->baseGlobal;
    }

    function setBaseGlobal($baseGlobal) {
        $this->baseGlobal = $baseGlobal;
    }

    function getBaseImpo() {
        return $this->baseImpo;
    }

    function setBaseImpo($baseImpo) {
        $this->baseImpo = $baseImpo;
    }

    /**
     * Recupera la información relativa a la nota de crédito.
     * Crea el objeto Comprobante
     */
    private function comprobante() {

        /* @var $emisor cfdi40\Comprobante40 */
        $this->comprobante = new cfdi40\Comprobante40();
        $sql = "SELECT 
                    nc.id Folio, 
                    DATE_FORMAT(nc.fecha, '%Y-%m-%dT%H:%i:%s') Fecha, 
                    nc.formadepago FormaPago, 
                    nc.metododepago MetodoPago, 
                    nc.total Total,
                    TRIM( cia.codigo ) LugarExpedicion
              FROM nc JOIN cia ON TRUE
              WHERE nc.id = " . $this->folio;

        $Sql2 = "SELECT valor FROM omicrom.variables_corporativo where llave like '%series_notas_credito%';";
        if (($query2 = $this->mysqlConnection->query($Sql2)) && ($rs2 = $query2->fetch_assoc())) {
            $SERIE = $rs2["valor"];
            $Upd = "UPDATE nc SET serie = '$SERIE' WHERE id=" . $this->folio;
            $this->mysqlConnection->query($Upd);
            if (($query = $this->mysqlConnection->query($sql)) && ($rs = $query->fetch_assoc())) {
                $this->comprobante->setSerie($SERIE);
                $this->comprobante->setFolio($rs['Folio']);
                $this->comprobante->setFecha($rs['Fecha']);
                $this->comprobante->setTipoDeComprobante("E");
                $this->comprobante->setVersion("4.0");
                $this->comprobante->setFormaPago($rs['FormaPago']);
                $this->comprobante->setMetodoPago($rs['MetodoPago']);
                $this->comprobante->setMoneda("MXN");
                $this->comprobante->setTipoCambio(1);
                $this->comprobante->setTotal(number_format($rs['Total'], 2, '.', ''));
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
        $sql = "SELECT usocfdi FROM nc WHERE id = " . $this->folio;

        if (($query = $this->mysqlConnection->query($sql)) && ($rs = $query->fetch_assoc())) {
            /* @var $emisor cfdi40\Comprobante40\Receptor */
            $receptor = new cfdi40\Comprobante40\Receptor();
            $Client = \ClientesDAO::getClientData($this->folio, "nc");
            $receptor->setNombre($Client->getNombre());
            $receptor->setRfc($Client->getRfc());
            $receptor->setUsoCFDI($rs["usocfdi"]);
            $receptor->setDomicilioFiscalReceptor($Client->getCodigo());
            $receptor->setRegimenFiscalReceptor($Client->getRegimenFiscal());
        }
        $this->comprobante->setReceptor($receptor);
    }

//receptor

    /**
     * Recupera el CFDI relacionado. Por definición de Detisa, solo se soporta un CFDI relacionado.
     * En el caso de la nota de crédito, este nodo es obligatorio.
     * Crea el nodo CfdiRelacionados.
     */
    private function cfdiRelacionados() {

        $cfdiRelacionados = new cfdi40\Comprobante40\CfdiRelacionados();
        $cfdiRelacionado = new cfdi40\Comprobante40\CfdiRelacionados\CfdiRelacionado();

        $sql = "
            SELECT IFNULL(N.tiporelacion,  '') TipoRelacion, IFNULL(R.uuid,  '') UUID
            FROM nc N
            LEFT JOIN fc R ON R.id = N.relacioncfdi
            WHERE N.id = " . $this->folio;

        if (($query = $this->mysqlConnection->query($sql)) && ($rs = $query->fetch_assoc())) {

            if (!empty($rs['UUID'])) {

                $cfdiRelacionado->setUUID($rs['UUID']);
                $cfdiRelacionados->addCfdiRelacionado($cfdiRelacionado);
                $cfdiRelacionados->setTipoRelacion($rs['TipoRelacion']);
                $this->comprobante->addCfdiRelacionados($cfdiRelacionados);
            }
        }
    }

//cfdiRelacionados

    /**
     * Recupera los conceptos asociados a la nota de crédito.
     * Crea el nodo Conceptos, el arreglo de nodos Concepto y los nodos de Impuesto asociados a cada Concepto.
     */
    private function conceptos() {

        $conceptos = new cfdi40\Comprobante40\Conceptos();
        $subTotal = 0.00;
        $sql = "
        SELECT 
                IFNULL( com.clave, ncd.clave_producto ) NoIdentificacion,
                inv.inv_cunidad ClaveUnidad,
                inv.inv_cproducto ClaveProdServ,
                inv.descripcion Descripcion,
                ncd.factoriva,
                ncd.factorieps,
                ncd.preciou + ROUND( IF( cli.desgloseIEPS = 'S', 0.0000, ncd.factorieps ) , 4 ) ValorUnitario,
                ROUND(ncd.cantidad, 2) base_ieps,
                ncd.subtotal base_iva,
                ROUND( ncd.subtotal + ( ncd.total - ( ncd.subtotal + ncd.tax_iva + ncd.tax_ieps ) ) + IF( cli.desgloseIEPS = 'S', 0.0000, ncd.tax_ieps ), 2 ) Importe,
                ROUND( ( ncd.subtotal + ( ncd.total - ( ncd.subtotal + ncd.tax_iva + ncd.tax_ieps ) ) ) / ( ncd.preciou ), 4 ) Cantidad,
                ncd.tax_iva,
                IF( cli.desgloseIEPS = 'S', ncd.tax_ieps, 0.00 ) tax_ieps
        FROM (
                SELECT 
                    id folio,
                    clave_producto,
                    factoriva,
                    factorieps,
                    preciop,
                    preciou,
                    cantidad,
                    ROUND( cantidad * preciou, 2 ) subtotal,
                    ROUND( cantidad * preciou * factoriva, 2 ) tax_iva,
                    ROUND( cantidad * factorieps, 2 ) tax_ieps,
                    total
                FROM (
                        SELECT 
                            ncd.id,
                            ncd.producto clave_producto,
                            CAST( ncd.iva AS DECIMAL( 10, 6 ) ) factoriva,
                            CAST( ncd.ieps AS DECIMAL( 10, 6 ) ) factorieps,
                            ncd.preciob preciop,
                            ROUND( (  ncd.preciob-ncd.ieps )/( 1+ncd.iva ), 4 ) preciou,
                            ROUND( SUM( ncd.importe/ncd.preciob ), 4 ) cantidad,
                            ROUND( SUM( ncd.importe ), 2 ) total
                        FROM ncd 
                        WHERE ncd.id = " . $this->folio . " AND ncd.producto <= 10
                        GROUP BY producto, preciob, ieps
                        UNION ALL
                        SELECT
                            id,
                            ncd.producto clave_producto,
                            CAST( ncd.iva AS DECIMAL( 10, 6 ) ) factoriva,
                            CAST( ncd.ieps AS DECIMAL( 10, 6 ) ) factorips,
                            ncd.preciob preciop,
                            ROUND( ( ncd.preciob-ncd.ieps )/( 1+ncd.iva ), 4 ) preciou,
                            ROUND( ( ncd.importe/ncd.preciob ), 4 ) cantidad,
                            ROUND( ncd.importe, 2 ) total
                        FROM ncd 
                        WHERE ncd.id = " . $this->folio . " AND ncd.producto > 10
                ) A
        ) ncd
        JOIN inv ON inv.id = ncd.clave_producto
        LEFT JOIN com ON com.id = ncd.clave_producto
        JOIN nc ON ncd.folio = nc.id 
        JOIN cli ON cli.id = nc.cliente
        ORDER BY NoIdentificacion, ValorUnitario, factorieps
        ";

        if (($query = $this->mysqlConnection->query($sql))) {
            $BaseivaSm = 0;
            $BaseImp = 0;
            while (($rs = $query->fetch_assoc())) {

                $concepto = new cfdi40\Comprobante40\Conceptos\Concepto();
                $concepto->setClaveProdServ($rs['ClaveProdServ']);
                $concepto->setClaveUnidad($rs['ClaveUnidad']);
                $concepto->setDescripcion($rs['Descripcion']);
                $concepto->setImporte(number_format($rs['Importe'], 2, '.', ''));
                $concepto->setCantidad(number_format($rs['Cantidad'], 4, '.', ''));
                $concepto->setNoIdentificacion($rs['NoIdentificacion']);
                $rs["factoriva"] > 0 ? $concepto->setObjetoImp("02") : $concepto->setObjetoImp("01");
                $concepto->setValorUnitario(number_format($rs['ValorUnitario'], 4, '.', ''));

                $subTotal += $rs['Importe'];

                $traslados = new cfdi40\Comprobante40\Conceptos\Concepto\Impuestos\Traslados();
                $BaseivaSm += $rs['base_iva'];
                $BaseImp += $rs['tax_iva'];
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

                $impuestos = new cfdi40\Comprobante40\Conceptos\Concepto\Impuestos();
                $impuestos->setTraslados($traslados);
                $concepto->setImpuestos($impuestos);
                $conceptos->addConcepto($concepto);
            }//while

            $this->comprobante->setSubTotal(number_format($subTotal, 2, '.', ''));
            $this->comprobante->setConceptos($conceptos);
        }
        $this->baseGlobal = $BaseivaSm;
        $this->baseImpo = $BaseImp;
    }

//conceptos

    /**
     * Recupera el sumarizado de impuestos asociados a la nota de crédito.
     * Crea el nodo Impuestos y el nodo Traslados. En el caso de Omicrom, el nodo de Retenciones no existe.
     */
    private function impuestos() {

        $impuestos = new cfdi40\Comprobante40\Impuestos();
        $traslados = new cfdi40\Comprobante40\Impuestos\Traslados();
        $retens = new cfdi40\Comprobante40\Impuestos\Retenciones();

        $sql = "
            SELECT 
               ncd.factoriva,
               ncd.factorieps,
               SUM( IF( cli.desgloseIEPS = 'S', ROUND( ncd.cantidad , 2 ), 0.00 ) ) ttieps,
               SUM( ROUND( ncd.cantidad * ncd.preciou * ncd.factoriva, 2 ) ) tax_iva,
               SUM( IF( cli.desgloseIEPS = 'S', ROUND( ncd.cantidad * ncd.factorieps, 2 ), 0.00 ) ) tax_ieps,
               SUM(base_iva) base_iva
            FROM (
                   SELECT 
                       ncd.id folio,
                       CAST( ncd.iva AS DECIMAL( 10, 6 ) ) factoriva,
                       CAST( ncd.ieps AS DECIMAL( 10, 6 ) ) factorieps,
                       ROUND( (  ncd.preciob-ncd.ieps )/( 1+ncd.iva ), 4 ) preciou,
                       ROUND( SUM( ncd.importe/ncd.preciob ), 4 ) cantidad,
                       ROUND( SUM( ncd.importe/ncd.preciob ), 4 ) * ROUND( (  ncd.preciob-ncd.ieps )/( 1+ncd.iva ), 4 ) base_iva
                   FROM ncd 
                   WHERE ncd.id = " . $this->folio . " AND ncd.producto <= 10
                   GROUP BY producto, ieps, preciob
                   UNION ALL
                   SELECT
                       ncd.id folio,
                       CAST( ncd.iva AS DECIMAL( 10, 6 ) ) factoriva,
                       CAST( ncd.ieps AS DECIMAL( 10, 6 ) ) factorips,
                       ROUND( ( ncd.preciob-ncd.ieps )/( 1+ncd.iva ), 4 ) preciou,
                       ROUND( ( ncd.importe/ncd.preciob ), 4 ) cantidad,
                       ROUND( SUM( ncd.importe/ncd.preciob ), 4 ) * ROUND( (  ncd.preciob-ncd.ieps )/( 1+ncd.iva ), 4 ) base_iva
                   FROM ncd 
                   WHERE ncd.id = " . $this->folio . " AND ncd.producto > 10
            ) ncd
            JOIN nc ON ncd.folio = nc.id 
            JOIN cli ON cli.id = nc.cliente
            GROUP BY ncd.factoriva, ncd.factorieps
        ";

        $importe_iva = 0.00;
        $total_traslado = 0.00;
        $factor_iva = 0.000000;
        $base_iva = 0.00;
        if (($query = $this->mysqlConnection->query($sql))) {

            while (($rs = $query->fetch_assoc())) {

                $total_traslado += $rs['tax_iva'] + $rs['tax_ieps'];
                $importe_iva += $rs['tax_iva'];
                $factor_iva = $rs['factoriva'];
                $base_iva += $rs["base_iva"];
                if ($rs['tax_ieps'] > 0) {

                    $ieps = new cfdi40\Comprobante40\Impuestos\Traslados\Traslado();
                    $ieps->setBase(number_format($rs['ttieps'], 2, '.', ''));
                    $ieps->setImporte(number_format($rs['tax_ieps'], 2, '.', ''));
                    $ieps->setImpuesto('003');
                    $ieps->setTasaOCuota($rs['factorieps']);
                    $ieps->setTipoFactor('Cuota');
                    $traslados->addTraslado($ieps);
                }
            }
            $iva = new cfdi40\Comprobante40\Impuestos\Traslados\Traslado();
            $iva->setImporte(number_format($importe_iva, 2, '.', ''));
            //$iva->setImporte(number_format($this->baseImpo, 2, '.', ''));
            $iva->setImpuesto('002');
            $iva->setTasaOCuota($factor_iva);
            $iva->setTipoFactor('Tasa');
            $iva->setBase(round($this->baseGlobal, 2));
            //$iva->setBase(round($base_iva, 2));
            $traslados->addTraslado($iva);

            $impuestos->setTraslados($traslados);
            //$impuestos->setTotalImpuestosRetenidos(number_format(0, 2, '.', ''));
            $impuestos->setTotalImpuestosTrasladados(number_format($total_traslado, 2, '.', ''));
            //$impuestos->setTotalImpuestosTrasladados(number_format($this->baseImpo, 2, '.', ''));

            $this->comprobante->setImpuestos($impuestos);
        }
    }

//impuestos

    /**
     * Recupera el valor del campo observaciones en nc.
     * Si existe, crea la addenda Observaciones, definida por Detisa
     *
      private function observaciones() {

      $observaciones = new cfdi40\Comprobante40\adde  addenda\Observaciones();
      $sql = "
      SELECT nc.observaciones Observacion
      FROM nc
      WHERE nc.id = " . $this->folio;

      if (($query = $this->mysqlConnection->query($sql)) && ($rs = $query->fetch_assoc())) {
      $observaciones->addObservaciones(new cfdi33\Comprobante\addenda\Observaciones\Observacion($observacion = $rs['Observacion']));
      $this->comprobante->addAddenda($observaciones);
      }
      } */
}

//NotaCreditoDAO