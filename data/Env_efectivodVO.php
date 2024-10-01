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
class Env_efectivodVO {

    private $id;
    private $id_ee;
    private $monto;
    private $id_corte;

    public function __construct() {
        
    }

    public function getId() {
        return $this->id;
    }

    public function getId_ee() {
        return $this->id_ee;
    }

    public function getMonto() {
        return $this->monto;
    }

    public function getId_corte() {
        return $this->id_corte;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function setId_ee($id_ee): void {
        $this->id_ee = $id_ee;
    }

    public function setMonto($monto): void {
        $this->monto = $monto;
    }

    public function setId_corte($id_corte): void {
        $this->id_corte = $id_corte;
    }

}
