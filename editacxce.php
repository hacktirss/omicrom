<?php
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$self = utils\HTTPUtils::self();

$Titulo = "Edita archivo de cobranza";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

require_once "./services/CxcService.php";

$editarcxcDAO = new CxcDAO();

$editarcxcVO = new CxcVO();
$editarcxcVO->setFecha(date("Y-m-d"));
$editarcxcVO->setReferencia(0);
$editarcxcVO->setTm("C");
$editarcxcVO->setCliente(0);
if (is_numeric($busca)) {
    $editarcxcVO = $editarcxcDAO->retrieve($busca);
}
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom.php"; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#busca").val("<?= $busca ?>");

                if ($("#busca").val() !== "NUEVO") {
                    $("#Boton").hide();
                    $("#cId").val("<?= $busca ?>");
                }

                $("#Fecha").val("<?= $editarcxcVO->getFecha() ?>").attr("size", "10").addClass("texto_tablas");
                $("#cFecha").css("cursor", "hand").click(function () {
                    displayCalendar($("#Fecha")[0], "yyyy-mm-dd", $(this)[0]);
                });
                //$("#Cliente").val("<?= $editarcxcVO->getCliente() ?>");
                $("#Tm").val("<?= $editarcxcVO->getTm() ?>");
                $("#Placas").val("<?= $editarcxcVO->getPlacas() ?>");
                $("#Referencia").val("<?= $editarcxcVO->getReferencia() ?>");
                $("#Concepto").val("<?= $editarcxcVO->getConcepto() ?>");
                $("#Factura").val("<?= $editarcxcVO->getFactura() ?>");
                $("#Importe").val("<?= $editarcxcVO->getImporte() ?>");

                $("#autocomplete").activeComboBox(
                        $("[name='form1']"),
                        "SELECT id as data, CONCAT(id, ' | ', tipodepago, ' | ', nombre) value FROM cli " +
                        "WHERE cli.id >= 10 AND cli.tipodepago NOT REGEXP 'Contado|Puntos'",
                        "nombre"
                        );
                $("#autocomplete").val("<?= $editarcxcVO->getClienteDescripcion() ?>");
            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="editacxc.php"><div class="RegresarCss " style="">Regresar</div></a>
                </td>
                <td style="vertical-align: top;">
                    <form name="form1" id="form1" method="post" action="">
                        <table style="width: 95%" aria-hidden="true">
                            <tbody>
                                <?php
                                cInput("Id:", "text", "4", "busca", "right", $busca, "4", true, true, "");
                                cInput("Fecha:", "text", "10", "Fecha", "right", "", "10", true, false, "<i class=\"fa-regular fa-calendar-plus fa-2xl\" style=\"color: #117A65;padding-left: 5px;\" id=\"cFecha\" alt=\"Calendario\"></i>", "");
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
                                cInput("Concepto:", "text", "40", "Concepto", "right", "", "40", true, false, "", ' style="width: 200px;"', "required");
                                cInput("Factura:", "text", "10", "Factura", "right", "", "10", true, false, "", ' style="width: 200px;"');
                                cInput("Importe:", "text", "10", "Importe", "right", "", "10", true, false, "", ' style="width: 200px;"');

                                if (is_numeric($busca)) {
                                    cInput("Clave de cancelacion: ", "Password", "20", "Password", "right", "", "40", false, false, '<input type="submit" class="nombre_cliente" name="BotonE" value="Cancelar">', ' placeholder="********" style="width: 200px;"');
                                    echo '<input type="hidden" name="op" value="Si"><input type="hidden" name="cId" id="cId">';
                                }
                                ?>
                                <tr>
                                    <td colspan="2" align="center">
                                        <input type="submit" class="nombre_cliente" name="Boton" id="Boton" value="Agregar">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <input type="hidden" name="busca" id="busca">
                    </form>

                </td>
            </tr>
        </table>
        <script type="text/javascript">
            $(document).ready(function () {
                $("#Importe").keypress(function () {
                    setTimeout(function () {
                        if (parseInt($("#Importe").val()) < 0) {
                            confirmSwal();
                            $("#Importe").val("").focus();
                        }
                    }, 150);
                });
            });
            function confirmSwal() {
                console.log($("#Importe").val());
                Swal.fire({
                    title: "¡El importe ingresado tiene que ser mayor a $0.01!<br>\n\
        Favor de verificar",
                    icon: "error",
                    background: "#F5B7B1",
                    position: "center",
                    timer: 3000
                });
            }
        </script>

        <?php
        BordeSuperiorCerrar();

        PieDePagina();
        ?>

    </body>

</html>

