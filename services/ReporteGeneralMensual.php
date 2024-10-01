<?php

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Turnos = "SELECT descripcion,turno FROM tur WHERE activo = 'Si';";
$Tns = utils\IConnection::getRowsFromQuery($Turnos);
$e = 1;
foreach ($Tns as $ts) {
    $TurnosActivs[$e] = $ts["descripcion"];
    $SqlAddTunor .= " LEFT JOIN (SELECT SUM(rm.importe) importe,date_format(fecha_venta,'%Y-%m-%d') fecha,rm.corte,egr.importe impEgr FROM rm "
            . "LEFT JOIN (SELECT importe,corte FROM (SELECT SUM(importe) importe,corte FROM egr WHERE corte > 0 GROUP BY corte) c) egr ON egr.corte = rm.corte "
            . "WHERE turno = " . $ts["turno"] . " AND DATE_FORMAT(rm.fecha_venta, '%Y-%m-%d') BETWEEN '$FechaI' AND '$FechaF' AND tipo_venta != 'J' group by fecha_venta,turno) t" . $ts["turno"] . " ON  DATE_FORMAT(rm.fecha_venta, '%Y-%m-%d' ) = t" . $ts["turno"] . ".fecha  ";
    $SqlSelected .= "t" . $ts["turno"] . ".importe turno" . $ts["turno"] . ",t" . $ts["turno"] . ".impEgr egr" . $ts["turno"] . ", ";
    $e++;
}

$Bancos = "SELECT alias concepto,id FROM cli WHERE tipodepago='Tarjeta'";
$Bnc = utils\IConnection::getRowsFromQuery($Bancos);
$e = 1;
foreach ($Bnc as $bn) {
    $BancosExist[$e] = $bn["concepto"];
    $BancosExistId["Bnc" . $e] = $bn["id"];
    $Vvl .= "SUM(IF(cli.tipodepago='Tarjeta' AND cli.alias='" . $bn["concepto"] . "',rm.pagoreal ,0)) b$e,";
    $VvlP .= "SUM(IF(p.aliaspago='" . $bn["concepto"] . "',p.importe ,0)) importePagos$e,";
    $AliasPagos .= "importePagos$e,";
    $e++;
}
$Monederos = "SELECT alias nombre,id FROM cli WHERE tipodepago = 'Monedero' AND alias NOT LIKE '%OMICROM%' GROUP BY alias;";
$Mnd = utils\IConnection::getRowsFromQuery($Monederos);
$e = 1;
foreach ($Mnd as $mn) {
    $MonederosExist[$e] = $mn["nombre"];
    $MonederosExistId[$e] = $mn["id"];
    $Vvl .= "SUM(IF(cli.tipodepago='Monedero' AND cli.alias='" . $mn["nombre"] . "',rm.pagoreal - rm.descuento ,0)) m$e,";
    $e++;
}

$SqlG = "SELECT DATE_FORMAT(fecha_venta, '%Y-%m-%d') AS Fecha, $Vvl $SqlSelected $AliasPagos SUM(IF(cli.tipodepago='Credito',rm.pagoreal - rm.descuento ,0)) creditoImp, SUM(IF(cli.tipodepago='Prepago',rm.pagoreal - rm.descuento,0)) prepagoImp,"
        . " IFNULL(pago_credito,0) pago_credito, IFNULL(pago_prepago,0) pago_prepago,SUM(case 
        WHEN cli.tipodepago = 'Contado' THEN rm.importe
        WHEN cli.tipodepago != 'Contado' THEN rm.importe - rm.pagoreal
        END) efectivoG "
        . "FROM rm "
        . "LEFT JOIN cli ON cli.id=rm.cliente "
        . "LEFT JOIN (SELECT fecha,$VvlP "
        . "SUM(IF(tipodepago='Credito',importe,0)) pago_credito,SUM(IF(tipodepago='Prepago',importe,0)) pago_prepago "
        . "FROM (SELECT DATE_FORMAT(fecha_deposito,'%Y-%m-%d') fecha,importe,formapago,cli.tipodepago,cli.alias aliaspago FROM pagos LEFT JOIN cli ON cli.id=pagos.cliente WHERE status='Cerrada') p "
        . "GROUP BY fecha) pagos ON pagos.fecha = DATE_FORMAT(rm.fecha_venta, '%Y-%m-%d')  "
        . "$SqlAddTunor"
        . "WHERE DATE_FORMAT(rm.fecha_venta, '%Y-%m-%d') BETWEEN '$FechaI' AND '$FechaF' AND  tipo_venta != 'J' GROUP BY fecha_venta";

$RsQ = utils\IConnection::getRowsFromQuery($SqlG);
foreach ($BancosExist as $Be) {
    utils\HTTPUtils::setSessionValue($Be, 0);
}
foreach ($MonederosExist as $Me) {
    utils\HTTPUtils::setSessionValue($Me, 0);
}
$numeroMes = date("m", strtotime($FechaI));
$numeroAnio = date("Y", strtotime($FechaI));
$SqlInicial = "SELECT mesNo,mes,importe_deuda,fecha_analisis,cli.alias FROM cxc_mensual LEFT JOIN cli ON cli.id=cxc_mensual.id_cli
WHERE mesNo='$numeroMes' AND anio = '$numeroAnio';";
$CxcInicial = utils\IConnection::getRowsFromQuery($SqlInicial);
foreach ($CxcInicial as $CxcI) {
    utils\HTTPUtils::setSessionValue($CxcI["alias"], $CxcI["importe_deuda"]);
}
$fecha = new DateTime($FechaF);
$fecha->modify('+1 month');
$nueva_fecha = $fecha->format('Y-m-d');
$numeroMes = date("m", strtotime($nueva_fecha));
$numeroAnio = date("Y", strtotime($nueva_fecha));
$SqlInicial = "SELECT mesNo,mes,importe_deuda,fecha_analisis,cli.alias FROM cxc_mensual LEFT JOIN cli ON cli.id=cxc_mensual.id_cli
WHERE mesNo='$numeroMes' AND anio = '$numeroAnio';";
$CxcInicial = utils\IConnection::getRowsFromQuery($SqlInicial);
foreach ($CxcInicial as $CxcI) {
    utils\HTTPUtils::setSessionValue($CxcI["alias"] . "Fin", $CxcI["importe_deuda"]);
}