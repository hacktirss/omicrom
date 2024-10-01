<?php

/**
 * Description of ListasVO
 * omicrom®
 * © 2021, Detisa 
 * http://www.detisa.com.mx
 * @author Alejandro Ayala Gonzalez
 * @version 1.0
 * @since mar 2021
 */

class ListasVO{
    private $id_lista;
    private $nombre_lista;
    private $descripcion_lista;
    private $default_lista;
    private $tipo_dato_lista;
    private $longitud_lista;
    private $estado_lista;
    private $mayus_lista;
    private $min_lista;
    private $max_lista;
    
    function __construct() {
        
    }
    
    function getId_lista() {
        return $this->id_lista;
    }

    function getNombre_lista() {
        return $this->nombre_lista;
    }

    function getDescripcion_lista() {
        return $this->descripcion_lista;
    }

    function getDefault_lista() {
        return $this->default_lista;
    }

    function getTipo_dato_lista() {
        return $this->tipo_dato_lista;
    }

    function getLongitud_lista() {
        return $this->longitud_lista;
    }

    function getEstado_lista() {
        return $this->estado_lista;
    }

    function getMayus_lista() {
        return $this->mayus_lista;
    }

    function getMin_lista() {
        return $this->min_lista;
    }

    function getMax_lista() {
        return $this->max_lista;
    }

    function setId_lista($id_lista) {
        $this->id_lista = $id_lista;
    }

    function setNombre_lista($nombre_lista) {
        $this->nombre_lista = $nombre_lista;
    }

    function setDescripcion_lista($descripcion_lista) {
        $this->descripcion_lista = $descripcion_lista;
    }

    function setDefault_lista($default_lista) {
        $this->default_lista = $default_lista;
    }

    function setTipo_dato_lista($tipo_dato_lista) {
        $this->tipo_dato_lista = $tipo_dato_lista;
    }

    function setLongitud_lista($longitud_lista) {
        $this->longitud_lista = $longitud_lista;
    }

    function setEstado_lista($estado_lista) {
        $this->estado_lista = $estado_lista;
    }

    function setMayus_lista($mayus_lista) {
        $this->mayus_lista = $mayus_lista;
    }

    function setMin_lista($min_lista) {
        $this->min_lista = $min_lista;
    }

    function setMax_lista($max_lista) {
        $this->max_lista = $max_lista;
    }
}