<?php

/**
 * Description of TerminalPosVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class TerminalPosVO {

    private $id;
    private $printed_serial;
    private $serial;
    private $model;
    private $ip;
    private $maclan;
    private $macwifi;
    private $kernel;
    private $status;
    private $appVersion;
    private $lastConnection;
    private $dispositivo;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getPrinted_serial() {
        return $this->printed_serial;
    }
    function getSerial() {
        return $this->serial;
    }

    function getModel() {
        return $this->model;
    }

    function getIp() {
        return $this->ip;
    }

    function getMaclan() {
        return $this->maclan == null ? "" : $this->maclan;
    }

    function getMacwifi() {
        return $this->macwifi == null ? "" : $this->macwifi;
    }

    function getKernel() {
        return $this->kernel == null ? "" : $this->kernel;
    }

    function getStatus() {
        return $this->status == null ? "I" : $this->status;
    }

    function getAppVersion() {
        return $this->appVersion == null ? "-----" : $this->appVersion;
    }

    function getLastConnection() {
        return $this->lastConnection;
    }

    function getDispositivo() {
        return $this->dispositivo == null ? "" : $this->dispositivo;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setPrinted_serial($printed_serial) {
        $this->printed_serial = $printed_serial;
    }
    
    function setSerial($serial) {
        $this->serial = $serial;
    }

    function setModel($model) {
        $this->model = $model;
    }

    function setIp($ip) {
        $this->ip = $ip;
    }

    function setMaclan($maclan) {
        $this->maclan = $maclan;
    }

    function setMacwifi($macwifi) {
        $this->macwifi = $macwifi;
    }

    function setKernel($kernel) {
        $this->kernel = $kernel;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function setAppVersion($appVersion) {
        $this->appVersion = $appVersion;
    }

    function setLastConnection($lastConnection) {
        $this->lastConnection = $lastConnection;
    }

    function setDispositivo($dispositivo) {
        $this->dispositivo = $dispositivo;
    }

}
