<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "services/PagosDespachadordService.php";

$request = utils\HTTPUtils::getRequest();
$session = new OmicromSession("p.id", "p.id", $nameVariableSession);

$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));
$Titulo = "Faltantes que afecta el presente pago";
$Id = 34;

$cSql = "SELECT * FROM (
            SELECT pagosdespd.id, pagosdespd.pago, pagosdespd.referencia, cxd.fecha, cxd.concepto, cxd.importe total,pagosdespd.importe, sub.pagado
            FROM pagosdesp,pagosdespd,cxd, (SELECT IFNULL(SUM(importe) , 0) pagado,referencia, id FROM pagosdespd GROUP BY referencia) AS sub
            WHERE pagosdesp.id = pagosdespd.pago
            AND pagosdespd.referencia = cxd.referencia 
            AND pagosdespd.referencia = sub.referencia 
            GROUP BY pagosdesp.id, pagosdespd.referencia
        ) pagosdesp
        WHERE pagosdesp.pago = $cVarVal";

$paginador = new Paginador($Id,
        "p.status",
        "LEFT JOIN ven ON p.vendedor = ven.id  ",
        "",
        "",
        $session->getSessionAttribute("sortField"),
        $session->getSessionAttribute("criteriaField"),
        utils\Utils::split($session->getSessionAttribute("criteria"), "|"),
        strtoupper($session->getSessionAttribute("sortType")),
        $session->getSessionAttribute("page"),
        "REGEXP",
        "pagosdesp.php");

$registros = utils\IConnection::getRowsFromQuery($cSql);

$self = utils\HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <div id="DatosEncabezado">
            <table aria-hidden="true">
                <tr>
                    <td>Id:  <?= $cVarVal ?></td>
                    <td><?= $He["vendedor"] . " | " . $He["nombre"] ?></td>
                    <td><?= $He["fecha"] ?></td>
                </tr>
                <tr>
                    <td>Status: <?= statusLetra($He["status"]) ?> </td>
                    <td>Importe pagado por el despachador: <strong><?= number_format($He["importe"], 2) ?></strong></td>
                    <td style="font-weight: bold;">Saldo por aplicar: <?= number_format($He["importe"] - $Hed["total"], 2); ?></td>
                <tr>
            </table>
        </div>

        <div id="TablaDatos">
            <table aria-hidden="true">
                <tr>
                    <td class="fondoVerde">Referencia</td>
                    <td class="fondoVerde">Fecha</td>
                    <td class="fondoVerde">Concepto</td>
                    <td class="fondoVerde">Importe</td>
                    <td class="fondoVerde">Abono</td>
                    <td class="fondoVerde">Saldo</td>
                    <td class="fondoVerde">Borrar</td>
                </tr>

                <?php
                $ImpDoc = $He["importe"];
                $Imp = 0;
                foreach ($registros as $rg) {
                    ?>
                    <tr>
                        <td style="text-align: center;"><?= $rg["referencia"] ?></td>
                        <td><?= $rg["fecha"] ?></td>
                        <td><?= $rg["concepto"] ?></td>
                        <td style="text-align: right;"><?= $rg["total"] ?></td>
                        <td style="text-align: right;"><?= $rg["importe"] ?></td>
                        <td style="text-align: right;"><?= number_format($rg["total"] - $rg["pagado"], 2) ?></td>

                        <td style="text-align: center;">
                            <?php if ($He["status"] == StatusPagoDespachador::ABIERTO) { ?>
                                <a href=javascript:borrarRegistro("<?= $self ?>",<?= $rg["id"] ?>,"cId"); class="textosCualli"><i class="icon fa fa-lg fa-trash" aria-hidden="true"></i></a>
                                <?php } ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>

        <?php
        $nLink = array();
        if ($He["status"] == StatusPagoDespachador::ABIERTO) {
            if (abs($He["importe"] - $Hed["total"]) < 1) {
                $nLink["<i class='icon fa fa-flag parpadea' aria-hidden=\"true\"></i> Documento cuadrado, da click aqui para cerrarla <i class='icon fa fa-flag parpadea' aria-hidden=\"true\"></i>"] = "$self?op=Cerrar";
            } else {
                $returnLink = urlencode("pagosdespd.php");
                $backLink = urlencode("pagosdespd.php");
                $nLink["<i class='icon fa fa-lg fa-plus-circle' aria-hidden=\"true\"></i> Agregar faltante"] = "catpagosdesp.php?criteria=ini&backLink=$backLink&returnLink=$returnLink";
            }
        }
        echo $paginador->footer(false, $nLink, false, false, 0, false);

        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>