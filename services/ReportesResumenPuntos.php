<?php

set_time_limit(300);
/*
 * Generar las consultas que se utilizaran en los reportes de ventas
 */
include_once ("libnvo/lib.php");
include_once ("data/FcDAO.php");
include_once ("data/PagoDAO.php");
include_once ("data/NcDAO.php");
include_once ("data/ClientesDAO.php");
include_once ('data/V_CorporativoDAO.php');

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

if ($request->hasAttribute("criteria")) {
    utils\HTTPUtils::setSessionValue("Tipodepago", "");
}
if ($request->hasAttribute("Tipodepago")) {
    utils\HTTPUtils::setSessionValue("Tipodepago", $sanitize->sanitizeString("Tipodepago"));
}
$Tipodepago = utils\HTTPUtils::getSessionValue("Tipodepago");
if ($Tipodepago <> "") {
    $TipoPago = " cli.tipodepago = '" . $Tipodepago . "' AND ";
}

$Productos = array();
$FechaInicioP = utils\IConnection::execSql("SELECT valor FROM variables_corporativo WHERE llave = 'Inicio_Puntos';");
$FechaPuntos = "";
if ($FechaInicioP["valor"] <> "") {
    $FechaPuntos = " DATE(rm.fecha_venta) >= DATE ('" . $FechaInicioP["valor"] . "') AND ";
    $Fechapunto = "  DATE(fecha) >= DATE('" . $FechaInicioP["valor"] . "') ";
}
$PuntosPor = utils\IConnection::execSql("SELECT valor FROM variables_corporativo WHERE llave = 'PuntoPor';");
$SelectPuntos = "SELECT cli.id,cli.nombre,cli.tipodepago,ROUND(sum(rm." . $PuntosPor["valor"] . "/cnt_por_punto) * cia.pesosporpunto,0 ) puntos,Pts.smpts "
        . "FROM rm LEFT JOIN cli ON cli.id = rm.cliente LEFT JOIN "
        . "(SELECT IFNULL(SUM(puntos),0) smpts,cliente,fecha FROM puntos WHERE $Fechapunto GROUP  BY cliente) Pts "
        . "ON cli.id = Pts.cliente LEFT JOIN cia ON TRUE LEFT JOIN com ON rm.producto = com.clavei "
        . "WHERE $FechaPuntos  $TipoPago cli.id > 10 GROUP BY cli.id ORDER BY cli.tipodepago,cli.id;";
