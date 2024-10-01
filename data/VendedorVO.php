<?php

/**
 * Description of VenVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class VendedorVO {

    private $id;
    private $nombre;
    private $direccion;
    private $colonia;
    private $municipio;
    private $alias;
    private $telefono;
    private $activo;
    private $nip;
    private $ncc;
    private $num_empleado;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getNombre() {
        return $this->nombre;
    }

    function getDireccion() {
        return $this->direccion;
    }

    function getColonia() {
        return $this->colonia;
    }

    function getMunicipio() {
        return $this->municipio;
    }

    function getAlias() {
        return $this->alias;
    }

    function getTelefono() {
        return $this->telefono;
    }

    function getActivo() {
        return $this->activo;
    }

    function getNip() {
        return $this->nip;
    }

    function getNcc() {
        return $this->ncc;
    }

    function getNum_empleado() {
        return $this->num_empleado;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    function setDireccion($direccion) {
        $this->direccion = $direccion;
    }

    function setColonia($colonia) {
        $this->colonia = $colonia;
    }

    function setMunicipio($municipio) {
        $this->municipio = $municipio;
    }

    function setAlias($alias) {
        $this->alias = $alias;
    }

    function setTelefono($telefono) {
        $this->telefono = $telefono;
    }

    function setActivo($activo) {
        $this->activo = $activo;
    }

    function setNip($nip) {
        $this->nip = $nip;
    }

    function setNcc($ncc) {
        $this->ncc = $ncc;
    }

    function setNum_empleado($num_empleado) {
        $this->num_empleado = $num_empleado;
    }

}
