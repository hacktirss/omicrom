<?php

/**
 * Description of DictamenVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class DictamenVO {

    private $id;
    private $proveedor;
    private $lote;
    private $numeroFolio;
    private $fechaEmision;
    private $resultado = "";
    private $noCarga;
    private $estado = 0;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getProveedor() {
        return $this->proveedor;
    }

    function getLote() {
        return $this->lote;
    }

    function getNumeroFolio() {
        return $this->numeroFolio;
    }

    function getFechaEmision() {
        return empty($this->fechaEmision) ? date("Y-m-d") : $this->fechaEmision;
    }

    function getResultado() {
        return $this->resultado;
    }

    function getNoCarga() {
        return $this->noCarga;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setProveedor($proveedor) {
        $this->proveedor = $proveedor;
    }

    function setLote($lote) {
        $this->lote = $lote;
    }

    function setNumeroFolio($numeroFolio) {
        $this->numeroFolio = $numeroFolio;
    }

    function setFechaEmision($fechaEmision) {
        $this->fechaEmision = $fechaEmision;
    }

    function setResultado($resultado) {
        $this->resultado = $resultado;
    }
    function setNoCarga($noCarga) {
        $this->noCarga = $noCarga;
    }
    
    function getEstado() {
        return $this->estado;
    }

    function setEstado($estado) {
        $this->estado = $estado;
    }

}
