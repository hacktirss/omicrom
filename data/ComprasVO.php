<?php

/**
 * Description of ComprasVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class ComprasVO {

    private $id;
    private $fecha;
    private $proveedor;
    private $concepto;
    private $documento;
    private $cantidad;
    private $importe;
    private $importesin;
    private $iva;
    private $status;
    private $nombre;
    private $alias;
    private $tipodepago;
    private $dias_credito;
    private $proveedorde;
    private $observaciones;
    private $uuid;

    function __construct() {
        
    }

    public function getUuid() {
        return $this->uuid;
    }

    public function setUuid($uuid): void {
        $this->uuid = $uuid;
    }

    function getId() {
        return $this->id;
    }

    function getFecha() {
        return $this->fecha;
    }

    function getProveedor() {
        return $this->proveedor;
    }

    function getConcepto() {
        return $this->concepto;
    }

    function getDocumento() {
        return $this->documento;
    }

    function getCantidad() {
        return $this->cantidad == null ? 0 : $this->cantidad;
    }

    function getImporte() {
        return $this->importe == null ? 0 : $this->importe;
    }

    function getImportesin() {
        return $this->importesin;
    }

    function getIva() {
        return $this->iva;
    }

    function getStatus() {
        return $this->status;
    }

    function getObservaciones() {
        return $this->observaciones;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    function setProveedor($proveedor) {
        $this->proveedor = $proveedor;
    }

    function setConcepto($concepto) {
        $this->concepto = $concepto;
    }

    function setDocumento($documento) {
        $this->documento = $documento;
    }

    function setCantidad($cantidad) {
        $this->cantidad = $cantidad;
    }

    function setImporte($importe) {
        $this->importe = $importe;
    }

    function setImportesin($importesin) {
        $this->importesin = $importesin;
    }

    function setIva($iva) {
        $this->iva = $iva;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function getNombre() {
        return $this->nombre;
    }

    function getAlias() {
        return $this->alias;
    }

    function getTipodepago() {
        return $this->tipodepago;
    }

    function getDias_credito() {
        return $this->dias_credito;
    }

    function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    function setAlias($alias) {
        $this->alias = $alias;
    }

    function setTipodepago($tipodepago) {
        $this->tipodepago = $tipodepago;
    }

    function setDias_credito($dias_credito) {
        $this->dias_credito = $dias_credito;
    }

    function getProveedorde() {
        return $this->proveedorde;
    }

    function setProveedorde($proveedorde) {
        $this->proveedorde = $proveedorde;
    }

    function setObservaciones($observaciones) {
        $this->observaciones = $observaciones;
    }

}
