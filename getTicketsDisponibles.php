<?php

header("Cache-Control: no-cache,no-store");
include_once ("libnvo/lib.php");
include_once ("data/BitacoraDAO.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$usuarioSesion = getSessionUsuario();
$BitacoraDAO = new BitacoraDAO();
$jsonString = Array();
$jsonString["success"] = false;
$jsonString["message"] = "Sin proceso registrado";
$Op = $request->getAttribute("Op");
/* Archivo JS para estas vistas */
$Html = "<script type='text/javascript' src='js/ticketsTraslate.js'></script>";
switch ($Op) {
    case "ObtenerTickets":
        $ClienteIngreso = $request->getAttribute("ClienteIngreso");
        $ClienteExtraccion = explode("|", $request->getAttribute("ClienteExtraccion"));
        $Producto = $request->getAttribute("Producto");
        $Importe = $request->getAttribute("Importe");
        $Fecha = $request->getAttribute("Fecha");
        $Corte = $request->getAttribute("Corte");
        if ($Importe > 0) {
            $SqlAdd .= " AND rm.importe <=  '" . $Importe . "' ";
        }
        if ($Corte > 0) {
            $SqlAdd .= " AND rm.corte = '$Corte' ";
        }
        if (strlen($Fecha) > 1) {
            $Fecha = date('Ymd', strtotime($Fecha));
            $SqlAdd .= " AND rm.fecha_venta = '$Fecha' ";
        }
        if (strlen($Producto) >= 1) {
            $SqlAdd .= " AND rm.producto = '$Producto' ";
        }
        if ($ClienteExtraccion[0] > 20) {
            $SqlAdd .= " AND rm.cliente= $ClienteExtraccion[0] ";
        } else {
            $SqlAdd .= " AND rm.cliente= 0 ";
        }
        $SqlTicketLibre = "SELECT rm.id,com.descripcion producto,rm.inicio_venta fecha,rm.volumen,rm.importe,rm.corte,cli.nombre FROM ct 
        LEFT JOIN rm ON rm.corte=ct.id 
        LEFT JOIN com ON com.clavei=rm.producto 
        LEFT JOIN cli ON cli.id=rm.cliente
        WHERE statusctv='Abierto' and rm.id > 0 AND rm.importe > 0 AND LENGTH(com.descripcion) > 0
        AND uuid = '-----' $SqlAdd ORDER BY rm.corte,com.descripcion,rm.importe;";
        error_log($SqlTicketLibre);
        $STicket = utils\IConnection::getRowsFromQuery($SqlTicketLibre);
        $Html .= "<table style='width:100%;border:1px solid #929292;border-radius:4px;'><tr style='background-color:#ff6633;color:white;font-size:14px;font-weight: bold;'><td>Id</td><td>Producto</td><td>Cliente</td><td>Fecha</td><td>Volumen</td><td>Importe</td><td>Corte</td><td></td></tr>";
        $e = 0;
        foreach ($STicket as $St) {
            $Clr = $e % 2 == 0 ? "#F8F8F8" : "#E6E6E6";
            $Html .= "<tr style='background-color:$Clr'>";
            $Html .= "<td style='text-align:right;padding-right:10px;'>" . $St["id"] . "</td>";
            $Html .= "<td>" . $St["producto"] . "</td>";
            $Html .= "<td>" . $St["nombre"] . "</td>";
            $Html .= "<td>" . $St["fecha"] . "</td>";
            $Html .= "<td style='text-align:right;padding-right:10px;'>" . number_format($St["volumen"], 2) . "</td>";
            $Html .= "<td style='text-align:right;padding-right:10px;'>" . number_format($St["importe"], 2) . "</td>";
            $Html .= "<td style='text-align:right;padding-right:10px;'>" . $St["corte"] . "</td>";
            $Html .= "<td style='text-align:center'><input type='checkbox' name='CheckRm' class='botonAnimatedMin' value='" . $St["id"] . "' data-import='" . $St["importe"] . "'></td>";
            $Html .= "</tr>";
            $e++;
        }
        $Html .= "</table>";
        echo $Html;
        break;
    case "LanzarProcesoPorTicket":
        $SelectVenta = "SELECT volumen,importe,corte,producto,cliente FROM rm WHERE rm.id = " . $request->getAttribute("Ticket");
        $Sv = utils\IConnection::execSql($SelectVenta);
        $SelectCli = "SELECT tipodepago FROM cli WHERE id = " . $request->getAttribute("IdAuth");
        $Scl = utils\IConnection::execSql($SelectCli);
        $UpdateRm = "UPDATE rm SET cliente = " . $request->getAttribute("IdAuth") . ","
                . "tipodepago='" . $Scl["tipodepago"] . "' WHERE id = " . $request->getAttribute("Ticket");
        utils\IConnection::execSql($UpdateRm);
        $BuscaExistenciaCxc = "SELECT * FROM cxc WHERE tm='C' AND referencia=" . $request->getAttribute("Ticket") . " AND producto != 'A';";
        $Vl = utils\IConnection::execSql($BuscaExistenciaCxc);
        if ($Vl["id"] > 0) {
            $MvCxc = "UPDATE cxc SET cliente = " . $request->getAttribute("IdAuth") . " WHERE id = " . $Vl["id"];
            $ClienteAnt = 0;
        } else {
            $MvCxc = "INSERT INTO cxc (cliente,placas,referencia,fecha,hora,tm,concepto,cantidad,"
                    . "importe,recibo,corte,producto,rubro,factura) VALUES "
                    . "(" . $request->getAttribute("IdAuth") . " ,'-----'," . $request->getAttribute("Ticket") . ",'" . date("Y-m-d") . "','" . date("H:i:s") . "',"
                    . "'C','Venta de combustible Asig.','" . $Sv["volumen"] . "','" . $Sv["importe"] . "',0," . $Sv["corte"] . ","
                    . "'" . $Sv["producto"] . "','-----',0);";
            $ClienteAnt = $Vl["cliente"];
        }
        utils\IConnection::execSql($MvCxc);
        $BitacoraDAO->saveLogSn($request->getAttribute("AuthName"), "ADM", "Mueve ticket no " . $request->getAttribute("Ticket") . " Proceso: transferencia de tickets,C.Ant." . $Sv["cliente"] . " C.Asig." . $request->getAttribute("IdAuth"));
        break;
}

//echo json_encode($jsonString);
