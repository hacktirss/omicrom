<?php

/**
 * Description of ManVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class ManVO {

    private $id;
    private $posicion;
    private $productos;
    private $activo;
    private $lado;
    private $isla;
    private $isla_pos;
    private $despachador;
    private $man;
    private $inventario;
    private $dispensario;
    private $numventas;
    private $conteoventas;
    private $despachadorsig;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getPosicion() {
        return $this->posicion;
    }

    function getProductos() {
        return $this->productos;
    }

    function getActivo() {
        return $this->activo == null ? "No" : $this->activo;
    }

    function getLado() {
        return $this->lado;
    }

    function getIsla() {
        return $this->isla == null ? 1 : $this->isla;
    }

    function getDespachador() {
        return $this->despachador == null ? 0 : $this->despachador;
    }

    function getMan() {
        return $this->man == null ? "No" : $this->man;
    }

    function getInventario() {
        return $this->inventario == null ? "No" : $this->inventario;
    }

    function getDispensario() {
        return $this->dispensario == null ? 0 : $this->dispensario;
    }

    function getNumventas() {
        return $this->numventas == null ? 0 : $this->numventas;
    }

    function getConteoventas() {
        return $this->conteoventas == null ? 0 : $this->conteoventas;
    }

    function getDespachadorsig() {
        return $this->despachadorsig == null ? 0 : $this->despachadorsig;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setPosicion($posicion) {
        $this->posicion = $posicion;
    }

    function setProductos($productos) {
        $this->productos = $productos;
    }

    function setActivo($activo) {
        $this->activo = $activo;
    }

    function setLado($lado) {
        $this->lado = $lado;
    }

    function setIsla($isla) {
        $this->isla = $isla;
    }

    function setDespachador($despachador) {
        $this->despachador = $despachador;
    }

    function setMan($man) {
        $this->man = $man;
    }

    function setInventario($inventario) {
        $this->inventario = $inventario;
    }

    function setDispensario($dispensario) {
        $this->dispensario = $dispensario;
    }

    function setNumventas($numventas) {
        $this->numventas = $numventas;
    }

    function setConteoventas($conteoventas) {
        $this->conteoventas = $conteoventas;
    }

    function setDespachadorsig($despachadorsig) {
        $this->despachadorsig = $despachadorsig;
    }

    function getIsla_pos() {
        return $this->isla_pos;
    }

    function setIsla_pos($isla_pos) {
        $this->isla_pos = $isla_pos;
    }

}
