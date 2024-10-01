<?php

/*
 * MetodoDePagoVO
 * common®
 * © 2017, Detisa 
 * http://www.detisa.com.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since ago 2017
 */

class MetodoDePagoVO {
    private $clave;
    private $descripcion;
    
    function getClave() {
        return $this->clave;
    }

    function getDescripcion() {
        return $this->descripcion;
    }

    function setClave($clave) {
        $this->clave = $clave;
    }

    function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    public function __toString() {
        return "MetodoDePagoVO={"
        . "clave=".$this->clave
        . ", descripcion=".$this->descripcion
        . "}";
    }
}
