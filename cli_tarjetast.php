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
$Titulo = "Transferir saldo a tarjetas";
$usuarioSesion = getSessionUsuario();
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php include './config_omicrom_clientes.php'; ?>    
        <title><?= $Gcia ?></title>
        <script type="text/javascript" src="js/cli_tarjetast.js?var=<?= md5_file("js/cli_tarjetast.js") ?>"></script>
    </head>
    <body>
        <input type="hidden" name="IdPagoT" id="IdPagoT" value="<?= $busca ?>">
        <input type="hidden" name="NombreUsr" id="NombreUsr" value="<?= $usuarioSesion->getNombre() ?>">
        <?php BordeSuperior(true) ?>
        <input type="hidden" id="IdUnidSelec" name="IdUnidSelec">
        <div class="texto_tablas">
            <p>
                <strong>Seleccionar tarjeta a la que le desea transferir y seleccionar</strong><br>
                <select name="IdUnidadSeleccionada" id="IdUnidadSeleccionada" class="texto_tablas">
                    <?php
                    $SqlUnidades = "SELECT unidades.id,unidades.descripcion,unidades.codigo,unidades.importe FROM unidades 
                            WHERE unidades.cliente = $Cliente AND unidades.periodo = 'B'  GROUP BY unidades.id;";
                    if ($vl2 = $mysqli->query($SqlUnidades)) {
                        while ($Und = $vl2->fetch_assoc()) {
                            ?>
                            <option value="<?= $Und["id"] ?>"><?= $Und["id"] ?> .- <?= $Und["descripcion"] ?></option>
                            <?php
                        }
                    }
                    ?>
                </select>
                <input type="button" name="Seleccionar" id="Seleccionar" value="Seleccionar" class="texto_tablas">
            </p>
        </div>
        <div id="TablaDatos" style="width: 90%;">
            <table aria-hidden="true">
                <thead>
                    <tr>
                        <th class="fondoVerde">Unidad</th>
                        <th class="fondoVerde">Descripcion</th>
                        <th class="fondoVerde">Codigo</th>
                        <th class="fondoVerde">Importe</th>
                        <th class="fondoVerde"></th>
                        <th class="fondoVerde">Abonar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $SqlUnidades = "SELECT unidades.id,unidades.descripcion,unidades.codigo,unidades.importe FROM unidades 
                            WHERE unidades.cliente = $Cliente AND unidades.periodo = 'B'  GROUP BY unidades.id;";
                    if ($vl2 = $mysqli->query($SqlUnidades)) {
                        while ($Und = $vl2->fetch_assoc()) {
                            ?>   
                            <tr style="font-family: sans-serif; font-size: 11px;">
                                <td><?= $Und["id"] ?></td>
                                <td><?= $Und["descripcion"] ?></td>
                                <td class="text-right-omicrom"><?= $Und["codigo"] ?></td>
                                <td class="text-right-omicrom"><?= number_format($Und["importe"], 2) ?></td>
                                <td style="width: 60px;"><input type="number" name="ImporteTransferir" class="ImporteTransferir texto_tablas" placeholder="0.00"  style="width: 60px;text-align: right;" data-idOrigenNum="<?= $Und["id"] ?>"></td>
                                <td style="width: 80px;"  class="text-center-omicrom"><input type="button" name="Transferir"  value="Transferir" data-idUnidad="<?= $Und["id"] ?>" data-importe="<?= $Und["importe"] ?>" data-idOrigenInput="<?= $Und["id"] ?>" class="Transferir texto_tablas"></td>
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
                        <td colspan="2"></td>

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