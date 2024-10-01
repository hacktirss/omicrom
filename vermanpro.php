<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$Titulo = "Distribucion de mangueras";

$selectDistribucion = "
            SELECT m.isla,m.dispensario,m.posicion,m.manguera,m.dis_mang,com.descripcion,m.enable,m.m
            FROM man_pro m,com
            WHERE m.producto = com.clavei AND m.activo = 'Si'  
            ORDER BY m.posicion,m.dis_mang ";

$registros = utils\IConnection::getRowsFromQuery($selectDistribucion);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?>  
        <title><?= $Gcia ?></title>
    </head>

    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo) ?>
            <div id="Reportes">
                 <table aria-hidden="true">
                    <thead>
                        <tr>
                            <td>Isla</td>
                            <td>Disp</td>
                            <td>Posicion</td>
                            <td>Manguera</td>
                            <td>Dis_mang</td>
                            <td>Producto</td>
                            <td>Enable</td>
                            <td>M</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($registros as $rg) {
                            ?>
                            <tr>
                                <td class="numero"><?= $rg["isla"] ?></td>
                                <td class="numero"><?= $rg["dispensario"] ?></td>
                                <td class="numero"><?= $rg["posicion"] ?></td>
                                <td class="numero"><?= $rg["manguera"] ?></td>
                                <td class="numero"><?= $rg[dis_mang] ?></td>
                                <td><?= $rg["descripcion"] ?></td>
                                <td class="numero"><?= $rg["enable"] ?></td>
                                <td class="numero"><?= $rg["m"] ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="footer">
            <form name="formActions" method="post" action="" class="oculto">
                <div id="Controles">
                     <table aria-hidden="true">
                        <tbody>
                            <tr>
                                <td>
                                    <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
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
