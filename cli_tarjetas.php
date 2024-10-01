<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$session = new OmicromSession("unidades.codigo", "unidades.codigo");

$Cliente = utils\HTTPUtils::getSessionValue("Cuenta");

$busca = $session->getSessionAttribute("criteria");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Id = 64;
$Titulo = "Catalogo de tarjetas y unidades";

$paginador = new Paginador($Id,
        "unidades.id",
        "LEFT JOIN cli ON unidades.cliente = cli.id",
        "",
        "unidades.cliente = $Cliente",
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
        <?php include './config_omicrom_clientes.php'; ?>   
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#autocomplete").focus();
            });
        </script>
        <?php $paginador->script(); ?>
    </head>

    <body>

        <?php BordeSuperior(true); ?>

        <div id="TablaDatos">
            <table class="paginador" aria-hidden="true">
                <?php
                echo $paginador->headers(array("Editar"), array());
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr>                        
                        <td style="text-align: center;"><a href="<?= $cLinkd ?>?busca=<?= $row["id"] ?>"><i class="icon fa fa-lg fa-edit" aria-hidden="true"></i></a></td>
                                <?php echo $paginador->formatRow(); ?>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>
        <?php
        $nLink = array("<i class='fa-solid fa-money-bill-transfer'></i> Transferencias" => "cli_tarjetast.php");

        echo $paginador->footer(false, $nLink, false, true);
        echo $paginador->filter();
        echo "<div class='mensajes'>$Msj</div>";
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>