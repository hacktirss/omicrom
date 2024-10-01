<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("comboBoxes.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();

require './services/GeneraValesService.php';

$Titulo = "Generacion de vales";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);


$genbolVO = new GenbolVO();
$genbolVO->setFecha(date("Y-m-d"));
$genbolVO->setFechav(date('Y-m-d', strtotime('+30 day', strtotime(date('Y-m-d')))));
if (is_numeric($busca)) {
    $genbolVO = $genbolDAO->retrieve($busca);
}
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                let busca = "<?= $busca ?>";
                $("#busca").val(busca);

                $("#Cliente").val("<?= $genbolVO->getCliente() ?>").prop("required", true);
                $("#Fechav").val("<?= $genbolVO->getFechav() ?>").attr("size", "10").addClass("texto_tablas");
                $("#cFechav").css("cursor", "hand").click(function () {
                    displayCalendar($("#Fechav")[0], "yyyy-mm-dd", $(this)[0]);
                });

                if (busca !== "NUEVO") {
                    $("#Importe").prop("disabled", true);
                }

                $("#Cliente").focus();
            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                     <a href="genboletos.php"><div class="RegresarCss " alt="Flecha regresar" style="">Regresar</div></a>
                </td>
                <td style="vertical-align: top;">
                    <form name="form1" id="form1" method="post" action="">

                        <?php
                        cTable("99%", "0");

                        cInput("Id:", "Text", "5", "Id", "right", $busca, "40", false, true, "");

                        echo "<tr class='nombre_cliente'><td  bgcolor='#e1e1e1' align='right'> Cliente: &nbsp;</td><td>";
                        ComboboxClientes::generate("Cliente", "'Prepago'", "350px");
                        echo "</td></tr>";

                        cInput("Fecha: ", "Text", "10", "Fecha", "right", $genbolVO->getFecha(), "10", true, true, '');
                        cInput("Fecha de vencimiento: ", "Text", "12", "Fechav", "right", "", "10", true, false, '<img id="cFechav" src="libnvo/calendar.png" alt="Calendario">');
                        cInput("Cantidad:", "Text", "5", "Cantidad", "right", $genbolVO->getCantidad(), "5", true, true, '');
                        cInput("Importe:", "Text", "10", "Importe", "right", (!empty($genbolVO->getImporte())? $genbolVO->getImporte() : ""), "10", true, false, '', " required");
                        cInput("Autorizado y/o recibido:", "Text", "40", "Recibe", "right", $genbolVO->getRecibe(), "100", true, false, '', " style='width: 350px' placeholder='Nombre de la persona quien recibe los vales'");

                        echo "<tr><td colspan='2' align='center'>";
                        if (is_numeric($busca) && $genbolVO->getStatus() === StatusVales::ABIERTO) {
                            echo "<input type='submit' class='nombre_cliente' name='Boton' value='Actualizar'>";
                        } elseif (!is_numeric($busca)) {
                            echo "<input type='submit' class='nombre_cliente' name='Boton' value='Agregar'>";
                        }
                        echo "</td><tr>";

                        if ($genbolVO->getStatus() === StatusVales::CERRADO) {
                            cInput("Clave de cancelacion: ", "password  ", "20", "Password", "right", '', "40", false, false, "<input type='submit' class='nombre_cliente' name='Boton' value='Cancelar'>", " placeholder='********' autocomplete='new-password'");
                        }

                        cTableCie();
                        ?>
                        <input type='hidden' name='busca' id='busca'>
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