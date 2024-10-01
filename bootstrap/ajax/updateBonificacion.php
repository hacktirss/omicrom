<?php

include_once ("../../softcoatl/SoftcoatlHTTP.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$connection = utils\IConnection::getConnection();
$jsonString = array();
$cargas = trim($sanitize->sanitizeString("capturas"));
$jsonString["Response"] = false;
$Qry0 = "SELECT tanque FROM cargas WHERE id IN ($cargas) AND entrada = 0 ORDER BY id DESC;";

$rows1 = $connection->query($Qry0);
$Rst = true;
$vale = true;
if ($request->getAttribute("op") == 1) {
    if (preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/', $request->getAttribute("FechaActual"))) {
        $query = "UPDATE `variables_corporativo` SET `valor` = '" . $request->getAttribute("FechaActual") . "' WHERE (`llave` = 'Inicio_Puntos');";
        if ($rows = $connection->query($query)) {
            echo json_encode("Fecha actualizada con exito!");
        } else {
            echo $connection->error;
        }
    } else {
        echo $request->getAttribute("FechaActual") . " <br>No cumple con el patron requerido 'YYYY-mm-dd'";
    }
} else if ($request->getAttribute("op") == 2) {
    $query = "UPDATE `com` SET `cnt_por_punto` = '" . $request->getAttribute("Cnt") . "' WHERE (`id` = " . $_REQUEST["IdCom"] . ");";
    if ($rows = $connection->query($query)) {
        echo json_encode("Cantidad actualizada con exito!");
    } else {
        echo $connection->error;
    }
}
exit();
