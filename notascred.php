<?php
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("comboBoxes.php");

use com\softcoatl\utils as utils;

require_once './services/NotasCreditodService.php';

$session = new OmicromSession("ncd.idnvo", "ncd.idnvo", $nameVariableSession);

$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Id = 75;
$Titulo = "Detalle de nota de credito";

$pacA = $mysqli->query("SELECT * FROM proveedor_pac WHERE activo = 1");
$pac = $pacA->fetch_array();

$paginador = new Paginador($Id,
        "ncd.idnvo",
        "LEFT JOIN inv ON ncd.producto = inv.id",
        "",
        "ncd.id = '$cVarVal'",
        $session->getSessionAttribute("sortField"),
        $session->getSessionAttribute("criteriaField"),
        utils\Utils::split($session->getSessionAttribute("criteria"), "|"),
        strtoupper($session->getSessionAttribute("sortType")),
        $session->getSessionAttribute("page"),
        "REGEXP",
        "notascre.php");

$ciaDAO = new CiaDAO();
$ciaVO = $ciaDAO->retrieve(1);

$HeA = $mysqli->query("SELECT nc.fecha,nc.fecha,nc.cliente,cli.nombre,nc.cantidad,nc.importe,nc.iva,nc.importe,nc.ieps,
                nc.status,nc.total,cli.rfc,cli.colonia,cli.municipio,cli.direccion, cli.numeroext,nc.factura,cli.tipodepago
                FROM nc LEFT JOIN cli ON nc.cliente=cli.id
                WHERE nc.id='$cVarVal'");

$He = $HeA->fetch_array();

$self = utils\HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#autocomplete").focus();
            });
        </script>
        <?php $paginador->script(); ?>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <?php if ($pac['pruebas'] == '1') { ?>
            <div style="background-color: red; color: white; text-align:center;margin-bottom: 5px; border-radius: 5px;font-family: Helvetica, Arial, Verdana, Tahoma, sans-serif; font-size:14px; font-weight:bold;">
                ALERTA FACTURANDO EN MODO DE DEMOSTRACIÓN
            </div>
            <?php
        }
        ?>

        <div id="DatosEncabezado">
            <table aria-hidden="true">
                <tr>
                    <td><label>Id: </label><span><?= $cVarVal ?></span> <label>Folio: </label><span class="naranja"><?= $He["folio"] ?></span></td>
                    <td colspan="3"><label>Cliente: </label><span><?= $He["cliente"] . " | " . substr($He["nombre"], 0, 60) ?></span></td>
                    <td><label>Tipo: </label><span><?= $He["tipodepago"] ?></span></td>
                    <td><span><?= $He["fecha"] ?></span></td>
                </tr>
                <tr>
                    <td><label>RFC: </label><span><?= $He["rfc"] ?></span></td>
                    <td><label>Cantidad: </label><span class="naranja"><?= number_format($He["cantidad"], 2) ?></span></td>
                    <td><label>Importe: </label><span><?= number_format($He["importe"], 2) ?></span></td>
                    <td><label>Ieps: </label><span><?= number_format($He["ieps"], 2) ?></span></td>
                    <td><label>Iva: </label><span><?= number_format($He["iva"], 2) ?></span></td>
                    <td><label>Total: </label><span class="naranja"><?= number_format(round($He["importe"] + $He["ieps"] + $He["iva"], 2), 2) ?></span></td>
                </tr>
            </table>
        </div>


        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr height="25">
                <td>
                    <?php
                    if ($He["status"] == StatusNotaCredito::ABIERTO) {
                        if ($He["importe"] > 0) {
                            if ($pac['pruebas'] == '1') {
                                echo "<a  class='enlace_timbre' href='gennotacredito331.php'>&nbsp;GENERAR NOTA DE CREDITO EN MODO DE DEMOSTRACIÓN</a>";
                            } else {
                                echo "<a  class='enlace_timbre' href='gennotacredito331.php'>&nbsp;GENERAR NOTA DE CREDITO</a>";
                            }
                        } 
                    } else {
                        if ($He["status"] == StatusNotaCredito::CERRADO) {
                            echo "<font color='#990000'> Nota cerrada y timbrada";
                        } elseif ($He["status"] != StatusNotaCredito::CANCELADO) {
                            echo "<font color='#990000'> Nota cancelada y timbrada";
                        } elseif ($He["status"] != StatusNotaCredito::CANCELADO_ST) {
                            echo "<font color='#990000'> Nota cancelada sin timbrar";
                        }
                    }
                    echo "</td><td align='right' class='subtitulos'>";
                    if ($ciaVO->getFacturacion() === "No") {
                        echo "Programa demo ";
                    }
                    ?>
                </td>
            </tr>
        </table>

        <div id="TablaDatos">
            <table class="paginador" aria-hidden="true">
                <?php
                echo $paginador->headers(array(), array(" "));
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr>
                        <?php echo $paginador->formatRow(); ?>
                        <td style="text-align: center;">
                            <?php if ($He["status"] == StatusNotaCredito::ABIERTO) { ?>
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
        echo $paginador->footer(false, null, false, true);

        if ($He["status"] == StatusNotaCredito::ABIERTO) {
            ?>
            <form name="form1" method="post" action="" id="form1">
                <table style="width: 100%" class="texto_tablas" aria-hidden="true"> 
                    <tr>
                        <td>
                            <table style="width: 100%" class="texto_tablas" aria-hidden="true">
                                <tr style="background-color: #E1E1E1;height: 30px;">
                                    <td>
                                        <?php
                                        if ($He["status"] == StatusNotaCredito::ABIERTO) {
                                            ComboboxInventario::generate("Producto", "'Aceites','Combustible','Otros'", "350px", " required='required'");
                                            cInputDat("&nbsp; Cnt:", "Text", "5", "Cantidad", "-", $Cpo["volumenfac"], "12", true, false);
                                            echo "&nbsp;<strong>&Oacute;</strong> ";
                                            cInputDat("&nbsp; Importe:", "Text", "10", "Importe", "-", $Cpo["importe"], "12", true, false);
                                            echo "&nbsp;<input type='submit' name='Boton' value='Agregar' class='nombre_cliente'>";
                                        }
                                        ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </form>

            <?php
        }
        echo "<div class='mensajes'>$Msj</div>";
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>
