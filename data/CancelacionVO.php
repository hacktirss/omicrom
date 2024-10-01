<?php

/**
 * Description of CancelacionVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Ayala Gonzalez Alejandro
 * @version 1.0
 * @since oct 2023
 */
class CancelacionVO {

    private $id;
    private $tabla;
    private $id_origen;
    private $descripcion_evento;
    private $fecha_registro;

    public function __construct() {
        
    }

    public function getId() {
        return $this->id;
    }

    public function getTabla() {
        return $this->tabla;
    }

    public function getId_origen() {
        return $this->id_origen;
    }

    public function getDescripcion_evento() {
        return $this->descripcion_evento;
    }

    public function getFecha_registro() {
        return $this->fecha_registro;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function setTabla($tabla): void {
        $this->tabla = $tabla;
    }

    public function setId_origen($id_origen): void {
        $this->id_origen = $id_origen;
    }

    public function setDescripcion_evento($descripcion_evento): void {
        $this->descripcion_evento = $descripcion_evento;
    }

    public function setFecha_registro($fecha_registro): void {
        $this->fecha_registro = $fecha_registro;
    }

}
