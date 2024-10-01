<?php

include_once ("../../softcoatl/SoftcoatlHTTP.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$connection = utils\IConnection::getConnection();
$jsonString = array();
$Sql = "SELECT * FROM periodo_puntos WHERE id = " . $_REQUEST["Var"];

if ($FechaInicioP = $connection->query($Sql)->fetch_array()) {
    $TipoC = $FechaInicioP["tipo_concepto"] === "V" ? "volumen" : "importe";
    if ($FechaInicioP["valor"] <> "") {
        $FechaPuntos = "";
        $Fechapunto = "  ";
    }
    $CalculaPuntos = "SELECT ROUND(sum(rm." . $TipoC . "/(SELECT monto_promocion FROM periodo_puntos WHERE id= " . $_REQUEST["Var"] . ")),0 )  puntos,Pts.smpts puntosConsumidos "
            . "FROM rm LEFT JOIN cli ON cli.id = rm.cliente LEFT JOIN "
            . "(SELECT SUM(puntos) smpts,cliente,fecha FROM puntos WHERE "
            . "DATE(fecha) BETWEEN DATE ('" . $FechaInicioP["fecha_inicial"] . "')  AND DATE ('" . $FechaInicioP["fecha_final"] . "') "
            . "AND cliente = " . $_REQUEST["Cliente"] . " AND id_periodo = " . $_REQUEST["Var"] . "  GROUP  BY cliente) Pts "
            . "ON cli.id = Pts.cliente "
            . "LEFT JOIN cia ON TRUE LEFT JOIN com ON rm.producto = com.clavei "
            . "WHERE DATE(rm.fecha_venta) BETWEEN DATE(DATE_FORMAT('" . $FechaInicioP["fecha_inicial"] . "','%Y%m%d')) "
            . "AND DATE(DATE_FORMAT('" . $FechaInicioP["fecha_culmina"] . "','%Y%m%d')) AND rm.$TipoC > " . $FechaInicioP["limite_inferior"] . "  "
            . "AND cli.id = " . $_REQUEST["Cliente"] . " GROUP BY cli.id ORDER BY cli.tipodepago;";
    
    if ($Calculo = $connection->query($CalculaPuntos)->fetch_array()) {
        $jsonString["Puntos"] = $Calculo["puntos"] == null ? 0 : $Calculo["puntos"];
        $jsonString["puntosConsumidos"] = $Calculo["puntosConsumidos"];
        error_log("JSON " . print_r($jsonString, true));
    } else {
        $jsonString["Puntos"] = 0;
        $jsonString["puntosConsumidos"] = 0;
        error_log($connection->error);
    }
}
$Sql = "SELECT precio FROM inv WHERE id = " . $_REQUEST["Producto"];
error_log($Sql);
if ($InvPuntos = $connection->query($Sql)->fetch_array()) {
    $jsonString["InvPuntos"] = $InvPuntos["precio"];
}
echo json_encode($jsonString);
