<?php

/**
 * Description of Env_efectivoVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Alejandro Ayala Gonzalez
 * @version 1.0
 * @since ene 2023
 */
class Env_efectivoVO {

    private $id;
    private $id_banco = 0;
    private $descripcion;
    private $importe;
    private $fecha_envio;
    private $fecha_creacion = "";
    private $status;

    public function __construct() {
        
    }

    public function getId() {
        return $this->id;
    }

    public function getId_banco() {
        return $this->id_banco;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function getImporte() {
        return $this->importe;
    }

    public function getFecha_envio() {
        return $this->fecha_envio;
    }

    public function getFecha_creacion() {
        return $this->fecha_creacion;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function setId_banco($id_banco): void {
        $this->id_banco = $id_banco;
    }

    public function setDescripcion($descripcion): void {
        $this->descripcion = $descripcion;
    }

    public function setImporte($importe): void {
        $this->importe = $importe;
    }

    public function setFecha_envio($fecha_envio): void {
        $this->fecha_envio = $fecha_envio;
    }

    public function setFecha_creacion($fecha_creacion): void {
        $this->fecha_creacion = $fecha_creacion;
    }

    public function setStatus($status): void {
        $this->status = $status;
    }

}
