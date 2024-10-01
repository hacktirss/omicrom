<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$session = new OmicromSession("cxc.id", "cxc.id");

$busca = $session->getSessionAttribute("criteria");
$op = utils\HTTPUtils::getRequest()->getAttribute("op");
$Msj = utf8_encode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Id = 45;
$Titulo = "Histórico de cuentas por cobrar";

$paginador = new Paginador($Id, 
        "cxc.id", 
        "LEFT JOIN cli on cxc.cliente = cli.id", 
        "", 
        "cxc.factura > 0", 
        $session->getSessionAttribute("sortField"), 
        $session->getSessionAttribute("criteriaField"), 
        utils\Utils::split($session->getSessionAttribute("criteria"), "|"), 
        strtoupper($session->getSessionAttribute("sortType")), 
        $session->getSessionAttribute("page"), 
        "REGEXP", 
        "", 
        "cxch cxc");

$self = utils\HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");

$cLink = substr($self, 0, strrpos($self, ".")) . 'e.php';
$cLinkd = substr($self, 0, strrpos($self, ".")) . 'd.php';
//require_once './services/CxcService.php';
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

        <?php
        BordeSuperior();
        ?>
        <div id="TablaDatos">
             <table class="paginador" aria-hidden="true">
                <?php
                echo $paginador->headers(array("Editar"), array());
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr>
                        <td style="text-align: center;"><a href="<?= $cLink ?>?busca=<?= $row['id'] ?>"><i class="icon fa fa-lg fa-edit" aria-hidden="true"></i></a></td>
                        <?php
                        echo $paginador->formatRow();
                        ?>
                    </tr>
                <?php }
                ?> 
            </table>
        </div>
        <?php
        echo $paginador->footer(false);
        echo $paginador->filter();
        echo "<div class='mensajes'>$Msj</div>";
        BordeSuperiorCerrar();
        PieDePagina();
        ?>


    </body>
</html>