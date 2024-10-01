<?php

/**
 * Description of MedDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('MedVO.php');

class MedTmpDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "med_tmp";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \MedVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO = MedVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "id,"
                . "clave,"
                . "cantidad,"
                . "precio"
                . ") "
                . "VALUES(?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssss",
                    $objectVO->getId(),
                    $objectVO->getClave(),
                    $objectVO->getCantidad(),
                    $objectVO->getPrecio()
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
     * @return \MedVO
     */
    public function fillObject($rs) {
        $objectVO = new MedVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setIdnvo($rs["idnvo"]);
            $objectVO->setClave($rs["clave"]);
            $objectVO->setCantidad($rs["cantidad"]);
            $objectVO->setPrecio($rs["precio"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \MedVO
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
        $sql = "DELETE FROM " . self::TABLA . " WHERE " . $field . " = ?";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("s", $idObjectVO
            );
            return $ps->execute();
        }
    }
    
    /**
     * 
     * @param int $idUsuario Id del usuario que realiza la captura
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function removeByUsuario($idUsuario) {
        $sql = "DELETE med_tmp FROM med_tmp, me_tmp WHERE med_tmp.id = me_tmp.id AND me_tmp.usuario = ?";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("i", $idUsuario
            );
            return $ps->execute();
        }
    }

    /**
     * 
     * @param int $idObjectVO Llave primaria o identificador 
     * @param string $field Nombre del campo a buscar
     * @return \MedVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new MedVO();
        $sql = "SELECT * FROM " . self::TABLA . " WHERE " . $field . " = '" . $idObjectVO . "'";
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
     * @param \MedVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = MedVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "id = ?, "
                . "clave = ?, "
                . "cantidad = ?, "
                . "precio = ?, "
                . "WHERE idnvo = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssi",
                    $objectVO->getId(),
                    $objectVO->getClave(),
                    $objectVO->getCantidad(),
                    $objectVO->getPrecio(),
                    $objectVO->getIdnvo()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}
