<?php

include_once ("softcoatl/SoftcoatlHTTP.php");
include_once("data/RmDAO.php");
include_once ("data/PedidosDAO.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$jsonString = array();
$PedidosDAO = new PedidosDAO();
$PedidosVO = new PedidosVO();
if ($request->getAttribute("Op") === "BuscaCli") {
    $Select = "SELECT * FROM authuser WHERE name  like '%" . $request->getAttribute("query") . "%' AND team='Cliente';";
    $rows_ = utils\IConnection::getRowsFromQuery($Select, $connection);
    foreach ($rows_ as $value) {
        $jsonString["rows"][$value["id"]] = $value["name"];
    }
} else if ($request->getAttribute("Op") === "AgregaRm") {
    $Cliente1 = $request->getAttribute("Cliente") == "" ? $request->getAttribute("IdFault") : $request->getAttribute("Cliente");
    $dateNow = date("Y-m-d H:i:s");
    $dateAdd = date($request->getAttribute("Fecha"));
    error_log("Fecha actual" . $dateNow . " Fecha antigua " . $dateAdd);
    if ($dateNow <= $dateAdd) {
//        $PedidosVO->setId_cia($request->getAttribute("Id_Cia"));
        $PedidosVO->setVolumen($request->getAttribute("Cantidad"));
        $PedidosVO->setId_user($Cliente1);
        $PedidosVO->setFecha($request->getAttribute("Fecha"));
        $PedidosVO->setFechafin($request->getAttribute("Fecha"));
        $PedidosVO->setProducto($request->getAttribute("Combustible"));
        $PedidosVO->setTerminal_almacenamiento($request->getAttribute("TerminalA"));
        $id = $PedidosDAO->create($PedidosVO);
        $jsonString["rows"]["sts"] = "Exito ";
    } else {
        error_log("Fecha erronea");
        $jsonString["rows"]["sts"] = "Error";
    }
} else if ($request->getAttribute("Op") === "TraeTicket") {
    $PedidosVO = $PedidosDAO->retrieve($request->getAttribute("IdNvo"));
    $SqlRm = "SELECT id FROM rm WHERE idcxc = " . $request->getAttribute("IdNvo");
    $VlRmId = utils\IConnection::execSql($SqlRm);
    $Cli = "SELECT name FROM authuser  WHERE id = " . $PedidosVO->getId_user();
    $CliNombre = utils\IConnection::execSql($Cli);
    $BuscaDetalle = "SELECT CONCAT(id,'. ',llave,' ',descripcion) prm FROM permisos_cre WHERE id = " . $PedidosVO->getTerminal_almacenamiento();
    $PermisoCre = utils\IConnection::execSql($BuscaDetalle);
    $jsonString["rows"]["Id"] = $PedidosVO->getId();
    $jsonString["rows"]["Cliente"] = $CliNombre["name"];
    $jsonString["rows"]["Volumen"] = $PedidosVO->getVolumen();
    $jsonString["rows"]["Inicia"] = $PedidosVO->getFecha();
    $jsonString["rows"]["Producto"] = $PedidosVO->getProducto();
    $jsonString["rows"]["Terminal"] = $PermisoCre["prm"];
    $jsonString["rows"]["IdRm"] = $VlRmId["id"];
    $jsonString["rows"]["Status"] = $PedidosVO->getStatus();
    $jsonString["rows"]["Alerta"] = $PedidosVO->getAlert();
} elseif ($request->getAttribute("Op") === "ActualizaHora") {
    $Stst = "SELECT status FROM pedidos WHERE id = " . $request->getAttribute("IdNvo");
    $Sts = utils\IConnection::execSql($Stst);
    if ($Sts["status"] <= 1) {
        $dateNow = date("Y-m-d H:i:s");
        $dateAdd = date($request->getAttribute("Fecha"));
        if ($dateNow <= $dateAdd) {
            $Update = "UPDATE pedidos SET fecha = '" . $request->getAttribute("Fecha") . "', fechafin='" . $request->getAttribute("FechaF") . "' WHERE id = " . $request->getAttribute("IdNvo");
            utils\IConnection::execSql($Update);
            $jsonString["rows"]["sts"] = "Exito ";
        } else {
            error_log("Fecha erronea");
            $jsonString["rows"]["sts"] = "Error";
        }
    } else {
        $jsonString["rows"]["sts"] = "Error2";
    }
} elseif ($request->getAttribute("Op") === "AumentaHora") {
    $Update = "UPDATE pedidos SET fechafin = '" . $request->getAttribute("Fecha") . "' WHERE id = " . $request->getAttribute("IdNvo");
    utils\IConnection::execSql($Update);
    error_log($Update);
} elseif ($request->getAttribute("Op") === "AceptarPedido") {
    $IdPedido = $request->getAttribute("IdPago");
    $Dts = "SELECT producto,fecha,fechafin,volumen,volumen,id FROM pedidos WHERE id = " . $IdPedido;
    error_log("Pedido" . $Dts);
    $Pd = utils\IConnection::execSql($Dts);
    $IepsCom = "SELECT com.ieps FROM com WHERE com.clavei = '" . $Pd["producto"] . "'";
    $IpsC = utils\IConnection::execSql($IepsCom);
    error_log("IEPS : " . print_r($IpsC, true));
    $RmDAO = new RmDAO();
    $RmVO = new RmVO();
    $RmVO->setPesosp(0);
    $RmVO->setImporte(0);
    $RmVO->setIva(0.16);
    $RmVO->setFactor(0);
    $RmVO->setPesos(0);
    $RmVO->setVdm(0);
    $RmVO->setPrecio(0);
    $RmVO->setVendedor(0);
    $RmVO->setCorte(0);
    $RmVO->setTurno(0);
    $RmVO->setDispensario(0);
    $RmVO->setPosicion(0);
    $RmVO->setManguera(0);
    $RmVO->setDis_mang(0);
    $RmVO->setProducto($Pd["producto"]);
    $RmVO->setInicio_venta($Pd["fecha"]);
    $RmVO->setFin_venta($Pd["fechafin"]);
    $RmVO->setVolumen($Pd["volumen"]);
    $RmVO->setVolumenp($Pd["volumen"]);
    $RmVO->setIeps($IpsC["ieps"]);
    $RmVO->setIdcxc($IdPedido);
    $RmDAO->create($RmVO);
    $RMid = "SELECT id FROM rm WHERE idcxc = " . $IdPedido . " AND dispensario=0;";
    $RmId = utils\IConnection::execSql($RMid);

    $InsertCargas = "INSERT INTO cargas (tanque,producto,clave_producto,t_inicial,vol_inicial,tcVinicial,fecha_inicio,t_final,vol_final,"
            . "tcVfinal,fecha_fin,aumento,tcAumento,fecha_insercion,entrada,inicia_carga,finaliza_carga,tipo,folioenvios,enviado,vol_doc) "
            . "SELECT com.id,com.descripcion,com.clave,0,0,0,P.fecha,0,P.volumen,0,P.fechafin,P.volumen,0,now()," . $RmId["id"] . ",now(),now(),0,0,0,0 "
            . "FROM pedidos P LEFT JOIN com ON P.producto=com.clavei WHERE P.id=" . $request->getAttribute("IdPago");
    utils\IConnection::execSql($InsertCargas);
    $Update = "UPDATE pedidos SET status = 2 WHERE id = " . $request->getAttribute("IdPago");
    utils\IConnection::execSql($Update);
} elseif ($request->getAttribute("Op") === "CancelarPedido") {
    $Update = "UPDATE pedidos SET status = 5 WHERE id = " . $request->getAttribute("IdPago");
    utils\IConnection::execSql($Update);
} elseif ($request->getAttribute("Op") === "BuscaTerminal") {
    $Select = "SELECT id,concat(llave,' ', descripcion) descipcion FROM omicrom.permisos_cre where catalogo='TERMINALES_ALMACENAMIENTO' "
            . "AND (llave  LIKE '%" . $request->getAttribute("query") . "%' OR descripcion LIKE '%" . $request->getAttribute("query") . "%');";
    $rows_ = utils\IConnection::getRowsFromQuery($Select, $connection);
    foreach ($rows_ as $value) {
        $jsonString["rows"][$value["id"]] = $value["descipcion"];
    }
} else if ($request->getAttribute("Op") === "ActualizaCantidadPedido") {
    error_log(print_r($request, true));
    $IdTerminal = explode(".", $request->getAttribute("Terminal"));
    $PedidosVO = $PedidosDAO->retrieve($request->getAttribute("IdPedido"));
    error_log("TERMINAL " . $IdTerminal[0]);
    if ($IdTerminal[0] !== null || $IdTerminal[0] !== "") {
        $PedidosVO->setTerminal_almacenamiento($IdTerminal[0]);
    }
    $PedidosVO->setVolumen($request->getAttribute("Cantidad"));
    $PedidosVO->setAlert(1);
    $PedidosDAO->update($PedidosVO);
}
echo json_encode($jsonString["rows"]);
