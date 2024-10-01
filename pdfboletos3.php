<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("importeletras.php");
include_once ("data/SysFilesDAO.php");

require_once ("com/softcoatl/cfdi/utils/NumericalCurrencyConverter.php");
require_once ("com/softcoatl/cfdi/utils/Currency.php");
require_once ("com/softcoatl/cfdi/utils/SpanishNumbers.php");

use com\softcoatl\utils as utils;
use com\softcoatl\cfdi\utils\NumericalCurrencyConverter;
use com\softcoatl\cfdi\utils\SpanishNumbers;
use com\softcoatl\cfdi\utils\Currency;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$usuarioSesion = getSessionUsuario();

$converter = new NumericalCurrencyConverter(new SpanishNumbers(), new Currency('PESOS', 'PESO'));

$busca = $request->getAttribute("busca");

$ciaDAO = new CiaDAO();
$sysFilesDAO = new SysFilesDAO();
$sysFilesVO = $sysFilesDAO->retrieve("fc_img");

$ciaVO = $ciaDAO->retrieve(1);
$logo = $sysFilesVO->getFile();

$selectCliente = "SELECT genbol.cliente,genbol.fecha,cli.nombre,cli.direccion,genbol.fechav 
                  FROM cli,genbol WHERE genbol.id = '$busca' AND genbol.cliente = cli.id";
$CliA = $mysqli->query($selectCliente);
$Cli = $CliA->fetch_array();

$Dato1 = ucwords(strtolower($ciaVO->getCia())) . " Suc: " . $ciaVO->getEstacion();
$Dato2 = " R.f.c.: " . $ciaVO->getRfc();
$Dato3 = empty($ciaVO->getTelefono()) ? "" : " Tel." . $ciaVO->getTelefono();
$Dato5 = $Cli["nombre"];

$selectFirst = "SELECT idnvo FROM boletos WHERE boletos.id = '$busca' ORDER BY idnvo ASC";
$first = $mysqli->query($selectFirst)->fetch_array();
$Operador = "<>";
if ($first["idnvo"] % 2 == 0) {
    $Operador = "=";
}

$selectVales = "SELECT boletos.idnvo,boletos.codigo,boletos.importe,boletos.secuencia,genbol.fecha,genbol.fechav,
                IFNULL(SUB.codigo,0) codigoR ,IFNULL(SUB.secuencia,0) secuenciaR ,IFNULL(SUB.importe,0) importeR
                FROM genbol,boletos 
                LEFT JOIN boletos SUB ON boletos.id = SUB.id AND boletos.idnvo + 1 = SUB.idnvo
                WHERE  boletos.id = genbol.id AND boletos.id = '$busca' AND boletos.idnvo % 2 $Operador 0
                ORDER BY boletos.idnvo;";
$result = $mysqli->query($selectVales);

$Titulo = "Vales de combustible";
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
        <?php
        $contador = 0;
        $paginaInicio = true;
        $paginaFin = false;
        $sheet = 0;
        while ($Boletos = $result->fetch_array()) {
            if ($paginaInicio) {
                ?>
                <div class="sheet padding-10mm">
                    <div id="content">
                        <?php
                        //nuevoEncabezadoPrint("");
                        $paginaInicio = false;
                        $sheet++;
                    }
                    ?>
                    <table style="width: 100%;padding-bottom: 2px;text-align: center" aria-hidden="true">
                        <tr>
                            <td style="width: 90%;">
                                <div id="boletos" style="float: right; width: 19cm;">
                                    <table aria-hidden="true">
                                        <tbody>
                                            <tr>
                                                <td rowspan="6"><img src="data:image/jpeg;base64,<?= base64_encode($logo) ?>" class="logo" alt=""></td>
                                                <td><?= $Dato1 ?></td>
                                            </tr>
                                            <tr><td><?= $Dato2 . " " . $Dato3 ?></td></tr>
                                            <tr><td><strong>Vale de combustible</strong></td></tr>
                                            <tr><td>Emisión: <?= $Cli["fecha"] ?>Expira al: <?= $Cli["fechav"] ?></td></tr> 
                                            <tr><td style="font-size: 12px;"><strong>Bueno por $ <?= number_format($Boletos["importeR"], 2) ?></strong></td></tr>            
                                            <tr><td colspan="2"><?= impletras($Boletos["importeR"], 'pesos') ?></td></tr>            
                                            <tr><td colspan="2"><?= $Dato5 ?></td></tr>            
                                            <tr>
                                                <td>Sec: <?= $Boletos["secuenciaR"] ?></td>
                                                <td>
                                                    <img src="phpbarcode/barcode.php?f=svg&s=code128a&d=<?= $Boletos["codigoR"] ?>&w=220&h=50&pt=1&pb=13&ts=12&ls=8" alt=""/>
                                                </td>
                                            </tr> 
                                        </tbody>
                                    </table>    
                                </div>
                            </td>
                        </tr>
                    </table>

                    <?php
                    $contador++;
                    if ($contador % 5 == 0) {
                        $paginaInicio = true;
                        $paginaFin = true;
                        //sleep(1);
                    }

                    if ($paginaFin) {
                        ?>
                        <div id="pageFooter">Página <?= $sheet; ?></div>
                    </div>
                </div>
                <?php
                $paginaFin = false;
            }
        }
        ?>
    </body>
</html>     

