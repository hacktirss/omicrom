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

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$mysqli = iconnect();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();

error_log(print_r($request, true));

$ciaDAO = new CiaDAO();
$ciaVO = $ciaDAO->retrieve(1);

$ConcentrarVtasTarjeta = $ciaVO->getVentastarxticket();

if ($request->hasAttribute("criteria")) {
    utils\HTTPUtils::setSessionValue("Fecha", date("Y-m-d"));
    utils\HTTPUtils::setSessionValue("FechaI", date('Y-m-d', strtotime('-1 day', strtotime(date('Y-m-d')))));
    utils\HTTPUtils::setSessionValue("FechaF", date('Y-m-d', strtotime('-1 day', strtotime(date('Y-m-d')))));
    utils\HTTPUtils::setSessionValue("Turno", "*");
    utils\HTTPUtils::setSessionValue("Disponible", "N");
    utils\HTTPUtils::setSessionValue("Detallado", "No");
    utils\HTTPUtils::setSessionValue("Desglose", "Cortes");
    utils\HTTPUtils::setSessionValue("Producto", "*");
    utils\HTTPUtils::setSessionValue("Cliente", 0);
    utils\HTTPUtils::setSessionValue("SCliente", "");
    utils\HTTPUtils::setSessionValue("orden", "factura");
    utils\HTTPUtils::setSessionValue("TipoCliente", "*");
    utils\HTTPUtils::setSessionValue("FormaPago", "*");
    utils\HTTPUtils::setSessionValue("Corte", 0);
    utils\HTTPUtils::setSessionValue("Status", "*");
    utils\HTTPUtils::setSessionValue("Descartar", "No");
    utils\HTTPUtils::setSessionValue("Tipo", "Importe");
    utils\HTTPUtils::setSessionValue("TipoRelacion", "Facturacion");
    utils\HTTPUtils::setSessionValue("criterio", "fc.fecha");
    utils\HTTPUtils::setSessionValue("busca", "");
    utils\HTTPUtils::setSessionValue("Posicion", "*");
    utils\HTTPUtils::setSessionValue("Dispensario", "*");
    utils\HTTPUtils::setSessionValue("IslaPosicion", "*");
    utils\HTTPUtils::setSessionValue("Despachador", "");
    utils\HTTPUtils::setSessionValue("Informacion", "Omicrom");
    utils\HTTPUtils::setSessionValue("Nombre", "Reporte");
    utils\HTTPUtils::setSessionValue("Reporte", 0);
}

$TiposClienteArray = Array(
    "Credito" => "Credito",
    "Contado" => "Contado",
    "Tarjeta" => "Tarjeta",
    "Efectivale" => "Efectivale",
    "Consignacion" => "Consignacion",
    "Prepago" => "Prepago",
    "Puntos" => "Puntos",
    "Vales" => "Vales",
    "*" => "Todos"
);
$StatusCFDI = array(
    3 => "Cancelada S/Timbrar",
    2 => "Cancelada Timbrada",
    1 => "Timbrada",
    0 => "Abierta",
    "*" => "Todos"
);
$TipoCFDI = array(
    "Facturacion" => "Facturacion",
    "Notas" => "Notas",
    "Complementos" => "Complementos y Pagos"
);
$TipoInformacion = array(
    1 => "Sistema omicrom",
    2 => "Archivos de CV",
    3 => "Comparativo"
);

abstract class TipoInformacion extends BasicEnum {

    const OMICROM = 1;
    const ARCHIVOS = 2;
    const COMPARATIVO = 3;

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
if ($request->hasAttribute("Turno")) {
    utils\HTTPUtils::setSessionValue("Turno", $sanitize->sanitizeString("Turno"));
}
if ($request->hasAttribute("Desglose")) {
    utils\HTTPUtils::setSessionValue("Desglose", $sanitize->sanitizeString("Desglose"));
}
if ($request->hasAttribute("Detallado")) {
    utils\HTTPUtils::setSessionValue("Detallado", $sanitize->sanitizeString("Detallado"));
}
if ($request->hasAttribute("Producto")) {
    utils\HTTPUtils::setSessionValue("Producto", $sanitize->sanitizeString("Producto"));
}
if ($request->hasAttribute("ClienteS")) {
    utils\HTTPUtils::setSessionValue("SCliente", $sanitize->sanitizeString("ClienteS"));
    $SCliente = explode("|", strpos($sanitize->sanitizeString("ClienteS"), "Array") ? "" : $sanitize->sanitizeString("ClienteS"));
    $Var = trim($SCliente[0]);
    if ($Var > 0) {
        $selectCli = "SELECT id, CONCAT(id, ' | ', tipodepago, ' | ', nombre) cliente FROM cli WHERE id = '$Var'";
        if (($dbCliQuery = $mysqli->query($selectCli)) && ($dbCliRS = $dbCliQuery->fetch_array())) {
            $SCliente = $dbCliRS['cliente'];
            $Cliente = $dbCliRS['id'];
            utils\HTTPUtils::setSessionValue("Cliente", $Cliente);
        }
    }
}
if ($request->hasAttribute("Cliente")) {
    utils\HTTPUtils::setSessionValue("SCliente", $sanitize->sanitizeString("Cliente"));
    $SCliente = explode("|", strpos($sanitize->sanitizeString("Cliente"), "Array") ? "" : $sanitize->sanitizeString("Cliente"));
    $Var = trim($SCliente[0]);
    if ($Var > 0) {
        $selectCli = "SELECT id, CONCAT(id, ' | ', tipodepago, ' | ', nombre) cliente FROM cli WHERE id = '$Var'";
        if (($dbCliQuery = $mysqli->query($selectCli)) && ($dbCliRS = $dbCliQuery->fetch_array())) {
            $SCliente = $dbCliRS['cliente'];
            $Cliente = $dbCliRS['id'];
            utils\HTTPUtils::setSessionValue("Cliente", $Cliente);
        }
    }
}
if ($request->hasAttribute("orden")) {
    utils\HTTPUtils::setSessionValue("orden", $sanitize->sanitizeString("orden"));
}
if ($request->hasAttribute("TipoCliente")) {
    utils\HTTPUtils::setSessionValue("TipoCliente", $sanitize->sanitizeString("TipoCliente"));
}
if ($request->hasAttribute("FormaPago")) {
    utils\HTTPUtils::setSessionValue("FormaPago", $sanitize->sanitizeString("FormaPago"));
}
if ($request->hasAttribute("Corte")) {
    utils\HTTPUtils::setSessionValue("Corte", $sanitize->sanitizeString("Corte"));
}
if ($request->hasAttribute("Status")) {
    utils\HTTPUtils::setSessionValue("Status", $sanitize->sanitizeString("Status"));
}
if ($request->hasAttribute("Descartar")) {
    utils\HTTPUtils::setSessionValue("Descartar", $sanitize->sanitizeString("Descartar"));
}
if ($request->hasAttribute("Tipo")) {
    utils\HTTPUtils::setSessionValue("Tipo", $sanitize->sanitizeString("Tipo"));
}
if ($request->hasAttribute("TipoRelacion")) {
    utils\HTTPUtils::setSessionValue("TipoRelacion", $sanitize->sanitizeString("TipoRelacion"));
}
if ($request->hasAttribute("criterio")) {
    utils\HTTPUtils::setSessionValue("criterio", $sanitize->sanitizeString("criterio"));
}
if ($request->hasAttribute("busca")) {
    utils\HTTPUtils::setSessionValue("busca", $sanitize->sanitizeString("busca"));
}
if ($request->hasAttribute("Posicion")) {
    utils\HTTPUtils::setSessionValue("Posicion", $sanitize->sanitizeString("Posicion"));
}
if ($request->hasAttribute("Dispensario")) {
    utils\HTTPUtils::setSessionValue("Dispensario", $sanitize->sanitizeString("Dispensario"));
}
if ($request->hasAttribute("IslaPosicion")) {
    utils\HTTPUtils::setSessionValue("IslaPosicion", $sanitize->sanitizeString("IslaPosicion"));
}
if ($request->hasAttribute("Despachador")) {
    utils\HTTPUtils::setSessionValue("Despachador", $sanitize->sanitizeString("Despachador"));
}
if ($request->hasAttribute("Informacion")) {
    utils\HTTPUtils::setSessionValue("Informacion", $sanitize->sanitizeString("Informacion"));
}
if ($request->hasAttribute("Nombre")) {
    utils\HTTPUtils::setSessionValue("Nombre", $sanitize->sanitizeString("Nombre"));
}
if ($request->hasAttribute("Reporte")) {
    utils\HTTPUtils::setSessionValue("Reporte", $sanitize->sanitizeString("Reporte"));
}
if ($request->hasAttribute("Disponible")) {
    utils\HTTPUtils::setSessionValue("Disponible", $sanitize->sanitizeString("Disponible"));
}

if ($request->hasAttribute("mes")) {
    utils\HTTPUtils::setSessionValue("mes", $sanitize->sanitizeString("mes"));
}

if ($request->hasAttribute("anio")) {
    utils\HTTPUtils::setSessionValue("anio", $sanitize->sanitizeString("anio"));
}

if ($request->hasAttribute("mesS")) {
    utils\HTTPUtils::setSessionValue("mesS", $sanitize->sanitizeString("mesS"));
}

if ($request->hasAttribute("anioS")) {
    utils\HTTPUtils::setSessionValue("anioS", $sanitize->sanitizeString("anioS"));
}
if ($request->hasAttribute("serie")) {
    utils\HTTPUtils::setSessionValue("serie", $sanitize->sanitizeString("serie"));
}
$Fecha = utils\HTTPUtils::getSessionValue("Fecha");
$FechaI = utils\HTTPUtils::getSessionValue("FechaI");
$FechaF = utils\HTTPUtils::getSessionValue("FechaF");
$Turno = utils\HTTPUtils::getSessionValue("Turno");
$Detallado = utils\HTTPUtils::getSessionValue("Detallado");
$Desglose = utils\HTTPUtils::getSessionValue("Desglose");
$Producto = utils\HTTPUtils::getSessionValue("Producto");
$Cliente = utils\HTTPUtils::getSessionValue("Cliente");
$SCliente = utils\HTTPUtils::getSessionValue("SCliente");
$orden = utils\HTTPUtils::getSessionValue("orden");
$ordenPago = utils\HTTPUtils::getSessionValue("ordenPago");
$TipoCliente = utils\HTTPUtils::getSessionValue("TipoCliente");
$FormaPago = utils\HTTPUtils::getSessionValue("FormaPago");
$Corte = utils\HTTPUtils::getSessionValue("Corte");
$Status = utils\HTTPUtils::getSessionValue("Status");
$Descartar = utils\HTTPUtils::getSessionValue("Descartar");
$Tipo = utils\HTTPUtils::getSessionValue("Tipo");
$TipoRelacion = utils\HTTPUtils::getSessionValue("TipoRelacion");
$Criterio = utils\HTTPUtils::getSessionValue("criterio");
$busca = utils\HTTPUtils::getSessionValue("busca");
$Posicion = utils\HTTPUtils::getSessionValue("Posicion");
$Dispensario = utils\HTTPUtils::getSessionValue("Dispensario");
$IslaPosicion = utils\HTTPUtils::getSessionValue("IslaPosicion");
$Despachador = utils\HTTPUtils::getSessionValue("Despachador");
$Informacion = utils\HTTPUtils::getSessionValue("Informacion");
$Nombre = utils\HTTPUtils::getSessionValue("Nombre") . "_" . date("His");
$Reporte = utils\HTTPUtils::getSessionValue("Reporte");
$Disponible = utils\HTTPUtils::getSessionValue("Disponible");
$serie = $mes = utils\HTTPUtils::getSessionValue("mes");
$anio = utils\HTTPUtils::getSessionValue("anio");
$mesS = utils\HTTPUtils::getSessionValue("mesS");
$anioS = utils\HTTPUtils::getSessionValue("anioS");
$serie = utils\HTTPUtils::getSessionValue("serie");

$Productos = array();
$selectProductosActivos = $mysqli->query("SELECT id,clave,clavei,descripcion,color FROM com WHERE activo = 'Si' ORDER BY descripcion DESC;");
while ($row = $selectProductosActivos->fetch_array()) {
    $Productos[] = $row;
}

$Combustibles = array();
$selectCombustibles = "
        SELECT COUNT(tanques.id) limite, com.clave, com.clavei, 
        SUBSTRING_INDEX(com.descripcion, ' ', -1) descripcion, GROUP_CONCAT(tanques.tanque) tanque, 
        com.activo, com.claveSubProducto, com.claveProducto
        FROM com, tanques
        WHERE com.clave = tanques.clave_producto AND com.activo = 'Si' AND tanques.estado = 1
        GROUP BY com.descripcion
        ORDER BY com.descripcion DESC ";

$selectCombustiblesActivos = $mysqli->query($selectCombustibles);
while ($row = $selectCombustiblesActivos->fetch_array()) {
    $Combustibles[] = $row;
}

$PosicionesInventario = array();
$selectPosicionesInventario = "SELECT posicion FROM  man WHERE activo = 'Si' AND inventario = 'Si'";
if (($result = $mysqli->query($selectPosicionesInventario))) {
    while ($row = $result->fetch_array()) {
        $PosicionesInventario[] = $row;
    }
}

$DispensariosActivos = array();
$DispensariosActivos["*"] = "Todos";
$selectDispensariosActivos = "SELECT dispensario FROM man WHERE activo = 'Si' GROUP BY dispensario";
if (($result = $mysqli->query($selectDispensariosActivos))) {
    while ($row = $result->fetch_array()) {
        $DispensariosActivos[$row[dispensario]] = $row[dispensario];
    }
}

$PosicionesActivas = array();
$PosicionesActivas["*"] = "Todos";
$selectPosicionesActivas = "SELECT posicion FROM  man WHERE activo = 'Si'";
if ($Dispensario !== "*") {
    $selectPosicionesActivas .= " AND dispensario =  $Dispensario";
}
if (($result = $mysqli->query($selectPosicionesActivas))) {
    while ($row = $result->fetch_array()) {
        $PosicionesActivas[$row[posicion]] = $row[posicion];
    }
}

$IslasPosicion = array();
$IslasPosicion["*"] = "Todos";
$selectIslasPosicion = "SELECT isla_pos FROM  man 
                        WHERE activo = 'Si'
                        GROUP BY isla_pos";
if (($result = $mysqli->query($selectIslasPosicion))) {
    while ($row = $result->fetch_array()) {
        $IslasPosicion[$row[isla_pos]] = $row[isla_pos];
    }
}

$IslasPosicionInventario = array();
$selectIslasPosicionInventario = "SELECT isla_pos FROM  man 
                                  WHERE activo = 'Si' AND inventario = 'Si' 
                                  GROUP BY isla_pos";
if (($result = $mysqli->query($selectIslasPosicionInventario))) {
    while ($row = $result->fetch_array()) {
        $IslasPosicionInventario[$row[isla_pos]] = $row[isla_pos];
    }
}

$Turnos = array();
$selectTurnosActivos = "SELECT turno FROM tur WHERE activo = 'Si'";

if (($result = $mysqli->query($selectTurnosActivos))) {
    while ($row = $result->fetch_array()) {
        $Turnos[$row[turno]] = $row[turno];
    }
}

$PrmIva = 1 + $ciaVO->getIva() / 100;

$cSql = "SELECT cia razonSocial FROM cia";

if ($Reporte == 21) : /* Consulta para Consumos de clientes */

    $cWhere = '';
    $cWhereT = '';
    $cWhereA = '';

    if ($Desglose === "Cortes") :
        if (!empty($Turno) && $Turno !== "*") :
            $cWhere = $cWhere . " AND ct.turno='$Turno'";
        endif;
        $cWhere = $cWhere . " AND DATE(ct.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')"; //ventas
        $cWhereT = $cWhereT . " AND DATE(ct.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')"; //cttarjetas
        $cWhereA = $cWhereA . " AND DATE(ct.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')"; //aditivos
    else :
        $FechaIQ = $FechaI . " " . ($HoraI <> "**" ? $HoraI . ":00:00" : "00:00:00");
        $FechaFQ = $FechaF . " " . ($HoraF <> "**" ? $HoraF . ":59:59" : "23:59:59");

        $cWhere = $cWhere . " AND rm.fin_venta BETWEEN '$FechaIQ' AND '$FechaFQ'"; //ventas
        $cWhereT = $cWhereT . " AND cttarjetas.fecha BETWEEN '$FechaIQ' AND '$FechaFQ'"; //cttarjetas
        $cWhereA = $cWhereA . " AND vt.fecha BETWEEN '$FechaIQ' AND '$FechaFQ'"; //aditivos
    endif;

    if (!empty($Cliente)) :
        $cWhere .= " AND rm.cliente = '$Cliente'";
        $cWhereT = $cWhereT . " AND cttarjetas.banco = '$Cliente'";
        $cWhereA = $cWhereA . " AND vt.cliente = '$Cliente'";
    endif;

    if (!empty($Codigo) && $Codigo !== "*") :
        $cWhere .= " AND rm.codigo LIKE '%$Codigo%'";
    endif;
    if ($Producto !== "*") :
        $cWhere .= " AND rm.producto = '$Producto'";
    endif;

    if ($TipoCliente !== "*") :
        $cWhere .= " AND cli.tipodepago = '$TipoCliente'";
        $cWhereA .= " AND cli.tipodepago = '$TipoCliente'";
    endif;

    if ($Detallado === "No") :
        //$cWhere .= " AND rm.uuid = '" . FcDAO::SIN_TIMBRAR . "'";
        //$cWhereA .= " AND vt.uuid = '" . FcDAO::SIN_TIMBRAR . "'";
    endif;

    if (is_numeric($IslaPosicion)) :
        $cWhere .= " AND man.isla_pos = $IslaPosicion";
        $cWhereA .= " AND man.isla_pos = $IslaPosicion";
    endif;

    $cSql = "
        SELECT   nombre, placas, descripcion,numeco, cliente,IF(impreso <> '', CONCAT('\'', impreso, '\''), impreso) impreso,
        DATE_FORMAT(fecha, '%Y-%m-%d') fecha,DATE_FORMAT(fecha, '%H:%i:%s') hora, ticket, isla_pos posicion,
        producto,volumen,importe, uuid, tipodepago,kilometraje
        FROM (
            SELECT tipo, isla_pos, ticket, codigo, impreso, fecha, placas, kilometraje, descripcion, producto, uuid, 
            tipodepago, cliente, nombre, volumen, importe, pagoreal,numeco
            FROM (
                SELECT 0 tipo, rm.posicion isla_pos, rm.id ticket, rm.corte, 
                CASE 
                    WHEN LENGTH(TRIM(rm.codigo)) = 20 THEN rm.codigo
                    WHEN LENGTH(TRIM(rm.codigo)) = 10 OR LENGTH(TRIM(rm.codigo)) > 20 THEN 'Vales'
                    ELSE ''
                END codigo, 
                IF(unidades.impreso IS NULL,'" . FcDAO::SIN_TIMBRAR . "',unidades.impreso) impreso, 
                rm.fin_venta fecha,                 
                unidades.placas COLLATE utf8_general_ci placas, 
                unidades.numeco,
                rm.kilometraje, IF(unidades.descripcion IS NULL,'" . FcDAO::SIN_TIMBRAR . "',unidades.descripcion) descripcion, 
                com.descripcion producto, rm.uuid,
                cli.tipodepago, rm.cliente, cli.nombre, rm.volumen, rm.pesos importe, rm.pagoreal                                         
                FROM cli,com ,ct, man, rm 
                LEFT JOIN unidades ON rm.codigo = unidades.codigo 
                LEFT JOIN cxc ON rm.id = cxc.referencia
                WHERE 1 =1 AND rm.cliente = cli.id AND rm.producto = com.clavei AND ct.id = rm.corte AND man.posicion = rm.posicion AND man.activo = 'Si'
                AND rm.cliente > 0 AND rm.tipo_venta in ('D','N') $cWhere 
                GROUP BY rm.id 
                ORDER BY rm.cliente,rm.id
            ) AS SUB_A
            UNION ALL
            SELECT tipo, isla_pos, ticket,  codigo, impreso, fecha, placas, kilometraje, descripcion, producto, uuid, 
            tipodepago, cliente, nombre, volumen, importe, pagoreal,numeco
            FROM (               
                SELECT 1 tipo, man.isla_pos, vt.id ticket, vt.corte, vt.codigo, IFNULL(unidades.impreso, '-----') impreso, vt.fecha, 
                '' placas, 0 kilometraje, vt.descripcion, vt.clave producto, vt.uuid, 
                cli.tipodepago, vt.cliente, cli.nombre,unidades.numeco,
                vt.cantidad volumen, vt.total importe, vt.total pagoreal
                FROM ct, man, cli, vtaditivos vt
                LEFT JOIN unidades ON vt.codigo = unidades.codigo 
                WHERE TRUE 
                AND vt.posicion = man.posicion AND cli.id = vt.cliente  
                AND vt.corte = ct.id AND vt.tm = 'C' AND vt.cliente > 0
                $cWhereA
                ORDER BY vt.fecha
            ) AS SUB_B
            UNION ALL
            SELECT tipo, isla_pos, ticket, codigo, impreso, fecha, placas, kilometraje, descripcion, producto, uuid, 
            tipodepago, cliente, nombre, volumen, importe, pagoreal,numeco
            FROM 
            (
                SELECT 3 tipo, 0 isla_pos, cttarjetas.idnvo ticket, cttarjetas.id corte, '' codigo, '' impreso, cttarjetas.fecha, cxc.placas,
                '' kilometraje, 'T' descripcion, '' producto, '" . FcDAO::SIN_TIMBRAR . "' uuid, cli.tipodepago,'-' numeco,
                cli.id cliente, cli.nombre, 0 volumen, cttarjetas.importe,  cttarjetas.importe pagoreal
                FROM cli, ct,cttarjetas  
                LEFT JOIN cxc ON cttarjetas.id = cxc.referencia AND cttarjetas.id = cxc.corte AND cxc.tm = 'C' AND cxc.producto = '-'
                WHERE cttarjetas.banco = cli.id  AND  cttarjetas.id = ct.id 
                AND cli.tipodepago = 'Tarjeta'  $cWhereT    
                ORDER BY cttarjetas.banco,cttarjetas.idnvo
            ) AS SUB_C
        ) AS SUB
        WHERE 1 = 1
        ORDER BY SUB.tipo, SUB.cliente,SUB.fecha ASC
        ";
    error_log($cSql);
elseif ($Reporte == 32) : /* Consulta para Ventas por día */
    $cSql = "";
    if ($Detallado === "Si") :

        if ($Turno === "No") :

            if ($Desglose == "Cortes") :

                $Sql = "
                (
                    SELECT ct.id corte, DATE(ct.fecha)fecha 
                    FROM ct 
                    WHERE DATE(ct.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF') 
                    GROUP BY DATE(ct.fecha)
               ) ct ";

            else :

                $Sql = "
                (
                    SELECT DATE(rm.fin_venta) fecha 
                    FROM rm 
                    WHERE DATE(rm.fin_venta) BETWEEN DATE('$FechaI') AND DATE('$FechaF') 
                    GROUP BY DATE(rm.fin_venta)
               ) ct ";

            endif;

            $campos = $totalPesos = $totalVolumen = $ventas = "";

            foreach ($Productos as $rg) :

                $campos .= empty($campos) ? "IFNULL($rg[clavei].volumen_$rg[descripcion],0) litros_$rg[descripcion]" : ", IFNULL($rg[clavei].volumen_$rg[descripcion],0) litros_$rg[descripcion]";
                $campos .= empty($campos) ? "IFNULL($rg[clavei].pesos_$rg[descripcion],0) importe_$rg[descripcion]" : ", IFNULL($rg[clavei].pesos_$rg[descripcion],0) importe_$rg[descripcion]";

                $ventas .= empty($ventas) ? "(IFNULL($rg[clavei].ventas_$rg[descripcion],0) " : "+ IFNULL($rg[clavei].ventas_$rg[descripcion],0)";
                $totalPesos .= empty($totalPesos) ? "IFNULL($rg[clavei].pesos_$rg[descripcion],0)" : " + IFNULL($rg[clavei].pesos_$rg[descripcion],0)";
                $totalVolumen .= empty($totalVolumen) ? "IFNULL($rg[clavei].volumen_$rg[descripcion],0)" : " + IFNULL($rg[clavei].volumen_$rg[descripcion],0) ";

                if ($Desglose == "Cortes") :

                    $Sql .= "
                        LEFT JOIN (
                            SELECT rm.corte, DATE(ct.fecha) fecha, COUNT(*) ventas_$rg[descripcion], rm.producto,
                            ROUND(SUM(rm.pesosp), 2) pesos_$rg[descripcion], 
                            ROUND(SUM(rm.volumenp), 2) volumen_$rg[descripcion]
                            FROM ct, man, rm
                            WHERE 1 = 1
                            AND ct.id = rm.corte 
                            AND man.posicion = rm.posicion AND man.activo = 'Si'
                            AND DATE(ct.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
                            AND rm.tipo_venta = 'D' 
                            AND rm.producto LIKE '%$rg[clavei]%'
                            GROUP BY DATE(ct.fecha)
                      ) $rg[clavei] ON DATE(ct.fecha) = $rg[clavei].fecha";
                else :

                    $Sql .= "
                        LEFT JOIN (
                            SELECT DATE(rm.fin_venta) fecha, COUNT(*) ventas_$rg[descripcion], rm.producto,
                            ROUND(SUM(rm.importe), 2) pesos_$rg[descripcion], 
                            ROUND(SUM(rm.importe / rm.precio), 2) volumen_$rg[descripcion]
                            FROM man, rm
                            WHERE 1 = 1
                            AND man.posicion = rm.posicion AND man.activo = 'Si'
                            AND DATE(rm.fin_venta) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
                            AND rm.tipo_venta = 'D' 
                            AND rm.producto LIKE '%$rg[clavei]%'
                            GROUP BY DATE(rm.fin_venta)
                      ) $rg[clavei] ON DATE(ct.fecha) = $rg[clavei].fecha";
                endif;

            endforeach;

            $ventas .= ") ventas";
            $pesos .= "";
            $volumen .= "";

            if ($Desglose == "Cortes") :
                $Sql .= " 
                        LEFT JOIN (
                            SELECT vt.corte,DATE(ct.fecha) fecha, SUM(vt.total) importe
                            FROM ct, man, vtaditivos vt
                            WHERE 1 = 1 
                            AND ct.id = vt.corte
                            AND man.posicion = vt.posicion AND man.activo = 'Si'
                            AND DATE(ct.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
                            AND vt.tm = 'C'
                            AND vt.cantidad > 0
                            GROUP BY DATE(ct.fecha)
                      ) vt ON DATE(ct.fecha) = vt.fecha 
                       GROUP BY ct.fecha ";
            else :

                $Sql .= " 
                        LEFT JOIN (
                            SELECT DATE(vt.fecha) fecha, SUM(vt.total) importe
                            FROM  man, vtaditivos vt
                            WHERE 1 = 1
                            AND man.posicion = vt.posicion AND man.activo = 'Si'
                            AND DATE(vt.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
                            AND vt.tm = 'C' AND vt.cantidad > 0
                            GROUP BY DATE(vt.fecha)
                      ) vt ON DATE(ct.fecha) = vt.fecha 
                       GROUP BY ct.fecha ";
            endif;

            $cSql = "
            SELECT * FROM (
                SELECT ct.fecha, $campos, $ventas, 
                ($totalVolumen) total_litros, ($totalPesos) total_importe, IFNULL(SUM(vt.importe),0) aceites,
                ($totalPesos + IFNULL(SUM(vt.importe),0)) total
                FROM $Sql
          ) sub 
            GROUP BY sub.fecha 
            ORDER BY sub.fecha";
        else :

            $cSql = "
            SELECT sub.fecha, sub.corte, sub.producto, sub.ventas, sub.volumen litros, sub.importe
                FROM (
                    SELECT 0 tipo, ct.id corte,DATE(ct.fecha) fecha,com.descripcion producto,
                    COUNT(rm.id) ventas,
                    ROUND(IFNULL(SUM(rm.pesosp),0), 2) importe,
                    ROUND(IFNULL(SUM(rm.volumenp), 0),2)  volumen
                    FROM com, ct, man, rm
                    WHERE 1 =1 
                    AND ct.id = rm.corte AND rm.producto = com.clavei AND rm.tipo_venta = 'D'
                    AND man.posicion = rm.posicion AND man.activo = 'Si'      
                    AND DATE(ct.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')                                  
                    AND com.activo = 'Si'
                    GROUP BY ct.id,com.clavei
                    
                    UNION 

                    SELECT 1 tipo, vt.corte,DATE(ct.fecha) fecha, 'ACEITES y/o ADITIVOS' productos, 
                    0 ventas, ROUND(SUM(vt.total), 2) importe, 0 volumen
                    FROM ct, man, vtaditivos vt
                    WHERE 1 = 1 
                    AND ct.id = vt.corte
                    AND man.posicion = vt.posicion AND man.activo = 'Si'
                    AND DATE(ct.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
                    AND vt.tm = 'C' AND vt.cantidad > 0
                    GROUP BY ct.id             
              ) sub                   
            WHERE TRUE
            ORDER BY sub.corte ASC, sub.tipo ASC
            ";

        endif;

    else :

        if ($Desglose === "Cortes") :

            $cSql = "
            SELECT sub.fecha, sub.ventas, sub.volumen litros, sub.pesos importe, IFNULL(SUM(vt.importe),0) aceites,
            sub.pesos + IFNULL(SUM(vt.importe),0) total
            FROM (               
                SELECT ct.id corte,DATE(ct.fecha)  fecha, COUNT(*) ventas, 
                ROUND(SUM(rm.pesosp), 2) pesos, 
                ROUND(SUM(rm.volumenp), 2) volumen
                FROM ct, man, rm
                WHERE 1 = 1 
                AND man.posicion = rm.posicion AND man.activo = 'Si'
                AND ct.id = rm.corte AND rm.tipo_venta = 'D'
                AND DATE(ct.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
                GROUP BY DATE(ct.fecha)               
           ) sub 
            LEFT JOIN (
                SELECT vt.corte,DATE(ct.fecha) fecha, SUM(vt.total) importe
                FROM ct, man, vtaditivos vt
                WHERE 1 = 1 
                AND man.posicion = vt.posicion AND man.activo = 'Si'
                AND ct.id = vt.corte
                AND DATE(ct.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
                AND vt.tm = 'C'
                AND vt.cantidad > 0
                GROUP BY DATE(ct.fecha)
           ) vt ON DATE(sub.fecha) = vt.fecha
            GROUP BY sub.fecha;";

        else :

            $cSql = "
            SELECT sub.fecha, sub.ventas, sub.volumen litros, sub.pesos importe, IFNULL(SUM(vt.importe),0) aceites,
            sub.pesos + IFNULL(SUM(vt.importe),0) total
            FROM (              
                SELECT DATE(rm.fin_venta) fecha, COUNT(*) ventas, 
                ROUND(SUM(rm.importe) , 2) pesos, 
                ROUND(SUM(rm.importe / rm.precio), 2) volumen
                FROM man, rm 
                WHERE 1 = 1 
                AND man.posicion = rm.posicion AND man.activo = 'Si'
                AND DATE(rm.fin_venta) BETWEEN DATE('$FechaI') AND DATE('$FechaF')  
                AND rm.tipo_venta = 'D' 
                GROUP BY DATE(rm.fin_venta)                
          )  sub
            LEFT JOIN (
                SELECT DATE(vt.fecha) fecha, SUM(vt.total) importe
                FROM man, vtaditivos vt
                WHERE 1 = 1 
                AND man.posicion = vt.posicion AND man.activo = 'Si'
                AND DATE(vt.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
                AND vt.tm = 'C'
                AND vt.cantidad > 0
                GROUP BY DATE(vt.fecha)
           ) vt ON DATE(sub.fecha) = vt.fecha 
            GROUP BY sub.fecha;";

        endif;

    endif;

elseif ($Reporte == 33) : /* Consulta para Ventas por producto */

    if ($Desglose === "Cortes") :
        $cSql = "
            SELECT com.descripcion producto, 
            IFNULL(rm_d.ventas, 0) + IFNULL(rm_n.ventas, 0) ventas,
            IFNULL(rm_d.volumen, 0) litros_normal,
            IFNULL(rm_n.volumen, 0) litros_consignacion,
            IFNULL(rm_d.volumen, 0) + IFNULL(rm_n.volumen, 0) litros_total,
            IFNULL(rm_d.pesos, 0) importe_normal,
            IFNULL(rm_n.pesos, 0) importe_consignacion,
            IFNULL(rm_d.pesos, 0) + IFNULL(rm_n.pesos, 0) importe_total
            FROM com
            LEFT JOIN (
                    SELECT rm.tipo_venta,rm.producto, COUNT(*) ventas,
                    ROUND(SUM(rm.pesosp), 2) pesos,ROUND(SUM(rm.volumenp), 2) volumen
                    FROM ct, man, rm
                    WHERE 1 = 1 
                    AND man.posicion = rm.posicion AND man.activo = 'Si'
                    AND DATE(ct.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
                    AND ct.id = rm.corte
                    AND rm.tipo_venta IN ('D')
                    GROUP BY rm.producto, rm.tipo_venta                 
            ) rm_d ON TRUE AND rm_d.producto = com.clavei
            LEFT JOIN (
                    SELECT rm.tipo_venta,rm.producto, COUNT(*) ventas,
                    ROUND(SUM(rm.pesosp), 2) pesos,ROUND(SUM(rm.volumenp), 2) volumen
                    FROM ct, man, rm
                    WHERE 1 = 1 
                    AND man.posicion = rm.posicion AND man.activo = 'Si'
                    AND DATE(ct.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
                    AND ct.id = rm.corte
                    AND rm.tipo_venta IN ('N')
                    GROUP BY rm.producto, rm.tipo_venta                 
            ) rm_n ON TRUE AND rm_n.producto = com.clavei
            WHERE 1 = 1
            AND com.activo = 'Si'
            ORDER BY com.descripcion DESC
            ";
    else :
        $cSql = "
            SELECT com.descripcion producto, 
            IFNULL(rm_d.ventas, 0) + IFNULL(rm_n.ventas, 0) ventas,
            IFNULL(rm_d.volumen, 0) litros_normal,
            IFNULL(rm_n.volumen, 0) litros_consignacion,
            IFNULL(rm_d.volumen, 0) + IFNULL(rm_n.volumen, 0) litros_total,
            IFNULL(rm_d.pesos, 0) importe_normal,
            IFNULL(rm_n.pesos, 0) importe_consignacion,
            IFNULL(rm_d.pesos, 0) + IFNULL(rm_n.pesos, 0) importe_total
            FROM com
            LEFT JOIN (
                    SELECT rm.tipo_venta,rm.producto, COUNT(*) ventas,
                    ROUND(SUM(rm.importe) - SUM(rm.descuento), 2) pesos,ROUND(SUM(rm.importe/rm.precio), 2) volumen
                    FROM  man, rm
                    WHERE 1 = 1 
                    AND man.posicion = rm.posicion AND man.activo = 'Si'
                    AND DATE(rm.fin_venta) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
                    AND rm.tipo_venta IN ('D')
                    GROUP BY rm.producto, rm.tipo_venta                 
            ) rm_d ON TRUE AND rm_d.producto = com.clavei
            LEFT JOIN (
                    SELECT rm.tipo_venta,rm.producto, COUNT(*) ventas,
                    ROUND(SUM(rm.importe) - SUM(rm.descuento), 2) pesos,ROUND(SUM(rm.importe/rm.precio), 2) volumen
                    FROM man, rm
                    WHERE 1 = 1 
                    AND man.posicion = rm.posicion AND man.activo = 'Si'
                    AND DATE(rm.fin_venta) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
                    AND rm.tipo_venta IN ('N')
                    GROUP BY rm.producto, rm.tipo_venta                 
            ) rm_n ON TRUE AND rm_n.producto = com.clavei
            WHERE 1 = 1
            AND com.activo = 'Si'
            ORDER BY com.descripcion DESC
            ";

    endif;

elseif ($Reporte == 34) : /* Consulta para Pipas capturadas */

    $cSql = "
        SELECT me.id entrada, me.foliofac factura, prv.nombre proveedor_combustible, 
        cre1.llave terminal_almacenamiento, cre2.llave proveedor_transporte,
        me.fechae fechaEntrada, DATE(me.fecha) fechaCaptura, 
        com.descripcion producto, ROUND(SUM(me.volumenfac) * 1000,3) cantidadDocumentada,
        me.incremento as aumento_Bruto,cargas.tcaumento as aumento_Neto,ROUND((SUM(me.volumenfac * 1000) - me.incremento),3) diferencia,
        ROUND(SUM(me.importefac) - SUM(med.cantidad * med.precio), 2) importe , 
        ROUND(SUM(med.cantidad * med.precio),2) iva, ROUND(SUM(me.importefac),2) total , 
        round(sum((select sum(if(me.volumenfac < 0,0,(precio*cantidad))) preciounitario from med a where id = me.id and clave in (1,2,3,4,5,10))),2)  precioCompra 
        FROM com, prv, me 
        LEFT JOIN med ON me.id = med.id
        LEFT JOIN cargas ON me.carga = cargas.id
        LEFT JOIN permisos_cre cre1 ON me.terminal = cre1.id
        LEFT JOIN permisos_cre cre2 ON me.proveedorTransporte = cre2.id
        WHERE TRUE 
        AND com.clave = me.producto
        AND prv.id = me.proveedor
        AND DATE(me.fechae) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
        AND med.clave = 6 AND me.documento IN ('CP','RP') AND me.carga > 0
        GROUP BY me.carga";

elseif ($Reporte == 120) : /* Consulta para Balance de productos */

    $cSql = "
            SELECT SUB.*";

    if ($busca == 1) {
        $cSql .= ",
            IFNULL(SUM(cargas.aumento), 0) compras, IFNULL(GROUP_CONCAT(cargas.id),0) idsCargas ";
    } else {
        $cSql .= ",
            IFNULL(SUM(me.volumenfac * 1000), 0) compras, 0 idsCargas";
    }
    $cSql .= "
            FROM(
                    SELECT 
                    com.*, dias.fecha, l.nombrearchivo, IFNULL(t3.cantidad,0) inicial,
                    ROUND(IFNULL(rmD.venta, 0), 3) venta,
                    ROUND(IFNULL(rmD.bruto, 0), 3) bruto,
                    ROUND(IFNULL(rmD.diferencia, 0),3) diferencia
                    FROM (
                            SELECT @rownum:=@rownum+1 n,DATE_ADD(DATE('$FechaI'),INTERVAL @rownum DAY) fecha
                            FROM man_pro, (SELECT @rownum:=-1,DATEDIFF('$FechaF','$FechaI') diff) r WHERE @rownum < diff
                    ) dias
                    LEFT JOIN (
                            SELECT com.clave, com.clavei, SUBSTRING_INDEX(com.descripcion, ' ', -1) descripcion, 
                            GROUP_CONCAT( tanques.tanque ) tanque,com.claveSubProducto, com.claveProducto,COUNT(tanques.id) limite
                            FROM com, tanques
                            WHERE com.clave = tanques.clave_producto AND com.activo = 'Si' AND tanques.estado = 1
                            GROUP BY com.descripcion
                            ORDER BY com.descripcion DESC
                    ) com ON 1 = 1
                    LEFT JOIN(
                            SELECT t2.clave, t2.producto, t2.fecha, SUM(t2.cantidad) cantidad 
                            FROM (
                                SELECT t1.* 
                                FROM (
                                    SELECT tanques.clave_producto clave,tanques_h.tanque, tanques_h.producto,DATE (tanques_h.fecha_hora_s) fecha, tanques_h.fecha_hora_s,IFNULL(tanques_h.volumen_actual, 0)  cantidad
                                    FROM tanques,tanques_h
                                    WHERE 1 = 1 AND tanques.tanque = tanques_h.tanque AND DATE (tanques_h.fecha_hora_s) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
                                    ORDER BY tanques_h.fecha_hora_s
                                ) t1
                                GROUP BY t1.fecha,t1.tanque
                            ) t2 
                            GROUP BY t2.fecha,t2.producto
                            ORDER BY t2.producto DESC,t2.fecha
                    ) t3 ON dias.fecha = t3.fecha AND t3.clave = com.clave
                    LEFT JOIN (
                            SELECT DATE(rm.fin_venta) fecha, rm.producto,
                            SUM(rm.importe / rm.precio) venta,SUM(rm.volumen) bruto,
                            SUM(rm.volumen) - SUM(rm.importe / rm.precio) diferencia
                            FROM man, rm
                            WHERE 1 = 1
                            AND man.posicion = rm.posicion AND man.activo = 'Si'
                            AND rm.tipo_venta IN ('D','N')
                            AND DATE(rm.fin_venta) BETWEEN DATE('$FechaI') AND DATE('$FechaF') 
                            GROUP BY DATE(rm.fin_venta), rm.producto
                    ) rmD ON DATE(rmD.fecha) = dias.fecha AND rmD.producto = com.clavei
                    LEFT JOIN(
                            SELECT * 
                            FROM(
                                SELECT l.id,l.fecha_informacion info,l.nombrearchivo,l.resp_pemex 
                                FROM logenvios20 l
                                WHERE l.fecha_informacion BETWEEN DATE('$FechaI') AND DATE('$FechaF') 
                                AND (l.resp_pemex LIKE '0|%' OR l.resp_pemex LIKE '417|%')
                                ORDER BY l.fecha_informacion ASC,l.id DESC
                            ) l
                            GROUP BY l.info
                    ) l ON dias.fecha = l.info
                    GROUP BY com.clavei,dias.fecha
                    ORDER BY com.clavei DESC,dias.fecha ASC
            ) SUB 
            LEFT JOIN cargas ON cargas.tipo = 0 AND DATE(cargas.fecha_fin) = SUB.fecha AND cargas.clave_producto = SUB.clave 
            ";

    if ($busca != 1) {
        $cSql .= "
            LEFT JOIN me ON cargas.id = me.carga ";
    }

    $cSql .= "
            WHERE 1 = 1
            GROUP BY SUB.clavei,SUB.fecha
            ORDER BY SUB.clavei DESC,SUB.fecha ASC;";

elseif ($Reporte == 36) : /* Consulta para Vendido y facturado */

elseif ($Reporte == 37) : /* Consulta para Inventario aceites */

elseif ($Reporte == 38) : /* Consulta para Venta de aceites */

elseif ($Reporte == 39) : /* Consulta para Venta de aceites por despachador */

elseif ($Reporte == 40) : /* Consulta para Concentrado movimientos contable */

elseif ($Reporte == 117) : /* Consulta para Relación de facturas */

elseif ($Reporte == 42) : /* Consulta para Relación de facturas	 */

    if ($TipoRelacion === "Facturacion") {
        if ($Status < StatusFactura::CANCELADO_ST || $Status === "*") {
            $cSql = "
        SELECT fc.id,fc.serie, fc.folio, fc.fecha, fc.cliente, cli.nombre,
                IF(fc.status in (3) , 0, fc.cantidad) cantidad,  
                IF(fc.status in (3) , 0,if( fc.ieps=0,fc.importe-(if(fc.total <= 0,0, T.ieps)),fc.importe)) importe,
                IF(fc.status in (3) , 0,fc.iva) iva, 
                IF(fc.status in (3) , 0,if(fc.total <=0,0,T.ieps)) ieps,
                IF(fc.status in (3) , 0,fc.total) total,T.descuento,
            IFNULL(cp.descripcion, 'NA') concepto, fc.status, TRIM(fc.uuid) uuid, cli.tipodepago, fc.origen,
            IFNULL(DATE(f.FechaCancelacion),IF(fc.status = " . StatusFactura::CANCELADO . ",DATE(fc.fecha),'')) FechaCancelacion,fc.usr usuario,
            CASE 
                WHEN T.tickets > 0 AND fc.origen = 1  THEN 'Omicrom'
                    WHEN fc.origen = 3  THEN 'Externa'
                    WHEN fc.origen = 2  THEN 'Terminal'
                    ELSE 'Omicrom/Manual'
            END formato,
            T.productos
            FROM fc LEFT JOIN cli ON fc.cliente = cli.id
             LEFT JOIN 
            ( SELECT id_fc_fk id,uuid, 
                    ExtractValue(cfdi_xml, '/cfdi:Comprobante/@FormaPago') clave,
                    IFNULL(TIMESTAMP(IF(EXTRACTVALUE(facturas.acuse_cancelacion, '/S:Envelope/S:Body/ns2:cancelaCFDIResponse/return/Acuse/@Fecha') <> '',
                                                    EXTRACTVALUE(facturas.acuse_cancelacion, '/S:Envelope/S:Body/ns2:cancelaCFDIResponse/return/Acuse/@Fecha') , 
                                                    EXTRACTVALUE(facturas.acuse_cancelacion,'/s:Envelope/s:Body/CancelaCFDResponse/CancelaCFDResult/@Fecha'))), 
                                                    '') FechaCancelacion
            FROM facturas) f 
            ON fc.uuid = f.uuid
            LEFT JOIN cfdi33_c_fpago cp ON cp.clave = IFNULL(f.clave,cli.formadepago)
            LEFT JOIN (
                SELECT fcd.id factura, 
                SUM(fcd.ticket) tickets,SUM(fcd.descuento) descuento,
                SUM(fcd.ieps*fcd.cantidad) ieps,
                GROUP_CONCAT(DISTINCT inv.descripcion ORDER BY descripcion ASC SEPARATOR ', ') productos
                FROM fcd,inv 
                WHERE 
                fcd.producto = inv.id 
		    AND inv.activo = 'Si'
                GROUP BY fcd.id
          ) T ON fc.id = T.factura
            WHERE 
            DATE(fc.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF') 
            ";
        } else {
            $cSql = "
            SELECT fc.id,fc.folio, fc.fecha, fc.cliente, cli.nombre, fc.cantidad,fc.importe, fc.iva, fc.ieps,fc.total, fc.serie,
            'NA' concepto, fc.status, fc.uuid, cli.tipodepago, fc.origen,IFNULL(fc.usr,'') usuario ,
            CASE 
                    WHEN T.tickets > 0 AND fc.origen = 1  THEN 'Omicrom'
                    WHEN fc.origen = 3  THEN 'Externa'
                    WHEN fc.origen = 2  THEN 'Terminal'
                ELSE 'Omicrom/Manual'
            END formato,
            T.productos
            FROM fc 
            LEFT JOIN cli ON fc.cliente = cli.id
            LEFT JOIN (
                    SELECT fcd.id factura, 
                    SUM(fcd.ticket) tickets,
                    GROUP_CONCAT(DISTINCT inv.descripcion ORDER BY descripcion ASC SEPARATOR ', ') productos
                    FROM fcd,inv 
                    WHERE 
                    fcd.producto = inv.id 
                    AND inv.activo = 'Si'
                    GROUP BY fcd.id
            ) T ON fc.id = T.factura
            WHERE 
            DATE(fc.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF') 
            ";
        }
    } elseif ($TipoRelacion === "Notas") {

        $cSql = "
            SELECT fc.id,fc.serie, fc.folio, fc.fecha, fc.cliente, cli.nombre,fc.cantidad, fc.iva, fc.ieps, fc.importe,fc.total,
            IFNULL(cp.descripcion, 'NA') concepto, fc.status, fc.uuid, cli.tipodepago, fc.origen,fc.FechaCancelacion
            FROM (
                SELECT fc.id,fc.serie, fc.id folio, fc.fecha, fc.cliente, cli.nombre, fc.cantidad, fc.iva, fc.ieps, fc.importe,fc.total,
                fc.status, TRIM(fc.uuid) uuid, cli.tipodepago, 1 origen,IFNULL(f.clave,'') clave,
                IFNULL(DATE(f.FechaCancelacion),IF( fc.status = " . StatusFactura::CANCELADO . ",fc.fecha,'')) FechaCancelacion
                FROM nc fc LEFT JOIN cli ON fc.cliente = cli.id
                LEFT JOIN 
                (   
                        SELECT id_fc_fk id, uuid, 
                        ExtractValue(cfdi_xml, '/cfdi:Comprobante/@FormaPago') clave,
                        IFNULL( TIMESTAMP( IF( EXTRACTVALUE( facturas.acuse_cancelacion, '/S:Envelope/S:Body/ns2:cancelaCFDIResponse/return/Acuse/@Fecha' ) <> '',
                                                        EXTRACTVALUE( facturas.acuse_cancelacion, '/S:Envelope/S:Body/ns2:cancelaCFDIResponse/return/Acuse/@Fecha' ) , 
                                                        EXTRACTVALUE( facturas.acuse_cancelacion,'/s:Envelope/s:Body/CancelaCFDResponse/CancelaCFDResult/@Fecha' ) ) ), 
                                                        '' ) FechaCancelacion
                        FROM facturas
                ) f ON fc.uuid = f.uuid
                
                WHERE 
                DATE(fc.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF') 
            ) fc 
            LEFT JOIN cli ON fc.cliente = cli.id
            LEFT JOIN cfdi33_c_fpago cp ON cp.clave = IF(fc.clave = '',cli.formadepago, fc.clave)
            WHERE 1=1 
            ";
    } else {

        $cSql = "
            SELECT fc.id,fc.serie, fc.folio, fc.fecha, fc.cliente, cli.nombre,fc.cantidad, fc.iva, fc.ieps, fc.importe,fc.importe total,
            IFNULL(cp.descripcion, 'NA') concepto,fc.status, fc.uuid, cli.tipodepago, fc.origen,fc.FechaCancelacion
            FROM (
                SELECT fc.id,fc.serie, fc.id folio, IFNULL(f.fecha_timbrado,fc.fecha) fecha, fc.cliente,1 cantidad, 0 iva, 0 ieps, fc.importe,fc.importe total,
                fc.statusCFDI status, fc.uuid, 1 origen,IFNULL(f.clave,'') clave,
                IFNULL(DATE(f.FechaCancelacion),IF( fc.statusCFDI = " . StatusFactura::CANCELADO . ",fc.fecha,'')) FechaCancelacion
                FROM pagos fc 
                LEFT JOIN 
                (   
                        SELECT id_fc_fk id, uuid, fecha_timbrado,
                        ExtractValue(cfdi_xml, '/cfdi:Comprobante/@FormaPago') clave,
                        IFNULL( TIMESTAMP( IF( EXTRACTVALUE( facturas.acuse_cancelacion, '/S:Envelope/S:Body/ns2:cancelaCFDIResponse/return/Acuse/@Fecha' ) <> '',
                                                        EXTRACTVALUE( facturas.acuse_cancelacion, '/S:Envelope/S:Body/ns2:cancelaCFDIResponse/return/Acuse/@Fecha' ) , 
                                                        EXTRACTVALUE( facturas.acuse_cancelacion,'/s:Envelope/s:Body/CancelaCFDResponse/CancelaCFDResult/@Fecha' ) ) ), 
                                                        '' ) FechaCancelacion
                        FROM facturas
                ) f ON fc.uuid = f.uuid
                WHERE 
                DATE(IFNULL(f.fecha_timbrado,fc.fecha)) BETWEEN DATE('$FechaI') AND DATE('$FechaF') 
            ) fc 
            LEFT JOIN cli ON fc.cliente = cli.id
            LEFT JOIN cfdi33_c_fpago cp ON cp.clave = IF(fc.clave = '',cli.formadepago, fc.clave)
            WHERE 1=1 
            ";
    }
    if ($TipoCliente !== "*") {
        $cSql .= " AND cli.tipodepago = '$TipoCliente'";
    }
    if ($Descartar === "Si") {
        $cSql .= " AND cli.rfc <> 'XAXX010101000' ";
    }
    if (is_numeric($Status)) {
        $cSql .= " AND fc.status = $Status";
    }
    if (!empty($Cliente)) {
        $cSql .= " AND cli.id  = $Cliente";
    }
    //$cSql .= " AND fc.status NOT IN (0,2,3) ";

    $cSql .= " GROUP BY fc.id ORDER BY fc.folio ";

elseif ($Reporte == 43) : /* Consulta para Facturas x rango de fecha (PDF, XML)	 */
    $fechaVenta = str_replace("-", "", $Fecha);
    $fechaVentaf = str_replace("-", "", $request->getAttribute("Fechaf"));
    $cSql = " SELECT sub.*
                FROM (
                        SELECT fc.id factura, fc.folio, fc.serie, fc.origen, fc.fecha fecha_generacion, fc.formadepago forma_pago, 
                        cli.tipodepago tipo_cliente, cli.id cliente, cli.nombre,
                        fcd.ticket, rm.fin_venta fecha_venta, inv.descripcion producto,
                        fc.uuid, rm.precio, ROUND(rm.volumen, 3) volumen,ROUND(((rm.importe-(rm.ieps*rm.volumen))/(1.16)),2) importe,
                        ROUND(((rm.importe-(rm.ieps*rm.volumen))/(1.16))*(rm.iva),2) Iva,ROUND(rm.ieps*rm.volumen,2) ieps,
                        ROUND(rm.pesos, 2) total
                        FROM cli, rm, fcd, inv, fc
                        WHERE TRUE
                        AND cli.id = rm.cliente
                        AND rm.id = fcd.ticket AND fcd.producto < 10
                        AND fcd.producto = inv.id
                        AND fcd.id = fc.id AND fc.status = 1
                        AND rm.fecha_venta BETWEEN $fechaVenta AND $fechaVentaf
                        UNION
                        SELECT fc.id factura, fc.folio, fc.serie, 1 origen, fc.fecha fecha_generacion, fc.formadepago forma_pago, 
                        cli.tipodepago tipo_cliente, cli.id cliente, cli.nombre,
                        fcd.ticket, vt.fecha fecha_venta, vt.descripcion producto, 
                        fc.uuid, vt.unitario precio, vt.cantidad volumen, ROUND(vt.total /1.16,2) importe,ROUND(vt.total-(vt.total /1.16),2) Iva, 0 ieps ,ROUND(vt.total,2) total
                        FROM cli, vtaditivos vt, fcd, fc
                        WHERE TRUE
                        AND cli.id = vt.cliente
                        AND vt.id = fcd.ticket AND fcd.producto >= 10 AND vt.tm = 'C'
                        AND fcd.id = fc.id AND fc.status = 1
                        AND DATE(vt.fecha) between DATE('$Fecha') and DATE('" . $request->getAttribute("Fechaf") . "')
                ) sub                
                WHERE TRUE  
                ";

    if (!empty($FormaPago) && $FormaPago !== "*"):
        $cSql .= " AND sub.forma_pago = '" . $FormaPago . "'";
    endif;
    if (!empty($TipoCliente) && $TipoCliente !== "*"):
        $cSql .= " AND sub.tipo_cliente = '" . $TipoCliente . "'";
    endif;

    $cSql .= " 
                ORDER BY sub.fecha_venta ASC";
elseif ($Reporte == 45) : /* Consulta Exportar cobranza de facturas */
    /* Consulta para cobranza de factuaras */
    $selectFacturasPendientes = "
        SELECT * FROM (
            SELECT 2 sub1,cli.tipodepago,fc.cliente,cli.nombre,fc.folio factura,fc.fecha,
            ROUND(fc.total,2) total,
            ROUND(fc.total - IFNULL(A.abono,0),2) saldo,
            IFNULL(A.abono,0) abono,
            GROUP_CONCAT(A.recibo) recibo
            FROM cli,fc 
                LEFT JOIN
                (
                    SELECT cxc.factura,cxc.cliente,ROUND(SUM(cxc.importe),2) abono,
                    GROUP_CONCAT(IF(cxc.placas = 'Nota_cre',CONCAT('N', cxc.referencia),cxc.recibo) SEPARATOR ' | ') recibo
                    FROM cxc , fc
                    WHERE cxc.factura = fc.id AND cxc.tm = 'H' 
                    AND DATE(fc.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF') AND cxc.cliente > 0
                    GROUP BY cxc.factura
                    UNION ALL
                    SELECT cxc.factura,cxc.cliente,ROUND(SUM(cxc.importe),2) abono,
                    GROUP_CONCAT(IF(cxc.placas = 'Nota_cre',CONCAT('N', cxc.referencia),cxc.recibo) SEPARATOR ' | ') recibo
                    FROM cxch cxc , fc
                    WHERE cxc.factura = fc.id AND cxc.tm = 'H' 
                    AND DATE(fc.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF') AND cxc.cliente > 0
                    GROUP BY cxc.factura
                ) A ON fc.id = A.factura
            WHERE  
                fc.cliente = cli.id
                AND fc.status = " . StatusFactura::CERRADO . "
                AND cli.tipodepago NOT REGEXP 'Contado|Puntos'
                AND DATE(fc.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
            GROUP BY fc.id
        ) cxc
        WHERE TRUE  ";
    $TipoCliente = $request->getAttribute("TipoCliente");
    $Cliente = $request->getAttribute("Cliente");
    $Detallado = $request->getAttribute("Detallado");
    if ($TipoCliente !== "*") {
        $selectFacturasPendientes .= " AND cxc.tipodepago = '$TipoCliente'";
    }
    if (!empty($Cliente) && $Cliente > 0) {
        $selectFacturasPendientes .= " AND cxc.cliente = '$Cliente'";
    }
    if ($Detallado === "No") {
        $selectFacturasPendientes .= " AND cxc.saldo > 0";
    }
    $selectFacturasPendientes .= " ORDER BY cxc.tipodepago,cxc.cliente,cxc.factura";
    $cSql = $selectFacturasPendientes;
elseif ($Reporte == 44) : /* Consulta para Exporta archivos CV */

elseif ($Reporte == 56) : /* Consulta para Relación de vales */

elseif ($Reporte == 58) : /* Consulta para Concentrado 24hrs */

elseif ($Reporte == 59) : /* Consulta para Jarreos */

elseif ($Reporte == 122) : /* Consulta para Reporte de ieps */

elseif ($Reporte == 121) : /* Consulta para Ieps */

elseif ($Reporte == 92) : /* Consulta para Vendido y facturado aceites */

elseif ($Reporte == 94) : /* Consulta para Corte mensual */

elseif ($Reporte == 95) : /* Consulta para Depósitos Bancarios */

elseif ($Reporte == 200) : /* Consulta reporte siguiente mes */
    $selectFacturas = "
    SELECT  fc.folio,fc.serie,fc.fecha
    ,fc.uuid,rm.id ticket, inv.descripcion producto,rm.inicio_venta fecha_venta,ROUND(fcd.cantidad,2) volumen,
    ROUND(fcd.cantidad*fcd.precio,2) importe,ROUND((fcd.cantidad*fcd.precio)*fcd.iva,2) importeIva,
    ROUND(fcd.cantidad * fcd.ieps,2) ieps,
    ROUND(rm.importe,2) importeTotal
    FROM fc LEFT JOIN fcd on fc.id =fcd.id left join rm ON fcd.ticket=rm.id
    LEFT join inv ON fcd.producto = inv.id
    WHERE YEAR(fc.fecha) = '$anioS' AND MONTH(fc.fecha) = '$mesS' and fc.uuid != '-----' 
    AND fc.status=1 AND  fcd.ticket in (SELECT id FROM rm WHERE YEAR(DATE(fecha_venta)) = '$anio' 
    AND  MONTH(DATE(fecha_venta)) = '$mes' AND fc.uuid <> '-----' ) AND fcd.producto <= 5
    order by inv.descripcion";
    $cSql = $selectFacturas;
elseif ($Reporte == 201) : /* Consulta reporte factura */
    $selectFacturas = "
        SELECT fc.serie,inv.descripcion,
		round(sum(if(producto>5,fcd.cantidad,0)),2) piezas,
        round(sum(if(producto<5,fcd.cantidad,0)),2) volumen,
        round(sum((fcd.cantidad * fcd.precio)),2) importe,
        round(sum((fcd.cantidad * fcd.precio) * fcd.iva),2) iva,
        round(sum((fcd.cantidad * fcd.ieps)),2) ieps,
        round(sum(fcd.importe),2) total
        FROM fc left join fcd on fc.id = fcd.id
        left join inv on fcd.producto = inv.id
        WHERE YEAR(fc.fecha) = '$anio' AND MONTH(fc.fecha) = '$mes' and fc.uuid != '-----'  and fc.serie = '$serie' and status = 1 group by producto;
        ";
    $cSql = $selectFacturas;
elseif ($Reporte == 202) : /* Consulta reporte factura */
    $cSql = "select noticket,productoT,fecha_venta,serie,folio,fechaF fecha_factura,uuid,volumen,importe,iva,ieps,total,ROUND(I_V,2) Importe_Venta,ROUND(I_F,2) Importe_Factura,ROUND(dif,1) diferencia from (
        select d.noticket,com.descripcion productoT,d.inicio_venta fecha_venta,d.serie,d.folio,d.fechaF,d.uuid,
                ROUND(sum(d.cantidad),2) volumen,
                ROUND(sum((d.cantidad * round(d.precio,3))),2) importe,
                ROUND(sum((d.cantidad * round( d.precio,3)) * d.iva),2) iva,
                ROUND(sum((d.cantidad * d.ieps)),2) ieps,
                ROUND(sum(d.importe),2) total    ,
                SUM(imprm),
                ROUND(sum(d.importe),2)-SUM(imprm) dif,
                SUM(imprm) I_V,
                sum(d.importe) I_F
                    From (SELECT fcd.*,fc.serie,rm.producto as descpro,fc.folio,rm.importe imprm,fc.fecha fechaF,rm.inicio_venta
                    ,fc.uuid,rm.id noticket, rm.producto productoT
                            FROM rm left join fcd on fcd.ticket=rm.id left join fc on fcd.id=fc.id LEFT JOIN com ON rm.producto = com.clavei
                            WHERE month(date(rm.fecha_venta)) = " . $request->getAttribute("mes") . " AND YEAR(date(rm.fecha_venta)) = " . $request->getAttribute("Anio") . "
                            AND rm.uuid <> '-----' AND rm.tipo_venta='D' AND fc.status = 1 AND fcd.producto < 5 
          )as d inner join com on d.productoT = com.clavei
          group by d.serie,d.descpro,d.folio order by d.serie,d.folio,descpro) a";
elseif ($Reporte == 203) :
    $cSql = "select
                    f.serie
                    , month(f.fecha_venta) mes_venta
                    , descripcion
                    , round(sum(f.cantidad),2) volumen
                    , ROUND( SUM(ROUND(cantidad * precio,3)), 2 ) subtotal 
                    , ROUND( SUM(ROUND(cantidad * precio * iva,3)), 2 ) tax_iva 
                    , ROUND( SUM(round(cantidad * ieps,4)), 2 ) tax_ieps 
                    , round(sum(f.importe),2) importe
                    , 'COMBUSTIBLE'  tps
                    FROM  (
                    SELECT 
                    fc.id
                    , fc.serie
                    , fc.folio
                    , fcd.cantidad
                    , fcd.precio
                    , fcd.iva
                    , fcd.ieps
                    , fcd.importe
                    , rm.fecha_venta
                    , inv.descripcion
                    FROM fc inner join fcd
                    ON fc.id = fcd.id 
                    INNER JOIN rm 
                    on fcd.ticket = rm.id
                    inner join inv
                    on fcd.producto = inv.id
                    WHERE YEAR(fc.fecha) = " . $request->getAttribute("Anio") . "
                    AND MONTH(fc.fecha) = " . $request->getAttribute("mes") . " 
                    and fc.uuid != '-----' 
                    and status = 1

                    ) f
                    group by f.serie , month(f.fecha_venta), descripcion
                    union all
                    select
                    f.serie
                    , month(f.fecha_venta) mes_venta
                    , descripcion
                    , round(sum(f.cantidad),2) volumen
                    , ROUND( SUM(ROUND(cantidad * preciob/(1+iva),3)), 2 ) subtotal 
                    , ROUND( SUM(ROUND(cantidad * preciob/(1+iva),3)*iva), 2 )  tax_iva 
                    , 0 tax_ieps 
                    , round(sum(f.importe),2) importe
                    , 'ADITIVOS'
                    FROM  (
                    SELECT 
                    fc.id
                    , fc.serie
                    , fc.folio
                    , fcd.cantidad
                    , fcd.preciob
                    , fcd.iva
                    , fcd.ieps
                    , fcd.importe
                    , date(vtaditivos.fecha) fecha_venta
                    , descripcion
                    FROM fc inner join fcd
                    ON fc.id = fcd.id 
                    INNER JOIN vtaditivos 
                    on fcd.ticket = vtaditivos.id
                    WHERE YEAR(fc.fecha) = " . $request->getAttribute("Anio") . " 
                    AND MONTH(fc.fecha) = " . $request->getAttribute("mes") . "
                    and fc.uuid != '-----' 
                    and status = 1
                    and producto > 5
                    ) f
                    group by f.serie , month(f.fecha_venta), descripcion
                    union all
                    select
                    f.serie
                    , month(f.fecha_venta) mes_venta
                    , descripcion
                    , round(sum(f.cantidad),2) volumen
                    , ROUND( SUM(ROUND(cantidad * preciob/(1+iva),3)), 2 ) subtotal 
                    , ROUND( SUM(ROUND(cantidad * preciob/(1+iva),3)*iva), 2 )  tax_iva 
                    , 0 tax_ieps 
                    , round(sum(f.importe),2) importe
                    , 'ADITIVOS_MANUEALES'
                    FROM  (
                    SELECT 
                    fc.id
                    , fc.serie
                    , fc.folio
                    , fcd.cantidad
                    , fcd.preciob
                    , fcd.iva
                    , fcd.ieps
                    , fcd.importe
                    , date(fc.fecha) fecha_venta
                    , inv.descripcion
                    FROM fc inner join fcd
                    ON fc.id = fcd.id inner join inv
                    on fcd.producto = inv.id
                    WHERE YEAR(fc.fecha) = " . $request->getAttribute("Anio") . " 
                    AND MONTH(fc.fecha) = " . $request->getAttribute("mes") . "
                    and fc.uuid != '-----' 
                    and status = 1
                    and producto > 5
                    and fcd.ticket = 0
                    ) f
                    group by f.serie , month(f.fecha_venta),descripcion
                    ";
elseif ($Reporte == 204) :
    $Sql = "CALL omicrom.balance_productos('" . $request->getAttribute("FechaI") . "', '" . $request->getAttribute("FechaF") . "');";
    $mysqli->query($Sql);
    $cSql = "SELECT b.descripcion,b.fecha, ROUND(b.inicial / 1000,3) Inicial,ROUND(b.venta / 1000,3) Ventas,
                ROUND(b.jarreos / 1000,3) Jarreos,ROUND(b.compras / 1000,3) Compras ,ROUND((inicial-b.venta+b.compras)/1000,3) invTeorico
                ,ROUND(IFNULL(( 
                        SELECT IFNULL(volumen_actual, 0) cantidad FROM tanques_h
                        WHERE TRUE AND tanque IN (b.tanques) AND 
                    DATE( fecha_hora_s ) = DATE_ADD(b.fecha,INTERVAL 1 DAY) ORDER BY fecha_hora_s  ASC LIMIT 1),0)/1000,3)  InvFinal,
                    ROUND((IFNULL(( 
                        SELECT IFNULL(volumen_actual, 0) cantidad FROM tanques_h
                        WHERE TRUE AND tanque IN (b.tanques) AND 
                    DATE( fecha_hora_s ) = DATE_ADD(b.fecha,INTERVAL 1 DAY) ORDER BY fecha_hora_s  ASC LIMIT 1),0) - ((inicial-b.venta+b.compras))) /1000,3) Diferencia
                FROM balance_productos b inner join com on b.clave = com.clave;";

elseif ($Reporte == 205) :
    $cSql = "SELECT  concat(fc.serie,' ',fc.folio) folio,cli.nombre,date(fc.fecha) fecha,
    case
        WHEN fc.status = 0 THEN 'Fact Abierta'
        WHEN fc.status = 1 THEN 'Fact Timbrada'
        WHEN fc.status = 2 THEN 'Fact Cancelada'
        WHEN fc.status = 3 THEN 'Fact Cancelada'
        ELSE 'Sin Facturar'
    END statusfc
        ,inv.descripcion,
        #round(ifnull(fcd.precio,(rm.precio-rm.ieps)/(1+rm.iva)),2) precio,
        sum(round(fcd.cantidad,2)) cantidad,
        ROUND( sum( fcd.cantidad * ( preciob - fcd.ieps  ) / (1 + fcd.iva) ), 2) importe,
        ROUND( sum( fcd.cantidad * ( preciob - fcd.ieps  ) / (1 + fcd.iva) ) * fcd.iva, 2) iva,
        round(sum((fcd.cantidad * fcd.ieps)),2) ieps,
        round(sum(fcd.importe),2) total,
        cli.rfc,
        fc.uuid
        from fc
            left join fcd on fcd.id = fc.id
            left join cli on cli.id = fc.cliente
            left join inv on inv.id = fcd.producto
        where  date(fc.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
            and fc.status = 1
            group by fc.serie,fc.folio,fcd.producto
UNION ALL
SELECT  '' folio
, ''
, 'TOTALES' fecha,
     case
        WHEN fc.status = 0 THEN 'Fact Abierta'
        WHEN fc.status = 1 THEN 'Fact Timbrada'
        WHEN fc.status = 2 THEN 'Fact Pend Cancelacion'
        WHEN fc.status = 3 THEN 'Fact Cancelada'
        ELSE 'Sin Facturar'
    END statusfc
        ,inv.descripcion ,
        IFNULL(sum(round(fcd.cantidad,2)),0) cantidad,
        IFNULL(ROUND( sum( fcd.cantidad * ( preciob - fcd.ieps  ) / (1 + fcd.iva) ), 2),0) importe,
        IFNULL(ROUND( sum( fcd.cantidad * ( preciob - fcd.ieps  ) / (1 + fcd.iva) ) * fcd.iva, 2),0) iva,
        IFNULL(round(sum((fcd.cantidad * fcd.ieps)),2),0) ieps,
        IFNULL(round(sum(fcd.importe),2),0) total,
        '',
        ''
        from fc
            left join fcd on fcd.id = fc.id
            left join cli on cli.id = fc.cliente
            left join inv on inv.id = fcd.producto
        where  date(fc.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
            and fc.status = 1
            -- group by case when fcd.producto <=5 THEN fcd.producto ELSE 0 END
            group by fc.status, fcd.producto 


";
    /* $cSql = "SELECT  concat(fc.serie,' ',fc.folio) folio,cli.nombre,fc.fecha,
      case
      WHEN fc.status = 0 THEN 'Fact Abierta'
      WHEN fc.status = 1 THEN 'Fact Timbrada'
      WHEN fc.status = 2 THEN 'Fact Cancelada'
      WHEN fc.status = 3 THEN 'Fact Cancelada'
      ELSE 'Sin Facturar'
      END statusfc
      ,com.descripcion,
      round(ifnull(fcd.precio,(rm.precio-rm.ieps)/(1+rm.iva)),2) precio,
      sum(round(ifnull(fcd.cantidad,(rm.importe)/(rm.precio)),2)) cantidad,
      sum(round(ifnull((fcd.cantidad * fcd.precio),(rm.importe)/(rm.precio)*(rm.precio-rm.ieps)/(1+rm.iva)),2)) importe,
      sum(round(ifnull((fcd.cantidad * fcd.precio) * fcd.iva,(rm.importe)/(rm.precio)*(rm.precio-rm.ieps)/(1+rm.iva) * rm.iva),2)) iva,
      sum(round(ifnull((fcd.cantidad * fcd.ieps),(rm.importe)/(rm.precio)*rm.ieps),2)) ieps,
      sum(round(ifnull(fcd.importe,rm.importe),2)) total
      cli.rfc,
      fc.uuid
      from rm left join fcd on rm.id = fcd.ticket and fcd.producto < 5
      left join fc on fcd.id = fc.id
      left join cli on cli.id = fc.cliente
      left join com on rm.producto = com.clavei
      where  rm.fecha_venta BETWEEN " . str_replace("-", "", $FechaI) . " AND " . str_replace("-", "", $FechaF) . "
      and rm.tipo_venta = 'D'
      and rm.importe > 0
      group by fc.serie,fc.folio,fcd.producto

      union all

      select concat(fc.serie,' ',fc.folio) folio,cli.nombre,fc.fecha,
      case
      WHEN fc.status = 0 THEN 'Fact Abierta'
      WHEN fc.status = 1 THEN 'Fact Timbrada'
      WHEN fc.status = 2 THEN 'Fact Cancelada'
      WHEN fc.status = 3 THEN 'Fact Cancelada'
      ELSE 'Sin Facturar'
      END statusfc,
      vta.descripcion,
      vta.unitario precio,
      vta.cantidad,
      round((vta.total/(1+vta.iva)),2) as importe,
      round((vta.total/(1+vta.iva)*vta.iva),2) as iva,
      0 ieps,
      vta.total,
      cli.rfc,
      fc.uuid
      from vtaditivos vta
      left join fcd on vta.id = fcd.ticket and fcd.producto > 5
      left join fc on fcd.id =fc.id
      left join cli on cli.id = fc.cliente
      WHERE DATE(vta.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
      AND vta.tm = 'C'
      and vta.cantidad > 0
      group by fc.serie,fc.folio,vta.descripcion;"; */

    error_log("El query para exportar es: " . $sql);
else:
endif;