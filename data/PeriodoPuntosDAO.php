<?php

/**
 * Description of PeriodoPuntosDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Ayala Gonzalez Alejandro
 * @version 1.1
 * @since ene 2023
 */
include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('BasicEnum.php');
include_once ('PeriodoPuntosVO.php');

class PeriodoPuntosDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "periodo_puntos";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \PeriodoPuntosVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "descripcion,"
                . "fecha_inicial,"
                . "fecha_culmina,"
                . "fecha_final,"
                . "activo,"
                . "tipo_periodo,"
                . "tipo_concepto,"
                . "monto_promocion,"
                . "limite_inferior,"
                . "limite_superior,"
                . "producto_promocion,"
                . "factores_producto,"
                . "limites_inferiores"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssssssss",
                    $objectVO->getDescripcion(),
                    $objectVO->getFecha_inicial(),
                    $objectVO->getFecha_culmina(),
                    $objectVO->getFecha_final(),
                    $objectVO->getActivo(),
                    $objectVO->getTipo_periodo(),
                    $objectVO->getTipo_concentrado(),
                    $objectVO->getMonto_promocion(),
                    $objectVO->getLimite_inferior(),
                    $objectVO->getLimite_superior(),
                    $objectVO->getProducto_promocion(),
                    $objectVO->getFactores_producto(),
                    $objectVO->getLimites_inferiores()
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
     * @return \PeriodoPuntosVO
     */
    public function fillObject($rs) {
        $objectVO = new PeriodoPuntosVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setDescripcion($rs["descripcion"]);
            $objectVO->setFecha_inicial($rs["fecha_inicial"]);
            $objectVO->setFecha_final($rs["fecha_final"]);
            $objectVO->setFecha_culmina($rs["fecha_culmina"]);
            $objectVO->setActivo($rs["activo"]);
            $objectVO->setTipo_concentrado($rs["tipo_concepto"]);
            $objectVO->setMonto_promocion($rs["monto_promocion"]);
            $objectVO->setLimite_inferior($rs["limite_inferior"]);
            $objectVO->setLimite_superior($rs["limite_superior"]);
            $objectVO->setTipo_periodo($rs["tipo_periodo"]);
            $objectVO->setProducto_promocion($rs["producto_promocion"]);
            $objectVO->setFactores_producto($rs["factores_producto"]);
            $objectVO->setLimites_inferiores($rs["limites_inferiores"]);
        }
//        error_log(print_r($objectVO, true));
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \PeriodoPuntosVO
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

    /*
     *
     * @param int $idObjectVO Llave primaria o identificador
     * @param string $field

      Nombre del campo a buscar
     * @return \PeriodoPuntosVO
     */

    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new PeriodoPuntosVO();
        $sql = "SELECT * FROM " . self::TABLA . " WHERE " . $field . " = '" . $idObjectVO . "'";
//        error_log($sql);
        if (($query = $this->conn->query($sql)) && ($rs = $query->fetch_assoc())) {
            $objectVO = $this->fillObject($rs);
            return $objectVO;
        } else {
            error_log($this->conn->error);
        }
        return $objectVO;
    }

    public function retrieve_vPuntos() {
        $objectVO = new PeriodoPuntosVO();
        $Sql = "SELECT * FROM periodo_puntos WHERE activo = 1 AND tipo_periodo = 'P' LIMIT 1;";
        if (($query = $this->conn->query($Sql)) && ($rs = $query->fetch_assoc())) {
            $objectVO = $this->fillObject($rs);
            return $objectVO;
        } else {
            error_log($this->conn->error);
        }
        return $objectVO;
    }

    /**
     * 
     * @param \PeriodoPuntosVO $objectVO


     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = PeriodoPuntosVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "descripcion = ?, "
                . "fecha_inicial = ?, "
                . "fecha_culmina = ?, "
                . "fecha_final = ?, "
                . "activo = ?, "
                . "tipo_concepto = ?, "
                . "monto_promocion = ?, "
                . "limite_inferior = ?, "
                . "limite_superior = ? ,"
                . "producto_promocion = ?, "
                . "factores_producto = ? ,"
                . "limites_inferiores = ? "
                . "WHERE id = ? ";

        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssssssssssi",
                    $objectVO->getDescripcion(),
                    $objectVO->getFecha_inicial(),
                    $objectVO->getFecha_culmina(),
                    $objectVO->getFecha_final(),
                    $objectVO->getActivo(),
                    $objectVO->getTipo_concentrado(),
                    $objectVO->getMonto_promocion(),
                    $objectVO->getLimite_inferior(),
                    $objectVO->getLimite_superior(),
                    $objectVO->getProducto_promocion(),
                    $objectVO->getFactores_producto(),
                    $objectVO->getLimites_inferiores(),
                    $objectVO->getId()
            );
            if ($ps->execute()) {
                error_log(print_r($objectVO, true));
                return true;
            }
        }
        error_log($this->conn->error);
        return false;
    }

}

abstract class TiposCobroDeBeneficio extends BasicEnum {

    const Importe = "I";
    const Volumen = "V";

}

abstract class TiposDeBeneficio extends BasicEnum {

    const P = "Puntos";
    const A = "Acumulativo";
    const C = "Consumos";

}
