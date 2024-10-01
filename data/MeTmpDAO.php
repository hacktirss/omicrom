<?php

/**
 * Description of MeDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('MeVO.php');

class MeTmpDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "me_tmp";

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
                . "usuario,"
                . "tanque,"
                . "fecha,"
                . "fechae,"
                . "proveedor,"
                . "producto,"
                . "vol_inicial,"
                . "vol_final,"
                . "fechafac,"
                . "foliofac,"
                . "uuid,"
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
                . "proveedorTransporte,"
                . "punto_exportacion,"
                . "punto_internacion,"
                . "pais_destino,"
                . "pais_origen,"
                . "medio_transporte_entrada,"
                . "medio_transporte_salida,"
                . "incoterms,"
                . "volumen_devolucion,"
                . "tipocomprobante"
                . ") "
                . "VALUES(?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("dsssssssssssssssssssssssssssssssssss",
                    $objectVO->getUsuario(),
                    $objectVO->getTanque(),
                    $objectVO->getFechae(),
                    $objectVO->getProveedor(),
                    $objectVO->getProducto(),
                    $objectVO->getVol_inicial(),
                    $objectVO->getVol_final(),
                    $objectVO->getFechafac(),
                    $objectVO->getFoliofac(),
                    $objectVO->getUuid(),
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
                    $objectVO->getFolioenvios(),
                    $objectVO->getProveedortransporte(),
                    $objectVO->getPunto_exportacion(),
                    $objectVO->getPunto_internacion(),
                    $objectVO->getPais_destino(),
                    $objectVO->getPais_origen(),
                    $objectVO->getMedio_transporte_entrada(),
                    $objectVO->getMedio_transporte_salida(),
                    $objectVO->getIncoterms(),
                    $objectVO->getVolumen_devolucion(),
                    $objectVO->getTipocomprobante()
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
            $objectVO->setUsuario($rs["usuario"]);
            $objectVO->setTanque($rs["tanque"]);
            $objectVO->setFecha($rs["fecha"]);
            $objectVO->setFechae($rs["fechae"]);
            $objectVO->setProveedor($rs["proveedor"]);
            $objectVO->setProducto($rs["producto"]);
            $objectVO->setVol_inicial($rs["vol_inicial"]);
            $objectVO->setVol_final($rs["vol_final"]);
            $objectVO->setFechafac($rs["fechafac"]);
            $objectVO->setFoliofac($rs["foliofac"]);
            $objectVO->setUuid($rs["uuid"]);
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
            $objectVO->setPunto_exportacion($rs["punto_exportacion"]);
            $objectVO->setPunto_internacion($rs["punto_internacion"]);
            $objectVO->setPais_destino($rs["pais_destino"]);
            $objectVO->setPais_origen($rs["pais_origen"]);
            $objectVO->setMedio_transporte_entrada($rs["medio_transporte_entrada"]);
            $objectVO->setMedio_transporte_salida($rs["medio_transporte_salida"]);
            $objectVO->setIncoterms($rs["incoterms"]);
            $objectVO->setVolumen_devolucion($rs["volumen_devolucion"]);
            $objectVO->setTipocomprobante($rs["tipocomprobante"]);
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
        $sql = "DELETE FROM " . self::TABLA . " WHERE " . $field . " = ?";
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
        $sql = "SELECT " . self::TABLA . ".* FROM " . self::TABLA . " WHERE $field = '$idObjectVO';";
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
                . "usuario = ?, "
                . "tanque = ?, "
                . "fecha = ?, "
                . "fechae = ?, "
                . "proveedor = ?, "
                . "producto = ?, "
                . "vol_inicial = ?, "
                . "vol_final = ?, "
                . "fechafac = ?, "
                . "foliofac = ?, "
                . "uuid = ?, "
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
                . "proveedorTransporte = ?, "
                . "punto_exportacion = ?, "
                . "punto_internacion = ?, "
                . "pais_destino = ?, "
                . "pais_origen = ?, "
                . "medio_transporte_entrada = ?, "
                . "medio_transporte_salida = ?, "
                . "incoterms = ?, "
                . "volumen_devolucion = ?,"
                . "tipocomprobante = ?  "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("dsssssssssssssssssssssssssssssssssssssi",
                    $objectVO->getUsuario(),
                    $objectVO->getTanque(),
                    $objectVO->getFecha(),
                    $objectVO->getFechae(),
                    $objectVO->getProveedor(),
                    $objectVO->getProducto(),
                    $objectVO->getVol_inicial(),
                    $objectVO->getVol_final(),
                    $objectVO->getFechafac(),
                    $objectVO->getFoliofac(),
                    $objectVO->getUuid(),
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
                    $objectVO->getPunto_exportacion(),
                    $objectVO->getPunto_internacion(),
                    $objectVO->getPais_destino(),
                    $objectVO->getPais_origen(),
                    $objectVO->getMedio_transporte_entrada(),
                    $objectVO->getMedio_transporte_salida(),
                    $objectVO->getIncoterms(),
                    $objectVO->getVolumen_devolucion(),
                    $objectVO->getTipocomprobante(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}
