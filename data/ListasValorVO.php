<?php

/**
 * Description of ListasValorVO
 * omicrom®
 * © 2021, Detisa 
 * http://www.detisa.com.mx
 * @author Alejandro Ayala Gonzalez
 * @version 1.0
 * @since mar 2021
 */
class ListasValorVO {

    private $id_lista_valor;
    private $llave_lista_valor;
    private $valor_lista_valor;
    private $estado_lista_valor;
    private $alarma_lista_valor;
    private $id_lista_lista_valor;
    
    function __construct() {
        
    }
    
    function getId_lista_valor() {
        return $this->id_lista_valor;
    }

    function getLlave_lista_valor() {
        return $this->llave_lista_valor;
    }

    function getValor_lista_valor() {
        return $this->valor_lista_valor;
    }

    function getEstado_lista_valor() {
        return $this->estado_lista_valor;
    }

    function getAlarma_lista_valor() {
        return $this->alarma_lista_valor;
    }

    function getId_lista_lista_valor() {
        return $this->id_lista_lista_valor;
    }

    function setId_lista_valor($id_lista_valor) {
        $this->id_lista_valor = $id_lista_valor;
    }

    function setLlave_lista_valor($llave_lista_valor) {
        $this->llave_lista_valor = $llave_lista_valor;
    }

    function setValor_lista_valor($valor_lista_valor) {
        $this->valor_lista_valor = $valor_lista_valor;
    }

    function setEstado_lista_valor($estado_lista_valor) {
        $this->estado_lista_valor = $estado_lista_valor;
    }

    function setAlarma_lista_valor($alarma_lista_valor) {
        $this->alarma_lista_valor = $alarma_lista_valor;
    }

    function setId_lista_lista_valor($id_lista_lista_valor) {
        $this->id_lista_lista_valor = $id_lista_lista_valor;
    }


}
