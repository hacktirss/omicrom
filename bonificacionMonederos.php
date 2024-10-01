<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$session = new OmicromSession("cli.nombre", "cli.nombre");

$busca = $session->getSessionAttribute("criteria");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Titulo = "Bonificaciones Monederos a clientes";
$Id = 161;

$paginador = new Paginador($Id,
        "u.id,cli.id idCli",
        "",
        "",
        "",
        $session->getSessionAttribute("sortField"),
        $session->getSessionAttribute("criteriaField"),
        utils\Utils::split($session->getSessionAttribute("criteria"), "|"),
        strtoupper($session->getSessionAttribute("sortType")),
        $session->getSessionAttribute("page"),
        "REGEXP",
        "");

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

        <div id="TablaDatos">
            <table class="paginador" aria-hidden="true">
                <?php
                echo $paginador->headers(array("Recompensa", "Unidad"), array());
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr>
                        <td style="text-align: center;">
                            <a href="<?= $cLinkd ?>?criteria=ini&busca=<?= $row["id"] ?>"><em class="fa-solid fa-gift fa-lg" style="color: #E74C3C;"></em></a>
                        </td> 
                        <td style="text-align: center;">
                            <?= $row["id"] ?>
                        </td>
                        <?php echo $paginador->formatRow(); ?>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>
        <?php
        echo $paginador->footer(false, array("<div style='display:inline-block;' id='Bonificar'><i class='fa-solid fa-address-book fa-lg'></i> Bonificar</div>" => "#",
            "<i class='fa-solid fa-circle-exclamation'></i> Detalle" => "javascript:winmin('detalleBonificacion.php')"));
        echo $paginador->filter();
        echo "<div class='mensajes'>$Msj</div>";
        BordeSuperiorCerrar();
        PieDePagina();
        ?>
    </body>
</html>
<script type="text/javascript" src="js/bonifica.js"></script>