<?php

/**
 * Description of Env_efectivoDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Alejandro Ayala Gonzalez
 * @version 1.0
 * @since ene 2023
 */
include_once ('mysqlUtils.php');
include_once ('Env_efectivoVO.php');
include_once ('FunctionsDAO.php');

class Env_efectivoDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "env_efectivo";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function _destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \Env_efectivoVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "id_banco,"
                . "descripcion,"
                . "importe,"
                . "fecha_envio,"
                . "status"
                . ") "
                . "VALUES(?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("issss",
                    $objectVO->getId_banco(),
                    $objectVO->getDescripcion(),
                    $objectVO->getImporte(),
                    $objectVO->getFecha_envio(),
                    $objectVO->getStatus()
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
     * @return \Env_efectivoVO
     */
    public function fillObject($rs) {
        $objectVO = new Env_efectivoVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setId_banco($rs["id_banco"]);
            $objectVO->setDescripcion($rs["descripcion"]);
            $objectVO->setImporte($rs["importe"]);
            $objectVO->setFecha_envio($rs["fecha_envio"]);
            $objectVO->setFecha_creacion($rs["fecha_creacion"]);
            $objectVO->setStatus($rs["status"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param int $idObjectVO Llave primaria o identificador 
     * @param string $field Nombre del campo a buscar
     * @return \Env_efectivoVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new Env_efectivoVO();
        $sql = "SELECT * FROM " . self::TABLA . " WHERE " . $field . " = " . $idObjectVO;
        //error_log($sql);
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
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \Env_efectivoVO
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
     * @param \Env_efectivoVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = Env_efectivoVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "id_banco = ?, "
                . "descripcion = ?, "
                . "importe = ?, "
                . "fecha_envio = ?, "
                . "fecha_creacion = ?, "
                . "status = ? "
                . "WHERE id = ?";
        //error_log($sql);
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("isssssi",
                    $objectVO->getId_banco(),
                    $objectVO->getDescripcion(),
                    $objectVO->getImporte(),
                    $objectVO->getFecha_envio(),
                    $objectVO->getFecha_creacion(),
                    $objectVO->getStatus(),
                    $objectVO->getId()
            );
            error_log(print_r($objectVO, true));
            if ($ps->execute()) {
                return true;
            }
        }
        error_log($this->conn->error);
        return false;
    }

}
