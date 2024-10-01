<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("comboBoxes.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

$request = utils\HTTPUtils::getRequest();

$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Titulo = "Captura de datos, opciones multiples";
$Return = "pidedatos.php?busca=$busca";

$Id = 0;

if ($busca == 1) {
    $Titulo = "Reporte de pipas capturadas por rango de fecha";
    $Return = "repentpipas.php";
} elseif ($busca == 2) {
    $Titulo = "Facturas emitidas por cliente";
    $Return = "expfacturas.php";
    $Id = 43; /* Número de en el orden de la tabla submenus */
} elseif ($busca == 3) {
    $Titulo = "Relacion de facturas";
    $Return = "repfacturas.php";
    $Return1 = "facturaprodct.php";
} elseif ($busca == 4) {
    
} elseif ($busca == 5) {
    $Titulo = "Control volumetrico";
    $Return = "balanceproductos.php";
} elseif ($busca == 6) {
    $Titulo = "Control de Despachos";
    $Return = "controldesp.php";
} elseif ($busca == 7) {
    $Titulo = "Pagos realizados";
    $Return = "pagosP.php";
} elseif ($busca == 8) {
    $Titulo = "Notas de Credito Generadas";
    $Return = "repnotas.php";
} elseif ($busca == 9) {
    $Titulo = "Dictamen Generados";
    $Return = "repdictamen.php";
} elseif ($busca == 10) {
    $Titulo = "Resumen de ventas";
    $Return = "resumen.php";
} elseif ($busca == 11) {
    $Titulo = "Pagos a Provedores";
    $Return = "reprv.php";
}
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports.php'; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                var cliente = "<?= html_entity_decode($SCliente) ?>";
                var busca = "<?= $busca ?>";
                var url = "report_excel_reports.php?";
                var params_url = {Nombre: "Relacion de facturas emitidas por fecha de venta", Reporte: "<?= $Id ?>",
                    Fecha: "<?= $Fecha ?>", TipoCliente: "<?= $TipoCliente ?>", FormaPago: "<?= $FormaPago ?>"};

                var params_url1 = {Nombre: "Relacion de facturas ", Reporte: "<?= 205 ?>",
                    FechaI: "<?= $FechaI ?>", FechaF: "<?= $FechaF ?>"};

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
                $("#FechaI33").val("<?= $FechaI ?>").attr("size", "10");
                $("#FechaF33").val("<?= $FechaF ?>").attr("size", "10");
                $(".Fecha").val("<?= $Fecha ?>").attr("size", "10");
                $("#cFechaI").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaI")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#cFechaF").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaF")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#cFecha2").css("cursor", "hand").click(function () {
                    displayCalendar($("#Fecha2")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#cFecha3").css("cursor", "hand").click(function () {
                    displayCalendar($("#Fecha3")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#cFechaF3").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaF3")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#cFechaI33").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaI33")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#cFechaF33").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaF33")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#TipoRelacion").val("<?= $TipoRelacion ?>");
                $(".FormaPago").val("<?= $FormaPago ?>");
                $(".TipoCliente").val("<?= $TipoCliente ?>");

                if (busca === "2") {

                    $("#ReporteExcel").attr("href", url + $.param(params_url));

                    $("#form2 .TipoCliente").change(function () {
                        $("#form2 .FormaPago").val("*");
                    });
                    $("#form2 .FormaPago").change(function () {
                        $("#form2 .TipoCliente").val("*");
                    });

                    $("#ReporteExcel").hover(function () {
                        params_url.Fecha = $("#Fecha3").val();
                        params_url.Fechaf = $("#FechaF3").val();
                        params_url.TipoCliente = $("#form3 .TipoCliente").val();
                        params_url.FormaPago = $("#form3 .FormaPago").val();
                        $("#ReporteExcel").attr("href", url + $.param(params_url));
                    });
                }
                if (busca === "3") {
                    $("#ReporteExcel3").hover(function () {
                        params_url1.FechaI = $("#FechaI").val();
                        params_url1.FechaF = $("#FechaF").val();
                        $("#ReporteExcel3").attr("href", url + $.param(params_url1));
                    });
                }

                $("input[type='submit']").click(function () {
                    console.log("value: " + $(this).val());
                    clicksForm = 0;
                });
            });
        </script>
    </head>

    <body>

        <div id="container">
            <?php nuevoEncabezado($Titulo) ?>    
            <form name="form1" method="post" action="<?= $Return ?>">
                <div id="PideDatos">
                    <div><?= $Titulo ?></div>

                    <table aria-hidden="true">
                        <thead>
                            <?php if ($busca < 7) { ?>
                                <tr>
                                    <td colspan="3">Favor de pedir un rango no mayor a 10 dias</td>
                                </tr>
                            <?php } ?>
                        </thead>
                        <tbody>
                            <?php if ($busca != 10) { ?>
                                <tr>
                                    <td style="text-align: right">F.inicial:</td>
                                    <td><input type="text" id="FechaI" name="FechaI"></td>
                                    <td class="calendario" style="text-align: left"><i id="cFechaI" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right">F.final:</td>
                                    <td><input type="text" id="FechaF" name="FechaF"></td>
                                    <td class="calendario" style="text-align: left"><i id="cFechaF" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
                                </tr>
                            <?php } ?>

                            <?php if ($busca == 6) { ?>
                                <tr>
                                    <td style="text-align: right">Desglose:</td>
                                    <td style="text-align: left"><select id="Desglose" name="Desglose">
                                            <option value="Cortes">Cortes</option>
                                            <option value="Dia">Dia</option>
                                        </select></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right">Tipo:</td>
                                    <td style="text-align: left"><select id="Tipo" name="Tipo">
                                            <option value="importe">Importe</option>
                                            <option value="pagoreal">Pago Real</option>
                                        </select></td>
                                </tr>
                            <?php } ?>

                            <?php if ($busca == 10) { ?>
                                <tr>
                                    <td style="text-align: right">Año</td>
                                    <td><input type="number" name="FechaNum" id="FechaNum" min="2020" max="2060" value="<?= date("Y") ?>"></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right">Mes</td>
                                    <td><select name="Mes" id="Mes">
                                            <?php
                                            foreach (getMonts() as $key => $value) {
                                                echo "<option value='$key'>$value</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                            <?php } ?>



                            <?php if ($busca == 2 || $busca == 3) { ?>
                                <tr height="40">
                                    <td align="left" colspan="3">
                                        <div style="position: relative;">
                                            <input style="width: 100%;" type="search" id="autocomplete" name="ClienteS" <?= $busca == 2 ? "required" : "" ?>>
                                        </div>
                                        <div id="autocomplete-suggestions"></div>
                                    </td>
                                </tr>
                            <?php } ?>

                            <?php if ($busca == 3) { ?>
                                <tr>
                                    <td colspan="3">
                                        <select name="TipoRelacion" class="texto_tablas" id="TipoRelacion">
                                            <?php
                                            foreach ($TipoCFDI as $key => $value) {
                                                echo "<option value='$key'>$value</option>";
                                            }
                                            ?>
                                        </select>

                                    </td>
                                </tr>
                            <?php } ?>

                            <?php if ($busca == 5) { ?>
                                <tr>
                                    <td colspan="3">
                                        <select name="Informacion" id="Informacion">
                                            <?php
                                            foreach ($TipoInformacion as $key => $value) {
                                                echo "<option value='$key'>$value</option>";
                                            }
                                            ?>
                                            <option value='$key'>$value</option>;
                                        </select>
                                    </td>
                                </tr>
                            <input type="hidden" name="busca" value="1">
                        <?php } ?>
                        <tr>
                            <td colspan="3">
                                <span><input type="submit" name="Boton" value="Enviar"></span>
                            </td>
                        </tr>
                        <tbody>
                    </table>
                </div>
            </form>
            <?php if ($busca == 3) { ?>
                <form name="form2" id="form2" method="post" action="<?= $Return1 ?>">
                    <div id="PideDatos">
                        <div><?= "Facturas por desglose de Producto" ?></div>

                        <table aria-hidden="true">
                            <tr>
                                <td style="text-align: right">F.inicial:</td>
                                <td><input type="text" id="FechaI33" name="FechaI"></td>
                                <td class="calendario" style="text-align: left"><i id="cFechaI33" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
                            </tr>
                            <tr>
                                <td style="text-align: right">F.final:</td>
                                <td><input type="text" id="FechaF33" name="FechaF"></td>
                                <td class="calendario" style="text-align: left"><i id="cFechaF33" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
                            </tr>
                            <td colspan="3">
                                <span><input type="submit" name="BotonT" value="Enviar"></span>
                            </td>
                        </table>
                    </div>
                </form>
            <?php } ?>
            <?php if ($busca == 2) { ?>
                <form name="form2" id="form2" method="post" action="<?= $Return ?>">
                    <div id="PideDatos">
                        <div>Facturas emitidas por día</div>

                        <table aria-hidden="true">
                            <thead>
                                <tr>
                                    <td colspan="3">Obtiene los CFDIS de todos los clientes</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="text-align: right">Fecha:</td>
                                    <td><input type="text" id="Fecha2" name="Fecha" class="Fecha"></td>
                                    <td class="calendario" style="text-align: left"><i id="cFecha2" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
                                </tr>
                                <tr>
                                    <td style="text-align: center;" colspan="3">Solo público en general (XML)
                                        <br/><input type="checkbox" name="General"></td>
                                </tr>
                                <tr>
                                    <td style="text-align: center;" colspan="3">Facturas por Forma de Pago o Tipo de Cliente (XML)<br/><br/>
                                        <select name="FormaPago" class="FormaPago" style="width: 150px;">
                                            <option value="*">Todos</option>
                                            <?php
                                            foreach (CatalogosSelectores::getFormasDePago() as $key => $value) {
                                                echo "<option value='$key'>$value</option>";
                                            }
                                            ?>
                                        </select>
                                        <strong>ó</strong>
                                        <select name="TipoCliente" class="TipoCliente" style="width: 150px;">
                                            <option value="*">Todos</option>
                                            <?php
                                            foreach ($TiposClienteArray as $key => $value) {
                                                echo "<option value='$key'>$value</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        <span><input type="submit" name="BotonT" value="Enviar"></span>
                                    </td>
                                </tr>
                            <tbody>
                        </table>
                    </div>
                </form>

                <form name="form3" id="form3" method="post" action="<?= $Return ?>">
                    <div id="PideDatos">
                        <div>Facturas por fecha de venta</div>

                        <table aria-hidden="true">
                            <thead>
                                <tr>
                                    <td colspan="3">Obtiene los CFDIS de todos los clientes (XML's)</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td style="text-align: right">F.inicial:</td>
                                    <td><input type="text" id="Fecha3" name="Fecha" class="Fecha"></td>
                                    <td class="calendario" style="text-align: left"><i id="cFecha3" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
                                </tr>
                                <tr>
                                    <td style="text-align: right">F.final:</td>
                                    <td><input type="text" id="FechaF3" name="FechaF" class="Fecha"></td>
                                    <td class="calendario" style="text-align: left"><i id="cFechaF3" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
                                </tr>
                                <tr>
                                    <td style="text-align: center;" colspan="3">Facturas por Forma de Pago o Tipo de Cliente<br/><br/>
                                        <select name="FormaPago" class="FormaPago" style="width: 150px;">
                                            <option value="*">Todos</option>
                                            <?php
                                            foreach (CatalogosSelectores::getFormasDePago() as $key => $value) {
                                                echo "<option value='$key'>$value</option>";
                                            }
                                            ?>
                                        </select>
                                        <strong>ó</strong>
                                        <select name="TipoCliente" class="TipoCliente" style="width: 150px;">
                                            <option value="*">Todos</option>
                                            <?php
                                            foreach ($TiposClienteArray as $key => $value) {
                                                echo "<option value='$key'>$value</option>";
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        <span><input type="submit" name="BotonG" value="Enviar"></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        <a id="ReporteExcel" title="Exportar a formato Excel"><i class="icon fa fa-lg fa-file-excel" aria-hidden="true"></i></a>
                                    </td>
                                </tr>
                            <tbody>
                        </table>
                    </div>
                </form>
            <?php } ?>

            <div class="mensajes"><?= $Msj ?></div>
        </div>
    </body>
</html>
