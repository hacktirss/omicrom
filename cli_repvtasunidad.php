<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesClientesService.php";

$request = utils\HTTPUtils::getRequest();

$Titulo = "Consumos del $FechaI al $FechaF ";

$registros = utils\IConnection::getRowsFromQuery($selectConsumos);

$registrosT = utils\IConnection::getRowsFromQuery($selectConsumosTotalesByProducto);

$cSql = $selectConsumos;
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#FechaI").val("<?= $FechaI ?>").attr("size", "10");
                $("#FechaF").val("<?= $FechaF ?>").attr("size", "10");
                $("#Codigo").val("<?= $Codigo ?>");
                $("#Clave").val("<?= $Clave ?>");
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
                        $contCliente = 1;
                        $contCodigo = 1;
                        $cCli = "";
                        $cCod = "";
                        $uptitles = true;
                        $nImpR = $nImpC = $nLtsC = 0;
                        foreach ($registros as $rg) {
                        //error_log("cCod: $cCod && $cCod !== $rg["codigo"] && ($cCli == $rg["cliente"] ||  $contCodigo > 1)");
                        $style = "";
                        if ($cCod !== trim($rg["codigo"]) && ($cCli === $rg["cliente"] || $contCodigo > 1)) {
                            ?>
                            <tr class="subtotal">
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>                                        
                                <td></td>
                                <td>Subtotal <?= $contCodigo ?></td>
                                <td></td>  
                                <td><?= number_format($nLtsC, 2) ?></td>
                                <td><?= number_format($nImpR, 2) ?></td>
                                <td><?= number_format($nImpC, 2) ?></td>
                            </tr>
                            <?php
                            $uptitles = false;
                            $nImpR = $nImpC = $nLtsC = 0;
                            $nRng = 0;
                            $contCodigo ++;
                        }
                        $cCod = trim($rg["codigo"]);

                        if ($cCli !== $rg["cliente"]) {
                            if (!empty($cCli)) {
                                ?>
                                <tr class="subtotal">
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>                                        
                                    <td></td>
                                    <td>Total <?= $contCliente ?></td>
                                    <td></td> 
                                    <td><?= number_format($nLts, 2) ?></td>
                                    <td><?= number_format($rImp, 2) ?></td>
                                    <td><?= number_format($nImp, 2) ?></td>
                                </tr>
                                <?php
                                $rImp = $nImp = $nLts = 0;
                                $nImpR = $nImpC = $nLtsC = 0;
                                $nRng = 0;

                                $uptitles = true;
                                $contCodigo = 1;
                                $contCliente++;
                            }
                            ?>
                            <tr class="subtitulo"><td colspan="100%" class="tdCliente">***<?= $rg["cliente"] . " " . $rg["nombre"] ?> ***</td></tr>
                            <?php
                        }
                        $cCli = $rg["cliente"];
                        if (abs($rg["pagoreal"] - $rg["importe"]) > 0.5) {
                            $style = "style='background-color: #F7FF7C' title='El importe fue modificado'";
                        }
                        ?>
                        <tr <?= $style ?>>
                            <td><?= $cont ?></td>
                            <td><?= $rg["isla_pos"] ?></td>
                            <td><?= $rg["ticket"] ?></td>
                            <td><?= $rg["corte"] ?></td>
                            <td><?= $rg["impreso"] ?></td>
                            <td><?= $rg["fecha"] ?></td>
                            <td><?= ucwords(strtoupper($rg["placas"])) ?></td>
                            <td><?= $rg["kilometraje"] ?></td>
                            
                            <?php if ($rg["tipo"] === "0") { ?>
                                <td class="overflow"><?= ucwords(strtolower($rg["descripcion"])) ?></td>
                                <td><?= $rg["producto"] ?></td>                                
                            <?php } else { ?>
                                <td class="overflow" colspan="2"><?= ucwords(strtolower($rg["descripcion"])) ?></td>
                            <?php } ?>
                                
                            <?php if ($rg["uuid"] !== "-----") { ?>
                                <td align="center" style="font-weight: bold;"><i class="fa fa-check-square-o" aria-hidden="true"></i></td>
                            <?php } else { ?>
                                <td align="center"><i class="fa fa-square-o" aria-hidden="true"></i></td>
                            <?php } ?>

                            <td class="numero"><?= number_format($rg["volumen"], 2) ?></td>
                            <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                            <td class="numero"><?= number_format($rg["pagoreal"], 2) ?></td>
                            <?php ?>
                        </tr>
                        <?php
                        $rImp += $rg["importe"];
                        $nImp += $rg["pagoreal"];
                        $nLts += $rg["volumen"];
                        $nImpR += $rg["importe"];
                        $nImpC += $rg["pagoreal"];
                        $nLtsC += $rg["volumen"];
                        $nImpTR += $rg["importe"];
                        $nImpT += $rg["pagoreal"];
                        $nLtsT += $rg["volumen"];
                        $nRng++;
                        $cont++;
                    }
                        ?>

                        <?php if (!$uptitles) { ?>
                            <tr class="subtotal">
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>                                        
                                <td></td>
                                <td>Subtotal <?= $contCodigo ?></td>
                                <td></td>
                                <td><?= number_format($nLtsC, 2) ?></td>
                                <td><?= number_format($nImpR, 2) ?></td>
                                <td><?= number_format($nImpC, 2) ?></td>
                            </tr>
                        <?php } ?>
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
                            <td>Total <?= $contCliente == 1 ? "" : $contCliente ?></td>
                            <td></td>
                            <td><?= number_format($nLts, 2) ?></td>
                            <td><?= number_format($rImp, 2) ?></td>
                            <td><?= number_format($nImp, 2) ?></td>
                        </tr>

                        <tr>
                            <td colspan="9"></td>
                            <td>Gran Total</td>                                     
                            <td></td>
                            <td><?= number_format($nLtsT, 2) ?></td>
                            <td><?= number_format($nImpTR, 2) ?></td>
                            <td><?= number_format($nImpT, 2) ?></td>
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
                                <td class="numero"><?= $rg["cargas"] ?></td>
                                <td class="numero"><?= number_format($rg["volumen"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["pesos"], 2) ?></td>
                            </tr>
                            <?php
                            $Imp += $rg["pesos"];
                            $Lts += $rg["volumen"];
                            $Car += $rg["cargas"];
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>Total</td>
                            <td><?= $Car ?></td>
                            <td><?= number_format($Lts, 2) ?></td>
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
                                <td style="width: 30%;">
                                    <table aria-hidden="true">
                                        <tr>
                                            <td>F.inicial:</td>
                                            <td><input type="text" id="FechaI" name="FechaI" alt="Calendario"></td>
                                            <td class="calendario"><i id="cFechaI" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
                                        </tr>
                                        <tr>
                                            <td>F.final:</td>
                                            <td><input type="text" id="FechaF" name="FechaF" alt="Calendario"></td>
                                            <td class="calendario"><i id="cFechaF" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
                                        </tr>
                                    </table>
                                </td>
                                <td>
                                    <select name="Codigo" id="Codigo"> 
                                        <option value="*">* Todos los códigos</option>
                                        <?php
                                        $UniA = $mysqli->query("SELECT codigo,impreso,placas FROM unidades WHERE cliente='$Cliente'");
                                        while ($rg = $UniA->fetch_array()) {
                                            echo "<option value='".$rg["codigo"]."'>" . substr($rg["impreso"], 5) . " | ".$rg["placas"]."</option>";
                                        }
                                        ?>
                                    </select>
                                    <select name="Clave" id="Clave"> 
                                        <option value="*">* Todos los productos</option>
                                        <?php
                                        $Produc = $mysqli->query("SELECT * FROM com where activo = 'Si'");
                                        while ($rg = $Produc->fetch_array()) {
                                            echo "<option value='".$rg["descripcion"]."'>".$rg["descripcion"]."</option>";
                                        }
                                        ?>
                                        <option value="Aditivos">ADITIVOS</option>
                                    </select>
                                </td>
                                <td>
                                    <span><input type="submit" name="Boton" value="Enviar"></span>
                                    <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                                    <span class="ButtonExcel"><a href="bajarep.php?cSql=<?= urlencode($cSql)?>"><i class="icon fa fa-lg fa-bold fa-file-excel-o" aria-hidden="true"></i></a></span>
                                </td>
                            </tr>
                    </table>
                </div>
            </form>
            <?php topePagina(); ?>
        </div>
    </body>
</html>

