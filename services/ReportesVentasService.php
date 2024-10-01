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

$ConcentrarVtasTarjeta = $ciaVO->getVentastarxticket();

if ($request->hasAttribute("criteria")) {
    utils\HTTPUtils::setSessionValue("Anio", date("Y"));
    utils\HTTPUtils::setSessionValue("Mes", date("m"));
    utils\HTTPUtils::setSessionValue("Fecha", date("Y-m-d"));
    utils\HTTPUtils::setSessionValue("FechaI", date('Y-m-d', strtotime('-1 day', strtotime(date('Y-m-d')))));
    utils\HTTPUtils::setSessionValue("FechaF", date('Y-m-d', strtotime('-1 day', strtotime(date('Y-m-d')))));
    utils\HTTPUtils::setSessionValue("Turno", "No");
    utils\HTTPUtils::setSessionValue("Detallado", "No");
    utils\HTTPUtils::setSessionValue("Detalle", "No");
    $TDesglose = utils\IConnection::execSql("SELECT valor FROM variables_corporativo WHERE llave='Rep_gvc_visual'");
    $TipoDesglose = $TDesglose["valor"] == 2 ? "Cortes" : "Dia";
    utils\HTTPUtils::setSessionValue("Desglose", $TipoDesglose);
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
    utils\HTTPUtils::setSessionValue("FormaPago", "*");
    utils\HTTPUtils::setSessionValue("TVenta", "Jarreo");
}

$TiposClienteArray = Array(
    "Credito" => "Credito",
    "Contado" => "Contado",
    "Tarjeta" => "Tarjeta",
    "Monedero" => "Monederos",
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
if ($request->hasAttribute("Anio")) {
    utils\HTTPUtils::setSessionValue("Anio", $sanitize->sanitizeString("Anio"));
}
if ($request->hasAttribute("Mes")) {
    utils\HTTPUtils::setSessionValue("Mes", $sanitize->sanitizeString("Mes"));
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
    utils\HTTPUtils::setSessionValue("Consig", $sanitize->sanitizeString("Consig"));
}
if ($request->hasAttribute("Detallado")) {
    utils\HTTPUtils::setSessionValue("Detallado", $sanitize->sanitizeString("Detallado"));
}
if ($request->hasAttribute("Detalle")) {
    utils\HTTPUtils::setSessionValue("Detalle", $sanitize->sanitizeString("Detalle"));
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
if ($request->hasAttribute("FormaPago")) {
    utils\HTTPUtils::setSessionValue("FormaPago", $sanitize->sanitizeString("FormaPago"));
}

if ($request->hasAttribute("TVenta")) {
    utils\HTTPUtils::setSessionValue("TVenta", $sanitize->sanitizeString("TVenta"));
}

$Anio = utils\HTTPUtils::getSessionValue("Anio");
$Mes = utils\HTTPUtils::getSessionValue("Mes");
$Fecha = utils\HTTPUtils::getSessionValue("Fecha");
$FechaI = utils\HTTPUtils::getSessionValue("FechaI");
$FechaF = utils\HTTPUtils::getSessionValue("FechaF");
$Turno = utils\HTTPUtils::getSessionValue("Turno");
$Detallado = utils\HTTPUtils::getSessionValue("Detallado");
$Detalle = utils\HTTPUtils::getSessionValue("Detalle");
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
$FormaPago = utils\HTTPUtils::getSessionValue("FormaPago");
$TVenta = utils\HTTPUtils::getSessionValue("TVenta");

$Productos = array();
$sqlAdd = "";
if ($Producto && $Producto <> "*") {
    $sqlAdd = " AND clavei = '" . $Producto . "'";
}
$selectProductosActivos = $mysqli->query("SELECT id,clave,clavei,descripcion,color FROM com WHERE activo = 'Si' $sqlAdd ORDER BY descripcion DESC;");
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
        $DispensariosActivos[$row["dispensario"]] = $row["dispensario"];
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
        $PosicionesActivas[$row["posicion"]] = $row["posicion"];
    }
}

$IslasPosicion = array();
$IslasPosicion["*"] = "Todos";
$selectIslasPosicion = "SELECT isla_pos FROM  man 
                        WHERE activo = 'Si'
                        GROUP BY isla_pos";
if (($result = $mysqli->query($selectIslasPosicion))) {
    while ($row = $result->fetch_array()) {
        $IslasPosicion[$row["isla_pos"]] = $row["isla_pos"];
    }
}

$IslasPosicionInventario = array();
$selectIslasPosicionInventario = "SELECT isla_pos FROM  man 
                                  WHERE activo = 'Si' AND inventario = 'Si' 
                                  GROUP BY isla_pos";
if (($result = $mysqli->query($selectIslasPosicionInventario))) {
    while ($row = $result->fetch_array()) {
        $IslasPosicionInventario[$row["isla_pos"]] = $row["isla_pos"];
    }
}

$Turnos = array();
$selectTurnosActivos = "SELECT turno FROM tur WHERE activo = 'Si'";

if (($result = $mysqli->query($selectTurnosActivos))) {
    while ($row = $result->fetch_array()) {
        $Turnos[$row["turno"]] = $row["turno"];
    }
}

$PrmIva = 1 + $ciaVO->getIva() / 100;

$ajuste = utils\IConnection::execSql("SELECT valor FROM variables_corporativo WHERE llave = 'ajustar_totales'");
$groupcorte = utils\IConnection::execSql("SELECT valor FROM variables_corporativo WHERE llave = 'agruparXisla'");

if ($Desglose === "Cortes") {
    $selectRmDefault = "
                    SELECT DATE(ct.fecha) fecha, rm.corte, rm.tipo_venta, rm.producto, com.descripcion, 
                    rm.precio precios,
                    COUNT(rm.id) ventas,
                    AVG(rm.ieps) ieps,
                    SUM(rm.volumen) volumen, 
                    SUM(rm.pesos) pesos, 
                    COUNT(1) cantidadVenta,
                    SUM(rm.volumenp) volumenp, 
                    SUM(rm.pesosp) pesosp,
                    SUM(rm.descuento) descuento
                    FROM com, rm, ct
                    WHERE TRUE
                    AND com.clavei = rm.producto AND com.activo = 'Si'
                    AND rm.corte = ct.id AND rm.tipo_venta IN ('D')
                    AND DATE(ct.fecha) BETWEEN DATE('$FechaI') AND  DATE('$FechaF') 
        ";
    $selectRmDefaultN = "
                    SELECT DATE(ct.fecha) fecha, rm.corte, rm.tipo_venta, rm.producto, com.descripcion, 
                    rm.precio precios,
                    COUNT(rm.id) ventas,
                    AVG(rm.ieps) ieps,
                    SUM(rm.volumen) volumen, 
                    SUM(rm.pesos) pesos, 
                    COUNT(1) cantidadVenta,
                    SUM(rm.volumen) volumenp, 
                    SUM(rm.pesosp) pesosp,
                    sum(volumen) - sum(volumenp) Producido,
                    SUM(rm.pesos-rm.pesosp) ProducidoP,
                    SUM(rm.descuento) descuento
                    FROM com, rm, ct
                    WHERE TRUE
                    AND com.clavei = rm.producto AND com.activo = 'Si'
                    AND rm.corte = ct.id AND rm.tipo_venta IN ('N')
                    AND DATE(ct.fecha) BETWEEN DATE('$FechaI') AND  DATE('$FechaF') 
        ";
} else {
    $selectRmDefault = "
                    SELECT DATE(rm.fin_venta) fecha, 0 corte, rm.tipo_venta, rm.producto, com.descripcion, 
                    rm.precio precios,
                    COUNT(rm.id) ventas,
                    AVG(rm.ieps) ieps,
                    SUM(rm.volumen) volumen, 
                    SUM(rm.pesos) pesos, 
                    COUNT(1) cantidadVenta,
                    SUM(rm.importe / rm.precio) volumenp, 
                    SUM(rm.importe) pesosp,
                    SUM(rm.descuento) descuento
                    FROM com, rm
                    WHERE TRUE
                    AND com.clavei = rm.producto AND com.activo = 'Si'
                    AND rm.tipo_venta IN ('D')
                    AND rm.fecha_venta BETWEEN " . str_replace("-", "", $FechaI) . " AND " . str_replace("-", "", $FechaF) . " 
        ";
    $selectRmDefaultN = "
                    SELECT DATE(rm.fin_venta) fecha, 0 corte, rm.tipo_venta, rm.producto, com.descripcion, 
                    rm.precio precios,
                    COUNT(rm.id) ventas,
                    AVG(rm.ieps) ieps,
                    SUM(rm.volumen) volumen, 
                    SUM(rm.pesos) pesos, 
                    COUNT(1) cantidadVenta,
                    SUM(rm.importe / rm.precio) volumenp, 
                    SUM(rm.importe) pesosp,
                    sum(volumen) - sum(volumenp) Producido,
                    SUM(rm.pesos-rm.pesosp) ProducidoP,
                    SUM(rm.descuento) descuento
                    FROM com, rm
                    WHERE TRUE
                    AND com.clavei = rm.producto AND com.activo = 'Si'
                    AND rm.tipo_venta IN ('N')
                    AND rm.fecha_venta BETWEEN " . str_replace("-", "", $FechaI) . " AND " . str_replace("-", "", $FechaF) . " 
        ";
}


/* Consultas para reportes de venta por dia (Se utiliza en reporte de venta por dia GRAFICO) */

$selectByDia = "";
if ($Detallado === "Si") {
    if ($Turno === "No") {
        if ($Desglose == "Cortes") {
            $Sql = "
                (SELECT ct.id corte, DATE(ct.fecha)fecha 
                FROM ct 
                WHERE DATE(ct.fecha) BETWEEN DATE('$FechaI')
                AND DATE('$FechaF') 
                GROUP BY DATE(ct.fecha)) ct ";
        } else {
            $Sql = "
                (SELECT DATE(rm.fin_venta) fecha 
                FROM rm 
                WHERE DATE(rm.fin_venta) BETWEEN DATE('$FechaI')
                AND DATE('$FechaF') 
                GROUP BY DATE(rm.fin_venta)) ct ";
        }

        $pesos = $volumen = $ventas = "";

        foreach ($Productos as $rg) {
            $ventas .= $ventas == "" ? "(IFNULL($rg[clavei].ventas$rg[id],0) " : "+ IFNULL($rg[clavei].ventas$rg[id],0)";
            $ieps .= $ieps == "" ? "IFNULL($rg[clavei].ieps$rg[id] * $rg[clavei].volumen$rg[id],0) ieps$rg[id]" : " , IFNULL($rg[clavei].ieps$rg[id] * $rg[clavei].volumen$rg[id],0) ieps$rg[id]";
            $ImpSnIva .= $ImpSnIva == "" ? "IFNULL($rg[clavei].impSinIva$rg[id] ,0) impSinIva$rg[id]" : " , IFNULL($rg[clavei].impSinIva$rg[id] ,0) impSinIva$rg[id]";
            $DescuentoC .= $DescuentoC == "" ? "IFNULL($rg[clavei].descuento$rg[id] ,0) descuento$rg[id]" : " , IFNULL($rg[clavei].descuento$rg[id] ,0) descuento$rg[id]";
            $pesos .= $pesos == "" ? "IFNULL($rg[clavei].pesos$rg[id],0) pesos$rg[id]" : ", IFNULL($rg[clavei].pesos$rg[id],0) pesos$rg[id]";
            $volumen .= $volumen == "" ? "IFNULL($rg[clavei].volumen$rg[id],0) volumen$rg[id]" : ", IFNULL($rg[clavei].volumen$rg[id],0) volumen$rg[id]";
            $ventasP .= $ventasP == "" ? "IFNULL($rg[clavei].cantidadVenta$rg[id],0) cantidadVenta$rg[id]" : ", IFNULL($rg[clavei].cantidadVenta$rg[id],0) cantidadVenta$rg[id]";
            $pesosN .= $pesosN == "" ? "IFNULL($rg[clavei].pesosN$rg[id],0) pesosN$rg[id]" : ", IFNULL($rg[clavei].pesosN$rg[id],0) pesosN$rg[id]";
            $volumenN .= $volumenN == "" ? "IFNULL($rg[clavei].volumenN$rg[id],0) volumenN$rg[id]" : ", IFNULL($rg[clavei].volumenN$rg[id],0) volumenN$rg[id]";
            $ProducidoN .= $ProducidoN == "" ? "IFNULL($rg[clavei].producido$rg[id],0) producido$rg[id]" : ", IFNULL($rg[clavei].producido$rg[id],0) producido$rg[id]";
            $ProducidopN .= $ProducidopN == "" ? "IFNULL($rg[clavei].producidop$rg[id],0) producidop$rg[id]" : ", IFNULL($rg[clavei].producidop$rg[id],0) producidop$rg[id]";

            if ($Desglose == "Cortes") {
                $Sql .= "
                        LEFT JOIN (
                           SELECT rm.corte, rm.fecha, rm.ventas ventas$rg[id], rm.producto,rm.ieps ieps$rg[id],((rm.pesosp - IFNULL(rmN.ProducidoP,0) - (rm.ieps * (rm.volumenp - IFNULL(rmN.Producido,0) ) )) / 1.16) impSinIva$rg[id] , 
                            rm.pesosp - IFNULL(rmN.ProducidoP,0)  pesos$rg[id], rmN.Producido producido$rg[id],rmN.ProducidoP producidop$rg[id],
                            rm.volumenp volumen$rg[id],
                            rm.cantidadVenta cantidadVenta$rg[id],
                            rmN.pesosp pesosN$rg[id],
                            rmN.volumenp volumenN$rg[id],
                            rm.descuento descuento$rg[id]
                            FROM 
                            (
                                $selectRmDefault
                                AND rm.producto LIKE '%$rg[clavei]%'
                                GROUP BY DATE(ct.fecha)
                            ) rm 
                            LEFT JOIN (
                                $selectRmDefaultN
                                AND rm.producto LIKE '%$rg[clavei]%'
                                GROUP BY DATE(ct.fecha)
                            ) rmN ON rmN.fecha = rm.fecha
                            WHERE TRUE
                        ) $rg[clavei] ON DATE(ct.fecha) = $rg[clavei].fecha";
            } else {
                $Sql .= "
                        LEFT JOIN (
                            SELECT rm.corte, rm.fecha, rm.ventas ventas$rg[id], rm.producto,rm.ieps ieps$rg[id], ((rm.pesosp - IFNULL(rmN.ProducidoP,0) - (rm.ieps * (rm.volumenp - IFNULL(rmN.Producido,0)))) / 1.16) impSinIva$rg[id] , 
                            rm.pesosp - IFNULL(rmN.ProducidoP,0)  pesos$rg[id], rmN.producido producido$rg[id],rmN.ProducidoP producidop$rg[id],
                            rm.cantidadVenta cantidadVenta$rg[id],
                            rm.volumenp volumen$rg[id],
                            rmN.pesosp pesosN$rg[id],
                            rmN.volumenp volumenN$rg[id],
                            rm.descuento descuento$rg[id]
                            FROM 
                            (
                                $selectRmDefault
                                AND rm.producto LIKE '%$rg[clavei]%'
                                GROUP BY rm.fecha_venta
                            ) rm LEFT JOIN 
                            (
                                $selectRmDefaultN
                                AND rm.producto LIKE '%$rg[clavei]%'
                                GROUP BY rm.fecha_venta
                            ) rmN ON rm.fecha = rmN.fecha
                            WHERE TRUE
                        ) $rg[clavei] ON DATE(ct.fecha) = $rg[clavei].fecha";
            }
        }

        $ventas .= ") ventas";
        $pesos .= "";
        $volumen .= "";
        $ventasP .= "";
        if ($Desglose == "Cortes") {
            $Sql .= " 
            LEFT JOIN (
                SELECT vt.corte,DATE( ct.fecha ) fecha, SUM( vt.total  ) pesosA
                FROM ct, man, vtaditivos vt
                WHERE 1 = 1 
                AND ct.id = vt.corte
                AND man.posicion = vt.posicion AND man.activo = 'Si'
                AND DATE( ct.fecha ) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
                AND vt.tm = 'C'
                AND vt.cantidad > 0
                GROUP BY DATE( ct.fecha )
            ) VT ON DATE( ct.fecha ) = VT.fecha GROUP BY ct.fecha ";
        } else {
            $Sql .= " 
            LEFT JOIN (
                SELECT DATE( vt.fecha ) fecha, SUM( vt.total  ) pesosA
                FROM  man, vtaditivos vt
                WHERE 1 = 1
                AND man.posicion = vt.posicion AND man.activo = 'Si'
                AND DATE( vt.fecha ) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
                AND vt.tm = 'C' AND vt.cantidad > 0
                GROUP BY DATE( vt.fecha )
            ) VT ON DATE(ct.fecha) = VT.fecha GROUP BY ct.fecha ";
        }

        $selectByDia = "
            SELECT * FROM ( 
                SELECT  CONCAT('| ',ct.fecha,' |') fecha , $ventas,$pesosN,$volumenN, $pesos, $volumen,$ventasP, $ProducidoN,$ProducidopN,$ieps,$ImpSnIva,$DescuentoC,SUM(VT.pesosA) pesos_ace
                FROM $Sql
            ) sub 
            GROUP BY sub.fecha 
            ORDER BY sub.fecha";
    } else {
        $selectByDia = "
            SELECT * FROM (
                SELECT sub.corte,  CONCAT('| ',sub.fecha,' |') fecha , sub.producto, sub.ventas, sub.pesosp importe, sub.volumenp volumen,VT.pesosA 
                FROM (
                    $selectRmDefault
                    GROUP BY ct.id, com.clavei
                ) sub
                LEFT JOIN (
                    SELECT vt.corte,DATE( ct.fecha ) fecha, SUM( vt.total  ) pesosA
                    FROM ct, man, vtaditivos vt
                    WHERE 1 = 1 
                    AND ct.id = vt.corte
                    AND man.posicion = vt.posicion AND man.activo = 'Si'
                    AND DATE( ct.fecha ) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
                    AND vt.tm = 'C' AND vt.cantidad > 0
                    GROUP BY ct.id
                ) VT ON sub.corte = VT.corte
            ) sub
            WHERE sub.corte > 0;";
    }
} else {                //if ($Detallado === "No")
    if ($Desglose === "Cortes") {

        $selectByDia = "
            SELECT  CONCAT('| ',sub.fecha,' |') fecha ,sub.ventas ventas,sub.pesos  - IFNULL(subN.ProducidoP,0) pesos, sub.volumen - IFNULL(subN.Producido,0) volumen,SUM(VT.pesosA) pesos_ace,
            subN.pesos pesosN, subN.volumen volumenN,sub.descuento
            FROM (
                SELECT rm.fecha, rm.corte, rm.ventas, rm.pesosp pesos, rm.volumenp volumen,rm.descuento
                FROM (
                    $selectRmDefault
                    GROUP BY DATE(ct.fecha)
                ) rm 
                GROUP BY rm.fecha
            ) sub LEFT JOIN (
                SELECT rm.fecha, rm.corte, rm.ventas, rm.pesosp pesos, rm.volumenp volumen,rm.Producido,rm.ProducidoP
                FROM (
                    $selectRmDefaultN
                    GROUP BY DATE(ct.fecha)
                ) rm 
                GROUP BY rm.fecha
            ) subN ON sub.fecha=subN.fecha 
            LEFT JOIN (
                SELECT vt.corte,DATE( ct.fecha ) fecha, SUM( vt.total  ) pesosA
                FROM ct, man, vtaditivos vt
                WHERE 1 = 1 
                AND man.posicion = vt.posicion AND man.activo = 'Si'
                AND ct.id = vt.corte
                AND DATE( ct.fecha ) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
                AND vt.tm = 'C'
                AND vt.cantidad > 0
                GROUP BY DATE( ct.fecha )
            ) VT ON DATE( sub.fecha ) = VT.fecha
            GROUP BY fecha;";
    } else {

        $selectByDia = "
            SELECT CONCAT('| ',sub.fecha,' |') fecha ,sub.ventas ventas,sub.pesos pesos, sub.volumen volumen,subN.pesos pesosN,subN.volumen volumenN,
            VT.pesosA pesos_ace,sub.descuento
            FROM (
                SELECT  CONCAT('| ',rm.fecha,' |') fecha , rm.corte, rm.ventas, rm.pesosp pesos, rm.volumenp volumen,rm.descuento
                FROM (
                    $selectRmDefault
                    GROUP BY rm.fecha_venta
                ) rm 
                GROUP BY rm.fecha 
            ) sub
            LEFT JOIN 
            (
                SELECT  CONCAT('| ',rm.fecha,' |') fecha, rm.corte, rm.ventas, rm.pesosp pesos, rm.volumenp volumen,rm.Producido,rm.ProducidoP
                FROM (
                    $selectRmDefaultN
                    GROUP BY rm.fecha_venta
                ) rm 
                GROUP BY rm.fecha 
            ) subN ON sub.fecha = subN.fecha
            LEFT JOIN (
                SELECT DATE( vt.fecha ) fecha, SUM( vt.total  ) pesosA
                FROM man, vtaditivos vt
                WHERE 1 = 1 
                AND man.posicion = vt.posicion AND man.activo = 'Si'
                AND DATE( vt.fecha ) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
                AND vt.tm = 'C'
                AND vt.cantidad > 0
                GROUP BY DATE( vt.fecha )
            ) VT ON DATE(sub.fecha) = VT.fecha ";
    }
}


/* Consultas para reportes de venta por dia clientes */

$selectByDiaCli = "";
if ($Detallado === "Si") {

    $Sql = "
            (SELECT DATE(rm.fin_venta) fecha 
            FROM rm 
            WHERE DATE(rm.fin_venta) BETWEEN DATE('$FechaI')
            AND DATE('$FechaF') 
            GROUP BY DATE(rm.fin_venta)) ct ";

    $pesos = $volumen = $ventas = "";

    foreach ($Productos as $rg) {
        $ventas .= $ventas == "" ? "(IFNULL($rg[clavei].ventas$rg[id],0) " : "+ IFNULL($rg[clavei].ventas$rg[id],0)";
        $pesos .= $pesos == "" ? "IFNULL($rg[clavei].pesos$rg[id],0) pesos$rg[id]" : ", IFNULL($rg[clavei].pesos$rg[id],0) pesos$rg[id]";
        $volumen .= $volumen == "" ? "IFNULL($rg[clavei].volumen$rg[id],0) volumen$rg[id]" : ", IFNULL($rg[clavei].volumen$rg[id],0) volumen$rg[id]";

        $Sql .= "
                LEFT JOIN (
                    SELECT DATE(rm.fin_venta) fecha, COUNT( * ) ventas$rg[id],rm.producto,
                    SUM( rm.pagoreal ) pesos$rg[id], 
                    SUM( rm.pagoreal / rm.precio ) volumen$rg[id]
                    FROM man, rm 
                    WHERE 1 = 1 
                    AND man.posicion = rm.posicion AND man.activo = 'Si'
                    AND DATE(rm.fin_venta) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
                    AND rm.tipo_venta = 'D' AND rm.cliente = '$Cliente'
                    AND rm.producto LIKE '%$rg[clavei]%'
                    GROUP BY DATE(rm.fin_venta)
                ) $rg[clavei] ON DATE(ct.fecha) = $rg[clavei].fecha";
    }

    $ventas .= ") ventas";
    $pesos .= "";
    $volumen .= "";

    $Sql .= " 
            LEFT JOIN (
                SELECT DATE( vt.fecha ) fecha, SUM( vt.total  ) pesosA
                FROM  man, vtaditivos vt
                WHERE 1 = 1 
                AND man.posicion = vt.posicion AND man.activo = 'Si'
                AND DATE( vt.fecha ) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
                AND vt.tm = 'C' 
                AND vt.cantidad > 0
                AND vt.cliente = '$Cliente'
                GROUP BY DATE( vt.fecha )
            ) VT ON DATE(ct.fecha) = VT.fecha GROUP BY ct.fecha ";

    $selectByDiaCli = "
            SELECT * FROM ( 
                SELECT ct.fecha,$ventas,$pesos,$volumen,SUM(VT.pesosA) pesosA FROM $Sql
            ) sub 
            WHERE sub.ventas > 0
            GROUP BY sub.fecha 
            ORDER BY sub.fecha";
} else {
    $selectByDiaCli = "
            SELECT sub.*, VT.pesosA 
            FROM (
                SELECT rm.*
                FROM (
                    SELECT DATE( rm.fin_venta ) fecha, COUNT( * ) ventas, 
                    ROUND( SUM( rm.pagoreal ) , 2) pesos, 
                    ROUND( SUM( rm.pagoreal / rm.precio ), 2) volumen
                    FROM man, rm 
                    WHERE 1 = 1 
                    AND man.posicion = rm.posicion AND man.activo = 'Si'
                    AND DATE( rm.fin_venta ) BETWEEN DATE('$FechaI') AND DATE('$FechaF')  
                    AND rm.tipo_venta = 'D' AND rm.cliente = '$Cliente'
                    GROUP BY DATE( rm.fin_venta )
                ) rm 
                GROUP BY rm.fecha 
            ) sub
            LEFT JOIN (
                SELECT DATE( vt.fecha ) fecha, SUM( vt.total  ) pesosA
                FROM man, vtaditivos vt
                WHERE 1 = 1 
                AND man.posicion = vt.posicion AND man.activo = 'Si'
                AND DATE( vt.fecha ) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
                AND vt.tm = 'C' AND vt.cantidad > 0
                AND vt.cliente = '$Cliente'
                GROUP BY DATE( vt.fecha )
            ) VT ON DATE(sub.fecha) = VT.fecha ";
}

/* Consulta para reporte de ventas por producto */

$selectByProducto = "
            SELECT rm.tipo_venta tipo, (rm.ventas + IFNUll(rmN.ventas,0 )) ventas, rm.descripcion producto, 
            rm.pesosp -  IFNUll(ProducidoP,0) pesos, rm.volumenp - IFNULL(Producido,0) volumen,0 pesosN, 
            rmN.volumenp volumenN,rmN.tipo_venta tipoN,rm.descuento
            FROM 
            (
                $selectRmDefault
                GROUP BY rm.producto, rm.tipo_venta 
                ORDER BY rm.producto DESC
            ) rm LEFT JOIN  (
                $selectRmDefaultN
                GROUP BY rm.producto, rm.tipo_venta 
                ORDER BY rm.producto DESC
            ) rmN ON rm.producto = rmN.producto
            WHERE TRUE
            ";

/* Consultas para inventario de aceites */

if ($Detallado === "No") {
    $selectInventario = "SELECT * FROM inv WHERE rubro = 'Aceites' AND activo = 'Si' ORDER BY clave_producto";
} else {
    $selectInventario = "
                SELECT inv.id clave,inv.clave_producto,inv.descripcion,IFNULL(inicio,0) inicio,IFNULL(compras,0) compras,IFNULL(ventas,0) ventas,
                (IFNULL(inicio,0) + IFNULL(compras,0) - IFNULL(ventas,0) ) exi
                FROM inv LEFT JOIN (
                    SELECT vt.clave,
                    IFNULL(MAX(vt.inicio),0) inicio,IFNULL(MAX(vt.compras),0) compras,IFNULL(MAX(vt.ventas),0) ventas
                    FROM(
                        SELECT vt.clave,vt.descripcion,vt.tm,
                        SUM(IF(vt.tm = 'C',-vt.cantidad,IF(vt.posicion = '0',vt.cantidad,0))) inicio,0 ventas,0 compras 
                        FROM man, vtaditivos vt
                        WHERE 1 = 1 
                        AND man.posicion = vt.posicion AND man.activo = 'Si'
                        AND DATE(vt.fecha) < DATE('$FechaI')
                        AND vt.cantidad > 0 AND vt.cantidad > 0 
                        GROUP BY vt.clave
                        
                        UNION ALL
                        
                        SELECT vt.clave,vt.descripcion,vt.tm,0 inicio,SUM(vt.cantidad) ventas,0 compras 
                        FROM man, vtaditivos vt
                        WHERE 1 = 1 
                        AND man.posicion = vt.posicion AND man.activo = 'Si'
                        AND DATE(vt.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF') AND tm = 'C'
                        AND vt.cantidad > 0 AND vt.cantidad > 0
                        GROUP BY vt.clave 
                        
                        UNION ALL
                        
                        SELECT vt.clave,vt.descripcion,vt.tm,0 inicio,0 ventas,SUM(vt.cantidad) compras 
                        FROM man, vtaditivos vt
                        WHERE 1 = 1 
                        AND man.posicion = vt.posicion AND man.activo = 'Si'
                        AND DATE(vt.fecha)  BETWEEN DATE('$FechaI') AND DATE('$FechaF') AND tm = 'H'
                        AND vt.cantidad > 0 AND vt.cantidad > 0 AND vt.posicion = 0
                        GROUP BY vt.clave 
                    ) vt
                GROUP BY vt.clave) sub ON inv.id = sub.clave
                WHERE inv.rubro = 'Aceites' AND inv.activo = 'Si'
                ORDER BY clave_producto;";
}

/* Consultas para venta de aceites */

if ($Detallado === "No") {
    if ($Desglose === "Cortes") {
        $selectVentaAceites = "
                SELECT man.isla, vt.posicion, IFNULL(ven.alias, CONCAT('Posicion ', vt.posicion)) alias, 
                SUM(vt.cantidad) cantidad, 
                ROUND( SUM( inv.costo * vt.cantidad ), 2) costo, SUM(vt.cantidad * vt.unitario) importe
                FROM ct, inv, man, vtaditivos vt
                LEFT JOIN ven ON vt.vendedor = ven.id
                WHERE 1 = 1 
                AND man.posicion = vt.posicion AND man.activo = 'Si' 
                AND vt.clave = inv.id AND inv.activo = 'Si'
                AND ct.id = vt.corte
                AND DATE(ct.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
                AND vt.tm = 'C' AND vt.cantidad > 0                
                GROUP BY vt.posicion";

        $selectVentaAceitesP = "
                SELECT vt.clave, inv.clave_producto,inv.descripcion, SUM(vt.cantidad) cantidad,
                vt.unitario, SUM( vt.cantidad * vt.unitario ) importe,
                ROUND( SUM( inv.costo * vt.cantidad ), 2) costo
                FROM ct, man, vtaditivos vt, inv
                WHERE 1 = 1 
                AND man.posicion = vt.posicion 
                AND vt.clave = inv.id AND inv.activo = 'Si'
                AND ct.id = vt.corte 
                AND DATE( ct.fecha ) BETWEEN DATE('$FechaI') AND  DATE('$FechaF')
                AND vt.tm = 'C' AND vt.cantidad > 0
                GROUP BY vt.clave
                order by  inv.clave_producto asc";
        $selectVentaAceitesNvo = "SELECT isla, posicion,fecha, IFNULL(alias, CONCAT('Posicion ', posicion)) alias,clave_producto,  descripcion,SUM(cantidad) cantidad, ROUND( SUM( costo * cantidad ), 2) costo, SUM(cantidad * unitario) importe FROM 
                (SELECT man.isla, vt.posicion, IFNULL(ven.alias, CONCAT('Posicion ', vt.posicion)) alias, inv.clave_producto,  inv.descripcion,
                inv.costo,vt.cantidad , vt.unitario ,DATE_FORMAT(vt.fecha, '%Y-%m-%d') fecha
                FROM ct, inv, man, vtaditivos vt LEFT JOIN ven ON vt.vendedor = ven.id WHERE 1 = 1 AND man.posicion = vt.posicion 
                AND man.activo = 'Si' AND vt.clave = inv.id AND inv.activo = 'Si' AND ct.id = vt.corte AND 
                DATE(ct.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF') AND vt.tm = 'C' AND vt.cantidad > 0) a GROUP BY fecha ORDER BY fecha;";
    } else {
        $selectVentaAceites = "
                SELECT man.isla, vt.posicion, IFNULL(ven.alias, CONCAT('Posicion ', vt.posicion)) alias, 
                SUM(vt.cantidad) cantidad, 
                ROUND( SUM( inv.costo * vt.cantidad ), 2) costo, SUM(vt.cantidad * vt.unitario) importe
                FROM inv, man, vtaditivos vt
                LEFT JOIN ven ON vt.vendedor = ven.id
                WHERE 1 = 1 
                AND man.posicion = vt.posicion AND man.activo = 'Si' 
                AND vt.clave = inv.id AND inv.activo = 'Si'
                AND DATE(vt.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
                AND vt.tm = 'C' AND vt.cantidad > 0                
                GROUP BY vt.posicion";

        $selectVentaAceitesP = "
                SELECT vt.clave, inv.clave_producto, inv.descripcion, SUM(vt.cantidad) cantidad,
                ROUND( SUM( inv.costo * vt.cantidad ), 2) costo, SUM( vt.cantidad * vt.unitario ) importe
                FROM man, vtaditivos vt, inv
                WHERE 1 = 1 
                AND man.posicion = vt.posicion  
                AND vt.clave = inv.id AND inv.activo = 'Si'
                AND DATE( vt.fecha ) BETWEEN DATE('$FechaI') AND  DATE('$FechaF')
                AND vt.tm = 'C' AND vt.cantidad > 0
                GROUP BY vt.clave
                order by  inv.clave_producto asc";
        $selectVentaAceitesNvo = "SELECT isla, posicion,fecha, IFNULL(alias, CONCAT('Posicion ', posicion)) alias,clave_producto,  descripcion,SUM(cantidad) cantidad, ROUND( SUM( costo * cantidad ), 2) costo, SUM(cantidad * unitario) importe FROM 
                (SELECT man.isla, vt.posicion, IFNULL(ven.alias, CONCAT('Posicion ', vt.posicion)) alias, inv.clave_producto,  inv.descripcion,
                inv.costo,vt.cantidad , vt.unitario ,DATE_FORMAT(vt.fecha, '%Y-%m-%d') fecha
                FROM ct, inv, man, vtaditivos vt LEFT JOIN ven ON vt.vendedor = ven.id WHERE 1 = 1 AND man.posicion = vt.posicion 
                AND man.activo = 'Si' AND vt.clave = inv.id AND inv.activo = 'Si' AND ct.id = vt.corte AND 
                DATE(ct.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF') AND vt.tm = 'C' AND vt.cantidad > 0) a GROUP BY fecha ORDER BY fecha;";
    }
} elseif ($Detallado === "Si") {
    if ($Desglose === "Cortes") {
        $selectVentaAceites = "
                SELECT vt.posicion, inv.clave_producto, vt.clave, IFNULL(ven.alias, CONCAT('Posicion ', vt.posicion)) alias, 
                inv.descripcion,SUM( vt.cantidad ) cantidad, 
                inv.precio,SUM( vt.cantidad * vt.unitario ) importe
                FROM ct, inv, man, vtaditivos vt
                LEFT JOIN ven ON vt.vendedor = ven.id
                WHERE 1 = 1 
                AND man.posicion = vt.posicion AND man.activo = 'Si' 
                AND vt.clave = inv.id AND inv.activo = 'Si'
                AND ct.id = vt.corte
                AND DATE( ct.fecha ) BETWEEN DATE('$FechaI') AND  DATE('$FechaF')
                AND vt.tm = 'C' AND vt.cantidad > 0
                GROUP BY vt.posicion, vt.clave
                order by  inv.clave_producto asc";
    } else {
        $selectVentaAceites = "
                SELECT vt.posicion, vt.clave clave_producto, IFNULL(ven.alias, CONCAT('Posicion ', vt.posicion)) alias, 
                inv.descripcion,SUM( vt.cantidad ) cantidad, 
                inv.precio,SUM( vt.cantidad * vt.unitario ) importe
                FROM inv, man, vtaditivos vt
                LEFT JOIN ven ON vt.vendedor = ven.id
                WHERE 1 = 1 
                AND man.posicion = vt.posicion AND man.activo = 'Si' 
                AND vt.clave = inv.id AND inv.activo = 'Si'
                AND DATE( vt.fecha ) BETWEEN DATE('$FechaI') AND  DATE('$FechaF')
                AND vt.tm = 'C' AND vt.cantidad > 0
                GROUP BY vt.posicion, vt.clave";
    }
}

/* Consulta para reporte de vendido y facturado de aceites */


$selectVF_Aceites = "
            SELECT cli.tipodepago,'Aditivos y aceites' producto, SUM(vt.cantidad) cantidad,SUM(vt.total) total,
            ROUND(SUM((vt.total/(1+vt.iva)) * vt.iva),2) iva,ROUND(SUM(vt.total - ((vt.total/(1+vt.iva)) * vt.iva)),2) importe
            FROM cli,vtaditivos vt,inv 
            WHERE TRUE
            AND cli.id = vt.cliente
            AND vt.clave = inv.id AND inv.activo = 'Si'
            AND vt.tm = 'C'
            AND vt.cantidad > 0
            AND DATE(vt.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF') 
            GROUP BY cli.tipodepago
            ORDER BY cli.tipodepago DESC";

$selectVF_AceitesFAC = "
            SELECT fcd.producto, ROUND(SUM( fcd.cantidad ),2) cantidad, 
            ROUND(SUM( fcd.importe ),2) importe, 'Venta facturada' descripcion, COUNT( * ) folios, 
            ROUND(SUM( fcd.importe * ( cia.iva /100 ) ),2) iva
            FROM fc,fcd,inv,cia,cli
            WHERE fc.id = fcd.id 
            AND fcd.producto = inv.id AND fc.cliente = cli.id
            AND DATE(fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
            AND inv.id > 10
            AND cli.rfc NOT LIKE 'XAXX%'
            AND inv.rubro = 'Aceites'
            AND inv.activo = 'Si'
            AND fc.status = " . StatusFactura::CERRADO . "
            ORDER BY inv.descripcion DESC";

$selectVF_AceitesDIF = "
            SELECT cli.tipodepago, 'Aditivos y aceites' producto, 
            (SUM(vt.cantidad) - IFNULL(fac.cantidad,0)) cantidad,
            ROUND(SUM(vt.total * vt.iva) - IFNULL(fac.iva,0),2) iva, 
            ROUND(SUM(vt.total) - IFNULL(fac.importe,0),2) total
            FROM cli, vtaditivos vt,inv 
            LEFT JOIN($selectVF_AceitesFAC) fac ON true
            WHERE TRUE
            AND cli.id = vt.cliente
            AND vt.clave = inv.id AND inv.rubro = 'Aceites'
            AND vt.tm = 'C'
            AND vt.cantidad > 0
            AND DATE(vt.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
            GROUP BY cli.tipodepago
            ORDER BY cli.tipodepago DESC";

$selectVF_AceitesGRAL = "
            SELECT 'Aditivos y aceites' descripcion, ROUND(SUM( fcd.cantidad ),2) cantidad, 
            ROUND(SUM(fcd.importe),2) importe, 
            ROUND(SUM( fcd.cantidad * fcd.ieps ),2) ieps
            FROM inv,fcd,fc,cli
            WHERE  inv.id = fcd.producto 
            AND fcd.id = fc.id
            AND fc.cliente = cli.id
            AND fcd.producto > 5 
            AND DATE(fc.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF') AND fc.id = fcd.id 
            AND fc.status = " . StatusFactura::CERRADO . " AND cli.rfc LIKE 'XAXX%' AND cli.tipodepago = 'Contado'
            AND inv.rubro = 'Aceites'
            AND inv.activo = 'Si'
            ORDER BY inv.descripcion DESC";

/* Consultas para reporte concentrado */
$Tot = utils\IConnection::execSql("SELECT valor FROM variables_corporativo WHERE llave='OrdenReportes'");
$FiltroSql = $Tot["valor"] != "" ? $Tot["valor"] : "ORDER BY com.descripcion DESC";
$selectConcentradoGRAL = "
            SELECT com.descripcion producto, rm.ventas, rm.precios precio,
            rm.volumenp volumen, rm.pesosp pesos,(rm.volumenp * rm.ieps) ieps
            FROM com
            LEFT JOIN             
            (
                $selectRmDefault
                GROUP BY rm.producto, rm.precio  
                ORDER BY rm.producto DESC
            ) rm ON rm.producto = com.clavei 
            WHERE TRUE AND com.activo = 'Si'
            " . $FiltroSql;
$selectConcentradoGRALN = "
            SELECT com.descripcion producto, rm.ventas, rm.precios precio,
            rm.volumenp volumen, rm.pesosp pesos,(rm.volumenp * rm.ieps) ieps
            FROM com
            LEFT JOIN             
            (
                $selectRmDefaultN
                GROUP BY rm.producto, rm.precio  
                ORDER BY rm.producto DESC
            ) rm ON rm.producto = com.clavei 
            WHERE TRUE AND com.activo = 'Si'
            " . $FiltroSql;
if ($Desglose == "Cortes") {
    $selectConcentradoAceGRAL = "
            SELECT SUM(vt.cantidad) cantidad,SUM(vt.total) importe,iva
            FROM vtaditivos vt,ct
            WHERE vt.corte = ct.id 
            AND vt.tm = 'C'
            AND vt.cantidad > 0
            AND DATE( ct.fecha )  BETWEEN DATE('$FechaI') AND DATE('$FechaF')";

    $selectConcentradoJar = "
            SELECT com.descripcion producto, SUM( volumen ) volumen , SUM( pesos ) pesos , 
            ROUND(SUM( rm.pesos - IF(DATE( ct.fecha ) = CURDATE(),rm.pesosp,rm.pesosp) ), 2) ieps
            FROM rm,ct,com 
            WHERE rm.corte = ct.id AND rm.producto = com.clavei
            AND DATE( ct.fecha ) BETWEEN DATE('$FechaI') AND DATE('$FechaF') 
            AND rm.tipo_venta IN ('J','A')
            GROUP BY rm.producto DESC";

    $selectConcentradoCli = "
            SELECT  cli.tipodepago, rm.cliente, cli.nombre,rm.inicio_venta,
            SUM( IF( ABS(rm.pesos - rm.pagoreal) > 1, rm.pagoreal/rm.precio, rm.volumen) ) cantidad, 
            SUM(rm.pagoreal) importe,rm.id
            FROM rm,ct,cli 
            WHERE rm.corte = ct.id AND rm.cliente = cli.id
            AND DATE(ct.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
            AND rm.tipo_venta IN ('D','N') AND cli.tipodepago NOT REGEXP 'Contado|Puntos'
            GROUP BY " . ($Turno === "Si" ? "rm.turno," : "") . "cli.tipodepago, cli.id";

    $selectConcentradoTar = "
            SELECT CONCAT(IFNULL(cli.alias,''),' ',IFNULL(vaucher,'')) cpto, importe, vendedor,vaucher
            FROM ct,cttarjetas
            LEFT JOIN cli ON cttarjetas.banco = cli.id
            WHERE ct.id = cttarjetas.id AND DATE(ct.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF') ";

    if ($Detallado === "Si") {
        $totl = ",vt.total ";
        $Fch = ",vt.fecha fecha";
    } elseif ($Detalle == "Si") {
        $totl = ",vt.total ";
        $Fch = ",'' fecha";
    } else {
        $totl = ",SUM(vt.total) ";
        $Fch = ",'' fecha";
    }

    $selectConcentradoAce = "
            SELECT CONCAT(vt.clave, ' | ', vt.descripcion) descripcion,vt.cantidad cantidad $totl importe,vt.id $Fch,cli.tipodepago,vt.cliente,cli.nombre
            FROM vtaditivos vt,cli,ct
            WHERE vt.cliente = cli.id  AND vt.corte = ct.id 
            AND vt.tm = 'C' 
            AND vt.cantidad > 0
            -- AND cli.tipodepago NOT REGEXP 'Contado|Puntos'
            AND DATE( ct.fecha )  BETWEEN DATE('$FechaI') AND DATE('$FechaF')
            ";
} else {

    $selectConcentradoAceGRAL = "
            SELECT vt.posicion,SUM(vt.cantidad) cantidad,SUM(vt.total) importe
            FROM vtaditivos vt
            WHERE vt.tm = 'C'
            AND vt.cantidad > 0
            AND DATE(vt.fecha)  BETWEEN DATE('$FechaI') AND DATE('$FechaF')";

    $selectConcentradoJar = "
            SELECT com.descripcion producto, SUM( volumen ) volumen , SUM( pesos ) pesos , 
            ROUND(SUM( rm.pesos - IF(DATE( ct.fecha ) = CURDATE(),rm.pesosp,rm.pesosp) ), 2) ieps
            FROM rm,com,ct 
            WHERE rm.producto = com.clavei AND rm.corte = ct.id
            AND  rm.fecha_venta  BETWEEN " . str_replace("-", "", $FechaI) . " AND " . str_replace("-", "", $FechaF) . " 
            AND rm.tipo_venta IN ('J','A')
            GROUP BY rm.producto DESC";

    $selectConcentradoCli = "
            SELECT  cli.tipodepago, rm.cliente, cli.nombre,rm.inicio_venta,
            SUM( IF( ABS(rm.pesos - rm.pagoreal) > 1, rm.pagoreal/rm.precio, rm.volumen) ) cantidad, 
            SUM(rm.pagoreal) importe,rm.id
            FROM rm,cli 
            WHERE rm.cliente = cli.id AND 
            rm.fecha_venta  BETWEEN " . str_replace("-", "", $FechaI) . " AND " . str_replace("-", "", $FechaF) . " 
            AND rm.tipo_venta IN ('D','N') AND cli.tipodepago NOT REGEXP 'Contado|Puntos'
            GROUP BY cli.tipodepago, cli.id";

    $selectConcentradoTar = "
            SELECT CONCAT(IFNULL(cli.alias,''),' ',IFNULL(vaucher,'')) cpto, importe, vendedor,vaucher
            FROM cttarjetas
            LEFT JOIN cli ON cttarjetas.banco = cli.id
            WHERE DATE(cttarjetas.fecha)  BETWEEN DATE('$FechaI') AND DATE('$FechaF') AND cttarjetas.id > 0 ";

    if ($Detallado === "Si") {
        $totl = ",vt.total ";
        $Fch = ",vt.fecha fecha";
    } elseif ($Detalle == "Si") {
        $totl = ",vt.total ";
        $Fch = ",'' fecha";
    } else {
        $totl = ",SUM(vt.total) ";
        $Fch = ",'' fecha";
    }
    $selectConcentradoAce = "
            SELECT CONCAT(vt.clave, ' | ', vt.descripcion) descripcion,vt.cantidad cantidad $totl importe,vt.id $Fch,cli.tipodepago,vt.cliente,cli.nombre
            FROM vtaditivos vt,cli 
            WHERE vt.cliente = cli.id  
            AND vt.tm = 'C' 
            AND vt.cantidad > 0
            -- AND cli.tipodepago NOT REGEXP 'Contado|Puntos'
            AND DATE(vt.fecha)  BETWEEN DATE('$FechaI') AND DATE('$FechaF')
            ";
}

if ($Detallado === "Si") {
    $selectConcentradoCli .= ", rm.id";
    $selectConcentradoAce .= "ORDER BY cli.id,cli.tipodepago";
} else if ($Detallado === "No" && $Detalle == "Si") {
    $selectConcentradoAce .= "ORDER BY cli.id,cli.tipodepago";
} else {
    $selectConcentradoTar .= "GROUP BY cttarjetas.banco";
    $selectConcentradoAce .= "GROUP BY cli.id,cli.tipodepago";
}

$selectConcentradoGastos = "
            SELECT ctpagos.corte,ctpagos.cliente,cli.nombre as nombrec,ctpagos.concepto,
            ctpagos.importe,ctpagos.idnvo 
            FROM ctpagos 
            LEFT JOIN cli ON ctpagos.cliente = cli.id
            LEFT JOIN ct ON ctpagos.corte = ct.id
            WHERE DATE(ct.fecha)  BETWEEN DATE('$FechaI') AND DATE('$FechaF')";

$selectConcentradoIngresos1 = "
            SELECT egr.clave, bancos.banco, bancos.cuenta, CONCAT(bancos.concepto,' - ',egr.concepto) concepto, egr.plomo des, 
            SUM(egr.importe) importe
            FROM ct,egr LEFT JOIN bancos ON egr.clave = bancos.id 
            WHERE ct.id = egr.corte 
            AND DATE(ct.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
            AND bancos.cuenta <> 'EFECTIVO'
            GROUP BY egr.clave,UPPER(TRIM(egr.concepto))
            ORDER BY bancos.banco";

if ($variablesCorpDepositos->getValor() == 1) {
    $selectConcentradoIngresos1 = "
            SELECT egr.clave, bancos.banco, bancos.cuenta, CONCAT(bancos.concepto,' - ',egr.concepto) concepto, egr.plomo des, 
            egr.importe
            FROM ct,egr LEFT JOIN bancos ON egr.clave = bancos.id 
            WHERE ct.id = egr.corte 
            AND DATE(ct.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
            AND bancos.cuenta <> 'EFECTIVO'
            ORDER BY bancos.banco";
}

$selectConcentradoIngresos2 = " 
            SELECT egr.clave, bancos.banco, bancos.cuenta, CONCAT(bancos.concepto,' - ',egr.concepto) concepto, egr.concepto des, 
            SUM(egr.importe) importe 
            FROM ct,egr LEFT JOIN bancos ON egr.clave = bancos.id 
            WHERE ct.id = egr.corte 
            AND DATE(ct.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
            AND bancos.cuenta = 'EFECTIVO'
            GROUP BY egr.clave
            ORDER BY bancos.banco";

if ($variablesCorpDepositos->getValor() == 1) {
    $selectConcentradoIngresos2 = " 
            SELECT egr.clave, bancos.banco, bancos.cuenta, CONCAT(bancos.concepto,' - ',egr.concepto) concepto, egr.concepto des, 
            egr.importe
            FROM ct,egr LEFT JOIN bancos ON egr.clave = bancos.id 
            WHERE ct.id = egr.corte 
            AND DATE(ct.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
            AND bancos.cuenta = 'EFECTIVO'
            ORDER BY bancos.banco";
}

/* Consultas para relacion de vales */

$selectVales = "
            SELECT boletos.codigo,genbol.fecha,boletos.secuencia,cli.nombre as cliente,boletos.importe,
            boletos.importecargado,boletos.vigente,genbol.fechav 
            FROM cli,boletos 
            LEFT JOIN genbol ON boletos.id = genbol.id
            WHERE genbol.cliente = cli.id 
            AND DATE(genbol.fecha) BETWEEN DATE('$FechaI') AND  DATE('$FechaF') AND genbol.status = 'Cerrada' ";

$selectValesT = "
            SELECT genbol.cliente,cli.nombre,COUNT(boletos.codigo) vales,SUM(boletos.importe) importe,
            SUM(boletos.importecargado) importecargado,IF(boletos.vigente = 'Si' AND genbol.fechav > CURDATE(), 'Vigente', 'Vencido') status
            FROM cli,boletos
            LEFT JOIN genbol ON boletos.id = genbol.id
            WHERE genbol.cliente = cli.id
            AND DATE(genbol.fecha) BETWEEN DATE('$FechaI') AND  DATE('$FechaF') AND genbol.status = 'Cerrada' ";

if ($Cliente !== "*" && is_numeric($Cliente) && $Cliente > 0) {
    $selectVales .= " AND cli.id = '$Cliente' ";
    $selectValesT .= " AND cli.id = '$Cliente' ";
}

if ($Status !== "*") {
    $selectVales .= " AND boletos.vigente LIKE '%$Status%' ";
    $selectValesT .= " AND boletos.vigente LIKE '%$Status%' ";
}

$selectVales .= " 
            ORDER BY cli.nombre,genbol.fecha,boletos.secuencia DESC";
$selectValesT .= " 
            GROUP BY genbol.cliente,boletos.vigente
            ORDER BY cli.nombre,genbol.fecha,boletos.secuencia DESC";

/* Consultas para relacion de vales detalle */
$selectValesD = "
            SELECT g.id orden,g.cliente,cli.nombre,
            rm.codigo codigo_rm,b.ticket,rm.fin_venta fecha,rm.pesos pesos_rm,
            ROUND(SUM(b.importe1 + b.importe2),2) consumido,
            ROUND(SUM(b.importecargado),2) importecargado
            FROM cli,genbol g,boletos b, rm
            WHERE cli.id = g.cliente AND g.id = b.id AND b.ticket  = rm.id
            AND DATE(rm.fin_venta) BETWEEN DATE('$FechaI') AND  DATE('$FechaF') ";

if ($Cliente !== "*" && is_numeric($Cliente) && $Cliente > 0) {
    $selectValesD .= " AND cli.id = '$Cliente' ";
}

if ($Status !== "*") {
    $selectValesD .= " AND b.vigente LIKE '%$Status%' ";
}

$selectValesD .= " GROUP BY b.ticket ORDER BY g.cliente,b.ticket ASC;";

/* Consultas para jarreos */

if ($TVenta == "Jarreo") {
    $TventaS = "'J','A'";
    $TventaO = "ORDER BY rm.fin_venta";
} elseif ($TVenta == "Consignacion") {
    $TventaS = "'N'";
    $TventaO = "ORDER BY rm.fin_venta";
} elseif ($TVenta == "Ambos") {
    $TventaS = "'J','A','N'";
    $TventaO = "ORDER BY rm.tipo_venta, rm.fin_venta ";
}
if ($Desglose === "Cortes") {
    $selectJarreos = "
            SELECT rm.id,rm.corte,rm.fin_venta fecha,rm.posicion,com.descripcion,rm.volumen,rm.pesos,
            CASE 
	            WHEN rm.tipo_venta = 'J' THEN 'Jarreo'
                WHEN rm.tipo_venta = 'A' THEN 'Auto Jarreo'
                WHEN rm.tipo_venta = 'N' THEN 'Consignación'
            END as tipo_venta 
            FROM ct,rm LEFT JOIN com ON rm.producto = com.clavei
            WHERE ct.id = rm.corte AND 
            DATE(ct.fecha) BETWEEN DATE('$FechaI') AND ('$FechaF') 
            AND rm.tipo_venta IN ($TventaS) $TventaO;";
} else {
    $selectJarreos = "
            SELECT rm.id,rm.corte,rm.fin_venta as fecha,rm.posicion,com.descripcion,rm.volumen,rm.pesos, 
            CASE 
	            WHEN rm.tipo_venta = 'J' THEN 'Jarreo'
                WHEN rm.tipo_venta = 'A' THEN 'Auto Jarreo'
                WHEN rm.tipo_venta = 'N' THEN 'Consignación'
            END as tipo_venta
            FROM rm LEFT JOIN com ON rm.producto = com.clavei
            WHERE DATE(rm.fin_venta) BETWEEN DATE('$FechaI') AND DATE('$FechaF') 
            AND tipo_venta IN ($TventaS) $TventaO;";
}

/* Consultas para reporte de ieps */
if (empty($Corte)) {
    if ($Desglose == "Cortes") {
        $selectIeps .= " SELECT 
        A.fecha,
        A.descripcion,
        SUM(rm.descuento) descuento,
        A.clavei,
        IFNULL(rm.ieps, '0.00') ieps,
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
                ROUND(SUM(rm.descuento), 2) descuento,
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
            $selectIeps .= " AND rm.tipo_venta = 'D'";
        } elseif ($Consig === "Si") {
            $selectIeps .= " AND rm.tipo_venta in ('D','N')";
        }

        $selectIeps .= " WHERE
            DATE(ct.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
        GROUP BY ct.id , rm.producto , rm.precio , rm.ieps,rm.fecha_venta) rm ON A.corte = rm.corte
            AND rm.producto = A.clavei
    GROUP BY A.fecha , A.clavei , rm.precio
   ORDER BY A.fecha ,rm.producto ASC;";
    } else {
        $selectIeps = "  SELECT rm.fecha,
    A.descripcion,
    A.clavei,
    SUM(rm.descuento) descuento,
    IFNULL(rm.ieps, '0.00') ieps,
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
        SELECT DATE(rm.fin_venta) fecha,
            rm.corte,
            rm.producto,
            COUNT(*) ventas,
            rm.ieps,
            ROUND(SUM(rm.descuento), 2) descuento,
            rm.iva,
            rm.precio,
            ROUND(SUM(rm.importe), 2) pesos,
            ROUND(SUM((importe / precio)), 2) volumen
        FROM rm
        WHERE DATE(rm.fin_venta) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
            AND rm.pesos > 0 ";

        if ($Consig === "No") {
            $selectIeps .= " AND rm.tipo_venta = 'D'";
        } elseif ($Consig === "Si") {
            $selectIeps .= " AND rm.tipo_venta in ('D','N')";
        }
        $selectIeps .= " GROUP BY rm.corte,
            rm.producto,
            rm.precio,
            rm.ieps,
            rm.fecha_venta
        ORDER BY producto ASC
    ) rm ON rm.producto = A.clavei
GROUP BY rm.fecha,
    A.clavei,
    rm.precio
ORDER BY rm.fecha ,rm.producto ASC;";
    }
} else {
    $selectIeps = "
                SELECT rm.*,
                ROUND((rm.volumen * ( rm.precio -  rm.ieps))/(rm.iva + 1),2) importe,
                ROUND(rm.volumen * rm.ieps,2) iepsCuota,
                ROUND((rm.volumen * (rm.precio - rm.ieps)) - (rm.volumen * (rm.precio - rm.ieps))/(1 + rm.iva),2) iva,
                ROUND(rm.volumen * rm.precio,2) total
                FROM(
                        SELECT rm.dispensario,rm.posicion,rm.manguera,com.descripcion, COUNT(*) ventas,rm.ieps,rm.iva,rm.precio,
                        ROUND(SUM(rm.volumen),2) volumen, ROUND(SUM(rm.pesos),2) pesos, ROUND(SUM(rm.descuento)) descuento
                        FROM ct,rm 
                        LEFT JOIN com ON com.clavei = rm.producto AND com.activo = 'Si'
                        WHERE ct.id = $Corte AND ct.id = rm.corte AND rm.corte > 0 AND rm.tipo_venta = 'D'
                        GROUP BY ct.id,rm.dispensario,rm.posicion,rm.manguera,rm.producto,rm.precio,rm.ieps
                ) rm;
                        ";
    $selectIepsT = "
                SELECT 
                com.descripcion producto,
                IFNULL(COUNT(*),0) ventas,IFNULL(rm.precio,'0.00') precio,
                IFNULL(ROUND(SUM(rm.pesos),2),0) pesos,IFNULL(ROUND(SUM(rm.volumen),2),0) volumen 
                FROM ct 
                LEFT JOIN rm ON ct.id = rm.corte AND rm.tipo_venta = 'D'
                LEFT JOIN com ON rm.producto = com.clavei AND com.activo = 'Si'
                WHERE ct.id = $Corte
                GROUP BY rm.producto,rm.precio
                ORDER BY rm.producto DESC";
}

/* Consultas para reporte de ingresos(bancos) */

$selectBancos = "
                SELECT ct.fechaf fecha,ct.id corte,egr.clave,bancos.banco,bancos.cuenta,
                bancos.concepto cptcuenta,egr.concepto,egr.importe,ct.turno,egr.plomo,
                ct.statusctv
                FROM ct,egr 
                LEFT JOIN bancos ON egr.clave = bancos.id 
                WHERE ct.id = egr.corte
                AND DATE(ct.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
                ORDER BY bancos.banco,ct.id";

/* Consultas para timbrado de CFDI */

if ($TipoRelacion === "Facturacion") {
    if ($Status < StatusFactura::CANCELADO_ST || $Status === "*") {
        $selectCFDI = "
        SELECT fc.id,fc.serie, fc.folio,fc.cancelacion, fc.fecha, fc.cliente, cli.nombre, fc.cantidad, fc.iva, IF(fc.origen=3,T.ieps,fc.ieps) ieps, IF(fc.ieps=0,fc.importe-T.ieps,fc.importe) importe,fc.total,T.descuento,
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
        $selectCFDI = "
            SELECT fc.id,fc.folio,fc.cancelacion, fc.fecha, fc.cliente, cli.nombre, fc.cantidad, fc.iva, fc.ieps, fc.importe,fc.total, fc.serie,
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

    $selectCFDI = "
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

    $selectCFDI = "
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
    $selectCFDI .= " AND cli.tipodepago = '$TipoCliente'";
}
if ($Descartar === "Si") {
    $selectCFDI .= " AND cli.rfc <> 'XAXX010101000' ";
}
if (is_numeric($Status)) {
    $selectCFDI .= " AND fc.status = $Status";
}
if (!empty($Cliente)) {
    $selectCFDI .= " AND cli.id  = $Cliente";
}
$selectCFDICancelados = $selectCFDI . " AND fc.status IN (2,3)";

$selectCFDI .= " AND fc.status NOT IN (0,2,3) ";

$selectCFDI .= " GROUP BY fc.id ORDER BY fc.folio ";

/* Consulta de CFDI y pagos relacionados */

$selectCFDI_Pagos = "
            SELECT * FROM (
                SELECT fc.id, fc.folio, fc.fecha, fc.cliente, cli.nombre,cli.tipodepago, IFNULL(cp.descripcion, 'NA') formadepago,
                fc.cantidad, fc.importe, fc.iva, fc.ieps,fc.total, fc.status, TRIM(fc.uuid) uuid, T.productos,
                IFNULL(pagos.id, '') pago, IFNULL(pagos.fecha_deposito, '') fecha_pago,  IFNULL(pagose.importe, 0) importe_pago
                FROM fc 
                LEFT JOIN cli ON fc.cliente = cli.id
                LEFT JOIN pagose ON fc.id = pagose.factura
                LEFT JOIN pagos ON pagose.id = pagos.id AND pagos.statusCFDI = " . StatusPagoCFDI::CERRADO . "
                LEFT JOIN cfdi33_c_fpago cp ON cp.clave = cli.formadepago
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
                DATE($Criterio) BETWEEN DATE('$FechaI') AND DATE('$FechaF') AND cli.tipodepago = '" . TiposCliente::CREDITO . "'
                    
                UNION
                
                SELECT fc.id, fc.folio, fc.fecha, fc.cliente, cli.nombre,cli.tipodepago, IFNULL(cp.descripcion, 'NA') formadepago,
                fc.cantidad, fc.importe, fc.iva, fc.ieps,fc.total, fc.status, TRIM(fc.uuid) uuid, T.productos,
                fc.relacioncfdi pago, pagos.fecha_deposito fecha_pago, pagos.importe importe_pago
                FROM fc 
                LEFT JOIN cli ON fc.cliente = cli.id
                LEFT JOIN pagos ON fc.relacioncfdi = pagos.id AND fc.tdoctorelacionado = 'ANT' AND pagos.statusCFDI = " . StatusPagoCFDI::CERRADO . "
                LEFT JOIN cfdi33_c_fpago cp ON cp.clave = cli.formadepago
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
                DATE($Criterio) BETWEEN DATE('$FechaI') AND DATE('$FechaF') AND cli.tipodepago = '" . TiposCliente::PREPAGO . "' AND fc.relacioncfdi > 0
                ) fc
                WHERE 1 = 1 
            ";

if ($TipoCliente !== "*") {
    $selectCFDI_Pagos .= " AND fc.tipodepago = '$TipoCliente'";
}
if (is_numeric($Status)) {
    $selectCFDI_Pagos .= " AND fc.status = $Status";
}
if (!empty($Cliente)) {
    $selectCFDI_Pagos .= " AND fc.cliente  = $Cliente";
}

$selectCFDI_Pagos .= " GROUP BY fc.id ORDER BY fc.folio ";

/* Consulta para reporte de 24 Hrs */

$select24hrs = "
            SELECT man_pro.dispensario,man_pro.posicion,man_pro.manguera,man_pro.producto,com.descripcion,IFNULL(ventasD.precio,0) precio,
            IFNULL(ventasD.despachos,0) despachosD,IFNULL(ventasD.volumen,0) volumenD,IFNULL(ventasD.importe,0) importeD,IFNULL(ventasD.realD,0) realD,
            IFNULL(ventasJ.despachos,0) despachosJ,IFNULL(ventasJ.volumen,0) volumenJ,IFNULL(ventasJ.importe,0) importeJ,
            IFNULL(ctd.ivolumen1,0) ivolumen1,IFNULL(ctd.ivolumen2,0) ivolumen2,IFNULL(ctd.ivolumen3,0) ivolumen3,
            IFNULL(ctd.fvolumen1,0) fvolumen1,IFNULL(ctd.fvolumen2,0) fvolumen2,IFNULL(ctd.fvolumen3,0) fvolumen3,
            IFNULL(ctd.fvolumen1 - ctd.ivolumen1,0) tvolumen1,IFNULL(ctd.fvolumen2 - ctd.ivolumen2,0) tvolumen2,IFNULL(ctd.fvolumen3 - ctd.ivolumen3,0) tvolumen3
            FROM  com, man_pro
            LEFT JOIN (
                    SELECT rm.posicion, rm.manguera, rm.precio, COUNT( * ) despachos,SUM( rm.volumenp) volumen, SUM( rm.pesosp ) importe, SUM( rm.pesos ) realD
                    FROM ct, rm
                    WHERE DATE( ct.fecha ) = DATE('$Fecha') AND ct.id = rm.corte AND rm.tipo_venta IN ('D','N')
                    GROUP BY rm.posicion, rm.manguera
            ) ventasD ON man_pro.posicion = ventasD.posicion AND man_pro.manguera = ventasD.manguera
            LEFT JOIN (
                    SELECT rm.posicion, rm.manguera, rm.precio, COUNT( * ) despachos,SUM( rm.volumen) volumen, SUM( rm.pesos ) importe
                    FROM ct, rm
                    WHERE DATE( ct.fecha ) = DATE('$Fecha') AND ct.id = rm.corte AND rm.tipo_venta IN ('J','A')
                    GROUP BY rm.posicion, rm.manguera
            ) ventasJ ON man_pro.posicion = ventasJ.posicion AND man_pro.manguera = ventasJ.manguera
            LEFT JOIN (
                    SELECT ctd.id,ctd.posicion,MIN(ctd.ivolumen1) ivolumen1,MIN(ctd.ivolumen2) ivolumen2,MIN(ctd.ivolumen3) ivolumen3,
                    MAX(ctd.fvolumen1) fvolumen1, MAX(ctd.fvolumen2) fvolumen2, MAX(ctd.fvolumen3) fvolumen3
                    FROM ct,ctd WHERE ct.id = ctd.id AND DATE( ct.fecha ) = DATE('$Fecha')
                    GROUP BY ctd.posicion
            ) ctd ON man_pro.posicion = ctd.posicion
            WHERE man_pro.producto = com.clavei AND man_pro.activo = 'Si' AND com.activo = 'Si'
            ORDER BY man_pro.posicion,man_pro.manguera
            ";

$select24hrsT = "
            SELECT man_pro.dispensario,man_pro.posicion,man_pro.manguera,man_pro.producto,com.descripcion,IFNULL(ventasD.precio,0) precio,
            IFNULL(ventasD.despachos,0) despachosD,IFNULL(ventasD.volumen,0) volumenD,IFNULL(ventasD.importe,0) importeD,IFNULL(ventasD.realD,0) realD
            FROM  com, man_pro
            LEFT JOIN (
                    SELECT rm.producto, rm.precio, COUNT( * ) despachos,SUM( rm.volumenp) volumen, SUM( rm.pesosp ) importe, SUM( rm.pesos ) realD
                    FROM ct, rm
                    WHERE DATE( ct.fecha ) = DATE('$Fecha') AND ct.id = rm.corte AND rm.tipo_venta IN ('D','N')
                    GROUP BY rm.producto
            ) ventasD ON man_pro.producto = ventasD.producto
            WHERE man_pro.producto = com.clavei AND man_pro.activo = 'Si' AND com.activo = 'Si'
            GROUP BY man_pro.producto 
            ORDER BY man_pro.producto
            ";

/* Consulta para reporte de venta por hora GRAFICO */

$selectByHora = "
            SELECT LPAD(H.hora,2,0) hora,IFNULL(rm.importe,0) importe,IFNULL(rm.volumen,0) volumen,IFNULL(rm.ventas,0) ventas 
            FROM 
                (SELECT @i:=@i+1 AS hora
                 FROM information_schema.TABLES, (SELECT @i:=-1) h
                 WHERE @i < 23
                 ) H
            LEFT JOIN 
                (
                    SELECT fecha,hora,COUNT(*) ventas,SUM(importe) importe,SUM(volumen) volumen 
                    FROM (
                            SELECT rm.id,rm.fin_venta fecha, HOUR(rm.fin_venta) hora, 
                            ROUND(rm.pesosp,2) importe, ROUND(rm.volumenp,3) volumen
                            FROM rm
                            WHERE DATE(fin_venta) BETWEEN DATE('$FechaI') AND DATE('$FechaF') 
                            AND rm.tipo_venta='D' 
                    ) sub
                    GROUP BY hora
                ) rm ON rm.hora = H.hora
            ORDER BY H.hora;";

/* Consulta para reporte de venta por despachador GRAFICO */

$selectByVendedor = "
            SELECT A.posicion,A.ventas,A.importe combustible,ven.alias vendedor,
            A.volumen,IFNULL(B.cantidad,0) cantidad,IFNULL(B.total,0) aceites
            FROM ven, 
            (
                SELECT DATE( ct.fecha ) fecha, count( * ) ventas,rm.vendedor,rm.posicion,
                ROUND(SUM( rm.pesosp ),2) importe, ROUND(SUM( rm.volumenp ),3) volumen
                FROM ct, rm
                WHERE DATE( ct.fecha ) BETWEEN DATE('$FechaI') AND DATE('$FechaF')  
                AND rm.tipo_venta = 'D' AND ct.id = rm.corte
                GROUP BY rm.vendedor
            ) A 
            LEFT JOIN
            (
                SELECT IFNULL(SUM(vt.cantidad),0) cantidad,IFNULL(SUM(vt.total),0) total,vt.vendedor
                FROM vtaditivos vt,ct
                WHERE DATE( ct.fecha ) BETWEEN DATE('$FechaI') AND DATE('$FechaF')  
                AND ct.id = vt.corte 
                AND vt.tm = 'C'
                AND vt.cantidad > 0
                GROUP BY vt.vendedor  
            ) B ON A.vendedor = B.vendedor 
            WHERE ven.id = A.vendedor;";

/* Consultas para reporte de corte cerrado (contable) */

$selectPreciosByCorte = "
            SELECT com.descripcion,  ROUND(IFNULL(AVG(SUB.precio), getproducto($Corte, com.id)),2) precio 
            FROM com LEFT JOIN (
                    SELECT rm.producto, rm.precio
                    FROM rm WHERE TRUE
                    AND rm.corte = $Corte
                    GROUP BY rm.producto, rm.precio
            ) SUB ON com.clavei = SUB.producto
            WHERE com.activo = 'Si'
            GROUP BY com.descripcion
            ORDER BY com.descripcion DESC;
            ";

if ($Detallado === "Si") {
    $selectVentaByCorteCerrado = "
            SELECT com.descripcion combustible,sub.posicion,sub.manguera,sub.ventas,
            sub.volumenp litros,sub.pesosp importe,
            sub.v_jarreos litrosj,sub.p_jarreos importej,
            sub.v_total,sub.p_total,N_vtotal,N_ptotal
            FROM
            com,(
                SELECT m.posicion,m.manguera,m.producto,IFNULL(A.vendedor,m.posicion) vendedor,IFNULL(A.ventas,0) ventas,IFNULL(A.volumenp,0) volumenp,
                IFNULL(A.pesosp,0) pesosp,IFNULL(B.volumen,0) v_jarreos,IFNULL(B.pesos,0) p_jarreos,
                IFNULL(A.volumenp,0) v_total,IFNULL(A.pesosp,0) - IFNULL(A.descuento,0) p_total
                ,IFNULL(N.volumen,0) N_vtotal, IFNULL(N.pesos,0) N_ptotal
                FROM man,man_pro m 
                LEFT JOIN
                (
                    SELECT rm.posicion,rm.manguera,ven.alias vendedor,COUNT(*) ventas,ROUND(SUM(rm.volumenp),2) volumenp,ROUND(SUM(rm.pesosp),2) pesosp,SUM(descuento) descuento
                    FROM man,rm 
                    LEFT JOIN ven ON rm.vendedor = ven.id
                    WHERE 1 = 1 
                    AND man.posicion = rm.posicion AND man.activo = 'Si'
                    AND rm.corte = $Corte AND rm.tipo_venta = 'D'
                    GROUP BY rm.posicion,rm.manguera
                ) A ON m.posicion = A.posicion AND m.manguera = A.manguera
                LEFT JOIN
                (
                    SELECT rm.posicion,rm.manguera,COUNT(*) ventas,ROUND(SUM(rm.volumen),2) volumen,ROUND(SUM(rm.pesos),2) pesos
                    FROM man,rm 
                    WHERE 1 = 1 
                    AND man.posicion = rm.posicion AND man.activo = 'Si'
                    AND rm.corte = $Corte AND rm.tipo_venta = 'J' 
                    GROUP BY rm.posicion,rm.manguera
                ) B ON m.posicion = B.posicion AND m.manguera = B.manguera
                 LEFT JOIN
                (
                    SELECT rm.posicion,rm.manguera,COUNT(*) ventas,ROUND(SUM(rm.volumen),2) volumen,ROUND(SUM(rm.pesos),2) pesos
                    FROM man,rm 
                    WHERE 1 = 1 
                    AND man.posicion = rm.posicion AND man.activo = 'Si'
                    AND rm.corte = $Corte AND rm.tipo_venta = 'N' 
                    GROUP BY rm.posicion,rm.manguera
                ) N ON m.posicion = N.posicion AND m.manguera = N.manguera
                WHERE 1 = 1
                AND man.posicion = m.posicion AND man.activo = 'Si'
                AND m.activo = 'Si') sub
            WHERE com.clavei = sub.producto
            GROUP BY sub.posicion,sub.manguera
            ORDER BY sub.posicion,sub.manguera ASC;";
} else {
    $selectVentaByCorteCerrado = "
            SELECT com.descripcion combustible,sub.posicion,sub.despachador,
            SUM(sub.ventas) ventas,SUM(sub.pesosp) importe,SUM(sub.volumenp) litros,
            SUM(sub.v_jarreos) litrosj,SUM(sub.p_jarreos) importej,
            SUM(sub.v_total) v_total,SUM(sub.p_total) p_total, N_vtotal,N_ptotal
            FROM
            com,(
                SELECT m.posicion,m.manguera,m.producto,IFNULL(A.vendedor,m.posicion) despachador,IFNULL(A.ventas,0) ventas,IFNULL(A.volumenp,0) volumenp,
                IFNULL(A.pesosp,0) pesosp,IFNULL(B.volumen,0) v_jarreos,IFNULL(B.pesos,0) p_jarreos,
                IFNULL(A.volumenp,0) - IFNULL(N.vol,0) v_total,IFNULL(A.pesosp,0)  - IFNULL(N.imp,0) - IFNULL(A.descuento,0) p_total
                 ,IFNULL(N.volumen,0) N_vtotal, IFNULL(N.pesos,0) N_ptotal
                FROM man,man_pro m 
                LEFT JOIN
                (
                    SELECT rm.posicion,rm.manguera,ven.alias vendedor,COUNT(*) ventas,SUM(rm.volumenp) volumenp,SUM(rm.pesosp) pesosp,SUM(descuento) descuento
                    FROM man,rm 
                    LEFT JOIN ven ON rm.vendedor = ven.id
                    WHERE 1 = 1 
                    AND man.posicion = rm.posicion AND man.activo = 'Si'
                    AND rm.corte = $Corte AND rm.tipo_venta = 'D'
                    GROUP BY rm.posicion,rm.manguera
                ) A ON m.posicion = A.posicion AND m.manguera = A.manguera
                LEFT JOIN
                (
                    SELECT rm.posicion,rm.manguera,COUNT(*) ventas,SUM(rm.volumen) volumen,SUM(rm.pesos) pesos
                    FROM man,rm 
                    WHERE 1 = 1 
                    AND man.posicion = rm.posicion AND man.activo = 'Si'
                    AND rm.corte = $Corte AND rm.tipo_venta IN ('J','A')
                    GROUP BY rm.posicion,rm.manguera
                ) B ON m.posicion = B.posicion AND m.manguera = B.manguera
                 LEFT JOIN
                (
                    SELECT rm.posicion,rm.manguera,COUNT(*) ventas,ROUND(SUM(rm.volumen),2) volumen,ROUND(SUM(rm.pesos),2) pesos,
                    sum(volumen) - SUM(volumenp) vol,SUM(importe) -  SUM(pesosp) imp
                    FROM man,rm 
                    WHERE 1 = 1 
                    AND man.posicion = rm.posicion AND man.activo = 'Si'
                    AND rm.corte = $Corte AND rm.tipo_venta = 'N' 
                    GROUP BY rm.posicion,rm.manguera
                ) N ON m.posicion = N.posicion AND m.manguera = N.manguera
                WHERE 1 = 1
                AND man.posicion = m.posicion AND man.activo = 'Si'
                AND m.activo = 'Si') sub
            WHERE com.clavei = sub.producto
            GROUP BY sub.posicion
            ORDER BY sub.posicion,sub.manguera ASC;";
}

$selectAditivosByCorteCerrado = "
            SELECT vt.clave,inv.descripcion,vt.cantidad,
            vt.unitario,vt.total,cli.tipodepago,cli.nombre
            FROM cli, man, vtaditivos vt 
            LEFT JOIN inv ON vt.clave = inv.id AND inv.activo = 'Si'
            WHERE 1 = 1 
            AND vt.cliente = cli.id 
            AND man.posicion = vt.posicion AND man.activo = 'Si'
            AND vt.corte = $Corte                         
            AND vt.tm = 'C' AND vt.cantidad > 0";

$selectGastosByCorteCerrado = "
            SELECT ctpagos.corte,ctpagos.cliente,cli.alias,ctpagos.concepto,
            ctpagos.importe,ctpagos.idnvo 
            FROM ctpagos
            LEFT JOIN cli ON ctpagos.cliente=cli.id
            WHERE ctpagos.corte = $Corte";

$selectProductoByCorteCerrado = "
            SELECT ROUND(SUM( rm.pesosp) - IFNULL(Tn.imp,0),2) importe, ROUND(SUM( rm.volumenp ) - IFNULL(Tn.vol,0),3) volumen , com.descripcion,
            COUNT( * ) despachos 
            FROM man, rm 
            LEFT JOIN com ON rm.producto = com.clavei
            LEFT JOIN 
	(SELECT sum(volumen) - SUM(volumenp) vol,SUM(importe) -  SUM(pesosp) imp,producto
                FROM rm WHERE corte = $Corte AND tipo_venta='N' GROUP BY producto) Tn
            ON Tn.producto=rm.producto
            WHERE 1 = 1 
            AND man.posicion = rm.posicion AND man.activo = 'Si'
            AND rm.corte = $Corte AND rm.tipo_venta in ('D')
            GROUP BY com.descripcion DESC";

/* Consulta para reporte de corte abierto */

$selectVentaByCorteAbierto = "
            CREATE TEMPORARY TABLE corte_tmp
            SELECT  man.isla_pos, m.dispensario,m.posicion,m.manguera,com.descripcion producto,com.clavei,descuento,
                    IFNULL(rmd.ventas,0) ventas_d,IFNULL(rmd.pesos,0) pesos_d,IFNULL(rmd.volumen,0) volumen_d,
                    IFNULL(rmj.ventas,0) ventas_j,IFNULL(rmj.pesos,0) pesos_j,IFNULL(rmj.volumen,0) volumen_j,
                    IFNULL(rmN.volumen,0) volumen_n,IFNULL(rmN.pesos,0) pesos_n,
                    IFNULL(ct.i_vol1,0) i_vol1,
                    IF(IFNULL(ct.f_vol1,0) = 0,IFNULL(rmd.volumen,0) + IFNULL(rmj.volumen,0),IFNULL(ct.f_vol1,0)) f_vol1,
                    IF(IFNULL(ct.vol1,0) = 0,IFNULL(rmd.volumen,0),IFNULL(ct.vol1,0)) vol1,
                    IFNULL(ct.i_vol2,0) i_vol2,IFNULL(ct.f_vol2,0) f_vol2,IFNULL(ct.vol2,0) vol2,
                    IFNULL(ct.i_vol3,0) i_vol3,IFNULL(ct.f_vol3,0) f_vol3,IFNULL(ct.vol3,0) vol3,
                    IFNULL(ct.i_mon1,0) i_mon1,
                    IF(IFNULL(ct.f_mon1,0) = 0,IFNULL(rmd.pesos,0) + IFNULL(rmj.pesos,0),IFNULL(ct.f_mon1,0)) f_mon1,
                    IF(IFNULL(ct.mon1,0) = 0,IFNULL(rmd.pesos,0),IFNULL(ct.mon1,0)) mon1,
                    IFNULL(ct.i_mon2,0) i_mon2,IFNULL(ct.f_mon2,0) f_mon2,IFNULL(ct.mon2,0) mon2,
                    IFNULL(ct.i_mon3,0) i_mon3,IFNULL(ct.f_mon3,0) f_mon3,IFNULL(ct.mon3,0) mon3,
                    (SELECT alias FROM ven WHERE id = rmd.vendedor) ven
            FROM com, man, man_pro m 
            LEFT JOIN  
            (
                    SELECT man.isla_pos, rm.posicion,rm.manguera,rm.producto,COUNT(*) ventas,SUM(rm.pesos) pesos,SUM(rm.volumen) volumen,SUM(descuento) descuento,vendedor
                    FROM man, rm WHERE TRUE 
                    AND man.posicion = rm.posicion AND man.activo = 'Si'
                    AND rm.corte = $Corte AND rm.tipo_venta = 'D'
                    GROUP BY rm.posicion,rm.manguera
            ) rmd ON m.posicion = rmd.posicion AND m.manguera = rmd.manguera
            LEFT JOIN 
            (
                    SELECT man.isla_pos, rm.posicion,rm.manguera,rm.producto,COUNT(*) ventas,SUM(rm.pesos) pesos,SUM(rm.volumen) volumen 
                    FROM man, rm WHERE TRUE 
                    AND man.posicion = rm.posicion AND man.activo = 'Si'
                    AND rm.corte = $Corte AND rm.tipo_venta = 'N'
                    GROUP BY rm.posicion,rm.manguera
            ) rmN ON m.posicion = rmN.posicion AND m.manguera = rmN.manguera LEFT JOIN  
            (
                    SELECT man.isla_pos, rm.posicion,rm.manguera,rm.producto,COUNT(*) ventas,SUM(rm.pesos) pesos,SUM(rm.volumen) volumen 
                    FROM man, rm WHERE TRUE 
                    AND man.posicion = rm.posicion AND man.activo = 'Si'
                    AND rm.corte = $Corte AND rm.tipo_venta in ('J','A')
                    GROUP BY rm.posicion,rm.manguera
            ) rmj ON m.posicion = rmj.posicion AND m.manguera = rmj.manguera LEFT JOIN
            (
                    SELECT 
                    man.isla_pos,
                    ctd.posicion,
                    ROUND(ctd.ivolumen1,3) i_vol1,
                    ROUND(ctd.fvolumen1,3) f_vol1,
                    ROUND(IF(ctd.fvolumen1 - ctd.ivolumen1 < 0, (ctd.fvolumen1 - ctd.ivolumen1)  + getLimiteTotalizadores('V',man.marca,man.hw_version),ctd.fvolumen1 - ctd.ivolumen1),3) vol1,
                    ROUND(ctd.ivolumen2,3) i_vol2,
                    ROUND(ctd.fvolumen2,3) f_vol2,
                    ROUND(IF(ctd.fvolumen2 - ctd.ivolumen2 < 0, (ctd.fvolumen2 - ctd.ivolumen2)  +  getLimiteTotalizadores('V',man.marca,man.hw_version),ctd.fvolumen2 - ctd.ivolumen2),3) vol2,
                    ROUND(ctd.ivolumen3,3) i_vol3,
                    ROUND(ctd.fvolumen3,3) f_vol3,
                    ROUND(IF(ctd.fvolumen3 - ctd.ivolumen3 < 0, (ctd.fvolumen3 - ctd.ivolumen3)  +  getLimiteTotalizadores('V',man.marca,man.hw_version),ctd.fvolumen3 - ctd.ivolumen3),3) vol3,
                    ROUND(ctd.imonto1,2) i_mon1,
                    ROUND(ctd.fmonto1,2) f_mon1,
                    ROUND(IF(ctd.fmonto1 - ctd.imonto1 < 0, (ctd.fmonto1 - ctd.imonto1) +  getLimiteTotalizadores('I',man.marca,man.hw_version),ctd.fmonto1 - ctd.imonto1),2) mon1,
                    ROUND(ctd.imonto2,2) i_mon2,
                    ROUND(ctd.fmonto2,2) f_mon2,
                    ROUND(IF(ctd.fmonto2 - ctd.imonto2 < 0, (ctd.fmonto2 - ctd.imonto2) +  getLimiteTotalizadores('I',man.marca,man.hw_version),ctd.fmonto2 - ctd.imonto2),2) mon2,
                    ROUND(ctd.imonto3,2) i_mon3,
                    ROUND(ctd.fmonto3,2) f_mon3,
                    ROUND(IF(ctd.fmonto3 - ctd.imonto3 < 0, (ctd.fmonto3 - ctd.imonto3) +  getLimiteTotalizadores('I',man.marca,man.hw_version),ctd.fmonto3 - ctd.imonto3),2) mon3
                    FROM ct, ctd, man
                    WHERE TRUE
                    AND ct.id = ctd.id 
                    AND ctd.posicion = man.posicion
                    AND ct.id = $Corte
            ) ct ON m.posicion = ct.posicion
            WHERE 1 =1 
            AND man.posicion = m.posicion AND man.activo = 'Si'
            AND com.clavei = m.producto AND m.activo = 'Si'
            ORDER BY man.isla_pos,m.posicion,m.manguera;";

$selectVentaByCorteAbiertoT = "
            SELECT producto,SUM(ventas_d) ventas_d,SUM(pesos_d) pesos_d,SUM(volumen_d) volumen_d,
            SUM(volumen_j) volumen_j,SUM(pesos_j) pesos_j
            , SUM(volumen_n) volumen_n,SUM(pesos_n) pesos_n,SUM(descuento) descuento_n
            FROM corte_tmp 
            GROUP BY producto 
            ORDER BY producto DESC";

$selectVentaByCorteAbiertoDetalle = "
            SELECT man.isla_pos, m.dispensario,m.posicion,
            (IFNULL(cre.ventas,0) + IFNULL(tar.ventas,0) + IFNULL(cnt.ventas,0) + IFNULL(val.ventas,0) + IFNULL(mon.ventas,0)) ventas,
            (IFNULL(cre.volumen,0) + IFNULL(tar.volumen,0) + IFNULL(cnt.volumen,0) + IFNULL(val.volumen,0) + IFNULL(mon.volumen,0)) volumen,
            (IFNULL(cnt.pesos,0) - IFNULL(tar_ct.pesos,0) + IFNULL(cre.efectivo,0) + IFNULL(tar.efectivo,0) + IFNULL(val.efectivo,0) + IFNULL(mon.efectivo,0)) efectivo,
            (IFNULL(cre.pesos,0)) credito,
            IFNULL(val.pesos,0) vales,
            (IFNULL(tar.pesos,0) + IFNULL(tar_ct.pesos,0)) tarjeta,
            IFNULL(mon.pesos,0) monederos,
            (IFNULL(cre.pesos,0) + IFNULL(tar.pesos,0) + IFNULL(val.pesos,0) + IFNULL(mon.pesos,0) + IFNULL(cnt.pesos,0) + IFNULL(cre.efectivo,0) + IFNULL(tar.efectivo,0) + IFNULL(val.efectivo,0) + IFNULL(mon.efectivo,0)) venta,
            IFNULL(cnt_a.pesos,0) efectivo_a,
            (IFNULL(cre_a.pesos,0) + IFNULL(pre_a.pesos,0) + IFNULL(tar_a.pesos,0)) credito_a,
            IFNULL(tar_a.pesos,0) tarjeta_a,
            (IFNULL(cre_a.pesos,0) + IFNULL(pre_a.pesos,0) + IFNULL(tar_a.pesos,0) + IFNULL(cnt_a.pesos,0)) venta_a,
            IFNULL(cre.descuento,0)+IFNULL(val.descuento,0)+IFNULL(tar.descuento,0)+IFNULL(cnt.descuento,0) descuento
            FROM man, man_pro m 
            LEFT JOIN 
            (
                    SELECT rm.posicion,COUNT(*) ventas,
                    SUM( rm.pagoreal ) pesos,
                    SUM( rm.descuento ) descuento,
                    SUM( rm.volumen ) volumen,
                    SUM( CASE WHEN ABS( rm.pesos - rm.pagoreal ) > 0.01 THEN rm.pesos - rm.pagoreal ELSE 0 END ) efectivo
                    FROM man, rm, cli 
                    WHERE TRUE 
                    AND man.posicion = rm.posicion AND man.activo = 'Si'
                    AND rm.cliente = cli.id AND rm.corte = $Corte AND rm.tipo_venta = 'D' AND cli.tipodepago IN ('Credito' , 'Prepago', 'AutoConsumo', 'Cortesía')
                    GROUP BY rm.posicion
            ) cre ON m.posicion = cre.posicion 
            LEFT JOIN 
            (
                    SELECT rm.posicion,COUNT(*) ventas,
                    SUM( rm.pagoreal ) pesos,
                    SUM( rm.descuento ) descuento,
                    SUM( rm.volumen ) volumen,
                    SUM( CASE WHEN ABS( rm.pesos - rm.pagoreal ) > 0.01 THEN rm.pesos - rm.pagoreal ELSE 0 END ) efectivo
                    FROM man, rm, cli 
                    WHERE TRUE 
                    AND man.posicion = rm.posicion AND man.activo = 'Si'
                    AND rm.cliente = cli.id AND rm.corte = $Corte AND rm.tipo_venta = 'D' AND cli.tipodepago IN ('Vales')
                    GROUP BY rm.posicion
            ) val ON m.posicion = val.posicion 
            LEFT JOIN 
            (
                    SELECT rm.posicion,COUNT(*) ventas,
                    SUM( rm.pagoreal ) pesos,
                    SUM( rm.descuento ) descuento,
                    SUM( rm.volumen ) volumen,
                    SUM( CASE WHEN ABS( rm.pesos - rm.pagoreal ) > 0.01 THEN rm.pesos - rm.pagoreal ELSE 0 END ) efectivo
                    FROM man, rm, cli 
                    WHERE TRUE 
                    AND man.posicion = rm.posicion AND man.activo = 'Si'
                    AND rm.cliente = cli.id AND rm.corte = $Corte AND rm.tipo_venta = 'D' AND cli.tipodepago IN ('Tarjeta')
                    GROUP BY rm.posicion
            ) tar ON m.posicion = tar.posicion 
             LEFT JOIN 
            (
                    SELECT rm.posicion,COUNT(*) ventas,
                    SUM( rm.pagoreal ) pesos,
                    SUM( rm.descuento ) descuento,
                    SUM( rm.volumen ) volumen,
                    SUM( CASE WHEN ABS( rm.pesos - rm.pagoreal ) > 0.01 THEN rm.pesos - rm.pagoreal ELSE 0 END ) efectivo
                    FROM man, rm, cli 
                    WHERE TRUE 
                    AND man.posicion = rm.posicion AND man.activo = 'Si'
                    AND rm.cliente = cli.id AND rm.corte = $Corte AND rm.tipo_venta = 'D' AND cli.tipodepago IN ('Monedero','Reembolso')
                    GROUP BY rm.posicion
            ) mon ON m.posicion = mon.posicion 
            LEFT JOIN 
            (
                    SELECT rm.posicion,COUNT(*) ventas,
                    SUM( rm.pesos ) pesos,
                    SUM( rm.descuento ) descuento,
                    SUM( rm.volumen ) volumen 
                    FROM man, rm, cli 
                    WHERE TRUE 
                    AND man.posicion = rm.posicion AND man.activo = 'Si'
                    AND rm.cliente = cli.id AND rm.corte = $Corte AND rm.tipo_venta = 'D' AND cli.tipodepago IN ('Contado','Puntos')
                    GROUP BY rm.posicion
            ) cnt ON m.posicion = cnt.posicion 
            LEFT JOIN
            (
                    SELECT vt.posicion,ROUND(SUM(vt.total),2) pesos 
                    FROM man, vtaditivos vt, cli 
                    WHERE TRUE 
                    AND man.posicion = vt.posicion AND man.activo = 'Si'
                    AND vt.cliente = cli.id AND vt.corte = $Corte AND cli.tipodepago IN ('Credito') 
                    AND vt.tm = 'C'
                    AND vt.total > 0
                    GROUP BY vt.posicion
            ) cre_a ON m.posicion = cre_a.posicion
            LEFT JOIN 
            (
                    SELECT vt.posicion,ROUND(SUM(vt.total),2) pesos 
                    FROM man, vtaditivos vt, cli 
                    WHERE TRUE 
                    AND man.posicion = vt.posicion AND man.activo = 'Si'
                    AND vt.cliente = cli.id AND vt.corte = $Corte AND cli.tipodepago IN ('Prepago') 
                    AND vt.tm = 'C'
                    AND vt.total > 0
                    GROUP BY vt.posicion
            ) pre_a ON m.posicion = pre_a.posicion
            LEFT JOIN 
            (
                    SELECT vt.posicion,ROUND(SUM(vt.total),2) pesos 
                    FROM man, vtaditivos vt, cli 
                    WHERE TRUE 
                    AND man.posicion = vt.posicion AND man.activo = 'Si'
                    AND vt.cliente = cli.id AND vt.corte = $Corte AND cli.tipodepago IN ('Tarjeta','Vales','Monedero','Reembolso') 
                    AND vt.tm = 'C'
                    AND vt.total > 0
                    GROUP BY vt.posicion
            ) tar_a ON m.posicion = tar_a.posicion
            LEFT JOIN 
            (
                    SELECT vt.posicion,ROUND(SUM(vt.total),2) pesos 
                    FROM man, vtaditivos vt, cli 
                    WHERE TRUE 
                    AND man.posicion = vt.posicion AND man.activo = 'Si'
                    AND vt.cliente = cli.id AND vt.corte = $Corte AND cli.tipodepago IN ('Contado','Puntos') 
                    AND vt.tm = 'C'
                    AND vt.total > 0
                    GROUP BY vt.posicion
            ) cnt_a ON m.posicion = cnt_a.posicion
            LEFT JOIN 
            (
                    SELECT rm.posicion,ROUND(SUM(c.importe),2) pesos 
                    FROM cttarjetas c,(
                        SELECT posicion,vendedor FROM rm 
                        WHERE corte = $Corte 
                        GROUP BY vendedor ORDER BY posicion ASC
                    ) rm 
                    WHERE c.vendedor = rm.vendedor AND c.id = $Corte
                    GROUP BY c.vendedor ORDER BY rm.posicion ASC
            ) tar_ct ON m.posicion = tar_ct.posicion
            WHERE 1 = 1
            AND man.posicion = m.posicion AND man.activo = 'Si'
            AND m.activo = 'Si' 
            GROUP BY posicion;
            ";

/* Consulta para diferencia en cortes */

// $selectVentaCorteDiferencia = "
//             SELECT com.descripcion producto, SUM( rm.volumen ) volumen , 
//             SUM( rm.volumenp ) volumenp ,SUM( round(rm.pesos,3) ) pesos , 
//             SUM( round(rm.pesosp,3) ) pesosp , rm.precio
//             FROM man, rm, com
//             WHERE 1 = 1 
//             AND man.posicion = rm.posicion AND man.activo = 'Si'
//             AND rm.producto = com.clavei AND rm.corte = $Corte AND rm.tipo_venta='D' AND com.activo = 'Si'
//             GROUP BY com.descripcion DESC";
$selectVentaCorteDiferencia = "SELECT 
com.descripcion producto,
IFNULL(SUM(CASE WHEN rm.tipo_venta = 'D' THEN rm.volumen END),0) volumenD,
IFNULL(SUM(ROUND(CASE WHEN rm.tipo_venta = 'D' THEN rm.pesos END, 3)),0) pesosD,
IFNULL(SUM(CASE WHEN rm.tipo_venta = 'N' THEN rm.volumen END), 0) volumenN,
IFNULL(SUM(ROUND(CASE WHEN rm.tipo_venta = 'N' THEN rm.pesos END, 3)), 0) pesosN,
IFNULL(SUM(CASE WHEN rm.tipo_venta = 'D' THEN rm.volumenp END),0) volumenpD,
IFNULL(SUM(ROUND(CASE WHEN rm.tipo_venta = 'D' THEN rm.pesosp END, 3)),0) pesospD,
IFNULL(SUM(CASE WHEN rm.tipo_venta = 'N' THEN rm.volumenp END), 0) volumenpN,
IFNULL(SUM(ROUND(CASE WHEN rm.tipo_venta = 'N' THEN rm.pesosp END, 3)), 0) pesospN,
IFNULL(SUM( rm.volumen ), 0) volumenT,
IFNULL(SUM(ROUND( rm.pesos , 3)), 0) pesosT,
IFNULL(SUM( rm.volumenp ), 0) volumenpT,
IFNULL(SUM(ROUND( rm.pesosp , 3)), 0) pesospT,
rm.precio
FROM
rm
    INNER JOIN
com ON com.activo = 'Si'
    AND rm.producto = com.clavei
    INNER JOIN
man ON man.activo = 'Si'
    AND man.posicion = rm.posicion
WHERE
1 = 1 AND rm.corte = " . $Corte . " AND rm.tipo_venta IN ('D' , 'N')
GROUP BY com.descripcion DESC";

/* Consulta para exportar CFDIS */

$selectCFDIS_Exportar = "SELECT f.id_fc_fk folio,f.pdf_format pdf,f.uuid,f.cfdi_xml xml,f.emisor rfc, f.tabla ,f.version
            FROM facturas f,fc
            WHERE 
            f.id_fc_fk = fc.id AND f.uuid = fc.uuid 
            AND DATE(fc.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')";

if (!empty($Cliente)) {
    $selectCFDIS_Exportar .= " AND fc.cliente = $Cliente";
}


/* Consulta para Balance de productos */

/* $selectBalance = "
  SELECT SUB.*";

  if ($busca == 1) {
  $selectBalance .= ",
  IFNULL(SUM(cargas.aumento), 0) compras, IFNULL(GROUP_CONCAT(cargas.id),0) idsCargas ";
  } else {
  $selectBalance .= ",
  IFNULL(SUM(me.volumenfac * 1000), 0) compras, 0 idsCargas";
  }
  $selectBalance .= "
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
  $selectBalance .= "
  LEFT JOIN me ON cargas.id = me.carga ";
  }

  $selectBalance .= "
  WHERE 1 = 1
  GROUP BY SUB.clavei,SUB.fecha
  ORDER BY SUB.clavei DESC,SUB.fecha ASC;"; */

$selectBalanceCreate = "CALL omicrom.balance_productos('$FechaI', '$FechaF');";
$Tot = utils\IConnection::execSql("SELECT valor FROM variables_corporativo WHERE llave='OrdenReportes'");
$FiltroSql = $Tot["valor"] != "" ? "ORDER BY clave ASC" : "";
$selectBalance = "SELECT b.*,getUMedida(com.cve_producto_sat,com.cve_sub_producto_sat) um FROM balance_productos b inner join com on b.clave = com.clave " . $FiltroSql;

/* Consultas para reporte de 3ra impresora */

if ($Detallado === "Si") {
    $selectInv3ra = "
            SELECT inv.id producto,inv.clave_producto idClave, man.isla_pos, inv.descripcion
            FROM inv, man 
            WHERE 1=1 AND inv.rubro = 'Aceites' AND inv.activo = 'Si'
                ";

    if (!empty($IslaPosicion) && $IslaPosicion !== "*") {
        $selectInv3ra .= " AND man.isla_pos = $IslaPosicion";
    }

    $selectInv3ra .= " 
            GROUP BY inv.id,man.isla_pos
            ORDER BY man.isla_pos,inv.id ASC;";

    $selectInv3rad = "
            SELECT inv.id producto, man.isla_pos, inv.descripcion,
            IFNULL(ini.cantidad,0) inicial,
            IFNULL(vt.cantidad,0) ventas,IFNULL(ets.cantidad,0) entradas,
            (IFNULL(ini.cantidad,0) - IFNULL(vt.cantidad,0) + IFNULL(ets.cantidad,0)) total
            FROM man
            LEFT JOIN inv ON TRUE AND inv.activo = 'Si' AND inv.rubro = 'Aceites'
            LEFT JOIN (
                    SELECT vt.clave producto, man.isla_pos, IFNULL(SUM(IF(vt.tm = 'C', -vt.cantidad, vt.cantidad)),0) cantidad 
                    FROM vtaditivos vt,man 
                    WHERE 1= 1
                AND vt.posicion = man.posicion
                    AND vt.corte < $Corte
                    AND vt.posicion > 0 AND vt.cantidad > 0
                    GROUP BY vt.clave,man.isla_pos
            ) ini ON inv.id = ini.producto AND ini.isla_pos = man.isla_pos
            LEFT JOIN (
                    SELECT vt.clave producto, man.isla_pos, IFNULL(SUM(vt.cantidad),0) cantidad 
                    FROM vtaditivos vt,man 
                    WHERE 1= 1
                AND vt.posicion = man.posicion
                    AND vt.corte = $Corte
                    AND vt.posicion > 0 AND vt.cantidad > 0
                    AND vt.tm = 'C'
                    GROUP BY vt.clave,man.isla_pos
            ) vt ON inv.id = vt.producto AND vt.isla_pos = man.isla_pos
            LEFT JOIN (
                    SELECT vt.clave producto, man.isla_pos, IFNULL(SUM(vt.cantidad),0) cantidad 
                    FROM vtaditivos vt ,man 
                    WHERE 1= 1
                AND vt.posicion = man.posicion
                    AND vt.corte = $Corte
                    AND vt.posicion > 0 AND vt.cantidad > 0
                    AND vt.tm = 'H'
                    GROUP BY vt.clave,man.isla_pos
            ) ets ON inv.id = ets.producto AND ets.isla_pos = man.isla_pos
            WHERE 1= 1
            AND man.activo = 'Si'
                ";
    if (!empty($IslaPosicion) && $IslaPosicion !== "*") {
        $selectInv3rad .= " AND man.isla_pos = $IslaPosicion";
    }

    $selectInv3rad .= " 
            GROUP BY inv.id,man.isla_pos
            ORDER BY inv.id,man.isla_pos ASC;";
} else {
    $selectInv3ra = " 
            SELECT inv.id producto,inv.clave_producto idClave, inv.descripcion, IFNULL(ini.cantidad,0) inicial,
            IFNULL(vt.cantidad,0) ventas,IFNULL(ets.cantidad,0) entradas,
            (IFNULL(ini.cantidad,0) - IFNULL(vt.cantidad,0) + IFNULL(ets.cantidad,0)) total
            FROM inv
            LEFT JOIN (
                    SELECT vt.clave producto, IFNULL(SUM(IF(vt.tm = 'C', -vt.cantidad, vt.cantidad)),0) cantidad 
                    FROM vtaditivos vt 
                    WHERE 1= 1
                    AND vt.corte < $Corte
                    AND vt.posicion > 0 AND vt.cantidad > 0
                    GROUP BY vt.clave
            ) ini ON inv.id = ini.producto
            LEFT JOIN (
                    SELECT vt.clave producto, IFNULL(SUM(vt.cantidad),0) cantidad 
                    FROM vtaditivos vt 
                    WHERE 1= 1
                    AND vt.corte = $Corte
                    AND vt.posicion > 0 AND vt.cantidad > 0
                    AND vt.tm = 'C'
                    GROUP BY vt.clave
            ) vt ON inv.id = vt.producto
            LEFT JOIN (
                    SELECT vt.clave producto, IFNULL(SUM(vt.cantidad),0) cantidad 
                    FROM vtaditivos vt 
                    WHERE 1= 1
                    AND vt.corte = $Corte
                    AND vt.posicion > 0 AND vt.cantidad > 0
                    AND vt.tm = 'H'
                    GROUP BY vt.clave
            ) ets ON inv.id = ets.producto
            WHERE 1= 1
            AND inv.activo = 'Si' AND inv.rubro = 'Aceites'
            GROUP BY inv.clave_producto
            ORDER BY inv.clave_producto ASC;    
            ";
}

/* Consultas para venta de aceites por despachador */

if ($Desglose === "Dia"):

    if ($Detallado === "No"):
        $selectVentaAceitesDespachador = "
            SELECT vt.vendedor, ven.nombre, inv.categoria, SUM(vt.cantidad) cantidad, SUM(vt.total) importe
            FROM  man, vtaditivos vt, inv, ven
            WHERE 1 = 1
            AND man.posicion = vt.posicion AND vt.vendedor = ven.id
            AND vt.clave = inv.id
            AND DATE( vt.fecha ) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
            AND vt.tm = 'C'
            AND vt.cantidad > 0
            GROUP BY vt.vendedor, inv.categoria";
        $selectVentaGasolinaDespachador = "select COUNT(1) Cnt,SUM(importe) importe, SUM(volumen) volumen, ven.nombre from rm left join ven on ven.id=rm.vendedor where "
                . "DATE(inicio_venta) BETWEEN DATE('$FechaI') AND DATE('$FechaF') group by vendedor;";

    else :
        $selectVentaAceitesDespachador = "
            SELECT DATE(vt.fecha) fecha, man.isla_pos, vt.vendedor, ven.nombre,inv.clave_producto , vt.clave producto, vt.descripcion, inv.categoria, SUM(vt.cantidad) cantidad, SUM(vt.total) importe
            FROM  man, vtaditivos vt, ven, inv
            WHERE 1 = 1
            AND man.posicion = vt.posicion AND vt.vendedor = ven.id AND vt.clave = inv.id
            AND DATE( vt.fecha ) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
            AND vt.tm = 'C'
            AND vt.cantidad > 0
            GROUP BY vt.vendedor, DATE(vt.fecha), man.isla_pos, vt.clave 
            ORDER BY vt.vendedor, DATE(vt.fecha), man.isla_pos, vt.clave, inv.categoria";
        $selectVentaGasolinaDespachador = "select 1 Cnt,importe, volumen, ven.nombre from rm left join ven on ven.id=rm.vendedor where "
                . "DATE(inicio_venta) BETWEEN DATE('$FechaI') AND DATE('$FechaF') ORDER BY ven.id;";
    endif;

else :
    if ($Detallado === "No"):

        $selectVentaAceitesDespachador = "
            SELECT vt.vendedor, ven.nombre, inv.categoria, SUM(vt.cantidad) cantidad, SUM(vt.total) importe
            FROM  man, vtaditivos vt, inv, ven, ct
            WHERE 1 = 1
            AND man.posicion = vt.posicion AND vt.vendedor = ven.id AND vt.corte = ct.id
            AND vt.clave = inv.id
            AND DATE( ct.fecha ) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
            AND vt.tm = 'C'
            AND vt.cantidad > 0
            GROUP BY vt.vendedor, inv.categoria";

        $selectVentaGasolinaDespachador = "select  COUNT(1) Cnt,SUM(importe) importe, SUM(volumen) volumen, ven.nombre from rm left join ct on ct.id=rm.corte left join ven on ven.id=rm.vendedor 
where DATE( ct.fecha ) BETWEEN DATE('$FechaI') AND DATE('$FechaF') group by vendedor;";

    else :
        $selectVentaAceitesDespachador = "
            SELECT DATE(vt.fecha) fecha, man.isla_pos, vt.vendedor, ven.nombre,inv.clave_producto, vt.clave producto, vt.descripcion, inv.categoria, SUM(vt.cantidad) cantidad, SUM(vt.total) importe
            FROM  man, vtaditivos vt, ven, ct, inv
            WHERE 1 = 1
            AND man.posicion = vt.posicion AND vt.vendedor = ven.id AND vt.corte = ct.id AND vt.clave = inv.id
            AND DATE( ct.fecha ) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
            AND vt.tm = 'C'
            AND vt.cantidad > 0
            GROUP BY vt.vendedor, DATE(vt.fecha), man.isla_pos, vt.clave 
            ORDER BY vt.vendedor, DATE(vt.fecha), man.isla_pos, vt.clave, inv.categoria";
        $selectVentaGasolinaDespachador = "select  1 Cnt,importe, volumen, ven.nombre  from rm left join ct on ct.id=rm.corte left join ven on ven.id=rm.vendedor 
where DATE( ct.fecha ) BETWEEN DATE('$FechaI') AND DATE('$FechaF') order by vendedor;";
    endif;

endif;

/* Consultas para reporte generencial */

/* $selectGerencia1 = "
  SELECT tur.turno, com.clavei,
  ROUND(IFNULL(SUM(rm.volumen), 0), 3) volumen,
  ROUND(IFNULL(SUM(rm.importe), 0), 2) importe
  FROM tur
  LEFt JOIN com ON TRUE AND com.activo = 'Si'
  LEFT JOIN (
  SELECT ct.turno,rm.producto,
  SUM(rm.pesosp) importe,
  SUM(rm.volumenp) volumen
  FROM rm,ct
  WHERE rm.corte = ct.id AND DATE(ct.fecha) = DATE('$Fecha') AND rm.tipo_venta = 'D'
  GROUP BY rm.corte,rm.producto
  ) rm ON rm.producto = com.clavei AND rm.turno = tur.turno
  WHERE 1 = 1
  AND tur.activo = 'Si'
  GROUP BY com.clavei, tur.turno
  ORDER BY com.clavei DESC, tur.turno ASC;"; */

$selectGerencia1 = "
 SELECT tur.turno, com.clavei,
 ROUND(IFNULL(SUM(rm.volumen), 0), 3) volumen, 
 ROUND(IFNULL(SUM(rm.importe), 0), 2) importe,
 ROUND(IFNULL(SUM(rm.importeS), 0), 2) importeS,
 ROUND(IFNULL(SUM(rm.iva), 0), 2) iva,
 ROUND(IFNULL(SUM(rm.ieps), 0), 2) ieps
 FROM tur
 LEFt JOIN com ON TRUE AND com.activo = 'Si'
 LEFT JOIN (
        SELECT ct.turno,rm.producto,
         round(sum(rm.pesosp),2) importe,
         round(sum(rm.volumenp),2) volumen,
         round(sum((rm.pesosp / (1 + rm.iva))-(rm.ieps * rm.volumenp)),2) importeS,
         round(sum((((rm.pesosp / (1 + rm.iva))) *  rm.iva)),2) iva,
         round(sum((rm.volumenp * rm.ieps)),2) ieps
         FROM rm,ct 
         WHERE rm.corte = ct.id AND DATE(ct.fecha) = DATE('$Fecha') AND rm.tipo_venta = 'D'
         GROUP BY rm.corte,rm.producto
 ) rm ON rm.producto = com.clavei AND rm.turno = tur.turno
 WHERE 1 = 1 
 AND tur.activo = 'Si' 
 GROUP BY com.clavei, tur.turno 
 ORDER BY com.clavei DESC, tur.turno ASC; 
            ";

$selectGerencia2 = " SELECT tur.turno, com.clavei, com.descripcion, tan.volumen_actual inicial,
            sub.fecha_hora_s, sub.volumen_actual volumen,
            IFNULL(sub2.volumen, 0) compras, 0.00 pemex,
            IFNULL(rm.vol, 0) vol, IFNULL(rm.volp, 0) volp,IFNULL(rm.merma, 0) merma
            FROM tur
            LEFt JOIN com ON TRUE AND com.activo = 'Si'
            LEFT JOIN (
                    SELECT t.producto,t.fecha_hora_s,t.volumen_actual 
                    FROM tanques_h t 
                    WHERE DATE(t.fecha_hora_s) = DATE('$Fecha')
                GROUP BY t.producto
            ) tan ON com.descripcion = tan.producto
            LEFT JOIN (
                    SELECT ct.turno,ct.id corte,t.producto,t.fecha_hora_s,t.volumen_actual 
                    FROM ct,tanques_h t
                    WHERE 1 = 1 
                    AND DATE(ct.fecha) = DATE('$Fecha') 
                    AND t.fecha_hora_s BETWEEN ct.fecha AND ct.fechaf
                    GROUP BY ct.id,t.producto 
            ) sub ON sub.producto = com.descripcion AND sub.turno = tur.turno
            LEFT JOIN (
                    SELECT c.clave_producto clave, c.producto, SUM(c.aumento) volumen
                    FROM cargas c  
                    WHERE 1 = 1 AND DATE(c.fecha_insercion) = DATE('$Fecha') AND c.tipo = 0
                GROUP BY c.producto
            ) sub2 ON com.clave = sub2.clave
            LEFT JOIN (
                    SELECT ct.id corte,ct.turno,rm.producto,
                    ROUND(SUM(rm.volumen),3) vol,ROUND(SUM(rm.volumenp),3) volp,
                    ROUND(SUM(rm.volumen - rm.volumenp),3) merma
                    FROM ct,rm
                    WHERE rm.corte = ct.id AND DATE(ct.fecha) = DATE('$Fecha')
                    GROUP BY ct.id, rm.producto
            ) rm ON com.clavei = rm.producto AND tur.turno = rm.turno
            WHERE 1 = 1 
            AND tur.activo = 'Si';";

$selectGerencia3 = "SELECT ct.turno,IFNULL(A.imp,0) credito,IFNULL(B.imp,0) prepago,IFNULL(C.total,0) lubricantes,IFNULL(D.imp,0) jarreos,0.00 internos
            FROM ct LEFT JOIN
            (
                    SELECT ct.id corte,ct.turno,cli.tipodepago,ROUND(SUM(rm.pesos),2) imp,ROUND(SUM(rm.volumen),2) vol
                    FROM ct,rm,cli
                    WHERE rm.corte = ct.id AND rm.cliente = cli.id AND DATE(ct.fecha) = DATE('$Fecha') AND rm.cliente <> 0 AND rm.tipo_venta='D' AND cli.tipodepago = 'Credito'
                    GROUP BY rm.corte,cli.tipodepago
            ) AS A ON ct.id = A.corte LEFT JOIN
            (
                    SELECT ct.id corte,ct.turno,cli.tipodepago,ROUND(SUM(rm.pesos),2) imp,ROUND(SUM(rm.volumen),2) vol
                    FROM ct,rm,cli
                    WHERE rm.corte = ct.id AND rm.cliente = cli.id AND DATE(ct.fecha) = DATE('$Fecha') AND rm.cliente <> 0 AND rm.tipo_venta='D' AND cli.tipodepago = 'Prepago'
                    GROUP BY rm.corte,cli.tipodepago
            ) AS B ON ct.id = B.corte LEFT JOIN
            (
                    SELECT ct.id corte,ct.turno,SUM(v.cantidad) cnt,SUM(v.total) total FROM ct,vtaditivos v 
                    WHERE ct.id = v.corte AND DATE(ct.fecha) = DATE('$Fecha') AND v.tm = 'C'
                    GROUP BY ct.id
            ) AS C ON ct.id = C.corte LEFT JOIN
            (
                    SELECT ct.id corte,ct.turno,ROUND(SUM(rm.pesos),2) imp,ROUND(SUM(rm.volumen),2) vol
                    FROM ct,rm
                    WHERE rm.corte = ct.id  AND DATE(ct.fecha) = DATE('$Fecha') AND rm.tipo_venta = 'J'
                    GROUP BY rm.corte
            ) AS D ON ct.id = D.corte 
            WHERE DATE(ct.fecha) = DATE('$Fecha')
            GROUP BY ct.turno;";

$selectGerencia4 = "SELECT 
            IFNULL(SUM(A.imp),0) producto1,
            IFNULL(SUM(B.imp),0) producto2,
            IFNULL(SUM(C.imp),0) producto3,
            (IFNULL(SUM(A.imp),0)+
             IFNULL(SUM(B.imp),0)+
             IFNULL(SUM(C.imp),0)) total
            FROM ct LEFT JOIN
            (
                            SELECT rm.corte,ROUND(SUM((rm.pesos - rm.pesosp)),2) imp
                            FROM ct,rm,com
                            WHERE rm.corte = ct.id AND rm.producto = com.clavei AND com.id = 1
                    AND DATE(ct.fecha) = DATE('$Fecha') AND rm.tipo_venta = 'D'
                    GROUP BY rm.corte
            ) AS A ON ct.id = A.corte LEFT JOIN
            (
                            SELECT rm.corte,ROUND(SUM((rm.pesos - rm.pesosp)),2) imp
                            FROM ct,rm,com
                            WHERE rm.corte = ct.id  AND rm.producto = com.clavei AND com.id = 2
                    AND DATE(ct.fecha) = DATE('$Fecha') AND rm.tipo_venta = 'D'
                    GROUP BY rm.corte
            ) AS B ON ct.id = B.corte LEFT JOIN
            (
                            SELECT rm.corte,ROUND(SUM((rm.pesos - rm.pesosp)),2) imp
                            FROM ct,rm,com
                            WHERE rm.corte = ct.id  AND rm.producto = com.clavei AND com.id = 3
                    AND DATE(ct.fecha) = DATE('$Fecha') AND rm.tipo_venta = 'D'
                    GROUP BY rm.corte
            ) AS C ON ct.id = C.corte
            WHERE DATE(ct.fecha) = DATE('$Fecha');";

$selectGerencia5 = "SELECT c.fecha_insercion,IFNULL(SUM(c.aumento),0) aumento 
            FROM ct 
            LEFT JOIN cargas c ON c.fecha_insercion BETWEEN ct.fecha AND ct.fechaf AND c.tipo = 0
            WHERE DATE(ct.fecha) = DATE('$Fecha');";

$selectGerencia6 = "SELECT ct.id,ct.turno,CONCAT('DEPOSITO - TURNO ',ct.turno) concepto,ROUND(SUM(egr.importe),2) importe 
            FROM ct,egr
            WHERE ct.id = egr.corte AND DATE(ct.fecha) = DATE('$Fecha') 
            GROUP BY ct.id ORDER BY ct.turno;";

$selectGerencia7 = "
            SELECT concepto,SUM(importe) importe 
            FROM(
                SELECT 'VENTA DE COMBUSTIBLE' concepto,ROUND(SUM(rm.pesosp),2) importe
                FROM rm,ct 
                WHERE rm.corte = ct.id AND DATE(ct.fecha) = DATE('$Fecha') AND rm.tipo_venta = 'D'
                UNION
                SELECT 'VENTA DE LUBRICANTES' concepto,ROUND(SUM(v.total),2) importe
                FROM vtaditivos v,ct 
                WHERE v.corte = ct.id AND DATE(ct.fecha) = DATE('$Fecha') AND v.tm = 'C'
                UNION
                SELECT CONCAT('VENTA ',IF(cli.tipodepago = 'Tarjeta','CON ','DE '), 
                UPPER(cli.tipodepago)) concepto,
                ROUND(SUM(rm.pagoreal),2) * -1 importe
                FROM rm,ct,cli
                WHERE 1 = 1 
                AND rm.corte = ct.id AND rm.cliente = cli.id AND DATE(ct.fecha) = DATE('$Fecha') 
                AND cli.tipodepago NOT LIKE '%Contado%' AND rm.tipo_venta = 'D'
                GROUP BY cli.tipodepago
                UNION
                SELECT 'VENTA CON TARJETA' concepto,ROUND(SUM(cttarjetas.importe),2) * -1 importe
                FROM cttarjetas,ct
                WHERE cttarjetas.id = ct.id AND  DATE(ct.fecha) = DATE('$Fecha')
                UNION
                SELECT CONCAT('VENTA ',IF(cli.tipodepago = 'Tarjeta','CON ','DE '),UPPER(cli.tipodepago)) concepto,
                ROUND(SUM(vt.total),2) * -1 importe
                FROM vtaditivos vt,ct,cli
                WHERE vt.corte = ct.id AND vt.cliente= cli.id AND  DATE(ct.fecha) = DATE('$Fecha') 
                AND cli.tipodepago NOT REGEXP 'Contado|Puntos' AND vt.tm = 'C'
                AND vt.total > 0 AND vt.cantidad > 0
                GROUP BY cli.tipodepago
            ) sub 
            GROUP BY concepto ORDER BY importe DESC";

$selectGerencia8 = "SELECT inv.descripcion aditivo,v.cantidad,ROUND(inv.costo,2) costo,ROUND(v.total,2) total
            FROM vtaditivos v,inv,ct 
            WHERE v.corte = ct.id AND v.clave = inv.id AND DATE(ct.fecha) = DATE('$Fecha') AND v.cantidad > 0 AND v.tm = 'C'";

$selectGerencia9 = "
            SELECT clave,banco,SUM(importe) importe 
            FROM (
                SELECT LPAD(rm.cliente,5,0) clave,UPPER(cli.alias) banco,ROUND(SUM(rm.pagoreal),2) importe 
                FROM rm,cli,ct
                WHERE 1 =1 
                AND rm.cliente=cli.id AND rm.corte = ct.id AND DATE(ct.fecha) = DATE('$Fecha') AND rm.tipo_venta = 'D'
                AND cli.tipodepago IN ('Tarjeta', 'Monedero','Reembolso')
                GROUP BY rm.cliente
                UNION 
                SELECT LPAD(cttarjetas.banco,5,0) clave,UPPER(cli.alias) banco,ROUND(SUM(cttarjetas.importe),2) importe 
                FROM cttarjetas,ct,cli
                WHERE cttarjetas.id = ct.id AND  DATE(ct.fecha) = DATE('$Fecha') AND cttarjetas.banco = cli.id
                GROUP BY cli.id 
                UNION 
                SELECT LPAD(vt.cliente,5,0) clave,UPPER(cli.alias) banco,ROUND(SUM(vt.total),2) importe 
                FROM vtaditivos vt,cli,ct
                WHERE 1 = 1 
                AND vt.cliente = cli.id AND vt.corte = ct.id AND DATE(ct.fecha) = DATE('$Fecha') 
                AND cli.tipodepago IN ('Tarjeta', 'Monedero','Reembolso') AND vt.tm = 'C' 
                AND vt.total > 0 AND vt.cantidad > 0
                GROUP BY vt.cliente
            ) sub 
            GROUP BY clave";

/* Consultas para reporte de pipas capturadas */

$selectPipas = "
            SELECT me.id entrada, me.foliofac factura,cre1.llave terminal, cre2.llave proveedorTransporte,
            me.fechae fechaEntrada, DATE( me.fecha ) fechaCaptura,carga, 
            com.descripcion producto, ROUND(SUM(IF(me.tipocomprobante ='I',me.volumenfac,-me.volumenfac)),3) cantidadDocumentada,
            me.incremento,cargas.tcaumento as bruto, ROUND((SUM(IF(me.tipocomprobante ='I',me.volumenfac,-me.volumenfac) * 1000) - me.incremento),3) diferencia,
            ROUND(SUM(IF(me.tipocomprobante ='I',me.importefac,-me.importefac)) - SUM(IF(me.tipocomprobante ='I',med.cantidad * med.precio,-(med.cantidad * med.precio))), 2) importe , 
            ROUND(SUM(IF(me.tipocomprobante ='I',med.cantidad * med.precio,-(med.cantidad * med.precio))),2) iva, ROUND(SUM(IF(me.tipocomprobante ='I',me.importefac,-me.importefac)),2) total, sum(volumen_devolucion) vd,
            dc.lote,dc.numeroFolio , 
            round(sum( IF(me.tipocomprobante ='I', (select sum(if(me.volumenfac < 0,0,(precio*cantidad))) preciounitario from med a where id = me.id and clave in (1,2,3,4,5,10)),
            - (select sum(if(me.volumenfac < 0,0,(precio*cantidad))) preciounitario from med a where id = me.id and clave in (1,2,3,4,5,10)))   ),2)  precioCompra 
            FROM com, me 
            LEFT JOIN med ON me.id = med.id
            LEFT JOIN cargas ON me.carga = cargas.id
            LEFT JOIN permisos_cre cre1 ON me.terminal = cre1.id
            LEFT JOIN permisos_cre cre2 ON me.proveedorTransporte = cre2.id
            LEFT JOIN dictamen dc ON me.id=dc.noCarga 
            WHERE DATE( me.fechae ) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
            AND me.producto = com.clave AND med.clave = 6 AND me.documento IN ('CP','RP') AND me.carga > 0
            GROUP BY me.carga
            ";

$selectTotales = "
            select * from (SELECT COUNT(distinct sub.id) cargas, sub.producto descripcion,
            SUM(sub.incremento) incremento,SUM(sub.neto) as neto, SUM(volumenIngreso) volumenfac,volumenEgreso volumenfacnc,importeIngreso,importeEgreso,
            ROUND(SUM(sub.importefac),2) importefac,  ROUND(SUM(sub.precioCompra),2) importeNet
            FROM (
                SELECT 
                    c.id, c.producto
                    , DATE(me.fechae) fecha
                    , c.aumento incremento
                    , c.tcaumento as neto
                    ,SUM(IF(me.tipocomprobante='I',me.volumenfac,0)) volumenIngreso
                    ,SUM(IF(me.tipocomprobante='E',me.volumenfac,0)) volumenEgreso
                    ,SUM(IF(me.tipocomprobante='I',me.importefac,0)) importeIngreso
                    ,SUM(IF(me.tipocomprobante='E',me.importefac,0)) importeEgreso
        , sum(if(me.volumenfac < 0,0 ,me.volumenfac)) volumenfac
        , sum(me.importefac) importefac
    , round(sum(IF(me.tipocomprobante='I',(select sum(if(me.volumenfac < 0,0,(precio*cantidad))) preciounitario from med a where id = me.id and clave in (1,2,3,4,5,10)),
    -(select sum(if(me.volumenfac < 0,0,(precio*cantidad))) preciounitario from med a where id = me.id and clave in (1,2,3,4,5,10)))),2)  precioCompra 
                FROM cargas c inner join me 
                WHERE 1 = 1
                AND c.id = me.carga 
                AND c.entrada > 0 AND c.tipo = 0 AND me.documento IN ('CP','RP')
                and date(c.fecha_insercion) BETWEEN DATE('$FechaI') AND DATE('$FechaF') GROUP BY c.id
            ) sub GROUP BY sub.producto
            ) ctr inner join (
            SELECT  producto,sum( IFNULL(CASE WHEN aumento_merma != 0 THEN aumento_merma ELSE c.tcaumento END ,0)) aumento_neto
                FROM cargas c WHERE c.tipo = 0 AND DATE(fecha_fin) between date('$FechaI') and date('$FechaF') 
                group by producto
            ) merma on ctr.descripcion = merma.producto";

/* -- Control despachos         */

if ($Desglose === "Cortes") {
    $selectDespachos = "SELECT  distinct 
                      fin_venta Fecha_Hora
                    , rm.id Despacho
                    , posicion Posicion
                    , rm.producto	Producto
                    , round(rm.importe/precio,3) Cantidad
                    , precio Precio
                    , round(((rm.precio-rm.ieps)/(1+rm.iva))*(rm.importe/precio),3) Subtotal
                    , round(((rm.precio-rm.ieps)/(1+rm.iva))*(rm.importe/precio)*(rm.iva),3) Iva
                    , round((rm.importe/precio)*(rm.ieps),3) Ieps
                    , round(rm.importe,3)	Importe
                    , round(((rm.precio-rm.ieps)/(1+rm.iva))*(rm.descuento/precio),3) descuentoSubtotal
                    , round(((rm.precio-rm.ieps)/(1+rm.iva))*(rm.descuento/precio)*(rm.iva),3) descuentoIva
                    , round((rm.descuento/precio)*(rm.ieps),3) descuentoIeps
                    , round(rm.descuento,3) Descuento
                    /*, round(rm.pagoreal,3)	PagoReal*/
                    , GROUP_CONCAT(impresiones_pos.id_ven)	Despachador -- 	Nota	
                    , concat(fc.serie,' | ',fc.folio) Factura	
                    , cli.nombre Cliente
                    , rm.codigo	Codigo
                    , cli.tipodepago 	Tipo
                    , rm.tipo_venta
                    , u.descripcion 	Vehículo
                    , u.placas	Placas
                    , rm.uuid
                    , if(rm.uuid != '-----',clif.rfc,'') rfc
                    , if(rm.uuid != '-----',clif.nombre,'') nombre
                    , rm.comprobante
                    , GROUP_CONCAT(ven.nombre) Imprime
                    from 
                    rm 
                    inner join cli
                    on rm.cliente = cli.id
                    left join unidades u
                    ON rm.codigo = u.codigo
                    left join impresiones_pos 
                    ON rm.id = impresiones_pos.id_rm
                    left join ven 
                    ON impresiones_pos.id_ven = ven.id
                    left join ct 
                    ON rm.corte = ct.id
                    left join fc 
                    ON rm.uuid = fc.uuid  
                    and fc.status = 1
                    and rm.uuid <> '-----'
                    left join cli clif
                    on fc.cliente = clif.id 
                    where 
	    rm.tipo_venta = 'D'
                    AND DATE(ct.fecha) BETWEEN " . str_replace("-", "", $FechaI) . " and " . str_replace("-", "", $FechaF) . " 
                    and rm.importe > 0
                    group by rm.id
                    order by rm.id";
    $selectDespachos2 = "
                    SELECT * FROM (
                        SELECT
                            cli.nombre Cliente
                            , rm.producto Producto
                            , ROUND(SUM(rm.importe/precio),3) Cantidad
                            , ROUND(SUM(((rm.precio-rm.ieps)/(1+rm.iva))*(rm.importe/precio)),3) Subtotal
                            , ROUND(SUM(((rm.precio-rm.ieps)/(1+rm.iva))*(rm.importe/precio)*(rm.iva)),3) Iva
                            , ROUND(SUM((rm.importe/precio)*(rm.ieps)),3) Ieps
                            , ROUND(SUM(rm.importe),3) Importe	
                            , 'Timbrado' timbrado
                            FROM rm 
                            INNER JOIN cli ON rm.cliente = cli.id
                            LEFT JOIN ct ON rm.corte = ct.id
                            WHERE rm.tipo_venta = 'D' AND rm.uuid <> '-----'
                            AND DATE(ct.fecha) BETWEEN '" . $FechaI . "' and '" . $FechaF . "' 
                            AND rm.importe > 0 GROUP BY rm.cliente,rm.producto
                        UNION ALL 
                        SELECT
                            cli.nombre Cliente,
                             rm.producto Producto
                            , ROUND(SUM(rm.importe/precio),3) Cantidad
                            , ROUND(SUM(((rm.precio-rm.ieps)/(1+rm.iva))*(rm.importe/precio)),3) Subtotal
                            , ROUND(SUM(((rm.precio-rm.ieps)/(1+rm.iva))*(rm.importe/precio)*(rm.iva)),3) Iva
                            , ROUND(SUM((rm.importe/precio)*(rm.ieps)),3) Ieps
                            , ROUND(SUM(rm.importe),3) Importe	
                             , 'No timbrado' timbrado
                            FROM 
                            rm INNER JOIN cli ON rm.cliente = cli.id
                            LEFT JOIN ct ON rm.corte = ct.id
                            WHERE rm.tipo_venta = 'D' AND rm.uuid = '-----'
                            AND DATE(ct.fecha) BETWEEN '" . $FechaI . "' AND '" . $FechaF . "' 
                            AND rm.importe > 0
                            GROUP BY rm.cliente,rm.producto) tb 
                    WHERE true ORDER BY Cliente,Producto,timbrado ASC";
} else {
    $selectDespachos = "SELECT  distinct 
                      fin_venta Fecha_Hora
                    , rm.id Despacho
                    , posicion Posicion
                    , rm.producto	Producto
                    , round(rm.$Tipo/precio,3) Cantidad
                    , precio Precio
                    , round(((rm.precio-rm.ieps)/(1+rm.iva))*(rm.$Tipo/precio),3) Subtotal
                    , round(((rm.precio-rm.ieps)/(1+rm.iva))*(rm.$Tipo/precio)*(rm.iva),3) Iva
                    , round((rm.$Tipo/precio)*(rm.ieps),3) Ieps
                    , round(rm.$Tipo,3)	Importe
                    , round(((rm.precio-rm.ieps)/(1+rm.iva))*(rm.descuento/precio),3) descuentoSubtotal
                    , round(((rm.precio-rm.ieps)/(1+rm.iva))*(rm.descuento/precio)*(rm.iva),3) descuentoIva
                    , round((rm.descuento/precio)*(rm.ieps),3) descuentoIeps
                    , round(rm.descuento,3)	Descuento
                    /*, round(rm.pagoreal,3)	PagoReal*/
                    , GROUP_CONCAT(impresiones_pos.id_ven)	Despachador -- 	Nota	
                    , concat(fc.serie,' | ',fc.folio) Factura	
                    , cli.nombre Cliente
                    , rm.codigo	Codigo
                    , cli.tipodepago 	Tipo
                    , rm.tipo_venta
                    , u.descripcion 	Vehículo
                    , u.placas	Placas
                    , rm.uuid
                    , if(rm.uuid != '-----',clif.rfc,'') rfc
                    , if(rm.uuid != '-----',clif.nombre,'') nombre
                    , rm.comprobante
                    , GROUP_CONCAT(ven.nombre) Imprime
                    from 
                    rm 
                    inner join cli
                    on rm.cliente = cli.id
                    left join unidades u
                    ON rm.codigo = u.codigo
                    left join impresiones_pos 
                    ON rm.id = impresiones_pos.id_rm
                    left join ven 
                    ON impresiones_pos.id_ven = ven.id
                    left join fc 
                    ON rm.uuid = fc.uuid  
                    and fc.status = 1
                    and rm.uuid <> '-----'
                    left join cli clif
                    on fc.cliente = clif.id 
                    where 
                    rm.fecha_venta BETWEEN " . str_replace("-", "", $FechaI) . " and " . str_replace("-", "", $FechaF) . "
                    and rm.$Tipo > 0
                    and rm.tipo_venta = 'D'
                    group by rm.id
                    order by rm.id";
    $selectDespachos2 = "
                    SELECT * FROM (
                        SELECT
                            cli.nombre Cliente
                            , rm.producto Producto
                            , ROUND(SUM(rm.$Tipo/precio),3) Cantidad
                            , ROUND(SUM(((rm.precio-rm.ieps)/(1+rm.iva))*(rm.$Tipo/precio)),3) Subtotal
                            , ROUND(SUM(((rm.precio-rm.ieps)/(1+rm.iva))*(rm.$Tipo/precio)*(rm.iva)),3) Iva
                            , ROUND(SUM((rm.$Tipo/precio)*(rm.ieps)),3) Ieps
                            , ROUND(SUM(rm.$Tipo),3) Importe	
                            , ROUND(SUM(rm.descuento),3) Descuento	
                            , ROUND(SUM(rm.$Tipo - rm.descuento),3) Total
                            , 'Timbrado' timbrado
                            FROM rm 
                            INNER JOIN cli ON rm.cliente = cli.id
                            WHERE rm.tipo_venta = 'D' AND rm.uuid <> '-----'
                            AND rm.fecha_venta BETWEEN " . str_replace("-", "", $FechaI) . " and " . str_replace("-", "", $FechaF) . " 
                            AND rm.$Tipo > 0 GROUP BY rm.cliente,rm.producto
                        UNION ALL 
                        SELECT
                            cli.nombre Cliente,
                             rm.producto Producto
                            , ROUND(SUM(rm.$Tipo/precio),3) Cantidad
                            , ROUND(SUM(((rm.precio-rm.ieps)/(1+rm.iva))*(rm.$Tipo/precio)),3) Subtotal
                            , ROUND(SUM(((rm.precio-rm.ieps)/(1+rm.iva))*(rm.$Tipo/precio)*(rm.iva)),3) Iva
                            , ROUND(SUM((rm.$Tipo/precio)*(rm.ieps)),3) Ieps
                            , ROUND(SUM(rm.$Tipo),3) Importe	
                            , ROUND(SUM(rm.descuento),3) Descuento	
                            , ROUND(SUM(rm.$Tipo - rm.descuento),3) Total
                            , 'No timbrado' timbrado
                            FROM 
                            rm INNER JOIN cli ON rm.cliente = cli.id
                            WHERE rm.tipo_venta = 'D' AND rm.uuid = '-----'
                            AND rm.fecha_venta BETWEEN " . str_replace("-", "", $FechaI) . " AND " . str_replace("-", "", $FechaF) . " 
                            AND rm.$Tipo > 0
                            GROUP BY rm.cliente,rm.producto) tb 
                    WHERE true ORDER BY Cliente,Producto,timbrado ASC";
}
if ($varg) {
    $SQLCompras = "SELECT et.id,prv.nombre,inv.descripcion,et.concepto,etd.producto,SUM(etd.cantidad) cnt,"
            . "etd.costo,etd.costo*SUM(etd.cantidad) importe,prv.id idPrv FROM et LEFT JOIN etd ON et.id=etd.id "
            . "LEFT JOIN prv ON prv.id=et.proveedor LEFT JOIN inv ON etd.producto=inv.id WHERE inv.id > 0 AND "
            . "et.fecha BETWEEN '$FechaI' AND '$FechaF' AND et.status='Cerrada' GROUP BY et.proveedor,etd.producto,et.id ORDER BY prv.id,et.id;";
}

//* Reporte de facturas por producto */

$selectFacturasProct = "SELECT  concat(fc.serie,' ',fc.folio) folio,cli.nombre,date(fc.fecha) fecha,
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
        group by fc.serie,fc.folio,fcd.producto";

$selectFacturasProctT = "SELECT  inv.descripcion ,
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
            group by fc.status, fcd.producto ";
$SqlDescuentosAplicadosTarjetaMonederoXConsumos = "SELECT count(1) cnt,cli.id,cli.nombre,SUM(volumen) volumen,SUM(rm.importe) importe,SUM(rm.descuento) descuento,
    u.codigo,rm.fecha_venta,DATE_FORMAT(STR_TO_DATE(fecha_venta, '%Y%m%d'), '%Y-%m-%d') fecha 
        FROM omicrom.beneficios b 
        LEFT JOIN rm ON rm.id=b.id_consumo 
        LEFT JOIN unidades u ON u.id=b.id_unidad
        LEFT JOIN cli ON cli.id=u.cliente
    WHERE rm.descuento > 0 AND rm.fecha_venta BETWEEN " . str_replace("-", "", $FechaI) . " AND " . str_replace("-", "", $FechaF) . " AND b.tipo='I'
    GROUP BY cli.id,rm.fecha_venta ORDER BY cli.id,rm.fecha_venta DESC;";

$addWhere = $v > 1 ? "WHERE id NOT IN ($Rvvl) " : "";
$addWhereIn = $v > 1 ? "WHERE id IN ($Rvvl) " : "";
$fecha_ini = date("Ymd", strtotime($FechaI));
$fecha_fin = date("Ymd", strtotime($FechaF));
$ordn = $MenosVenta ? "ASC" : "DESC";
$Vproducto = $Producto <> "*" ? "rm.producto = '$Producto' AND" : "";
if ($Promocion) {
    $SqlPnts = "
        SELECT nombre,volumen,id FROM (    
        SELECT 
            count(1) cnt,
            cli.id,
            CONCAT(cli.id, '.- ', cli.nombre) nombre,
            SUM(volumen) volumen,
            u.codigo,
            rm.fecha_venta,
            DATE_FORMAT(STR_TO_DATE(fecha_venta, '%Y%m%d'),'%Y-%m-%d') fecha
        FROM
            beneficios b
                LEFT JOIN
            rm ON rm.id = b.id_consumo
                LEFT JOIN
            unidades u ON u.id = b.id_unidad
                LEFT JOIN
            cli ON cli.id = u.cliente
                LEFT JOIN
            (SELECT 
                COUNT(1) cntG,
                    SUM(volumen) volumenG,
                    SUM(descuento) descuentoG,
                    u.cliente clienteG
            FROM
                beneficios b
            LEFT JOIN rm ON b.id_consumo = rm.id
            LEFT JOIN unidades u ON b.id_unidad = u.id
            WHERE $Vproducto
                rm.fecha_venta BETWEEN $fecha_ini AND $fecha_fin
            GROUP BY u.cliente) ttCli ON ttCli.clienteG = cli.id
        WHERE cli.id > 0 AND $Vproducto cli.tipodepago NOT IN ('Tarjeta','Monedero') AND
            rm.descuento > 0
                AND rm.fecha_venta BETWEEN $fecha_ini AND  $fecha_fin
                AND b.tipo = 'I'
        GROUP BY cli.id) Vv $addWhere ORDER BY volumen $ordn;";
    if ($Rvvl !== "") {
        $SqlPntsIn = "
            SELECT nombre,volumen,id FROM (    
            SELECT 
                count(1) cnt,
                cli.id,
                CONCAT(cli.id, '.- ', cli.nombre) nombre,
                SUM(volumen) volumen,
                u.codigo,
                rm.fecha_venta,
                DATE_FORMAT(STR_TO_DATE(fecha_venta, '%Y%m%d'),'%Y-%m-%d') fecha
            FROM
                beneficios b
                    LEFT JOIN
                rm ON rm.id = b.id_consumo
                    LEFT JOIN
                unidades u ON u.id = b.id_unidad
                    LEFT JOIN
                cli ON cli.id = u.cliente
                    LEFT JOIN
                (SELECT 
                    COUNT(1) cntG,
                        SUM(volumen) volumenG,
                        SUM(descuento) descuentoG,
                        u.cliente clienteG
                FROM
                    beneficios b
                LEFT JOIN rm ON b.id_consumo = rm.id
                LEFT JOIN unidades u ON b.id_unidad = u.id
                WHERE $Vproducto
                    rm.fecha_venta BETWEEN $fecha_ini AND $fecha_fin
                GROUP BY u.cliente) ttCli ON ttCli.clienteG = cli.id
            WHERE cli.id > 0 AND $Vproducto cli.tipodepago NOT IN ('Tarjeta','Monedero') AND
                rm.descuento > 0
                    AND rm.fecha_venta BETWEEN $fecha_ini AND  $fecha_fin
                    AND b.tipo = 'I'
            GROUP BY cli.id) Vv $addWhereIn ORDER BY volumen $ordn;";
    }
} elseif ($PorVentas) {
    $SqlPnts = "SELECT * FROM (SELECT CONCAT(cli.id, '.- ', cli.nombre) nombre,SUM(volumen) volumen,cli.id
        FROM rm LEFT JOIN cli ON cli.id=rm.cliente 
        WHERE $Vproducto cli.tipodepago NOT IN ('Tarjeta','Monedero') AND  fecha_venta BETWEEN $fecha_ini AND $fecha_fin AND cli.id > 0 GROUP BY cli.id) AS vv $addWhere ORDER BY volumen $ordn";
    if ($Rvvl !== "") {
        $SqlPntsIn = "SELECT * FROM (SELECT CONCAT(cli.id, '.- ', cli.nombre) nombre,SUM(volumen) volumen,cli.id
        FROM rm LEFT JOIN cli ON cli.id=rm.cliente 
        WHERE $Vproducto cli.tipodepago NOT IN ('Tarjeta','Monedero') AND fecha_venta BETWEEN $fecha_ini AND $fecha_fin AND cli.id > 0 GROUP BY cli.id) AS vv $addWhereIn ORDER BY volumen $ordn";
    }
}