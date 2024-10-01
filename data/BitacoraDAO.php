<?php

/*
 * BitacoraDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Lino Diaz Soto
 * @version 1.0
 * @since April 2019
 */
include_once ('mysqlUtils.php');
include_once ('BitacoraVO.php');
include_once ('FunctionsDAO.php');

class BitacoraDAO implements FunctionsDAO {

    const RESPONSE_VALID = "OK";
    const TABLA = "bitacora_eventos";

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function _destruct() {
        $this->conn->close();
    }

    // Hold an instance of the class
    private static $instance;

    public static function getInstance() {
        if (!self::$instance instanceof self) {
            self::$instance = new BitacoraDAO();
        }
        return self::$instance;
    }

    /**
     * 
     * @param \BitacoraVO $objectVO
     * @return int Nuevo identificador generado
     */
    public function create($objectVO) {
        $sql = " INSERT INTO  " . self::TABLA . " "
                . " (fecha_evento,"
                . "hora_evento,"
                . "usuario,"
                . "tipo_evento,"
                . "descripcion_evento,"
                . "ip_evento,"
                . "query_str,"
                . "numero_alarma) "
                . " VALUES "
                . " ( ? , ? , ? , ? , ? , ? , ? , ? ) ";
        $ps = $this->conn->prepare($sql);
        if (($ps)) {
            $ps->bind_param("ssssssss"
                    , $objectVO->getFechaEvento()
                    , $objectVO->getHoraEvento()
                    , $objectVO->getUsuario()
                    , $objectVO->getTipoEvento()
                    , $objectVO->getDescripcionEvento()
                    , $objectVO->getIpEvento()
                    , $objectVO->getQueryStr()
                    , $objectVO->getNumeroAlarma());
            $ps->execute();
            //error_log("Se inserto eN BITACORA.....");
            $ps->close();
        }
    }

    /**
     * 
     * @param array() $rs
     * @return \BitacoraVO
     */
    public function fillObject($rs) {
        $ObjectVO = new BitacoraVO();
        if (is_array($rs)) {
            $ObjectVO->setFechaEvento($rs["fecha_evento"]);
            $ObjectVO->setHoraEvento($rs["hora_evento"]);
            $ObjectVO->setUsuario($rs["usuario"]);
            $ObjectVO->setTipoEvento($rs["tipo_evento"]);
            $ObjectVO->setDescripcionEvento($rs["descripcion_evento"]);
            $ObjectVO->setIpEvento($rs["ip_evento"]);
            $ObjectVO->setQueryStr($rs["query_str"]);
            $ObjectVO->setNumeroAlarma($rs["numero_alarma"]);
        }
        return $ObjectVO;
    }

    /**
     * 
     * @param int $idObjectVO Llave primaria o identificador 
     * @param string $field Nombre del campo a buscar
     * @return \BitacoraVO
     */
    public function retrieve($idObjectVO, $field = "id_bitacora") {
        $objectVO = new BitacoraVO();
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
     * @param string $sql Consulta SQL
     * @return array Arreglo de objetos \BitacoraVO
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
    public function remove($idObjectVO, $field = "id_bitacora") {
        $sql = "DELETE FROM " . self::TABLA . " WHERE " . $field . " = ? LIMIT 1";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("s", $idObjectVO
            );
            return $ps->execute();
        }
    }

    /**
     * 
     * @param \BitacoraVO $objectVO
     * @return boolean Si la operación fue exitosa devolvera TRUE
     */
    public function update($objectVO = BitacoraVO) {
        $sql = "UPDATE " . self::TABLA . " SET "
                . "fecha_evento = ?, "
                . "hora_evento = ?, "
                . "usuario = ?, "
                . "tipo_evento = ?, "
                . "descripcion_evento = ?, "
                . "ip_evento = ?, "
                . "query_str = ?, "
                . "numero_alarma = ? "
                . "WHERE id = ?";
        //error_log($sql);
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssssssii",
                    $objectVO->getFechaEvento(),
                    $objectVO->getHoraEvento(),
                    $objectVO->getUsuario(),
                    $objectVO->getTipoEvento(),
                    $objectVO->getDescripcionEvento(),
                    $objectVO->getIpEvento(),
                    $objectVO->getQueryStr(),
                    $objectVO->getNumeroAlarma(),
                    $objectVO->getId()
            );
            if ($ps->execute()) {
                $this->changeDivisa($objectVO);
                return true;
            }
        }
        error_log($this->conn->error);
        return false;
    }

    public function saveLog($user, $evtType, $evtDesc, $query_str = "", $numberAlarm = 0) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $mac = system('arp -a ' . escapeshellarg($ip));
        $usuarioSesion = getSessionUsuario();
        $sql = " INSERT INTO  bitacora_eventos "
                . " ( fecha_evento, hora_evento, usuario , tipo_evento , descripcion_evento, query_str, numero_alarma,ip_evento,mac) "
                . " VALUES "
                . " ( current_date() , current_time() , ? , ? , ? , ? , ?, ?, ?) ";
        $ps = $this->conn->prepare($sql);

        if (($ps)) {
            $ps->bind_param("sssssss", $user, $evtType, $evtDesc, $query_str, $numberAlarm, $usuarioSesion->getIdLocation(), $mac);
            $ps->execute();
            $ps->close();
        } else {
            error_log("Error al ps: " . $this->conn->error);
        }
    }

    public function saveLogSn($user, $evtType, $evtDesc, $query_str = "", $numberAlarm = 0) {
        $usuarioSesion = getSessionUsuario();
        $sql = " INSERT INTO  bitacora_eventos "
                . " ( fecha_evento, hora_evento, usuario , tipo_evento , descripcion_evento, query_str, numero_alarma,ip_evento,mac) "
                . " VALUES "
                . " ( current_date() , current_time() , ? , ? , ? , ? , ?, ?, '-') ";
        error_log($sql);
        $ps = $this->conn->prepare($sql);

        if (($ps)) {
            $ps->bind_param("ssssss", $user, $evtType, $evtDesc, $query_str, $numberAlarm, $usuarioSesion->getIdLocation());
            error_log("_____________");
            
            $ps->execute();
            error_log($ps->error);
            $ps->close();
        } else {
            error_log("Error al ps: " . $this->conn->error);
        }
    }

}
