<?php

/*
 * FcdVO
 * common
 * 2017, Detisa 
 * http://www.detisa.com.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since jul 2017
 */

class FcdVO {

    private $id;
    private $idnvo;
    private $producto;
    private $cantidad;
    private $precio;
    private $iva;
    private $iva_retenido;
    private $ieps;
    private $importe;
    private $ticket = 0;
    private $tipoc = "C";
    private $preciob;
    private $descuento = 0;
    private $tipodepago;
    private $clavei;
    private $isr_retenido = 0;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getIdnvo() {
        return $this->idnvo;
    }

    function getProducto() {
        return $this->producto;
    }

    function getCantidad() {
        return $this->cantidad;
    }

    function getPrecio() {
        return $this->precio;
    }

    function getIva() {
        return $this->iva;
    }

    function getIva_retenido() {
        return $this->iva_retenido;
    }

    function getIsr_retenido() {
        return $this->isr_retenido;
    }

    function getIeps() {
        return $this->ieps;
    }

    function getImporte() {
        return $this->importe;
    }

    function getTicket() {
        return $this->ticket;
    }

    function getTipoc() {
        return $this->tipoc;
    }

    function getPreciob() {
        return $this->preciob;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setIdnvo($idnvo) {
        $this->idnvo = $idnvo;
    }

    function setProducto($producto) {
        $this->producto = $producto;
    }

    function setCantidad($cantidad) {
        $this->cantidad = $cantidad;
    }

    function setPrecio($precio) {
        $this->precio = $precio;
    }

    function setIva($iva) {
        $this->iva = $iva;
    }

    function setIva_retenido($iva_retenido) {
        $this->iva_retenido = $iva_retenido;
    }
    
    function setIsr_retenido($isr_retenido){
        $this->isr_retenido = $isr_retenido;
    }

    function setIeps($ieps) {
        $this->ieps = $ieps;
    }

    function setImporte($importe) {
        $this->importe = $importe;
    }

    function setTicket($ticket) {
        $this->ticket = $ticket;
    }

    function setTipoc($tipoc) {
        $this->tipoc = $tipoc;
    }

    function setPreciob($preciob) {
        $this->preciob = $preciob;
    }

    function getDescuento() {
        return $this->descuento;
    }

    function setDescuento($descuento) {
        $this->descuento = $descuento;
    }

    function getTipodepago() {
        return $this->tipodepago;
    }

    function setTipodepago($tipodepago) {
        $this->tipodepago = $tipodepago;
    }

    function getClavei() {
        return $this->clavei;
    }

    function setClavei($clavei) {
        $this->clavei = $clavei;
    }

    /**
     * Overrides toString method
     * @return String
     */
    public function __toString() {
        return "FcdVO={"
                . "id=" . $this->id
                . ", idnvo=" . $this->idnvo
                . ", producto=" . $this->producto
                . ", cantidad=" . $this->cantidad
                . ", precio=" . $this->precio
                . ", iva=" . $this->iva
                . ", ieps=" . $this->ieps
                . ", importe=" . $this->importe
                . ", ticket=" . $this->ticket
                . ", tipoc=" . $this->tipoc
                . ", preciob=" . $this->preciob
                . "}";
    }

//__toString
}
