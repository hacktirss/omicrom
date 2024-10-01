<?php

/*
 * PeriodoPuntosVO
 * omicrom
 *  2017, Detisa 
 * http://www.detisa.com.mx
 * @author Ayala Gonzalez Alejandro
 * @version 1.0
 * @since nov 2022
 */

class PeriodoPuntosVO {

    private $id;
    private $descripcion;
    private $fecha_inicial;
    private $fecha_culmina;
    private $fecha_final;
    private $activo;
    private $tipo_concentrado;
    private $monto_promocion;
    private $limite_inferior;
    private $limite_superior;
    private $tipo_periodo;
    private $producto_promocion;
    private $factores_producto;
    private $limites_inferiores;

    public function __construct() {
        $this->descripcion = "Promocion tipo :";
        $this->fecha_culmina = date("Y-m-d");
        $this->fecha_final = date("Y-m-d");
        $this->fecha_inicial = date("Y-m-d");
        $this->activo = 1;
        $this->tipo_concentrado = "V";
        $this->monto_promocion = 1;
        $this->limite_inferior = 0;
        $this->limite_superior = 0;
        $this->tipo_periodo = "A";
        $this->producto_promocion = 'P,M,D,A';
        $this->factores_producto = "1.00,1.00,1.00,0.00";
        $this->limites_inferiores = "1.00,1.00,1.00,0.00";
    }

    public function getLimites_inferiores() {
        return $this->limites_inferiores;
    }

    public function setLimites_inferiores($limites_inferiores): void {
        $this->limites_inferiores = $limites_inferiores;
    }

    public function getId() {
        return $this->id;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function getFecha_inicial() {
        return $this->fecha_inicial;
    }

    public function getFecha_culmina() {
        return $this->fecha_culmina;
    }

    public function getFecha_final() {
        return $this->fecha_final;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function getTipo_concentrado() {
        return $this->tipo_concentrado;
    }

    public function getMonto_promocion() {
        return $this->monto_promocion;
    }

    public function getLimite_inferior() {
        return $this->limite_inferior;
    }

    public function getLimite_superior() {
        return $this->limite_superior;
    }

    public function getTipo_periodo() {
        return $this->tipo_periodo;
    }

    public function getProducto_promocion() {
        return $this->producto_promocion;
    }

    public function getFactores_producto() {
        return $this->factores_producto;
    }

    public function setTipo_periodo($tipo_periodo) {
        $this->tipo_periodo = $tipo_periodo;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function setDescripcion($descripcion): void {
        $this->descripcion = $descripcion;
    }

    public function setFecha_inicial($fecha_inicial): void {
        $this->fecha_inicial = $fecha_inicial;
    }

    public function setFecha_culmina($fecha_culmina): void {
        $this->fecha_culmina = $fecha_culmina;
    }

    public function setFecha_final($fecha_final): void {
        $this->fecha_final = $fecha_final;
    }

    public function setActivo($activo): void {
        $this->activo = $activo;
    }

    public function setTipo_concentrado($tipo_concentrado): void {
        $this->tipo_concentrado = $tipo_concentrado;
    }

    public function setMonto_promocion($monto_promocion): void {
        $this->monto_promocion = $monto_promocion;
    }

    public function setLimite_inferior($limite_inferior): void {
        $this->limite_inferior = $limite_inferior;
    }

    public function setLimite_superior($limite_superior): void {
        $this->limite_superior = $limite_superior;
    }

    public function setProducto_promocion($producto_promocion): void {
        $this->producto_promocion = $producto_promocion;
    }

    public function setFactores_producto($factores_producto): void {
        $this->factores_producto = $factores_producto;
    }

}
