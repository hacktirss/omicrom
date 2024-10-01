<?php
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$self = utils\HTTPUtils::self();
$Tabla = "cxch";

$nameVarBusca = "busca";
if($request->hasAttribute($nameVarBusca)){
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif($request->hasAttribute("id")){
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);
        
$Titulo = "Edita archivo de movimientos[$busca]";

$cSql = "SELECT $Tabla.*, CONCAT(cli.id, ' | ', cli.tipodepago, ' | ', cli.nombre) clienteDescripcion 
        FROM $Tabla  LEFT JOIN cli ON $Tabla.cliente = cli.id 
        WHERE $Tabla.id='$busca'";
$CpoA = $mysqli->query($cSql);
$Cpo = $CpoA->fetch_array();

?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
       <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#busca").val("<?= $busca ?>");

                if ($("#busca").val() !== "NUEVO") {
                    $("#Boton").hide();
                } else {
                    $("#cId").val("<?= $busca ?>");
                }

                $("#Fecha").val("<?= $Cpo["fecha"] ?>").attr("size", "10").addClass("texto_tablas");
                $("#cFecha").css("cursor", "hand").click(function () {
                    displayCalendar($("#Fecha")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#Tm").val("<?= $Cpo["tm"] ?>");
                $("#Placas").val("<?= $Cpo["placas"] ?>");
                $("#Referencia").val("<?= $Cpo["referencia"] ?>");
                $("#Concepto").val("<?= $Cpo["concepto"] ?>");
                $("#Factura").val("<?= $Cpo["factura"] ?>");
                $("#Importe").val("<?= $Cpo["importe"] ?>");

                $("#autocomplete").val("<?= $Cpo["clienteDescripcion"] ?>");
            });
        </script>
    </head>

    <body>

         <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="editacxch.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">
                    <form name="form1" id="form1" method="post" action="">
                        <table style="width: 95%" aria-hidden="true">
                            <tbody>
                                <?php
                                cInput("Id:", "text", "4", "busca", "right", $busca, "4", true, true, "");
                                cInput("Fecha:", "text", "10", "Fecha", "right", "", "10", true, false, "<img id=\"cFecha\" src=\"libnvo/calendar.png\">", "");
                                ?>
                                <tr class="nombre_cliente">
                                    <td align="right" bgcolor="#e1e1e1">Cliente: &nbsp;</td>
                                    <td valign="middle"> 
                                        <div style="position: relative;">
                                            &nbsp;<input type="search" size="50" class="texto_tablas" placeholder="Buscar cliente" name="ClienteS" id="autocomplete" required="required">
                                        </div>
                                        <div id="autocomplete-suggestions"></div>
                                    </td>
                                </tr>

                                <tr class="nombre_cliente">
                                    <td align="right" bgcolor="#e1e1e1"> Tipo de movto: &nbsp;</td>
                                    <td>&nbsp;<select name="Tm" id="Tm" class="texto_tablas" style="width: 200px;" required>
                                            <option value="C">Cargo</option>
                                            <option value="H">Abono</option>
                                        </select>
                                    </td>
                                </tr>
                                <?php
                                cInput("Placas:", "text", "10", "Placas", "right", "", "10", true, false, " ", ' style="width: 200px;"');
                                cInput("No.ticket:", "number", "10", "Referencia", "right", "", "10", true, false, "", ' min="0" max="10000000"  style="width: 200px;"');
                                cInput("Concepto:", "text", "40", "Concepto", "right", "", "40", true, false, "", ' style="width: 200px;"');
                                cInput("Factura:", "text", "10", "Factura", "right", "", "10", true, false, "", ' style="width: 200px;"');
                                cInput("Importe:", "text", "10", "Importe", "right", "", "10", true, false, "", ' style="width: 200px;"');

                                ?>
                               
                            </tbody>
                        </table>
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

