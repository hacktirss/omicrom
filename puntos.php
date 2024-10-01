<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesClientesService.php";

$Titulo = "Puntos  del $FechaI al $FechaF ";

$registros = utils\IConnection::getRowsFromQuery($selectPuntos);

$registrosT = utils\IConnection::getRowsFromQuery($selectPuntosT);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports.php'; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#autocomplete").val("<?= $SCliente ?>")
                        .attr("size", "50")
                        .attr("placeholder", "* Favor de buscar al cliente de Puntos *")
                        .click(function () {
                            this.select();
                        }).focus()
                        .activeComboBox(
                                $("[name=\"form1\"]"),
                                "SELECT id as data, CONCAT(id, \' | \', mid(nombre,1,50)) value FROM cli WHERE id>=10 AND tipodepago LIKE \'Puntos\'",
                                "nombre");
                $("#FechaI").val("<?= $FechaI ?>").attr("size", "10");
                $("#FechaF").val("<?= $FechaF ?>").attr("size", "10");
                $("#cFechaI").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaI")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#cFechaF").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaF")[0], "yyyy-mm-dd", $(this)[0]);
                });
            });
        </script>


    </head>

    <body>
        <div id="container">
            <?php
            nuevoEncabezado($Titulo);
            ?>

            <div id="Reportes" style="min-height: 150px;">
                 <table aria-hidden="true">
                    <thead>
                        <tr>
                            <td>#</td>
                            <td>No.ticket</td>
                            <td>No.tarjeta</td>
                            <td>Fecha</td>
                            <td>No.placas</td>
                            <td>Descripcion</td>
                            <td>Producto</td>
                            <td>Puntos</td>
                            <td>Litros</td>
                            <td>Importe</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $codigo = 0;
                        $cont = 1;
                        $contCliente = 1;
                        $cli = "";
                        foreach ($registros as $rg) {

                            if ($rg["cliente"] !== $cli) {
                                ?>
                                <tr class="subtitulo">
                                    <td colspan="10">*** <?= $rg["cliente"] ?> | <?= $rg["nombre"] ?> ***</td>
                                </tr>
                                <?php
                            }

                            if ($codigo != 0 && $codigo != $rg["codigo"]) {
                                ?>
                                <tr class="subtotal">
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td> #<?= $contCliente ?> Total</td>
                                    <td class="numero"><?= number_format($Puntos, 0) ?></td>
                                    <td class="numero"><?= number_format($Lts, 2) ?></td>
                                    <td class="moneda"><?= number_format($Imp, 2) ?></td>
                                </tr>
                                <?php
                                $Puntos = $Imp = $Lts = 0;
                                $contCliente++;
                            }
                            ?>

                            <tr>
                                <td><?= $cont ?></td>
                                <td><?= $rg["id"] ?></td>
                                <td class="overflow"><?= $rg["impreso"] ?></td>
                                <td class="overflow"><?= $rg["fecha"] ?></td>
                                <td class="overflow"><?= $rg["placas"] ?></td>
                                <td class="overflow"><?= $rg["descripcion"] ?></td>
                                <td><?= $rg["producto"] ?></td>
                                <td class="numero"><?= number_format($rg["puntos"], 0) ?></td>
                                <td class="numero"><?= number_format($rg["volumen"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["pesos"], 2) ?></td>
                            </tr>

                            <?php
                            $codigo = $rg["codigo"];
                            $Puntos += $rg["puntos"];
                            $Imp += $rg["pesos"];
                            $Lts += $rg["volumen"];
                            $cli = $rg["cliente"];

                            $nRng++;
                            $cont++;
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
                            <td> #<?= $contCliente ?> Total</td>
                            <td class="numero"><?= number_format($Puntos, 0) ?></td>
                            <td class="numero"><?= number_format($Lts, 2) ?></td>
                            <td class="moneda"><?= number_format($Imp, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>


            <div id="Reportes" style="min-height: 150px;width: 70%">
                 <table aria-hidden="true">
                    <thead>
                        <tr class="titulo">
                            <td colspan="5">Totales por producto</td>
                        </tr>

                        <tr>
                            <td>Producto</td>
                            <td>Consumos</td>
                            <td>Puntos</td>
                            <td>Litros</td>
                            <td>Importe</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $Imp = $Lts = $Car = $Pun = 0;
                        foreach ($registrosT as $rg) {
                            ?>
                            <tr>
                                <td><?= $rg["producto"] ?></td>
                                <td class="numero"><?= $rg["cargas"] ?></td>
                                <td class="numero"><?= $rg["puntos"] ?></td>
                                <td class="numero"><?= number_format($rg["volumen"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["pesos"], 2) ?></td>
                            </tr>
                            <?php
                            $Imp += $rg["pesos"];
                            $Lts += $rg["volumen"];
                            $Car += $rg["cargas"];
                            $Pun += $rg["puntos"];
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>Total</td>
                            <td><?= $Car ?></td>
                            <td><?= $Pun ?></td>
                            <td><?= number_format($Lts, 2) ?></td>
                            <td class="moneda"><?= number_format($Imp, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div id="footer">
            <form name="formActions" method="post" action="" id="form" class="oculto">
                <div id="Controles">
                     <table aria-hidden="true">
                        <tr height="40">
                            <td align="left" colspan="2">

                                <div style="position: relative;">
                                    <input style="width: 100%;" type="search" id="autocomplete" name="ClienteS">
                                </div>
                                <div id="autocomplete-suggestions"></div>
                            </td>
                        </tr>
                        <tr style="height: 40px;">
                            <td style="width: 30%;">
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
