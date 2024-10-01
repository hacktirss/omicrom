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

$Titulo = "Detalle de producto";
$nameSession = "catalogoProductos";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);
$Rubro = utils\HTTPUtils::getSessionBiValue($nameSession, "Rubro");

require_once "./services/ProductosService.php";

$arrayCategorias = array("" => "Ninguna");

$productoDAO = new ProductoDAO();
$productoVO = new ProductoVO();
$productoVO->setActivo("Si");
$productoVO->setExistencia(0);
if ($Rubro == 1) {
    $arrayRubro = array("Aceites" => "Aceites");
    $arrayCategorias["Aceites"] = "Aceites";
    $arrayCategorias["Aditivos"] = "Aditivos";
} else {
    $arrayRubro["Puntos"] = "Puntos";
    $arrayRubro["Seguro"] = "Seguro";
    $arrayRubro["Servicio"] = "Servicio";
    $arrayRubro["Ent-pipas"] = "Ent-pipas";
    $arrayRubro["Otros"] = "Otros";
}

if (is_numeric($busca)) {
    $productoVO = $productoDAO->retrieve($busca);
} else {
    if ($Rubro == 1) {
        $productoVO->setRubro("Aceites");
    } else {
        $productoVO->setRubro("Otros");
    }
}

$selectDetalle = "
                SELECT invd.* FROM inv,invd,man WHERE 1=1 
                AND inv.id = invd.id AND invd.isla_pos = man.isla_pos
                AND man.inventario = 'Si' AND man.activo = 'Si' AND inv.id = '$busca' 
                GROUP BY invd.isla_pos;";
$rows = utils\IConnection::getRowsFromQuery($selectDetalle);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom.php"; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#busca").val("<?= $busca ?>");
                $("#Descripcion").val("<?= $productoVO->getDescripcion() ?>");
                $("#cumedida").val("<?= $productoVO->getInv_cunidad() ?>");
                $("#common_claveps").val("<?= $productoVO->getInv_cproducto() ?>");
                $("#Ncc_vt").val("<?= $productoVO->getNcc_vt() ?>");
                $("#Ncc_cv").val("<?= $productoVO->getNcc_cv() ?>");
                $("#Ncc_al").val("<?= $productoVO->getNcc_al() ?>");
                $("#Codigo").val("<?= $productoVO->getCodigo() ?>");
                $("#Existencia").val("<?= $productoVO->getExistencia() ?>");
                $("#Precio").val("<?= $productoVO->getPrecio() ?>");
                $("#cRubro").val("<?= $productoVO->getRubro() ?>");
                $("#Categoria").val("<?= $productoVO->getCategoria() ?>");
                $("#Minimo").val("<?= $productoVO->getMinimo() ?>");
                $("#Maximo").val("<?= $productoVO->getMaximo() ?>");
                $("#Activo").val("<?= $productoVO->getActivo() ?>");
                $("#Clave_producto").val("<?= $productoVO->getClave_producto() ?>");
                $("#FactorIva").val("<?= $productoVO->getFactorIva() ?>");
                $("#UltimoCosto").val("<?= $productoVO->getCosto() ?>");

                $("#Porcentaje").val("<?= $productoVO->getPorcentaje() ?>");
                $("#LastCost").val("<?= $productoVO->getCosto() ?>").addClass("clase-3").removeClass("clase-5").prop("disabled", true);
                if ("<?= $productoVO->getRetiene_iva() ?>" == "Si") {
                    $("#Retiene_iva_si").prop("checked", true);
                } else {
                    $("#Retiene_iva_no").prop("checked", true);
                }
                $("#AverageCost").val("<?= $productoVO->getCosto_prom() ?>").prop("disabled", true);
                if ("<?= $usuarioSesion->getTeam() !== UsuarioPerfilDAO::PERFIL_ADMIN ?>") {
                    $("#Existencia").prop("disabled", true);
                }

                $("#formulario1").submit(function (e) {
                    let minimo = parseInt($("#Minimo").val());
                    let maximo = parseInt($("#Maximo").val());
                    $("#Response").html("");
                    if (minimo > maximo) {
                        e.preventDefault();
                        $("#Response").html("El valor de stock maximo debe ser mayor o igual que el minimo!");
                        clicksForm = 0;
                        return false;
                    }
                    return true;
                });
                $("#Retiene_iva_no").on("click", function () {
                    $("#Retiene_iva_si").prop("checked", false);
                });
                $("#Retiene_iva_si").on("click", function () {
                    $("#Retiene_iva_no").prop("checked", false);
                });

            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="productos.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">
                    <div id="Formularios">
                        <?php
                        abrirFormulario("formulario1");
                        crearInputNumber("Clave", "Clave_producto", 0, 99999999999, $siRequerido, "", $clase1, "Posición del producto dentro del catalogo");
                        crearInputText("Descripción", "Descripcion", 300, $siMayusculas, $siRequerido, "", $clase2, " Id: $busca");
                        crearInputSelect("Unidad de Medida", "cumedida", CatalogosSelectores::getUnidades(), $siRequerido, $clase2, "&nbsp;<i class=\"icon fa fa-lg fa-question-circle\" title='Para habilitar otra unidad de medida favor de comunicarse a Soporte' height='18' aria-hidden=\"true\"></i>");
                        crearInputSelect("Clave de Producto", "common_claveps", CatalogosSelectores::getProductoServicio(), $siRequerido, $clase2, "&nbsp;<i class=\"icon fa fa-lg fa-search-plus\"' title='Busca Producto/Servicio' onclick=\"location='categoriasSAT.php?busca=$busca'\" aria-hidden=\"true\"></i>&nbsp;<i class=\"icon fa fa-lg fa-question-circle\" title='En caso de requerir alguna nueva clave, favor de dar click en la lupa y en el campo de buscar de una descripcion del producto, elija el que mas se asemeje a su producto' height='18' aria-hidden=\"true\"></i>");
                        crearInputText("Número de cuenta contable", "Ncc_vt", 20, $siMayusculas, "", "", $clase2, "<small>(Total de Venta)</small>");
                        crearInputText("Número de cuenta contable", "Ncc_cv", 20, $siMayusculas, "", "", $clase2, "<small>(Costo de Venta)</small>");
                        crearInputText("Número de cuenta contable", "Ncc_al", 20, $siMayusculas, "", "", $clase2, "<small>(Almacen)</small>");
                        crearInputTextBy2("Código de Barras", "Exist.almacen", "Codigo", "Existencia", 20, 20, "", "", "", "", $clase2, $tipoText, $tipoText);
                        crearInputTextBy2("Stock Mínimo", "Máximo", "Minimo", "Maximo", 10, 16, "", "", "", "", $clase2, $tipoNumber, $tipoNumber, null, null, " &nbsp;(Formato 1)", 0, 6999);
                        crearInputTextBy2("U.Rubro", "Activo", "cRubro", "Activo", 0, 0, "", "", "", $siRequerido, $clase2, $tipoSelect, $tipoSelect, $arrayRubro, ListasCatalogo::getArrayList("SI NO"), " Activar en reportes", 0, 0);
                        crearInputSelect("Categoria", "Categoria", $arrayCategorias, "", $clase2, "");
                        crearInputTextBy2("Último costo $", "Costo promedio $", "LastCost", "AverageCost", 16, 16, "", "", "", "", $clase2, $tipoText, $tipoText);
                        crearInputText("Precio venta con iva", "Precio", 10, $siMayusculas, $siRequerido, "", $clase0, "* En caso de que el rubro sea puntos");
                        crearInputText("Ultimo Costo", "UltimoCosto", 10, $siMayusculas, $siRequerido, "", $clase0, "* Habilitado momentaneamente");
                        ?>
                        <div class="grupo1">
                            <div>
                                Retiene iva:
                            </div>
                            <div>
                                <div class="clase-2">
                                    <div>
                                        <input type="checkbox" name="Retiene_iva" value="Si" id="Retiene_iva_si"> Si 
                                        <input type="checkbox" name="Retiene_iva" value="No" id="Retiene_iva_no"> No
                                    </div>
                                    <div>
                                        Porcentaje:
                                    </div>
                                    <div>
                                        <input type="text" name="Porcentaje" id="Porcentaje" class="clase-5">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grupo1">
                            <div>
                                % de IVA:
                            </div>
                            <div>
                                <input type="number" name="FactorIva" id="FactorIva" max="16" min="0">
                            </div>
                        </div>
                        <?php
                        crearInputHidden("Rubro");
                        crearBoton("Boton", is_numeric($busca) ? $request->hasAttribute("Facturar") ? "Facturar" : "Actualizar" : "Agregar");
                        crearInputHidden("busca");
                        cerrarFormulario();
                        ?>
                    </div>
                    <div>
                        <div style="text-align: center" class="subtitulos">Configuración de stock por isla <span style="font-size: 11px;"><sub>(Formato 2)</sub></span></div>
                    </div>
                    <div id="TablaDatos" style="min-height: 100px">
                        <table class="paginador" aria-hidden="true" style="width: 50%;margin-left: auto;margin-right: auto;">
                            <tr>
                                <th scope="col">Editar</th>
                                <th scope="col">Isla/Disp.</th>
                                <th scope="col">Minimo</th>
                                <th scope="col">Maximo</th>
                                <th scope="col">Existencia Actual</th>
                            </tr>
                            <?php foreach ($rows as $value) { ?>
                                <tr style="text-align: center">
                                    <td><div data-id="<?= $value["idnvo"] ?>" class="nombre_cliente Editar"><i class="icon fa fa-lg fa-edit" aria-hidden="true"></i></div></td>
                                    <td><?= $value["isla_pos"] ?></td>
                                    <td><?= $value["minimo"] ?></td>
                                    <td><?= $value["maximo"] ?></td>
                                    <td><?= $value["existencia"] ?></td>
                                </tr>
                            <?php } ?>
                        </table>
                    </div>
                    <div id="Formularios">
                        <form name="formulario2" method="post" action="">
                            <table aria-hidden="true" style="width: 50%;margin-left: auto;margin-right: auto;">
                                <tr style="height: 40px;">
                                    <td>Isla o Dispensario:</td>
                                    <td style="text-align: left;padding-left: 1px">
                                        <div id="Isla" style="font-weight: bold;">0</div>
                                    </td>
                                    <td>Mínimo:</td>
                                    <td style="text-align: left;padding-left: 5px">
                                        <input type="number" name="Minimo" id="MinimoD" min="0" max="1000"/>
                                    </td>
                                    <td>Máximo:</td>
                                    <td style="text-align: left;padding-left: 5px">
                                        <input type="number" name="Maximo" id="MaximoD" min="0" max="5000"/>
                                    </td>
                                    <td><span><input type="submit" name="BotonD" value="Actualizar" id="BotonD" disabled="disabled"></span></td>
                                </tr>
                            </table>
                            <input type="hidden" name="InvIslaPos" id="InvIslaPos">
                        </form>
                    </div>
                </td>
            </tr>
        </table>

        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

        <script>
            $(".Editar").click(function () {

                let element = $(this);
                let value = element.data("id");

                $.ajax({
                    url: "getTicket.php",
                    type: "post",
                    data: {"InvIslaPos": value},
                    dataType: "json",
                    success: function (response) {
                        console.log(response);

                        $("#InvIslaPos").val(value);
                        $("#Isla").html(response.isla_pos);
                        $("#MinimoD").val(response.minimo);
                        $("#MaximoD").val(response.maximo);
                        $("#BotonD").prop("disabled", false);
                        $("#MinimoD").focus();

                    },
                    error: function (jqXHR, ex) {
                        console.log("Status: " + jqXHR.status);
                        console.log("Uncaught Error.\n" + jqXHR.responseText);
                        console.log(ex);
                    }
                });

            });
        </script>
    </body>
</html>