<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();

$Fecha = date("Y-m-d");
if ($request->hasAttribute("Fecha")) {
    $Fecha = $request->getAttribute("Fecha");
}
$Hora = date("H:i");
$Titulo = "Combustibles";
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script type="text/javascript">

            var timeOutRef;
            var count = 0;
            function loadImg() {
                count++;
                var stringImg = "<div align='center'>";
                for (var i = 1; i <= count; i++) {
                    stringImg = stringImg + "<img src='libnvo/img1.gif'>";
                }
                stringImg = stringImg + "</div>";
                if (count === 5) {
                    count = 1;
                }
                $('#msj').html(stringImg);
            }

            function callLoad(milis) {
                window.setInterval(function () {
                    loadImg();
                }, milis);
            }

            function callVisor() {
                window.setInterval(function () {
                    $('#contenedor').load('inventariocom_ajax.php', function (response, status, xhr) {
                        if (status === "error") {
                            //window.location = "500.html";
                        }
                    });
                }, 1000);
            }

            $(document).ready(function () {
                $('#Fecha').val('<?= $Fecha ?>').attr('size', '10').addClass('texto_tablas');
                $('#HoraCom').val('<?= $Hora ?>').addClass('texto_tablas');
                $('#cFecha').css('cursor', 'hand').click(function () {
                    displayCalendar($('#Fecha')[0], 'yyyy-mm-dd', $(this)[0]);
                });
                callLoad(5000);
                callVisor();
                $("#BotonT").click(function () {
                    valCampos();
                });
            });
            function valCampos() {
                window.open("pdfticketcom.php?Producto=" + $("#Producto").val() + "&Fecha=" + $("#Fecha").val() + "&Hora=" + $("#HoraCom").val(), "Soporte Tecnico Detisa", "width=750px,height=550px,top=250px,left=80px,Menubar=No,Resizable=NO,Location=NO,Scrollbars=yes,Status=no,Toolbar=no");
            }
        </script>

    </head>

    <body>

        <?php BordeSuperior(); ?>

        <table width="100%" aria-hidden="true">
            <tr>
                <td height="280" align="center">
                    <div id="msj" style="height: 15px;"> </div>
                    <div id="contenedor"></div>
                    <div class="texto_tablas" >
                        <table style="width: 70%;height: 50px;margin-left: 5%;border: 1px solid #808B96;border-radius: 15px;padding: 5px;background-color: #D5D8DC;"
                               summary="Inventario por combustibles">
                            <tr>
                                <th style="padding-left: 10px;">
                                    <select name="Producto" id="Producto" class="texto_tablas">

                                        <?php
                                        $result = $mysqli->query("SELECT tanque,producto FROM tanques");
                                        while ($rg = $result->fetch_array()) {
                                            echo "<option value='" . $rg["tanque"] . "'>" . $rg["tanque"] . " | " . $rg["producto"] . "</option>";
                                        }
                                        ?>
                                    </select> &nbsp 
                                </th>
                                <th style="padding-left: 10px;">
                                    Fecha: 
                                    <input type="date" name="Fecha" id="Fecha" size="10"  class="texto_tablas">
                                    <!--<img src="libnvo/calendar.png" id="cFecha" alt="Calendario">-->
                                </th>
                                <th style="padding-left: 10px;">
                                    &nbsp Hora(hh:mm): 
                                    <input class="texto_tablas" type="time" name="Hora" id="HoraCom" size="6">
                                </th>
                                <th style="padding-left: 10px;">
                                    &nbsp <input style="height: 26px;width: 140px;background-color: #089999;color: white;border-radius: 7px;font-weight: bold;" class="nombre_cliente" type="submit" name="Boton" id="BotonT" value="Enviar ticket" onclick="valCampos();">
                                </th>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>

</html>
