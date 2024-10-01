<?php

/**
 * 
 */
class PermisoCreVO {

    private $id;
    private $catalogo = "";
    private $llave = null;
    private $permiso = null;
    private $descripcion = "";
    private $padre = 0;
    private $estado = 1;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getCatalogo() {
        return $this->catalogo;
    }

    function getLlave() {
        return $this->llave;
    }

    function getPermiso() {
        return $this->permiso;
    }

    function getDescripcion() {
        return $this->descripcion;
    }

    function getPadre() {
        return $this->padre;
    }

    function getEstado() {
        return $this->estado;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setCatalogo($catalogo) {
        $this->catalogo = $catalogo;
    }

    function setLlave($llave) {
        $this->llave = $llave;
    }

    function setPermiso($permiso) {
        $this->permiso = $permiso;
    }

    function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    function setPadre($padre) {
        $this->padre = $padre;
    }

    function setEstado($estado) {
        $this->estado = $estado;
    }

}
