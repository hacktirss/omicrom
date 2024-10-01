<?php

/**
 * Description of ProveedorVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class ProveedorVO {

    private $id;
    private $nombre;
    private $direccion;
    private $colonia;
    private $municipio;
    private $alias;
    private $telefono;
    private $activo = "Si";
    private $contacto;
    private $observaciones;
    private $tipodepago = "Contado";
    private $limite = 0;
    private $codigo;
    private $rfc;
    private $correo;
    private $numeroint;
    private $numeroext;
    private $enviarcorreo;
    private $cuentaban;
    private $ncc;
    private $dias_credito = 0;
    private $proveedorde = "Combustibles";
    private $dias_cre = 0;
    private $clabe;
    private $cuenta = "";
    private $banco = "";
    private $tipoProveedor = "Nacional";
    private $permisoCRE;
    private $tipo;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getNombre() {
        return $this->nombre;
    }

    function getDireccion() {
        return $this->direccion;
    }

    function getColonia() {
        return $this->colonia;
    }

    function getMunicipio() {
        return $this->municipio;
    }

    function getAlias() {
        return $this->alias;
    }

    function getTelefono() {
        return $this->telefono;
    }

    function getActivo() {
        return $this->activo;
    }

    function getContacto() {
        return $this->contacto;
    }

    function getObservaciones() {
        return $this->observaciones;
    }

    function getTipodepago() {
        return $this->tipodepago;
    }

    function getLimite() {
        return $this->limite;
    }

    function getCodigo() {
        return $this->codigo;
    }

    function getRfc() {
        return $this->rfc;
    }

    function getCorreo() {
        return $this->correo;
    }

    function getNumeroint() {
        return $this->numeroint;
    }

    function getNumeroext() {
        return $this->numeroext;
    }

    function getEnviarcorreo() {
        return $this->enviarcorreo;
    }

    function getCuentaban() {
        return $this->cuentaban;
    }

    function getNcc() {
        return $this->ncc;
    }

    function getDias_credito() {
        return $this->dias_credito;
    }

    function getProveedorde() {
        return $this->proveedorde;
    }

    function getDias_cre() {
        return $this->dias_cre;
    }

    function getClabe() {
        return $this->clabe;
    }

    function getCuenta() {
        return $this->cuenta;
    }

    function getBanco() {
        return $this->banco;
    }

    function getTipoProveedor() {
        return $this->tipoProveedor;
    }

    function getPermisoCRE() {
        return $this->permisoCRE;
    }

    function getTipo() {
        return $this->tipo;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    function setDireccion($direccion) {
        $this->direccion = $direccion;
    }

    function setColonia($colonia) {
        $this->colonia = $colonia;
    }

    function setMunicipio($municipio) {
        $this->municipio = $municipio;
    }

    function setAlias($alias) {
        $this->alias = $alias;
    }

    function setTelefono($telefono) {
        $this->telefono = $telefono;
    }

    function setActivo($activo) {
        $this->activo = $activo;
    }

    function setContacto($contacto) {
        $this->contacto = $contacto;
    }

    function setObservaciones($observaciones) {
        $this->observaciones = $observaciones;
    }

    function setTipodepago($tipodepago) {
        $this->tipodepago = $tipodepago;
    }

    function setLimite($limite) {
        $this->limite = $limite;
    }

    function setCodigo($codigo) {
        $this->codigo = $codigo;
    }

    function setRfc($rfc) {
        $this->rfc = $rfc;
    }

    function setCorreo($correo) {
        $this->correo = $correo;
    }

    function setNumeroint($numeroint) {
        $this->numeroint = $numeroint;
    }

    function setNumeroext($numeroext) {
        $this->numeroext = $numeroext;
    }

    function setEnviarcorreo($enviarcorreo) {
        $this->enviarcorreo = $enviarcorreo;
    }

    function setCuentaban($cuentaban) {
        $this->cuentaban = $cuentaban;
    }

    function setNcc($ncc) {
        $this->ncc = $ncc;
    }

    function setDias_credito($dias_credito) {
        $this->dias_credito = $dias_credito;
    }

    function setProveedorde($proveedorde) {
        $this->proveedorde = $proveedorde;
    }

    function setDias_cre($dias_cre) {
        $this->dias_cre = $dias_cre;
    }

    function setClabe($clabe) {
        $this->clabe = $clabe;
    }

    function setCuenta($cuenta) {
        $this->cuenta = $cuenta;
    }

    function setBanco($banco) {
        $this->banco = $banco;
    }

    function setTipoProveedor($tipoProveedor) {
        $this->tipoProveedor = $tipoProveedor;
    }

    function setPermisoCRE($permisoCRE) {
        $this->permisoCRE = $permisoCRE;
    }

    function setTipo($tipo) {
        $this->tipo = $tipo;
    }

}
