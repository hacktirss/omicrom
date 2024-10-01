<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//namespace com\detisa\omicrom;

/**
 * Description of UsuarioPerfilVO
 *
 * @author 3PX89LA_RS5
 */
class UsuarioPerfilVO {
    private $id;
    private $idUsuario;
    private $menuEstacion;
    private $menuCxc;
    private $menuCatalogos;
    private $menuReportes;
    private $menuLateral;
    private $menuCambioTurno;
    private $menuGraficas;
    private $menuCxp;
    private $menuPolizas;
    private $menuConfiguracion;
    
    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getIdUsuario() {
        return $this->idUsuario;
    }

    function getMenuEstacion() {
        return $this->menuEstacion;
    }

    function getMenuCxc() {
        return $this->menuCxc;
    }

    function getMenuCatalogos() {
        return $this->menuCatalogos;
    }

    function getMenuReportes() {
        return $this->menuReportes;
    }

    function getMenuLateral() {
        return $this->menuLateral;
    }

    function getMenuCambioTurno() {
        return $this->menuCambioTurno;
    }

    function getMenuGraficas() {
        return $this->menuGraficas;
    }

    function getMenuCxp() {
        return $this->menuCxp;
    }

    function getMenuPolizas() {
        return $this->menuPolizas;
    }

    function getMenuConfiguracion() {
        return $this->menuConfiguracion;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setIdUsuario($idUsuario) {
        $this->idUsuario = $idUsuario;
    }

    function setMenuEstacion($menuEstacion) {
        $this->menuEstacion = $menuEstacion;
    }

    function setMenuCxc($menuCxc) {
        $this->menuCxc = $menuCxc;
    }

    function setMenuCatalogos($menuCatalogos) {
        $this->menuCatalogos = $menuCatalogos;
    }

    function setMenuReportes($menuReportes) {
        $this->menuReportes = $menuReportes;
    }

    function setMenuLateral($menuLateral) {
        $this->menuLateral = $menuLateral;
    }

    function setMenuCambioTurno($menuCambioTurno) {
        $this->menuCambioTurno = $menuCambioTurno;
    }

    function setMenuGraficas($menuGraficas) {
        $this->menuGraficas = $menuGraficas;
    }

    function setMenuCxp($menuCxp) {
        $this->menuCxp = $menuCxp;
    }

    function setMenuPolizas($menuPolizas) {
        $this->menuPolizas = $menuPolizas;
    }

    function setMenuConfiguracion($menuConfiguracion) {
        $this->menuConfiguracion = $menuConfiguracion;
    }

}
