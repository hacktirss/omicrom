<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$session = new OmicromSession("vh.id", "vh.id");

$busca = $session->getSessionAttribute("criteria");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Id = 145;
$Titulo = "Catalogo de Vehiculos varios";

$paginador = new Paginador($Id,
        "",
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

if (!empty($session->getSessionAttribute("returnLink"))) {
    $rLink = $session->getSessionAttribute("returnLink");
}

require_once './services/VehiculosService.php';
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
                if (empty($session->getSessionAttribute("returnLink"))) {
                    echo $paginador->headers(array("Editar"), array("Borrar"));
                    while ($paginador->next()) {
                        $row = $paginador->getDataRow();
                        ?>
                        <tr>
                            <td style="text-align: center;"><a href="<?= $cLink ?>?buscaV=<?= $row['id'] ?>"><i class="icon fa fa-lg fa-edit" aria-hidden="true"></i></a></td>
                            <?php echo $paginador->formatRow(); ?>
                            <td style="text-align: center;"><a href=javascript:borrarRegistro("<?= $self ?>",<?= $row["id"] ?>,"cId");><i class="icon fa fa-lg fa-trash" aria-hidden="true"></i></a></td>
                        </tr>
                        <?php
                    }
                } else {
                    echo $paginador->headers(array(" ",), array());
                    while ($paginador->next()) {
                        $row = $paginador->getDataRow();
                        ?>
                        <tr>
                            <td style="text-align: center;"><a href="<?= $rLink ?>&Vehiculo=<?= $row["id"] ?>">seleccionar</a></td>
                            <?php echo $paginador->formatRow(); ?>
                        </tr>
                        <?php
                    }
                }
                ?>
            </table>
        </div>
        <?php
        $nLink = array();
        if (!empty($session->getSessionAttribute("backLink"))) {
            $nLink["<i class=\"icon fa fa-lg fa-arrow-circle-left\" aria-hidden=\"true\"></i> Regresar"] = $session->getSessionAttribute("backLink");
        }
        echo $paginador->footer($usuarioSesion->getLevel() >= 7 && empty($session->getSessionAttribute("returnLink")), $nLink, false, true);
        echo $paginador->filter();
        echo "<div class='mensajes'>$Msj</div>";
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>