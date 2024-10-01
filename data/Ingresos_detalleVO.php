<?php

/**
 * Description of Ingresos_detalleVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Alejandro Ayala Gonzalez
 * @version 1.0
 * @since may 2022
 */
class Ingresos_detalleVO {

    private $id;
    private $idnvo;
    private $producto;
    private $cantidad;
    private $preciob;
    private $precio;
    private $iva;
    private $ieps;
    private $importe;

    public function __construct() {
        
    }

    public function getId() {
        return $this->id;
    }

    public function getIdnvo() {
        return $this->idnvo;
    }

    public function getProducto() {
        return $this->producto;
    }

    public function getCantidad() {
        return $this->cantidad;
    }

    public function getPreciob() {
        return $this->preciob;
    }

    public function getPrecio() {
        return $this->precio;
    }

    public function getIva() {
        return $this->iva;
    }

    public function getIeps() {
        return $this->ieps;
    }

    public function getImporte() {
        return $this->importe;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function setIdnvo($idnvo): void {
        $this->idnvo = $idnvo;
    }

    public function setProducto($producto): void {
        $this->producto = $producto;
    }

    public function setCantidad($cantidad): void {
        $this->cantidad = $cantidad;
    }

    public function setPreciob($preciob): void {
        $this->preciob = $preciob;
    }

    public function setPrecio($precio): void {
        $this->precio = $precio;
    }

    public function setIva($iva): void {
        $this->iva = $iva;
    }

    public function setIeps($ieps): void {
        $this->ieps = $ieps;
    }

    public function setImporte($importe): void {
        $this->importe = $importe;
    }

}
