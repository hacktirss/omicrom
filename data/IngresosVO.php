<?php

/**
 * Description of IngresosVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Alejandro Ayala Gonzalez
 * @version 1.0
 * @since may 2022
 */
class IngresosVO {

    private $id;
    private $serie;
    private $folio;
    private $fecha;
    private $cantidad = 0;
    private $importe = 0;
    private $iva = 0;
    private $ieps = 0;
    private $total = 0;
    private $status = 0;
    private $uuid = '-----';
    private $observaciones;
    private $usr;
    private $stCancelacion;
    private $motivoCan;
    private $id_cli;
    private $claveProdServ;
    private $cli;
    private $metodopago;
    private $formadepago;
    private $usocfdi;
    private $sello = "";

    public function __construct() {
        
    }

    public function getId() {
        return $this->id;
    }

    public function getSerie() {
        return $this->serie;
    }

    public function getFolio() {
        return $this->folio;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function getCantidad() {
        return $this->cantidad;
    }

    public function getImporte() {
        return $this->importe;
    }

    public function getIva() {
        return $this->iva;
    }

    public function getIeps() {
        return $this->ieps;
    }

    public function getTotal() {
        return $this->total;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getUuid() {
        return $this->uuid;
    }

    public function getObservaciones() {
        return $this->observaciones;
    }

    public function getUsr() {
        return $this->usr;
    }

    public function getStCancelacion() {
        return $this->stCancelacion;
    }

    public function getMotivoCan() {
        return $this->motivoCan;
    }

    public function getId_cli() {
        return $this->id_cli;
    }

    public function getClaveProdServ() {
        return $this->claveProdServ;
    }

    public function getCli() {
        return $this->cli;
    }

    public function getMetodopago() {
        return $this->metodopago;
    }

    public function getFormadepago() {
        return $this->formadepago;
    }

    public function getUsocfdi() {
        return $this->usocfdi;
    }

    public function getSello() {
        return $this->sello;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function setSerie($serie): void {
        $this->serie = $serie;
    }

    public function setFolio($folio): void {
        $this->folio = $folio;
    }

    public function setFecha($fecha): void {
        $this->fecha = $fecha;
    }

    public function setCantidad($cantidad): void {
        $this->cantidad = $cantidad;
    }

    public function setImporte($importe): void {
        $this->importe = $importe;
    }

    public function setIva($iva): void {
        $this->iva = $iva;
    }

    public function setIeps($ieps): void {
        $this->ieps = $ieps;
    }

    public function setTotal($total): void {
        $this->total = $total;
    }

    public function setStatus($status): void {
        $this->status = $status;
    }

    public function setUuid($uuid): void {
        $this->uuid = $uuid;
    }

    public function setObservaciones($observaciones): void {
        $this->observaciones = $observaciones;
    }

    public function setUsr($usr): void {
        $this->usr = $usr;
    }

    public function setStCancelacion($stCancelacion): void {
        $this->stCancelacion = $stCancelacion;
    }

    public function setMotivoCan($motivoCan): void {
        $this->motivoCan = $motivoCan;
    }

    public function setId_cli($id_cli): void {
        $this->id_cli = $id_cli;
    }

    public function setClaveProdServ($claveProdServ): void {
        $this->claveProdServ = $claveProdServ;
    }

    public function setCli($cli): void {
        $this->cli = $cli;
    }

    public function setMetodopago($metodopago): void {
        $this->metodopago = $metodopago;
    }

    public function setFormadepago($formadepago): void {
        $this->formadepago = $formadepago;
    }

    public function setUsocfdi($usocfdi): void {
        $this->usocfdi = $usocfdi;
    }

    public function setSello($sello): void {
        $this->sello = $sello;
    }

}
