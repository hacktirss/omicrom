<?php

/**
 * Description of ListasDAO
 * omicrom®
 * © 2021, Detisa 
 * http://www.detisa.com.mx
 * @author Alejandro Ayala Gonzalez
 * @version 1.0
 * @since mar
 */
include_once ('mysqlUtils.php');
include_once ('ListasVO.php');
include_once ('FunctionsDAO.php');

class ListasDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "listas";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \ListasVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "nombre_lista, "
                . "descripcion_lista, "
                . "default_lista, "
                . "tipo_dato_lista, "
                . "longitud_lista, "
                . "estado_lista, "
                . "mayus_lista, "
                . "min_lista, "
                . "max_lista "
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssss",
                    $objectVO->getNombre_lista(),
                    $objectVO->getDescripcion_lista(),
                    $objectVO->getDefault_lista(),
                    $objectVO->getTipo_dato_lista(),
                    $objectVO->getLongitud_lista(),
                    $objectVO->getEstado_lista(),
                    $objectVO->getMayus_lista(),
                    $objectVO->getMin_lista(),
                    $objectVO->getMax_lista()
            );
            $id = $ps->execute() ? $ps->insert_id : -1;
            error_log(mysqli_error($this->conn));
            $ps->close();
            return $id;
        }
        return 0;
    }

    /**
     * 
     * @param array() $rs
     * @return \ListasVO
     */
    public function fillObject($rs) {
        $objectVO = new ListasVO();
        if (is_array($rs)) {
            $objectVO->setId_lista($rs["id_lista"]);
            $objectVO->setNombre_lista($rs["nombre_lista"]);
            $objectVO->setDescripcion_lista($rs["descripcion_lista"]);
            $objectVO->setDefault_lista($rs["default_lista"]);
            $objectVO->setTipo_dato_lista($rs["tipo_dato_lista"]);
            $objectVO->setLongitud_lista($rs["longitud_lista"]);
            $objectVO->setEstado_lista($rs["estado_lista"]);
            $objectVO->setMayus_lista($rs["mayus_lista"]);
            $objectVO->setMin_lista($rs["min_lista"]);
            $objectVO->setMax_lista($rs["max_lista"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param int $idObjectVO Llave primaria o identificador 
     * @param string $field Nombre del campo a buscar
     * @return \ListasVO
     */
    public function retrieve($idObjectVO, $field = "id_lista") {
        $objectVO = new ListasVO();
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
     * @return array Arreglo de objetos \ListasVO
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
    public function remove($idObjectVO, $field = "id_lista") {
        $sql = "DELETE FROM " . self::TABLA . " WHERE " . $field . " = ? LIMIT 1";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("s", $idObjectVO
            );
            return $ps->execute();
        }
    }

    /**
     * 
     * @param \ListasVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "nombre_lista = ?, "
                . "descripcion_lista = ?, "
                . "default_lista = ?, "
                . "tipo_dato_lista = ?, "
                . "longitud_lista = ?, "
                . "estado_lista = ?, "
                . "mayus_lista = ?, "
                . "min_lista = ?, "
                . "max_lista = ? "
                . "WHERE id_lista = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssssi",
                    $objectVO->getNombre_lista(),
                    $objectVO->getDescripcion_lista(),
                    $objectVO->getDefault_lista(),
                    $objectVO->getTipo_dato_lista(),
                    $objectVO->getLongitud_lista(),
                    $objectVO->getEstado_lista(),
                    $objectVO->getMayus_lista(),
                    $objectVO->getMin_lista(),
                    $objectVO->getMax_lista(),
                    $objectVO->getId_lista()
            );
            return $ps->execute();
        }
    }
}
