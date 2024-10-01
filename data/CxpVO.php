<?php

/**
 * Description of CxpVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class CxpVO {

    private $id;
    private $proveedor;
    private $referencia;
    private $fecha;
    private $fechav;
    private $tm;
    private $concepto;
    private $cantidad;
    private $importe;
    private $numpago;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getProveedor() {
        return $this->proveedor;
    }

    function getReferencia() {
        return $this->referencia;
    }

    function getFecha() {
        return $this->fecha;
    }

    function getFechav() {
        return $this->fechav === "" ? $this->fecha : $this->fechav;
    }

    function getTm() {
        return $this->tm;
    }

    function getConcepto() {
        return $this->concepto;
    }

    function getCantidad() {
        return $this->cantidad === "" ? 0 : $this->cantidad;
    }

    function getImporte() {
        return $this->importe;
    }

    function getNumpago() {
        return $this->numpago;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setProveedor($proveedor) {
        $this->proveedor = $proveedor;
    }

    function setReferencia($referencia) {
        $this->referencia = $referencia;
    }

    function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    function setFechav($fechav) {
        $this->fechav = $fechav;
    }

    function setTm($tm) {
        $this->tm = $tm;
    }

    function setConcepto($concepto) {
        $this->concepto = $concepto;
    }

    function setCantidad($cantidad) {
        $this->cantidad = $cantidad;
    }

    function setImporte($importe) {
        $this->importe = $importe;
    }

    function setNumpago($numpago) {
        $this->numpago = $numpago;
    }

}
