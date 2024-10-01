<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require_once './services/ReportesVentasService.php';
require_once './services/CambioTurnoService.php';

$session = new OmicromSession("rm.id", "rm.id", $nameVariableSession);

$Titulo = "Efectivo del corte $Corte ";
$Id = 14;

$paginador = new Paginador($Id,
        "man.isla_pos,rm.id folio,com.descripcion producto,rm.posicion,rm.fin_venta fecha,rm.descuento,
        ROUND(IF(rm.pesos = rm.pagoreal,rm.pesos,(rm.pesos - rm.pagoreal))/rm.precio ,3) volumenR,
        ROUND(IF(rm.pesos = rm.pagoreal,rm.pesos,(rm.pesos - rm.pagoreal)),2) pesosR,rm.uuid",
        "LEFT JOIN cli ON rm.cliente = cli.id 
        LEFT JOIN man ON rm.posicion = man.posicion",
        "",
        "man.activo = 'Si' AND rm.producto = com.clavei AND com.activo = 'Si' 
        AND rm.corte = '$Corte' AND (cli.tipodepago REGEXP 'Contado|Puntos' OR rm.pesos <> rm.pagoreal) 
        AND rm.tipo_venta='D' AND rm.pesos > 0 " . (is_numeric($IslaPosicion) ? " AND man.isla_pos = $IslaPosicion" : ""),
        $session->getSessionAttribute("sortField"),
        $session->getSessionAttribute("criteriaField"),
        utils\Utils::split($session->getSessionAttribute("criteria"), "|"),
        strtoupper($session->getSessionAttribute("sortType")),
        $session->getSessionAttribute("page"),
        "REGEXP",
        "cambiotur.php?criteria=ini");
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#autocomplete").focus();
                $("#autocomplete")
                        .addClass("texto_tablas")
                        .activeComboBox(
                                $("[name=\"form1\"]"),
                                "SELECT id as data, CONCAT(id, ' | ' , tipodepago, ' | ' , nombre) value FROM cli " +
                                "WHERE TRUE AND cli.tipodepago in ('Contado') AND cli.activo = 'Si' ",
                                "nombre");
            });
        </script>
        <?php $paginador->script(); ?>
    </head>

    <body>

        <?php BordeSuperior(); ?>
        <?php TotalizaCorte(); ?>
        <div id="FormulariosBoots">
            <div class="container no-margin">
                <form name="form1" method="post" action="">
                    <div class="row no-padding" id="InicialB">
                        <div class="col-3 align-right">Ticket: <input style="width: 70%;" type="number" name="TicketEfectivo"></div>
                        <div class="col-3"><input type="text" name="ClienteEfectivo" id="autocomplete" placeholder="Buscar cliente"></div>
                        <div class="col-1"><input type="submit" name="Boton" value="Agregar"></div>
                        <div class="col-1 warning"><a href="movvtasefe.php" id="Cancelar" title="Cancelar operación"><i class="icon fa fa-lg fa-ban" aria-hidden="true" ></i></a></div>
                    </div>
                    <input type="hidden" name="AddTicketContado" value="ok">
                </form>
            </div>
        </div>
        <div id="TablaDatos">
            <table class="paginador2" aria-hidden="true">
                <?php
                echo $paginador->headers(array("Pdf", "Isla"), array("Desc", "Libera"));
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr>
                        <td style="text-align: center;">
                            <?php if (!empty($row["uuid"]) && $row["uuid"] !== FcDAO::SIN_TIMBRAR) { ?>
                                <a style="color: red;" href=javascript:winuni("enviafile.php?id=<?= $row["uuid"] ?>&type=pdf&formato=0")><i class="icon fa fa-lg fa-file-pdf-o" aria-hidden="true"></i></a>
                            <?php } ?>
                        </td>
                        <td><?= $row["isla_pos"] ?></td>
                        <td><?= $row["id"] ?></td>
                        <td><?= $row["turno"] ?></td>
                        <td><?= $row["posicion"] ?></td>
                        <td><?= $row["producto"] ?></td>
                        <td><?= $row["fecha"] ?></td>
                        <td><?= $row["alias"] ?></td>
                        <td style="text-align: right;"><?= $row["pesosR"] ?></td>
                        <td style="text-align: right;"><?= $row["volumenR"] ?></td>
                        <td><?= $row["um"] ?></td>
                        <td style="text-align: right;"><?= $row["descuento"] ?></td>
                        <td style="text-align: center;">
                            <?php if (!(!empty($row["uuid"]) && $row["uuid"] !== FcDAO::SIN_TIMBRAR)) { ?>
                                <a href="movvtasefe.php?op=Lb&IdT=<?= $row["id"] ?>">
                                    <i class="fa fa-trash-o fa-lg" aria-hidden="true" style="color:#FF4430"></i>
                                </a>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>

        <?php
        echo $paginador->footer(false, null, true, true);
        echo $paginador->filter();
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>