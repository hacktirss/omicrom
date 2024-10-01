<?php

/**
 * Description of SysFilesVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class SysFilesVO {

    private $key_file;
    private $file;
    private $description;
    private $format;
    private $additional;

    function __construct() {
        
    }

    function getKey_file() {
        return $this->key_file;
    }

    function getFile() {
        return $this->file;
    }

    function getDescription() {
        return $this->description;
    }

    function getFormat() {
        return $this->format;
    }

    function getAdditional() {
        return $this->additional;
    }

    function setKey_file($key_file) {
        $this->key_file = $key_file;
    }

    function setFile($file) {
        $this->file = $file;
    }

    function setDescription($description) {
        $this->description = $description;
    }

    function setFormat($format) {
        $this->format = $format;
    }

    function setAdditional($additional) {
        $this->additional = $additional;
    }

}
