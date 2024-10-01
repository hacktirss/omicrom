<?php

/**
 * Description of CambioPreciosVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class CambioPreciosVO {

    private $id;
    private $fecha;
    private $fechaapli;
    private $hora;
    private $producto;
    private $precio;
    private $status;
    private $idtarea;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getFecha() {
        return $this->fecha;
    }

    function getFechaapli() {
        return $this->fechaapli;
    }

    function getHora() {
        return $this->hora;
    }

    function getProducto() {
        return $this->producto;
    }

    function getPrecio() {
        return $this->precio;
    }

    function getStatus() {
        return $this->status;
    }

    function getIdtarea() {
        return $this->idtarea;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    function setFechaapli($fechaapli) {
        $this->fechaapli = $fechaapli;
    }

    function setHora($hora) {
        $this->hora = $hora;
    }

    function setProducto($producto) {
        $this->producto = $producto;
    }

    function setPrecio($precio) {
        $this->precio = $precio;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function setIdtarea($idtarea) {
        $this->idtarea = $idtarea;
    }

}
