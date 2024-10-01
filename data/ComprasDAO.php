<?php

/**
 * Description of ComprasDAO
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
include_once ('ComprasVO.php');

class ComprasDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "et";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \ComprasVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "fecha,"
                . "proveedor,"
                . "concepto,"
                . "documento,"
                . "cantidad,"
                . "importe,"
                . "importesin,"
                . "iva,"
                . "observaciones,"
                . "uuid"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssssssss",
                    $objectVO->getFecha(),
                    $objectVO->getProveedor(),
                    $objectVO->getConcepto(),
                    $objectVO->getDocumento(),
                    $objectVO->getCantidad(),
                    $objectVO->getImporte(),
                    $objectVO->getImportesin(),
                    $objectVO->getIva(),
                    $objectVO->getObservaciones(),
                    $objectVO->getUuid()
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
     * @return \ComprasVO
     */
    public function fillObject($rs) {
        $objectVO = new ComprasVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setFecha($rs["fecha"]);
            $objectVO->setProveedor($rs["proveedor"]);
            $objectVO->setConcepto($rs["concepto"]);
            $objectVO->setDocumento($rs["documento"]);
            $objectVO->setCantidad($rs["cantidad"]);
            $objectVO->setImporte($rs["importe"]);
            $objectVO->setImportesin($rs["importesin"]);
            $objectVO->setIva($rs["iva"]);
            $objectVO->setStatus($rs["status"]);
            $objectVO->setNombre($rs["nombre"]);
            $objectVO->setAlias($rs["alias"]);
            $objectVO->setTipodepago($rs["tipodepago"]);
            $objectVO->setDias_credito($rs["dias_credito"]);
            $objectVO->setProveedorde($rs["proveedorde"]);
            $objectVO->setObservaciones($rs["observaciones"]);
            $objectVO->setUuid($rs["uuid"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \ComprasVO
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
     * @return \ComprasVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new ComprasVO();
        $sql = "SELECT " . self::TABLA . ".*,prv.nombre,prv.alias,prv.tipodepago,prv.dias_credito,prv.proveedorde FROM " . self::TABLA . " "
                . "LEFT JOIN prv ON " . self::TABLA . ".proveedor = prv.id "
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
     * @param \ComprasVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "fecha = ?, "
                . "proveedor = ?, "
                . "concepto = ?, "
                . "documento = ?, "
                . "cantidad = ?, "
                . "importe = ?, "
                . "importesin = ?, "
                . "iva = ?, "
                . "status = ?, "
                . "observaciones = ?, "
                . "uuid = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssssssi",
                    $objectVO->getFecha(),
                    $objectVO->getProveedor(),
                    $objectVO->getConcepto(),
                    $objectVO->getDocumento(),
                    $objectVO->getCantidad(),
                    $objectVO->getImporte(),
                    $objectVO->getImportesin(),
                    $objectVO->getIva(),
                    $objectVO->getStatus(),
                    $objectVO->getObservaciones(),
                    $objectVO->getUuid(),
                    $objectVO->getId()
            );
            if ($ps->execute()) {
                return true;
            }
        }
        error_log($this->conn->error);
        return false;
    }

}

abstract class StatusCompra extends BasicEnum {

    const ABIERTO = "Abierta";
    const CERRADO = "Cerrada";
    const CANCELADO = "Cancelado";

}
