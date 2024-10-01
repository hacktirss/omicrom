<?php

/**
 * Description of genbolDAO
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
include_once ('GenbolVO.php');

class GenbolDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "genbol";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \GenbolVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO = GenbolVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "fecha,"
                . "cliente,"
                . "fechav,"
                . "cantidad,"
                . "importe,"
                . "status,"
                . "recibe"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sisidss",
                    $objectVO->getFecha(),
                    $objectVO->getCliente(),
                    $objectVO->getFechav(),
                    $objectVO->getCantidad(),
                    $objectVO->getImporte(),
                    $objectVO->getStatus(),
                    $objectVO->getRecibe()
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
     * @return \GenbolVO
     */
    public function fillObject($rs) {
        $objectVO = new GenbolVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setFecha($rs["fecha"]);
            $objectVO->setCliente($rs["cliente"]);
            $objectVO->setFechav($rs["fechav"]);
            $objectVO->setCantidad($rs["cantidad"]);
            $objectVO->setImporte($rs["importe"]);
            $objectVO->setStatus($rs["status"]);
            $objectVO->setRecibe($rs["recibe"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \GenbolVO
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
     * @return \GenbolVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new GenbolVO();
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
     * @param \GenbolVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = GenbolVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "fecha = ?, "
                . "cliente = ?, "
                . "fechav = ?, "
                . "cantidad = ?, "
                . "importe = ?, "
                . "status = ?, "
                . "recibe = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sisidssi",
                    $objectVO->getFecha(),
                    $objectVO->getCliente(),
                    $objectVO->getFechav(),
                    $objectVO->getCantidad(),
                    $objectVO->getImporte(),
                    $objectVO->getStatus(),
                    $objectVO->getRecibe(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}

abstract class StatusVales extends BasicEnum {
    const ABIERTO = "Abierta";
    const CERRADO = "Cerrada";
    const CANCELADO = "Cancelada";
}