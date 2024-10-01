<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("data/VentaAditivosDAO.php");

use com\softcoatl\utils as utils;

require "./services/ReportesResumenPuntos.php";

$registros = utils\IConnection::getRowsFromQuery($selectByDia);

$Id = 32; /* Número de en el orden de la tabla submenus */
$data = array("Nombre" => "Resumen mensual", "Reporte" => $Id, "Fecha" => $Fecha, "FechaF" => $FechaF,
    "Detallado" => $Detallado, "Desglose" => $Desglose,
    "Turno" => $Turno, "Textos" => "Subtotal", "Filtro" => "1");
$tipo_cliente = Array("Credito" => "Credito", "Contado" => "Contado", "Consignacion" => "Consignacion", "Monedero" => "Monederos",
    "Prepago" => "Prepago", "Puntos" => "Puntos", "Tarjeta" => "Tarjeta Bancaria", "Vales" => "Vales");
$Titulo = "Reporte de puntos por cliente";
$query = utils\IConnection::getRowsFromQuery($SelectPuntos);
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#FechaIni").val("<?= $FechaIni ?>");
                $("#FechaFin").val("<?= $FechaFin ?>");
            });
        </script>
    </head>

    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo); ?>
            <div id="Reportes" style="min-height: 200px;"> 
                <?php
                $PeriodosdePuntos = "SELECT * FROM periodo_puntos WHERE fecha_inicial < now()";
                $PPs = utils\IConnection::getRowsFromQuery($PeriodosdePuntos);
                $Tc = $PPs["tipo_concepto"] === "V" ? "volumen" : "importe";

                $Sql = "SELECT valor FROM variables_corporativo WHERE llave = 'PuntoPor';";
                $PuntosPor = utils\IConnection::execSql($Sql);
                foreach ($PPs as $pps) {
                    ?>
                    <table style="width: 100%;margin-bottom: 30px;" summary="Resumen por puntos">
                        <thead>
                            <tr>
                                <th style="width: 6%">Id. <?= $pps["id"] ?></th>
                                <th style="width: 40%"><?= $pps["descripcion"] ?></th>
                                <th colspan="3">
                                    <div style="display: inline-block;margin-right: 25px;">F. Inicial <?= $pps["fecha_inicial"] ?></div>
                                    <div style="display: inline-block;margin-right: 25px;">F. Final <?= $pps["fecha_culmina"] ?></div>
                                    <div style="display: inline-block;">Expira <?= $pps["fecha_final"] ?></div>
                                </th>
                            </tr>
                        </thead> 
                        <tbody>
                            <tr style="font-weight: bold;"  class="titulo">
                                <td>Id</td>
                                <td>Descripcion</td>
                                <td>Puntos Acumulados</td>
                                <td>Puntos Gastados</td>
                                <td>Total de puntos</td>
                            </tr>
                            <?php
                            $CalculaPuntos = "SELECT cli.id,cli.nombre, ROUND(sum(rm." . $Tc . "/(SELECT monto_promocion FROM periodo_puntos WHERE id= " . $pps["id"] . ")),0 )  puntos,IFNULL(Pts.smpts,0) puntosConsumidos "
                                    . "FROM rm LEFT JOIN cli ON cli.id = rm.cliente LEFT JOIN "
                                    . "(SELECT SUM(puntos) smpts,cliente,fecha FROM puntos WHERE "
                                    . "DATE(fecha) BETWEEN DATE ('" . $pps["fecha_inicial"] . "')  AND DATE ('" . $pps["fecha_final"] . "') "
                                    . " AND id_periodo = " . $pps["id"] . "  GROUP  BY cliente) Pts "
                                    . "ON cli.id = Pts.cliente "
                                    . "LEFT JOIN cia ON TRUE LEFT JOIN com ON rm.producto = com.clavei "
                                    . "WHERE DATE(rm.fecha_venta) BETWEEN DATE(DATE_FORMAT('" . $pps["fecha_inicial"] . "','%Y%m%d')) "
                                    . "AND DATE(DATE_FORMAT('" . $pps["fecha_culmina"] . "','%Y%m%d'))  AND Pts.smpts > 0 "
                                    . "GROUP BY cli.id ORDER BY cli.tipodepago;";

                            $CPs = utils\IConnection::getRowsFromQuery($CalculaPuntos);
                            foreach ($CPs as $cps) {
                                ?>
                                <tr style="background-color: <?= $clr ?>">
                                    <td><?= $cps["id"] ?></td>
                                    <td><?= $cps["nombre"] ?></td>
                                    <td><?= $cps["puntos"] ?></td>
                                    <td><?= $cps["puntosConsumidos"] ?></td>
                                    <td><?= $cps["puntos"] - $cps["puntosConsumidos"] ?></td>
                                </tr>
                                <?php
                            }
                            ?>

                        </tbody>
                    </table>
                    <?php
                }
                ?>
            </div>
        </div>
        <div id="footer">
            <form name="formActions" method="post" action="" id="form" class="oculto">
                <div id="Controles">
                    <table aria-hidden="true">
                        <tr style="height: 40px;">
                            <td style="width: 50%;">
                                <table aria-hidden="true">
                                    <tr>
                                        <td>Tipo de Cliente:</td>
                                        <td>
                                            <select name="Tipodepago" id="Tipodepago" class="clase-<?= $clase ?>">
                                                <?php
                                                $arrayDatos = $tipo_cliente;
                                                foreach ($arrayDatos as $key => $value) {
                                                    ?>
                                                    <option value="<?= $key ?>"/><?= $value ?></option>
                                                    <?php
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <span style="margin-left: 65%"><input type="submit" name="Boton" value="Enviar"></span>
                                <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                                <!--<span class="ButtonExcel"><a href="report_excel_resumen.php?<?= http_build_query($data) ?>"><i class="icon fa fa-lg fa-bold fa-file-excel-o" aria-hidden="true"></i></a></span>-->
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
            <?php topePagina(); ?>
        </div>
    </body>
</html>
