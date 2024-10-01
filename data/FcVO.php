<?php

/*
 * FcVO
 * omicrom
 * 2017, Detisa 
 * http://www.detisa.com.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since jul 2017
 */

class FcVO {

    private $id;
    private $serie;
    private $fecha;
    private $cliente;
    private $cantidad;
    private $importe;
    private $iva;
    private $ieps;
    private $status;
    private $total;
    private $uuid;
    private $ticket;
    private $observaciones;
    private $usr;
    private $origen;
    private $stCancelacion;
    private $motivoCan;
    private $tiporelacion;
    private $relacioncfdi = 0;
    private $usocfdi;
    private $formadepago;
    private $metododepago;
    private $folio;
    private $relacionfolio;
    private $documentoRelacion;
    private $sello;
    private $periodo = 00;
    private $meses = 00;
    private $ano = 0000;
    private $descuento = 0;
    private $cancelacion = "-";

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getFecha() {
        return $this->fecha;
    }

    function getSerie() {
        return $this->serie;
    }

    function getCliente() {
        return $this->cliente;
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

    function getStatus() {
        return $this->status;
    }

    function getTotal() {
        return $this->total;
    }

    function getUuid() {
        return $this->uuid;
    }

    function getTicket() {
        return $this->ticket;
    }

    function getObservaciones() {
        return $this->observaciones;
    }

    function getUsr() {
        return $this->usr;
    }

    function getOrigen() {
        return $this->origen;
    }

    function getStCancelacion() {
        return $this->stCancelacion;
    }

    function getMotivoCan() {
        return $this->motivoCan;
    }

    function getTiporelacion() {
        return $this->tiporelacion;
    }

    function getRelacioncfdi() {
        return $this->relacioncfdi;
    }

    function getUsocfdi() {
        return $this->usocfdi;
    }

    function getFormadepago() {
        return $this->formadepago;
    }

    function getMetododepago() {
        return $this->metododepago;
    }

    function getRelacionfolio() {
        return $this->relacionfolio;
    }

    function getPeriodo() {
        return $this->periodo;
    }

    function getMeses() {
        return $this->meses;
    }

    function getAno() {
        return $this->ano;
    }

    function getDescuento() {
        return $this->descuento;
    }

    function getCancelacion() {
        return $this->cancelacion;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setSerie($serie) {
        $this->serie = $serie;
    }

    function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    function setCliente($cliente) {
        $this->cliente = $cliente;
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

    function setStatus($status) {
        $this->status = $status;
    }

    function setTotal($total) {
        $this->total = $total;
    }

    function setUuid($uuid) {
        $this->uuid = $uuid;
    }

    function setTicket($ticket) {
        $this->ticket = $ticket;
    }

    function setObservaciones($observaciones) {
        $this->observaciones = $observaciones;
    }

    function setUsr($usr) {
        $this->usr = $usr;
    }

    function setOrigen($origen) {
        $this->origen = $origen;
    }

    function setStCancelacion($stCancelacion) {
        $this->stCancelacion = $stCancelacion;
    }

    function setMotivoCan($motivoCan) {
        $this->motivoCan = $motivoCan;
    }

    function setTiporelacion($tiporelacion) {
        $this->tiporelacion = $tiporelacion;
    }

    function setRelacioncfdi($relacioncfdi) {
        $this->relacioncfdi = $relacioncfdi;
    }

    function setUsocfdi($usocfdi) {
        $this->usocfdi = $usocfdi;
    }

    function setFormadepago($formadepago) {
        $this->formadepago = $formadepago;
    }

    function setMetododepago($metododepago) {
        $this->metododepago = $metododepago;
    }

    function setRelacionfolio($relacionfolio) {
        $this->relacionfolio = $relacionfolio;
    }

    function setPeriodo($periodo) {
        $this->periodo = $periodo;
    }

    function setMeses($meses) {
        $this->meses = $meses;
    }

    function setAno($ano) {
        $this->ano = $ano;
    }

    function setDescuento($descuento) {
        $this->descuento = $descuento;
    }

    function setCancelacion($cancelacion) {
        $this->cancelacion = $cancelacion;
    }

    public function __toString() {
        return "FcVO={id=" . $this->id
                . ", serie=" . $this->serie
                . ", fecha=" . $this->fecha
                . ", cliente=" . $this->cliente
                . ", cantidad=" . $this->cantidad
                . ", importe=" . $this->importe
                . ", iva=" . $this->iva
                . ", ieps=" . $this->ieps
                . ". status=" . $this->status
                . ". total=" . $this->total
                . ", uuid=" . $this->uuid
                . ", ticket=" . $this->ticket
                . ", observaciones=" . $this->observaciones
                . ", usr=" . $this->usr
                . ", origen=" . $this->origen
                . ", stCancelacion=" . $this->stCancelacion
                . ", relacioncfdi=" . $this->relacioncfdi
                . ", tiporelacion=" . $this->tiporelacion
                . ", usocfdi=" . $this->usocfdi
                . ", formadepago=" . $this->formadepago
                . ", metododepago=" . $this->metododepago . ""
                . ", periodo=" . $this->periodo . ""
                . ", meses = " . $this->meses . ""
                . ", ano = " . $this->ano . "}";
    }

    function getDocumentoRelacion() {
        return $this->documentoRelacion;
    }

    function setDocumentoRelacion($documentoRelacion) {
        $this->documentoRelacion = $documentoRelacion;
    }

    function getFolio() {
        return $this->folio;
    }

    function setFolio($folio) {
        $this->folio = $folio;
    }

    function getSello() {
        return $this->sello;
    }

    function setSello($sello) {
        $this->sello = $sello;
    }

}
