<?php

/**
 * Description of VendedorDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('VendedorVO.php');

class VendedorDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "ven";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \VendedorVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO = VendedorVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "nombre,"
                . "direccion,"
                . "colonia,"
                . "municipio,"
                . "alias,"
                . "telefono,"
                . "activo,"
                . "nip,"
                . "ncc,"
                . "num_empleado"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssssssss",
                    $objectVO->getNombre(),
                    $objectVO->getDireccion(),
                    $objectVO->getColonia(),
                    $objectVO->getMunicipio(),
                    $objectVO->getAlias(),
                    $objectVO->getTelefono(),
                    $objectVO->getActivo(),
                    $objectVO->getNip(),
                    $objectVO->getNcc(),
                    $objectVO->getNum_empleado()
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
     * @return \VendedorVO
     */
    public function fillObject($rs) {
        $objectVO = new VendedorVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setNombre($rs["nombre"]);
            $objectVO->setDireccion($rs["direccion"]);
            $objectVO->setColonia($rs["colonia"]);
            $objectVO->setMunicipio($rs["municipio"]);
            $objectVO->setAlias($rs["alias"]);
            $objectVO->setTelefono($rs["telefono"]);
            $objectVO->setActivo($rs["activo"]);
            $objectVO->setNip($rs["nip"]);
            $objectVO->setNcc($rs["ncc"]);
            $objectVO->setNum_empleado($rs["num_empleado"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \VendedorVO
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
     * @return \VendedorVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new VendedorVO();
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
     * @param \VendedorVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = VendedorVO) {
        //$objectVO = new VendedorVO();
        $sql = "UPDATE " . self::TABLA . " SET "
                . "nombre = ?, "
                . "direccion = ?, "
                . "colonia = ?, "
                . "municipio = ?, "
                . "alias = ?, "
                . "telefono = ?, "
                . "activo = ?, "
                . "nip = ?, "
                . "ncc = ? ,"
                . "num_empleado = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssssssssi",
                    $objectVO->getNombre(),
                    $objectVO->getDireccion(),
                    $objectVO->getColonia(),
                    $objectVO->getMunicipio(),
                    $objectVO->getAlias(),
                    $objectVO->getTelefono(),
                    $objectVO->getActivo(),
                    $objectVO->getNip(),
                    $objectVO->getNcc(),
                    $objectVO->getNum_empleado(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}
