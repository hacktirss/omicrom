<?php

/**
 * Description of NcdVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class NcdVO {

    private $id;
    private $idnvo;
    private $producto;
    private $cantidad;
    private $precio;
    private $iva;
    private $ieps;
    private $importe;
    private $tipoc;
    private $preciob;

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

    function getPrecio() {
        return $this->precio;
    }

    function getIva() {
        return $this->iva;
    }

    function getIeps() {
        return $this->ieps;
    }

    function getImporte() {
        return $this->importe;
    }

    function getTipoc() {
        return $this->tipoc;
    }

    function getPreciob() {
        return $this->preciob;
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

    function setPrecio($precio) {
        $this->precio = $precio;
    }

    function setIva($iva) {
        $this->iva = $iva;
    }

    function setIeps($ieps) {
        $this->ieps = $ieps;
    }

    function setImporte($importe) {
        $this->importe = $importe;
    }

    function setTipoc($tipoc) {
        $this->tipoc = $tipoc;
    }

    function setPreciob($preciob) {
        $this->preciob = $preciob;
    }

}
