<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();

require_once "./services/TransferenciasService.php";

$Titulo = "Cancelacion de Transferencia";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

if (is_numeric($busca)) {
    $sql = "SELECT transf.*,inv.descripcion FROM transf LEFT JOIN inv ON transf.producto = inv.id WHERE transf.id = " . $busca;
    $CpoA = $mysqli->query($sql);
    $Cpo = $CpoA->fetch_array();
}
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom.php"; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                var busca = "<?= $busca ?>";
                $("#busca").val(busca);
                if (busca !== "NUEVO") {
                    $("#Password").focus();
                }
            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;width: 50px; vertical-align: middle;" class="nombre_cliente">
                    <a href="transferencias.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">
                    <form name="form1" id="form1" method="post" action="">

                        <?php
                        if (is_numeric($busca)) {
                            cTable("99%", "0");

                            cInput("Id:", "Text", "5", "Id", "right", $busca, "40", false, true, "");
                            cInput("Fecha: ", "Text", "40", "Fecha", "right", $Cpo["fecha"], "80", true, true, "");
                            cInput("Corte: ", "Text", "40", "Corte", "right", $Cpo["corte"], "80", true, true, "");
                            cInput("Producto: ", "Text", "40", "Producto", "right", $Cpo["producto"], "80", true, true, "");
                            cInput("Cantidad: ", "Text", "40", "Cantidad", "right", $Cpo["cantidad"], "80", true, true, "");
                            cInput("Posicion: ", "Text", "40", "Posicion", "right", $Cpo["posicion"], "80", true, true, "");
                            cInput("Cancelar salida: ", "Password", "20", "Password", "right", "", "40", false, false, " <input type='submit' name='Boton' value='Cancelar' class='nombre_cliente'/>", " placeholder='******'");

                            cTableCie();
                        }
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