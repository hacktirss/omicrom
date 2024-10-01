<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ('data/RmDAO.php');

use com\softcoatl\utils as utils;

$Contable = true;

require "./services/ReportesVentasService.php";
$SqlSaldoT = "SELECT * FROM (
        SELECT p.id id,p.fecha ,cli.nombre,'Abono' tm,ul.importe importeActual,ABS(ul.importeDelPago) importe,u.codigo,cli.id idCli,ul.usr 
        FROM unidades_log ul LEFT JOIN unidades u ON ul.idUnidad=u.id LEFT JOIN cli ON u.cliente=cli.id LEFT JOIN pagos p 
        ON p.id=ul.noPago
        WHERE u.periodo='B' AND importeDelPago > 0 AND u.estado='a'
        UNION ALL
        SELECT r.id id,r.fin_venta fecha,cli.nombre,'Venta' tm,ul.importe importeActual,ABS(ul.importeDelPago) importe,u.codigo, cli.id idCli,ul.usr
        FROM unidades_log ul LEFT JOIN unidades u ON ul.idUnidad=u.id LEFT JOIN cli ON u.cliente=cli.id LEFT JOIN rm r 
        ON r.id=ul.noPago 
        WHERE u.periodo='B' AND importeDelPago < 0 AND u.estado='a') edoCuenta WHERE fecha BETWEEN '$FechaI 00:00:01' AND '$FechaF 23:59:59'
        ORDER BY idCli,codigo,fecha;";
//echo $SqlSaldoT;
$Titulo = "Desglose de venta por tarjeta $Desglose del $FechaI al $FechaF";
$registrosCLI = utils\IConnection::getRowsFromQuery($SqlSaldoT);
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports.php'; ?> 
        <script type="text/javascript" src="https://unpkg.com/xlsx@0.15.1/dist/xlsx.full.min.js"></script>
        <title><?= $Gcia ?></title>
        <style>
            #Concentrado{
                width: 100%;
                border-collapse: separate;
                font-family: Arial, Helvetica, sans-serif;
                font-size: 12px;
                color: #55514e;
            }
            #Concentrado > thead > tr > td{
                height: 25px;
                background-color: white;
                border-bottom: solid 2px gray;
                font-weight: bold;
                text-align: center;
            }
            #Concentrado > thead > tr > td > a{
                text-decoration: none;
                color: #55514e;
                font-weight: bold;
            }
            #Concentrado > thead > tr.titulo > td{
                background-color: var(--GrisClaro);
                border-bottom: solid 2px white;
            }
            #Concentrado > tbody > tr > td{
                padding-left: 5px;
                padding-right: 5px;
                text-align: left;
            }
            #Concentrado > tbody > tr > td.overflow{
                max-width: 200px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            #Concentrado > tbody > tr > td.overflow:hover{
                overflow: visible;
                white-space: normal;
            }
            #Concentrado > tbody > tr:nth-child(odd) {
                background-color: var(--GrisClaro);
            }

            #Concentrado > tbody > tr:nth-child(even) {
                background-color: white;
            }

            #Concentrado > tbody > tr:nth-child(odd):hover {
                background-color: var(--VerdeHover);
            }

            #Concentrado > tbody > tr:nth-child(even):hover {
                background-color: var(--VerdeHover);
            }
            #Concentrado > tbody > tr.titulos > td{
                height: 25px;
                background-color: white;
                border-bottom: solid 2px gray;
                font-weight: bold;
                text-align: center;
            }
            #Concentrado > tbody > tr.subtotal > td{
                height: 25px;
                background-color: white;
                border-top: solid 2px gray;
                font-weight: bold;
                text-align: right;
                padding-bottom: 10px;
            }
            #Concentrado > tbody > tr.titulo > td{
                height: 25px;
                background-color: var(--GrisClaro);
                font-weight: bold;
                text-align: right;
                text-align: center;
            }
            #Concentrado > tbody > tr.subtitulo > td{
                height: 25px;
                background-color: white;
                font-weight: bold;
                text-align: right;
                text-align: center;
            }
            #Concentrado > tbody > tr > td.numero,.moneda{
                text-align: right;
            }
            #Concentrado > tbody > tr > td.remarcar{
                background-color: #F7FF7C;
            }
            #Concentrado > tbody > tr > td.moneda:before{
                content: "$ ";
            }
            #Concentrado > tfoot > tr > td{
                height: 25px;
                background-color: white;
                /*    border-top: solid 2px gray;*/
                font-weight: bold;
                text-align: right;
                padding-left: 5px;
                padding-right: 5px;
            }
            #Concentrado > tfoot > tr:first-child > td{
                border-top: solid 2px gray;
                padding-bottom: 10px;
            }
            #Concentrado > tfoot > tr > td.moneda:before{
                content: "$ ";
            }
        </style>
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
            });
            function ExportToExcel(type, fn, dl) {
                var elt = document.getElementById('tbl_exporttable_to_xls');
                var wb = XLSX.utils.table_to_book(elt, {sheet: "sheet1"});
                return dl ?
                        XLSX.write(wb, {bookType: type, bookSST: true, type: 'base64'}) :
                        XLSX.writeFile(wb, fn || ('ReporteGerencia.' + (type || 'xlsx')));
            }
            ;
        </script>
    </head>

    <body>
        <div id="container">
            <div id="tbl_exporttable_to_xls">
                <?php nuevoEncabezado($Titulo); ?>
                <div id="Reportes" style="min-height: 200px;"> 
                    <table style="width: 100%;" summary="Saldo de tarjetas">
                        <tr>
                            <th>
                                <h3><?= $Titulo ?></h3>
                            </th>
                        </tr>
                    </table>
                    <table id="Concentrado" summary="Detalle del saldo">
                        <tr><th colspan="7"></th></tr>
                        <tbody>
                            <?php
                            $NombreCli = "";
                            $Codigo = 0;
                            foreach ($registrosCLI as $rg) {
                                if ($NombreCli <> $rg["nombre"]) {
                                    ?>
                                    <tr>
                                        <td colspan="7" style="font-size: 15px;text-align: center;background-color: #566573;color: white;font-weight: bold;"><?= $rg["nombre"] ?></td>
                                    </tr>
                                    <tr style="background-color: #D5D8DC;font-weight: bold">
                                        <td style="text-align: center;font-size: 13px;">Tipo</td>
                                        <td style="text-align: center;font-size: 13px;">Identificador</td>
                                        <td style="text-align: center;font-size: 13px;">Fecha</td>
                                        <td style="text-align: center;font-size: 13px;">Codigo</td>
                                        <td style="text-align: center;font-size: 13px;">Importe</td>
                                        <td style="text-align: center;font-size: 13px;">Saldo Disponible</td>
                                        <td style="text-align: center;font-size: 13px;">Responsable</td>
                                    </tr>
                                    <?php
                                    $x = 1;
                                }
                                $Color = $rg["codigo"] <> $Codigo && $x == 0 ? " style='border-top:2px solid #566573;'" : "";
                                ?>
                                <tr >
                                    <td <?= $Color ?>><?= $rg["tm"] ?></td>
                                    <td class="numero" <?= $Color ?>><?= $rg["id"] ?></td>
                                    <td <?= $Color ?>><?= $rg["fecha"] ?></td>
                                    <td class="numero" <?= $Color ?>><?= $rg["codigo"] ?></td>
                                    <td class="numero" <?= $Color ?>>$ <?= number_format($rg["importe"], 2) ?></td>
                                    <td class="numero" <?= $Color ?>>$ <?= number_format($rg["importeActual"], 2) ?></td>
                                    <td <?= $Color ?>><?= ucwords(strtolower($rg["usr"])) ?></td>
                                </tr>

                                <?php
                                $NombreCli = $rg["nombre"];
                                $Codigo = $rg["codigo"];
                                $x = 0;
                            }
                            ?>
                        </tbody>
                    </table>
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
                            <td style="text-align: right">
                                <span><input type="submit" name="Boton" value="Enviar"></span>
                                <?php
                                if ($usuarioSesion->getTeam() !== "Operador") {
                                    ?>
                                    <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                                    <span><button onclick="ExportToExcel('xlsx')"><i class="icon fa fa-lg fa-bold fa-file-excel-o" aria-hidden="true"></i></button></span>
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
