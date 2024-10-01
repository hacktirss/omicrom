<?php

/**
 * Description of CargasVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Alejandro Ayala Gonzalez
 * @version 1.0
 * @since mar 2022
 */
class ComisionesVO {

    private $id;
    private $id_prv;
    private $id_com;
    private $vigencia;
    private $monto;
    private $vigenciafin;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getId_prv() {
        return $this->id_prv;
    }

    function getId_com() {
        return $this->id_com;
    }

    function getVigencia() {
        return $this->vigencia;
    }

    function getMonto() {
        return $this->monto;
    }

    function getVigenciafin() {
        return $this->vigenciafin;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setId_prv($id_prv) {
        $this->id_prv = $id_prv;
    }

    function setId_com($id_com) {
        $this->id_com = $id_com;
    }

    function setVigencia($vigencia) {
        $this->vigencia = $vigencia;
    }

    function setMonto($monto) {
        $this->monto = $monto;
    }

    function setVigenciafin($vigenciafin) {
        $this->vigenciafin = $vigenciafin;
    }

}
