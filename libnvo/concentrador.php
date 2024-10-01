<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of concentrador
 *
 * @author rolando
 */
class concentrador {
    //put your code here
    private $parameters;
    private $omiConn;
    public function __construct($parameters) {
        $this->parameters = $parameters;
        $this->omiConn = $this->openConnection();
    }

    function getOmiConn() {
        return $this->omiConn;
    }

    private function openConnection() {
        $omiConn = com\softcoatl\utils\IConnection::getConnection();

        if ($omiConn->connect_errno 
                || !$omiConn->select_db("omicrom")
                || !($psSetLocale = $omiConn->prepare("SET lc_time_names = 'es_MX'"))
                || !$psSetLocale->execute()) {
            throw new Exception("Error de conexión: (" . $omiConn->connect_errno . ") " . $omiConn->connect_error);
        }
        return $omiConn;
    }

    public function close() {
        $this->omiConn->close();
    }

    public function execute() {

        if (!$this->omiConn) {
            throw new Exception("Error obteniendo conexión (" . $this->omiConn->errno . ") " . $this->omiConn->error);
        }
            
        if (($qryTitle = $this->omiConn->query("SELECT ID, CONCAT('OMICROM', '_', sistema, '_', nombre, '.txt') titulo "
                     . "FROM formatosT F "
                     . "WHERE F.nombre LIKE '" . $this->parameters['poliza'] . "'"))) {

             if (($rsTitle = $qryTitle->fetch_assoc())) {
                 $title = $rsTitle['titulo'];
                 $idFMT = $rsTitle['ID'];
             } else {
                 throw new Exception("Error obteniendo datos del formato concentrador (" . $this->omiConn->errno . ") " . $this->omiConn->error);
             }
        }

        $qrySeccion = $this->omiConn->query("SELECT id, grupo, sqlexp, param FROM seccionesT WHERE id_fmt_fk = $idFMT ORDER BY orden");
        $this->omiConn->query("CREATE TEMPORARY TABLE IF NOT EXISTS concentrado (Grupo INT(3), Corte INT(3), NCC VARCHAR(20), TipoMovimiento INT(1), Importe DECIMAL(12, 6), Concepto VARCHAR(256), DatoUno VARCHAR(256), RFC VARCHAR(3), Factura VARCHAR(11), CargoAbono CHAR(1))");
        while ($rsSeccion = $qrySeccion->fetch_assoc()) {
            $idSCC  = $rsSeccion['id'];
            $sqlExp = "INSERT INTO concentrado SELECT " . $rsSeccion['grupo'] . " Grupo, ";
            $sParam = $rsSeccion['param'];

            $qryCampo  = $this->omiConn->query("SELECT sqlexp campo, nombre FROM camposT WHERE id_fmt_fk = $idFMT AND id_scc_fk = $idSCC ORDER BY orden");

            $rowNum = $qryCampo->num_rows; $idx = 0;
            if ($rowNum>0) {
                while ($rsCampo = $qryCampo->fetch_assoc()) {
                    $sqlExp = $sqlExp . $rsCampo['campo'] . (++$idx == $rowNum ? " " . $rsCampo['nombre'] : " " . $rsCampo['nombre'] . ", ");
                }//for each field
                $sqlExp = $sqlExp . " " . $rsSeccion['sqlexp'];
                error_log($sqlExp);

                if (!($psRow = $this->omiConn->prepare($sqlExp))) {
                    throw new Exception("Error de conexión ps: (" . $this->omiConn->errno . ") " . $this->omiConn->error);
                }

                $params = explode(",", $rsSeccion['param']);
                $sFlags = "";
                for ($i = 0; $i < count($params); $i++) {
                    $sFlags = "s" . $sFlags;
                }

                $pValues = array();
                array_push($pValues, filter_var($sFlags, FILTER_SANITIZE_STRING));

                foreach ($params as $param) {
                    # Optional Parameters
                    error_log("********************************************************** " . $param . "::" . $this->parameters[$param]);
                    array_push($pValues, filter_var($this->parameters[$param], FILTER_SANITIZE_STRING));
                }
                error_log("************************************************************** " . implode(",", $pValues));

                //call_user_func_array(array($psRow, 'bind_param'), &$pValues);
                call_user_func_array(array($psRow, 'bind_param'), refValues($pValues));
                
                if (!$psRow->execute()) {
                    throw new Exception("Error de conexión ex: (" . $psRow->errno . ") " . $psRow->error);
                }
                error_log("Insertando " . $psRow->affected_rows . " registros en el concentrador");
                $psRow->close();
            }
        }//for each seccion
    }
}
