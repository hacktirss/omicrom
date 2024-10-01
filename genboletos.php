<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$session = new OmicromSession("genbol.id", "genbol.id");

$busca = $session->getSessionAttribute("criteria");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Titulo = "Generacion de vales";
$Id = 71;

$paginador = new Paginador($Id,
        "",
        "LEFT JOIN cli ON genbol.cliente = cli.id",
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
                echo $paginador->headers(array("Editar", "Detalle", "Vales", "Acuse"), array());
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr>
                        <td style="text-align: center;"><a href="<?= $cLink ?>?busca=<?= $row['id'] ?>"><i class="icon fa fa-lg fa-edit" aria-hidden="true"></i></a></td>
                        <td style="text-align: center;"><a href="<?= $cLinkd ?>?criteria=ini&cVarVal=<?= $row["id"] ?>" title="Detalle de la entrada"><i class="icon fa fa-lg fa-file-text" aria-hidden="true"></i></a></td>

                        <?php if ($row["status"] == "Cerrada") { ?>
                            <td style="text-align: center;">
                                <?php
                                $Var = utils\IConnection::execSql("SELECT valor FROM variables_corporativo WHERE llave = 'Vales_Sin_Img'");
                                if ($Var["valor"] == 1) {
                                    $Pagina = "pdfboletos2";
                                } elseif ($Var["valor"] == 2) {
                                    $Pagina = "pdfboletos3";
                                } else {
                                    $Pagina = "pdfboletos";
                                }
                                ?>
                                <a href=javascript:wingral("<?= $Pagina ?>.php?busca=<?= $row["id"] ?>") title="Imprimir boletos fisicos"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></a>
                            </td>
                        <?php } else { ?>
                            <td style="text-align: center;"></td>
                        <?php } ?>

                        <?php if ($row["status"] == "Cerrada") { ?>
                            <td style="text-align: center;"><a href=javascript:wingral("acusebol.php?busca=<?= $row["id"] ?>") title="Imprimir acuse de recibido"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></td>
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
        echo $paginador->footer($usuarioSesion->getLevel() >= 7, null, false, true);
        echo $paginador->filter();
        echo "<div class='mensajes'>$Msj</div>";
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>