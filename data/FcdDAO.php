<?php

/*
 * FcdDAO
 * omicrom®
 * © 2017, Detisa 
 * http://www.detisa.com.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since jul 2017
 */

include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('BasicEnum.php');
include_once ('FcdVO.php');

class FcdDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "fcd";

    private $conn;

    public function __construct() {
        $this->conn = getConnection();
    }

    public function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \FcdVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO = FcdVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "id,"
                . "producto,"
                . "cantidad,"
                . "precio,"
                . "iva,"
                . "iva_retenido,"
                . "ieps,"
                . "importe,"
                . "ticket,"
                . "tipoc,"
                . "preciob,"
                . "descuento,"
                . "isr_retenido"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            error_log($objectVO->getCantidad());
            $ps->bind_param("iiddddddisdds",
                    $objectVO->getId(),
                    $objectVO->getProducto(),
                    $objectVO->getCantidad(),
                    $objectVO->getPrecio(),
                    $objectVO->getIva(),
                    $objectVO->getIva_retenido(),
                    $objectVO->getIeps(),
                    $objectVO->getImporte(),
                    $objectVO->getTicket(),
                    $objectVO->getTipoc(),
                    $objectVO->getPreciob(),
                    $objectVO->getDescuento(),
                    $objectVO->getIsr_retenido()
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
     * @return \FcdVO
     */
    public function fillObject($rs) {
        $objectVO = new FcdVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setIdnvo($rs["idnvo"]);
            $objectVO->setProducto($rs["producto"]);
            $objectVO->setCantidad($rs["cantidad"]);
            $objectVO->setPrecio($rs["precio"]);
            $objectVO->setIva($rs["iva"]);
            $objectVO->setIva_retenido($rs["iva_retenido"]);
            $objectVO->setIeps($rs["ieps"]);
            $objectVO->setImporte($rs["importe"]);
            $objectVO->setTicket($rs["ticket"]);
            $objectVO->setTipoc($rs["tipoc"]);
            $objectVO->setPreciob($rs["preciob"]);
            $objectVO->setDescuento($rs["descuento"]);
            $objectVO->setTipodepago($rs["tipodepago"]);
            $objectVO->setClavei($rs["clavei"]);
            $objectVO->setIsr_retenido($rs["isr_retenido"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \FcdVO
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
     * @return \FcdVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new FcdVO();
        $sql = "SELECT fcd.*,IFNULL(rm.tipodepago,'Contado') tipodepago,IFNULL(rm.producto,'') clavei FROM " . self::TABLA . " "
                . "LEFT JOIN rm ON fcd.ticket = rm.id AND fcd.producto <= 5 "
                . "WHERE " . self::TABLA . "." . $field . " = '" . $idObjectVO . "'";
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
     * @param \FcdVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = FcdVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "id = ?, "
                . "producto = ?, "
                . "cantidad = ?, "
                . "precio = ?, "
                . "iva = ?, "
                . "iva_retenido = ?, "
                . "ieps = ?, "
                . "importe = ?, "
                . "ticket = ?, "
                . "tipoc = ?, "
                . "preciob = ?, "
                . "descuento = ?, "
                . "isr_retenido = ? "
                . "WHERE idnvo = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("iisdddddisddsi",
                    $objectVO->getId(),
                    $objectVO->getProducto(),
                    $objectVO->getCantidad(),
                    $objectVO->getPrecio(),
                    $objectVO->getIva(),
                    $objectVO->getIva_retenido(),
                    $objectVO->getIeps(),
                    $objectVO->getImporte(),
                    $objectVO->getTicket(),
                    $objectVO->getTipoc(),
                    $objectVO->getPreciob(),
                    $objectVO->getDescuento(),
                    $objectVO->getIsr_retenido(),
                    $objectVO->getIdnvo()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}

abstract class TipoProductoFCD extends BasicEnum {

    const COMBUSTIBLE = "C";
    const ADITIVOS = "A";

}
