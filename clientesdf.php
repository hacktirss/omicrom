<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require './services/TarjetasService.php';

$request = utils\HTTPUtils::getRequest();
$session = new OmicromSession("unidades.impreso", "unidades.id", $nameVariableSession);

$usuarioSesion = getSessionUsuario();
$Id = 47;
$Titulo = "Distribución de saldos por unidades de importe";
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$clienteVO = new ClientesVO();
if (is_numeric($cVarVal)) {
    $clienteVO = $clienteDAO->retrieve($cVarVal);
}

$self = utils\HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");
$cLink = substr($self, 0, strrpos($self, ".")) . 'e.php';
$cLinkd = substr($self, 0, strrpos($self, ".")) . 'd.php';
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script type="text/javascript" src="js/clientesdf.js"></script>
    </head>
    <body>
        <?php BordeSuperior(); ?>
        <div style="width: 98%;margin-left: auto;margin-right: auto;border: 2px solid gray;margin-bottom: 10px;padding: 3px 1px;">
            <table style="width: 98%;margin-left: auto;margin-right: auto;" class="texto_tablas" aria-hidden="true">
                <tr style="background-color: #E1E1E1;height: 20px;">
                    <td> &nbsp; <strong>Cuenta:</strong> <?= $cVarVal ?></td><td> &nbsp; <strong>Nombre:</strong> <?= $clienteVO->getNombre() ?></td>
                    <td> &nbsp; <strong>Tipo de Cliente:</strong> <?= $clienteVO->getTipodepago() ?> </td>
                </tr>
            </table>
        </div>
        <table style="width: 100%;" title="Tabla unidades">
            <tr><th colspan="2" style="height: 0px;background-color: white"></th></tr>
            <tr>
                <td style="width: 30%;vertical-align: top">
                    <table aria-hidden="true" style="width: 95%;" title="Tabla Unidades">
                        <tr><th colspan="2"></th></tr>
                        <tr>
                            <td style="width: 30%;text-align: right;font-family: sans-serif;font-size: 13px;color: #212F3D;">Unidad:</td>
                            <td>
                                <input type="hidden" name="IdUnidadSeleccionada" id="IdUnidadSeleccionada">
                                <input type="hidden" name="UsrA" id="UsrA" value="<?= $usuarioSesion->getNombre() ?>">
                                <select name="Unidad" id="Unidad" style="font-size: 13px;color: #212F3D;">
                                    <option> Seleccionar una opción</option>
                                    <?php
                                    $SqlUnidades = "SELECT * FROM unidades WHERE cliente = $cVarVal  AND periodo = 'B'";
                                    $Unidades = utils\IConnection::getRowsFromQuery($SqlUnidades);
                                    foreach ($Unidades as $Und) {
                                        ?>
                                        <option style="font-size: 10px;font-family: sans-serif" value="<?= $Und["id"] ?>" data-importe="<?= $Und["importe"] ?>"><?= $Und["descripcion"] ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" style="text-align: center;height: 50px;"><input type="button" name="Seleccionar" id="SeleccionarTarjeta" value="Seleccionar Tarjeta"></td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <div style="width: 100%;font-family: sans-serif;font-size: 14px;color: #212F3D;" id="ResultValues"></div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="vertical-align: top">
                    <table class="paginador" aria-hidden="true" style="width: 95%;" title="Detalle de unidades">
                        <tr><th>Unidad</th><th>Descipcion</th><th>Codigo</th><th>Importe</th><th>Abono</th><th></th></tr>
                        <?php
                        $SqlUnidades = "SELECT * FROM unidades WHERE cliente = $cVarVal  AND periodo = 'B'";
                        $Unidades = utils\IConnection::getRowsFromQuery($SqlUnidades);
                        foreach ($Unidades as $Und) {
                            ?>
                            <tr style="font-family: sans-serif; font-size: 11px;">
                                <td><?= $Und["id"] ?></td>
                                <td><?= $Und["descripcion"] ?></td>
                                <td><?= $Und["codigo"] ?></td>
                                <td><?= $Und["importe"] ?></td>
                                <td><input data-IdUnidad="<?= $Und["id"] ?>" data-importeUnidad="<?= $Und["importe"] ?>" name="ImporteUnidad" class="ImporteUnidad" type="number" style="width: 70px;" min="0" max="<?= $Und["importe"] ?>"></td>
                                <td><input type="button" name="Agregar" class="AddImporte" value="Transferir" data-IdUnidad="<?= $Und["id"] ?>"></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </table>
                </td>
            </tr>
        </table>
        <?php
        $nLink = array("<i class=\"icon fa fa-lg fa-plus-circle\" aria-hidden=\"true\"></i> Agregar" => "clientesde.php?Tarjetas=1", "<i class='fa-solid fa-money-bill-transfer fa-lg'></i> Diferir Saldos" => "clientesdf.php");
        echo "<div class='mensajes'>$Msj</div>";
        BordeSuperiorCerrar();
        PieDePagina();
        ?>
    </body>
</html>