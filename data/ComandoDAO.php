<?php

/**
 * Description of ComandoDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('ComandoVO.php');

class ComandoDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "comandos";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \ComandoVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "posicion,"
                . "manguera,"
                . "comando,"
                . "fecha_insercion,"
                . "fecha_programada,"
                . "intentos,"
                . "ejecucion,"
                . "descripcion,"
                . "idtarea,"
                . "replica"
                . ") "
                . "VALUES(?, ?, ?, NOW(), ?, 0 ,0, ?, ?, 0)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssss",
                    $objectVO->getPosicion(),
                    $objectVO->getManguera(),
                    $objectVO->getComando(),
                    $objectVO->getFecha_programada(),
                    $objectVO->getDescripcion(),
                    $objectVO->getIdtarea()
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
     * @return \ComandoVO
     */
    public function fillObject($rs) {
        $objectVO = new ComandoVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setPosicion($rs["posicion"]);
            $objectVO->setManguera($rs["manguera"]);
            $objectVO->setComando($rs["comando"]);
            $objectVO->setFecha_insercion($rs["fecha_insercion"]);
            $objectVO->setFecha_programada($rs["fecha_programada"]);
            $objectVO->setFecha_ejecucion($rs["fecha_ejecucion"]);
            $objectVO->setIntentos($rs["intentos"]);
            $objectVO->setEjecucion($rs["ejecucion"]);
            $objectVO->setDescripcion($rs["descripcion"]);
            $objectVO->setIdtarea($rs["idtarea"]);
            $objectVO->setReplica($rs["replica"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \ComandoVO
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
     * @return \ComandoVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new ComandoVO();
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
     * @param \ComandoVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "posicion = ?, "
                . "manguera = ?, "
                . "comando = ?, "
                . "fecha_insercion = ?, "
                . "fecha_programada = ?, "
                . "fecha_ejecucion = ?, "
                . "intentos = ?, "
                . "ejecucion = ?, "
                . "descripcion = ?, "
                . "idtarea = ?, "
                . "replica = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssssssi",
                    $objectVO->getPosicion(),
                    $objectVO->getManguera(),
                    $objectVO->getComando(),
                    $objectVO->getFecha_insercion(),
                    $objectVO->getFecha_programada(),
                    $objectVO->getFecha_ejecucion(),
                    $objectVO->getIntentos(),
                    $objectVO->getEjecucion(),
                    $objectVO->getDescripcion(),
                    $objectVO->getIdtarea(),
                    $objectVO->getReplica(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}
