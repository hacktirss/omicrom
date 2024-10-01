<?php
#Librerias
session_start();

include_once ("check.php");
include_once ('comboBoxes.php');
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();

require './services/CombustiblesService.php';

$Titulo = "Detalle de combustibles";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$objectVO = new CombustiblesVO();
if (is_numeric($busca)) {
    $objectVO = $objectDAO->retrieve($busca, "id", false);
}
$SQL = "SELECT descripcion FROM catalogos_sat_cv WHERE id = "
        . "(SELECT unidad_medida FROM catalogos_sat_cv WHERE clave = '" . $objectVO->getCve_sub_producto_sat() . "')";

$rst = $mysqli->query($SQL);
$u_medida = $rst->fetch_array();
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="combustibles.php"><div class="RegresarCss " alt="Flecha regresar" style="">Regresar</div></a>
                </td>
                <td style="vertical-align: top;">

                    <div id="FormulariosBoots">

                        <div class="container">

                            <div class="row background">
                                <div class="col-11 align-left title">Producto: <span id="Producto"></span></div>
                                <div class="col-1"><i class="fa fa-cog fa-2x" id="EditaCntPuntos" 
                                                      title="Editar la cantidad de pesos/volumen se necesitan para obtener un punto del sistema de puntos" aria-hidden="true"></i></div>
                                <div class="col-12 align-left">Tipo producto: <span id="Tipo"></span></div>
                                <div class="col-12 align-left">Estado: <span id="Activo"></span></div>
                                <div class="col-12 align-left">Unidad de Medida: <span id="UM"></span></div>
                            </div>

                            <div class="row background">                                
                                <div class="col-12 no-margin">
                                    <form name="formulario1" id="formulario1" method="post" action="">
                                        <div class="row no-padding">
                                            <div class="col-12 align-left subtitle">Parámetros del SAT</div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right"><label class="label">Clave Producto: </label></div>
                                            <div class="col-4"><?php ListasCatalogo::getDataFromCatalogoSatCv("Cve_producto_sat", "CLAVES_PRODUCTO", "", " required=''") ?></div>
                                            <div class="col-1"><i class="fa fa-lg fa-question-circle"  aria-hidden = "true" data-toggle="modal" data-target="#modal-combustibles-listas" data-identificador="CLAVES_PRODUCTO" data-operacion="11"></i></div>
                                        </div>
                                        <div class="row no-padding" id="subProducto">
                                            <div class="col-3 align-right"><label class="label">Clave Subproducto: </label></div>
                                            <div class="col-4"><select name="Cve_sub_producto_sat" id="Cve_sub_producto_sat"></select></div>
                                            <div class="col-1"><i class="fa fa-lg fa-question-circle" aria-hidden = "true" data-toggle="modal" data-target="#modal-combustibles-listas" data-identificador="CLAVES_SUBPRODUCTO" data-operacion="11"></i></div>
                                        </div>
                                        <div class="row no-padding OcultaCampo" id="Calorifico">
                                            <div class="col-3 align-right required"><label class="label">Poder Calorifico <sup class="sup">1</sup>: </label></div>
                                            <div class="col-2"><input type="text" name="Poder_calorifico" id="Poder_calorifico" placeholder="" required=""/></div>
                                            <div class="col-4"><label for="Poder_calorifico"></label></div>
                                        </div>
                                        <div class="row no-padding OcultaCampo " id="Densid">
                                            <div class="col-3 align-right required"><label class="label">Densidad <sup class="sup">2</sup>: </label></div>
                                            <div class="col-2"><input type="text" name="Densidad" id="Densidad" placeholder="" required=""/></div>
                                            <div class="col-4"><label for="Densidad"></label></div>
                                        </div>
                                        <div class="row no-padding OcultaCampo " id="Azufre">
                                            <div class="col-3 align-right required"><label class="label">Composición de Azufre(S) <sup class="sup">3</sup>: </label></div>
                                            <div class="col-2"><input type="text" name="Comp_azufre" id="Comp_azufre" placeholder="" required=""/></div>
                                            <div class="col-4"><label for="Comp_azufre"></label></div>
                                        </div>
                                        <div class="row no-padding OcultaCampo " id="Molar">
                                            <div class="col-3 align-right required"><label class="label">Fracción Molar <sup class="sup">4</sup>: </label></div>
                                            <div class="col-2"><input type="text" name="Fraccion_molar" id="Fraccion_molar" placeholder="" required=""/></div>
                                            <div class="col-4"><label for="Fraccion_molar"></label></div>
                                        </div>
                                        <div class="row no-padding OcultaCampo " id="Gravedad">
                                            <div class="col-3 align-right required"><label class="label">Gravedad Específica : </label></div>
                                            <div class="col-2"><input type="text" name="Gravedad_especifica" id="Gravedad_especifica" placeholder="" required=""/></div>
                                            <div class="col-4"><label for="Gravedad_especifica"></label></div>
                                        </div>
                                        <div class="row no-padding OcultaCampo " id="Fosil">
                                            <div class="col-3 align-right required"><label class="label">Composición Fosil : </label></div>
                                            <div class="col-2"><input type="text" name="Comp_fosil" id="Comp_fosil" placeholder="" required=""/></div>
                                            <div class="col-4"><label for="Comp_fosil"></label></div>
                                        </div>
                                        <div class="row no-padding OcultaCampo " id="Propano">
                                            <div class="col-3 align-right required"><label class="label">Composición Propano <sup class="sup">5</sup>: </label></div>
                                            <div class="col-2"><input type="text" name="Comp_propano" id="Comp_propano" placeholder="" required=""/></div>
                                            <div class="col-4"><label for="Comp_propano"></label></div>
                                        </div>
                                        <div class="row no-padding OcultaCampo" id="Butano">
                                            <div class="col-3 align-right required"><label class="label">Composición Butano <sup class="sup">6</sup>: </label></div>
                                            <div class="col-2"><input type="text" name="Comp_butano" id="Comp_butano" placeholder="" required=""/></div>
                                            <div class="col-4"><label for="Comp_butano"></label></div>
                                        </div>
                                        <div class="row no-padding OcultaCampo" id="Octanaje">
                                            <div class="col-3 align-right required"><label class="label">Octanaje: </label></div>
                                            <div class="col-2"><input type="number" name="ComOctanajeGas" id="ComOctanajeGas" placeholder="" required="" min="0" max="99"/></div>
                                        </div>
                                        <div class="row no-padding OcultaCampo " id="Contiene_Etanol">
                                            <div class="col-3 align-right required"><label class="label">Contiene Etanol: </label></div>
                                            <div class="col-1">
                                                <div class="image-radio" data-valor="Si" id="Radio-Si">
                                                    <i class="icon fa fa-lg fa-circle-o" aria-hidden = "true"></i> Si
                                                </div>
                                            </div>
                                            <div class="col-1">
                                                <div class="image-radio" data-valor="No" id="Radio-No">
                                                    <i class="icon fa fa-lg fa-check-circle-o" aria-hidden = "true"></i> No
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row no-padding OcultaCampo" id="TieneEtanol">
                                            <div class="col-3 align-right required"><label class="label">% Etanol: </label></div>
                                            <div class="col-2"><input type="number" name="ComDeEtanolEnGasolina" id="ComDeEtanolEnGasolina" placeholder="" min="0" max="99"/></div>
                                        </div> 
                                        <div class="row no-padding ">
                                            <div class="col-3 align-right required"><label class="label">Color : </label></div>
                                            <div class="col-2">
                                                <select name="Color" id="Color">
                                                    <?php
                                                    foreach ($colors as $Key => $value) {
                                                        ?>
                                                        <option style="color:<?= $Key ?>;" value="<?= $Key ?>">
                                                            &#x25FC; <?= $Key ?>
                                                        </option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right"></div>
                                            <div class="col-2"><button type="submit" class="btn-boots" name="Boton" value="Actualizar">Actualizar</button></div>
                                        </div>
                                        <input type="hidden" name="busca" class="busca"/>
                                        <input type="hidden" name="GasConEtanol" id="GasConEtanol"/>
                                    </form>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 no-margin" style="font-size: 10px; color:#55514e;">

                                    <div class="row no-padding">
                                        <div class="col-12">
                                            <p>
                                                <strong class="sup">1. Poder Calorifico : </strong>expresar el poder calorífico por cada componente expresado en “ComposGasNaturalOCondensados”, expresado en BTU/pie cúbico para el gas natural y MMBTU tratándose de condensados.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row no-padding">
                                        <div class="col-12 ">
                                            <p>
                                                <strong  class="sup">2. Densidad : </strong>
                                                Condicional tratándose de sujetos que hayan manifestado ser “contratista” o “asignatario” en el elemento 
                                                “TipoCaracter”, en caso de que haya seleccionado el producto PR08, para expresar la densidad del petróleo,
                                                expresada en °API. Deberá tener un valor mínimo de 0.1 a 80.0.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row no-padding">
                                        <div class="col-12">
                                            <p>
                                                <strong class="sup">3. Composición Azufre : </strong>
                                                Condicional tratándose de sujetos que hayan manifestado ser
                                                “contratista” o “asignatario” en el elemento “TipoCaracter" en
                                                caso de que haya seleccionado el producto PR08, para
                                                expresar el porcentaje de azufre en el petróleo. Deberá tener
                                                un valor mínimo de 0.1 a 10.0.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row no-padding">
                                        <div class="col-12">
                                            <p>
                                                <strong class="sup">4. Fracción Molar : </strong>
                                                Requerido para expresar la fracción molar por cada
                                                componente expresado en
                                                “ComposGasNaturalOCondensados”. Deberá tener un valor
                                                mínimo de 0 a 0.999 por cada componente de la mezcla. La
                                                suma de la fracción molar de todos los componentes debe ser
                                                igual a 1.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row no-padding">
                                        <div class="col-12">
                                            <p>
                                                <strong class="sup">5. Composición Propano : </strong>
                                                condicional en caso de que haya seleccionado el producto
                                                PR12, para expresar el porcentaje normalizado de propano en
                                                el gas licuado de petróleo, conforme a los resultados del
                                                dictamen emitido por el proveedor del servicio de emisión de
                                                dictámenes que determinen el tipo de hidrocarburo o
                                                petrolífero de que se trate. Deberá tener un valor mínimo de
                                                0.01 a 99.99.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row no-padding">
                                        <div class="col-12">
                                            <p>
                                                <strong class="sup">6. Composición Butano : </strong>
                                                condicional en caso de que haya seleccionado el producto
                                                PR12, para expresar el porcentaje normalizado de butano en el
                                                gas licuado de petróleo, conforme a los resultados del
                                                dictamen emitido por el proveedor del servicio de emisión de
                                                dictámenes que determinen el tipo de hidrocarburo o
                                                petrolífero de que se trate. Deberá tener un valor mínimo de
                                                0.01 a 99.99.
                                            </p>
                                        </div>
                                    </div>
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
        <link rel="stylesheet" href="bootstrap/bootstrap-4.0.0/dist/css/bootstrap-modal.css" type="text/css">

        <?php include_once ("./bootstrap/modals/modal_combustibles.html"); ?>

        <script src="./bootstrap/controller/utils.js"></script>
        <script src="./bootstrap/controller/combustibles.js?var=<?= md5_file("bootstrap/controller/combustibles.js") ?>"></script>
        <?php
        $PxP = utils\IConnection::execSql("SELECT clavei,cnt_por_punto FROM com WHERE id = " . $objectVO->getId());
        $VpPp = utils\IConnection::execSql("SELECT valor FROM variables_corporativo WHERE llave ='PuntoPor'");
        $Vpp = $VpPp["valor"] === "volumen" ? "Litro(s)" : "Peso(s)";
        $Text = "<p>1 Punto por " . $PxP["cnt_por_punto"] . " " . $Vpp . "</p> ";
        ?>
        <script type="text/javascript">
            $(document).ready(function () {
                $("#Color").change(function () {
                    var elColor = $("#Color").val();
                    $('#Color').css("background-color", elColor);
                });
                $(".busca").val("<?= $busca ?>");

                $("#Producto").html("<?= $objectVO->getDescripcion() . " | " . $objectVO->getClavei() ?>");
                $("#Tipo").html(getTipoCombustible("<?= $objectVO->getCve_producto_sat() ?>"));
                $("#Activo").html(getEstado("<?= $objectVO->getActivo() ?>"));
                $("#UM").html("<?= $u_medida["descripcion"] ?>");
                $("#Cve_producto_sat").val("<?= $objectVO->getCve_producto_sat() ?>");
                $("#Poder_calorifico").val("<?= $objectVO->getPoder_calorifico() ?>");
                $("#Densidad").val("<?= $objectVO->getDensidad() ?>");
                $("#Comp_azufre").val("<?= $objectVO->getComp_azufre() ?>");
                $("#Fraccion_molar").val("<?= $objectVO->getFraccion_molar() ?>");
                $("#ComOctanajeGas").val("<?= $objectVO->getComOctanajeGas() ?>");
                $("#Gravedad_especifica").val("<?= $objectVO->getGravedad_especifica() ?>");
                $("#Comp_fosil").val("<?= $objectVO->getComp_fosil() ?>");
                $("#Comp_propano").val("<?= $objectVO->getComp_propano() ?>");
                $("#Comp_butano").val("<?= $objectVO->getComp_butano() ?>");
                $(".OcultaCampo").hide();
                $("#Color").val("<?= strtolower($objectVO->getColor()) ?>").css("color", "<?= strtolower($objectVO->getColor()) ?>");
                $("#ComDeEtanolEnGasolina").val("<?= $objectVO->getComDeEtanolEnGasolina() ?>");
                $("#GasConEtanol").val("<?= $objectVO->getGasConEtanol() ?>");
                //$("input[name=GasConEtanol][value='']").attr("checked", "checked");
                fillSubProduct("<?= $objectVO->getCve_sub_producto_sat() ?>");
                gasConEtanol("<?= $objectVO->getGasConEtanol() ?>");
                siCombustible();
                Muestra($("#Cve_producto_sat").val(), "<?= $objectVO->getClave_instalacion() ?>");

                $("#Cve_producto_sat").on("change", function () {
                    $(".OcultaCampo").hide();
                    Muestra($("#Cve_producto_sat").val(), "<?= $objectVO->getClave_instalacion() ?>");
                    siCombustible();
                    gasConEtanol("<?= $objectVO->getGasConEtanol() ?>");
                });
                $("#Cve_producto_sat").on("change", function () {
                    fillSubProduct(null);
                });
                $(".image-radio").on("click", function () {
                    var subControl = $(this).data("valor");
                    //console.log(subControl);
                    gasConEtanol(subControl);
                    if (subControl === "Si") {
                        $("#TieneEtanol").show();
                    } else {
                        $("#TieneEtanol").hide();
                    }
                    $("#GasConEtanol").val(subControl);
                });
                $("#Tipo").on("change", function () {
                    siCombustible();
                });
                $("#Cve_producto_sat").on("change", function () {
                    siCombustible();
                });
                $("#formulario1").submit(function (e) {
                    clicksForm = 0;
                    clave = "<?= $objectVO->getClave_instalacion() ?>";
                    dato = "<?= $objectVO->getCve_producto_sat() ?>";
                    if (clave === "EDS") {
                        if (dato === "PR07") {
                            if (!validateFieldWithLabel("Comp_fosil")) {
                                e.preventDefault();
                            }
                            if (!validateFieldWithLabel("ComDeEtanolEnGasolina")) {
                                e.preventDefault();
                            }
                        } else if (dato === "PR03") {
                            if (!validateFieldWithLabel("Comp_fosil")) {
                                e.preventDefault();
                            }
                            if (!validateFieldWithLabel("ComDeEtanolEnGasolina")) {
                                e.preventDefault();
                            }
                        } else if (dato === "PR08") {
                            if (!validateFieldWithLabel("Gravedad_especifica")) {
                                e.preventDefault();
                            }
                            if (!validateFieldWithLabel("Comp_azufre")) {
                                e.preventDefault();
                            }
                            if (!validateFieldWithLabel("Densidad")) {
                                e.preventDefault();
                            }
                        } else if (dato === "PR09") {
                            if (!validateFieldWithLabel("Fraccion_molar")) {
                                e.preventDefault();
                            }
                            if (!validateFieldWithLabel("Poder_calorifico")) {
                                e.preventDefault();
                            }
                        } else if (dato === "PR11") {
                            if (!validateFieldWithLabel("Comp_fosil")) {
                                e.preventDefault();
                            }
                        } else if (dato === "PR12") {
                            $("#Propano").show();
                            $("#Butano").show();
                        }
                    } else if (clave === "RCN" || clave === "TDP") {
                        if (dato === "PR08") {
                            if (!validateFieldWithLabel("Gravedad_especifica")) {
                                e.preventDefault();
                            }
                            if (!validateFieldWithLabel("Comp_azufre")) {
                                e.preventDefault();
                            }
                        } else if (dato === "PR09") {
                            if (!validateFieldWithLabel("Fraccion_molar")) {
                                e.preventDefault();
                            }
                            if (!validateFieldWithLabel("Poder_calorifico")) {
                                e.preventDefault();
                            }
                        }
                    }
                    if ($("#Cve_producto_sat").val() === "PR08") {
                        if ($("#Densidad").val() < 0.1 && $("#Densidad").val() < 80) {
                            $("#Densidad").css("background", "#E6B0AA");
                            $(".OcultaCampo").hide();
                            $("#Densid").show();
                            $("#Azufre").show();
                            $("#Gravedad").show();
                            alert("Cantidad para densidad es incorrecta");
                            return false;
                        }
                        if ($("#Comp_azufre").val() < 0.1 && $("#Comp_azufre").val() < 10) {
                            $("#Comp_azufre").css("background", "#E6B0AA");
                            $(".OcultaCampo").hide();
                            $("#Densid").show();
                            $("#Azufre").show();
                            $("#Gravedad").show();
                            alert("Cantidad para Com. Azufre es incorrecta");
                            return false;
                        }
                    }
                    if ($("#Cve_producto_sat").val() !== "PR12") {
                        $("#Cve_sub_producto_sat").attr('required', true);
                    }
                });

                $("#EditaCntPuntos").click(function () {
                    Swal.fire({
                        title: "Actualizar puntos por combustible <?= $objectVO->getId() ?> <?= $objectVO->getDescripcion() ?>",
                        background: "#E9E9E9",
                        showConfirmButton: true,
                        confirmButtonText: "Cambiar",
                        input: 'text',
                        inputValue: "<?= $PxP["cnt_por_punto"] ?>",
                        inputPlaceholder: 'Ejemplo: 1',
                        footer: '<?= $Text ?>',
                        backdrop: 'swal2-backdrop-show'

                    }).then((result) => {
                        if (result.isConfirmed) {
                            jQuery.ajax({
                                type: "POST",
                                url: "bootstrap/ajax/updateBonificacion.php",
                                dataType: "json",
                                cache: false,
                                data: {"op": 2, "IdCom": <?= $objectVO->getId() ?>, "Cnt": result.value},
                                beforeSend: function (xhr) {
                                    $("#Msj").hide();
                                    $("#Fail").hide();
                                    $("#myLoader").modal("toggle");
                                },
                                success: function (data) {
                                    console.log(data);
                                    Swal.fire({
                                        icon: 'success',
                                        iconColor: 'green',
                                        title: data,
                                        background: "#ABEBC6"
                                    })
                                    location.reload();

                                },
                                error: function (jqXHR, textStatus) {
                                    console.log(jqXHR);
                                    Swal.fire({
                                        icon: 'warning',
                                        iconColor: 'red',
                                        title: jqXHR.responseText,
                                        background: "#F5B7B1"
                                    })
                                }
                            });
                            //setInterval(GoToPipas(), 3500);
                        }
                        //                    
                    });
                });

            });

        </script>
    </body>
</html> 
