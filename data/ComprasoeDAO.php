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
include_once ('ComprasoeVO.php');

class ComprasoeDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "eto";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function _destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \ComprasoeVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO= ComprasoeVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " (" 
                . "fecha,"
                . "fechav,"
                . "proveedor,"
                . "concepto,"
                . "documento,"
                . "cantidad,"
                . "importe,"
                . "iva"
                . ") "
                . "VALUES( NOW(), ?, ?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssss",
                    $objectVO->getFechav(),
                    $objectVO->getProveedor(),
                    $objectVO->getConcepto(),
                    $objectVO->getDocumento(),
                    $objectVO->getCantidad(),
                    $objectVO->getImporte(),
                    $objectVO->getIva()
                    
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
     * @return \ComprasoeVO
     */
    public function fillObject($rs) {
        $objectVO = new ComprasoeVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setFecha($rs["fecha"]);
            $objectVO->setFechaV($rs["fechav"]);
            $objectVO->setProveedor($rs["proveedor"]);
            $objectVO->setConcepto($rs["concepto"]);
            $objectVO->setDocumento($rs["documento"]);
            $objectVO->setCantidad($rs["cantidad"]);
            $objectVO->setImporte($rs["importe"]);
            $objectVO->setIva($rs["iva"]);
            $objectVO->setStatus($rs["status"]);
            
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \ProductoVO
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
     * @return \ProductoVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new ComprasoeVO();
        $sql = "SELECT * FROM " . self::TABLA . " WHERE " . $field . " = '" . $idObjectVO . "'";
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
    public function update($objectVO = ComprasoeVO) {
        //$objectVO = new ComprasoeVO();
        $sql = "UPDATE " . self::TABLA . " SET "
                . "fechav = ?, "
                . "proveedor = ?, "
                . "concepto = ?, "
                . "documento = ?, "
                . "cantidad = ?, "
                . "importe = ?, "
                . "iva = ?, "
                . "status = ? "
                . "WHERE id = ? ";
        //error_log($sql);
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssssssi",
                    $objectVO->getFechav(),
                    $objectVO->getProveedor(),
                    $objectVO->getConcepto(),
                    $objectVO->getDocumento(),
                    $objectVO->getCantidad(),
                    $objectVO->getImporte(),
                    $objectVO->getIva(),
                    $objectVO->getStatus(),
                    $objectVO->getId()
            );
            return $ps->execute();
        } 
        error_log($this->conn->error);
        return false;
    }

}
