<?php

session_start();

include "libnvo/lib.php";
include "excel/PHPExcel.php";

use com\softcoatl\utils as utils;

$mysqli = utils\IConnection::getConnection();
$request = utils\HTTPUtils::getRequest();

require "./services/ConsultasCatalogos.php";

//error_log($cSql);
$result = $mysqli->query($cSql);
error_log("Rows: " . $result->num_rows);
$count = $result->field_count;
error_log("Fields: " . $count);
$registros = utils\IConnection::getRowsFromQuery($cSql, $mysqli);

$objPHPExcel = new PHPExcel();
$objPHPExcel->getActiveSheet()->setTitle($Nombre);

$font = new PHPExcel_Style_Font();
$font->setName("Helvetica");
$font->setSize(10);

$sheet = 0;
$row = 1;
$column = 0X41;

/**
 * Set headers
 */
for ($i = 0; $i < $count; $i++) {
    $ident = chr($column) . "" . $row;
    $objPHPExcel->setActiveSheetIndex($sheet)->setCellValue($ident, ucwords(strtolower(field_name($result, $i))));
    $objPHPExcel->setActiveSheetIndex($sheet)->getCell($ident)->getStyle()->setFont($font);
    $objPHPExcel->setActiveSheetIndex($sheet)->getStyle($ident)->getFont()->setBold(true);
    $objPHPExcel->setActiveSheetIndex($sheet)->getStyle($ident)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $column = $column + 1;
}

/**
 * Fill data
 */
$arrayTotes = array(0 => "Gran Total");
$rows = 0;
if ($request->getAttribute("Detallado") === "Si"):
    $block = 0;
    $Texto = "SubTotal";
    if ($request->hasAttribute("Textos")) {
        $Texto = explode(",", $request->getAttribute("Textos"));
    }
    $Filtro = array(0);
    if ($request->hasAttribute("Filtro")) {
        $Filtro = explode(",", $request->getAttribute("Filtro"));
    }

    foreach ($Filtro as $key => $value) :
        error_log("Key: " . $key . " Value: " . $value);
        $arraySubTotes["sub" . $value] = array(0 => $Texto[$key]);
    endforeach;

    foreach ($registros as $datos) :
        $column = 0X41;
        $row++;

        for ($i = 0; $i < $count; $i++) :
            $ident = chr($column) . "" . $row;

            if ($i > 0):
                if (is_numeric($datos[$i])):
                    $arrayTotes[$i] = empty($arrayTotes[$i]) ? $datos[$i] : $arrayTotes[$i] + $datos[$i];
                endif;
            endif;
            $objPHPExcel->setActiveSheetIndex($sheet)->setCellValue($ident, $datos[$i]);
            $objPHPExcel->setActiveSheetIndex($sheet)->getCell($ident)->getStyle()->setFont($font);
            if (is_numeric($datos[$i])):
                $objPHPExcel->setActiveSheetIndex($sheet)->getStyle($ident)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            else:
                $objPHPExcel->setActiveSheetIndex($sheet)->getStyle($ident)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            endif;
            $column = $column + 1;
        endfor;

        foreach ($Filtro as $key => $value) :
            for ($i = 0; $i < $count; $i++) :
                if (is_numeric($datos[$i]) && $i > 0):
                    $arraySubTotes["sub" . $value][$i] = empty($arraySubTotes["sub" . $value][$i]) ? $datos[$i] : $arraySubTotes["sub" . $value][$i] + $datos[$i];
                endif;
            endfor;
        endforeach;

        $rows++;

        foreach ($Filtro as $key => $value) :
            if ($registros[$rows][$value] !== $datos[$value]) :
                $column = 0X41;
                $row++;

                for ($i = 0; $i < $count; $i++) :
                    $ident = chr($column) . "" . $row;
                    $objPHPExcel->setActiveSheetIndex($sheet)->setCellValue($ident, $arraySubTotes["sub" . $value][$i]);
                    $objPHPExcel->setActiveSheetIndex($sheet)->getCell($ident)->getStyle()->setFont($font);
                    $objPHPExcel->setActiveSheetIndex($sheet)->getStyle($ident)->getFont()->setBold(true);
                    $objPHPExcel->setActiveSheetIndex($sheet)->getStyle($ident)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                    $column = $column + 1;
                endfor;
                $block++;
                $arraySubTotes["sub" . $value] = array(0 => $Texto[$key]);
            endif;
        endforeach;

    endforeach;
else:
    foreach ($registros as $datos) :
        $column = 0X41;
        $row++;

        for ($i = 0; $i < $count; $i++) {
            $ident = chr($column) . "" . $row;

            if ($i > 0):
                if (is_numeric($datos[$i])):
                    $arrayTotes[$i] = empty($arrayTotes[$i]) ? $datos[$i] : $arrayTotes[$i] + $datos[$i];
                endif;
            endif;
            $objPHPExcel->setActiveSheetIndex($sheet)->setCellValue($ident, $datos[$i]);
            $objPHPExcel->setActiveSheetIndex($sheet)->getCell($ident)->getStyle()->setFont($font);
            if (is_numeric($datos[$i])):
                $objPHPExcel->setActiveSheetIndex($sheet)->getStyle($ident)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            else:
                $objPHPExcel->setActiveSheetIndex($sheet)->getStyle($ident)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
            endif;
            $column = $column + 1;
        }
    endforeach;
endif;

/**
 * Set totes
 */
$row++;
$column = 0X41;

for ($i = 0; $i < $count; $i++) {
    $ident = chr($column) . "" . $row;
    $objPHPExcel->setActiveSheetIndex($sheet)->setCellValue($ident, $arrayTotes[$i]);
    $objPHPExcel->setActiveSheetIndex($sheet)->getCell($ident)->getStyle()->setFont($font);
    $objPHPExcel->setActiveSheetIndex($sheet)->getStyle($ident)->getFont()->setBold(true);
    $objPHPExcel->setActiveSheetIndex($sheet)->getStyle($ident)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    cellColor($ident, "DADADA");
    $column = $column + 1;
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
header('Content-Disposition: attachment;filename="' . $Nombre . '.xlsx"');
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

function cellColor($cells, $color) {
    global $objPHPExcel;

    $objPHPExcel->getActiveSheet()->getStyle($cells)->getFill()->applyFromArray(array(
        "type" => PHPExcel_Style_Fill::FILL_SOLID,
        "startcolor" => array("rgb" => $color)
    ));
}
