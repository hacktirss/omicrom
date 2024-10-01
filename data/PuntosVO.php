<?php

/**
 * Description of PuntosVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Ayala Gonzalez Alejandro
 * @version 1.0
 * @since ago 2023
 */
class PuntosVO {

    private $cliente;
    private $id;
    private $producto;
    private $puntos;
    private $fecha;
    private $status;
    private $id_periodo;

    function __construct() {
        
    }

    public function getCliente() {
        return $this->cliente;
    }

    public function getId() {
        return $this->id;
    }

    public function getProducto() {
        return $this->producto;
    }

    public function getPuntos() {
        return $this->puntos;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getId_periodo() {
        return $this->id_periodo;
    }

    public function setCliente($cliente): void {
        $this->cliente = $cliente;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function setProducto($producto): void {
        $this->producto = $producto;
    }

    public function setPuntos($puntos): void {
        $this->puntos = $puntos;
    }

    public function setFecha($fecha): void {
        $this->fecha = $fecha;
    }

    public function setStatus($status): void {
        $this->status = $status;
    }

    public function setId_periodo($id_periodo): void {
        $this->id_periodo = $id_periodo;
    }

}
