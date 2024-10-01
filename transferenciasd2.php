<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

set_time_limit(300);

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";
require "./services/TransferenciasService.php";

$Titulo = "Surtido automático de aceites por isla a piso [Formato 2]";
$request = utils\HTTPUtils::getRequest();
error_log(print_r($request, TRUE));
$nameVarOp = "op";
$nameVarTarea = "tarea";
if ($request->hasAttribute($nameVarOp)) {
    utils\HTTPUtils::setSessionValue($nameVarOp, $request->getAttribute($nameVarOp));
}
$op = utils\HTTPUtils::getSessionValue($nameVarOp);
if ($op === "ini") {
    utils\HTTPUtils::setSessionValue($nameVarTarea, "");
}

if ($request->hasAttribute($nameVarOp) && $op === "1") {
    utils\HTTPUtils::setSessionValue($nameVarTarea, $request->getAttribute($nameVarTarea));
}
$tarea = utils\HTTPUtils::getSessionValue($nameVarTarea);

$registrosArray = array();

if ($op === "1") {
    $selectInv = "
                SELECT inv.clave_producto id, inv.id claveid ,inv.descripcion, transf.isla_pos, transf.cantidad 
                FROM inv, transf 
                WHERE  inv.id = transf.producto AND inv.rubro = 'Aceites' AND inv.activo = 'Si'
                AND transf.tarea = '$tarea'
                ORDER BY transf.producto ASC;";
} else {
    $selectInv = "
                SELECT inv.clave_producto producto, inv.id claveid ,inv.descripcion,inv.minimo, inv.maximo, inv.existencia almacen
                FROM inv WHERE inv.rubro = 'Aceites' AND inv.activo = 'Si' ORDER BY inv.clave_producto ASC";

    $selectInvd = "
                SELECT invd.*
                FROM inv,invd
                WHERE 1 = 1 
                AND inv.id = invd.id
                AND inv.rubro = 'Aceites' AND inv.activo = 'Si'
                ORDER BY inv.id, invd.isla_pos";

    $registros = utils\IConnection::getRowsFromQuery($selectInvd);

    foreach ($registros as $value) {
        $registrosArray[$value["id"]][$value[isla_pos]]["existencia"] = $value["existencia"];
        $registrosArray[$value["id"]][$value[isla_pos]]["minimo"] = $value["minimo"];
        $registrosArray[$value["id"]][$value[isla_pos]]["maximo"] = $value["maximo"];
    }
}
//error_log(print_r($registrosArray[11], TRUE));
$rows = utils\IConnection::getRowsFromQuery($selectInv);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                let op = "<?= $op ?>";
                $("#Boton").hide();
                if (op === "ini" || op === "") {
                    $("#Boton").show();
                }
            });
        </script>
    </head>

    <body>

        <div id="container">
            <?php nuevoEncabezado($Titulo); ?>
            <div id="Reportes" style="min-height: 200px;"> 
                <?php if (!empty($tarea)) { ?>
                    <table aria-hidden="true">
                        <thead>
                            <tr>
                                <td>Producto</td>
                                <td>Descripcion</td>
                                <td>Isla</td>
                                <td>Cantidad</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $Total = 0;
                            $SubTotal = 0;
                            $Producto = 0;
                            foreach ($rows as $inv) {
                                if ($Producto > 0 && $Producto != $inv["claveid"]) {
                                    ?>
                                    <tr class="subtotal"><td colspan="100%"><?= $SubTotal ?></td></tr>
                                    <?php
                                    $SubTotal = 0;
                                }
                                ?>
                                <tr>
                                    <td style="text-align: center"><?= $inv["id"] ?></td>
                                    <td><?= $inv["descripcion"] ?></td>
                                    <td class="numero"><?= $inv["isla_pos"] ?></td>
                                    <td class="numero"><?= $inv["cantidad"] ?></td>
                                </tr>
                                <?php
                                $Producto = $inv["id"];
                                $Total += $inv["cantidad"];
                                $SubTotal += $inv["cantidad"];
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3">Salida total</td>
                                <td><?= $Total ?></td>
                            </tr>
                        </tfoot>
                    </table>
                <?php } else { ?>
                    <table aria-hidden="true">
                        <thead>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td colspan="<?= count($IslasPosicionInventario) ?>">Existencia actual en islas</td>
                                <td colspan="<?= count($IslasPosicionInventario) ?>">Faltantes en islas</td>
                                <td>Salida</td>
                            </tr>
                            <tr>
                                <td>Producto</td>
                                <td>Descripcion</td>
                                <td>Almacen</td>
                                <?php foreach ($IslasPosicionInventario as $value) { ?>
                                    <td><?= $value ?></td>
                                <?php } ?>
                                <?php foreach ($IslasPosicionInventario as $value) { ?>
                                    <td><?= $value ?></td>
                                <?php } ?>
                                <td>Total</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $Total = 0;
                            $ExistenciaTotal = 0;
                            foreach ($rows as $inv) {
                                $SubTotal = 0;
                                $ExistenciaTotal += $inv["almacen"];
                                $ExistenciaVariable = $inv["almacen"];
                                ?>
                                <tr>
                                    <td><?= $inv["producto"] ?></td>
                                    <td><?= $inv["descripcion"] ?></td>
                                    <td class="numero"><?= $inv["almacen"] ?></td>
                                    <?php foreach ($IslasPosicionInventario as $value) { ?>
                                        <td class="numero"><?= $registrosArray[$inv["claveid"]][$value]["existencia"] ?></td>
                                        <?php
                                    }
                                    foreach ($IslasPosicionInventario as $value) {
                                        $porLlenar = 0;
                                        $existencia = $registrosArray[$inv["claveid"]][$value]["existencia"];
                                        $minimo = $registrosArray[$inv["claveid"]][$value]["minimo"];
                                        $maximo = $registrosArray[$inv["claveid"]][$value]["maximo"];

                                        if ($ExistenciaVariable > 0 && $existencia < $minimo) {
                                            if ($ExistenciaVariable >= ($maximo - $existencia)) {
                                                $porLlenar = ($maximo - $existencia);
                                                $ExistenciaVariable -= $porLlenar;
                                            } else {
                                                $porLlenar = $ExistenciaVariable;
                                                $ExistenciaVariable = 0;
                                            }
                                            $SubTotal += $porLlenar;
                                        }
                                        ?>
                                        <td class="numero remarcar"><?= $porLlenar ?></td>
                                        <?php
                                    }
                                    ?>
                                    <td class="numero"><?= $SubTotal ?></td>
                                </tr>
                                <?php
                                $Total += $SubTotal;
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2">Total</td>
                                <td><?= $ExistenciaTotal ?></td>
                                <td colspan="100%"><?= $Total ?></td>
                            </tr>
                        </tfoot>
                    </table>
                <?php } ?>
            </div>
        </div>

        <div id="footer">
            <form name="formActions" method="post" action="" id="form" class="oculto">
                <div id="Controles">
                    <table aria-hidden="true">
                        <tr style="height: 40px;">
                            <td>
                                <span><button name="Boton2" value="Realizar salida" id="Boton"><i class="icon fa fa-lg fa-sign-out" aria-hidden="true"></i> Realizar salida</button></span>
                                <span><button onclick="print()" title="Imprimir reporte" id="Imprimir"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                            </td>
                        </tr>

                    </table>
                </div>
            </form>
            <?php topePagina(); ?>
        </div>
    </body>
</html>
