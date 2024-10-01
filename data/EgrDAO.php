<?php

/**
 * Description of EgrDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
include_once ('mysqlUtils.php');
include_once ('EgrVO.php');
include_once ('FunctionsDAO.php');

class EgrDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "egr";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function _destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \EgrVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "corte, "
                . "clave, "
                . "concepto, "
                . "importe, "
                . "plomo, "
                . "tipo_cambio "
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("iisdsd",
                    $objectVO->getCorte(),
                    $objectVO->getClave(),
                    $objectVO->getConcepto(),
                    $objectVO->getImporte(),
                    $objectVO->getPlomo(),
                    $objectVO->getTipo_cambio()
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
     * @return \EgrVO
     */
    public function fillObject($rs) {
        $objectVO = new EgrVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setCorte($rs["corte"]);
            $objectVO->setClave($rs["clave"]);
            $objectVO->setConcepto($rs["concepto"]);
            $objectVO->setImporte($rs["importe"]);
            $objectVO->setPlomo($rs["plomo"]);
            $objectVO->setTipo_cambio($rs["tipo_cambio"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \EgrVO
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
     * @param string $field Nombre del campo a buscar
     * @return \BancosVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new EgrVO();
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
     * @param \EgrVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = EgrVO) {
        //$objectVO = new EgrVO();
        $sql = "UPDATE " . self::TABLA . " SET "
                . "corte = ?, "
                . "clave = ?, "
                . "concepto = ?, "
                . "importe = ?, "
                . "plomo = ?, "
                . "tipo_cambio = ? "
                . "WHERE id = ?";
        //error_log($sql);
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("iisdsdi",
                    $objectVO->getCorte(),
                    $objectVO->getClave(),
                    $objectVO->getConcepto(),
                    $objectVO->getImporte(),
                    $objectVO->getPlomo(),
                    $objectVO->getTipo_cambio(),
                    $objectVO->getId()
            );
            return $ps->execute();
        } else {
            error_log($this->conn->error);
            return false;
        }
    }

}
