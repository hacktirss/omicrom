<?php

session_start();
include_once ("libnvo/lib.php");
define("IDTAREA", -100);

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();

$OrdenP = $request->getAttribute("Orden");
$jsondata = array();
$jsondata["success"] = false;
if ($request->getAttribute("GCorte") === "Guardar") {
    utils\HTTPUtils::setSessionValue("GuardarCorte", true);
}
$SerieCP = "SELECT IFNULL(MAX(serie),0) + 1 sr FROM omicrom.ct_parcial_fecha limit 1";
$Sr = utils\IConnection::execSql($SerieCP);
utils\HTTPUtils::setSessionValue("SerieCp", $Sr["sr"]);
//error_log(print_r($request, TRUE));

try {
    $del1 = "UPDATE totalizadores SET idtarea = 0 WHERE idtarea = " . IDTAREA . ";";
    if (!$mysqli->query($del1)) {
        error_log($mysqli->error);
    }
    $del2 = "UPDATE comandos SET idtarea = 0 WHERE idtarea = " . IDTAREA . ";";
    if (!$mysqli->query($del2)) {
        error_log($mysqli->error);
    }

    $tru = "DELETE FROM ct_parcial WHERE 1 = 1;";
    if (!$mysqli->query($tru)) {
        error_log($mysqli->error);
    }

    $mensaje = "Movimientos enviados:  ";
    $val = false;
    $registros = $request->getAttribute("registros");

    if ($OrdenP === "P") {
        if (is_array($registros)) {
            foreach ($registros as $key => $value) {
                error_log("pos: " . $value);

                $insert = "INSERT INTO comandos (posicion,manguera,comando,fecha_insercion,fecha_programada,idtarea) 
                            SELECT man.posicion, 1 manguera, CONCAT('T',LPAD(man.posicion,2,'0')) comando, 
                            NOW() fecha_insercion, NOW() fecha_programada, '" . IDTAREA . "' idtarea
                            FROM man 
                            WHERE activo = 'Si' AND man.posicion = '$value';";

                if (!$mysqli->query($insert)) {
                    error_log($mysqli->error);
                } else {
                    $mensaje .= $value . ",";
                    $val = true;
                }
            }
        }
    } elseif ($OrdenP === "D") {
        if (is_array($registros)) {
            foreach ($registros as $key => $value) {
                error_log("des: " . $value);

                $insert = "INSERT INTO comandos (posicion,manguera,comando,fecha_insercion,fecha_programada,idtarea) 
                            SELECT man.posicion, 1 manguera, CONCAT('T',LPAD(man.posicion,2,'0')) comando, 
                            NOW() fecha_insercion, NOW() fecha_programada, '" . IDTAREA . "' idtarea
                            FROM man 
                            WHERE activo = 'Si' AND man.despachador = '$value';";

                if (!$mysqli->query($insert)) {
                    error_log($mysqli->error);
                } else {
                    $mensaje .= $value . ",";
                    $val = true;
                }
            }
        }
    } elseif ($OrdenP === "I") {
        if (is_array($registros)) {
            foreach ($registros as $key => $value) {
                error_log("isl: " . $value);

                $insert = "INSERT INTO comandos (posicion,manguera,comando,fecha_insercion,fecha_programada,idtarea) 
                            SELECT man.posicion, 1 manguera, CONCAT('T',LPAD(man.posicion,2,'0')) comando, 
                            NOW() fecha_insercion, NOW() fecha_programada, '" . IDTAREA . "' idtarea
                            FROM man 
                            WHERE activo = 'Si' AND man.isla_pos = '$value';";

                if (!$mysqli->query($insert)) {
                    error_log($mysqli->error);
                } else {
                    $mensaje .= $value . ",";
                    $val = true;
                }
            }
        }
    } else {
        error_log("Invalid!");
    }
    $jsondata["success"] = $val;
    $jsondata["message"] = $mensaje;
} catch (Exception $ex) {
    error_log($ex);
} finally {
    $mysqli->close();
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($jsondata, JSON_FORCE_OBJECT);
}