<?php

/*
 * ClientesVO
 * omicrom
 * 2017, Detisa 
 * http://www.detisa.com.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since jul 2017
 */

class ClientesVO {

    private $id;
    private $nombre;
    private $direccion = "";
    private $colonia = "";
    private $municipio = "";
    private $alias = "";
    private $telefono = "";
    private $activo = "Si";
    private $contacto = "";
    private $observaciones = "";
    private $tipodepago = "Contado";
    private $limite = 0;
    private $rfc = "";
    private $codigo = "";
    private $correo = "";
    private $numeroext = "";
    private $numeroint = "";
    private $enviarcorreo = "Si";
    private $cuentaban = "";
    private $estado = "";
    private $formadepago = "01";
    private $correo2 = "";
    private $puntos = 0;
    private $desgloseIEPS = "N";
    private $ncc = "";
    private $nombreFactura = "";
    private $facturacion = 1;
    private $autorizaCorporativo = 0;
    private $RegimenFiscal = 601;
    private $ultimaModificacion = "";
    private $diasCredito = 0;
    private $tipoMonedero;

    private function nvl($value) {
        return $value === NULL ? "" : $value;
    }

    public function getTipoMonedero() {
        return $this->tipoMonedero;
    }

    public function setTipoMonedero($tipoMonedero): void {
        $this->tipoMonedero = $tipoMonedero;
    }

    function getId() {
        return $this->nvl($this->id);
    }

    function getNombre() {
        return $this->nvl($this->nombre);
    }

    function getDireccion() {
        return $this->nvl($this->direccion);
    }

    function getColonia() {
        return $this->nvl($this->colonia);
    }

    function getMunicipio() {
        return $this->nvl($this->municipio);
    }

    function getAlias() {
        return $this->nvl($this->alias);
    }

    function getTelefono() {
        return $this->nvl($this->telefono);
    }

    function getActivo() {
        return $this->activo == null ? "Si" : $this->activo;
    }

    function getContacto() {
        return $this->nvl($this->contacto);
    }

    function getObservaciones() {
        return $this->nvl($this->observaciones);
    }

    function getTipodepago() {
        return $this->nvl($this->tipodepago);
    }

    function getLimite() {
        return $this->nvl($this->limite) === "" ? 0 : $this->limite;
    }

    function getRfc() {
        return $this->nvl($this->rfc);
    }

    function getCodigo() {
        return $this->nvl($this->codigo);
    }

    function getCorreo() {
        return $this->nvl($this->correo);
    }

    function getNumeroext() {
        return $this->nvl($this->numeroext);
    }

    function getNumeroint() {
        return $this->nvl($this->numeroint);
    }

    function getEnviarcorreo() {
        return $this->nvl($this->enviarcorreo);
    }

    function getCuentaban() {
        return $this->nvl($this->cuentaban);
    }

    function getEstado() {
        return $this->nvl($this->estado);
    }

    function getFormadepago() {
        return $this->nvl($this->formadepago);
    }

    function getCorreo2() {
        return $this->nvl($this->correo2);
    }

    function getPuntos() {
        return $this->nvl($this->puntos) === "" ? 0 : $this->puntos;
    }

    function getDesgloseIEPS() {
        return $this->nvl($this->desgloseIEPS);
    }

    function getNcc() {
        return $this->nvl($this->ncc);
    }

    function getNombreFactura() {
        return $this->nombreFactura;
    }

    function getAutorizaCorporativo() {
        return $this->autorizaCorporativo;
    }

    function getRegimenFiscal() {
        return $this->RegimenFiscal;
    }

    function getUlitmaModificacion() {
        return $this->ultimaModificacion;
    }

    function getDiasCredito() {
        return $this->diasCredito;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    function setDireccion($direccion) {
        $this->direccion = $direccion;
    }

    function setColonia($colonia) {
        $this->colonia = $colonia;
    }

    function setMunicipio($municipio) {
        $this->municipio = $municipio;
    }

    function setAlias($alias) {
        $this->alias = $alias;
    }

    function setTelefono($telefono) {
        $this->telefono = $telefono;
    }

    function setActivo($activo) {
        $this->activo = $activo;
    }

    function setContacto($contacto) {
        $this->contacto = $contacto;
    }

    function setObservaciones($observaciones) {
        $this->observaciones = $observaciones;
    }

    function setTipodepago($tipodepago) {
        $this->tipodepago = $tipodepago;
    }

    function setLimite($limite) {
        $this->limite = $limite;
    }

    function setRfc($rfc) {
        $this->rfc = $rfc;
    }

    function setCodigo($codigo) {
        $this->codigo = $codigo;
    }

    function setCorreo($correo) {
        $this->correo = $correo;
    }

    function setNumeroext($numeroext) {
        $this->numeroext = $numeroext;
    }

    function setNumeroint($numeroint) {
        $this->numeroint = $numeroint;
    }

    function setEnviarcorreo($enviarcorreo) {
        $this->enviarcorreo = $enviarcorreo;
    }

    function setCuentaban($cuentaban) {
        $this->cuentaban = $cuentaban;
    }

    function setEstado($estado) {
        $this->estado = $estado;
    }

    function setFormadepago($formadepago) {
        $this->formadepago = $formadepago;
    }

    function setCorreo2($correo2) {
        $this->correo2 = $correo2;
    }

    function setPuntos($puntos) {
        $this->puntos = $puntos;
    }

    function setDesgloseIEPS($desgloseIEPS) {
        $this->desgloseIEPS = $desgloseIEPS;
    }

    function setNcc($ncc) {
        $this->ncc = $ncc;
    }

    function setNombreFactura($nombreFactura) {
        $this->nombreFactura = $nombreFactura;
    }

    public function getFacturacion() {
        return $this->facturacion;
    }

    public function setFacturacion($facturacion) {
        $this->facturacion = $facturacion;
    }

    function setAutorizaCorporativo($autorizaCorporativo) {
        $this->autorizaCorporativo = $autorizaCorporativo;
    }

    function setRegimenFiscal($RegimenFiscal) {
        $this->RegimenFiscal = $RegimenFiscal;
    }

    function setUltimaModificacion($UltimaModificacion) {
        $this->ultimaModificacion = $UltimaModificacion;
    }

    function setDiasCredito($DiasCredito) {
        $this->diasCredito = $DiasCredito;
    }

    public static function retrieveDeencryptFieds() {
        return "id,"
                . "nombre,"
                . "deencrypt_data(direccion) direccion,"
                . "deencrypt_data(colonia) colonia,"
                . "deencrypt_data(municipio) municipio,"
                . "alias,"
                . "deencrypt_data(telefono) telefono,"
                . "activo,"
                . "contacto,"
                . "observaciones,"
                . "tipodepago,"
                . "limite,"
                . "rfc,"
                . "codigo,"
                . "deencrypt_data(correo) correo,"
                . "deencrypt_data(numeroext) numeroext,"
                . "deencrypt_data(numeroint) numeroint,"
                . "enviarcorreo,"
                . "deencrypt_data(cuentaban) cuentaban,"
                . "estado,"
                . "formadepago,"
                . "correo2,"
                . "puntos,"
                . "desgloseIEPS,"
                . "ncc,"
                . "nombreFactura,"
                . "facturacion ,"
                . "autorizaCorporativo,"
                . "regimenfiscal,"
                . "ultimaModificacion,"
                . "diasCredito,"
                . "tipoMonedero";
    }

    public static function retrieveEncryptFieds() {
        return "nombre = ?, "
                . "direccion = encrypt_data(?), "
                . "colonia = encrypt_data(?), "
                . "municipio = encrypt_data(?), "
                . "alias = ?, "
                . "telefono = encrypt_data(?), "
                . "activo = ?, "
                . "contacto = ?, "
                . "observaciones = ?, "
                . "tipodepago = ?, "
                . "limite = ?, "
                . "rfc = ?, "
                . "codigo = ?, "
                . "correo = encrypt_data(?), "
                . "numeroext = encrypt_data(?), "
                . "numeroint = encrypt_data(?), "
                . "enviarcorreo = ?, "
                . "cuentaban = encrypt_data(?), "
                . "estado = ?, "
                . "formadepago = ?, "
                . "correo2 = ?, "
                . "puntos = ?, "
                . "desgloseIEPS = ?, "
                . "ncc = ?, "
                . "nombreFactura = ?, "
                . "facturacion = ? , "
                . "autorizaCorporativo = ? ,"
                . "regimenfiscal = ? ,"
                . "ultimaModificacion = ? ,"
                . "diasCredito = ?,"
                . "tipoMonedero = ? ";
    }

    public static function prepareEncryptFieds() {
        return "?, "
                . "encrypt_data(?), "
                . "encrypt_data(?), "
                . "encrypt_data(?), "
                . "?, "
                . "encrypt_data(?), "
                . "?, "
                . "?, "
                . "?, "
                . "?, "
                . "?, "
                . "?, "
                . "?, "
                . "encrypt_data(?), "
                . "encrypt_data(?), "
                . "encrypt_data(?), "
                . "?, "
                . "encrypt_data(?), "
                . "?, "
                . "?, "
                . "?, "
                . "?, "
                . "?, "
                . "?, "
                . "?, "
                . "?, "
                . "?,"
                . "?, "
                . "?, "
                . "?,"
                . "? ";
    }

    /**
     * Parses paramaeters array into ClientesVO = object
     * @param array $queryParameters
     * @return ClientesVO
     */
    public static function parse($queryParameters) {
        $cliente = new ClientesVO();
        $cliente->setId($queryParameters['Cliente']);
        $cliente->setNombre($queryParameters['Nombre']);
        $cliente->setAlias($queryParameters['Alias']);
        $cliente->setRfc($queryParameters['Rfc']);
        $cliente->setDireccion($queryParameters['Direccion']);
        $cliente->setNumeroext($queryParameters['Numeroext']);
        $cliente->setNumeroint($queryParameters['Numeroint']);
        $cliente->setColonia($queryParameters['Colonia']);
        $cliente->setMunicipio($queryParameters['Municipio']);
        $cliente->setEstado($queryParameters['Estado']);
        $cliente->setCodigo($queryParameters['Codigo']);
        $cliente->setContacto($queryParameters['Contacto']);
        $cliente->setTelefono($queryParameters['Telefono']);
        $cliente->setCorreo($queryParameters['Correo']);
        $cliente->setEnviarcorreo($queryParameters['Enviarcorreo']);
        $cliente->setCuentaban($queryParameters['Cuentaban']);
        $cliente->setFormadepago($queryParameters['Formadepago']);
        $cliente->setTipodepago($queryParameters['Tipodepago']);
        $cliente->setLimite($queryParameters['Limite']);
        $cliente->setDesgloseIEPS($queryParameters['DesgloseIeps']);
        $cliente->setNcc($queryParameters['Ncc']);
        $cliente->setNombreFactura($queryParameters['nombreFactura']);
        $cliente->setFacturacion($queryParameters['facturacion']);
        $cliente->setAutorizaCorporativo($queryParameters['autorizaCorporativo']);
        $cliente->setRegimenFiscal($queryParameters['regimenfiscal']);
        $cliente->setUltimaModificacion($queryParameters['ultimaModificacion']);
        $cliente->setDiasCredito($queryParameters['diasCredito']);
        //error_log($cliente);
        return $cliente;
    }

//parse

    /**
     * Overrides toString function
     * @return String
     */
    public function __toString() {
        return "ClientesVO={id=" . $this->id
                . "nombre=" . $this->nombre
                . ",alias=" . $this->alias
                . ",rfc=" . $this->rfc
                . ",direccion=" . $this->direccion
                . ",numeroext=" . $this->numeroext
                . ",numeroint=" . $this->numeroint
                . ",colonia=" . $this->colonia
                . ",municipio=" . $this->municipio
                . ",estado=" . $this->estado
                . ",contacto=" . $this->contacto
                . ",telefono=" . $this->telefono
                . ",correo=" . $this->correo
                . ",correo2=" . $this->correo2
                . ",enviarcorreo=" . $this->enviarcorreo
                . ",cuentaban=" . $this->cuentaban
                . ",formadepago=" . $this->formadepago
                . ",tipodepago=" . $this->tipodepago
                . ",limite=" . $this->limite
                . ",codigo=" . $this->codigo
                . ",puntos=" . $this->puntos
                . ",desgloseIEPS=" . $this->desgloseIEPS
                . ",ncc=" . $this->ncc
                . ",nombreFactura=" . $this->nombreFactura
                . ",observaciones=" . $this->observaciones
                . ",facturacion=" . $this->facturacion
                . ",activo=" . $this->activo . ""
                . ",autorizaCorporativo = " . $this->autorizaCorporativo . ""
                . ",regimenFiscal = " . $this->RegimenFiscal . ","
                . "ultimaModificacion = " . $this->ultimaModificacion . ""
                . ",diasCredito = " . $this->diasCredito . "}";
    }

//__toString

    /**
     * @return String
     */
    public function __toDescription() {
        return $this->id . " | " . $this->tipodepago . " | " . $this->nombre;
    }

//__toDescription
}

//ClienteVO
