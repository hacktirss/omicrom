<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class CxcMensualVO {

    private $id;
    private $anio;
    private $mesNo;
    private $mes;
    private $importe_deuda;
    private $fecha_analisis;
    private $id_cli;

    public function __construct() {
        
    }

    public function getAnio() {
        return $this->anio;
    }

    public function getId_cli() {
        return $this->id_cli;
    }

    public function setAnio($anio): void {
        $this->anio = $anio;
    }

    public function setId_cli($id_cli): void {
        $this->id_cli = $id_cli;
    }

    public function getId() {
        return $this->id;
    }

    public function getMesNo() {
        return $this->mesNo;
    }

    public function getMes() {
        return $this->mes;
    }

    public function getImporte_deuda() {
        return $this->importe_deuda;
    }

    public function getFecha_analisis() {
        return $this->fecha_analisis;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function setMesNo($mesNo): void {
        $this->mesNo = $mesNo;
    }

    public function setMes($mes): void {
        $this->mes = $mes;
    }

    public function setImporte_deuda($importe_deuda): void {
        $this->importe_deuda = $importe_deuda;
    }

    public function setFecha_analisis($fecha_analisis): void {
        $this->fecha_analisis = $fecha_analisis;
    }

}
