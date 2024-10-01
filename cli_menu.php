<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$Titulo = "Servicio a clientes menu principal";
$Id = 1;
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php include './config_omicrom_clientes.php'; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {

            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(true); ?>
        <?php
        $Band = "SELECT tipo_permiso FROM cia;";
        $stB = utils\IConnection::execSql($Band);
        if ($stB["tipo_permiso"] === "TRA") {
            ?>
            <a href=javascript:winuni("calendarRm.php?busca=ini");><i class="fa fa-calendar-plus-o fa-lg" aria-hidden="true" style="color:#009080">Registro de pedidos</i></a>
        <?php } ?>
        <table aria-hidden="true">
            <tr>
                <td></td>
            </tr>
        </table>

        <?php BordeSuperiorCerrar(); ?>
        <?php PieDePagina(); ?>

    </body>
</html>
