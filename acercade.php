<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$selectServicios = "SELECT id,nombre,version,md5,status FROM servicios WHERE status = 'Si' ORDER BY nombre;";

$registros = utils\IConnection::getRowsFromQuery($selectServicios);
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports.php'; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {

            });
        </script>
    </head>

    <body>
        <div id="container">

            <?php nuevoEncabezado($Titulo) ?>
            <div id="Reportes" style="width: 80%">
                <table aria-hidden="true">
                    <thead>
                        <tr>
                            <td>Equipo</td>
                            <td>Ver</td>
                            <td>Md5</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($registros as $rg) {
                            ?>
                            <tr>
                                <td><?= $rg["nombre"] ?></td>
                                <td><?= $rg["version"] ?></td>
                                <td><?= $rg[md5] ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="footer"  style="width: 80%">
            <form name="formActions" method="post" action="" class="oculto">
                <div id="Controles">
                    <table aria-hidden="true">
                        <tbody>
                            <tr>
                                <td>
                                    <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print"  aria-hidden="true" aria-hidden="true"></i></button></span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </form>
            <?php topePagina(); ?>
        </div>
    </body>
</html>
