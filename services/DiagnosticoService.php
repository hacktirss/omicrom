<?php

#Librerias
include_once ("data/VariablesDAO.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "diagnostico.php?";


if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {

    if ($request->getAttribute("Boton") === "Diagnostico") {
        BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "SE REALIZA DIAGNOSTICO DE COMPONENTES DE cv");
    }
}

/**
 * 
 * @param int $volumenActual
 * @param int $volumen_operativo
 * @param string $color
 * @return string
 */
function getImagenCombustible($volumenActual, $volumen_operativo, $color) {
    $valor = 80;
    $ruta_imagen = "";
    if ((($volumenActual / $volumen_operativo) * 100) < 15) {
        $valor = 10;
    } elseif (((($volumenActual / $volumen_operativo) * 100) < 30)) {
        $valor = 20;
    } elseif (((($volumenActual / $volumen_operativo) * 100) < 50)) {
        $valor = 40;
    } elseif (((($volumenActual / $volumen_operativo) * 100) < 70)) {
        $valor = 60;
    }
    if ($color == "GREEN") {
        $ruta_imagen = "libnvo/verde_$valor.png";
    } elseif ($color == "RED") {
        $ruta_imagen = "libnvo/rojo_$valor.png";
    } elseif ($color == "BLACK") {
        $ruta_imagen = "libnvo/negro_$valor.png";
    }
    return $ruta_imagen;
}

/**
 * 
 * @param string $host
 * @return array()
 */
function ping($host) {
    $res = $rval = "";
    exec(sprintf("ping -c 1 -W 5 %s", escapeshellarg($host)), $res, $rval);
    //error_log(print_r($res, true));
    return $res;
}

class CheckDevice {

    public function myOS() {
        if (strtoupper(substr(PHP_OS, 0, 3)) === (chr(87) . chr(73) . chr(78))) {
            return true;
        }
        return false;
    }

    public function ping($ip_addr) {
        if ($this->myOS()) {
            if (!exec("ping -n 1 -w 1 " . $ip_addr . " 2>NUL > NUL && (echo 0) || (echo 1)")) {
                return true;
            }
        } else {
            if (!exec("ping -q -c1 " . $ip_addr . " >/dev/null 2>&1 ; echo $?")) {
                return true;
            }
        }

        return false;
    }

}
