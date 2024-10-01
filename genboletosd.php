<?php
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("comboBoxes.php");

use com\softcoatl\utils as utils;

$session = new OmicromSession("genbold.precio", "genbold.precio");

$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$self = utils\HTTPUtils::self();

require './services/GeneraValesService.php';

$Titulo = "Desgloce de vales";
$Id = 72;

$paginador = new Paginador($Id,
        "genbold.idnvo",
        "",
        "",
        "genbold.id = '$cVarVal'",
        $session->getSessionAttribute("sortField"),
        $session->getSessionAttribute("criteriaField"),
        utils\Utils::split($session->getSessionAttribute("criteria"), "|"),
        strtoupper($session->getSessionAttribute("sortType")),
        $session->getSessionAttribute("page"),
        "REGEXP",
        "genboletos.php?criteria=ini");


$sqlBol = "
        SELECT genbol.id,genbol.fecha,genbol.cliente,cli.nombre,genbol.status,genbol.fechav,genbol.cantidad,genbol.importe,
        IFNULL(SUM(genbold.precio * genbold.boletos) , 0) importe_vales
        FROM cli, genbol 
        LEFT JOIN genbold ON genbol.id = genbold.id
        WHERE genbol.cliente=cli.id  AND genbol.id = '$cVarVal'";
$HeA = $mysqli->query($sqlBol);
$He = $HeA->fetch_array();
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <?= $lBd ? "<meta http-equiv='refresh' content='2;url=genboletosd.php?op=Genera' />" : "" ?>
        <script>
            $(document).ready(function () {
                $("#Boletos").focus();
            });
        </script>
        <?php $paginador->script(); ?>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <div id="DatosEncabezado">
            <table aria-hidden="true">
                <tr>
                    <td align='left'> &nbsp; <strong>Id: </strong> <?= $cVarVal ?>&nbsp; </td>
                    <td align='left'> &nbsp; <?= $He["nombre"] ?>&nbsp; </td>
                    <td align='left'> &nbsp;  <strong>No.de boletos:</strong> <?= number_format($He["cantidad"], "0") ?>&nbsp; </td>
                </tr>
                <tr><td> &nbsp;</td>
                    <td align='left'> &nbsp; Fecha:<?= $He["fecha"] ?> &nbsp; Fecha Vencimiento: <?= $He["fechav"] ?> </td>
                    <td align='left'> &nbsp; <strong>Importe:</strong> <?= number_format($He["importe"], "2") ?> &nbsp; </td>
                </tr>
            </table>
        </div>

        <div id="TablaDatos">
            <table class="paginador" aria-hidden="true">
                <?php
                echo $paginador->headers(array(), array("Importe", "Borrar"));
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr>

                        <?php echo $paginador->formatRow(); ?>

                        <td style="text-align: right;"><?= number_format($row["boletos"] * $row["precio"], "2") ?></td>
                        <td style="text-align: center;">
                            <?php if ($He["status"] === StatusVales::ABIERTO) { ?>
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
        if ($He["status"] === StatusVales::ABIERTO && abs($He["importe"] - $He["importe_vales"]) < 0.5) {
            $nLink["<i class='icon fa fa-flag' aria-hidden=\"true\"></i> Generar vales y cerrar la orden <i class='icon fa fa-flag' aria-hidden=\"true\"></i>"] = "genboletosd.php?op=cr";
        }
        echo $paginador->footer(false, $nLink, false, false);
        ?>

        <?php
        if ($He["status"] === StatusVales::ABIERTO) {
            if ($lBd) {
                ?>
                <div style="text-align: center;" class="texto_tablas">
                    <i class="fa fa-spinner fa-pulse fa-4x" aria-hidden="true"></i>
                    <span class="sr-only">Loading...</span>
                </div>

                <?php
            } else {
                ?>
                <form name='form1' method='get' action="">
                    <table style="width: 100%;margin-left: auto;margin-right: auto;" aria-hidden="true">
                        <tr style="background-color: #CACACA;height: 25px;" class='nombre_cliente'>
                            <?php
                            echo "<td>";
                            cInputDat("&nbsp; No.de  vales:", "number", "5", "Boletos", "-", 1, "7", false, false, " required='required' min='1' max='1000' placeholder=' 0'");
                            echo "</td>";
                            echo "<td>";
                            cInputDat("&nbsp; Importe por vale:", "Text", "10", "Precio", "-", '', "12", false, false, " required='required' placeholder='0.00'");
                            echo "</td>";
                            echo "<td>";
                            echo " <input type='submit' name='BotonD' value='Agregar' class='nombre_cliente'>";
                            echo "</td>";
                            ?>
                        </tr>
                    </table>
                    <input type='hidden' name='Boton' value='AgregarD'>
                </form>
                <?php
            }
        }
        if ($He["importe_vales"] > $He["importe"]) {
            echo "<div class='mensajes'>La suma de vales a generar debe ser menor o igual a $" . $He["importe"] . "</div>";
        }
        echo "<div class='mensajes'>$Msj</div>";
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>
