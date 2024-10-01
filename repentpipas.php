<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("importeletras.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

$Titulo = "Registro de pipas capturadas del $FechaI al $FechaF";

$cSql = $selectPipas;

$registros = utils\IConnection::getRowsFromQuery($cSql);

$registrosT = utils\IConnection::getRowsFromQuery($selectTotales);

$Id = 34; /* Número de en el orden de la tabla submenus */
$data = array("Nombre" => $Titulo, "Reporte" => $Id, "FechaI" => $FechaI, "FechaF" => $FechaF);

$registrosLandscape = 25;
$registrosVertical = 40;
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports_print.php'; ?> 
        <title><?= $Gcia ?></title>
        <style>
            @page {
                size: A4 landscape;
            }
        </style>
    </head>

    <!-- Set "A5", "A4" or "A3" for class name -->
    <!-- Set also "landscape" if you need -->
    <body class="A4 landscape">
        <div class="iconos">
            <table aria-hidden="true">
                <tr>
                    <td style="text-align: left"><?= $Titulo ?></td>
                    <td>&nbsp;</td>
                    <?php
                    if ($usuarioSesion->getTeam() !== "Operador") {
                        ?>
                        <td style="text-align: right;">
                            <a href="report_excel_reports.php?<?= http_build_query($data) ?>"><i title="Descargar archivo Excel" class="icon fa fa-lg fa-file-excel-o" aria-hidden="true"></i></a>
                        </td>
                        <td style="text-align: center"><i onclick="print();" title="Imprimir" class='icon fa fa-lg fa-print' aria-hidden="true"></i></td>
                        <?php
                    }
                    ?>
                </tr>
            </table>
        </div>
        <div id="TablaExcel">
            <!-- Each sheet element should have the class "sheet" -->
            <!-- "padding-**mm" is optional: you can set 10, 15, 20 or 25 -->
            <?php
            $nRng = 1;
            $Num = 1;
            $close = false;
            $sheet = 0;

            $Litros = 0;
            $LitrosA = 0;
            $LitrosN = 0;
            $Dif = 0;
            $Importe = 0;
            $Iva = 0;
            $Total = 0;
            $object = "";
            $Gtotal = 0;
            $ImporteN = 0;
            foreach ($registros as $rg) {
                if ((($nRng - 1) % $registrosLandscape == 0) || $open) {
                    $open = false;
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
                                <table aria-hidden="true" style="max-width: 200px !important;"> <!-- Abre tabla 1-->
                                    <thead>
                                        <tr  class="<?= $ignore ?>">
                                            <td>#</td>
                                            <td>Entrada</td>
                                            <td>No.Fact</td>
                                            <td>Terminal</td>
                                            <td>Transportista</td>
                                            <td>Fec.Entrada</td>
                                            <td>Fec.Captura</td>
                                            <td>Producto</td>
                                            <td>Cantidad doc.</td>
                                            <td>Aumt. Bruto</td>
                                            <td>Aumt. Neto</td>
                                            <td>Diferencia</td>
                                            <td>Importe</td>
                                            <td>Iva</td>
                                            <td>Neto</td>
                                            <td>Total</td>
                                            <td>V.Dev</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                    }
                                    $sql = "SELECT me.id,me.foliofac,me.volumenfac,prv.nombre,me.uuid,me.tipocomprobante FROM me inner join prv on me.proveedor = prv.id WHERE me.carga = '" . $rg["carga"] . "'";
                                    $query = $mysqli->query($sql);
                                    $Class = $query->num_rows > 1 ? "color" : "";
                                    ?>
                                    <tr class="">              
                                        <td><?= $Num ?></td>
                                        <td><?= $rg["entrada"] ?></td>
                                        <td><?= $rg["factura"] ?></td>
                                        <td><?= $rg["terminal"] ?></td>
                                        <td class="texto"><?= $rg["proveedorTransporte"] ?></td>
                                        <td><?= $rg["fechaEntrada"] ?></td>
                                        <td><?= $rg["fechaCaptura"] ?></td>
                                        <td><?= ucwords(strtolower($rg["producto"])) ?></td>
                                        <td class="numero"><?= number_format($rg["cantidadDocumentada"] * 1000, 3) ?></td>
                                        <td class="numero"><?= number_format($rg["incremento"], 0) ?></td>
                                        <td class="numero"><?= number_format($rg["bruto"], 0) ?></td>
                                        <td class="numero"><?= number_format($rg["diferencia"], 3) ?></td>
                                        <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                                        <td class="numero"><?= number_format($rg["iva"], 2) ?></td>
                                        <td class="numero"><?= number_format($rg["precioCompra"], 2) ?></td>
                                        <td class="numero"><?= number_format($rg["total"], 2) ?></td>
                                        <td class="numero"><?= number_format($rg["vd"], 2) ?></td>
                                    </tr>
                                    <?php
                                    //if ($query->num_rows > 1) {
                                    while (($dt = $query->fetch_array())) {
                                        ?>
                                        <tr class="subt">
                                            <td><?= $dt["id"] ?></td>
                                            <td><?= $dt["tipocomprobante"] === "E" ? "Nota Credito" : "Ingreso" ?> </td>
                                            <td><?= $dt["foliofac"] ?></td>
                                            <td colspan="4"><?= "L : " . $rg["lote"] . "; F:" . $rg["numeroFolio"] ?></td>
                                            <td>Volumen : </td>
                                            <td><?= number_format($dt["volumenfac"] * 1000, 2) ?></td>
                                            <td colspan="4"><?= $dt["nombre"] ?></td>
                                            <td colspan="4"><?= $dt["uuid"] ?></td>
                                        </tr>
                                        <?php
                                        if ($nRng % $registrosLandscape == 0) {
                                            echo ''
                                            . '</tbody>'
                                            . '</table> <!-- Cierra tabla 1 si hay mas de 25 registros-->'
                                            . '</div>'
                                            . '</div> <!-- Cierra div estilos-->'
                                            . '</div> <!-- Cierra hoja si hay mas de 25 registros-->';
                                            $close = true;
                                            $open = true;
                                        }
                                        $nRng++;
                                    }
                                    //}
                                    ?>
                                    <?php
                                    $Litros += $rg["cantidadDocumentada"];
                                    $LitrosA += $rg["incremento"];
                                    $LitrosN += $rg["bruto"];
                                    $Dif += $rg["diferencia"];
                                    $Importe += $rg["importe"];
                                    $Iva += $rg["iva"];
                                    $Total += $rg["total"];
                                    $object = "";
                                    $Gtotal += $rg["total"];
                                    if ($nRng % $registrosLandscape == 0) {
                                        echo ''
                                        . '</tbody>'
                                        . '</table> <!-- Cierra tabla 1 si hay mas de 25 registros-->'
                                        . '</div>'
                                        . '</div> <!-- Cierra div estilos-->'
                                        . '</div> <!-- Cierra hoja si hay mas de 25 registros-->';
                                        $close = true;
                                    }
                                    $nRng++;
                                    $Num++;
                                }
                                if (!$close) {
                                    ?>
                                </tbody>
                                <tfoot style="font-size: 7pt;">
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
                                        <td><?= number_format($Litros * 1000, 3) ?></td>
                                        <td><?= number_format($LitrosA, 3) ?></td>
                                        <td><?= number_format($LitrosN, 3) ?></td>
                                        <td><?= number_format($Dif, 3) ?></td>    
                                        <td><?= number_format($Importe, 2) ?></td>
                                        <td><?= number_format($Iva, 2) ?></td>
                                        <td><?= number_format($Total, 2) ?></td>
                                    </tr>
                                </tfoot>
                            </table> <!-- Cierra tabla 1 si hay menos de 25 registros-->
                        </div>
                    </div> <!-- Cierra div estilos-->
                    <?php
                }
                error_log("operacion: " . (($registrosLandscape * $sheet) - $nRng));
                if ((($registrosLandscape * $sheet) - $nRng) < 7) {
                    ?>
                </div> <!-- Cierra hoja si hay mas de 25 registros-->
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

                        <div style="width: 60%;padding-top: 10px;min-height: 200px;margin-left: auto;margin-right: auto;">
                            <div><h3>C o n c e n t r a d o </h3></div>
                            <table aria-hidden="true">
                                <thead>
                                    <tr class="tableexport-ignore">
                                        <td></td>
                                        <td>Cargas</td>
                                        <td>Litros Facturados</td>
                                        <td>Litros NC</td>
                                        <td>Aumento Bruto (Ltrs)</td>
                                        <td>Aumento Neto (Ltrs)</td>
                                        <td>Aumento Merma (Ltrs)</td>
                                        <td>Importe Ingreso</td>
                                        <td>Importe Egreso</td>
                                        <td>Importe Neto</td>
                                        <td>Importe Total</td>

                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $merma = 0;
                                    $Cargas = $Litros = $Importe = $LitrosA = $LitrosN = $ImporteN = 0;
                                    foreach ($registrosT as $rg) {
                                        ?>
                                        <tr class="tableexport-ignore">                 
                                            <td><?= $rg["descripcion"] ?></td>
                                            <td class="numero"><?= number_format($rg["cargas"], 0) ?></td>
                                            <td class="numero"><?= number_format($rg["volumenfac"] * 1000, 0) ?></td>
                                            <td class="numero"><?= number_format($rg["volumenfacnc"] * 1000, 0) ?></td>
                                            <td class="numero"><?= number_format($rg["incremento"], 0) ?></td>
                                            <td class="numero"><?= number_format($rg["neto"], 0) ?></td>
                                            <td class="numero"><?= number_format($rg["aumento_neto"], 0) ?></td>
                                            <td class="numero">$<?= number_format($rg["importeIngreso"], 2) ?></td>
                                            <td class="numero">$<?= number_format($rg["importeEgreso"], 2) ?></td>
                                            <td class="numero">$<?= number_format($rg["importeNet"], 2) ?></td>
                                            <td class="numero">$<?= number_format($rg["importeIngreso"] - $rg["importeEgreso"], 2) ?></td>

                                        </tr>
                                        <?php
                                        $Cargas += $rg["cargas"];
                                        $Litros += $rg["volumenfac"];
                                        $LitrosNc += $rg["volumenfacnc"];
                                        $Importe += $rg["importeIngreso"] - $rg["importeEgreso"];
                                        $LitrosA += $rg["incremento"];
                                        $LitrosN += $rg["neto"];
                                        $ImporteN += $rg["importeNet"];
                                        $merma += $rg["aumento_neto"];
                                        $TImpIng += $rg["importeIngreso"];
                                        $TImpEgr += $rg["importeEgreso"];
                                    }
                                    ?>
                                </tbody>
                                <tfoot>
                                    <tr class="tableexport-ignore">
                                        <td>Gran Total</td>
                                        <td><?= number_format($Cargas, 0) ?></td>
                                        <td><?= number_format(($Litros) * 1000, 0) ?></td>
                                        <td><?= number_format(($LitrosNc) * 1000, 0) ?></td>
                                        <td><?= number_format($LitrosA, 0) ?></td>
                                        <td><?= number_format($LitrosN, 0) ?></td>
                                        <td><?= number_format($merma, 0) ?></td>
                                        <td>$<?= number_format($TImpIng, 2) ?></td>
                                        <td>$<?= number_format($TImpEgr, 2) ?></td>
                                        <td>$<?= number_format($ImporteN, 2) ?></td>
                                        <td>$<?= number_format($Importe, 2) ?></td>

                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <?php
                        if (!$close) {
                            ?>
                        </div> <!-- Cierra div estilos-->
                    </div> <!-- Cierra hoja si hay mas de 25 registros-->
                    <?php
                }
                ?>
            </div>
            <script>
                $(document).ready(function () {
                    var num = 1;
                    $(".subt").show();

                    $(".color").click(function () {
                        if (Number.isInteger(num / 2)) {
                            $(".subt").show();
                        } else {
                            $(".subt").hide();
                        }
                        num++;
                    });


                });
            </script>
    </body>
</html>     

