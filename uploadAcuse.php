<?php

include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$imagen_temporal = $_FILES['file']['tmp_name'];
$ObtenerFecha = explode("_", $_FILES["file"]["name"][0]);
$Obteniendo = $ObtenerFecha[1];
$arr1 = str_split($Obteniendo);
$MES = $Obteniendo[4] . $Obteniendo[5];
if (!strstr($_FILES["file"]["name"][0], "Rechazo") && strstr($_FILES["file"]["name"][0], "Aceptacion")) {
    // Leemos el contenido del archivo temporal en binario.
    if ($MES == $_REQUEST["Mes"]) {
        $fp = fopen($imagen_temporal, 'r+b');
        $data = fread($fp, filesize($imagen_temporal));
        fclose($fp);
        $data = $mysqli->real_escape_string($data);
        $Upload = "UPDATE log_envios_sat SET acuse_sat = '" . $data . "'  WHERE id = " . $_REQUEST["busca"];
        $RsUp = utils\IConnection::execSql($Upload);
        $Msj = 1;
    } else {
        $Msj = 2;
    }
} else {
    error_log("Archivo Rechazado");
    $Msj = 0;
}
echo json_encode($Msj);
die();
?>