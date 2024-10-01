<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require './services/DispensariosService.php';

$request = utils\HTTPUtils::getRequest();
$session = new OmicromSession("man_pro.manguera", "man_pro.manguera", $nameVariableSession);

$busca = $session->getSessionAttribute("criteria");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Id = 21;
$Titulo = "Mangueras y combustibles x posicion";

$paginador = new Paginador($Id,
        "man_pro.id , man_pro.cpu, man_pro.m",
        "LEFT JOIN com ON man_pro.producto = com.clavei",
        "",
        "man_pro.posicion = $cVarVal AND man_pro.activo = 'Si'",
        $session->getSessionAttribute("sortField"),
        $session->getSessionAttribute("criteriaField"),
        utils\Utils::split($session->getSessionAttribute("criteria"), "|"),
        strtoupper($session->getSessionAttribute("sortType")),
        $session->getSessionAttribute("page"),
        "REGEXP",
        "dispensarios.php");

$query = "SELECT * FROM man LEFT JOIN prv ON prv.id=man.id_proveedor WHERE man.id = $cVarVal;";
$He = $mysqli->query($query)->fetch_array();

$query1 = "SELECT MAX(man.posicion) maximo FROM man;";
$rows1 = $mysqli->query($query1)->fetch_array();
$Maxim = $rows1['maximo'];

$mayor = $cVarVal + 1;
$menor = $cVarVal - 1;

$self = utils\HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");
$cLink = substr($self, 0, strrpos($self, ".")) . 'e.php';
$cLinkd = substr($self, 0, strrpos($self, ".")) . 'd.php';
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php include './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#busca").focus();
            });
        </script>
        <?php $paginador->script(); ?>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <div style="width: 98%;margin-left: auto;margin-right: auto;border: 2px solid gray;margin-bottom: 10px;padding: 3px 1px;">
            <table style="width: 98%;margin-left: auto;margin-right: auto;" class="texto_tablas" aria-hidden="true">
                <tr style="background-color: #E1E1E1;height: 20px;">
                    <td><strong>Isla:</strong> <?= $He["isla"] ?></td>
                    <td><strong>Dispensario:</strong> <?= $He["dispensario"] ?></td>
                    <td><strong>Posicion:</strong> <?= $He["posicion"] ?></td>
                    <td><strong>Productos:</strong> <?= $He["productos"] ?> </td>
                    <td><strong>Activo:</strong> <?= $He["activo"] ?></td>
                    <td><strong>Proveedor:</strong> <?= $He["nombre"] ?></td>
                </tr>
            </table>
        </div>

        <div id="TablaDatos">
            <table class="paginador" aria-hidden="true">
                <?php
                echo $paginador->headers(array(" "), array(" ", " "));
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr>
                        <td style="text-align: center;"><a href="<?= $cLink ?>?busca=<?= $row['id'] ?>"><i class="icon fa fa-lg fa-edit" aria-hidden="true"></i></a></td>
                        <td style="text-align: center;">MGA-<?= sprintf("%04d", $row['manguera']) ?></td>
                        <td style="text-align: center;"><?= $row['producto'] ?></td>
                        <td style="text-align: center;"><?= $row['descripcion'] ?></td>
                        <td style="text-align: center;"><?= $row['dis_mang'] ?></td>
                        <td style="text-align: center;"><?= $row['activo'] ?></td>
                        <td><?= $row["cpu"] ?></td>
                        <td><?= $row["m"] ?></td>
                    </tr>
                    <?php
                }
                ?> 
            </table>

            <br/>
            <div>
                <div style="display: inline;">
                    <?php
                    if ($cVarVal > 1) {
                        echo "<a class='textosCualli_i' href='dispensariosd.php?cVarVal=$menor' style='font-size: 25px;'><i class=\"icon fa fa-lg fa-arrow-circle-left\" aria-hidden=\"true\"></i></a>";
                    }
                    ?>
                </div>
                <div style="display: inline">
                    <?php
                    if ($cVarVal < $Maxim) {
                        echo "<a class='textosCualli_i' href='dispensariosd.php?cVarVal=$mayor' style='font-size: 25px;'><i class=\"icon fa fa-lg fa-arrow-circle-right\" aria-hidden=\"true\"></i></a>";
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
        $nLink = array();
        echo $paginador->footer(false, $nLink, false);
        echo "<div class='mensajes'>$Msj</div>";
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>
