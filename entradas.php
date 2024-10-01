<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$nameSession = "catalogoPipasCapturadas";
$session = new OmicromSession("me.id", "me.id", $nameSession);

utils\HTTPUtils::setSessionObject("Tipo", 1);
$busca = $session->getSessionAttribute("criteria");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Titulo = "Pipas capturadas";
$Id = 18;

$paginador = new Paginador($Id,
        "me.carga,me.volumenfac*1000 volumenfac",
        "LEFT JOIN com ON me.producto = com.clave 
         LEFT JOIN cargas ON me.carga = cargas.id",
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
$cLink = substr($self, 0, strrpos($self, ".")) . 'ee.php';
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
                echo $paginador->headers(array("Editar", "Detalle", "Acuse", "Dct"), array("V.Fac.", "", ""));
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    $Dct = "SELECT * FROM dictamen WHERE noCarga = " . $row["id"];
                    $Dcts = utils\IConnection::execSql($Dct);
                    ?>
                    <tr>
                        <td style="text-align: center;">
                            <?php if ($usuarioSesion->getLevel() == UsuarioDAO::LEVEL_MASTER) { ?>
                                <a href="<?= $cLink ?>?busca=<?= $row["id"] ?>"><i class="icon fa fa-lg fa-edit" aria-hidden="true"></i></a>
                            <?php } ?>
                        </td>

                        <td style="text-align: center;"><a href="<?= $cLinkd ?>?criteria=ini&busca=<?= $row["id"] ?>"><i class="icon fa fa-lg fa-file-text" aria-hidden="true"></i></a></td>
                        <td style="text-align: center;"><a href=javascript:wingral("pdfentrada.php?busca=<?= $row["id"] ?>"); title="Imprimir recibo"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></a></td>
                        <td>
                            <?php if ($Dcts["id"] > 0) { ?>
                                <a href="dictamenese.php?busca=<?= $Dcts["id"] ?>&return=entradas.php?criteria=ini"><em class="icon fa fa-lg fa-print" aria-hidden="true"></em></a>
                            <?php } else { ?>
                                <a href="dictamenese.php?busca=NUEVO&IdE=<?= $row["id"] ?>&return=entradas.php?criteria=ini"><em class="fa-solid fa-download"></em></a>
                            <?php } ?>
                        </td>
                        <?php echo $paginador->formatRow(); ?>

                        <td style="text-align: right;"><?= number_format($row["volumenfac"], 1) ?></td>
                        <td style="text-align: center;"><a href=javascript:winmin("cpayuda.php?busca=<?= $row["carga"] ?>"); title="Ventas durante la descarga"><i class="icon fa fa-lg fa-file-text-o" aria-hidden="true"></i></a></td>
                        <td style="text-align: center;"><a href=javascript:winmin("cpconciliacion.php?busca=<?= $row["id"] ?>"); title="Conciliacion"><i class="icon fa fa-lg fa-gear" aria-hidden="true"></i></a></td>

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