<?php
session_start();
set_time_limit(600);

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();

$Titulo = "Cuentas x pagar historico";
$Tabla = "cxph";
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

require "./services/ReportesProveedoresService.php";

$registros = utils\IConnection::getRowsFromQuery($selectCxp);

if ($orden === "referencia") {
    $cRef = 'checked';
} else {
    $cFec = 'checked';
}

?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php include './config_omicrom.php'; ?>    
        <title><?= $Gcia ?></title>
        <script>

            function Confirma() {
                return confirm('ATENCION!!! enviaras todos los movimientos saldados a historico, Esta seguro de realizar esta operacion?');
            }

            $(document).ready(function () {
                $('#FechaI').val('<?= $FechaI ?>').attr('size', '10').addClass('texto_tablas');
                $('#FechaF').val('<?= $FechaF ?>').attr('size', '10').addClass('texto_tablas');
                $('#Fecha').val('<?= $Fecha ?>').attr('size', '10').addClass('texto_tablas');
                $('#cFechaI').css('cursor', 'hand').click(function () {
                    displayCalendar($('#FechaI')[0], 'yyyy-mm-dd', $(this)[0]);
                });
                $('#cFechaF').css('cursor', 'hand').click(function () {
                    displayCalendar($('#FechaF')[0], 'yyyy-mm-dd', $(this)[0]);
                });
                $('#cFecha').css('cursor', 'hand').click(function () {
                    displayCalendar($('#Fecha')[0], 'yyyy-mm-dd', $(this)[0]);
                });
                $('#autocomplete').val("<?= $ProveedorS ?>");

                $('#autocomplete').activeComboBox(
                        $("[name='form1']"),
                        "SELECT id as data, CONCAT(id, ' | ', nombre) value FROM prv WHERE prv.id > 0",
                        "nombre"
                        );
                $('#autocomplete').focus();
            });

        </script>
        <style>
            .cxcOpciones{
                width: 100%;
                border-collapse: collapse; 
                border: 1px solid white; 
                background-color: #CACACA
            }
            .cxcOpciones td{
                border: 1px solid white; 
                vertical-align: middle;
                padding: 5px;
            }
            .cxcOpciones td img{
                height: 20px;
                padding-left: 5px;
                vertical-align: middle;
            }
        </style>
    </head>


    <body>
        <?php BordeSuperior(); ?>

        <form name='form1' method='post' action="">
            <table class="cxcOpciones" aria-hidden="true">
                <tr class="texto_tablas">
                    <td colspan="3">  
                        <div style="position: relative;">
                            <input type="search" style="width: 100%" class="texto_tablas" name="ProveedorS" id="autocomplete" placeholder="Buscar proveedor" required>
                        </div>
                        <div id="autocomplete-suggestions"></div>
                    </td>
                    <td align="center">Ordenado por</td>
                </tr>
                <tr class="texto_tablas">
                    <td>F.inicio: <input class="texto_tablas" type="text" name="FechaI" id="FechaI" size="10"><img src="libnvo/calendar.png" id="cFechaI" alt="Calendario"></td>
                    <td>F.final: <input class="texto_tablas" type="text" name="FechaF" id="FechaF" size="10"><img src="libnvo/calendar.png" id="cFechaF" alt="Calendario"></td>
                    <td align="right"><input class="nombre_cliente" type="submit"  name="Boton" value="Enviar"></td>
                    <td align="center">
                        &nbsp; <input type="radio" class="botonAnimatedMin" name="orden" value="referencia" <?= $cRef ?> onChange=submit();> Referencia 
                        &nbsp; <input type="radio" class="botonAnimatedMin" name="orden" value="fecha" <?= $cFec ?> onChange=submit(); > Fecha
                    </td>
                </tr>
            </table>
            <input type='hidden' name='Proveedor' value='<?= $Proveedor?>'>
        </form>
        <div class='texto_tablas' style="text-align: center;margin: 0;padding: 0;color: red;"><?= $Msj ?></div>


        <div id='TablaDatos'>
             <table aria-hidden="true">
                <tr>
                    <td class='fondoVerde'></td>
                    <td class='fondoVerde'>Referencia</td>
                    <td class='fondoVerde'>Fecha</td>
                    <td class='fondoVerde'>Vencimiento</td>
                    <td class='fondoVerde'>Concepto</td>
                    <td class='fondoVerde'>Cargo</td>
                    <td class='fondoVerde'>Abono</td>
                    <td class='fondoVerde'>Saldo</td>
                </tr>

                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td align='right'>SALDO INICIAL </font></td>
                    <td align='right'><?= number_format($Cargo, 2) ?></td>
                    <td align='right'><?= number_format($Abono, 2) ?></td>
                    <td align='right'><?= number_format($Cargo - $Abono, 2) ?></td>
                </tr>

                <?php
                $nRng = 2;

                foreach ($registros as $registro) {
                    $Cargo += $registro["cargo"];
                    $Abono += $registro["abono"];

                    echo "<tr>";

                    echo "<td>$nRng</td>";
                    echo "<td>  ".$registro["referencia"]."</td>";
                    echo "<td>  ".$registro["fecha"]."</td>";
                    echo "<td>  ".$registro["fechav"]."</td>";
                    echo "<td>  " . ucwords(strtolower($registro["concepto"])) . "</td>";

                   echo "<td align='right'>" . number_format($registro["cargo"], 2) . "</td>";
                    echo "<td align='right'>" . number_format($registro["abono"], 2) . "</td>";
                    echo "<td align='right'>" . number_format(($Cargo - $Abono), 2) . "</td>";

                    echo "</tr>";

                    $nRng++;
                }
                ?>
                <tr>
                    <td colspan='5' class="upTitles">   TOTALES ------></td>
                    <td class="upTitles"><?= number_format($Cargo, 2) ?></td>
                    <td class="upTitles"><?= number_format($Abono, 2) ?></td>
                    <td class="upTitles"><?= number_format($Cargo - $Abono, 2) ?></td>
                </tr>

            </table>
        </div>

        <?php BordeSuperiorCerrar() ?>
        <?php PieDePagina() ?>

    </body>
</html>
