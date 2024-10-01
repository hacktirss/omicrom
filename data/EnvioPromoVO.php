<?php

/*
 * EnvioPromoVO
 * omicrom
 *  2017, Detisa 
 * http://www.detisa.com.mx
 * @author Ayala Gonzalez Alejandro
 * @version 1.0
 * @since oct 2023
 */

class EnvioPromoVO {

    private $id;
    private $descripcion;
    private $fecha_creacion;
    private $fecha_inicio;
    private $fecha_final;
    private $descuento;
    private $id_producto;
    private $id_user;
    private $consumo_min;
    private $status;

    public function __construct() {
        
    }

    public function getId() {
        return $this->id;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function getFecha_creacion() {
        return $this->fecha_creacion;
    }

    public function getFecha_inicio() {
        return $this->fecha_inicio;
    }

    public function getFecha_final() {
        return $this->fecha_final;
    }

    public function getDescuento() {
        return $this->descuento;
    }

    public function getId_producto() {
        return $this->id_producto;
    }

    public function getId_user() {
        return $this->id_user;
    }

    public function getConsumo_min() {
        return $this->consumo_min;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function setDescripcion($descripcion): void {
        $this->descripcion = $descripcion;
    }

    public function setFecha_creacion($fecha_creacion): void {
        $this->fecha_creacion = $fecha_creacion;
    }

    public function setFecha_inicio($fecha_inicio): void {
        $this->fecha_inicio = $fecha_inicio;
    }

    public function setFecha_final($fecha_final): void {
        $this->fecha_final = $fecha_final;
    }

    public function setDescuento($descuento): void {
        $this->descuento = $descuento;
    }

    public function setId_producto($id_producto): void {
        $this->id_producto = $id_producto;
    }

    public function setId_user($id_user): void {
        $this->id_user = $id_user;
    }

    public function setConsumo_min($consumo_min): void {
        $this->consumo_min = $consumo_min;
    }

    public function setStatus($status): void {
        $this->status = $status;
    }

}
