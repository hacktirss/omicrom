<?php

include_once ("../../softcoatl/SoftcoatlHTTP.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$connection = utils\IConnection::getConnection();
$jsonString = array();


$validator = "Entradas";
/**
 * {0: "POST", 1 : "GET", 2 : "PUT"}
 */
$paramValidator = "paramValidator";

if ($request->hasAttribute("validator") && $request->getAttribute("validator") === $validator) :
    $jsonString["Response"] = false;
    $jsonString["Message"] = utils\Messages::MESSAGE_NO_OPERATION;

    $Identificador = $request->getAttribute("Identificador");

    if ($request->getAttribute($paramValidator) == utils\Messages::METHOD_GET . utils\Messages::METHOD_GET) :

        $selectCatalogo = "
            SELECT id, llave clave, permiso, descripcion, IF(LENGTH(descripcion) > 20, CONCAT(SUBSTRING(descripcion, 1, 20), '...'), descripcion) descripcion_corta 
            FROM permisos_cre 
            WHERE TRUE AND padre > 0 AND estado = 1 AND catalogo = '" . $Identificador . "' ";
        if ($request->hasAttribute("SubIdentificador") && $request->getAttribute("SubIdentificador") > 0) {
            $selectCatalogo .= "AND padre = '" . $request->getAttribute("SubIdentificador") . "' ";
        } 

        $selectCatalogo .= "ORDER BY llave;";
        $rows_ = utils\IConnection::getRowsFromQuery($selectCatalogo, $connection);
        foreach ($rows_ as $value) {
            $jsonString["rows"][] = $value;
        }

        $jsonString["Response"] = true;

    endif;

endif;

if (is_null($jsonString)) :
    error_log(json_last_error());
endif;

echo json_encode($jsonString);

