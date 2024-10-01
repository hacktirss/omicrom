<?php

/**
 * Description of PagosdespVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class PagosDespVO {

    private $id;
    private $vendedor = "";
    private $deposito;
    private $fecha;
    private $concepto;
    private $importe;
    private $status = 0;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getVendedor() {
        return $this->vendedor;
    }

    function getFecha() {
        return $this->fecha;
    }

    function getConcepto() {
        return $this->concepto;
    }

    function getImporte() {
        return $this->importe;
    }

    function getStatus() {
        return $this->status;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setVendedor($vendedor) {
        $this->vendedor = $vendedor;
    }

    function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    function setConcepto($concepto) {
        $this->concepto = $concepto;
    }

    function setImporte($importe) {
        $this->importe = $importe;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function getDeposito() {
        return $this->deposito;
    }

    function setDeposito($deposito) {
        $this->deposito = $deposito;
    }

}
