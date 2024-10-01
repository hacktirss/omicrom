<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$nameSession = "catalogoPipasCapturadasDetalle";
$session = new OmicromSession("med.idnvo", "med.idnvo", $nameSession);

if ($request->hasAttribute("busca")) {
    utils\HTTPUtils::setSessionValue("cVarVal", $request->getAttribute("busca"));
}

$Titulo = "Detalle captura de pipas";
$Id = 20;
$busca = utils\HTTPUtils::getSessionValue("cVarVal");

$HeA = $mysqli->query("SELECT me.fecha,me.fechafac,me.uuid,me.producto,me.foliofac,me.volumenfac,me.terminal,me.clavevehiculo,me.documento,
  			  com.descripcion,me.importefac,me.status            
  			  FROM me LEFT JOIN com ON me.producto=com.clave 
  			  WHERE me.id='$busca'");

$He = $HeA->fetch_array();

$paginador = new Paginador($Id,
        "med.idnvo",
        "LEFT JOIN inv ON med.clave = inv.id",
        "",
        "med.id='$busca'",
        $session->getSessionAttribute("sortField"),
        $session->getSessionAttribute("criteriaField"),
        utils\Utils::split($session->getSessionAttribute("criteria"), "|"),
        strtoupper($session->getSessionAttribute("sortType")),
        $session->getSessionAttribute("page"),
        "REGEXP",
        "entradas.php");

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

        <div style="width: 98%;margin-left: auto;margin-right: auto;border: 2px solid gray;margin-bottom: 10px;padding: 3px 1px;">
            <table style="width: 98%;margin-left: auto;margin-right: auto;" class="texto_tablas" aria-hidden="true">
                <tr style="background-color: #E1E1E1;height: 20px;">
                    <td> &nbsp; <strong>No.entrada:</strong><?= $busca ?> </td><td> &nbsp; <strong>Fecha:</strong> <?= $He["fecha"] ?></td>
                    <td> &nbsp; <strong>Fec.Factura:</strong> <?= $He["fechafac"] ?> </td>
                </tr>
                <tr style="background-color: #E1E1E1;height: 20px;">
                    <td> &nbsp; <strong>Folio factura:</strong> <?= $He["foliofac"] ?>&nbsp; <strong>Terminal:</strong> <?= $He["terminal"] ?> </td><td>&nbsp; <strong>Clv.vehiculo:</strong> <?= $He["clavevehiculo"] ?></td>
                    <td> &nbsp; <strong>Docto:</strong> <?= $He["documento"] ?> </td>
                </tr>
                <tr style="background-color: #E1E1E1;height: 20px;">
                    <td> &nbsp; <strong>Producto:</strong> <?= $He["producto"] . " " . $He["descripcion"] ?></td><td>&nbsp; <strong>Cantidad:</strong> <?= number_format($He["volumenfac"], "2") ?></td>
                    <td> &nbsp; <strong>Imp.factura: $</strong> <?= number_format($He["importefac"], "2") ?> &nbsp; </td>
                </tr>
                <tr style="background-color: #E1E1E1;height: 20px;">
                    <td colspan="3"> &nbsp;  <strong>UUID:</strong> <?= $He["uuid"] ?></td>
                </tr>
            </table>
        </div>

        <div id="TablaDatos">
            <table class="paginador" aria-hidden="true">
                <?php
                echo $paginador->headers(array(), array());
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();

                    echo "<tr>";

                    echo $paginador->formatRow();

                    echo "</tr>";
                }
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