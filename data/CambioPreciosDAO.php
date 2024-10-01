<?php

/**
 * Description of CambioPreciosDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('CambioPreciosVO.php');

class CambioPreciosDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "cp";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \CambioPreciosVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "fecha,"
                . "fechaapli,"
                . "hora,"
                . "producto,"
                . "precio,"
                . "status,"
                . "idtarea"
                . ") "
                . "VALUES(CURRENT_DATE(), ?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssss",
                    $objectVO->getFechaapli(),
                    $objectVO->getHora(),
                    $objectVO->getProducto(),
                    $objectVO->getPrecio(),
                    $objectVO->getStatus(),
                    $objectVO->getIdtarea()
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
     * @return \CambioPreciosVO
     */
    public function fillObject($rs) {
        $objectVO = new CambioPreciosVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setFecha($rs["fecha"]);
            $objectVO->setFechaapli($rs["fechaapli"]);
            $objectVO->setHora($rs["hora"]);
            $objectVO->setProducto($rs["producto"]);
            $objectVO->setPrecio($rs["precio"]);
            $objectVO->setStatus($rs["status"]);
            $objectVO->setIdtarea($rs["idtarea"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \CambioPreciosVO
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
     * @return \CambioPreciosVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new CambioPreciosVO();
        $sql = "SELECT * FROM " . self::TABLA . " WHERE " . $field . " = '" . $idObjectVO . "'";
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
     * @param \CambioPreciosVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "fecha = CURRENT_DATE(), "
                . "fechaapli = ?, "
                . "hora = ?, "
                . "producto = ?, "
                . "precio = ?, "
                . "status = ?, "
                . "idtarea = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssssi",
                    $objectVO->getFechaapli(),
                    $objectVO->getHora(),
                    $objectVO->getProducto(),
                    $objectVO->getPrecio(),
                    $objectVO->getStatus(),
                    $objectVO->getIdtarea(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}
