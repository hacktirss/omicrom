<?php

/**
 * Description of EgrVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class EgrVO {

    private $id;
    private $corte;
    private $clave;
    private $concepto;
    private $importe;
    private $plomo;
    private $tipo_cambio;
    private $tm;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getCorte() {
        return $this->corte;
    }

    function getClave() {
        return $this->clave;
    }

    function getConcepto() {
        return $this->concepto;
    }

    function getImporte() {
        return $this->importe;
    }

    function getPlomo() {
        return $this->plomo;
    }

    function getTipo_cambio() {
        return $this->tipo_cambio;
    }

    function getTm() {
        return $this->tm;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setCorte($corte) {
        $this->corte = $corte;
    }

    function setClave($clave) {
        $this->clave = $clave;
    }

    function setConcepto($concepto) {
        $this->concepto = $concepto;
    }

    function setImporte($importe) {
        $this->importe = $importe;
    }

    function setPlomo($plomo) {
        $this->plomo = $plomo;
    }

    function setTipo_cambio($tipo_cambio) {
        $this->tipo_cambio = $tipo_cambio;
    }

    function setTm($tm) {
        $this->tm = $tm;
    }

}
