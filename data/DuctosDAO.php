<?php

/**
 * Description of DuctosDAO
 * omicrom®
 * © 2021, Detisa 
 * http://www.detisa.com.mx
 * @author Alejandro Ayala Gonzalez
 * @version 1.0
 * @since mar 2021
 */
include_once ('mysqlUtils.php');
include_once ('DuctosDAO.php');
include_once ('DuctosVO.php');

class DuctosDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "ductos";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \DuctosVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO = DuctosVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "tipo_ducto,"
                . "clave_identificacion_ducto,"
                . "descripcion_ducto,"
                . "diametro_ducto,"
                . "descripcion_tipo_ducto,"
                . "cve_producto_sat_ducto,"
                . "almacenamiento_ducto,"
                . "vigencia_calibracion_ducto,"
                . "sistema_medicion,"
                . "medidor"
                . ") "
                . "VALUES(?,?,?,?,?,?,?,?,?,?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("isssssssss",
                    $objectVO->getTipo_ducto(),
                    $objectVO->getClave_identificacion_ducto(),
                    $objectVO->getDescripcion_ducto(),
                    $objectVO->getDiametro_ducto(),
                    $objectVO->getDescripcion_tipo_ducto(),
                    $objectVO->getCve_producto_sat_ducto(),
                    $objectVO->getAlmacenamiento_ducto(),
                    $objectVO->getVigencia_calibracion_ducto(),
                    $objectVO->getSistema_medicion(),
                    $objectVO->getMedidor()
            );
            if ($ps->execute()) {
                $id = $ps->insert_id;
                $ps->close();
                return $id;
            } else {
                error_log("ERROR: ");
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
     * @return \DuctosVO
     */
    public function fillObject($rs) {
        $objectVO = new DuctosVO();
        if (is_array($rs)) {
            $objectVO->setId_ducto($rs["id_ducto"]);
            $objectVO->setTipo_ducto($rs["tipo_ducto"]);
            $objectVO->setClave_identificacion_ducto($rs["clave_identificacion_ducto"]);
            $objectVO->setDescripcion_ducto($rs["descripcion_ducto"]);
            $objectVO->setDiametro_ducto($rs["diametro_ducto"]);
            $objectVO->setDescripcion_tipo_ducto($rs["descripcion_tipo_ducto"]);
            $objectVO->setClave_instalacion($rs["clave_instalacion"]);
            $objectVO->setCve_producto_sat_ducto($rs["cve_producto_sat_ducto"]);
            $objectVO->setAlmacenamiento_ducto($rs["almacenamiento_ducto"]);
            $objectVO->setVigencia_calibracion_ducto($rs["vigencia_calibracion_ducto"]);
            $objectVO->setSistema_medicion($rs["sistema_medicion"]);
            $objectVO->setMedidor($rs["medidor"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \DuctosVO
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
     * @return \DuctosVO
     */
    public function retrieve($idObjectVO, $field = "id_ducto", $fields = "*") {
        $objectVO = new DuctosVO();
        $sql = "SELECT " . $fields . " FROM " . self::TABLA . " LEFT JOIN cia ON TRUE "
                . " WHERE " . self::TABLA . "." . $field . " = '" . $idObjectVO . "'";
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
     * @param \DuctosVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = DuctosVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "tipo_ducto = ?, "
                . "clave_identificacion_ducto = ?, "
                . "descripcion_ducto = ?, "
                . "diametro_ducto = ?, "
                . "descripcion_tipo_ducto = ?, "
                . "cve_producto_sat_ducto = ?, "
                . "almacenamiento_ducto = ?, "
                . "vigencia_calibracion_ducto = ?,"
                . "sistema_medicion = ?, "
                . "medidor = ?  "
                . "WHERE id_ducto = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("issdssssssi",
                    $objectVO->getTipo_ducto(),
                    $objectVO->getClave_identificacion_ducto(),
                    $objectVO->getDescripcion_ducto(),
                    $objectVO->getDiametro_ducto(),
                    $objectVO->getDescripcion_tipo_ducto(),
                    $objectVO->getCve_producto_sat_ducto(),
                    $objectVO->getAlmacenamiento_ducto(),
                    $objectVO->getVigencia_calibracion_ducto(),
                    $objectVO->getSistema_medicion(),
                    $objectVO->getMedidor(),
                    $objectVO->getId_ducto()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}
