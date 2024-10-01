<?php

/**
 * Description of Cobranza_BeneficiosVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Ayala Gonzalez Alejandro
 * @version 1.0
 * @since ago 2023
 */
class Cobranza_beneficiosVO {

    private $id;
    private $id_beneficio;
    private $puntos;
    private $fecha;
    private $id_ticket_beneficio;
    private $tm;

    function __construct() {
        
    }

    public function getId() {
        return $this->id;
    }

    public function getId_beneficio() {
        return $this->id_beneficio;
    }

    public function getPuntos() {
        return $this->puntos;
    }

    public function getFecha() {
        return $this->fecha;
    }

    public function getId_ticket_beneficio() {
        return $this->id_ticket_beneficio;
    }

    public function getTm() {
        return $this->tm;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function setId_beneficio($id_beneficio): void {
        $this->id_beneficio = $id_beneficio;
    }

    public function setPuntos($puntos): void {
        $this->puntos = $puntos;
    }

    public function setFecha($fecha): void {
        $this->fecha = $fecha;
    }

    public function setId_ticket_beneficio($id_ticket_beneficio): void {
        $this->id_ticket_beneficio = $id_ticket_beneficio;
    }

    public function setTm($tm): void {
        $this->tm = $tm;
    }

}
