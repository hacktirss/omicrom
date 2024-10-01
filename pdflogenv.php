<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("importeletras.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$usuarioSesion = getSessionUsuario();

$busca = $request->getAttribute("busca");
$tipo = $request->getAttribute("Tipo");

$ciaDAO = new CiaDAO();
$ciaVO = $ciaDAO->retrieve(1);

$select = "SELECT fecha_informacion informacion, generacion, envio, codigodeenvio codigo, checksum, resp_pemex, nombrearchivo archivo FROM logenvios20 WHERE id = '$busca'";
if($tipo == 2){
    $select = "SELECT fecha_informacion informacion, fecha_generacion generacion, fecha_envio envio, codigo_envio codigo, '' checksum, '' resp_pemex, nombre_archivo archivo FROM log_envios_sat WHERE id = '$busca'";
}
$Cpo = $mysqli->query($select)->fetch_array();

$months = array();
setlocale(LC_TIME, 'es_MX.UTF-8');
for ($m = 1; $m <= 12; $m++) {
    $months[cZeros($m, 2, "LEFT")] = strftime("%B", mktime(0, 0, 0, $m, 12));
}
$cFecha = $ciaVO->getColonia() . " " . $ciaVO->getCiudad() . " a " . date("d") . " de " . $months[date("m")] . " de " . date("Y");

$Titulo = "Acuse de recibo de archivo de control volumetrico";
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports_print.php'; ?> 
        <title><?= $Gcia ?></title>
        <style>
            @page { 
                size: A4 /*landscape*/; 
            }
        </style>
        <script type="text/javascript">
            $(document).ready(function () {

            });
        </script>

    </head>

    <!-- Set "A5", "A4" or "A3" for class name -->
    <!-- Set also "landscape" if you need -->
    <body class="A4">
        <div class="iconos">
             <table aria-hidden="true">
                <tr>
                    <td style="text-align: left"><?= $Titulo ?></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="text-align: center"><i onclick="print();" title="Imprimir" class='icon fa fa-lg fa-print' aria-hidden="true"></i></td>
                </tr>
            </table>
        </div>
        <!-- Each sheet element should have the class "sheet" -->
        <!-- "padding-**mm" is optional: you can set 10, 15, 20 or 25 -->
        <div class="sheet padding-10mm">

            <?php nuevoEncabezadoPrint(null) ?>

            <br/><p style="text-align: center">&nbsp;</p>
            <table style="width: 100%;" aria-hidden="true">
                <tr><td style="text-align: center"  height="35"><h3>Acuse de recibo de archivo de control volumetrico</h3></td></tr>
                <tr><td style="text-align: center"><?= $cFecha ?></td></tr>
            </table>

            <table style="width: 100%;" aria-hidden="true">
                <tr><td style="text-align: right;height: 80px;width: 300px;"><h2>Fecha de informacion: </h2></td><td><?= $Cpo["informacion"] ?></td></tr>
                <tr><td style="text-align: right;height: 80px;width: 300px;"><h2>Fecha de generacion de datos: </h2></td><td><?= $Cpo["generacion"] ?></td></tr>
                <tr><td style="text-align: right;height: 80px;width: 300px;"><h2>Nombre del archivo generado: </h2></td><td><?= $Cpo["archivo"] ?></td></tr>
                <tr><td style="text-align: right;height: 80px;width: 300px;"><h2>Fecha de envio: </h2></td><td><?= $Cpo["envio"] ?></td></tr>
                <tr><td style="text-align: right;height: 80px;width: 300px;"><h2>Checksum del archivo: </h2></td><td><?= $Cpo["checksum"] ?></td></tr>
                <tr><td style="text-align: right;height: 80px;width: 300px;"><h2>Respuesta pemex: </h2></td><td><?= $Cpo["resp_pemex"] ?></td></tr>
            </table>

            <br/><p style="text-align: center">----------- ~ ----------</p>

        </div>
    </body>
</html>     

