<?php

/*
 * ClientesDAO
 * omicrom®
 * © 2017, Detisa 
 * http://www.detisa.com.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since jul 2017
 */

include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('BasicEnum.php');
include_once ('ClientesVO.php');
include_once ('V_CorporativoDAO.php');

class ClientesDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "cli";
    const GENERIC_RFC = "XAXX010101000";

    private $conn;
    private $v_corporativoDAO;
    private $encrypt = 0;

    function __construct() {
        $this->conn = getConnection();
        $this->v_corporativoDAO = new V_CorporativoDAO();
        $v_corporativoVO = $this->v_corporativoDAO->retrieve(V_CorporativoDAO::ENCRIPT_FIELD);
        $this->encrypt = $v_corporativoVO->getValor();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \ClientesVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO = ClientesVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "nombre,"
                . "direccion,"
                . "colonia,"
                . "municipio,"
                . "alias,"
                . "telefono,"
                . "activo,"
                . "contacto,"
                . "observaciones,"
                . "tipodepago,"
                . "limite,"
                . "rfc,"
                . "codigo,"
                . "correo,"
                . "numeroext,"
                . "numeroint,"
                . "enviarcorreo,"
                . "cuentaban,"
                . "estado,"
                . "formadepago,"
                . "correo2,"
                . "puntos,"
                . "desgloseIEPS,"
                . "ncc,"
                . "nombreFactura,"
                . "facturacion,"
                . "autorizaCorporativo,"
                . "regimenfiscal,"
                . "ultimaModificacion,"
                . "diasCredito,"
                . "tipoMonedero"
                . ") ";
        if ($this->encrypt == 1) {
            $sql .= "VALUES(" . ClientesVO::prepareEncryptFieds() . ")";
        } else {
            $sql .= "VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        }
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssssssssssssssssssssiiisii",
                    $objectVO->getNombre(),
                    $objectVO->getDireccion(),
                    $objectVO->getColonia(),
                    $objectVO->getMunicipio(),
                    $objectVO->getAlias(),
                    $objectVO->getTelefono(),
                    $objectVO->getActivo(),
                    $objectVO->getContacto(),
                    $objectVO->getObservaciones(),
                    $objectVO->getTipodepago(),
                    $objectVO->getLimite(),
                    $objectVO->getRfc(),
                    $objectVO->getCodigo(),
                    $objectVO->getCorreo(),
                    $objectVO->getNumeroext(),
                    $objectVO->getNumeroint(),
                    $objectVO->getEnviarcorreo(),
                    $objectVO->getCuentaban(),
                    $objectVO->getEstado(),
                    $objectVO->getFormadepago(),
                    $objectVO->getCorreo2(),
                    $objectVO->getPuntos(),
                    $objectVO->getDesgloseieps(),
                    $objectVO->getNcc(),
                    $objectVO->getNombrefactura(),
                    $objectVO->getFacturacion(),
                    $objectVO->getAutorizaCorporativo(),
                    $objectVO->getRegimenFiscal(),
                    date("Y-m-d H:i:s"),
                    $objectVO->getDiasCredito(),
                    $objectVO->getTipoMonedero()
            );
            if ($ps->execute()) {
                $id = $ps->insert_id;
                $ps->close();
                return $id;
            } else {
                error_log($this->conn->error);
            }
            $ps->close();
        } else {
            error_log($this->conn->error);
        }
        return $id;
    }

    /**
     * 
     * @param array() $rs
     * @return \ClientesVO
     */
    public function fillObject($rs) {
        $objectVO = new ClientesVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setNombre($rs["nombre"]);
            $objectVO->setDireccion($rs["direccion"]);
            $objectVO->setColonia($rs["colonia"]);
            $objectVO->setMunicipio($rs["municipio"]);
            $objectVO->setAlias($rs["alias"]);
            $objectVO->setTelefono($rs["telefono"]);
            $objectVO->setActivo($rs["activo"]);
            $objectVO->setContacto($rs["contacto"]);
            $objectVO->setObservaciones($rs["observaciones"]);
            $objectVO->setTipodepago($rs["tipodepago"]);
            $objectVO->setLimite($rs["limite"]);
            $objectVO->setRfc($rs["rfc"]);
            $objectVO->setCodigo($rs["codigo"]);
            $objectVO->setCorreo($rs["correo"]);
            $objectVO->setNumeroext($rs["numeroext"]);
            $objectVO->setNumeroint($rs["numeroint"]);
            $objectVO->setEnviarcorreo($rs["enviarcorreo"]);
            $objectVO->setCuentaban($rs["cuentaban"]);
            $objectVO->setEstado($rs["estado"]);
            $objectVO->setFormadepago($rs["formadepago"]);
            $objectVO->setCorreo2($rs["correo2"]);
            $objectVO->setPuntos($rs["puntos"]);
            $objectVO->setDesgloseieps($rs["desgloseIEPS"]);
            $objectVO->setNcc($rs["ncc"]);
            $objectVO->setNombrefactura($rs["nombreFactura"]);
            $objectVO->setFacturacion($rs["facturacion"]);
            $objectVO->setAutorizaCorporativo($rs["autorizaCorporativo"]);
            $objectVO->setRegimenFiscal($rs["regimenfiscal"]);
            $objectVO->setUltimaModificacion($rs["ultimaModificacion"]);
            $objectVO->setDiasCredito($rs["diasCredito"]);
            $objectVO->setTipoMonedero($rs["tipoMonedero"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \ClientesVO
     */
    public function getAll($sql) {
        $array = array();
        if (($query = $this->conn->query($sql))) {
            while (($rs = $query->fetch_assoc())) {
                $objectVO = $this->fillObject($rs);
                array_push($array, $objectVO);
            }
        } else {
            error_log($this->conn->error);
        }
        return $array;
    }

    /**
     * 
     * @param int $idObjectVO Llave primaria o identificador 
     * @param string $field Nombre del campo para borrar
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function remove($idObjectVO, $field = "id") {
        $sql = "DELETE FROM " . self::TABLA . " WHERE " . $field . " = ? LIMIT 1";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("s", $idObjectVO
            );
            return $ps->execute();
        }
    }

    /**
     * 
     * @param int $idObjectVO Llave primaria o identificador 
     * @param string $field Nombre del campo a buscar
     * @return \ClientesVO
     */
    public function retrieve($idObjectVO, $field = "id", $Add = "") {
        $objectVO = new ClientesVO();
        $sql = "SELECT * FROM " . self::TABLA . " WHERE " . $field . " = '" . $idObjectVO . "' $Add LIMIT 1";
        if ($this->encrypt == 1) {
            $sql = "SELECT " . ClientesVO::retrieveDeencryptFieds() . " FROM " . self::TABLA . " WHERE " . $field . " = '" . $idObjectVO . "' LIMIT 1";
        }
        //error_log($sql);
        if (($query = $this->conn->query($sql)) && ($rs = $query->fetch_assoc())) {
            $objectVO = $this->fillObject($rs);
        } else {
            error_log($this->conn->error);
        }
        return $objectVO;
    }

    /**
     * 
     * @param \ClientesVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = ClientesVO) {
        //error_log(print_r($objectVO, TRUE));
        $sql = "UPDATE " . self::TABLA . " SET "
                . "nombre = ?, "
                . "direccion = ?, "
                . "colonia = ?, "
                . "municipio = ?, "
                . "alias = ?, "
                . "telefono = ?, "
                . "activo = ?, "
                . "contacto = ?, "
                . "observaciones = ?, "
                . "tipodepago = ?, "
                . "limite = ?, "
                . "rfc = ?, "
                . "codigo = ?, "
                . "correo = ?, "
                . "numeroext = ?, "
                . "numeroint = ?, "
                . "enviarcorreo = ?, "
                . "cuentaban = ?, "
                . "estado = ?, "
                . "formadepago = ?, "
                . "correo2 = ?, "
                . "puntos = ?, "
                . "desgloseIEPS = ?, "
                . "ncc = ?, "
                . "nombreFactura = ?,"
                . "facturacion = ? , "
                . "autorizaCorporativo = ? ,"
                . "regimenfiscal = ?,"
                . "ultimaModificacion = ? ,"
                . "diasCredito = ? ,"
                . "tipoMonedero = ? "
                . "WHERE id = ? AND id > 0 LIMIT 1";

        if ($this->encrypt == 1) {
            $sql = "UPDATE " . self::TABLA . " SET  "
                    . ClientesVO::retrieveEncryptFieds()
                    . " WHERE id = ? AND id > 0 LIMIT 1";
        }

        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssssssssssssssssssssiissiii",
                    $objectVO->getNombre(),
                    $objectVO->getDireccion(),
                    $objectVO->getColonia(),
                    $objectVO->getMunicipio(),
                    $objectVO->getAlias(),
                    $objectVO->getTelefono(),
                    $objectVO->getActivo(),
                    $objectVO->getContacto(),
                    $objectVO->getObservaciones(),
                    $objectVO->getTipodepago(),
                    $objectVO->getLimite(),
                    $objectVO->getRfc(),
                    $objectVO->getCodigo(),
                    $objectVO->getCorreo(),
                    $objectVO->getNumeroext(),
                    $objectVO->getNumeroint(),
                    $objectVO->getEnviarcorreo(),
                    $objectVO->getCuentaban(),
                    $objectVO->getEstado(),
                    $objectVO->getFormadepago(),
                    $objectVO->getCorreo2(),
                    $objectVO->getPuntos(),
                    $objectVO->getDesgloseieps(),
                    $objectVO->getNcc(),
                    $objectVO->getNombrefactura(),
                    $objectVO->getFacturacion(),
                    $objectVO->getAutorizaCorporativo(),
                    $objectVO->getRegimenFiscal(),
                    $objectVO->getUlitmaModificacion(),
                    $objectVO->getDiasCredito(),
                    $objectVO->getTipoMonedero(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

    /**
     * @param \ClientesVO $objectVO
     */
    public function update2($objectVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "nombre = UPPER(?), "
                . "rfc = UPPER(?), "
                . "correo = ?, "
                . "enviarcorreo = ?, "
                . "desgloseIEPS = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssss",
                    $objectVO->getNombre(),
                    $objectVO->getRfc(),
                    $objectVO->getCorreo(),
                    $objectVO->getEnviarcorreo(),
                    $objectVO->getDesgloseIEPS(),
                    $objectVO->getId());
            return $ps->execute();
        }
    }

    public static function getClientData($id, $tabla) {
        $receptor = new ClientesVO();
        $conn = \com\softcoatl\utils\IConnection::getConnection();
        $sql = "SELECT 
                    trim(REGEXP_REPLACE( UPPER( cli.nombre ), 
                    '([, ]{1,4})?[S][.]?[A][.]?[ ]{1,3}[DE]{2}[ ]{1,3}([C][.]?[V][.]?|[R][.]?[L][.]?)$', '' )) Nombre, 
                    cli.rfc Rfc, $tabla.usocfdi UsoCFDI ,cli.regimenfiscal,cli.codigo
                FROM $tabla JOIN cli ON $tabla.cliente = cli.id WHERE $tabla.id = " . $id;

        if (($query = $conn->query($sql)) && ($rs = $query->fetch_assoc())) {
            $receptor->setNombre($rs["Nombre"]);
            $receptor->setRegimenFiscal($rs["regimenfiscal"]);
            $receptor->setCodigo($rs["codigo"]);
            $receptor->setRfc($rs["Rfc"]);
            $receptor->setObservaciones("CP01");
        }
        return $receptor;
    }

    public static function getEmisor() {
        $emisor = new ClientesVO();
        $conn = \com\softcoatl\utils\IConnection::getConnection();
        $sql = "SELECT REGEXP_REPLACE(upper(cia), '([, ]{1,4})?[S][.]?[A][.]?[ ]{1,3}[DE]{2}[ ]{1,3}([C][.]?[V][.]?|[R][.]?[L][.]?)$','') Nombre,"
                . " rfc Rfc, clave_regimen RegimenFiscal FROM cia";
//        $sql = "SELECT upper(cia) Nombre,"
//                . " rfc Rfc, clave_regimen RegimenFiscal FROM cia";

        if (($query = $conn->query($sql)) && ($rs = $query->fetch_assoc())) {
            $emisor->setNombre($rs["Nombre"]);
            $emisor->setRegimenFiscal($rs["RegimenFiscal"]);
            $emisor->setRfc($rs["Rfc"]);
        }
        return $emisor;
    }

}

abstract class TiposCliente extends BasicEnum {

    const CONTADO = "Contado";
    const CREDITO = "Credito";
    const PREPAGO = "Prepago";
    const TARJETA = "Tarjeta";
    const VALES = "Vales";
    const PUNTOS = "Puntos";
    const CONSIGNACION = "Consignacion";
    const EFECTIVALE = "Efectivale";
    const MONEDERO = "Monedero";
    const REEMBOLSO = "Reembolso";
    const AUTOCONSUMO = "AutoConsumo";
    const CORTESIA = "Cortesía";

}

abstract class StatusCliente extends BasicEnum {

    const ACTIVO = "Si";
    const INACTIVO = "No";

}

abstract class tipoFacturaMonedero extends BasicEnum {

    const NORMAL = 0;
    const MONTONETO = 1;
    const EFECTIVALE = 2;

}
