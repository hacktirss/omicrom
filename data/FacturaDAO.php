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
require_once ('com/softcoatl/cfdi/v40/schema/Comprobante40.php');
require_once ('com/softcoatl/cfdi/v40/schema/Comprobante40/Addenda.php');
require_once ('com/softcoatl/cfdi/addenda/detisa/Observaciones.php');
require_once ('com/softcoatl/cfdi/complemento/ine/INE.php');
require_once ('pdf/PDFTransformer.php');

use \com\softcoatl\cfdi\v40\schema as cfdi40;
use com\softcoatl\cfdi\complemento as complemento;
use com\softcoatl\cfdi\addenda as Adenda;
use com\softcoatl\utils as utils;

class FacturaDAO {

    private $folio;
    private $totalIvaImpuesto;
    private $diferencia;
    private $totalIvaImporte;
    /* @var $comprobante cfdi40\Comprobante */
    private $comprobante;
    /* @var $mysqlConnection \mysqli */
    private $mysqlConnection;
    private $tdoctorelacionado;

    function __construct($folio) {

        error_log("CFDI40 Cargando CFDI con folio " . $folio);

        $this->folio = $folio;
        $this->comprobante = new cfdi40\Comprobante40();
        $this->mysqlConnection = getConnection();
        $sql = "SELECT  descuento FROM ( SELECT ROUND(SUM( descuento),2) descuento
        FROM (SELECT descuento FROM fcd WHERE id = '$folio') as SUB
        ) SUBQ";
        if (($query = $this->mysqlConnection->query($sql)) && ($rs = $query->fetch_assoc()) && $rs["descuento"] > 0) {
            error_log("Creamos con descuento");
            $this->comprobanteDescuento();
            $this->emisor();
            $this->receptor();
            $this->cfdiRelacionadosDescuento();
            $this->conceptosDescuento();
            $this->impuestosDescuento();
            $this->ine();
            $this->observaciones();
        } else {
            error_log("Creamos sin descuento");
            $this->comprobante();
            $this->emisor();
            $this->receptor();
            $this->cfdiRelacionados();
            $this->conceptos();
            $this->impuestos();
            $this->ine();
            $this->observaciones();
        }
        utils\HTTPUtils::setSessionValue("TipoCantidad", false);
    }

//constructor

    public function __destruct() {
        $this->mysqlConnection->close();
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

    function getTotalIvaImporte() {
        return $this->totalIvaImporte;
    }

    function setTotalIvaImporte($totalIvaImporte) {
        $this->totalIvaImporte = $totalIvaImporte;
    }

    function getDiferencia() {
        return $this->diferencia;
    }

    function setDiferencia($diferencia) {
        $this->diferencia = $diferencia;
    }

    function getTotalIvaImpuesto() {
        return $this->totalIvaImpuesto;
    }

    function setTotalIvaImpuesto($totalIvaImpuesto) {
        $this->totalIvaImpuesto = $totalIvaImpuesto;
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
		    , tdoctorelacionado
                FROM fc JOIN cia ON TRUE 
                WHERE fc.id = " . $this->folio;

        if (($query = $this->mysqlConnection->query($sql)) && ($rs = $query->fetch_assoc())) {

            $this->comprobante->setFolio($rs['Folio']);
            $this->comprobante->setSerie($rs['Serie']);
            $this->comprobante->setFecha($rs['Fecha']);
            $this->comprobante->setTipoDeComprobante("I");
            $this->comprobante->setVersion("4.0");
            $this->comprobante->setFormaPago($rs['FormaPago']);
            $this->comprobante->setMetodoPago($rs['MetodoPago']);
            $this->comprobante->setMoneda("MXN");
            $this->comprobante->setTipoCambio(1);
            $this->comprobante->setTotal(number_format($rs['Total'], 2, '.', ''));
            $this->comprobante->setLugarExpedicion($rs['LugarExpedicion']);
            $this->tdoctorelacionado = $rs['tdoctorelacionado'];
            $this->comprobante->setExportacion("01");
        }//if
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

        $Sql = "SELECT usocfdi FROM fc WHERE fc.id = " . $this->folio . " ";
        if (($query = $this->mysqlConnection->query($Sql)) && ($rs = $query->fetch_assoc())) {
            $Client = \ClientesDAO::getClientData($this->folio, "fc");
            $receptor = new cfdi40\Comprobante40\Receptor();
            if (utils\HTTPUtils::getSessionValue("cGeneric") == 1) {
                /* Requerimientos necesarios para facturar publico en general */
                $Nombre = "PUBLICO EN GENERAL";
                $Rfc = "XAXX010101000";
                $RegimenF = 616;
                $DFR = $this->comprobante->getLugarExpedicion();
                $usoCfdi = "S01";
            } else {
                /* Cualquier cliente */
                $Nombre = $Client->getNombre();
                $Rfc = utils\HTTPUtils::getSessionValue("cGenericPerso") ? "XAXX010101000" : $Client->getRfc();
                $DFR = utils\HTTPUtils::getSessionValue("cGenericPerso") ? $this->comprobante->getLugarExpedicion() : $Client->getCodigo();
                $RegimenF = utils\HTTPUtils::getSessionValue("cGenericPerso") ? "616" : $Client->getRegimenFiscal();
                $usoCfdi = utils\HTTPUtils::getSessionValue("cGenericPerso") ? "S01" : $rs["usocfdi"];
            }
            /* @var $emisor cfdi40\Comprobante40\Receptor */
            $receptor->setNombre($Nombre);
            $receptor->setRfc($Rfc);
            $receptor->setUsoCFDI($usoCfdi);
            $receptor->setDomicilioFiscalReceptor($DFR);
            $receptor->setRegimenFiscalReceptor($RegimenF);
        }
        $this->comprobante->setReceptor($receptor);
    }

//receptor

    /**
     * Recupera el CFDI relacionado. Por definición de Detisa, solo se soporta un CFDI relacionado.
     * Crea el nodo CfdiRelacionados si es necesario.
     */
    private function cfdiRelacionados() {

        $cfdiRelacionados = new cfdi40\Comprobante40\CfdiRelacionados();
        $sql = "
            SELECT IFNULL(F.tiporelacion,  '') TipoRelacion, IFNULL(R.uuid,  '') UUID,F.relacioncfdi
            FROM fc F
            LEFT JOIN fc R ON R.id = F.relacioncfdi
            WHERE F.id = " . $this->folio;

        if ($this->tdoctorelacionado == 'ANT') {
            $sql = "
            SELECT IFNULL(F.tiporelacion,  '') TipoRelacion, IFNULL(p.uuid,  '') UUID,F.relacioncfdi
            FROM fc F
            LEFT JOIN pagos p ON p.id = F.relacioncfdi
            WHERE F.id = " . $this->folio;
        }
        $noRela = 0;
        if (($query = $this->mysqlConnection->query($sql)) && ($rs = $query->fetch_assoc())) {
            $noRela = $rs["relacioncfdi"];
        }
        if ($noRela > 0) {
            $cfdiRelacionado = new cfdi40\Comprobante40\CfdiRelacionados\CfdiRelacionado();
            if (($query = $this->mysqlConnection->query($sql)) && ($rs = $query->fetch_assoc())) {
                if (!empty($rs['UUID'])) {
                    $cfdiRelacionado->setUUID($rs['UUID']);
                    $cfdiRelacionados->addCfdiRelacionado($cfdiRelacionado);
                    $cfdiRelacionados->setTipoRelacion($rs['TipoRelacion']);
                    $this->comprobante->addCfdiRelacionados($cfdiRelacionados);
                }
            }
        } else {
            $RelacionesCfdi = "SELECT * FROM relacion_cfdi WHERE id_fc = '" . $this->folio . "'";
            $TipoRelacion = "";
            if ($query = $this->mysqlConnection->query($RelacionesCfdi)) {
                while (($rs = $query->fetch_assoc())) {
                    $cfdiRelacionado = new cfdi40\Comprobante40\CfdiRelacionados\CfdiRelacionado();
                    $cfdiRelacionado->setUUID($rs['uuid_relacionado']);
                    error_log("RESULTADO UUID " . $cfdiRelacionado->getUUID());
                    $cfdiRelacionados->addCfdiRelacionado($cfdiRelacionado);
                    $cfdiRelacionados->setTipoRelacion($rs['tipo_relacion']);
                    $TipoRelacion = $rs['tipo_relacion'];
                }
                if ($TipoRelacion <> "") {
                    error_log("RELACIONADOOOOOS " . print_r($cfdiRelacionados));
                    $this->comprobante->addCfdiRelacionados($cfdiRelacionados);
                }
            }
        }
    }

//cfdiRelacionados

    /**
     * Recupera los conceptos asociados a la factura.
     * Crea el nodo Conceptos, el arreglo de nodos Concepto y los nodos de Impuesto asociados a cada Concepto.
     */
    private function conceptos() {

        $conceptos = new cfdi40\Comprobante40\Conceptos();
        $subTotal = 0.00;
        $TCantidad = utils\HTTPUtils::getSessionValue("TipoCantidad") == 1 ?
                "ROUND( ( fcd.subtotal + diferencia + ROUND( IF( cli.desgloseIEPS = 'S', 0.0000, fcd.tax_ieps ), 4 ) ) / ( fcd.preciou + ROUND( IF( cli.desgloseIEPS = 'S', 0.0000, fcd.factorieps ), 4 ) ), 4 ) Cantidad, " :
                "fcd.cantidad Cantidad,";
        error_log("Tipo de Cantidad : " . $TCantidad);
        $sql = "SELECT "
                . "CONCAT( cia.cre, '-', fcd.ticket ) NoIdentificacion, "
                . "inv.inv_cunidad ClaveUnidad, "
                . "inv.inv_cproducto ClaveProdServ, "
                . "IF( (inv.rubro='Combustible' || inv.rubro = 'Aceites'),CONCAT( inv.descripcion, IF( fcd.ticket = 0, ' Captura Manual', CONCAT( ' Ticket no: ' , fcd.ticket ) ) ) , inv.descripcion) Descripcion, "
                . "fcd.factoriva, "
                . "fcd.factorieps, "
                . "fcd.factorivaretenido,"
                . "fcd.factorisrretenido,"
                . "fcd.tax_isr_retenido,"
                . "fcd.preciop,"
                . "fcd.preciou + ROUND( IF( cli.desgloseIEPS = 'S', 0.0000, fcd.factorieps ) , 4 ) ValorUnitario, "
                . "fcd.cantidad base_ieps, "
                . "fcd.subtotal base_iva, "
                . "fcd.folio, "
                ."round(fcd.cantidad * round(preciou + ROUND(IF(cli.desgloseIEPS = 'S',0.0000,fcd.factorieps),4),4) ,2) Importe, "
                . $TCantidad
                . "fcd.tax_iva, "
                . "IF( cli.desgloseIEPS = 'S', fcd.tax_ieps, 0.00 ) tax_ieps "
                . "FROM ( "
                . "SELECT "
                . "id folio, "
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
                . "ROUND( cantidad * preciou, 4 ) subtotal, "
                . "ROUND( cantidad * preciou * factoriva, 2 ) tax_iva, "
                . "ROUND( cantidad * factorieps, 2 ) tax_ieps, "
                . "total, "
                . "total - ROUND( cantidad * preciou, 2 ) - ROUND( cantidad * preciou * factoriva, 2 ) - ROUND( cantidad * factorieps, 2 ) diferencia "
                . "FROM ( "
                . "SELECT "
                . "fcd.id, "
                . "fcd.ticket ticket, "
                . "fcd.producto clave_producto, "
                . "CAST( fcd.iva AS DECIMAL( 10, 6 ) ) factoriva, "
                . "CAST( fcd.ieps AS DECIMAL( 10, 6 ) ) factorieps, "
                . "CAST( fcd.iva_retenido AS DECIMAL( 10, 6 ) ) factorivaretenido, "
                . "CAST( fcd.isr_retenido AS DECIMAL( 10, 6 ) ) factorisrretenido, "
                . "fcd.preciob preciop, "
                . "ROUND( ( fcd.preciob-fcd.ieps )/( 1+fcd.iva ), 4 ) preciou, "
                . "ROUND( fcd.importe/fcd.preciob, 3 ) cantidad, "
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
        $total = 0.00;
        error_log($sql);

        if (($query = $this->mysqlConnection->query($sql))) {

            while (($rs = $query->fetch_assoc())) {

                $numDecPrecio = 4;
                $numDecCant = 3;
                $num2 = pow(10, -$numDecCant) / 2;
                $num = pow(10, -$numDecPrecio) / 2;
                $LimitInferior = (number_format($rs['Cantidad'], 3, '.', '') - $num2) * (number_format($rs['ValorUnitario'], 4, '.', '') - $num2);
                $LimitSuperior = (number_format($rs['Cantidad'], 3, '.', '') + $num) * (number_format($rs['ValorUnitario'], 4, '.', '') + $num);
                if ($rs['Importe'] > $LimitSuperior || $rs['Importe'] < $LimitInferior) {
                    error_log("DIFERENCIAS EN LIMITES PARA Ticket no. " . $rs["NoIdentificacion"] . "  Limite Superior " . $LimitSuperior . " E Inferior  " . $LimitInferior . " Importe : " . $rs['Importe']);
                }
                //error_log("Ticket: " . $rs["NoIdentificacion"] . ": " . $LimitInferior . " < " . $rs['Importe'] . " < " . $LimitSuperior);

                $concepto = new cfdi40\Comprobante40\Conceptos\Concepto();
                $concepto->setClaveProdServ($rs['ClaveProdServ']);
                $concepto->setClaveUnidad($rs['ClaveUnidad']);
                $concepto->setDescripcion($rs['Descripcion']);
                $concepto->setImporte(number_format($rs['Importe'], 2, '.', ''));
                $concepto->setCantidad(number_format($rs['Cantidad'], 3, '.', ''));
                $concepto->setNoIdentificacion($rs['NoIdentificacion']);
                $concepto->setValorUnitario(number_format($rs['ValorUnitario'], 4, '.', ''));
//                $rs["factoriva"] > 0 ? $concepto->setObjetoImp("02") : $concepto->setObjetoImp("01");
                $concepto->setObjetoImp("02");

                $subTotal += number_format($rs['Importe'], 4, '.', '');

                $traslados = new cfdi40\Comprobante40\Conceptos\Concepto\Impuestos\Traslados();
                $retenciones = new cfdi40\Comprobante40\Conceptos\Concepto\Impuestos\Retenciones();

                $iva = new cfdi40\Comprobante40\Conceptos\Concepto\Impuestos\Traslados\Traslado();
                $ImpT = $rs['Importe'] * $rs['factoriva'];
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

                if ($rs['factorivaretenido'] > 0) {
                    $retencion_iva = new cfdi40\Comprobante40\Conceptos\Concepto\Impuestos\Retenciones\Retencion();
                    $base1 = $rs["base_iva"] * $rs["factorivaretenido"];
                    $retencion_iva->setBase(number_format($rs["base_iva"], 2, '.', ''));
                    $retencion_iva->setImpuesto('002');
                    $retencion_iva->setTipoFactor('Tasa');
                    $retencion_iva->setTasaOCuota($rs['factorivaretenido']);
                    $retencion_iva->setImporte(number_format($base1, 2, '.', ''));
                    $retenciones->addRetencion($retencion_iva);
                    $total += number_format($rs['Importe'], 2, ".", "") + number_format($rs['tax_iva'], 2, ".", "") + number_format($rs['tax_ieps'], 2, ".", "") - $base1;
                } else {
                    $total += number_format($rs['Importe'], 2, ".", "") + number_format($rs['tax_iva'], 2, ".", "") + number_format($rs['tax_ieps'], 2, ".", "");
                }

                if ($rs['factorisrretenido'] > 0) {
                    $retencion_isr = new cfdi40\Comprobante40\Conceptos\Concepto\Impuestos\Retenciones\Retencion();
                    $Importe_Isr = $rs["base_iva"] * $rs['factorisrretenido'];
                    $retencion_isr->setBase(number_format($rs["base_iva"], 2, '.', ''));
                    $retencion_isr->setImpuesto('001');
                    $retencion_isr->setTipoFactor('Tasa');
                    $retencion_isr->setTasaOCuota($rs['factorisrretenido']);
                    $retencion_isr->setImporte(number_format($Importe_Isr, 2, '.', ''));
                    $retenciones->addRetencion($retencion_isr);
                }

                $impuestos = new cfdi40\Comprobante40\Conceptos\Concepto\Impuestos();
                $impuestos->setTraslados($traslados);
                if ($rs['factorivaretenido'] > 0 || $rs['factorisrretenido'] > 0) {
                    $impuestos->setRetenciones($retenciones);
                }

                $concepto->setImpuestos($impuestos);
                $conceptos->addConcepto($concepto);
            }//while

            error_log("CFDI40 TOTAL 2 : " . $total);
            error_log("CFDI40 TOTAL : " . $this->getComprobante()->getTotal());
            if ($total !== $this->comprobante->getTotal()) {
                $difference = round($this->comprobante->getTotal() - $total, 2);
                error_log("There is a difference " . $difference);
                $arreglo = $conceptos->getConcepto();
                for ($i = 0; $i < count($arreglo); $i++) {
                    $subTotal += $difference;
                    error_log("Modificando: " . $arreglo[$i]->getNoIdentificacion());
                    $importe = $arreglo[$i]->getImporte() + $difference;
                    $cantidad = round($importe / $arreglo[$i]->getValorUnitario(), 4);
                    $arreglo[$i]->setImporte(number_format($importe, 2, '.', ''));
                    $arreglo[$i]->setCantidad($cantidad);
                    break;
                }
            }
            error_log("SubTotal: " . number_format($subTotal, 2, '.', ''));
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
                ROUND(SUM( ROUND( fcd.cantidad * fcd.preciou, 2 ) ),2) base_iva,
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
                       ROUND( ( fcd.importe/fcd.preciob ), 3 ) cantidad,
                       CAST( fcd.iva_retenido AS DECIMAL( 10, 6 ) ) factoriva_retenido,
                       fcd.preciob,
                       CAST( fcd.isr_retenido AS DECIMAL( 10, 6 ) ) factorisr_retenido
                   FROM fcd 
                   WHERE fcd.id = " . $this->folio . "
            ) fcd
            JOIN fc ON fcd.folio = fc.id 
            JOIN cli ON cli.id = fc.cliente
            GROUP BY fcd.factoriva, fcd.factorieps";
        $BaseIva = 0.00;
        $importe_iva = 0.00;
        $total_traslado = 0.00;
        $factor_iva = 0.000000;
        $importe = 0.00;
        $importe_Retenido = 0.00;
        $BaseIva0 = 0.00;
        if (($query = $this->mysqlConnection->query($sql))) {
            while (($rs = $query->fetch_assoc())) {
                $total_traslado += $rs['tax_iva'] + $rs['tax_ieps'];
                $importe_iva += $rs['tax_iva'];
                $factor_ieps = $rs['factorieps'];
                if ($rs["tax_iva"] > 0) {
                    $BaseIva += $rs["base_iva"];
                    $factor_iva = $rs['factoriva'];
                } else {
                    $BaseIva0 += $rs["base_iva"];
                    $FactorIva0 = $rs["factoriva"];
                    $factor_iva0 = $rs['factoriva'];
                }
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
            }

            if ($importe_Retenido > 0) {
                $R_impuesto = new cfdi40\Comprobante40\Impuestos\Retenciones\Retencion();
                $R_impuesto->setImpuesto('002'); // cambia 001 por 002
                $R_impuesto->setImporte(number_format($importe_Retenido, 2, '.', ''));
                $retens->addRetencion($R_impuesto);
//$importe_Retenido = $importe_Retenido + $rs["tax_isr_retenido"];
            }
            if ($BaseIva0 > 0) {
                $iva = new cfdi40\Comprobante40\Impuestos\Traslados\Traslado();
                $iva->setImporte(number_format($factor_iva0, 2, '.', ''));
                $iva->setImpuesto('002');
                $iva->setTasaOCuota($FactorIva0);
                $iva->setTipoFactor('Tasa');
                $iva->setBase(number_format($BaseIva0, 2, '.', ''));
                $traslados->addTraslado($iva);
            }
            if ($BaseIva > 0) {
                $iva = new cfdi40\Comprobante40\Impuestos\Traslados\Traslado();
                $iva->setImporte(number_format($importe_iva, 2, '.', ''));
                $iva->setImpuesto('002');
                $iva->setTasaOCuota($factor_iva);
                $iva->setTipoFactor('Tasa');
                $iva->setBase(number_format($BaseIva, 2, '.', ''));
                $traslados->addTraslado($iva);
            }
            $impuestos->setTraslados($traslados);
            if ($importe_Retenido > 0) {
                $impuestos->setTotalImpuestosRetenidos(number_format($importe_Retenido, 2, '.', ''));
                $impuestos->setRetenciones($retens);
                $TotalF = $this->comprobante->getTotal();
                $TotalF -= number_format($importe_Retenido, 2, '.', '');
//$this->comprobante->setTotal($TotalF);
            }
            $impuestos->setTotalImpuestosTrasladados(number_format($total_traslado, 2, '.', ''));

            $this->comprobante->setImpuestos($impuestos);
        }
    }

//impuestos

    private function ine() {
        $sql = "SELECT complemento, atributo, IFNULL( valor, defecto ) valor "
                . "FROM ( SELECT A.id_complemento, A.id id_atributo, A.nombre atributo, C.nombre complemento, A.defecto FROM complementos C JOIN complemento_attr A ON C.id = A.id_complemento WHERE C.id = ? ) complemento "
                . "LEFT JOIN ( SELECT * FROM complemento_val WHERE id_fc_fk = ? ) valores USING( id_complemento, id_atributo )";
        $complemento = "1";
        $valores = array();
        if (($ps = $this->mysqlConnection->prepare($sql))) {
            $ps->bind_param("ii",
                    $complemento,
                    $this->folio);
            if ($ps->execute()) {
                $complemento = NULL;
                $atributo = NULL;
                $valor = NULL;

                $ps->bind_result($complemento, $atributo, $valor);
                while ($ps->fetch()) {
                    $valores[$atributo] = $valor;
                }//for each row
            }
        }

        $complemento = new cfdi40\Comprobante40\Complemento();
        if (!empty($valores['IdContabilidad'])) {
            $ine = new complemento\ine\INE;
//            $ine = new complemento\INE();
            $ine->setVersion($valores['Version']);
            $ine->setTipoProceso($valores['TipoProceso']);

            $entidad = new complemento\INE\Entidad();
            $entidad->setAmbito($valores['Ambito']);
            $entidad->setClaveEntidad($valores['ClaveEntidad']);

            if ("Ordinario" == $ine->getTipoProceso()) {
                $ine->setTipoComite($valores['TipoComite']);
            }

            if ("Ordinario" == $ine->getTipoProceso() && "Ejecutivo Nacional" == $ine->getTipoComite()) {
                $ine->setIdContabilidad($valores['IdContabilidad']);
            } else {
                $contabilidad = new complemento\INE\Entidad\Contabilidad();
                $contabilidad->setIdContabilidad($valores['IdContabilidad']);
                $entidad->addContabilidad($contabilidad);
            }
//            $ine->addEntidad($entidad);
            $ine->setEntidad(array($entidad));
            $complemento->addAny($ine);
            error_log(print_r($complemento, true));
            $this->comprobante->setComplemento($complemento);
        }
    }

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
        $SQL2 = "SELECT nombreFactura,id,alias FROM omicrom.cli where id = (select cliente from fc where id = " . $this->folio . ");";
        if (($query = $this->mysqlConnection->query($SQL2))) {
            $rs = $query->fetch_assoc();
            if ($rs["nombreFactura"] === "C") {
                $Union = "Cuenta : " . $rs["id"];
            } else if ($rs["nombreFactura"] === "F") {
                $Union = "Cuenta : " . $rs["id"] . " " . $rs["alias"];
            } else if ($rs["nombreFactura"] === "A") {
                $Union = "Cuenta : " . $rs["alias"];
            } else {
                $Union = "";
            }
        }

        if (($query = $this->mysqlConnection->query($sql))) {
            while (($rs = $query->fetch_assoc())) {
                $observacion = $rs['Observacion'] . " | $Union";
            }
            if ($rs['Observacion'] !== "" || $Union !== "") {
                $Observaciones = new Adenda\detisa\Observaciones();
                $Observacion = new Adenda\detisa\Observaciones\Observacion($observacion);
                $Observaciones->addObservaciones($Observacion);
                $complemento->addAny($Observaciones);
                $this->comprobante->setAddenda($complemento);
            }
        }
    }

    /**
     * Recupera la información relativa a la factura.
     * Crea el objeto ComprobanteDescuento
     */
    private function comprobanteDescuento() {
        $sql = "
                SELECT 
                    fc.folio Folio, 
                    fc.serie Serie, 
                    DATE_FORMAT(fc.fecha, '%Y-%m-%dT%H:%i:%s') Fecha, 
                    fc.formadepago FormaPago, 
                    fc.metododepago MetodoPago, 
                    fc.total - fc.descuento Total, 
                    TRIM( cia.codigo ) LugarExpedicion 
		    , tdoctorelacionado
                FROM fc JOIN cia ON TRUE 
                WHERE fc.id = " . $this->folio;

        if (($query = $this->mysqlConnection->query($sql)) && ($rs = $query->fetch_assoc())) {

            $this->comprobante->setFolio($rs['Folio']);
            $this->comprobante->setSerie($rs['Serie']);
            $this->comprobante->setFecha($rs['Fecha']);
            $this->comprobante->setTipoDeComprobante("I");
            $this->comprobante->setVersion("4.0");
            $this->comprobante->setFormaPago($rs['FormaPago']);
            $this->comprobante->setMetodoPago($rs['MetodoPago']);
            $this->comprobante->setMoneda("MXN");
            $this->comprobante->setTipoCambio(1);
            $this->comprobante->setTotal(number_format($rs['Total'], 2, '.', ''));
            $this->comprobante->setLugarExpedicion($rs['LugarExpedicion']);
            $this->tdoctorelacionado = $rs['tdoctorelacionado'];
            $this->comprobante->setExportacion("01");
        }//if
    }

//comprobante

    /**
     * Recupera el CFDI relacionado. Por definición de Detisa, solo se soporta un CFDI relacionado.
     * Crea el nodo CfdiRelacionados si es necesario.
     */
    private function cfdiRelacionadosDescuento() {

        $cfdiRelacionados = new cfdi40\Comprobante40\CfdiRelacionados();
        $cfdiRelacionado = new cfdi40\Comprobante40\CfdiRelacionados\CfdiRelacionado();

        $sql = "
            SELECT IFNULL(F.tiporelacion,  '') TipoRelacion, IFNULL(R.uuid,  '') UUID
            FROM fc F
            LEFT JOIN fc R ON R.id = F.relacioncfdi
            WHERE F.id = " . $this->folio;

        if ($this->tdoctorelacionado == 'ANT') {
            $sql = "
            SELECT IFNULL(F.tiporelacion,  '') TipoRelacion, IFNULL(p.uuid,  '') UUID
            FROM fc F
            LEFT JOIN pagos p ON p.id = F.relacioncfdi
            WHERE F.id = " . $this->folio;
        }

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
     * Recupera los conceptos asociados a la factura.
     * Crea el nodo Conceptos, el arreglo de nodos Concepto y los nodos de Impuesto asociados a cada Concepto.
     */
    private function conceptosDescuento() {

        $conceptos = new cfdi40\Comprobante40\Conceptos();
        $subTotal = 0.00;

        $sql = "SELECT "
                . "CONCAT( cia.cre, '-', fcd.ticket ) NoIdentificacion, "
                . "inv.inv_cunidad ClaveUnidad, "
                . "inv.inv_cproducto ClaveProdServ, "
                . "CONCAT( inv.descripcion, IF( fcd.ticket = 0, ' Captura Manual', CONCAT( ' Ticket no: ' , fcd.ticket ) ) ) Descripcion, "
                . "fcd.factoriva, "
                . "fcd.factorieps,"
                . "fcd.descuento / (1 + fcd.factoriva) descuento, "
                . "fcd.descuento DescuentoTotal, "
                . "fcd.factorivaretenido,"
                . "fcd.factorisrretenido,"
                . "fcd.tax_isr_retenido,"
                . "fcd.preciop,"
                . "fcd.preciou + ROUND( IF( cli.desgloseIEPS = 'S', 0.0000, fcd.factorieps ) , 4 ) ValorUnitario, "
                . "fcd.cantidad base_ieps, "
                . "fcd.subtotal base_iva, "
                . "fcd.folio, "
                //. "ROUND( fcd.subtotal + diferencia + IF( cli.desgloseIEPS = 'S', 0.0000, fcd.tax_ieps ), 4 ) Importe, "
                . "round(fcd.cantidad * round(preciou + ROUND(IF(cli.desgloseIEPS = 'S',0.0000,fcd.factorieps),4),4) ,2) Importe,"
                . "fcd.cantidad Cantidad, "
                //. "ROUND( ( fcd.subtotal + diferencia + ROUND( IF( cli.desgloseIEPS = 'S', 0.0000, fcd.tax_ieps ), 4 ) ) / ( fcd.preciou + ROUND( IF( cli.desgloseIEPS = 'S', 0.0000, fcd.factorieps ), 4 ) ), 4 ) Cantidad, "
                . "fcd.tax_iva, "
                . "IF( cli.desgloseIEPS = 'S', fcd.tax_ieps, 0.00 ) tax_ieps "
                . "FROM ( "
                . "SELECT "
                . "id folio, "
                . "ticket, "
                . "clave_producto, "
                . "factoriva,"
                . "descuento, "
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
                . "fcd.ticket ticket,descuento, "
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
        $total = 0.00;
        $tot = 0.00;
        $SumIvaComprara = 0.00;
        $DescTotal = 0.00;
        $SumImporteIva = 0.00;
        $DescuentoSum = 0.00;
        $DescTotal = 0.00;
        if (($query = $this->mysqlConnection->query($sql))) {
            $DescuentoSum = 0;
            $TT = 0;
            while (($rs = $query->fetch_assoc())) {

                $numDecPrecio = 4;
                $numDecCant = 3;
                $num2 = pow(10, -$numDecCant) / 2;
                $num = pow(10, -$numDecPrecio) / 2;
                $LimitInferior = (number_format($rs['Cantidad'], 3, '.', '') - $num2) * (number_format($rs['ValorUnitario'], 4, '.', '') - $num);
                $LimitSuperior = (number_format($rs['Cantidad'], 3, '.', '') + $num2) * (number_format($rs['ValorUnitario'], 4, '.', '') + $num);
                if ($rs['Importe'] > $LimitSuperior || $rs['Importe'] < $LimitInferior) {
                    error_log("DIFERENCIAS EN LIMITES PARA Ticket no. " . $rs["NoIdentificacion"] . "  Limite Superior " . $LimitSuperior . " E Inferior  " . $LimitInferior . " Importe : " . $rs['Importe']);
                }

                $concepto = new cfdi40\Comprobante40\Conceptos\Concepto();
                $concepto->setClaveProdServ($rs['ClaveProdServ']);
                $concepto->setClaveUnidad($rs['ClaveUnidad']);
                $concepto->setDescripcion($rs['Descripcion']);
                $concepto->setImporte(number_format($rs['Importe'], 2, '.', ''));
                $concepto->setCantidad(number_format($rs['Cantidad'], 2, '.', ''));
                $concepto->setNoIdentificacion($rs['NoIdentificacion']);
                $concepto->setValorUnitario(number_format($rs['ValorUnitario'], 4, '.', ''));
                $Descuento = number_format($rs['descuento'], 2, '.', '');
                $DescuentoTotal = number_format($rs['DescuentoTotal'], 2);
                if ($Descuento > 0) {
                    $concepto->setDescuento(number_format($Descuento, 2));
                }

                $rs["factoriva"] > 0 ? $concepto->setObjetoImp("02") : $concepto->setObjetoImp("01");
                $subTotal += $rs['Importe'];

                $traslados = new cfdi40\Comprobante40\Conceptos\Concepto\Impuestos\Traslados();
                $retenciones = new cfdi40\Comprobante40\Conceptos\Concepto\Impuestos\Retenciones();

                $iva = new cfdi40\Comprobante40\Conceptos\Concepto\Impuestos\Traslados\Traslado();
                $ImpT = number_format($rs['Importe'] * $rs['factoriva'], 2, '.', '');
                if ($Descuento > 0) {
                    $iva->setBase(number_format($rs['base_iva'] - $Descuento, 2, '.', ''));
                } else {
                    $iva->setBase(number_format($rs['base_iva'], 2, '.', ''));
//                    $iva->setBase(number_format($rs['Importe'], 2, '.', ''));
                }

                $iva->setImpuesto('002');
                $iva->setTasaOCuota($rs['factoriva']);
                $iva->setTipoFactor('Tasa');
                $ImpIva = number_format($iva->getBase() * $rs["factoriva"], 2, '.', '');
                //$ImpIva = $Descuento > 0 ? number_format($iva->getBase() * $rs["factoriva"], 3) : number_format($iva->getBase() * $rs["factoriva"], 3);
                $iva->setImporte(number_format($ImpIva, 2, '.', ''));
                //$iva->setImporte(number_format($ImpIva, 3, '.', ''));

                $tot += number_format($rs['Importe'], 4, '.', '') - $Descuento + $ImpIva;
                $TotalIva = (number_format(number_format($rs['Importe'], 2, '.', '') - $Descuento, 2, '.', '') + number_format(number_format(number_format($rs['Importe'], 2, '.', '') - $Descuento, 2, '.', '') * $rs['factoriva'], 2, '.', ''));
                $traslados->addTraslado($iva);

                if ($rs['tax_ieps'] > 0) {

                    $ieps = new cfdi40\Comprobante40\Conceptos\Concepto\Impuestos\Traslados\Traslado();
                    $ieps->setBase(number_format($rs['Cantidad'], 2, '.', ''));
                    $ieps->setImpuesto('003');
                    $ieps->setTasaOCuota($rs['factorieps']);
                    $ieps->setTipoFactor('Cuota');
                    $ieps->setImporte(number_format($rs['tax_ieps'], 2, '.', ''));
                    $TotalIeps = number_format($rs['tax_ieps'], 2, '.', '');
                    $traslados->addTraslado($ieps);
                }

                if ($rs['factorivaretenido'] > 0) {
                    $retencion_iva = new cfdi40\Comprobante40\Conceptos\Concepto\Impuestos\Retenciones\Retencion();
                    $base1 = $rs["base_iva"] * $rs["factorivaretenido"];
                    $retencion_iva->setBase(number_format($rs["base_iva"], 2, '.', ''));
                    $retencion_iva->setImpuesto('002');
                    $retencion_iva->setTipoFactor('Tasa');
                    $retencion_iva->setTasaOCuota($rs['factorivaretenido']);
                    $retencion_iva->setImporte(number_format($base1, 2, '.', ''));
                    $retenciones->addRetencion($retencion_iva);
                    $total += $rs['Importe'] + $rs['tax_iva'] + $rs['tax_ieps'] - $base1;
                } else {
                    $total += $rs['Importe'] + $rs['tax_iva'] + $rs['tax_ieps'];
                }

                if ($rs['factorisrretenido'] > 0) {
                    $retencion_isr = new cfdi40\Comprobante40\Conceptos\Concepto\Impuestos\Retenciones\Retencion();
                    $Importe_Isr = $rs["base_iva"] * $rs['factorisrretenido'];
                    $retencion_isr->setBase(number_format($rs["base_iva"], 2, '.', ''));
                    $retencion_isr->setImpuesto('001');
                    $retencion_isr->setTipoFactor('Tasa');
                    $retencion_isr->setTasaOCuota($rs['factorisrretenido']);
                    $retencion_isr->setImporte(number_format($Importe_Isr, 2, '.', ''));
                    $retenciones->addRetencion($retencion_isr);
                }

                $impuestos = new cfdi40\Comprobante40\Conceptos\Concepto\Impuestos();
                $impuestos->setTraslados($traslados);
                if ($rs['factorivaretenido'] > 0 || $rs['factorisrretenido'] > 0) {
                    $impuestos->setRetenciones($retenciones);
                }

                $concepto->setImpuestos($impuestos);
                $conceptos->addConcepto($concepto);
                $DescTotal += $DescuentoTotal;
                $DescuentoSum += number_format($Descuento, 2);
                $TT += number_format($TotalIva + $TotalIeps, 2);
                $SumIvaComprara += $iva->getBase();
                $SumImporteIva += $iva->getImporte();
            }//while
            error_log("Calculado :" . number_format($tot, 2, ".", ""));
            $this->totalIvaImpuesto = $SumIvaComprara;
            $this->totalIvaImporte = $SumImporteIva;
            if ($DescuentoSum > 0) {
                $this->comprobante->setDescuento(number_format($DescuentoSum, 2, '.', ''));
                //$this->comprobante->setTotal(number_format(($subTotal - $this->comprobante->getDescuento()) * 1.16, 2, ".", ""));
//                $this->comprobante->setTotal($TT);
            }
            $total = number_format($total - $DescTotal, 2, ".", "");

            error_log("CFDI40 TOTAL : -- " . $tot);
            $this->getComprobante()->setTotal(number_format($tot, 2, ".", ""));
            error_log("CFDI40 TOTAL : " . $this->getComprobante()->getTotal());

            $difference = round($total - $this->comprobante->getTotal(), 2);
            error_log("Diferencia " . $difference);
            $this->diferencia = $difference;
            if ($total !== $this->comprobante->getTotal() && FALSE) {
                error_log("There is a difference " . $difference);
                $arreglo = $conceptos->getConcepto();
                for ($i = 0;
                        $i < count($arreglo);
                        $i++) {
                    $subTotal += $difference;

                    error_log("Modificando: " . $arreglo[$i]->getNoIdentificacion());
                    $importe = $arreglo[$i]->getImporte() + $difference;
                    $cantidad = round($importe / $arreglo[$i]->getValorUnitario(), 4);
                    $arreglo[$i]->setImporte(number_format($importe, 2, '.', ''));
                    $arreglo[$i]->setCantidad($cantidad);
                    break;
                }
            }
            error_log("SubTotal: " . number_format($subTotal, 2, '.', ''));
            $this->comprobante->setSubTotal(number_format($subTotal, 2, '.', ''));
            $this->comprobante->setConceptos($conceptos);
        }
    }

//conceptos

    /**
     * Recupera el sumarizado de impuestos asociados a la factura.
     * Crea el nodo Impuestos y el nodo Traslados. En el caso de Omicrom, el nodo de Retenciones no existe.
     */
    private function impuestosDescuento() {

        $impuestos = new cfdi40\Comprobante40\Impuestos();
        $traslados = new cfdi40\Comprobante40\Impuestos\Traslados();
        $retens = new cfdi40\Comprobante40\Impuestos\Retenciones();
        /* SUM( ROUND(( fcd.cantidad * (fcd.preciou + IF(cli.desgloseIEPS ='S',0,factorieps)) - Impdesc), 2 ) ) base_iva,
          SUM( ROUND((( fcd.cantidad * (fcd.preciou + IF(cli.desgloseIEPS ='S',0,factorieps))-Impdesc)* fcd.factoriva), 2 ) ) tax_iva, */
        $sql = "
            SELECT 
               fcd.factoriva,
               fcd.factorieps,
               SUM( ROUND(( fcd.cantidad * (fcd.preciou + IF(cli.desgloseIEPS = 'S',0,fcd.factorieps))) - Impdesc, 2 ) ) base_iva,
               SUM( IF( cli.desgloseIEPS = 'S', ROUND( fcd.cantidad, 2 ), 0.00 ) ) base_ieps ,
               SUM( ROUND((( fcd.cantidad * (fcd.preciou) - Impdesc)* fcd.factoriva), 2 ) ) tax_iva,
               SUM( ROUND( ((fcd.cantidad * (fcd.precio + factorieps)) - (fcd.descuento/1+fcd.factoriva)) * fcd.factoriva, 2 ) ) descTt,
               SUM( IF( cli.desgloseIEPS = 'S', ROUND( fcd.cantidad * fcd.factorieps, 2 ), 0.00 ) ) tax_ieps,
               SUM( ROUND( fcd.cantidad * fcd.preciou * fcd.factorisr_retenido, 2 ) ) tax_isr_retenido,
               SUM( ROUND( fcd.cantidad * fcd.preciou * fcd.factoriva_retenido, 2 ) ) tax_iva_retenido,
               fcd.factoriva_retenido,SUM(fcd.cantidad) cnt,fcd.preciou,SUM(fcd.descuento) descuento
            FROM (
                   SELECT 
                       fcd.id folio,
                       CAST( fcd.iva AS DECIMAL( 10, 6 ) ) factoriva,
                       CAST( fcd.ieps AS DECIMAL( 10, 6 ) ) factorieps,
                       ROUND( (  fcd.preciob-fcd.ieps )/( 1+fcd.iva ), 4 ) preciou,
                       ROUND( ( fcd.importe/fcd.preciob ), 4 ) cantidad,
                       CAST( fcd.iva_retenido AS DECIMAL( 10, 6 ) ) factoriva_retenido,
                       fcd.importe - fcd.descuento importeDesc,
                       fcd.importe,fcd.precio,
                       ROUND(fcd.descuento / (1 + fcd.iva),2) Impdesc,
                       ((fcd.descuento/1+fcd.iva) * (fcd.iva)) ImpIva,
                       fcd.preciob,fcd.descuento,(fcd.descuento / 1 + fcd.iva) DescImp,
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
        if (($query = $this->mysqlConnection->query($sql))) {
            $TotalIeps = 0;
            while (($rs = $query->fetch_assoc())) {
                if ($this->totalIvaImpuesto > 0) {
                    error_log("Sumatoria de Base iva" . $this->totalIvaImpuesto);
                    if ($importe_iva <> $this->totalIvaImpuesto) {
                        error_log("Entra a dif");
                        $total_traslado += $rs['tax_iva'] + $rs['tax_ieps'];
                        $SegundoCambio = $this->totalIvaImpuesto;
                        $this->totalIvaImpuesto = 0.00;
                    } else {
                        error_log("No entra a dif");
                        $total_traslado += $rs['tax_iva'] + $rs['tax_ieps'];
                    }
                }
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
                    $TotalIeps += number_format($rs['tax_ieps'], 2, '.', '');
                }

                $tt = $rs["tax_iva_retenido"] > 0 ? $rs["tax_iva_retenido"] : 0;
                $importe_Retenido += $rs["tax_isr_retenido"] + $tt;
                $BaseIva += $rs["base_iva"];
            }

            if ($importe_Retenido > 0) {
                $R_impuesto = new cfdi40\Comprobante40\Impuestos\Retenciones\Retencion();
                $R_impuesto->setImpuesto('002'); // cambia 001 por 002
                $R_impuesto->setImporte(number_format($importe_Retenido, 2, '.', ''));
                $retens->addRetencion($R_impuesto);
            }
            $SubTotalCalculado = $SegundoCambio;
//            $dif = $this->diferencia > 0.01 ? 0.01 : $this->diferencia;
//            error_log("Diferencia " . $dif);
            $SubTotalCalculadoIva = $this->totalIvaImporte;
            //$SubTotalCalculadoIva = ($SubTotalCalculado * 0.16) + $dif;   /* Controlamos diferencia */
            error_log("Calculado SQL " . $BaseIva);
            error_log("Calculado con objeto" . $SubTotalCalculado);
            error_log("Calculado con objeto importe IVA " . $this->totalIvaImporte);
            error_log("Calculado con objeto IVA " . ($SubTotalCalculado * 0.16));
            error_log("Calculado con objeto Recalculado IVA " . $SubTotalCalculadoIva);
            error_log($SubTotalCalculado . " <> " . $BaseIva);
            if ($SubTotalCalculado <> $BaseIva || true) {
                error_log("Recalculamos todo Importe de impuestos");
                $iva = new cfdi40\Comprobante40\Impuestos\Traslados\Traslado();
                $iva->setImporte(number_format($SubTotalCalculadoIva, 2, '.', ''));
                $iva->setImpuesto('002');
                $iva->setTasaOCuota($factor_iva);
                $iva->setTipoFactor('Tasa');
                $iva->setBase(number_format($SubTotalCalculado, 2, '.', ''));
                $total_traslado = $SubTotalCalculadoIva;
                $importe_iva = number_format($SubTotalCalculadoIva, 2, ".", "");
            } else {
                $iva = new cfdi40\Comprobante40\Impuestos\Traslados\Traslado();
                $iva->setImporte(number_format($importe_iva, 2, '.', ''));
                $iva->setImpuesto('002');
                $iva->setTasaOCuota($factor_iva);
                $iva->setTipoFactor('Tasa');
                $iva->setBase(number_format($BaseIva, 2, '.', ''));
            }
            $traslados->addTraslado($iva);
            $impuestos->setTraslados($traslados);
            if ($importe_Retenido > 0) {
                $impuestos->setTotalImpuestosRetenidos(number_format($importe_Retenido, 2, '.', ''));
                $impuestos->setRetenciones($retens);
                $TotalF = $this->comprobante->getTotal();
                $TotalF -= number_format($importe_Retenido, 2, '.', '');
//$this->comprobante->setTotal($TotalF);
            }
            $Total1 = $importe_iva + $TotalIeps + $this->comprobante->getSubTotal() - $this->comprobante->getDescuento();
            $importe_iva += $TotalIeps;
            $this->comprobante->setTotal($Total1);
            $impuestos->setTotalImpuestosTrasladados(number_format($importe_iva, 2, '.', ''));
            $this->comprobante->setImpuestos($impuestos);
        }
    }

//impuestos

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
     * @param cfdi40\Comprobante $Comprobante Objeto Comprobante
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
