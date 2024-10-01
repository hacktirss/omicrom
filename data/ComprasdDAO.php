<?php

/**
 * Description of ComprasdDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('ComprasdVO.php');

class ComprasdDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "etd";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \ComprasdVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "id,"
                . "producto,"
                . "cantidad,"
                . "costo,"
                . "descuento,"
                . "adicional"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssss",
                    $objectVO->getId(),
                    $objectVO->getProducto(),
                    $objectVO->getCantidad(),
                    $objectVO->getCosto(),
                    $objectVO->getDescuento(),
                    $objectVO->getAdicional()
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
     * @return \ComprasdVO
     */
    public function fillObject($rs) {
        $objectVO = new ComprasdVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setIdnvo($rs["idnvo"]);
            $objectVO->setProducto($rs["producto"]);
            $objectVO->setCantidad($rs["cantidad"]);
            $objectVO->setCosto($rs["costo"]);
            $objectVO->setDescuento($rs["descuento"]);
            $objectVO->setAdicional($rs["adicional"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \ComprasdVO
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
     * @return \ComprasdVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new ComprasdVO();
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
     * @param \ComprasdVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "id = ?, "
                . "producto = ?, "
                . "cantidad = ?, "
                . "costo = ?, "
                . "descuento = ?, "
                . "adicional = ? "
                . "WHERE idnvo = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssssi",
                    $objectVO->getId(),
                    $objectVO->getProducto(),
                    $objectVO->getCantidad(),
                    $objectVO->getCosto(),
                    $objectVO->getDescuento(),
                    $objectVO->getAdicional(),
                    $objectVO->getIdnvo()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}
