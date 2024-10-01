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
require_once ('NotaCreditoDAO.php');
//require_once ('cfdi33/addenda/Observaciones.php');

use \com\softcoatl\cfdi\v40\schema as cfdi40;

class NotaCreditoAnticipoDAO extends NotaCreditoDAO {

    private $subTotal;

    function __construct($folio) {

        parent::__construct($folio);
        error_log("Cargando Nota de Credito con Folio: " . $folio);

        $this->conceptos();
        $this->impuestos();
    }

    /**
     * Recupera los conceptos asociados a la nota de crédito.
     * Crea el nodo Conceptos, el arreglo de nodos Concepto y los nodos de Impuesto asociados a cada Concepto.
     */
    public function conceptos() {
        error_log("Llenando Conceptos de la nota de credito" . $this->getFolio());

        $conceptos = new cfdi40\Comprobante40\Conceptos();

        $sql = "
            SELECT 
                    nc.total
                    , '84111506' cveprod
                    , nc.cantidad
                    , 'ACT' cveunidad
                    , 'Aplicacion de anticipo' descripcion
                    , round(total/1.16,2) valorunitario
                    , round(total/1.16,2) base_iva
                    , '0.160000' factoriva
                    , round(total - round(total/1.16,2),2) tax_iva
            FROM nc  LEFT JOIN ncd ON nc.id = ncd.id where nc.id = " . $this->getFolio();

        if (($query = $this->getMysqlConnection()->query($sql))) {

            while (($rs = $query->fetch_assoc())) {
                error_log("Llenando Conceptos: " . $rs['cveprod']);

                $concepto = new cfdi40\Comprobante40\Conceptos\Concepto();
                $concepto->setClaveProdServ($rs['cveprod']);
                $concepto->setClaveUnidad($rs['cveunidad']);
                $concepto->setDescripcion($rs['descripcion']);
                $concepto->setImporte(number_format($rs['valorunitario'], 2, '.', ''));
                $concepto->setCantidad(number_format($rs['cantidad'], 4, '.', ''));
                $concepto->setValorUnitario(number_format($rs['valorunitario'], 4, '.', ''));
                $concepto->setObjetoImp("02");

                $this->subTotal += $rs['valorunitario'];

                $traslados = new cfdi40\Comprobante40\Conceptos\Concepto\Impuestos\Traslados();

                $iva = new cfdi40\Comprobante40\Conceptos\Concepto\Impuestos\Traslados\Traslado();
                $iva->setBase(number_format($rs['base_iva'], 2, '.', ''));
                $iva->setImpuesto('002');
                $iva->setTasaOCuota($rs['factoriva']);
                $iva->setTipoFactor('Tasa');
                $iva->setImporte(number_format($rs['tax_iva'], 2, '.', ''));

                $traslados->addTraslado($iva);

                $impuestos = new cfdi40\Comprobante40\Conceptos\Concepto\Impuestos();
                $impuestos->setTraslados($traslados);
                $concepto->setImpuestos($impuestos);
                $conceptos->addConcepto($concepto);
            }//while

            $this->getComprobante()->setSubTotal(number_format($this->subTotal, 2, '.', ''));
            $this->getComprobante()->setConceptos($conceptos);
        }
    }

//conceptos

    /**
     * Recupera el sumarizado de impuestos asociados a la nota de crédito.
     * Crea el nodo Impuestos y el nodo Traslados. En el caso de Omicrom, el nodo de Retenciones no existe.
     */
    public function impuestos() {

        $impuestos = new cfdi40\Comprobante40\Impuestos();
        $traslados = new cfdi40\Comprobante40\Impuestos\Traslados();

        $sql = "
            SELECT 
               0.160000 factoriva
               , 0 factorieps
               , round(total - round(total/1.16,2),2) tax_iva
               , 0 tax_ieps
            FROM nc  LEFT JOIN ncd ON nc.id = ncd.id where nc.id = " . $this->getFolio();

        $importe_iva = 0.00;
        $total_traslado = 0.00;
        $factor_iva = 0.000000;

        if (($query = $this->getMysqlConnection()->query($sql))) {

            while (($rs = $query->fetch_assoc())) {

                $total_traslado += $rs['tax_iva'] + $rs['tax_ieps'];
                $importe_iva += $rs['tax_iva'];
                $factor_iva = $rs['factoriva'];
            }
            $iva = new cfdi40\Comprobante40\Impuestos\Traslados\Traslado();
            $iva->setBase(number_format($this->subTotal, 2, '.', ''));
            $iva->setImpuesto('002');
            $iva->setTasaOCuota($factor_iva);
            $iva->setTipoFactor('Tasa');
            $iva->setImporte(number_format($importe_iva, 2, '.', ''));
            $traslados->addTraslado($iva);

            $impuestos->setTraslados($traslados);
            //$impuestos->setTotalImpuestosRetenidos(number_format(0, 2, '.', ''));
            $impuestos->setTotalImpuestosTrasladados(number_format($total_traslado, 2, '.', ''));

            $this->getComprobante()->setImpuestos($impuestos);
        }
    }

//impuestos
}

//NotaCreditoDAO