<?php

/**
 * Description of MeVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class MeVO {

    private $id;
    private $usuario = 0;
    private $tanque;
    private $fecha;
    private $fechae;
    private $proveedor = "";
    private $producto;
    private $vol_inicial;
    private $vol_final;
    private $fechafac;
    private $foliofac;
    private $uuid = "-----";
    private $volumenfac;
    private $terminal = "";
    private $clavevehiculo;
    private $documento = "RP";
    private $status;
    private $entcombustible;
    private $facturas;
    private $preciou;
    private $importefac;
    private $carga = 0;
    private $cuadrada = 0;
    private $tipo = "Normal";
    private $t_final;
    private $incremento;
    private $horaincremento;
    private $enviado = 0;
    private $folioenvios = 0;
    private $proveedorTransporte = "";
    private $punto_exportacion;
    private $punto_internacion;
    private $pais_destino;
    private $pais_origen;
    private $medio_transporte_entrada;
    private $medio_transporte_salida;
    private $incoterms;
    private $volumen_devolucion;
    private $tipocomprobante = "I";

    function __construct() {
        
    }

    public function getTipocomprobante() {
        return $this->tipocomprobante;
    }

    public function setTipocomprobante($tipocomprobante): void {
        $this->tipocomprobante = $tipocomprobante;
    }

    function getId() {
        return $this->id;
    }

    function getTanque() {
        return $this->tanque;
    }

    function getFecha() {
        return $this->fecha;
    }

    function getFechae() {
        return $this->fechae;
    }

    function getProveedor() {
        return $this->proveedor;
    }

    function getProducto() {
        return $this->producto;
    }

    function getVol_inicial() {
        return $this->vol_inicial;
    }

    function getVol_final() {
        return $this->vol_final;
    }

    function getFechafac() {
        return empty($this->fechafac) ? date("Y-m-d") : $this->fechafac;
    }

    function getFoliofac() {
        return $this->foliofac == null ? "" : $this->foliofac;
    }

    function getVolumenfac() {
        return $this->volumenfac == null ? 0 : $this->volumenfac;
    }

    function getTerminal() {
        return $this->terminal;
    }

    function getClavevehiculo() {
        return $this->clavevehiculo;
    }

    function getDocumento() {
        return $this->documento;
    }

    function getStatus() {
        return $this->status;
    }

    function getEntcombustible() {
        return $this->entcombustible == null ? 0 : $this->entcombustible;
    }

    function getFacturas() {
        return $this->facturas == null ? 1 : $this->facturas;
    }

    function getPreciou() {
        return $this->preciou == null ? 0 : $this->preciou;
    }

    function getImportefac() {
        return $this->importefac == null ? 0 : $this->importefac;
    }

    function getCarga() {
        return $this->carga;
    }

    function getCuadrada() {
        return $this->cuadrada == null ? 0 : $this->cuadrada;
    }

    function getTipo() {
        return $this->tipo;
    }

    function getT_final() {
        return $this->t_final;
    }

    function getIncremento() {
        return $this->incremento;
    }

    function getHoraincremento() {
        return $this->horaincremento;
    }

    function getEnviado() {
        return $this->enviado;
    }

    function getFolioenvios() {
        return $this->folioenvios;
    }

    function getProveedorTransporte() {
        return $this->proveedorTransporte;
    }

    function getPunto_exportacion() {
        return $this->punto_exportacion;
    }

    function getPunto_internacion() {
        return $this->punto_internacion;
    }

    function getPais_destino() {
        return $this->pais_destino;
    }

    function getPais_origen() {
        return $this->pais_origen;
    }

    function getMedio_transporte_entrada() {
        return $this->medio_transporte_entrada;
    }

    function getMedio_transporte_salida() {
        return $this->medio_transporte_salida;
    }

    function getIncoterms() {
        return $this->incoterms;
    }

    function getVolumen_devolucion() {
        return $this->volumen_devolucion;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setTanque($tanque) {
        $this->tanque = $tanque;
    }

    function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    function setFechae($fechae) {
        $this->fechae = $fechae;
    }

    function setProveedor($proveedor) {
        $this->proveedor = $proveedor;
    }

    function setProducto($producto) {
        $this->producto = $producto;
    }

    function setVol_inicial($vol_inicial) {
        $this->vol_inicial = $vol_inicial;
    }

    function setVol_final($vol_final) {
        $this->vol_final = $vol_final;
    }

    function setFechafac($fechafac) {
        $this->fechafac = $fechafac;
    }

    function setFoliofac($foliofac) {
        $this->foliofac = $foliofac;
    }

    function setVolumenfac($volumenfac) {
        $this->volumenfac = $volumenfac;
    }

    function setTerminal($terminal) {
        $this->terminal = $terminal;
    }

    function setClavevehiculo($clavevehiculo) {
        $this->clavevehiculo = $clavevehiculo;
    }

    function setDocumento($documento) {
        $this->documento = $documento;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function setEntcombustible($entcombustible) {
        $this->entcombustible = $entcombustible;
    }

    function setFacturas($facturas) {
        $this->facturas = $facturas;
    }

    function setPreciou($preciou) {
        $this->preciou = $preciou;
    }

    function setImportefac($importefac) {
        $this->importefac = $importefac;
    }

    function setCarga($carga) {
        $this->carga = $carga;
    }

    function setCuadrada($cuadrada) {
        $this->cuadrada = $cuadrada;
    }

    function setTipo($tipo) {
        $this->tipo = $tipo;
    }

    function setT_final($t_final) {
        $this->t_final = $t_final;
    }

    function setIncremento($incremento) {
        $this->incremento = $incremento;
    }

    function setHoraincremento($horaincremento) {
        $this->horaincremento = $horaincremento;
    }

    function setEnviado($enviado) {
        $this->enviado = $enviado;
    }

    function setFolioenvios($folioenvios) {
        $this->folioenvios = $folioenvios;
    }

    function setProveedorTransporte($proveedorTransporte) {
        $this->proveedorTransporte = $proveedorTransporte;
    }

    function setTipoConversion($TipoConversion) {
        $this->folioenvios = $TipoConversion;
    }

    function getUuid() {
        return $this->uuid;
    }

    function setUuid($uuid) {
        $this->uuid = $uuid;
    }

    function setPunto_exportacion($punto_exportacion) {
        $this->punto_exportacion = $punto_exportacion;
    }

    function setPunto_internacion($punto_internacion) {
        $this->punto_internacion = $punto_internacion;
    }

    function setPais_destino($pais_destino) {
        $this->pais_destino = $pais_destino;
    }

    function setPais_origen($pais_origen) {
        $this->pais_origen = $pais_origen;
    }

    function setMedio_transporte_entrada($medio_transporte_entrada) {
        $this->medio_transporte_entrada = $medio_transporte_entrada;
    }

    function setMedio_transporte_salida($medio_transporte_salida) {
        $this->medio_transporte_salida = $medio_transporte_salida;
    }

    function setIncoterms($incoterms) {
        $this->incoterms = $incoterms;
    }

    function setVolumen_devolucion($volumen_devolucion) {
        $this->volumen_devolucion = $volumen_devolucion;
    }

    public function getUsuario() {
        return $this->usuario;
    }

    public function setUsuario($usuario) {
        $this->usuario = $usuario;
    }

}
