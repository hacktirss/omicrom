<?php
session_start();

// Detisa libraries
require_once("libnvo/mysqlUtils.php");
require_once("libnvo/concentrador.php");

try {
    $omiConn = getConnection("127.0.0.1","root","det15a");

    // Parsing request parameters
    parse_str(parse_url($_SERVER["REQUEST_URI"], PHP_URL_QUERY), $queryParameters);

    // Mandatory Parameter
    $poliza   = filter_var($queryParameters['poliza'],  FILTER_SANITIZE_STRING);
    $sistema  = filter_var($queryParameters['sistema'], FILTER_SANITIZE_STRING);
    $formato  = filter_var($queryParameters['formato'], FILTER_SANITIZE_STRING);

    $sqlName = "SELECT ID, CONCAT('OMICROM', '_', sistema, '_', nombre) name "
                . "FROM formatosT F "
                . "WHERE F.nombre = '" . $poliza . "' "
                . "AND F.sistema = '" . $sistema . "' "
                . "AND F.formato = '" . $formato . "' ";
    // Gets downloaded file name  get file name on a library
    if (($qryName = $omiConn->query($sqlName))) {
        if (($rsName = $qryName->fetch_assoc())) {
            $name = $rsName['name'];
            $idFMT = $rsTitle['ID'];
        } else {
            error_log("Error de conexión: (" . $omiConn->errno . ") " . $omiConn->error);
            die("Error de conexión: (" . $omiConn->errno . ") " . $omiConn->error);
        }
    } else {
        error_log("Error de conexión: (" . $omiConn->errno . ") " . $omiConn->error);
        die("Error de conexión: (" . $omiConn->errno . ") " . $omiConn->error);
    }
    error_log($name);

    #Set headers
    header("Content-Type: text/plain");
    header("Content-Disposition: attachment; filename=\"$name\".txt;");
    header("Content-Transfer-Encoding: binary");

    // Gets Document titles
    $sqlTitle = '';
    $sqlTitleParameters = '';
    $sqlTitulos = "SELECT titulo, parametros "
            . "FROM titulosT T "
            . "JOIN formatosT F ON F.id = T.id_fmt_fk "
            .     "AND F.nombre = '" . $poliza . "' "
            .     "AND F.sistema = '" . $sistema . "' "
            .    "AND F.formato = '" . $formato . "' "
            . "WHERE F.nombre =  '" . $poliza . "'";
    if ($qryTitle = $omiConn->query($sqlTitulos)) {
        if (($rsTitle = $qryTitle->fetch_assoc())) {
            $sqlTitle = $rsTitle['titulo'];
            $sqlTitleParameters = $rsTitle['parametros'];
        }
    } else {
        error_log("Error de conexión: (" . $omiConn->errno . ") " . $omiConn->error);
        die("Error obteniendo título: (" . $omiConn->errno . ") " . $omiConn->error);
    }

    $preparedStmt = preparedStatement($omiConn, $sqlTitle, $sqlTitleParameters, $queryParameters);    
    if (!$preparedStmt->execute()) {
        error_log("Error de conexión ex: (" . $preparedStmt->errno . ") " . $preparedStmt->error);
        die("Error de conexión: (" . $preparedStmt->errno . ") " . $preparedStmt->error);
    }

    $meta = $preparedStmt->result_metadata(); 
    while ($field = $meta->fetch_field()) { 
        $params[] = &$row[$field->name]; 
    }
    call_user_func_array(array($preparedStmt, 'bind_result'), $params); 
    while ($preparedStmt->fetch()) { 
        foreach($row as $key => $val) { 
            echo $val . " ";
        }
    }
    $preparedStmt->close();
    echo "\r\n";

    // Execute de dialy concentrator into concentrado temporary table
    $con = new concentrador($queryParameters);
    $con->execute();

    $arraySQLGrupos = array();
    $sqlGrupos = "SELECT G.* "
            . "FROM gruposT G "
            . "JOIN formatosT F ON F.id = G.id_fmt_fk "
            .     "AND F.nombre = '" . $poliza . "' "
            .     "AND F.sistema = '" . $sistema . "' "
            .     "AND F.formato = '" . $formato . "' "
            . "ORDER BY G.orden";
    error_log($sqlGrupos);
    if (($qryGrupos = $omiConn->query($sqlGrupos))) {
        while (($rsGrupos = $qryGrupos->fetch_assoc())) {
            $gruposSQL = "SELECT * FROM ( SELECT " . $rsGrupos['campos'] . ($rsGrupos['totalizador'] ? ", " . $rsGrupos['totalizador'] :  "") . " FROM concentrado WHERE Grupo = '" . $rsGrupos['id'] . "' " . $rsGrupos['groupBy'] . " ORDER BY NCC ) S WHERE TRUE " . $rsGrupos['condicion'];
            error_log($gruposSQL);
            array_push($arraySQLGrupos, $gruposSQL);
        }
    } else {
        error_log("Error (" . $omiConn->errno . ") " . $omiConn->error);
    }

    $total = 0;
    $abonos = array();
    $cargos = array();
    foreach ($arraySQLGrupos as $SQLGrupo) {
        error_log($SQLGrupo);
        if(($qryData = $con->getOmiConn()->query($SQLGrupo))) {
            $fieldNames = getFieldNames($qryData->fetch_fields());
            if ($qryData->num_rows>0) {
                while (($rsData = $qryData->fetch_assoc())) {
                    if ($rsData['NCC']!='') {
                        $renglon = "";
                        $isAbono = false;
                        $isAbono = $rsData['CargoAbono']=='A';
                        foreach ($fieldNames as $field) {
                            if ($field=='T') {
                                $total = $total + $rsData[$field];
                            } else {
                                $renglon = $renglon . $rsData[$field] . " ";
                            }
                        }
                        $renglon = trim($renglon) . "\r\n";
                        if ($isAbono) {
                            array_push($abonos, $renglon);
                        } else {
                            array_push($cargos, $renglon);
                        }
                    }
                }
            }
        } else {
            error_log("Error de conexión: (" . $con->getOmiConn()->errno . ") " . $con->getOmiConn()->error);
        }
    }//foreach Grupo
    
    $sqlCuadre = "SELECT cuadre, parametros "
            . "FROM cuadresT C "
            . "JOIN formatosT F ON F.id = C.id_fmt_fk "
            .     "AND F.nombre = '" . $poliza . "' "
            .     "AND F.sistema = '" . $sistema . "' "
            .    "AND F.formato = '" . $formato . "' "
            . "WHERE F.nombre =  '" . $poliza . "'";
    if ($qryCuadre = $omiConn->query($sqlCuadre)) {
        if (($rsCuadre = $qryCuadre->fetch_assoc())) {
            $sqlCuadre = $rsCuadre['cuadre'];
            $sqlCuadreParameters = $rsCuadre['parametros'];
        }
    } else {
        error_log("Error de conexión: (" . $omiConn->errno . ") " . $omiConn->error);
        die("Error obteniendo cuenta de cuadres: (" . $omiConn->errno . ") " . $omiConn->error);
    }
    error_log($sqlCuadre);
    error_log($sqlCuadreParameters);

    $cuadreParameters = array();
    if ($total<0) {
        $cuadreParameters['tipoMovimiento'] = "1";
        $cuadreParameters['importeCuadre'] = round($total, 2);
    } else if ($total>0) {
        $cuadreParameters['tipoMovimiento'] = "2";
        $cuadreParameters['importeCuadre'] = round($total, 2);
    }
    
    error_log(implode(",", $cuadreParameters));

    $preparedStmt = preparedStatement($omiConn, $sqlCuadre, $sqlCuadreParameters, $cuadreParameters);    
    if (!$preparedStmt->execute()) {
        error_log("Error de conexión ex: (" . $preparedStmt->errno . ") " . $preparedStmt->error);
        die("Error de conexión: (" . $preparedStmt->errno . ") " . $preparedStmt->error);
    }

    $preparedStmt->bind_result($C);
    while ($preparedStmt->fetch()) { 
        $cuadreTotal = $C;
    }
    $preparedStmt->close();

    error_log("----------------------------------------------------------------------------------------------------------------------------------------" . sizeof($abonos));
    error_log("----------------------------------------------------------------------------------------------------------------------------------------" . sizeof($cargos));

    if ($total<0) {
        array_push($abonos, $cuadreTotal . "\r\n");
    } else if ($total>0) {
        array_push($cargos, $cuadreTotal . "\r\n");
    }
    
    error_log("----------------------------------------------------------------------------------------------------------------------------------------" . sizeof($abonos));
    error_log("----------------------------------------------------------------------------------------------------------------------------------------" . sizeof($cargos));

    foreach ($abonos as $abono) {
        echo $abono;
    }
    
    foreach ($cargos as $cargo) {
        echo $cargo;
    }
} catch (Exception $exc) {
    error_log("Error de conexión: (" . $exc->getMessage());
    die($exc->getMessage());
}
