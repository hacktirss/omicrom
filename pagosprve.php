<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("comboBoxes.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$self = utils\HTTPUtils::self();

$Titulo = "Pagos a proveedores";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);


include_once './services/PagosProveedorService.php';

$pagosPrvDAO = new PagosPrvDAO();
$pagoPrvVO = new PagosPrvVO();
if (is_numeric($busca)) {
    $pagoPrvVO = $pagosPrvDAO->retrieve($busca);
} else {
    $pagoPrvVO->setFecha(date("Y-m-d"));
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
                $("#Proveedor").val("<?= $pagoPrvVO->getProveedor() ?>");
                $("#Concepto").val("<?= $pagoPrvVO->getConcepto() ?>");
                $("#Importe").val("<?= $pagoPrvVO->getImporte() ?>");
                $('#Fecha').val('<?= $pagoPrvVO->getFecha() ?>').attr('size', '10').addClass('texto_tablas');
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
                    <a href="pagosprv.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">
                    <form name="form1" id="form1" method="post" action="">
                        <table style="width: 95%; border: 0px; padding: 0px;" class="texto_tablas" aria-hidden="true">
                            <?php
                            cInput("Id:", "Text", "5", "Id", "right", $busca, "40", false, true, "");

                            cInput("Fecha: ", "Text", "20", "Fecha", "right", "", "20", true, false, "&nbsp <img src='libnvo/calendar.png' id='cFecha'>", " required");

                            echo "<tr><td align='right' class='nombre_cliente' bgcolor='#e1e1e1'> Proveedor: &nbsp;</td>";
                            echo "<td>";
                            ComboboxProveedor::generate("Proveedor", "'Combustibles','Aceites'", "270px", " required");
                            echo "</td>";
                            echo "</tr>";

                            cInput("Concepto: ", "Text", "40", "Concepto", "right", "", "80", true, false, '', "required");

                            cInput("Importe: ", "Text", "10", "Importe", "right", "", "40", true, false, '', "required");

                            echo "<tr><td colspan='2' align='center'>";
                            if (is_numeric($busca)) {
                                echo "<input type='submit' class='nombre_cliente' name='Boton' value='Actualizar'>";
                            } else {
                                echo "<input type='submit' class='nombre_cliente' name='Boton' value='Agregar'>";
                            }
                            echo "</td><tr>";
                            ?>
                        </table>
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

