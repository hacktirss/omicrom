<?php

/**
 * Description of CtVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class CtVO {

    private $id;
    private $fecha;
    private $hora;
    private $fechaf;
    private $concepto;
    private $isla;
    private $turno;
    private $usr;
    private $status;
    private $statusctv;
    private $enviado;
    private $producto1;
    private $producto2;
    private $producto3;
    private $producto4;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getFecha() {
        return $this->fecha;
    }

    function getHora() {
        return $this->hora;
    }

    function getFechaf() {
        return $this->fechaf;
    }

    function getConcepto() {
        return $this->concepto;
    }

    function getIsla() {
        return $this->isla;
    }

    function getTurno() {
        return $this->turno;
    }

    function getUsr() {
        return $this->usr;
    }

    function getStatus() {
        return $this->status;
    }

    function getStatusctv() {
        return $this->statusctv;
    }

    function getEnviado() {
        return $this->enviado;
    }

    function getProducto1() {
        return $this->producto1;
    }

    function getProducto2() {
        return $this->producto2;
    }

    function getProducto3() {
        return $this->producto3;
    }

    function getProducto4() {
        return $this->producto4;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    function setHora($hora) {
        $this->hora = $hora;
    }

    function setFechaf($fechaf) {
        $this->fechaf = $fechaf;
    }

    function setConcepto($concepto) {
        $this->concepto = $concepto;
    }

    function setIsla($isla) {
        $this->isla = $isla;
    }

    function setTurno($turno) {
        $this->turno = $turno;
    }

    function setUsr($usr) {
        $this->usr = $usr;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function setStatusctv($statusctv) {
        $this->statusctv = $statusctv;
    }

    function setEnviado($enviado) {
        $this->enviado = $enviado;
    }

    function setProducto1($producto1) {
        $this->producto1 = $producto1;
    }

    function setProducto2($producto2) {
        $this->producto2 = $producto2;
    }

    function setProducto3($producto3) {
        $this->producto3 = $producto3;
    }

    function setProducto4($producto4) {
        $this->producto4 = $producto4;
    }


}
