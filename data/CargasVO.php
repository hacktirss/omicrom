<?php

/**
 * Description of CargasVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class CargasVO {

    private $id;
    private $tanque;
    private $producto;
    private $clave_producto;
    private $t_inicial;
    private $vol_inicial;
    private $fecha_inicio;
    private $t_final;
    private $vol_final;
    private $fecha_fin;
    private $aumento;
    private $fecha_insercion;
    private $entrada;
    private $inicia_carga;
    private $finaliza_carga;
    private $tipo;
    private $folioenvios;
    private $enviado;
    private $clave;
    private $vol_doc;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getTanque() {
        return $this->tanque;
    }

    function getProducto() {
        return $this->producto;
    }

    function getClave_producto() {
        return $this->clave_producto;
    }

    function getT_inicial() {
        return $this->t_inicial;
    }

    function getVol_inicial() {
        return $this->vol_inicial;
    }

    function getFecha_inicio() {
        return $this->fecha_inicio;
    }

    function getT_final() {
        return $this->t_final;
    }

    function getVol_final() {
        return $this->vol_final;
    }

    function getFecha_fin() {
        return $this->fecha_fin;
    }

    function getAumento() {
        return $this->aumento;
    }

    function getFecha_insercion() {
        return $this->fecha_insercion;
    }

    function getEntrada() {
        return $this->entrada;
    }

    function getInicia_carga() {
        return $this->inicia_carga;
    }

    function getFinaliza_carga() {
        return $this->finaliza_carga;
    }

    function getTipo() {
        return $this->tipo;
    }

    function getFolioenvios() {
        return $this->folioenvios;
    }

    function getEnviado() {
        return $this->enviado;
    }

    function getVol_doc() {
        return $this->vol_doc;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setTanque($tanque) {
        $this->tanque = $tanque;
    }

    function setProducto($producto) {
        $this->producto = $producto;
    }

    function setClave_producto($clave_producto) {
        $this->clave_producto = $clave_producto;
    }

    function setT_inicial($t_inicial) {
        $this->t_inicial = $t_inicial;
    }

    function setVol_inicial($vol_inicial) {
        $this->vol_inicial = $vol_inicial;
    }

    function setFecha_inicio($fecha_inicio) {
        $this->fecha_inicio = $fecha_inicio;
    }

    function setT_final($t_final) {
        $this->t_final = $t_final;
    }

    function setVol_final($vol_final) {
        $this->vol_final = $vol_final;
    }

    function setFecha_fin($fecha_fin) {
        $this->fecha_fin = $fecha_fin;
    }

    function setAumento($aumento) {
        $this->aumento = $aumento;
    }

    function setFecha_insercion($fecha_insercion) {
        $this->fecha_insercion = $fecha_insercion;
    }

    function setEntrada($entrada) {
        $this->entrada = $entrada;
    }

    function setInicia_carga($inicia_carga) {
        $this->inicia_carga = $inicia_carga;
    }

    function setFinaliza_carga($finaliza_carga) {
        $this->finaliza_carga = $finaliza_carga;
    }

    function setTipo($tipo) {
        $this->tipo = $tipo;
    }

    function setFolioenvios($folioenvios) {
        $this->folioenvios = $folioenvios;
    }

    function setEnviado($enviado) {
        $this->enviado = $enviado;
    }

    function getClave() {
        return $this->clave;
    }

    function setClave($clave) {
        $this->clave = $clave;
    }

    function setVol_doc($vol_doc) {
        $this->vol_doc = $vol_doc;
    }

}
