<?php

/**
 * Description of DictamenDVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class DictamenDVO {

    private $idnvo;
    private $id;
    private $tanque;
    private $comp_azufre;
    private $fraccion_molar;
    private $poder_calorifico;
    private $comp_octanaje;
    private $comp_etanol;
    private $cve_producto_sat;
    private $gravedad_especifica;
    private $comp_fosil;
    private $comp_propano;
    private $comp_butano;
    private $clave_instalacion;
    private $contiene_fosil;

    function __construct() {
        
    }

    function getIdnvo() {
        return $this->idnvo;
    }

    function getId() {
        return $this->id;
    }

    function getTanque() {
        return $this->tanque;
    }

    function getPoder_calorifico() {
        return $this->poder_calorifico;
    }

    function getComp_azufre() {
        return $this->comp_azufre;
    }

    function getFraccion_molar() {
        return $this->fraccion_molar;
    }

    function getCve_producto_sat() {
        return $this->cve_producto_sat;
    }

    function getGravedad_especifica() {
        return $this->gravedad_especifica;
    }

    function getComp_fosil() {
        return $this->comp_fosil;
    }

    function getComp_propano() {
        return $this->comp_propano;
    }

    function getComp_butano() {
        return $this->comp_butano;
    }

    function getClave_instalacion() {
        return $this->clave_instalacion;
    }

    function getContiene_fosil() {
        return $this->contiene_fosil;
    }
    
    function setContiene_fosil($contiene_fosil) {
        $this->contiene_fosil = $contiene_fosil;
    }

    function setIdnvo($idnvo) {
        $this->idnvo = $idnvo;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setTanque($tanque) {
        $this->tanque = $tanque;
    }

    function setPoder_calorifico($poder_calorifico) {
        $this->poder_calorifico = $poder_calorifico;
    }

    function setComp_azufre($comp_azufre) {
        $this->comp_azufre = $comp_azufre;
    }

    function setFraccion_molar($fraccion_molar) {
        $this->fraccion_molar = $fraccion_molar;
    }

    function getComp_octanaje() {
        return $this->comp_octanaje;
    }

    function getComp_etanol() {
        return $this->comp_etanol;
    }

    function setComp_octanaje($comp_octanaje) {
        $this->comp_octanaje = $comp_octanaje;
    }

    function setComp_etanol($comp_etanol) {
        $this->comp_etanol = $comp_etanol;
    }

    function setCve_producto_sat($cve_producto_sat) {
        $this->cve_producto_sat = $cve_producto_sat;
    }

    function setGravedad_especifica($gravedad_especifica) {
        $this->gravedad_especifica = $gravedad_especifica;
    }

    function setComp_fosil($comp_fosil) {
        $this->comp_fosil = $comp_fosil;
    }

    function setComp_propano($comp_propano) {
        $this->comp_propano = $comp_propano;
    }

    function setComp_butano($comp_butano) {
        $this->comp_butano = $comp_butano;
    }

    function setClave_instalacion($clave_instalacion) {
        $this->clave_instalacion = $clave_instalacion;
    }

}
