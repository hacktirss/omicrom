<?php

/**
 * Description of RelacionCfdiVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Ayala Gonzalez Alejandro
 * @version 1.0
 * @since feb 2023
 */
class RelacionCfdiVO {

    private $id;
    private $serie;
    private $folio_factura;
    private $origen;
    private $uuid;
    private $uuid_relacionado;
    private $tipo_relacion;
    private $importe;
    private $id_fc;

    public function __construct() {
        
    }

    public function getId() {
        return $this->id;
    }

    public function getSerie() {
        return $this->serie;
    }

    public function getFolio_factura() {
        return $this->folio_factura;
    }

    public function getOrigen() {
        return $this->origen;
    }

    public function getUuid() {
        return $this->uuid;
    }

    public function getUuid_relacionado() {
        return $this->uuid_relacionado;
    }

    public function getTipo_relacion() {
        return $this->tipo_relacion;
    }

    public function getImporte() {
        return $this->importe;
    }

    public function getId_fc() {
        return $this->id_fc;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function setSerie($serie): void {
        $this->serie = $serie;
    }

    public function setFolio_factura($folio_factura): void {
        $this->folio_factura = $folio_factura;
    }

    public function setOrigen($origen): void {
        $this->origen = $origen;
    }

    public function setUuid($uuid): void {
        $this->uuid = $uuid;
    }

    public function setUuid_relacionado($uuid_relacionado): void {
        $this->uuid_relacionado = $uuid_relacionado;
    }

    public function setTipo_relacion($tipo_relacion): void {
        $this->tipo_relacion = $tipo_relacion;
    }

    public function setImporte($importe): void {
        $this->importe = $importe;
    }

    public function setId_fc($id_fc): void {
        $this->id_fc = $id_fc;
    }

}
