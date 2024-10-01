<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$session = new OmicromSession("et.id", "et.id");

$busca = $session->getSessionAttribute("criteria");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Titulo = "Entradas de aceites y otros";
$Id = 50;

$paginador = new Paginador($Id,
        "",
        "LEFT JOIN prv ON et.proveedor = prv.id",
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

require_once './services/ComprasService.php';
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
                echo $paginador->headers(array("Editar", "Detalle", "Acuse"), array("Cancelar"));
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr>
                        <td style="text-align: center;"><a href="<?= $cLink ?>?busca=<?= $row['id'] ?>"><i class="icon fa fa-lg fa-edit" aria-hidden="true"></i></a></td>
                        <td style="text-align: center;"><a href="<?= $cLinkd ?>?criteria=ini&cVarVal=<?= $row['id'] ?>"><i class="icon fa fa-lg fa-file-text" aria-hidden="true"></i></a></td>
                        <td style="text-align: center;">
                            <?php if ($row["status"] === StatusCompra::CERRADO) { ?>
                                <a href=javascript:wingral("impentaceites.php?busca=<?= $row["id"] ?>")><i class="icon fa fa-lg fa-print" aria-hidden="true"></i>
                                <?php } ?>
                        </td>

                        <?php echo $paginador->formatRow(); ?>

                        <td style="text-align: center;">
                            <?php if ($row["status"] !== StatusCompra::CANCELADO) { ?>
                                <a href=javascript:borrarRegistro("<?= $self ?>",<?= $row["id"] ?>,"cId");><i class="icon fa fa-lg fa-trash" aria-hidden="true"></i></a>
                                <?php } ?>
                        </td>
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
        $Msjx = getExternalMessage();
        if ($Msjx !== "") {
            $Clr = strstr($Msjx, "ERROR") ? "#F5B7B1" : "#ABEBC6";
            $Icon = strstr($Msjx, "ERROR") ? "error" : "success";
            $Time = strstr($Msjx, "ERROR") ? 100000 : 2000;
            $Error = false;
            $Error = strstr($Msjx, "1001") ? "1" : "0";
            ?>
            <script type="text/javascript">
                $(document).ready(function () {
                    Swal.fire({
                        title: "<?= $Msjx ?>",
                        background: "<?= $Clr ?>",
                        icon: "<?= $Icon ?>",
                        timer: <?= $Time ?>
                    });
                });
            </script>
            <?php
        }
        ?>

    </body>
</html>