<?php

/**
 * Description of IngresosDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Alejandro Ayala Gonzalez
 * @version 1.0
 * @since may 2022
 */
include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('BasicEnum.php');
include_once ('IngresosVO.php');

class IngresosDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "ingresos";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \IngresosVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO = IngresosVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "serie,"
                . "folio,"
                . "fecha,"
                . "cantidad,"
                . "importe,"
                . "iva,"
                . "ieps,"
                . "total,"
                . "status,"
                . "uuid,"
                . "observaciones,"
                . "usr,"
                . "stCancelacion,"
                . "motivoCan,"
                . "id_cli,"
                . "ClaveProdServ,"
                . "cli,"
                . "metodopago,"
                . "formadepago,"
                . "usocfdi"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssssssssssssssssss",
                    $objectVO->getSerie(),
                    $objectVO->getFolio(),
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
                    $objectVO->getId_cli(),
                    $objectVO->getClaveProdServ(),
                    $objectVO->getId_cli(),
                    $objectVO->getMetodopago(),
                    $objectVO->getFormadepago(),
                    $objectVO->getUsocfdi()
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
     * @return \IngresosVO
     */
    public function fillObject($rs) {
        $objectVO = new IngresosVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setSerie($rs["serie"]);
            $objectVO->setFolio($rs["folio"]);
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
            $objectVO->setId_cli($rs["id_cli"]);
            $objectVO->setClaveProdServ($rs["ClaveProdServ"]);
            $objectVO->setCli($rs["cli"]);
            $objectVO->setMetodopago($rs["metodoPago"]);
            $objectVO->setFormadepago($rs["formadepago"]);
            $objectVO->setUsocfdi($rs["usoCfdi"]);
            $objectVO->setSello($rs["sello"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \IngresosVO
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
     * @return \IngresosVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new IngresosVO();
        $sql = "SELECT t.id,t.id_cli,t.serie,t.folio,t.ClaveProdServ,t.fecha,t.cantidad,t.importe,t.iva,t.ieps,t.total,t.status, t.uuid,t.observaciones,"
                . "t.usr,t.stCancelacion,t.motivoCan,t.metodoPago,t.formadepago,t.usoCfdi, IFNULL(ExtractValue(f.cfdi_xml, '/cfdi:Comprobante/@Sello'),'') sello "
                . "FROM " . self::TABLA . " t LEFT JOIN facturas f 
                    ON t.uuid = f.uuid WHERE " . $field . " = '" . $idObjectVO . "'";
        //echo $sql;
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
     * @param \IngresosVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = IngresosVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "serie = ? ,"
                . "folio = ?, "
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
                . "id_cli = ?, "
                . "ClaveProdServ = ?, "
                . "cli = ?, "
                . "metodopago = ?, "
                . "formadepago = ?, "
                . "usocfdi = ?  "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssssssssssssssisssi",
                    $objectVO->getSerie(),
                    $objectVO->getFolio(),
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
                    $objectVO->getId_cli(),
                    $objectVO->getClaveProdServ(),
                    $objectVO->getCli(),
                    $objectVO->getMetodopago(),
                    $objectVO->getFormadepago(),
                    $objectVO->getUsocfdi(),
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

abstract class OrigenFacturaIngreso extends BasicEnum {
    const OMICROM = 1;
    const TERMINAL = 2;
    const ONLINE = 3;
    const SINTIMBRAR = "-----";
}
