<?php

include_once ("../../softcoatl/SoftcoatlHTTP.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$connection = utils\IConnection::getConnection();
$jsonString = array();

$validator = "Tanques";
/**
 * {0: "POST", 1 : "GET", 2 : "PUT"}
 */
$paramValidator = "paramValidator";

if ($request->hasAttribute("validator") && $request->getAttribute("validator") === $validator) :
    $jsonString["Response"] = false;
    $jsonString["Message"] = utils\Messages::MESSAGE_NO_OPERATION;

    $Identificador = $request->getAttribute("Identificador");

    if ($request->getAttribute($paramValidator) == utils\Messages::METHOD_GET . utils\Messages::METHOD_GET) :

        $selectCatalogo = "SELECT id, clave, descripcion, IF(LENGTH(descripcion) > 20, CONCAT(SUBSTRING(descripcion, 1, 20), '...'), descripcion) descripcion_corta "
            . "FROM catalogos_sat_cv WHERE TRUE AND catalogo = '" . $Identificador . "' ";
        if ($request->hasAttribute("SubIdentificador") && $request->getAttribute("SubIdentificador") > 0) {
            $selectCatalogo .= "AND padre = '" . $request->getAttribute("SubIdentificador") . "' ";
        }

        $selectCatalogo .= "ORDER BY clave;";
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

