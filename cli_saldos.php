<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();

$Titulo = "Saldo al " . date("Y-m-d");
$Cliente = utils\HTTPUtils::getSessionValue("Cuenta");

$selectSaldos = "SELECT * FROM (
                    SELECT cxc.cliente, cli.nombre, cli.tipodepago, cli.alias,
                    ROUND( SUM( CASE WHEN tm = 'C' THEN importe ELSE -importe END ), 2 ) importe,
                    CASE WHEN cli.tipodepago IN ('Prepago') THEN 2 ELSE 2 END orden
                    FROM cxc,cli
                    WHERE cxc.cliente = cli.id AND cli.tipodepago NOT REGEXP 'Contado|Puntos' 
                    AND cli.activo = 'Si' AND cxc.cliente = '$Cliente' 
                    GROUP BY cxc.cliente
                ) cxc";
$CpoA = $mysqli->query($selectSaldos);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php include './config_omicrom_clientes.php'; ?>  
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#autocomplete").focus();
            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(TRUE); ?>

        <div id="TablaDatos">
            <table aria-hidden="true">
                <tr style="text-align: center;">
                    <td class="fondoVerde">No.cuenta</td>
                    <td class="fondoVerde">Nombre</td>
                    <td class="fondoVerde">Saldo</td>
                </tr>

                <?php while ($rg = $CpoA->fetch_array()) { ?>
                    <tr>
                        <td style="text-align: center"><?= $rg["cliente"] ?></td>
                        <td><?= $rg["nombre"] ?></td>
                        <td style="text-align: right"><strong><?= number_format($rg["importe"], 2) ?></td>
                    </tr>
                <?php } ?>
            </table>
        </div>

        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>