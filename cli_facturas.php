<?php
#Librerias
session_start();
set_time_limit(300);

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ('data/FcDAO.php');

use com\softcoatl\utils as utils;

$request = utils\Request::instance();
$mysqli = iconnect();
$session = new OmicromSession("fc.id", "fc.id");
$Cliente = utils\HTTPUtils::getSessionValue("Cuenta");

$Id = 53;

$Titulo = "Modulo para facturacion";

$paginador = new Paginador($Id,
        "fc.id, fc.uuid, fc.status, fc.origen, fc.stCancelacion, cli.tipodepago, cli.rfc receptor",
        "LEFT JOIN cli ON fc.cliente = cli.id",
        "",
        "fc.cliente = '$Cliente' AND fc.status <> 'Abierta'",
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
        </script>
        <?php $paginador->script(); ?>
    </head>

    <body onload="pop();">
        <?php BordeSuperior(true); ?>

        <div id="TablaDatos">
            <table class="paginador" aria-hidden="true">
                <?php
                echo $paginador->headers(array("Pdf", "Xml"), array("Status"));
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    $SqlTvv = "SELECT ExtractValue(facturas.cfdi_xml, '/cfdi:Comprobante/cfdi:Receptor/@Rfc') vvl FROM omicrom.facturas where uuid = '" . $row["uuid"] . "'";
                    $Pass = utils\IConnection::execSql($SqlTvv);
                    if ($Pass["vvl"] !== "XAXX010101000") {
                        ?>
                        <tr>

                            <?php
                            if ($row['uuid'] !== FcDAO::SIN_TIMBRAR && !empty($row['uuid'])) {
                                if ($row['status'] !== StatusFactura::CANCELADO) {
                                    ?>
                                    <td style="white-space: nowrap; text-align: center;">
                                        <a style="color: red;" href="javascript:winuni('enviafile.php?id=<?= $row['uuid'] ?>&type=pdf&formato=0')"><i class="icon fa fa-lg fa-file-pdf-o" title="Obtener PDF Tamaño Carta" aria-hidden="true"></i></a>
                                        <a style="color: graytext;" href="javascript:winuni('enviafile.php?id=<?= $row['uuid'] ?>&type=pdf&formato=1')"><i class="icon fa fa-lg fa-file-pdf-o" title="Obtener PDF Formato Ticket" aria-hidden="true"></i></a>
                                    </td>
                                    <?php
                                } else {
                                    ?>
                                    <td align="center">
                                        <a style="color: red;" href="javascript:winuni('acusecanpdf.php?table=fc&busca=<?= $row['id'] ?>')">
                                            <i class="icon fa fa-lg fa-file-pdf-o" title="Obtener Acuse de Cancelación" aria-hidden="true"></i>
                                        </a>
                                    </td>
                                <?php }
                                ?>
                                <td align="center">
                                    <a href="javascript:winuni('enviafile.php?id=<?= $row['uuid'] ?>&type=xml')">
                                        <i class="icon fa fa-lg fa-file-code-o" title="Obtener archivo XML" aria-hidden="true"></i>
                                    </a>
                                </td>
                            <?php } else {
                                ?>
                                <td align="center" />
                                <td align="center" />
                                <?php
                            }
                            echo $paginador->formatRow();
                            ?>
                            <td align="left" width="150px">
                                <?= statusCFDI($row["status"]) ?>
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?> 
            </table>
        </div>

        <?php
        $nLink = array();
        echo $paginador->footer(false, $nLink, false, false);
        echo $paginador->filter();
        BordeSuperiorCerrar();
        PieDePagina();
        ?>
    </body>
</html>
