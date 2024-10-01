<?php

/**
 * Description of CxdVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class CxdVO {

    private $id;
    private $vendedor = 0;
    private $referencia = 0;
    private $recibo = 0;
    private $corte;
    private $fecha;
    private $tm = "C";
    private $concepto;
    private $importe;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getVendedor() {
        return $this->vendedor;
    }

    function getReferencia() {
        return $this->referencia;
    }

    function getRecibo() {
        return $this->recibo;
    }

    function getCorte() {
        return $this->corte;
    }

    function getFecha() {
        return $this->fecha;
    }

    function getTm() {
        return $this->tm;
    }

    function getConcepto() {
        return $this->concepto;
    }

    function getImporte() {
        return $this->importe;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setVendedor($vendedor) {
        $this->vendedor = $vendedor;
    }

    function setReferencia($referencia) {
        $this->referencia = $referencia;
    }

    function setRecibo($recibo) {
        $this->recibo = $recibo;
    }

    function setCorte($corte) {
        $this->corte = $corte;
    }

    function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    function setTm($tm) {
        $this->tm = $tm;
    }

    function setConcepto($concepto) {
        $this->concepto = $concepto;
    }

    function setImporte($importe) {
        $this->importe = $importe;
    }

}
