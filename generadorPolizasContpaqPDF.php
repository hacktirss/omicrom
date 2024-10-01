<?php
#Librerias
session_start();

// Detisa libraries
require_once("libnvo/mysqlUtils.php");
require_once("libnvo/concentrador.php");
// TCPDF libraries
require_once("tcpdf2/config/lang/eng.php");
require_once("tcpdf2/tcpdf.php");
  
$totalCargos = 0;
$totalAbonos = 0;

function totalizaApartado($connection, $poliza, $sistema, $formato, $cargoAbono) {
    global $totalCargos ;
    global $totalAbonos;

    $rowApartado = array();
    $arraySQLGrupos = array();
    $arraySQLHeaders = array();

    error_log($poliza);
    error_log($sistema);
    error_log($formato);

    $sqlGrupos = "SELECT "
                    . "gruposT.id_fmt_fk, gruposT.id, gruposT.campos, gruposT.encabezado, gruposT.totalizador, gruposT.condicion, gruposT.groupBy "
                . "FROM formatosT JOIN gruposT ON formatosT.id = gruposT.id_fmt_fk "
                .           "AND formatosT.nombre = '" . $poliza . "' "
                .           "AND formatosT.sistema = '" . $sistema . "' "
                .           "AND formatosT.formato = '" . $formato . "' "
                . "ORDER BY gruposT.orden";
    error_log($sqlGrupos);
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
            error_log($headerSQL);
            error_log($gruposSQL);
            array_push($arraySQLGrupos, $gruposSQL);
            array_push($arraySQLHeaders, $headerSQL);
        }
    } else {
        error_log("Error (" . $connection->errno . ") " . $connection->error);
    }

    $jdx = 0;
    foreach ($arraySQLGrupos as $SQLGrupo) {
        $movimientosGrupo = 0;
        $rowsGrupo = array();
        if(($qryData = $connection->query($SQLGrupo))) {
            $fieldNames = getFieldNames($qryData->fetch_fields());
            if ($qryData->num_rows>0) {
                if(($headerQryData = $connection->query($arraySQLHeaders[$jdx])) && ($headerData = $headerQryData->fetch_assoc())) {
                    $headerNames = getFieldNames($headerQryData->fetch_fields());
                    $kdx = 0;
                    $headerRow = "<strong>";
                    foreach ($headerNames as $header) {
                        $headerRow = $headerRow . ($kdx++==0 ? "" : " ") . $headerData[$header];
                    }
                    array_push($rowsGrupo, $headerRow . "</strong>");
                }
                $contentRow = '';
                while (($rsData = $qryData->fetch_assoc())) {
                    $idx = 0;
                    $contentRow = "";
                    $movimientosGrupo += $rsData['I'];
                    $totalAbonos = $totalAbonos + ($cargoAbono=='A' ? $rsData['I'] : 0);
                    $totalCargos = $totalCargos + ($cargoAbono=='C' ? $rsData['I'] : 0);
                    error_log("*****************************************************************************************************************************" . $rsData['C']);
                    foreach ($fieldNames as $field) {
                        $contentRow = $contentRow . ($idx++==0 ? "" : " ") . $rsData[$field];
                    }
                    array_push($rowsGrupo, $contentRow);
                }
            }
            error_log("Total Grupo " . $movimientosGrupo . " " . $headerRow);
            if ($movimientosGrupo>0) {
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
        $headerdata = $this->getHeaderData();
        $headerfont = $this->getHeaderFont();

        $this->SetTextColor(0x74, 0x9B, 0x9C);			
        $this->SetFont($headerfont[0], '', $headerfont[2]-4);
        $this->Cell(0, 0, $headerdata['title'], 0, 1, 'C', 0, '', 0, false);
        $this->SetFont($headerfont[0], '', $headerfont[2]);
        $this->MultiCell(0, 0, $headerdata['string'], 0, 'C', 0, 2, 10, 10, false, 0, true, false);
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

    $sqlName = "SELECT ID, CONCAT('OMICROM', '_', sistema, '_', nombre) name "
                . "FROM formatosT F "
                . "WHERE F.nombre = '" . $poliza . "' "
                . "AND F.sistema = '" . $sistema . "' "
                . "AND F.formato = '" . $formato . "' ";
    // Gets downloaded file name  get file name on a library
    if (($qryName = $omiConn->query($sqlName))) {
        if (($rsName = $qryName->fetch_assoc())) {
            $name = $rsName['name'];
            $idFMT = $rsName['ID'];
        } else {
            error_log("Error de conexión 1: (" . $omiConn->errno . ") " . $omiConn->error);
            die("Error de conexión 1: (" . $omiConn->errno . ") " . $omiConn->error);
        }
    } else {
        error_log("Error de conexión 2: (" . $omiConn->errno . ") " . $omiConn->error);
        die("Error de conexión 2: (" . $omiConn->errno . ") " . $omiConn->error);
    }
    error_log($name);

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
    error_log($sqlTitulos);
    if (($qryTitle = $omiConn->query($sqlTitulos))) {
        if (($rsTitle = $qryTitle->fetch_assoc())) {
            $sqlTitle = $rsTitle['titulo'];
            $sqlTitleParameters = $rsTitle['parametros'];
        }
    } else {
        error_log("Error obteniendo título : (" . $omiConn->errno . ") " . $omiConn->error);
        die("Error obteniendo título: (" . $omiConn->errno . ") " . $omiConn->error);
    }
    error_log($sqlTitle);
    error_log($sqlTitleParameters);
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
    $cuadreParameters['importeCuadre'] = round( abs( $totalAbonos-$totalCargos ), 2 );
    
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

    if ($totalAbonos<$totalCargos) {
        foreach (explode("<br/>", $cuadreTotal) as $spt) {
            array_push($abonos, $spt);
        }
    } else if ($totalAbonos>$totalCargos) {
        foreach (explode("<br/>", $cuadreTotal) as $spt) {
            array_push($cargos, $spt);
        }
    }

    array_push($abonos, "<strong>               SUMAS IGUALES                         " . str_pad($totalAbonos+($totalAbonos<$totalCargos ? round(abs($totalAbonos-$totalCargos), 2) : 0), 16, " ") . "</strong>");
    array_push($cargos, "<strong>               SUMAS IGUALES                         " . str_pad($totalCargos+($totalAbonos>$totalCargos ? round(abs($totalAbonos-$totalCargos), 2) : 0), 16, " ") . "</strong>");

    $fillAbono  = sizeof($cargos)>sizeof($abonos) ? sizeof($cargos) - sizeof($abonos) : 0;
    $fillCargos = sizeof($cargos)>sizeof($abonos) ? 0 : sizeof($abonos) - sizeof($cargos);

    
    $html = "<table><tr><td><tt>";
    $i = 1;
    foreach ($cargos as $row) {
        if ($i++==sizeof($cargos)) {
            for ($k=0; $k<$fillCargos; $k++) {
                $html = $html . "<br/>";
            }
            $html = $html . "<br/>";
        }
        $html = $html . str_replace(" ", "&nbsp;", $row) . "<br/>";
    }
    $html = $html . "</tt></td><td><tt>";

    $i = 1;
    foreach ($abonos as $row) {
        if ($i++==sizeof($abonos)) {
            for ($k=0; $k<$fillAbono; $k++) {
                $html = $html . "<br/>";
            }
            $html = $html . "<br/>";
        }
        $html = $html . str_replace(" ", "&nbsp;", $row) . "<br/>";
    }
    $html = $html . "</tt></td></tr></table>";
    error_log($html);

    define ("PDF_PAGE_FORMAT", "A4");
    define ("PDF_MARGIN_TOP", 30);
    define ("PDF_MARGIN_BOTTOM", 20);
    define ("PDF_FONT_SIZE_MAIN", 16);
    define ("PDF_FONT_SIZE_DATA", 8); 

    $pdf = new CUSTOM_PDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true); 
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor(PDF_AUTHOR);
    $pdf->SetHeaderData('', 0, $outSubtitle, $outTitle);
    $pdf->SetTitle($outTitle);
    $pdf->SetFont('helvetica', '', 12);
    $pdf->SetMargins(10, 20, 10);
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
    $pdf->SetFont('helvetica', '', 8    );
    $pdf->writeHTML($html, true, 0, true, 1);
    $pdf->SetTextColor();

    $pdf->Output($name . ".pdf", 'D');

    $omiConn->close();
} catch (Exception $exc) {
    error_log("FATAL Error de conexión: (" . $exc->getMessage());
    die($exc->getMessage());
}

