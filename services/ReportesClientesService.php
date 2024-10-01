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
$ciaDAO = new CiaDAO();
$ciaVO = $ciaDAO->retrieve(1);
$ConcentrarVtasTarjeta = $ciaVO->getVentastarxticket();

//error_log("------El valor de hashattribute222---" . print_r($request->hasAttribute("criteria"), true));
if ($request->hasAttribute("criteria")) {
    utils\HTTPUtils::setSessionValue("Fecha", date("Y-m-d"));
    utils\HTTPUtils::setSessionValue("FechaI", date("Y-m-d", strtotime("-1 day", strtotime(date("Y-m-d")))));
    utils\HTTPUtils::setSessionValue("FechaF", date("Y-m-d"));
    utils\HTTPUtils::setSessionValue("HoraI", "**");
    utils\HTTPUtils::setSessionValue("HoraF", "**");
    utils\HTTPUtils::setSessionValue("Turno", "*");
    utils\HTTPUtils::setSessionValue("Detallado", "Si");
    utils\HTTPUtils::setSessionValue("Desglose", "Cortes");
    utils\HTTPUtils::setSessionValue("Producto", "*");
    utils\HTTPUtils::setSessionValue("Cliente", 0);
    utils\HTTPUtils::setSessionValue("Codigo", "*");
    utils\HTTPUtils::setSessionValue("SCliente", "");
    utils\HTTPUtils::setSessionValue("orden", "factura");
    utils\HTTPUtils::setSessionValue("ordenPago", "pagos.fecha");
    utils\HTTPUtils::setSessionValue("TipoCliente", "*");
    utils\HTTPUtils::setSessionValue("IslaPosicion", "*");
    utils\HTTPUtils::setSessionValue("Descripcion", "");
    utils\HTTPUtils::setSessionValue("Clave", "*");
}
$TiposClienteArray = Array(
    "Credito" => "Credito",
    "Contado" => "Contado",
    "Tarjeta" => "Tarjeta",
    "Monedero" => "Monedero",
    "Consignacion" => "Consignacion",
    "Prepago" => "Prepago",
    "Puntos" => "Puntos",
    "Vales" => "Vales",
    "*" => "Todos"
);

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
if ($request->hasAttribute("Producto")) {
    utils\HTTPUtils::setSessionValue("Producto", $sanitize->sanitizeString("Producto"));
}
if ($request->hasAttribute("Clave")) {
    utils\HTTPUtils::setSessionValue("Clave", $sanitize->sanitizeString("Clave"));
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
    } else {
        utils\HTTPUtils::setSessionValue("Cliente", "");
    }
}
if ($request->hasAttribute("Codigo")) {
    utils\HTTPUtils::setSessionValue("Codigo", $sanitize->sanitizeString("Codigo"));
}
if ($request->hasAttribute("orden")) {
    utils\HTTPUtils::setSessionValue("orden", $sanitize->sanitizeString("orden"));
}
if ($request->hasAttribute("ordenPago")) {
    utils\HTTPUtils::setSessionValue("ordenPago", $sanitize->sanitizeString("ordenPago"));
}
if ($request->hasAttribute("TipoCliente")) {
    utils\HTTPUtils::setSessionValue("TipoCliente", $sanitize->sanitizeString("TipoCliente"));
}
if ($request->hasAttribute("IslaPosicion")) {
    utils\HTTPUtils::setSessionValue("IslaPosicion", $sanitize->sanitizeString("IslaPosicion"));
}
if ($request->hasAttribute("Descripcion")) {
    utils\HTTPUtils::setSessionValue("Descripcion", $request->getAttribute("Descripcion"));
}

$Fecha = utils\HTTPUtils::getSessionValue("Fecha");
$FechaI = utils\HTTPUtils::getSessionValue("FechaI");
$FechaF = utils\HTTPUtils::getSessionValue("FechaF");
$HoraI = utils\HTTPUtils::getSessionValue("HoraI");
$HoraF = utils\HTTPUtils::getSessionValue("HoraF");
$Turno = utils\HTTPUtils::getSessionValue("Turno");
$Detallado = utils\HTTPUtils::getSessionValue("Detallado");
$Desglose = utils\HTTPUtils::getSessionValue("Desglose");
$Producto = utils\HTTPUtils::getSessionValue("Producto");
$Cliente = utils\HTTPUtils::getSessionValue("Cliente");
$Codigo = utils\HTTPUtils::getSessionValue("Codigo");
$SCliente = utils\HTTPUtils::getSessionValue("SCliente");
$orden = utils\HTTPUtils::getSessionValue("orden");
$ordenPago = utils\HTTPUtils::getSessionValue("ordenPago");
$TipoCliente = utils\HTTPUtils::getSessionValue("TipoCliente");
$IslaPosicion = utils\HTTPUtils::getSessionValue("IslaPosicion");
$Descripcion = utils\HTTPUtils::getSessionValue("Descripcion");
$Clave = utils\HTTPUtils::getSessionValue("Clave");

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

/* Consulta para revisar estados de cuenta */
$Tabla = $Tabla === "cxch" ? "cxch" : "cxc";
$selectAbonos = "SELECT SUM(importe) importe FROM $Tabla AS cxc WHERE cliente = '$Cliente' AND tm = 'H' AND fecha < DATE('$FechaI')";
$resultAbonos = utils\IConnection::execSql($selectAbonos);
$Abono = $resultAbonos["importe"];

$selectCargos = "SELECT SUM(importe) importe FROM $Tabla AS cxc WHERE cliente = '$Cliente' AND tm = 'C' AND fecha < DATE('$FechaI')";
$resultCargos = utils\IConnection::execSql($selectCargos);
$Cargo = $resultCargos["importe"];

$selectCxc = "";

if ($orden == "factura") {
    $selectCxc .= " 
                SELECT SUB.referencia,SUB.placas,SUB.fecha,DATEDIFF(CURRENT_DATE(), SUB.fecha) antiguedad,
                IF(ISNULL(SUB.factura) OR SUB.factura  = 0, CONCAT(SUB.conceptoOriginal, ' ' , descripcion), conceptoFactura) concepto,
                SUB.factura,IF(SUB.tm = 'C',SUB.importe, 0) cargo, IF(SUB.tm = 'H',SUB.importe, 0) abono
                FROM (
                    SELECT 
                    -- IF(ISNULL( cxc.factura ) OR cxc.factura = 0, cxc.fecha, DATE(fc.fecha) ) fecha,
                    cxc.fecha,
                    IF(ISNULL( cxc.factura ) OR cxc.factura = 0, cxc.referencia,  cxc.factura) referencia, cxc.tm, cxc.placas,
                    IFNULL(
                            CASE cxc.factura  
                            WHEN ( ISNULL( cxc.factura ) OR cxc.factura = 0) THEN cxc.concepto
                            ELSE 
                             CONCAT(
                                    IF( cxc.tm = 'H', 'Pago De ', '' ), 'Factura No. ', IF( cxc.factura = 0, cxc.referencia, 
                                    CONCAT( IF ( ISNULL( fc.serie ) OR fc.serie = '', '', CONCAT( fc.serie, '-' ) ), fc.folio ) ) 
                                    )
                            END, 
                    cxc.concepto ) conceptoFactura,
                    IFNULL(com.descripcion,'') descripcion,
                    cxc.concepto conceptoOriginal,
                    ROUND(SUM(cxc.importe ),2) importe,
                    CONCAT( IF( ISNULL(fc.serie) OR fc.serie = '', '', CONCAT(fc.serie,'-') ), fc.folio) factura
                    FROM $Tabla AS cxc
                    LEFT JOIN fc ON cxc.factura = fc.id
                    LEFT JOIN com ON com.clavei = cxc.producto
                    WHERE  cxc.cliente = '$Cliente' 
                    AND cxc.fecha BETWEEN DATE('$FechaI') AND DATE('$FechaF') 
                    AND cxc.cliente > 0
                    GROUP BY IFNULL( IF( cxc.factura = 0, cxc.referencia, cxc.factura ), cxc.referencia ), cxc.tm 
                    ORDER BY cxc.$orden,cxc.fecha,cxc.tm
                ) SUB ";
} else {
    $selectCxc .= " 

                SELECT cxc.referencia,cxc.placas,cxc.fecha,DATEDIFF(CURRENT_DATE(), cxc.fecha) antiguedad,cxc.tm,
                CONCAT(cxc.concepto, ' ', IFNULL(com.descripcion,'')) concepto,
                CONCAT( IF( ISNULL(fc.serie) OR fc.serie = '', '', CONCAT(fc.serie,'-') ), fc.folio) factura,
                IF(cxc.tm = 'C',cxc.importe, 0) cargo, IF(cxc.tm = 'H',cxc.importe, 0) abono
                FROM $Tabla AS cxc
                LEFT JOIN fc ON cxc.factura = fc.id
                LEFT JOIN com ON com.clavei = cxc.producto
                WHERE  cxc.cliente = '$Cliente' AND cxc.fecha BETWEEN DATE('$FechaI') AND DATE('$FechaF')  
                AND cxc.cliente > 0
                ORDER BY cxc.fecha,cxc.$orden,cxc.tm ;";
}
//error_log($selectCxc);


/* Consulta para saldos por cliente */
$selectSaldos = "
        SELECT * FROM (
            SELECT cxc.cliente, cli.nombre, cli.tipodepago, cli.diasCredito, cli.alias, cli.ncc,cli.limite,
            ROUND( SUM( CASE WHEN tm = 'C' THEN importe ELSE -importe END ), 2 ) importe,
            CASE WHEN cli.tipodepago IN ('Prepago') THEN 2 ELSE 2 END orden,
            SUM(IF(cli.tipodepago='Consignacion',cantidad,0)) cntConsignacion
            FROM cxc INNER JOIN cli ON cxc.cliente = cli.id
            WHERE cxc.fecha <= DATE('" . $Fecha . "') AND cli.tipodepago NOT REGEXP 'Contado|Puntos' 
            AND cli.activo = 'Si'
            GROUP BY cxc.cliente
        ) cxc
        ";

/* Consulta para reporte de consumos */

$cWhere = '';
$cWhereT = '';
$cWhereA = '';

if ($Desglose === "Cortes") {
    if (!empty($Turno) && $Turno !== "*") {
        $cWhere = $cWhere . " AND ct.turno='$Turno'";
    }
    $cWhere = $cWhere . " AND DATE(ct.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')"; //ventas
    $cWhereT = $cWhereT . " AND DATE(ct.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')"; //cttarjetas
    $cWhereA = $cWhereA . " AND DATE(ct.fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF')"; //aditivos
} else {
    $FechaIQ = $FechaI . " " . ($HoraI <> "**" ? $HoraI . ":00:00" : "00:00:00");
    $FechaFQ = $FechaF . " " . ($HoraF <> "**" ? $HoraF . ":59:59" : "23:59:59");

    $cWhere = $cWhere . " AND rm.fin_venta BETWEEN '$FechaIQ' AND '$FechaFQ'"; //ventas
    $cWhereT = $cWhereT . " AND cttarjetas.fecha BETWEEN '$FechaIQ' AND '$FechaFQ'"; //cttarjetas
    $cWhereA = $cWhereA . " AND vt.fecha BETWEEN '$FechaIQ' AND '$FechaFQ'"; //aditivos
}

if (!empty($Cliente)) {
    $cWhere .= " AND rm.cliente = '$Cliente'";
    $cWhereT = $cWhereT . " AND cttarjetas.banco = '$Cliente'";
    $cWhereA = $cWhereA . " AND vt.cliente = '$Cliente'";
}

if (!empty($Codigo) && $Codigo !== "*") {
    $cWhere .= " AND rm.codigo LIKE '%$Codigo%'";
}
if ($Producto !== "*") {
    $cWhere .= " AND rm.producto = '$Producto'";
}

if ($TipoCliente !== "*") {
    $cWhere .= " AND cli.tipodepago = '$TipoCliente'";
    $cWhereA .= " AND cli.tipodepago = '$TipoCliente'";
}

if ($Detallado === "No") {
    $cWhere .= " AND rm.uuid = '" . FcDAO::SIN_TIMBRAR . "'";
    $cWhereA .= " AND vt.uuid = '" . FcDAO::SIN_TIMBRAR . "'";
}

if (is_numeric($IslaPosicion)) {
    $cWhere .= " AND man.isla_pos = $IslaPosicion";
    $cWhereA .= " AND man.isla_pos = $IslaPosicion";
}

/* $selectConsumos = "SELECT * FROM 
  (
  SELECT * FROM
  (
  SELECT man.isla_pos, cli.nombre, cli.tipodepago, rm.corte,rm.cliente, rm.id, rm.fin_venta fecha, rm.pagoreal pesos, rm.pesos importe, rm.volumen,
  com.descripcion producto, rm.posicion, rm.kilometraje,
  IF(cxc.placas = '-',UPPER(rm.placas),UPPER(cxc.placas)) placas,
  CASE
  WHEN LENGTH(TRIM(rm.codigo)) = 20 THEN rm.codigo
  WHEN LENGTH(TRIM(rm.codigo)) = 10 OR LENGTH(TRIM(rm.codigo)) > 20 THEN 'Vales'
  ELSE ''
  END codigo,
  IF(unidades.impreso IS NULL,'" . FcDAO::SIN_TIMBRAR . "',unidades.impreso) impreso,
  IF(unidades.descripcion IS NULL,'" . FcDAO::SIN_TIMBRAR . "',unidades.descripcion) descripcion,
  rm.uuid
  FROM cli,com ,ct, man, rm
  LEFT JOIN unidades ON rm.codigo = unidades.codigo
  LEFT JOIN cxc ON rm.id = cxc.referencia
  WHERE 1 =1 AND rm.cliente = cli.id AND rm.producto = com.clavei AND ct.id = rm.corte AND man.posicion = rm.posicion AND man.activo = 'Si'
  AND rm.cliente > 0 AND rm.tipo_venta = 'D' $cWhere
  GROUP BY rm.id
  ORDER BY rm.cliente,rm.id
  ) AS A
  UNION
  SELECT * FROM
  (
  SELECT 0 isla_pos, cli.nombre, cli.tipodepago, cttarjetas.id corte,cli.id cliente, cttarjetas.idnvo, cttarjetas.fecha,
  cttarjetas.importe pesos, cttarjetas.importe, 0 volumen,
  '' producto, 0 posicion, '' kilometraje, cxc.placas, '' codigo, '' impreso, 'T' descripcion,'".FcDAO::SIN_TIMBRAR."' uuid
  FROM cli, ct,cttarjetas
  LEFT JOIN cxc ON cttarjetas.id = cxc.referencia AND cttarjetas.id = cxc.corte
  WHERE cttarjetas.banco = cli.id  AND  cttarjetas.id = ct.id
  AND cli.tipodepago = 'Tarjeta'  $cWhereT
  ORDER BY cttarjetas.banco,cttarjetas.idnvo
  ) AS B
  ) SUB
  WHERE 1 = 1
  ORDER BY SUB.cliente,SUB.codigo,SUB.fecha
  "; */

$selectConsumos = "
        SELECT * FROM (
            SELECT tipo, isla_pos, ticket, corte, codigo, impreso, fecha, placas, kilometraje, descripcion, producto, uuid, 
            tipodepago, cliente, nombre, volumen, importe, pagoreal,iva,ieps,numeco,alias,descUnidad
            FROM (
                SELECT 0 tipo, man.isla_pos, rm.id ticket, rm.corte, rm.iva,rm.ieps,
                CASE 
                    WHEN LENGTH(TRIM(rm.codigo)) = 19 OR  LENGTH(TRIM(rm.codigo)) = 11 OR LENGTH(TRIM(rm.codigo)) = 20 THEN rm.codigo
                    WHEN LENGTH(TRIM(rm.codigo)) = 10 OR LENGTH(TRIM(rm.codigo)) > 20 THEN 'Vales'
                    ELSE ''
                END codigo, 
                IF(unidades.impreso IS NULL,'" . FcDAO::SIN_TIMBRAR . "',unidades.impreso) impreso, 
                rm.fin_venta fecha, unidades.numeco,   ven.alias ,unidades.descripcion descUnidad,
                unidades.placas COLLATE utf8_general_ci placas, 
                rm.kilometraje, IF(unidades.descripcion IS NULL,'" . FcDAO::SIN_TIMBRAR . "',unidades.descripcion) descripcion, 
                com.descripcion producto, rm.uuid,
                cli.tipodepago, rm.cliente, cli.nombre, rm.volumen, rm.pesos importe, rm.pagoreal                                         
                FROM cli,com ,ct, man, rm LEFT JOIN ven ON ven.id = rm.vendedor
                LEFT JOIN unidades ON rm.codigo = unidades.codigo 
                LEFT JOIN cxc ON rm.id = cxc.referencia
                WHERE 1 =1 AND rm.cliente = cli.id AND rm.producto = com.clavei AND ct.id = rm.corte AND man.posicion = rm.posicion AND man.activo = 'Si'
                AND rm.cliente > 0 AND rm.tipo_venta in ('D','N') $cWhere 
                GROUP BY rm.id 
                ORDER BY rm.cliente,rm.id
            ) AS SUB_A
            UNION ALL
            SELECT tipo, isla_pos, ticket, corte, codigo, impreso, fecha, placas, kilometraje, descripcion, producto, uuid, 
            tipodepago, cliente, nombre, volumen, importe, pagoreal,iva,ieps,numeco,'-' alias,'-' descUnidad
            FROM (               
                SELECT 1 tipo, man.isla_pos, vt.id ticket, vt.corte, vt.codigo, IFNULL(unidades.impreso, '-----') impreso, vt.fecha, 
                '' placas, 0 kilometraje, vt.descripcion, vt.clave producto, vt.uuid, 
                cli.tipodepago, vt.cliente, cli.nombre,
                vt.cantidad volumen, vt.total importe, vt.total pagoreal, 0 ieps,vt.iva,unidades.numeco
                FROM ct, man, cli, vtaditivos vt
                LEFT JOIN unidades ON vt.codigo = unidades.codigo 
                WHERE TRUE 
                AND vt.posicion = man.posicion AND cli.id = vt.cliente  
                AND vt.corte = ct.id AND vt.tm = 'C' AND vt.cliente > 0
                $cWhereA
                ORDER BY vt.fecha
            ) AS SUB_B
            UNION ALL
            SELECT tipo, isla_pos, ticket, corte, codigo, impreso, fecha, placas, kilometraje, descripcion, producto, uuid, 
            tipodepago, cliente, nombre, volumen, importe, pagoreal,iva,ieps, '-' numeco,'-' alias,'-' descUnidad
            FROM 
            (
                SELECT 3 tipo, 0 isla_pos, cttarjetas.idnvo ticket, cttarjetas.id corte, '' codigo, '' impreso, cttarjetas.fecha, cxc.placas,
                '' kilometraje, 'T' descripcion, '' producto, '" . FcDAO::SIN_TIMBRAR . "' uuid, cli.tipodepago,
                cli.id cliente, cli.nombre, 0 volumen, cttarjetas.importe,  cttarjetas.importe pagoreal, 0 ieps, 0.16 iva
                FROM cli, ct,cttarjetas  
                LEFT JOIN cxc ON cttarjetas.id = cxc.referencia AND cttarjetas.id = cxc.corte AND cxc.tm = 'C' AND cxc.producto = '-'
                WHERE cttarjetas.banco = cli.id  AND  cttarjetas.id = ct.id 
                AND cli.tipodepago = 'Tarjeta'  $cWhereT    
                ORDER BY cttarjetas.banco,cttarjetas.idnvo
            ) AS SUB_C
        ) AS SUB
        WHERE 1 = 1
        ORDER BY SUB.tipo, SUB.cliente,SUB.fecha,SUB.codigo,SUB.ticket ASC
        ";

if ($Clave != "*" AND $Clave != "Aditivos") {
    $selectConsumos = "select * from (
                                $selectConsumos 
                                )consumos
                                WHERE consumos.producto = '$Clave' ;
                            ";
} if ($Clave == "Aditivos") {
    $selectConsumos = "select * from (
                $selectConsumos 
                )consumos
                WHERE consumos.tipo = 1 ;
            ";
}
$selectConsumosAce = "
        SELECT * FROM (
            SELECT man.isla_pos, vt.id, vt.corte, '-----' impreso, vt.fecha, 
            '' placas, 0 kilometraje, vt.descripcion, vt.clave producto, vt.uuid, 
            vt.cantidad volumen, vt.total importe, vt.total pesos
            FROM vtaditivos vt, man,cli ,ct
            WHERE TRUE 
            AND vt.posicion = man.posicion AND cli.id = vt.cliente  
            AND vt.corte = ct.id AND vt.tm = 'C' AND vt.cliente > 0
            $cWhereA
        ) SUB
        WHERE TRUE
        ORDER BY SUB.fecha
        ";

$selectConsumosTotalesByProducto = "
        SELECT count(*) cargas, IF(tipo_venta='D',SUM(rm.importe),0) pesos, SUM(rm.volumen) volumen,IF(tipo_venta='D',SUM(rm.pagoreal),0) pr,
        ROUND(rm.ieps * SUM(rm.volumen),2) ieps,
        ((SUM(importe) - ROUND(rm.ieps * SUM(rm.volumen),2))/(1+rm.iva)) * rm.iva iva2,
        (SUM(importe) -ROUND(rm.ieps * SUM(rm.volumen),2))/(1+rm.iva) imp,
        com.descripcion producto ,IF(tipo_venta='D','Venta','Consignación') tv 
        FROM cli, man, rm, com ,ct
        WHERE 1 =1 AND rm.cliente = cli.id AND man.posicion = rm.posicion AND rm.producto = com.clavei AND rm.corte = ct.id 
        AND  rm.tipo_venta in ('D','N')
        AND rm.cliente > 0 $cWhere
        GROUP BY rm.producto,rm.tipo_venta";

$selectConsumosAditivos = "
        SELECT vt.cliente,cli.nombre,SUM(vt.total) importe, SUM(vt.cantidad) cantidad,vt.iva
        FROM vtaditivos vt, man,cli ,ct
        WHERE 1=1 AND vt.posicion = man.posicion AND cli.id = vt.cliente  AND vt.corte = ct.id AND vt.tm = 'C' AND vt.cliente > 0
        $cWhereA
        GROUP BY vt.cliente";

$selectConsumosTarjetasConcetrado = "
        SELECT cli.id,UPPER(cli.alias) banco, SUM(cttarjetas.importe) importe
        FROM cli, cttarjetas, ct
        WHERE cli.tipodepago = 'Tarjeta' AND cttarjetas.id = ct.id
        AND cttarjetas.banco = cli.id 
        $cWhereT
        GROUP BY cttarjetas.banco";

/* Consultas para reporte de pagos */
$selectPagosCredito = "
        SELECT pagos.id,pagos.cliente,pagos.importe,pagos.fecha_deposito deposito, pagos.fecha aplicacion,cli.nombre, pagos.formapago, cli.ncc
        FROM pagos LEFT JOIN cli ON pagos.cliente = cli.id
        WHERE DATE($ordenPago) BETWEEN DATE('$FechaI') AND DATE('$FechaF') 
        AND pagos.status = 'Cerrada' AND cli.tipodepago='Credito' ";

$selectPagosContado = "
        SELECT pagos.id,pagos.cliente,pagos.importe,pagos.fecha_deposito deposito, pagos.fecha aplicacion,cli.nombre, pagos.formapago, cli.ncc
        FROM pagos LEFT JOIN cli ON pagos.cliente = cli.id
        WHERE DATE($ordenPago) BETWEEN DATE('$FechaI') AND DATE('$FechaF') 
        AND pagos.status = 'Cerrada' AND cli.tipodepago='Contado' ";

$selectPagosConsignacion = "
        SELECT pagos.id,pagos.cliente,pagos.importe,pagos.fecha_deposito deposito, pagos.fecha aplicacion,cli.nombre, pagos.formapago, cli.ncc
        FROM pagos LEFT JOIN cli ON pagos.cliente = cli.id
        WHERE DATE($ordenPago) BETWEEN DATE('$FechaI') AND DATE('$FechaF') 
        AND pagos.status = 'Cerrada' AND cli.tipodepago='Consignacion' ";

$selectPagosMonederos = "
        SELECT pagos.id,pagos.cliente,pagos.importe,pagos.fecha_deposito deposito, pagos.fecha aplicacion,cli.nombre, pagos.formapago
        FROM pagos LEFT JOIN cli ON pagos.cliente = cli.id
        WHERE DATE($ordenPago) BETWEEN DATE('$FechaI') AND DATE('$FechaF') 
        AND pagos.status = 'Cerrada' AND cli.tipodepago='Monedero' ";
$selectPagosPrepago = "
        SELECT pagos.id,pagos.cliente,pagos.importe,pagos.fecha_deposito deposito, pagos.fecha aplicacion,cli.nombre, pagos.formapago
        FROM pagos LEFT JOIN cli ON pagos.cliente = cli.id
        WHERE DATE($ordenPago) BETWEEN DATE('$FechaI') AND DATE('$FechaF') 
        AND pagos.status = 'Cerrada' AND cli.tipodepago='Prepago' ";

$selectPagosPuntos = "
        SELECT pagos.id,pagos.cliente,pagos.importe,pagos.fecha_deposito deposito, pagos.fecha aplicacion,cli.nombre, pagos.formapago
        FROM pagos LEFT JOIN cli ON pagos.cliente = cli.id
        WHERE DATE($ordenPago) BETWEEN DATE('$FechaI') AND DATE('$FechaF') 
        AND pagos.status = 'Cerrada' AND cli.tipodepago='Puntos' ";

$selectPagosTarjeta = "
        SELECT pagos.id,pagos.cliente,pagos.importe,pagos.fecha_deposito deposito, pagos.fecha aplicacion,cli.nombre, pagos.formapago
        FROM pagos LEFT JOIN cli ON pagos.cliente = cli.id
        WHERE DATE($ordenPago) BETWEEN DATE('$FechaI') AND DATE('$FechaF') 
        AND pagos.status = 'Cerrada' AND cli.tipodepago='Tarjeta' ";

$selectPagosVales = "
        SELECT pagos.id,pagos.cliente,pagos.importe,pagos.fecha_deposito deposito, pagos.fecha aplicacion,cli.nombre, pagos.formapago
        FROM pagos LEFT JOIN cli ON pagos.cliente = cli.id
        WHERE DATE($ordenPago) BETWEEN DATE('$FechaI') AND DATE('$FechaF') 
        AND pagos.status = 'Cerrada' AND cli.tipodepago='Vales' ";

$selectPagos = "
        SELECT pagos.id,pagos.cliente,pagos.importe,pagos.fecha_deposito deposito, pagos.fecha aplicacion,cli.nombre, pagos.formapago, cli.ncc
        FROM pagos LEFT JOIN cli ON pagos.cliente = cli.id
        WHERE DATE($ordenPago) BETWEEN DATE('$FechaI') AND DATE('$FechaF') 
        AND pagos.status = 'Cerrada' ";

if ($Cliente > 0) {
    $Cli = " AND pagos.cliente = '$Cliente' ";
    $selectPagosCredito .= $Cli;
    $selectPagosContado .= $Cli;
    $selectPagosConsignacion .= $Cli;
    $selectPagosMonederos .= $Cli;
    $selectPagosPrepago .= $Cli;
    $selectPagosPuntos .= $Cli;
    $selectPagosTarjeta .= $Cli;
    $selectPagosVales .= $Cli;
    $selectPagos .= $Cli;
}
if ($TipoCliente !== "*") {
    $Cli = "AND cli.tipodepago = '$TipoCliente' ";
    $selectPagosCredito .= $Cli;
    $selectPagosContado .= $Cli;
    $selectPagosConsignacion .= $Cli;
    $selectPagosMonederos .= $Cli;
    $selectPagosPrepago .= $Cli;
    $selectPagosPuntos .= $Cli;
    $selectPagosTarjeta .= $Cli;
    $selectPagosVales .= $Cli;
    $selectPagos .= $Cli;
}
$Cli = " ORDER BY $ordenPago";
$selectPagosCredito .= $Cli;
$selectPagosContado .= $Cli;
$selectPagosConsignacion .= $Cli;
$selectPagosMonederos .= $Cli;
$selectPagosPrepago .= $Cli;
$selectPagosPuntos .= $Cli;
$selectPagosTarjeta .= $Cli;
$selectPagosVales .= $Cli;
$selectPagos .= $Cli;

$selectPagos .= " 
        ORDER BY $ordenPago";

$selectPagosT = "
        SELECT pagos.cliente,SUM(pagos.importe) importe,cli.nombre, COUNT(*) pagos
        FROM pagos LEFT JOIN cli ON pagos.cliente = cli.id
        WHERE DATE($ordenPago) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
        AND pagos.status = 'Cerrada'
        ";
if ($Cliente > 0) {
    $selectPagosT .= "AND pagos.cliente = '$Cliente'";
}
if ($TipoCliente !== "*") {
    $selectPagosT .= "AND cli.tipodepago = '$TipoCliente'";
}
$selectPagosT .= "
        GROUP BY pagos.cliente    
        ORDER BY pagos.cliente";

/* Consulta para reporte de cargos, abonos y saldo */

$selectC_A_S = "
SELECT * FROM (
    SELECT 
    C.cliente, 
    cli.nombre, 
    cli.tipodepago, 
    cli.alias, 
    SUM(IFNULL(inicial, 0)) inicial,
    SUM(IFNULL(cargo, 0)) cargos,
    SUM(IFNULL(abono, 0)) abonos,
    ROUND(SUM(IFNULL(inicial, 0)) + SUM(IFNULL(cargo, 0)) - SUM(IFNULL(abono, 0)) , 2) importe,
    CASE WHEN cli.tipodepago IN ('Prepago') THEN 2 ELSE 1 END orden
    FROM cli
    JOIN (
        SELECT cli.id, cxc.cliente,ROUND( SUM( IF(tm = 'C',importe,-importe) ), 2) inicial, 0 abono,0 cargo FROM cli 
        INNER JOIN cxc  USE INDEX (idx_fecha) ON cxc.cliente = cli.id WHERE cxc.fecha < '$FechaI' 
        AND cli.id > 0 AND cli.tipodepago NOT REGEXP 'Contado|Puntos' GROUP BY cli.id
        UNION ALL
        SELECT cli.id, cxc.cliente,0 inicial, ROUND( SUM( IF(tm = 'C',0,importe) ), 2) abono, 
        ROUND( SUM( IF(tm = 'C',importe,0) ), 2) cargo FROM cxc USE INDEX (idx_fecha) INNER JOIN 
        cli ON cxc.cliente = cli.id WHERE cxc.fecha BETWEEN DATE('$FechaI') AND DATE('$FechaF') 
            AND cxc.cliente > 0 AND cli.tipodepago NOT REGEXP 'Contado|Puntos' GROUP BY cli.id
    ) C ON C.cliente = cli.id AND cli.tipodepago NOT REGEXP 'Contado|Puntos'
    WHERE cli.activo = 'Si'
    GROUP by cliente
    ";

if ($Detallado === "No") {
    $selectC_A_S .= "  
    ) rep
    ORDER BY rep.orden,rep.tipodepago,rep.cliente";
} else {
    $selectC_A_S .= "
        HAVING SUM(IFNULL(inicial, 0)) + SUM(IFNULL(cargo, 0)) - SUM(IFNULL(abono, 0)) !=0
    ) rep
    ORDER BY rep.orden,rep.tipodepago,rep.cliente";
}

/* Consultas para reporte de puntos */

$selectPuntos = "
            SELECT rm.id,DATE_FORMAT(rm.fin_venta,'%Y-%m-%d %H:%s') fecha,rm.pesos pesos,rm.volumen volumen,rm.puntos,
            com.descripcion producto,rm.posicion,rm.kilometraje,rm.placas,rm.codigo,
            unidades.impreso,unidades.descripcion,rm.cliente,cli.nombre  
            FROM com,cli,rm LEFT JOIN unidades ON rm.codigo = unidades.codigo 
            WHERE com.clavei = rm.producto AND cli.id = rm.cliente AND rm.cliente = '$Cliente' 
            AND DATE(rm.fin_venta) BETWEEN DATE('$FechaI') AND DATE('$FechaF') 
            AND cli.tipodepago = '" . TiposCliente::PUNTOS . "'
            ORDER BY rm.codigo,rm.id";

$selectPuntosT = "
            SELECT com.descripcion producto,COUNT(*) cargas,SUM(rm.pesos) pesos,SUM(rm.volumen) volumen,SUM(rm.puntos) puntos
            FROM com,rm,cli
            WHERE com.clavei = rm.producto AND cli.id = rm.cliente AND rm.cliente = '$Cliente' 
            AND DATE(rm.fin_venta) BETWEEN DATE('$FechaI') AND DATE('$FechaF') 
            AND cli.tipodepago = '" . TiposCliente::PUNTOS . "'
            GROUP BY rm.producto
            ORDER BY rm.producto DESC";

/* Consulta para cobranza de factuaras */


$selectFacturasPendientes = "
            SELECT cxc.*,p.fecha fechap,p.fecha_deposito,p.serie seriep FROM (
                SELECT 2 sub1,cli.tipodepago,fc.cliente,cli.nombre,fc.folio factura,fc.serie,fc.fecha,
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
            left join pagos p on cxc.recibo = p.id
            WHERE TRUE ";

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

/* Consulta para antiguedad de facturas */

$selectAntiguedad = "
            SELECT cli.id cliente,UPPER(cli.nombre) nombre,cli.tipodepago,
            (sub.importe - (IFNULL(sub_a.importe,0) + IFNULL(sub_b.importe,0) +IFNULL(sub_c.importe,0) + IFNULL(sub_d.importe,0) ) ) activo,
            IFNULL(sub_a.importe,0) a,
            IFNULL(sub_b.importe,0) b,
            IFNULL(sub_c.importe,0) c,
            IFNULL(sub_d.importe,0) d,
            sub.importe total
            FROM cli 
            LEFT JOIN (
                    SELECT cxc.cliente,ROUND(SUM(IF(cxc.tm = 'C',cxc.importe,-cxc.importe)),2) importe FROM cxc 
                    GROUP BY cxc.cliente
                    ORDER BY cxc.cliente
            ) sub ON cli.id = sub.cliente
            LEFT JOIN (
                    SELECT sub_a.cliente,SUM(sub_a.importe) importe
                    FROM (
                        SELECT sub.fecha,sub.cliente,ROUND(SUM(sub.importe),2) importe
                        FROM (
                            SELECT cxc.cliente,cxc.fecha,cxc.factura,IF(cxc.tm = 'C',cxc.importe,-cxc.importe) importe
                            FROM cxc
                            WHERE (cxc.placas LIKE '%Factura%' OR cxc.concepto LIKE '%Factura%' OR cxc.factura > 0)
                            AND cxc.fecha <= DATE('$Fecha')
                            ORDER BY cxc.cliente, cxc.factura
                        ) sub
                        GROUP BY sub.cliente,sub.factura
                    ) sub_a 
                    WHERE ABS(sub_a.importe) > 0
                    AND sub_a.fecha >= DATE_SUB(CURDATE(),INTERVAL 7 DAY)
                    GROUP BY sub_a.cliente
            ) sub_a ON cli.id = sub_a.cliente
            LEFT JOIN (
                    SELECT sub_a.cliente,SUM(sub_a.importe) importe
                    FROM (
                        SELECT sub.fecha,sub.cliente,ROUND(SUM(sub.importe),2) importe
                        FROM (
                            SELECT cxc.cliente,cxc.fecha,cxc.factura,IF(cxc.tm = 'C',cxc.importe,-cxc.importe) importe
                            FROM cxc
                            WHERE (cxc.placas LIKE '%Factura%' OR cxc.concepto LIKE '%Factura%' OR cxc.factura > 0)
                            AND cxc.fecha <= DATE('$Fecha')
                            ORDER BY cxc.cliente, cxc.factura
                        ) sub
                        GROUP BY sub.cliente,sub.factura
                    ) sub_a 
                    WHERE ABS(sub_a.importe) > 0
                    AND sub_a.fecha BETWEEN DATE_SUB(CURDATE(),INTERVAL 15 DAY) AND DATE_SUB(CURDATE(),INTERVAL 8 DAY)
                    GROUP BY sub_a.cliente
            ) sub_b ON cli.id = sub_b.cliente
            LEFT JOIN (
                    SELECT sub_a.cliente,SUM(sub_a.importe) importe
                    FROM (
                        SELECT sub.fecha,sub.cliente,ROUND(SUM(sub.importe),2) importe
                        FROM (
                            SELECT cxc.cliente,cxc.fecha,cxc.factura,IF(cxc.tm = 'C',cxc.importe,-cxc.importe) importe
                            FROM cxc
                            WHERE (cxc.placas LIKE '%Factura%' OR cxc.concepto LIKE '%Factura%' OR cxc.factura > 0)
                            AND cxc.fecha <= DATE('$Fecha')
                            ORDER BY cxc.cliente, cxc.factura
                        ) sub
                        GROUP BY sub.cliente,sub.factura
                    ) sub_a 
                    WHERE ABS(sub_a.importe) > 0
                    AND sub_a.fecha BETWEEN DATE_SUB(CURDATE(),INTERVAL 21 DAY) AND DATE_SUB(CURDATE(),INTERVAL 16 DAY)
                    GROUP BY sub_a.cliente
            ) sub_c ON cli.id = sub_c.cliente
            LEFT JOIN (
                    SELECT sub_a.cliente,SUM(sub_a.importe) importe
                    FROM (
                        SELECT sub.fecha,sub.cliente,ROUND(SUM(sub.importe),2) importe
                        FROM (
                            SELECT cxc.cliente,cxc.fecha,cxc.factura,IF(cxc.tm = 'C',cxc.importe,-cxc.importe) importe
                            FROM cxc
                            WHERE (cxc.placas LIKE '%Factura%' OR cxc.concepto LIKE '%Factura%' OR cxc.factura > 0)
                            AND cxc.fecha <= DATE('$Fecha')
                            ORDER BY cxc.cliente, cxc.factura
                        ) sub
                        GROUP BY sub.cliente,sub.factura
                    ) sub_a 
                    WHERE ABS(sub_a.importe) > 0
                    AND sub_a.fecha <= DATE_SUB(CURDATE(),INTERVAL 22 DAY)
                    GROUP BY sub_a.cliente
            ) sub_d ON cli.id = sub_d.cliente
            WHERE cli.tipodepago NOT REGEXP 'Contado|Puntos'
            AND cli.activo = 'Si'
            
        ";

/* Consultas para detalle de antiguedad de saldos */

$selectAntiguedadConsumos = "        
        SELECT man.isla_pos, cli.nombre, cli.tipodepago, rm.corte,rm.cliente, rm.id, rm.fin_venta fecha, rm.pagoreal pesos, rm.pesos importe, rm.volumen,
            com.descripcion producto, rm.posicion, rm.kilometraje, 
            IF(cxc.placas = '-',UPPER(rm.placas),UPPER(cxc.placas)) placas, 
            CASE 
                WHEN LENGTH(TRIM(rm.codigo)) = 20 THEN rm.codigo
                WHEN LENGTH(TRIM(rm.codigo)) = 10 OR LENGTH(TRIM(rm.codigo)) > 20 THEN 'Vales'
                ELSE ''
            END codigo, 
            IF(unidades.impreso IS NULL,'" . FcDAO::SIN_TIMBRAR . "',unidades.impreso) impreso, 
            IF(unidades.descripcion IS NULL,'" . FcDAO::SIN_TIMBRAR . "',unidades.descripcion) descripcion,
            rm.uuid
        FROM cli,com ,ct, man, rm 
        LEFT JOIN unidades ON rm.codigo = unidades.codigo 
        LEFT JOIN cxc ON rm.id = cxc.referencia
        WHERE 1 =1 AND rm.cliente = cli.id AND rm.producto = com.clavei AND ct.id = rm.corte AND man.posicion = rm.posicion AND man.activo = 'Si'
        AND rm.cliente > 0 AND rm.tipo_venta = 'D' AND rm.uuid = '" . FcDAO::SIN_TIMBRAR . "' AND rm.cliente = '$SCliente'
        GROUP BY rm.id 
        ORDER BY rm.cliente,rm.id               
        ";

$selectAntiguedadConsumosTotalesByProducto = "
        SELECT com.descripcion producto , COUNT(*) consumos, SUM(rm.volumen) cantidad, SUM(rm.pagoreal) importe
        FROM cli, man, rm, com ,ct
        WHERE 1 =1 AND rm.cliente = cli.id AND man.posicion = rm.posicion AND rm.producto = com.clavei AND rm.corte = ct.id 
        AND  rm.tipo_venta = 'D'
        AND rm.cliente > 0 AND rm.uuid = '" . FcDAO::SIN_TIMBRAR . "' AND rm.cliente = '$SCliente'
        GROUP BY rm.producto 
        
        UNION ALL 
        
        SELECT vt.descripcion, COUNT(vt.id) consumos, SUM(vt.cantidad) cantidad, SUM(vt.total) importe
        FROM vtaditivos vt, man
        WHERE 1=1 AND vt.posicion = man.posicion AND vt.tm = 'C' AND vt.cliente > 0
        AND vt.uuid = '" . FcDAO::SIN_TIMBRAR . "' AND vt.cliente = '$SCliente'
        GROUP BY vt.clave";

/* Consulta para reporte de Cargas */

$selectCargas = "
        SELECT * FROM(
            SELECT COUNT(*) cargas,DATE(rm.fin_venta) fecha,rm.cliente,cli.nombre,SUM(volumen) litros,SUM( pesos ) pesos,
            SUM( IF( ABS(pesos - pagoreal) > 1, pagoreal/precio, volumen) ) volumencalcu, SUM(pagoreal) pagoreal
            FROM rm LEFT JOIN cli ON rm.cliente = cli.id 
            WHERE DATE( rm.fin_venta ) BETWEEN DATE('$FechaI') AND DATE('$FechaF') AND rm.cliente > 0 AND rm.tipo_venta = 'D'
            GROUP BY rm.cliente,DATE(rm.fin_venta) 
            ORDER BY rm.cliente 
        ) sub1
        UNION 
        SELECT * FROM (
            SELECT COUNT(*) cargas,DATE(ct.fecha) fecha, cttarjetas.banco cliente,cli.nombre,'' litros,SUM(cttarjetas.importe) pesos,0 volumencalcu, 0 pagoreal
            FROM ct, cttarjetas LEFT JOIN cli ON cttarjetas.banco = cli.id
            WHERE DATE( ct.fecha ) BETWEEN DATE('$FechaI') AND DATE('$FechaF')
            AND ct.id = cttarjetas.id
            GROUP BY cttarjetas.banco,ct.fecha
            ORDER BY cttarjetas.banco,ct.fecha
        ) sub2";

/* Consulta para obtener bitacora de eventos */

$selectBitacora = "
        SELECT id_bitacora numero_evento, fecha_evento, hora_evento, usuario, descripcion_evento 
        FROM bitacora_eventos 
        WHERE TRUE AND DATE( bitacora_eventos.fecha_evento ) BETWEEN DATE('$FechaI') AND DATE('$FechaF') ";
if (!empty($Descripcion)) {
    $selectBitacora .= "AND (descripcion_evento REGEXP '$Descripcion' OR usuario REGEXP '$Descripcion' OR tipo_evento REGEXP '$Descripcion')";
}