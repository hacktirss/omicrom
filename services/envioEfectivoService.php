<?php

#Librerias
include_once ('data/Env_efectivoDAO.php');
include_once ('data/Env_efectivodDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "envioEfectivo.php?";

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $objectVO = new Env_efectivoVO();
    $objectDAO = new Env_efectivoDAO();
    if ($request->getAttribute("Boton") === "Actualizar") {
        $objectVO = $objectDAO->retrieve($request->getAttribute("busca"));
    }
    $objectVO->setDescripcion($request->getAttribute("Descripcion"));
    $objectVO->setFecha_envio($request->getAttribute("FechaEnvio"));
    $objectVO->setId_banco($request->getAttribute("Banco"));
    $objectVO->setImporte($request->getAttribute("Importe"));
    if ($request->getAttribute("Boton") === "Actualizar") {
        $objectDAO->update($objectVO);
    } elseif ($request->getAttribute("Boton") === "Agregar") {
        $objectVO->setStatus("Abierto");
        $objectDAO->create($objectVO);
        $Return .= "Msj=Registro ingresado con exito!";
    } elseif ($request->getAttribute("Boton") === "Edita") {
        $Update = "UPDATE env_efectivod SET monto='" . $request->getAttribute("NuevoTotal") . "' WHERE id = " . $request->getAttribute("IdEnvEf");
        utils\IConnection::execSql($Update);
        TotalizaEnvio($request->getAttribute("BuscaId"));
        $Msj = "Registro actualizado con exito";
        $Return = "envioEfectivod.php?Msj=$Msj";
    } elseif ($request->getAttribute("Boton") === "Cancelar") {
        $Pss = "SELECT master FROM cia";
        $Pas = utils\IConnection::execSql($Pss);
        if ($Pas["master"] === $request->getAttribute("CancelMov")) {
            $Update = "UPDATE env_efectivo SET status = 'Cancelado' WHERE id = " . $busca;
            utils\IConnection::execSql($Update);
            $Update = "UPDATE env_efectivod SET id_corte = -id_corte WHERE id_ee = " . $busca;
            utils\IConnection::execSql($Update);
        }
    }

    header("Location: $Return");
}
if ($request->hasAttribute("Op")) {
    $Msj = "Sin modificaciones";
    switch ($request->getAttribute("Op")) {
        case "Delete":
            $Return = "envioEfectivod.php?";
            $EnvEfectivodDAO = new Env_efectivodDAO();
            if ($EnvEfectivodDAO->remove($request->getAttribute("IdD"))) {
                $Return .= "Msj=Registro eliminado con exito";
                $RCalcul = "UPDATE env_efectivo ee LEFT JOIN 
                                    (SELECT sum(monto) monto,id_ee FROM env_efectivod eed WHERE id_ee = " . $request->getAttribute("IdR") . ") eed ON ee.id=eed.id_ee 
                                    SET ee.importe = eed.monto WHERE ee.id=" . $request->getAttribute("IdR");
                utils\IConnection::execSql($RCalcul);
            }
            header("Location: $Return");
            break;
        case "Cerrar":
            $Return = "envioEfectivod.php?";
            $EnvEfectivoDAO = new Env_efectivoDAO();
            $EnvEfectivoVO = new Env_efectivoVO();
            error_log(print_r($request, true));
            $EnvEfectivoVO = $EnvEfectivoDAO->retrieve($request->getAttribute("IdOp"));
            $EnvEfectivoVO->setStatus("Cerrado");
            if ($EnvEfectivoDAO->update($EnvEfectivoVO)) {
                $Return .= "Msj=Registro cerrado con exito";
            }
            header("Location: $Return");
            break;
    }
}

function TotalizaEnvio($id) {
    $RCalcul = "UPDATE env_efectivo ee LEFT JOIN 
                                    (SELECT sum(monto) monto,id_ee FROM env_efectivod eed WHERE id_ee = " . $id . ") eed ON ee.id=eed.id_ee 
                                    SET ee.importe = eed.monto WHERE ee.id=" . $id;
    utils\IConnection::execSql($RCalcul);
}

$sql = "SELECT cli.nombre,u.descripcion,u.codigo,IFNULL(b.puntos,0) puntos , u.id,cli.id idCli FROM unidades u  "
        . "LEFT JOIN cli ON u.cliente=cli.id LEFT JOIN (SELECT SUM(puntos - consumido) puntos,id_unidad "
        . "FROM beneficios WHERE TRUE AND consumido < puntos AND tipo='P' GROUP BY id_unidad) b "
        . "ON b.id_unidad=u.id WHERE TRUE  AND u.id=" . $busca;
$Rst = utils\IConnection::execSql($sql);
$SqlBnf = "SELECT SUM(cb.puntos) puntos,fecha,0 importe,cb.id,0 descuento,cb.tm,cb.id idCb ,cb.id_ticket_beneficio itb
                FROM cobranza_beneficios cb LEFT JOIN beneficios b ON cb.id_beneficio=b.id 
                WHERE b.id_unidad=" . $Rst["id"] . " 
                AND cb.tm='A'   GROUP BY cb.id_ticket_beneficio,cb.fecha
                UNION ALL
                SELECT SUM(cb.puntos) puntos,fecha,rm.importe,rm.id,rm.descuento,cb.tm,cb.id idCb ,cb.id_ticket_beneficio itb
                FROM cobranza_beneficios cb LEFT JOIN beneficios b ON cb.id_beneficio=b.id 
                LEFT JOIN rm ON cb.id_ticket_beneficio=rm.id WHERE b.id_unidad=" . $Rst["id"] . " AND cb.tm='C'  
                GROUP BY id_ticket_beneficio ORDER BY fecha DESC;";

$rsEf = utils\IConnection::getRowsFromQuery($SqlBnf);
