<?php

/**
 * Description of BancosDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
include_once ('mysqlUtils.php');
include_once ('PromosVO.php');
include_once ('FunctionsDAO.php');

class PromosDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "promos";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function _destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \PromosVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "fecha_creacion,"
                . "id_authuser,"
                . "fecha_limite,"
                . "minimo,"
                . "tipo,"
                . "codigo_promo,"
                . "status,"
                . "id_cli,"
                . "importe"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sisssssis",
                    $objectVO->getFecha_creacion(),
                    $objectVO->getId_authuser(),
                    $objectVO->getFecha_limite(),
                    $objectVO->getMinimo(),
                    $objectVO->getTipo(),
                    $objectVO->getCodigo_promo(),
                    $objectVO->getStatus(),
                    $objectVO->getId_cli(),
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
     * @return \PromosVO
     */
    public function fillObject($rs) {
        $objectVO = new PromosVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setFecha_creacion($rs["fecha_creacion"]);
            $objectVO->setId_authuser($rs["id_authuser"]);
            $objectVO->setFecha_limite($rs["fecha_limite"]);
            $objectVO->setMinimo($rs["minimo"]);
            $objectVO->setTipo($rs["tipo"]);
            $objectVO->setCodigo_promo($rs["codigo_promo"]);
            $objectVO->setStatus($rs["status"]);
            $objectVO->setId_cli($rs["id_cli"]);
            $objectVO->setImporte($rs["importe"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param int $idObjectVO Llave primaria o identificador 
     * @param string $field Nombre del campo a buscar
     * @return \PromosVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new PromosVO();
        $sql = "SELECT * FROM " . self::TABLA . " WHERE " . $field . " = " . $idObjectVO;
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
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \PromosVO
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
     * @param \PromosVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = PromosVO) {

        $sql = "UPDATE " . self::TABLA . " SET "
                . "fecha_creacion = ?, "
                . "id_authuser = ?, "
                . "fecha_limite = ?, "
                . "minimo = ?, "
                . "tipo = ?, "
                . "codigo_promo = ?, "
                . "status = ?, "
                . "id_cli = ?, "
                . "importe = ? "
                . "WHERE id = ?";
        //error_log($sql);
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sisssssisi",
                    $objectVO->getFecha_creacion(),
                    $objectVO->getId_authuser(),
                    $objectVO->getFecha_limite(),
                    $objectVO->getMinimo(),
                    $objectVO->getTipo(),
                    $objectVO->getCodigo_promo(),
                    $objectVO->getStatus(),
                    $objectVO->getId_cli(),
                    $objectVO->getImporte(),
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
