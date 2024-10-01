<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("data/mysqlUtils.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require './services/DispensariosService.php';

$request = utils\HTTPUtils::getRequest();
$session = new OmicromSession("man.posicion", "man.posicion");

$busca = $session->getSessionAttribute("criteria");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Id = 10;
$Titulo = "Dispensarios y posiciones";

$paginador = new Paginador($Id,
        "id",
        "",
        "",
        "man.activo = 'Si'",
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
                $("#FechaC").val("<?= date("Y-m-d", strtotime(date("Y-m-d") . "+ 6 month")) ?>")
            });
        </script>
        <?php $paginador->script(); ?>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <div id="TablaDatos">
            <table class="paginador" aria-hidden="true">
                <?php
                echo $paginador->headers(array("Editar", "Detalle"), array());
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr>
                        <td style="text-align: center;"></td>
                        <td style="text-align: center;"><a href="<?= $cLinkd ?>?cVarVal=<?= $row["id"] ?>&criteria=ini"><i class="icon fa fa-lg fa-file-text" aria-hidden="true"></i></a></td>
                        <td style="text-align: center;"><?= $row['posicion'] ?></td>
                        <td style="text-align: center;">DISP-<?= sprintf("%04d", $row['dispensario']) ?></td>
                        <td style="text-align: center;"><?= $row['isla'] ?></td>
                        <td style="text-align: center;"><?= $row['productos'] ?></td>
                        <td style="text-align: center;"><?= $row['activo'] ?></td>
                        <td style="text-align: center;"><?= $row['inventario'] ?></td>
                    </tr>
                    <?php
                }
                ?> 
            </table>
        </div>
        <?php
        $nLink = ["<i class='icon fa fa-lg fa-list-alt' aria-hidden=\"true\"></i> Visor de posiciones" => "javascript:winuni('vermanpro.php?busca=ini')",
                "<span data-toggle='modal' data-target='#modal-dispensarios' data-identificador='Dispensarios'><i class='icon fa fa-lg fa-pencil-square' aria-hidden=\"true\"></i> Configuraciones Generales</span>" => "javascript:void(0)"];
        echo $paginador->footer(false, $nLink, false);
        echo $paginador->filter();
        echo "<div class='mensajes'>$Msj</div>";
        BordeSuperiorCerrar();
        PieDePagina();
        ?>
        <link rel="stylesheet" href="bootstrap/bootstrap-4.0.0/dist/css/bootstrap-modal.css" type="text/css">
        <?php include_once ("./bootstrap/modals/modal_dispensarios.html"); ?>
        <script src="./bootstrap/controller/utils.js"></script>
        <script src="./bootstrap/controller/dispensarios.js"></script>
    </body>
</html>
