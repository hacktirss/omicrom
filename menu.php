<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("data/MensajesDAO.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$usuarioSesion = getSessionUsuario();

$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));
$Titulo = "Menu principal";
$Id = 5;

if ($request->hasAttribute("op")) {
    if ($request->getAttribute("op") === "st") {
        $sql = "SELECT * FROM msj WHERE tipo = '" . TipoMensaje::SIN_LEER . "' AND DATE_ADD(fecha,INTERVAL vigencia DAY) >= CURRENT_DATE()";
        $registros = utils\IConnection::getRowsFromQuery($sql);
        $numRegistros = count($registros);
        if ($numRegistros == 0) {
            header("Location: servicio.php");
        } else {
            header("Location: servicio.php?pop=1");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php include './config_omicrom.php'; ?> 
        <title><?= $Gcia ?></title>
        <script type="text/javascript">

        </script>
    </head>

    <body>
        <?php BordeSuperior(); ?>
        
        <div class="texto_tablas" align="center"><?= $Msj ?></div>

        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>
    </body>
</html>
