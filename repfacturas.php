<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

$Titulo = "Relacion de $TipoRelacion emitidas del $FechaI al $FechaF";

$registros = utils\IConnection::getRowsFromQuery($selectCFDI);
$registrosCancelados = utils\IConnection::getRowsFromQuery($selectCFDICancelados);

$Id = 42; /* Número de en el orden de la tabla submenus */
$data = array("Nombre" => $Titulo, "Reporte" => $Id,
    "TipoRelacion" => $TipoRelacion,
    "FechaI" => $FechaI, "FechaF" => $FechaF,
    "TipoCliente" => $TipoCliente, "Descartar" => $Descartar,
    "Status" => $Status);
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
                                "SELECT id as data, CONCAT(id, \' | \', mid(nombre,1,50)) value FROM cli WHERE id>=10",
                                "nombre");
                $("#FechaI").val("<?= $FechaI ?>").attr("size", "10");
                $("#FechaF").val("<?= $FechaF ?>").attr("size", "10");
                $("#cFechaI").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaI")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#cFechaF").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaF")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#Descartar").val("<?= $Descartar ?>");
                $("#TipoCliente").val("<?= $TipoCliente ?>");
                $("#Status").val("<?= $Status ?>");
                $("#TipoRelacion").val("<?= $TipoRelacion ?>");
            });
        </script>
    </head>

    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo) ?>    
            <div id="Reportes">
                <h4>Facturas emitidas</h4>
                <em class="fa-solid fa-soap" id="Clean"></em>
                <table aria-hidden="true">
                    <thead>
                        <tr>
                            <td>#</td>
                            <td>Folio</td>
                            <td>Fecha</td>
                            <td class="oculto">Cancelacion</td>
                            <td>Cta</td>
                            <td>Cliente</td>
                            <td>Tipo</td>
                            <td>Forma de pago</td>
                            <td>Cantidad</td>
                            <td>Importe</td>
                            <td>Iva</td>
                            <td>Ieps</td>
                            <td>Total</td> 
                            <td>Descuento</td>
                            <td>Status</td>
                            <td>Creación</td>
                            <td>Tipo</td>
                            <td class="oculto">Productos</td>
                            <td></td>
                        </tr>
                    </thead>
                    <tbody>

                        <?php
                        $nRng = 1;

                        $oOmi = $oPos = $oExt = $oNA = 0;
                        foreach ($registros as $rg) {
                            $uuid = trim($rg['uuid']);
                            if ($rg["status"] == StatusFactura::CERRADO) {
                                $nTim++;
                            } elseif ($rg["status"] == StatusFactura::CANCELADO_ST) {
                                $nSinTim++;
                            } elseif ($rg["status"] == StatusFactura::CANCELADO) {
                                $nTimCan++;
                            } elseif ($rg["status"] == StatusFactura::ABIERTO) {
                                $nAb++;
                            }

                            if ($rg["origen"] == 1) {
                                $oOmi++;
                            } elseif ($rg["origen"] == 2) {
                                $oPos++;
                            } elseif ($rg["origen"] == 3) {
                                $oExt++;
                            } else {
                                $oNA++;
                            }
                            ?>
                            <tr class="prins">
                                <td><?= $nRng++ ?></td>
                                <td><?= $rg["serie"] . "-" . $rg["folio"] ?></td>
                                <td><?= $rg["fecha"] ?></td>
                                <td class="oculto"><?= $rg["FechaCancelacion"] ?></td>
                                <td><?= $rg["cliente"] ?></td>
                                <td><?= ucwords(strtolower(substr($rg["nombre"], 0, 30))) ?></td>
                                <td><?= $rg["tipodepago"] ?></td>
                                <td><?= ucwords(strtolower(substr($rg["concepto"], 0, 25))) ?></td>
                                <td style="text-align: right;"><?= number_format($rg["cantidad"], 2) ?></td>
                                <td style="text-align: right;"><?= number_format($rg["importe"], 2) ?></td>
                                <td style="text-align: right;"><?= number_format($rg["iva"], 2) ?></td>
                                <td style="text-align: right;"><?= number_format($rg["ieps"], 2) ?></td>
                                <td style="text-align: right;"><?= number_format($rg["total"], 2) ?></td>
                                <td style="text-align: right;"><?= number_format($rg["descuento"], 2) ?></td>
                                <td><?= statusCFDI($rg["status"]) . ($rg["status"] == StatusFactura::CANCELADO ? " (" . $rg["usuario"] . ")" : "") ?></td>
                                <td><?= ucwords(strtolower($rg["usuario"])) ?></td>
                                <td><?= $rg["formato"] ?></td>
                                <td class="oculto"><?= $rg["productos"] ?></td>
                                <td><em class="fa-regular fa-chart-bar detalleFactura" data-idG="<?= $rg["id"] ?>"></em></td>
                            </tr>
                            <?php
                            $nCantidadt += $rg["cantidad"];
                            $nImportet += $rg["importe"];
                            $nIvat += $rg["iva"];
                            $nIepst += $rg["ieps"];
                            $nTotal += $rg["total"];
                            $TDesc += $rg["descuento"];
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="oculto"></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>Total</td>
                            <td><?= number_format($nCantidadt, 2) ?></td>
                            <td><?= number_format($nImportet, 2) ?></td>
                            <td><?= number_format($nIvat, 2) ?></td>
                            <td><?= number_format($nIepst, 2) ?></td>
                            <td><?= number_format($nTotal, 2) ?></td>
                            <td><?= number_format($TDesc, 2) ?></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="oculto"></td>
                        </tr>
                    </tfoot>
                </table>
                <?php
                $nCantidadt = 0;
                $nImportet = 0;
                $nIvat = 0;
                $nIepst = 0;
                $nTotal = 0;
                $TDesc = 0;
                ?>
                <h4>Facturas canceladas</h4>
                <table aria-hidden="true">
                    <thead>
                        <tr>
                            <td>#</td>
                            <td>Folio</td>
                            <td>Fecha</td>
                            <td class="oculto">Cancelacion</td>
                            <td>Cta</td>
                            <td>Cliente</td>
                            <td>Tipo</td>
                            <td>Forma de pago</td>
                            <td>Cantidad</td>
                            <td>Importe</td>
                            <td>Iva</td>
                            <td>Ieps</td>
                            <td>Total</td>                        
                            <td>Status</td>
                            <td>Creación</td>
                            <td>Cancelacion</td>
                            <td class="oculto">Productos</td>
                            <td></td>
                        </tr>
                    </thead>
                    <tbody>

                        <?php
                        $nRng = 1;

                        $oOmi = $oPos = $oExt = $oNA = 0;
                        foreach ($registrosCancelados as $rg) {
                            $uuid = trim($rg['uuid']);
                            if ($rg["status"] == StatusFactura::CERRADO) {
                                $nTim++;
                            } elseif ($rg["status"] == StatusFactura::CANCELADO_ST) {
                                $nSinTim++;
                            } elseif ($rg["status"] == StatusFactura::CANCELADO) {
                                $nTimCan++;
                            } elseif ($rg["status"] == StatusFactura::ABIERTO) {
                                $nAb++;
                            }

                            if ($rg["origen"] == 1) {
                                $oOmi++;
                            } elseif ($rg["origen"] == 2) {
                                $oPos++;
                            } elseif ($rg["origen"] == 3) {
                                $oExt++;
                            } else {
                                $oNA++;
                            }
                            ?>
                            <tr>
                                <td><?= $nRng++ ?></td>
                                <td><?= $rg["folio"] ?></td>
                                <td><?= $rg["fecha"] ?></td>
                                <td class="oculto"><?= $rg["FechaCancelacion"] ?></td>
                                <td><?= $rg["cliente"] ?></td>
                                <td><?= ucwords(strtolower(substr($rg["nombre"], 0, 30))) ?></td>
                                <td><?= $rg["tipodepago"] ?></td>
                                <td><?= ucwords(strtolower(substr($rg["concepto"], 0, 25))) ?></td>
                                <td><?= number_format(0, 2) ?></td>
                                <td><?= number_format(0, 2) ?></td>
                                <td><?= number_format(0, 2) ?></td>
                                <td><?= number_format(0, 2) ?></td>
                                <td><?= number_format(0, 2) ?></td>
                                <td><?= statusCFDI($rg["status"]) . ($rg["status"] == StatusFactura::CANCELADO ? " (" . $rg["usuario"] . ")" : "") ?></td>
                                <td><?= ucwords(strtolower($rg["usuario"])) ?></td>
                                <td><?= $rg["cancelacion"] ?></td>
                                <td class="oculto"><?= $rg["productos"] ?></td>
                                <td><em class="fa-regular fa-chart-bar detalleFactura" data-idG="<?= $rg["id"] ?>"></em></td>
                            </tr>
                            <?php
//                            $nCantidadt += $rg["cantidad"];
//                            $nImportet += $rg["importe"];
//                            $nIvat += $rg["iva"];
//                            $nIepst += $rg["ieps"];
//                            $nTotal += $rg["total"];
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="oculto"></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>Total</td>
                            <td><?= number_format($nCantidadt, 2) ?></td>
                            <td><?= number_format($nImportet, 2) ?></td>
                            <td><?= number_format($nIvat, 2) ?></td>
                            <td><?= number_format($nIepst, 2) ?></td>
                            <td><?= number_format($nTotal, 2) ?></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="oculto"></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>


                <h4>Estado y origen de facturas</h4>
                <div align="center" >
                    <table style="border:solid #E1E1E1 3px;width: 50%;" align="center" class="texto_tablas" aria-hidden="true">
                        <tr>
                            <td width="50%" align="center">
                                <div align="left">
                                    <table width="80%" aria-hidden="true">
                                        <tr>
                                            <td width="40%" align="center"><strong>Concepto</strong></td><td align="center"><strong>Cantidad</strong></td>
                                        </tr>
                                        <tr>
                                            <td  align="left">Timbradas:</td><td align="right"><?= number_format($nTim + $nTimCan, 0) ?></td>
                                        </tr>
                                        <tr>
                                            <td  align="left">Canceladas/Timbradas:</td><td align="right"><?= number_format($nTimCan, 0) ?></td>
                                        </tr>
                                        <tr>
                                            <td  align="left">S/Timbrar:</td><td align="right"><?= number_format($nSinTim, 0) ?></td>
                                        </tr>
                                        <tr>
                                            <td  align="left">Total facturado:</td><td align="right"><strong><?= number_format($nTim + ($nTimCan * 2), 0) ?></td>
                                        </tr>
                                    </table>
                                </div>

                            </td>
                            <td width="50%">
                                <div align="center">
                                    <table width="100%" aria-hidden="true">
                                        <tr>
                                            <td width="50%" align="center"><strong>Fuente</strong></td><td width="50%" align="center"><strong>Cantidad</strong></td>
                                        </tr>
                                        <tr>
                                            <td align="left">Sistema/Omicrom:</td><td align="right"><?= $oOmi ?></td>
                                        </tr>
                                        <tr>
                                            <td align="left">Terminal:</td><td align="right"><?= $oPos ?></td>
                                        </tr>
                                        <tr>
                                            <td  align="left">En linea:</td><td align="right"><?= $oExt ?></td>
                                        </tr>
                                        <tr>
                                            <td  align="left">Sin indentificar:</td><td align="right"><?= $oNA ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div id="footer">
            <form name="formActions" method="post" action="" id="form" class="oculto">
                <div id="Controles">
                    <table aria-hidden="true">
                        <tr>
                            <td align="left" colspan="2">
                                <div style="position: relative;">
                                    <input style="width: 100%;" type="search" id="autocomplete" name="ClienteS">
                                </div>
                                <div id="autocomplete-suggestions"></div>
                            </td>
                            <td align="center">
                                <?php
                                if ($usuarioSesion->getTeam() !== "Operador") {
                                    ?>
                                    <a href="report_excel_reports.php?<?= http_build_query($data) ?>" title="Exportar a formato Excel"><i class="icon fa fa-lg fa-file-excel-o" aria-hidden="true"></i></a>
                                    <?php
                                }
                                ?>
                            </td>
                            <td align="center">
                                <a href="repfacturaspagos.php?" title="Relacion de facturas pagadas">Pagos</a> 
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
                                <table aria-hidden="true">
                                    <tr>
                                        <td>Tipo de relacion:</td>
                                        <td>
                                            <select name="TipoRelacion" id="TipoRelacion" style="width: 100px;">
                                                <?php
                                                foreach ($TipoCFDI as $key => $value) {
                                                    echo "<option value='$key'>$value</option>";
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Descartar XAXX010101000:</td>
                                        <td align="left">
                                            <select name="Descartar" id="Descartar" style="width: 100px;">
                                                <option value="Si">Si</option>
                                                <option value="No">No</option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </td> 
                            <td>
                                <table style="text-align: left;" aria-hidden="true">
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
                                        <td align="right">Status:</td>
                                        <td align="left">
                                            <select name='Status' id="Status" style="width: 100px;">
                                                <?php
                                                foreach ($StatusCFDI as $key => $value) {
                                                    echo "<option value='$key'>$value</option>";
                                                }
                                                ?>

                                            </select>   
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <span><input type="submit" name="Boton" value="Enviar"></span>
                                <?php
                                if ($usuarioSesion->getTeam() !== "Operador") {
                                    ?>
                                    <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                                        <?php }
                                        ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
            <?php topePagina() ?>
        </div>
    </body>
</html>
<script type="text/javascript">
    $(document).ready(function () {
        $("#Clean").click(function () {
            $(".AgregadoAjax").hide();
        });
        $(".detalleFactura").click(function () {
            $(".AgregadoAjax").hide();
            var thisb = this;
            jQuery.ajax({
                type: "POST",
                url: "getByAjax.php",
                dataType: "json",
                cache: false,
                data: {"Op": "ObtenDetalleFactura", "IdBusca": this.dataset.idg},
                success: function (data) {
                    var e = 0;
                    $.each(data.Array, function (ind, elem) {
                        var color = e % 2 == 0 ? "#EAFAF1" : "#FFF";
                        $(thisb).parent().parent().after("<tr class='AgregadoAjax' style='background-color: " + color + ";'><td style='border-left:1px solid black;'>" + elem.ticket + "</td><td>" +
                                elem.descripcion + "</td><td style='text-align:right;'>" +
                                elem.cantidad + "</td><td style='text-align:right;'>" +
                                elem.importe + "</td><td style='text-align:right;'>" +
                                elem.sinIva + "</td><td style='text-align:right;'>" +
                                elem.ieps + "</td><td style='text-align:right;'>" +
                                elem.total + "</td></tr>");
                        e++;
                    });
                    $(thisb).parent().parent().after("<tr class='AgregadoAjax' style='background-color: #A9DFBF'><td style='border-top:1px solid black;border-left:1px solid black;'>Ticket</td>\n\
                    <td style='border-top:1px solid black;'>Producto</td><td style='border-top:1px solid black;'>Cantidad</td>\n\
                    <td style='border-top:1px solid black;border-right:1px solid black;'>Importe</td>\n\
                    <td style='border-top:1px solid black;border-right:1px solid black;'>IVA</td>\n\
                    <td style='border-top:1px solid black;border-right:1px solid black;'>IEPS</td>\n\
                    <td style='border-top:1px solid black;border-right:1px solid black;'>Total</td></tr>");
                }
            });
        });
    });
</script>
