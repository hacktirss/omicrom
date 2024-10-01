<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesClientesService.php";

$Titulo = "Cobranza de facturas del $FechaI al $FechaF ";

$registros = utils\IConnection::getRowsFromQuery($selectFacturasPendientes);
$data = array("Nombre" => $Titulo, "Reporte" => 45, "FechaI" => $FechaI, "FechaF" => $FechaF, "TipoCliente" => $TipoCliente, "Cliente" => $Cliente, "Detallado" => $Detallado);
//error_log($selectFacturasPendientes);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
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
                                "SELECT id as data, CONCAT(id, ' | ', tipodepago, ' | ', nombre) value FROM cli WHERE id>=10",
                                "nombre");
                $("#FechaI").val("<?= $FechaI ?>").attr("size", "10");
                $("#FechaF").val("<?= $FechaF ?>").attr("size", "10");
                $("#cFechaI").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaI")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#cFechaF").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaF")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#Detallado").val("<?= $Detallado ?>");
                $("#TipoCliente").val("<?= $TipoCliente ?>");
            });
        </script>
    </head>

    <body>
        <div id="container">

            <?php nuevoEncabezado($Titulo); ?>

            <div id="Reportes">

                <table aria-hidden="true">
                    <thead>
                        <tr>
                            <td></td>
                            <td>Fecha</td>
                            <td>Folio</td>
                            <td>Serie</td>
                            <td>Total</td>
                            <td>Pagado</td>
                            <td>Saldo</td>
                            <td>#Pago(s)</td>
                            <td>Fecha Emision</td>
                            <td>Fecha Deposito</td>
                            <td>Serie pago</td>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $cCli = "";
                        $tipo = "";
                        $nRng = 1;
                        foreach ($registros as $rg) {

                            if ($cCli <> $rg["cliente"]) {
                                if (!empty($cCli)) {
                                    ?>
                                    <tr class="subtotal">
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td>Subtotal</td>
                                        <td class="moneda"><?= number_format($nTot, 2) ?></td>
                                        <td class="moneda"><?= number_format($nPag, 2) ?></td>
                                        <td class="moneda"><?= number_format($nSal, 2) ?></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <?php
                                }

                                if ($tipo <> $rg["tipodepago"]) {
                                    ?>
                                    <tr class="titulo">
                                        <td colspan="10"><?= $rg["tipodepago"] ?></td>
                                    </tr>
                                    <?php
                                }
                                $tipo = $rg["tipodepago"];
                                $cCli = $rg["cliente"];

                                $nTot = $nSal = $nPag = 0;
                                ?>
                                <tr class="subtitulo">
                                    <td colspan="10">*** <?= $rg["cliente"] . " " . $rg["nombre"] ?> ***</td>
                                </tr>
                            <?php } ?>

                            <tr>
                                <td><?= $nRng++ ?></td>
                                <td><?= $rg["fecha"] ?></td>
                                <td><?= $rg["factura"] ?></td>
                                <td><?= $rg["serie"] ?></td>
                                <td class="numero"><?= number_format($rg["total"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["abono"], 2) ?></td> 
                                <td class="numero"><?= number_format($rg["saldo"], 2) ?></td> 
                                <td class="numero"><?= $rg["recibo"] ?></td>
                                <td><?= $rg["fechap"] ?></td>
                                <td><?= $rg["fecha_deposito"] ?></td>
                                <td><?= $rg["seriep"] ?></td>
                            </tr>
                            <?php
                            $nTot += $rg["total"];
                            $nPag += $rg["abono"];
                            $nSal += $rg["saldo"];

                            $nTotT += $rg["total"];
                            $nPagT += $rg["abono"];
                            $nSalT += $rg["saldo"];
                        }
                        ?>

                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>Subtotal</td>
                            <td class="moneda"><?= number_format($nTot, 2) ?></td>
                            <td class="moneda"><?= number_format($nPag, 2) ?></td>
                            <td class="moneda"><?= number_format($nSal, 2) ?></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>


                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>Gran total</td>
                            <td class="moneda"><?= number_format($nTotT, 2) ?></td>
                            <td class="moneda"><?= number_format($nPagT, 2) ?></td>
                            <td class="moneda"><?= number_format($nTotT - $nPagT, 2) ?></td>
                            <td></td>
                            <td></td>
                            <td></td>
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
                            <td colspan="3">
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
                                <table aria-hidden="true">
                                    <tr>
                                        <td>Tipo de pago:</td>
                                        <td>
                                            <select name="TipoCliente" id="TipoCliente" style="width: 100px;">
                                                <?php
                                                foreach ($TiposClienteArray as $key => $value) {
                                                    echo "<option value='$key'>$value</option>";
                                                }
                                                ?>
                                            </select>                
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Mostrar pagadas:</td>
                                        <td align="left">
                                            <select name="Detallado" id="Detallado" style="width: 100px;">
                                                <option value="Si">Si</option>
                                                <option value="No">No</option> 
                                            </select>   
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <span><input type="submit" name="Boton" value="Enviar"></span>
                                <span><i style="margin-left: 10px;margin-right: 10px;"class="icon fa fa-lg fa-print" aria-hidden="true" onclick="print()" title="Imprimir reporte"></i></span>
                                <span><a title="Exportar reporte Excel" href="report_excel_reports.php?<?= http_build_query($data) ?>"><i title="Descargar archivo Excel" class="icon fa fa-lg fa-file-excel-o" aria-hidden="true"></i></a></span>
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
            <?php topePagina(); ?>
        </div>
    </body>
</html>
