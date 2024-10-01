<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$session = new OmicromSession("pagosprv.id", "pagosprv.id");

$busca = $session->getSessionAttribute("criteria");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Titulo = "Pagos a proveedores";
$Id = 100;

$paginador = new Paginador($Id,
        "pagosprv.status",
        "LEFT JOIN prv ON pagosprv.proveedor = prv.id ",
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
                echo $paginador->headers(array("Editar", "Detalle", "Acuse"), array());
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr>
                        <td style="text-align: center;"><a href="<?= $cLink ?>?busca=<?= $row['id'] ?>"><i class="icon fa fa-lg fa-edit" aria-hidden="true"></i></a></td>
                        <td style="text-align: center;"><a href="<?= $cLinkd ?>?cVarVal=<?= $row["id"] ?>&criteria=ini"><i class="icon fa fa-lg fa-file-text" title="Detalle de pago" aria-hidden="true"></i></a></td>
                        <?php if ($row["status"] == "Cerrada") { ?>
                            <td style="text-align: center;"><a href=javascript:wingral("pdfreciboprv.php?busca=<?= $row["id"] ?>")><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></a></td>
                        <?php } else { ?>
                            <td style="text-align: center;"></td>
                        <?php } ?>    
                        <?php echo $paginador->formatRow(); ?>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>
        <?php
        $nLink = Array("<i class=\"icon fa fa-lg fa-plus-circle\" aria-hidden=\"true\"></i> ExportarXFecha" => "javascript:wingral('pidedatos.php?criteria=ini&busca=11')");
        echo $paginador->footer($usuarioSesion->getLevel() > 7,$nLink, false, true);
        echo $paginador->filter();
        echo "<div class='mensajes'>$Msj</div>";
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>