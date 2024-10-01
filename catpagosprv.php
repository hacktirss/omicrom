<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$session = new OmicromSession("pagosprv.id", "pagosprv.id");

$Titulo = "Compras pendientes de pago";
if($request->hasAttribute("Proveedor")){
    utils\HTTPUtils::setSessionValue("busca", $request->getAttribute("Proveedor"));
}
$Proveedor = utils\HTTPUtils::getSessionValue("busca");

$mysqli->query("DELETE FROM saldos");

$insertSaldos = "
                INSERT INTO saldos
                SELECT cxp.proveedor,referencia,sum( cxp.importe ) 
                FROM cxp 
                WHERE tm = 'C' AND referencia > 0 AND proveedor = '$Proveedor' 
                AND concepto NOT LIKE 'SALDO%'
                GROUP BY cxp.proveedor,cxp.referencia
                UNION 
                SELECT cxp.proveedor,referencia,sum( cxp.importe )*-1 
                FROM cxp 
                WHERE tm = 'H' AND referencia > 0 AND proveedor = '$Proveedor'
                AND concepto NOT LIKE 'SALDO%'
                GROUP BY cxp.proveedor,cxp.referencia
                ";

$mysqli->query($insertSaldos);

$selectPipas = " 
                SELECT saldos.referencia id,saldos.cliente proveedor,prv.nombre,
                CASE 
                WHEN prv.proveedorde = 'Combustibles' THEN me.fechae
                WHEN prv.proveedorde = 'Aceites' THEN et.fecha
                ELSE NOW()
                END fecha,
                CASE 
                WHEN prv.proveedorde = 'Combustibles' THEN me.status
                WHEN prv.proveedorde = 'Aceites' THEN et.status
                ELSE NOW()
                END status,
                ROUND(SUM(saldos.importe),2) importe
                FROM prv,saldos
                LEFT JOIN me ON me.proveedor = saldos.cliente AND saldos.referencia = me.id
                LEFT JOIN et ON et.proveedor = saldos.cliente AND saldos.referencia = et.id
                WHERE saldos.cliente = prv.id 
                GROUP BY saldos.referencia 
                HAVING SUM(saldos.importe) > 0;";

$registros = utils\IConnection::getRowsFromQuery($selectPipas);

$Id = 100;
$paginador = new Paginador($Id,
        "",
        "LEFT JOIN prv ON pagosprv.proveedor = prv.id ",
        "",
        "",
        $session->getSessionAttribute("sortField"),
        $session->getSessionAttribute("criteriaField"),
        utils\Utils::split($session->getSessionAttribute("criteria"), "|"),
        strtoupper($session->getSessionAttribute("sortType")),
        $session->getSessionAttribute("page"),
        "REGEXP",
        "pagosprvd.php");
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <div id="TablaDatos">
             <table aria-hidden="true">
                <tr>
                    <td class="fondoVerde"></td>
                    <td class="fondoVerde">Compra</td>
                    <td class="fondoVerde">Fecha</td>
                    <td class="fondoVerde">Cuenta</td>
                    <td class="fondoVerde">Proveedor</td>
                    <td class="fondoVerde">Importe</td>
                    <td class="fondoVerde">Status</td>
                </tr>

                <?php
                foreach ($registros as $rg) {
                    ?>
                    <tr>
                        <td><a class="textosCualli" href="pagosprvd.php?Entrada=<?= $rg["id"] ?>&Imp=<?= $rg["importe"] ?>">seleccionar</a></td>
                        <td><?= $rg["id"] ?></td>
                        <td><?= $rg["fecha"] ?></td>
                        <td><?= $Proveedor ?></td>
                        <td><?= $rg["nombre"] ?></td>
                        <td><?= $rg["importe"] ?></td>
                        <td><?= $rg["status"] ?></td>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>


        <?php
        echo $paginador->footer(false, false, false, false, 0, false);
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>