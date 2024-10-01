<?php

/**
 * Description of RmVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class RmVO {

    private $id;
    private $dispensario;
    private $posicion;
    private $manguera;
    private $dis_mang;
    private $producto;
    private $precio;
    private $inicio_venta;
    private $fin_venta;
    private $pesos;
    private $volumen;
    private $pesosp;
    private $volumenp;
    private $importe;
    private $comprobante;
    private $factor;
    private $completo;
    private $vendedor;
    private $turno;
    private $corte;
    private $iva;
    private $ieps;
    private $tipo_venta;
    private $procesado;
    private $enviado;
    private $cliente;
    private $placas;
    private $codigo;
    private $kilometraje;
    private $uuid;
    private $depto;
    private $vdm;
    private $pagado;
    private $puntos;
    private $informacorporativo;
    private $inventario;
    private $pagoreal;
    private $idcxc;
    private $tipodepago;
    private $totalizadorVI;
    private $totalizadorVF;
    private $descuento = 0;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getDispensario() {
        return $this->dispensario;
    }

    function getPosicion() {
        return $this->posicion;
    }

    function getManguera() {
        return $this->manguera;
    }

    function getDis_mang() {
        return $this->dis_mang;
    }

    function getProducto() {
        return $this->producto;
    }

    function getPrecio() {
        return $this->precio;
    }

    function getInicio_venta() {
        return $this->inicio_venta;
    }

    function getFin_venta() {
        return $this->fin_venta;
    }

    function getPesos() {
        return $this->pesos;
    }

    function getVolumen() {
        return $this->volumen;
    }

    function getPesosp() {
        return $this->pesosp;
    }

    function getVolumenp() {
        return $this->volumenp;
    }

    function getImporte() {
        return $this->importe;
    }

    function getComprobante() {
        return $this->comprobante;
    }

    function getFactor() {
        return $this->factor;
    }

    function getCompleto() {
        return $this->completo;
    }

    function getVendedor() {
        return $this->vendedor;
    }

    function getTurno() {
        return $this->turno;
    }

    function getCorte() {
        return $this->corte;
    }

    function getIva() {
        return $this->iva;
    }

    function getIeps() {
        return $this->ieps;
    }

    function getTipo_venta() {
        return $this->tipo_venta;
    }

    function getProcesado() {
        return $this->procesado;
    }

    function getEnviado() {
        return $this->enviado;
    }

    function getCliente() {
        return $this->cliente;
    }

    function getPlacas() {
        return $this->placas;
    }

    function getCodigo() {
        return $this->codigo;
    }

    function getKilometraje() {
        return $this->kilometraje;
    }

    function getUuid() {
        return $this->uuid;
    }

    function getDepto() {
        return $this->depto;
    }

    function getVdm() {
        return $this->vdm;
    }

    function getPagado() {
        return $this->pagado;
    }

    function getPuntos() {
        return $this->puntos;
    }

    function getInformacorporativo() {
        return $this->informacorporativo;
    }

    function getInventario() {
        return $this->inventario;
    }

    function getPagoreal() {
        return $this->pagoreal;
    }

    function getIdcxc() {
        return $this->idcxc;
    }

    function getTipodepago() {
        return $this->tipodepago;
    }

    function getTotalizadorVI() {
        return $this->totalizadorVI;
    }

    function getTotalizadorVF() {
        return $this->totalizadorVF;
    }

    function getDescuento() {
        return $this->descuento;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setDispensario($dispensario) {
        $this->dispensario = $dispensario;
    }

    function setPosicion($posicion) {
        $this->posicion = $posicion;
    }

    function setManguera($manguera) {
        $this->manguera = $manguera;
    }

    function setDis_mang($dis_mang) {
        $this->dis_mang = $dis_mang;
    }

    function setProducto($producto) {
        $this->producto = $producto;
    }

    function setPrecio($precio) {
        $this->precio = $precio;
    }

    function setInicio_venta($inicio_venta) {
        $this->inicio_venta = $inicio_venta;
    }

    function setFin_venta($fin_venta) {
        $this->fin_venta = $fin_venta;
    }

    function setPesos($pesos) {
        $this->pesos = $pesos;
    }

    function setVolumen($volumen) {
        $this->volumen = $volumen;
    }

    function setPesosp($pesosp) {
        $this->pesosp = $pesosp;
    }

    function setVolumenp($volumenp) {
        $this->volumenp = $volumenp;
    }

    function setImporte($importe) {
        $this->importe = $importe;
    }

    function setComprobante($comprobante) {
        $this->comprobante = $comprobante;
    }

    function setFactor($factor) {
        $this->factor = $factor;
    }

    function setCompleto($completo) {
        $this->completo = $completo;
    }

    function setVendedor($vendedor) {
        $this->vendedor = $vendedor;
    }

    function setTurno($turno) {
        $this->turno = $turno;
    }

    function setCorte($corte) {
        $this->corte = $corte;
    }

    function setIva($iva) {
        $this->iva = $iva;
    }

    function setIeps($ieps) {
        $this->ieps = $ieps;
    }

    function setTipo_venta($tipo_venta) {
        $this->tipo_venta = $tipo_venta;
    }

    function setProcesado($procesado) {
        $this->procesado = $procesado;
    }

    function setEnviado($enviado) {
        $this->enviado = $enviado;
    }

    function setCliente($cliente) {
        $this->cliente = $cliente;
    }

    function setPlacas($placas) {
        $this->placas = $placas;
    }

    function setCodigo($codigo) {
        $this->codigo = $codigo;
    }

    function setKilometraje($kilometraje) {
        $this->kilometraje = $kilometraje;
    }

    function setUuid($uuid) {
        $this->uuid = $uuid;
    }

    function setDepto($depto) {
        $this->depto = $depto;
    }

    function setVdm($vdm) {
        $this->vdm = $vdm;
    }

    function setPagado($pagado) {
        $this->pagado = $pagado;
    }

    function setPuntos($puntos) {
        $this->puntos = $puntos;
    }

    function setInformacorporativo($informacorporativo) {
        $this->informacorporativo = $informacorporativo;
    }

    function setInventario($inventario) {
        $this->inventario = $inventario;
    }

    function setPagoreal($pagoreal) {
        $this->pagoreal = $pagoreal;
    }

    function setIdcxc($idcxc) {
        $this->idcxc = $idcxc;
    }

    function setTipodepago($tipodepago) {
        $this->tipodepago = $tipodepago;
    }

    function setTotalizadorVI($totalizadorVI) {
        $this->totalizadorVI = $totalizadorVI;
    }

    function setTotalizadorVF($totalizadorVF) {
        $this->totalizadorVF = $totalizadorVF;
    }

    function setDescuento($descuento) {
        $this->descuento = $descuento;
    }

}
