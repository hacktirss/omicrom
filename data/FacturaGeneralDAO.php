<?php

/*
 * FacturaGeneralDAO Objeto DAO.
 * Recupera la información referente a la factura con fc.id = $folio
 * Crea un objeto de tipo Comprobante y los nodos requeridos.
 * La información vaciada en Comprobante se encuentra contenida en las tablas cia, cli, fc, fcd, rm, vtaditivos.
 * Este módulo está escrito de acuerdo a la estructura de base de datos, reglas y definiciones del sistema Omicrom®, Sistema de Control Volumétrico,
 * y cumple con las especificaciones definidas por la autoridad tributaria SAT.
 * 
 * omicrom®
 * © 2017, Detisa 
 * http://www.detisa.com.mx
 * @author Ayala Gonzalez Alejandro, Softcoatl
 * @version 1.0
 * @since jul 2017
 */

namespace com\detisa\omicrom;

require_once ('mysqlUtils.php');
require_once ('com/softcoatl/cfdi/v40/schema/Comprobante40.php');
require_once ('com/softcoatl/cfdi/addenda/detisa/Observaciones.php');
require_once ('pdf/PDFTransformer.php');
require_once ('FacturaDAO.php');

use com\softcoatl\cfdi\v40\schema as cfdi40;
use com\softcoatl\cfdi\addenda as Adenda;

class FacturaGeneralDAO extends FacturaDAO {

    private $folio;
    /* @var $mysqlConnection \mysqli */
    private $mysqlConnection;
    private $Cliente;

    function __construct($folio) {
        parent::__construct($folio);
        error_log("CFDI40 Cargando CFDI con folio " . $folio);
        $this->receptor();
        $this->InformacionGlobal();
    }

//comprobante

    private function InformacionGlobal() {
        $InfGlobal = new cfdi40\Comprobante40\InformacionGlobal();

        $sql = "SELECT periodo,meses,ano FROM fc JOIN cia ON TRUE WHERE fc.id = " . $this->getFolio();
        if (($query = $this->getMysqlConnection()->query($sql)) && ($rs = $query->fetch_assoc())) {
            if ($rs["ano"] > 2000) {
                $InfGlobal->setAnio($rs["ano"]);
                $InfGlobal->setMeses($rs["meses"]);
                $InfGlobal->setPeriodicidad($rs["periodo"]);
                $this->getComprobante()->setInformacionGlobal($InfGlobal);
            }
        }
    }

    /**
     * Recupera los datos del receptor del CFDI.
     * Crea el nodo Receptor.
     */
    private function receptor() {

        $sqlExp = "SELECT TRIM( cia.codigo ) LugarExpedicion,cli.nombre
                FROM fc LEFT JOIN cli ON fc.cliente=cli.id JOIN cia ON TRUE 
                WHERE fc.id= " . $this->getFolio();
        error_log($sqlExp);
        if (($query = $this->getMysqlConnection()->query($queryExp)) && ($rsExp = $query->fetch_assoc())) {
            $this->Cliente = $rsExp["nombre"];
            /* @var $receptor cfdi40\Comprobante40\Receptor */
            $receptor = new cfdi40\Comprobante40\Receptor();
            $receptor->setNombre($rsExp["nombre"]);
            $receptor->setDomicilioFiscalReceptor($rsExp["LugarExpedicion"]);
            $receptor->setRegimenFiscalReceptor("616");
            $receptor->setRfc("XAXX010101000");
            $receptor->setUsoCFDI("S01");
            $this->getComprobante()->setReceptor($receptor);
        }
    }

    /**
     * Recupera el sumarizado de impuestos asociados a la factura.
     * Crea el nodo Impuestos y el nodo Traslados. En el caso de Omicrom, el nodo de Retenciones no existe.
     */
    private function impuestos() {

        $impuestos = new cfdi40\Comprobante40\Impuestos();
        $traslados = new cfdi40\Comprobante40\Impuestos\Traslados();

        $sql = "
            SELECT 
               fcd.factoriva,
               SUM( ROUND( fcd.cantidad * fcd.preciou * fcd.factoriva, 2 ) ) tax_iva,
               SUM( ROUND( fcd.cantidad * fcd.preciou , 2 ) )  base_iva
            FROM (
                   SELECT 
                       fcd.id folio,
                       CAST( fcd.iva AS DECIMAL( 10, 6 ) ) factoriva,
                       ROUND( (  fcd.preciob-fcd.ieps )/( 1+fcd.iva ), 4 ) preciou,
                       ROUND( ( fcd.importe/fcd.preciob ), 4 ) cantidad,
                       fcd.preciob,fcd.ieps,fcd.iva,fcd.importe
                   FROM fcd 
                   WHERE fcd.id = " . $this->getFolio() . "
            ) fcd
            JOIN fc ON fcd.folio = fc.id 
            JOIN cli ON cli.id = fc.cliente
            GROUP BY fcd.factoriva
        ";

        $importe_iva = 0.00;
        $importe_ieps = 0.00;
        $total_traslado = 0.00;
        if (($query = $this->getMysqlConnection()->query($sql))) {

            while (($rs = $query->fetch_assoc())) {

                $total_traslado += $rs['tax_iva'];
                $importe_iva += $rs['tax_iva'];

                if ($rs['tax_iva'] > 0) {

                    $iva = new cfdi40\Comprobante40\Impuestos\Traslados\Traslado();
                    $iva->setImporte(number_format($rs['tax_iva'], 2, '.', ''));
                    $iva->setImpuesto('002');
                    $iva->setTasaOCuota($rs['factoriva']);
                    $iva->setTipoFactor('Tasa');
                    $iva->setBase($rs['base_iva']);
                    $traslados->addTraslado($iva);
                }
            }
        }

        $sql = "
            SELECT 
               fcd.factorieps,
               SUM( IF( variables.incorporaieps = 'Si', 0.00, ROUND( fcd.cantidad * fcd.factorieps, 2 ) ) ) tax_ieps,
               SUM( ROUND( fcd.cantidad, 2 )) base_ieps 
            FROM (
                   SELECT 
                       fcd.id folio,
                       CAST( fcd.ieps AS DECIMAL( 10, 6 ) ) factorieps,
                       ROUND( ( fcd.importe/fcd.preciob ), 4 ) cantidad
                   FROM fcd 
                   WHERE fcd.id = " . $this->folio . "
            ) fcd
            JOIN variables ON TRUE
            JOIN fc ON fcd.folio = fc.id 
            JOIN cli ON cli.id = fc.cliente
            GROUP BY fcd.factorieps
        ";
        if (($query = $this->getMysqlConnection()->query($sql))) {

            while (($rs = $query->fetch_assoc())) {
                $total_traslado += $rs['tax_ieps'];
                $importe_ieps += $rs['tax_ieps'];

                if ($rs['tax_ieps'] > 0) {

                    $ieps = new cfdi40\Comprobante40\Impuestos\Traslados\Traslado();
                    $ieps->setImporte(number_format($rs['tax_ieps'], 2, '.', ''));
                    $ieps->setImpuesto('003');
                    $ieps->setTasaOCuota($rs['factorieps']);
                    $ieps->setTipoFactor('Cuota');
                    $ieps->setBase($rs["base_ieps"]);
                    $traslados->addTraslado($ieps);
                }
            }
        }
        $impuestos->setTraslados($traslados);
        $impuestos->setTotalImpuestosTrasladados(number_format($total_traslado, 2, '.', ''));
        $this->getComprobante()->setImpuestos($impuestos);
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
        return $this->getMysqlConnection()->query($sql);
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
        return $this->getMysqlConnection()->query($sql);
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
        return $this->getMysqlConnection()->query($sql);
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
        $stmt = $this->getMysqlConnection()->prepare($sql);

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
