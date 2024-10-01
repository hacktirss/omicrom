<?php

/**
 * Description of VehiculoVO
 * omicrom®
 * © 2022, Detisa 
 * http://www.detisa.com.mx
 * @author Alan Rodriguez 
 * @version 1.0
 * @since feb 2022
 */
class VehiculoVO {

    private $id;
    private $descripcion;
    private $conf_vehicular;
    private $placa;
    private $anio_modelo;
    private $subtipo_remolque;
    private $placa_remolque;
    private $permiso_sct;
    private $numero_sct;
    private $nombre_aseguradora;
    private $numero_seguro;
    private $tipo_figura;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getConf_vehicular() {
        return $this->conf_vehicular;
    }

    function getPlaca() {
        return $this->placa;
    }

    function getAnio_modelo() {
        return $this->anio_modelo;
    }

    function getSubtipo_remolque() {
        return $this->subtipo_remolque;
    }

    function getPlaca_remolque() {
        return $this->placa_remolque;
    }

    function getPermiso_sct() {
        return $this->permiso_sct;
    }

    function getNumero_sct() {
        return $this->numero_sct;
    }

    function getNombre_aseguradora() {
        return $this->nombre_aseguradora;
    }

    function getNumero_seguro() {
        return $this->numero_seguro;
    }

    function getTipo_figura() {
        return $this->tipo_figura;
    }

    function getDescripcion() {
        return $this->descripcion;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setConf_vehicular($conf_vehicular) {
        $this->conf_vehicular = $conf_vehicular;
    }

    function setPlaca($placa) {
        $this->placa = $placa;
    }

    function setAnio_modelo($anio_modelo) {
        $this->anio_modelo = $anio_modelo;
    }

    function setSubtipo_remolque($subtipo_remolque) {
        $this->subtipo_remolque = $subtipo_remolque;
    }

    function setPlaca_remolque($placa_remolque) {
        $this->placa_remolque = $placa_remolque;
    }

    function setPermiso_sct($permiso_sct) {
        $this->permiso_sct = $permiso_sct;
    }

    function setNumero_sct($numero_sct) {
        $this->numero_sct = $numero_sct;
    }

    function setNombre_aseguradora($nombre_aseguradora) {
        $this->nombre_aseguradora = $nombre_aseguradora;
    }

    function setNumero_seguro($numero_seguro) {
        $this->numero_seguro = $numero_seguro;
    }

    function setTipo_figura($tipo_figura) {
        $this->tipo_figura = $tipo_figura;
    }

    function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

}
