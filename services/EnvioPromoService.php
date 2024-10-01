<?php

#Librerias
include_once('data/EnvioPromoDAO.php');
include_once('data/EnvioPromodDAO.php');
include_once('data/ClientesDAO.php');
include_once('data/BitacoraDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "envioPromo.php?";
$busca = $sanitize->sanitizeString("busca");
$BitacoraDAO = new BitacoraDAO();

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $EnvioPromoDAO = new EnvioPromoDAO();
    $EnvioPromoVO = new EnvioPromoVO();
    $EnvioPromoVO->setId(utils\HTTPUtils::getSessionValue("busca"));
    $EnvioPromoVO->setDescripcion($request->getAttribute("Descripcion"));
    $EnvioPromoVO->setFecha_inicio($request->getAttribute("FechaInicial"));
    $EnvioPromoVO->setFecha_final($request->getAttribute("FechaFinal"));
    $EnvioPromoVO->setDescuento($request->getAttribute("Descuento"));
    $EnvioPromoVO->setId_producto($request->getAttribute("Producto"));
    $EnvioPromoVO->setId_user($usuarioSesion->getId());
    $EnvioPromoVO->setConsumo_min($request->getAttribute("Consumo_Min"));
    try {
        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            if ($id = $EnvioPromoDAO->create($EnvioPromoVO)) {
                $BitacoraDAO->saveLog($usuarioSesion->getUsername(), "ADM", "Crea envio de promoción id $id ");
                $Return = "envioPromoe.php?busca=" . $id;
                $Msj = utils\Messages::MESSAGE_DEFAULT;
            }
        } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
            $EnvioPromoVO->setStatus($request->getAttribute("Status"));
            if ($EnvioPromoDAO->update($EnvioPromoVO)) {
                $BitacoraDAO->saveLog($usuarioSesion->getUsername(), "ADM", "Edita promocion enviada id $busca");
                $Msj = utils\Messages::MESSAGE_DEFAULT;
                $Return = "envioPromoe.php?busca=" . $EnvioPromoVO->getId();
            }
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en pagos: " . $ex);
    } finally {
        header("Location: $Return");
    }
}

if ($request->hasAttribute("BotonD") && $request->getAttribute("BotonD") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $EnvioPromodVO = new EnvioPromoVO();
    $EnvioPromodDAO = new EnvioPromoDAO();
    $ClienteDAO = new ClientesDAO();
    $ClienteVO = new ClientesVO();
    $ClienteVO = $ClienteDAO->retrieve($request->getAttribute("Cliente"));
    $Vlt = true;
    if ($request->getAttribute("BotonD") === utils\Messages::OP_ADD) {
        if ($request->getAttribute("Cliente") === "*") {
            $SqlCli = "SELECT id FROM cli WHERE id > 20 AND activo='Si' AND telefono <> ''";
            $CliRows = utils\IConnection::getRowsFromQuery($SqlCli);

            foreach ($CliRows as $row) {
                $Insert = "INSERT INTO envioPromod (id,id_authuser,codigo) VALUES (" . utils\HTTPUtils::getSessionValue("busca") . ","
                        . $row["id"] . "," . rand(100000, 999999) . ")";
                utils\IConnection::execSql($Insert);
            }
            $Vlt = false;
        } else {
            if ($ClienteVO->getTelefono() !== "") {
                $InsertS = "INSERT INTO envioPromod (id,id_authuser,codigo) SELECT " . utils\HTTPUtils::getSessionValue("busca") . ",id," . rand(100000, 999999) . " "
                        . "FROM cli WHERE id =" . $request->getAttribute("Cliente");
                utils\IConnection::execSql($InsertS);
            }
        }
    }
}

if ($request->hasAttribute("op") && $request->getAttribute("op") !== utils\Messages::OP_NO_OPERATION_VALID) {
    if ($request->getAttribute("op") === "LanzarPromo") {
        $EnvioPromoDAO = new EnvioPromoDAO();
        $EnvioPromoVO = new EnvioPromoVO();
        $EnvioPromoVO = $EnvioPromoDAO->retrieve(utils\HTTPUtils::getSessionValue("busca"));
        $EnvioPromoVO->setStatus("Cerrada");
        $EnvioPromoDAO->update($EnvioPromoVO);
        $command = "sudo java -cp /home/omicrom/OMICROMNotifier/OMICROMNOTIFIER-1.0.jar com.mx.detisa.MessageSender '";
        $Msj = utils\Messages::MESSAGE_DEFAULT;
//        exec($command);
    }
}