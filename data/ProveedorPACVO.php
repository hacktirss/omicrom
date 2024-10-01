<?php

/*
 * ProveedorPACVO
 * omicrom®
 * © 2017, Detisa 
 * http://www.detisa.com.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since jul 2017
 */

class ProveedorPACVO {
    private $id_pac;
    private $clave_pac;
    private $nombre_pac;
    private $url_webservice;
    private $usuario;
    private $password;
    private $clave_aux;
    private $clave_aux2;
    private $activo;
    private $pruebas;
    private $prioridad;
    
    function getId_pac() {
        return $this->id_pac;
    }

    function getClave_pac() {
        return $this->clave_pac;
    }

    function getNombre_pac() {
        return $this->nombre_pac;
    }

    function getUrl_webservice() {
        return $this->url_webservice;
    }

    function getUsuario() {
        return $this->usuario;
    }

    function getPassword() {
        return $this->password;
    }

    function getClave_aux() {
        return $this->clave_aux;
    }

    function getClave_aux2() {
        return $this->clave_aux2;
    }

    function getActivo() {
        return $this->activo;
    }

    function getPruebas() {
        return $this->pruebas;
    }

    function getPrioridad() {
        return $this->prioridad;
    }

    function setId_pac($id_pac) {
        $this->id_pac = $id_pac;
    }

    function setClave_pac($clave_pac) {
        $this->clave_pac = $clave_pac;
    }

    function setNombre_pac($nombre_pac) {
        $this->nombre_pac = $nombre_pac;
    }

    function setUrl_webservice($url_webservice) {
        $this->url_webservice = $url_webservice;
    }

    function setUsuario($usuario) {
        $this->usuario = $usuario;
    }

    function setPassword($password) {
        $this->password = $password;
    }

    function setClave_aux($clave_aux) {
        $this->clave_aux = $clave_aux;
    }

    function setClave_aux2($clave_aux2) {
        $this->clave_aux2 = $clave_aux2;
    }

    function setActivo($activo) {
        $this->activo = $activo;
    }

    function setPruebas($pruebas) {
        $this->pruebas = $pruebas;
    }

    function setPrioridad($prioridad) {
        $this->prioridad = $prioridad;
    }
}
