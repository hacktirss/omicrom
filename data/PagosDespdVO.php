<?php

/**
 * Description of PagosDespdVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class PagosDespdVO {

    private $id;
    private $pago;
    private $referencia;
    private $importe;

    function __construct($pago, $referencia, $importe) {
        $this->pago = $pago;
        $this->referencia = $referencia;
        $this->importe = $importe;
    }

    function getId() {
        return $this->id;
    }

    function getPago() {
        return $this->pago;
    }

    function getReferencia() {
        return $this->referencia;
    }

    function getImporte() {
        return $this->importe;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setPago($pago) {
        $this->pago = $pago;
    }

    function setReferencia($referencia) {
        $this->referencia = $referencia;
    }

    function setImporte($importe) {
        $this->importe = $importe;
    }

}
