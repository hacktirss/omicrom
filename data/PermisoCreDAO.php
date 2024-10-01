<?php

/**
 * Description of PermisoCreDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
include_once ('mysqlUtils.php');
include_once ('PermisoCreVO.php');
include_once ('FunctionsDAO.php');

class PermisoCreDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "permisos_cre";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function _destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \PermisoCreVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "catalogo, "
                . "llave, "
                . "permiso, "
                . "descripcion, "
                . "padre, "
                . "estado"
                . ") "
                . "VALUES(?,?,?,?,?,?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssii",
                    $objectVO->getCatalogo(),
                    $objectVO->getLlave(),
                    $objectVO->getPermiso(),
                    $objectVO->getDescripcion(),
                    $objectVO->getPadre(),
                    $objectVO->getEstado()
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
     * @return \PermisoCreVO
     */
    public function fillObject($rs) {
        $objectVO = new PermisoCreVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setCatalogo($rs["catalogo"]);
            $objectVO->setLlave($rs["llave"]);
            $objectVO->setPermiso($rs["permiso"]);
            $objectVO->setDescripcion($rs["descripcion"]);
            $objectVO->setPadre($rs["padre"]);
            $objectVO->setEstado($rs["estado"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param int $idObjectVO Llave primaria o identificador 
     * @param string $field Nombre del campo a buscar
     * @return \PermisoCreVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new PermisoCreVO();
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
     * @return array Arreglo de objetos \PermisoCreVO
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
     * @param \PermisoCreVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = PermisoCreVO) {
        //$objectVO = new PermisoCreVO();
        $sql = "UPDATE " . self::TABLA . " SET "
                . "catalogo = ?, "
                . "llave = ?, "
                . "permiso = ?, "
                . "descripcion = ?, "
                . "padre = ?, "
                . "estado = ? "
                . "WHERE id = ?";
        //error_log($sql);
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssiii",
                    $objectVO->getCatalogo(),
                    $objectVO->getLlave(),
                    $objectVO->getPermiso(),
                    $objectVO->getDescripcion(),
                    $objectVO->getPadre(),
                    $objectVO->getEstado(),
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

abstract class StatusPermiso extends BasicEnum {

    const ACTIVO = 1;
    const INACTIVO = 0;

}
