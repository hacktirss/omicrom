<?php

/**
 * Description of UsoGeneralDAO
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
include_once ('UsoGeneralVO.php');

class UsoGeneralDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "catalogo_universal";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \UsoGeneralVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "nombre_catalogo,"
                . "descripcion,"
                . "llave,"
                . "descripcion_llave,"
                . "valor,"
                . "valor_2,"
                . "valor_3,"
                . "valor_4,"
                . "contrasenia"
                . ") "
                . "VALUES(UPPER(?), UPPER(?), ?, ?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssss",
                    $objectVO->getNombre_catalogo(),
                    $objectVO->getDescripcion(),
                    $objectVO->getLlave(),
                    $objectVO->getDescripcion_llave(),
                    $objectVO->getValor(),
                    $objectVO->getValor_2(),
                    $objectVO->getValor_3(),
                    $objectVO->getValor_4(),
                    $objectVO->getContrasenia()
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
     * @return \UsoGeneralVO
     */
    public function fillObject($rs) {
        $objectVO = new UsoGeneralVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id_catalogo"]);
            $objectVO->setNombre_catalogo($rs["nombre_catalogo"]);
            $objectVO->setDescripcion($rs["descripcion"]);
            $objectVO->setLlave($rs["llave"]);
            $objectVO->setDescripcion_llave($rs["descripcion_llave"]);
            $objectVO->setValor($rs["valor"]);
            $objectVO->setValor_2($rs["valor_2"]);
            $objectVO->setValor_3($rs["valor_3"]);
            $objectVO->setValor_4($rs["valor_4"]);
            $objectVO->setContrasenia($rs["contrasenia"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \UsoGeneralVO
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
     * @return \UsoGeneralVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new UsoGeneralVO();
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
     * @param \UsoGeneralVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "nombre_catalogo = ?, "
                . "descripcion = ?, "
                . "llave = ?, "
                . "descripcion_llave = ?, "
                . "valor = ?, "
                . "valor_2 = ?, "
                . "valor_3 = ?, "
                . "valor_4 = ?, "
                . "contrasenia = ? "
                . "WHERE id_catalogo = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssssi",
                    $objectVO->getNombre_catalogo(),
                    $objectVO->getDescripcion(),
                    $objectVO->getLlave(),
                    $objectVO->getDescripcion_llave(),
                    $objectVO->getValor(),
                    $objectVO->getValor_2(),
                    $objectVO->getValor_3(),
                    $objectVO->getValor_4(),
                    $objectVO->getContrasenia(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}
