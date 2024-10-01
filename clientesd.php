<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require './services/TarjetasService.php';

$request = utils\HTTPUtils::getRequest();
$session = new OmicromSession("unidades.impreso", "unidades.id", $nameVariableSession);

$Id = 47;
$Titulo = "Códigos y unidades por cliente";
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$paginador = new Paginador($Id,
        "unidades.id,IF(unidades.importe > 0, unidades.importe, unidades.litros) cantidad, IF(unidades.estado = 'a', 'Activo','Inactivo') status,unidades.periodo",
        "",
        "",
        "unidades.cliente = " . $cVarVal,
        $session->getSessionAttribute("sortField"),
        $session->getSessionAttribute("criteriaField"),
        utils\Utils::split($session->getSessionAttribute("criteria"), "|"),
        strtoupper($session->getSessionAttribute("sortType")),
        $session->getSessionAttribute("page"),
        "REGEXP",
        "clientes.php");

$clienteVO = new ClientesVO();
if (is_numeric($cVarVal)) {
    $clienteVO = $clienteDAO->retrieve($cVarVal);
}
$arrayPeriodo = array("D" => "Diario", "S" => "Semanal", "Q" => "Quincenal", "M" => "Mensual", "B" => "Saldos", "A" => "M. Acumulativo", "C" => "M. Consumos");

$self = utils\HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");
$cLink = substr($self, 0, strrpos($self, ".")) . 'e.php';
$cLinkd = substr($self, 0, strrpos($self, ".")) . 'd.php';
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#autocomplete").focus();
            });
        </script>
        <?php $paginador->script(); ?>
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

        <div id="TablaDatos">
            <table class="paginador" aria-hidden="true">
                <?php
                echo $paginador->headers(array("Configurar"), array("Periodo", "Cantidad", "Estatus", " "));
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr>
                        <td style="text-align: center;"><a href="<?= $cLink ?>?busca=<?= $row['id'] ?>" title="Configurar unidad"><i class="icon fa fa-lg fa-gear" aria-hidden="true"></i></a></td>
                        <?php echo $paginador->formatRow(); ?>
                        <td><?= $arrayPeriodo[$row["periodo"]] ?></td>
                        <td><?= $row['cantidad'] ?></td>
                        <td><?= $row['status'] ?></td>
                        <td style="text-align: center;"><a class="textosCualli" href=javascript:confirmar("Deseas_liberar_el_registro_<?= $row["id"] ?>?","<?= $self ?>?cId=<?= $row["id"] ?>&op=Liberar");>liberar</a></td>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>
        <?php
        $nLink = array("<i class=\"icon fa fa-lg fa-plus-circle\" aria-hidden=\"true\"></i> Agregar" => "clientesde.php?Tarjetas=1", "<i class='fa-solid fa-money-bill-transfer fa-lg'></i> Transferir Saldos" => "clientesdf.php");

        echo $paginador->footer(false, $nLink, false, true);
        echo $paginador->filter();
        echo "<div class='mensajes'>$Msj</div>";
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>