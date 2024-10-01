<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ComprasoeVO {
    private $id;
    private $fecha;
    private $fechav;
    private $proveedor;
    private $concepto;
    private $documento;
    private $cantidad;
    private $importe;
    private $iva;
    private $status;
    private $total;
 
    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getFecha() {
        return $this->fecha;
    }

    function getFechav() {
        return $this->fechav == null ? date("Y-m-d") : $this->fechav;
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

    function getIva() {
        return $this->iva == null ? 0 : $this->iva;
    }

    function getStatus() {
        return $this->status;
    }
    
    function getTotal() {
        return $this->total;
    }
    
    function setId($id) {
        $this->id = $id;
    }
    
    function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    function setFechav($fechav) {
        $this->fechav = $fechav;
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

    function setIva($iva) {
        $this->iva = $iva;
    }

    function setStatus($status) {
        $this->status = $status;
    }
    
    function setTotal($total) {
        $this->total = $total;
    }
}

    
    
    
    


