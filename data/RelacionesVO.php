<?php

/*
 * RelacionesVO
 * Detisa®
 * © 2017, Detisa 
 * http://www.detisa.com.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since sep 2017
 */

/**
 * Description of RelacionesVO
 *
 * @author Rolando Esquivel
 */
class RelacionesVO {
    private $folio;
    private $relacionado;
    private $tipoRelacion;
    private $uuid;
    
    function getFolio() {
        return $this->folio;
    }

    function getRelacionado() {
        return $this->relacionado;
    }

    function getTipoRelacion() {
        return $this->tipoRelacion;
    }

    function getUuid() {
        return $this->uuid;
    }

    function setFolio($folio) {
        $this->folio = $folio;
    }

    function setRelacionado($relacionado) {
        $this->relacionado = $relacionado;
    }

    function setTipoRelacion($tipoRelacion) {
        $this->tipoRelacion = $tipoRelacion;
    }

    function setUuid($uuid) {
        $this->uuid = $uuid;
    }

    public function __toString() {
        return "RelacionesVO={folio=".$this->folio.
                "relacionado=".$this->relacionado.
                "tipo=".$this->tipoRelacion.
                "uuid=".$this->uuid.
                "}";
    }

    function hasRelated() {
        return $this->uuid != NULL;
    }
}
