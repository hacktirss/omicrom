<?php

/**
 * Description of PagosDespDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('PagosDespVO.php');
include_once ('PagosDespdVO.php');

class PagosDespDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "pagosdesp";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \PagosDespVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "fecha, "
                . "vendedor, "
                . "deposito, "
                . "concepto, "
                . "importe, "
                . "status "
                . ") "
                . "VALUES(NOW(), ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("issdi",
                    $objectVO->getVendedor(),
                    $objectVO->getDeposito(),
                    $objectVO->getConcepto(),
                    $objectVO->getImporte(),
                    $objectVO->getStatus()
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
     * @return \PagosDespVO
     */
    public function fillObject($rs) {
        $objectVO = new PagosDespVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setVendedor($rs["vendedor"]);
            $objectVO->setFecha($rs["fecha"]);
            $objectVO->setDeposito($rs["deposito"]);
            $objectVO->setConcepto($rs["concepto"]);
            $objectVO->setImporte($rs["importe"]);
            $objectVO->setStatus($rs["status"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \PagosDespVO
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
     * @return \PagosDespVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new PagosDespVO();
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
     * @param \PagosDespVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "vendedor = ?, "
                . "deposito = ?, "
                . "concepto = ?, "
                . "importe = ?, "
                . "status = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("issdii",
                    $objectVO->getVendedor(),
                    $objectVO->getDeposito(),
                    $objectVO->getConcepto(),
                    $objectVO->getImporte(),
                    $objectVO->getStatus(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

    /**
     * 
     * @param \PagosDespdVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function createD($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . "d ("
                . "pago, "
                . "referencia, "
                . "importe "
                . ") "
                . "VALUES(?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("iid",
                    $objectVO->getPago(),
                    $objectVO->getReferencia(),
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
     * @param \PagosDespdVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function updateD($objectVO) {
        $sql = "UPDATE " . self::TABLA . "d SET "
                . "pago = -pago, "
                . "referencia = -referencia "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("i",$objectVO->getId());
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}

abstract class StatusPagoDespachador extends BasicEnum {

    const ABIERTO = 0;
    const CERRADO = 1;
    const CANCELADO = 2;
    const CANCELADO_ST = 3;

}
