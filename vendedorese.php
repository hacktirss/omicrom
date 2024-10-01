<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("comboBoxes.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();

$Titulo = "Vendedores detalle";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

require_once "./services/VendedoresService.php";

$vendedorDAO = new VendedorDAO();
$vendedorVO = new VendedorVO();
$vendedorVO->setActivo("Si");

if (is_numeric($busca)) {
    $vendedorVO = $vendedorDAO->retrieve($busca);
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
                $("#Activo").val("<?= $vendedorVO->getActivo() ?>");
                $("#Nombre").val("<?= html_entity_decode($vendedorVO->getNombre()) ?>");
                $("#Alias").val("<?= html_entity_decode($vendedorVO->getAlias()) ?>");
                $("#Direccion").val("<?= $vendedorVO->getDireccion() ?>");
                $("#Colonia").val("<?= $vendedorVO->getColonia() ?>");
                $("#Municipio").val("<?= $vendedorVO->getMunicipio() ?>");
                $("#Telefono").val("<?= $vendedorVO->getTelefono() ?>");
                $("#Ncc").val("<?= $vendedorVO->getNcc() ?>");
                $("#Nip").val("<?= $vendedorVO->getNip() ?>");
                $("#Ncc").val("<?= $vendedorVO->getNcc() ?>");
                $("#NumeroEmpleado").val("<?= $vendedorVO->getNum_empleado() ?>");
                $("#Nombre").focus();
                $("#Resp").hide();
                $("#ShowNip").click(function () {
                    $("#Nip").attr('type', 'text');

                });
                $("#Nip").change(function () {
                    if ($("#Nip").val() > 0 & $("#Nip").val() <= 9999) {
                        $("#Nip").css("background-color", "#ABEBC6");
                        $("#RespNip").hide();
                        $("#Resp").hide();
                    } else {
                        $("#Nip").css("background-color", "#F5B7B1");
                        $("#Resp").show();
                        $("#RespNip").show();
                        $("#RespNip").html("El valor del Nip debe de estar entre los valores 1 y 9999").css("color", "#A93226");
                    }
                });
            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="vendedores.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">
                    <div id="FormulariosBoots">
                        <div class="container no-margin">
                            <div class="row no-padding">
                                <div class="col-8 background no-margin">
                                    <form name="formulario1" id="formulario1" method="post" action="">
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Id: </div>
                                            <div class="col-4"><?= $busca ?></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Nombre: </div>
                                            <div class="col-4">
                                                <input type="text" style="width: 300px;" name="Nombre" id="Nombre" maxlength="40" class="clase-<?= $clase2 ?>" required/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Numero de Empleado: </div>
                                            <div class="col-4">
                                                <input type="text" style="width: 300px;" name="NumeroEmpleado" id="NumeroEmpleado" maxlength="40" class="clase-<?= $clase2 ?>" required/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Alias: </div>
                                            <div class="col-4">
                                                <input type="text" style="width: 300px;" name="Alias" id="Alias" maxlength="15" class="clase-<?= $clase2 ?>" required/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">No. Cuenta Contable: </div>
                                            <div class="col-4">
                                                <input type="text" style="width: 300px;" name="Ncc" id="Ncc" maxlength="15" class="clase-<?= $clase2 ?>" required/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Nip: </div>
                                            <div class="col-3">
                                                <input type="password" style="width: 100px;" min="1" max="9999" name="Nip" id="Nip" placeholder="En caso de ser necesario" class="clase-<?= $clase2 ?>"/>
                                            </div>
                                            <div class="col-1">
                                                <?php
                                                if ($usuarioSesion->getTeam() === "Administrador") {
                                                    ?><i class="fa fa-eye fa-lg" id="ShowNip" aria-hidden="true" title="Muestra clave del vendedor"></i><?php
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <div id="Resp" class="row no-padding">
                                            <div class="col-8 align-right"><div id="RespNip"></div></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Activo: </div>
                                            <div class="col-2">
                                                <select id="Activo" name="Activo">
                                                    <option value="Si">SI</option>
                                                    <option value="No">NO</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-8 align-center">
                                                <?php
                                                crearBoton("Boton", is_numeric($busca) ? "Actualizar" : "Agregar");
                                                crearInputHidden("busca");
                                                ?>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="mensajeInput col-11 align-right">
                                                (<sup><i style="color: red;font-size: 8px;" class="fa fa-lg fa-asterisk" aria-hidden="true"></i></sup>) Campos necesarios para control de venta
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
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