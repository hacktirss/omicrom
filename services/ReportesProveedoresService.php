<?php
/*
 * Generar las consultas que se utilizaran en los reportes
 */
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$mysqli = iconnect();
$sanitize = SanitizeUtil::getInstance();

//error_log(print_r($request, true));

$ciaDAO = new CiaDAO();
$ciaVO = $ciaDAO->retrieve(1);

if ($request->hasAttribute("criteria")) {
    utils\HTTPUtils::setSessionValue("Fecha", date("Y-m-d"));
    utils\HTTPUtils::setSessionValue("FechaI", date('Y-m-d', strtotime('-7 day', strtotime(date('Y-m-d')))));
    utils\HTTPUtils::setSessionValue("FechaF", date("Y-m-d"));
    utils\HTTPUtils::setSessionValue("Detallado", "Si");
    utils\HTTPUtils::setSessionValue("Producto", "*");
    utils\HTTPUtils::setSessionValue("Proveedor", 0);
    utils\HTTPUtils::setSessionValue("ProveedorS", "");
    utils\HTTPUtils::setSessionValue("orden", "referencia");
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
if ($request->hasAttribute("Detallado")) {
    utils\HTTPUtils::setSessionValue("Detallado", $sanitize->sanitizeString("Detallado"));
}
if ($request->hasAttribute("Producto")) {
    utils\HTTPUtils::setSessionValue("Producto", $sanitize->sanitizeString("Producto"));
}
if ($request->hasAttribute("ProveedorS")) {
    utils\HTTPUtils::setSessionValue("ProveedorS", $sanitize->sanitizeString("ProveedorS"));
    $ProveedorS = explode("|", strpos($sanitize->sanitizeString("ProveedorS"), "Array") ? "" : $sanitize->sanitizeString("ProveedorS"));
    $Var = trim($ProveedorS[0]);
    error_log(print_r($Var, true));
    if ($Var > 0) {
        $selectPrv = "SELECT id, CONCAT(id, ' | ', nombre) proveedor FROM prv WHERE id = '$Var'";
        if (($dbCliQuery = $mysqli->query($selectPrv)) && ($dbCliRS = $dbCliQuery->fetch_array())) {
            $ProveedorS = $dbCliRS['proveedor'];
            $Proveedor = $dbCliRS['id'];
            utils\HTTPUtils::setSessionValue("Proveedor", $Proveedor);
        }
    }
}
if ($request->hasAttribute("orden")) {
    utils\HTTPUtils::setSessionValue("orden", $sanitize->sanitizeString("orden"));
    $orden = $sanitize->sanitizeString("orden");
}

$Fecha = utils\HTTPUtils::getSessionValue("Fecha");
$FechaI = utils\HTTPUtils::getSessionValue("FechaI");
$FechaF = utils\HTTPUtils::getSessionValue("FechaF");
$Detallado = utils\HTTPUtils::getSessionValue("Detallado");
$Producto = utils\HTTPUtils::getSessionValue("Producto");
$Proveedor = utils\HTTPUtils::getSessionValue("Proveedor");
$ProveedorS = utils\HTTPUtils::getSessionValue("ProveedorS");
$orden = utils\HTTPUtils::getSessionValue("orden");

/* Consulta para revisar estados de cuenta */
$selectAbonos = "SELECT SUM(importe) importe FROM $Tabla AS cxp WHERE proveedor = '$Proveedor' AND tm = 'H' AND fecha < DATE('$FechaI')";
$resultAbonos = utils\IConnection::execSql($selectAbonos);
$Abono = $resultAbonos["importe"];

$selectCargos = "SELECT SUM(importe) importe FROM $Tabla AS cxp WHERE proveedor = '$Proveedor' AND tm = 'C' AND fecha < DATE('$FechaI')";
$resultCargos = utils\IConnection::execSql($selectCargos);
$Cargo = $resultCargos["importe"];

$selectCxp = "
            SELECT cxp.referencia,cxp.fecha,cxp.fechav,
            IF(cxp.tm = 'C',CONCAT(cxp.concepto, ' Compra Num: ', cxp.referencia), cxp.concepto) concepto,
            IF(cxp.tm = 'C', cxp.importe , 0) cargo,
            IF(cxp.tm = 'H', cxp.importe , 0) abono
            FROM $Tabla AS cxp     
            WHERE cxp.proveedor = '$Proveedor' AND cxp.fecha BETWEEN DATE('$FechaI') AND DATE('$FechaF') ORDER BY cxp.$orden,cxp.tm ";

/*Consultas para reporte de saldos*/

$selectSaldos = "
            SELECT CXP.proveedor,prv.alias,prv.nombre,sum(CXP.importe) importe
            FROM (
                SELECT cxp.proveedor,sum( cxp.importe ) importe
                FROM cxp
                WHERE cxp.tm = 'C' AND cxp.proveedor > 0
                GROUP BY cxp.proveedor 
                UNION 
                SELECT cxp.proveedor,sum( cxp.importe )*-1 importe
                FROM cxp 
                WHERE cxp.tm = 'H' AND cxp.proveedor > 0
                GROUP BY cxp.proveedor
            ) CXP
            LEFT JOIN prv ON CXP.proveedor = prv.id 
            GROUP BY prv.id 
            ORDER BY prv.id";    

/*Consultas para reporte de compras*/

$selectProveedoresG = "
            SELECT * FROM (
                SELECT et.id compra,DATE(et.fecha) fecha,prv.id proveedor,prv.nombre,et.concepto,et.documento,
                et.cantidad,et.importe,et.iva,(et.importe + et.iva) total
                FROM et,prv 
                WHERE et.proveedor = prv.id AND et.status = 'Cerrada' AND
                DATE( et.fecha ) BETWEEN DATE ('$FechaI') AND DATE ('$FechaF') 
                UNION
                SELECT me.id compra,DATE(me.fecha) fecha,prv.id proveedor,prv.nombre,
                CONCAT('COMPRA DE COMBUSTIBLE ' , com.descripcion) concepto,me.foliofac,
                me.incremento cantidad,ROUND((me.importefac - IFNULL(med.cantidad * med.precio,0)), 2) importe,
                ROUND(IFNULL(med.cantidad * med.precio,0), 2)iva,me.importefac total
                FROM com,prv,me
                LEFT JOIN med ON me.id = med.id AND med.clave = 6
                WHERE com.clave = me.producto AND me.proveedor = prv.id AND me.status = 'Cerrada' AND
                DATE( me.fecha ) BETWEEN DATE ('$FechaI') AND DATE ('$FechaF') 
            ) SUB         
            ";
if ($Proveedor !== "" || $Proveedor > 0) {
    $selectProveedoresG .= " WHERE SUB.proveedor = $Proveedor";
}

$selectProveedoresG .= " ORDER BY SUB.fecha";
/*Consultas para reporte de relacion de pagos*/

$selectRPagos = "
            SELECT pagosprv.id pago,pagosprv.proveedor,prv.nombre,UPPER(pagosprv.concepto) concepto,pagosprv.fecha,
            pagosprv.importe
            FROM prv,pagosprv
            WHERE prv.id = pagosprv.id AND DATE(pagosprv.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
            AND pagosprv.status = 'Cerrada'
            ORDER BY pagosprv.proveedor,pagosprv.fecha;";

/* Consulta para reporte de cargos, abonos y saldo */

$selectC_P_S = "
            SELECT * FROM (
                SELECT 
                C.proveedor, 
                prv.tipodepago,
                prv.nombre, 
                prv.alias, 
                SUM(IFNULL(inicial, 0)) inicial,
                SUM(IFNULL(cargo, 0)) cargos,
                SUM(IFNULL(abono, 0)) abonos,
                ROUND(SUM(IFNULL(inicial, 0)) + SUM(IFNULL(cargo, 0)) - SUM(IFNULL(abono, 0)) , 2) importe,
                CASE WHEN prv.tipodepago IN ('Credito') THEN 1 ELSE 2 END orden
                FROM prv
                JOIN (
                    SELECT cxp.proveedor,ROUND( SUM( IF(tm = 'C',importe,-importe) ), 2) inicial,
                    0 abono,0 cargo
                    FROM cxp
                    WHERE cxp.proveedor > 0 AND DATE(cxp.fecha) < DATE('$FechaI')
                    GROUP BY cxp.proveedor

                    UNION ALL

                    SELECT
                    cxp.proveedor,0 inicial,
                    ROUND( SUM( IF(tm = 'C',0,importe) ), 2) abono,
                    ROUND( SUM( IF(tm = 'C',importe,0) ), 2) cargo
                    FROM cxp
                    WHERE cxp.proveedor > 0 AND DATE(cxp.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
                    GROUP BY cxp.proveedor,cxp.tm
                ) C ON C.proveedor = prv.id
                GROUP BY proveedor ) rep
            ORDER BY rep.orden,rep.tipodepago,rep.proveedor
        ";
