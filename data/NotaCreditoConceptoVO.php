<?php

/*
 * NotaCreditoConceptoVO
 * omicrom®
 * © 2017; Detisa 
 * http://www.detisa.com.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since nov 2017
 */

class NotaCreditoConceptoVO {

    private $id;                    // FCD ID
    private $clave;                 // Clave producto
    private $producto;              // ID Producto
    private $descripcion;           // Descripción de producto

    private $cantidad;              // Cantidad
    private $precio;                // Precio Unitario (Antes de impuestos y descuentos)

    private $iva;                   // IVA CFDI 3.2
    private $ieps;                  // IEPS CFDI 3.2
    private $umedida;               // Unídad de medida CFDI 3.2

    private $factoriva;             // Tasa IVA CFDI v3.3
    private $factorieps;            // Tasa IEPS CFDI 3.3
    private $inv_cproducto = '';    // Clave de Producto/Servicio CFDI3.3
    private $inv_cunidad = '';      // Clave de Unidad CFDI 3.3

    private $subtotal;              // Importe del concepto (Antes de impuestos y descuentos)
    private $baseIva;               // Base gravable para IVA
    private $baseIeps;              // Base gravable para IEPS
    private $descuento;             // Importe del descuento
    private $impiva;                // Importe del IVA
    private $impieps;               // Importe del IEPS
    private $total;                 // Total (Subtotal + traslados - retenciones - descuentos)
    
    function getId() {
        return $this->id;
    }

    function getClave() {
        return $this->clave;
    }

    function getProducto() {
        return $this->producto;
    }

    function getDescripcion() {
        return $this->descripcion;
    }

    function getCantidad() {
        return $this->cantidad;
    }

    function getPrecio() {
        return $this->precio;
    }

    /**************************** CFDI 3.2 ************************************/
    function getIva() {
        return $this->iva;
    }

    function getIeps() {
        return $this->ieps;
    }

    function getUmedida() {
        return $this->umedida;
    }

    /**************************** CFDI 3.3 ************************************/
    function getFactoriva() {
        return $this->factoriva;
    }

    function getFactorieps() {
        return $this->factorieps;
    }

    function getInv_cproducto() {
        return $this->inv_cproducto;
    }

    function getInv_cunidad() {
        return $this->inv_cunidad;
    }
    /**************************************************************************/
    function getSubtotal() {
        return $this->subtotal;
    }

    function getBaseIva() {
        return $this->baseIva;
    }

    function getBaseIeps() {
        return $this->baseIeps;
    }

    function getDescuento() {
        return $this->descuento;
    }

    function getImpiva() {
        return $this->impiva;
    }

    function getImpieps() {
        return $this->impieps;
    }

    function getTotal() {
        return $this->total;
    }

    function setId($id) {
        $this->id = $id;
    }
    
    function setClave($clave) {
        $this->clave = $clave;
    }

    function setProducto($producto) {
        $this->producto = $producto;
    }

    function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    function setCantidad($cantidad) {
        $this->cantidad = $cantidad;
    }

    function setPrecio($precio) {
        $this->precio = $precio;
    }
    
    /**************************** CFDI 3.2 ************************************/
    function setIva($iva) {
        $this->iva = $iva;
    }

    function setIeps($ieps) {
        $this->ieps = $ieps;
    }

    function setUmedida($umedida) {
        $this->umedida = $umedida;
    }

    /**************************** CFDI 3.3 ************************************/
    function setFactoriva($factoriva) {
        $this->factoriva = $factoriva;
    }

    function setFactorieps($factorieps) {
        $this->factorieps = $factorieps;
    }

    function setInv_cproducto($inv_cproducto) {
        $this->inv_cproducto = $inv_cproducto;
    }

    function setInv_cunidad($inv_cunidad) {
        $this->inv_cunidad = $inv_cunidad;
    }

    /**************************************************************************/
    function setSubtotal($subtotal) {
        $this->subtotal = $subtotal;
    }

    function setDescuento($descuento) {
        $this->descuento = $descuento;
    }

    function setBaseIva($base) {
        $this->baseIva = $base;
    }

    function setBaseIeps($base) {
        $this->baseIeps = $base;
    }

    function setImpiva($impiva) {
        $this->impiva = $impiva;
    }

    function setImpieps($impieps) {
        $this->impieps = $impieps;
    }

    function setTotal($total) {
        $this->total = $total;
    }

    public function __toString() {
        return "FacturaConceptoVO={"
                .   "id=".$this->id
                . ", clave=".$this->clave
                . ", producto=".$this->producto
                . ", descripcion=".$this->descripcion
                . ", cantidad=".$this->cantidad
                . ", precio=".$this->precio 
                . ", 3.2={"
                .           "iva=".$this->iva
                .         ", ieps=".$this->ieps
                .         ", umedida=".$this->umedida . "}"
                . ", 3.3={"
                .           "factoriva=".$this->factoriva
                .         ", factorieps=".$this->factorieps
                .         ", inv_cproducto=".$this->inv_cproducto
                .         ", inv_cunidad=".$this->inv_cunidad . "}"
                . ", subtotal=".$this->subtotal
                . ", descuento=".$this->descuento
                . ", baseiva=".$this->baseIva
                . ", baseieps=".$this->baseIeps
                . ", impiva=".$this->impiva
                . ", impieps=".$this->impieps
                . ", total=".$this->total."}";
    }//toString
}//FacturaConceptoVO
