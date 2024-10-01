<?php
#Librerias
session_start();
// Detisa libraries
require_once("libnvo/mysqlUtils.php");
require_once("libnvo/concentrador.php");
// TCPDF libraries
require_once("tcpdf2/config/lang/eng.php");
require_once("tcpdf2/tcpdf.php");
  
setlocale(LC_ALL, "es_MX.utf8");

$totalCargos = 0;
$totalAbonos = 0;

function totalizaApartado($connection, $poliza, $sistema, $formato, $cargoAbono) {
    global $totalCargos ;
    global $totalAbonos;

    $rowApartado = array();
    $arraySQLGrupos = array();
    $arraySQLHeaders = array();

    $sqlGrupos = "SELECT "
                    . "gruposT.id_fmt_fk, gruposT.id, gruposT.campos, gruposT.encabezado, gruposT.totalizador, gruposT.condicion, gruposT.groupBy "
                . "FROM formatosT JOIN gruposT ON formatosT.id = gruposT.id_fmt_fk "
                .           "AND formatosT.nombre = '" . $poliza . "' "
                .           "AND formatosT.sistema = '" . $sistema . "' "
                .           "AND formatosT.formato = '" . $formato . "' "
                . "ORDER BY gruposT.orden";
    if (($qryGrupos = $connection->query($sqlGrupos))) {
        while (($rsGrupos = $qryGrupos->fetch_assoc())) {
            $gruposSQL = "SELECT * FROM ("
                    . "SELECT " . $rsGrupos['campos']  . " "
                    . "FROM concentrado "
                    . "JOIN (SELECT formatosT.id, "
                    .           "SUBSTRING( niveles_cc, 1, 1 ) uno, SUBSTRING( niveles_cc, 2, 1 ) dos, "
                    .           "SUBSTRING( niveles_cc, 3, 1 ) tres, SUBSTRING( niveles_cc, 4, 1 ) cuatro "
                    .       "FROM formatosT WHERE formatosT.nombre = '" . $poliza . "' "
                    .           "AND formatosT.sistema = '" . $sistema . "' "
                    .           "AND formatosT.formato = '" . $formato . "' ) formatosT ON TRUE "
                    . "WHERE Grupo = '" . $rsGrupos['id'] . "' AND CargoAbono = '" . $cargoAbono . "'" . $rsGrupos['groupBy'] . " ORDER BY NCC) S " . $rsGrupos['condicion'];
            $headerSQL = "SELECT " . $rsGrupos['encabezado'] . ", " . $rsGrupos['totalizador'] . " FROM concentrado WHERE Grupo = '" . $rsGrupos['id'] . "' AND CargoAbono = '" . $cargoAbono . "'";
            array_push($arraySQLGrupos, $gruposSQL);
            array_push($arraySQLHeaders, $headerSQL);
        }
    } else {
        error_log("Error (" . $connection->errno . ") " . $connection->error);
    }

    $jdx = 0;
    foreach ($arraySQLGrupos as $SQLGrupo) {
        $abonosGrupo = 0;
        $cargosGrupo = 0;
        $rowsGrupo = array();
        error_log("EXEC " . $sqlGrupos);
        if(($qryData = $connection->query($SQLGrupo))) {
            $fieldNames = getFieldNames($qryData->fetch_fields());
            if ($qryData->num_rows>0) {
                error_log("EXEC HDR " . $arraySQLHeaders[$jdx]);
                if(($headerQryData = $connection->query($arraySQLHeaders[$jdx])) && ($headerData = $headerQryData->fetch_assoc())) {
                    error_log(print_r($headerData, true));
                    $abonosGrupo += $headerData["A"];
                    $cargosGrupo += $headerData["C"];
                    $totalAbonos += $headerData["A"];
                    $totalCargos += $headerData["C"];
                    error_log("Abonos Grupo " . $abonosGrupo . " " . $headerRow);
                    error_log("Cargos Grupo " . $cargosGrupo . " " . $headerRow);
                }
                error_log("ERR" . $connection->error);
                $contentRow = '';
                while (($rsData = $qryData->fetch_assoc())) {
                    $idx = 0;
                    $contentRow = $rsData["D"];
                    array_push($rowsGrupo, $contentRow);
                }
                array_push($rowsGrupo, $footerRow);
            }
            if ($abonosGrupo>0 || $cargosGrupo>0) {
                foreach($rowsGrupo as $row) {
                    array_push($rowApartado, $row);
                }
            }
            $jdx++;
        } else {
            error_log("Error de conexión: (" . $connection->errno . ") " . $connection->error);
            die("Error obteniendo título: (" . $connection->errno . ") " . $connection->error);
        }
    }//foreach Grupo
    return $rowApartado;
}//totalizaApartado

try {
    $omiConn = com\softcoatl\utils\IConnection::getConnection();

    // Parsing request parameters
    parse_str(parse_url($_SERVER["REQUEST_URI"], PHP_URL_QUERY), $queryParameters);

    // Mandatory Parameter
    $poliza   = filter_var($queryParameters['poliza'],  FILTER_SANITIZE_STRING);
    $sistema  = filter_var($queryParameters['sistema'], FILTER_SANITIZE_STRING);
    $formato  = filter_var($queryParameters['formato'], FILTER_SANITIZE_STRING);

    $sqlName = "SELECT ID, CONCAT('OMICROM', '_', sistema, '_', nombre, '_', REPLACE( cia.estacion, ' ', '_' ), '_' ) name "
                . "FROM formatosT F JOIN cia ON TRUE "
                . "WHERE F.nombre = '" . $poliza . "' "
                . "AND F.sistema = '" . $sistema . "' "
                . "AND F.formato = '" . $formato . "' ";
    // Gets downloaded file name  get file name on a library
    if (($qryName = $omiConn->query($sqlName))) {
        if (($rsName = $qryName->fetch_assoc())) {
            $name = $rsName["name"] . str_replace("-", "", $queryParameters["fecha"]) . ".pol";
            $idFMT = $rsName["ID"];
        } else {
            error_log("Error de conexión 1: (" . $omiConn->errno . ") " . $omiConn->error);
            die("Error de conexión 1: (" . $omiConn->errno . ") " . $omiConn->error);
        }
    } else {
        error_log("Error de conexión 2: (" . $omiConn->errno . ") " . $omiConn->error);
        die("Error de conexión 2: (" . $omiConn->errno . ") " . $omiConn->error);
    }

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
    if (($qryTitle = $omiConn->query($sqlTitulos))) {
        if (($rsTitle = $qryTitle->fetch_assoc())) {
            $sqlTitle = $rsTitle['titulo'];
            $sqlTitleParameters = $rsTitle['parametros'];
        }
    } else {
        error_log("Error obteniendo título : (" . $omiConn->errno . ") " . $omiConn->error);
        die("Error obteniendo título: (" . $omiConn->errno . ") " . $omiConn->error);
    }
    $preparedStmt = preparedStatement($omiConn, $sqlTitle, $sqlTitleParameters, $queryParameters);    
    if (!$preparedStmt->execute()) {
        error_log("Error de conexión ex: (" . $preparedStmt->errno . ") " . $preparedStmt->error);
        die("Error de conexión ex: (" . $preparedStmt->errno . ") " . $preparedStmt->error);
    }

    $preparedStmt->bind_result($outTitle, $outSubtitle);
    while ($preparedStmt->fetch()) {
        error_log("Title ".$outTitle);
    }
    $preparedStmt->close();

    // Execute de dialy concentrator into concentrado temporary table
    $con = new concentrador($queryParameters);
    $con->execute();

    $abonos = totalizaApartado($con->getOmiConn(), $poliza, $sistema, $formato, "A");
    $cargos = totalizaApartado($con->getOmiConn(), $poliza, $sistema, $formato, "C");

    $table = '<?xml version="1.0" standalone="yes" ?>' .
        '<DATAPACKET Version="2.0">' .
            '<METADATA>' .
                '<FIELDS>' .
                    '<FIELD attrname="VersionCOI" fieldtype="i2"/>' .
                    '<FIELD attrname="TipoPoliz" fieldtype="string" WIDTH="2"/>' .
                    '<FIELD attrname="DiaPoliz" fieldtype="string" WIDTH="2"/>' .
                    '<FIELD attrname="ConcepPoliz" fieldtype="string" WIDTH="120"/>' .
                    '<FIELD attrname="Partidas" fieldtype="nested">' .
                        '<FIELDS>' .
                            '<FIELD attrname="Cuenta" fieldtype="string" WIDTH="21"/>' .
                            '<FIELD attrname="Depto" fieldtype="i4"/>' .
                            '<FIELD attrname="ConceptoPol" fieldtype="string" WIDTH="120"/>' .
                            '<FIELD attrname="Monto" fieldtype="r8"/>' .
                            '<FIELD attrname="TipoCambio" fieldtype="r8"/>' .
                            '<FIELD attrname="DebeHaber" fieldtype="string" WIDTH="1"/>' .
                        '</FIELDS>' .
                        '<PARAMS />' .
                    '</FIELD>' .
                '</FIELDS>' .
                '<PARAMS />' .
            '</METADATA>' .
            '<ROWDATA>' . $outTitle . '<Partidas>';
            foreach ($cargos as $row) {
                $table .= $row;
            }
            foreach ($abonos as $row) {
                $table .= $row;
            }

            if ($totalAbonos != $totalCargos) {
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
                $cuadreParameters = array();
                $cuadreParameters["diferencia"] = $totalCargos-$totalAbonos;

                $preparedStmt = preparedStatement($omiConn, $sqlCuadre, $sqlCuadreParameters, $cuadreParameters);    
                if (!$preparedStmt->execute()) {
                    error_log("Error de conexión ex: (" . $preparedStmt->errno . ") " . $preparedStmt->error);
                    die("Error de conexión: (" . $preparedStmt->errno . ") " . $preparedStmt->error);
                }
                $preparedStmt->bind_result( $cuadreHeader );
                if ($preparedStmt->fetch()) { 
                    $table .= $cuadreHeader;
                }
                $preparedStmt->close();
            }

    $table .= '</Partidas></ROW></ROWDATA></DATAPACKET>';
    $omiConn->close();

    $dom = new DOMDocument("1.0");
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML($table);

    error_log("Archivo " . $name);
    #Set headers
    header("Content-Type: application/xml");
    header("Content-Disposition: attachment; filename=\"{$name}\"");
    header("Content-Transfer-Encoding: binary");
    echo $dom->saveXML();    
} catch (Exception $exc) {
    error_log("FATAL Error de conexión: (" . $exc->getMessage());
    die($exc->getMessage());
}

