<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();

$Titulo = "Agregar Registro en la Bitacora";

require_once './services/BitacoraService.php';

$Fecha = date("Y-m-d");
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom.php"; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#busca").val("<?= $busca ?>");
                $('#Fecha').val('<?= $Fecha ?>').attr('size', '12').addClass('texto_tablas');
                $('#cFechaI').css('cursor', 'hand').click(function () {
                    displayCalendar($('#Fecha')[0], 'yyyy-mm-dd', $(this)[0]);
                });
            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;width: 90px;" class="nombre_cliente">
                    <a href="bitacoraEventos.php"><div class="RegresarCss " alt="Flecha regresar" >Regresar</div></a>
                </td>
                <td style="vertical-align: top;">
                    <form name="form1" id="form1" method="post" action="">

                        <?php
                        cTable("99%", "0");
                        $calendar = "<i class='fa-regular fa-calendar-plus fa-lg' style='color:#099' id='cFechaI'></i>";

                        cInput("Fecha: ", "Text", "12", "Fecha", "right", "", "12", true, 0, $calendar,  "", $required);

                        cInput("Hora: ", "Time", "40", "HoraEv", "right", "12:00", "50", true, 0, "",  "", $required);

                        echo "<tr>";
                        echo "<td bgcolor='#e1e1e1' align='right' class='nombre_cliente'> Tipo Evento: &nbsp; </td><td>";
                        ListasCatalogo::listaNombreCatalogo("TipoEvento", "BITACORA DE EVENTOS");
                        echo "</td>";
                        echo "</tr>";

                        echo "<tr>";
                        echo "<td bgcolor='#e1e1e1' align='right' class='nombre_cliente'> Descripcion Evento: &nbsp; </td>";
                        echo "<td> &nbsp;<textarea name='taDescripcion' id='taDescripcion' rows='8'  cols='40' required='required' class='texto_tablas'></textarea>";
                        echo "</td>";
                        echo "</tr>";


                        echo "<tr><td colspan = '2' align = 'center'>";
                        echo "<input type = 'submit' class = 'nombre_cliente' name = 'Boton' value = 'Agregar'>";
                        echo "</td><tr>";

                        cTableCie();
                        ?>

                        <input type="hidden" name="busca" id="busca">
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