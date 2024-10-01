<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("data/CtDAO.php");

use com\softcoatl\utils as utils;

require './services/ReportesVentasService.php';

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$usuarioSesion = getSessionUsuario();

$ctDAO = new CtDAO();
$ctVO = $ctDAO->retrieve($Corte);

$op = $request->getAttribute("op");

$Titulo = "Depositos del Corte: $Corte Turno:  " . $ctVO->getTurno() . " fecha: " . $ctVO->getFecha();

$display = "Desglose monetario MXN";


$selectDepositos = "
            SELECT ctdep.despachador,ven.nombre, SUM(ctdep.total) total 
            FROM ctdep, ven 
            WHERE 1 = 1 
            AND ctdep.despachador = ven.id
            AND ctdep.corte = $Corte 
            GROUP BY ctdep.despachador";

$registros = utils\IConnection::getRowsFromQuery($selectDepositos);
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#Corte").val("<?= $Corte ?>");
                $("#op").val("<?= $op ?>");
            });
        </script>
    </head>

    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo) ?>
            <div id="Reportes" style="min-height: 250px;width: 75%">
                <table aria-hidden="true">
                    <thead>
                        <tr>
                            <td>Denominacion</td>
                            <td>Cantidad</td>
                            <td>Total</td>
                        </tr>
                    </thead>
                    <tbody>

                        <?php
                        $GTotal = 0;
                        foreach ($registros as $rg) {
                            ?>
                            <tr>
                                <td><?= $rg["despachador"] ?></td>
                                <td><?= $rg["nombre"] ?></td>
                                <td class="numero"><?= number_format($rg["total"], 2) ?></td>
                            </tr>
                            <?php
                            $GTotal += $rg["total"];
                        }
                        ?>

                    </tbody>
                    <tfoot>

                        <tr>
                            <td></td>
                            <td>Total</td>
                            <td><?= number_format($GTotal, 2) ?></td>
                        </tr>
                    </tfoot>
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
                <input type="hidden" name="Corte" id="Corte">
                <input type="hidden" name="op" id="op">
            </form>
            <?php topePagina(); ?>
        </div>
    </body>
</html>

