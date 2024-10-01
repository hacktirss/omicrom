<?php

/**
 * Description of CxcVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class CxchVO {

    private $id;
    private $cliente;
    private $placas;
    private $referencia;
    private $fecha;
    private $hora;
    private $tm;
    private $concepto;
    private $cantidad;
    private $importe;
    private $recibo;
    private $corte;
    private $producto;
    private $rubro;
    private $factura;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getCliente() {
        return $this->cliente;
    }

    function getPlacas() {
        return $this->placas;
    }

    function getReferencia() {
        return $this->referencia;
    }

    function getFecha() {
        return $this->fecha;
    }

    function getHora() {
        return $this->hora;
    }

    function getTm() {
        return $this->tm;
    }

    function getConcepto() {
        return $this->concepto;
    }

    function getCantidad() {
        return $this->cantidad;
    }

    function getImporte() {
        return $this->importe;
    }

    function getRecibo() {
        return $this->recibo;
    }

    function getCorte() {
        return $this->corte;
    }

    function getProducto() {
        return $this->producto;
    }

    function getRubro() {
        return $this->rubro;
    }

    function getFactura() {
        return $this->factura;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setCliente($cliente) {
        $this->cliente = $cliente;
    }

    function setPlacas($placas) {
        $this->placas = $placas;
    }

    function setReferencia($referencia) {
        $this->referencia = $referencia;
    }

    function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    function setHora($hora) {
        $this->hora = $hora;
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

    function setRecibo($recibo) {
        $this->recibo = $recibo;
    }

    function setCorte($corte) {
        $this->corte = $corte;
    }

    function setProducto($producto) {
        $this->producto = $producto;
    }

    function setRubro($rubro) {
        $this->rubro = $rubro;
    }

    function setFactura($factura) {
        $this->factura = $factura;
    }

}
