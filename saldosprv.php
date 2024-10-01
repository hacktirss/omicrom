<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesProveedoresService.php";

$Titulo = "Saldos por proveedor al $Fecha ";

$registros = utils\IConnection::getRowsFromQuery($selectSaldos);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#FechaI").val("<?= $FechaI ?>").attr("size", "10");
                $("#FechaF").val("<?= $FechaF ?>").attr("size", "10");
                $("#cFechaI").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaI")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#cFechaF").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaF")[0], "yyyy-mm-dd", $(this)[0]);
                });
            });
        </script>
    </head>

    <body>
        <div id="container">
           <?php nuevoEncabezado($Titulo); ?>
            <div id="Reportes" style="min-height: 200px;">
                 <table aria-hidden="true">
                    <thead>
                        <tr>
                            <td>No.Cta</td>
                            <td>Alias</td>
                            <td>Nombre</td>
                            <td>Importe</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $cTipoPago = '';
                        foreach ($registros as $registro) {                           
                            ?>
                            <tr>
                                <td class="numero"><?= $registro["proveedor"] ?></td>
                                <td><?= ucwords(strtolower($registro["alias"])) ?></td>
                                <td><?= ucwords(strtolower($registro["nombre"])) ?></td>
                                <td class="numero"><?= number_format($registro["importe"],2) ?></td>
                            </tr>
                            <?php
                            $nImporte += $registro["importe"];
                            $nImporteT += $registro["importe"];
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3">Total</td>
                            <td><?= number_format($nImporte, 2) ?></td>
                        </tr>
                        <tr>
                            <td colspan="3"> Gran total</td>
                            <td><?= number_format($nImporteT, 2) ?></td>
                        </tr>
                    </tfoot>
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
