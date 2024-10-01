<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "services/ComprasDiversasdService.php";

$request = utils\HTTPUtils::getRequest();
$session = new OmicromSession("etod.clave", "etod.clave", $nameVariableSession);

$Titulo = "Detalle de compra";

$Id = 106;
$paginador = new Paginador($Id,
        "etod.idnvo",
        "",
        "",
        "etod.id = '$cVarVal'",
        $session->getSessionAttribute("sortField"),
        $session->getSessionAttribute("criteriaField"),
        utils\Utils::split($session->getSessionAttribute("criteria"), "|"),
        strtoupper($session->getSessionAttribute("sortType")),
        $session->getSessionAttribute("page"),
        "REGEXP",
        "compraso.php");

$selectHe = "SELECT eto.fecha,eto.concepto,eto.proveedor,prv.alias,eto.status,
           eto.cantidad,eto.importe,eto.documento,prv.proveedorde            
           FROM eto LEFT JOIN prv ON eto.proveedor=prv.id 
           WHERE eto.id = '$cVarVal'";
$He = utils\IConnection::execSql($selectHe);

$selectCosto = "SELECT SUM(costo) costo FROM etod WHERE id = '$cVarVal'";
$CpoD = utils\IConnection::execSql($selectCosto);

$selectCuentas = "SELECT id,descripcion FROM cuentasm ORDER BY id";
$Cuentas = utils\IConnection::getRowsFromQuery($selectCuentas);

$self = utils\HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php include './config_omicrom.php'; ?>    
        <title><?= $Gcia ?></title>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <div style="width: 98%;margin-left: auto;margin-right: auto;border: 2px solid gray;margin-bottom: 10px;padding: 3px 1px;">
            <table style="width: 98%;margin-left: auto;margin-right: auto;" class="texto_tablas" aria-hidden="true">
                <tr style="background-color: #E1E1E1;height: 20px;">
                    <td><strong>No.entrada:</strong> <?= $cVarVal ?></td><td><strong>Fecha:</strong> <?= $He["fecha"] ?></td>
                    <td><strong>Docto:</strong> <?= $He["documento"] ?></td>
                </tr>
                <tr style="background-color: #E1E1E1;height: 20px;">
                    <td><strong>Proveedor:</strong> <?= $He["alias"] ?></td><td><strong>Concepto:</strong> <?= $He["concepto"] ?></td>
                    <td><strong>Importe:</strong> <?= number_format($He["importe"], 2) ?> </td>
                </tr>
            </table>
        </div>

        <div id="TablaDatos">
            <table class="paginador" aria-hidden="true">
                <?php
                echo $paginador->headers(array(), array("Borrar"));
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr>

                        <?php echo $paginador->formatRow(); ?>
                        <td style="text-align: center;">
                            <?php if ($He["status"] !== "Cerrada") { ?>
                                <a href=javascript:borrarRegistro("<?= $self ?>",<?= $row["idnvo"] ?>,"cId");><i class="icon fa fa-lg fa-trash" aria-hidden="true"></i></a>
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
            if (abs($He["importe"] - $CpoD["costo"]) < .5) {
                $nLink["<i class='icon fa fa-flag' aria-hidden=\"true\"></i> Documento cuadrado, da click aqui para cerrarla <i class='icon fa fa-flag' aria-hidden=\"true\"></i>"] = "$self?op=Cerrar";
            }
        }
        echo $paginador->footer(false, $nLink, false, false, 0, false);
        echo "<div class='mensajes'>$Msj</div>";
        ?>

        <?php
        if ($He["status"] == "Abierta") {
            if (abs($He["importe"] - $CpoD["costo"]) > .5) {
                ?>
                <form name="form1" method="get" action="">
                    <table style="width: 100%;background-color: #DADADA;margin-bottom: 10px;" class="texto_tablas" aria-hidden="true">
                        <tr>
                            <td>Cuenta: &nbsp;
                                <select name="Clave" class="nombre_cliente" class="texto_tablas">
                                    <?php
                                    foreach ($Cuentas as $rg) {
                                        echo "<option value='" . $rg["id"] . "'>" . ucwords(strtolower($rg["descripcion"])) . "</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td>Concepto: &nbsp; <input type="text" name="Concepto" id="Concepto" class="texto_tablas" required="required" placeholder="Breve descripcion">
                            </td>
                            <td>Costo: &nbsp;<input type="text" name="Costo" id="Costo" class="texto_tablas" style="width: 100px;"  required="required" placeholder="0.00">
                                &nbsp; <input class="nombre_cliente" type="submit" name="Boton" value="Agregar">
                            </td>
                        </tr>
                    </table>
                </form>
                <?php
            }
        }
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>
