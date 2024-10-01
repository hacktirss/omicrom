<?php

/**
 * Description of TurnoVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class TurnoVO {

    private $id;
    private $isla;
    private $turno;
    private $descripcion;
    private $horai;
    private $horaf;
    private $activo;
    private $cortea;

    function __construct() {
        
    }

    public function getId() {
        return $this->id;
    }

    public function getIsla() {
        return $this->isla;
    }

    public function getTurno() {
        return $this->turno;
    }

    public function getDescripcion() {
        return $this->descripcion;
    }

    public function getHorai() {
        return $this->horai;
    }

    public function getHoraf() {
        return $this->horaf;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function getCortea() {
        return $this->cortea;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function setIsla($isla): void {
        $this->isla = $isla;
    }

    public function setTurno($turno): void {
        $this->turno = $turno;
    }

    public function setDescripcion($descripcion): void {
        $this->descripcion = $descripcion;
    }

    public function setHorai($horai): void {
        $this->horai = $horai;
    }

    public function setHoraf($horaf): void {
        $this->horaf = $horaf;
    }

    public function setActivo($activo): void {
        $this->activo = $activo;
    }

    public function setCortea($cortea): void {
        $this->cortea = $cortea;
    }

}
