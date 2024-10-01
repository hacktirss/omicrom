<?php

/**
 * Description of CtDAO
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
include_once ('CtVO.php');

class CtDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "ct";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \CtVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO = CtVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "fecha,"
                . "hora,"
                . "fechaf,"
                . "concepto,"
                . "isla,"
                . "turno,"
                . "usr"
                . ") "
                . "VALUES(CURRENT_DATE(), CURRENT_TIME(), CURRENT_DATE(), CONCAT(DATE_FORMAT(NOW(),'%y/%m/%d %H:%i:%s'),' (OMI)'), ?, ?, 'OMI')";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ss",
                    $objectVO->getIsla(),
                    $objectVO->getTurno()
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
     * @return \CtVO
     */
    public function fillObject($rs) {
        $objectVO = new CtVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setFecha($rs["fecha"]);
            $objectVO->setHora($rs["hora"]);
            $objectVO->setFechaf($rs["fechaf"]);
            $objectVO->setConcepto($rs["concepto"]);
            $objectVO->setIsla($rs["isla"]);
            $objectVO->setTurno($rs["turno"]);
            $objectVO->setUsr($rs["usr"]);
            $objectVO->setStatus($rs["status"]);
            $objectVO->setStatusctv($rs["statusctv"]);
            $objectVO->setEnviado($rs["enviado"]);
            $objectVO->setProducto1($rs["producto1"]);
            $objectVO->setProducto2($rs["producto2"]);
            $objectVO->setProducto3($rs["producto3"]);
            $objectVO->setProducto4($rs["producto4"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \CtVO
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
     * @return \CtVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new CtVO();
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
     * @param \CtVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = CtVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "fecha = ?, "
                . "hora = ?, "
                . "fechaf = ?, "
                . "concepto = ?, "
                . "isla = ?, "
                . "turno = ?, "
                . "usr = ?, "
                . "status = ?, "
                . "statusctv = ?, "
                . "enviado = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssssssssi",
                    $objectVO->getFecha(),
                    $objectVO->getHora(),
                    $objectVO->getFechaf(),
                    $objectVO->getConcepto(),
                    $objectVO->getIsla(),
                    $objectVO->getTurno(),
                    $objectVO->getUsr(),
                    $objectVO->getStatus(),
                    $objectVO->getStatusctv(),
                    $objectVO->getEnviado(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}

abstract class StatusCorte extends BasicEnum {
    const ABIERTO = "Abierto";
    const CERRADO = "Cerrado";
}

abstract class ConcentrarTarjetasCorte extends BasicEnum {
    const SI = "S";
    const NO = "N";
}