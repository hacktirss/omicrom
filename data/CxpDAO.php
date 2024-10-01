<?php

/**
 * Description of CxpDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('CxpVO.php');

class CxpDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "cxp";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \CxpVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "proveedor,"
                . "referencia,"
                . "fecha,"
                . "fechav,"
                . "tm,"
                . "concepto,"
                . "cantidad,"
                . "importe,"
                . "numpago"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssss",
                    $objectVO->getProveedor(),
                    $objectVO->getReferencia(),
                    $objectVO->getFecha(),
                    $objectVO->getFechav(),
                    $objectVO->getTm(),
                    $objectVO->getConcepto(),
                    $objectVO->getCantidad(),
                    $objectVO->getImporte(),
                    $objectVO->getNumpago()
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
     * @return \CxpVO
     */
    public function fillObject($rs) {
        $objectVO = new CxpVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setProveedor($rs["proveedor"]);
            $objectVO->setReferencia($rs["referencia"]);
            $objectVO->setFecha($rs["fecha"]);
            $objectVO->setFechav($rs["fechav"]);
            $objectVO->setTm($rs["tm"]);
            $objectVO->setConcepto($rs["concepto"]);
            $objectVO->setCantidad($rs["cantidad"]);
            $objectVO->setImporte($rs["importe"]);
            $objectVO->setNumpago($rs["numpago"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \CxpVO
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
     * @return \CxpVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new CxpVO();
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
     * @param \CxpVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "proveedor = ?, "
                . "referencia = ?, "
                . "fecha = ?, "
                . "fechav = ?, "
                . "tm = ?, "
                . "concepto = ?, "
                . "cantidad = ?, "
                . "importe = ?, "
                . "numpago = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssssi",
                    $objectVO->getProveedor(),
                    $objectVO->getReferencia(),
                    $objectVO->getFecha(),
                    $objectVO->getFechav(),
                    $objectVO->getTm(),
                    $objectVO->getConcepto(),
                    $objectVO->getCantidad(),
                    $objectVO->getImporte(),
                    $objectVO->getNumpago(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}
