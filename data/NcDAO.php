<?php

/*
 * NcDAO
 * omicrom
 * 2017, Detisa 
 * http://www.detisa.com.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since nov 2017
 */

include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('BasicEnum.php');
include_once ('NcVO.php');

class NcDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "nc";
    const SIN_TIMBRAR = "-----";

    private $conn;

    public function __construct() {
        $this->conn = getConnection();
    }

    public function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param NcVO $objectVO
     * @return int
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " (cliente, fecha, factura, relacioncfdi, uuid) "
                . "SELECT ?, NOW(), ?, ?, '" . self::SIN_TIMBRAR . "' FROM cia";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sss", $objectVO->getCliente(), $objectVO->getFactura(), $objectVO->getRelacioncfdi());
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
     * @return \NcVO
     */
    public function fillObject($rs) {
        $objectVO = new NcVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setFecha($rs["fecha"]);
            $objectVO->setCliente($rs["cliente"]);
            $objectVO->setCantidad($rs["cantidad"]);
            $objectVO->setImporte($rs["importe"]);
            $objectVO->setIva($rs["iva"]);
            $objectVO->setIeps($rs["ieps"]);
            $objectVO->setStatus($rs["status"]);
            $objectVO->setTotal($rs["total"]);
            $objectVO->setUuid($rs["uuid"]);
            $objectVO->setObservaciones($rs["observaciones"]);
            $objectVO->setFormadepago($rs["formadepago"]);
            $objectVO->setMetododepago($rs["metododepago"]);
            $objectVO->setFactura($rs["factura"]);
            $objectVO->setStcancelacion($rs["stCancelacion"]);
            $objectVO->setUsocfdi($rs["usocfdi"]);
            $objectVO->setTiporelacion($rs["tiporelacion"]);
            $objectVO->setRelacioncfdi($rs["relacioncfdi"]);
            $objectVO->setUsr($rs["usr"]);
            $objectVO->setSello($rs["sello"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \NcVO
     */
    public function getAll($sql) {
        
    }

    /**
     * 
     * @param int $idObjectVO Llave primaria o identificador 
     * @param string $field Nombre del campo para borrar
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function remove($idObjectVO, $field = "id") {
        
    }

    /**
     * 
     * @param int $idObjectVO Llave primaria o identificador 
     * @param string $field Nombre del campo a buscar
     * @return \NcVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new NcVO();
        $sql = "SELECT " . self::TABLA . ".*, IFNULL(ExtractValue(facturas.cfdi_xml, '/cfdi:Comprobante/@Sello'),'') sello "
                . "FROM " . self::TABLA . " "
                . "LEFT JOIN facturas ON nc.uuid = facturas.uuid "
                . "WHERE " . self::TABLA . "." . $field . " = '" . $idObjectVO . "'";
        if (($query = $this->conn->query($sql)) && ($rs = $query->fetch_assoc())) {
            $objectVO = $this->fillObject($rs);
        }
        return $objectVO;
    }

    /**
     * 
     * @param NcVO $objectVO
     * @return boolean
     * @throws Exception
     */
    public function update($objectVO) {
        $sql = "UPDATE nc SET "
                . "fecha = ?, "
                . "cliente = ?, "
                . "cantidad = ?, "
                . "importe = ?, "
                . "iva = ?, "
                . "ieps = ?, "
                . "status = ?, "
                . "total = ?, "
                . "uuid = ?, "
                . "observaciones = ?, "
                . "formadepago = ?, "
                . "metododepago = ?, "
                . "factura = ?, "
                . "stCancelacion = ?, "
                . "usocfdi = ?, "
                . "tiporelacion = ?, "
                . "relacioncfdi = ?, "
                . "usr = ? "
                . "WHERE id = ?";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssssssssssssssssi",
                    $objectVO->getFecha(),
                    $objectVO->getCliente(),
                    $objectVO->getCantidad(),
                    $objectVO->getImporte(),
                    $objectVO->getIva(),
                    $objectVO->getIeps(),
                    $objectVO->getStatus(),
                    $objectVO->getTotal(),
                    $objectVO->getUuid(),
                    $objectVO->getObservaciones(),
                    $objectVO->getFormadepago(),
                    $objectVO->getMetododepago(),
                    $objectVO->getFactura(),
                    $objectVO->getStcancelacion(),
                    $objectVO->getUsocfdi(),
                    $objectVO->getTiporelacion(),
                    $objectVO->getRelacioncfdi(),
                    $objectVO->getUsr(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}

abstract class StatusNotaCredito extends BasicEnum {

    const ABIERTO = 0;
    const CERRADO = 1;
    const CANCELADO = 2;
    const CANCELADO_ST = 3;

}
