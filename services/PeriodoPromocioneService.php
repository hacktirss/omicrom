<?php

#Librerias
include_once('data/PeriodoPuntosDAO.php');
include_once('data/BitacoraDAO.php');
include_once('data/CombustiblesDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "periodopromocion.php?";
$busca = $sanitize->sanitizeString("busca");
$BitacoraDAO = new BitacoraDAO();

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;
    $PeriodoPuntosDAO = new PeriodoPuntosDAO();
    $PeriodoPuntosVO = new PeriodoPuntosVO();
    if ($busca > 0) {
        error_log("ENTRAMOS");
        $PeriodoPuntosVO = $PeriodoPuntosDAO->retrieve($busca);
        error_log(print_r($PeriodoPuntosVO, true));
        $ValFechaIni = $PeriodoPuntosVO->getFecha_inicial();
    }
    $PeriodoPuntosVO->setDescripcion($request->getAttribute("Descripcion"));
    $PeriodoPuntosVO->setFecha_inicial($request->getAttribute("FechaInicial"));
    $PeriodoPuntosVO->setFecha_final($request->getAttribute("FechaFinal"));
    $PeriodoPuntosVO->setFecha_culmina($request->getAttribute("FechaCulmina"));
    $PeriodoPuntosVO->setActivo($request->getAttribute("Status"));
    $PeriodoPuntosVO->setTipo_concentrado($request->getAttribute("TipoV"));
    $PeriodoPuntosVO->setMonto_promocion($request->getAttribute("Monto"));
    $PeriodoPuntosVO->setLimite_inferior($request->getAttribute("LimiteInferior"));
    $PeriodoPuntosVO->setLimite_superior($request->getAttribute("LimiteSuperior"));

    $Cm = explode(",", $PeriodoPuntosVO->getProducto_promocion());
    $impTt = "";
    $e = 0;
    foreach ($Cm as $rCm) {
        $impTt .= ($e <= 2) ? $request->getAttribute($rCm) . "," : "";
        $e++;
    }
    $impTt .= "0.0";
    $e = 0;
    $impTtLimit = "";
    foreach ($Cm as $rCm) {
        error_log("Corre de nuez");
        error_log(print_r($request, trues));
        $impTtLimit .= ($e <= 2) ? $request->getAttribute($rCm . "_Limit_Inf") . "," : "";
        $e++;
    }
    $impTtLimit .= "0.0";
    error_log(" LIMIT S " . $impTtLimit);
    $PeriodoPuntosVO->setLimites_inferiores($impTtLimit);
    $PeriodoPuntosVO->setFactores_producto($impTt);
    try {
        $Periodos = "SELECT fecha_culmina FROM periodo_puntos WHERE fecha_culmina > '" .
                $request->getAttribute("FechaInicial") . "' AND id <> $busca ORDER BY fecha_culmina DESC;";
        $Fecha_culmina = utils\IConnection::execSql($Periodos);
        if ($Fecha_culmina["fecha_culmina"] < $request->getAttribute("FechaInicial") || true) {
            if ($request->getAttribute("Boton") === utils\Messages::OP_ADD) {
                if ($id = $PeriodoPuntosDAO->create($PeriodoPuntosVO)) {
                    $BitacoraDAO->saveLog($usuarioSesion->getUsername(), "ADM", "Crea promoción id $id ");
                    $Msj = utils\Messages::MESSAGE_DEFAULT;
                }
            } elseif ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
                if ($PeriodoPuntosDAO->update($PeriodoPuntosVO)) {
                    $BitacoraDAO->saveLog($usuarioSesion->getUsername(), "ADM", "Edita promoción id $busca Fecha inicial $ValFechaIni");
                    $Msj = utils\Messages::MESSAGE_DEFAULT;
                }
            }
        } else {
            $Msj = "La fecha inicial esta dentro de otra promoción";
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en pagos: " . $ex);
    } finally {
        header("Location: $Return");
    }
}
