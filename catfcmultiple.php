<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("data/FcDAO.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$mysqli = iconnect();

$arrayFilter = array("Cliente" => $request->getAttribute("Cliente"), "FcOrigen" => $request->getAttribute("FcOrigen"));
$nameSession = "catalogoCDFIRelacionado";
$session = new OmicromSession("fc.folio", "fc.folio", $nameSession, $arrayFilter, "Cliente");

foreach ($arrayFilter as $key => $value) {
    ${$key} = utils\HTTPUtils::getSessionBiValue($nameSession, $key);
}
$Id = 53;
$Titulo = "CFDI relacionado del cliente " . $Cliente;

$conditions = "fc.cliente = '" . $Cliente . "' AND fc.status IN (" . StatusFactura::CERRADO . "," . StatusFactura::CANCELADO . ")";

$cSql = "  (
                SELECT fc.serie, fc.id, fc.fecha, fc.cliente, fc.cantidad, fc.total, fc.uuid, fc.status, fc.folio,'FAC' tipo,'fc' tabla,fc.usr,relacion_cfdi.id_fc
                FROM fc LEFT JOIN (
		SELECT id_fc,uuid_relacionado,importe FROM relacion_cfdi
                    ) relacion_cfdi 
                ON relacion_cfdi.uuid_relacionado = fc.uuid
                WHERE TRUE AND fc.uuid <> '-----' AND $conditions
            ) fc ";
$paginador = new Paginador($Id,
        "fc.serie,fc.folio,fc.fecha,fc.cliente,fc.cantidad,fc.total,fc.uuid,fc.status,fc.id,fc.tipo,IF(fc.tipo = 'FAC','Factura','Anticipo') tipoRelacion,fc.tabla,id_fc ",
        "LEFT JOIN cli ON fc.cliente = cli.id",
        "",
        "",
        $session->getSessionAttribute("sortField"),
        $session->getSessionAttribute("criteriaField"),
        utils\Utils::split($session->getSessionAttribute("criteria"), "|"),
        strtoupper($session->getSessionAttribute("sortType")),
        $session->getSessionAttribute("page"),
        "REGEXP",
        "",
        $cSql);

$self = utils\HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");
$cLink = substr($self, 0, strrpos($self, ".")) . 'e.php';
$cLinkd = substr($self, 0, strrpos($self, ".")) . 'd.php';
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            function setParentValue(id, uuid, serie, folio, fcOrigen) {
                jQuery.ajax({
                    type: 'GET',
                    url: 'getByAjax.php',
                    dataType: 'json',
                    cache: false,
                    data: {
                        "Op": "RelacionaFcFc",
                        "Id": id,
                        "IdFcOrigen": fcOrigen,
                        "Uuid": uuid,
                        'Serie': serie,
                        'Folio': folio
                    },
                    success: function (data) {
                        window.close();
                    },
                    error: function (jqXHR) {
                        console.log(jqXHR);
                    }
                });
            }
        </script>
        <?php $paginador->script(); ?>
    </head>

    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo) ?>
            <div id="TablaDatos">
                <table class="paginador" aria-hidden="true">
                    <?php
                    echo $paginador->headers(array(" "), array(" ", " "));
                    while ($paginador->next()) {
                        $row = $paginador->getDataRow();
                        if ($row["id_fc"] === NULL) {
                            ?>
                            <tr>
                                <td align='center'><a class='textosCualli' href=javascript:setParentValue('<?= $row["id"] ?>','<?= $row["uuid"] ?>','<?= $row["serie"] ?>','<?= $row["folio"] ?>','<?= $FcOrigen ?>');>seleccionar</a></td>
                                <?= $paginador->formatRow() ?>
                                <td align='left'><?= $row['tipoRelacion'] ?></td>
                                <td align='left'><?= $row['status'] ?></td>
                            </tr>
                            <?php
                        }
                    }
                    ?> 
                </table>
            </div>

            <?php
            echo $paginador->footer(false, false, false, false);
            echo $paginador->filter();
            ?>
        </div>
    </body>
</html>
