<?php

/**
 * Description of ProveedorDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('BasicEnum.php');
include_once ('ProveedorVO.php');

class ProveedorDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "prv";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \ProveedorVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO = ProveedorVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "nombre,"
                . "direccion,"
                . "colonia,"
                . "municipio,"
                . "alias,"
                . "telefono,"
                . "contacto,"
                . "observaciones,"
                . "tipodepago,"
                . "limite,"
                . "codigo,"
                . "rfc,"
                . "correo,"
                . "numeroint,"
                . "numeroext,"
                . "cuentaban,"
                . "ncc,"
                . "dias_credito,"
                . "proveedorde,"
                . "clabe,"
                . "cuenta,"
                . "banco,"
                . "permisoCRE,"
                . "tipoProveedor"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssssssssssssssssssssss",
                    $objectVO->getNombre(),
                    $objectVO->getDireccion(),
                    $objectVO->getColonia(),
                    $objectVO->getMunicipio(),
                    $objectVO->getAlias(),
                    $objectVO->getTelefono(),
                    $objectVO->getContacto(),
                    $objectVO->getObservaciones(),
                    $objectVO->getTipodepago(),
                    $objectVO->getLimite(),
                    $objectVO->getCodigo(),
                    $objectVO->getRfc(),
                    $objectVO->getCorreo(),
                    $objectVO->getNumeroint(),
                    $objectVO->getNumeroext(),
                    $objectVO->getCuentaban(),
                    $objectVO->getNcc(),
                    $objectVO->getDias_credito(),
                    $objectVO->getProveedorde(),
                    $objectVO->getClabe(),
                    $objectVO->getCuenta(),
                    $objectVO->getBanco(),
                    $objectVO->getPermisocre(),
                    $objectVO->getTipoProveedor()
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
     * @return \ProveedorVO
     */
    public function fillObject($rs) {
        $objectVO = new ProveedorVO();
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
            $objectVO->setCodigo($rs["codigo"]);
            $objectVO->setRfc($rs["rfc"]);
            $objectVO->setCorreo($rs["correo"]);
            $objectVO->setNumeroint($rs["numeroint"]);
            $objectVO->setNumeroext($rs["numeroext"]);
            $objectVO->setEnviarcorreo($rs["enviarcorreo"]);
            $objectVO->setCuentaban($rs["cuentaban"]);
            $objectVO->setNcc($rs["ncc"]);
            $objectVO->setDias_credito($rs["dias_credito"]);
            $objectVO->setProveedorde($rs["proveedorde"]);
            $objectVO->setDias_cre($rs["dias_cre"]);
            $objectVO->setClabe($rs["clabe"]);
            $objectVO->setCuenta($rs["cuenta"]);
            $objectVO->setBanco($rs["banco"]);
            $objectVO->setTipoproveedor($rs["tipoProveedor"]);
            $objectVO->setPermisocre($rs["permisoCRE"]);
            $objectVO->setTipo($rs["tipo"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \ProveedorVO
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
     * @return \ProveedorVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new ProveedorVO();
        $sql = "SELECT * FROM " . self::TABLA . " WHERE " . $field . " = '" . $idObjectVO . "'";
        if (($query = $this->conn->query($sql)) && ($rs = $query->fetch_assoc())) {
            $objectVO = $this->fillObject($rs);
            return $objectVO;
        } else {
            error_log($this->conn->error);
        }
        return $objectVO;
    }

    /**
     * 
     * @param \ProveedorVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = ProveedorVO) {
        //$objectVO = new ProveedorVO();
        $sql = "UPDATE " . self::TABLA . " SET "
                . "nombre = ?, "
                . "direccion = ?, "
                . "colonia = ?, "
                . "municipio = ?, "
                . "alias = ?, "
                . "telefono = ?, "
                . "contacto = ?, "
                . "tipodepago = ?, "
                . "limite = ?, "
                . "codigo = ?, "
                . "rfc = ?, "
                . "correo = ?, "
                . "numeroint = ?, "
                . "numeroext = ?, "
                . "ncc = ?, "
                . "dias_credito = ?, "
                . "proveedorde = ?, "
                . "clabe = ?, "
                . "cuenta = ?, "
                . "banco = ?, "
                . "permisoCRE = ?,"
                . "tipoProveedor = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssssssssssssssssssssi",
                    $objectVO->getNombre(),
                    $objectVO->getDireccion(),
                    $objectVO->getColonia(),
                    $objectVO->getMunicipio(),
                    $objectVO->getAlias(),
                    $objectVO->getTelefono(),
                    $objectVO->getContacto(),
                    $objectVO->getTipodepago(),
                    $objectVO->getLimite(),
                    $objectVO->getCodigo(),
                    $objectVO->getRfc(),
                    $objectVO->getCorreo(),
                    $objectVO->getNumeroint(),
                    $objectVO->getNumeroext(),
                    $objectVO->getNcc(),
                    $objectVO->getDias_credito(),
                    $objectVO->getProveedorde(),
                    $objectVO->getClabe(),
                    $objectVO->getCuenta(),
                    $objectVO->getBanco(),
                    $objectVO->getPermisocre(),
                    $objectVO->getTipoProveedor(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}

abstract class TipoProveedor extends BasicEnum {

    const COMBUSTIBLES = "Combustibles";
    const ACEITES = "Aceites";
    const OTROS = "Otros";

}
