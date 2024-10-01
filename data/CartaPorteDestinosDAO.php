<?php

/**
 * Description of CartaPorteDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Alan Rodríguez
 * @version 1.0
 * @since dic 2021
 */
include_once ("mysqlUtils.php");
include_once ("FunctionsDAO.php");
include_once ("CartaPorteDestinosVO.php");

class CartaPorteDestinosDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "carta_porte_destinos";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \CartaPorteDestinosVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "rfcDestinatario,"
                . "nombreDestinatario,"
                . "calle,"
                . "no_ext,"
                . "no_int,"
                . "colonia,"
                . "localidad,"
                . "referencia,"
                . "municipio,"
                . "estado,"
                . "pais,"
                . "codigo_postal,"
                . "origenDestino"
                . ") "
                . "VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)";

        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssssssss",
                    $objectVO->getRfcDestinatario(),
                    $objectVO->getNombreDestinatario(),
                    $objectVO->getCalle(),
                    $objectVO->getNo_ext(),
                    $objectVO->getNo_int(),
                    $objectVO->getColonia(),
                    $objectVO->getLocalidad(),
                    $objectVO->getReferencia(),
                    $objectVO->getMunicipio(),
                    $objectVO->getEstado(),
                    $objectVO->getPais(),
                    $objectVO->getCodigo_postal(),
                    $objectVO->getOrigenDestino()
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
     * @return \CartaPorteDestinosVO
     */
    public function fillObject($rs) {
        $objectVO = new CartaPorteDestinosVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setRfcDestinatario($rs["rfcDestinatario"]);
            $objectVO->setNombreDestinatario($rs["nombreDestinatario"]);
            $objectVO->setCalle($rs["calle"]);
            $objectVO->setNo_ext($rs["no_ext"]);
            $objectVO->setNo_int($rs["no_int"]);
            $objectVO->setColonia($rs["colonia"]);
            $objectVO->setLocalidad($rs["localidad"]);
            $objectVO->setReferencia($rs["referencia"]);
            $objectVO->setMunicipio($rs["municipio"]);
            $objectVO->setEstado($rs["estado"]);
            $objectVO->setPais($rs["pais"]);
            $objectVO->setCodigo_postal($rs["codigo_postal"]);
            $objectVO->setOrigenDestino($rs["origenDestino"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \CartaPorteVO
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
     * @return \CartaPorteVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new CartaPorteVO();
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
     * @param \CartaPorteDestinosVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "rfcDestinatario = ?, "
                . "nombreDestinatario = ?, "
                . "calle = ?, "
                . "no_ext = ?, "
                . "no_int = ?, "
                . "colonia = ?, "
                . "localidad = ?, "
                . "referencia = ?, "
                . "municipio = ?, "
                . "estado = ?, "
                . "pais = ?, "
                . "codigo_postal = ?, "
                . "origenDestino = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssssssssi",
                    $objectVO->getRfcDestinatario(),
                    $objectVO->getNombreDestinatario(),
                    $objectVO->getCalle(),
                    $objectVO->getNo_ext(),
                    $objectVO->getNo_int(),
                    $objectVO->getColonia(),
                    $objectVO->getLocalidad(),
                    $objectVO->getReferencia(),
                    $objectVO->getMunicipio(),
                    $objectVO->getEstado(),
                    $objectVO->getPais(),
                    $objectVO->getCodigo_postal(),
                    $objectVO->getOrigenDestino(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}
