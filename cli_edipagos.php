<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require './services/PagosService.php';

$request = utils\HTTPUtils::getRequest();
$session = new OmicromSession("pagos.id", "pagos.id");

$Cliente = utils\HTTPUtils::getSessionValue("Cuenta");
$busca = $session->getSessionAttribute("criteria");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Id = 46;
$Titulo = "Módulo de pagos";
$CliTp = "SELECT tipodepago FROM cli WHERE id = " . $Cliente;
$cliRtp = utils\IConnection::execSql($CliTp);
$paginador = new Paginador($Id,
        "cli.tipodepago,pagos.status_pago,pagos.status, pagos.uuid, pagos.statusCFDI, pagos.stCancelacion",
        "LEFT JOIN cli ON pagos.cliente = cli.id",
        "",
        "pagos.cliente = $Cliente",
        $session->getSessionAttribute("sortField"),
        $session->getSessionAttribute("criteriaField"),
        utils\Utils::split($session->getSessionAttribute("criteria"), "|"),
        strtoupper($session->getSessionAttribute("sortType")),
        $session->getSessionAttribute("page"),
        "REGEXP",
        "");

$self = utils\HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_clientes.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#autocomplete").focus();
            });
        </script>
        <?php $paginador->script(); ?>
    </head>

    <body>

        <?php BordeSuperior(TRUE); ?>

        <div id="TablaDatos">
            <table class="paginador" aria-hidden="true">
                <?php
                if ($cliRtp["tipodepago"] === "Prepago") {
                    echo $paginador->headers(array("Pdf", "Xml"), array("CFDI", "Tipo", " ", " "));
                } else {
                    echo $paginador->headers(array("Pdf", "Xml"), array("CFDI", "Tipo", " "));
                }
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr>

                        <?php if (!empty($row["uuid"]) && $row["uuid"] !== PagoDAO::SIN_TIMBRAR) { ?>
                            <td style="text-align: center;">
                                <?php if ($row["statusCFDI"] != StatusPagoCFDI::CANCELADO) { ?>
                                    <?php if ($row["tipodepago"] === TiposCliente::PREPAGO) { ?>
                                        <a style="color: red;" href=javascript:winuni("enviafile.php?id=<?= $row["uuid"] ?>&type=pdf&formato=3")><em class="icon fa fa-lg fa-file-pdf-o" title="Obtener PDF Tamaño Carta" aria-hidden="true"></i></a>
                                    <?php } else { ?>
                                        <a style="color: red;" href=javascript:winuni("enviafile.php?id=<?= $row["uuid"] ?>&type=pdf")><em class="icon fa fa-lg fa-file-pdf-o" title="Obtener PDF Tamaño Carta" aria-hidden="true"></em></a>
                                    <?php } ?>
                                <?php } else { ?>
                                    <a style="color: red;" href=javascript:winuni("acusecanpdf.php?table=pagos&busca=<?= $row["id"] ?>")><em class="icon fa fa-lg fa-file-pdf-o" alt="Obtener Acuse de Cancelaci&oacute;" aria-hidden="true"></em></a>
                                <?php } ?>
                            </td>
                            <td><a href=javascript:winuni("enviafile.php?id=<?= $row["uuid"] ?>&type=xml")><em class="icon fa fa-lg fa-file-code-o" alt="Obten archivo xml" aria-hidden="true"></em></a></td>
                        <?php } else { ?>
                            <td style="text-align: center;"></td>
                            <td style="text-align: center;"></td>
                        <?php } ?>

                        <?php echo $paginador->formatRow(); ?>

                        <td style="text-align: center;">
                            <?= statusCFDI($row["statusCFDI"]) ?>
                            <?php if ($row["statusCFDI"] == StatusPagoCFDI::CANCELADO && $row["stCancelacion"] != StatusCancelacionFactura::CANCELADA_CONFIRMADA) { ?>
                                <em class="icon fa fa-lg fa-question" aria-hidden="true"></em>
                            <?php } ?>
                        </td>
                        <td style="text-align: center;"><?= $row["tipodepago"] ?></td>
                        <?php if ($row["status_pago"] == StatusPagoPrepago::CON_NOTA_CREDITO) { ?>
                            <td><img src="libnvo/verde.png" height="15" alt="Incompleto"></td>
                        <?php } else { ?>
                            <td><img src="libnvo/amarillo.png" height="15" alt="Completo"></td>
                        <?php } ?>
                        <?php
                        if ($cliRtp["tipodepago"] === "Prepago") {
                            ?>
                            <td>
                                <?php
                                if ($row["status_pago"] == 2) {
                                    ?>
                                    <a href="cli_edipagost.php?busca=<?= $row["id"] ?>&criteria=ini">
                                        <em class = 'fa-solid fa-money-bill-transfer fa-lg' style="color: #1E8449"></em>
                                    </a>
                                    <?php
                                }
                                ?>
                            </td>
                            <?php
                        }
                        ?>
                    </tr>
                <?php }
                ?>
            </table>
        </div>

        <?php
        echo $paginador->footer(false, null, false, true);
        echo $paginador->filter();
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>