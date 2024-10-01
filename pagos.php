<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require './services/PagosService.php';

$request = utils\HTTPUtils::getRequest();
$session = new OmicromSession("pagos.id", "pagos.id", $nameVariableSession);

$busca = $session->getSessionAttribute("criteria");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Id = 46;
$Titulo = "Módulo de pagos";

$paginador = new Paginador($Id,
        "cli.tipodepago,pagos.status_pago,pagos.status, pagos.statusCFDI, pagos.stCancelacion,
        IF(cli.tipodepago <> 'Monedero', pagos.uuid, IFNULL((SELECT fc.uuid FROM fc WHERE relacioncfdi = pagos.id LIMIT 1), '-----')) uuid",
        "LEFT JOIN cli ON pagos.cliente = cli.id",
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
$cLink = substr($self, 0, strrpos($self, ".")) . 'e33.php';
$cLinkd = substr($self, 0, strrpos($self, ".")) . 'd33.php';
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
            function winieps(url) {
                window.open(url, 'miniwin', 'width=400,height=200,left=200,top=120,location=no');
            }
        </script>
        <?php $paginador->script(); ?>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <div id="TablaDatos">
            <table class="paginador" aria-hidden="true">
                <?php
                echo $paginador->headers(array("Editar", "Detalle", "Acuse", "Pdf", "Xml"), array("CFDI", "Tipo", " "));
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr>
                        <td style="text-align: center;"><a href="<?= $cLink ?>?busca=<?= $row["id"] ?>"><i class="icon fa fa-lg fa-edit" aria-hidden="true"></i></a></td>
                        <td style="text-align: center;"><a href="<?= $cLinkd ?>?cVarVal=<?= $row["id"] ?>&criteria=ini"><i class="icon fa fa-lg fa-file-text" title="Detalle de pago" aria-hidden="true"></i></a></td>
                        <?php if ($row["status"] === StatusPago::CERRADO) { ?>
                            <td style="text-align: center;"><a href=javascript:wingral("pdfrecibo.php?busca=<?= $row["id"] ?>")><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></a></td>
                        <?php } else { ?>
                            <td style="text-align: center;"></td>
                        <?php } ?>

                        <?php if (!empty($row["uuid"]) && $row["uuid"] !== PagoDAO::SIN_TIMBRAR) { ?>
                            <td style="text-align: center;">
                                <?php if ($row["statusCFDI"] != StatusPagoCFDI::CANCELADO) { ?>
                                    <?php if ($row["tipodepago"] === TiposCliente::PREPAGO) { ?>
                                        <a style="color: red;" href=javascript:winuni("enviafile.php?id=<?= $row["uuid"] ?>&type=pdf&formato=3")><i class="icon fa fa-lg fa-file-pdf-o" title="Obtener PDF Tamaño Carta" aria-hidden="true"></i></i></a>
                                    <?php } else { ?>
                                        <a style="color: red;" href=javascript:winuni("enviafile.php?id=<?= $row["uuid"] ?>&type=pdf")><i class="icon fa fa-lg fa-file-pdf-o" title="Obtener PDF Tamaño Carta" aria-hidden="true"></i></a>
                                    <?php } ?>
                                <?php } else { ?>
                                    <a style="color: red;" href=javascript:winuni("acusecanpdf.php?table=pagos&busca=<?= $row["id"] ?>")><i class="icon fa fa-lg fa-file-pdf-o" alt="Obtener Acuse de Cancelaci&oacute;" aria-hidden="true"></i></a>
                                <?php } ?>
                            </td>
                            <td><a href=javascript:winuni("enviafile.php?id=<?= $row["uuid"] ?>&type=xml")><i class="icon fa fa-lg fa-file-code-o" alt="Obten archivo xml" aria-hidden="true"></i></a></td>
                        <?php } else { ?>
                            <td style="text-align: center;"></td>
                            <td style="text-align: center;"></td>
                        <?php } ?>

                        <?php echo $paginador->formatRow(); ?>

                        <td style="text-align: center;">
                            <?php if ($row["tipodepago"] === TiposCliente::PREPAGO || $row["tipodepago"] === TiposCliente::CREDITO || $row["tipodepago"] === TiposCliente::CONSIGNACION) { ?>
                                <a href="cantimbrepago.php?busca=<?= $row["id"] ?>"><?= statusCFDI($row["statusCFDI"]) ?>
                                    <?php if ($row["statusCFDI"] == StatusPagoCFDI::CANCELADO && $row["stCancelacion"] != StatusCancelacionFactura::CANCELADA_CONFIRMADA) { ?>
                                        <i class="icon fa fa-lg fa-clock-o" aria-hidden="true"></i>
                                    <?php } ?>
                                </a>
                            <?php } else { ?>
                                <?= $row["status"] ?>
                            <?php } ?>
                        </td>
                        <td style="text-align: center;"><?= $row["tipodepago"] ?></td>
                        <?php if ($row["status_pago"] == StatusPagoPrepago::CON_NOTA_CREDITO) { ?>
                            <td><img src="libnvo/verde.png" height="15" alt="Pago completo"></td>
                        <?php } else { ?>
                            <td><img src="libnvo/amarillo.png" height="15" alt="Pago incompleto"></td>
                        <?php } ?>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>
        <?php
        $nLink = Array("<i class=\"icon fa fa-lg fa-plus-circle\" aria-hidden=\"true\"></i> Agregar" => "pagose33.php?id=NUEVO",
            "<i class=\"fa-regular fa-file-lines\"></i> Exportar por fecha" => "javascript:wingral('pidedatos.php?criteria=ini&busca=7')");
        echo $paginador->footer(false, $nLink, true, true);
        echo $paginador->filter();
        echo "<div class='mensajes'>$Msj</div>";
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>