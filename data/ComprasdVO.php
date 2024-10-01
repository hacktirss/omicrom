<?php

/**
 * Description of ComprasdVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class ComprasdVO {

    private $id;
    private $idnvo;
    private $producto;
    private $cantidad;
    private $costo;
    private $descuento;
    private $adicional;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getIdnvo() {
        return $this->idnvo;
    }

    function getProducto() {
        return $this->producto;
    }

    function getCantidad() {
        return $this->cantidad;
    }

    function getCosto() {
        return $this->costo;
    }

    function getDescuento() {
        return $this->descuento == null ? 0 : $this->descuento;
    }

    function getAdicional() {
        return $this->adicional == null ? 0 : $this->adicional;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setIdnvo($idnvo) {
        $this->idnvo = $idnvo;
    }

    function setProducto($producto) {
        $this->producto = $producto;
    }

    function setCantidad($cantidad) {
        $this->cantidad = $cantidad;
    }

    function setCosto($costo) {
        $this->costo = $costo;
    }

    function setDescuento($descuento) {
        $this->descuento = $descuento;
    }

    function setAdicional($adicional) {
        $this->adicional = $adicional;
    }

}
