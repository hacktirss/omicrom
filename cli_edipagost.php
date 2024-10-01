<?php
session_start();
set_time_limit(600);

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$session = new OmicromSession("pagos.id", "pagos.id");
$busca = $session->getSessionAttribute("criteria");
$Cliente = utils\HTTPUtils::getSessionValue("Cuenta");
$mysqli = iconnect();
if ($_REQUEST["criteria"] === "ini") {
    utils\HTTPUtils::setSessionValue("busca", $_REQUEST["busca"]);
}
$busca = utils\HTTPUtils::getSessionValue("busca");
$Titulo = "Diferir saldo a tarjetas";
$SqlPago = "SELECT CONCAT(p.serie,'-',p.id) folio,p.fecha,cli.nombre,p.importe FROM pagos p LEFT JOIN cli ON p.cliente=cli.id "
        . "WHERE p.id = " . $busca;
if ($vl = $mysqli->query($SqlPago)->fetch_assoc()) {
    
}
//require "./services/ReportesClientesService.php";
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php include './config_omicrom_clientes.php'; ?>    
        <title><?= $Gcia ?></title>
        <script type="text/javascript" src="js/cli_edipagost.js"></script>
    </head>
    <body>
        <input type="hidden" name="IdPagoT" id="IdPagoT" value="<?= $busca ?>">
        <input type="hidden" name="NombreUsr" id="NombreUsr" value="<?= $Cliente ?>">
        <?php BordeSuperior(true) ?>
        <table style="width: 90%;font-family: sans-serif;font-size: 13px;background-color: #B2BABB;border-radius: 5px;margin-top: 10px;margin-bottom: 10px;height: 35px;">
            <tr>
                <td><strong>Pago:</strong> <?= $vl["folio"] ?></td>
                <td><strong>Fecha:</strong> <?= $vl["fecha"] ?></td>
                <td><strong>Cliente:</strong> <?= $vl["nombre"] ?></td>
                <td><strong>Importe:</strong> <?= number_format($vl["importe"], 2) ?></td>
            </tr>
        </table>
        <div id="TablaDatos" style="width: 90%;">
            <table aria-hidden="true">
                <thead>
                    <tr>
                        <th class="fondoVerde">Unidad</th>
                        <th class="fondoVerde">Descripcion</th>
                        <th class="fondoVerde">Codigo</th>
                        <th class="fondoVerde">Importe</th>
                        <th class="fondoVerde">Abonado</th>
                        <th class="fondoVerde cuadrado">Abono</th>
                        <th class="fondoVerde cuadrado">Saldo</th>
                        <th class="fondoVerde"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $SqlUnidades = "SELECT unidades.id,unidades.descripcion,unidades.codigo,unidades.importe FROM unidades LEFT JOIN pagos ON pagos.cliente=unidades.cliente "
                            . "WHERE unidades.cliente = $Cliente  AND unidades.periodo = 'B' AND pagos.id = " . $busca . " "
                            . "GROUP BY unidades.id";
                    //echo $SqlUnidades;
                    if ($vl2 = $mysqli->query($SqlUnidades)) {
                        while ($Und = $vl2->fetch_assoc()) {
                            $Abono = "SELECT id,idUnidad,importeDelPago importeDelPago"
                                    . " FROM unidades_log WHERE noPago = " . $busca . "  AND idUnidad = " . $Und["id"];
                            $rAb = utils\IConnection::execSql($Abono);
                            ?>   
                            <tr style="font-family: sans-serif; font-size: 11px;">
                                <td><?= $Und["id"] ?></td>
                                <td><?= $Und["descripcion"] ?></td>
                                <td><?= $Und["codigo"] ?></td>
                                <td style="text-align: right;"><?= number_format($Und["importe"], 2) ?></td>
                                <td style="text-align: right;"><?= number_format($rAb["importeDelPago"], 2) ?></td>
                                <td class="cuadrado">
                                    <?php
                                    if ($rAb["importeDelPago"] == null) {
                                        ?>
                                        <input data-IdUnidad="<?= $Und["id"] ?>" data-importeUnidad="<?= $Und["importe"] ?>" name="ImporteUnidad" class="ImporteUnidad" type="number" style="width: 70px;" min="0" max="<?= $Und["importe"] ?>">
                                        <?php
                                    }
                                    ?>
                                </td>
                                <td class="cuadrado">
                                    <?php
                                    if ($rAb["importeDelPago"] == null) {
                                        ?>
                                        <input type="button" name="Agregar" class="AddImporte" value="Transferir" data-IdUnidad="<?= $Und["id"] ?>"></td>
                                    <?php
                                }
                                ?>
                                <td style="text-align: center;"><i  class="fa-solid fa-trash DeleteDif" data-idNvo="<?= $rAb["id"] ?>" data-idUnidad="<?= $Und["id"] ?>" data-importeAbono="<?= $rAb["importeDelPago"] ?>"></i></td>
                            </tr>
                            <?php
                            $Imp += $Und["importe"];
                            $Abn += $rAb["importeDelPago"];
                        }
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr style="font-weight: bold;">
                        <td colspan="3" style="text-align: right;">Totales:</td>
                        <td style="text-align: right;"><?= number_format($Imp, 2) ?></td>
                        <td style="text-align: right;"><?= number_format($Abn, 2) ?></td>
                        <td colspan="2" class="cuadrado" style="text-align: right;padding-right: 15px;">Diferencia: <?= number_format($vl["importe"] - $Abn, 2) ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>


        <?php
        if ($vl["importe"] == $Abn) {
            ?>
            <input type="hidden" name="AbonoTotal" id="AbonoTotal" value="Cuadrado">
            <?php
        }
        ?>
        <?php PieDePagina() ?>

    </body>
</html>