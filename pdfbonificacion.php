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
$ciaDAO = new CiaDAO();
$ciaVO = $ciaDAO->retrieve(1);

$sql = "SELECT p.id,DATE(p.fecha)fecha,TIME(p.fecha) hora,cli.nombre,inv.descripcion,p.cliente,p.puntos "
        . "FROM puntos p,cli,inv "
        . "WHERE p.cliente=cli.id AND p.producto=inv.id AND p.id='$busca'";
$Cpo = $mysqli->query($sql)->fetch_array();

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

            <table style="width: 100%;font-weight: bold;text-align: right" aria-hidden="true">
                <tr><td><?= $cFecha ?></td></tr>
            </table>

            <table style="text-align: center" aria-hidden="true">
                <tr><td style="width: 300px;height: 80px;text-align: right;"><h2>Recibo por binificacion No.: </h2></td><td align="left"><h3><?= $busca ?></h3></td></tr>
                <tr><td style="width: 300px;height: 80px;text-align: right;"><h2>Fecha: </h2></td><td align="left"><h3><?= $Cpo["fecha"] ?></h3></td></tr>
                <tr><td style="width: 300px;height: 80px;text-align: right;"><h2>Hora: </h2></td><td align="left"><h3><?= $Cpo["hora"] ?></h3></td></tr>
                <tr><td style="width: 300px;height: 80px;text-align: right;"><h2>No.cuenta: </h2></td><td align="left"><h3><?= $Cpo["cliente"] ?></h3></td></tr>
                <tr><td style="width: 300px;height: 80px;text-align: right;"><h2>Nombre: </h2></td><td align="left"><h3><?= $Cpo["nombre"] ?></h3></td></tr>
                <tr><td style="width: 300px;height: 80px;text-align: right;"><h2>Puntos consumidos: </h2></td><td align="left"><h3><?= number_format($Cpo["puntos"], 0) ?></h3></td></tr>
                <tr><td style="width: 300px;height: 80px;text-align: right;"><h2>Concepto: </h2></td><td align="left"><h3><?= $Cpo["descripcion"] ?></h3></td></tr>

            </table>

            <br/>

            <p style="text-align: center">-----------------------------------------------------------</p>
            <p style="text-align: center" style="border-top: solid 1px white;"><?= $ciaVO->getCia() ?></p>

        </div>
    </body>
</html>     

