<?php
session_start();
set_time_limit(600);

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();

require './services/CxpService.php';

$Titulo = "Cuentas x pagar";
$Tabla = "cxp";
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

require "./services/ReportesProveedoresService.php";

$registros = utils\IConnection::getRowsFromQuery($selectCxp);

if ($orden === "referencia") {
    $cRef = 'checked';
} else {
    $cFec = 'checked';
}

$Id = 114;
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
                    <td colspan="4">  
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
                    <td>Abonar: $ <input class="texto_tablas" type="text" name="Abono" value="" size="7" placeholder="0.00"></td>
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

        <form name="form2" method="post" action="" onsubmit="return Confirma();" id="form">
            <table class="cxcOpciones table1" aria-hidden="true">
                <tr> 
                    <td  valign='center' align='center'>
                        <input type='submit' name='Boton' value='Enviar a historico' class='nombre_cliente'>
                    </td>

                    <td>
                         <table aria-hidden="true">
                            <tr>
                                <td class="texto_tablas" align="right" width="40%" style="border: 0;">Determinar saldo al: </td>
                                <td width="30%" style="border: 0;">
                                    <input type='text' name="Fecha" id="Fecha">
                                    <img src='libnvo/calendar.png' id="cFecha" alt="Calendario">
                                </td>
                                <td rowspan=2 align="right" valign="center" style="border: 0;">
                                    <input type='submit' name='Boton' value='Determinar saldo' class='nombre_cliente'>
                                </td> 
                            </tr>
                            <tr>
                                <td class="texto_tablas" align="right" style="border: 0;"> Contraseña: </td>
                                <td style="border: 0;">
                                    <input class='texto_tablas' type='password' name='Password' autocomplete="new-password">
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td align="center" style="padding: 10px ">
                        <a class="textosCualli_i" href=javascript:wingral("cxpdf.php?Proveedor=<?= urlencode($Proveedor) ?>&FechaI=<?= $FechaI ?>&FechaF=<?= $FechaF ?>&orden=<?= $orden ?>&T=<?= $Tabla ?>"); style="color: red;">
                            <em class="icon fa fa-lg fa-file-pdf-o" aria-hidden="true"></em>
                        </a>
                    </td>
                </tr>
            </table>
            <p class="texto_tablas">Para determinar un saldo, coloque la fecha deseada y de click en el botón de <strong>Determinar saldo</strong>; 
                en ese momento todos sus movimientos anteriores a esa fecha se mandarán a historico y unicamente insertará un registro con el saldo calculado a dicha fecha. 
                <br/>
            </p>

            <input type='hidden' name='Proveedor' value='<?= $Proveedor ?>'>
            <input type='hidden' name='FechaI' value='<?= $FechaI ?>'>
            <input type='hidden' name='FechaF' value='<?= $FechaF ?>'>
        </form>

        <?php BordeSuperiorCerrar() ?>
        <?php PieDePagina() ?>

    </body>
</html>
