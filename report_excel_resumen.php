<?php

session_start();

include "libnvo/lib.php";
include "excel/PHPExcel.php";

use com\softcoatl\utils as utils;

$mysqli = utils\IConnection::getConnection();
$request = utils\HTTPUtils::getRequest();

$Nombre = $request->getAttribute("Nombre");
$Com = "SELECT clave,clavei,descripcion FROM com WHERE activo = 'Si';";

$registros = utils\IConnection::getRowsFromQuery($Com);
$object = array();
foreach ($registros as $rg) {
    $cSql = "SELECT '" . $rg["descripcion"] . "' Descripcion,TH.cantidad InventarioFinal,
            SUM(ME.inc) 'Incremento Lts',ROUND(SUM(ME.vf),2) 'Compras Lts',sum(ME.ifc) 'Compras $',
            V.volum 'Vendido Lts',V.importe 'Vendido $',IFNULL(F.importe,0.00) 'Facturado $',TL.importe 'Monederos $',
            round(V.importe - IFNULL(F.importe, 0.00) - IFNULL(TL.importe, 0.00) , 2) 'Por facturar $',V.movimientos
            FROM 
                (SELECT COUNT(*) movimientos,ROUND(SUM(importe),2) importe, producto,ROUND(sum(importe/precio),2) volum
                FROM rm 
                WHERE month(rm.fin_venta) = month('" . $request->getAttribute("Fecha") . "') AND rm.tipo_venta = 'D'
                AND producto='" . $rg["clavei"] . "') V
            LEFT JOIN (
                SELECT count(*) movimientos,ROUND(SUM(rm.importe),2) importe, producto
                FROM rm 
                WHERE month(rm.fin_venta) = month('" . $request->getAttribute("Fecha") . "') 
                AND rm.uuid <> '-----' AND rm.tipo_venta='D' AND producto='" . $rg["clavei"] . "'
            ) F ON F.producto = V.producto
            LEFT JOIN (
                SELECT COUNT(*) movimientosL, ROUND(SUM(fcd.importe),2) importe ,
                '" . $rg["clavei"] . "' prdcs
                FROM fcd
                LEFT JOIN fc ON fcd.id = fc.id 
                LEFT JOIN cli ON fc.cliente = cli.id
                WHERE (fcd.ticket = 0 OR fcd.ticket IS NULL)
                AND (cli.rfc NOT LIKE 'XAXX010101000' OR cli.tipodepago = 'Monedero') 
                AND fc.status = 1
                AND month(fc.fecha) = month('" . $request->getAttribute("Fecha") . "') AND fcd.producto = 
                (select inv.id from com LEFT JOIN inv ON com.descripcion = inv.descripcion 
            WHERE clavei ='" . $rg["clavei"] . "'))
            TL ON V.producto = TL.prdcs
            LEFT JOIN (
                SELECT sum(incremento) inc, sum(volumenfac) vf, sum(importefac) ifc,
                '" . $rg["clavei"] . "' product FROM omicrom.me WHERE month(fechae) = month('" . $request->getAttribute("Fecha") . "') 
                AND producto = '" . $rg["clave"] . "') 
            ME ON ME.product = V.producto
            LEFT JOIN (
                SELECT SUM(cantidad) cantidad, '" . $rg["clavei"] . "' product  
            FROM (SELECT * FROM (
                    SELECT IFNULL(ROUND(volumen_actual , 3), 0) cantidad,
                DATE ( fecha_hora_s ) fecha, tanque FROM tanques_h WHERE producto LIKE '%" . $rg["descripcion"] . "%' 
                AND DATE ( fecha_hora_s ) = DATE_ADD(DATE('" . $request->getAttribute("Fecha") . "'),INTERVAL 1 MONTH) 
            ORDER BY fecha_hora_s ASC ) t GROUP BY DATE ( t.fecha ),t.tanque ) 
            t GROUP BY DATE ( fecha )) 
            TH ON  TH.product = V.producto;";
    if (($query = $mysqli->query($cSql))) {
        while (($rs = $query->fetch_array())) {
            $object[] = $rs;
        }
    }
}

$result = $mysqli->query($cSql);
error_log("Rows: " . $result->num_rows);
$count = $result->field_count;
error_log("Fields: " . $count);
$registros = $object;
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
    cellColor($ident, "B2B2B2");
    $column = $column + 1;
}

/**
 * Fill data
 */
$arrayTotes = array(0 => "Gran Total");
$rows = 0;

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

/* * Exportamos Jarreos *** */

$cSql2 = "SELECT producto Producto,COUNT(*) Movimientos,ROUND(SUM(importe),2) Importe,sum(volumen) Volumen
        FROM rm 
        WHERE month(rm.fin_venta) = month('" . $request->getAttribute("Fecha") . "') AND rm.tipo_venta = 'J' group by producto";
error_log($cSql2);
$result = $mysqli->query($cSql2);
error_log("Rows 2da: " . $result->num_rows);
$count = $result->field_count;
$Jarreos = array();

if (($query = $mysqli->query($cSql2))) {
    while (($rs = $query->fetch_array())) {
        $object2[] = $rs;
    }
}

$registros = $object2;
error_log("Fields 2da: " . $count);

$sheet = 0;
$row = 7;
$column = 0X41;

/**
 * Set headers
 */
for ($e = 0; $e < $count; $e++) {
    $ident = chr($column) . "" . $row;
    error_log("SEGUNDA " . $ident);
    $objPHPExcel->setActiveSheetIndex($sheet)->setCellValue($ident, ucwords(strtolower(field_name($result, $e))));
    $objPHPExcel->setActiveSheetIndex($sheet)->getCell($ident)->getStyle()->setFont($font);
    $objPHPExcel->setActiveSheetIndex($sheet)->getStyle($ident)->getFont()->setBold(true);
    $objPHPExcel->setActiveSheetIndex($sheet)->getStyle($ident)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    cellColor($ident, "B2B2B2");
    $column = $column + 1;
}

/**
 * Fill data
 */
$arrayTotese = array(0 => "Gran Total");
$rows = 7;

foreach ($registros as $datos) :
    $column = 0X41;
    $row++;
    error_log("REGISTROS 2da" . print_r($datos, true));
    for ($e = 0; $e < $count; $e++) {
        $ident = chr($column) . "" . $row;

        if ($e > 0):
            if (is_numeric($datos[$e])):
                $arrayTotese[$e] = empty($arrayTotese[$e]) ? $datos[$e] : $arrayTotese[$e] + $datos[$e];
            endif;
        endif;
        error_log("SEGUNDA 2" . $ident);
        $objPHPExcel->setActiveSheetIndex($sheet)->setCellValue($ident, $datos[$e]);
        $objPHPExcel->setActiveSheetIndex($sheet)->getCell($ident)->getStyle()->setFont($font);
        if (is_numeric($datos[$e])):
            $objPHPExcel->setActiveSheetIndex($sheet)->getStyle($ident)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        else:
            $objPHPExcel->setActiveSheetIndex($sheet)->getStyle($ident)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        endif;
        $column = $column + 1;
    }
endforeach;

/**
 * Set totes
 */
$row++;
$column = 0X41;

for ($e = 0; $e < $count; $e++) {
    $ident = chr($column) . "" . $row;
    $objPHPExcel->setActiveSheetIndex($sheet)->setCellValue($ident, $arrayTotese[$e]);
    $objPHPExcel->setActiveSheetIndex($sheet)->getCell($ident)->getStyle()->setFont($font);
    $objPHPExcel->setActiveSheetIndex($sheet)->getStyle($ident)->getFont()->setBold(true);
    $objPHPExcel->setActiveSheetIndex($sheet)->getStyle($ident)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    cellColor($ident, "DADADA");
    $column = $column + 1;
}

/* * **** Exportamos Facturacion por prefijos y series ****** */

$cSql3 = "SELECT count(*) movimientos,ROUND(SUM(rm.importe),2) importe,fc.serie
        FROM rm LEFT JOIN fc ON rm.uuid=fc.uuid
        WHERE month(rm.fin_venta) = month('" . $request->getAttribute("Fecha") . "') 
        AND rm.uuid <> '-----' AND rm.tipo_venta='D'  group by fc.serie;";
error_log($cSql2);
$result = $mysqli->query($cSql3);
error_log("Rows 3ra: " . $result->num_rows);
$count = $result->field_count;
$Jarreos = array();

if (($query = $mysqli->query($cSql3))) {
    while (($rs = $query->fetch_array())) {
        $object3[] = $rs;
    }
}

$registros = $object3;
error_log("Fields 3ra: " . $count);

$sheet = 0;
$row = 13;
$column = 0X41;

/**
 * Set headers
 */
for ($e = 0; $e < $count; $e++) {
    $ident = chr($column) . "" . $row;
    error_log("3ra. " . $ident);
    $objPHPExcel->setActiveSheetIndex($sheet)->setCellValue($ident, ucwords(strtolower(field_name($result, $e))));
    $objPHPExcel->setActiveSheetIndex($sheet)->getCell($ident)->getStyle()->setFont($font);
    $objPHPExcel->setActiveSheetIndex($sheet)->getStyle($ident)->getFont()->setBold(true);
    $objPHPExcel->setActiveSheetIndex($sheet)->getStyle($ident)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    cellColor($ident, "B2B2B2");
    $column = $column + 1;
}

/**
 * Fill data
 */
$arrayTotese = array(0 => "Gran Total");
$rows = 7;

foreach ($registros as $datos) :
    $column = 0X41;
    $row++;
    error_log("REGISTROS 2da" . print_r($datos, true));
    for ($e = 0; $e < $count; $e++) {
        $ident = chr($column) . "" . $row;

        if ($e > 0):
            if (is_numeric($datos[$e])):
                $arrayTotese[$e] = empty($arrayTotese[$e]) ? $datos[$e] : $arrayTotese[$e] + $datos[$e];
            endif;
        endif;
        error_log("SEGUNDA 2" . $ident);
        $objPHPExcel->setActiveSheetIndex($sheet)->setCellValue($ident, $datos[$e]);
        $objPHPExcel->setActiveSheetIndex($sheet)->getCell($ident)->getStyle()->setFont($font);
        if (is_numeric($datos[$e])):
            $objPHPExcel->setActiveSheetIndex($sheet)->getStyle($ident)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        else:
            $objPHPExcel->setActiveSheetIndex($sheet)->getStyle($ident)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        endif;
        $column = $column + 1;
    }
endforeach;

/**
 * Set totes
 */
$row++;
$column = 0X41;

for ($e = 0; $e < $count; $e++) {
    $ident = chr($column) . "" . $row;
    $objPHPExcel->setActiveSheetIndex($sheet)->setCellValue($ident, $arrayTotese[$e]);
    $objPHPExcel->setActiveSheetIndex($sheet)->getCell($ident)->getStyle()->setFont($font);
    $objPHPExcel->setActiveSheetIndex($sheet)->getStyle($ident)->getFont()->setBold(true);
    $objPHPExcel->setActiveSheetIndex($sheet)->getStyle($ident)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    cellColor($ident, "DADADA");
    $column = $column + 1;
}

/* * *** */





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

function get_key($array, $index) {
    $idx = 0;
    while ($idx != $index && next($array)) {
        $idx++;
    }
    if ($idx == $index) {
        return key($array);
    }
    return "";
}
