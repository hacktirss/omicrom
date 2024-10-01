<?php
#Librerias
session_start();
include_once ("auth.php");
include_once ("authconfig.php");
include_once ("libnvo/lib.php");
include_once ("libnvo/Utilerias.php");

use com\softcoatl\utils as utils;

$username = utils\HTTPUtils::getSessionValue(Usuarios::SESSION_USERNAME);
$password = utils\HTTPUtils::getSessionValue(Usuarios::SESSION_PASSWORD);

$auth = new Auth();
$auth->setUsername($username);
$auth->setPassword($password);

$usuarioVO = $auth->authenticate();

if ($usuarioVO->getCount() == 0 || $auth->isExpired($usuarioVO)) {
    $Redirect = $changepass . "?user=" . $usuarioVO->getId();
} else {
    $ip = getRealUserIp("localhost", FILTER_FLAG_NO_RES_RANGE);
    $usuarioVO->setIdLocation($ip);
    ini_set("session.cookie_lifetime", "0");
    utils\HTTPUtils::setSessionValue("USUARIO", serialize($usuarioVO));
    if ($usuarioVO->getLevel() < UsuarioDAO::LEVEL_MASTER) {
        BitacoraDAO::getInstance()->saveLog($username, "ACCESO", "LOGIN EXITOSO", "", 0, $ip);
    }
    if ($usuarioVO->getLevel() == 2) {
        utils\HTTPUtils::setSessionValue("Nombre", $usuarioVO->getNombre());
        $IdCli = explode(".", $usuarioVO->getNombre());
        $IdCli = is_numeric($usuarioVO->getUsername()) ? $usuarioVO->getUsername() : $IdCli[0];
        utils\HTTPUtils::setSessionValue("Cuenta", $IdCli);
        $Redirect = $clientes;
    } else {

        /* Nos conectamos a la BD */
        $connection = iconnect();

        /* Mandamos los menus existentes */
        $CpoB = $connection->query("SELECT nombre,orden,tipo,id FROM menus ORDER BY orden,tipo");
        $x = 0;
        while ($Cpob = $CpoB->fetch_array()) {
            $menus[$x][0] = $Cpob["nombre"];
            $menus[$x][1] = $Cpob["orden"];
            $menus[$x][2] = $Cpob["tipo"];
            $menus[$x][3] = $Cpob["id"];
            $x++;
        }
        $_SESSION["MENUS"] = $menus;
        /* Mandamos la tabla con todos los submenus */
        $sql = "SELECT s.menu,menus.nombre,s.submenu,s.url,s.permisos,menus.orden,s.posicion
                FROM menus,submenus s
                WHERE s.menu = menus.id 
                ORDER BY menus.orden,s.posicion";
        $CpoC = $connection->query($sql);
        $y = 0;
        $submenus = Array();
        while ($rg = $CpoC->fetch_array()) {
            $consulta[$y][0] = $rg["menu"]; //id del menu  
            $consulta[$y][1] = $rg["submenu"];
            $consulta[$y][2] = $rg["url"];
            $consulta[$y][3] = $rg["permisos"];
            $consulta[$y][4] = $rg["orden"];
            $submenus[$rg["menu"]][$rg["posicion"]]["direccion"] = $rg["url"];
            $submenus[$rg["menu"]][$rg["posicion"]]["nombre"] = $rg["submenu"];
            $submenus[$rg["menu"]][$rg["posicion"]]["permiso"] = $rg["permisos"];
            $y++;
        }
        $_SESSION["SUBMENUS"] = $consulta;
        $_SESSION["S_USER"] = $submenus;
        $Redirect = $success;
    }
}
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <title>Autenticacion omicrom</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <script>
            location.replace("<?= $Redirect ?>");
        </script>
    </head>
</html>