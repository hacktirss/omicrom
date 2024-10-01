<?php

/**
 * Description of MedVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class MedVO {

    private $id;
    private $idnvo;
    private $clave;
    private $cantidad;
    private $precio;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getIdnvo() {
        return $this->idnvo;
    }

    function getClave() {
        return $this->clave;
    }

    function getCantidad() {
        return $this->cantidad;
    }

    function getPrecio() {
        return $this->precio;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setIdnvo($idnvo) {
        $this->idnvo = $idnvo;
    }

    function setClave($clave) {
        $this->clave = $clave;
    }

    function setCantidad($cantidad) {
        $this->cantidad = $cantidad;
    }

    function setPrecio($precio) {
        $this->precio = $precio;
    }

}
