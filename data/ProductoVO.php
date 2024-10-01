<?php

/**
 * Description of ProductoVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class ProductoVO {

    private $id;
    private $descripcion;
    private $umedida;
    private $rubro;
    private $categoria;
    private $activo;
    private $existencia;
    private $minimo;
    private $maximo;
    private $precio;
    private $costo;
    private $costo_prom;
    private $dlls;
    private $codigo;
    private $ncc_vt;
    private $ncc_cv;
    private $ncc_al;
    private $inv_cunidad;
    private $inv_cproducto;
    private $clave_producto;
    private $retiene_iva;
    private $porcentaje;
    private $factorIva;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getDescripcion() {
        return $this->descripcion;
    }

    function getUmedida() {
        return $this->umedida;
    }

    function getRubro() {
        return $this->rubro;
    }

    function getActivo() {
        return $this->activo;
    }

    function getExistencia() {
        return $this->existencia === "" || $this->existencia === null ? 0 : $this->existencia;
    }

    function getPrecio() {
        return $this->precio;
    }

    function getCosto() {
        return $this->costo;
    }

    function getCosto_prom() {
        return $this->costo_prom;
    }

    function getDlls() {
        return $this->dlls;
    }

    function getCodigo() {
        return $this->codigo;
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

    function getInv_cunidad() {
        return $this->inv_cunidad;
    }

    function getInv_cproducto() {
        return $this->inv_cproducto;
    }

    function getClave_producto() {
        return $this->clave_producto;
    }

    function getRetiene_iva() {
        return $this->retiene_iva;
    }

    function getPorcentaje() {
        return $this->porcentaje;
    }

    function getFactorIva() {
        return $this->factorIva;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    function setUmedida($umedida) {
        $this->umedida = $umedida;
    }

    function setRubro($rubro) {
        $this->rubro = $rubro;
    }

    function setActivo($activo) {
        $this->activo = $activo;
    }

    function setExistencia($existencia) {
        $this->existencia = $existencia;
    }

    function setPrecio($precio) {
        $this->precio = $precio;
    }

    function setCosto($costo) {
        $this->costo = $costo;
    }

    function setCosto_prom($costo_prom) {
        $this->costo_prom = $costo_prom;
    }

    function setDlls($dlls) {
        $this->dlls = $dlls;
    }

    function setCodigo($codigo) {
        $this->codigo = $codigo;
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

    function setInv_cunidad($inv_cunidad) {
        $this->inv_cunidad = $inv_cunidad;
    }

    function setInv_cproducto($inv_cproducto) {
        $this->inv_cproducto = $inv_cproducto;
    }

    function getMinimo() {
        return $this->minimo == null ? 0 : $this->minimo;
    }

    function getMaximo() {
        return $this->maximo == null ? 0 : $this->maximo;
    }

    function setMinimo($minimo) {
        $this->minimo = $minimo;
    }

    function setMaximo($maximo) {
        $this->maximo = $maximo;
    }

    function getCategoria() {
        return $this->categoria;
    }

    function setCategoria($categoria) {
        $this->categoria = $categoria;
    }

    function setClave_producto($clave_producto) {
        $this->clave_producto = $clave_producto;
    }

    function setRetiene_iva($retiene_iva) {
        $this->retiene_iva = $retiene_iva;
    }

    function setPorcentaje($porcentaje) {
        $this->porcentaje = $porcentaje;
    }

    function setFactorIva($factorIva) {
        $this->factorIva = $factorIva;
    }

    /**
     * Overrides toString function
     * @return String
     */
    public function __toString() {
        return "ProductoVO={id=" . $this->id . ",descripcion=" . $this->descripcion . ",umedida=" . $this->umedida . ","
                . "rubro=" . $this->rubro . ",categoria=" . $this->categoria . ",activo=" . $this->activo . ","
                . "existencia=" . $this->existencia . ",minimo=" . $this->minimo . ",maximo=" . $this->maximo . ","
                . "precio=" . $this->precio . ",costo=" . $this->costo . ",costo_prom=" . $this->costo_prom . ","
                . "dlls=" . $this->dlls . ",codigo=" . $this->codigo . ",ncc_vt=" . $this->ncc_vt . ",nnc_cv=" . $this->ncc_cv . ","
                . "ncc_al=" . $this->ncc_al . ",inv_cunidad=" . $this->inv_cunidad . ",inv_cproducto=" . $this->inv_cproducto . ","
                . "clave_producto=" . $this->clave_producto . ",retiene_iva=" . $this->retiene_iva . ","
                . "porcentaje=" . $this->porcentaje . ",factoriva=" . $this->factorIva . "}";
    }

}
