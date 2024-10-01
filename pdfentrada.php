<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("importeletras.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$usuarioSesion = getSessionUsuario();

$busca = $request->getAttribute("busca");

$sqlSelect = "  SELECT me.tanque,me.fecha,me.fechae,me.proveedor,me.producto,me.vol_inicial,me.vol_final,me.fechafac,
                me.foliofac,me.volumenfac,cre1.llave terminal,me.clavevehiculo,me.documento,me.preciou,me.importefac,
                com.descripcion,prv.alias,me.proveedor,me.id,prv.nombre,prv.direccion,prv.colonia,prv.municipio,
                me.incremento,me.horaincremento,me.carga,cargas.tcAumento,me.volumen_devolucion
                FROM prv,me LEFT JOIN cargas ON me.id=cargas.entrada
                LEFT JOIN com ON me.producto = com.clave 
                LEFT JOIN permisos_cre cre1 ON cre1.id = me.terminal
                WHERE me.id = '$busca' AND me.proveedor = prv.id";

$Cpo = $mysqli->query($sqlSelect)->fetch_array();

$sqlSelectD = " SELECT inv.descripcion,med.cantidad,med.precio,inv.umedida
                FROM med LEFT JOIN inv ON med.clave=inv.id
                WHERE med.id='$busca'  
                ORDER BY med.idnvo";


$CpoD = $mysqli->query($sqlSelectD);

$Titulo = "Recibo de entrada de combustible";
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports_print.php'; ?> 
        <title><?= $Gcia ?></title>
        <style>
            @page { 
                size: A4 /*landscape*/; 
            }
        </style>
        <script type="text/javascript">
            $(document).ready(function () {

            });
        </script>

    </head>

    <!-- Set "A5", "A4" or "A3" for class name -->
    <!-- Set also "landscape" if you need -->
    <body class="A4">
        <div class="iconos">
             <table aria-hidden="true">
                <tr>
                    <td><?= $Titulo?></td>
<!--                    <td style="text-align: center">1/1</td>-->
                    <td style="text-align: center"><!-- <i class='icon fa fa-lg fa-download' aria-hidden="true"></i>--><i onclick="print();" title="Imprimir" class='icon fa fa-lg fa-print' aria-hidden="true"></i></td>
                </tr>
            </table>
        </div>
        <!-- Each sheet element should have the class "sheet" -->
        <!-- "padding-**mm" is optional: you can set 10, 15, 20 or 25 -->
        <div class="sheet padding-10mm">

            <?php nuevoEncabezadoPrint($Titulo) ?>

            <div id="TablaDatosReporte">
                <div>
                    <h3 align="right" ><?= $Cpo["fechae"] ?> </h3>
                    <h2 align="left">ENTRADA FOLIO: <?= $Cpo["id"] ?></h2>
                </div>

                <div style="text-align: left;">
                    <h3>Datos Del proveedor</h3>
                    <span><?= $Cpo["nombre"] ?> <?= $Cpo["alias"] ?> <br /></span>
                    <span><?= $Cpo["direccion"] ?><br /></span> 
                    <span><?= $Cpo["colonia"] ?><br /></span>
                    <span>Tel.<?= $Cpo["telefono"] ?></span>
                </div>

                <div>Detalle del producto Registrado </div>

                <div style="padding-top: 10px;">

                     <table aria-hidden="true">
                        <thead>
                            <tr>
                                <td>Combustible</td>
                                <td>Fecha Factura</td>
                                <td>Fecha Entrada</td>
                                <td>Folio</td>
                                <td>Terminal</td>
                                <td class="moneda">Importe</td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?= $Cpo["producto"] ?> <?= $Cpo["descripcion"] ?></td>
                                <td><?= $Cpo["fechafac"] ?></td>
                                <td><?= $Cpo["fechae"] ?></td>
                                <td><?= $Cpo["foliofac"] ?></td>
                                <td class="numero"><?= $Cpo["terminal"] ?></td>
                                <td class="numero"><?= number_format($Cpo["importefac"], 2) ?></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6"></td>
                            </tr>
                        </tfoot>
                    </table>

                </div>

                <div style="padding-top: 20px;">

                     <table aria-hidden="true">
                        <thead>
                            <tr>
                                <td>Clave del vehiculo </td>
                                <td>Tipo docto.</td>
                                <td class="moneda">Precio U.</td>
                                <td>Vol. factura: </td>
                                <td>Carga:</td>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?= $Cpo["clavevehiculo"] ?></td>
                                <td><?= $Cpo["documento"] ?></td>
                                <td class="numero"><?= number_format($Cpo["preciou"], 2) ?> </td>
                                <td class="numero"><?= $Cpo["volumenfac"] ?> </td>
                                <td class="numero"> <?= $Cpo["carga"] ?></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6"></td>
                            </tr>
                        </tfoot>
                    </table>

                </div>

                <div style="padding-top: 10px;">

                     <table aria-hidden="true">
                        <thead>
                            <tr>
                                <td class="fondoGris">No.de tanque</td><td class="numero"> <?= $Cpo["tanque"] ?></td>
                                <td class="fondoGris">Hra.del incremento</td><td> <?= $Cpo["horaincremento"] ?></td>
                                <td class="fondoGris">Incremento Buto</td><td class="numero"> <?= $Cpo["incremento"] ?></td>
                                <td class="fondoGris">Incremento Neto</td><td class="numero"> <?= $Cpo["tcAumento"] ?></td>
                                <td class="fondoGris">Vol. Devolución</td><td class="numero"> <?= $Cpo["volumen_devolucion"] ?></td>
                            </tr>
                        </thead>
                    </table>

                </div>

                <div style="padding-top: 10px;">Desgloce de la nota de entrada</div>

                <div style="padding-top: 10px;">

                     <table aria-hidden="true">
                        <thead>
                            <tr>
                                <td>Descripcion</td>
                                <td class="numero">Cantidad</td>
                                <td class="moneda">Precio</td>
                                <td class="moneda">Importe</td>
                            </tr>
                        </thead>
                        <tbody>

                            <?php while ($rg = $CpoD->fetch_array()) { ?>
                                <tr>
                                    <td><?= ucwords(strtolower($rg["descripcion"])) ?></td>
                                    <td class="numero"><?= number_format($rg["cantidad"], 4) ?></td>
                                    <td class="numero"><?= number_format($rg["cantidad"], 0) ?></td>
                                    <td class="numero"><?= number_format($rg["cantidad"] * $rg["precio"], 2) ?></td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td></td>
                                <td></td>
                                <td>Totales</td>
                                <td class="moneda"><?= number_format($Cpo["importefac"], 2) ?></td>
                            </tr>
                        </tfoot>

                    </table>

                    <p style="text-align: center"><strong><?= impletras($Cpo["importefac"], 'pesos') ?></strong></p>

                </div>
            </div>
            
        </div>
<!--        <div class="sheetFooter">Page </div>-->
    </body>
</html>

