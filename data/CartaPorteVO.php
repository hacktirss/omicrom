<?php

/**
 * Description of CartaPorteVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Alan Rodríguez Martínez
 * @version 1.0
 * @since dic 2021
 */

namespace com\detisa\omicrom {

    class CartaPorteVO {

        private $id;
        private $id_origen;
        private $origen;
        private $transpInternac;
        private $rfcRemitenteDestinatario;
        private $fechaHoraSalidaLlegada;
        private $moneda;
        private $embalaje;
        private $idOrigen = '00';
        private $idDestino = '00';
        private $id_operador = '0';
        private $id_vehiculo = '0';
        private $id_direccion = '0';

        function __construct() {
            
        }

        function getId() {
            return $this->id;
        }

        function getId_origen() {
            return $this->id_origen;
        }

        function getOrigen() {
            return $this->origen;
        }

        function getTranspInternac() {
            return $this->transpInternac;
        }

        function getRfcRemitenteDestinatario() {
            return $this->rfcRemitenteDestinatario;
        }

        function getFechaHoraSalidaLlegada() {
            return $this->fechaHoraSalidaLlegada;
        }

        function getMoneda() {
            return $this->moneda;
        }

        function getEmbalaje() {
            return $this->embalaje;
        }

        function getIdOrigen() {
            return $this->idOrigen;
        }

        function getIdDestino() {
            return $this->idDestino;
        }

        function getId_operador() {
            return $this->id_operador;
        }

        function getId_vehiculo() {
            return $this->id_vehiculo;
        }

        function getId_direccion() {
            return $this->id_direccion;
        }

        function setId($id) {
            $this->id = $id;
        }

        function setId_origen($id_origen) {
            $this->id_origen = $id_origen;
        }

        function setOrigen($origen) {
            $this->origen = $origen;
        }

        function setTranspInternac($transpInternac) {
            $this->transpInternac = $transpInternac;
        }

        function setRfcRemitenteDestinatario($rfcRemitenteDestinatario) {
            $this->rfcRemitenteDestinatario = $rfcRemitenteDestinatario;
        }

        function setFechaHoraSalidaLlegada($fechaHoraSalidaLlegada) {
            $this->fechaHoraSalidaLlegada = $fechaHoraSalidaLlegada;
        }

        function setMoneda($moneda) {
            $this->moneda = $moneda;
        }

        function setEmbalaje($embalaje) {
            $this->embalaje = $embalaje;
        }

        function setIdOrigen($idOrigen) {
            $this->idOrigen = $idOrigen;
        }

        function setIdDestino($idDestino) {
            $this->idDestino = $idDestino;
        }

        function setId_operador($id_operador) {
            $this->id_operador = $id_operador;
        }

        function setId_vehiculo($id_vehiculo) {
            $this->id_vehiculo = $id_vehiculo;
        }

        function setId_direccion($id_direccion) {
            $this->id_direccion = $id_direccion;
        }

    }

}