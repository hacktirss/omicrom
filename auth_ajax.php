<?php

#Librerias
include_once ("libnvo/lib.php");
include_once ('./data/UsuarioVO.php');

use com\softcoatl\utils as utils;

$sanitize = SanitizeUtil::getInstance();

$username = $sanitize->sanitizeString("username");
$password = $sanitize->sanitizeString("password");
$usuarioVO = new UsuarioVO();
$varSuccess = "success";
$varCount = "count";

$jsonString = Array();
$jsonString[$varSuccess] = false;
$jsonString["message"] = "";
$jsonString[$varCount] = 0;
$jsonString["redirect"] = "vAuthenticate.php";

try {
    session_start();
    $_SESSION = array();
    $usuarioDAO = new UsuarioDAO();
    $usuarioVO_U = $usuarioDAO->findByUname($username);

    if ($usuarioVO_U != null) {
        $usuarioVO = $usuarioDAO->finfByUnameAndPassword($username, $password);
        if ($usuarioVO != null && $usuarioVO->getStatus() === StatusUsuario::ACTIVO && $usuarioVO->getLocked() < Usuarios::MAX_INTENTS_LOGIN) {

            if ($usuarioVO->getAlive() == StatusSesion::DEAD || $usuarioVO->getDifference() < 0) {
                $jsonString[$varSuccess] = true;
                $usr = $username;
                $Ms5Omicrom = utils\IConnection::execSql("SELECT md5 FROM servicios WHERE nombre = 'Omicrom';");
                exec("find /var/www/html/omicrom/ -name \"*.php\" -type f -exec sha256sum {} \; | sort -f -k 1 | cut -d ' ' -f1 | sha256sum | cut -d ' ' -f1 > /home/omicrom/xml/sha.txt");
                $dr = "/home/omicrom/xml/sha.txt";
                $archivo = fopen($dr, "r");
                if (file_exists($dr)) {
                    while (!feof($archivo)) {
                        $traer = fgets($archivo);
                        if ($traer <> "" && $traer <> $Ms5Omicrom["md5"]) {
                            error_log("Cambio en sha256sum actualizando ...");
                            $update = "UPDATE servicios SET md5='$traer' WHERE nombre = 'Omicrom'";
                            error_log("Actualizamos md5 " . $traer);
                            utils\IConnection::execSql($update);
                        }
                    }
                } else {
                    error_log("***************************ERROR **********************************************");
                    error_log("Correr siguientes lineas en bash ");
                    error_log("mkdir /home/omicrom/xml");
                    error_log("chown -R www-data:www-data /home/omicrom/xml/");
                }
                BitacoraDAO::getInstance()->saveLogSn($username, "ACCESO", "LOGIN EXITOSO", "", AlarmasDAO::VAL20);
// Cerrando el archivo
                fclose($archivo);
                utils\HTTPUtils::setSessionValue(Usuarios::SESSION_USERNAME, $usuarioVO->getUsername());
                utils\HTTPUtils::setSessionValue(Usuarios::SESSION_PASSWORD, $usuarioVO->getPassword());
            } elseif ($usuarioVO->getAlive() == StatusSesion::ALIVE) {
                $jsonString[$varCount] = null;
                $Msj = str_replace("?", $username, utils\Messages::RESPONSE_USER_ALIVE);
            }
        } else {
            if ($usuarioVO_U->getAlive() == StatusSesion::DEAD) {
                $usuarioDAO->updateLocked($usuarioVO_U);
                $ip = getRealUserIp("localhost", FILTER_FLAG_NO_RES_RANGE);
                $usuarioVO1 = new UsuarioVO();
                $usuarioVO1->setIdLocation($ip);
                utils\HTTPUtils::setSessionValue("USUARIO", serialize($usuarioVO1));
                BitacoraDAO::getInstance()->saveLogSn($username, "ACCESO", "LOGIN FALLIDO", "", AlarmasDAO::VAL20);
                $jsonString[$varCount] = $usuarioVO_U->getLocked();
                if ($usuarioVO_U->getLocked() < Usuarios::MAX_INTENTS_LOGIN) {
                    $Msj = utils\Messages::RESPONSE_USER_DATA_INVALID;
                } else {
                    $Msj = utils\Messages::RESPONSE_USER_MAX_INTENTS;
                }
            } else {
                $jsonString[$varCount] = null;
                $Msj = str_replace("?", $username, utils\Messages::RESPONSE_USER_ALIVE);
            }
        }
    } else {
        $ip = getRealUserIp("localhost", FILTER_FLAG_NO_RES_RANGE);
        $usuarioVO->setIdLocation($ip);
        utils\HTTPUtils::setSessionValue("USUARIO", serialize($usuarioVO));
        BitacoraDAO::getInstance()->saveLogSn($username, "ACCESO", "LOGIN FALLIDO", "", AlarmasDAO::VAL20);
        $Msj = utils\Messages::RESPONSE_USER_DATA_INVALID;
    }
} catch (Exception $exc) {
    $Msj = "A ocurrido un error";
    $jsonString[$varSuccess] = true;
    $jsonString["redirect"] = "400.html";
} finally {
    $jsonString["message"] = $Msj;
    echo json_encode($jsonString);
}


