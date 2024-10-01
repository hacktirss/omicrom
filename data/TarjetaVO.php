<?php

/**
 * Description of TarjetaVO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
class TarjetaVO {

    private $id;
    private $descripcion;
    private $cliente;
    private $placas;
    private $codigo;
    private $impreso;
    private $combustible;
    private $litros;
    private $importe;
    private $periodo;
    private $simultaneo;
    private $local;
    private $luni;
    private $lunf;
    private $mari;
    private $marf;
    private $miei;
    private $mief;
    private $juei;
    private $juef;
    private $viei;
    private $vief;
    private $sabi;
    private $sabf;
    private $domi;
    private $domf;
    private $interes;
    private $estado;
    private $depto;
    private $nip;
    private $chip;
    private $departamento;
    private $numeco;

    function __construct() {
        
    }

    function getId() {
        return $this->id;
    }

    function getDescripcion() {
        return $this->descripcion;
    }

    function getCliente() {
        return $this->cliente;
    }

    function getPlacas() {
        return $this->placas;
    }

    function getCodigo() {
        return $this->codigo;
    }

    function getImpreso() {
        return $this->impreso;
    }

    function getCombustible() {
        return $this->combustible;
    }

    function getLitros() {
        return $this->litros;
    }

    function getImporte() {
        return $this->importe;
    }

    function getPeriodo() {
        return $this->periodo;
    }

    function getSimultaneo() {
        return $this->simultaneo;
    }

    function getLocal() {
        return $this->local;
    }

    function getLuni() {
        return $this->luni;
    }

    function getLunf() {
        return $this->lunf;
    }

    function getMari() {
        return $this->mari;
    }

    function getMarf() {
        return $this->marf;
    }

    function getMiei() {
        return $this->miei;
    }

    function getMief() {
        return $this->mief;
    }

    function getJuei() {
        return $this->juei;
    }

    function getJuef() {
        return $this->juef;
    }

    function getViei() {
        return $this->viei;
    }

    function getVief() {
        return $this->vief;
    }

    function getSabi() {
        return $this->sabi;
    }

    function getSabf() {
        return $this->sabf;
    }

    function getDomi() {
        return $this->domi;
    }

    function getDomf() {
        return $this->domf;
    }

    function getInteres() {
        return $this->interes;
    }

    function getEstado() {
        return $this->estado;
    }

    function getDepto() {
        return $this->depto;
    }

    function getNip() {
        return $this->nip;
    }

    function getChip() {
        return $this->chip;
    }

    function getDepartamento() {
        return $this->departamento;
    }

    function getNumeco() {
        return $this->numeco;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    function setCliente($cliente) {
        $this->cliente = $cliente;
    }

    function setPlacas($placas) {
        $this->placas = $placas;
    }

    function setCodigo($codigo) {
        $this->codigo = $codigo;
    }

    function setImpreso($impreso) {
        $this->impreso = $impreso;
    }

    function setCombustible($combustible) {
        $this->combustible = $combustible;
    }

    function setLitros($litros) {
        $this->litros = $litros;
    }

    function setImporte($importe) {
        $this->importe = $importe;
    }

    function setPeriodo($periodo) {
        $this->periodo = $periodo;
    }

    function setSimultaneo($simultaneo) {
        $this->simultaneo = $simultaneo;
    }

    function setLocal($local) {
        $this->local = $local;
    }

    function setLuni($luni) {
        $this->luni = $luni;
    }

    function setLunf($lunf) {
        $this->lunf = $lunf;
    }

    function setMari($mari) {
        $this->mari = $mari;
    }

    function setMarf($marf) {
        $this->marf = $marf;
    }

    function setMiei($miei) {
        $this->miei = $miei;
    }

    function setMief($mief) {
        $this->mief = $mief;
    }

    function setJuei($juei) {
        $this->juei = $juei;
    }

    function setJuef($juef) {
        $this->juef = $juef;
    }

    function setViei($viei) {
        $this->viei = $viei;
    }

    function setVief($vief) {
        $this->vief = $vief;
    }

    function setSabi($sabi) {
        $this->sabi = $sabi;
    }

    function setSabf($sabf) {
        $this->sabf = $sabf;
    }

    function setDomi($domi) {
        $this->domi = $domi;
    }

    function setDomf($domf) {
        $this->domf = $domf;
    }

    function setInteres($interes) {
        $this->interes = $interes;
    }

    function setEstado($estado) {
        $this->estado = $estado;
    }

    function setDepto($depto) {
        $this->depto = $depto;
    }

    function setNip($nip) {
        $this->nip = $nip;
    }

    function setChip($chip) {
        $this->chip = $chip;
    }

    function setDepartamento($departamento) {
        $this->departamento = $departamento;
    }

    function setNumeco($numeco) {
        $this->numeco = $numeco;
    }

    public function __toString() {
        $objectClass = "{id = " . $this->id . ",descripcion = " . $this->descripcion . ",cliente = " . $this->cliente . ", placas = " . $this->placas . ", codigo = " . $this->codigo . ","
                . " impreso = " . $this->impreso . ", periodo = " . $this->periodo . ", simultaneo = " . $this->simultaneo . ",local = " . $this->local . ",  interes = " . $this->interes . ","
                . " estado = " . $this->estado . ", depto = " . $this->depto . ", nip = " . $this->nip . ", chip = " . $this->chip . ", departamento = " . $this->departamento . "}";
        return $objectClass;
    }

}
