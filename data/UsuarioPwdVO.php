<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//namespace com\detisa\omicrom;

/**
 * Description of UsuariosPwd
 *
 * @author 3PX89LA_RS5
 */
class UsuarioPwdVO {
    private $id;
    private $idUsuario;
    private $password;
    private $creation;
    private $active;
    
    function __construct() {
       
    }
    
    function getId() {
        return $this->id;
    }

    function getIdUsuario() {
        return $this->idUsuario;
    }

    function getPassword() {
        return $this->password;
    }

    function getCreation() {
        return $this->creation;
    }

    function getActive() {
        return $this->active;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setIdUsuario($idUsuario) {
        $this->idUsuario = $idUsuario;
    }

    function setPassword($password) {
        $this->password = $password;
    }

    function setCreation($creation) {
        $this->creation = $creation;
    }

    function setActive($active) {
        $this->active = $active;
    }



}
