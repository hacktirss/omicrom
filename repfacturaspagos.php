<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

$Titulo = "Relacion de facturas y pagos del $FechaI al $FechaF";

$registros = utils\IConnection::getRowsFromQuery($selectCFDI_Pagos);

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
                 <table aria-hidden="true">
                    <thead>
                        <tr class="titulo">
                            <td colspan="14">Datos de la factura</td>
                            <td colspan="3">Datos del pago</td>
                        </tr>
                        <tr>
                            <td>#</td>
                            <td>Folio</td>
                            <td>Fecha</td>
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
                            <td class="oculto">Productos</td>
                            <td>Pago</td>
                            <td>Fecha</td>
                            <td>Importe</td>
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
                                $oPos ++;
                            } elseif ($rg["origen"] == 3) {
                                $oExt ++;
                            } else {
                                $oNA ++;
                            }
                            ?>
                            <tr>
                                <td><?= $nRng++ ?></td>
                                <td><?= $rg["folio"] ?></td>
                                <td><?= $rg["fecha"] ?></td>
                                <td><?= $rg["cliente"] ?></td>
                                <td><?= ucwords(strtolower(substr($rg["nombre"], 0, 30))) ?></td>
                                <td><?= $rg["tipodepago"] ?></td>
                                <td><?= ucwords(strtolower($rg["formadepago"])) ?></td>
                                <td class="numero"><?= number_format($rg["cantidad"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["iva"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["ieps"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["total"], 2) ?></td>
                                <td><?= statusCFDI($rg["status"]) ?></td>
                                <td class="oculto"><?= $rg["productos"] ?></td>
                                <td><?= $rg["pago"] ?></td>
                                <td><?= $rg[fecha_pago] ?></td>
                                <td class="numero"><?= number_format($rg[importe_pago], 2) ?></td>
                            </tr>
                            <?php
                            $nCantidadt += $rg["cantidad"];
                            $nImportet += $rg["importe"];
                            $nIvat += $rg["iva"];
                            $nIepst += $rg["ieps"];
                            $nTotal += $rg["total"];
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
                            <td>Total</td>
                            <td><?= number_format($nCantidadt, 2) ?></td>
                            <td><?= number_format($nImportet, 2) ?></td>
                            <td><?= number_format($nIvat, 2) ?></td>
                            <td><?= number_format($nIepst, 2) ?></td>
                            <td><?= number_format($nTotal, 2) ?></td>
                            <td></td>
                            <td></td>
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
                            <td align="left" colspan="2">
                                <div style="position: relative;">
                                    <input style="width: 100%;" type="search" id="autocomplete" name="ClienteS">
                                </div>
                                <div id="autocomplete-suggestions"></div>
                            </td>
                            <td align="center">
                                <a href="bajarep.php?Nombre=<?= urlencode($Titulo) ?>&cSql=<?= urlencode($selectCFDI_Pagos) ?>" title="Exportar a formato Excel"><i class="icon fa fa-lg fa-file-excel-o" aria-hidden="true"></i></a>
                            </td>
                            <td align="center">
                                <a href="repfacturas.php?" title="Relacion de facturas">Facturación</a> 
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
                                        <td>Criterio de búsqueda:</td>
                                        <td>
                                            Factura <input type="radio" name="criterio" value="fc.fecha" <?= $Criterio === "fc.fecha" ? "checked" : "" ?>>  
                                            Pago <input type="radio" name="criterio" value="pagos.fecha_deposito" <?= $Criterio === "pagos.fecha_deposito" ? "checked" : "" ?>>
                                        </td>
                                    </tr>
                                 </table>
                            </td> 
                            <td>
                                <table style="text-align: left" aria-hidden="true">
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
                                <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
            <?php topePagina() ?>
        </div>
    </body>
</html>
