<?php

/**
 * Description of CartaPorteDestinosVO
 * omicrom®
 * © 2022, Detisa 
 * http://www.detisa.com.mx
 * @author Alejandro Ayala Gonzalez
 * @version 1.0
 * @since feb 2022
 */
class CartaPorteDestinosVO {

    private $id;
    private $rfcDestinatario;
    private $nombreDestinatario;
    private $calle;
    private $no_ext;
    private $no_int;
    private $colonia;
    private $localidad;
    private $referencia;
    private $municipio;
    private $estado;
    private $pais;
    private $codigo_postal;
    private $origenDestino;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getRfcDestinatario() {
        return $this->rfcDestinatario;
    }

    function getNombreDestinatario() {
        return $this->nombreDestinatario;
    }

    function getCalle() {
        return $this->calle;
    }

    function getNo_ext() {
        return $this->no_ext;
    }

    function getNo_int() {
        return $this->no_int;
    }

    function getColonia() {
        return $this->colonia;
    }

    function getLocalidad() {
        return $this->localidad;
    }

    function getReferencia() {
        return $this->referencia;
    }

    function getMunicipio() {
        return $this->municipio;
    }

    function getEstado() {
        return $this->estado;
    }

    function getPais() {
        return $this->pais;
    }

    function getCodigo_postal() {
        return $this->codigo_postal;
    }

    function getOrigenDestino() {
        return $this->origenDestino;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setRfcDestinatario($rfcDestinatario) {
        $this->rfcDestinatario = $rfcDestinatario;
    }

    function setNombreDestinatario($nombreDestinatario) {
        $this->nombreDestinatario = $nombreDestinatario;
    }

    function setCalle($calle) {
        $this->calle = $calle;
    }

    function setNo_ext($no_ext) {
        $this->no_ext = $no_ext;
    }

    function setNo_int($no_int) {
        $this->no_int = $no_int;
    }

    function setColonia($colonia) {
        $this->colonia = $colonia;
    }

    function setLocalidad($localidad) {
        $this->localidad = $localidad;
    }

    function setReferencia($referencia) {
        $this->referencia = $referencia;
    }

    function setMunicipio($municipio) {
        $this->municipio = $municipio;
    }

    function setEstado($estado) {
        $this->estado = $estado;
    }

    function setPais($pais) {
        $this->pais = $pais;
    }

    function setCodigo_postal($codigo_postal) {
        $this->codigo_postal = $codigo_postal;
    }

    function setOrigenDestino($origenDestino) {
        $this->origenDestino = $origenDestino;
    }

}
