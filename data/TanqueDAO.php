<?php

/**
 * Description of TanqueDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
include_once ('mysqlUtils.php');
include_once ('FunctionsDAO.php');
include_once ('TanqueVO.php');

class TanqueDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "tanques";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    /**
     * 
     * @param \TanqueVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO = TanqueVO) {
        $id = -1;
        $sql = "INSERT INTO " . self::TABLA . " ("
                . "tanque,"
                . "producto,"
                . "clave_producto,"
                . "volumen_actual,"
                . "volumen_faltante,"
                . "volumen_operativo,"
                . "capacidad_total,"
                . "volumen_minimo,"
                . "volumen_fondaje,"
                . "presion,"
                . "altura,"
                . "agua,"
                . "temperatura,"
                . "fecha_hora_veeder,"
                . "fecha_hora_s,"
                . "estado,"
                . "procesado,"
                . "cargando,"
                . "volumen,"
                . "vigencia_calibracion,"
                . "prefijo_sat, "
                . "sistema_medicion, "
                . "sensor, "
                . "incertidumbre_sensor ,"
                . "descripcion,"
                . "id_proveedor,"
                . "id_proveedor_sensor"
                . ") "
                . "VALUES(?, ?, ?, 0, 0, ?, ?, ?, ?, 0, 0, 0, 0, NOW(), NOW(), ?, 0, 0, 0, CURRENT_DATE(), ?, ?, ?, ?,?,?,?)";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssssssssii",
                    $objectVO->getTanque(),
                    $objectVO->getProducto(),
                    $objectVO->getClave_producto(),
                    $objectVO->getVolumen_operativo(),
                    $objectVO->getCapacidad_total(),
                    $objectVO->getVolumen_minimo(),
                    $objectVO->getVolumen_fondaje(),
                    $objectVO->getEstado(),
                    $objectVO->getPrefijo_sat(),
                    $objectVO->getSistema_medicion(),
                    $objectVO->getSensor(),
                    $objectVO->getIncertidumbre_sensor(),
                    $objectVO->getDescripcion(),
                    $objectVO->getIdProveedor(),
                    $objectVO->getIdProveedorSensor()
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
     * @return \TanqueVO
     */
    public function fillObject($rs) {
        $objectVO = new TanqueVO();
        if (is_array($rs)) {
            $objectVO->setId($rs["id"]);
            $objectVO->setTanque($rs["tanque"]);
            $objectVO->setProducto($rs["producto"]);
            $objectVO->setClave_producto($rs["clave_producto"]);
            $objectVO->setVolumen_actual($rs["volumen_actual"]);
            $objectVO->setVolumen_faltante($rs["volumen_faltante"]);
            $objectVO->setVolumen_operativo($rs["volumen_operativo"]);
            $objectVO->setCapacidad_total($rs["capacidad_total"]);
            $objectVO->setVolumen_minimo($rs["volumen_minimo"]);
            $objectVO->setVolumen_fondaje($rs["volumen_fondaje"]);
            $objectVO->setPresion($rs["presion"]);
            $objectVO->setAltura($rs["altura"]);
            $objectVO->setAgua($rs["agua"]);
            $objectVO->setTemperatura($rs["temperatura"]);
            $objectVO->setFecha_hora_veeder($rs["fecha_hora_veeder"]);
            $objectVO->setFecha_hora_s($rs["fecha_hora_s"]);
            $objectVO->setEstado($rs["estado"]);
            $objectVO->setProcesado($rs["procesado"]);
            $objectVO->setCargando($rs["cargando"]);
            $objectVO->setVolumen($rs["volumen"]);
            $objectVO->setVigencia_calibracion($rs["vigencia_calibracion"]);
            $objectVO->setPrefijo_sat($rs["prefijo_sat"]);
            $objectVO->setSistema_medicion($rs["sistema_medicion"]);
            $objectVO->setSensor($rs["sensor"]);
            $objectVO->setIncertidumbre_sensor($rs["incertidumbre_sensor"]);
            $objectVO->setDescripcion($rs["descripcion"]);
            $objectVO->setIdProveedor($rs["id_proveedor"]);
            $objectVO->setIdProveedorSesor($rs["id_proveedor_sensor"]);
        }
        return $objectVO;
    }

    /**
     * 
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \TanqueVO
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
     * @return \TanqueVO
     */
    public function retrieve($idObjectVO, $field = "id") {
        $objectVO = new TanqueVO();
        $sql = "SELECT " . self::TABLA . ".* FROM " . self::TABLA . " "
                . "WHERE " . self::TABLA . "." . $field . " = '" . $idObjectVO . "'";
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
     * @param \TanqueVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = TanqueVO) {
        error_log(print_r($objectVO, true));
        $sql = "UPDATE " . self::TABLA . " SET "
                . "tanque = ?, "
                . "producto = ?, "
                . "clave_producto = ?, "
                . "volumen_actual = ?, "
                . "volumen_faltante = ?, "
                . "volumen_operativo = ?, "
                . "capacidad_total = ?, "
                . "volumen_minimo = ?, "
                . "volumen_fondaje = ?, "
                . "presion = ?, "
                . "altura = ?, "
                . "agua = ?, "
                . "temperatura = ?, "
                . "fecha_hora_veeder = ?, "
                . "fecha_hora_s = ?, "
                . "estado = ?, "
                . "procesado = ?, "
                . "cargando = ?, "
                . "volumen = ?, "
                . "vigencia_calibracion = ?, "
                . "prefijo_sat = ?, "
                . "sistema_medicion = ?, "
                . "sensor = ?, "
                . "incertidumbre_sensor = ? ,"
                . "descripcion = ? , "
                . "id_proveedor = ?, "
                . "id_proveedor_sensor = ? "
                . "WHERE id = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssssssssssssssssssssssi",
                    $objectVO->getTanque(),
                    $objectVO->getProducto(),
                    $objectVO->getClave_producto(),
                    $objectVO->getVolumen_actual(),
                    $objectVO->getVolumen_faltante(),
                    $objectVO->getVolumen_operativo(),
                    $objectVO->getCapacidad_total(),
                    $objectVO->getVolumen_minimo(),
                    $objectVO->getVolumen_fondaje(),
                    $objectVO->getPresion(),
                    $objectVO->getAltura(),
                    $objectVO->getAgua(),
                    $objectVO->getTemperatura(),
                    $objectVO->getFecha_hora_veeder(),
                    $objectVO->getFecha_hora_s(),
                    $objectVO->getEstado(),
                    $objectVO->getProcesado(),
                    $objectVO->getCargando(),
                    $objectVO->getVolumen(),
                    $objectVO->getVigencia_calibracion(),
                    $objectVO->getPrefijo_sat(),
                    $objectVO->getSistema_medicion(),
                    $objectVO->getSensor(),
                    $objectVO->getIncertidumbre_sensor(),
                    $objectVO->getDescripcion(),
                    $objectVO->getIdProveedor(),
                    $objectVO->getIdProveedorSensor(),
                    $objectVO->getId()
            );
            return $ps->execute();
        }
        error_log($this->conn->error);
        return false;
    }

}
