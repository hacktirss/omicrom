<?php

session_start();

include "./libnvo/lib.php";
include "./excel/PHPExcel.php";

set_time_limit(600);

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$mysqli = iconnect();

$name = "Reporte";
if ($request->hasAttribute("Nombre") && !empty($request->getAttribute("Nombre"))) {
    $name = $request->getAttribute("Nombre");
}
$name .= "_" . date("His");

$cSql = rawurldecode($request->getAttribute("cSql"));
$objPHPExcel = new PHPExcel();
$objPHPExcel->getActiveSheet()->setTitle($name);

$objPHPExcel->getProperties()->setCreator("DETI Desarrollo y Transferencia de Informática")
        ->setLastModifiedBy("DETI Desarrollo y Transferencia de Informática")
        ->setTitle($name)
        ->setSubject($name)
        ->setDescription($name)
        ->setKeywords("Reporte Omicrom Detisa")
        ->setCategory($name);

$result = $mysqli->query($cSql);
error_log("Rows: " . $result->num_rows);
$count = $result->field_count;
error_log("Fields: " . $count);

$font = new PHPExcel_Style_Font();
$font->setName("Helvetica");
$font->setSize(10);

$sheet = 0;
$row = 1;
$column = 0X41;

for ($i = 0; $i < $count; $i++) {
    $ident = chr($column) . "" . $row;
    $objPHPExcel->setActiveSheetIndex($sheet)->setCellValue($ident, ucwords(strtolower(field_name($result, $i))));
    $objPHPExcel->setActiveSheetIndex($sheet)->getCell($ident)->getStyle()->setFont($font);
    $objPHPExcel->setActiveSheetIndex($sheet)->getStyle($ident)->getFont()->setBold(true);
    $objPHPExcel->setActiveSheetIndex($sheet)->getStyle($ident)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $column = $column + 1;
}


$i = 0;
while ($datos = $result->fetch_array()) {
    $column = 0X41;
    $row++;

    for ($i = 0; $i < $count; $i++) {
        $ident = chr($column) . "" . $row;
        $objPHPExcel->setActiveSheetIndex($sheet)->setCellValue($ident, $datos[$i]);
        $objPHPExcel->setActiveSheetIndex($sheet)->getCell($ident)->getStyle()->setFont($font);
        if (is_numeric($datos[$i])) {
            $objPHPExcel->setActiveSheetIndex($sheet)->getStyle($ident)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        } else {
            $objPHPExcel->setActiveSheetIndex($sheet)->getStyle($ident)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        }
        $column = $column + 1;
    }
    $i++;
}
$cSql2 = rawurldecode($request->getAttribute("cSql2"));
$result = $mysqli->query($cSql2);
error_log("Rows: " . $result->num_rows);
$count = $result->field_count;
error_log("Fields: " . $count);

$font = new PHPExcel_Style_Font();
$font->setName("Helvetica");
$font->setSize(10);

$sheet = 0;
$row = $i;
$column = 0X41;

for ($i = 0; $i < $count; $i++) {
    $ident = chr($column) . "" . $row;
    $objPHPExcel->setActiveSheetIndex($sheet)->setCellValue($ident, ucwords(strtolower(field_name($result, $i))));
    $objPHPExcel->setActiveSheetIndex($sheet)->getCell($ident)->getStyle()->setFont($font);
    $objPHPExcel->setActiveSheetIndex($sheet)->getStyle($ident)->getFont()->setBold(true);
    $objPHPExcel->setActiveSheetIndex($sheet)->getStyle($ident)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $column = $column + 1;
}



while ($datos = $result->fetch_array()) {
    $column = 0X41;
    $row++;

    for ($i = 0; $i < $count; $i++) {
        $ident = chr($column) . "" . $row;
        $objPHPExcel->setActiveSheetIndex($sheet)->setCellValue($ident, $datos[$i]);
        $objPHPExcel->setActiveSheetIndex($sheet)->getCell($ident)->getStyle()->setFont($font);
        if (is_numeric($datos[$i])) {
            $objPHPExcel->setActiveSheetIndex($sheet)->getStyle($ident)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        } else {
            $objPHPExcel->setActiveSheetIndex($sheet)->getStyle($ident)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        }
        $column = $column + 1;
    }
}



// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex($sheet);

// Auto size columns for each worksheet
$sheet = $objPHPExcel->getActiveSheet();
$cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
$cellIterator->setIterateOnlyExistingCells(true);
/** @var PHPExcel_Cell $cell */
foreach ($cellIterator as $cell) {
    $sheet->getColumnDimension($cell->getColumn())->setAutoSize(true);
}

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
ob_end_clean();
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $name . '.xlsx"');
header('Cache-Control: max-age=0');
header('Cache-Control: max-age=1');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0
$objWriter->save('php://output');

$mysqli->close();

function field_name($result, $field_offset) {
    $properties = mysqli_fetch_field_direct($result, $field_offset);
    return is_object($properties) ? $properties->name : null;
}
