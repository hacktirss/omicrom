<?php

/**
 * Description of PagoseVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class PagoseVO {

    private $id;
    private $idPago;
    private $factura = 0;
    private $referencia = 0;
    private $importe = 0;
    private $tipo = 0;
    private $imp = 0;
    private $iva = 0;
    private $ieps = 0;
    private $porcentaje = 0;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getIdPago() {
        return $this->idPago;
    }

    function getFactura() {
        return $this->factura;
    }

    function getImporte() {
        return $this->importe;
    }

    function getImp() {
        return $this->imp;
    }

    function getIva() {
        return $this->iva;
    }

    function getIeps() {
        return $this->ieps;
    }

    function getPorcentaje() {
        return $this->porcentaje;
    }

    function setImp($imp) {
        $this->imp = $imp;
    }

    function setIva($iva) {
        $this->iva = $iva;
    }

    function setIeps($ieps) {
        $this->ieps = $ieps;
    }

    function setPorcentaje($porcentaje) {
        $this->porcentaje = $porcentaje;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setIdPago($idPago) {
        $this->idPago = $idPago;
    }

    function setFactura($factura) {
        $this->factura = $factura;
    }

    function setImporte($importe) {
        $this->importe = $importe;
    }

    function getReferencia() {
        return $this->referencia;
    }

    function setReferencia($referencia) {
        $this->referencia = $referencia;
    }

    function getTipo() {
        return $this->tipo;
    }

    function setTipo($tipo) {
        $this->tipo = $tipo;
    }

}
