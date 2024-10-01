<?php

/**
 * Description of EnvioPromoDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Ayala Gonzalez Alejandro
 * @version 1.1
 * @since oct 2023
 */
include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('BasicEnum.php');
include_once ('EnvioPromoVO.php');

class EnvioPromoDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "envioPromo";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \EnvioPromoVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "descripcion,"
                . "fecha_inicio,"
                . "fecha_final,"
                . "descuento,"
                . "id_producto,"
                . "id_user,"
                . "consumo_min"
                . ") "
                . "VALUES(?,?,?,?,?,?,?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssiis",
                    $objectVO->getDescripcion(),
                    $objectVO->getFecha_inicio(),
                    $objectVO->getFecha_final(),
                    $objectVO->getDescuento(),
                    $objectVO->getId_producto(),
                    $objectVO->getId_user(),
                    $objectVO->getConsumo_min()
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
     * @return \EnvioPromoVO
     */
    public function fillObject($rs) {
        $objectVO = new EnvioPromoVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setDescripcion($rs["descripcion"]);
            $objectVO->setFecha_creacion($rs["fecha_creacion"]);
            $objectVO->setFecha_inicio($rs["fecha_inicio"]);
            $objectVO->setFecha_final($rs["fecha_final"]);
            $objectVO->setDescuento($rs["descuento"]);
            $objectVO->setId_producto($rs["id_producto"]);
            $objectVO->setId_user($rs["id_user"]);
            $objectVO->setConsumo_min($rs["consumo_min"]);
            $objectVO->setStatus($rs["status"]);
        }
//        error_log(print_r($objectVO, true));
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \EnvioPromoVO
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

    /*
     *
     * @param int $idObjectVO Llave primaria o identificador
     * @param string $field

      Nombre del campo a buscar
     * @return \EnvioPromoVO
     */

    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new EnvioPromoVO();
        $sql = "SELECT * FROM " . self::TABLA . " WHERE " . $field . " = '" . $idObjectVO . "'";
//        error_log($sql);
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
     * @param \EnvioPromoVO $objectVO


     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = PeriodoPuntosVO) {
        error_log(print_r($objectVO, true));
        $sql = "UPDATE " . self::TABLA . " SET "
                . "descripcion = ?, "
                . "fecha_inicio = ?, "
                . "fecha_final = ?, "
                . "descuento = ?, "
                . "id_producto = ?, "
                . "id_user = ?, "
                . "consumo_min = ?, "
                . "status = ? "
                . "WHERE id = ? ";
        error_log($sql);
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssiissi",
                    $objectVO->getDescripcion(),
                    $objectVO->getFecha_inicio(),
                    $objectVO->getFecha_final(),
                    $objectVO->getDescuento(),
                    $objectVO->getId_producto(),
                    $objectVO->getId_user(),
                    $objectVO->getConsumo_min(),
                    $objectVO->getStatus(),
                    $objectVO->getId()
            );
            error_log($this->conn->error);
            if ($ps->execute()) {
                error_log(print_r($objectVO, true));
                return true;
            }
            error_log($this->conn->error);
        }
        error_log($this->conn->error);
        return false;
    }

}
