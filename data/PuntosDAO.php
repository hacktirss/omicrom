<?php

/**
 * Description of PuntosDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Ayala Gonzalez Alejandro
 * @version 1.0
 * @since ago 2023
 */
include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('BasicEnum.php');
include_once ('PuntosVO.php');

class PuntosDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "puntos";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \PuntosVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO = PuntosVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "cliente,"
                . "producto,"
                . "puntos,"
                . "fecha,"
                . "status,"
                . "id_periodo"
                . ") "
                . "VALUES(?,?,?,NOW(),?,?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("iiissi",
                    $objectVO->getCliente(),
                    $objectVO->getProducto(),
                    $objectVO->getPuntos(),
                    $objectVO->getStatus(),
                    $objectVO->getId_periodo()
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
     * @return \PuntosVO
     */
    public function fillObject($rs) {
        $objectVO = new PuntosVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setCliente($rs["cliente"]);
            $objectVO->setProducto($rs["producto"]);
            $objectVO->setPuntos($rs["puntos"]);
            $objectVO->setFecha($rs["fecha"]);
            $objectVO->setStatus($rs["status"]);
            $objectVO->setId_periodo($rs["id_periodo"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \PuntosVO
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
     * @return \PuntosVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new PuntosVO();
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
     * @param \PuntosVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = PuntosVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "cliente = ?, "
                . "producto = ?, "
                . "puntos = ?, "
                . "fecha = ?, "
                . "status = ?, "
                . "id_periodo = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("iiissii",
                    $objectVO->getCliente(),
                    $objectVO->getProducto(),
                    $objectVO->getPuntos(),
                    $objectVO->getFecha(),
                    $objectVO->getStatus(),
                    $objectVO->getId_periodo(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}
