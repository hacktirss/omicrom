<?php

/**
 * Description of TurnoDAO
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
include_once ('TurnoVO.php');

class TurnoDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "tur";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \TurnoVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "isla,"
                . "turno,"
                . "descripcion,"
                . "horai,"
                . "horaf,"
                . "activo"
                . ") "
                . "VALUES(?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssss",
                    $objectVO->getIsla(),
                    $objectVO->getTurno(),
                    $objectVO->getDescripcion(),
                    $objectVO->getHorai(),
                    $objectVO->getHoraf(),
                    $objectVO->getActivo()
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
     * @return \TurnoVO
     */
    public function fillObject($rs) {
        $objectVO = new TurnoVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setIsla($rs["isla"]);
            $objectVO->setTurno($rs["turno"]);
            $objectVO->setDescripcion($rs["descripcion"]);
            $objectVO->setHorai($rs["horai"]);
            $objectVO->setHoraf($rs["horaf"]);
            $objectVO->setActivo($rs["activo"]);
            $objectVO->setCortea($rs["cortea"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \TurnoVO
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
     * @return \TurnoVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new TurnoVO();
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
     * @param \TurnoVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "isla = ?, "
                . "turno = ?, "
                . "descripcion = ?, "
                . "horai = ?, "
                . "horaf = ?, "
                . "activo = ? ,"
                . "cortea = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssssii",
                    $objectVO->getIsla(),
                    $objectVO->getTurno(),
                    $objectVO->getDescripcion(),
                    $objectVO->getHorai(),
                    $objectVO->getHoraf(),
                    $objectVO->getActivo(),
                    $objectVO->getCortea(),
                    $objectVO->getId()
            );
            if ($ps->execute()) {
                return true;
            } else {
                error_log($this->conn->error);
            }
        }
        return false;
    }

}
