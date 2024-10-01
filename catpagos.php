<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ('data/PagoDAO.php');

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$mysqli = iconnect();

$selectPagos = "SELECT pagos.id, DATE(pagos.fecha) fecha, pagos.cliente, cli.nombre, pagos.importe, pagos.status 
        FROM pagos LEFT JOIN cli ON pagos.cliente = cli.id
        WHERE pagos.cliente = '" . $request->getAttribute("cliente") . "' AND pagos.uuid <> '" . PagoDAO::SIN_TIMBRAR . "'
        AND pagos.statusCFDI IN (" . StatusPagoCFDI::CANCELADO. "," . StatusPagoCFDI::CERRADO. ")
        ORDER BY pagos.id";

//error_log($selectPagos);
$registros = utils\IConnection::getRowsFromQuery($selectPagos);

$Titulo = "Recibos cancelados del cliente: " . $request->getAttribute("cliente");
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <title><?= $Gcia ?></title>
        <script>
            function setParentValue(id) {
                window.opener.document.getElementById('Relacioncfdi').value = id;
                window.close();
            }
        </script>
    </head>

    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo) ?>

            <div id="Reportes">
                 <table aria-hidden="true">
                    <thead>
                        <tr class="titulo">
                            <td colspan="6"><?= $Titulo ?></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Pago</td>
                            <td>Fecha</td>
                            <td>Cuenta</td>
                            <td>Nombre</td>
                            <td>Importe</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($registros as $rg) {
                            ?>
                            <tr>
                                <td><a class='textosCualli' href=javascript:setParentValue('<?= $rg["id"] ?>');>seleccionar</a></td>
                                <td><?= $rg["id"] ?></td>
                                <td><?= $rg["fecha"] ?></td>
                                <td><?= $rg["cliente"] ?></td>
                                <td><?= $rg["nombre"] ?></td>
                                <td><?= $rg["importe"] ?></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    </body>
</html>