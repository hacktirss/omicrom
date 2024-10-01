<?php

/**
 * Description of IslaVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class IslaVO {

    private $isla;
    private $descripcion;
    private $turno;
    private $activo;
    private $status;
    private $corte;

    function __construct() {
        
    }

    function getIsla() {
        return $this->isla;
    }

    function getDescripcion() {
        return $this->descripcion;
    }

    function getTurno() {
        return $this->turno;
    }

    function getActivo() {
        return $this->activo;
    }

    function getStatus() {
        return $this->status;
    }

    function getCorte() {
        return $this->corte;
    }

    function setIsla($isla) {
        $this->isla = $isla;
    }

    function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    function setTurno($turno) {
        $this->turno = $turno;
    }

    function setActivo($activo) {
        $this->activo = $activo;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function setCorte($corte) {
        $this->corte = $corte;
    }

}
