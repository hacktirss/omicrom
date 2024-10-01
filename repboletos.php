<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

$Titulo = "Estatus de vales del $FechaI al $FechaF";

$registros = utils\IConnection::getRowsFromQuery($selectVales);

$registrosT = utils\IConnection::getRowsFromQuery($selectValesT);
//error_log($selectVales);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports.php'; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                var cliente = "<?= html_entity_decode($SCliente) ?>";
                $("#autocomplete").val(cliente.replace("Array", ""))
                        .attr("placeholder", "* Favor de buscar al cliente *")
                        .click(function () {
                            this.select();
                        }).focus()
                        .activeComboBox(
                                $("[name=\"form1\"]"),
                                "SELECT id as data, CONCAT(id, ' | ', tipodepago, ' | ', nombre) value FROM cli " +
                                "WHERE TRUE AND cli.tipodepago NOT REGEXP 'Contado|Puntos'",
                                "nombre");
                $("#FechaI").val("<?= $FechaI ?>").attr("size", "10");
                $("#FechaF").val("<?= $FechaF ?>").attr("size", "10");
                $("#cFechaI").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaI")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#cFechaF").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaF")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#Status").val("<?= $Status ?>");
            });
        </script>
    </head>

    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo) ?>
            <div id="Reportes" style="padding-bottom: 10px">
                <table aria-hidden="true">
                    <thead>
                        <tr>
                            <td></td>
                            <td>Folio</td>
                            <td>Generación</td>
                            <td>Secuencia</td>
                            <td>Cliente</td>
                            <td>Importe</td>
                            <td>Importe cargado</td>
                            <td>Saldo</td>
                            <td>Status</td>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $contS = $contN = $importeS = $importeN = $cargadoS = $cargadoN = 0;

                        $nRng = 1;
                        foreach ($registros as $rg) {

                            if ($rg["fechav"] > $Hoy && $rg["vigente"] == "Si") {
                                $cStatus = "Vigente";
                                $contS ++;
                                $importeS += $rg["importe"];
                                $cargadoS += $rg["importecargado"];
                            } else {
                                $cStatus = "Vencido";
                                $contN ++;
                                $importeN += $rg["importe"];
                                $cargadoN += $rg["importecargado"];
                            }
                            ?>
                            <tr>
                                <td><?= $nRng ?></td>
                                <td><?= $rg["codigo"] ?></td>
                                <td><?= $rg["fecha"] ?></td>
                                <td><?= $rg["secuencia"] ?></td>
                                <td><?= ucwords(strtolower($rg["cliente"])) ?></td>
                                <td class="numero"><?= number_format($rg["importe"], 0) ?></td>
                                <td class="numero"><?= number_format($rg["importecargado"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["importe"] - $rg["importecargado"], 2) ?></td>
                                <td><?= $cStatus ?></td>
                            </tr>
                            <?php
                            $nRng ++;
                        }
                        ?>
                    </tbody>
                </table>
            </div> 



            <div id="Reportes" style="width: 80%;min-height: 50px;">
                <table aria-hidden="true">
                    <thead>
                        <tr class="titulo">
                            <td colspan="7">DETALLE POR CLIENTE</td>
                        </tr>
                        <tr>
                            <td>Cliente</td>
                            <td>Nombre</td>
                            <td>Status</td>
                            <td>No. Boletos</td>
                            <td>Importe</td>
                            <td>Importe Cargado</td>
                            <td>Saldo</td>
                        </tr>
                    </thead>
                    <tbody>
                       <?php
                        $contST = $contNT = $importeS = $importeN = $cargadoS = $cargadoN = 0;
                        $saldoT = 0;
                        foreach ($registrosT as $rg) {
                             if ($rg["status"] === "Vigente") {
                                $contST   += $rg["vales"];
                                $importeS += $rg["importe"];
                                $cargadoS += $rg["importecargado"];
                            } else {
                                $contNT   += $rg["vales"];
                                $importeN += $rg["importe"];
                                $cargadoN += $rg["importecargado"];
                            }
                            ?>
                            <tr>
                                <td><?= $rg["cliente"] ?></td>
                                <td><?= $rg["nombre"] ?></td>
                                <td><?= $rg["status"] ?></td>
                                <td class="numero"><?= $rg["vales"] ?></td>
                                <td class="numero"><?= number_format($rg["importe"], 0) ?></td>
                                <td class="numero"><?= number_format($rg["importecargado"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["importe"] - $rg["importecargado"], 2) ?></td>
                            </tr>
                            <?php
                            $saldoT += $rg["importe"] - $rg["importecargado"];
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td><?= number_format($contST + $contNT) ?></td>
                            <td><?= number_format($importeN + $importeS, 2) ?></td>
                            <td><?= number_format($cargadoN + $cargadoS, 2) ?></td>
                            <td><?= number_format($saldoT, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>

        <div id="footer">
            <form name="formActions" method="post" action="" id="form" class="oculto">
                <div id="Controles">
                    <table aria-hidden="true">
                        <tr>
                            <td colspan="2">
                                <div style="position: relative;">
                                    <input style="width: 100%;" type="search" id="autocomplete" name="ClienteS">
                                </div>
                                <div id="autocomplete-suggestions"></div>
                            </td>
                            <td>
                                <a href="repboletosd.php?Cliente=<?= $Cliente ?>&FechaI=<?= $FechaI ?>&FechaF=<?= $FechaF ?>">Consumos</a> 
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
                            <td>Estado x Vale:
                                <select id="Status" name="Status" class="texto_tablas">
                                    <option value="*">Todos</option>
                                    <option value="Si">Vigentes</option>
                                    <option value="No">Vencidos</option>
                                </select>
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