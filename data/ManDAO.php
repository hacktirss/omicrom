<?php

/**
 * Description of ManDAO
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
include_once ('ManVO.php');

class ManDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "man";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \ManVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO = ManVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "posicion,"
                . "productos,"
                . "activo,"
                . "lado,"
                . "isla,"
                . "isla_pos,"
                . "despachador,"
                . "man,"
                . "inventario,"
                . "dispensario,"
                . "numventas,"
                . "conteoventas,"
                . "despachadorsig"
                . ") "
                . "VALUES(?,?,?,?,?,?,?,?,?,?,?,?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssssssss",
                    $objectVO->getPosicion(),
                    $objectVO->getProductos(),
                    $objectVO->getActivo(),
                    $objectVO->getLado(),
                    $objectVO->getIsla(),
                    $objectVO->getIsla_pos(),
                    $objectVO->getDespachador(),
                    $objectVO->getMan(),
                    $objectVO->getInventario(),
                    $objectVO->getDispensario(),
                    $objectVO->getNumventas(),
                    $objectVO->getConteoventas(),
                    $objectVO->getDespachadorsig()
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
     * @return \ManVO
     */
    public function fillObject($rs) {
        $objectVO = new ManVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setPosicion($rs["posicion"]);
            $objectVO->setProductos($rs["productos"]);
            $objectVO->setActivo($rs["activo"]);
            $objectVO->setLado($rs["lado"]);
            $objectVO->setIsla($rs["isla"]);
            $objectVO->setIsla_pos($rs["isla_pos"]);
            $objectVO->setDespachador($rs["despachador"]);
            $objectVO->setMan($rs["man"]);
            $objectVO->setInventario($rs["inventario"]);
            $objectVO->setDispensario($rs["dispensario"]);
            $objectVO->setNumventas($rs["numventas"]);
            $objectVO->setConteoventas($rs["conteoventas"]);
            $objectVO->setDespachadorsig($rs["despachadorsig"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \ManVO
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
     * @return \ManVO
     */
    public function retrieve($idObjectVO, $field = "id", $activos = true) {
        $objectVO = new ManVO();
        $sql = "SELECT " . self::TABLA . ".* FROM " . self::TABLA . " "
                . "WHERE " . self::TABLA . "." . $field . " = '" . $idObjectVO . "'";
        if ($activos) {
            $sql .= " AND " . self::TABLA . ".activo = 'Si'";
        }
        $sql .= " LIMIT 1;";
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
     * @param \ManVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = ManVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "posicion = ?, "
                . "productos = ?, "
                . "activo = ?, "
                . "lado = ?, "
                . "isla = ?, "
                . "isla_pos = ?, "
                . "despachador = ?, "
                . "man = ?, "
                . "inventario = ?, "
                . "dispensario = ?, "
                . "numventas = ?, "
                . "conteoventas = ?, "
                . "despachadorsig = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("iissiiiisiiiii",
                    $objectVO->getPosicion(),
                    $objectVO->getProductos(),
                    $objectVO->getActivo(),
                    $objectVO->getLado(),
                    $objectVO->getIsla(),
                    $objectVO->getIsla_pos(),
                    $objectVO->getDespachador(),
                    $objectVO->getMan(),
                    $objectVO->getInventario(),
                    $objectVO->getDispensario(),
                    $objectVO->getNumventas(),
                    $objectVO->getConteoventas(),
                    $objectVO->getDespachadorsig(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}

abstract class StatusMan extends BasicEnum {
    const ACTIVO = "Si";
    const INACTIVO = "No";
}
