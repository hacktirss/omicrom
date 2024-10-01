<?php

/**
 * Description of TerminalPosDAO
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
include_once ('TerminalPosVO.php');

class TerminalPosDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "pos_catalog";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \TerminalPosVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        error_log( print_r($objectVO,true));
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("               
                . "printed_serial,"
                . "serial,"
                . "model,"
                . "ip,"
                . "maclan,"
                . "macwifi,"
                . "kernel,"
                . "status,"
                . "appVersion,"
                . "dispositivo,"
                . "lastConnection"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?,?, NOW())";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssssssss",
                    $objectVO->getPrinted_serial(),
                    $objectVO->getSerial(),
                    $objectVO->getModel(),
                    $objectVO->getIp(),
                    $objectVO->getMaclan(),
                    $objectVO->getMacwifi(),
                    $objectVO->getKernel(),
                    $objectVO->getStatus(),
                    $objectVO->getAppversion(),
                    $objectVO->getDispositivo()
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
     * @return \TerminalPosVO
     */
    public function fillObject($rs) {
        $objectVO = new TerminalPosVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["pos_id"]);
            $objectVO->setPrinted_serial($rs["printed_serial"]);
            $objectVO->setSerial($rs["serial"]);
            $objectVO->setModel($rs["model"]);
            $objectVO->setIp($rs["ip"]);
            $objectVO->setMaclan($rs["maclan"]);
            $objectVO->setMacwifi($rs["macwifi"]);
            $objectVO->setKernel($rs["kernel"]);
            $objectVO->setStatus($rs["status"]);
            $objectVO->setAppversion($rs["appVersion"]);
            $objectVO->setDispositivo($rs["dispositivo"]);
            $objectVO->setLastconnection($rs["lastConnection"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \TerminalPosVO
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
     * @return \TerminalPosVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new TerminalPosVO();
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
     * @param \TerminalPosVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "printed_serial = ?, "
                . "serial = ?, "
                . "model = ?, "
                . "ip = ?, "
                . "maclan = ?, "
                . "macwifi = ?, "
                . "kernel = ?, "
                . "status = ?, "
                . "appVersion = ?, "
                . "dispositivo = ?, "
                . "lastConnection = ? "
                . "WHERE pos_id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssssssi",
                    $objectVO->getPrinted_serial(),
                    $objectVO->getSerial(),
                    $objectVO->getModel(),
                    $objectVO->getIp(),
                    $objectVO->getMaclan(),
                    $objectVO->getMacwifi(),
                    $objectVO->getKernel(),
                    $objectVO->getStatus(),
                    $objectVO->getAppversion(),
                    $objectVO->getDispositivo(),
                    $objectVO->getLastconnection(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}

abstract class StatusTerminal extends BasicEnum {
    const ACTIVO = "A";
    const INACTIVO = "I";
}