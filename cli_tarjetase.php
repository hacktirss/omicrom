<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();

$Titulo = "Tarjetas prepago y credito";

if ($request->hasAttribute("busca")) {
    utils\HTTPUtils::setSessionValue("busca", $request->getAttribute("busca"));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue("busca", $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue("busca");

if ($request->hasAttribute("Boton")) {
    $estado = $request->getAttribute("Estado");

    $updateUnidades = "UPDATE unidades SET estado ='$estado' WHERE id = '$busca'";
    if ($mysqli->query($updateUnidades)) {
        $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
    } else {
        $Msj = utils\Messages::RESPONSE_ERROR;
    }

    header("Location: cli_tarjetas.php?Msj=$Msj");
}

$selectUnidad = "SELECT codigo,litros,importe,LOWER(estado) estado FROM unidades WHERE id = '$busca'";
$Cpo = utils\IConnection::execSql($selectUnidad);
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php include './config_omicrom_clientes.php'; ?>   
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#busca").val("<?= $busca ?>");
                $("#Estado").val("<?= $Cpo["estado"] ?>");
            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(TRUE); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="cli_tarjetas.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">
                    <form name="form1" id="form1" method="post" action="">

                        <?php
                        cTable("99%", "0");
                        cInput("Codigo de barras:", "Text", "20", "Codigo", "right", $Cpo["codigo"], "30", true, true);

                        cInput("Litros:", "Text", "20", "Litros", "right", $Cpo["litros"], "30", true, true);

                        cInput("Importe:", "Text", "20", "Importe", "right", $Cpo["importe"], "30", true, true);


                        echo "<tr><td align='right' bgcolor=#e1e1e1 class=nombre_cliente>Status: </td><td>";
                        echo "<select name='Estado' id='Estado' class='texto_tablas'>";
                        echo "<option value='a'>Activo</option>";
                        echo "<option value='d'>Inactivo</option>";
                        echo "</select></td></tr>";

                        echo "<tr><td colspan='2' align='center'><br/>";
                        if (is_numeric($busca)) {
                            echo "<input type='submit' class='nombre_cliente' name='Boton' value='Actualizar'>";
                        } else {
                            echo "<input type='submit' class='nombre_cliente' name='Boton' value='Agregar'>";
                        }
                        echo "</td></tr>";

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