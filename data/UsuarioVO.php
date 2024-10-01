<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

//namespace com\detisa\omicrom;

/**
 * Description of BitacoraVO
 *
 * @author lino
 */
class UsuarioVO {

    private $idUsuario;
    private $nombre;
    private $username;
    private $password;
    private $rol = 3;
    private $team = "Operador";
    private $level = 8;
    private $status = "active";
    private $lastlogin;
    private $lastactivity;
    private $count = 0;
    private $creation;
    private $locked = 0;
    private $alive = 0;
    private $mail = "";
    private $difference = 0;
    private $idLocation;

    function __construct() {
        
    }

    function getId() {
        return $this->idUsuario;
    }

    function getNombre() {
        return $this->nombre;
    }

    function getUsername() {
        return $this->username;
    }

    function getPassword() {
        return $this->password;
    }

    function getTeam() {
        return $this->team;
    }

    function getLevel() {
        return $this->level;
    }

    function getStatus() {
        return $this->status;
    }

    function getLastlogin() {
        return $this->lastlogin;
    }

    function getCount() {
        return $this->count;
    }

    function getCreation() {
        return $this->creation;
    }

    function getLocked() {
        return $this->locked;
    }

    function getMail() {
        return $this->mail;
    }

    function getIdLocation() {
        return $this->idLocation;
    }

    function setId($idUsuario) {
        $this->idUsuario = $idUsuario;
    }

    function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    function setUsername($username) {
        $this->username = $username;
    }

    function setPassword($password) {
        $this->password = $password;
    }

    function setTeam($team) {
        $this->team = $team;
    }

    function setLevel($level) {
        $this->level = $level;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function setLastlogin($lastlogin) {
        $this->lastlogin = $lastlogin;
    }

    function setCount($count) {
        $this->count = $count;
    }

    function setCreation($creation) {
        $this->creation = $creation;
    }

    function setLocked($locked) {
        $this->locked = $locked;
    }

    function setMail($mail) {
        $this->mail = $mail;
    }

    function getAlive() {
        return $this->alive;
    }

    function setAlive($alive) {
        $this->alive = $alive;
    }

    function getLastactivity() {
        return $this->lastactivity;
    }

    function setLastactivity($lastactivity) {
        $this->lastactivity = $lastactivity;
    }

    function getIdUsuario() {
        return $this->idUsuario;
    }

    function getRol() {
        return $this->rol;
    }

    function getDifference() {
        return $this->difference;
    }

    function setIdUsuario($idUsuario) {
        $this->idUsuario = $idUsuario;
    }

    function setRol($rol) {
        $this->rol = $rol;
    }

    function setDifference($difference) {
        $this->difference = $difference;
    }

    function setIdLocation($idLocation) {
        $this->idLocation = $idLocation;
    }

    public function __toString() {
        $objectClass = "{idUser = " . $this->idUsuario . ", uname = " . $this->nombre . ", lastlogin = " . $this->lastlogin . "}";
        return $objectClass;
    }

}
