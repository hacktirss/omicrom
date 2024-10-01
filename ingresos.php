<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

$Titulo = "Reporte de depositos bancarios del " . $FechaI . "al" . $FechaF;

$registros = utils\IConnection::getRowsFromQuery($selectBancos);
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
                $("#cFechaI").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaI")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#cFechaF").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaF")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#Desglose").val("<?= $Desglose ?>");
            });
        </script>
    </head>

    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo) ?>
            <div  id="Reportes" style="min-height: 200px;">
                 <table aria-hidden="true">
                    <thead>
                        <tr>
                            <td>Banco</td>
                            <td>Cuenta</td>
                            <td>Corte</td>
                            <td>Turno</td>
                            <td>Fecha</td>
                            <td>Plomo</td>
                            <td>Status</td>
                            <td>Importe</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $nRng = 0;
                        $cont = 1;
                        $cCli = '';
                        $cCod = '';
                        $banco = "";
                        foreach ($registros as $rg) {
                            if ($banco !== "" && $banco !== $rg["banco"]) {
                                ?>
                                <tr class="subtotal">
                                    <td colspan="6"></td>
                                    <td>Total</td>
                                    <td class="moneda"><?= number_format($nSub, 2) ?></td>
                                </tr>
                                <?php
                                $nSub = 0;
                            }
                            ?>
                            <tr>
                                <td><?= ucwords(strtolower($rg["banco"])) ?></td>
                                <td class="numero"><?= $rg["cuenta"] ?></td>
                                <td class="numero"><?= $rg["corte"] ?></td>
                                <td class="numero"><?= $rg["turno"] ?></td>
                                <td><?= $rg["fecha"] ?></td>
                                <td><?= ucwords(strtolower($rg["plomo"])) ?></td>
                                <?php
                                if (ucwords(strtolower($rg["statusctv"])) !== "Cerrado") {
                                    ?>
                                <td style="font-weight: bold"><?= ucwords(strtolower($rg["statusctv"])) ?></td>
                                    <?php
                                } else {
                                    ?>
                                    <td><?= ucwords(strtolower($rg["statusctv"])) ?></td>
                                    <?php
                                }
                                ?>
                                <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                            </tr>
                            <?php
                            $banco = $rg["banco"];
                            $nSub += $rg["importe"];
                            $nImpT += $rg["importe"];
                            $nRng++;
                            $cont++;
                        }
                        ?>

                        <tr class="subtotal">
                            <td colspan="6"> </td>
                            <td>Total</td>
                            <td class="moneda"><?= number_format($nSub, 2) ?></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr >
                            <td colspan="6"></td>
                            <td>Gran total</td>
                            <td class="moneda"><?= number_format($nImpT, 2) ?></td>

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
                            <td>
                                <a href="egresos.php" title="">Compras</a> 
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
            <?php topePagina(); ?>
        </div>
    </body>
</html>
