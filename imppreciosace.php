<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("importeletras.php");

use com\softcoatl\utils as utils;

$Titulo = "Lista de precios al " . date("Y-m-d");

$selectAceites = "
        SELECT inv.id,inv.descripcion,c.nombre medida,IF(inv.codigo = '',0,inv.codigo) codigo,inv.precio 
        FROM inv LEFT JOIN cfdi33_c_unidades c ON inv.inv_cunidad = c.clave
        WHERE inv.rubro = 'Aceites'";

$registros = utils\IConnection::getRowsFromQuery($selectAceites);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports.php'; ?>
        <title><?= $Gcia ?></title>
    </head>

    <body>
        <div id="container">

            <?php nuevoEncabezado($Titulo); ?>

            <div id="Reportes">
                 <table aria-hidden="true">
                    <thead>
                        <tr>
                            <td>Clave</td>
                            <td>Descripcion</td>
                            <td>U.medida</td>
                            <td>C.barras</td>
                            <td>Precio</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($registros as $rg) {
                            ?>
                            <tr>
                                <td><?= $rg["id"] ?></td>
                                <td><?= $rg["descripcion"] ?></td>
                                <td><?= $rg["medida"] ?></td>
                                <td><?= $rg["codigo"] ?></td>
                                <td class="numero"><?= number_format($rg["precio"], 2) ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="footer">
            <form name="formActions" method="post" action="" id="form" class="oculto">
                <div id="Controles">
                     <table aria-hidden="true">
                        <tr style="height: 40px;">
                            <td>
                                <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                            </td>
                        </tr>

                    </table>
                </div>
            </form>
            <?php topePagina(); ?>
        </div>
    </body>
</html>
