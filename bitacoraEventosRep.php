<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesClientesService.php";

$Titulo = "Consulta de operaciones del $FechaI al $FechaF";

$registros = utils\IConnection::getRowsFromQuery($selectBitacora);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <link type="text/css" rel="stylesheet"  href="bootstrap/bootstrap-4.0.0/dist/css/bootstrap-grid.css"/>
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
                $("#Descripcion").val("<?= $Descripcion ?>");
                $("#Descripcion").focus();

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
                            <td>#</td>
                            <td>Evento</td>
                            <td>Fecha</td>
                            <td>Hora</td>
                            <td>Usuario</td>
                            <td>Descripcion</td>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $nRng = 0;
                        foreach ($registros as $rg) {
                            ?>
                            <tr>
                                <td><?= ++$nRng; ?></td>
                                <td><?= $rg["numero_evento"] ?></td>
                                <td><?= $rg["fecha_evento"] ?></td>
                                <td><?= $rg["hora_evento"] ?></td>
                                <td><?= $rg["usuario"] ?></td>
                                <td class="overflow"><?= $rg["descripcion_evento"] ?></td>
                            </tr>
                            <?php
                            $nImp += $rg["importe"];
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
                        <tr>
                            <td colspan="100%" class="container no-margin">
                                <div class="row no-padding">
                                    <div class="col-12"><input type="search" name="Descripcion" id="Descripcion" style="width: 100%" placeholder="Busqueda de informacion"></div>
                                    <div class="col-12" style="text-align: left;">
                                        <p><strong>Como realizar las busquedas:</strong></p>
                                        <ul>
                                            <li>Texto simple. Ej. "Concepto a buscar"</li>
                                            <li>Texto avanzado. Ej. "Concepto a buscar 1 | Concepto a buscar 2 | ... | Concepto a buscar n". El uso de la barra o pipe "<strong>|</strong>" es indispensable para separar cada concepto</li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr style="height: 40px;">
                            <td>
                                <table aria-hidden="true">
                                    <tr>
                                        <td>F.inicial:</td>
                                        <td><input type="text" id="FechaI" name="FechaI"></td>
                                        <td class="calendario"><i id="cFechaI" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
                                    </tr>
                                    <tr>
                                        <td>F.final:</td>
                                        <td><input type="text" id="FechaF" name="FechaF"></td>
                                        <td class="calendario"><i id="cFechaF" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
                                    </tr>
                                </table>
                            </td>                            
                            <td>
                                <span><input type="submit" name="Boton" value="Enviar"></span>
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

