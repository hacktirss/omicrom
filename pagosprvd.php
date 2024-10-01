<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require 'services/PagosProveedordService.php';

$request = utils\HTTPUtils::getRequest();
$session = new OmicromSession("pagosprv.id", "pagosprv.id", $nameVariableSession);

$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));
$Titulo = "Facturas que afecta el presente pago";
$Id = 100;

$selectHe = "SELECT pagosprv.id,pagosprv.fecha,pagosprv.concepto,pagosprv.importe,pagosprv.status,pagosprv.proveedor,
        prv.nombre,prv.rfc,prv.id  
        FROM pagosprv LEFT JOIN prv ON pagosprv.proveedor=prv.id 
        WHERE pagosprv.id='$cVarVal'";
$He = utils\IConnection::execSql($selectHe);

$selectHed = "SELECT sum(importe) as total FROM pagosprvd WHERE id='$cVarVal'";
$Hed = utils\IConnection::execSql($selectHed);

$cSql = "SELECT * FROM 
        (
        SELECT pagosprvd.id pago,pagosprvd.factura compra,cxp.fecha,cxp.importe total,pagosprvd.importe,pagosprvd.idnvo,sub.pagado
        FROM pagosprv,pagosprvd,cxp, (SELECT IFNULL(SUM(importe) , 0) pagado,factura compra,idnvo FROM pagosprvd GROUP BY factura) AS sub
        WHERE pagosprv.id = pagosprvd.id 
        AND pagosprvd.factura = cxp.referencia 
        AND pagosprvd.factura = sub.compra 
        AND pagosprv.status='" . $He["status"] . "'
        GROUP BY pagosprvd.idnvo
        UNION
        SELECT pagosprvd.id pago,pagosprvd.factura compra,cxph.fecha,cxph.importe total,pagosprvd.importe,pagosprvd.idnvo,sub.pagado
        FROM pagosprv,pagosprvd,cxph, (SELECT IFNULL(SUM(importe) , 0) pagado,factura compra,idnvo FROM pagosprvd GROUP BY factura) AS sub
        WHERE pagosprv.id = pagosprvd.id 
        AND pagosprvd.factura = cxph.referencia 
        AND pagosprvd.factura = sub.compra 
        AND pagosprv.status='" . $He["status"] . "'
        GROUP BY pagosprvd.idnvo
        ) AS pag
        WHERE pag.pago = $cVarVal";

$paginador = new Paginador($Id,
        "pagosprv.status",
        "LEFT JOIN prv ON pagosprv.proveedor = prv.id ",
        "",
        "",
        $session->getSessionAttribute("sortField"),
        $session->getSessionAttribute("criteriaField"),
        utils\Utils::split($session->getSessionAttribute("criteria"), "|"),
        strtoupper($session->getSessionAttribute("sortType")),
        $session->getSessionAttribute("page"),
        "REGEXP",
        "pagosprv.php");

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
                    <td><?= $He["proveedor"] . " | " . $He["nombre"] ?></td>
                    <td><?= $He["fecha"] ?></td>
                </tr>
                <tr>
                    <td>RFC: <?= $He["rfc"] ?> </td>
                    <td>Importe pagado por el cliente:<?= number_format($He["importe"], "2") ?></td>
                    <td class='seleccionar' style="font-weight: bold;">Saldo por aplicar: <?= number_format($He["importe"] - $Hed["total"], "2"); ?></td>
                <tr>
            </table>
        </div>

        <div id="TablaDatos">
            <table aria-hidden="true">
                <tr>
                    <td class="fondoVerde">No.Compra</td>
                    <td class="fondoVerde">Fec.Compra</td>
                    <td class="fondoVerde">Importe/Compra</td>
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
                        <td><?= $rg["compra"] ?></td>
                        <td><?= $rg["fecha"] ?></td>
                        <td style="text-align: right;"><?= $rg["total"] ?></td>
                        <td style="text-align: right;"><?= $rg["importe"] ?></td>
                        <td style="text-align: right;"><?= number_format($rg["total"] - $rg["pagado"], 2) ?></td>

                        <td style="text-align: center;">
                            <?php if ($He["status"] !== "Cerrada") { ?>
                                <a href=javascript:borrarRegistro("<?= $self ?>",<?= $rg["idnvo"] ?>,"cId"); class="textosCualli"><i class="icon fa fa-lg fa-trash" aria-hidden="true""></i></a>
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
        if ($He["status"] == "Abierta") {
            if (abs($He["importe"] - $Hed["total"]) < 1) {
                $nLink["<i class='icon fa fa-flag' aria-hidden=\"true\"></i> Documento cuadrado, da click aqui para cerrarla <i class='icon fa fa-flag' aria-hidden=\"true\"></i>"] = "$self?op=Cerrar";
            } else {
                $nLink["<i class='icon fa fa-lg fa-plus-circle' aria-hidden=\"true\"></i> Agregar entrada"] = "catpagosprv.php?criteria=ini&Proveedor=" . $He["proveedor"] . "";
            }
        }
        echo $paginador->footer(false, $nLink, false, false, 0, false);

        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>