<?php

header("Cache-Control: no-cache,no-store");
$wsdl = 'http://localhost:9080/DetiPOS/detisa/services/DetiPOS?wsdl';

include_once ("libnvo/lib.php");
include_once ("data/VentaAditivosDAO.php");
include_once ("data/Env_efectivodDAO.php");
include_once ("data/ProductoDAO.php");
include_once ("data/ManDAO.php");
include_once ("data/CtDAO.php");
include_once ("data/FcDAO.php");
include_once ("data/BitacoraDAO.php");
include_once ("data/CxcMensualDAO.php");
include_once ("data/RelacionCfdiDAO.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();

$dt = $request->getAttribute("Var");
$mysqli = iconnect();
$jsonString = Array();
$display = Array();
$jsonString["success"] = false;
$jsonString["message"] = "";
if ($request->getAttribute("Origen") === "GetRegimenFiscales") {
    $numCaracteres = mb_strlen($request->getAttribute("Var"), 'UTF-8');
    if ($numCaracteres >= 13) {
        $Sql = "SELECT clave,descripcion FROM omicrom.cfdi33_c_regimenes WHERE tipo in (1,3) AND status = 1;";
    } else if (mb_strlen($request->getAttribute("Var"), 'UTF-8') === 12) {
        $Sql = "SELECT clave,descripcion FROM omicrom.cfdi33_c_regimenes WHERE tipo in (2,3) AND status = 1;";
    }
    $display = utils\IConnection::getRowsFromQuery($Sql);
} else if ($request->getAttribute("Origen") === "GetProducto") {
    $Sql = "SELECT descripcion FROM inv WHERE id = " . $request->getAttribute("IdProducto");
    $display[0] = utils\IConnection::getRowsFromQuery($Sql);
    $Sql = "SELECT id FROM ct WHERE fecha BETWEEN '" . $request->getAttribute("FechaInicial") . "' AND '" . $request->getAttribute("FechaFin") . "';";
    $display[1] = utils\IConnection::getRowsFromQuery($Sql);
    if ($display[1][0] == "") {
        $Sql = "SELECT id FROM ct WHERE  fecha < '" . $request->getAttribute("FechaInicial") . "' ORDER BY id DESC LIMIT 1;";
        $display[1] = utils\IConnection::getRowsFromQuery($Sql);
    }
} else if ($request->getAttribute("Origen") === "AgregaProducto") {
    $productoDAO = new ProductoDAO();
    $productoVO = new ProductoVO();
    $productoVO = $productoDAO->retrieve($request->getAttribute("IdProducto"));
    $vtAdtitivosDAO = new VentaAditivosDAO();
    $vtAdtitivosVO = new VentaAditivosVO();
    $manDAO = new ManDAO();
    $manVO = new ManVO();
    $ctDAO = new CtDAO();
    $ctVO = new CtVO();
    $manVO = $manDAO->retrieve($request->getAttribute("Posicion"), "isla_pos", true);
    $ctVO = $ctDAO->retrieve($request->getAttribute("Corte"));
    $vtAdtitivosVO->setProducto($request->getAttribute("IdProducto"));
    $vtAdtitivosVO->setCantidad($request->getAttribute("Cantidad"));
    $vtAdtitivosVO->setUnitario($productoVO->getPrecio());
    $vtAdtitivosVO->setTotal($productoVO->getPrecio() * $request->getAttribute("Cantidad"));
    $vtAdtitivosVO->setCorte($request->getAttribute("Corte"));
    $vtAdtitivosVO->setPosicion($manVO->getPosicion());
    $vtAdtitivosVO->setFecha($ctVO->getFecha());
    $vtAdtitivosVO->setDescripcion(substr($productoVO->getDescripcion(), 0, 45));
    $vtAdtitivosVO->setCliente(0);
    $vtAdtitivosVO->setReferencia(0);
    $vtAdtitivosVO->setCosto($productoVO->getCosto());
    $vtAdtitivosVO->setIva(0.16);
    $vtAdtitivosVO->setVendedor($manVO->getDespachador());
    $vtAdtitivosVO->setTm("C");
    $vtAdtitivosVO->setComentarios("Ajuste por " . $request->getAttribute("idUser") . " .-" . $request->getAttribute("Name"));

    if ($id = $vtAdtitivosDAO->create($vtAdtitivosVO)) {
        $display[0] = "Proceso exitoso";
        error_log("RESPUESTA :" . print_r($display, true));
    }
} else if ($request->getAttribute("Origen") === "InsertEnvios") {
    $EnvEfectivodDAO = new Env_efectivodDAO();
    $EnvEfectivodVO = new Env_efectivodVO();
    $Sql = "select ct.id,ct.fecha,egr.imp from ct "
            . "LEFT JOIN (select sum(importe)-IFNULL(eed.monto,0) imp,corte FROM egr left join 
                (SELECT id_corte,SUM(monto) monto FROM env_efectivod WHERE id_corte='" . $request->getAttribute("Id_corte") . "') eed on 
                eed.id_corte=egr.corte  WHERE tm='C' and corte = '" . $request->getAttribute("Id_corte") . "'  GROUP BY corte)  egr ON "
            . "egr.corte=ct.id "
            . "where id =" . $request->getAttribute("Id_corte");

    $rsEf = utils\IConnection::execSql($Sql);
    $EnvEfectivodVO->setId_ee($request->getAttribute("Id_ee"));
    $EnvEfectivodVO->setMonto($rsEf["imp"]);
    $EnvEfectivodVO->setId_corte($request->getAttribute("Id_corte"));
    if ($EnvEfectivodDAO->create($EnvEfectivodVO)) {
        $SqlB = "SELECT if(sum(eed.monto)>ee.importe,sum(eed.monto)-ee.importe,0) dif, ROUND(sum(eed.monto),2) total FROM env_efectivod eed "
                . "LEFT JOIN env_efectivo ee ON eed.id_ee = ee.id where id_ee = " . $request->getAttribute("Id_ee");
        $Difrs = utils\IConnection::execSql($SqlB);
        $Upd = "UPDATE env_efectivo ee LEFT JOIN 
                                    (SELECT sum(monto) monto,id_ee FROM env_efectivod eed WHERE id_ee = " . $request->getAttribute("Id_ee") . ") eed ON ee.id=eed.id_ee 
                                    SET ee.importe = eed.monto WHERE ee.id= " . $request->getAttribute("Id_ee");
        utils\IConnection::execSql($Upd);

        $display["Rs"] = true;
    }
} else if ($request->getAttribute("Origen") === "EditaEnvio") {
    $Sql = "SELECT id,monto,id_corte,id_ee FROM env_efectivod WHERE id = " . $request->getAttribute("IdIdentifica");
    $rsEnv = utils\IConnection::execSql($Sql);
    $Html = "<form name='form1' method='post' action=''><table class='paginador'><tr><th>Corte</th><th>Importe</th><th>Envio</th><th></th></tr>";
    $Html .= "<tr><td>" . $rsEnv["id_corte"] . "</td><td>" . $rsEnv["monto"] . "</td>"
            . "<td style='text-align:center;'><input type='text' name='NuevoTotal' id='NuevoTotal' style='width:80px;'></td>"
            . "<td><input type='submit' name='Boton' value='Edita'></td></tr></table>"
            . "<input type='hidden' name='IdEnvEf' id='IdEnvEf' value='" . $rsEnv["id"] . "'>"
            . "<input type='hidden' name='BuscaId' value='" . $rsEnv["id_ee"] . "'></form>";
    $display["Rs"] = $Html;
} elseif ($request->getAttribute("Origen") === "GeneraProcesoGrupoG") {
    $Corte = $request->getAttribute("Corte");
    $Val = $request->getAttribute("Value");
    $Exc = "sudo java -jar /home/omicrom/GESInfoGenerator/GESConnector.jar $Corte ALL";
    error_log($Exc);
    exec($Exc);
} elseif ($request->getAttribute("Origen") === "ObtenAcercaDe") {
    $selectAcerca = "SELECT id,nombre,version,md5,status FROM servicios WHERE status = 'Si' ORDER BY nombre;";
    $rows_ = utils\IConnection::getRowsFromQuery($selectAcerca);
    foreach ($rows_ as $value) {
        $display["rows"][] = $value;
    }

    $display["Response"] = true;
} elseif ($request->getAttribute("Origen") === "ActualizaSeries") {
    $display["Msj"] = utils\Messages::MESSAGE_NO_OPERATION;
    $display["Success"] = false;
    $display["img"] = "error";
    $display["color"] = "red";
    $UpdateSerie = "UPDATE variables_corporativo SET valor= '" . $request->getAttribute("Value") . "' "
            . "WHERE llave = '" . $request->getAttribute("Llave") . "';";
    utils\IConnection::execSql($UpdateSerie);
    $Seq = "SELECT valor FROM variables_corporativo WHERE llave = '" . $request->getAttribute("Llave") . "';";
    $Val = utils\IConnection::execSql($Seq);
    if ($Val["valor"] === $request->getAttribute("Value")) {
        $InsertBitacora = "INSERT INTO bitacora_eventos (fecha_eventos,hora_evento,usuario,tipo_evento,descripcion_evento) values "
                . "('" . date("Y-m-d") . "','" . date("h:i:s") . "','" . $request->getAttribute("Usr") . "','" . "','" . "')";
        $display["img"] = "success";
        $display["color"] = "#ABEBC6";
        $display["Msj"] = utils\Messages::MESSAGE_DEFAULT;
        $display["Success"] = true;
    }
} elseif ($request->getAttribute("Op") === "ObtenDetalleFactura") {
    $Sql = "SELECT inv.descripcion,fcd.ticket,FORMAT(fcd.cantidad,2) cantidad,FORMAT(fcd.importe,2) total,
                FORMAT(IF(inv.id>=5,fcd.importe/(1+fcd.iva),(fcd.importe-(fcd.ieps * fcd.cantidad))/(1+fcd.iva)),2) importe,
                FORMAT(fcd.ieps * fcd.cantidad,2) ieps,
                FORMAT(fcd.importe-(IF(inv.id>=5,fcd.importe/(1+fcd.iva),(fcd.importe-(fcd.ieps * fcd.cantidad))/(1+fcd.iva))+(fcd.ieps * fcd.cantidad)),2) sinIva 
                FROM  fcd LEFT JOIN inv ON fcd.producto=inv.id WHERE fcd.id = " . $request->getAttribute("IdBusca");
    $RsSql = utils\IConnection::getRowsFromQuery($Sql);
    $display["Array"] = $RsSql;
} else if ($request->getAttribute("Origen") === "ObtenRespues") {
    $Sql = "SELECT tipo_alarma,descripcion_alarma,componente_alarma FROM alarmas LEFT JOIN bitacora_eventos be  on alarmas.id_bitacora= be.id_bitacora 
                WHERE id_alarma = " . $request->getAttribute("IdAlarma") . " order by id_alarma desc;";
    $RsAlarm = utils\IConnection::execSql($Sql);
    $display["ResultadoAlarma"] = $RsAlarm["tipo_alarma"];
    $display["DescripcionAlarma"] = $RsAlarm["descripcion_alarma"];
    $display["ComponenteAlarma"] = $RsAlarm["componente_alarma"];
} else if ($request->getAttribute("Origen") === "AgregaObservacion") {
    $idAlarma = $request->getAttribute("IdAlarma");
    $Solucion = $request->getAttribute("DescripcionEvento");
    $usr = $request->getAttribute("Usr");
    $Insert = "INSERT INTO res_alarmas (id_alarma, solucion, id_user,origen) VALUES ($idAlarma,'$Solucion',$usr,2);";
    if ($mysqli->query($Insert)) {
        $display["Msj"] = utils\Messages::MESSAGE_DEFAULT;
        $display["Icon"] = utils\Messages::SUCCESSICON;
    } else {
        $display["Msj"] = $mysqli->error;
        $display["Icon"] = utils\Messages::SUCCESSICON;
    }
} else if ($request->getAttribute("Origen") === "LiberaTickets") {
    $cId = $request->getAttribute("IdFc");
    $FcDAO = new FcDAO();
    $FcVO = new FcVO();
    $BitacoraDAO = new BitacoraDAO();
    $BitacoraVO = new BitacoraVO();
    $BitacoraDAO->saveLogSn($request->getAttribute("Usr"), "ADM", "Factura [$cId] Liberación anticipada de ticket");
    $FcVO = $FcDAO->retrieve($cId);
    $updateRm2 = "UPDATE rm,fc SET rm.uuid = '-----' WHERE rm.uuid = fc.uuid AND fc.id = $cId;";
    utils\IConnection::execSql($updateRm2);
    $updateRm3 = "UPDATE fcd SET ticket = -abs(ticket) WHERE abs(id) = $cId";
    utils\IConnection::execSql($updateRm3);
    $updateRm4 = "UPDATE rm SET uuid = '-----' WHERE uuid = '" . $FcVO->getUuid() . "';";
    utils\IConnection::execSql($updateRm4);
    $display["Msj"] = utils\Messages::MESSAGE_DEFAULT;
} else if ($request->getAttribute("Origen") === "AgregaObservacionBitacora") {
    $display["Msj"] = "Registro agregado con exito";
    $idAlarma = $request->getAttribute("IdAlarma");
    $Solucion = $request->getAttribute("DescripcionEvento");
    $usr = $request->getAttribute("Usr");

    $IfCreate = "SELECT id FROM res_alarmas WHERE id_alarma = " . $idAlarma;
    $RsIf = utils\IConnection::execSql($IfCreate);

    if ($RsIf["id"] > 0) {
        $Sqlt = "UPDATE res_alarmas SET solucion = '$Solucion' WHERE id_alarma=$idAlarma AND origen = 1;";
    } else {
        $Sqlt = "INSERT INTO res_alarmas (id_alarma, solucion, id_user,origen) VALUES ($idAlarma,'$Solucion',$usr,1);";
    }
    $RsValue = $mysqli->query($Sqlt);
    $display["Error"] = false;
    if ($mysqli->error !== "") {
        error_log("Entramos en error");
        $display["Error"] = true;
        $display["Msj"] = $mysqli->error;
    }
    $BitacoraDAO = new BitacoraDAO();
    $BitacoraVO = new BitacoraVO();
    $AuthU = "SELECT uname FROM authuser WHERE id = " . $usr;
    $AuthUR = utils\IConnection::execSql($AuthU);
    $BitacoraDAO->saveLogSn($AuthUR["uname"], "ADM", "Se agrega respuesta a la bitacora no. $idAlarma");
} elseif ($request->getAttribute("Origen") === "BuscaBitacora") {
    $Sql = "SELECT solucion FROM res_alarmas WHERE id_alarma = " . $request->getAttribute("IdAlarma") . " AND origen = 1";
    $Cut = utils\IConnection::execSql($Sql);
    $display["Solucion"] = $Cut["solucion"];
} elseif ($request->getAttribute("Origen") === "AbirTurno") {
    $Sql = "SELECT statusctv FROM ct WHERE id = " . $request->getAttribute("idCorte");
    $RSql = utils\IConnection::execSql($Sql);
    $display["Success"] = false;
    $display["Icon"] = "error";
    $display["Timer"] = 2000;
    if ($RSql["statusctv"] === "Cerrado") {
        $Update = "UPDATE ct SET statusctv='Abierto'  WHERE id = " . $request->getAttribute("idCorte");
        $BitacoraDAO = new BitacoraDAO();
        $BitacoraVO = new BitacoraVO();

        $BitacoraDAO->saveLogSn($request->getAttribute("User"), "ADM", "Actualiza status ctv de cortes a Abierto, corte no." . $request->getAttribute("idCorte"));
        $Msj = utils\Messages::MESSAGE_DEFAULT;
        utils\IConnection::execSql($Update);
        $display["Success"] = true;
        $display["Icon"] = "success";
        $display["Timer"] = 1000;
    } elseif ($RSql["statusctv"] === "Abierto") {
        $Msj = utils\Messages::MESSAGE_NO_OPERATION;
        $display["Icon"] = "warning";
    } else {
        $Msj = "No se encontro el corte no. " . $request->getAttribute("idCorte");
    }
    $display["Msj"] = $Msj;
} elseif ($request->getAttribute("Op") === "ObtenDetalleEnvio") {
    error_log(print_r($request, true));
    $Sql = "SELECT id_corte Corte,FORMAT(monto,2) Enviado,fecha FechaCorte FROM env_efectivod 
LEFT JOIN ct ON id_corte = ct.id WHERE id_ee = " . $request->getAttribute("IdBusca") . " ORDER BY id_corte DESC";
    error_log($Sql);
    $RsSql = utils\IConnection::getRowsFromQuery($Sql);
    $display["Array"] = $RsSql;
} elseif ($request->getAttribute("Op") === "CalculaBonificacion") {
    $MontoP = utils\IConnection::execSql("SELECT monto_promocion FROM periodo_puntos WHERE tipo_periodo ='A';");
    error_log($request->getAttribute("CntPuntos") . " / " . $MontoP["monto_promocion"]);
    $ImporteEnPuntos = number_format($request->getAttribute("CntPuntos") / $MontoP["monto_promocion"], 2);
    error_log($ImporteEnPuntos);
    $ImporteTicket = utils\IConnection::execSql("SELECT importe FROM rm WHERE id = " . $request->getAttribute("Ticket"));
    $Total = number_format($ImporteTicket["importe"] - $ImporteEnPuntos, 2);
    $Html = "<table style='width:100%;margin-top:15px;background-color:#D5D8DC;border-radius:5px;' class='texto_tablas'><tr><th>Id</th><th>Importe</th><th>Descuento</th><th>Total</th></tr>";
    $Html .= "<tr><td>" . $request->getAttribute("Ticket") . "</td><td>" . number_format($ImporteTicket["importe"], 2)
            . "</td><td>$ImporteEnPuntos</td><td>$Total</td></tr></table>";
    $display["Html"] = $Html;
} elseif ($request->getAttribute("Op") === "IngresaBonificacion") {
    $VerificaIngreso = utils\IConnection::execSql("select SUM(puntos) pts from cobranza_beneficios WHERE id_ticket_beneficio = " . $request->getAttribute("Ticket"));
    if (!($VerificaIngreso["pts"] > 0)) {
        $MontoP = utils\IConnection::execSql("SELECT monto_promocion FROM periodo_puntos WHERE tipo_periodo ='A';");
        $ImporteEnPuntos = number_format($request->getAttribute("CntPuntos") / $MontoP["monto_promocion"], 2);
        $RestarPuntos = "SELECT id, puntos, consumido, id_unidad, puntos - consumido restantes "
                . "FROM beneficios WHERE id_unidad = " . $request->getAttribute("IdUnidad") . " "
                . "AND puntos > consumido ORDER BY id ASC;";
        $rPp = utils\IConnection::getRowsFromQuery($RestarPuntos);
        $sumP = 0;
        $TtPuntos = $request->getAttribute("CntPuntos");
        $Corte = true;
        foreach ($rPp as $rs) {
            if ($sumP < $TtPuntos) {
                if ($Corte) {
                    $ValCom = $sumP + $rs["restantes"];
                    if ($ValCom < $TtPuntos) {
                        $Puntos_restantes = $rs["restantes"];
                        $sumP += $rs["restantes"];
                    } else {
                        $Puntos_restantes = $TtPuntos - $sumP;
                        $Corte = false;
                    }
                    $UpdateBonificacion = "UPDATE beneficios SET consumido = consumido + $Puntos_restantes WHERE id  = " . $rs["id"];
                    $Insrt = "INSERT cobranza_beneficios  (id_beneficio,puntos,id_ticket_beneficio) VALUES ('" . $rs["id"] . "',$Puntos_restantes," . $request->getAttribute("Ticket") . ")";
                    utils\IConnection::execSql($Insrt);
                    utils\IConnection::execSql($UpdateBonificacion);
                }
            }
        }
        $UpdateRm = "UPDATE rm SET descuento = '$ImporteEnPuntos' WHERE id = '" . $request->getAttribute("Ticket") . "'";
        error_log($UpdateRm);
        utils\IConnection::execSql($UpdateRm);
    } else {
        $display["Msj"] = "Error el ticket que ingreso ya se a bonificado";
    }
} elseif ($request->getAttribute("Op") === "Saldo") {
    $UnidadSaldo = "SELECT importe FROM unidades WHERE id = " . $request->getAttribute("IdUnidad");
    $RsUn = utils\IConnection::execSql($UnidadSaldo);
    $display["ImporteUnidad"] = "Importe : " . $RsUn["importe"] . " ";
} elseif ($request->getAttribute("Op") === "TransfiereSaldo") {
    $Insrt = "INSERT INTO unidades_log (noPago,importeAnt,importe,importeDelPago,idUnidad,usr) 
                SELECT 0,importe, importe - " . $request->getAttribute("ImpTransf") . "," . $request->getAttribute("ImpTransf") . ","
            . $request->getAttribute("IdQuita") . ",'" . $request->getAttribute("Usr") . "' FROM unidades WHERE id = " . $request->getAttribute("IdQuita");
    utils\IConnection::execSql($Insrt);
    $Update = "UPDATE unidades SET importe = importe - " . $request->getAttribute("ImpTransf") . " WHERE id = " . $request->getAttribute("IdQuita");
    utils\IConnection::execSql($Update);

    $Insrt = "INSERT INTO unidades_log (noPago,importeAnt,importe,importeDelPago,idUnidad,usr) 
                SELECT 0,importe, importe + " . $request->getAttribute("ImpTransf") . "," . $request->getAttribute("ImpTransf") . ","
            . $request->getAttribute("IdPone") . ",'" . $request->getAttribute("Usr") . "' FROM unidades WHERE id = " . $request->getAttribute("IdPone");
    utils\IConnection::execSql($Insrt);
    $Update = "UPDATE unidades SET importe = importe + " . $request->getAttribute("ImpTransf") . " WHERE id = " . $request->getAttribute("IdPone");
    utils\IConnection::execSql($Update);
    $BitacoraDAO = new BitacoraDAO();
    $BitacoraVO = new BitacoraVO();
    $BitacoraDAO->saveLogSn($request->getAttribute("Usr"), "ADM", "Transferencia de $" . $request->getAttribute("ImpTransf") . ", Unidades " . $request->getAttribute("IdQuita") . " -> " . $request->getAttribute("IdPone"));
} elseif ($request->getAttribute("Op") === "IngresaAbono") {
    $Name = "SELECT name FROM authuser WHERE uname = '" . $request->getAttribute("UsrName") . "';";
    $NSql = utils\IConnection::execSql($Name);

    $ValidaDiferencia = "SELECT p.importe importePago,SUM(ul.importeDelPago) importe,
                p.importe - SUM(ul.importeDelPago) Dif FROM pagos p 
                LEFT JOIN unidades_log ul ON ul.noPago=p.id 
                WHERE p.id = " . $request->getAttribute("IdPago");
    $Vd = utils\IConnection::execSql($ValidaDiferencia);

    if ($Vd["Dif"] >= $request->getAttribute("ImporteAbono") || ($Vd["Dif"] == null && $Vd["importePago"] >= $request->getAttribute("ImporteAbono"))) {
        $display["Sucess"] = true;
        $InsertSelect = "INSERT INTO unidades_log (noPago,importeAnt,importe,importeDelPago,idUnidad,usr) "
                . "SELECT " . $request->getAttribute("IdPago") . ",importe,importe+"
                . $request->getAttribute("ImporteAbono") . "," . $request->getAttribute("ImporteAbono") . ","
                . $request->getAttribute("IdUnidad") . ",'" . $NSql["name"] . "' FROM unidades_log "
                . "WHERE idUnidad=" . $request->getAttribute("IdUnidad") . " ORDER BY id DESC limit 1;";
        utils\IConnection::execSql($InsertSelect);
        $update = "UPDATE unidades SET importe = importe  + " . $request->getAttribute("ImporteAbono") . " WHERE id = " . $request->getAttribute("IdUnidad");
        utils\IConnection::execSql($update);
    } else {
        $display["Msj"] = "Favor de verificar diferencia disponible de " . $Vd["Dif"];
        $display["Sucess"] = false;
    }
} elseif ($request->getAttribute("Op") === "DeleteUL") {
    $UltimoId = "SELECT id FROM unidades_log WHERE idUnidad =  " . $request->getAttribute("IdUnidad") . " ORDER BY id DESC LIMIT 1";
    $UId = utils\IConnection::execSql($UltimoId);
    $display["Sucess"] = false;
    error_log($request->getAttribute("IdLogUnidades") . " === " . $UId["id"]);
    if ($request->getAttribute("IdLogUnidades") === $UId["id"]) {
        $Delete = "DELETE FROM unidades_log WHERE id = " . $request->getAttribute("IdLogUnidades") . " LIMIT 1";
        utils\IConnection::execSql($Delete);

        $UpdateU = "UPDATE unidades SET importe = importe - " . $request->getAttribute("Importe") . " WHERE id=" . $request->getAttribute("IdUnidad");
        utils\IConnection::execSql($UpdateU);
        $display["Sucess"] = true;
    } else {
        $display["Msj"] = "Existen moviemientos despues de este moviemiento, por lo que no se puede modificar.";
    }
} elseif ($request->getAttribute("Op") === "UtimoDictamen") {
    $Sql = "SELECT id FROM omicrom.dictamen order by id desc limit 1";
    $Vl = utils\IConnection::execSql($Sql);
    $display["respuesta"] = $Vl["id"];
} else if ($request->getAttribute("Op") === "EliminaEnvioPromoPorCliente") {
    $Delete = "DELETE FROM envioPromod WHERE idNvo = " . $request->getAttribute("Var");
    error_log($Delete);
    utils\IConnection::execSql($Delete);
} else if ($request->getAttribute("Op") === "LanzamientoDePromo") {
    $Sqld = "SELECT * FROM envioPromod WHERE id = " . $request->getAttribute("Var");
    $rsS = utils\IConnection::getRowsFromQuery($Sqld);
    foreach ($rsS as $rs) {
        $command = 'java -cp /home/omicrom/whatsapp/OMICROMNOTIFIER-1.0.jar com.mx.detisa.InvoceMessageSender '
                . '';
        error_log($command);
    }
} else if ($request->getAttribute("Op") === "CalculaRm") {
    error_log(print_r($request, true));
    $BuscaX = $request->getAttribute("Origen") === "V" ? "id" : "idcxc";
    $SelectRm = "SELECT volumen,importe,uuid,id FROM rm WHERE $BuscaX = " . $request->getAttribute("id_Mov");
    $Vlr = utils\IConnection::execSql($SelectRm);

    if ($Vlr["importe"] == 0 && $Vlr["uuid"] == "-----") {
        $Importe = $request->getAttribute("Value") * $Vlr["volumen"];
        $NewValues = " UPDATE rm SET precio = " . $request->getAttribute("Value") . " , pesos = $Importe, pesosp = $Importe, importe = $Importe WHERE id =" . $Vlr["id"];
        utils\IConnection::execSql($NewValues);
        $display["message"] = "Proceso realisado con exito";
        $display["success"] = true;
    } else {
        $display["message"] = "El ticket seleccionado ya fue modificado";
        $display["success"] = false;
    }
} else if ($request->getAttribute("Op") === "ObtenDetallePagos") {
    $display["Pass"] = true;
    $SelectP = "SELECT pagose.factura,CONCAT(fc.serie,' ',fc.folio) serie ,ROUND(pagose.importe,2) importe, ROUND(pagose.iva,2) iva,ROUND(pagose.ieps,2) ieps,ROUND(pagose.importe + pagose.iva + pagose.ieps,2) total "
            . "FROM pagose LEFT JOIN fc ON pagose.factura = fc.id WHERE pagose.id = " . $request->getAttribute("IdBusca");
    error_log($SelectP);
    $RsSql = utils\IConnection::getRowsFromQuery($SelectP);
    if (empty($RsSql)) {
        $display["Pass"] = false;
    }
    $display["Array"] = $RsSql;
} else if ($request->getAttribute("Op") === "CheckTicket") {
    $busca = $request->getAttribute("idTicket");
    $SqlRm = "SELECT uuid FROM rm WHERE id = $busca";
    $valRm = utils\IConnection::execSql($SqlRm);
    if ($valRm["uuid"] === "-----") {
        $mysqli->query("UPDATE rm SET comprobante = comprobante + 1, enviado = 0 WHERE id = '$busca'");
    } else {
        $mysqli->query("UPDATE rm SET comprobante = comprobante + 1 WHERE id = '$busca'");
    }
    BitacoraDAO::getInstance()->saveLogSn($request->getAttribute("Usr"), "ADM", "IMPRESION DE TICKET " . $busca);

    $display["Pass"] = true;
} else if ($request->getAttribute("Op") === "Ingresa_Cxc") {
    $CxcMensualDAO = new CxcMensualDAO();
    $CxcMensualVO = new CxcMensualVO();

    $CxcMensualVO->setAnio($request->getAttribute("Anio"));
    $CxcMensualVO->setMesNo($request->getAttribute("MesNo"));
    $CxcMensualVO->setMes($request->getAttribute("Mes"));
    $CxcMensualVO->setImporte_deuda($request->getAttribute("Importe"));
    $CxcMensualVO->setId_cli($request->getAttribute("IdCliente"));
    BitacoraDAO::getInstance()->saveLogSn($request->getAttribute("IdUsr"), "ADM", "Inicia saldo incial de  cli id : " . $CxcMensualVO->getId_cli() . " Mes " . $CxcMensualVO->getMes() . " Año " . $CxcMensualVO->getAnio());
    $CxcMensualDAO->create($CxcMensualVO);
} else if ($request->getAttribute("Op") === "ValidaExistencia") {
    $CxcMensualDAO = new CxcMensualDAO();
    $CxcMensualVO = new CxcMensualVO();
    $CxcMensualVO->setAnio($request->getAttribute("Anio"));
    $CxcMensualVO->setMesNo($request->getAttribute("MesNo"));
    $CxcMensualVO->setId_cli($request->getAttribute("IdCliente"));
    $CxcMensualVO = $CxcMensualDAO->retrieve($CxcMensualVO);
    $display["idRegistro"] = $CxcMensualVO->getId();
} else if ($request->getAttribute("Op") === "Actualizar_Cxc") {
    $CxcMensualDAO = new CxcMensualDAO();
    $CxcMensualVO = new CxcMensualVO();
    $CxcMensualVO->setAnio($request->getAttribute("Anio"));
    $CxcMensualVO->setMesNo($request->getAttribute("MesNo"));
    $CxcMensualVO->setMes($request->getAttribute("Mes"));
    $CxcMensualVO->setImporte_deuda($request->getAttribute("Importe"));
    $CxcMensualVO->setId_cli($request->getAttribute("IdCliente"));
    BitacoraDAO::getInstance()->saveLogSn($request->getAttribute("IdUsr"), "ADM", "Modifica saldo incial de  cli id : " . $CxcMensualVO->getId_cli() . " Mes " . $CxcMensualVO->getMes() . " Año " . $CxcMensualVO->getAnio());
    $CxcMensualDAO->update($CxcMensualVO);
} else if ($request->getAttribute("Op") === "ActualizaStatusAuthCli") {
    $id = $request->getAttribute("idAuth");
    $Sts = $request->getAttribute("Status") === "active" ? "inactive" : "active";
    $UpdateAuth = "UPDATE authuser SET status = '$Sts' WHERE id = $id";
    utils\IConnection::execSql($UpdateAuth);
} else if ($request->getAttribute("Op") === "GeneraMovimiento") {
    $id = $request->getAttribute("idAuth");
    $Sts = $request->getAttribute("Status") == 0 ? 1 : 0;
    $UpdateAuth = "UPDATE authuser SET receive_msg = '$Sts' WHERE id = $id";
    error_log($UpdateAuth);
    utils\IConnection::execSql($UpdateAuth);
} else if ($request->getAttribute("Op") === "ValidaFactura") {
    $Sql = "SELECT uuid FROM fc WHERE id = " . $request->getAttribute("Id");
    $rsUuid = utils\IConnection::execSql($Sql);
    $display["Pass"] = $rsUuid["uuid"] === "-----" ? 1 : 0;
} else if ($request->getAttribute("Op") === "BuscaEnvios") {
    $EnviosD = "SELECT  GROUP_CONCAT(id_ee SEPARATOR ', ') AS ids FROM env_efectivod WHERE id_corte = " . $request->getAttribute("idCorte") . " group by id_corte";
    $Env = utils\IConnection::execSql($EnviosD);
    $display["idEnvio"] = $Env["ids"];
} else if ($request->getAttribute("Op") === "RelacionaFcFc") {
    $RelacionCFDIDAO = new RelacionCfdiDAO();
    $RelacionCFDIVO = new RelacionCfdiVO();

    $RelacionActual = "SELECT tipo_relacion FROM relacion_cfdi WHERE id_fc = " . $request->getAttribute("IdFcOrigen") . " LIMIT 1;";
    $ValRA = utils\IConnection::execSql($RelacionActual);
    $RelacionCFDIVO->setSerie($request->getAttribute("Serie"));
    $RelacionCFDIVO->setFolio_factura($request->getAttribute("Folio"));
    $RelacionCFDIVO->setOrigen(2);
    $RelacionCFDIVO->setUuid("-----");
    $RelacionCFDIVO->setUuid_relacionado($request->getAttribute("Uuid"));
    $RelacionCFDIVO->setTipo_relacion(strlen($ValRA["tipo_relacion"]) > 1 ? $ValRA["tipo_relacion"] : "07");
    $RelacionCFDIVO->setImporte(0);
    $RelacionCFDIVO->setId_fc($request->getAttribute("IdFcOrigen"));
    $RelacionCFDIDAO->create($RelacionCFDIVO);
} else if ($request->getAttribute("Op") === "ActualizaRelacionMultiple") {
    $RegistrosRelacionados = "SELECT id FROM relacion_cfdi WHERE id_fc = " . $request->getAttribute("IdRegistro") . " order by id desc";
    $Rr = utils\IConnection::getRowsFromQuery($RegistrosRelacionados);
    $RelacionCFDIDAO = new RelacionCfdiDAO();
    $RelacionCFDIVO = new RelacionCfdiVO();
    foreach ($Rr as $Rsr) {
        $RelacionCFDIVO = $RelacionCFDIDAO->retrieve($Rsr["id"]);
        $RelacionCFDIVO->setTipo_relacion($request->getAttribute("TipoRelacion"));
        $RelacionCFDIDAO->update($RelacionCFDIVO);
    }
} else if ($request->getAttribute("Op") === "RevisaIngresoRegistro") {
    $CntRg = "SELECT COUNT(1) cnt FROM relacion_cfdi WHERE id_fc = " . $request->getAttribute("IdFc") . " ORDER BY id DESC;";
    $Cnt = utils\IConnection::execSql($CntRg);
    error_log($Cnt["cnt"] . " == " . $request->getAttribute("Cnt"));
    $display["Value"] = $Cnt["cnt"] == $request->getAttribute("Cnt") ? false : true;
} else if ($request->getAttribute("Op") === "DetalleDeDepositos") {
    $sql = "SELECT LPAD(ctdep.id, 8, 0) folio,ven.nombre,ctdep.fecha,ctdep.corte,ct.turno,ctdep.total total,ctdep.id
            FROM  ct,ctdep,ven
            WHERE  ct.id = ctdep.corte AND ctdep.despachador = ven.id AND ctdep.corte = " . $request->getAttribute("Corte") . " AND ctdep.despachador= " . $request->getAttribute("Despachador") . ";";
    $Vval = utils\IConnection::getRowsFromQuery($sql);
    $Html = "<tr><td colspan='100%;'><table class='DetallePago'><thead style='font-weight:bold;'><tr><td></td><td>Fecha</td><td>Importe</td></tr></thead>";
    $Html .= "<tbody>";
    foreach ($Vval as $vl) {
        $Html .= "<tr><td style='text-align:center;'>"
                . '<a style="margin-right:30px;" class="textosCualli_i_n" href=javascript:winmin("mdepositos_imp.php?busca=' . $vl["id"] . '&Op=0");><i class="icon fa fa-lg- fa-print" aria-hidden="true"></i></a>'
                . "<a class='textosCualli_i_n' href=javascript:confirmar(\"Deseas_borrar_el_registro_" . $vl["id"] . "?\",\"mdepositos.php?cId=" . $vl["id"] . "&op=Depositos\");><i class='fa-solid fa-trash-can'></i></a>"
                . "</td><td>" . $vl["fecha"] . "</td><td style='text-align:right;'>$ " . number_format($vl["total"], 2) . "</td></tr>";
        $Tt += $vl["total"];
    }
    $Html .= "</tbody>";
    $Html .= "</table></td></tr>";
    $display["Html"] = $Html;
}
echo json_encode($display);
