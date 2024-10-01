<?php

/**
 * Description of AlarmasVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class AlarmasVO {
    private $idAlarma;
    private $fechaAlarma;
    private $horaAlarma;
    private $componenteAlarma;
    private $tipoAlarma;
    private $descripcionAlarma;
    private $revisionAlarma;
    
    function __construct() {
        
    }
    
    function getIdAlarma() {
        return $this->idAlarma;
    }

    function getFechaAlarma() {
        return $this->fechaAlarma;
    }

    function getHoraAlarma() {
        return $this->horaAlarma;
    }

    function getComponenteAlarma() {
        return $this->componenteAlarma;
    }

    function getTipoAlarma() {
        return $this->tipoAlarma;
    }

    function getDescripcionAlarma() {
        return $this->descripcionAlarma;
    }

    function getRevisionAlarma() {
        return $this->revisionAlarma;
    }

    function setIdAlarma($idAlarma) {
        $this->idAlarma = $idAlarma;
    }

    function setFechaAlarma($fechaAlarma) {
        $this->fechaAlarma = $fechaAlarma;
    }

    function setHoraAlarma($horaAlarma) {
        $this->horaAlarma = $horaAlarma;
    }

    function setComponenteAlarma($componenteAlarma) {
        $this->componenteAlarma = $componenteAlarma;
    }

    function setTipoAlarma($tipoAlarma) {
        $this->tipoAlarma = $tipoAlarma;
    }

    function setDescripcionAlarma($descripcionAlarma) {
        $this->descripcionAlarma = $descripcionAlarma;
    }

    function setRevisionAlarma($revisionAlarma) {
        $this->revisionAlarma = $revisionAlarma;
    }

    public function __toString() {
        $data = "Alarma{id = " . $this->idAlarma. " , descripcion = " . $this->descripcionAlarma . "}";
        return $data;
    }
}
