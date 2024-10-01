<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$usuarioSesion = getSessionUsuario();
require "./services/ReportesVentasService.php";

$request = utils\HTTPUtils::getRequest();
$DetalleTexto = $Detallado === "Si" ? "detallado" : "";
$registros = utils\IConnection::getRowsFromQuery($selectByDia);
if (!$request->hasAttribute("criteria")) {
    $Titulo = "Ventas por $Desglose del $FechaI al $FechaF $DetalleTexto [Reporte Contable]";
    require "./services/ReporteGeneralMensual.php";
}
$Id = 32; /* Número de en el orden de la tabla submenus */
$data = array("Nombre" => $Titulo, "Reporte" => $Id,
    "FechaI" => $FechaI, "FechaF" => $FechaF,
    "Detallado" => $Detallado, "Desglose" => $Desglose,
    "Turno" => $Turno, "Textos" => "Subtotal", "Filtro" => "1");
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>

        <?php require "./config_omicrom_reports.php"; ?>         
        <script type="text/javascript" src="js/export_.js"></script>
        <script type="text/javascript" src="js/repvtasG.js"></script>

        <title><?= $Gcia ?></title>
        <script>

            function ExportToExcel(type, fn, dl) {
                var elt = document.getElementById('tbl_exporttable_to_xls');
                var wb = XLSX.utils.table_to_book(elt, {sheet: "sheet1"});
                return dl ?
                        XLSX.write(wb, {bookType: type, bookSST: true, type: 'base64'}) :
                        XLSX.writeFile(wb, fn || ('ReporteGerencia.' + (type || 'xlsx')));
            }
            $(document).ready(function () {
                $('#cFechaI').css('cursor', 'hand').click(function () {
                    displayCalendar($('#FechaI')[0], 'yyyy-mm-dd', $(this)[0]);
                });
                $('#cFechaF').css('cursor', 'hand').click(function () {
                    displayCalendar($('#FechaF')[0], 'yyyy-mm-dd', $(this)[0]);
                });
                $("#FechaI").val("<?= $FechaI ?>");
                $("#FechaF").val("<?= $FechaF ?>");
            });
        </script>
    </head>
    <style>
        #contenedor {
            height: 35vw; /* Altura del contenedor */
            width: 94vw; /* Ancho del contenedor al 100% del ancho de la pantalla */
            overflow-y: scroll; /* Hace que el contenido sea desplazable verticalmente */
            border: 1px solid #ccc; /* Borde para el contenedor */
            padding: 10px; /* Relleno interno */
        }
    </style>
    <body>
        <input type="hidden" name="idUsuario" value="<?= $usuarioSesion->getId() ?>" id="idUsuario">
        <?php nuevoEncabezado($Titulo); ?> 
        <div id="tbl_exporttable_to_xls">
            <div id="container">
                <div id="contenedor">
                    <div id="Reportes" style="min-height: 200px;"> 
                        <table>
                            <thead>
                                <tr style="color: white;font-size: 11px;">
                                    <td style="background-color: #2C3E50;"></td>
                                    <td style="background-color: #A569BD;" colspan="<?= count($BancosExist) + 1 ?>">Tarjetas bancarias</td>
                                    <td style="background-color: #F5B041;" colspan="<?= count($MonederosExist) + 1 ?>">Monederos</td>
                                    <td style="background-color: #58D68D;width: 90px;" colspan="2">Credito</td>
                                    <td style="background-color: #45B39D;width: 90px;" colspan="2">Débito</td>
                                    <td colspan="2" style="text-align: center;background-color: #5DADE2;">Pagos</td>
                                    <td colspan="12" style="background-color: #D5D8DC;"></td>
                                </tr>
                                <tr style="font-size: 8px;">
                                    <td style="min-width:  80px;">Fecha</td>
                                    <?php
                                    $e = 1;
                                    $numeroMes = date("m", strtotime($FechaI));
                                    $numeroMesTxt = date("F", strtotime($FechaI));
                                    foreach ($BancosExist as $Be) {
                                        ?>
                                        <td data-mes='<?= $numeroMesTxt ?>' 
                                            data-mesno='<?= $numeroMes ?>' 
                                            data-anio='<?= $numeroAnio ?>'  
                                            data-idcli='<?= $BancosExistId["Bnc" . $e] ?>'
                                            data-name='<?= $Be ?>' class='tiposCliente'><?= $Be ?></td>
                                            <?php
                                            $e++;
                                        }
                                        $e = 1;
                                        ?>
                                    <td style="font-size: 12px;">Total</td>
                                    <?php
                                    $e = 1;
                                    foreach ($MonederosExist as $Me) {
                                        ?>
                                        <td data-mes='<?= $numeroMesTxt ?>'  
                                            data-idcli='<?= $MonederosExistId[$e] ?>' 
                                            data-mesno='<?= $numeroMes ?>' 
                                            data-anio='<?= $numeroAnio ?>'  
                                            data-idcli='<?= $MonederosExistId["Bnc" . $e] ?>' 
                                            data-name='<?= $Me ?>' 
                                            class='tiposCliente'><?= $Me ?></td>
                                            <?php
                                            $e++;
                                        }
                                        $e = 1;
                                        ?>
                                    <td style="font-size: 12px;">Total</td>
                                    <td>Pagos</td>
                                    <td>Consumo</td>
                                    <td>Pagos</td>
                                    <td>Consumos</td>
                                    <td>Total Pagos</td>
                                    <td>Total Consumos</td>
                                    <?php
                                    foreach ($TurnosActivs as $Ta) {
                                        ?>
                                        <td><?= $Ta ?></td>
                                        <?php
                                    }
                                    ?>
                                    <td>Efectivo</td>
                                    <?php
                                    foreach ($TurnosActivs as $Ta) {
                                        ?>
                                        <td>Desposito Bancario</td>
                                        <?php
                                    }
                                    ?>
                                    <td  style="font-size: 12px;">Total</td>
                                </tr>
                            </thead>
                            <tbody style="font-size: 11px;">
                                <?php
                                $Mes = explode("-", $FechaI);
                                $DiaG = $Mes[2];
                                if ($DiaG === "01") {
                                    ?>
                                    <tr style="background-color: #A2D9CE;font-size: 12px;font-weight: bold;">
                                        <td style="min-width:  80px;">Edo. Incial</td>
                                        <?php
                                        $e = 1;
                                        $GttIniB = 0;
                                        foreach ($BancosExist as $Be) {
                                            ?>
                                            <td style="text-align: right;"><?= number_format(utils\HTTPUtils::getSessionValue($Be) > 0 ? utils\HTTPUtils::getSessionValue($Be) : 0, 2) ?></td>
                                            <?php
                                            $GttIniB += $GtTTurno["Bancos" . $e] = utils\HTTPUtils::getSessionValue($Be) > 0 ? utils\HTTPUtils::getSessionValue($Be) : 0;
                                            $e++;
                                        }
                                        $GtotalBancos = $GttIniB;
                                        ?>
                                        <td style="text-align: right;"><?= number_format($GttIniB, 2) ?></td>
                                        <?php
                                        $GttIniM = 0;
                                        $e = 1;
                                        foreach ($MonederosExist as $Me) {
                                            ?>
                                            <td style="text-align: right;"><?= number_format(utils\HTTPUtils::getSessionValue($Me) > 0 ? utils\HTTPUtils::getSessionValue($Me) : 0, 2) ?></td>
                                            <?php
                                            $GttIniM += $GtTTurno["Monedero" . $e] = utils\HTTPUtils::getSessionValue($Me) > 0 ? utils\HTTPUtils::getSessionValue($Me) : 0;
                                            $resultados[$Me] = number_format(utils\HTTPUtils::getSessionValue($Me) > 0 ? utils\HTTPUtils::getSessionValue($Me) : 0, 2);
                                        }
                                        $TotalesMonederosG = $GttIniM;
                                        ?>
                                        <td style="text-align: right;"><?= number_format($GttIniM, 2) ?></td>
                                        <td colspan="6"></td>
                                        <?php
                                        foreach ($TurnosActivs as $Ta) {
                                            ?>
                                            <td></td>
                                            <?php
                                        }
                                        ?>
                                        <td></td>
                                        <?php
                                        foreach ($TurnosActivs as $Ta) {
                                            ?>
                                            <td></td>
                                            <?php
                                        }
                                        ?>
                                        <td></td>
                                    </tr>
                                    <?php
                                }
                                $Pass1 = 0;
                                foreach ($RsQ as $rs) {
                                    $Mes = explode("-", $rs["Fecha"]);
                                    $Mes = $Mes[1];
                                    $DiaG = $Mes[2];
                                    if ($MesAnterior <> $Mes && $Pass1 > 0) {
                                        $numeroMes = date("m", strtotime($FechaI));
                                        $numeroAnio = date("Y", strtotime($FechaI));
                                        $SqlInicial = "SELECT mesNo,mes,importe_deuda,fecha_analisis,cli.alias FROM cxc_mensual LEFT JOIN cli ON cli.id=cxc_mensual.id_cli
                                    WHERE mesNo='$Mes' AND anio = '$numeroAnio';";
                                        $CxcInicial = utils\IConnection::getRowsFromQuery($SqlInicial);
                                        foreach ($CxcInicial as $CxcI) {
                                            utils\HTTPUtils::setSessionValue($CxcI["alias"] . $Mes, $CxcI["importe_deuda"]);
                                        }
                                        ?>
                                        <tr style="font-weight: bold;font-size: 13px;">
                                            <td style="text-align: right;">Totales : </td>
                                            <?php
                                            $e = 1;
                                            $Tt = 0;
                                            foreach ($BancosExist as $Be) {
                                                ?>
                                                <td style="text-align: right;padding-right: 5px;"><?= number_format($GtTTurno["Bancos" . $e], 2) ?></td>
                                                <?php
                                                $e++;
                                                $Tt += $rs[$r];
                                            }
                                            ?>
                                            <td style="text-align: right;padding-right: 5px;"><?= number_format($GtotalBancos, 2) ?></td>
                                            <?php
                                            $e = 1;
                                            $Tt = 0;
                                            foreach ($MonederosExist as $Me) {
                                                ?>
                                                <td style="text-align: right;padding-right: 5px;"><?= number_format($GtTTurno["Monedero" . $e], 2) ?></td>
                                                <?php
                                                $e++;
                                            }
                                            ?>
                                            <td style="text-align: right;padding-right: 5px;"><?= number_format($TotalesMonederosG, 2) ?></td>
                                            <td style="text-align: right;padding-right: 5px;"><?= number_format($GtTpago_credito, 2) ?></td>
                                            <td style="text-align: right;padding-right: 5px;"><?= number_format($GttCreditoImp, 2) ?></td>
                                            <td style="text-align: right;padding-right: 5px;"><?= number_format($GttPagoPrep, 2) ?></td>
                                            <td style="text-align: right;padding-right: 5px;"><?= number_format($GTtPrepagoImp, 2) ?></td>
                                            <td style="text-align: right;padding-right: 5px;"><?= number_format($GTTotalesPago, 2) ?></td>
                                            <td style="text-align: right;padding-right: 5px;"><?= number_format($GTTotalesConsumos, 2) ?></td>
                                            <?php
                                            $e = 1;
                                            foreach ($TurnosActivs as $Ta) {
                                                ?>
                                                <td style="text-align: right;padding-right: 5px;"><?= number_format($GtTTurno["Turno" . $e], 2) ?></td>
                                                <?php
                                                $GtTTurno["Turno" . $e] = 0;
                                                $e++;
                                            }
                                            ?>
                                            <td style="text-align: right;padding-right: 5px;"><?= number_format($GTEfect, 2) ?></td>
                                            <?php
                                            $e = 1;
                                            foreach ($TurnosActivs as $Ta) {
                                                ?>
                                                <td>
                                                    <?= number_format($TotalEgr["Egresos" . $e] > 0 ? $TotalEgr["Egresos" . $e] : 0, 2) ?>    
                                                </td>
                                                <?php
                                                $TotalEgr["Egresos" . $e] = 0;
                                                $e++;
                                            }
                                            ?>
                                            <td style="text-align: right;padding-right: 5px;"><?= number_format($GGTotal, 2) ?></td>
                                        </tr>
                                        <tr style="font-weight: bold;">
                                            <td style="text-align: right;">Pagos : </td>
                                            <?php
                                            $e = 1;
                                            $tPagos = 0;
                                            foreach ($BancosExist as $Be) {
                                                ?>
                                                <td style="text-align: right;padding-right: 5px;"><?= number_format($GtTTurno["BancosPagos" . $e], 2) ?></td>
                                                <?php
                                                $e++;
                                                $tPagos += $GtTTurno["BancosPagos" . $e];
                                            }
                                            ?>
                                            <td style="text-align: right;padding-right: 5px;"><?= number_format($tPagos, 2) ?></td>
                                            <?php
                                            $e = 1;
                                            $tPagos = 0;
                                            foreach ($MonederosExist as $Me) {
                                                ?>
                                                <td style="text-align: right;padding-right: 5px;"><?= number_format($GtTTurno["MonederoPagos" . $e], 2) ?></td>
                                                <?php
                                                $e++;
                                                $tPagos += $GtTTurno["MonederoPagos" . $e];
                                            }
                                            ?>
                                            <td style="text-align: right;padding-right: 5px;" ><?= number_format($tPagos, 2) ?></td>
                                            <td style="text-align: right;padding-right: 5px;" colspan="7"></td>
                                            <?php
                                            $e = 1;
                                            foreach ($TurnosActivs as $Ta) {
                                                ?>
                                                <td style="text-align: right;padding-right: 5px;"></td>
                                                <?php
                                                $e++;
                                            }
                                            ?>
                                            <td style="text-align: right;padding-right: 5px;"></td>
                                            <?php
                                            $e = 1;
                                            foreach ($TurnosActivs as $Ta) {
                                                ?>
                                                <td></td>
                                                <?php
                                                $e++;
                                            }
                                            ?>
                                            <td style="text-align: right;padding-right: 5px;">></td>
                                        </tr>
                                        <tr style="font-weight: bold;">
                                            <td style="text-align: right;">Gran total : </td>
                                            <?php
                                            $e = 1;
                                            $Gtotal = 0;
                                            foreach ($BancosExist as $Be) {
                                                ?>
                                                <td style="text-align: right;padding-right: 5px;"><?= number_format($GtTTurno["Bancos" . $e] - $GtTTurno["BancosPagos" . $e], 2) ?></td>
                                                <?php
                                                $Gtotal += $GtTTurno["Bancos" . $e] - $GtTTurno["BancosPagos" . $e];
                                                $e++;
                                            }
                                            ?>
                                            <td style="text-align: right;padding-right: 5px;"><?= number_format($Gtotal, 2) ?></td>
                                            <?php
                                            $e = 1;
                                            $Gtotal = 0;
                                            foreach ($MonederosExist as $Me) {
                                                ?>
                                                <td style="text-align: right;padding-right: 5px;"><?= number_format($GtTTurno["Monedero" . $e] - $GtTTurno["MonederoPagos" . $e], 2) ?></td>
                                                <?php
                                                $Gtotal += $GtTTurno["Monedero" . $e] - $GtTTurno["MonederoPagos" . $e];
                                                $GtTTurno["Monedero" . $e] = 0;
                                                $e++;
                                            }
                                            ?>
                                            <td style="text-align: right;padding-right: 5px;"><?= number_format($Gtotal, 2) ?></td>
                                            <td style="text-align: right;padding-right: 5px;" colspan="7"></td>
                                            <?php
                                            $e = 1;
                                            foreach ($TurnosActivs as $Ta) {
                                                ?>
                                                <td style="text-align: right;padding-right: 5px;"></td>
                                                <?php
                                                $e++;
                                            }
                                            ?>
                                            <td style="text-align: right;padding-right: 5px;"></td>
                                            <?php
                                            $e = 1;
                                            foreach ($TurnosActivs as $Ta) {
                                                ?>
                                                <td>
                                                </td>
                                                <?php
                                                $e++;
                                            }
                                            ?>
                                            <td style="text-align: right;padding-right: 5px;"></td>
                                        </tr>
                                        <?php
                                        $GGTotal = 0;
                                        $GtTpago_credito = $GttCreditoImp = $GttPagoPrep = $GTtPrepagoImp = $GTTotalesPago = $GTTotalesConsumos = $GTEfect = $GGTotal = 0;
                                        ?>
                                        <tr style="background-color: #A2D9CE;font-size: 12px;font-weight: bold;">
                                            <td style="min-width:  80px;">Edo. Incial</td>
                                            <?php
                                            $e = 1;
                                            $GttIniB = 0;
                                            foreach ($BancosExist as $Be) {
                                                ?>
                                                <td style="text-align: right;"><?= number_format(utils\HTTPUtils::getSessionValue($Be . $Mes) > 0 ? utils\HTTPUtils::getSessionValue($Be . $Mes) : 0, 2) ?></td>
                                                <?php
                                                $GttIniB += $GtTTurno["Bancos" . $e] = utils\HTTPUtils::getSessionValue($Be . $Mes) > 0 ? utils\HTTPUtils::getSessionValue($Be . $Mes) : 0;
                                                $e++;
                                            }
                                            $GtotalBancos = $GttIniB;
                                            ?>
                                            <td style="text-align: right;"><?= number_format($GttIniB, 2) ?></td>
                                            <?php
                                            $GttIniM = 0;
                                            $e = 1;
                                            foreach ($MonederosExist as $Me) {
                                                $GtTTurno["Monedero" . $e] = 0;
                                                ?>
                                                <td style="text-align: right;"><?= number_format(utils\HTTPUtils::getSessionValue($Me . $Mes) > 0 ? utils\HTTPUtils::getSessionValue($Me . $Mes) : 0, 2) ?></td>
                                                <?php
                                                $GttIniM += $GtTTurno["Monedero" . $e] += utils\HTTPUtils::getSessionValue($Me . $Mes) > 0 ? utils\HTTPUtils::getSessionValue($Me . $Mes) : 0;
                                                $resultados[$Me] = number_format(utils\HTTPUtils::getSessionValue($Me . $Mes) > 0 ? utils\HTTPUtils::getSessionValue($Me . $Mes) : 0, 2);
                                            }
                                            $TotalesMonederosG = $GttIniM;
                                            ?>
                                            <td style="text-align: right;"><?= number_format($GttIniM, 2) ?></td>
                                            <td colspan="6"></td>
                                            <?php
                                            foreach ($TurnosActivs as $Ta) {
                                                ?>
                                                <td></td>
                                                <?php
                                            }
                                            ?>
                                            <td></td>
                                            <?php
                                            foreach ($TurnosActivs as $Ta) {
                                                ?>
                                                <td></td>
                                                <?php
                                            }
                                            ?>
                                            <td></td>
                                        </tr>
                                        <?php
                                    }

                                    foreach ($BancosExist as $Be) {
                                        $resultados[$Be] = 0;
                                    }
                                    foreach ($MonederosExist as $Me) {
                                        $resultados[$Me] = 0;
                                    }
                                    $PagosSql = "SELECT cli.alias,(importe + montonoreconocido) importe,fecha_ini,fecha_fin 
                                        FROM pagos 
                                        LEFT JOIN cli ON cli.id=pagos.cliente 
                                        WHERE cli.tipodepago IN ('Tarjeta','Monedero') AND 
                                      ('" . $rs["Fecha"] . "' BETWEEN fecha_ini AND fecha_fin || '" . $rs["Fecha"] . "' BETWEEN fecha_ini AND fecha_fin);";
                                    $RsP = utils\IConnection::getRowsFromQuery($PagosSql);
                                    $resultados[$rsp["alias"]] = 0;
                                    foreach ($RsP as $rsp) {
                                        $resultados[$rsp["alias"]] = $rsp["importe"];
                                    }
                                    ?>
                                    <tr>
                                        <td><?= $rs["Fecha"] ?></td>
                                        <?php
                                        $e = 1;
                                        $Tt = 0;
                                        foreach ($BancosExist as $Be) {
                                            $r = "b" . $e;
                                            if ($resultados[$Be] > 0) {
                                                $rst = "background-color:#ABEBC6;";
                                                if (!in_array($resultados[$Be], $miArrayBancos)) {
                                                    $miArrayBancos[] = $resultados[$Be];
                                                    $GtTTurno["BancosPagos" . $e] += $resultados[$Be];
                                                }
                                            } else {
                                                $rst = "";
                                            }
                                            $titleg = $resultados[$Be] > 0 ? "title='Pago por $" . $resultados[$Be] . "'" : "";
                                            ?>
                                            <td style="text-align: right;padding-right: 5px;<?= $rst ?>" <?= $titleg ?>><?= number_format($rs[$r], 2) ?></td>
                                            <?php
                                            $GtTTurno["Bancos" . $e] += $rs[$r];
                                            $e++;
                                            $Tt += $rs[$r];
                                        }
                                        $GtotalBancos += $Tt;
                                        ?>
                                        <td style="text-align: right;padding-right: 5px;"><?= number_format($Tt, 2) ?></td>
                                        <?php
                                        $e = 1;
                                        $Tt = 0;
                                        foreach ($MonederosExist as $Me) {
                                            $r = "m" . $e;
                                            if ($resultados[$Me] > 0) {
                                                $rst = "background-color:#ABEBC6;";
                                                if (!in_array($resultados[$Me], $miArrayMonederos)) {
                                                    $miArrayMonederos[] = $resultados[$Me];
                                                    $GtTTurno["MonederoPagos" . $e] += $resultados[$Me];
                                                }
                                            } else {
                                                $rst = "";
                                            };
                                            $titleg = $resultados[$Me] > 0 ? " title='Pago por $" . $resultados[$Me] . "'" : "";
                                            ?>
                                            <td style="text-align: right;padding-right: 5px;<?= $rst ?>" <?= $titleg ?>><?= number_format($rs[$r], 2) ?></td>
                                            <?php
                                            $GtTTurno["Monedero" . $e] += $rs[$r];
                                            $e++;
                                            $Tt += $rs[$r];
                                        }
                                        $TotalesPago = 0;
                                        $TotalesConsumos = $rs["creditoImp"] + $rs["prepagoImp"];
                                        $TotalesPago = $rs["pago_credito"] + $rs["pago_prepago"];
                                        $TotalesMonederosG += $Tt;
                                        ?>
                                        <td style="text-align: right;padding-right: 5px;"><?= number_format($Tt, 2) ?></td>
                                        <td style="text-align: right;padding-right: 5px;"><?= number_format($rs["pago_credito"], 2) ?></td>
                                        <td style="text-align: right;padding-right: 5px;"><?= number_format($rs["creditoImp"], 2) ?></td>
                                        <td style="text-align: right;padding-right: 5px;"><?= number_format($rs["pago_prepago"], 2) ?></td>
                                        <td style="text-align: right;padding-right: 5px;"><?= number_format($rs["prepagoImp"], 2) ?></td>
                                        <td style="text-align: right;padding-right: 5px;"><?= number_format($TotalesPago, 2) ?></td>
                                        <td style="text-align: right;padding-right: 5px;"><?= number_format($TotalesConsumos, 2) ?></td>
                                        <?php
                                        $e = 1;
                                        $GrandesTotales = 0;
                                        foreach ($TurnosActivs as $Ta) {
                                            ?>
                                            <td style="text-align: right;padding-right: 5px;"><?= number_format($rs["turno" . $e], 2) ?></td>
                                            <?php
                                            $GtTTurno["Turno" . $e] += $rs["turno" . $e];
                                            $GrandesTotales += $rs["turno" . $e];
                                            $e++;
                                        }
                                        $TtalPagos = $rs["efectivo"] + $rs["transferencia"];
                                        ?>

                                        <td style="text-align: right;padding-right: 5px;"><?= number_format($rs["efectivoG"], 2) ?></td>
                                        <?php
                                        $GrandesTotales += $rs["efectivoG"];
                                        $e = 1;
                                        foreach ($TurnosActivs as $Ta) {
                                            ?>
                                            <td style="text-align: right;padding-right: 5px;"><?= number_format($rs["egr" . $e], 2) ?></td>
                                            <?php
                                            $TotalEgr["Egresos" . $e] += $rs["egr" . $e];
                                            $GrandesTotales += $rs["egr" . $e];
                                            $e++;
                                        }
                                        ?>
                                        <td style="text-align: right;padding-right: 5px;"><?= number_format($GrandesTotales, 2) ?></td>
                                    </tr>
                                    <?php
                                    $GGTotal += $GrandesTotales;
                                    $GtTpago_credito += $rs["pago_credito"];
                                    $GttCreditoImp += $rs["creditoImp"];
                                    $GttPagoPrep += $rs["pago_prepago"];
                                    $GTtPrepagoImp += $rs["prepagoImp"];
                                    $GTTotalesPago += $TotalesPago;
                                    $GTTotalesConsumos += $TotalesConsumos;
                                    $GTEfect += $rs["efectivoG"];
                                    $GTTransfer += $rs["transferencia"];
                                    $GTCheque += $rs["cheque"];
                                    $GTtalPagos += $TtalPagos;
                                    $MesAnterior = $Mes;
                                    $Pass1++;

                                    $fecha = $rs["Fecha"];
                                    $date = new DateTime($fecha);
                                    $ultimo_dia_del_mes = $date->format('Y-m-t');
                                    if ($rs["Fecha"] === $ultimo_dia_del_mes && $request->getAttribute("FinMes") == 1) {
                                        ?>
                                        <tr style="color: white;font-size: 11px;font-weight: bold;">
                                            <td style="background-color: #F5B041;text-align: center;" colspan="100%">Inicializar saldos del siguiente mes</td>
                                        </tr>
                                        <tr style="font-size: 8px;font-weight: bold;">
                                            <td style="min-width:  80px;">Fecha</td>
                                            <?php
                                            $e = 1;
                                            $fecha = new DateTime($FechaF);
                                            $fecha->modify('+1 month');
                                            $nueva_fecha = $fecha->format('Y-m-d');
                                            $numeroMes = date("m", strtotime($nueva_fecha));
                                            $numeroMesTxt = date("F", strtotime($nueva_fecha));
                                            foreach ($BancosExist as $Be) {
                                                ?>
                                                <td data-mes='<?= $numeroMesTxt ?>' 
                                                    data-mesno='<?= $numeroMes ?>' 
                                                    data-anio='<?= $numeroAnio ?>'  
                                                    data-idcli='<?= $BancosExistId["Bnc" . $e] ?>'
                                                    data-name='<?= $Be ?>' class='tiposCliente'><?= $Be ?></td>
                                                    <?php
                                                    $e++;
                                                }
                                                $e = 1;
                                                ?>
                                            <td style="font-size: 12px;"></td>
                                            <?php
                                            $e = 1;
                                            foreach ($MonederosExist as $Me) {
                                                ?>
                                                <td data-mes='<?= $numeroMesTxt ?>'  
                                                    data-idcli='<?= $MonederosExistId[$e] ?>' 
                                                    data-mesno='<?= $numeroMes ?>' 
                                                    data-anio='<?= $numeroAnio ?>'  
                                                    data-idcli='<?= $MonederosExistId["Bnc" . $e] ?>' 
                                                    data-name='<?= $Me ?>' 
                                                    class='tiposCliente'><?= $Me ?></td>
                                                    <?php
                                                    $e++;
                                                }
                                                $e = 1;
                                                ?>
                                            <td style="font-size: 12px;"></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <?php
                                            foreach ($TurnosActivs as $Ta) {
                                                ?>
                                                <td></td>
                                                <?php
                                            }
                                            ?>
                                            <td></td>
                                            <?php
                                            foreach ($TurnosActivs as $Ta) {
                                                ?>
                                                <td></td>
                                                <?php
                                            }
                                            ?>
                                            <td  style="font-size: 12px;"></td>
                                        </tr>

                                        <tr style="background-color: #A2D9CE;font-size: 12px;font-weight: bold;">
                                            <td style="min-width:  80px;"></td>
                                            <?php
                                            $e = 1;
                                            $GttIniB = 0;
                                            foreach ($BancosExist as $Be) {
                                                ?>
                                                <td style="text-align: right;"><?= number_format(utils\HTTPUtils::getSessionValue($Be . "Fin") > 0 ? utils\HTTPUtils::getSessionValue($Be . "Fin") : 0, 2) ?></td>
                                                <?php
                                                $e++;
                                            }
//                                            $GtotalBancos = $GttIniB;
                                            ?>
                                            <td style="text-align: right;"></td>
                                            <?php
                                            $GttIniM = 0;
                                            $e = 1;
                                            foreach ($MonederosExist as $Me) {
                                                ?>
                                                <td style="text-align: right;"><?= number_format(utils\HTTPUtils::getSessionValue($Me . "Fin") > 0 ? utils\HTTPUtils::getSessionValue($Me . "Fin") : 0, 2) ?></td>
                                                <?php
                                            }
//                                            $TotalesMonederosG = $GttIniM;
                                            ?>
                                            <td style="text-align: right;"></td>
                                            <td colspan="6"></td>
                                            <?php
                                            foreach ($TurnosActivs as $Ta) {
                                                ?>
                                                <td></td>
                                                <?php
                                            }
                                            ?>
                                            <td></td>
                                            <?php
                                            foreach ($TurnosActivs as $Ta) {
                                                ?>
                                                <td></td>
                                                <?php
                                            }
                                            ?>
                                            <td></td>
                                        </tr>
                                        <?php
                                    }
                                }
                                ?>
                            </tbody>
                            <tfoot>
                                <tr style="height: 14px;">
                                    <td>Totales : </td>
                                    <?php
                                    $e = 1;
                                    $Tt = 0;
                                    foreach ($BancosExist as $Be) {
                                        ?>
                                        <td style="text-align: right;padding-right: 5px;"><?= number_format($GtTTurno["Bancos" . $e], 2) ?></td>
                                        <?php
                                        $e++;
                                        $Tt += $rs[$r];
                                    }
                                    ?>
                                    <td style="text-align: right;padding-right: 5px;"><?= number_format($GtotalBancos, 2) ?></td>
                                    <?php
                                    $e = 1;
                                    $Tt = 0;
                                    foreach ($MonederosExist as $Me) {
                                        ?>
                                        <td style="text-align: right;padding-right: 5px;"><?= number_format($GtTTurno["Monedero" . $e], 2) ?></td>
                                        <?php
                                        $e++;
                                    }
                                    ?>
                                    <td style="text-align: right;padding-right: 5px;"><?= number_format($TotalesMonederosG, 2) ?></td>
                                    <td style="text-align: right;padding-right: 5px;"><?= number_format($GtTpago_credito, 2) ?></td>
                                    <td style="text-align: right;padding-right: 5px;"><?= number_format($GttCreditoImp, 2) ?></td>
                                    <td style="text-align: right;padding-right: 5px;"><?= number_format($GttPagoPrep, 2) ?></td>
                                    <td style="text-align: right;padding-right: 5px;"><?= number_format($GTtPrepagoImp, 2) ?></td>
                                    <td style="text-align: right;padding-right: 5px;"><?= number_format($GTTotalesPago, 2) ?></td>
                                    <td style="text-align: right;padding-right: 5px;"><?= number_format($GTTotalesConsumos, 2) ?></td>
                                    <?php
                                    $e = 1;
                                    foreach ($TurnosActivs as $Ta) {
                                        ?>
                                        <td style="text-align: right;padding-right: 5px;"><?= number_format($GtTTurno["Turno" . $e], 2) ?></td>
                                        <?php
                                        $e++;
                                    }
                                    ?>
                                    <td style="text-align: right;padding-right: 5px;"><?= number_format($GTEfect, 2) ?></td>
                                    <?php
                                    $e = 1;
                                    foreach ($TurnosActivs as $Ta) {
                                        ?>
                                        <td>
                                            <?= number_format($TotalEgr["Egresos" . $e] > 0 ? $TotalEgr["Egresos" . $e] : 0, 2) ?>    
                                        </td>
                                        <?php
                                        $e++;
                                    }
                                    ?>
                                    <td style="text-align: right;padding-right: 5px;"><?= number_format($GGTotal, 2) ?></td>
                                </tr>
                                <tr  style="font-weight: bold;height: 14px;">
                                    <td style="text-align: right;">Pagos : </td>
                                    <?php
                                    $e = 1;
                                    $tPagos = 0;
                                    foreach ($BancosExist as $Be) {
                                        ?>
                                        <td style="text-align: right;padding-right: 5px;"><?= number_format($GtTTurno["BancosPagos" . $e], 2) ?></td>
                                        <?php
                                        $e++;
                                        $tPagos += $GtTTurno["BancosPagos" . $e];
                                    }
                                    ?>
                                    <td style="text-align: right;padding-right: 5px;"><?= number_format($tPagos, 2) ?></td>
                                    <?php
                                    $e = 1;
                                    $tPagos = 0;
                                    foreach ($MonederosExist as $Me) {
                                        ?>
                                        <td style="text-align: right;padding-right: 5px;"><?= number_format($GtTTurno["MonederoPagos" . $e], 2) ?></td>
                                        <?php
                                        $e++;
                                        $tPagos += $GtTTurno["MonederoPagos" . $e];
                                    }
                                    ?>
                                    <td style="text-align: right;padding-right: 5px;" ><?= number_format($tPagos, 2) ?></td>
                                    <td style="text-align: right;padding-right: 5px;" colspan="7"></td>
                                    <?php
                                    $e = 1;
                                    foreach ($TurnosActivs as $Ta) {
                                        ?>
                                        <td style="text-align: right;padding-right: 5px;"></td>
                                        <?php
                                        $e++;
                                    }
                                    ?>
                                    <td style="text-align: right;padding-right: 5px;"></td>
                                    <?php
                                    $e = 1;
                                    foreach ($TurnosActivs as $Ta) {
                                        ?>
                                        <td></td>
                                        <?php
                                        $e++;
                                    }
                                    ?>
                                    <td style="text-align: right;padding-right: 5px;">></td>
                                </tr>
                                <tr style="font-weight: bold;height: 14px;">
                                    <td style="text-align: right;">Gran total : </td>
                                    <?php
                                    $e = 1;
                                    $Gtotal = 0;
                                    foreach ($BancosExist as $Be) {
                                        ?>
                                        <td style="text-align: right;padding-right: 5px;"><?= number_format($GtTTurno["Bancos" . $e] - $GtTTurno["BancosPagos" . $e], 2) ?></td>
                                        <?php
                                        $Gtotal += $GtTTurno["Bancos" . $e] - $GtTTurno["BancosPagos" . $e];
                                        $e++;
                                    }
                                    ?>
                                    <td style="text-align: right;padding-right: 5px;"><?= number_format($Gtotal, 2) ?></td>
                                    <?php
                                    $e = 1;
                                    $Gtotal = 0;
                                    foreach ($MonederosExist as $Me) {
                                        ?>
                                        <td style="text-align: right;padding-right: 5px;"><?= number_format($GtTTurno["Monedero" . $e] - $GtTTurno["MonederoPagos" . $e], 2) ?></td>
                                        <?php
                                        $Gtotal += $GtTTurno["Monedero" . $e] - $GtTTurno["MonederoPagos" . $e];
                                        $e++;
                                    }
                                    ?>
                                    <td style="text-align: right;padding-right: 5px;"><?= number_format($Gtotal, 2) ?></td>
                                    <td style="text-align: right;padding-right: 5px;" colspan="7"></td>
                                    <?php
                                    $e = 1;
                                    foreach ($TurnosActivs as $Ta) {
                                        ?>
                                        <td style="text-align: right;padding-right: 5px;"></td>
                                        <?php
                                        $e++;
                                    }
                                    ?>
                                    <td style="text-align: right;padding-right: 5px;"></td>
                                    <?php
                                    $e = 1;
                                    foreach ($TurnosActivs as $Ta) {
                                        ?>
                                        <td>
                                        </td>
                                        <?php
                                        $e++;
                                    }
                                    ?>
                                    <td style="text-align: right;padding-right: 5px;"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
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
                                Ingresar finales del mes <input type="checkbox" name="FinMes" id="FinMes" value="1">
                            </td>
                            <td style="text-align: right;padding-right: 25px;">
                                <span><input type="submit" name="Boton" value="Enviar" id="Enviar"></span>
                                <?php
                                if ($usuarioSesion->getTeam() !== "Operador") {
                                    ?>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <!--<span class="ButtonExcel"><a href="report_excel_reports.php?<?= http_build_query($data) ?>"><i class="icon fa fa-lg fa-bold fa-file-excel-o" aria-hidden="true"></i></a></span>-->
                                    <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                                    <span><button onclick="ExportToExcel('xlsx')"><i class="icon fa fa-lg fa-bold fa-file-excel-o" aria-hidden="true">v2</i></button></span>
                                    <?php
                                }
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
            <?php topePagina(); ?>
        </div>
    </body>
</html>
