<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

$request = utils\HTTPUtils::getRequest();

if (!$request->hasAttribute("FechaI")) {
    $FechaI = $Anio . "-" . $Mes . "-01";
    $FechaF = $Anio . "-" . $Mes . "-" . lastDayPerMonth($Anio, $Mes);
}

$Titulo = "Reporte de archivos del SAT del $FechaI al $FechaF ";

$selectLogs = "
        SELECT fecha, IF(reporte = 'M', 'MENSUAL' , 'DIARIO') reporte,
        etiqueta concepto, producto, SUM(valor) valor
        FROM resumen_reporte_sat
        WHERE TRUE
        AND fecha BETWEEN DATE('$FechaI') AND DATE('$FechaF')
        GROUP BY reporte, etiqueta, producto
        ORDER BY etiqueta, producto DESC
        ;";

$registros = utils\IConnection::getRowsFromQuery($selectLogs);
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#Mes").val("<?= $Mes ?>");
                $("#Anio").val("<?= $Anio ?>");
            });
        </script>
    </head>

    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo); ?>
            <div id="Reportes">
                <table aria-hidden="true">
                    <thead>
                        <tr>
                            <td></td>
                            <td>Reporte</td>
                            <td>Concepto</td>
                            <td>Producto</td>
                            <td>Valor</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 0;
                        $subtotal = 0;
                        foreach ($registros as $rg) {
                            ?>
                            <tr class="texto_tablas">
                                <td><?= $i + 1 ?></td>
                                <td><?= $rg["reporte"] ?></td>
                                <td><?= $rg["concepto"] ?></td>
                                <td><?= $rg["producto"] ?></td>
                                <td class="numero"><?= number_format($rg["valor"], 2, ".", ",") ?></td>
                            </tr>
                            <?php
                            $subtotal += $rg["valor"];
                            if ($registros[$i + 1]["concepto"] !== $rg["concepto"]) {
                                ?>
                                <tr class="subtotal">
                                    <td colspan="4">Total</td>
                                    <td><?= number_format($subtotal, 2, ".", ",") ?></td>
                                </tr>
                                <?php
                                $subtotal = 0;
                            }
                            $i++;
                        }
                        ?>
                    </tbody>
                </table>
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
                                        <td>Mes:</td>
                                        <td>
                                            <select name="Mes" id="Mes">
                                                <?php
                                                foreach (getMonts() as $key => $value) {
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
                                    <tr>
                                        <td>Año:</td>
                                        <td>
                                            <select name="Anio" id="Anio">
                                                <?php
                                                foreach (getYears() as $key => $value) {
                                                    echo "<option value='$key'>$value</option>";
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <?php
                                if ($request->getAttribute("return") === "resumen.php") {
                                    ?>
                                    <a href="<?= $request->getAttribute("return") ?>">
                                        <i class="fa fa-reply fa-2x" aria-hidden="true"></i>
                                    </a>
                                    <?php
                                }
                                ?>
                                <span><input type="submit" name="Boton" value="Enviar"></span>
                                <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
            <?php topePagina(); ?>
        </div>
    </body>
</html>

