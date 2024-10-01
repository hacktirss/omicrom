<?php

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
if ($Recupera === "Clave") {
    $Catalogo = "SELECT clave,descripcion FROM catalogos_sat_cv WHERE catalogo='CLAVES_INSTALACION';";
} elseif ($Recupera === "Modalidad") {
    $Catalogo = "SELECT clave,descripcion FROM catalogos_sat_cv WHERE catalogo='CLAVES_PERMISO';";
}