<?php

/**
 * Description of Uso_webVO
 * omicrom®
 * © 2023, Detisa 
 * http://www.detisa.com.mx
 * @author Alejandro Ayala Gonzalez
 * @version 1.0
 * @since abr 2023
 */
class Uso_webVO {

    private $idNvo;
    private $id;
    private $origen = '-';
    private $fecha = '-';
    private $id_authuser = 0;

    public function __construct() {
        
    }

    public function getIdNvo() {
        return $this->idNvo;
    }

    public function getId() {
        return $this->id;
    }

    public function getOrigen() {
        return $this->origen;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function getId_authuser() {
        return $this->id_authuser;
    }

    public function setIdNvo($idNvo): void {
        $this->idNvo = $idNvo;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function setOrigen($origen): void {
        $this->origen = $origen;
    }

    public function setFecha($fecha): void {
        $this->fecha = $fecha;
    }

    public function setId_authuser($id_authuser): void {
        $this->id_authuser = $id_authuser;
    }

}
