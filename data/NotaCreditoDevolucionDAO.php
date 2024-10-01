<?php

/*
 * NotaCreditoDevolucionDAO Objeto DAO.
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
require_once ('com/softcoatl/cfdi/addenda/detisa/Observaciones.php');

use \com\softcoatl\cfdi\v40\schema as cfdi40;

class NotaCreditoDevolucionDAO extends NotaCreditoDAO {

    function __construct($folio) {

        parent::__construct($folio);
        error_log("Cargando CFDI Devolucion con folio " . $folio);

        $this->folio = $folio;
        $this->comprobante = new cfdi40\Comprobante40();
        $this->mysqlConnection = getConnection();
        //$this->InformacionGlobal();
        $this->conceptos();
    }

    private function InformacionGlobal() {
        $InfGlobal = new cfdi40\Comprobante40\InformacionGlobal();

        $sql = "SELECT periodo,meses,ano FROM fc WHERE id = (SELECT factura FROM nc WHERE id =" . $this->getFolio() . ");";
        if (($query = $this->getMysqlConnection()->query($sql)) && ($rs = $query->fetch_assoc())) {
            $InfGlobal->setAnio($rs["ano"]);
            $InfGlobal->setMeses($rs["meses"]);
            $InfGlobal->setPeriodicidad($rs["periodo"]);
            $this->getComprobante()->setInformacionGlobal($InfGlobal);
        }
    }

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
                        GROUP BY producto, ieps, preciob
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

            while (($rs = $query->fetch_assoc())) {

                $concepto = new cfdi40\Comprobante40\Conceptos\Concepto();
                $concepto->setClaveProdServ("84111506");
                $concepto->setClaveUnidad("ACT");
                $concepto->setDescripcion("Devolucion de Mercancias");
                $concepto->setImporte(number_format($rs['Importe'], 2, '.', ''));
                $concepto->setCantidad("1");
                $concepto->setValorUnitario(number_format($rs['Importe'], 2, '.', ''));
                $rs["factoriva"] > 0 ? $concepto->setObjetoImp("02") : $concepto->setObjetoImp("01");
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

                $impuestos = new cfdi40\Comprobante40\Conceptos\Concepto\Impuestos();
                $impuestos->setTraslados($traslados);
                $concepto->setImpuestos($impuestos);
                $conceptos->addConcepto($concepto);
            }//while

            $this->getComprobante()->setSubTotal(number_format($subTotal, 2, '.', ''));
            $this->getComprobante()->setConceptos($conceptos);
        }
    }

//conceptos
}

//NotaCreditoDevolucionDAO
