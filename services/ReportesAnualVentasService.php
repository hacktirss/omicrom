<?php

set_time_limit(300);
/*
 * Generar las consultas que se utilizaran en los reportes de ventas
 */
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$mysqli = iconnect();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();

//error_log(print_r($request, true));

$ciaDAO = new CiaDAO();
$variablesCorpDAO = new V_CorporativoDAO();

$ciaVO = $ciaDAO->retrieve(1);
$variablesCorpDepositos = $variablesCorpDAO->retrieve(ListaLlaves::DESGLOSE_DEPOSITOS);

$ConcentrarVtasTarjeta = $ciaVO->getVentastarxticket();

$Combustibles = array();
$CombustiblesLetra = array();
$selectTurnosActivos = "SELECT descripcion,color,clavei FROM com WHERE activo = 'Si'";

if (($result = $mysqli->query($selectTurnosActivos))) {
    while ($row = $result->fetch_array()) {
        $Combustibles[$row["descripcion"]] = $row["color"];
        $CombustiblesLetra[] = $row["clavei"];
    }
}

if ($request->getAttribute("Fecha")) {
    $añoInicial = $request->getAttribute("Fecha") . "-01-01 00:00:01";
    $añoFinal = $request->getAttribute("Fechafin") . "-12-31 23:59:59";
} else {
    $año = date("Y");
    $añoInicial = $año . "-01-01 00:00:01";
    $añoFinal = $año . "-12-31 23:59:59";
}
$Datos = array();
$selectVentas = "SELECT MONTHNAME(fin_venta) mes, YEAR(fin_venta) year, ";
$i = 1;

foreach ($CombustiblesLetra as $key => $value):
    $selectVentas .= "(SELECT  IFNULL(sum(rm.volumen),0) FROM rm WHERE producto = '$value' AND mes=monthname(fin_venta) AND YEAR(fin_venta)=year) AS rs$i, "
            . "(SELECT  IFNULL(AVG(rm.precio),0) FROM rm WHERE producto = '$value' AND mes=monthname(fin_venta)) AS ps$i, "
            . "(SELECT  sum(importefac) / sum(volumenfac * 1000) as resul FROM me LEFT JOIN tanques ON me.tanque = tanques.tanque LEFT JOIN com ON tanques.producto = com.descripcion WHERE com.clavei='$value' AND me.tipo <> 'Jarreo' AND mes=monthname(fecha)) AS costo$i,";
    $i++;
endforeach;
$selectVentas .= "(SELECT sum(rm.volumen) FROM rm WHERE mes=monthname(fin_venta)) AS volumentotal,"
        . "SUM(me.incremento) incremento FROM rm "
        . "LEFT JOIN com ON rm.producto = com.clavei "
        . "LEFT JOIN tanques ON com.descripcion = tanques.producto "
        . "LEFT JOIN me ON tanques.tanque = me.tanque "
        . "WHERE  fin_venta > '$añoInicial' AND fin_venta < '$añoFinal' AND rm.producto IN "
        . "(SELECT clavei FROM com ORDER BY clavei) GROUP BY mes,year ORDER BY fin_venta ASC";
error_log($selectVentas);
if (($result = $mysqli->query($selectVentas))) {
    while ($row = $result->fetch_array()) {
        $Datos[] = $row;
    }
}
