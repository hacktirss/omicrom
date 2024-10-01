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
                    $headerRow = $headerData["H"];
                    $footerRow = $headerData["F"];
                    $abonosGrupo += $headerData["A"];
                    $cargosGrupo += $headerData["C"];
                    $totalAbonos += $headerData["A"];
                    $totalCargos += $headerData["C"];
                    error_log("Abonos Grupo " . $abonosGrupo . " " . $headerRow);
                    error_log("Cargos Grupo " . $cargosGrupo . " " . $headerRow);
                }
                error_log("ERR" . $connection->error);
                array_push($rowsGrupo, $headerRow);
                $contentRow = '';
                while (($rsData = $qryData->fetch_assoc())) {
                    error_log(print_r($rsData, true));
                    $idx = 0;
                    $contentRow = "";
                    error_log("*****************************************************************************************************************************" . $rsData['C']);
                    foreach ($fieldNames as $field) {
                        $contentRow .= $rsData[$field];
                    }
                    error_log($contentRow);
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

class CUSTOM_PDF extends TCPDF {
    public function Header() {
        
        $omiConn = com\softcoatl\utils\IConnection::getConnection();
        if (($qryName = $omiConn->query("SELECT CONCAT( cia, '- ', estacion ) cia FROM cia LIMIT 1")) && ($rsName = $qryName->fetch_assoc())) {
            $name = $rsName['cia'];
            error_log($name);
        }
        $fecha = "Fecha de Impresión ". ucfirst(strftime("%A %d de %B de %Y", date_create()->getTimestamp()));
        $headerdata = $this->getHeaderData();
        $headerfont = $this->getHeaderFont();
        error_log(print_r($headerfont, true));

        $this->SetTextColor(0x74, 0x9B, 0x9C);
        $this->SetFont($headerfont[0], '', $headerfont[2]);
        $this->Cell(0, 0, $name, 0, 1, 'L', 0, '', 0, false);
        $this->SetFont($headerfont[0], '', $headerfont[2]-2);
        $this->MultiCell(0, 0, $headerdata['title'], 0, 'L', 0, 2, 10, 10, false, 0, true, false);
        $this->MultiCell(0, 0, $headerdata['string'], 0, 'L', 0, 2, 10, 15, false, 0, true, false);
        $this->MultiCell(0, 0, $fecha, 0, 'L', 0, 2, 10, 20, false, 0, true, false);
        $this->SetLineStyle(array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0x74, 0x9B, 0x9C)));
        $this->Line(5, $this->y+1, $this->w - 5, $this->y+1);
        $this->SetTextColor();
    }
    // Page footer
    public function Footer() {
        $this->SetTextColor(0x74, 0x9B, 0x9C);			
        $line_width = 0.85 / $this->getScaleFactor();
        $this->SetLineStyle(array('width' => $line_width, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0x74, 0x9B, 0x9C)));
        $this->SetY(-15);
        $this->SetFont('helvetica', 'B', 10);
        $this->Line(5, $this->y+1, $this->w - 5, $this->y+1);
        $this->Cell(0, 10, "DETISA S.A. DE C.V. Texcoco Edo. de Méx. Tel. 01 595 9250401 http://detisa.com.mx", 0, false, 'C', 0, '', 0, false, 'T', 'M');
        $this->SetTextColor();
    }
}

try {
    if (!function_exists('set_magic_quotes_runtime')) {
        function set_magic_quotes_runtime($new_setting) {
            return true;
        }
    }
    $omiConn = com\softcoatl\utils\IConnection::getConnection();

    // Parsing request parameters
    parse_str(parse_url($_SERVER["REQUEST_URI"], PHP_URL_QUERY), $queryParameters);

    // Mandatory Parameter
    $poliza   = filter_var($queryParameters['poliza'],  FILTER_SANITIZE_STRING);
    $sistema  = filter_var($queryParameters['sistema'], FILTER_SANITIZE_STRING);
    $formato  = filter_var($queryParameters['formato'], FILTER_SANITIZE_STRING);

    $sqlName = "SELECT ID, CONCAT('OMICROM', '_', sistema, '_', nombre, '_', REPLACE( cia.estacion, ' ', '_' ), '_' ) name "
                . "FROM formatosT F JOIN cia ON TRUE  "
                . "WHERE F.nombre = '" . $poliza . "' "
                . "AND F.sistema = '" . $sistema . "' "
                . "AND F.formato = '" . $formato . "' ";
    // Gets downloaded file name  get file name on a library
    if (($qryName = $omiConn->query($sqlName))) {
        if (($rsName = $qryName->fetch_assoc())) {
            $name = $rsName["name"] . str_replace("-", "", $queryParameters["fecha"]);
            $idFMT = $rsName['ID'];
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

    $outTitle = NULL;
    $outSubtitle = NULL;

    $preparedStmt->bind_result($outTitle, $outSubtitle);
    while ($preparedStmt->fetch()) {
        error_log($outTitle . " " . $outSubtitle);
    }//for each row
    $preparedStmt->close();

    // Execute de dialy concentrator into concentrado temporary table
    $con = new concentrador($queryParameters);
    $con->execute();

    $abonos = totalizaApartado($con->getOmiConn(), $poliza, $sistema, $formato, "A");
    $cargos = totalizaApartado($con->getOmiConn(), $poliza, $sistema, $formato, "C");

    $table = "<table>";
    $table .= "<tr><td style=\"width: 2.5cm;\">Cuenta</td><td style=\"width: 10cm;\">Descripción</td><td style=\"width: 2cm;\">Parcial</td><td style=\"width: 2cm; text-align: right; font-weight:bold;\">Debe</td><td style=\"width: 2cm; text-align: right; font-weight:bold; border: 2px solid #000000;\">Haber</td></tr>";
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
        $cuadreParameters["abono"] = $totalAbonos < $totalCargos ? round( abs( $totalAbonos-$totalCargos ), 2 ) : "0.00";
        $cuadreParameters["cargo"] = $totalCargos < $totalAbonos ? round( abs( $totalAbonos-$totalCargos ), 2 ) : "0.00";
    
        $preparedStmt = preparedStatement($omiConn, $sqlCuadre, $sqlCuadreParameters, $cuadreParameters);    
        if (!$preparedStmt->execute()) {
            error_log("Error de conexión ex: (" . $preparedStmt->errno . ") " . $preparedStmt->error);
            die("Error de conexión: (" . $preparedStmt->errno . ") " . $preparedStmt->error);
        }
        $preparedStmt->bind_result( $cuadreHeader, $cuadreTotal, $cuadreFooter );
        if ($preparedStmt->fetch()) { 
            $table .= $cuadreHeader;
            $table .= $cuadreTotal;
            $table .= $cuadreFooter;
            if ($totalAbonos < $totalCargos) {
                $totalAbonos += round( abs( $totalAbonos-$totalCargos ), 2 );
            } else if ($totalAbonos > $totalCargos) {
                $totalCargos += round( abs( $totalAbonos-$totalCargos ), 2 );
            }
        }
        $preparedStmt->close();
    }

    $table .= "<tr><td style=\"width: 2.5cm;\"></td><td style=\"width: 10cm;\"></td><td style=\"width: 2cm;\"></td><td style=\"width: 2cm; text-align: right; font-weight:bold;\">" . str_pad($totalCargos, 16, " ") . "</td><td style=\"width: 2cm; text-align: right; font-weight:bold; border: 2px solid #000000;\">" . str_pad($totalAbonos, 16, " ") . "</td></tr>";
    $table .= "</table>";
    $html .= $table;
    define ("PDF_PAGE_FORMAT", "A4");
    define ("PDF_MARGIN_TOP", 30);
    define ("PDF_MARGIN_BOTTOM", 20);
    define ("PDF_FONT_SIZE_MAIN", 16);
    define ("PDF_FONT_SIZE_DATA", 8); 

    $pdf = new CUSTOM_PDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true); 
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor(PDF_AUTHOR);
    $pdf->SetHeaderData('', 0, $outSubtitle, $outTitle);
    $pdf->SetTitle($outTitle);
    $pdf->SetFont('helvetica', '', 12);
    $pdf->SetMargins(10, 35, 10);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); //set image scale factor
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->setLanguageArray($l); //set language items
    $pdf->AliasNbPages();
    $pdf->AddPage();

    $pdf->SetTextColor();
    $pdf->SetFont('helvetica', '', 7    );
    $pdf->writeHTML($html, true, 0, true, 1);
    $pdf->SetTextColor();

    $pdf->Output($name . ".pdf", 'D');

    $omiConn->close();
} catch (Exception $exc) {
    error_log("FATAL Error de conexión: (" . $exc->getMessage());
    die($exc->getMessage());
}

