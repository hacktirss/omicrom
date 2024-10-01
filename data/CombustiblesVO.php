<?php

/**
 * Description of CombustiblesVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class CombustiblesVO {

    private $id;
    private $clave;
    private $clavei;
    private $descripcion;
    private $precio;
    private $activo;
    private $iva;
    private $ieps;
    private $medidor;
    private $ncc_vt;
    private $ncc_cv;
    private $ncc_al;
    private $ncc_mr;
    private $ncc_ieps;
    private $color;
    private $claveProducto;
    private $claveSubProducto;
    private $ComOctanajeGas;
    private $GasConEtanol;
    private $ComDeEtanolEnGasolina;
    private $otros;
    private $marca;
    private $tipo_producto;
    private $marcaje;
    private $conc_sustancia_marcaje;
    private $marca_comercial;
    private $cve_producto_sat;
    private $cve_sub_producto_sat;
    private $poder_calorifico;
    private $densidad;
    private $comp_azufre;
    private $fraccion_molar;
    private $gravedad_especifica;
    private $comp_fosil;
    private $comp_propano;
    private $comp_butano;
    private $clave_instalacion;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getClave() {
        return $this->clave;
    }

    function getClavei() {
        return $this->clavei;
    }

    function getDescripcion() {
        return $this->descripcion;
    }

    function getPrecio() {
        return $this->precio;
    }

    function getActivo() {
        return $this->activo;
    }

    function getIva() {
        return $this->iva;
    }

    function getIeps() {
        return $this->ieps;
    }

    function getMedidor() {
        return $this->medidor;
    }

    function getNcc_vt() {
        return $this->ncc_vt;
    }

    function getNcc_cv() {
        return $this->ncc_cv;
    }

    function getNcc_al() {
        return $this->ncc_al;
    }

    function getNcc_mr() {
        return $this->ncc_mr;
    }

    function getNcc_ieps() {
        return $this->ncc_ieps;
    }

    function getColor() {
        return $this->color;
    }

    function getClaveProducto() {
        return $this->claveProducto;
    }

    function getClaveSubProducto() {
        return $this->claveSubProducto;
    }

    function getComOctanajeGas() {
        return $this->ComOctanajeGas;
    }

    function getGasConEtanol() {
        return $this->GasConEtanol;
    }

    function getComDeEtanolEnGasolina() {
        return $this->ComDeEtanolEnGasolina;
    }

    function getOtros() {
        return $this->otros;
    }

    function getMarca() {
        return $this->marca;
    }

    function getTipo_producto() {
        return $this->tipo_producto;
    }

    function getMarcaje() {
        return $this->marcaje;
    }

    function getConc_sustancia_marcaje() {
        return $this->conc_sustancia_marcaje;
    }

    function getMarca_comercial() {
        return $this->marca_comercial;
    }

    function getCve_producto_sat() {
        return $this->cve_producto_sat;
    }

    function getCve_sub_producto_sat() {
        return $this->cve_sub_producto_sat;
    }

    function getPoder_calorifico() {
        return $this->poder_calorifico;
    }
    
    function getGravedad_especifica(){
        return $this->gravedad_especifica;
    }
    
    function getComp_fosil(){
        return $this->comp_fosil;
    }
    
    function getComp_propano(){
        return $this->comp_propano;
    }
    
    function getComp_butano(){
        return $this->comp_butano;
    }
    
    function getClave_instalacion(){
        return $this->clave_instalacion;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setClave($clave) {
        $this->clave = $clave;
    }

    function setClavei($clavei) {
        $this->clavei = $clavei;
    }

    function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    function setPrecio($precio) {
        $this->precio = $precio;
    }

    function setActivo($activo) {
        $this->activo = $activo;
    }

    function setIva($iva) {
        $this->iva = $iva;
    }

    function setIeps($ieps) {
        $this->ieps = $ieps;
    }

    function setMedidor($medidor) {
        $this->medidor = $medidor;
    }

    function setNcc_vt($ncc_vt) {
        $this->ncc_vt = $ncc_vt;
    }

    function setNcc_cv($ncc_cv) {
        $this->ncc_cv = $ncc_cv;
    }

    function setNcc_al($ncc_al) {
        $this->ncc_al = $ncc_al;
    }

    function setNcc_mr($ncc_mr) {
        $this->ncc_mr = $ncc_mr;
    }

    function setNcc_ieps($ncc_ieps) {
        $this->ncc_ieps = $ncc_ieps;
    }

    function setColor($color) {
        $this->color = $color;
    }

    function setClaveProducto($claveProducto) {
        $this->claveProducto = $claveProducto;
    }

    function setClaveSubProducto($claveSubProducto) {
        $this->claveSubProducto = $claveSubProducto;
    }

    function setComOctanajeGas($ComOctanajeGas) {
        $this->ComOctanajeGas = $ComOctanajeGas;
    }

    function setGasConEtanol($GasConEtanol) {
        $this->GasConEtanol = $GasConEtanol;
    }

    function setComDeEtanolEnGasolina($ComDeEtanolEnGasolina) {
        $this->ComDeEtanolEnGasolina = $ComDeEtanolEnGasolina;
    }

    function setOtros($otros) {
        $this->otros = $otros;
    }

    function setMarca($marca) {
        $this->marca = $marca;
    }

    function setTipo_producto($tipo_producto) {
        $this->tipo_producto = $tipo_producto;
    }

    function setMarcaje($marcaje) {
        $this->marcaje = $marcaje;
    }

    function setConc_sustancia_marcaje($conc_sustancia_marcaje) {
        $this->conc_sustancia_marcaje = $conc_sustancia_marcaje;
    }

    function setMarca_comercial($marca_comercial) {
        $this->marca_comercial = $marca_comercial;
    }

    function setCve_producto_sat($cve_producto_sat) {
        $this->cve_producto_sat = $cve_producto_sat;
    }

    function setCve_sub_producto_sat($cve_sub_producto_sat) {
        $this->cve_sub_producto_sat = $cve_sub_producto_sat;
    }

    function setPoder_calorifico($poder_calorifico) {
        $this->poder_calorifico = $poder_calorifico;
    }
    
    function getDensidad() {
        return $this->densidad;
    }

    function getComp_azufre() {
        return $this->comp_azufre;
    }

    function getFraccion_molar() {
        return $this->fraccion_molar;
    }

    function setDensidad($densidad) {
        $this->densidad = $densidad;
    }

    function setComp_azufre($comp_azufre) {
        $this->comp_azufre = $comp_azufre;
    }

    function setFraccion_molar($fraccion_molar) {
        $this->fraccion_molar = $fraccion_molar;
    }
    
    function setGravedad_especifica($gravedad_especifica){
        $this->gravedad_especifica = $gravedad_especifica;
    }

    function setComp_fosil($comp_fosil){
        $this->comp_fosil = $comp_fosil;
    }
    
    function setComp_propano($comp_propano){
        $this->comp_propano = $comp_propano;
    }
    
    function setComp_butano($comp_butano){
        $this->comp_butano = $comp_butano;
    }
    
    function setClave_instalacion($clave_instalacion){
        $this->clave_instalacion = $clave_instalacion;
    }
}
