<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$mysqli = iconnect();

require_once './services/CambioTurnoService.php';

$ctVO = new CtVO();
if ($Corte > 0) {
    $ctVO = $ctDAO->retrieve($Corte);
}

$Titulo = "Corte: $Corte turno: " . $ctVO->getTurno() . " " . $ctVO->getFecha() . " ";

$cSql = "SELECT ven.alias,COUNT(*) depositos,SUM(ctdep.cincuentac) cincuentac,SUM(ctdep.peso) peso,SUM(ctdep.dos) dos,"
        . "SUM(ctdep.cinco) cinco,SUM(ctdep.diez) diez,SUM(ctdep.veinte) veinte,SUM(ctdep.cincuenta) cincuenta,SUM(ctdep.cien) cien,"
        . "SUM(ctdep.doscientos) doscientos,SUM(ctdep.quinientos) quinientos,SUM(ctdep.mil) mil,SUM(ctdep.total) total "
        . "FROM ctdep LEFT JOIN ven ON ctdep.despachador = ven.id ";
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#autocomplete").focus();
            });
            function redirigir(variable) {
                window.location.href = variable;
            }
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>
        <?php TotalizaDepositos(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr style="background-color: #E1E1E1;font-weight: bold;text-align: center;height: 25px;">
                <td style="width: 25%;" onclick="redirigir('mdepositos.php')">Depositos</td>
                <td style="width: 25%;background-color: #F63;color: white;">Desglose monetario</td>
                <td style="width: 25%;" onclick="redirigir('mdepositost.php')">Saldos x despachador</td>
                <td style="width: 25%;" onclick="redirigir('mvendedores.php')">Vendedores x posicion</td>
            </tr> 
            <tr>
                <td colspan='4' align='left'>

                    <div id="TablaDatos">
                        <table aria-hidden="true">
                            <tr>
                                <td class="fondoNaranja">Despachador</td>
                                <td class="fondoNaranja">#Veces</td>
                                <td class="fondoNaranja">50ctvos</td>
                                <td class="fondoNaranja">$1.00</td>
                                <td class="fondoNaranja">$2.00</td>
                                <td class="fondoNaranja">$5.00</td>
                                <td class="fondoNaranja">$10.00</td>
                                <td class="fondoNaranja">$20.00</td>
                                <td class="fondoNaranja">$50.00</td>
                                <td class="fondoNaranja">$100.00</td>
                                <td class="fondoNaranja">$200.00</td>
                                <td class="fondoNaranja">$500.00</td>
                                <td class="fondoNaranja">$1000.00</td>
                                <td class="fondoNaranja">Total</td>
                            </tr>
                            <?php
                            for ($i = 1; $i < 3; $i = $i + 1) {

                                if ($i == 1) {
                                    $Sql = $cSql . " WHERE corte='$Corte' AND ctdep.despachador <> 0 AND tipo_cambio = 1 GROUP BY despachador ORDER BY despachador";
                                    ?>
                                    <tr>
                                        <td colspan="14" style="text-align: center; font-weight: bold;font-size: 14px;background-color: white;">Desglose en MXN</td>
                                    </tr>
                                    <?php
                                } else {
                                    $Sql = $cSql . " WHERE corte='$Corte' AND ctdep.despachador <> 0 AND tipo_cambio <> 1 GROUP BY despachador ORDER BY despachador";
                                    ?>
                                    <tr>
                                        <td colspan="14" style="text-align: center; font-weight: bold;font-size: 14px;background-color: white;">Desglose en USD</td>
                                    </tr>
                                    <?php
                                }

                                $res = $mysqli->query($Sql);

                                while ($rg = $res->fetch_array()) {

                                    echo "<tr>";

                                    echo "<td style='text-align: right'>" . $rg["alias"] . "</td>";
                                    echo "<td style='text-align: right'>" . $rg["depositos"] . "</td>";
                                    echo "<td style='text-align: right'>" . $rg["cincuentac"] . "</td>";
                                    echo "<td style='text-align: right'>" . $rg["peso"] . "</td>";
                                    echo "<td style='text-align: right'>" . $rg["dos"] . "</td>";
                                    echo "<td style='text-align: right'>" . $rg["cinco"] . "</td>";
                                    echo "<td style='text-align: right'>" . $rg["diez"] . "</td>";
                                    echo "<td style='text-align: right'>" . $rg["veinte"] . "</td>";
                                    echo "<td style='text-align: right'>" . $rg["cincuenta"] . "</td>";
                                    echo "<td style='text-align: right'>" . $rg["cien"] . "</td>";
                                    echo "<td style='text-align: right'>" . $rg["docientos"] . "</td>";
                                    echo "<td style='text-align: right'>" . $rg["quinientos"] . "</td>";
                                    echo "<td style='text-align: right'>" . $rg["mil"] . "</td>";
                                    echo "<td>-</td>";

                                    echo "</tr>";

                                    if ($i == 2) {
                                        $Total += $rg["total"];
                                    } else {
                                        $Total += ($rg[2] * .5) + $rg[3] + ($rg[4] * 2) + ($rg[5] * 5) + ($rg[6] * 10) + ($rg[7] * 20) + ($rg[8] * 50) + ($rg[9] * 100) + ($rg[10] * 200) + ($rg[11] * 500) + ($rg[12] * 1000);
                                    }
                                    ?>
                                    <tr>
                                        <td class="upTitles">Sub-total</td>
                                        <td class="upTitles"></td>
                                        <td class="upTitles"><?= number_format($rg[2] * .5, 1) ?></td>
                                        <td class="upTitles"><?= number_format($rg[3], 0) ?></td>
                                        <td class="upTitles"><?= number_format($rg[4] * 2, 0) ?></td>
                                        <td class="upTitles"><?= number_format($rg[5] * 5, 0) ?></td>
                                        <td class="upTitles"><?= number_format($rg[6] * 10, 0) ?></td>
                                        <td class="upTitles"><?= number_format($rg[7] * 20, 0) ?></td>
                                        <td class="upTitles"><?= number_format($rg[8] * 50, 0) ?></td>
                                        <td class="upTitles"><?= number_format($rg[9] * 100, 0) ?></td>
                                        <td class="upTitles"><?= number_format($rg[10] * 200, 0) ?></td>
                                        <td class="upTitles"><?= number_format($rg[11] * 500, 0) ?></td>
                                        <td class="upTitles"><?= number_format($rg[12] * 1000, 0) ?></td>
                                        <td class="upTitles"><?= number_format($Total, 1) ?></td>
                                    </tr>

                                    <?php
                                    $Gtotal += $Total;
                                    $Total = 0;
                                    $n5c += $rg[2];
                                    $n1 += $rg[3];
                                    $n2 += $rg[4];
                                    $n5 += $rg[5];
                                    $n10 += $rg[6];
                                    $n20 += $rg[7];
                                    $n50 += $rg[8];
                                    $n100 += $rg[9];
                                    $n200 += $rg[10];
                                    $n500 += $rg[11];
                                    $n1000 += $rg[12];

                                    $nRng++;
                                }   //Endwhile
                            }//endfor    
                            ?>


                            <tr>
                                <td colspan="14" style="text-align: center; font-weight: bold;color: #FF6633;font-size: 14px;background-color: white;">Gran total</td>
                            </tr>


                            <tr>
                                <td align='right'>Desgloce</td>
                                <td align='right'></td>
                                <td align='right'><?= number_format($n5c, 2) ?></td>
                                <td align='right'><?= number_format($n1, 0) ?></td>
                                <td align='right'><?= number_format($n2, 0) ?></td>
                                <td align='right'><?= number_format($n5, 0) ?></td>
                                <td align='right'><?= number_format($n10, 0) ?></td>
                                <td align='right'><?= number_format($n20, 0) ?></td>
                                <td align='right'><?= number_format($n50, 0) ?></td>
                                <td align='right'><?= number_format($n100, 0) ?></td>
                                <td align='right'><?= number_format($n200, 0) ?></td>
                                <td align='right'><?= number_format($n500, 0) ?></td>
                                <td align='right'><?= number_format($n1000, 0) ?></td>
                                <td align='left'>-</td></tr>

                            <tr>
                                <td class="upTitles"></td>
                                <td class="upTitles"></td>
                                <td class="upTitles"><?= number_format($n5c * .5, 2) ?></td>
                                <td class="upTitles"><?= number_format($n1 * 1, 0) ?></td>
                                <td class="upTitles"><?= number_format($n2 * 2, 0) ?></td>
                                <td class="upTitles"><?= number_format($n5 * 5, 0) ?></td>
                                <td class="upTitles"><?= number_format($n10 * 10, 0) ?></td>
                                <td class="upTitles"><?= number_format($n20 * 20, 0) ?></td>
                                <td class="upTitles"><?= number_format($n50 * 50, 0) ?></td>
                                <td class="upTitles"><?= number_format($n100 * 100, 0) ?></td>
                                <td class="upTitles"><?= number_format($n200 * 200, 0) ?></td>
                                <td class="upTitles"><?= number_format($n500 * 500, 0) ?></td>
                                <td class="upTitles"><?= number_format($n1000 * 1000, 0) ?></td>
                                <td class="upTitles"><?= number_format($Gtotal, 1) ?></td>
                            </tr>

                            <?php
                            $nMonedas = ($n5c * .5) + ($n1 * 1) + ($n2 * 2) + ($n5 * 5) + ($n10 * 10);
                            $nBilletes = ($n20 * 20) + ($n50 * 50) + ($n100 * 100) + ($n200 * 200) + ($n500 * 500) + ($n1000 * 1000);
                            ?>

                            <tr class='texto_tablas'>
                                <td align='center' colspan=7  bgcolor='#e1e1e1'>Monedas: <?= number_format($nMonedas, 2) ?></td>
                                <td align = 'center' colspan = 7 bgcolor = '#e1e1e1'>Billetes: <?= number_format($nBilletes, 2) ?></td>
                            </tr>

                        </table>

                    </div>

                    <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
                        <tr>
                            <td width='50%' align='center' class='texto_tablas'><a class="textosCualli_i" href=javascript:winuni('impdesgloce.php?Corte=<?= $Corte ?>&op=1')><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></a><br>Desgloce en MXN</td>
                            <td width='50%' align='center' class='texto_tablas'><a class="textosCualli_i" href=javascript:winuni('impdesgloce.php?Corte=<?= $Corte ?>&op=2')><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></a><br>Desgloce en USD</td>
                        </tr>
                    </table>

                </td>
            </tr>

        </table>

        <?php echo $paginador->footer(false, null, false, false, 0, false); ?>

        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>