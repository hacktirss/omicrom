<?php

session_start();

try {
    $omiConn = new mysqli("127.0.0.1","root","det15a");

    if ($omiConn->connect_errno 
            || !$omiConn->select_db("omicrom")
            || !($psSetLocale = $omiConn->prepare("SET lc_time_names = 'es_MX'"))
            || !$psSetLocale->execute()) {
        error_log("Error de conexión: (" . $omiConn->connect_errno . ") " . $omiConn->connect_error);
        die("Error de conexión: (" . $omiConn->connect_errno . ") " . $omiConn->connect_error);
    }

    # Mandatory Parameters
    $poliza  = filter_input(INPUT_GET, 'poliza', FILTER_SANITIZE_STRING);

   if (($qryTitle = $omiConn->query("SELECT ID, CONCAT('OMICROM', '_', sistema, '_', nombre, '.txt') titulo "
                . "FROM formatos F "
                . "WHERE F.nombre LIKE '$poliza'"))) {

        if (($rsTitle = $qryTitle->fetch_assoc())) {
            $title = $rsTitle['titulo'];
            $idFMT = $rsTitle['ID'];
        } else {
            error_log("Error de conexión: (" . $omiConn->errno . ") " . $omiConn->error);
            die("Error de conexión: (" . $omiConn->errno . ") " . $omiConn->error);
        }
    } else {
        error_log("Error de conexión: (" . $omiConn->errno . ") " . $omiConn->error);
        die("Error de conexión: (" . $omiConn->errno . ") " . $omiConn->error);
    }

    error_log($title);

    #Set headers
    header("Content-Type: text/plain");
    header("Content-Disposition: attachment; filename=\"$title\";");
    header("Content-Transfer-Encoding: binary");

    $qrySeccion = $omiConn->query("SELECT id, sqlexp, param FROM secciones WHERE id_fmt_fk = $idFMT ORDER BY orden");
    while ($rsSeccion = $qrySeccion->fetch_assoc()) {
        $idSCC  = $rsSeccion['id'];
        $sqlExp = "SELECT CONCAT(";
        $sParam = $rsSeccion['param'];

        $qryCampo  = $omiConn->query("SELECT sqlexp campo FROM campos WHERE id_fmt_fk = $idFMT AND id_scc_fk = $idSCC ORDER BY orden");

        $rowNum = $qryCampo->num_rows; $idx = 0;
        if ($rowNum>0) {
            while ($rsCampo = $qryCampo->fetch_assoc()) {
                $sqlExp = $sqlExp . $rsCampo['campo'] . (++$idx == $rowNum ? ") row" : ", ' ', ");
            }//for each field
            $sqlExp = $sqlExp . " " . $rsSeccion['sqlexp'];
            error_log($sqlExp);

            if (!($psRow = $omiConn->prepare($sqlExp))) {
                error_log("Error de conexión ps: (" . $omiConn->errno . ") " . $omiConn->error);
                die("Error de conexión: (" . $omiConn->errno . ") " . $omiConn->error);
            }

            $params = explode(",", $rsSeccion['param']);
            $pValues = array();
            $sFlags = "";
            for ($i = 0; $i < count($params); $i++) {
                $sFlags = "s" . $sFlags;
            }
            array_push($pValues, $sFlags);
            foreach ($params as $param) {
                # Optional Parameters
                array_push($pValues, filter_input(INPUT_GET, $param, FILTER_SANITIZE_STRING));
            }
            call_user_func_array(array($psRow, 'bind_param'), &$pValues);

            if (!$psRow->execute()) {
                error_log("Error de conexión ex: (" . $psRow->errno . ") " . $psRow->error);
                die("Error de conexión: (" . $psRow->errno . ") " . $psRow->error);
            }

            $outRow = NULL;
            $psRow->bind_result($outRow);
            while ($psRow->fetch()) {
                error_log($outRow);
                if ($outRow!=null && trim($outRow)!='') echo $outRow . "\r\n";
            }//for each row
            $psRow->close();
        }
    }//for each seccion

    $omiConn->close();
} catch (Exception $exc) {
    error_log("Error de conexión: (" . $exc->getMessage());
    die($exc->getMessage());
}
?>
