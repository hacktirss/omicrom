<?php

/**
 * Description of EnvioPromodDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Alejandro Ayala Gonzalez
 * @version 1.0
 * @since oct 2023
 */
include_once ('mysqlUtils.php');
include_once ('EnvioPromodVO.php');
include_once ('FunctionsDAO.php');

class EnvioPromodDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "envioPromod";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function _destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \EnvioPromodVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "id,"
                . "id_authuser,"
                . "codigo"
                . ") "
                . "VALUES(?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("iis",
                    $objectVO->getId(),
                    $objectVO->getId_authuser(),
                    rand(1000000000, 9999999999)
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
     * @return \EnvioPromodVO
     */
    public function fillObject($rs) {
        $objectVO = new EnvioPromodVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setIdNvo($rs["idNvo"]);
            $objectVO->setId_authuser($rs["id_authuser"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param int $idObjectVO Llave primaria o identificador 
     * @param string $field Nombre del campo a buscar
     * @return \EnvioPromodVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new EnvioPromodVO();
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
     * @return array Arreglo de objetos \EnvioPromodVO
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
     * @param \EnvioPromodVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = Env_efectivoVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "id = ?, "
                . "id_authuser = ? "
                . "WHERE idNvo = ?";
        //error_log($sql);
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("iii",
                    $objectVO->getId(),
                    $objectVO->getId_authuser(),
                    $objectVO->getIdNvo()
            );
            if ($ps->execute()) {
                return true;
            }
        }
        error_log($this->conn->error);
        return false;
    }

}
