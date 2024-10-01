<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("comboBoxes.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$self = utils\HTTPUtils::self();

$nameSession = "catalogocomprasdiversas";

$Titulo = "Detalle de compras otros";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);
$Rubro = utils\HTTPUtils::getSessionBiValue($nameSession, "Rubro");

require_once './services/ComprasDiversasService.php';

$comprasoeVO = new ComprasoeVO();
$proveedorVO = new ProveedorVO();
if (is_numeric($busca)) {
    $comprasoeVO = $comprasoeDAO->retrieve($busca);
    $proveedorVO = $proveedorDAO->retrieve($comprasoeVO->getProveedor());
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
                $("#Concepto").val("<?= $comprasoeVO->getConcepto() ?>");
                $("#Proveedor").val("<?= $comprasoeVO->getProveedor() ?>");
                $("#Importe").val("<?= $comprasoeVO->getImporte() ?>");
                $("#Documento").val("<?= $comprasoeVO->getDocumento() ?>");

                $('#Fecha').val('<?= $comprasoeVO->getFechav() ?>').attr('size', '10').addClass('texto_tablas');
                $('#cFecha').css('cursor', 'hand').click(function () {
                    displayCalendar($('#Fecha')[0], 'yyyy-mm-dd', $(this)[0]);
                });
            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="compraso.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">
                    <form name="form1" id="form1" method="post" action="">

                        <?php
                        cTable("99%", "0");

                        cInput("Id:", "Text", "5", "Id", "right", $busca, "40", false, true, "");

                        echo "<tr><td align='right' class='nombre_cliente' bgcolor='#e1e1e1'> Proveedor: &nbsp;</td>";
                        echo "<td>";
                        ComboboxProveedor::generate("Proveedor", "'Otros'", "270px", " required='required'");
                        echo "</td>";
                        echo "</tr>";

                        cInput("Fecha: ", "text", "20", "", "right", $comprasoeVO->getFecha(), "20", true, true, "");

                        cInput("Fecha de vencimiento: ", "text", "10", "Fecha", "right", "", "10", true, false, "&nbsp <img src='libnvo/calendar.png' id='cFecha'>", "required=\"true\" ");

                        cInput("No.de factura:", "Text", "20", "Documento", "right", "", "20", true, false, ' &oacute; numero de docto. que ampare la compra');

                        cInput("Concepto:", "Text", "50", "Concepto", "right", "", "50", true, false);

                        cInput("Importe total:", "Text", "20", "Importe", "right", "", "20", true, false, ' (Con impuestos)', "required='required'");

                        echo "<tr><td colspan='2' align='center'>";
                        if (is_numeric($busca)) {
                            echo "<input type='submit' class='nombre_cliente' name='Boton' value='Actualizar'>";
                        } else {
                            echo "<input type='submit' class='nombre_cliente' name='Boton' value='Agregar'>";
                        }
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

