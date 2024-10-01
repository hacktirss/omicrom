<?php

/**
 * Description of BancosDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('CxcMensualVO.php');

class CxcMensualDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "cxc_mensual";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function _destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \CxcMensualVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "anio,"
                . "mesNo,"
                . "mes,"
                . "importe_deuda,"
                . "id_cli"
                . ") "
                . "VALUES(?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssi",
                    $objectVO->getAnio(),
                    $objectVO->getMesNo(),
                    $objectVO->getMes(),
                    $objectVO->getImporte_deuda(),
                    $objectVO->getId_cli()
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
     * @return \CxcMensualVO
     */
    public function fillObject($rs) {
        $objectVO = new CxcMensualVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setId_cli($rs["id_cli"]);
            $objectVO->setAnio($rs["anio"]);
            $objectVO->setMesNo($rs["mesNo"]);
            $objectVO->setMes($rs["mes"]);
            $objectVO->setFecha_analisis($rs["fecha_analisis"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param \CxcMensualVO $idObjectVO
     * @param string $field Nombre del campo a buscar
     * @return \CxcMensualVO
     */
    public function retrieve($idObjectVO, $field = "id_cli") {
        $objectVO = new CxcMensualVO();
        $sql = "SELECT * FROM " . self::TABLA . " WHERE " . $field . " = " . $idObjectVO->getId_cli() . " AND anio = " . $idObjectVO->getAnio() . " AND mesNo = " . $idObjectVO->getMesNo() . "";
        error_log($sql);
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
     * @return  \CxcMensualVO array Arreglo de objetos
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
     * @param \CxcMensualVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = CxcMensualVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "anio = ?, "
                . "mesNo = ?, "
                . "mes = ?, "
                . "importe_deuda = ?, "
                . "fecha_analisis = NOW() "
                . "WHERE id_cli = ? AND anio = ? AND mesNo = ? ";
        error_log($sql);
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssiss",
                    $objectVO->getAnio(),
                    $objectVO->getMesNo(),
                    $objectVO->getMes(),
                    $objectVO->getImporte_deuda(),
                    $objectVO->getId_cli(),
                    $objectVO->getAnio(),
                    $objectVO->getMesNo()
            );
            if ($ps->execute()) {
                return true;
            }
        }
        error_log($this->conn->error);
        return false;
    }

}
