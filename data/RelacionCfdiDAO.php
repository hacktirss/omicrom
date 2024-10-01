<?php

/**
 * Description of RelacionCfdiDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Ayala Gonzalez Alejandro
 * @version 1.0
 * @since feb 2023
 */
include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('BasicEnum.php');
include_once ('RelacionCfdiVO.php');

class RelacionCfdiDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "relacion_cfdi";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \RelacionCfdiVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO = RelacionCfdiVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "serie,"
                . "folio_factura,"
                . "origen,"
                . "uuid,"
                . "uuid_relacionado,"
                . "tipo_relacion,"
                . "importe,"
                . "id_fc) "
                . "VALUES(?, ?, ?, ?, ?, ?, ?, ?)";

        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssi",
                    $objectVO->getSerie(),
                    $objectVO->getFolio_factura(),
                    $objectVO->getOrigen(),
                    $objectVO->getUuid(),
                    $objectVO->getUuid_relacionado(),
                    $objectVO->getTipo_relacion(),
                    $objectVO->getImporte(),
                    $objectVO->getId_fc()
            );
            if ($ps->execute()) {
                $id = $ps->insert_id;
                $ps->close();
                return $id;
            } else {
                error_log($ps->error);
                error_log($this->conn->info);
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
     * @return \RelacionCfdiVO
     */
    public function fillObject($rs) {
        $objectVO = new RelacionCfdiVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setSerie($rs["serie"]);
            $objectVO->setFolio_factura($rs["folio_factura"]);
            $objectVO->setOrigen($rs["origen"]);
            $objectVO->setUuid($rs["uuid"]);
            $objectVO->setUuid_relacionado($rs["uuid_relacionado"]);
            $objectVO->setTipo_relacion($rs["tipo_relacion"]);
            $objectVO->setImporte($rs["importe"]);
            $objectVO->setId_fc($rs["id_fc"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \RelacionCfdiVO
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
     * @return \RelacionCfdiVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new RelacionCfdiVO();
        $sql = "SELECT " . self::TABLA . ".*"
                . "FROM " . self::TABLA . " WHERE " . $field . " = '" . $idObjectVO . "'";
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
     * @param \RelacionCfdiVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = RelacionCfdiVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "origen = ?, "
                . "uuid = ?, "
                . "uuid_relacionado = ?, "
                . "tipo_relacion = ? ,"
                . "serie = ?, "
                . "folio_factura = ?, "
                . "importe = ?, "
                . "id_fc = ? "
                . "WHERE id = ?  ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssii",
                    $objectVO->getOrigen(),
                    $objectVO->getUuid(),
                    $objectVO->getUuid_relacionado(),
                    $objectVO->getTipo_relacion(),
                    $objectVO->getSerie(),
                    $objectVO->getFolio_factura(),
                    $objectVO->getImporte(),
                    $objectVO->getId_fc(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

    public function liberaUuid($Uuid) {
        $buscaRelacion = "SELECT id,uuid_relacionado FROM relacion_cfdi WHERE uuid = '$Uuid';";
        if (($query = $this->conn->query($buscaRelacion)) && ($rs = $query->fetch_assoc())) {
            if ($rs["id"] > 0) {
                $UuidX = substr($Uuid, 0, 7) . "XX" . substr($Uuid, 9);
                $UuidR = substr($rs["uuid_relacionado"], 0, 7) . "XX" . substr($rs["uuid_relacionado"], 9);
                $InsertBe = "INSERT INTO bitacora_eventos (fecha_evento,hora_evento,usuario,tipo_evento,descripcion_evento,ip_evento,query_str,numero_alarma,mac) "
                        . "VALUES ('" . date("Y-m-d") . "','" . date("H:i:s") . "','liberacion','ADM','Uuid :$Uuid a $UuidX - " . $rs["uuid_relacionado"] . " a  $UuidR','-','-','0','-')";
                error_log($InsertBe);
                if ($this->conn->query($InsertBe)) {
                    $Update = "UPDATE relacion_cfdi SET uuid ='$UuidX', uuid_relacionado='" . $UuidR . "' WHERE id = " . $rs["id"];
                    error_log($Update);
                    if (!$this->conn->query($Update)) {
                        error_log($this->conn->error);
                    }
                } else {
                    error_log($this->conn->error);
                }
            }
        }
    }

}

abstract class StatusPagoProveedor extends BasicEnum {

    const ABIERTO = "Abierta";
    const CERRADO = "Cerrada";
    const CANCELADO = "Cancelado";

}
