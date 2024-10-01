<?php

/**
 * Description of DuctosVO
 * omicrom®
 * © 2021, Detisa 
 * http://www.detisa.com.mx
 * @author Alejandro Ayala Gonzalez
 * @version 1.0
 * @since mar 2021
 */
class DuctosVO {

    private $id_ducto;
    private $tipo_ducto = 0;
    private $clave_identificacion_ducto = "TRA";
    private $descripcion_ducto;
    private $diametro_ducto = 0.0;
    private $descripcion_tipo_ducto = "";
    private $clave_instalacion;
    private $cve_producto_sat_ducto = "PR07";
    private $almacenamiento_ducto = "0.00";
    private $vigencia_calibracion_ducto;
    private $sistema_medicion;
    private $medidor;

    function __construct() {
        
    }

    function getId_ducto() {
        return $this->id_ducto;
    }

    function getTipo_ducto() {
        return $this->tipo_ducto;
    }

    function getClave_identificacion_ducto() {
        return $this->clave_identificacion_ducto;
    }

    function getDescripcion_ducto() {
        return $this->descripcion_ducto;
    }

    function getDiametro_ducto() {
        return $this->diametro_ducto;
    }

    function getDescripcion_tipo_ducto() {
        return $this->descripcion_tipo_ducto;
    }

    function getClave_instalacion() {
        return $this->clave_instalacion;
    }

    function getCve_producto_sat_ducto() {
        return $this->cve_producto_sat_ducto;
    }

    function getAlmacenamiento_ducto() {
        return $this->almacenamiento_ducto;
    }

    function getVigencia_calibracion_ducto() {
        return $this->vigencia_calibracion_ducto;
    }

    function getMedidor() {
        return $this->medidor;
    }

    function getSistema_medicion() {
        return $this->sistema_medicion;
    }

    function setSistema_medicion($sistema_medicion) {
        $this->sistema_medicion = $sistema_medicion;
    }

    function setMedidor($medidor) {
        $this->medidor = $medidor;
    }

    function setId_ducto($id_ducto) {
        $this->id_ducto = $id_ducto;
    }

    function setTipo_ducto($tipo_ducto) {
        $this->tipo_ducto = $tipo_ducto;
    }

    function setClave_identificacion_ducto($clave_identificacion_ducto) {
        $this->clave_identificacion_ducto = $clave_identificacion_ducto;
    }

    function setDescripcion_ducto($descripcion_ducto) {
        $this->descripcion_ducto = $descripcion_ducto;
    }

    function setDiametro_ducto($diametro_ducto) {
        $this->diametro_ducto = $diametro_ducto;
    }

    function setDescripcion_tipo_ducto($descripcion_tipo_ducto) {
        $this->descripcion_tipo_ducto = $descripcion_tipo_ducto;
    }

    function setClave_instalacion($clave_instalacion) {
        $this->clave_instalacion = $clave_instalacion;
    }

    function setCve_producto_sat_ducto($cve_producto_sat_ducto) {
        $this->cve_producto_sat_ducto = $cve_producto_sat_ducto;
    }

    function setAlmacenamiento_ducto($almacenamiento_ducto) {
        $this->almacenamiento_ducto = $almacenamiento_ducto;
    }

    function setVigencia_calibracion_ducto($vigencia_calibracion_ducto) {
        $this->vigencia_calibracion_ducto = $vigencia_calibracion_ducto;
    }

}
