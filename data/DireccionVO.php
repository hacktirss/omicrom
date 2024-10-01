<?php

/**
 * Description of DireccionVO
 * omicrom®
 * © 2022, Detisa 
 * http://www.detisa.com.mx
 * @author Alan Rodriguez 
 * @version 1.0
 * @since feb 2022
 */
class DireccionVO {

    private $id;
    private $descripcion = "-";
    private $calle;
    private $num_exterior;
    private $num_interior;
    private $colonia;
    private $localidad;
    private $municipio;
    private $estado;
    private $codigo_postal;
    private $tabla_origen;
    private $id_origen;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getCalle() {
        return $this->calle;
    }

    function getNum_exterior() {
        return $this->num_exterior;
    }

    function getNum_interior() {
        return $this->num_interior;
    }

    function getColonia() {
        return $this->colonia;
    }

    function getLocalidad() {
        return $this->localidad;
    }

    function getMunicipio() {
        return $this->municipio;
    }

    function getEstado() {
        return $this->estado;
    }

    function getCodigo_postal() {
        return $this->codigo_postal;
    }

    function getTabla_origen() {
        return $this->tabla_origen;
    }

    function getId_origen() {
        return $this->id_origen;
    }

    function getDescripcion() {
        return $this->descripcion;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setCalle($calle) {
        $this->calle = $calle;
    }

    function setNum_exterior($num_exterior) {
        $this->num_exterior = $num_exterior;
    }

    function setNum_interior($num_interior) {
        $this->num_interior = $num_interior;
    }

    function setColonia($colonia) {
        $this->colonia = $colonia;
    }

    function setLocalidad($localidad) {
        $this->localidad = $localidad;
    }

    function setMunicipio($municipio) {
        $this->municipio = $municipio;
    }

    function setEstado($estado) {
        $this->estado = $estado;
    }

    function setCodigo_postal($codigo_postal) {
        $this->codigo_postal = $codigo_postal;
    }

    function setTabla_origen($tabla_origen) {
        $this->tabla_origen = $tabla_origen;
    }

    function setId_origen($id_origen) {
        $this->id_origen = $id_origen;
    }

    function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

}
