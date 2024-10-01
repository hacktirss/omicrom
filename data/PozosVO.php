<?php

/*
 * PozosVO
 * omicrom
 *  2017, Detisa 
 * http://www.detisa.com.mx
 * @author Ayala Gonzalez Alejandro
 * @version 1.0
 * @since sep 2022
 */

class PozosVO {

    private $id;
    private $descripcion;
    private $clave_sistema_medicion;
    private $descripcion_sistema_medicion;
    private $vigencia_sistema_medicion;
    private $incertidumbre_sistema_medicion;

    public function __construct() {
        
    }

    public function getId() {
        return $this->id;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function getClave_sistema_medicion() {
        return $this->clave_sistema_medicion;
    }

    public function getDescripcion_sistema_medicion() {
        return $this->descripcion_sistema_medicion;
    }

    public function getVigencia_sistema_medicion() {
        return $this->vigencia_sistema_medicion;
    }

    public function getIncertidumbre_sistema_medicion() {
        return $this->incertidumbre_sistema_medicion;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function setDescripcion($descripcion): void {
        $this->descripcion = $descripcion;
    }

    public function setClave_sistema_medicion($clave_sistema_medicion): void {
        $this->clave_sistema_medicion = $clave_sistema_medicion;
    }

    public function setDescripcion_sistema_medicion($descripcion_sistema_medicion): void {
        $this->descripcion_sistema_medicion = $descripcion_sistema_medicion;
    }

    public function setVigencia_sistema_medicion($vigencia_sistema_medicion): void {
        $this->vigencia_sistema_medicion = $vigencia_sistema_medicion;
    }

    public function setIncertidumbre_sistema_medicion($incertidumbre_sistema_medicion): void {
        $this->incertidumbre_sistema_medicion = $incertidumbre_sistema_medicion;
    }
}
