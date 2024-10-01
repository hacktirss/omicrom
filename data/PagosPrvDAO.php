<?php

/**
 * Description of PagosPrvDAO
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
include_once ('PagosPrvVO.php');

class PagosPrvDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "pagosprv";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \PagosPrvVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO = PagosPrvVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "proveedor,"
                . "fecha,"
                . "concepto,"
                . "importe,"
                . "aplicado,"
                . "referencia,"
                . "status"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?, ?)";

        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("issdiis",
                    $objectVO->getProveedor(),
                    $objectVO->getFecha(),
                    $objectVO->getConcepto(),
                    $objectVO->getImporte(),
                    $objectVO->getAplicado(),
                    $objectVO->getReferencia(),
                    $objectVO->getStatus()
            );
            if ($ps->execute()) {
                $id = $ps->insert_id;
                $ps->close();
                return $id;
            } else {
                error_log($ps->error);
                error_log($this->conn->info);
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
     * @return \PagosPrvVO
     */
    public function fillObject($rs) {
        $objectVO = new PagosPrvVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setProveedor($rs["proveedor"]);
            $objectVO->setFecha($rs["fecha"]);
            $objectVO->setConcepto($rs["concepto"]);
            $objectVO->setImporte($rs["importe"]);
            $objectVO->setAplicado($rs["aplicado"]);
            $objectVO->setReferencia($rs["referencia"]);
            $objectVO->setStatus($rs["status"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \PagosPrvVO
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
     * @return \PagosPrvVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new PagosPrvVO();
        $sql = "SELECT " . self::TABLA . ".*"
                . "FROM " . self::TABLA . " WHERE " . $field . " = '" . $idObjectVO . "'";
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
     * @param \PagosPrvVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = PagosPrvVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "proveedor = ?, "
                . "fecha = ?, "
                . "concepto = ?, "
                . "importe = ?, "
                . "aplicado = ?, "
                . "referencia = ?, "
                . "status = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("issdiisi",
                    $objectVO->getProveedor(),
                    $objectVO->getFecha(),
                    $objectVO->getConcepto(),
                    $objectVO->getImporte(),
                    $objectVO->getAplicado(),
                    $objectVO->getReferencia(),
                    $objectVO->getStatus(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}

abstract class StatusPagoProveedor extends BasicEnum {

    const ABIERTO = "Abierta";
    const CERRADO = "Cerrada";
    const CANCELADO = "Cancelado";

}
