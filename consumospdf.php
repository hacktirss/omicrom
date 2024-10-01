<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("importeletras.php");
include_once ("data/ClientesDAO.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$sanitize = SanitizeUtil::getInstance();
$clientesDAO = new ClientesDAO();

require "./services/ReportesClientesService.php";

$clienteVO = $clientesDAO->retrieve($Cliente);

$Titulo = "Consumos del $FechaI al $FechaF";

$registros = utils\IConnection::getRowsFromQuery($selectConsumos);

$registrosT = utils\IConnection::getRowsFromQuery($selectConsumosTotalesByProducto);

$registrosAd = utils\IConnection::getRowsFromQuery($selectConsumosAditivos);

$registrosLandscape = 25;
$registrosVertical = 25;
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports_print.php"; ?> 
        <title><?= $Gcia ?></title>
        <style>
            @page { 
                size: A4 landscape; 
            }
        </style>
        <script type="text/javascript">
            $(document).ready(function () {

            });
        </script>
    </head>

    <!-- Set "A5", "A4" or "A3" for class name -->
    <!-- Set also "landscape" if you need -->
    <body class="A4 landscape">
        <div class="iconos">
            <table aria-hidden="true">
                <tr>
                    <td style="text-align: left"><?= $Titulo ?></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="text-align: center"><i onclick="print();" title="Imprimir" class="icon fa fa-lg fa-print" aria-hidden="true"></i></td>
                </tr>
            </table>
        </div>
        <!-- Each sheet element should have the class "sheet" -->
        <!-- "padding-**mm" is optional: you can set 10, 15, 20 or 25 -->
        <?php
        $nRng = 1;
        $cont = 1;
        $close = false;
        $sheet = 0;
        foreach ($registros as $rg) {
            if (($nRng - 1) % $registrosVertical == 0) {
                $close = false;
                $sheet++;
                ?>
                <div class="sheet padding-10mm"> <!-- Abre hoja-->
                    <?php nuevoEncabezadoPrint($Titulo) ?>
                    <div id="TablaDatosReporte"> <!-- Abre div estilos-->
                        <div style="padding-top: 10px;">
                            <div style="padding-bottom: 10px;">Cliente: <?= $clienteVO->getId() ?> <?= $clienteVO->getNombre() ?></div>
                            <table aria-hidden="true"> <!-- Abre tabla 1-->
                                <thead>
                                    <tr>
                                        <td></td>
                                        <td>Ticket</td>
                                        <td>Corte</td>
                                        <td>Codigo</td>
                                        <td>Fecha</td>
                                        <td>No.placas</td>
                                        <td>Km.</td>
                                        <td>Descripcion</td>
                                        <td>Producto</td>
                                        <td>Fac</td>
                                        <td>Litros</td>
                                        <td>Importe</td>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php
                                }
                                ?>
                                <tr>
                                    <td><?= $cont ?></td>
                                    <td><?= $rg["ticket"] ?></td>
                                    <td><?= $rg["corte"] ?></td>
                                    <td><?= $rg["impreso"] ?></td>
                                    <td><?= $rg["fecha"] ?></td>
                                    <td><?= ucwords(strtoupper($rg["placas"])) ?></td>
                                    <td><?= $rg["kilometraje"] ?></td>
                                    <td class="overflow" style="text-align: left;"><?= ucwords(strtolower($rg["descripcion"])) ?></td>
                                    <td style="text-align: left;"><?= $rg["producto"] ?></td>
                                    <?php if ($rg["uuid"] !== "-----") { ?>
                                        <td align="center" style="font-weight: bold;"><i class="fa fa-check-square-o" aria-hidden="true"></i></td>
                                    <?php } else { ?>
                                        <td align="center"><i class="fa fa-square-o" aria-hidden="true"></i></td>
                                    <?php } ?>
                                    <td class="numero"><?= number_format($rg["volumen"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["pagoreal"], 2) ?></td>
                                    <?php ?>
                                </tr>
                                <?php
                                $nImp += $rg["pagoreal"];
                                $nLts += $rg["volumen"];
                                $cont++;
                                ?>  

                                <?php
                                if ($nRng % $registrosVertical == 0) {
                                    echo ''
                                    . '</tbody>'
                                    . '</table> <!-- Cierra tabla 1 si hay mas de 25 registros-->'
                                    . '</div>'
                                    . '</div> <!-- Cierra div estilos-->'
                                    . '</div> <!-- Cierra hoja si hay mas de 25 registros-->';
                                    $close = true;
                                }
                                $nRng++;
                            }
                            if (!$close) {
                                ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>                                        
                                    <td></td>
                                    <td>Total</td>
                                    <td></td> 
                                    <td><?= number_format($nLts, 2) ?></td>
                                    <td><?= number_format($nImp, 2) ?></td>
                                </tr>
                            </tfoot>
                        </table> <!-- Cierra tabla 1 si hay menos de 25 registros-->
                    </div>
                </div> <!-- Cierra div estilos-->
                <?php
            }

            if ((($registrosVertical * $sheet) % $nRng ) < 7) {
                ?>
            </div> <!-- Cierra hoja si hay mas de 40 registros-->
            <div class="sheet padding-10mm"> <!-- Abre hoja-->
                <div id="TablaDatosReporte"> <!-- Abre div estilos-->
                    <?php
                    $close = false;
                } else {
                    ?>
                    <div id="TablaDatosReporte"> <!-- Abre div estilos-->
                        <?php
                    }
                    ?>

                    <div style="width: 50%;padding-top: 10px;min-height: 200px;margin-left: auto;margin-right: auto;">
                        <div><h3>Totales por producto</h3></div>
                        <table aria-hidden="true">
                            <thead>
                                <tr>
                                    <td>Producto</td>
                                    <td>Consumo</td>
                                    <td>Litros</td>
                                    <td>Importe</td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $Cargas = $Litros = $Importe = $LitrosA = 0;
                                foreach ($registrosT as $rg) {
                                    ?>
                                    <tr>                 
                                        <td><?= $rg["producto"] ?></td>
                                        <td class="numero"><?= $rg["cargas"] ?></td>
                                        <td class="numero"><?= number_format($rg["volumen"], 2) ?></td>
                                        <td class="numero"><?= number_format($rg["pesos"], 2) ?></td>
                                    </tr>
                                    <?php
                                    $Imp += $rg["pesos"];
                                    $Lts += $rg["volumen"];
                                    $Car += $rg["cargas"];
                                    $nRng++;
                                }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td>Total</td>
                                    <td><?= $Car ?></td>
                                    <td><?= number_format($Lts, 2) ?></td>
                                    <td><?= number_format($Imp, 2) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                        
                        <div><h3>Total de aditivos por cliente</h3></div>

                        <table aria-hidden="true">
                            <thead>
                                <tr>
                                    <td>Cliente</td>
                                    <td>Nombre</td>
                                    <td>Importe</td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $Imp = $Lts = $Car = 0;
                                foreach ($registrosAd as $rg) {
                                    ?>
                                    <tr>
                                        <td><?= $rg["cliente"] ?></td>
                                        <td><?= $rg["nombre"] ?></td>
                                        <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                                    </tr>
                                    <?php
                                    $Imp += $rg["importe"];
                                    $Cnt += $rg["cantidad"];
                                }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2">Total</td>
                                    <td><?= number_format($Imp, 2) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <?php
                    if (!$close) {
                        ?>
                    </div> <!-- Cierra div estilos-->
                </div> <!-- Cierra hoja si hay mas de 40 registros-->
                <?php
            }
            ?>
    </body>
</html>     
