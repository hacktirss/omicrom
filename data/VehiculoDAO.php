<?php

/**
 * Description of VehiculoDAO
 * omicrom®
 * © 2022, Detisa 
 * http://www.detisa.com.mx
 * @author Alan Rodriguez 
 * @version 1.0
 * @since feb 2022
 */
include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('BasicEnum.php');
include_once ('VehiculoVO.php');

class VehiculoDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "catalogo_vehiculos";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \VehiculoVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO = VehiculoVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "descripcion,"
                . "conf_vehicular,"
                . "placa,"
                . "anio_modelo,"
                . "subtipo_remolque,"
                . "placa_remolque,"
                . "permiso_sct,"
                . "numero_sct,"
                . "nombre_aseguradora,"
                . "numero_seguro,"
                . "tipo_figura"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssssss",
                    $objectVO->getDescripcion(),
                    $objectVO->getConf_vehicular(),
                    $objectVO->getPlaca(),
                    $objectVO->getAnio_modelo(),
                    $objectVO->getSubtipo_remolque(),
                    $objectVO->getPlaca_remolque(),
                    $objectVO->getPermiso_sct(),
                    $objectVO->getNumero_sct(),
                    $objectVO->getNombre_aseguradora(),
                    $objectVO->getNumero_seguro(),
                    $objectVO->getTipo_figura()
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
     * @return \VehiculoVO
     */
    public function fillObject($rs) {
        $objectVO = new VehiculoVO();
        if (is_array($rs)) {
            $objectVO->setDescripcion($rs["descripcion"]);
            $objectVO->setId($rs["id"]);
            $objectVO->setConf_vehicular($rs["conf_vehicular"]);
            $objectVO->setPlaca($rs["placa"]);
            $objectVO->setAnio_modelo($rs["anio_modelo"]);
            $objectVO->setSubtipo_remolque($rs["subtipo_remolque"]);
            $objectVO->setPlaca_remolque($rs["placa_remolque"]);
            $objectVO->setPermiso_sct($rs["permiso_sct"]);
            $objectVO->setNumero_sct($rs["numero_sct"]);
            $objectVO->setNombre_aseguradora($rs["nombre_aseguradora"]);
            $objectVO->setNumero_seguro($rs["numero_seguro"]);
            $objectVO->setTipo_figura($rs["tipo_figura"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \VehiculoVO
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
     * @return \VehiculoVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new VehiculoVO();
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
     * @param \VehiculoVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = VehiculoVO) {
        //$objectVO = new VehiculoVO();
        $sql = "UPDATE " . self::TABLA . " SET "
                . "descripcion = ?, "
                . "conf_vehicular = ?, "
                . "placa = ?, "
                . "anio_modelo = ?, "
                . "subtipo_remolque = ?, "
                . "placa_remolque = ?, "
                . "permiso_sct = ?, "
                . "numero_sct = ?, "
                . "nombre_aseguradora = ?, "
                . "numero_seguro = ?, "
                . "tipo_figura = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssssssi",
                    $objectVO->getDescripcion(),
                    $objectVO->getConf_vehicular(),
                    $objectVO->getPlaca(),
                    $objectVO->getAnio_modelo(),
                    $objectVO->getSubtipo_remolque(),
                    $objectVO->getPlaca_remolque(),
                    $objectVO->getPermiso_sct(),
                    $objectVO->getNumero_sct(),
                    $objectVO->getNombre_aseguradora(),
                    $objectVO->getNumero_seguro(),
                    $objectVO->getTipo_figura(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}
