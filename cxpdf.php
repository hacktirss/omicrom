<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("importeletras.php");
include_once ("data/ProveedorDAO.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$sanitize = SanitizeUtil::getInstance();
$ProveedorDAO = new ProveedorDAO();

$Tabla = $sanitize->sanitizeString("T");

require "./services/ReportesProveedoresService.php";


$ProveedorVO = $ProveedorDAO->retrieve($Proveedor);


$Titulo = "Estado de cuenta del $FechaI al $FechaF";

$registros = utils\IConnection::getRowsFromQuery($selectCxp);

$registrosLandscape = 25;
$registrosVertical = 40;
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports_print.php"; ?> 
        <title><?= $Gcia ?></title>
        <style>
            @page { 
                size: A4 /*landscape*/; 
            }
        </style>
        <script type="text/javascript">
            $(document).ready(function () {

                $("#Descargar").click(function () {
                    var instance = new TableExport($("#TablaExcel"), {
                        formats: ["xlsx"],
                        ignoreCSS: ".tableexport-ignore",
                        trimWhitespace: true,
                        filename: "Estado de cuenta",
                        RTL: false,
                        bootstrap: true,
                        exportButtons: false
                    });
                    var exportData = instance.getExportData()["TablaExcel"]["xlsx"];
                    instance.export2file(exportData.data, exportData.mimeType, exportData.filename, exportData.fileExtension);
                });
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
                    <td style="text-align: right;">
                        <i id="Descargar" title="Descargar archivo Excel" class="icon fa fa-lg fa-file-excel-o" aria-hidden="true"></i>
                    </td>
                    <td style="text-align: center;"><i onclick="print();" title="Imprimir" class="icon fa fa-lg fa-print" aria-hidden="true"></i></td>
                </tr>
            </table>
        </div>
        <div id="TablaExcel">
            <!-- Each sheet element should have the class "sheet" -->
            <!-- "padding-**mm" is optional: you can set 10, 15, 20 or 25 -->
            <?php
            $nRng = 1;
            $close = false;
            $sheet = 0;
            if (count($registros) > 0) {
                foreach ($registros as $registro) {
                    if (($nRng - 1) % $registrosVertical == 0) {
                        $close = false;
                        $sheet++;

                        $ignore = "";
                        if ($sheet > 1) {
                            $ignore = "tableexport-ignore";
                        }
                        ?>
                        <div class="sheet padding-10mm"> <!-- Abre hoja-->
                            <?php nuevoEncabezadoPrint($Titulo) ?>
                            <div id="TablaDatosReporte"> <!-- Abre div estilos-->
                                <div style="padding-top: 10px;">
                                    <div style="padding-bottom: 10px;">Proveedor: <?=  $ProveedorVO->getId() ?> <?=  $ProveedorVO->getNombre() ?></div>
                                    <table aria-hidden="true"> <!-- Abre tabla 1-->
                                        <thead>
                                            <tr class="<?= $ignore ?>">
                                                <td>#</td>
                                                <td>Referencia</td>
                                                <td>Fecha</td>
                                                <td>Vencemiento</td>
                                                <td>Concepto</td>
                                                <td>Cargo</td>
                                                <td>Abono</td>
                                                <td>Saldo</td>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php
                                        }
                                        if ($nRng == 1) {
                                            $nRng += 1;
                                            ?>
                                            <tr>
                                                <td>1</td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td class="texto">SALDO INICIAL </font></td>
                                                <td class="numero tableexport-number"><?= number_format($Cargo, 2, ".", "") ?></td>
                                                <td class="numero tableexport-number"><?= number_format($Abono, 2, ".", "") ?></td>
                                                <td class="numero tableexport-number"><?= number_format($Cargo - $Abono, 2, ".", "") ?></td>
                                            </tr>

                                            <?php
                                        }
                                        $Cargo += $registro["cargo"];
                                        $Abono += $registro["abono"];
                                        ?>
                                        <tr>
                                            <td><?= $nRng ?></td>
                                            <td class="numero tableexport-number"><?= $registro["referencia"] ?></td>
                                            <td class="texto tableexport-date"><?= $registro["fecha"] ?></td>
                                            <td class="texto tableexport-string"><?= $registro["fechav"] ?></td>
                                            <td class="texto tableexport-string"><?= $registro["concepto"] ?></td>
                                            <td class="numero tableexport-number"><?= number_format($registro["cargo"], 2, ".", "") ?></td>
                                            <td class="numero tableexport-number"><?= number_format($registro["abono"], 2, ".", "") ?></td>
                                            <td class="numero tableexport-number"><?= number_format(($Cargo - $Abono), 2, ".", "") ?></td>

                                        </tr>

                                        <?php
                                        //error_log("Modulo $nRng: " . ($nRng % $registrosVertical));
                                        if ($nRng % $registrosVertical == 0) {
                                            if (($nRng - 1) == count($registros)) {
                                                
                                            } else {
                                                echo ''
                                                . '</tbody>'
                                                . '</table> <!-- Cierra tabla 1 si hay mas de 25 registros-->'
                                                . '</div>'
                                                . '</div> <!-- Cierra div estilos-->'
                                                . '</div> <!-- Cierra hoja si hay mas de 25 registros-->';
                                                $close = true;
                                            }
                                        }
                                        $nRng++;
                                    }
                                } else {

                                    echo '<div class="sheet padding-10mm"> <!-- Abre hoja--> ';
                                    nuevoEncabezadoPrint($Titulo);
                                    echo '<div id="TablaDatosReporte"> <!-- Abre div estilos--> '
                                    . '<div style="padding-top: 10px;">'
                                    . '<div style="padding-bottom: 10px;">Cliente: ' . $clienteVO->getId() . ' ' . $clienteVO->getNombre() . '</div>'
                                    . '<table aria-hidden="true"> <!-- Abre tabla 1--> '
                                    . '<thead>'
                                    . '<tr class="' . $ignore . '">'
                                    . '<td>#</td>'
                                    . '<td>Referencia</td>'
                                    . '<td>Placas</td>'
                                    . '<td>Fecha</td>'
                                    . '<td>Concepto</td>'
                                    . '<td>Factura</td>'
                                    . '<td>Cargo</td>'
                                    . '<td>Abono</td>'
                                    . '<td>Saldo</td>'
                                    . '</tr>'
                                    . '</thead>'
                                    . '<tbody>'
                                    . '<tr>'
                                    . '<td>1</td>'
                                    . '<td></td>'
                                    . '<td></td>'
                                    . '<td class="texto">' . $FechaI . '</font></td>'
                                    . '<td class="texto">SALDO INICIAL </font></td>'
                                    . '<td></td>'
                                    . '<td class="numero tableexport-number">' . number_format($Cargo, 2, ".", "") . '</td>'
                                    . '<td class="numero tableexport-number">' . number_format($Abono, 2, ".", "") . '</td>'
                                    . '<td class="numero tableexport-number">' . number_format($Cargo - $Abono, 2, ".", "") . '</td>'
                                    . '</tr>';
                                }

                                if (!$close) {
                                    ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5">TOTALES</td>
                                        <td class="moneda tableexport-number"><?= number_format($Cargo, 2, ".", "") ?></td>
                                        <td class="moneda tableexport-number"><?= number_format($Abono, 2, ".", "") ?></td>
                                        <td class="moneda tableexport-number"><?= number_format($Cargo - $Abono, 2, ".", "") ?></td>
                                    </tr>
                                </tfoot>
                            </table> <!-- Cierra tabla 1 si hay menos de 25 registros-->
                        </div>
                    </div> <!-- Cierra div estilos-->
                </div> <!-- Cierra hoja si hay mas de 25 registros-->
                <?php
            }
            ?>
        </div>
    </body>
</html>     
