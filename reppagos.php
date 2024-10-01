<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$Tabla = "cxc";
require "./services/ReportesClientesService.php";

$Titulo = "Relacion de pagos por cliente del $FechaI al $FechaF";

$registrosCredito = utils\IConnection::getRowsFromQuery($selectPagosCredito);
$registrosContado = utils\IConnection::getRowsFromQuery($selectPagosContado);
$registrosConsignacion = utils\IConnection::getRowsFromQuery($selectPagosConsignacion);
$registrosMonederos = utils\IConnection::getRowsFromQuery($selectPagosMonederos);
$registrosPrepago = utils\IConnection::getRowsFromQuery($selectPagosPrepago);
$registrosPuntos = utils\IConnection::getRowsFromQuery($selectPagosPuntos);
$registrosTarjeta = utils\IConnection::getRowsFromQuery($selectPagosTarjeta);
$registrosVales = utils\IConnection::getRowsFromQuery($selectPagosVales);

$registros = utils\IConnection::getRowsFromQuery($selectPagos);

$registrosT = utils\IConnection::getRowsFromQuery($selectPagosT);
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
                                "SELECT id as data, CONCAT(id, ' | ', tipodepago, ' | ', nombre) value FROM cli " +
                                "WHERE cli.id >= 10 AND cli.tipodepago NOT REGEXP 'Contado|Puntos'",
                                "nombre");

                $("#FechaI").val("<?= $FechaI ?>").attr("size", "10");
                $("#FechaF").val("<?= $FechaF ?>").attr("size", "10");
                $("#cFechaI").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaI")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#cFechaF").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaF")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#TipoCliente").val("<?= $TipoCliente ?>");
                $("#ordenPago").val("<?= $ordenPago ?>");
                $(".Info").click(function () {
                    var selected = this;
                    jQuery.ajax({
                        type: "POST",
                        url: "getByAjax.php",
                        dataType: "json",
                        cache: false,
                        data: {"Op": "ObtenDetallePagos", "IdBusca": this.dataset.idpago},
                        success: function (data) {
                            $(".Pasajero").hide();
                            if (data.Pass) {
                                $.each(data.Array, function (ind, elem) {
                                    var total = elem.importe + elem.iva + elem.ieps;
                                    $(selected).parent().append().after("<tr class='Pasajero'><td colspan='2'>" + elem.serie + "</td><td style='text-align:right;'>" + elem.importe + "</td><td style='text-align:right;'>" + elem.iva
                                            + "</td><td style='text-align:right;'>" + elem.ieps + "</td><td style='text-align:right;'>" + elem.total + "</td></tr>");
                                });
                                $(selected).parent().append().append().after("<tr class='Pasajero' style='background-color:#A0A0A0;color: white;font-weight: bold'><td colspan='2'>Series</td><td>Importe</td><td>Iva</td><td>IEPS</td><td>Total</td></tr>");
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    position: "top-end",
                                    toast: true,
                                    title: "No contiene ningun detalle",
                                    background: "#ABEBC6"
                                }).then((result) => {
                                    setTimeout(GoToPipas(), 2500);
                                });
                            }
                        }
                    });
                });
            });
        </script>
    </head>
    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo); ?>

            <div id="Reportes" style="min-height: 200px;"> 
                <?php
                TiposClientes($registrosCredito, "Clientes tipo Credito", $FechaI, $FechaF);

                TiposClientes($registrosContado, "Clientes tipo Contado", $FechaI, $FechaF);

                TiposClientes($registrosConsignacion, "Clientes tipo Consignacion", $FechaI, $FechaF);

                TiposClientes($registrosMonederos, "Clientes tipo Monederos", $FechaI, $FechaF);

                TiposClientes($registrosPrepago, "Clientes tipo Prepago", $FechaI, $FechaF);

                TiposClientes($registrosPuntos, "Clientes tipo Puntos", $FechaI, $FechaF);

                TiposClientes($registrosTarjeta, "Clientes tipo Tarjeta", $FechaI, $FechaF);

                TiposClientes($registrosVales, "Clientes tipo Vales", $FechaI, $FechaF);
                ?>
            </div>

            <div id="Reportes" style="min-height: 200px;width: 80%;"> 
                <table aria-hidden="true">
                    <thead>
                        <tr class="titulo"><td colspan="4">Concentrado por cliente</td>
                        <tr>
                            <td>Cuenta</td>
                            <td>Nombre</td>
                            <td>Pagos</td>
                            <td>Importe</td>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        foreach ($registrosT as $rg) {
                            ?>
                            <tr>
                                <td><?= $rg["cliente"] ?></td>
                                <td><?= $rg["nombre"] ?></td>
                                <td class="numero"><?= number_format($rg["pagos"], 0) ?></td>
                                <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                            </tr>
                            <?php
                            $nImporte += $rg["importe"];
                            $nPag += $rg["pagos"];
                        }
                        ?>
                    </tbody>

                    <tfoot>
                        <tr>
                            <td></td>
                            <td>Total</td>
                            <td class="numero"><?= number_format($nPag, 0) ?></td>
                            <td class="moneda"><?= number_format($nImporte, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div id="footer">
            <form name="formActions" method="post" action="" id="form" class="oculto">
                <div id="Controles">
                    <table aria-hidden="true">
                        <tr style="height: 40px;">
                            <td align="left" colspan="100%">
                                <div style="position: relative;">
                                    <input style="width: 100%;" type="search" id="autocomplete" name="ClienteS">
                                </div>
                                <div id="autocomplete-suggestions"></div>
                            </td>
                        </tr>
                        <tr style="height: 40px;">
                            <td>
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
                                <table aria-hidden="true">
                                    <tr>
                                        <td>Tipo:</td>
                                        <td style="text-align: left;">
                                            <select id="TipoCliente" name="TipoCliente">
                                                <?php
                                                foreach ($TiposClienteArray as $key => $value) {
                                                    echo "<option value='$key'>$value</option>";
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <table aria-hidden="true">
                                    <tr>
                                        <td>Referencia:</td>
                                        <td style="text-align: left;">
                                            <select id="ordenPago" name="ordenPago">
                                                <option value="pagos.fecha">Fecha de Aplicacion</option>
                                                <option value="pagos.fecha_deposito">Fecha de Deposito</option>
                                            </select>
                                        </td>
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
<?php

function TiposClientes($registros, $titulo, $FechaI, $FechaF) {
    if (!empty($registros)) {
        ?>
        <table aria-hidden="true">
            <thead>
                <tr><td colspan="8"><?= $titulo ?><td></tr>
                <tr>
                    <td>#</td>
                    <td>Pago</td>
                    <td><a href="reppagos.php?ordenPago=pagos.fecha_deposito&FechaI=<?= $FechaI ?>&FechaF=<?= $FechaF ?>">F.Deposito</a></td>
                    <td><a href="reppagos.php?ordenPago=pagos.fecha&FechaI=<?= $FechaI ?>&FechaF=<?= $FechaF ?>">F.Aplicacion</a></td>
                    <td>Forma Pago</td>
                    <td>Cuenta</td>                  
                    <td>Nombre</td>
                    <td>Importe</td>
                    <td></td>
                </tr>
            </thead>

            <tbody>
                <?php
                $nRng = 0;
                $Importe = 0;
                foreach ($registros as $rg) {
                    ?>
                    <tr>
                        <td><?= ++$nRng; ?></td>
                        <td><?= $rg["id"] ?></td>
                        <td class="overflow"><?= $rg["deposito"] ?></td>
                        <td class="overflow"><?= $rg["aplicacion"] ?></td>
                        <td><?= $rg["formapago"] ?></td>
                        <td><?= $rg["cliente"] ?></td>                       
                        <td class="overflow"><?= $rg["nombre"] ?></td>
                        <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                        <td style="text-align: center;" data-idPago="<?= $rg["id"] ?>" class="Info" title="Detalle de las facturas pagagadas"><i class="fa-solid fa-info"></i></td>
                    </tr>
                    <?php
                    $Importe += $rg["importe"];
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
                    <td>Total</td>
                    <td class="moneda"><?= number_format($Importe, 2) ?></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        <?php
    }
}
