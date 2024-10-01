<?php

/*
 * Comprobante33DAO
 * GlobalFAE®
 * © 2018, Detisa 
 * http://www.detisa.com.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since feb 2018
 */
namespace com\detisa\cfdi\factory;

use com\softcoatl\cfdi\Comprobante;

interface ComprobanteFactoryIface {

    public function createComprobante(array $rs): Comprobante;
    public function createComprobanteCfdiRelacionados(): Comprobante\CfdiRelacionados;
    public function createComprobanteCfdiRelacionadosCfdiRelacionado(array $rs): Comprobante\CfdiRelacionados\CfdiRelacionado;
    public function createComprobanteEmisor(array $rs): Comprobante\Emisor;
    public function createComprobanteReceptor(array $rs): Comprobante\Receptor;
    public function createComprobanteReceptorGenerico(array $rs): Comprobante\Receptor;
    public function createComprobanteConceptos(): Comprobante\Conceptos;
    public function createComprobanteConceptosConcepto(array $rs): Comprobante\Conceptos\Concepto;
    public function createComprobanteConceptosConceptoImpuestos(): Comprobante\Conceptos\Concepto\Impuestos;
    public function createComprobanteConceptosConceptoImpuestosTraslados(): Comprobante\Conceptos\Concepto\Impuestos\Traslados;
    public function createComprobanteConceptosConceptoImpuestosTrasladosTraslado(array $rs);
    public function createComprobanteConceptosConceptoImpuestosRetenciones(): Comprobante\Conceptos\Concepto\Impuestos\Retenciones;
    public function createComprobanteConceptosConceptoImpuestosRetencionesRetencion(array $rs);
    public function createComprobanteImpuestos(): Comprobante\Impuestos;
    public function createComprobanteImpuestosTraslados(): Comprobante\Impuestos\Traslados;
    public function createComprobanteImpuestosTrasladosTraslado(array $rs);
    public function createComprobanteImpuestosRetenciones(): Comprobante\Impuestos\Retenciones;
    public function createComprobanteImpuestosRetencionesRetencion(array $rs);
    public function createComprobanteComplemento(): Comprobante\Complemento;
    public function createComprobanteAddenda(): Comprobante\Addenda;
}
