<?php

/**
 * Description of PagoVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class PagoVO {

    private $id;
    private $cliente;
    private $fecha;
    private $fecha_deposito;
    private $concepto;
    private $importe = 0;
    private $aplicado = 0;
    private $referencia;
    private $status;
    private $banco;
    private $formapago;
    private $numoperacion;
    private $uuid = "-----";
    private $statusCFDI;
    private $stCancelacion = 0;
    private $fechar;
    private $tiporelacion;
    private $relacioncfdi;
    private $usr;
    private $status_pago = 1;
    private $fechaD;
    private $horaD;
    private $detalle;
    private $relacion = 0;
    private $usocfdi;
    private $saldoFavor = 0;
    private $fecha_ini;
    private $fecha_fin;
    private $montonoreconocido = 0;

    function __construct() {
        $this->fecha_ini = date("Y-m-d");
        $this->fecha_fin = date("Y-m-d");
    }

    public function getMontonoreconocido() {
        return $this->montonoreconocido;
    }

    public function setMontonoreconocido($montonoreconocido): void {
        $this->montonoreconocido = $montonoreconocido;
    }

    public function getFecha_ini() {
        return $this->fecha_ini;
    }

    public function getFecha_fin() {
        return $this->fecha_fin;
    }

    public function setFecha_ini($fecha_ini): void {
        $this->fecha_ini = $fecha_ini;
    }

    public function setFecha_fin($fecha_fin): void {
        $this->fecha_fin = $fecha_fin;
    }

    function getId() {
        return $this->id;
    }

    function getCliente() {
        return $this->cliente;
    }

    function getFecha() {
        return $this->fecha;
    }

    function getFecha_deposito() {
        return $this->fecha_deposito;
    }

    function getConcepto() {
        return $this->concepto;
    }

    function getImporte() {
        return $this->importe;
    }

    function getAplicado() {
        return $this->aplicado;
    }

    function getReferencia() {
        return $this->referencia;
    }

    function getStatus() {
        return $this->status;
    }

    function getBanco() {
        return $this->banco;
    }

    function getFormapago() {
        return $this->formapago;
    }

    function getNumoperacion() {
        return $this->numoperacion;
    }

    function getUuid() {
        return $this->uuid;
    }

    function getStatusCFDI() {
        return $this->statusCFDI;
    }

    function getStCancelacion() {
        return $this->stCancelacion;
    }

    function getFechar() {
        return $this->fechar;
    }

    function getTiporelacion() {
        return $this->tiporelacion;
    }

    function getRelacioncfdi() {
        return $this->relacioncfdi;
    }

    function getUsr() {
        return $this->usr;
    }

    function getStatus_pago() {
        return $this->status_pago;
    }

    function getRelacion() {
        return $this->relacion;
    }

    function getUsocfdi() {
        return $this->usocfdi;
    }

    function getSaldoFavor() {
        return $this->saldoFavor;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setCliente($cliente) {
        $this->cliente = $cliente;
    }

    function setFecha($fecha) {
        $this->fecha = $fecha;
    }

    function setFecha_deposito($fecha_deposito) {
        $this->fecha_deposito = $fecha_deposito;
    }

    function setConcepto($concepto) {
        $this->concepto = $concepto;
    }

    function setImporte($importe) {
        $this->importe = $importe;
    }

    function setAplicado($aplicado) {
        $this->aplicado = $aplicado;
    }

    function setReferencia($referencia) {
        $this->referencia = $referencia;
    }

    function setStatus($status) {
        $this->status = $status;
    }

    function setBanco($banco) {
        $this->banco = $banco;
    }

    function setFormapago($formapago) {
        $this->formapago = $formapago;
    }

    function setNumoperacion($numoperacion) {
        $this->numoperacion = $numoperacion;
    }

    function setUuid($uuid) {
        $this->uuid = $uuid;
    }

    function setStatusCFDI($statusCFDI) {
        $this->statusCFDI = $statusCFDI;
    }

    function setStCancelacion($stCancelacion) {
        $this->stCancelacion = $stCancelacion;
    }

    function setFechar($fechar) {
        $this->fechar = $fechar;
    }

    function setTiporelacion($tiporelacion) {
        $this->tiporelacion = $tiporelacion;
    }

    function setRelacioncfdi($relacioncfdi) {
        $this->relacioncfdi = $relacioncfdi;
    }

    function setUsr($usr) {
        $this->usr = $usr;
    }

    function setStatus_pago($status_pago) {
        $this->status_pago = $status_pago;
    }

    function getFechaD() {
        return $this->fechaD;
    }

    function getHoraD() {
        return $this->horaD;
    }

    function setFechaD($fechaD) {
        $this->fechaD = $fechaD;
    }

    function setHoraD($horaD) {
        $this->horaD = $horaD;
    }

    function getDetalle() {
        return $this->detalle;
    }

    function setDetalle($detalle) {
        $this->detalle = $detalle;
    }

    function setRelacion($relacion) {
        $this->relacion = $relacion;
    }

    function setUsocfdi($usocfdi) {
        $this->usocfdi = $usocfdi;
    }

    function setSaldoFavor($saldoFavor) {
        $this->saldoFavor = $saldoFavor;
    }

}
