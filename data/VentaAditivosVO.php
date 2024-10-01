<?php

/**
 * Description of VentaAditivosVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class VentaAditivosVO {

    private $id;
    private $clave;
    private $cantidad;
    private $unitario;
    private $costo;
    private $total;
    private $corte;
    private $posicion;
    private $fecha;
    private $descripcion;
    private $cliente;
    private $vendedor;
    private $referencia;
    private $pagado;
    private $codigo;
    private $iva;
    private $uuid;
    private $enviado;
    private $tm;
    private $datalist;
    private $enviado_grupo;
    private $comentarios;
    private $idtransaccion;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getProducto() {
        return $this->clave;
    }

    function getCantidad() {
        return $this->cantidad;
    }

    function getUnitario() {
        return $this->unitario;
    }

    function getCosto() {
        return $this->costo;
    }

    function getTotal() {
        return $this->total;
    }

    function getCorte() {
        return $this->corte;
    }

    function getPosicion() {
        return $this->posicion;
    }

    function getFecha() {
        return $this->fecha;
    }

    function getDescripcion() {
        return $this->descripcion;
    }

    function getCliente() {
        return $this->cliente;
    }

    function getVendedor() {
        return $this->vendedor;
    }

    function getReferencia() {
        return $this->referencia;
    }

    function getIva() {
        return $this->iva;
    }

    function getUuid() {
        return $this->uuid;
    }

    function getEnviado() {
        return $this->enviado;
    }

    function getTm() {
        return $this->tm;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setEnviado_grupo($enviado_grupo) {
        $this->enviado_grupo = $enviado_grupo;
    }

    function setComentarios($comentarios) {
        $this->comentarios = $comentarios;
    }

    function setIdtransaccion($idtransaccion) {
        $this->idtransaccion = $idtransaccion;
    }

    function setProducto($clave) {
        $this->clave = $clave;
    }

    function setCantidad($cantidad) {
        $this->cantidad = $cantidad;
    }

    function setUnitario($unitario) {
        $this->unitario = $unitario;
    }

    function setCosto($costo) {
        $this->costo = $costo;
    }

    function setTotal($total) {
        $this->total = $total;
    }

    function setCorte($corte) {
        $this->corte = $corte;
    }

    function setPosicion($posicion) {
        $this->posicion = $posicion;
    }

    function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    function setCliente($cliente) {
        $this->cliente = $cliente;
    }

    function setVendedor($vendedor) {
        $this->vendedor = $vendedor;
    }

    function setReferencia($referencia) {
        $this->referencia = $referencia;
    }

    function setIva($iva) {
        $this->iva = $iva;
    }

    function setUuid($uuid) {
        $this->uuid = $uuid;
    }

    function setEnviado($enviado) {
        $this->enviado = $enviado;
    }

    function setTm($tm) {
        $this->tm = $tm;
    }

    function getClave() {
        return $this->clave;
    }

    function getPagado() {
        return $this->pagado;
    }

    function setClave($clave) {
        $this->clave = $clave;
    }

    function setPagado($pagado) {
        $this->pagado = $pagado;
    }

    function getCodigo() {
        return $this->codigo;
    }

    function setCodigo($codigo) {
        $this->codigo = $codigo;
    }

    function getDatalist() {
        return $this->datalist;
    }

    function setDatalist($datalist) {
        $this->datalist = $datalist;
    }

    function getEnviado_grupo() {
        return $this->enviado_grupo;
    }

    function getComentarios() {
        return $this->comentarios;
    }

    function getIdtransaccion() {
        return $this->idtransaccion;
    }

}
