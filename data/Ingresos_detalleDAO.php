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
include_once ('Ingresos_detalleVO.php');

class Ingresos_detalleDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "ingresos_detalle";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \Ingresos_detalleVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "id,"
                . "producto,"
                . "cantidad,"
                . "preciob,"
                . "precio,"
                . "iva,"
                . "ieps,"
                . "importe"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("iissssss",
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
     * @return \Ingresos_detalleVO
     */
    public function fillObject($rs) {
        $objectVO = new Ingresos_detalleVO();
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
     * @return array Arreglo de objetos \Ingresos_detalleVO
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
     * @return \Ingresos_detalleVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new Ingresos_detalleVO();
        $sql = "SELECT *   FROM " . self::TABLA . " WHERE " . $field . " = '" . $idObjectVO . "'";
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
     * @param \Ingresos_detalleVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = Ingresos_detalleVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "producto = ?, "
                . "cantidad = ?, "
                . "preciob = ?, "
                . "precio = ?, "
                . "iva = ?, "
                . "ieps = ?, "
                . "importe = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssi",
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
