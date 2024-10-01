<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$session = new OmicromSession("cp.id", "cp.id");

$busca = $session->getSessionAttribute("criteria");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Id = 24;
$Titulo = "Cambios de precio";

$paginador = new Paginador($Id,
        "cp.ejecucion",
        "",
        "",
        "",
        $session->getSessionAttribute("sortField"),
        $session->getSessionAttribute("criteriaField"),
        utils\Utils::split($session->getSessionAttribute("criteria"), "|"),
        strtoupper($session->getSessionAttribute("sortType")),
        $session->getSessionAttribute("page"),
        "REGEXP",
        "",
        "(SELECT cp.id,cp.fecha,com.descripcion producto,cp.fechaapli,cp.precio,cp.status , IFNULL(comandos.ejecucion,1) ejecucion 
          FROM cp 
          LEFT JOIN comandos ON cp.idtarea = comandos.idtarea
          LEFT JOIN com ON cp.producto = com.clavei
          WHERE TRUE   
          GROUP BY cp.id) cp");

$self = utils\HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");
$cLink = substr($self, 0, strrpos($self, ".")) . 'e.php';
$cLinkd = substr($self, 0, strrpos($self, ".")) . 'd.php';

require_once './services/CambioDePreciosService.php';
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
                echo $paginador->headers(array(), array("", ""));
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr>

                        <?php
                        echo $paginador->formatRow();

                        if ($row["ejecucion"] == 0) {
                            echo "<td align='center'>Pendiente</td>";
                        } elseif ($row["ejecucion"] == 1) {
                            echo "<td align='center'>Ejecutado</td>";
                        } elseif ($row["ejecucion"] == 2) {
                            echo "<td align='center'>Fallido</td>";
                        } elseif ($row["ejecucion"] == 9) {
                            echo "<td align='center'>Cancelado</td>";
                        } else {
                            echo "<td align='center'>Error</td>";
                        }
                        ?>

                        <td style="text-align: center;">
                            <?php if ($row["ejecucion"] == 0 && $row["status"] !== "Cancelado") { ?>
                                <a href=javascript:borrarRegistro("<?= $self ?>",<?= $row["id"] ?>,"cId");><i class="icon fa fa-lg fa-trash" aria-hidden="true"></i></a>
                                <?php } ?>
                        </td>

                    </tr>
                <?php }
                ?>
            </table>
        </div>
        <?php
        echo $paginador->footer($usuarioSesion->getLevel() >= 7, null, false, true);
        echo $paginador->filter();
        echo "<div class='mensajes'>$Msj</div>";
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>