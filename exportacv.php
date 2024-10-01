<?php
#Variables comunes;
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

$request = utils\HTTPUtils::getRequest();

$busca = $request->getAttribute("busca");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Titulo = "Descarga de CV por rango de fecha";
$Return = "exportacvd.php";

if ($request->hasAttribute("op")) {
    $Titulo = "Reporte de envio de archivos";
    $Return = "impreplogenvios.php";
}
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports.php'; ?> 
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
                $('#Tipo').val('<?= $Tipo ?>');
            });
        </script>

    </head>

    <body>

        <div id="container">
            <?php nuevoEncabezado($Titulo) ?>    
            <form name="form1" method="post" action="<?= $Return ?>">
                <div id="PideDatos">
                    <div><?= $Titulo ?></div>

                     <table aria-hidden="true">
                        <thead>
                            <tr>
                                <td colspan="3">Favor de pedir un rango no mayor a 10 dias</td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="text-align: right">F.inicial:</td>
                                <td><input type="text" id="FechaI" name="FechaI"></td>
                                <td class="calendario" style="text-align: left"><i id="cFechaI" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
                            </tr>
                            <tr>
                                <td style="text-align: right">F.final:</td>
                                <td><input type="text" id="FechaF" name="FechaF"></td>
                                <td class="calendario" style="text-align: left"><i id="cFechaF" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
                            </tr>
                            <?php
                            if (!($request->hasAttribute("op"))) {
                                ?>
                                <tr>
                                    <td style="text-align: center" colspan="3">
                                        *Apartir de Enero del 2015 se cambio a la version 1.1*
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        <select name="cv">
                                            <option value='2'>Volumetrico 1.1</option>
                                            <option value='1'>Volumetrico 1.0</option>
                                        </select>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>

                            <tr>
                                <td colspan="3">
                                    <span><input type="submit" name="Boton" value="Enviar"></span>
                                </td>
                            </tr>
                        <tbody>
                    </table>
                    <div class="mensajes"><?= $Msj ?></div>
                </div>
            </form>
        </div>
    </body>
</html>
