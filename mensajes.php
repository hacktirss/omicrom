<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("data/MensajesDAO.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$session = new OmicromSession("msj.id", "msj.id");

$busca = $session->getSessionAttribute("criteria");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Id = 26;
$Titulo = "Mensajes";

$paginador = new Paginador($Id,
        "id, tipo, IF(tipo = 'L', 'Leido', 'Sin leer') titulo",
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
                echo $paginador->headers(array("Visor"), array("Tipo"));
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    $fa = $row["tipo"] === TipoMensaje::LEIDO ? "envelope-open-o" : "envelope";
                    ?>
                    <tr>
                        <td style="text-align: center;"><a href=javascript:winuni("vermensajes.php?busca=<?= $row["id"] ?>");><i class="icon fa fa-lg fa-eye" aria-hidden="true"></i></td>
                            <?php echo $paginador->formatRow(); ?>
                        <td style="text-align: center;"><a href="#" title="<?= $row["titulo"]?>"><i class="icon fa fa-lg fa-<?= $fa?>" aria-hidden="true"></i></a></td>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>
        <?php
        echo $paginador->footer(false, null, false, true);
        echo $paginador->filter();
        echo "<div class='mensajes'>$Msj</div>";
        BordeSuperiorCerrar();
        PieDePagina();
        ?>
    </body>
</html>