<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";
$Titulo = "Envio de efectivo del $FechaI al $FechaF ";
$Sql = "SELECT ee.id,bancos.banco,ee.descripcion,ee.importe,ee.fecha_creacion ,ee.fecha_envio fecha,ee.status FROM env_efectivo ee "
        . "LEFT JOIN bancos ON ee.id_banco=bancos.id "
        . "WHERE DATE(fecha_envio) BETWEEN DATE('$FechaI') AND DATE('$FechaF');";
$registros = utils\IConnection::getRowsFromQuery($Sql);
$Sql2 = "SELECT id_corte,egr.importe Ingreado, SUM(monto) enviado FROM omicrom.env_efectivo ee
            LEFT JOIN env_efectivod eed ON ee.id=eed.id_ee  
            LEFT JOIN (SELECT corte,SUM(importe) importe FROM egr WHERE corte > 0 AND tm = 'C' GROUP BY corte) egr
            ON id_corte=egr.corte
            WHERE DATE(fecha_envio) BETWEEN DATE('$FechaI') AND DATE('$FechaF') 
            AND eed.id != 0 group by id_corte
            ORDER BY ee.id ASC;";
$registros2 = utils\IConnection::getRowsFromQuery($Sql2);
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

        <script type="text/javascript" src="https://unpkg.com/xlsx@0.15.1/dist/xlsx.full.min.js"></script>
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
                $("#Detallado").val("<?= $Detallado ?>");
                $("#Desglose").val("<?= $Desglose ?>");
                $("#Turno");
                comboTurno();

                $("#Detallado").change(function () {
                    comboTurno();
                });

                $("#Desglose").change(function () {
                    comboTurno();
                });

                $("#FechaI").focus();

                function comboTurno() {
                    if ($("#Detallado").val() === "Si" && $("#Desglose").val() === "Cortes") {
                        $("#Turno").val("<?= $Turno ?>");
                        $("#showTurno").show();
                    } else {
                        $("#showTurno").hide();
                    }
                }
                $(".fa-list").click(function () {
                    $(".AgregadoAjax").hide();
                    var thisb = this;
                    console.log(thisb);
                    jQuery.ajax({
                        type: "POST",
                        url: "getByAjax.php",
                        dataType: "json",
                        cache: false,
                        data: {"Op": "ObtenDetalleEnvio", "IdBusca": this.dataset.envio},
                        success: function (data) {
                            console.log(data);
                            var e = 0;
                            $.each(data.Array, function (ind, elem) {
                                var color = e % 2 == 0 ? "#EAFAF1" : "#FFF";
                                $(thisb).parent().parent().after("<tr class='AgregadoAjax' style='background-color: " + color + ";'><td style='border-left:1px solid black;'>" +
                                        elem.Corte + "</td><td style='text-align:right;'>" +
                                        elem.Enviado + "</td><td style='text-align:right;border-right:1px solid black;'>" +
                                        elem.FechaCorte + "</td></tr>");
                                e++;
                            });
                            $(thisb).parent().parent().after("<tr class='AgregadoAjax' style='background-color: #A9DFBF'>\n\
                    <td style='border-top:1px solid black;border-left:1px solid black;'>Corte</td><td style='border-top:1px solid black;'>Importe Enviado</td>\n\
                    <td style='border-top:1px solid black;border-right:1px solid black;'>Fecha</td></tr>");
                        }
                    });
                });
            });
            function ExportToExcel(type, fn, dl) {
                var elt = document.getElementById('tbl_exporttable_to_xls');
                var wb = XLSX.utils.table_to_book(elt, {sheet: "sheet1"});
                return dl ?
                        XLSX.write(wb, {bookType: type, bookSST: true, type: 'base64'}) :
                        XLSX.writeFile(wb, fn || ('ReporteGerencia.' + (type || 'xlsx')));
            }
        </script>
    </head>

    <body>
        <div id="tbl_exporttable_to_xls">
            <div id="container">
                <?php nuevoEncabezado($Titulo); ?>
                <div id="Reportes" style="min-height: 200px;"> 


                    <table aria-hidden="true">
                        <thead>
                            <tr>
                                <td>Id</td>
                                <td>Banco</td>
                                <td>Descripcion</td>
                                <td>Importe</td>
                                <td>Fecha Creacion</td>
                                <td>Fecha Envio</td>
                                <td>Status</td>
                                <td></td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $Vts = $impAce = 0;
                            foreach ($registros as $rg) {
                                ?>
                                <tr>
                                    <td><?= $rg["id"] ?></td>
                                    <td><?= $rg["banco"] ?></td>
                                    <td><?= $rg["descripcion"] ?></td>
                                    <td style="text-align: right"><?= number_format($rg["importe"], 2) ?></td>
                                    <td><?= $rg["fecha_creacion"] ?></td>
                                    <td><?= $rg["fecha"] ?></td>
                                    <td><?= $rg["status"] ?></td>
                                    <td ><em class="fa-solid fa-list" data-envio="<?= $rg["id"] ?>"></em></td>
                                </tr>
                                <?php
                                $TtD += $rg["importe"];
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3"> Total -></td>
                                <td><?= number_format($TtD, 2) ?></td>
                                <td colspan="4"></td>
                            </tr>
                        </tfoot>
                    </table>
                    <table aria-hidden="true" style="width: 40%;margin-left: 30%;">
                        <thead>
                            <tr>
                                <td>Corte</td>
                                <td>Ingresado</td>
                                <td>Enviado</td>
                                <td>Restante</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $Vts = $impAce = 0;
                            foreach ($registros2 as $rg) {
                                ?>
                                <tr>
                                    <td style="text-align: center;"><?= $rg["id_corte"] ?></td>
                                    <td style="text-align: right;"><?= number_format($rg["Ingreado"], 2) ?></td>
                                    <td style="text-align: right;"><?= number_format($rg["enviado"], 2) ?></td>
                                    <td style="text-align: right;"><?= number_format($rg["Ingreado"] - $rg["enviado"], 2) ?></td>
                                </tr>
                                <?php
                                $ImpT += $rg["Ingreado"];
                                $EnvT += $rg["enviado"];
                                $Restante += $rg["Ingreado"] - $rg["enviado"];
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>Totales -></td>
                                <td><?= number_format($ImpT, 2) ?></td>
                                <td><?= number_format($EnvT, 2) ?></td>
                                <td><?= number_format($Restante, 2) ?></td>
                            </tr>
                        </tfoot>
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
                            <td id="showTurno">
                                <table style="width: 100%" aria-hidden="true">
                                    <tr>
                                        <td>Por Turno:</td>
                                        <td style="text-align: left;">
                                            <select id="Turno" name="Turno">
                                                <option value="No">No</option>
                                                <option value="Si">Si</option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <span><input type="submit" name="Boton" value="Enviar"></span>
                                <?php
                                if ($usuarioSesion->getTeam() !== "Operador") {
                                    ?>                                                                                                                                                                       <!--<span class="ButtonExcel"><a href="report_excel_reports.php?<?= http_build_query($data) ?>"><i class="icon fa fa-lg fa-bold fa-file-excel-o" aria-hidden="true"></i></a></span>-->
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
