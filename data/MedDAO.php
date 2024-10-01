<?php

/**
 * Description of MedDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('MedVO.php');

class MedDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "med";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \MeVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO = MeVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "tanque,"
                . "fecha,"
                . "fechae,"
                . "proveedor,"
                . "producto,"
                . "vol_inicial,"
                . "vol_final,"
                . "fechafac,"
                . "foliofac,"
                . "volumenfac,"
                . "terminal,"
                . "clavevehiculo,"
                . "documento,"
                . "status,"
                . "entcombustible,"
                . "facturas,"
                . "preciou,"
                . "importefac,"
                . "carga,"
                . "cuadrada,"
                . "tipo,"
                . "t_final,"
                . "incremento,"
                . "horaincremento,"
                . "enviado,"
                . "folioenvios,"
                . "proveedorTransporte"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssssssssssssssssssssss",
                    $objectVO->getTanque(),
                    $objectVO->getFecha(),
                    $objectVO->getFechae(),
                    $objectVO->getProveedor(),
                    $objectVO->getProducto(),
                    $objectVO->getVol_inicial(),
                    $objectVO->getVol_final(),
                    $objectVO->getFechafac(),
                    $objectVO->getFoliofac(),
                    $objectVO->getVolumenfac(),
                    $objectVO->getTerminal(),
                    $objectVO->getClavevehiculo(),
                    $objectVO->getDocumento(),
                    $objectVO->getStatus(),
                    $objectVO->getEntcombustible(),
                    $objectVO->getFacturas(),
                    $objectVO->getPreciou(),
                    $objectVO->getImportefac(),
                    $objectVO->getCarga(),
                    $objectVO->getCuadrada(),
                    $objectVO->getTipo(),
                    $objectVO->getT_final(),
                    $objectVO->getIncremento(),
                    $objectVO->getHoraincremento(),
                    $objectVO->getEnviado(),
                    $objectVO->getFolioenvios(),
                    $objectVO->getProveedortransporte()
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
     * @return \MeVO
     */
    public function fillObject($rs) {
        $objectVO = new MeVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setTanque($rs["tanque"]);
            $objectVO->setFecha($rs["fecha"]);
            $objectVO->setFechae($rs["fechae"]);
            $objectVO->setProveedor($rs["proveedor"]);
            $objectVO->setProducto($rs["producto"]);
            $objectVO->setVol_inicial($rs["vol_inicial"]);
            $objectVO->setVol_final($rs["vol_final"]);
            $objectVO->setFechafac($rs["fechafac"]);
            $objectVO->setFoliofac($rs["foliofac"]);
            $objectVO->setVolumenfac($rs["volumenfac"]);
            $objectVO->setTerminal($rs["terminal"]);
            $objectVO->setClavevehiculo($rs["clavevehiculo"]);
            $objectVO->setDocumento($rs["documento"]);
            $objectVO->setStatus($rs["status"]);
            $objectVO->setEntcombustible($rs["entcombustible"]);
            $objectVO->setFacturas($rs["facturas"]);
            $objectVO->setPreciou($rs["preciou"]);
            $objectVO->setImportefac($rs["importefac"]);
            $objectVO->setCarga($rs["carga"]);
            $objectVO->setCuadrada($rs["cuadrada"]);
            $objectVO->setTipo($rs["tipo"]);
            $objectVO->setT_final($rs["t_final"]);
            $objectVO->setIncremento($rs["incremento"]);
            $objectVO->setHoraincremento($rs["horaincremento"]);
            $objectVO->setEnviado($rs["enviado"]);
            $objectVO->setFolioenvios($rs["folioenvios"]);
            $objectVO->setProveedortransporte($rs["proveedorTransporte"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \MeVO
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
     * @return \MeVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new MeVO();
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
     * @param \MeVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = MeVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "tanque = ?, "
                . "fecha = ?, "
                . "fechae = ?, "
                . "proveedor = ?, "
                . "producto = ?, "
                . "vol_inicial = ?, "
                . "vol_final = ?, "
                . "fechafac = ?, "
                . "foliofac = ?, "
                . "volumenfac = ?, "
                . "terminal = ?, "
                . "clavevehiculo = ?, "
                . "documento = ?, "
                . "status = ?, "
                . "entcombustible = ?, "
                . "facturas = ?, "
                . "preciou = ?, "
                . "importefac = ?, "
                . "carga = ?, "
                . "cuadrada = ?, "
                . "tipo = ?, "
                . "t_final = ?, "
                . "incremento = ?, "
                . "horaincremento = ?, "
                . "enviado = ?, "
                . "folioenvios = ?, "
                . "proveedorTransporte = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssssssssssssssssssssssi",
                    $objectVO->getTanque(),
                    $objectVO->getFecha(),
                    $objectVO->getFechae(),
                    $objectVO->getProveedor(),
                    $objectVO->getProducto(),
                    $objectVO->getVol_inicial(),
                    $objectVO->getVol_final(),
                    $objectVO->getFechafac(),
                    $objectVO->getFoliofac(),
                    $objectVO->getVolumenfac(),
                    $objectVO->getTerminal(),
                    $objectVO->getClavevehiculo(),
                    $objectVO->getDocumento(),
                    $objectVO->getStatus(),
                    $objectVO->getEntcombustible(),
                    $objectVO->getFacturas(),
                    $objectVO->getPreciou(),
                    $objectVO->getImportefac(),
                    $objectVO->getCarga(),
                    $objectVO->getCuadrada(),
                    $objectVO->getTipo(),
                    $objectVO->getT_final(),
                    $objectVO->getIncremento(),
                    $objectVO->getHoraincremento(),
                    $objectVO->getEnviado(),
                    $objectVO->getFolioenvios(),
                    $objectVO->getProveedortransporte(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}
