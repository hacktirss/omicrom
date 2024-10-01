<?php
#Librerias
session_start();

include_once ("libnvo/lib.php");
require_once('tcpdf2/config/lang/eng.php');
require_once('tcpdf2/tcpdf.php');

$busca   = $_REQUEST['busca'];

function preparedStatement($omiConn, $sqlExp, $paramString, $queryParameters) {
    $psValues = array();
    $psFlags = "";

    if (!($preparedSmt = $omiConn->prepare($sqlExp))) {
        throw new Exception("Error de conexión ps: (" . $omiConn->errno . ") " . $omiConn->error);
    }

    $parameters = explode(",", $paramString);
    for ($i = 0; $i < count($parameters); $i++) {
        $psFlags = "s" . $psFlags;
    }
    array_push($psValues, $psFlags);
    foreach ($parameters as $parameter) {
        array_push($psValues, filter_var($queryParameters[$parameter], FILTER_SANITIZE_STRING));
    }
    call_user_func_array(array($preparedSmt, 'bind_param'), &$psValues);
    return $preparedSmt;
}

class CUSTOM_PDF extends TCPDF {
    public function Header() {
        $headerdata = $this->getHeaderData();
        $headerfont = $this->getHeaderFont();

        $this->SetFont($headerfont[0], '', $headerfont[2]-4);
        $this->Cell(0, 0, $headerdata['title'], 0, 1, 'C', 0, '', 0, false);
        $this->SetFont($headerfont[0], '', $headerfont[2]);
        $this->MultiCell(0, 0, $headerdata['string'], 0, 'C', 0, 2, 10, 10, false, 0, true, false);
        $this->SetLineStyle(array('width' => 1, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
        $this->Line(5, $this->y+1, $this->w - 5, $this->y+1);
    }
}

require_once('libnvo/concentrador.php');

try {
    $omiConn = iconnect();

    if ($omiConn->connect_errno 
            || !$omiConn->select_db("omicrom")
            || !($psSetLocale = $omiConn->prepare("SET lc_time_names = 'es_MX'"))
            || !$psSetLocale->execute()) {
        error_log("Error de conexión: (" . $omiConn->connect_errno . ") " . $omiConn->connect_error);
        die("Error de conexión: (" . $omiConn->connect_errno . ") " . $omiConn->connect_error);
    }

    // Parsing request parameters
    parse_str(parse_url($_SERVER["REQUEST_URI"], PHP_URL_QUERY), $queryParameters);

    // Mandatory Parameter
    $poliza  = filter_var($queryParameters['poliza'], FILTER_SANITIZE_STRING);

    // Gets Document titles
    $sqlTitle = '';
    $sqlTitleParameters = '';
    if ($qryTitle = $omiConn->query("SELECT titulo, parametros FROM titulosT T JOIN formatosT F ON F.id = T.id_fmt_fk WHERE F.nombre =  '$poliza'")) {
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

    // Gets contable information
    /*
    $qryTest = $con->getOmiConn()->query("SELECT * FROM concentrado");

    foreach ($qryTest->fetch_fields() as $key => $value) {
        foreach ($value as $key1 => $value1) {
            file_put_contents("/home/omicrom/concentrador.log", 
                    print_r("\n" . $value1 , true), FILE_APPEND);
            break;
        }
    }
    while (($rsTest = $qryTest->fetch_assoc())) {
        file_put_contents("/home/omicrom/concentrador.log", 
                print_r("\n" .  $rsTest['Grupo']  . "|" . $rsTest['Corte'] . "|" . $rsTest['NCC'] . "|" . $rsTest['TipoMovimiento'] . "|" . $rsTest['Importe'] . "|" . $rsTest['Concepto'], true), FILE_APPEND);
    }
    */

    $arraySQLGrupos = array();
    if (($qryGrupos = $con->getOmiConn()->query("SELECT G.* FROM gruposT G JOIN formatosT F ON F.id = G.id_fmt_fk AND F.nombre = '$poliza'"))) {
        while (($rsGrupos = $qryGrupos->fetch_assoc())) {
            $gruposSQL = "SELECT " . $rsGrupos['campos'] . " FROM concentrado WHERE Grupo = '" . $rsGrupos['id'] . "' " . $rsGrupos['groupBy'];
            array_push($arraySQLGrupos, $gruposSQL);
        }
    }

    foreach ($arraySQLGrupos as $SQLGrupo) {
        //file_put_contents("/home/omicrom/concentrador.log", print_r("\n" . $SQLGrupo, true), FILE_APPEND);
        if(($qryData = $con->getOmiConn()->query($SQLGrupo))) {
            $fieldNames = array();
            foreach ($qryData->fetch_fields() as $key => $value) {
                foreach ($value as $key1 => $value1) {
                    array_push($fieldNames, $value1);
                    //file_put_contents("/home/omicrom/concentrador.log", print_r("\n" . $value1 , true), FILE_APPEND);
                    break;
                }
            }
            while (($rsData = $qryData->fetch_assoc())) {
                $idx = 0;
                file_put_contents("/home/omicrom/concentrador.log", print_r("\n", true), FILE_APPEND);
                foreach ($fieldNames as $field) {
                    file_put_contents("/home/omicrom/concentrador.log", print_r(($idx++==0 ? "" : " "), true), FILE_APPEND);
                    file_put_contents("/home/omicrom/concentrador.log", print_r($rsData[$field], true), FILE_APPEND);
                }
            }
        } else {
            error_log("Error de conexión: (" . $con->getOmiConn()->errno . ") " . $con->getOmiConn()->error);
            die("Error obteniendo título: (" . $con->getOmiConn()->errno . ") " . $con->getOmiConn()->error);
        }
    }

    /*


    $sql = "SELECT UPPER(cia.cia) nombre FROM cia";
    if (($qryCIA = $omiConn->query($sql))) {
        if (($rsCIA = $qryCIA->fetch_assoc())) {
            $nombre    = $rsCIA['nombre'];
        }
    } else {
        error_log("Error de conexión: (" . $omiConn->errno . ") " . $omiConn->error);
        die("Error de conexión: (" . $omiConn->errno . ") " . $omiConn->error);
    }
    */

    define ("PDF_PAGE_FORMAT", "A4");
    define ("PDF_MARGIN_TOP", 30);
    define ("PDF_MARGIN_BOTTOM", 20);
    define ("PDF_FONT_SIZE_MAIN", 16);
    define ("PDF_FONT_SIZE_DATA", 8);

    $pdf = new CUSTOM_PDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true); 
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor(PDF_AUTHOR);
    $pdf->SetHeaderData('', 0, $outSubtitle, $outTitle);
    $pdf->SetFont('helvetica', '', 12);
    $pdf->SetMargins(10, 10, 10);
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
    $pdf->writeHTML('<div align="center"><h3></h3></div>', false, 0, false, 0);
    $pdf->SetTextColor();

    $pdf->Output();

    error_log("********************************************************************************************");
    $omiConn->close();
} catch (Exception $exc) {
    error_log("Error de conexión: (" . $exc->getMessage());
    die($exc->getMessage());
}

