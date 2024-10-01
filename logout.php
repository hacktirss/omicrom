<?php
session_start();
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$usuarioSesion = getSessionUsuario();
$usuarios = new Usuarios();
$Msj = 0;

if($request->hasAttribute("timeout")){
    $Msj = 3;
    if($usuarioSesion != null){
        $usuarios->deadSession($usuarioSesion);
        BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), 'ACCESO', 'SESION EXPIRADA');
    }
    $_SESSION = array();
} else {
    $Msj = 5;
    if($usuarioSesion != null){
        $usuarios->deadSession($usuarioSesion);
        BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), 'ACCESO', 'LOGOUT EXITOSO');
    }
    $_SESSION = array();
}
header("Location: index.php?Msj=" . $Msj);
