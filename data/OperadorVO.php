<?php

/**
 * Description of OperadorVO
 * omicrom®
 * © 2022, Detisa 
 * http://www.detisa.com.mx
 * @author Alan Rodriguez 
 * @version 1.0
 * @since ago 2022
 */

class OperadorVO{
    
    private $id;
    private $rfc_operador;
    private $nombre;
    private $num_licencia;
    
    function __construct() {
        
    }
    function getId(){
        return $this->id;
    }
    function getRfc_operador(){
        return $this->rfc_operador;
    }
    function getNombre(){
        return $this->nombre;
    }
    function getNum_licencia(){
        return $this->num_licencia;
    }
    
    function setId($id){
        $this->id=$id;
    }
    function setRfc_operador($rfc_operador){
        $this->rfc_operador=$rfc_operador;
    }
    function setNombre($nombre){
        $this->nombre=$nombre;
    }
    function setNum_licencia($num_licencia){
        $this->num_licencia= $num_licencia;
    }
}