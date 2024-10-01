<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$self = utils\HTTPUtils::self();

require './services/MensajesService.php';

$Titulo = "Detalle de mensajes";
$nameVarBusca = "busca";
if($request->hasAttribute($nameVarBusca)){
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif($request->hasAttribute("id")){
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

$mensajeVO = new MensajeVO();
$mensajeVO->setFecha(date("Y-m-d"));
$mensajeVO->setHora(date("H:i:s"));
$mensajeVO->setTipo(TipoMensaje::SIN_LEER);
$mensajeVO->setVigencia(1);
if (is_numeric($busca)) {
    $mensajeVO = $mensajeDAO->retrieve($busca);
}
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#busca").val("<?= $busca ?>");
                $("#De").val("<?= $mensajeVO->getDe() ?>");
                $("#Para").val("<?= $mensajeVO->getPara() ?>");
                $("#Titulo").val("<?= $mensajeVO->getTitulo() ?>");
                $("#Tipo").val("<?= $mensajeVO->getTipo() ?>");
                $("#Vigencia").val("<?= $mensajeVO->getVigencia() ?>");
            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="mensajes.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">
                    <form name="form1" id="form1" method="post" action="">

                        <?php
                        cTable("99%", "0");
                        cInput("Titulo:", "Text", "80", "Titulo", "right", "", "80", true, false, '', "placeholder='Breve descripcion'");
                        cInput("De:", "Text", "30", "De", "right", "", "25", true, false, '');
                        cInput("Para:", "Text", "30", "Para", "right", "", "25", true, false, '');
                        cInput("Fecha:", "Text", "30", "Fecha", "right", $mensajeVO->getFecha(), "40", true, true, "");
                        cInput("Hora:", "Text", "30", "Horas", "right", $mensajeVO->getHora(), "40", true, true, '');
                        cInput("Vigencia:", "Number", "10", "Vigencia", "right", $Cpo["vigencia"], "10", true, false, ' dias', " min='1' max='31' ");

                        echo "<tr class='texto_tablas'>";
                        echo "<td align='right' class='nombre_cliente' bgcolor='#e1e1e1'>Status: &nbsp;</td><td>&nbsp;";
                        echo "<select class='texto_tablas' name='Tipo' id='Tipo' style='width: 150px;'>";
                        echo "<option value='R'>No leido</option>";
                        echo "<option value='L'>Leido</option>";
                        echo "</select>";
                        echo "</td><tr>";
                        
                        echo "<tr class='texto_tablas'>";
                        echo "<td align='right' bgcolor='#e1e1e1' class='nombre_cliente'>Mensaje: &nbsp;</td><td>&nbsp;";
                        echo "<textarea name='Nota' class='texto_tablas' cols='80' rows='8'>" . $mensajeVO->getNota() . "</textarea>";
                        echo "</td></tr>";

                        echo "<tr><td colspan='2' align='center'>";
                        if (is_numeric($busca)) {
                            echo "<input type='submit' class='nombre_cliente' name='Boton' value='Actualizar'>";
                        } else {
                            echo "<input type='submit' class='nombre_cliente' name='Boton' value='Agregar'>";
                        }
                        echo "</td><tr>";

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