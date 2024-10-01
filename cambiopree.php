<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("comboBoxes.php");

use com\softcoatl\utils as utils;

require './services/CambioDePreciosService.php';

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$self = utils\HTTPUtils::self();

$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);
$Titulo = "Cambio de precios";

if ($busca === "NUEVO") {
    $Fechaapli = date("Y-m-d");
    $Fecha = date('Y-m-d');
    $cHora = date("H");
    $cMinuto = date("i");
} else {
    $Titulo = "Favor de confirmar";
}
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $('#Fechaapli').val(<?= '$Fechaapli' ?>).attr('size', '10').addClass('texto_tablas');
                $('#cFecha').css('cursor', 'hand').click(function () {
                    displayCalendar($('#Fechaapli')[0], 'yyyy-mm-dd', $(this)[0]);
                });
            });
        </script>
        <style>
            .aviso{
                color: #F63;
                font-size: 25px;
            }
        </style>
    </head>


    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;width: 90px;" class="nombre_cliente">
                    <a href="cambiopre.php"><div class="RegresarCss " alt="Flecha regresar" style="">Regresar</div></a>
                </td>
                <td style="vertical-align: top;">
                    <form name="form1" id="form1" method="post" action="">

                        <?php
                        cTable("99%", "0");

                        if ($busca === "NUEVO") {

                            cInput("Fecha: ", "Text", "10", "Fecha", "right", $Fecha, "10", false, true, '');

                            echo "<tr height='45px'><td bgcolor='#e1e1e1' align='right' class='nombre_cliente'>Tipo de combustible: &nbsp; </td><td> ";
                            ComboboxCombustibles::generate("Producto", "300px", " required");
                            echo "</td></tr>";

                            cInput("Fecha de aplicacion: ", "Text", "10", "Fechaapli", "right", $Fechaapli, "10", true, false, " <img src='libnvo/calendar.png' border='0' id='cFecha'>", " required");

                            echo "<tr height='45px'><td bgcolor='#e1e1e1' align='right' class='nombre_cliente'>Hora aplicacion:&nbsp;</td>";

                            echo "<td >";
                            echo "&nbsp;<select name='Horaapli' id='Horaapli' class='texto_tablas'>";
                            for ($i = 0; $i <= 23; $i++) {
                                $Hra = cZeros($i, 2);
                                echo "<option value='$Hra'>$Hra</option>";
                            }
                            echo "<option selected value='$cHora'>$cHora</option>";
                            echo "</select>:";

                            echo "<select name='Minutoapli' id='Minutoapli'  class='texto_tablas'>";
                            for ($i = 0; $i <= 60; $i++) {
                                $Min = cZeros($i, 2);
                                echo "<option value='$Min'>$Min</option>";
                            }
                            echo "<option selected value='$cMinuto'>$cMinuto</option>";
                            echo "</select>";
                            echo "</td></tr>";

                            echo "<tr height='45px'><td bgcolor='#e1e1e1' align='right' class='nombre_cliente'>Precio nuevo:&nbsp;</td>";
                            echo "<td class='nombre_cliente'> ";
                            echo "&nbsp;<select name='Pesos'  id='Pesos' class='texto_tablas'>";
                            for ($i = 1; $i <= 99; $i++) {
                                $Pesos = cZeros($i, 2);
                                echo "<option value='$Pesos'>$Pesos</option>";
                            }
                            echo "</select>";
                            echo " pesos &nbsp;  ";
                            echo "<select name='Centavos' id='Centavos' class='texto_tablas'>";
                            for ($i = 0; $i <= 99; $i++) {
                                $Centavos = cZeros($i, 2);
                                echo "<option value='$Centavos'>$Centavos</option>";
                            }
                            echo "</select>";
                            echo " centavos </td></tr>";

                            echo "<tr><td></td><td>&nbsp;<input type='submit' name='Boton' value='Enviar' class='nombre_cliente'></td></tr>";
                            echo "<input type='hidden' name='busca' value=''>";

                            cTableCie();
                        } else {

                            $Precio = $sanitize->sanitizeString("Pesos") . "." . $sanitize->sanitizeString("Centavos");
                            $Hora = $sanitize->sanitizeString("Horaapli");
                            $Minuto = $sanitize->sanitizeString("Minutoapli");
                            $Producto = $sanitize->sanitizeString("Producto");
                            $HoraApliacion = $Hora . ":" . $Minuto . ":00";
                            $FechaAplicacion = $sanitize->sanitizeString("Fechaapli") . " " . $HoraApliacion;

                            $combustiblesVO = $combustiblesDAO->retrieve($Producto, "clavei");

                            cInput("Tipo de combustible: ", "Text", "10", "", "right", "<font class='aviso'>" . $combustiblesVO->getDescripcion() . "</font>", "10", false, true, '');

                            cInput("Fecha de aplicacion: ", "Text", "10", "", "right", "<font class='aviso'>$FechaAplicacion</font>", "10", false, true, '');

                            cInput("Hora aplicacion: ", "Text", "10", "", "right", "<font class='aviso'>$HoraApliacion</font>", "10", false, true, '');

                            cInput("Nuevo precio $: ", "Text", "10", "", "right", "<font class='aviso'>$Precio</font>", "10", false, true, '');

                            cTableCie();

                            echo "<input type='hidden' name='Producto' value='$Producto'>";
                            echo "<input type='hidden' name='Fechaapli' value='" . $sanitize->sanitizeString("Fechaapli") . "'>";
                            echo "<input type='hidden' name='Horaapli' value='$Hora'>";
                            echo "<input type='hidden' name='Minutoapli' value='$Minuto'>";
                            echo "<input type='hidden' name='Pesos' value='" . $sanitize->sanitizeString("Pesos") . "'>";
                            echo "<input type='hidden' name='Centavos' value='" . $sanitize->sanitizeString("Centavos") . "'>";


                            echo "<p class='nombre_cliente' align='center'><font color='#F63'><strong>ATENCION!!!</strong> estas a punto de hacer un cambio de precio, si estas seguro de este proceso <br> favor de dar click en el boton de confirmar</strong><br></font>";
                            echo "<br/>";
                            echo "<input type='submit' name='Boton' value='Confirmar' class='nombre_cliente'>";
                            echo "</p>";
                        }
                        ?>
                    </form>

                </td>
            </tr>
        </table>


        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>

</html>