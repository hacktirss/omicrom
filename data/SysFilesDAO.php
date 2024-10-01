<?php

/**
 * Description of SysFilesDAO
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
include_once ('SysFilesVO.php');

class SysFilesDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "sys_files";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \SysFilesVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "key_file,"
                . "file,"
                . "description,"
                . "format,"
                . "additional"
                . ") "
                . "VALUES(?, ?, ? , ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sbsss",
                    $objectVO->getKey_file(),
                    $objectVO->getFile(),
                    $objectVO->getDescription(),
                    $objectVO->getFormat(),
                    $objectVO->getAdditional()
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
     * @return \SysFilesVO
     */
    public function fillObject($rs) {
        $objectVO = new SysFilesVO();
        if (is_array($rs)) {
            $objectVO->setKey_file($rs["key_file"]);
            $objectVO->setFile($rs["file"]);
            $objectVO->setDescription($rs["description"]);
            $objectVO->setFormat($rs["format"]);
            $objectVO->setAdditional($rs["additional"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \SysFilesVO
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
     * @return \SysFilesVO
     */
    public function retrieve($idObjectVO, $field = "key_file") {
        $objectVO = new SysFilesVO();
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
     * @param \SysFilesVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "file = ?, "
                . "description = ?, "
                . "format = ?, "
                . "additional = ? "
                . "WHERE key_file = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("bssss",
                    $objectVO->getFile(),
                    $objectVO->getDescription(),
                    $objectVO->getFormat(),
                    $objectVO->getAdditional(),
                    $objectVO->getKey_file()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}
