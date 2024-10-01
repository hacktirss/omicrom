<?php

/**
 * Description of PedidosVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Ayala Gonzalez Alejandro
 * @version 1.0
 * @since oct 2022
 */
class PedidosVO {

    private $id;
    private $id_user = 0;
    private $fecha;
    private $volumen;
    private $producto;
    private $status;
    private $fechafin;
    private $terminal_almacenamiento = 0;
    private $alert;

    public function __construct() {
        
    }

    public function getId() {
        return $this->id;
    }

    public function getId_user() {
        return $this->id_user;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function getVolumen() {
        return $this->volumen;
    }

    public function getProducto() {
        return $this->producto;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getFechafin() {
        return $this->fechafin;
    }

    public function getTerminal_almacenamiento() {
        return $this->terminal_almacenamiento;
    }

    public function getAlert() {
        return $this->alert;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function setId_user($id_user): void {
        $this->id_user = $id_user;
    }

    public function setFecha($fecha): void {
        $this->fecha = $fecha;
    }

    public function setVolumen($volumen): void {
        $this->volumen = $volumen;
    }

    public function setProducto($producto): void {
        $this->producto = $producto;
    }

    public function setStatus($status): void {
        $this->status = $status;
    }

    public function setFechafin($fechafin): void {
        $this->fechafin = $fechafin;
    }

    public function setTerminal_almacenamiento($terminal_almacenamiento): void {
        $this->terminal_almacenamiento = $terminal_almacenamiento;
    }

    public function setAlert($alert): void {
        $this->alert = $alert;
    }

}
