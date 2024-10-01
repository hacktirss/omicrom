<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//namespace com\detisa\omicrom;

/**
 * Description of BitacoraVO
 *
 * @author lino
 */
class BitacoraVO {

    private $idBitacora;
    private $fechaEvento;
    private $horaEvento;
    private $usuario;
    private $tipoEvento;
    private $descripcionEvento;
    private $ipEvento;
    private $queryStr;
    private $numeroAlarma;

    function __construct() {
        
    }

    function getIdBitacora() {
        return $this->idBitacora;
    }

    function getFechaEvento() {
        return $this->fechaEvento;
    }

    function getHoraEvento() {
        return $this->horaEvento;
    }

    function getUsuario() {
        return $this->usuario;
    }

    function getTipoEvento() {
        return $this->tipoEvento;
    }

    function getDescripcionEvento() {
        return $this->descripcionEvento;
    }

    function getIpEvento() {
        return $this->ipEvento;
    }

    function getQueryStr() {
        return $this->queryStr;
    }

    function getNumeroAlarma() {
        return $this->numeroAlarma;
    }

    function setIdBitacora($idBitacora) {
        $this->idBitacora = $idBitacora;
    }

    function setFechaEvento($fechaEvento) {
        $this->fechaEvento = $fechaEvento;
    }

    function setHoraEvento($horaEvento) {
        $this->horaEvento = $horaEvento;
    }

    function setUsuario($usuario) {
        $this->usuario = $usuario;
    }

    function setTipoEvento($tipoEvento) {
        $this->tipoEvento = $tipoEvento;
    }

    function setDescripcionEvento($descripcionEvento) {
        $this->descripcionEvento = $descripcionEvento;
    }
    
    function setIpEvento($ipEvento){
        $this->ipEvento = $ipEvento;
    }
    
    function setQueryStr($queryStr) {
        $this->queryStr = $queryStr;
    }

    function setNumeroAlarma($numeroAlarma) {
        $this->numeroAlarma = $numeroAlarma;
    }

}
