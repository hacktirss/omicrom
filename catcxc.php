<?php
#Librerias
session_start();

include_once ("check.php");
require_once ("libnvo/lib.php");
include_once ('data/FcDAO.php');

use com\softcoatl\utils as utils;

$request = utils\Request::instance();
$session = new OmicromSession("fc.id", "fc.id");

require_once 'services/PagosdService.php';

$cVarVal = utils\HTTPUtils::getSessionBiValue($nameVariableSession, "cVarVal");
$Id = 53;

$pagoDAO = new PagoDAO();
$pagoVO = $pagoDAO->retrieve($cVarVal);

$Titulo = "Facturas pendientes de pago";

$cSql = "SELECT sub.cliente,sub.factura id,SUM(sub.importe) importe 
        FROM(
            SELECT cxc.cliente,cxc.tm,cxc.factura,IFNULL(pagose.factura,0) ref,
            ROUND(SUM(IF(cxc.tm = 'H',-cxc.importe,cxc.importe)),2) importe
            FROM cxc 
            LEFT JOIN pagose ON cxc.factura = pagose.factura AND pagose.id = '$cVarVal'
            WHERE cxc.cliente = '" . $pagoVO->getCliente() . "'
            GROUP BY cxc.factura,cxc.tm
            ORDER BY cxc.factura DESC
        ) sub 
        WHERE sub.ref = 0
        GROUP BY sub.factura,sub.cliente
        HAVING SUM(sub.importe) > 0 AND sub.factura IS NOT NULL";

$paginador = new Paginador($Id,
        "fc.id, saldos.importe saldo ",
        "LEFT JOIN cli ON fc.cliente = cli.id
        LEFT JOIN ($cSql) saldos ON fc.id = saldos.id",
        "",
        " fc.status = '" . StatusFactura::CERRADO . "' AND fc.cliente = '" . $pagoVO->getCliente() . "' AND saldos.importe > 0",
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
        <script>
            $(document).ready(function () {
                $("#checkall").change(function () {
                    $("input:checkbox").prop("checked", $(this).prop("checked"));
                });
            });
        </script>
        <style>
            .thisButton{
                color: #006633;
            }
        </style>
    </head>

    <body>
        <?php BordeSuperior(); ?>

        <form name="form1" method="post" action="pagosd33.php">
            <div id="TablaDatos">
                <table class="paginador" aria-hidden="true">
                    <?php
                    echo $paginador->headers(array(" "), array("Pagado", "Selector <input type='checkbox' class='botonAnimatedMin' id='checkall'>"));
                    $NameClase = "Count";
                    $NameClase0 = "Count0";
                    $n = 0;
                    while ($paginador->next()) {
                        $row = $paginador->getDataRow();
                        $Class = $NameClase . "" . $n;
                        $Class0 = $NameClase0 . "" . $n;
                        ?>
                        <tr class="<?= $Class0 ?>">
                            <td class="alignCenter"><a href="<?= $rLink . "?Factura=" . $row["id"] . "" ?>">seleccionar</a></td>
                            <?php echo $paginador->formatRow(); ?>
                            <td align="right"><?= $row["saldo"] ?></td>
                            <td class="alignCenter <?= $Class ?>"><input type="checkbox" class="botonAnimatedMin" name="Facturas[]" value="<?= $row["id"] ?>"/></td>
                        </tr>
                        <?php
                        $n++;
                    }
                    ?> 
                </table>
            </div>

            <table summary="Concentrado Total" width="100%" align="right">
                <tr><th scope="col" colspan="2"></th></tr>
                <tr style="text-align:right;font-family: Arial, Helvetica, sans-serif;font-size: 12px;font-weight: bold;">
                    <td align="right">Total :</td>
                    <td style="width:75px;"><div style="padding-left: 5px;padding-right: 20px;" class="sumChecks"></div></td>
                </tr>
            </table>
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
        <script type="text/javascript" src="libnvo/js/catcxc.js"></script>
    </body>
</html>
