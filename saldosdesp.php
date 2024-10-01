<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesDespachadoresService.php";

$Titulo = "Saldos por vendedor al $Fecha ";

$registros = utils\IConnection::getRowsFromQuery($selectSaldos);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#Fecha").val("<?= $Fecha ?>").attr("size", "10");
                $("#cFecha").css("cursor", "hand").click(function () {
                    displayCalendar($("#Fecha")[0], "yyyy-mm-dd", $(this)[0]);
                });
                if ("<?= $Todos ?>" == "Si") {
                    $("#Todos").prop('checked', true);
                }

            });
        </script>
    </head>

    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo) ?>
            <div id="Reportes">
                <table aria-hidden="true">
                    <thead>
                        <tr>
                            <td>#</td>
                            <td>No.Cta</td>
                            <td>Alias</td>
                            <td>Nombre</td>
                            <td>Saldo</td>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $cTipoPago = "";
                        $SubTotal = 0;
                        foreach ($registros as $registro) {
                            ?>

                            <tr>
                                <td><?= number_format($nRng + 1) ?></td>
                                <td><?= $registro["vendedor"] ?></td>
                                <td><?= ucwords(strtolower($registro["alias"])) ?></td>
                                <td class="overflow"><?= ucwords(strtolower($registro["nombre"])) ?></td>
                                <td class="numero"> <?= number_format($registro["importe"], 2) ?></td>
                            </tr>
                            <?php
                            $nRng++;
                            $nImporte += $registro["importe"];
                        }
                        $nImporteT += $nImporte;
                        $SubTotal += $nImporte;
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>Total</td>
                            <td class="moneda"><?= number_format($SubTotal, 2) ?></td>
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
                                <td style="width: 50%;">
                                    <table width="100%" aria-hidden="true">
                                        <tr>
                                            <td width="30%">Fecha: <input type="text" id="Fecha" name="Fecha"></td>
                                            <td class="calendario"><i id="cFecha" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
                                            <td style="padding-right: 30%;">Todos : <input type="checkbox" class="botonAnimatedMin" id="Todos" name="Todos" value="Si"></td>
                                        </tr>
                                    </table>
                                </td>
                                <td>
                                    <span><input type="submit" name="Boton" value="Enviar"></span>
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
