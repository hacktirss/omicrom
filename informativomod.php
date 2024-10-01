<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$Recupera = "Modalidad";

require "./services/InformativoService.php";

$Titulo = "Descripcion de Modalidad de Permiso";

$registrosP = utils\IConnection::getRowsFromQuery($Catalogo);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
            });
        </script>
    </head>

    <body>

        <div id="container">
            <?php nuevoEncabezado($Titulo); ?>

            <div id="Reportes">
                <table aria-hidden="true">
                    <tr class="titulo">
                        <td >Clave</td>
                        <td >Descripcion</td>
                    </tr>
                    <?php
                    foreach ($registrosP as $rg) {
                        ?>
                        <tr>
                            <td><strong><?= $rg[0] ?></strong></td>
                            <td><a><?= $rg[1] ?></a></td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>
            </div>

        </div>


        <?php topePagina(); ?>

    </body>
</html>

