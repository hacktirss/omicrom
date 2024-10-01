<?php

/**
 * Description of MensajesVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class MensajeVO {

    private $id;
    private $tipo;
    private $de;
    private $para;
    private $titulo;
    private $nota;
    private $bd;
    private $fecha;
    private $hora;
    private $vigencia;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getTipo() {
        return $this->tipo;
    }

    function getDe() {
        return $this->de;
    }

    function getPara() {
        return $this->para;
    }

    function getTitulo() {
        return $this->titulo;
    }

    function getNota() {
        return $this->nota;
    }

    function getBd() {
        return $this->bd == null ? 0 :$this->bd;
    }

    function getFecha() {
        return $this->fecha;
    }

    function getHora() {
        return $this->hora;
    }

    function getVigencia() {
        return $this->vigencia;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setTipo($tipo) {
        $this->tipo = $tipo;
    }

    function setDe($de) {
        $this->de = $de;
    }

    function setPara($para) {
        $this->para = $para;
    }

    function setTitulo($titulo) {
        $this->titulo = $titulo;
    }

    function setNota($nota) {
        $this->nota = $nota;
    }

    function setBd($bd) {
        $this->bd = $bd;
    }

    function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    function setHora($hora) {
        $this->hora = $hora;
    }

    function setVigencia($vigencia) {
        $this->vigencia = $vigencia;
    }

}
