
<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

include_once ("data/NcDAO.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$session = new OmicromSession("nc.id", "nc.id");

$busca = $session->getSessionAttribute("criteria");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Id = 74;
$Titulo = "Notas de credito";

$paginador = new Paginador($Id,
        "nc.uuid,nc.status",
        "LEFT JOIN cli ON nc.cliente = cli.id",
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
                echo $paginador->headers(array("Editar", "Detalle", "Pdf", "Xml"), array("Status"));
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr>
                        <td style="text-align: center;"><a href="<?= $cLink ?>?busca=<?= $row['id'] ?>"><i class="icon fa fa-lg fa-edit" aria-hidden="true"></i></a></td>
                        <td style="text-align: center;"><a href="<?= $cLinkd ?>?criteria=ini&cVarVal=<?= $row['id'] ?>"><i class="icon fa fa-lg fa-file-text" aria-hidden="true"></i></a></td>

                        <?php if (!empty($row["uuid"]) && $row["uuid"] !== NcDAO::SIN_TIMBRAR) { ?>
                            <td style="text-align: center;">
                                <?php if ($row["status"] != StatusNotaCredito::CANCELADO) { ?>
                                    <a style="color: red;" href=javascript:winuni("enviafile.php?id=<?= $row["uuid"] ?>&type=pdf")><i class="icon fa fa-lg fa-file-pdf-o" title="Obtener PDF Tamaño Carta" aria-hidden="true"></i></a>
                                <?php } else { ?>
                                    <a style="color: red;" href=javascript:winuni("acusecanpdf.php?table=nc&busca=<?= $row["id"] ?>")><i class="icon fa fa-lg fa-file-pdf-o" alt="Obtener Acuse de Cancelaci&oacute;" aria-hidden="true"></i></a>
                                <?php } ?>
                            </td>
                            <td style="text-align: center;"><a href=javascript:winuni("enviafile.php?id=<?= $row["uuid"] ?>&type=xml")><i class="icon fa fa-lg fa-file-code-o" alt="Obten archivo xml" aria-hidden="true"></i></a></td>

                        <?php } else { ?>
                            <td style="text-align: center;"> </td>
                            <td style="text-align: center;"> </td>
                        <?php } ?>

                        <?php echo $paginador->formatRow(); ?>

                        <td style="text-align: center;"><a class="textosCualli" href="cannotascre.php?busca=<?= $row["id"] ?>"><?= statusCFDI($row["status"]) ?></a></td>

                    </tr>
                <?php } ?>
            </table>
        </div>
        <?php
        $nlink = Array("<i class=\"icon fa fa-lg fa-plus-circle\" aria-hidden=\"true\"></i> ExportarXFecha" => "javascript:wingral('pidedatos.php?criteria=ini&busca=8')");
        echo $paginador->footer($usuarioSesion->getLevel() >= 7, $nlink, false, true);
        echo $paginador->filter();
        echo "<div class='mensajes'>$Msj</div>";
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>