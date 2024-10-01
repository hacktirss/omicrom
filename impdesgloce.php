<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("data/CtDAO.php");

use com\softcoatl\utils as utils;

require './services/ReportesVentasService.php';

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$usuarioSesion = getSessionUsuario();

$ctDAO = new CtDAO();
$ctVO = $ctDAO->retrieve($Corte);

$op = $request->getAttribute("op");

$Titulo = "Corte: $Corte Turno:  " . $ctVO->getTurno() . " fecha: " . $ctVO->getFecha();

if ($op <> 1) {

    $display = "Desglose monetario en dolares";

    $query1 = " SELECT 20 denominacion,  SUM(veinte) cantidad    FROM ctdep WHERE corte = $Corte AND tipo_cambio<>1 UNION
                SELECT 50 denominacion,  SUM(cincuenta) cantidad FROM ctdep WHERE corte = $Corte AND tipo_cambio<>1 UNION
                SELECT 100 denominacion, SUM(cien) cantidad      FROM ctdep WHERE corte = $Corte AND tipo_cambio<>1 UNION
                SELECT 200 denominacion, SUM(doscientos) cantidad  FROM ctdep WHERE corte = $Corte AND tipo_cambio<>1 UNION
                SELECT 500 denominacion, SUM(quinientos) cantidad  FROM ctdep WHERE corte = $Corte AND tipo_cambio<>1 UNION
                SELECT 1000 denominacion,SUM(mil) cantidad       FROM ctdep WHERE corte = $Corte AND tipo_cambio<>1";


    $query2 = "SELECT 0.5 denominacion,  SUM(cincuentac) cantidad    FROM ctdep WHERE corte = $Corte AND tipo_cambio<>1 UNION
                SELECT 1 denominacion,  SUM(peso) cantidad FROM ctdep WHERE corte = $Corte AND tipo_cambio<>1 UNION
                SELECT 2 denominacion, SUM(dos) cantidad      FROM ctdep WHERE corte = $Corte AND tipo_cambio<>1 UNION
                SELECT 5 denominacion, SUM(cinco) cantidad  FROM ctdep WHERE corte = $Corte AND tipo_cambio<>1 UNION
                SELECT 10 denominacion, SUM(diez) cantidad  FROM ctdep WHERE corte = $Corte AND tipo_cambio<>1";

    $query3 = "SELECT bancos.banco,bancos.cuenta,egr.plomo,egr.concepto,egr.importe,
                bancos.tipo_moneda, bancos.tipo_cambio      
                FROM egr LEFT JOIN bancos ON egr.clave=bancos.id 
                WHERE egr.corte = $Corte AND egr.tipo_cambio <> 1";
} else {
    $display = "Desglose monetario MXN";


    $query1 = "SELECT 20 denominacion,  SUM(veinte) cantidad    FROM ctdep WHERE corte = $Corte AND tipo_cambio=1 UNION
                SELECT 50 denominacion,  SUM(cincuenta) cantidad FROM ctdep WHERE corte = $Corte AND tipo_cambio=1 UNION
                SELECT 100 denominacion, SUM(cien) cantidad      FROM ctdep WHERE corte = $Corte AND tipo_cambio=1 UNION
                SELECT 200 denominacion, SUM(doscientos) cantidad  FROM ctdep WHERE corte = $Corte AND tipo_cambio=1 UNION
                SELECT 500 denominacion, SUM(quinientos) cantidad  FROM ctdep WHERE corte = $Corte AND tipo_cambio=1 UNION
                SELECT 1000 denominacion,SUM(mil) cantidad       FROM ctdep WHERE corte = $Corte AND tipo_cambio=1";

    $query2 = "SELECT 0.5 denominacion,  SUM(cincuentac) cantidad    FROM ctdep WHERE corte = $Corte AND tipo_cambio=1 UNION
                SELECT 1 denominacion,  SUM(peso) cantidad FROM ctdep WHERE corte = $Corte AND tipo_cambio=1 UNION
                SELECT 2 denominacion, SUM(dos) cantidad      FROM ctdep WHERE corte = $Corte AND tipo_cambio=1 UNION
                SELECT 5 denominacion, SUM(cinco) cantidad  FROM ctdep WHERE corte = $Corte AND tipo_cambio=1 UNION
                SELECT 10 denominacion, SUM(diez) cantidad  FROM ctdep WHERE corte = $Corte AND tipo_cambio=1";

    $query3 = "SELECT bancos.banco,bancos.cuenta,egr.plomo,egr.concepto,egr.importe,
                bancos.tipo_moneda, bancos.tipo_cambio      
                FROM egr LEFT JOIN bancos ON egr.clave=bancos.id 
                WHERE egr.corte = $Corte AND egr.tipo_cambio = 1";
}

$registros1 = utils\IConnection::getRowsFromQuery($query1);

$registros2 = utils\IConnection::getRowsFromQuery($query2);

$registros3 = utils\IConnection::getRowsFromQuery($query3);
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#Corte").val("<?= $Corte ?>");
                $("#op").val("<?= $op ?>");
            });
        </script>
    </head>

    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo) ?>
            <div id="Reportes" style="min-height: 250px;width: 75%">
                <table aria-hidden="true">
                    <thead>
                        <tr class="titulo">
                            <td colspan="3"><?= $display ?></td>
                        </tr>
                        <tr>
                            <td>Denominacion</td>
                            <td>Cantidad</td>
                            <td>Total</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="subtitulo">
                            <td class="tdCliente" colspan="3" style="text-align: center;">*** Billetes ***</td>
                        </tr>

                        <?php
                        $bImp = 0;
                        foreach ($registros1 as $rg) {
                            echo "<tr>";
                            echo "<td>" . number_format($rg["denominacion"], 2) . "</td>";
                            echo "<td>" . $rg["cantidad"] . "</td>";
                            echo "<td>" . number_format($rg["denominacion"] * $rg["cantidad"], 2) . "</td>";
                            echo "</tr>";
                            $bImp += $rg["denominacion"] * $rg["cantidad"];
                        }
                        ?>
                        <tr class="subtotal">
                            <td></td>
                            <td></td>
                            <td><?= number_format($bImp, 2) ?></td>
                        </tr>

                        <tr class="subtitulo">
                            <td class="tdCliente" colspan="3" style="text-align: center;">*** Monedas ***</td>
                        </tr>

                        <?php
                        $mImp = 0;
                        foreach ($registros2 as $rg) {
                            $mImp += $rg["denominacion"] * $rg["cantidad"];
                            echo "<tr>";
                            echo "<td>" . number_format($rg["denominacion"], 2) . "</td>";
                            echo "<td>" . $rg["cantidad"] . "</td>";
                            echo "<td>" . number_format($rg["denominacion"] * $rg["cantidad"], 2) . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td></td>
                            <td><?= number_format($mImp, 2) ?></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Gran total</td>
                            <td><?= number_format($mImp + $bImp, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div id="Reportes" style="min-height: 100px;">
                <table aria-hidden="true">
                    <thead>
                        <tr>
                            <td>Banco</td>
                            <td>Cuenta</td>
                            <td>Número de plomo</td>
                            <td>Concepto</td>
                            <td>Moneda</td>
                            <td>Cant</td>
                            <td>Tpo.cambio</td>
                            <td>Importe</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($registros3 as $rg) {

                            echo "<tr>";

                            echo "<td>" . $rg["banco"] . "</td>";
                            echo "<td>" . $rg["cuenta"] . "</td>";
                            echo "<td>" . $rg["plomo"] . "</td>";
                            echo "<td>" . ucwords(strtolower($rg["concepto"])) . "</td>";
                            if ($rg[tipo_moneda] == 2) { //Dls
                                echo "<td align='center'>USD</td>";
                                echo "<td>" . number_format($rg["importe"] / $rg["tipo_cambio"], 2) . "</td>";
                                echo "<td>" . number_format($rg["tipo_cambio"], 2) . "</td>";
                                echo "<td>" . number_format($rg["importe"], 2) . "</td>";
                            } else {
                                echo "<td align='center'>MXN</td>";
                                echo "<td>" . number_format($rg["importe"], 2) . "</td>";
                                echo "<td>" . number_format($rg["tipo_cambio"], 2) . "</td>";
                                echo "<td>" . number_format($rg["importe"], 2) . "</td>";
                            }
                            echo "</tr>";
                            $nImp += $rg["importe"];
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="7"></td>
                            <td><?= number_format($nImp, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div id="Reportes" style="min-height: 50px;padding-top: 20px;">
                <table aria-hidden="true">
                    <tfoot>
                        <tr>
                            <td style="text-align: center;">Nombre del responsable</td>
                            <td style="text-align: center;">Firma</td>
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
                            <td>
                                <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                            </td>
                        </tr>
                    </table>
                </div>
                <input type="hidden" name="Corte" id="Corte">
                <input type="hidden" name="op" id="op">
            </form>
            <?php topePagina(); ?>
        </div>
    </body>
</html>

