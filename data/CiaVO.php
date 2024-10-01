<?php

/*
 * CiaVO
 * omicrom®
 * © 2017, Detisa 
 * http://www.detisa.com.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since jul 2017
 */

class CiaVO {

    private $idfae;
    private $representante_legal;
    private $rfc_representante_legal;
    private $cia;
    private $direccion;
    private $numeroext;
    private $numeroint;
    private $colonia;
    private $ciudad;
    private $estado;
    private $telefono;
    private $desgloce;
    private $iva;
    private $rfc;
    private $regimen;
    private $codigo;
    private $pasw;
    private $estacion;
    private $factor;
    private $numestacion;
    private $clavepemex;
    private $segundos;
    private $lastpein;
    private $folenvios;
    private $clavegpg;
    private $folioenvios;
    private $serie;
    private $facturacion;
    private $facclavesat;
    private $zonahoraria;
    private $master;
    private $clavesat;
    private $claveterminal;
    private $pesosporpunto;
    private $sesion;
    private $rfc_proveedor_sw;
    private $clave_envios_xml;
    private $activa_envio_xml;
    private $md5;
    private $firmwaremd5;
    private $direccionexp;
    private $numeroextexp;
    private $numerointexp;
    private $coloniaexp;
    private $ciudadexp;
    private $estadoexp;
    private $codigoexp;
    private $vigencia;
    private $ventastarxticket;
    private $diaslimiteticket;
    private $clave_regimen;
    private $version_cfdi;
    private $clave_cert_cv;
    private $permisocre;
    private $latitud;
    private $longitud;
    private $clave_instalacion;
    private $caracter_sat;
    private $modalidad_permiso;
    private $descripcion;

    function __construct() {
        
    }

    function getIdfae() {
        return $this->idfae;
    }

    function getRepresentante_legal() {
        return $this->representante_legal;
    }

    function getRfc_representante_legal() {
        return $this->rfc_representante_legal;
    }

    function getCia() {
        return $this->cia;
    }

    function getDireccion() {
        return $this->direccion;
    }

    function getNumeroext() {
        return $this->numeroext;
    }

    function getNumeroint() {
        return $this->numeroint;
    }

    function getColonia() {
        return $this->colonia;
    }

    function getCiudad() {
        return $this->ciudad;
    }

    function getEstado() {
        return $this->estado;
    }

    function getTelefono() {
        return $this->telefono;
    }

    function getDesgloce() {
        return $this->desgloce;
    }

    function getIva() {
        return $this->iva;
    }

    function getRfc() {
        return $this->rfc;
    }

    function getRegimen() {
        return $this->regimen;
    }

    function getCodigo() {
        return $this->codigo;
    }

    function getPasw() {
        return $this->pasw;
    }

    function getEstacion() {
        return $this->estacion;
    }

    function getFactor() {
        return $this->factor;
    }

    function getNumestacion() {
        return $this->numestacion;
    }

    function getClavepemex() {
        return $this->clavepemex;
    }

    function getSegundos() {
        return $this->segundos;
    }

    function getLastpein() {
        return $this->lastpein;
    }

    function getFolenvios() {
        return $this->folenvios;
    }

    function getClavegpg() {
        return $this->clavegpg;
    }

    function getFolioenvios() {
        return $this->folioenvios;
    }

    function getSerie() {
        return $this->serie;
    }

    function getFacturacion() {
        return $this->facturacion;
    }

    function getFacclavesat() {
        return $this->facclavesat;
    }

    function getZonahoraria() {
        return $this->zonahoraria;
    }

    function getMaster() {
        return $this->master;
    }

    function getClavesat() {
        return $this->clavesat;
    }

    function getClaveterminal() {
        return $this->claveterminal;
    }

    function getPesosporpunto() {
        return $this->pesosporpunto;
    }

    function getSesion() {
        return $this->sesion;
    }

    function getRfc_proveedor_sw() {
        return $this->rfc_proveedor_sw;
    }

    function getClave_envios_xml() {
        return $this->clave_envios_xml;
    }

    function getActiva_envio_xml() {
        return $this->activa_envio_xml;
    }

    function getMd5() {
        return $this->md5;
    }

    function getFirmwaremd5() {
        return $this->firmwaremd5;
    }

    function getDireccionexp() {
        return $this->direccionexp;
    }

    function getNumeroextexp() {
        return $this->numeroextexp;
    }

    function getNumerointexp() {
        return $this->numerointexp;
    }

    function getColoniaexp() {
        return $this->coloniaexp;
    }

    function getCiudadexp() {
        return $this->ciudadexp;
    }

    function getEstadoexp() {
        return $this->estadoexp;
    }

    function getCodigoexp() {
        return $this->codigoexp;
    }

    function getVigencia() {
        return $this->vigencia;
    }

    function getVentastarxticket() {
        return $this->ventastarxticket;
    }

    function getDiaslimiteticket() {
        return $this->diaslimiteticket;
    }

    function getClave_regimen() {
        return $this->clave_regimen;
    }

    function getVersion_cfdi() {
        return $this->version_cfdi;
    }

    function getClave_cert_cv() {
        return $this->clave_cert_cv;
    }

    function getClave_instalacion() {
        return $this->clave_instalacion;
    }

    function getCaracter_sat() {
        return $this->caracter_sat;
    }

    function getModalidad_permiso() {
        return $this->modalidad_permiso;
    }

    function getDescripcion() {
        return $this->descripcion;
    }

    function setIdfae($idfae) {
        $this->idfae = $idfae;
    }

    function setRepresentante_legal($representante_legal) {
        $this->representante_legal = $representante_legal;
    }

    function setRfc_representante_legal($rfc_representante_legal) {
        $this->rfc_representante_legal = $rfc_representante_legal;
    }

    function setCia($cia) {
        $this->cia = $cia;
    }

    function setDireccion($direccion) {
        $this->direccion = $direccion;
    }

    function setNumeroext($numeroext) {
        $this->numeroext = $numeroext;
    }

    function setNumeroint($numeroint) {
        $this->numeroint = $numeroint;
    }

    function setColonia($colonia) {
        $this->colonia = $colonia;
    }

    function setCiudad($ciudad) {
        $this->ciudad = $ciudad;
    }

    function setEstado($estado) {
        $this->estado = $estado;
    }

    function setTelefono($telefono) {
        $this->telefono = $telefono;
    }

    function setDesgloce($desgloce) {
        $this->desgloce = $desgloce;
    }

    function setIva($iva) {
        $this->iva = $iva;
    }

    function setRfc($rfc) {
        $this->rfc = $rfc;
    }

    function setRegimen($regimen) {
        $this->regimen = $regimen;
    }

    function setCodigo($codigo) {
        $this->codigo = $codigo;
    }

    function setPasw($pasw) {
        $this->pasw = $pasw;
    }

    function setEstacion($estacion) {
        $this->estacion = $estacion;
    }

    function setFactor($factor) {
        $this->factor = $factor;
    }

    function setNumestacion($numestacion) {
        $this->numestacion = $numestacion;
    }

    function setClavepemex($clavepemex) {
        $this->clavepemex = $clavepemex;
    }

    function setSegundos($segundos) {
        $this->segundos = $segundos;
    }

    function setLastpein($lastpein) {
        $this->lastpein = $lastpein;
    }

    function setFolenvios($folenvios) {
        $this->folenvios = $folenvios;
    }

    function setClavegpg($clavegpg) {
        $this->clavegpg = $clavegpg;
    }

    function setFolioenvios($folioenvios) {
        $this->folioenvios = $folioenvios;
    }

    function setSerie($serie) {
        $this->serie = $serie;
    }

    function setFacturacion($facturacion) {
        $this->facturacion = $facturacion;
    }

    function setFacclavesat($facclavesat) {
        $this->facclavesat = $facclavesat;
    }

    function setZonahoraria($zonahoraria) {
        $this->zonahoraria = $zonahoraria;
    }

    function setMaster($master) {
        $this->master = $master;
    }

    function setClavesat($clavesat) {
        $this->clavesat = $clavesat;
    }

    function setClaveterminal($claveterminal) {
        $this->claveterminal = $claveterminal;
    }

    function setPesosporpunto($pesosporpunto) {
        $this->pesosporpunto = $pesosporpunto;
    }

    function setSesion($sesion) {
        $this->sesion = $sesion;
    }

    function setRfc_proveedor_sw($rfc_proveedor_sw) {
        $this->rfc_proveedor_sw = $rfc_proveedor_sw;
    }

    function setClave_envios_xml($clave_envios_xml) {
        $this->clave_envios_xml = $clave_envios_xml;
    }

    function setActiva_envio_xml($activa_envio_xml) {
        $this->activa_envio_xml = $activa_envio_xml;
    }

    function setMd5($md5) {
        $this->md5 = $md5;
    }

    function setFirmwaremd5($firmwaremd5) {
        $this->firmwaremd5 = $firmwaremd5;
    }

    function setDireccionexp($direccionexp) {
        $this->direccionexp = $direccionexp;
    }

    function setNumeroextexp($numeroextexp) {
        $this->numeroextexp = $numeroextexp;
    }

    function setNumerointexp($numerointexp) {
        $this->numerointexp = $numerointexp;
    }

    function setColoniaexp($coloniaexp) {
        $this->coloniaexp = $coloniaexp;
    }

    function setCiudadexp($ciudadexp) {
        $this->ciudadexp = $ciudadexp;
    }

    function setEstadoexp($estadoexp) {
        $this->estadoexp = $estadoexp;
    }

    function setCodigoexp($codigoexp) {
        $this->codigoexp = $codigoexp;
    }

    function setVigencia($vigencia) {
        $this->vigencia = $vigencia;
    }

    function setVentastarxticket($ventastarxticket) {
        $this->ventastarxticket = $ventastarxticket;
    }

    function setDiaslimiteticket($diaslimiteticket) {
        $this->diaslimiteticket = $diaslimiteticket;
    }

    function setClave_regimen($clave_regimen) {
        $this->clave_regimen = $clave_regimen;
    }

    function setVersion_cfdi($version_cfdi) {
        $this->version_cfdi = $version_cfdi;
    }

    function setClave_cert_cv($clave_cert_cv) {
        $this->clave_cert_cv = $clave_cert_cv;
    }

    function getPermisocre() {
        return $this->permisocre;
    }

    function setPermisocre($permisocre) {
        $this->permisocre = $permisocre;
    }

    function getLatitud() {
        return $this->latitud;
    }

    function getLongitud() {
        return $this->longitud;
    }

    function setLatitud($latitud) {
        $this->latitud = $latitud;
    }

    function setLongitud($longitud) {
        $this->longitud = $longitud;
    }

    function setClave_instalacion($clave_instalacion) {
        $this->clave_instalacion = $clave_instalacion;
    }

    function setCaracter_sat($caracter_sat) {
        $this->caracter_sat = $caracter_sat;
    }

    function setModalidad_permiso($modalidad_permiso) {
        $this->modalidad_permiso = $modalidad_permiso;
    }

    function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }

    public static function retrieveDeencryptFieds() {
        return " idfae
                ,deencrypt_data(representante_legal) representante_legal
                ,deencrypt_data(rfc_representante_legal) rfc_representante_legal
                ,cia
                ,direccion
                ,numeroext
                ,numeroint
                ,colonia
                ,ciudad
                ,cia.estado
                ,telefono
                ,desgloce
                ,iva
                ,rfc
                ,regimen
                ,codigo
                ,pasw
                ,estacion
                ,factor
                ,numestacion
                ,deencrypt_data(clavepemex) clavepemex
                ,segundos
                ,lastpein
                ,folenvios
                ,deencrypt_data(clavegpg) clavegpg
                ,folioenvios
                ,serie
                ,facturacion
                ,deencrypt_data(facclavesat) facclavesat
                ,zonahoraria
                ,master
                ,deencrypt_data(clavesat) clavesat
                ,deencrypt_data(claveterminal) claveterminal
                ,pesosporpunto
                ,sesion
                ,rfc_proveedor_sw
                ,deencrypt_data(clave_envios_xml) clave_envios_xml
                ,activa_envio_xml
                ,md5
                ,firmwaremd5
                ,direccionexp
                ,numeroextexp
                ,numerointexp
                ,coloniaexp
                ,ciudadexp
                ,estadoexp
                ,codigoexp
                ,vigencia
                ,ventastarxticket
                ,diaslimiteticket
                ,clave_regimen
                ,version_cfdi
                ,deencrypt_data(clave_cert_cv) clave_cert_cv
                ,latitudGPS
                ,longitudGPS
                ,clave_instalacion 
                ,caracter_sat
                ,modalidad_permiso
                ,cia.descripcion ";
    }

    public static function retrieveEncryptFieds() {
        return "idfae = ?, "
                . "representante_legal = encrypt_data(?), "
                . "rfc_representante_legal = encrypt_data(?), "
                . "cia = ?, "
                . "direccion = ?, "
                . "numeroext = ?, "
                . "numeroint = ?, "
                . "colonia = ?, "
                . "ciudad = ?, "
                . "estado = ?, "
                . "telefono = ?, "
                . "desgloce = ?, "
                . "iva = ?, "
                . "rfc = ?, "
                . "regimen = ?, "
                . "codigo = ?, "
                . "pasw = ?, "
                . "estacion = ?, "
                . "factor = ?, "
                . "numestacion = ?, "
                . "clavepemex = encrypt_data(?), "
                . "segundos = ?, "
                . "lastpein = ?, "
                . "folenvios = ?, "
                . "clavegpg = encrypt_data(?), "
                . "folioenvios = ?, "
                . "serie = ?, "
                . "facturacion = ?, "
                . "facclavesat = encrypt_data(?), "
                . "zonahoraria = ?, "
                . "master = ?, "
                . "clavesat = encrypt_data(?), "
                . "claveterminal = encrypt_data(?), "
                . "pesosporpunto = ?, "
                . "sesion = ?, "
                . "rfc_proveedor_sw = ?, "
                . "clave_envios_xml = encrypt_data(?), "
                . "activa_envio_xml = ?, "
                . "md5 = ?, "
                . "firmwaremd5 = ?, "
                . "direccionexp = ?, "
                . "numeroextexp = ?, "
                . "numerointexp = ?, "
                . "coloniaexp = ?, "
                . "ciudadexp = ?, "
                . "estadoexp = ?, "
                . "codigoexp = ?, "
                . "vigencia = ?, "
                . "ventastarxticket = ?, "
                . "diaslimiteticket = ?, "
                . "clave_regimen = ?, "
                . "version_cfdi = ?, "
                . "clave_cert_cv = encrypt_data(?), "
                . "latitudGPS = ?, "
                . "longitudGPS = ?, "
                . "clave_instalacion = ?, "
                . "caracter_sat = ?, "
                . "modalidad_permiso = ?,"
                . "descripcion = ? ";
    }

    public function __toString() {
        return print_r($this, true);
    }

}
