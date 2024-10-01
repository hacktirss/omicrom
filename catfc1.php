<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("data/FcDAO.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$mysqli = iconnect();

$arrayFilter = array("Cliente" => $request->getAttribute("Cliente"));
$nameSession = "catalogoCDFIRelacionado";
$session = new OmicromSession("fc.folio", "fc.folio", $nameSession, $arrayFilter, "Cliente");

foreach ($arrayFilter as $key => $value) {
    ${$key} = utils\HTTPUtils::getSessionBiValue($nameSession, $key);
}

$Id = 53;
$Titulo = "CFDI relacionado del cliente " . $Cliente;

$conditions = "fc.cliente = '" . $Cliente . "' AND fc.status IN (" . StatusFactura::CERRADO . "," . StatusFactura::CANCELADO . ")";

$cSql = "  (
                SELECT fc.serie, fc.id, fc.fecha, fc.cliente, fc.cantidad, fc.total, fc.uuid, fc.status, fc.folio,'FAC' tipo,'fc' tabla,fc.usr
                FROM fc
                WHERE TRUE AND fc.uuid <> '-----' AND $conditions
                UNION ALL 
                SELECT 'MDEB' serie , p.id , p.fecha, p.cliente, 1 cantidad, p.importe total, p.uuid, p.statuscfdi status,p.id folio,'ANT' tipo,'pagos' tabla,'-' usr
                FROM pagos p 
                WHERE p.uuid <> '-----' AND p.status = 'Cerrada' AND p.cliente = '$Cliente' 
            ) fc ";

$paginador = new Paginador($Id,
        "fc.serie,fc.folio,fc.fecha,fc.cliente,fc.cantidad,fc.total,fc.uuid,fc.status,fc.id,fc.tipo,IF(fc.tipo = 'FAC','Factura','Anticipo') tipoRelacion,fc.tabla ",
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
            function setParentValue(id, folio, relacion) {
                window.opener.document.getElementById('Relacioncfdi').value = id;
                window.opener.document.getElementById('FolioRelacionado').value = folio;
                window.opener.document.getElementById('TipoRelacion').value = relacion;
                window.opener.document.getElementById('TipoR').innerHTML = relacion;
                if (relacion === "ANT") {
                    window.opener.document.getElementById('tiporelacion').value = '07';
                }
                window.close();
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

                        echo "<tr>";

                        echo "<td align='center'><a class='textosCualli' href=javascript:setParentValue(" . $row["id"] . "," . $row["folio"] . ",'" . $row["tipo"] . "');>seleccionar</a></td>";
                        echo $paginador->formatRow();

                        if ($row['tipo'] === "FAC") {
                            echo "<td align='left'>" . $row['tipoRelacion'] . "</td>";
                        } else {
                            echo "<td align='left'><font color='#e19494'>" . $row['tipoRelacion'] . "</td>";
                        }

                        echo "<td align='left'>" . $row['status'] . "</td>";

                        echo "</tr>";
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
