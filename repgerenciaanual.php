<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesAnualVentasService.php";
$request->getAttribute("Fecha") ? $yearNow = $request->getAttribute("Fecha") : $yearNow = date("Y");
$Titulo = "Reporte para gerencia promedio litros por día";
$registros1 = utils\IConnection::getRowsFromQuery($selectGerencia1);
$registros = array();
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <title><?= $Gcia ?></title>
    </head>
    <body>

        <div id="container">
            <?php nuevoEncabezado($Titulo); ?>
            <div id="Reportes">
                <table aria-hidden="true">  
                    <thead>
                        <tr><td colspan="3"></td>
                            <?php
                            foreach ($Combustibles as $key => $value):
                                echo "<td colspan='4' style='color:$value'> " . $key . " </td><td colspan='2' style='color:$value'> $ </td>";
                            endforeach;
                            ?>
                            <td colspan="4">Acumulado</td>
                        </tr>
                        <tr>
                            <td>Año</td>
                            <td>Mes</td>
                            <td>No.</td>
                            <?php
                            foreach ($Combustibles as $key => $value):
                                ?>
                                <td title="Litros promedio vendidos en el mes"> LEMP </td>
                                <td> Acumulada</td>
                                <td> Proyección </td>
                                <td title="Precio promedio de compra mensual"> MB </td>
                                <td title="Precio de compra promedio del mes"> Compra </td>
                                <td title="Precio de venta promedio del mes"> Venta </td>
                                <?php
                            endforeach;
                            ?>
                            <td>Diaria</td>
                            <td>Acumulada</td>
                            <td>Proyección</td>
                            <td>Total $ </td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $AcumuladoTotal = 0; //Acumulado promedio total
                        $Total = 0; //Dinero total promedio 
                        $AcumuladoProyeccion = 0;
                        $e = 0;
                        foreach ($Datos as $com) {
                            if ($com["mes"] === "enero") {
                                $e = 1;
                            } elseif ($com["mes"] === "febrero") {
                                $e = 2;
                            } elseif ($com["mes"] === "marzo") {
                                $e = 3;
                            } elseif ($com["mes"] === "abril") {
                                $e = 4;
                            } elseif ($com["mes"] === "mayo") {
                                $e = 5;
                            } elseif ($com["mes"] === "junio") {
                                $e = 6;
                            } elseif ($com["mes"] === "julio") {
                                $e = 7;
                            } elseif ($com["mes"] === "agosto") {
                                $e = 8;
                            } elseif ($com["mes"] === "septiembre") {
                                $e = 9;
                            } elseif ($com["mes"] === "octubre") {
                                $e = 10;
                            } elseif ($com["mes"] === "noviembre") {
                                $e = 11;
                            } else {
                                $e = 12;
                            }
                            ?>
                            <tr>
                                <td class="numero"><?= $com["year"] ?></td>
                                <td class="text"><?= $com["mes"] ?></td>
                                <td class="numero"><?= $e ?></td>
                                <?php
                                $i = 1;
                                $diasMes = cal_days_in_month(CAL_GREGORIAN, $e, $yearNow);
                                foreach ($Combustibles as $key => $value):
                                    $venta = $com["rs" . $i] * $com["ps" . $i];
                                    ?>
                                    <td class="numero"><?= number_format($com["rs$i"] / $diasMes, 2) ?></td>
                                    <?php
                                    if ($i == 1) {
                                        $suma1 += $com["rs$i"] / $diasMes;
                                        $sumaMB1 += $com["costo$i"] * $com["ps$i"];
                                        $tt1 = $sumaMB1;
                                        $porColum1 = $com["rs$i"] / $diasMes;
                                        echo "<td class='numero'>" . number_format($suma1, 2) . "</td>";
                                        $proyeccion1 = $suma1 / $e * 12;
                                        $resultadoAcu = $suma1 / $e;
                                        echo "<td class='numero'>" . number_format($proyeccion1, 2) . "</td>";
                                    } else if ($i == 2) {
                                        $suma2 += $com["rs$i"] / $diasMes;
                                        $sumaMB2 += $com["costo$i"] * $com["ps$i"];
                                        $tt2 = $sumaMB2;
                                        $porColum2 = $com["rs$i"] / $diasMes;
                                        echo "<td class='numero'>" . number_format($suma2, 2) . "</td>";
                                        $proyeccion2 = $suma2 / $e * 12;
                                        $resultadoAcu1 = $suma2 / $e;
                                        echo "<td class='numero'>" . number_format($proyeccion2, 2) . "</td>";
                                    } else if ($i == 3) {
                                        $suma3 += $com["rs$i"] / $diasMes;
                                        $sumaMB3 += $com["costo$i"] * $com["ps$i"];
                                        $tt3 = $sumaMB3;
                                        $porColum3 = $com["rs$i"] / $diasMes;
                                        echo "<td class='numero'>" . number_format($suma3, 2) . "</td>";
                                        $proyeccion3 = $suma3 / $e * 12;
                                        $resultadoAcu2 = $suma3 / $e;
                                        echo "<td class='numero'>" . number_format($proyeccion3, 2) . "</td>";
                                    } else if ($i == 4) {
                                        $suma4 += $com["rs$i"] / $diasMes;
                                        $sumaMB4 += $com["costo$i"] * $com["ps$i"];
                                        $tt4 = $sumaMB4;
                                        $porColum4 = $com["rs$i"] / $diasMes;
                                        echo "<td class='numero'>" . number_format($suma4, 2) . "</td>";
                                        $proyeccion4 = $suma4 / $e * 12;
                                        $resultadoAcu3 = $suma4 / $e;
                                        echo "<td class='numero'>" . number_format($proyeccion4, 2) . "</td>";
                                    } else if ($i == 5) {
                                        $suma5 += $com["rs$i"] / $diasMes;
                                        $sumaMB5 += $com["costo$i"] * $com["ps$i"];
                                        $tt5 = $sumaMB5;
                                        $porColum5 = $com["rs$i"] / $diasMes;
                                        echo "<td class='numero'>" . number_format($suma5, 2) . "</td>";
                                        $proyeccion5 = $suma5 / $e * 12;
                                        $resultadoAcu4 = $suma5 / $e;
                                        echo "<td class='numero'>" . number_format($proyeccion5, 2) . "</td>";
                                    }
                                    ?>
                                    <td class="numero"><?= number_format($com["costo$i"] * $com["ps$i"], 2) ?></td>
                                    <td class="numero"><?= number_format($com["costo$i"], 2) ?></td>
                                    <td class="numero"><?= number_format($com["ps$i"], 2) ?></td>
                                    <?php
                                    $acumulado += $com["costo$i"] * $com["ps$i"];
                                    $resulAcumulado = array("result1" => "$resultadoAcu", "result2" => "$resultadoAcu1", "result3" => "$resultadoAcu2", "result4" => "$resultadoAcu3", "result5" => "$resultadoAcu4");
                                    $resulAcumuladoMB = array("resultmb1" => "$tt1", "resultmb2" => "$tt2", "resultmb3" => "$tt3", "resultmb4" => "$tt4", "resultmb5" => "$tt5");
                                    $i++;
                                endforeach;
                                $TotalDiario = $porColum1 + $porColum2 + $porColum3 + $porColum4 + $porColum5 / $i;
                                $AcumuladoTotal += $TotalDiario;
                                $AcumuladoProyeccion = $AcumuladoTotal / $e * $diasMes;
                                ?>
                                <td class="numero"><?= number_format($TotalDiario, 2) ?></td>
                                <td class="numero"><?= number_format($AcumuladoTotal, 2) ?></td>
                                <td class="numero"><?= number_format($AcumuladoProyeccion, 2) ?></td>
                                <td class="numero"><?= number_format($acumulado, 2) ?> </td>
                            </tr>
                            <?php
                            $SumaAcumulado += $acumulado;
                            $acumulado = 0; 
                            $TotalDia += $TotalDiario;
                            $TotalMonto += $TotalIncrem;
                            if ($e == 12) {
                                ?>
                                <tr bgcolor="#FFFF00">
                                    <td class="numero" colspan="3"><strong>Real</strong></td>
                                    <?php
                                    $i = 1;
                                    foreach ($Combustibles as $key => $value):
                                        ?>
                                        <td><strong>lts/día:</strong></td>
                                        <td class="numero"><strong><?= number_format($resulAcumulado["result$i"], 2) ?></strong></td>
                                        <td  colspan="2" class="numero"><strong><?= number_format($resulAcumuladoMB["resultmb$i"], 2) ?></strong></td>
                                        <td colspan="2"></td>
                                        <?php
                                        $SumatoriaAcumulada += $resulAcumulado["result$i"];
                                        $i++;
                                    endforeach;
                                    ?>
                                    <td colspan="2"><strong>litros diarios</strong></td>
                                    <td><strong><?= number_format($SumatoriaAcumulada, 2) ?></strong></td>
                                    <td><strong><?= number_format($SumaAcumulado,2)?></strong></td>
                                </tr>
                                <tr bgcolor="#FFFF00">
                                    <td colspan="3"></td>
                                    <?php
                                    $i = 1;
                                    foreach ($Combustibles as $key => $value):
                                        ?>
                                        <td colspan="2"></td>
                                        <td class="numero"><strong>% <?= number_format(( $resulAcumulado["result$i"] / $SumatoriaAcumulada ) * 100, 0) ?></strong></td>
                                        <td colspan="2"></td>
                                        <?php
                                        $i++;
                                    endforeach;
                                    ?>
                                    <td colspan="4"></td>
                                </tr>
                                <?php
                                $suma1 = 0;
                                $suma2 = 0;
                                $suma3 = 0;
                                $suma4 = 0;
                                $suma5 = 0;
                                $proyeccion1 = 0;
                                $proyeccion2 = 0;
                                $proyeccion3 = 0;
                                $proyeccion4 = 0;
                                $proyeccion5 = 0;
                                $sumaMB1 = 0;
                                $sumaMB2 = 0;
                                $sumaMB3 = 0;
                                $sumaMB4 = 0;
                                $sumaMB5 = 0;
                                $SumatoriaAcumulada = 0;
                                $SumaAcumulado = 0;
                            }
                            $e++;
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3">Real</td>
                            <?php
                            $i = 1;
                            foreach ($Combustibles as $key => $value):
                                ?>
                                <td>Prom lts/día:</td>
                                <td class="numero"><?= number_format($resulAcumulado["result$i"], 2) ?></td>
                                <td></td>
                                <td><?= number_format($resulAcumuladoMB["resultmb$i"], 2) ?></td>
                                <td></td>
                                <td></td>
                                <?php
                                $SumatoriaAcumulada += $resulAcumulado["result$i"];
                                $i++;
                            endforeach;
                            ?>
                            <td colspan="2">Promedio de litros diarios</td>
                            <td>$<?= number_format($SumatoriaAcumulada, 2) ?></td>
                            <td><?= number_format($SumaAcumulado, 2) ?></td>
                        </tr>
                        <tr>
                            <td colspan="3"></td>
                            <?php
                            $i = 1;
                            foreach ($Combustibles as $key => $value):
                                ?>
                                <td></td>
                                <td></td>
                                <td>% <?= number_format(( $resulAcumulado["result$i"] / $SumatoriaAcumulada ) * 100, 0) ?></td>
                                <td></td>
                                <td></td>
                                <?php
                                $i++;
                            endforeach;
                            ?>
                            <td colspan="4"></td>
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
                            <td style="width: 35%;">
                                <table aria-hidden="true">
                                    <tr>
                                        <td>Año Inicio :</td>
                                        <td><input type="number" id="Fecha" name="Fecha"></td>
                                        <td>Año Fin:</td>
                                        <td><input type="number"  id="Fechafin" name="Fechafin"></td>
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
        <script type="text/javascript">
            var actual = (new Date).getFullYear();
            $("#Fecha").attr("max", actual).attr("min", 2014).val(actual - 1);
            $("#Fechafin").attr("max", actual).attr("min", 2014).val(actual);

        </script>
    </body>
</html>

