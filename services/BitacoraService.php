<?php

#Librerias
include_once ('data/BitacoraDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "bitacoraEventos.php";

$objectDAO = new BitacoraDAO();

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    $usuarioSesion = getSessionUsuario();
    $objectVO = new BitacoraVO();
    $objectVO->setFechaEvento($request->getAttribute("Fecha"));
    $objectVO->setHoraEvento($request->getAttribute("HoraEv"));
    $objectVO->setUsuario($usuarioSesion->getNombre());
    $objectVO->setTipoEvento($request->getAttribute("TipoEvento"));
    $objectVO->setDescripcionEvento($request->getAttribute("taDescripcion"));

    $sql = "SELECT lv.alarma_lista_valor FROM listas l,listas_valor lv WHERE l.id_lista = lv.id_lista_lista_valor  "
            . "AND l.nombre_lista = 'BITACORA DE EVENTOS' AND l.estado_lista = 1 AND lv.llave_lista_valor = '" . $request->getAttribute("TipoEvento") . "';";

    if ($request->getAttribute("TipoEvento") === "PEM" || $request->getAttribute("TipoEvento") === "ADM") {
        $NoVal = $request->getAttribute("TipoEvento") === "PEM" ? 21 : 1;
        $objectVO->setNumeroAlarma($NoVal);
    } else {
        $registros = utils\IConnection::execSql($sql);
        if (is_numeric($registros["alarma_lista_valor"]) && $registros["alarma_lista_valor"] >= 1) {
            $objectVO->setNumeroAlarma($registros["alarma_lista_valor"]);
        }
    }
    try {
        if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
            error_log(print_r($objectVO, TRUE));
            if ($objectDAO->create($objectVO) > 0) {
                $Msj = utils\Messages::RESPONSE_VALID_CREATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        }
    } catch (Exception $ex) {
        error_log("Error en bancos: " . $ex);
    } finally {
        header("Location: $Return");
    }
}
