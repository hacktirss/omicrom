<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("data/PagosDespDAO.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$nameVariableSession = "CatalogoPagosDespachador";
$session = new OmicromSession("p.id", "p.id", $nameVariableSession);

$busca = $session->getSessionAttribute("criteria");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Titulo = "Pagos a despachadores";
$Id = 34;

$paginador = new Paginador($Id,
        "p.status, CASE p.status WHEN 2 THEN 'red' WHEN 1 THEN 'limegreen' ELSE 'yellow' END color, "
        . "CASE p.status WHEN 2 THEN 'Pago cancelado' WHEN 1 THEN 'Pago cerrado' ELSE 'Pago abierto' END mensaje",
        "LEFT JOIN ven ON p.vendedor = ven.id ",
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
                echo $paginador->headers(array("Editar", "Detalle", "Acuse"), array("Status"));
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr>
                        <td style="text-align: center;"><a href="<?= $cLink ?>?busca=<?= $row['id'] ?>"><i class="icon fa fa-lg fa-edit" aria-hidden="true"></i></a></td>
                        <td style="text-align: center;"><a href="<?= $cLinkd ?>?cVarVal=<?= $row["id"] ?>&criteria=ini"><i class="icon fa fa-lg fa-file-text" title="Detalle de pago" aria-hidden="true"></i></a></td>
                        <td style="text-align: center;">
                            <?php if ($row["status"] == StatusPagoDespachador::CERRADO) { ?>
                                <a href=javascript:wingral("pdfrecibodesp.php?busca=<?= $row["id"] ?>")><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></a>                       
                            <?php } ?>    
                        </td>
                        <?php echo $paginador->formatRow(); ?>
                        <td style="text-align: center;color: <?= $row["color"] ?>"><i class="icon fa fa-lg fa-circle" title="<?= $row["mensaje"] ?>" aria-hidden="true"></i></td>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>
        <?php
        echo $paginador->footer($usuarioSesion->getLevel() > 7, null, false, true);
        echo $paginador->filter();
        echo "<div class='mensajes'>$Msj</div>";
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>