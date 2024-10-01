<?php

/**
 * Description of ManProVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class ManProVO {

    private $id;
    private $dispensario;
    private $posicion;
    private $manguera;
    private $dis_mang;
    private $producto;
    private $descripcion;
    private $isla;
    private $activo;
    private $factor;
    private $enable;
    private $proteccion;
    private $cpu;
    private $m;
    private $presente;
    private $manf;
    private $lc_emr3;
    private $back;
    private $tanque;
    private $totalizadorV;
    private $totalizadorI;
    private $vigencia_calibracion;
    private $valor_calibracion;
    private $num_medidor;
    private $tipo_medidor;
    private $modelo_medidor;
    private $incertidumbre;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getDispensario() {
        return $this->dispensario;
    }

    function getPosicion() {
        return $this->posicion;
    }

    function getManguera() {
        return $this->manguera;
    }

    function getDis_mang() {
        return $this->dis_mang;
    }

    function getProducto() {
        return $this->producto;
    }

    function getIsla() {
        return $this->isla == null ? 1 : $this->isla;
    }

    function getActivo() {
        return $this->activo == null ? "No" : $this->activo;
    }

    function getFactor() {
        return $this->factor == null ? 0 : $this->factor;
    }

    function getEnable() {
        return $this->enable == null ? 1 : $this->enable;
    }

    function getProteccion() {
        return $this->proteccion == null ? 0 : $this->proteccion;
    }

    function getCpu() {
        return $this->cpu == null ? 0 : $this->cpu;
    }

    function getM() {
        return $this->m == null ? 0 : $this->m;
    }

    function getPresente() {
        return $this->presente == null ? 0 : $this->presente;
    }

    function getManf() {
        return $this->manf == null ? 0 : $this->manf;
    }

    function getLc_emr3() {
        return $this->lc_emr3 == null ? "No" : $this->lc_emr3;
    }

    function getBack() {
        return $this->back == null ? 0 : $this->back;
    }

    function getTanque() {
        return $this->tanque == null ? 1 : $this->tanque;
    }

    function getTotalizadorV() {
        return $this->totalizadorV == null ? 0 : $this->totalizadorV;
    }

    function getTotalizadorI() {
        return $this->totalizadorI == null ? 0 : $this->totalizadorI;
    }

    function getVigencia_calibracion() {
        return $this->vigencia_calibracion;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setDispensario($dispensario) {
        $this->dispensario = $dispensario;
    }

    function setPosicion($posicion) {
        $this->posicion = $posicion;
    }

    function setManguera($manguera) {
        $this->manguera = $manguera;
    }

    function setDis_mang($dis_mang) {
        $this->dis_mang = $dis_mang;
    }

    function setProducto($producto) {
        $this->producto = $producto;
    }

    function setIsla($isla) {
        $this->isla = $isla;
    }

    function setActivo($activo) {
        $this->activo = $activo;
    }

    function setFactor($factor) {
        $this->factor = $factor;
    }

    function setEnable($enable) {
        $this->enable = $enable;
    }

    function setProteccion($proteccion) {
        $this->proteccion = $proteccion;
    }

    function setCpu($cpu) {
        $this->cpu = $cpu;
    }

    function setM($m) {
        $this->m = $m;
    }

    function setPresente($presente) {
        $this->presente = $presente;
    }

    function setManf($manf) {
        $this->manf = $manf;
    }

    function setLc_emr3($lc_emr3) {
        $this->lc_emr3 = $lc_emr3;
    }

    function setBack($back) {
        $this->back = $back;
    }

    function setTanque($tanque) {
        $this->tanque = $tanque;
    }

    function setTotalizadorV($totalizadorV) {
        $this->totalizadorV = $totalizadorV;
    }

    function setTotalizadorI($totalizadorI) {
        $this->totalizadorI = $totalizadorI;
    }

    function setVigencia_calibracion($vigencia_calibracion) {
        $this->vigencia_calibracion = $vigencia_calibracion;
    }

    function getValor_calibracion() {
        return $this->valor_calibracion;
    }

    function getNum_medidor() {
        return $this->num_medidor;
    }

    function getTipo_medidor() {
        return $this->tipo_medidor;
    }

    function getModelo_medidor() {
        return $this->modelo_medidor;
    }

    function getIncertidumbre() {
        return $this->incertidumbre;
    }

    function setValor_calibracion($valor_calibracion) {
        $this->valor_calibracion = $valor_calibracion;
    }

    function setNum_medidor($num_medidor) {
        $this->num_medidor = $num_medidor;
    }

    function setTipo_medidor($tipo_medidor) {
        $this->tipo_medidor = $tipo_medidor;
    }

    function setModelo_medidor($modelo_medidor) {
        $this->modelo_medidor = $modelo_medidor;
    }

    function setIncertidumbre($incertidumbre) {
        $this->incertidumbre = $incertidumbre;
    }

    function getDescripcion() {
        return $this->descripcion;
    }

    function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

}
