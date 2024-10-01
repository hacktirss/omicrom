<?php

/**
 * Description of AlarmasDAO
 * omicrom®
 * © 2019, Detisa 
 * http://www.detisa.com.mx
 * @author Tirso Bautista Anaya
 * @version 1.0
 * @since ago 2019
 */
include_once ('mysqlUtils.php');
include_once ('AlarmasVO.php');

class AlarmasDAO {

    const RESPONSE_VALID = "OK";

    /**
     *  administración del sistema					 
     */
    const VAL1 = 1;

    /**
     *  eventos de la UCC					 
     */
    const VAL2 = 2;

    /**
     *  eventos relacionados a los programas informáticos					 
     */
    const VAL3 = 3;

    /**
     *  eventos de comunicación					 
     */
    const VAL4 = 4;

    /**
     *  operaciones cotidianas					 
     */
    const VAL5 = 5;

    /**
     *  verificaciones realizadas por la autoridad fiscal o por proveedores autorizados por el SAT					 
     */
    const VAL6 = 6;

    /**
     *  exista una diferencia de más de 0.5 % tratándose de Hidrocarburos y Petrolíferos líquidos o de 1 % tratándose de Hidrocarburos y Petrolíferos gaseosos, en el volumen final del periodo, obtenido de sumar al volumen inicial en dicho periodo, las recepciones de producto y restar las entregas de producto, incluyendo las pérdidas por proceso"					 
     */
    const VAL7 = 7;

    /**
     *  el volumen de existencias registrado al corte del día, es igual al registrado en el corte del día anterior y existen registros de entradas o salidas en el corte del día					 
     */
    const VAL8 = 8;

    /**
     *  el volumen de existencias registrado por cada tipo de Hidrocarburo o Petrolífero y sistema de medición es menor a cero					 
     */
    const VAL9 = 9;

    /**
     *  el volumen de existencias registrado en el corte del día varía con respecto al corte del día anterior y no existen registros de entradas o salidas en el corte del día					 
     */
    const VAL10 = 10;

    /**
     *  el volumen de salidas en un lapso de veinticuatro horas es mayor al volumen de entradas del mismo lapso más el volumen de existencias del corte del día anterior					 
     */
    const VAL11 = 11;

    /**
     *  calibración no vigente					 
     */
    const VAL12 = 12;

    /**
     *  intento de alteración de cualquier registro					 
     */
    const VAL13 = 13;

    /**
     *  registros incompletos o duplicados					 
     */
    const VAL14 = 14;

    /**
     *  problemas de comunicación					 
     */
    const VAL15 = 15;

    /**
     *  falla del medio de almacenamiento					 
     */
    const VAL16 = 16;

    /**
     *  falla en la red de comunicación					 
     */
    const VAL17 = 17;

    /**
     *  falla de energía					 
     */
    const VAL18 = 18;

    /**
     *  error en la transmisión de información					 
     */
    const VAL19 = 19;

    /**
     *  rechazos de inicio de sesión					 
     */
    const VAL20 = 20;

    /**
     *  paro de emergencia					 
     */
    const VAL21 = 21;

    private $conn;

    function __construct() {
        $this->conn = getConnection();
    }

    function __destruct() {
        $this->conn->close();
    }

    public function retrive($idAlarma) {
        $alarmaVO = new AlarmasVO();
        $sql = "SELECT * FROM alarmas WHERE id_alarma = " . $idAlarma;
        if (($query = $this->conn->query($sql)) && ($rs = $query->fetch_assoc())) {
            $alarmaVO->setIdAlarma($rs['id_alarma']);
            $alarmaVO->setFechaAlarma($rs['fecha_alarma']);
            $alarmaVO->setHoraAlarma($rs['hora_alarma']);
            $alarmaVO->setComponenteAlarma($rs['componente_alarma']);
            $alarmaVO->setTipoAlarma($rs['tipo_alarma']);
            $alarmaVO->setDescripcionAlarma($rs['descripcion_alarma']);
            $alarmaVO->setRevisionAlarma($rs['revision_alarma']);
            error_log($alarmaVO);
            return $alarmaVO;
        }
        return null;
    }

    public function create($alarmaVO) {
        $sql = "INSERT INTO alarmas ("
                . "fecha_alarma, "
                . "hora_alarma, "
                . "componente_alarma, "
                . "tipo_alarma, "
                . "descripcion_alarma, "
                . "revision_alarma"
                . ") "
                . "VALUES(?, ?, ?, ?, ?, '1')";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("sssss",
                    $alarmaVO->getFechaAlarma(),
                    $alarmaVO->getHoraAlarma(),
                    $alarmaVO->getComponenteAlarma(),
                    $alarmaVO->getTipoAlarma(),
                    $alarmaVO->getDescripcionAlarma()
            );
            $id = $ps->execute() ? $ps->insert_id : -1;
            error_log(mysqli_error($this->conn));
            $ps->close();
            return $id;
        }
        return 0;
    }

    public function update($alarmaVO) {
        $sql = "UPDATE alarmas SET "
                . "revision_alarma = ? "
                . "WHERE id_alarma = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("ss", $alarmaVO->getRevisionAlarma(), $alarmaVO->getIdAlarma()
            );
            return $ps->execute();
        }
    }

    public function updateAll($revision = 1) {
        $sql = "UPDATE alarmas SET "
                . "revision_alarma = 0 "
                . "WHERE revision_alarma = ? ";
        error_log($sql);
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("s", $revision);
            return $ps->execute();
        }
    }

    public function remove($alarmaVO) {
        $sql = "DELETE FROM alarmas WHERE id_alarma = ? ";
        if (($ps = $this->conn->prepare($sql))) {
            $ps->bind_param("s", $alarmaVO->getIdAlarma()
            );
            return $ps->execute();
        }
    }

    public function getAll($sql) {
        $array = array();
        if (($query = $this->conn->query($sql))) {
            while (($rs = $query->fetch_assoc())) {
                $alarmaVO = new AlarmasVO();
                $alarmaVO->setIdAlarma($rs['id_alarma']);
                $alarmaVO->setFechaAlarma($rs['fecha_alarma']);
                $alarmaVO->setHoraAlarma($rs['hora_alarma']);
                $alarmaVO->setComponenteAlarma($rs['componente_alarma']);
                $alarmaVO->setTipoAlarma($rs['tipo_alarma']);
                $alarmaVO->setDescripcionAlarma($rs['descripcion_alarma']);
                $alarmaVO->setRevisionAlarma($rs['revision_alarma']);
                array_push($array, $alarmaVO);
            }
        } else {
            error_log($this->conn->error);
        }

        return $array;
    }

}
