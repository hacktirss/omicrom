<?php

/**
 * Description of CxdDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('CxdVO.php');

class CxdDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "cxd";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \CxdVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "vendedor, "
                . "referencia, "
                . "recibo, "
                . "corte, "
                . "fecha, "
                . "tm, "
                . "concepto, "
                . "importe "
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssssss",
                    $objectVO->getVendedor(),
                    $objectVO->getReferencia(),
                    $objectVO->getRecibo(),
                    $objectVO->getCorte(),
                    $objectVO->getFecha(),
                    $objectVO->getTm(),
                    $objectVO->getConcepto(),
                    $objectVO->getImporte()
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
     * @return \CxdVO
     */
    public function fillObject($rs) {
        $objectVO = new CxdVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setVendedor($rs["vendedor"]);
            $objectVO->setReferencia($rs["referencia"]);
            $objectVO->setRecibo($rs["recibo"]);
            $objectVO->setCorte($rs["corte"]);
            $objectVO->setFecha($rs["fecha"]);
            $objectVO->setTm($rs["tm"]);
            $objectVO->setConcepto($rs["concepto"]);
            $objectVO->setImporte($rs["importe"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \CxdVO
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
     * @return \CxdVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new CxdVO();
        $sql = "SELECT " . self::TABLA . ".* FROM " . self::TABLA . " "
                . "WHERE " . self::TABLA . "." . $field . " = '" . $idObjectVO . "'";
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
     * @param \CxdVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "vendedor = ?, "
                . "referencia = ?, "
                . "recibo = ?, "
                . "corte = ?, "
                . "fecha = ?, "
                . "tm = ?, "
                . "concepto = ?, "
                . "importe = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssssssi",
                    $objectVO->getVendedor(),
                    $objectVO->getReferencia(),
                    $objectVO->getRecibo(),
                    $objectVO->getCorte(),
                    $objectVO->getFecha(),
                    $objectVO->getTm(),
                    $objectVO->getConcepto(),
                    $objectVO->getImporte(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}

abstract class TipoMovCxd extends BasicEnum {

    const CARGO = "C";
    const ABONO = "H";

}
