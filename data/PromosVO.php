<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class PromosVO {

    private $id;
    private $fecha_creacion;
    private $id_authuser;
    private $fecha_limite;
    private $minimo;
    private $tipo;
    private $codigo_promo;
    private $status;
    private $id_cli;
    private $importe;

    function __construct() {
        
    }

    public function getId() {
        return $this->id;
    }

    public function getFecha_creacion() {
        return $this->fecha_creacion;
    }

    public function getId_authuser() {
        return $this->id_authuser;
    }

    public function getFecha_limite() {
        return $this->fecha_limite;
    }

    public function getMinimo() {
        return $this->minimo;
    }

    public function getTipo() {
        return $this->tipo;
    }

    public function getCodigo_promo() {
        return $this->codigo_promo;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getId_cli() {
        return $this->id_cli;
    }

    public function getImporte() {
        return $this->importe;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function setFecha_creacion($fecha_creacion): void {
        $this->fecha_creacion = $fecha_creacion;
    }

    public function setId_authuser($id_authuser): void {
        $this->id_authuser = $id_authuser;
    }

    public function setFecha_limite($fecha_limite): void {
        $this->fecha_limite = $fecha_limite;
    }

    public function setMinimo($minimo): void {
        $this->minimo = $minimo;
    }

    public function setTipo($tipo): void {
        $this->tipo = $tipo;
    }

    public function setCodigo_promo($codigo_promo): void {
        $this->codigo_promo = $codigo_promo;
    }

    public function setStatus($status): void {
        $this->status = $status;
    }

    public function setId_cli($id_cli): void {
        $this->id_cli = $id_cli;
    }

    public function setImporte($importe): void {
        $this->importe = $importe;
    }

}
