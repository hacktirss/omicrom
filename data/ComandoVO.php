<?php

/**
 * Description of ComandoVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class ComandoVO {

    private $id;
    private $posicion;
    private $manguera;
    private $comando;
    private $fecha_insercion;
    private $fecha_programada;
    private $fecha_ejecucion;
    private $intentos;
    private $ejecucion;
    private $descripcion;
    private $idtarea;
    private $replica;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getPosicion() {
        return $this->posicion;
    }

    function getManguera() {
        return $this->manguera;
    }

    function getComando() {
        return $this->comando;
    }

    function getFecha_insercion() {
        return $this->fecha_insercion;
    }

    function getFecha_programada() {
        return $this->fecha_programada;
    }

    function getFecha_ejecucion() {
        return $this->fecha_ejecucion;
    }

    function getIntentos() {
        return $this->intentos;
    }

    function getEjecucion() {
        return $this->ejecucion;
    }

    function getDescripcion() {
        return $this->descripcion;
    }

    function getIdtarea() {
        return $this->idtarea == null ? 0 : $this->idtarea;
    }

    function getReplica() {
        return $this->replica;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setPosicion($posicion) {
        $this->posicion = $posicion;
    }

    function setManguera($manguera) {
        $this->manguera = $manguera;
    }

    function setComando($comando) {
        $this->comando = $comando;
    }

    function setFecha_insercion($fecha_insercion) {
        $this->fecha_insercion = $fecha_insercion;
    }

    function setFecha_programada($fecha_programada) {
        $this->fecha_programada = $fecha_programada;
    }

    function setFecha_ejecucion($fecha_ejecucion) {
        $this->fecha_ejecucion = $fecha_ejecucion;
    }

    function setIntentos($intentos) {
        $this->intentos = $intentos;
    }

    function setEjecucion($ejecucion) {
        $this->ejecucion = $ejecucion;
    }

    function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    function setIdtarea($idtarea) {
        $this->idtarea = $idtarea;
    }

    function setReplica($replica) {
        $this->replica = $replica;
    }

}
