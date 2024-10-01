<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

$Titulo = "Consumo de vales del $FechaI al $FechaF";

$registros = utils\IConnection::getRowsFromQuery($selectValesD);
//error_log($selectValesD);
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
                                "SELECT id as data, CONCAT(id, \' | \', mid(nombre,1,50)) value FROM cli WHERE id>10 AND tipodepago NOT REGEXP \'Contado|Puntos\'",
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
            <div id="Reportes">
                <table aria-hidden="true">
                    <thead>
                        <tr>
                            <td></td>
                            <td>Orden</td>
                            <td>Ticket</td>
                            <td>Codigos</td>
                            <td>Fecha Consumo</td>
                            <td>Importe cargado</td>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $cargado = $cargadoT = 0;
                        $cliente = "";
                        $nRng = 1;
                        foreach ($registros as $rg) {

                            if ($rg["cliente"] !== $cliente) {
                                if (!empty($cliente)) {
                                    ?>
                                    <tr class="subtotal">
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td>Subtotal</td>
                                        <td><?= number_format($cargado, 2) ?></td>
                                    </tr>
                                    <?php
                                    $cargado = 0;
                                }
                                ?>
                                <tr class="subtitulo">
                                    <td colspan='6'><?= $rg["nombre"] ?></td>
                                </tr>
                                <?php
                            }
                            ?>
                            <tr>
                                <td><?= $nRng ?></td>
                                <td><?= $rg["orden"] ?></td>
                                <td><?= $rg["ticket"] ?></td>
                                <td><?= str_replace("|", ",", $rg[codigo_rm]) ?></td>
                                <td><?= $rg["fecha"] ?></td>
                                <td class="numero"><?= number_format($rg["importecargado"], 2) ?></td>
                            </tr>
                            <?php
                            $nRng ++;
                            $cliente = $rg["cliente"];
                            $cargado += $rg["importecargado"];
                            $cargadoT += $rg["importecargado"];
                        }
                        ?>
                    </tbody>

                    <tfoot>
                        <?php
                        if (!empty($cliente)) {
                            ?>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td> 
                                <td>Subtotal</td>
                                <td><?= number_format($cargado, 2) ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td> 
                            <td>Total</td>
                            <td><?= number_format($cargadoT, 2) ?></td>
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
                                <a href="repboletos.php?Cliente=<?= $Cliente ?>&FechaI=<?= $FechaI ?>&FechaF=<?= $FechaF ?>">Vales</a> 
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