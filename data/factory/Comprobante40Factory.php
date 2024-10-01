<?php

/*
 * Comprobante40DAO
 * GlobalFAE®
 * © 2018, Detisa 
 * http://www.detisa.com.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since feb 2018
 */

namespace com\detisa\cfdi\factory;

require_once ("com/softcoatl/cfdi/v40/schema/Comprobante40.php");
require_once ("ComprobanteFactoryIface.php");
require_once ("com/softcoatl/cfdi/CImpuestos.php");

use com\softcoatl\cfdi\v40\schema\Comprobante40;
use com\softcoatl\cfdi\Comprobante;
use com\softcoatl\cfdi\CImpuestos;

class Comprobante40Factory implements ComprobanteFactoryIface {

    public function createComprobante(array $rs): Comprobante {

        $comprobante = new Comprobante40();
        $comprobante->setFolio($rs["Folio"]);
        $comprobante->setSerie($rs["Serie"]);
        $comprobante->setFecha($rs["Fecha"]);
        $comprobante->setTipoDeComprobante($rs["TipoDeComprobante"]);
        $comprobante->setVersion($rs["Version"]);
        $comprobante->setFormaPago($rs["FormaPago"]);
        $comprobante->setMetodoPago($rs["MetodoPago"]);
        $comprobante->setCondicionesDePago($rs["CondicionesDePago"]);
        $comprobante->setMoneda($rs["Moneda"]);
        if ($rs["Moneda"] === "MXN") {
            $comprobante->setTipoCambio("1");
        } else if ($rs["Moneda"] !== "XXX") {
            $comprobante->setTipoCambio($rs["TipoCambio"]);
        }
        if ($rs["Descuento"] > 0) {
            $comprobante->setDescuento($rs["Descuento"]);
            $comprobante->setTotal(number_format($rs["Total"] - $rs["Descuento"], 2));
        } else {
            $comprobante->setTotal(number_format($rs["Total"], 2));
        }
        $comprobante->setSubTotal($rs["SubTotal"]);
        $comprobante->setExportacion("01");
        $comprobante->setLugarExpedicion($rs["LugarExpedicion"]);
        return $comprobante;
    }

    public function createComprobanteCfdiRelacionados(): \com\softcoatl\cfdi\Comprobante\CfdiRelacionados {
        return new Comprobante40\CfdiRelacionados();
    }

    public function createComprobanteCfdiRelacionadosCfdiRelacionado(array $rs): Comprobante\CfdiRelacionados\CfdiRelacionado {
        $cfdiRelacionado = new Comprobante40\CfdiRelacionados\CfdiRelacionado();
        $cfdiRelacionado->setUUID($rs["uuid_relacionado"]);
        return $cfdiRelacionado;
    }

    public function createComprobanteEmisor(array $rs): Comprobante\Emisor {

        $emisor = new Comprobante40\Emisor();
        $emisor->setNombre($rs["Nombre"]);
        $emisor->setRfc($rs["Rfc"]);
        $emisor->setRegimenFiscal($rs["RegimenFiscal"]);
        return $emisor;
    }

    public function createComprobanteReceptor(array $rs): Comprobante\Receptor {

        $receptor = new Comprobante40\Receptor();
        $receptor->setNombre($rs["Nombre"]);
        $receptor->setRfc($rs["Rfc"]);
        $receptor->setRegimenFiscalReceptor($rs["RegimenFiscalReceptor"]);
        $receptor->setDomicilioFiscalReceptor($rs["DomicilioFiscalReceptor"]);
        $receptor->setUsoCFDI($rs["UsoCFDI"]);
        return $receptor;
    }

    public function createComprobanteReceptorGenerico(array $rs): Comprobante\Receptor {

        $receptor = new Comprobante40\Receptor();
        $receptor->setNombre("PUBLICO EN GENERAL");
        $receptor->setDomicilioFiscalReceptor($rs["DomicilioFiscalReceptor"]);
        $receptor->setRegimenFiscalReceptor("616");
        $receptor->setRfc("XAXX010101000");
        $receptor->setUsoCFDI("S01");
        return $receptor;
    }

    public function createComprobanteConceptos(): Comprobante\Conceptos {

        return new Comprobante40\Conceptos();
    }

    public function createComprobanteConceptosConcepto(array $rs): Comprobante\Conceptos\Concepto {

        $concepto = new Comprobante40\Conceptos\Concepto();
        $concepto->setClaveProdServ($rs["ClaveProdServ"]);
        $concepto->setClaveUnidad($rs["ClaveUnidad"]);
        $concepto->setDescripcion($rs["Descripcion"]);
        $concepto->setImporte(number_format($rs["Importe"], 2, ".", ""));
        $concepto->setCantidad(number_format($rs["Cantidad"], 2, ".", ""));
        $concepto->setNoIdentificacion($rs["NoIdentificacion"]);
        $concepto->setValorUnitario(number_format($rs["ValorUnitario"], 4, ".", ""));
        $concepto->setObjetoImp($rs["ObjetoImp"]);
        if ($rs["Descuento"] > 0) {
            $concepto->setDescuento(number_format($rs["Descuento"], 2, ".", ""));
        }
        return $concepto;
    }

    public function createComprobanteConceptosConceptoImpuestos(): Comprobante\Conceptos\Concepto\Impuestos {
        return new Comprobante40\Conceptos\Concepto\Impuestos();
    }

    public function createComprobanteConceptosConceptoImpuestosTraslados(): Comprobante\Conceptos\Concepto\Impuestos\Traslados {
        return new Comprobante40\Conceptos\Concepto\Impuestos\Traslados();
    }

    public function createComprobanteConceptosConceptoImpuestosTrasladosTraslado(array $rs) {

        $traslado = new Comprobante40\Conceptos\Concepto\Impuestos\Traslados\Traslado();
        $traslado->setBase(number_format($rs["Base"], 2, ".", ""));
        $traslado->setImpuesto($rs["Impuesto"]);
        $traslado->setTipoFactor($rs["TipoFactor"]);
        if ($rs["TipoFactor"] !== CImpuestos::EXENTO) {
            $traslado->setTasaOCuota($rs["TasaOCuota"]);
            $traslado->setImporte(number_format($rs["Importe"], 2, ".", ""));
        }
        return $traslado;
    }

    public function createComprobanteConceptosConceptoImpuestosRetenciones(): Comprobante\Conceptos\Concepto\Impuestos\Retenciones {
        return new Comprobante40\Conceptos\Concepto\Impuestos\Retenciones();
    }

    public function createComprobanteConceptosConceptoImpuestosRetencionesRetencion(array $rs) {

        $retencion = new Comprobante40\Conceptos\Concepto\Impuestos\Retenciones\Retencion();
        $retencion->setBase(number_format($rs["Base"], 2, ".", ""));
        $retencion->setImpuesto($rs["Impuesto"]);
        $retencion->setTipoFactor($rs["TipoFactor"]);
        $retencion->setTasaOCuota($rs["TasaOCuota"]);
        $retencion->setImporte(number_format($rs["Importe"], 2, ".", ""));
        return $retencion;
    }

    public function createComprobanteImpuestos(): Comprobante\Impuestos {
        return new Comprobante40\Impuestos();
    }

    public function createComprobanteImpuestosTraslados(): Comprobante\Impuestos\Traslados {
        return new Comprobante40\Impuestos\Traslados();
    }

    public function createComprobanteImpuestosTrasladosTraslado(array $rs) {

        $traslado = new Comprobante40\Impuestos\Traslados\Traslado();
        $traslado->setBase(number_format($rs["Base"], 2, ".", ""));
        $traslado->setImpuesto($rs["Impuesto"]);
        $traslado->setTipoFactor($rs["TipoFactor"]);
        if ($rs["TipoFactor"] !== CImpuestos::EXENTO) {
            $traslado->setImporte(number_format($rs["Importe"], 2, ".", ""));
            $traslado->setTasaOCuota($rs["TasaOCuota"]);
        }
        return $traslado;
    }

    public function createComprobanteImpuestosRetenciones(): Comprobante\Impuestos\Retenciones {
        return new Comprobante40\Impuestos\Retenciones();
    }

    public function createComprobanteImpuestosRetencionesRetencion(array $rs) {

        $retencion = new Comprobante40\Impuestos\Retenciones\Retencion();
        $retencion->setImporte(number_format($rs["Importe"], 2, ".", ""));
        $retencion->setImpuesto($rs["Impuesto"]);
        return $retencion;
    }

    public function createComprobanteAddenda(): Comprobante\Addenda {
        return new Comprobante40\Addenda();
    }

    public function createComprobanteComplemento(): Comprobante\Complemento {
        return new Comprobante40\Complemento();
    }

}
