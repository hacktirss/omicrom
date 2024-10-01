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

#Variables comunes;
$Mes = date('m');
$Anio = date('Y');


if ($request->hasAttribute("Boton")) {
    $Mes = $request->getAttribute("Mes");
    $Anio = $request->getAttribute("Anio");
}

$inicio = $Anio . $Mes . "01";
$fin = $Anio . $Mes . "31";

$selectConsumos = "SELECT 
                    V.fecha fechaConsumos,  V.movimientos, V.importe vendido, 
                    IFNULL(F.importe,0.00) facturado, 
                    ROUND(V.importe - IFNULL(F.importe, 0.00), 2) pendiente
                    FROM (
                        SELECT DATE(rm.fin_venta) fecha, COUNT(*) movimientos,
                        ROUND(SUM(importe),2) importe 
                        FROM rm 
                        LEFT JOIN cli ON rm.cliente = cli.id 
                        WHERE fecha_venta BETWEEN $inicio AND $fin AND rm.tipo_venta = 'D'
                        GROUP BY DATE(rm.fecha_venta)
                    ) V
                    LEFT JOIN (
                        SELECT DATE(rm.fin_venta) fecha, COUNT(*) movimientos,
                        ROUND(SUM(rm.importe),2) importe 
                        FROM rm 
                        LEFT JOIN cli ON rm.cliente=cli.id 
                        WHERE fecha_venta BETWEEN $inicio AND $fin
                        AND rm.uuid <> '-----' AND rm.tipo_venta='D' 
                        GROUP BY DATE(rm.fecha_venta)
                    ) F ON F.fecha = V.fecha;";

$months = array();
setlocale(LC_TIME, "es_MX.UTF-8");
for ($m = 1; $m <= 12; $m++) {
    $months[cZeros($m, 2, "LEFT")] = strtoupper(strftime("%B", mktime(0, 0, 0, $m, 12)));
}
$years = array();
$selectYears = "SELECT YEAR(fin_venta) year FROM rm GROUP BY YEAR(fin_venta);";
$yearResult = $mysqli->query($selectYears);
while ($rg = $yearResult->fetch_array()) {
    $years[$rg["year"]] = $rg["year"];
}
$Titulo = "Dias pendientes de facturación de " . ucwords(strtolower($months[$Mes])) . " " . $years[$Anio];
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#Anio").val("<?= $Anio ?>");
                $("#Mes").val("<?= $Mes ?>");
            });
        </script>
    </head>

    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo) ?>
            <div id="Reportes" style="min-height: 150px;">
                 <table aria-hidden="true">
                    <thead>
                        <tr>
                            <td>Fecha</td>
                            <td>Ventas</td>
                            <td>Vendido</td>
                            <td>Facturado</td>
                            <td>Por facturar</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $vendido = $facturado = $pendiente = 0;
                        if (($Venta = $mysqli->query($selectConsumos))) {
                            while ($rg = $Venta->fetch_array()) {
                                $style = "";
                                if ($rg ["pendiente"] > 1) {
                                    $style = "background-color: #F7FF7C";
                                }
                                ?>
                                <tr style="<?= $style ?>">
                                    <td><?= $rg["fechaConsumos"] ?></td>
                                    <td class="numero"><?= number_format($rg["movimientos"], 0) ?></td>
                                    <td class="numero"><?= number_format($rg["vendido"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg ["facturado"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg ["pendiente"], 2) ?></td>
                                </tr>
                                <?php
                                $vendido += $rg["vendido"];
                                $facturado += $rg["facturado"];
                                $pendiente += $rg["pendiente"];
                            }
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td>Total</td>
                            <td><?= number_format($vendido, 2) ?></td>
                            <td><?= number_format($facturado, 2) ?></td>
                            <td><?= number_format($pendiente, 2) ?></td>
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
                                 <table aria-hidden="true">
                                    <tr class="texto_tablas">
                                        <td style="white-space: nowrap">Año: 
                                            <select name='Anio' id="Anio">
                                                <?php
                                                foreach ($years as $key => $value) {
                                                    echo "<option value='$key'>$value</option>";
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                 <table aria-hidden="true">
                                    <tr class="texto_tablas">
                                        <td style="white-space: nowrap">Mes: 
                                            <select name='Mes' id="Mes">
                                                <?php
                                                foreach ($months as $key => $value) {
                                                    echo "<option value='$key'>$value</option>";
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <span><input type="submit" name="Boton" value="Enviar"></span>
                                <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                            </td>
                            <td>
                                <a href="repvtasfac.php?">Vendido Vs Fac</a> 
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
            <?php topePagina() ?>
        </div>
    </body>
</html>

