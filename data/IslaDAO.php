<?php

/**
 * Description of IslaDAO
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
include_once ('IslaVO.php');

class IslaDAO implements FunctionsDAO{

    const RESPONSE_VALID = "OK";
    const TABLA = "islas";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \IslaVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO = IslaVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "isla,"
                . "descripcion,"
                . "turno,"
                . "activo,"
                . "status,"
                . "corte"
                . ") "
                . "VALUES(?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("isissi",
                    $objectVO->getIsla(),
                    $objectVO->getDescripcion(),
                    $objectVO->getTurno(),
                    $objectVO->getActivo(),
                    $objectVO->getStatus(),
                    $objectVO->getCorte()
            );
            if ($ps->execute()) {
                $id = $ps->insert_id;
                $ps->close();
                return $id;
            }else{
                error_log($this->conn->error);
            }
            $ps->close();
        }else{
            error_log($this->conn->error);
        }
        return $id;
    }

    /**
     * 
     * @param array() $rs
     * @return \IslaVO
     */
    public function fillObject($rs) {
        $objectVO = new IslaVO();
        if (is_array($rs)) {
            $objectVO->setIsla($rs["isla"]);
            $objectVO->setDescripcion($rs["descripcion"]);
            $objectVO->setTurno($rs["turno"]);
            $objectVO->setActivo($rs["activo"]);
            $objectVO->setStatus($rs["status"]);
            $objectVO->setCorte($rs["corte"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \IslaVO
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
     * @return \IslaVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new IslaVO();
        $sql = "SELECT * FROM " . self::TABLA . " WHERE " . $field . " = '" . $idObjectVO . "'";
        if (($query = $this->conn->query($sql)) && ($rs = $query->fetch_assoc())) {
            $objectVO = $this->fillObject($rs);
            return $objectVO;
        }else{
            error_log($this->conn->error);
        }
        return $objectVO;
    }

    /**
     * 
     * @param \IslaVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = IslaVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "descripcion = ?, "
                . "turno = ?, "
                . "activo = ?, "
                . "status = ?, "
                . "corte = ? "
                . "WHERE isla = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sissii",
                    $objectVO->getDescripcion(),
                    $objectVO->getTurno(),
                    $objectVO->getActivo(),
                    $objectVO->getStatus(),
                    $objectVO->getCorte(),
                    $objectVO->getIsla()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}

abstract class StatusIsla extends BasicEnum {
    const ABIERTO = "Abierta";
    const CERRADO = "Cerrada";
}