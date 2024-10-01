<?php

/**
 * Description of TrasladosDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Alejandro Ayala Gonzalez
 * @version 1.0
 * @since ene 2022
 */
include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('BasicEnum.php');
include_once ('TrasladosVO.php');

class TrasladosDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "traslados";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \TrasladosVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO = TrasladosVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "id_cli,"
                . "serie,"
                . "folio, "
                . "claveProductoServicio, "
                . "fecha, "
                . "cantidad, "
                . "importe, "
                . "iva, "
                . "ieps, "
                . "total, "
                . "status, "
                . "uuid, "
                . "observaciones, "
                . "usr, "
                . "stCancelacion, "
                . "motivoCan,"
                . "metodoPago,"
                . "formaPago,"
                . "usoCfdi"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("issssssssssssssssss",
                    $objectVO->getId_cli(),
                    $objectVO->getSerie(),
                    $objectVO->getFolio(),
                    $objectVO->getClaveProductoServicio(),
                    $objectVO->getFecha(),
                    $objectVO->getCantidad(),
                    $objectVO->getImporte(),
                    $objectVO->getIva(),
                    $objectVO->getIeps(),
                    $objectVO->getTotal(),
                    $objectVO->getStatus(),
                    $objectVO->getUuid(),
                    $objectVO->getObservaciones(),
                    $objectVO->getUsr(),
                    $objectVO->getStCancelacion(),
                    $objectVO->getMotivoCan(),
                    $objectVO->getMetodoPago(),
                    $objectVO->getFormaPago(),
                    $objectVO->getUsoCfdi()
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
     * @return \TrasladosVO
     */
    public function fillObject($rs) {
        $objectVO = new TrasladosVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setId_cli($rs["id_cli"]);
            $objectVO->setSerie($rs["serie"]);
            $objectVO->setFolio($rs["folio"]);
            $objectVO->setClaveProductoServicio($rs["claveProductoServicio"]);
            $objectVO->setFecha($rs["fecha"]);
            $objectVO->setCantidad($rs["cantidad"]);
            $objectVO->setImporte($rs["importe"]);
            $objectVO->setIva($rs["iva"]);
            $objectVO->setIeps($rs["ieps"]);
            $objectVO->setTotal($rs["total"]);
            $objectVO->setStatus($rs["status"]);
            $objectVO->setUuid($rs["uuid"]);
            $objectVO->setObservaciones($rs["observaciones"]);
            $objectVO->setUsr($rs["usr"]);
            $objectVO->setStCancelacion($rs["stCancelacion"]);
            $objectVO->setMotivoCan($rs["motivoCan"]);
            $objectVO->setMetodoPago($rs["metodoPago"]);
            $objectVO->setFormaPago($rs["formaPago"]);
            $objectVO->setUsoCfdi($rs["usoCfdi"]);
            $objectVO->setSello($rs["sello"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \TrasladosVO
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
     * @return \TrasladosVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new TrasladosVO();
        $sql = "SELECT t.id,t.id_cli,t.serie,t.folio,t.claveProductoServicio,t.fecha,t.cantidad,t.importe,t.iva,t.ieps,t.total,t.status,
                    t.uuid,t.observaciones,t.usr,t.stCancelacion,t.motivoCan,t.metodoPago,t.formaPago,t.usoCfdi,
                    IFNULL(ExtractValue(f.cfdi_xml, '/cfdi:Comprobante/@Sello'),'') sello   FROM " . self::TABLA . " t LEFT JOIN facturas f
                    ON t.uuid = f.uuid WHERE " . $field . " = '" . $idObjectVO . "'";
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
     * @param \TrasladosVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = TrasladosVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "id_cli = ? ,"
                . "serie = ?, "
                . "folio = ?, "
                . "claveProductoServicio = ?, "
                . "fecha = ?, "
                . "cantidad = ?, "
                . "importe = ?, "
                . "iva = ?, "
                . "ieps = ?, "
                . "total = ?, "
                . "status = ?, "
                . "uuid = ?, "
                . "observaciones = ?, "
                . "usr = ?, "
                . "stCancelacion = ?, "
                . "motivoCan = ?, "
                . "metodoPago = ?, "
                . "formaPago = ?, "
                . "usoCfdi = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("issssssssssssssssssi",
                    $objectVO->getId_cli(),
                    $objectVO->getSerie(),
                    $objectVO->getFolio(),
                    $objectVO->getClaveProductoServicio(),
                    $objectVO->getFecha(),
                    $objectVO->getCantidad(),
                    $objectVO->getImporte(),
                    $objectVO->getIva(),
                    $objectVO->getIeps(),
                    $objectVO->getTotal(),
                    $objectVO->getStatus(),
                    $objectVO->getUuid(),
                    $objectVO->getObservaciones(),
                    $objectVO->getUsr(),
                    $objectVO->getStCancelacion(),
                    $objectVO->getMotivoCan(),
                    $objectVO->getMetodoPago(),
                    $objectVO->getFormaPago(),
                    $objectVO->getUsoCfdi(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}
abstract class OrigenFacturaTraslados extends BasicEnum {
    const OMICROM = 1;
    const TERMINAL = 2;
    const ONLINE = 3;
    const SINTIMBRAR = "-----";
}
