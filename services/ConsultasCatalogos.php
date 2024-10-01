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

//error_log(print_r($request, true));

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
    utils\HTTPUtils::setSessionValue("Consig", "No");
    utils\HTTPUtils::setSessionValue("Producto", "*");
    utils\HTTPUtils::setSessionValue("Cliente", 0);
    utils\HTTPUtils::setSessionValue("SCliente", "");
    utils\HTTPUtils::setSessionValue("orden", "factura");
    utils\HTTPUtils::setSessionValue("TipoCliente", "*");
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
if ($request->hasAttribute("Consig")) {
    utils\HTTPUtils::setSessionValue("ConsConsigig", $sanitize->sanitizeString("Consig"));
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

$Fecha = utils\HTTPUtils::getSessionValue("Fecha");
$FechaI = utils\HTTPUtils::getSessionValue("FechaI");
$FechaF = utils\HTTPUtils::getSessionValue("FechaF");
$Turno = utils\HTTPUtils::getSessionValue("Turno");
$Detallado = utils\HTTPUtils::getSessionValue("Detallado");
$Desglose = utils\HTTPUtils::getSessionValue("Desglose");
$Consig = utils\HTTPUtils::getSessionValue("Consig");
$Producto = utils\HTTPUtils::getSessionValue("Producto");
$Cliente = utils\HTTPUtils::getSessionValue("Cliente");
$SCliente = utils\HTTPUtils::getSessionValue("SCliente");
$orden = utils\HTTPUtils::getSessionValue("orden");
$ordenPago = utils\HTTPUtils::getSessionValue("ordenPago");
$TipoCliente = utils\HTTPUtils::getSessionValue("TipoCliente");
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

$Productos = array();
$selectProductosActivos = $mysqli->query("SELECT id,clave,clavei,descripcion,color FROM com WHERE activo = 'Si' ORDER BY descripcion DESC;");
while ($row = $selectProductosActivos->fetch_array()) {
    $Productos[] = $row;
}

$Combustibles = array();
$selectCombustibles = "
        SELECT COUNT( tanques.id ) limite, com.clave, com.clavei, 
        SUBSTRING_INDEX(com.descripcion, ' ', -1) descripcion, GROUP_CONCAT( tanques.tanque ) tanque, 
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
if ($Reporte == 2) {
    $Fecha = str_replace("-", "", $Fecha);
    $cSql = "
        SELECT rm.id,rm.turno,rm.posicion,com.descripcion,rm.fin_venta,cli.alias,ROUND(IF(DATE(rm.fin_venta) = CURDATE(),rm.volumen,rm.importe/rm.precio),3) volumen,ROUND(IF(DATE(rm.fin_venta) = CURDATE(),rm.pesos,rm.importe),2) pesos , rm.tipo_venta, rm.cliente ,rm.tipodepago, rm.fin_venta fecha,
        rm.cliente, rm.corte, rm.comprobante, rm.uuid, ct.statusctv status,
        SUBSTR( UPPER( SHA1( CONCAT( '|', LPAD( rm.id, 7, '0' ), '|', LPAD( rm.posicion, 2, '0' ), '|', LPAD( rm.manguera, 2, '0' ), '|', LPAD( cia.idfae, 5, '0' ), '|', DATE_FORMAT( rm.fin_venta, '%Y-%m-%dT%H:%i:%s' ), '|', CAST( ROUND( rm.volumenv, 4 ) AS DECIMAL( 10, 4 ) ), '|', CAST( ROUND( rm.pesosv, 2 ) AS DECIMAL( 10, 2 ) ), '|' ) ) ), 1, 23 ) FOLIO_FAE FROM (
        SELECT
        aux.*, IF( DATE( aux.fin_venta ) = CURDATE() OR aux.importe = aux.pesosv OR (aux.uuid IS NOT NULL AND aux.uuid != '-----'), aux.pesosv, aux.importe) pesos,
        IF( DATE( aux.fin_venta ) = CURDATE() OR aux.importe = aux.pesosv OR (aux.uuid IS NOT NULL AND aux.uuid != '-----'),
        IF( ABS( aux.diferencia ) > 0, ROUND( ( aux.importec + IF( aux.desgloseieps = 'S', 0.00, aux.importeieps ) + aux.diferencia )/( aux.preciouu + IF( aux.desgloseieps = 'S', 0.0000, aux.ieps ) ), 4 ), aux.volumenv ),
        ROUND( aux.importe/aux.precio, 4 ) ) volumen
        FROM (
            SELECT
                IFNULL( cli.desgloseieps, 'N' ) desgloseieps, cli.tipodepago, rm.id, rm.tipo_venta,
                rm.posicion, rm.manguera, rm.fin_venta, rm.fecha_venta, rm.precio,
                ROUND( rm.volumen, 4 ) volumenv, ROUND( rm.pesos, 2 ) pesosv, ROUND( rm.importe, 2 ) importe,
                rm.producto, rm.iva,  rm.ieps, rm.comprobante, rm.cliente, rm.placas, rm.codigo, rm.turno, rm.corte,
                rm.uuid, rm.kilometraje,
                ROUND((rm.precio-rm.ieps)/(1+rm.iva), 4) preciouu, ROUND(rm.volumen * ROUND((rm.precio-rm.ieps)/(1+rm.iva), 4 ), 2) importec,
                ROUND(rm.volumen * ROUND((rm.precio-rm.ieps)/(1+rm.iva), 4 ) * rm.iva, 2 ) importeiva,
                ROUND(rm.volumen * rm.ieps, 2 ) importeieps,
                ROUND(rm.pesos, 2 ) - ROUND(rm.volumen * ROUND((rm.precio-rm.ieps)/(1+rm.iva), 4), 2) - ROUND(rm.volumen * ROUND((rm.precio-rm.ieps)/(1+rm.iva), 4 ) * rm.iva, 2) -ROUND(rm.volumen * rm.ieps, 2) diferencia
                FROM rm
                LEFT JOIN cli ON rm.cliente = cli.id
            ) aux
        ) rm
        LEFT JOIN cli ON rm.cliente = cli.id
        JOIN ct ON rm.corte = ct.id
        JOIN com ON rm.producto = com.clavei AND com.activo = 'Si'
        LEFT JOIN cia ON TRUE 
        WHERE TRUE ";

    if (strpos($Criterio, "rm.id") === false || empty($busca)) {
        if (!empty($Fecha)) {
            $cSql .= "AND rm.fecha_venta = $Fecha";
        } elseif (!empty($Corte) && $Corte > 0) {
            $cSql .= "AND rm.corte = $Corte";
            $Turno = "*";
        }

        if ($Posicion !== '*' && trim($Posicion) !== "") {
            $cSql .= " AND rm.posicion = '$Posicion'";
        }
        if ($Producto !== "*" && trim($Producto) !== "") {
            $cSql .= " AND rm.producto='$Producto' ";
        }
        if ($Turno !== '*' && trim($Turno) !== "") {
            $cSql .= " AND rm.turno = '$Turno'";
        }
        if ($Disponible === "S") {
            $cSql .= " AND rm.cliente = 0 and rm.uuid = '-----' AND rm.pesos > 0";
        }
    } else {
        $cSql .= "AND $Criterio = $busca";
    }
} elseif ($Reporte == 121) {
    error_log("desglose ieps: " . $Desglose);
    error_log("trae consignacion: " . $Consig);

    if ($Desglose === 'Cortes') {
        $cSql = " SELECT 
        A.fecha,
        A.descripcion,
        A.clavei,
        RPAD(IFNULL(rm.ieps, '0.00'), 6, 0) ieps,
        IFNULL(rm.precio, '0.00') precio,
        IFNULL(SUM(rm.ventas), 0) ventas,
        ROUND(IFNULL(SUM(rm.volumen), 0), 2) volumen,
        ROUND(IFNULL(SUM((rm.volumen * (rm.precio - rm.ieps)) / (rm.iva + 1)),
                        0),
                2) importe,
        ROUND(IFNULL(SUM(rm.volumen * rm.ieps), 0), 2) iepsCuota,
        ROUND(IFNULL(SUM((rm.volumen * (rm.precio - rm.ieps)) - (rm.volumen * (rm.precio - rm.ieps)) / (1 + rm.iva)),
                        0),
                2) iva,
        ROUND(IFNULL(SUM(rm.pesos), 0), 2) total
    FROM
        (SELECT 
            ct.id corte,
                DATE(ct.fecha) fecha,
                com.descripcion,
                com.clavei
        FROM
            com, ct
        WHERE
            com.activo = 'Si'
                AND DATE(ct.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
        GROUP BY ct.id , com.descripcion
        ORDER BY ct.id , com.descripcion DESC) A
            LEFT JOIN
        (SELECT 
            ct.id corte,
                rm.producto,
                COUNT(*) ventas,
                rm.ieps,
                rm.iva,
                rm.precio,
                ROUND(SUM(rm.pesosp), 2) pesos,
                ROUND(SUM(rm.volumenp), 2) volumen
        FROM
            ct
        LEFT JOIN rm ON ct.id = rm.corte AND rm.corte > 0
            AND rm.pesos > 0";
        if ($Consig === "No") {
            $cSql .= " AND rm.tipo_venta = 'D'";
        } elseif ($Consig === "Si") {
            $cSql .= " AND rm.tipo_venta in ('D','N')";
        }

        $cSql .= " WHERE
            DATE(ct.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
        GROUP BY ct.id , rm.producto , rm.precio , rm.ieps) rm ON A.corte = rm.corte
            AND rm.producto = A.clavei
    GROUP BY A.fecha , A.clavei , rm.precio
    ORDER BY A.fecha ,rm.pesos DESC;";
    } else {
        $cSql = "SELECT rm.fecha,
        A.descripcion,
        A.clavei,
        RPAD(IFNULL(rm.ieps, '0.00'), 6, 0) ieps,
        IFNULL(rm.precio, '0.00') precio,
        IFNULL(SUM(rm.ventas), 0) ventas,
        ROUND(IFNULL(SUM(rm.volumen), 0), 2) volumen,
        ROUND(
            IFNULL(
                SUM(
                    (rm.volumen * (rm.precio - rm.ieps)) / (rm.iva + 1)
                ),
                0
            ),
            2
        ) importe,
        ROUND(IFNULL(SUM(rm.volumen * rm.ieps), 0), 2) iepsCuota,
        ROUND(
            IFNULL(
                SUM(
                    (rm.volumen * (rm.precio - rm.ieps)) - (rm.volumen * (rm.precio - rm.ieps)) / (1 + rm.iva)
                ),
                0
            ),
            2
        ) iva,
        ROUND(IFNULL(SUM(rm.pesos), 0), 2) total
    FROM (
            SELECT com.descripcion,
                com.clavei
            FROM com
            WHERE com.activo = 'Si'
            GROUP BY com.descripcion
            ORDER BY com.descripcion DESC
        ) A
        LEFT JOIN (
            SELECT DATE(rm.fecha_venta) fecha,
                rm.corte,
                rm.producto,
                COUNT(*) ventas,
                rm.ieps,
                rm.iva,
                rm.precio,
                ROUND(SUM(rm.importe), 2) pesos,
                ROUND(SUM((importe / precio)), 2) volumen
            FROM rm
            WHERE DATE(rm.fecha_venta) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
                AND rm.pesos > 0
                AND rm.tipo_venta in ('D', 'N')
            GROUP BY rm.corte,
                rm.producto,
                rm.precio,
                rm.ieps
            ORDER BY producto ASC
        ) rm ON rm.producto = A.clavei
    GROUP BY rm.fecha,
        A.clavei,
        rm.precio
    ORDER BY rm.fecha,
        rm.pesos DESC;";
    }
} else if ($Reporte == 81) {
    if (!empty($Fecha)) {
        $ccSql .=" AND vt.fecha like '%" . $Fecha . "%'";
    }
    if (!empty($Corte) && $Corte > 0) {
        $ccSql .= "AND vt.corte = $Corte";
    }

    if ($Posicion !== '*' && trim($Posicion) !== "") {
        $ccSql .= " AND vt.posicion = '$Posicion'";
    }
    $cSql = "SELECT vt.id,vt.corte,vt.posicion,vt.fecha,vt.clave,vt.descripcion,vt.cantidad,vt.unitario,vt.total,cli.alias "
            . "FROM vtaditivos as vt LEFT JOIN cli ON vt.cliente = cli.id  WHERE TRUE $ccSql  AND vt.tm = 'C'   ORDER BY vt.id";
}