<?php

/**
 * Description of PagosPrvVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class PagosPrvVO {

    private $id;
    private $proveedor;
    private $fecha;
    private $concepto;
    private $importe;
    private $aplicado;
    private $referencia;
    private $status;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getProveedor() {
        return $this->proveedor;
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

    function getAplicado() {
        return $this->aplicado === "" ? 0 : $this->aplicado;
    }

    function getReferencia() {
        return $this->referencia;
    }

    function getStatus() {
        return $this->status;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setProveedor($proveedor) {
        $this->proveedor = $proveedor;
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

    function setAplicado($aplicado) {
        $this->aplicado = $aplicado;
    }

    function setReferencia($referencia) {
        $this->referencia = $referencia;
    }

    function setStatus($status) {
        $this->status = $status;
    }

}
