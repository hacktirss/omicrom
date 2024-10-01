<?php

/*
 * Generar las consultas que se utilizaran en los reportes
 */
include_once ("libnvo/lib.php");
include_once ("data/FcDAO.php");
include_once ("data/ClientesDAO.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$mysqli = iconnect();
$sanitize = SanitizeUtil::getInstance();

//error_log(print_r($request, true));

$ciaDAO = new CiaDAO();
$ciaVO = $ciaDAO->retrieve(1);
$ConcentrarVtasTarjeta = $ciaVO->getVentastarxticket();

if ($request->hasAttribute("criteria")) {
    utils\HTTPUtils::setSessionValue("Fecha", date("Y-m-d"));
    utils\HTTPUtils::setSessionValue("FechaI", date("Y-m-01"));
    utils\HTTPUtils::setSessionValue("FechaF", date("Y-m-d"));
    utils\HTTPUtils::setSessionValue("HoraI", "**");
    utils\HTTPUtils::setSessionValue("HoraF", "**");
    utils\HTTPUtils::setSessionValue("Turno", "*");
    utils\HTTPUtils::setSessionValue("Detallado", "Si");
    utils\HTTPUtils::setSessionValue("Desglose", "Cortes");
    utils\HTTPUtils::setSessionValue("Despachador", 0);
    utils\HTTPUtils::setSessionValue("SDespachador", "");
    utils\HTTPUtils::setSessionValue("orden", "cxc.corte");
    utils\HTTPUtils::setSessionValue("ordenPago", "pagosdesp.fecha");
}


if ($request->hasAttribute("Todos")) {
    utils\HTTPUtils::setSessionValue("Todos", $sanitize->sanitizeString("Todos"));
} else {
    utils\HTTPUtils::setSessionValue("Todos", "No");
}
if ($request->hasAttribute("Fecha")) {
    utils\HTTPUtils::setSessionValue("Fecha", $sanitize->sanitizeString("Fecha"));
}
if ($request->hasAttribute("FechaI")) {
    utils\HTTPUtils::setSessionValue("FechaI", $sanitize->sanitizeString("FechaI"));
}
if ($request->hasAttribute("FechaF")) {
    utils\HTTPUtils::setSessionValue("FechaF", $sanitize->sanitizeString("FechaF"));
}
if ($request->hasAttribute("HoraI")) {
    utils\HTTPUtils::setSessionValue("HoraI", $sanitize->sanitizeString("HoraI"));
}
if ($request->hasAttribute("HoraF")) {
    utils\HTTPUtils::setSessionValue("HoraF", $sanitize->sanitizeString("HoraF"));
}
if ($request->hasAttribute("Turno")) {
    utils\HTTPUtils::setSessionValue("Turno", $sanitize->sanitizeString("Turno"));
}
if ($request->hasAttribute("Desglose")) {
    utils\HTTPUtils::setSessionValue("Desglose", $sanitize->sanitizeString("Desglose"));
}
if ($request->hasAttribute("Detallado")) {
    utils\HTTPUtils::setSessionValue("Detallado", $sanitize->sanitizeString("Detallado"));
}
if ($request->hasAttribute("DespachadorS")) {
    utils\HTTPUtils::setSessionValue("SDespachador", $sanitize->sanitizeString("DespachadorS"));
    $SDespachador = explode("|", strpos($sanitize->sanitizeString("DespachadorS"), "Array") ? "" : $sanitize->sanitizeString("DespachadorS"));
    $Var = trim($SDespachador[0]);
    if ($Var > 0) {
        $selectCli = "SELECT id, CONCAT(id, ' | ', nombre) descripcion FROM ven WHERE id = '$Var'";
        error_log($selectCli);
        if (($dbCliQuery = $mysqli->query($selectCli)) && ($dbCliRS = $dbCliQuery->fetch_array())) {
            error_log(print_r($dbCliRS, true));
            $SDespachador = $dbCliRS['descripcion'];
            $Despachador = $dbCliRS['id'];
            utils\HTTPUtils::setSessionValue("Despachador", $Despachador);
        } else {
            error_log($mysqli->error);
        }
    } else {
        utils\HTTPUtils::setSessionValue("Despachador", "");
    }
}
if ($request->hasAttribute("orden")) {
    utils\HTTPUtils::setSessionValue("orden", $sanitize->sanitizeString("orden"));
}
if ($request->hasAttribute("ordenPago")) {
    utils\HTTPUtils::setSessionValue("ordenPago", $sanitize->sanitizeString("ordenPago"));
}

$Fecha = utils\HTTPUtils::getSessionValue("Fecha");
$FechaI = utils\HTTPUtils::getSessionValue("FechaI");
$FechaF = utils\HTTPUtils::getSessionValue("FechaF");
$HoraI = utils\HTTPUtils::getSessionValue("HoraI");
$HoraF = utils\HTTPUtils::getSessionValue("HoraF");
$Turno = utils\HTTPUtils::getSessionValue("Turno");
$Detallado = utils\HTTPUtils::getSessionValue("Detallado");
$Desglose = utils\HTTPUtils::getSessionValue("Desglose");
$Despachador = utils\HTTPUtils::getSessionValue("Despachador");
$SDespachador = utils\HTTPUtils::getSessionValue("SDespachador");
$orden = utils\HTTPUtils::getSessionValue("orden");
$ordenPago = utils\HTTPUtils::getSessionValue("ordenPago");
$Todos = utils\HTTPUtils::getSessionValue("Todos");


/* Consulta para revisar estados de cuenta */
$selectAbonos = "SELECT SUM(importe) importe FROM $Tabla AS cxd WHERE vendedor = '$Despachador' AND tm = 'H' AND fecha < DATE('$FechaI')";
$resultAbonos = utils\IConnection::execSql($selectAbonos);
$Abono = $resultAbonos["importe"];

$selectCargos = "SELECT SUM(importe) importe FROM $Tabla AS cxd WHERE vendedor = '$Despachador' AND tm = 'C' AND fecha < DATE('$FechaI')";
$resultCargos = utils\IConnection::execSql($selectCargos);
$Cargo = $resultCargos["importe"];

$selectCxc = " 
            SELECT cxc.corte referencia, DATE(ct.fecha) fecha, cxc.fecha fechaaplicacion, cxc.tm, cxc.concepto, IF(cxc.recibo = 0, '', cxc.recibo) recibo,
            IF(cxc.tm = 'C',cxc.importe, 0) cargo, IF(cxc.tm = 'H',cxc.importe, 0) abono
            FROM $Tabla AS cxc, ct
            WHERE TRUE AND cxc.corte = ct.id 
            AND cxc.vendedor = '$Despachador' AND DATE(cxc.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')  
            AND cxc.vendedor > 0
            ORDER BY $orden,cxc.tm ;";


/* Consulta para saldos por cliente */
if ($Todos === "Si") {
    $selectSaldos = "SELECT ven.id vendedor, ven.nombre, ven.alias,
            ROUND(IFNULL(SUM(IF(cxd.tm = 'C', cxd.importe ,-cxd.importe)),0),2) importe
            FROM ven
            LEFT JOIN cxd ON cxd.vendedor = ven.id 
            LEFT JOIN ct ON ct.id = cxd.corte
            WHERE TRUE
            AND ven.id >= 50
            AND ven.activo = 'Si' AND DATE(ct.fecha) <= DATE('$Fecha')
            GROUP BY ven.id";
} else {
    $selectSaldos = "   
            SELECT * FROM (
            SELECT ven.id vendedor, ven.nombre, ven.alias,
            ROUND(IFNULL(SUM(IF(cxd.tm = 'C', cxd.importe ,-cxd.importe)),0),2) importe
            FROM ven
            LEFT JOIN cxd ON cxd.vendedor = ven.id 
            LEFT JOIN ct ON ct.id = cxd.corte
            WHERE TRUE
            AND ven.id >= 50
            AND ven.activo = 'Si' AND DATE(ct.fecha) <= DATE('$Fecha')
            GROUP BY ven.id) tt WHERE tt.importe != 0;
            ";
}

