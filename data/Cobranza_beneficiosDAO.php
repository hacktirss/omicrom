<?php

/**
 * Description of RmDAO
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
include_once ('Cobranza_beneficiosVO.php');

class Cobranza_beneficiosDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "cobranza_beneficios";

    private $conn;

    public function __construct() {
        $this->conn = getConnection();
    }

    public function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \Cobranza_beneficiosVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO = Cobranza_beneficiosVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "id_beneficio,"
                . "puntos,"
                . "fecha,"
                . "id_ticket_beneficio,"
                . "tm"
                . ") "
                . "VALUES( ?, ?, NOW(), ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("iiis",
                    $objectVO->getId_beneficio(),
                    $objectVO->getPuntos(),
                    $objectVO->getId_ticket_beneficio(),
                    $objectVO->getTm()
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
     * @return \Cobranza_beneficiosVO
     */
    public function fillObject($rs) {
        $objectVO = new Cobranza_beneficiosVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setId_beneficio($rs["id_beneficio"]);
            $objectVO->setPuntos($rs["puntos"]);
            $objectVO->setFecha($rs["fecha"]);
            $objectVO->setId_ticket_beneficio($rs["id_ticket_beneficio"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \Cobranza_beneficiosVO
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
     * @return \Cobranza_beneficiosVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new Cobranza_beneficiosVO();
        $sql = "SELECT *  FROM " . self::TABLA . "  
                WHERE " . self::TABLA . "." . $field . " = '" . $idObjectVO . "'";
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
     * @param \Cobranza_beneficiosVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = Cobranza_beneficiosVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "id_beneficio = ?, "
                . "puntos = ?, "
                . "fecha = ?, "
                . "id_ticket_beneficio = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("iisii",
                    $objectVO->getId_beneficio(),
                    $objectVO->getPuntos(),
                    $objectVO->getFecha(),
                    $objectVO->getId_ticket_beneficio(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}
