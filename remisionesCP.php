<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$nameSession = "RemisionesCartaPorte";
$session = new OmicromSession("rm.id", "rm.id", $nameSession, $arrayFilter, "Filtros");
//
//require_once './services/BancosService.php';

$busca = $session->getSessionAttribute("criteria");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Id = 163;
$Titulo = "Catalogo de despachos";


$paginador = new Paginador($Id,
        "uuid",
        "",
        "",
        "$conditions",
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
                echo $paginador->headers(array("Editar", "PDF"), array());
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr>
                        <td style="text-align: center;"><a href="<?= $cLink ?>?busca=<?= $row["id"] ?>"><i class="icon fa fa-lg fa-edit" aria-hidden="true"></i></a></td>
                        <td style="text-align: center;">
                            <?php
                            if ($row["uuid"] !== "-----") {
                                ?> 
                                <a style="color: red;" href="javascript:winuni('enviafile.php?id=<?= $row['uuid'] ?>&type=pdf&formato=0')"><i class="icon fa fa-lg fa-file-pdf-o" title="Obtener PDF Tamaño Carta" aria-hidden="true"></i></a>
                                <a style="color: graytext;" href="javascript:winuni('enviafile.php?id=<?= $row['uuid'] ?>&type=pdf&formato=1')"><i class="icon fa fa-lg fa-file-pdf-o" title="Obtener PDF Formato Ticket" aria-hidden="true"></i></a>
                                    <?php
                                }
                                ?>
                        </td>
                        <?php echo $paginador->formatRow(); ?>
                    </tr>
                    <?php
                }
                ?> 
            </table>
        </div>
        <?php
        echo $paginador->footer($usuarioSesion->getLevel() >= 7);
        echo $paginador->filter();
        echo "<div class='mensajes'>$Msj</div>";
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>