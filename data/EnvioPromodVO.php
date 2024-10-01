<?php

/**
 * Description of EnvioPromodVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Alejandro Ayala Gonzalez
 * @version 1.0
 * @since oct 2023
 */
class EnvioPromodVO {

    private $id;
    private $idNvo;
    private $id_authuser;
    private $codigo;

    public function __construct() {
        
    }

    public function getId() {
        return $this->id;
    }

    public function getIdNvo() {
        return $this->idNvo;
    }

    public function getId_authuser() {
        return $this->id_authuser;
    }

    public function getCodigo() {
        return $this->codigo;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function setIdNvo($idNvo): void {
        $this->idNvo = $idNvo;
    }

    public function setId_authuser($id_authuser): void {
        $this->id_authuser = $id_authuser;
    }

    public function setCodigo($codigo): void {
        $this->codigo = $codigo;
    }

}
