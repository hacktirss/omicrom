<?php
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("comboBoxes.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();

$nameVarBusca = "busca";
if($request->hasAttribute($nameVarBusca)){
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif($request->hasAttribute("id")){
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);
$Titulo = "Edita archivo de movimientos[$busca]";

$selectCxp = "SELECT * FROM cxp WHERE id = $busca";
$Cpo = utils\IConnection::execSql($selectCxp);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>'
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#Tm").val("<?= $Cpo["tm"] ?>");
                $("#Proveedor").val("<?= $Cpo["proveedor"] ?>");
                $("#busca").val("<?= $busca ?>");

            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="editacxp.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">
                    <form name="form1" id="form1" method="post" action="">

                        <?php
                        cTable('90%', '0');

                        cInput('Id:', 'text', '4', 'Id', 'right', $busca, '4', true, true, '');
                        cInput('Fecha:', 'text', '10', 'Fecha', 'right', $Cpo["fecha"], '10', true, false, '');

                        echo "<tr><td align='right' class='nombre_cliente' bgcolor='#e1e1e1'> Proveedor: &nbsp;</td>";
                        echo "<td>";
                        ComboboxProveedor::generate("Proveedor", "'Otros','Combustibles','Aceites'", "270px", " required='required'");
                        echo "</td>";
                        echo "</tr>";

                        echo "<tr  class='nombre_cliente'><td align='right' bgcolor='#e1e1e1'> Tipo de movto : &nbsp;</td><td> ";
                        echo "<select name='Tm' id='Tm' class='texto_tablas'>";
                        echo "<option value='C'>Cargo</option>";
                        echo "<option value='H'>Abono</option>";
                        echo "</select>";
                        echo "</td></tr>";

                        cInput('Concepto:', 'text', '40', 'Concepto', 'right', $Cpo["concepto"], '40', true, false, '');
                        cInput('Importe:', 'text', '10', 'Importe', 'right', $Cpo["importe"], '10', true, false, '');

                        cTableCie();
                        ?>
                        <input type='hidden' name='busca' id="busca">
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

