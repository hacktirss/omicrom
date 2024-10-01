<?php

/*
 * CiaDAO
 * omicrom®
 * © 2017, Detisa 
 * http://www.detisa.com.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since jul 2017
 */

include_once ('mysqlUtils.php');
include_once ('CiaVO.php');
include_once ('FunctionsDAO.php');
include_once ('BitacoraDAO.php');
include_once ('V_CorporativoDAO.php');

class CiaDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "cia";

    private $conn;
    private $v_corporativoDAO;
    private $encrypt = 0;

    public function __construct() {
        $this->conn = getConnection();
        $this->v_corporativoDAO = new V_CorporativoDAO();
        $v_corporativoVO = $this->v_corporativoDAO->retrieve(V_CorporativoDAO::ENCRIPT_FIELD);
        $this->encrypt = $v_corporativoVO->getValor();
    }

    public function __destruct() {
        $this->conn->close();
    }

    public function create($objectVO) {
        error_log(print_r($objectVO, TRUE));
    }

    /*
     * @return CiaVO
     */

    public function fillObject($rs) {
        $objectVO = new CiaVO();
        if (is_array($rs)) {
            $objectVO->setIdfae($rs["idfae"]);
            $objectVO->setRepresentante_legal($rs["representante_legal"]);
            $objectVO->setRfc_representante_legal($rs["rfc_representante_legal"]);
            $objectVO->setCia($rs["cia"]);
            $objectVO->setDireccion($rs["direccion"]);
            $objectVO->setNumeroext($rs["numeroext"]);
            $objectVO->setNumeroint($rs["numeroint"]);
            $objectVO->setColonia($rs["colonia"]);
            $objectVO->setCiudad($rs["ciudad"]);
            $objectVO->setEstado($rs["estado"]);
            $objectVO->setTelefono($rs["telefono"]);
            $objectVO->setDesgloce($rs["desgloce"]);
            $objectVO->setIva($rs["iva"]);
            $objectVO->setRfc($rs["rfc"]);
            $objectVO->setRegimen($rs["regimen"]);
            $objectVO->setCodigo($rs["codigo"]);
            $objectVO->setPasw($rs["pasw"]);
            $objectVO->setEstacion($rs["estacion"]);
            $objectVO->setFactor($rs["factor"]);
            $objectVO->setNumestacion($rs["numestacion"]);
            $objectVO->setClavepemex($rs["clavepemex"]);
            $objectVO->setSegundos($rs["segundos"]);
            $objectVO->setLastpein($rs["lastpein"]);
            $objectVO->setFolenvios($rs["folenvios"]);
            $objectVO->setClavegpg($rs["clavegpg"]);
            $objectVO->setFolioenvios($rs["folioenvios"]);
            $objectVO->setSerie($rs["serie"]);
            $objectVO->setFacturacion($rs["facturacion"]);
            $objectVO->setFacclavesat($rs["facclavesat"]);
            $objectVO->setZonahoraria($rs["zonahoraria"]);
            $objectVO->setMaster($rs["master"]);
            $objectVO->setClavesat($rs["clavesat"]);
            $objectVO->setClaveterminal($rs["claveterminal"]);
            $objectVO->setPesosporpunto($rs["pesosporpunto"]);
            $objectVO->setSesion($rs["sesion"]);
            $objectVO->setRfc_proveedor_sw($rs["rfc_proveedor_sw"]);
            $objectVO->setClave_envios_xml($rs["clave_envios_xml"]);
            $objectVO->setActiva_envio_xml($rs["activa_envio_xml"]);
            $objectVO->setMd5($rs["md5"]);
            $objectVO->setFirmwaremd5($rs["firmwaremd5"]);
            $objectVO->setDireccionexp($rs["direccionexp"]);
            $objectVO->setNumeroextexp($rs["numeroextexp"]);
            $objectVO->setNumerointexp($rs["numerointexp"]);
            $objectVO->setColoniaexp($rs["coloniaexp"]);
            $objectVO->setCiudadexp($rs["ciudadexp"]);
            $objectVO->setEstadoexp($rs["estadoexp"]);
            $objectVO->setCodigoexp($rs["codigoexp"]);
            $objectVO->setVigencia($rs["vigencia"]);
            $objectVO->setVentastarxticket($rs["ventastarxticket"]);
            $objectVO->setDiaslimiteticket($rs["diaslimiteticket"]);
            $objectVO->setClave_regimen($rs["clave_regimen"]);
            $objectVO->setVersion_cfdi($rs["version_cfdi"]);
            $objectVO->setClave_cert_cv($rs["clave_cert_cv"]);
            $objectVO->setPermisocre($rs["permisocre"]);
            $objectVO->setLatitud($rs["latitudGPS"]);
            $objectVO->setLongitud($rs["longitudGPS"]);
            $objectVO->setClave_instalacion($rs["clave_instalacion"]);
            $objectVO->setCaracter_sat($rs["caracter_sat"]);
            $objectVO->setModalidad_permiso($rs["modalidad_permiso"]);
            $objectVO->setDescripcion($rs["descrip"]);
        }
        return $objectVO;
    }

    public function getAll($sql) {
        error_log($sql);
    }

    /**
     * 
     * @param int $idObject
     * @param string $fields
     * @return CiaVO
     */
    public function retrieve($idObject, $fields = "*") {
        $cia = new CiaVO();
        if ($fields === "*" && $this->encrypt == 1) {
            $fields = CiaVO::retrieveDeencryptFieds();
        }
        $sql = "SELECT " . $fields . ", IFNULL(cre.permiso, '') permisocre,cia.descripcion as descrip FROM cia 
                LEFT JOIN permisos_cre cre ON TRUE
                AND cre.catalogo = 'VARIABLES_EMPRESA'
                AND cre.llave = 'PERMISO_CRE'
                WHERE " . $idObject;
        if (($query = $this->conn->query($sql)) && ($rs = $query->fetch_assoc())) {
            $cia = $this->fillObject($rs);
        } else {
            error_log($this->conn->error);
        }
        return $cia;
    }

    /**
     * 
     * @param CiaVO $objectVO
     * @return boolean
     */
    public function update($objectVO) {
        $usuarioSesion = getSessionUsuario();
        $sql = " UPDATE " . self::TABLA . " SET "
                . "idfae = ?, "
                . "representante_legal = ?, "
                . "rfc_representante_legal = ?, "
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
                . "clavepemex = ?, "
                . "segundos = ?, "
                . "lastpein = ?, "
                . "folenvios = ?, "
                . "clavegpg = ?, "
                . "folioenvios = ?, "
                . "serie = ?, "
                . "facturacion = ?, "
                . "facclavesat = ?, "
                . "zonahoraria = ?, "
                . "master = ?, "
                . "clavesat = ?, "
                . "claveterminal = ?, "
                . "pesosporpunto = ?, "
                . "sesion = ?, "
                . "rfc_proveedor_sw = ?, "
                . "clave_envios_xml = ?, "
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
                . "clave_cert_cv = ?, "
                . "latitudGPS = ?, "
                . "longitudGPS = ?, "
                . "clave_instalacion = ?, "
                . "caracter_sat = ?, "
                . "modalidad_permiso = ?, "
                . "descripcion = ? "
                . "WHERE 1";

        if ($this->encrypt == 1) {
            $sql = " UPDATE " . self::TABLA . " SET " . CiaVO::retrieveEncryptFieds() . " WHERE 1";
        }

        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssssssssssssssssssssssssssssssssssssssssssssssssddssss",
                    $objectVO->getIdfae(),
                    $objectVO->getRepresentante_legal(),
                    $objectVO->getRfc_representante_legal(),
                    $objectVO->getCia(),
                    $objectVO->getDireccion(),
                    $objectVO->getNumeroext(),
                    $objectVO->getNumeroint(),
                    $objectVO->getColonia(),
                    $objectVO->getCiudad(),
                    $objectVO->getEstado(),
                    $objectVO->getTelefono(),
                    $objectVO->getDesgloce(),
                    $objectVO->getIva(),
                    $objectVO->getRfc(),
                    $objectVO->getRegimen(),
                    $objectVO->getCodigo(),
                    $objectVO->getPasw(),
                    $objectVO->getEstacion(),
                    $objectVO->getFactor(),
                    $objectVO->getNumestacion(),
                    $objectVO->getClavepemex(),
                    $objectVO->getSegundos(),
                    $objectVO->getLastpein(),
                    $objectVO->getFolenvios(),
                    $objectVO->getClavegpg(),
                    $objectVO->getFolioenvios(),
                    $objectVO->getSerie(),
                    $objectVO->getFacturacion(),
                    $objectVO->getFacclavesat(),
                    $objectVO->getZonahoraria(),
                    $objectVO->getMaster(),
                    $objectVO->getClavesat(),
                    $objectVO->getClaveterminal(),
                    $objectVO->getPesosporpunto(),
                    $objectVO->getSesion(),
                    $objectVO->getRfc_proveedor_sw(),
                    $objectVO->getClave_envios_xml(),
                    $objectVO->getActiva_envio_xml(),
                    $objectVO->getMd5(),
                    $objectVO->getFirmwaremd5(),
                    $objectVO->getDireccionexp(),
                    $objectVO->getNumeroextexp(),
                    $objectVO->getNumerointexp(),
                    $objectVO->getColoniaexp(),
                    $objectVO->getCiudadexp(),
                    $objectVO->getEstadoexp(),
                    $objectVO->getCodigoexp(),
                    $objectVO->getVigencia(),
                    $objectVO->getVentastarxticket(),
                    $objectVO->getDiaslimiteticket(),
                    $objectVO->getClave_regimen(),
                    $objectVO->getVersion_cfdi(),
                    $objectVO->getClave_cert_cv(),
                    $objectVO->getLatitud(),
                    $objectVO->getLongitud(),
                    $objectVO->getClave_instalacion(),
                    $objectVO->getCaracter_sat(),
                    $objectVO->getModalidad_permiso(),
                    $objectVO->getDescripcion()
            );

            if ($ps->execute()) {
                BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), 'ADM', 'MODIFICACION DE PARAMETROS DEL SISTEMA');
                $ps->close();
                return true;
            } else {
                error_log("Falló la ejecución: (" . $ps->errno . ") " . $ps->error);
            }
        }
        error_log($this->conn->error);
        return false;
    }

    public function remove($idObjectVO, $field = "id") {
        error_log($idObjectVO);
        error_log($field);
    }

}
