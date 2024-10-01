<?php

/**
 * Description of CargasDAO
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
include_once ('CargasVO.php');

class CargasDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "cargas";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \CargasVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO = CargasVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "tanque,"
                . "producto,"
                . "clave_producto,"
                . "t_inicial,"
                . "vol_inicial,"
                . "fecha_inicio,"
                . "t_final,"
                . "vol_final,"
                . "fecha_fin,"
                . "aumento,"
                . "fecha_insercion,"
                . "entrada,"
                . "inicia_carga,"
                . "finaliza_carga,"
                . "tipo,"
                . "folioenvios,"
                . "enviado,"
                . "vol_doc"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssssssssssssssss",
                    $objectVO->getTanque(),
                    $objectVO->getProducto(),
                    $objectVO->getClave_producto(),
                    $objectVO->getT_inicial(),
                    $objectVO->getVol_inicial(),
                    $objectVO->getFecha_inicio(),
                    $objectVO->getT_final(),
                    $objectVO->getVol_final(),
                    $objectVO->getFecha_fin(),
                    $objectVO->getAumento(),
                    $objectVO->getFecha_insercion(),
                    $objectVO->getEntrada(),
                    $objectVO->getInicia_carga(),
                    $objectVO->getFinaliza_carga(),
                    $objectVO->getTipo(),
                    $objectVO->getFolioenvios(),
                    $objectVO->getEnviado(),
                    $objectVO->getVol_doc()
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
     * @return \CargasVO
     */
    public function fillObject($rs) {
        $objectVO = new CargasVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setTanque($rs["tanque"]);
            $objectVO->setProducto($rs["producto"]);
            $objectVO->setClave_producto($rs["clave_producto"]);
            $objectVO->setT_inicial($rs["t_inicial"]);
            $objectVO->setVol_inicial($rs["vol_inicial"]);
            $objectVO->setFecha_inicio($rs["fecha_inicio"]);
            $objectVO->setT_final($rs["t_final"]);
            $objectVO->setVol_final($rs["vol_final"]);
            $objectVO->setFecha_fin($rs["fecha_fin"]);
            $objectVO->setAumento($rs["aumento"]);
            $objectVO->setFecha_insercion($rs["fecha_insercion"]);
            $objectVO->setEntrada($rs["entrada"]);
            $objectVO->setInicia_carga($rs["inicia_carga"]);
            $objectVO->setFinaliza_carga($rs["finaliza_carga"]);
            $objectVO->setTipo($rs["tipo"]);
            $objectVO->setFolioenvios($rs["folioenvios"]);
            $objectVO->setEnviado($rs["enviado"]);
            $objectVO->setClave($rs["clave"]);
            $objectVO->setVol_doc($rs["vol_doc"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \CargasVO
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
     * @return \CargasVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new CargasVO();
        $sql = "SELECT " . self::TABLA . ".*,inv.id clave FROM " . self::TABLA . " "
                . "LEFT JOIN com ON cargas.clave_producto = com.clave "
                . "LEFT JOIN inv ON com.descripcion = inv.descripcion "
                . "WHERE  " . self::TABLA . "." . $field . " = '" . $idObjectVO . "'";
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
     * @param \CargasVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = CargasVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "tanque = ?, "
                . "producto = ?, "
                . "clave_producto = ?, "
                . "t_inicial = ?, "
                . "vol_inicial = ?, "
                . "fecha_inicio = ?, "
                . "t_final = ?, "
                . "vol_final = ?, "
                . "fecha_fin = ?, "
                . "aumento = ?, "
                . "fecha_insercion = ?, "
                . "entrada = ?, "
                . "inicia_carga = ?, "
                . "finaliza_carga = ?, "
                . "tipo = ?, "
                . "folioenvios = ?, "
                . "enviado = ? ,"
                . "vol_doc = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ssssssssssssssssssi",
                    $objectVO->getTanque(),
                    $objectVO->getProducto(),
                    $objectVO->getClave_producto(),
                    $objectVO->getT_inicial(),
                    $objectVO->getVol_inicial(),
                    $objectVO->getFecha_inicio(),
                    $objectVO->getT_final(),
                    $objectVO->getVol_final(),
                    $objectVO->getFecha_fin(),
                    $objectVO->getAumento(),
                    $objectVO->getFecha_insercion(),
                    $objectVO->getEntrada(),
                    $objectVO->getInicia_carga(),
                    $objectVO->getFinaliza_carga(),
                    $objectVO->getTipo(),
                    $objectVO->getFolioenvios(),
                    $objectVO->getEnviado(),
                    $objectVO->getVol_doc(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}

abstract class TipoCarga extends BasicEnum {

    const NORMAL = "Normal";
    const CONSIGNACION = "Consignacion";
    const JARREO = "Jarreo";

}
