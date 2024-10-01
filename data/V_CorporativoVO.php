<?php

/**
 * Description of V_CorporativoVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class V_CorporativoVO {

    private $id;
    private $llave;
    private $valor;
    private $descripcion;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getLlave() {
        return $this->llave;
    }

    function getValor() {
        return $this->valor;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setLlave($llave) {
        $this->llave = $llave;
    }

    function setValor($valor) {
        $this->valor = $valor;
    }

    function getDescripcion() {
        return $this->descripcion;
    }

    function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

}
