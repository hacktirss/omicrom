<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

$Titulo = "Reporte de transacciones  del $FechaI al $FechaF ";

$selectpagos="
SELECT rm.id,rm.corte,t.fecha fechaT,rm.posicion,com.descripcion,rm.volumen,t.monto,t.transaccion
            FROM rm LEFT JOIN com ON rm.producto = com.clavei
            LEFT JOIN ct ON rm.corte=ct.id INNER JOIN rm_transacciones t ON rm.id = t.id
            WHERE ct.id = rm.corte AND t.id > 0 AND 
            DATE(ct.fecha) BETWEEN DATE('$FechaI') AND ('$FechaF') 
            AND rm.tipo_venta IN ('D');";


$registros = utils\IConnection::getRowsFromQuery($selectpagos);


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
            <?php nuevoEncabezado($Titulo); ?>

            <div id="Reportes" style="min-height: 200px;">

                <table aria-hidden="true">
                    <thead>
                        <tr>
                            <td></td>
                            <td>Despacho</td>
                            <td>Numero de tarjeta</td>
                            <td>Fecha de deposito</td>
                            <td>Corte</td>
                            <td>Posicion</td>
                            <td>Producto</td>
                            <td>Litros</td>
                            <td>Importe del deposito</td>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $nRng = 0;
                        foreach ($registros as $rg) {
                            $jsc = json_decode($rg["transaccion"], false);
                            ?>
                            <tr>
                                <td class="numero"><?= number_format($nRng + 1, 0) ?></td>
                                <td class="numero"><?= $rg["id"] ?></td>
                                <?php
                                 foreach($jsc as $key => $val){                            
                                    if($key== "CardNumber"){
                                    ?>
                                       <td><?=$val?></td>
                                    <?php 
                                    }                          //echo $jsc["monto"];;
                                } 
                                ?>
                                <td><?= $rg["fechaT"] ?></td>
                                <td class="numero"><?= $rg["corte"] ?></td>
                                <td class="numero"><?= $rg["posicion"] ?></td>
                                <td><?= $rg["descripcion"] ?></td>                        
                                <td class="numero"><?= number_format($rg["volumen"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["monto"], 2) ?></td>
                            </tr>
                            <?php
                            $nCnt += $rg["volumen"];
                            $nImp += $rg["monto"];
                            $nRng++;
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
                            <td >Total</td>
                            <td class="numero"><?= number_format($nCnt, 2) ?></td>
                            <td class="moneda"><?= number_format($nImp, 2) ?></td>
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
                                <table style="width: 100%" aria-hidden="true">

                                    <tr>
                                        <td>&nbsp;Desglose:</td>
                                        <td style="text-align: left;padding-left: 5px">
                                            <select id="Desglose" name="Desglose">
                                                <option value="Cortes">Cortes</option>
                                                <option value="Dia">Dia</option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </td>

                            <td>
                                <?php
                                if ($request->getAttribute("return") === "resumen.php") {
                                    ?>
                                    <a href="<?= $request->getAttribute("return") ?>">
                                        <i class="fa fa-reply fa-2x" aria-hidden="true"></i>
                                    </a>
                                    <?php
                                }
                                ?>
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
