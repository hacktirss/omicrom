<?php

/**
 * Description of TransferenciaVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class TransferenciaVO {

    private $id;
    private $fecha;
    private $corte;
    private $isla_pos;
    private $producto;
    private $cantidad;
    private $posicion;
    private $tarea;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getFecha() {
        return $this->fecha;
    }

    function getCorte() {
        return $this->corte;
    }

    function getIsla_pos() {
        return $this->isla_pos;
    }

    function getProducto() {
        return $this->producto;
    }

    function getCantidad() {
        return $this->cantidad;
    }

    function getPosicion() {
        return $this->posicion;
    }

    function getTarea() {
        return $this->tarea;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    function setCorte($corte) {
        $this->corte = $corte;
    }

    function setIsla_pos($isla_pos) {
        $this->isla_pos = $isla_pos;
    }

    function setProducto($producto) {
        $this->producto = $producto;
    }

    function setCantidad($cantidad) {
        $this->cantidad = $cantidad;
    }

    function setPosicion($posicion) {
        $this->posicion = $posicion;
    }

    function setTarea($tarea) {
        $this->tarea = $tarea;
    }

}
