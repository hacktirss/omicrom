<?php

/**
 * Description of TanqueVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class TanqueVO {

    private $id;
    private $tanque = 1;
    private $producto;
    private $clave_producto;
    private $volumen_actual;
    private $volumen_faltante;
    private $volumen_operativo;
    private $capacidad_total;
    private $volumen_minimo;
    private $volumen_fondaje;
    private $presion = 0;
    private $altura = 0;
    private $agua = 0;
    private $temperatura = 0;
    private $fecha_hora_veeder;
    private $fecha_hora_s;
    private $estado = 1;
    private $procesado = 0;
    private $cargando = 0;
    private $volumen = 0;
    private $vigencia_calibracion;
    private $prefijo_sat = "TQS";
    private $sistema_medicion = "SME";
    private $sensor = "";
    private $incertidumbre_sensor = "0.00";
    private $descripcion = "-";
    private $idProveedor = 0;
    private $idProveedorSesor = 0;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getTanque() {
        return $this->tanque;
    }

    function getProducto() {
        return $this->producto;
    }

    function getClave_producto() {
        return $this->clave_producto;
    }

    function getVolumen_actual() {
        return $this->volumen_actual;
    }

    function getVolumen_faltante() {
        return $this->volumen_faltante;
    }

    function getVolumen_operativo() {
        return $this->volumen_operativo;
    }

    function getCapacidad_total() {
        return $this->capacidad_total;
    }

    function getVolumen_minimo() {
        return $this->volumen_minimo;
    }

    function getVolumen_fondaje() {
        return $this->volumen_fondaje;
    }

    function getPresion() {
        return $this->presion;
    }

    function getAltura() {
        return $this->altura;
    }

    function getAgua() {
        return $this->agua;
    }

    function getTemperatura() {
        return $this->temperatura;
    }

    function getFecha_hora_veeder() {
        return $this->fecha_hora_veeder;
    }

    function getFecha_hora_s() {
        return $this->fecha_hora_s;
    }

    function getEstado() {
        return $this->estado;
    }

    function getProcesado() {
        return $this->procesado;
    }

    function getCargando() {
        return $this->cargando;
    }

    function getVolumen() {
        return $this->volumen;
    }

    function getVigencia_calibracion() {
        return empty($this->vigencia_calibracion) ? date("Y-m-d") : $this->vigencia_calibracion;
    }

    function getDescripcion() {
        return $this->descripcion;
    }

    function getIdProveedor() {
        return $this->idProveedor;
    }

    function getIdProveedorSensor() {
        return $this->idProveedorSesor;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setTanque($tanque) {
        $this->tanque = $tanque;
    }

    function setProducto($producto) {
        $this->producto = $producto;
    }

    function setClave_producto($clave_producto) {
        $this->clave_producto = $clave_producto;
    }

    function setVolumen_actual($volumen_actual) {
        $this->volumen_actual = $volumen_actual;
    }

    function setVolumen_faltante($volumen_faltante) {
        $this->volumen_faltante = $volumen_faltante;
    }

    function setVolumen_operativo($volumen_operativo) {
        $this->volumen_operativo = $volumen_operativo;
    }

    function setCapacidad_total($capacidad_total) {
        $this->capacidad_total = $capacidad_total;
    }

    function setVolumen_minimo($volumen_minimo) {
        $this->volumen_minimo = $volumen_minimo;
    }

    function setVolumen_fondaje($volumen_fondaje) {
        $this->volumen_fondaje = $volumen_fondaje;
    }

    function setPresion($presion) {
        $this->presion = $presion;
    }

    function setAltura($altura) {
        $this->altura = $altura;
    }

    function setAgua($agua) {
        $this->agua = $agua;
    }

    function setTemperatura($temperatura) {
        $this->temperatura = $temperatura;
    }

    function setFecha_hora_veeder($fecha_hora_veeder) {
        $this->fecha_hora_veeder = $fecha_hora_veeder;
    }

    function setFecha_hora_s($fecha_hora_s) {
        $this->fecha_hora_s = $fecha_hora_s;
    }

    function setEstado($estado) {
        $this->estado = $estado;
    }

    function setProcesado($procesado) {
        $this->procesado = $procesado;
    }

    function setCargando($cargando) {
        $this->cargando = $cargando;
    }

    function setVolumen($volumen) {
        $this->volumen = $volumen;
    }

    function setVigencia_calibracion($vigencia_calibracion) {
        $this->vigencia_calibracion = $vigencia_calibracion;
    }

    function getPrefijo_sat() {
        return $this->prefijo_sat;
    }

    function getSistema_medicion() {
        return $this->sistema_medicion;
    }

    function getSensor() {
        return $this->sensor;
    }

    function getIncertidumbre_sensor() {
        return $this->incertidumbre_sensor;
    }

    function setPrefijo_sat($prefijo_sat) {
        $this->prefijo_sat = $prefijo_sat;
    }

    function setSistema_medicion($sistema_medicion) {
        $this->sistema_medicion = $sistema_medicion;
    }

    function setSensor($sensor) {
        $this->sensor = $sensor;
    }

    function setIncertidumbre_sensor($incertidumbre_sensor) {
        $this->incertidumbre_sensor = $incertidumbre_sensor;
    }

    function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    function setIdProveedor($idProveedor) {
        $this->idProveedor = $idProveedor;
    }

    function setIdProveedorSesor($idProveedorSesor) {
        $this->idProveedorSesor = $idProveedorSesor;
    }

}
