<?php

include_once ("../../softcoatl/SoftcoatlHTTP.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$connection = utils\IConnection::getConnection();
$jsonString = array();


$validator = "Dispensarios";
/**
 * {0: "POST", 1 : "GET", 2 : "PUT"}
 */
$paramValidator = "paramValidator";

if ($request->hasAttribute("validator") && $request->getAttribute("validator") === $validator) :
    $jsonString["Response"] = false;
    $jsonString["Message"] = utils\Messages::MESSAGE_NO_OPERATION;

    $Identificador = $request->getAttribute("Identificador");

    if ($request->getAttribute($paramValidator) == utils\Messages::METHOD_GET . utils\Messages::METHOD_GET) :

        $selectCatalogo = "SELECT posicion,activo FROM man WHERE TRUE AND artivo = 'Si' ";
        $selectCatalogo .= "ORDER BY id;";
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

