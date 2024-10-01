<?php

/**
 * Description of ListasValorDAO
 * omicrom®
 * © 2021, Detisa 
 * http://www.detisa.com.mx
 * @author Alejandro Ayala Gonzalez
 * @version 1.0
 * @since mar 2021
 */
include_once ('mysqlUtils.php');
include_once ('ListasValorVO.php');
include_once ('FunctionsDAO.php');

class ListasValorDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "listas_valor";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param int $idObjectVO Llave primaria o identificador 
     * @param string $field Nombre del campo a buscar
     * @return \ListasValorVO
     */
    public function retrieve($idObjectVO, $field = "id_lista_valor") {
        $objectVO = new ListasValorVO();
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
     * @param \ListasValorVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "llave_lista_valor, "
                . "valor_lista_valor, "
                . "estado_lista_valor, "
                . "alarma_lista_valor, "
                . "id_lista_lista_valor "
                . ") "
                . "VALUES(?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssss",
                    $objectVO->getLlave_lista_valor(),
                    $objectVO->getValor_lista_valor(),
                    $objectVO->getEstado_lista_valor(),
                    $objectVO->getAlarma_lista_valor(),
                    $objectVO->getId_lista_lista_valor()
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
     * @param \ListasValorVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO) {
        $sql = "UPDATE listas_valor SET "
                . "llave_lista_valor = ?, "
                . "valor_lista_valor = ?, "
                . "estado_lista_valor = ?, "
                . "alarma_lista_valor = ?, "
                . "id_lista_lista_valor = ? "
                . "WHERE id_lista_valor = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssss",
                    $objectVO->getLlave_lista_valor(),
                    $objectVO->getValor_lista_valor(),
                    $objectVO->getEstado_lista_valor(),
                    $objectVO->getAlarma_lista_valor(),
                    $objectVO->getId_lista_lista_valor(),
                    $objectVO->getId_lista_valor()
            );
            return $ps->execute();
        }
    }

    /**
     * 
     * @param int $idObjectVO Llave primaria o identificador 
     * @param string $field Nombre del campo para borrar
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function remove($idObjectVO, $field = "id_lista_valor") {
        $sql = "DELETE FROM " . self::TABLA . " WHERE " . $field . " = ? LIMIT 1";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("s", $idObjectVO
            );
            return $ps->execute();
        }
    }

    /**
     * 
     * @param array() $rs
     * @return \ListasValorVO
     */
    public function fillObject($rs) {
        $objectVO = new ListasValorVO();
        if (is_array($rs)) {
            $objectVO->setId_lista_valor($rs["id_lista_valor"]);
            $objectVO->setLlave_lista_valor($rs["llave_lista_valor"]);
            $objectVO->setValor_lista_valor($rs["valor_lista_valor"]);
            $objectVO->setEstado_lista_valor($rs["estado_lista_valor"]);
            $objectVO->setAlarma_lista_valor($rs["alarma_lista_valor"]);
            $objectVO->setId_lista_lista_valor($rs["id_lista_lista_valor"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \ListasValorVO
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

}
