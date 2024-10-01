<?php

/**
 * Description of TransferenciaDAO
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
include_once ('TransferenciaVO.php');

class TransferenciaDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "transf";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \TransferenciaVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "fecha, "
                . "corte, "
                . "isla_pos, "
                . "producto, "
                . "cantidad, "
                . "posicion, "
                . "tarea "
                . ") "
                . "VALUES(NOW(),?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("iiiiis",
                    $objectVO->getCorte(),
                    $objectVO->getIsla_pos(),
                    $objectVO->getProducto(),
                    $objectVO->getCantidad(),
                    $objectVO->getPosicion(),
                    $objectVO->getTarea()
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
     * @return \TransferenciaVO
     */
    public function fillObject($rs) {
        $objectVO = new TransferenciaVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setFecha($rs["fecha"]);
            $objectVO->setCorte($rs["corte"]);
            $objectVO->setIsla_pos($rs["isla_pos"]);
            $objectVO->setProducto($rs["producto"]);
            $objectVO->setCantidad($rs["cantidad"]);
            $objectVO->setPosicion($rs["posicion"]);
            $objectVO->setTarea($rs["tarea"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \TransferenciaVO
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
     * @return \TransferenciaVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new TransferenciaVO();
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
     * @param \TransferenciaVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "corte = ?, "
                . "isla_pos = ?, "
                . "producto = ?, "
                . "cantidad = ?, "
                . "posicion = ?, "
                . "tarea = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("iiiiisi",
                    $objectVO->getCorte(),
                    $objectVO->getIsla_pos(),
                    $objectVO->getProducto(),
                    $objectVO->getCantidad(),
                    $objectVO->getPosicion(),
                    $objectVO->getTarea(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}
