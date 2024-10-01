<?php

/**
 * Description of TrasladosVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Alejandro Ayala Gonzalez
 * @version 1.0
 * @since ene 2022
 */
class TrasladosVO {

    private $id;
    private $id_cli;
    private $serie = "TCP";
    private $folio;
    private $claveProductoServicio;
    private $fecha;
    private $cantidad;
    private $importe;
    private $iva;
    private $ieps;
    private $total;
    private $status;
    private $uuid = "-----";
    private $observaciones;
    private $usr;
    private $stCancelacion;
    private $motivoCan;
    private $metodoPago = "PUE";
    private $formaPago = "01";
    private $usoCfdi = "S01";
    private $sello = "";

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getId_cli() {
        return $this->id_cli;
    }

    function getSerie() {
        return $this->serie;
    }

    function getFolio() {
        return $this->folio;
    }

    function getFecha() {
        return $this->fecha;
    }

    function getCantidad() {
        return $this->cantidad;
    }

    function getImporte() {
        return $this->importe;
    }

    function getIva() {
        return $this->iva;
    }

    function getIeps() {
        return $this->ieps;
    }

    function getTotal() {
        return $this->total;
    }

    function getStatus() {
        return $this->status;
    }

    function getUuid() {
        return $this->uuid;
    }

    function getObservaciones() {
        return $this->observaciones;
    }

    function getUsr() {
        return $this->usr;
    }

    function getStCancelacion() {
        return $this->stCancelacion;
    }

    function getMotivoCan() {
        return $this->motivoCan;
    }

    function getClaveProductoServicio() {
        return $this->claveProductoServicio;
    }

    function getMetodoPago() {
        return $this->metodoPago;
    }

    function getFormaPago() {
        return $this->formaPago;
    }

    function getUsoCfdi() {
        return $this->usoCfdi;
    }

    function getSello() {
        return $this->sello;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setId_cli($id_cli) {
        $this->id_cli = $id_cli;
    }

    function setSerie($serie) {
        $this->serie = $serie;
    }

    function setFolio($folio) {
        $this->folio = $folio;
    }

    function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    function setCantidad($cantidad) {
        $this->cantidad = $cantidad;
    }

    function setImporte($importe) {
        $this->importe = $importe;
    }

    function setIva($iva) {
        $this->iva = $iva;
    }

    function setIeps($ieps) {
        $this->ieps = $ieps;
    }

    function setTotal($total) {
        $this->total = $total;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function setUuid($uuid) {
        $this->uuid = $uuid;
    }

    function setObservaciones($observaciones) {
        $this->observaciones = $observaciones;
    }

    function setUsr($usr) {
        $this->usr = $usr;
    }

    function setStCancelacion($stCancelacion) {
        $this->stCancelacion = $stCancelacion;
    }

    function setMotivoCan($motivoCan) {
        $this->motivoCan = $motivoCan;
    }

    function setClaveProductoServicio($claveProductoServicio) {
        $this->claveProductoServicio = $claveProductoServicio;
    }

    function setMetodoPago($metodoPago) {
        $this->metodoPago = $metodoPago;
    }

    function setFormaPago($formaPago) {
        $this->formaPago = $formaPago;
    }

    function setUsoCfdi($usoCfdi) {
        $this->usoCfdi = $usoCfdi;
    }

    function setSello($sello) {
        $this->sello = $sello;
    }

}
