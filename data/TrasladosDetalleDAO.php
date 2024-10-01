<?php

/**
 * Description of TrasladosDetalleDAO
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
include_once ('TrasladosDetalleVO.php');

class TrasladosDetalleDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "traslados_detalle";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \TrasladosDetalleVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO = TrasladosDetalleVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "id, "
                . "producto, "
                . "cantidad, "
                . "preciob, "
                . "precio, "
                . "iva, "
                . "ieps, "
                . "importe"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssssss",
                    $objectVO->getId(),
                    $objectVO->getProducto(),
                    $objectVO->getCantidad(),
                    $objectVO->getPreciob(),
                    $objectVO->getPrecio(),
                    $objectVO->getIva(),
                    $objectVO->getIeps(),
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
     * @return \TrasladosDetalleVO
     */
    public function fillObject($rs) {
        $objectVO = new TrasladosDetalleVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setIdnvo($rs["idnvo"]);
            $objectVO->setProducto($rs["producto"]);
            $objectVO->setCantidad($rs["cantidad"]);
            $objectVO->setPreciob($rs["preciob"]);
            $objectVO->setPrecio($rs["precio"]);
            $objectVO->setIva($rs["iva"]);
            $objectVO->setIeps($rs["ieps"]);
            $objectVO->setImporte($rs["importe"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \TrasladosDetalleVO
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
    public function remove($idObjectVO, $field = "idnvo") {
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
     * @return \TrasladosDetalleVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new TrasladosDetalleVO();
        $sql = "SELECT * FROM " . self::TABLA . " WHERE " . $field . " = '" . $idObjectVO . "'";
        error_log("AQUI : " . $sql);
        if (($query = $this->conn->query($sql)) && ($rs = $query->fetch_assoc())) {

            $objectVO = $this->fillObject($rs);
            error_log(print_r($objectVO, true));
            return $objectVO;
        } else {
            error_log($this->conn->error);
        }

        return $objectVO;
    }

    /**
     * 
     * @param \TrasladosDetalleVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = TrasladosDetalleVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "idnvo = ?, "
                . "producto = ?, "
                . "cantidad = ?, "
                . "preciob = ?, "
                . "precio = ?, "
                . "iva = ?, "
                . "ieps = ?, "
                . "importe = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssssssi",
                    $objectVO->getIdnvo(),
                    $objectVO->getProducto(),
                    $objectVO->getCantidad(),
                    $objectVO->getPreciob(),
                    $objectVO->getPrecio(),
                    $objectVO->getIva(),
                    $objectVO->getIeps(),
                    $objectVO->getImporte(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}
