<?php

/**
 * Description of DictamenDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
include_once ('mysqlUtils.php');
include_once ('DictamenVO.php');
include_once ('DictamenDVO.php');
include_once ('FunctionsDAO.php');

class DictamenDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "dictamen";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function _destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \DictamenVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "proveedor, "
                . "lote, "
                . "numeroFolio, "
                . "fechaEmision, "
                . "resultado, "
                . "noCarga"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("isssss",
                    $objectVO->getProveedor(),
                    $objectVO->getLote(),
                    $objectVO->getNumerofolio(),
                    $objectVO->getFechaemision(),
                    $objectVO->getResultado(),
                    $objectVO->getNoCarga() 
            );
            if ($ps->execute()) {
                $id = $ps->insert_id;
                $this->insertDetalle($id);
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
     * @return \DictamenVO
     */
    public function fillObject($rs) {
        $objectVO = new DictamenVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setProveedor($rs["proveedor"]);
            $objectVO->setLote($rs["lote"]);
            $objectVO->setNumerofolio($rs["numeroFolio"]);
            $objectVO->setFechaemision($rs["fechaEmision"]);
            $objectVO->setResultado($rs["resultado"]);
            $objectVO->setNoCarga($rs["noCarga"]);
            $objectVO->setEstado($rs["estado"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param array() $rs
     * @return \DictamenDVO
     */
    public function fillObjectD($rs) {
        $objectVO = new DictamenDVO();
        //error_log(print_r($objectVO));
        if (is_array($rs)) {
            $objectVO->setIdnvo($rs["idnvo"]);
            $objectVO->setId($rs["id"]);
            $objectVO->setTanque($rs["tanque"]);
            $objectVO->setComp_azufre($rs["comp_azufre"]);
            $objectVO->setFraccion_molar($rs["fraccion_molar"]);
            $objectVO->setPoder_calorifico($rs["poder_calorifico"]);
            $objectVO->setComp_octanaje($rs["comp_octanaje"]);
            $objectVO->setComp_etanol($rs["comp_etanol"]);
            $objectVO->setCve_producto_sat($rs["cve_producto_sat"]);
            $objectVO->setGravedad_especifica($rs["gravedad_especifica"]);
            $objectVO->setComp_fosil($rs["comp_fosil"]);
            $objectVO->setComp_propano($rs["comp_propano"]);
            $objectVO->setComp_butano($rs["comp_butano"]);
            $objectVO->setClave_instalacion($rs["clave_instalacion"]);
            $objectVO->setContiene_fosil($rs["contiene_fosil"]);
        }

        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \DictamenVO
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
     * @param string $field Nombre del campo a buscar
     * @return \DictamenVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new DictamenVO();
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
     * @param int $idObjectVO Llave primaria o identificador 
     * @param string $field Nombre del campo a buscar
     * @return \DictamenDVO
     */
    public function retrieveD($idObjectVO, $field = "id", $fields = "*") {
        $objectVO = new DictamenDVO();
        $sql = "SELECT $fields FROM " . self::TABLA . "d LEFT JOIN tanques ON dictamend.tanque=tanques.id "
                . "LEFT JOIN com ON tanques.clave_producto = com.clave  LEFT JOIN cia ON TRUE WHERE " . $field . " = " . $idObjectVO;
        //error_log($sql);
        if (($query = $this->conn->query($sql)) && ($rs = $query->fetch_assoc())) {
            $objectVO = $this->fillObjectD($rs);
            //error_log(print_r($rs));
            return $objectVO;
        } else {
            error_log($this->conn->error);
        }
        return $objectVO;
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
     * @param string $field Nombre del campo para borrar
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function removeD($idObjectVO, $field = "id") {
        $sql = "DELETE FROM " . self::TABLA . "d WHERE " . $field . " = ? LIMIT 1";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("s", $idObjectVO
            );
            return $ps->execute();
        }
    }

    /**
     * 
     * @param \DictamenVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO) {
        //$objectVO = new DictamenVO();
        $sql = "UPDATE " . self::TABLA . " SET "
                . "proveedor = ?, "
                . "lote = ?, "
                . "numeroFolio = ?, "
                . "fechaEmision = ?, "
                . "resultado = ?, "
                . "estado = ?, "
                . "noCarga = ? "
                . "WHERE id = ?";
        //error_log($sql);
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("issssssi",
                    $objectVO->getProveedor(),
                    $objectVO->getLote(),
                    $objectVO->getNumerofolio(),
                    $objectVO->getFechaemision(),
                    $objectVO->getResultado(),
                    $objectVO->getEstado(),
                    $objectVO->getNoCarga(),
                    $objectVO->getId()
            );
            if ($ps->execute()) {
                return true;
            }
        }
        error_log($this->conn->error);
        return false;
    }

    /**
     * 
     * @param \DictamenDVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function updateD($objectVO) {
        //$objectVO = new DictamenVO();
        $sql = "UPDATE dictamend SET "
                . "comp_azufre = ?, "
                . "fraccion_molar = ?, "
                . "poder_calorifico = ?,"
                . "comp_octanaje = ?, "
                . "comp_etanol = ?, "
                . "gravedad_especifica = ?, "
                . "comp_fosil = ?, "
                . "comp_propano = ?, "
                . "comp_butano = ?,"
                . "contiene_fosil = ? "
                . "WHERE idnvo = ?;";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("dddddddddsi",
                    $objectVO->getComp_azufre(),
                    $objectVO->getFraccion_molar(),
                    $objectVO->getPoder_calorifico(),
                    $objectVO->getComp_octanaje(),
                    $objectVO->getComp_etanol(),
                    $objectVO->getGravedad_especifica(),
                    $objectVO->getComp_fosil(),
                    $objectVO->getComp_propano(),
                    $objectVO->getComp_butano(),
                    $objectVO->getContiene_fosil(),
                    $objectVO->getIdnvo());
            if ($ps->execute()) {
                return true;
            }
        }
        error_log($this->conn->error);
        return false;
    }

    public function insertDetalle($id) {
        $insetDetalle = "
                INSERT INTO dictamend (id, tanque)
                SELECT dictamen.id, tanques.tanque
                FROM dictamen, tanques 
                WHERE TRUE 
                AND tanques.estado = '1' AND dictamen.id = ?;";

        if (($ps = $this->conn->prepare($insetDetalle))) {
            $ps->bind_param("i", $id);
            if ($ps->execute()) {
                return true;
            }
        }
        error_log($this->conn->error);
        return false;
    }

}
