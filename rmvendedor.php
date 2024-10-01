<?php
#Librerias
session_start();

include_once ("check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$mysqli = iconnect();


$busca = $request->getAttribute("busca");
$Corte = $request->getAttribute("Corte");


$cSql = "SELECT rm.corte,rm.id,rm.fin_venta fecha,rm.pesos,rm.volumen,
            com.descripcion producto, rm.posicion, rm.kilometraje,rm.placas, rm.codigo, 
            IF(unidades.impreso IS NULL,'-----',unidades.impreso) impreso, 
            IF(unidades.descripcion IS NULL,'-----',unidades.descripcion) descripcion
        FROM com,rm 
        LEFT JOIN unidades ON rm.codigo = unidades.codigo AND unidades.cliente > 0
        WHERE com.clavei = rm.producto AND rm.corte = '$Corte' AND rm.vendedor = '$busca' 
        ORDER BY rm.id";

$VtaA = $mysqli->query($cSql);
$Titulo = "Ventas de corte: $Corte Vendedor: $busca";
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports.php'; ?> 
        <title><?= $Gcia ?></title>
    </head>

    <body>

        <div id='container'>
            <?php nuevoEncabezado($Titulo); ?>

            <div id="TablaDatosReporte">
                 <table aria-hidden="true">
                    <tr>
                        <td class="downTitles"></td>
                        <td class="downTitles">No.ticket</td>
                        <td class="downTitles">Corte</td>
                        <td class="downTitles">No.tarjeta</td>
                        <td class="downTitles">Fecha</td>
                        <td class="downTitles">No.placas</td>
                        <td class="downTitles">Kilometraje</td>
                        <td class="downTitles">Descripcion</td>
                        <td class="downTitles">Producto</td>
                        <td class="downTitles">Litros</td>
                        <td class="downTitles">Importe</td>
                    </tr>
                    <?php
                    $cont = 1;
                    $Imp = $nLtsC = 0;
                    while ($rg = $VtaA->fetch_array()) {
                        ?>

                        <tr>
                            <td align="right"><?= $cont ?></td>
                            <td align="right"><?= $rg["id"] ?></td>
                            <td align="right"><?= $rg["corte"] ?></td>
                            <td align="left"><?= $rg["impreso"] ?></td>
                            <td align="left"><?= $rg["fecha"] ?></td>
                            <td align="left"><?= ucwords(strtoupper($rg["placas"])) ?></td>
                            <td align="right"><?= $rg["kilometraje"] ?></td>
                            <td align="left"><?= ucwords(strtolower($rg["descripcion"])) ?></td>
                            <td align="left"><?= $rg["producto"] ?></td>
                            <td align="right"><?= number_format($rg["volumen"], 2) ?></td>
                            <td align="right"><?= number_format($rg["pesos"], 2) ?></td>
                        </tr>

                        <?php
                        $cont ++;
                        $nLtsC += $rg["volumen"];
                        $nImpC += $rg["pesos"];
                    }
                    ?>
                    <tr>
                        <td class="upTitles" colspan="9"></td>
                        <td class="upTitles"><?= number_format($nLtsC, 2) ?></td>
                        <td class="upTitles"><?= number_format($nImpC, 2) ?></td>
                    </tr>
                </table>
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
                </form>
                <?php topePagina() ?>
            </div>
    </body>
</html>