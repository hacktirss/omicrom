<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesClientesService.php";

$Titulo = "Consumos pendientes de facturar del cliente $SCliente ";

$registros = utils\IConnection::getRowsFromQuery($selectAntiguedadConsumos);

$registrosT = utils\IConnection::getRowsFromQuery($selectAntiguedadConsumosTotalesByProducto);

$cSql = $selectAntiguedadConsumos;
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <title><?= $Gcia ?></title>
        <script>

            $(document).ready(function () {

            });
        </script>
    </head>

    <body>
        <?php nuevoEncabezado($Titulo); ?>
        <div id="Reportes" style="min-height: 200px;"> 
            <table aria-hidden="true">
                <thead>
                    <tr>
                        <td></td>
                        <td>Isla/Disp.</td>
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
                        <td>Pago Real</td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $nRng = 0;
                    $cont = 1;
                    $uptitles = true;
                    $nImpR = $nImpC = $nLtsC = 0;
                    foreach ($registros as $rg) {
                        $style = "";

                        if (abs($rg["pesos"] - $rg["importe"]) > 0.5) {
                            $style = "style='background-color: #F7FF7C' title='El importe fue modificado'";
                        }
                        ?>
                        <tr <?= $style ?>>
                            <td><?= $cont++ ?></td>
                            <td><?= $rg[isla_pos] ?></td>
                            <td><?= $rg["id"] ?></td>
                            <td><?= $rg["corte"] ?></td>
                            <td><?= $rg["impreso"] ?></td>
                            <td><?= $rg["fecha"] ?></td>
                            <td><?= ucwords(strtoupper($rg["placas"])) ?></td>
                            <td><?= $rg["kilometraje"] ?></td>
                            <td class="overflow"><?= ucwords(strtolower($rg["descripcion"])) ?></td>
                            <td><?= $rg["producto"] ?></td>
                            <?php if ($rg["uuid"] !== "-----") { ?>
                                <td align="center" style="font-weight: bold;"><i class="fa fa-check-square-o" aria-hidden="true"></i></td>
                            <?php } else { ?>
                                <td align="center"><i class="fa fa-square-o" aria-hidden="true"></i></td>
                            <?php } ?>
                            <td class="numero"><?= number_format($rg["volumen"], 2) ?></td>
                            <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                            <td class="numero"><?= number_format($rg["pesos"], 2) ?></td>
                            <?php ?>
                        </tr>
                        <?php
                        $rImp += $rg["importe"];
                        $nImp += $rg["pesos"];
                        $nLts += $rg["volumen"];
                    }
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
                        <td></td> 
                        <td>Total</td>
                        <td></td>
                        <td><?= number_format($nLts, 2) ?></td>
                        <td><?= number_format($rImp, 2) ?></td>
                        <td><?= number_format($nImp, 2) ?></td>
                    </tr>

                </tfoot>
            </table>

        </div>

        <div id="Reportes" style="width: 50%;min-height: 150px;"> 
            <table aria-hidden="true">
                <thead>
                    <tr class="titulo"><td colspan="4">Totales por producto</td></tr>
                    <tr>
                        <td>Producto</td>
                        <td>Consumos</td>
                        <td>Litros</td>
                        <td>Importe</td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $Imp = $Lts = $Car = 0;
                    foreach ($registrosT as $rg) {
                        ?>
                        <tr>
                            <td><?= $rg["producto"] ?></td>
                            <td class="numero"><?= $rg["consumos"] ?></td>
                            <td class="numero"><?= number_format($rg["cantidad"], 2) ?></td>
                            <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                        </tr>
                        <?php
                        $Imp += $rg["importe"];
                        $Car += $rg["consumos"];
                        $nRng++;
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td><?= $Car ?></td>
                        <td></td>
                        <td><?= number_format($Imp, 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div id="footer">
        <form name="formActions" method="post" action="" id="form" class="oculto">
            <div id="Controles">
                <table aria-hidden="true">
                    <tbody>                        
                        <tr>
                            <td align="center">
                                <a href="antiguedadfac.php?criteria=ini">Antiguedad de saldos</a> 
                            </td>
                            <td>
                                <span><input type="submit" name="Boton" value="Enviar"></span>
                                <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>

                                <span class="ButtonExcel"><a href="bajarep.php?cSql=<?= urlencode($cSql) ?>"><i class="icon fa fa-lg fa-bold fa-file-excel-o" aria-hidden="true"></i></a></span>
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