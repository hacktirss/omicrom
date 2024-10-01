<?php

/**
 * Description of UsoGeneralVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class UsoGeneralVO {

    private $id;
    private $nombre_catalogo;
    private $descripcion;
    private $llave;
    private $descripcion_llave;
    private $valor;
    private $valor_2;
    private $valor_3;
    private $valor_4;
    private $contrasenia;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getNombre_catalogo() {
        return $this->nombre_catalogo;
    }

    /**
     * Define la descripcion del catalogo o puede contener el numero de alarma
     * @return string
     */
    function getDescripcion() {
        return $this->descripcion;
    }

    function getLlave() {
        return $this->llave;
    }

    function getValor() {
        return $this->valor;
    }

    /**
     * Define si el registro se podrá desplegar en Omicrom, si el valor es 1
     * podrá mostrarse como catalogo, si es 0 solo se podrá modificar desde 
     * Adminomc
     * @return int
     */
    function getValor_2() {
        return $this->valor_2 == null ? 1 : $this->valor_2;
    }

    /**
     * Define si el registro esta activo
     * @return int 
     */
    function getValor_3() {
        return $this->valor_3;
    }

    /**
     * Define si el registro genera alarma
     * @return int 
     */
    function getValor_4() {
        return $this->valor_4;
    }

    function getContrasenia() {
        return $this->contrasenia;
    }

    function  setId($id) {
        $this->id = $id;
    }

    function setNombre_catalogo($nombre_catalogo) {
        $this->nombre_catalogo = $nombre_catalogo;
    }

    /**
     * Define la descripcion del catalogo o puede contener el numero de alarma
     * @param string $descripcion
     */
    function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    function setLlave($llave) {
        $this->llave = $llave;
    }

    function setValor($valor) {
        $this->valor = $valor;
    }

    /**
     * Define si el registro se podrá desplegar en Omicrom, si el valor es 1
     * podrá mostrarse como catalogo, si es 0 solo se podrá modificar desde 
     * Adminomc
     */
    function setValor_2($valor_2) {
        $this->valor_2 = $valor_2;
    }

    /**
     * Define si el registro esta activo
     * @param type $valor_3
     */
    function setValor_3($valor_3) {
        $this->valor_3 = $valor_3;
    }

    /**
     * Define si el registro genera alarma
     * @param int $valor_4
     */
    function setValor_4($valor_4) {
        $this->valor_4 = $valor_4;
    }

    function setContrasenia($contrasenia) {
        $this->contrasenia = $contrasenia;
    }
    
    function getDescripcion_llave() {
        return $this->descripcion_llave;
    }

    function setDescripcion_llave($descripcion_llave) {
        $this->descripcion_llave = $descripcion_llave;
    }

}
