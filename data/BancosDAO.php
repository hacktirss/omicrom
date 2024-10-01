<?php

/**
 * Description of BancosDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
include_once ('mysqlUtils.php');
include_once ('BancosVO.php');
include_once ('FunctionsDAO.php');

class BancosDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "bancos";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function _destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \BancosVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "rubro,"
                . "banco,"
                . "cuenta,"
                . "concepto,"
                . "ncc,"
                . "tipo_moneda,"
                . "tipo_cambio,"
                . "activo"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("issssssi",
                    $objectVO->getRubro(),
                    $objectVO->getBanco(),
                    $objectVO->getCuenta(),
                    $objectVO->getConcepto(),
                    $objectVO->getNcc(),
                    $objectVO->getTipo_moneda(),
                    $objectVO->getTipo_cambio(),
                    $objectVO->getActivo()
            );
            if ($ps->execute()) {
                $id = $ps->insert_id;
                $ps->close();
                $this->changeDivisa($objectVO);
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
     * @return \BancosVO
     */
    public function fillObject($rs) {
        $objectVO = new BancosVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setRubro($rs["rubro"]);
            $objectVO->setBanco($rs["banco"]);
            $objectVO->setCuenta($rs["cuenta"]);
            $objectVO->setConcepto($rs["concepto"]);
            $objectVO->setNcc($rs["ncc"]);
            $objectVO->setTipo_moneda($rs["tipo_moneda"]);
            $objectVO->setTipo_cambio($rs["tipo_cambio"]);
            $objectVO->setActivo($rs["activo"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param int $idObjectVO Llave primaria o identificador 
     * @param string $field Nombre del campo a buscar
     * @return \BancosVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new BancosVO();
        $sql = "SELECT * FROM " . self::TABLA . " WHERE " . $field . " = " . $idObjectVO;
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
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \BancosVO
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
     * @param \BancosVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = BancosVO) {
        //$objectVO = new BancosVO();
        $sql = "UPDATE " . self::TABLA . " SET "
                . "rubro = ?, "
                . "banco = ?, "
                . "cuenta = ?, "
                . "concepto = ?, "
                . "ncc = ?, "
                . "tipo_moneda = ?, "
                . "tipo_cambio = ?, "
                . "activo = ? "
                . "WHERE id = ?";
        //error_log($sql);
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssii",
                    $objectVO->getRubro(),
                    $objectVO->getBanco(),
                    $objectVO->getCuenta(),
                    $objectVO->getConcepto(),
                    $objectVO->getNcc(),
                    $objectVO->getTipo_moneda(),
                    $objectVO->getTipo_cambio(),
                    $objectVO->getActivo(),
                    $objectVO->getId()
            );
            if ($ps->execute()) {
                $this->changeDivisa($objectVO);
                return true;
            }
        }
        error_log($this->conn->error);
        return false;
    }

    /**
     * 
     * @param BancosVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function changeDivisa($objectVO) {
        $sql = "INSERT INTO divisas(clave, tipo_de_cambio) VALUES(?,?) ON DUPLICATE KEY UPDATE tipo_de_cambio = VALUES(tipo_de_cambio)";
        $tipo = ($objectVO->getTipo_moneda() == 1 ? "MXN" : "USD");
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sd", $tipo, $objectVO->getTipo_cambio());
            if ($ps->execute()) {
                return true;
            }
        }
        error_log($this->conn->error);
        return false;
    }

}

abstract class RubroBanco extends BasicEnum {

    const EGRESOS = 0;
    const VENDEDORES = 1;
    const OTROS = 2;

}

abstract class StatusBanco extends BasicEnum {

    const ACTIVO = 1;
    const INACTIVO = 0;

}
