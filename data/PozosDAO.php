<?php

/**
 * Description of PozosDAO
 * omicrom®
 * © 2022, Detisa 
 * http://www.detisa.com.mx
 * @author Ayala Gonzalez Alejandro 
 * @version 1.0
 * @since sep 2022
 */
include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('BasicEnum.php');
include_once ('PozosVO.php');

class PozosDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "pozos";

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * @param \PozosVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;

        $sql = "INSERT INTO " . self::TABLA . " ("
                . "descripcion,"
                . "clave_sistema_medicion,"
                . "descripcion_sistema_medicion,"
                . "vigencia_sistema_medicion,"
                . "incertidumbre_sistema_medicion"
                . ") "
                . "VALUES(?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssss",
                    $objectVO->getDescripcion(),
                    $objectVO->getClave_sistema_medicion(),
                    $objectVO->getDescripcion_sistema_medicion(),
                    $objectVO->getVigencia_sistema_medicion(),
                    $objectVO->getIncertidumbre_sistema_medicion()
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
     * @return \PozosVO
     */
    public function fillObject($rs) {
        $objectVO = new PozosVO();
        if (is_array($rs)) {
            $objectVO->setDescripcion($rs["descripcion"]);
            $objectVO->setClave_sistema_medicion($rs["clave_sistema_medicion"]);
            $objectVO->setDescripcion_sistema_medicion($rs["descripcion_sistema_medicion"]);
            $objectVO->setVigencia_sistema_medicion($rs["vigencia_sistema_medicion"]);
            $objectVO->setIncertidumbre_sistema_medicion($rs["incertidumbre_sistema_medicion"]);
        }
        error_log(" FILL " . print_r($objectVO, true));
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \PozosVO
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
     * @return \PozosVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new PozosVO();
        $sql = "SELECT * FROM " . self::TABLA . " WHERE " . self::TABLA . "." . $field . " = '" . $idObjectVO . "'";
        error_log($sql);
        if (($query = $this->conn->query($sql)) && ($rs = $query->fetch_assoc())) {
            error_log("Entre" . print_r($rs, true));
            $objectVO = $this->fillObject($rs);
            return $objectVO;
        } else {
            error_log($this->conn->error);
        }
        return $objectVO;
    }

    /**
     * 
     * @param \PozosVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO) {
        error_log("EN UPDATE " . print_r($objectVO, true));
        $sql = "UPDATE " . self::TABLA . " SET "
                . "descripcion = ?, "
                . "clave_sistema_medicion = ?, "
                . "descripcion_sistema_medicion = ?, "
                . "vigencia_sistema_medicion = ?, "
                . "incertidumbre_sistema_medicion = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            error_log("ENTRAMOS EN UPDATRE");
            $ps->bind_param("sssssi",
                    $objectVO->getDescripcion(),
                    $objectVO->getClave_sistema_medicion(),
                    $objectVO->getDescripcion_sistema_medicion(),
                    $objectVO->getVigencia_sistema_medicion(),
                    $objectVO->getIncertidumbre_sistema_medicion(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}
