<?php

/**
 * Description of ComisionesDAO
 * omicrom®
 * © 2022, Detisa 
 * http://www.detisa.com.mx
 * @author Alejandro Ayala Gonzalez
 * @version 1.0
 * @since mar 2022
 */
include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('BasicEnum.php');
include_once ('ComisionesVO.php');

class ComisionesDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "comisiones";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \ComisionesVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO = ComisionesVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "id_prv,"
                . "id_com,"
                . "vigencia,"
                . "monto,"
                . "vigenciafin"
                . ") "
                . "VALUES(?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssss",
                    $objectVO->getId_prv(),
                    $objectVO->getId_com(),
                    $objectVO->getVigencia(),
                    $objectVO->getMonto(),
                    $objectVO->getVigenciafin()
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
     * @return \ComisionesVO
     */
    public function fillObject($rs) {
        $objectVO = new ComisionesVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setId_prv($rs["id_prv"]);
            $objectVO->setId_com($rs["id_com"]);
            $objectVO->setMonto($rs["monto"]);
            $objectVO->setVigencia($rs["vigencia"]);
            $objectVO->setVigenciafin($rs["vigenciafin"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \ComisionesVO
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
     * @return \ComisionesVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new ComisionesVO();
        $sql = "SELECT * FROM " . self::TABLA . " "
                . "WHERE  " . $field . " = '" . $idObjectVO . "'";
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
     * @param \ComisionesVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = CargasVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "id_prv = ?, "
                . "id_com = ?, "
                . "vigencia = ?, "
                . "monto = ?, "
                . "vigenciafin = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssi",
                    $objectVO->getId_prv(),
                    $objectVO->getId_com(),
                    $objectVO->getVigencia(),
                    $objectVO->getMonto(),
                    $objectVO->getVigenciafin(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}