<?php

/*
 * NcVO
 * omicrom
 *  2017, Detisa 
 * http://www.detisa.com.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since nov 2017
 */

class NcVO {

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
    private $observaciones;
    private $formadepago;
    private $metododepago;
    private $factura;
    private $stCancelacion;
    private $usocfdi;
    private $tiporelacion;
    private $relacioncfdi;
    private $usr;
    private $sello;

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

    function getFactura() {
        return $this->factura;
    }

    function getObservaciones() {
        return $this->observaciones;
    }

    function getFormadepago() {
        return $this->formadepago;
    }

    function getMetododepago() {
        return $this->metododepago;
    }

    function getStCancelacion() {
        return $this->stCancelacion;
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

    function setObservaciones($observaciones) {
        $this->observaciones = $observaciones;
    }

    function setFormadepago($formadepago) {
        $this->formadepago = $formadepago;
    }

    function setMetododepago($metododepago) {
        $this->metododepago = $metododepago;
    }

    function setStCancelacion($stCancelacion) {
        $this->stCancelacion = $stCancelacion;
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

    function getUsr() {
        return $this->usr;
    }

    function setUsr($usr) {
        $this->usr = $usr;
    }

    function setFactura($factura) {
        $this->factura = $factura;
    }

    function getSello() {
        return $this->sello;
    }

    function setSello($sello) {
        $this->sello = $sello;
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
                . ", observaciones=" . $this->observaciones
                . ", formadepago=" . $this->formadepago
                . ", metododepago=" . $this->metododepago
                . ", stCancelacion=" . $this->stCancelacion
                . ", relacioncfdi=" . $this->relacioncfdi
                . ", tiporelacion=" . $this->tiporelacion
                . ", usocfdi=" . $this->usocfdi . "}";
    }

}
