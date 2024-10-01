<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class BancosVO {

    private $id;
    private $rubro = 0;
    private $banco;
    private $cuenta;
    private $concepto;
    private $ncc = "";
    private $tipo_moneda = 1;
    private $tipo_cambio = 1;
    private $activo = 1;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getRubro() {
        return $this->rubro == null || empty($this->rubro) ? 0 : $this->rubro;
    }

    function getBanco() {
        return $this->banco;
    }

    function getCuenta() {
        return $this->cuenta;
    }

    function getConcepto() {
        return $this->concepto;
    }

    function getNcc() {
        return $this->ncc;
    }

    function getTipo_moneda() {
        return $this->tipo_moneda;
    }

    function getTipo_cambio() {
        return $this->tipo_cambio;
    }

    function setId($idBancos) {
        $this->id = $idBancos;
    }

    function setRubro($rubro) {
        $this->rubro = $rubro;
    }

    function setBanco($banco) {
        $this->banco = $banco;
    }

    function setCuenta($cuenta) {
        $this->cuenta = $cuenta;
    }

    function setConcepto($concepto) {
        $this->concepto = $concepto;
    }

    function setNcc($ncc) {
        $this->ncc = $ncc;
    }

    function setTipo_moneda($tipo_moneda) {
        $this->tipo_moneda = $tipo_moneda;
    }

    function setTipo_cambio($tipo_cambio) {
        $this->tipo_cambio = $tipo_cambio;
    }

    function getActivo() {
        return $this->activo;
    }

    function setActivo($activo) {
        $this->activo = $activo;
    }

}
