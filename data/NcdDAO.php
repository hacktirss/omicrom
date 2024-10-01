<?php

/**
 * Description of NcdDAO
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
include_once ('NcdVO.php');

class NcdDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "ncd";

    private $conn;

    public function __construct() {
        $this->conn = getConnection();
    }

    public function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param NcdVO $objectVO
     * @return int
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "id,"
                . "producto,"
                . "cantidad,"
                . "precio,"
                . "iva,"
                . "ieps,"
                . "importe,"
                . "tipoc,"
                . "preciob,";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssss",
                    $objectVO->getId(),
                    $objectVO->getProducto(),
                    $objectVO->getCantidad(),
                    $objectVO->getPrecio(),
                    $objectVO->getIva(),
                    $objectVO->getIeps(),
                    $objectVO->getImporte(),
                    $objectVO->getTipoc(),
                    $objectVO->getPreciob()
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
     * @return \NcdVO
     */
    public function fillObject($rs) {
        $objectVO = new NcdVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setIdnvo($rs["idnvo"]);
            $objectVO->setProducto($rs["producto"]);
            $objectVO->setCantidad($rs["cantidad"]);
            $objectVO->setPrecio($rs["precio"]);
            $objectVO->setIva($rs["iva"]);
            $objectVO->setIeps($rs["ieps"]);
            $objectVO->setImporte($rs["importe"]);
            $objectVO->setTipoc($rs["tipoc"]);
            $objectVO->setPreciob($rs["preciob"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \NcdVO
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
     * @return \NcdVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new NcdVO();
        $sql = "SELECT " . self::TABLA . ".* FROM " . self::TABLA . " "
                . "WHERE " . self::TABLA . "." . $field . " = '" . $idObjectVO . "'";
        if (($query = $this->conn->query($sql)) && ($rs = $query->fetch_assoc())) {
            $objectVO = $this->fillObject($rs);
        }
        return $objectVO;
    }

    /**
     * 
     * @param NcdVO $ncVO
     * @return boolean
     * @throws Exception
     */
    public function update($objectVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "id = ?, "
                . "producto = ?, "
                . "cantidad = ?, "
                . "precio = ?, "
                . "iva = ?, "
                . "ieps = ?, "
                . "importe = ?, "
                . "tipoc = ?, "
                . "preciob = ? "
                . "WHERE idnvo = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssssi",
                    $objectVO->getId(),
                    $objectVO->getProducto(),
                    $objectVO->getCantidad(),
                    $objectVO->getPrecio(),
                    $objectVO->getIva(),
                    $objectVO->getIeps(),
                    $objectVO->getImporte(),
                    $objectVO->getTipoc(),
                    $objectVO->getPreciob(),
                    $objectVO->getIdnvo()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}
