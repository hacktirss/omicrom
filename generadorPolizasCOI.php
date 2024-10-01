<?php
session_start();

// Detisa libraries
require("libnvo/mysqlUtils.php");
require_once("libnvo/concentrador.php");
// PHPExcel library
require_once("excel/PHPExcel.php");

try {
    $omiConn = getConnection("127.0.0.1","root","det15a");

    // Parsing request parameters
    parse_str(parse_url($_SERVER["REQUEST_URI"], PHP_URL_QUERY), $queryParameters);

    // Mandatory Parameter
    $poliza   = filter_var($queryParameters['poliza'],  FILTER_SANITIZE_STRING);
    $sistema  = filter_var($queryParameters['sistema'], FILTER_SANITIZE_STRING);
    $formato  = filter_var($queryParameters['formato'], FILTER_SANITIZE_STRING);

    $font = new PHPExcel_Style_Font();
    $font->setName("Helvetica");
    $font->setSize(8);

    // Create new PHPExcel object
    $objPHPExcel = new PHPExcel();
    // Set document properties
    $objPHPExcel->getProperties()->setCreator("DETI Desarrollo y Transferencia de Informática")
                                 ->setLastModifiedBy("DETI Desarrollo y Transferencia de Informática")
                                 ->setTitle("Póliza COI")
                                 ->setSubject("Póliza COI")
                                 ->setDescription("Póliza de Ingresos COI")
                                 ->setKeywords("COI Omicrom Detisa")
                                 ->setCategory("Póliza");

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
            error_log("Error de conexión: (" . $omiConn->errno . ") " . $omiConn->error);
            die("Error de conexión: (" . $omiConn->errno . ") " . $omiConn->error);
        }
    } else {
        error_log("Error de conexión: (" . $omiConn->errno . ") " . $omiConn->error);
        die("Error de conexión: (" . $omiConn->errno . ") " . $omiConn->error);
    }
    error_log($name);
    // Rename worksheet
    $objPHPExcel->getActiveSheet()->setTitle($name);
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
    $column = 0x41;
    while ($preparedStmt->fetch()) { 
        foreach($row as $key => $val) { 
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue(chr($column) . "3", $val);
            error_log(chr($column) . "3" . " = " . $val);
            $column = $column + 1;
        }
    }
    $preparedStmt->close();

    // Execute de dialy concentrator into concentrado temporary table
    $con = new concentrador($queryParameters);
    $con->execute();

    $arraySQLGrupos = array();
    $sqlGrupos = "SELECT G.* "
            . "FROM gruposT G "
            . "JOIN formatosT F ON F.id = G.id_fmt_fk "
            .     "AND F.nombre = '" . $poliza . "' "
            .     "AND F.sistema = '" . $sistema . "' "
            .     "AND F.formato = '" . $formato . "'";
    error_log($sqlGrupos);
    if (($qryGrupos = $omiConn->query($sqlGrupos))) {
        while (($rsGrupos = $qryGrupos->fetch_assoc())) {
            $gruposSQL = "SELECT " . $rsGrupos['campos']     . ", " . $rsGrupos['totalizador'] 
                    . " FROM concentrado "
                    . "JOIN (SELECT formatosT.id, "
                    .           "SUBSTRING( niveles_cc, 1, 1 ) uno, SUBSTRING( niveles_cc, 2, 1 ) dos, "
                    .           "SUBSTRING( niveles_cc, 3, 1 ) tres, SUBSTRING( niveles_cc, 4, 1 ) cuatro "
                    .       "FROM formatosT WHERE formatosT.nombre = '" . $poliza . "' "
                    .           "AND formatosT.sistema = '" . $sistema . "' "
                    .           "AND formatosT.formato = '" . $formato . "' ) formatosT ON TRUE "
                    . " WHERE Grupo = '" . $rsGrupos['id'] . "'" . $rsGrupos['groupBy'] . " ORDER BY NCC";
            array_push($arraySQLGrupos, $gruposSQL);
        }
    } else {
        error_log("Error (" . $omiConn->errno . ") " . $omiConn->error);
    }

    $i = 4;
    $total = 0;
    $objPHPExcel->setActiveSheetIndex(0);
    foreach ($arraySQLGrupos as $SQLGrupo) {
        error_log($SQLGrupo);
        if(($qryData = $con->getOmiConn()->query($SQLGrupo))) {
            $fieldNames = getFieldNames($qryData->fetch_fields());
            error_log(print_r($fieldNames, true));
            if ($qryData->num_rows>0) {
                while (($rsData = $qryData->fetch_assoc())) {
                    if ($rsData["Cargo"] > 0.00 || $rsData["Abono"] > 0.00) {
                        error_log(print_r($rsData, true));
                        $column = 0x41;
                        foreach ($fieldNames as $field) {
                            if ($field==="Cargo") {
                                $total = $total + $rsData[$field];
                            } else if ($field==="Abono") {
                                $total = $total - $rsData[$field];
                            }
                            if ($column===0x41) {
                                $objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit(chr($column) . "" . $i, $rsData[$field], PHPExcel_Cell_DataType::TYPE_STRING);
                            } else {
                                $objPHPExcel->setActiveSheetIndex(0)->setCellValue(chr($column) . "" . $i, $rsData[$field]);
                            }
                            $objPHPExcel->setActiveSheetIndex(0)->getCell(chr($column) . "" . $i)->getStyle()->setFont($font);
                            $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension(chr($column))->setAutoSize(true);
                            error_log(chr($column) . "" . $i . " = " . $rsData[$field]);
                            $column++;
                        }
                        $i++;
                    }
                }
            }
        } else {
            error_log("Error de conexión: (" . $omiConn->errno . ") " . $omiConn->error);
            die("Error obteniendo título: (" . $omiConn->errno . ") " . $omiConn->error);
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
        $cuadreParameters['abono'] = "0";
        $cuadreParameters['cargo'] = round($total, 2);
    } else if ($total>0) {
        $cuadreParameters['abono'] = round($total, 2);
        $cuadreParameters['cargo'] = "0";
    }

    error_log(implode(",", $cuadreParameters));

    $preparedStmt = preparedStatement($omiConn, $sqlCuadre, $sqlCuadreParameters, $cuadreParameters);    
    if (!$preparedStmt->execute()) {
        error_log("Error de conexión ex: (" . $preparedStmt->errno . ") " . $preparedStmt->error);
        die("Error de conexión: (" . $preparedStmt->errno . ") " . $preparedStmt->error);
    }

    $preparedStmt->bind_result($NCC, $D, $CON, $T, $B, $C, $A);
    while ($preparedStmt->fetch()) { 
        $objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicit(chr(0x41) . "" . $i, $NCC, PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->setActiveSheetIndex(0)->getCell(chr(0x41) . "" . $i)->getStyle()->setFont($font);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension(chr(0x41))->setAutoSize(true);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue(chr(0x42) . "" . $i, $D);
        $objPHPExcel->setActiveSheetIndex(0)->getCell(chr(0x42) . "" . $i)->getStyle()->setFont($font);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension(chr(0x42))->setAutoSize(true);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue(chr(0x43) . "" . $i, $CON);
        $objPHPExcel->setActiveSheetIndex(0)->getCell(chr(0x43) . "" . $i)->getStyle()->setFont($font);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension(chr(0x43))->setAutoSize(true);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue(chr(0x44) . "" . $i, $T);
        $objPHPExcel->setActiveSheetIndex(0)->getCell(chr(0x44) . "" . $i)->getStyle()->setFont($font);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension(chr(0x44))->setAutoSize(true);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue(chr(0x45) . "" . $i, $C);
        $objPHPExcel->setActiveSheetIndex(0)->getCell(chr(0x45) . "" . $i)->getStyle()->setFont($font);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension(chr(0x45))->setAutoSize(true);
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue(chr(0x46) . "" . $i, $A);
        $objPHPExcel->setActiveSheetIndex(0)->getCell(chr(0x46) . "" . $i)->getStyle()->setFont($font);
        $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension(chr(0x46))->setAutoSize(true);
    }
    $preparedStmt->close();


    // Set active sheet index to the first sheet, so Excel opens this as the first sheet
    $objPHPExcel->setActiveSheetIndex(0);
    // Redirect output to a client’s web browser (Excel5)
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $name . '.xls"');
    header('Cache-Control: max-age=0');
    // If you're serving to IE 9, then the following may be needed
    header('Cache-Control: max-age=1');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');

} catch (Exception $exc) {
    error_log("Error de conexión: (" . $exc->getMessage());
    die($exc->getMessage());
}
