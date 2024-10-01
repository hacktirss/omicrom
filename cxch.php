<?php
session_start();
set_time_limit(600);

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();

$Titulo = "Estado de cuenta historico";
$Tabla = "cxch";

require "./services/ReportesClientesService.php";

$registros = utils\IConnection::getRowsFromQuery($selectCxc);

if ($orden == 'referencia') {
    $cRef = 'checked';
} elseif ($orden == 'fecha') {
    $cFec = 'checked';
} else {
    $cFac = 'checked';
}

$Id = 113;
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php include './config_omicrom.php'; ?>    
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $('#FechaI').val('<?= $FechaI ?>').attr('size', '10').addClass('texto_tablas');
                $('#FechaF').val('<?= $FechaF ?>').attr('size', '10').addClass('texto_tablas');

                $('#cFechaI').css('cursor', 'hand').click(function () {
                    displayCalendar($('#FechaI')[0], 'yyyy-mm-dd', $(this)[0]);
                });
                $('#cFechaF').css('cursor', 'hand').click(function () {
                    displayCalendar($('#FechaF')[0], 'yyyy-mm-dd', $(this)[0]);
                });

                $("#autocomplete").val("<?= html_entity_decode($SCliente) ?>");

                $('#autocomplete').activeComboBox(
                        $("[name='form1']"),
                        "SELECT id as data, CONCAT(id, ' | ', tipodepago, ' | ', nombre) value FROM cli " +
                        "WHERE TRUE AND cli.tipodepago NOT REGEXP 'Contado|Puntos'",
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

        <?php BordeSuperior() ?>

        <form name="form1" method="post" action="">
            <table class="cxcOpciones" aria-hidden="true">
                <tr class="texto_tablas">
                    <td colspan="3">  
                        <div style="position: relative;">
                            <input type="search" style="width: 100%" class="texto_tablas" name="ClienteS" id="autocomplete" placeholder="Buscar cliente" required>
                        </div>
                        <div id="autocomplete-suggestions"></div>
                    </td>
                    <td align="center">Ordenado por</td>
                </tr>
                <tr class="texto_tablas">
                    <td>F.inicio: <input class="texto_tablas" type="text" name="FechaI" id="FechaI" size="10">
                        <em class="fa-regular fa-calendar-plus fa-2xl" style="color: #117A65;padding-left: 5px;" id="cFechaI" alt="Calendario"></em></td>
                    <td>F.final: <input class="texto_tablas" type="text" name="FechaF" id="FechaF" size="10">
                         <em class="fa-regular fa-calendar-plus fa-2xl" style="color: #117A65;padding-left: 5px;" id="cFechaF" alt="Calendario"></em></td>
                    <td align="right"><input class="nombre_cliente" type="submit"  name="Boton" value="Enviar"></td>
                    <td align="center">
                        &nbsp; <input type="radio" class="botonAnimatedMin" name="orden" value="referencia" <?= $cRef ?> onChange=submit();> Referencia 
                        &nbsp; <input type="radio" class="botonAnimatedMin" name="orden" value="factura" <?= $cFac ?> onChange=submit();> Factura 
                        &nbsp; <input type="radio" class="botonAnimatedMin" name="orden" value="fecha" <?= $cFec ?> onChange=submit(); > Fecha
                    </td>
                </tr>
            </table>
            <input type="hidden" name="Cliente" value="<?= $Cliente ?>">
        </form>

        <div id="TablaDatos">
            <table aria-hidden="true">
                <tr>
                    <td  class="fondoVerde">#</td>
                    <td  class="fondoVerde">Referencia</td>
                    <td  class="fondoVerde">Placas</td>
                    <td  class="fondoVerde">Fecha</td>
                    <td  class="fondoVerde">Concepto</td>
                    <td  class="fondoVerde">Factura</td>
                    <td  class="fondoVerde">Cargo</td>
                    <td  class="fondoVerde">Abono</td>
                    <td  class="fondoVerde">Saldo</td>
                </tr>

                <tr>
                    <td>1</td>
                    <td></td>
                    <td></td>
                    <td><?= $FechaI ?></font></td>
                    <td>SALDO INICIAL </font></td>
                    <td></td>
                    <td align="right"><?= number_format($Cargo, 2) ?></td>
                    <td align="right"><?= number_format($Abono, 2) ?></td>
                    <td align="right"><?= number_format($Cargo - $Abono, 2) ?></td>
                </tr>

                <?php
                $nRng = 2;

                foreach ($registros as $registro) {
                    $Cargo += $registro["cargo"];
                    $Abono += $registro["abono"];

                    echo "<tr>";

                    echo "<td>$nRng</td>";
                    echo "<td>" . $registro["referencia"] . "</td>";
                    echo "<td>" . $registro["placas"] . "</td>";
                    echo "<td>" . $registro["fecha"] . "</td>";
                    echo "<td>" . $registro["concepto"] . "</td>";
                    echo "<td align='right'>" . $registro["factura"] . "</td>";
                    echo "<td align='right'>" . number_format($registro["cargo"], 2) . "</td>";
                    echo "<td align='right'>" . number_format($registro["abono"], 2) . "</td>";
                    echo "<td align='right'>" . number_format(($Cargo - $Abono), 2) . "</td>";

                    echo "</tr>";

                    $nRng++;
                }
                ?>
                <tr>
                    <td colspan='6' class="upTitles">TOTALES</td>
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
