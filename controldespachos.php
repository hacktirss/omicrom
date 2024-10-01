<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

$Titulo = "Control de despachos del $FechaI al $FechaF";
$cSql = $selectDespachos;
$registros = utils\IConnection::getRowsFromQuery($cSql);
$Id = 139; /* Número de en el orden de la tabla submenus */
$data = array("Nombre" => $Titulo, "Reporte" => $Id, "FechaI" => $FechaI, "FechaF" => $FechaF);
$registrosLandscape = 25;
$registrosVertical = 40;
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports.php'; ?> 
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
                concentrado = $("#Concentrado").DataTable({
                    bFilter: false,
                    bPaginate: false,
                    bInfo: false,
                    bSort: false,
                    bAutoWidth: false,
                    dom: 'Bfrtip',
                    buttons: [{
                            extend: 'excelHtml5',
                            title: function () {
                                return "Control de despachos <?= $DetalleTexto . " del " . $FechaI . " al " . $FechaF ?>";
                            },
                            text: '<inline style="color: green; white-space: nowrap;" ><em class="fa fa-fw fa-file-excel-o"></em> Exportar Reporte a Excel</inline>',
                            className: 'btn btn-default btn-xs',
                            filename: function () {
                                return "Control de despachos_<?= $DetalleTexto . str_replace("-", "", $FechaI . "_" . $FechaF) ?>";
                            }
                        }]
                });

            });
        </script>
    </head>

    <body>
        <div id="container">
            <?php
            nuevoEncabezado($Titulo);
            $Num = 1;
            ?>
            <div id="Reportes" style="min-height: 200px;"> 
                <p id="mydesc">Concentrado</p>
                <table id="Concentrado" summary="Detalle en control de despachos">
                    <thead>
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#</td>
                            <td>Despacho</td>
                            <td>Posicion</td>
                            <td>Producto</td>
                            <td>Cantidad</td>
                            <td>Precio</td>
                            <td>Importe</td>
                            <td>Fecha</td>                                           
                            <td>Factura</td>
                            <td>Cliente</td>
                            <td>Tipo</td>
                            <td>Impreso Por</td>
                        </tr>
                        <?php foreach ($registros as $rg) { ?>
                            <tr>
                                <td><?= $Num ?></td>
                                <td><?= $rg["Despacho"] ?></td>
                                <td><?= $rg["Posicion"] ?></td>
                                <td><?= ucwords(strtolower($rg["Producto"])) ?></td>
                                <td><?= $rg["Cantidad"] ?></td>
                                <td class="numero"><?= number_format($rg["Precio"], 3) ?></td>
                                <td class="numero"><?= number_format($rg["Importe"], 3, ".", "") ?></td>
                                <td><?= $rg["Fecha_Hora"] ?></td>
                                <td><?= $rg["Factura"] ?></td>
                                <td><?= $rg["Cliente"] ?></td>
                                <td><?= $rg["Tipo"] ?></td>  
                                <td><?= $rg["Imprime"] ?></td> 
                            </tr>
                            <?php
                            $Num++;
                        }
                        ?>                
                    </tbody>
                </table>
            </div>
            <div style="height:30px;"></div>
            <?php topePagina(); ?>
        </div>
    </body>
</html>
