<?php

/**
 * Description of genbolVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class GenbolVO {

    private $id;
    private $fecha;
    private $cliente;
    private $fechav;
    private $cantidad;
    private $importe;
    private $status;
    private $recibe;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getFecha() {
        return $this->fecha;
    }

    function getCliente() {
        return $this->cliente;
    }

    function getFechav() {
        return $this->fechav;
    }

    function getCantidad() {
        return $this->cantidad == null ? 0 : $this->cantidad;
    }

    function getImporte() {
        return $this->importe == null ? 0 : $this->importe;
    }

    function getStatus() {
        return $this->status;
    }

    function getRecibe() {
        return $this->recibe;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    function setCliente($cliente) {
        $this->cliente = $cliente;
    }

    function setFechav($fechav) {
        $this->fechav = $fechav;
    }

    function setCantidad($cantidad) {
        $this->cantidad = $cantidad;
    }

    function setImporte($importe) {
        $this->importe = $importe;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function setRecibe($recibe) {
        $this->recibe = $recibe;
    }

}
