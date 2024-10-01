<?php

/**
 * Description of CambioPreciosDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('CancelacionVO.php');

class CancelacionDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "cancelacion";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \CancelacionVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "tabla,"
                . "id_origen,"
                . "descripcion_evento"
                . ") "
                . "VALUES(?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sis",
                    $objectVO->getTabla(),
                    $objectVO->getId_origen(),
                    $objectVO->getDescripcion_evento()
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
     * @return \CancelacionVO
     */
    public function fillObject($rs) {
        $objectVO = new CancelacionVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setTabla($rs["tabla"]);
            $objectVO->setId_origen($rs["id_origen"]);
            $objectVO->setDescripcion_evento($rs["descripcion_evento"]);
            $objectVO->setFecha_registro($rs["fecha_registro"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \CancelacionVO
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

    public function remove($idObjectVO, $field = "id") {
        
    }

    /**
     * 
     * @param int $idObjectVO Llave primaria o identificador 
     * @param string $field Nombre del campo a buscar
     * @return \CancelacionVO
     */
    public function retrieve($idObjectVO, $field = "fc") {
        $objectVO = new CancelacionVO();
        $sql = "SELECT * FROM " . self::TABLA . " WHERE tabla = '$field' AND id_origen = '" . $idObjectVO . "'";
        if (($query = $this->conn->query($sql)) && ($rs = $query->fetch_assoc())) {
            $objectVO = $this->fillObject($rs);
            return $objectVO;
        } else {
            error_log($this->conn->error);
        }
        return $objectVO;
    }

    public function update($objectVO) {
        
    }

}
