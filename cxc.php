<?php
session_start();
set_time_limit(600);

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$usuarioSesion = getSessionUsuario();
$mysqli = iconnect();

require "./services/CxcService.php";

$Titulo = "Estado de cuenta";
$Tabla = "cxc";
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

require "./services/ReportesClientesService.php";

$registros = utils\IConnection::getRowsFromQuery($selectCxc);
$cRef = $cFec = "";
if ($orden == "referencia") {
    $cRef = "checked";
} elseif ($orden == "fecha") {
    $cFec = "checked";
} else {
    $cFac = "checked";
}

$Id = 112;
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php include "./config_omicrom.php"; ?>    
        <title><?= $Gcia ?></title>
        <script>

            function Confirma() {
                return confirm("ATENCION!!! enviaras todos los movimientos saldados a historico, Esta seguro de realizar esta operacion?");
            }

            $(document).ready(function () {
                $("#FechaI").val("<?= $FechaI ?>").attr("size", "10").addClass("texto_tablas");
                $("#FechaF").val("<?= $FechaF ?>").attr("size", "10").addClass("texto_tablas");
                $("#Fecha").val("<?= $Fecha ?>").attr("size", "10").addClass("texto_tablas");
                $("#cFechaI").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaI")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#cFechaF").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaF")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#cFecha").css("cursor", "hand").click(function () {
                    displayCalendar($("#Fecha")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#autocomplete").val("<?= html_entity_decode($SCliente) ?>");

                $("#autocomplete").activeComboBox(
                        $("[name='form1']"),
                        "SELECT data, value FROM (SELECT id as data, CONCAT(id, ' | ', tipodepago, ' | ', nombre) value FROM cli " +
                        "WHERE TRUE AND cli.tipodepago NOT REGEXP 'Contado|Puntos') sub WHERE TRUE",
                        "value"
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
                    <td colspan="4">  
                        <div style="position: relative;">
                            <input type="search" style="width: 100%" class="texto_tablas" name="ClienteS" id="autocomplete" placeholder="Buscar cliente" required>
                        </div>
                        <div id="autocomplete-suggestions"></div>
                    </td>
                    <td align="center">Ordenado por</td>
                </tr>
                <tr class="texto_tablas">
                    <td>F.inicio: <input class="texto_tablas" type="text" name="FechaI" id="FechaI" size="10"><em class="fa-regular fa-calendar-plus fa-2xl" style="color: #117A65;padding-left: 5px;" id="cFechaI" alt="Calendario"></em></td>
                    <td>F.final: <input class="texto_tablas" type="text" name="FechaF" id="FechaF" size="10"><em class="fa-regular fa-calendar-plus fa-2xl" style="color: #117A65;padding-left: 5px;" id="cFechaF" alt="Calendario"></em></td>
                    <td>Abonar: $ <input class="texto_tablas" type="text" name="Abono" value="" size="7" placeholder="0.00"></td>
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
        <div class="texto_tablas" style="text-align: center;margin: 0;padding: 0;color: red;"><?= $Msj ?></div>

        <div id="TablaDatos">
            <table aria-hidden="true">
                <tr>
                    <td class="fondoVerde">#</td>
                    <td class="fondoVerde">Referencia</td>
                    <td class="fondoVerde">Placas</td>
                    <td class="fondoVerde">Fecha</td>
                    <td class="fondoVerde">Antigüedad</td>
                    <td class="fondoVerde">Concepto</td>
                    <td class="fondoVerde">Factura</td>
                    <td class="fondoVerde">Cargo</td>
                    <td class="fondoVerde">Abono</td>
                    <td class="fondoVerde">Saldo</td>
                </tr>

                <tr>
                    <td>1</td>
                    <td></td>
                    <td></td>
                    <td><?= $FechaI ?></font></td>
                    <td></td>
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
                    ?>
                    <tr>
                        <td><?= $nRng++ ?></td>
                        <td><?= $registro["referencia"] ?></td>
                        <td><?= $registro["placas"] ?></td>
                        <td><?= $registro["fecha"] ?></td>
                        <td><?= empty($registro["factura"]) ? $registro["antiguedad"] . " días" : "" ?></td>
                        <td><?= $registro["concepto"] ?></td>
                        <td align="right"><?= $registro["factura"] ?></td>
                        <td align="right"><?= number_format($registro["cargo"], 2) ?></td>
                        <td align="right"><?= number_format($registro["abono"], 2) ?></td>
                        <td align="right"><?= number_format(($Cargo - $Abono), 2) ?></td>
                    </tr>

                <?php } ?>
                <tr>
                    <td colspan="7" class="upTitles">TOTALES</td>
                    <td class="upTitles"><?= number_format($Cargo, 2) ?></td>
                    <td class="upTitles"><?= number_format($Abono, 2) ?></td>
                    <td class="upTitles"><?= number_format($Cargo - $Abono, 2) ?></td>
                </tr>

            </table>
        </div>


        <form name="form2" method="post" action="" onsubmit="return Confirma();" id="form">
            <table class="cxcOpciones table1" aria-hidden="true">
                <tr> 
                    <td  valign="center" align="center">
                        <input type="submit" name="Boton" value="Enviar a historico" class="nombre_cliente">
                    </td>

                    <td>
                        <table aria-hidden="true">
                            <tr>
                                <td class="texto_tablas" align="right" width="40%" style="border: 0;">Determinar saldo al: </td>
                                <td width="30%" style="border: 0;">
                                    <input type="text" name="Fecha" id="Fecha">
                                    <em class="fa-regular fa-calendar-plus fa-2xl" style="color: #117A65;padding-left: 5px;" id="cFecha" alt="Calendario"></em>
                                </td>
                                <td rowspan=2 align="right" valign="center" style="border: 0;">
                                    <input type="submit" name="Boton" value="Determinar saldo" class="nombre_cliente">
                                </td> 
                            </tr>
                            <tr>
                                <td class="texto_tablas" align="right" style="border: 0;"> Contraseña: </td>
                                <td style="border: 0;">
                                    <input class="texto_tablas" type="password" name="Password" autocomplete="new-password">
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td>
                        <?php
                        if ($usuarioSesion->getTeam() === "Administrador" && $Cliente > 0) {
                            $IdCliG = explode("|", utils\HTTPUtils::getSessionValue("SCliente"));
                            ?>
                            <a href="masterticket.php?cliente=<?= $IdCliG[0] ?>&saldo=<?= $Cargo - $Abono ?>&criteria=ini">Agrega Tickets <em class="fa-solid fa-arrow-up-right-dots"></em></a>
                            <?php
                        }
                        ?>
                    </td>
                    <td align="center" style="padding: 10px ">
                        <a class="textosCualli_i" href=javascript:wingral("cxcpdf.php?ClienteS=<?= urlencode($Cliente) ?>&FechaI=<?= $FechaI ?>&FechaF=<?= $FechaF ?>&orden=<?= $orden ?>&T=<?= $Tabla ?>"); style="color: red;">
                            <em class="icon fa fa-lg fa-file-pdf-o" aria-hidden="true"></em>
                        </a>
                    </td>
                </tr>
            </table>
            <p class="texto_tablas">Para determinar un saldo, coloque la fecha deseada y de click en el botón de <strong>Determinar saldo</strong>; 
                en ese momento todos sus movimientos anteriores a esa fecha se mandarán a historico y unicamente insertará un registro con el saldo calculado a dicha fecha. 
                <br/>
            </p>

            <input type="hidden" name="ClienteS" value="<?= $SCliente ?>">
            <input type="hidden" name="FechaI" value="<?= $FechaI ?>">
            <input type="hidden" name="FechaF" value="<?= $FechaF ?>">
        </form>

        <?php BordeSuperiorCerrar() ?>
        <?php PieDePagina() ?>

    </body>
</html>
