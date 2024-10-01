<?php
#Librerias
session_start();

include_once ("check.php");
require_once ("libnvo/lib.php");
include_once ('data/FcDAO.php');

use com\softcoatl\utils as utils;

$request = utils\Request::instance();
$session = new OmicromSession("cxd.referencia", "cxd.referencia");

require_once "services/PagosDespachadordService.php";

$Id = 35;
$Titulo = "Faltantes pendientes de pago";

$cSql = " 
        SELECT sub.id, sub.vendedor,sub.referencia,SUM(sub.importe) saldo 
        FROM(
                SELECT cxd.id, cxd.vendedor,cxd.tm,cxd.referencia,IFNULL(pagosdespd.referencia,0) ref,
                ROUND(SUM(IF(cxd.tm = 'H',-cxd.importe,cxd.importe)),2) importe
                FROM cxd 
                LEFT JOIN pagosdespd ON cxd.referencia = pagosdespd.referencia AND pagosdespd.pago = $cVarVal
                WHERE cxd.vendedor = '" . $He["vendedor"] . "'
                GROUP BY cxd.referencia,cxd.tm
                ORDER BY cxd.referencia DESC
        ) sub 
        WHERE sub.ref = 0
        GROUP BY sub.referencia,sub.vendedor
        HAVING SUM(sub.importe) > 0 AND sub.referencia IS NOT NULL ";

$paginador = new Paginador($Id,
        "",
        "LEFT JOIN ($cSql) saldos ON cxd.id = saldos.id",
        "",
        "cxd.vendedor = ven.id AND saldos.vendedor = cxd.vendedor AND saldos.saldo > 0",
        $session->getSessionAttribute("sortField"),
        $session->getSessionAttribute("criteriaField"),
        utils\Utils::split($session->getSessionAttribute("criteria"), "|"),
        strtoupper($session->getSessionAttribute("sortType")),
        $session->getSessionAttribute("page"),
        "REGEXP",
        "");

$rLink = $session->getSessionAttribute("returnLink");
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <style>
            .thisButton{
                color: #006633;
            }
        </style>
    </head>

    <body>
        <?php BordeSuperior(); ?>

        <form name="form1" method="post" action="">
            <div id="TablaDatos">
                <table class="paginador" aria-hidden="true">
                    <?php
                    echo $paginador->headers(array(" "), array("Selector"));
                    while ($paginador->next()) {
                        $row = $paginador->getDataRow();
                        ?>
                        <tr>
                            <td class="alignCenter"><a href="<?= $rLink . "?Faltante=" . $row["referencia"] . "" ?>">seleccionar</a></td>
                            <?php echo $paginador->formatRow(); ?>
                            <td class="alignCenter"><input type="checkbox" name="Faltantes[]" value="<?= $row["referencia"] ?>"/></td>
                        </tr>
                        <?php
                    }
                    ?> 
                </table>
            </div>
            <div style="text-align: right;"><button class="thisButton">Agregar Seleccionados</button></div>
        </form>
        <?php
        $nLink = array();
        $nLink["<i class=\"icon fa fa-lg fa-arrow-circle-left\" aria-hidden=\"true\"></i> Regresar"] = $session->getSessionAttribute("backLink");
        echo $paginador->footer(false, $nLink, false, false);
        /* echo $paginador->filter(); */

        BordeSuperiorCerrar();
        PieDePagina();
        ?>
    </body>
</html>
