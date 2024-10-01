<?php

/**
 * Description of DireccionDAO
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
include_once ('DireccionVO.php');

class DireccionDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "catalogo_direcciones";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \DireccionVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO = DireccionVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "descripcion,"
                . "calle,"
                . "num_exterior,"
                . "num_interior,"
                . "colonia,"
                . "localidad,"
                . "municipio,"
                . "estado,"
                . "codigo_postal,"
                . "tabla_origen,"
                . "id_origen"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssssss",
                    $objectVO->getDescripcion(),
                    $objectVO->getCalle(),
                    $objectVO->getNum_exterior(),
                    $objectVO->getNum_interior(),
                    $objectVO->getColonia(),
                    $objectVO->getLocalidad(),
                    $objectVO->getMunicipio(),
                    $objectVO->getEstado(),
                    $objectVO->getCodigo_postal(),
                    $objectVO->getTabla_origen(),
                    $objectVO->getId_origen()
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
     * @return \DireccionVO
     */
    public function fillObject($rs) {
        $objectVO = new DireccionVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setDescripcion($rs["descripcion"]);
            $objectVO->setCalle($rs["calle"]);
            $objectVO->setNum_exterior($rs["num_exterior"]);
            $objectVO->setNum_interior($rs["num_interior"]);
            $objectVO->setColonia($rs["colonia"]);
            $objectVO->setLocalidad($rs["localidad"]);
            $objectVO->setMunicipio($rs["municipio"]);
            $objectVO->setEstado($rs["estado"]);
            $objectVO->setCodigo_postal($rs["codigo_postal"]);
            $objectVO->setTabla_origen($rs["tabla_origen"]);
            $objectVO->setId_origen($rs["id_origen"]);
        }
        error_log("El valor de objectVO en direcciones : " . print_r($objectVO,true));
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \DireccionVO
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
        error_log("El valor de delete : " . $sql);
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
     * @return \DireccionVO
     */
    public function retrieve($idObjectVO, $field = "id", $Opcion = "") {
        $objectVO = new DireccionVO();
        $sql = "SELECT * FROM " . self::TABLA . " WHERE " . $field . " = '" . $idObjectVO . "' $Opcion";
        if (($query = $this->conn->query($sql)) && ($rs = $query->fetch_assoc())) {
            $objectVO = $this->fillObject($rs);
            return $objectVO;
        } else {
            error_log($this->conn->error);
        }
        return $objectVO;
    }

    /**
     * @param \DireccionVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = DireccionVO) {
        //$objectVO = new DireccionVO();
        $sql = "UPDATE " . self::TABLA . " SET "
                . "descripcion = ?, "
                . "calle = ?, "
                . "num_exterior = ?, "
                . "num_interior = ?, "
                . "colonia = ?, "
                . "localidad = ?, "
                . "municipio = ?, "
                . "estado = ?, "
                . "codigo_postal = ?, "
                . "tabla_origen = ?, "
                . "id_origen = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssssssi",
                    $objectVO->getDescripcion(),
                    $objectVO->getCalle(),
                    $objectVO->getNum_exterior(),
                    $objectVO->getNum_interior(),
                    $objectVO->getColonia(),
                    $objectVO->getLocalidad(),
                    $objectVO->getMunicipio(),
                    $objectVO->getEstado(),
                    $objectVO->getCodigo_postal(),
                    $objectVO->getTabla_origen(),
                    $objectVO->getId_origen(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}
