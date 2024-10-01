<?php

/*
 * ReciboPagoDAO Objeto DAO.
 * Recupera la informaciÃ³n referente al pago con pagos.id = $folio
 * Crea un objeto de tipo Comprobante y los nodos requeridos.
 * La informaciÃ³n vaciada en Comprobante se encuentra contenida en las tablas cia, cli, pagos, pagose.
 * Este mÃ³dulo estÃ¡ escrito de acuerdo a la estructura de base de datos, reglas y definiciones del sistema OmicromÂ®, Sistema de Control VolumÃ©trico,
 * y cumple con las especificaciones definidas por la autoridad tributaria SAT.
 * 
 * omicromÂ®
 * Â© 2017, Detisa 
 * http://www.detisa.com.mx
 * @author Rolando Esquivel VillafaÃ±a, Softcoatl
 * @version 1.0
 * @since jul 2017
 */

namespace com\detisa\omicrom;

require_once ('mysqlUtils.php');
require_once ('ClientesDAO.php');
require_once ('CiaDAO.php');
require_once ('com/softcoatl/cfdi/v40/schema/Comprobante40.php');
require_once ('com/softcoatl/cfdi/addenda/detisa/Observaciones.php');

use \com\softcoatl\cfdi\v40\schema as cfdi40;
use com\softcoatl\cfdi\addenda as addenda;

class ReciboAnticipoDAO {

    private $folio;
    /* @var $comprobante cfdi40\Comprobante40 */
    private $comprobante;
    /* @var $mysqlConnection \mysqli */
    private $mysqlConnection;
    private $total;
    private $subTotal;
    private $iva;
    private $Piva;

    function __construct($folio) {

        error_log("Cargando el Pago con folio " . $folio);
        $CiaDAO = new \CiaDAO;
        $CiaVO = new \CiaVO();
        $CiaVO = $CiaDAO->retrieve(true);
        $this->Piva = number_format($CiaVO->getIva() / 100, 6);
        $this->folio = $folio;
        $this->comprobante = new cfdi40\Comprobante40();
        $this->mysqlConnection = getConnection();
        $this->comprobante();
        $this->emisor();
        $this->receptor();
        // $this->cfdiRelacionados();
        $this->conceptos();
        //$this->pagos();
        //$this->observaciones();
        $this->impuestos();
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
     * Recupera la informaciÃ³n relativa al pago.
     * Crea el objeto Comprobante
     */
    private function comprobante() {

        /* @var $emisor cfdi40\Comprobante40 */
        $this->comprobante = new cfdi40\Comprobante40();
        $sql = "SELECT 
                    pagos.id Folio
                    , DATE_FORMAT('" . \com\softcoatl\utils\HTTPUtils::getSessionValue("FechaAnticipoRg") . "', '%Y-%m-%dT%H:%i:%s') Fecha
                    , formapago
                    , importe
                    , TRIM( cia.codigo ) LugarExpedicion
              FROM pagos JOIN cia ON TRUE
              WHERE pagos.id = " . $this->folio;

        $Sql2 = "SELECT valor FROM omicrom.variables_corporativo where llave like '%series_anticipos%';";
        if (($query2 = $this->mysqlConnection->query($Sql2)) && ($rs2 = $query2->fetch_assoc())) {
            $SERIE = $rs2["valor"];
            $Upd = "UPDATE pagos SET serie = '$SERIE' WHERE id=" . $this->folio;
            $this->mysqlConnection->query($Upd);
            if (($query = $this->mysqlConnection->query($sql)) && ($rs = $query->fetch_assoc())) {

                $this->total = $rs['importe'];

                $this->subTotal = round($this->total / (1 + $this->Piva), 2);
                $this->iva = $this->total - $this->subTotal;
                $this->comprobante->setSerie($SERIE);
                $this->comprobante->setFolio($rs['Folio']);
                $this->comprobante->setFecha($rs['Fecha']);
                $this->comprobante->setTipoDeComprobante("I");
                $this->comprobante->setVersion("4.0");
                $this->comprobante->setMoneda("MXN");
                $this->comprobante->setSubTotal($this->subTotal); // Pendiente
                $this->comprobante->setTotal($rs['importe']);    // Pendiente
                $this->comprobante->setLugarExpedicion($rs['LugarExpedicion']);
                $this->comprobante->setMetodoPago('PUE');
                $this->comprobante->setFormaPago($rs['formapago']);
                $this->comprobante->setExportacion("01");
            }//if
        } else {
            error_log("Falta serie de anticipo llave :  series_anticipos ");
        }
    }

//comprobante

    /**
     * Recupera los datos de la estaciÃ³n de servicio.
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
        error_log("Colocando el Receptor del pago: " . $this->folio);
        $Sql = "SELECT usocfdi FROM pagos WHERE pagos.id = " . $this->folio . " ";
        if (($query = $this->mysqlConnection->query($Sql)) && ($rs = $query->fetch_assoc())) {
            /* @var $emisor cfdi40\Comprobante40\Receptor */
            $receptor = new cfdi40\Comprobante40\Receptor();
            $Client = \ClientesDAO::getClientData($this->folio, "pagos");
            $receptor->setNombre($Client->getNombre());
            $receptor->setRfc($Client->getRfc());
            $receptor->setUsoCFDI($rs["usocfdi"]);
            $receptor->setDomicilioFiscalReceptor($Client->getCodigo());
            $receptor->setRegimenFiscalReceptor($Client->getRegimenFiscal());
            $this->comprobante->setReceptor($receptor);
        }
    }

//receptor

    /**
     * Crea el nodo Conceptos. 
     * De acuerdo a lo definido por el SAT, este tipo de comprobantes consta de un solo Concepto con valores fijos.
     */
    private function conceptos() {

        $conceptos = new cfdi40\Comprobante40\Conceptos();
        $concepto = new cfdi40\Comprobante40\Conceptos\Concepto();
        $concepto->setClaveProdServ('84111506');
        $concepto->setCantidad('1');
        $concepto->setClaveUnidad('ACT');
        $concepto->setDescripcion('Anticipo del bien o servicio');
        $concepto->setImporte($this->subTotal);
        $concepto->setValorUnitario($this->subTotal);
        $concepto->setObjetoImp("02");

        $traslados = new cfdi40\Comprobante40\Conceptos\Concepto\Impuestos\Traslados();

        $iva = new cfdi40\Comprobante40\Conceptos\Concepto\Impuestos\Traslados\Traslado();

        $iva->setBase(number_format($this->subTotal, 2, '.', ''));
        $iva->setImpuesto('002');
        $iva->setTasaOCuota($this->Piva);
        $iva->setTipoFactor('Tasa');
        $iva->setImporte(number_format($this->iva, 2, '.', ''));
        $traslados->addTraslado($iva);

        $impuestos = new cfdi40\Comprobante40\Conceptos\Concepto\Impuestos();
        $impuestos->setTraslados($traslados);
        $concepto->setImpuestos($impuestos);

        $conceptos->addConcepto($concepto);
        $this->comprobante->setConceptos($conceptos);
    }

//retrieveConceptosFactura

    /**
     * Recupera el sumarizado de impuestos asociados a la factura.
     * Crea el nodo Impuestos y el nodo Traslados. En el caso de Omicrom, el nodo de Retenciones no existe.
     */
    private function impuestos() {

        $impuestos = new cfdi40\Comprobante40\Impuestos();
        $traslados = new cfdi40\Comprobante40\Impuestos\Traslados();
        $total_traslado = $this->iva;

        $iva = new cfdi40\Comprobante40\Impuestos\Traslados\Traslado();
        $iva->setImpuesto('002');
        $iva->setBase(number_format($this->subTotal, 2, '.', ''));
        $iva->setImpuesto('002');
        $iva->setTasaOCuota($this->Piva);
        $iva->setTipoFactor('Tasa');
        $iva->setImporte(number_format($this->iva, 2, '.', ''));
        $traslados->addTraslado($iva);

        $impuestos->setTraslados($traslados);
        $impuestos->setTotalImpuestosTrasladados(round($total_traslado, 2));

        $this->comprobante->setImpuestos($impuestos);
    }

//impuestos

    /**
     * Crea el nodo DoctosRelacionados, requerido por el Complemento de RecepciÃ³n de Pagos.
     * En este nodo se detallan las facturas que ampara el pago y los importes pagados de cada CFDI.
     * @return array

      private function doctosRelacionados() {

      $doctosRelacionados = array();
      $sql = "
      SELECT
      fc.folio Folio,
      fc.uuid IdDocumento,
      pagose.importe ImpPagado,
      fc.total-IFNULL( cxc.importe, 0 )-IFNULL( nc.importe, 0 ) ImpSaldoAnt,
      fc.total-IFNULL( cxc.importe, 0 )-IFNULL( nc.importe, 0 )-pagose.importe ImpSaldoInsoluto,
      IFNULL( cxc.parcialidades, 0 )+1 NumParcialidad
      FROM pagose
      JOIN fc ON fc.id = pagose.factura
      LEFT JOIN (
      SELECT factura, COUNT( * ) parcialidades, SUM( importe ) importe FROM (
      SELECT * FROM cxc WHERE tm = 'H' AND concepto LIKE '%factura%'
      UNION ALL
      SELECT * FROM cxch WHERE tm = 'H' AND concepto LIKE '%factura%') cxch
      WHERE recibo < " . $this->folio . " GROUP BY factura ) cxc ON fc.id = cxc.factura
      LEFT JOIN (
      SELECT factura, SUM( total ) importe FROM nc WHERE status='Cerrada' GROUP BY factura ) nc ON pagose.factura = nc.factura
      WHERE pagose.id = " . $this->folio;

      if (($query = $this->mysqlConnection->query($sql))) {

      while (($rs = $query->fetch_assoc())) {

      $doctoRelacionado = new cfdi33\complemento\Pagos\Pago\DoctoRelacionado();
      $doctoRelacionado->setFolio($rs['Folio']);
      $doctoRelacionado->setIdDocumento($rs['IdDocumento']);
      $doctoRelacionado->setImpPagado(number_format($rs['ImpPagado'], 2, '.', ''));
      $doctoRelacionado->setImpSaldoAnt(number_format($rs['ImpSaldoAnt'], 2, '.', ''));
      $doctoRelacionado->setImpSaldoInsoluto(number_format($rs['ImpSaldoInsoluto'], 2, '.', ''));
      $doctoRelacionado->setMonedaDR("MXN");
      $doctoRelacionado->setMetodoDePagoDR("PPD");
      $doctoRelacionado->setNumParcialidad($rs['NumParcialidad']);
      array_push($doctosRelacionados, $doctoRelacionado);
      }
      return $doctosRelacionados;
      }
      }//doctosRelacionados
     */

    /**
     * Recupera el valor del campo concepto en pagos.
     * Si existe, crea la addenda Observaciones, definida por Detisa
     */
    private function observaciones() {

        $observaciones = new addenda\Observaciones();
        $sql = "
            SELECT pagos.concepto Observacion
            FROM pagos
            WHERE pagos.id = " . $this->folio
        ;

        if (($query = $this->mysqlConnection->query($sql)) && ($rs = $query->fetch_assoc())) {
            $observaciones->addObservaciones(new cfdi40\Comprobante40\addenda\Observaciones\Observacion($observacion = $rs['Observacion']));
            $this->comprobante->addAddenda($observaciones);
        }
    }

//observaciones
}

//ReciboPagoDAO