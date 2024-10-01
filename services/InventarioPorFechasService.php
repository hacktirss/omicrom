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
    utils\HTTPUtils::setSessionValue("FechaIni", date('Y-m-j', strtotime('-1 day', strtotime(date("Y-m-d H:i:s")))));
    utils\HTTPUtils::setSessionValue("FechaFin", date("Y-m-d H:i:s"));
}
if ($request->hasAttribute("FechaIni")) {
    utils\HTTPUtils::setSessionValue("FechaIni", $sanitize->sanitizeString("FechaIni") . " 00:00:00");
}
if ($request->hasAttribute("FechaFin")) {
    utils\HTTPUtils::setSessionValue("FechaFin", $sanitize->sanitizeString("FechaFin") . " 23:59:00");
}
$FechaFin = utils\HTTPUtils::getSessionValue("FechaFin");
$FechaIni = utils\HTTPUtils::getSessionValue("FechaIni");

$Sql = "
    SELECT inv.descripcion,inv.clave_producto,inv.id,inicial.total invI,compras.cnt Compras,ventas.cnt Ventas,final.total invF,agregados.cnt AddCnt  FROM inv
    LEFT JOIN
            (SELECT compras.id producto,IFNULL(compras.cnt,0) - IFNULL(ventas.cnt,0) total FROM 
                (SELECT fecha,producto, cnt,inv.id FROM inv LEFT JOIN (SELECT et.fecha,etd.producto,SUM(etd.cantidad) cnt 
                FROM et LEFT JOIN etd ON et.id = etd.id WHERE etd.producto > 0 AND fecha < '$FechaIni' AND et.status = 'Cerrada' 
                GROUP BY etd.producto) ent ON inv.id = ent.producto) compras 
            LEFT JOIN 
                (SELECT fecha,clave,cnt,inv.descripcion,inv.id FROM inv LEFT JOIN (SELECT fecha,clave,SUM(cantidad) cnt 
                FROM vtaditivos WHERE fecha <= '$FechaIni'  AND tm='C' 
                AND cantidad > 0 GROUP BY clave) vta ON inv.id = vta.clave) ventas 
        ON compras.id=ventas.id order by ventas.id ASC) inicial ON inv.id = inicial.producto 
    LEFT JOIN 
        (SELECT compras.id producto,IFNULL(compras.cnt,0) - IFNULL(ventas.cnt,0) total FROM 
                (SELECT fecha,producto, cnt,inv.id FROM inv LEFT JOIN (SELECT et.fecha,etd.producto,SUM(etd.cantidad) cnt 
                FROM et LEFT JOIN etd ON et.id = etd.id WHERE etd.producto > 0 AND fecha < '$FechaFin' AND et.status = 'Cerrada' 
                GROUP BY etd.producto) ent ON inv.id = ent.producto) compras 
            LEFT JOIN 
                (SELECT fecha,clave,cnt,inv.descripcion,inv.id FROM inv LEFT JOIN (SELECT fecha,clave,SUM(cantidad) cnt 
                FROM vtaditivos WHERE fecha <= '$FechaFin'  AND tm='C'
                AND cantidad > 0 GROUP BY clave) vta ON inv.id = vta.clave) ventas 
        ON compras.id=ventas.id order by ventas.id ASC) final 
    ON inv.id = final.producto
    LEFT JOIN 
            (SELECT et.fecha,etd.producto,SUM(etd.cantidad) cnt FROM 
        et LEFT JOIN etd ON et.id = etd.id WHERE etd.producto > 0 AND fecha BETWEEN '$FechaIni' AND '$FechaFin' 
        AND et.status = 'Cerrada' GROUP BY etd.producto) compras
    ON inv.id = compras.producto
    LEFT JOIN 
            (SELECT fecha,clave,SUM(cantidad) cnt FROM vtaditivos WHERE fecha BETWEEN '$FechaIni' AND '$FechaFin' AND cantidad > 0 AND tm='C' GROUP BY clave) ventas
    ON inv.id = ventas.clave 
    LEFT JOIN (SELECT fecha,clave,SUM(cantidad) cnt FROM vtaditivos 
            WHERE fecha BETWEEN '$FechaIni' AND '$FechaFin' 
            AND cantidad > 0 AND comentarios like '%Ajuste%' GROUP BY clave) agregados
    ON agregados.clave = inv.id
    WHERE rubro = 'Aceites' GROUP BY clave_producto
        ";
$selectBalanceCreate = "CALL omicrom.balance_productos('$FechaIni', '$FechaFin');";
$Tot = utils\IConnection::execSql("SELECT valor FROM variables_corporativo WHERE llave='OrdenReportes'");
$FiltroSql = $Tot["valor"] != "" ? "ORDER BY clave ASC" : "";
$selectBalance = "SELECT b.*,getUMedida(com.cve_producto_sat,com.cve_sub_producto_sat) um "
        . "FROM balance_productos b inner join com on b.clave = com.clave " . $FiltroSql;

abstract class TipoInformacion extends BasicEnum {

    const OMICROM = 1;
    const ARCHIVOS = 2;
    const COMPARATIVO = 3;

}

$cSqlA = "
select inv.clave_producto, inv.descripcion, detalle.*,(factMost+factPublico+porFacturar) Piezas
from inv 
left join (
SELECT  inv.clave_producto, vta.descripcion,vta.cantidad,
    ifnull(sum(case when fc.status = 1 or vta.uuid = '-----'  then vta.total end),0) total,
    ifnull(sum(case when vta.uuid != '-----' and cli.rfc != 'XAXX010101000' OR (cli.rfc = 'XAXX010101000' and cli.nombre not like '%PUB%') then vta.cantidad end),0) factMost,
    ifnull(sum(case when vta.uuid != '-----' and cli.rfc = 'XAXX010101000' and cli.nombre like '%PUB%' then vta.cantidad end),0) factPublico,
    ifnull(sum(case when vta.uuid = '-----'  then vta.cantidad end),0) porFacturar
  FROM vtaditivos vta 
       inner join inv on vta.clave = inv.id
       left join fcd on vta.id = fcd.ticket and producto > 5
       left join fc on fcd.id = fc.id and fc.status = 1
       LEFT JOIN cli ON fc.cliente=cli.id
  WHERE DATE(vta.fecha) BETWEEN DATE('$FechaIni') AND DATE('$FechaFin')  
  AND vta.tm = 'C' AND vta.cantidad > 0 
  group by vta.descripcion  order by cast(vta.clave as decimal ) asc
) as detalle on detalle.clave_producto = inv.clave_producto 
where rubro = 'Aceites'
                ";
