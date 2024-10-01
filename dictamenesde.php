<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$self = utils\HTTPUtils::self();

$Titulo = "Detalle de dictamen";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

require_once "./services/DictamenService.php";

$objectVO = new DictamenDVO();
if (is_numeric($busca)) {
    $objectVO = $objectDAO->retrieveD($busca, "idnvo", "com.cve_producto_sat,dictamend.tanque,dictamend.idnvo,dictamend.comp_azufre,dictamend.fraccion_molar,dictamend.poder_calorifico,"
            . "dictamend.comp_octanaje,dictamend.comp_etanol,contiene_fosil,dictamend.gravedad_especifica,dictamend.comp_fosil,dictamend.comp_propano,cia.clave_instalacion,"
            . "dictamend.comp_butano ");
    $Titulo = "Detalle de dictamen para tanque: " . $objectVO->getTanque();
}
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
                    <a href="dictamenesd.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">
                    <div id="FormulariosBoots">
                        <div class="container no-margin">
                            <div class="row no-padding">
                                <div class="col-9 no-margin">
                                    <form name="formulario1" id="formulario1" method="post" action="">
                                        <div class="row no-padding">
                                            <div class="col-4 align-right withBackground">Id:</div>
                                            <div class="col-4"><input type="text" name="Id" id="Id" placeholder="" disabled=""/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right withBackground">Tanque:</div>
                                            <div class="col-4"><?php ListasCatalogo::getTanques("Tanque", "tanque", "", "disabled='disabled' ") ?></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right withBackground">Clave Producto:</div>
                                            <div class="col-4"><?php ListasCatalogo::getDataFromCatalogoSatCv("Cve_Producto_Sat", "CLAVES_PRODUCTO", "", " required=''") ?></div>
                                            <div class="col-1"></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right withBackground">Clave Instalacion :</div>
                                            <div class="col-4"><?php ListasCatalogo::getDataFromCatalogoSatCv("Clave_inst", "CLAVES_INSTALACION", "", "") ?></div>
                                            <div class="col-1"><a href="parametros.php"><i class="fa fa-lg fa-question-circle" aria-hidden="true" title="Redirección a CONFIGURACION->Empresa"></i></a></div>
                                        </div>
                                        <div class="row no-padding OcultaCampo" id="Azufre">
                                            <div class="col-4 align-right withBackground required">Composición Azufre:</div>
                                            <div class="col-4"><input type="text" name="Comp_azufre" id="Comp_azufre" placeholder="" required=""/></div>
                                            <div class="col-4"> % <label for="Comp_azufre"></label></div>
                                        </div>
                                        <div class="row no-padding OcultaCampo" id="Molar">
                                            <div class="col-4 align-right withBackground required">Fraccion molar:</div>
                                            <div class="col-4"><input type="text" name="Fraccion_molar" id="Fraccion_molar" placeholder="" required=""/></div>
                                            <div class="col-4"> % <label for="Fraccion_molar"></label></div>
                                        </div>
                                        <div class="row no-padding OcultaCampo" id="Calorifico">
                                            <div class="col-4 align-right withBackground required">Poder calorifico:</div>
                                            <div class="col-4"><input type="text" name="Poder_calorifico" id="Poder_calorifico" placeholder=""/></div>
                                            <div class="col-4"><label for="Poder_calorifico"></label></div>
                                        </div>
                                        <div class="row no-padding OcultaCampo" id="Gravedad">
                                            <div class="col-4 align-right withBackground required">Gravedad especifica:</div>
                                            <div class="col-4"><input type="text" name="Gravedad_especifica" id="Gravedad_especifica" placeholder=""/></div>
                                            <div class="col-4"><label for="Gravedad_especifica"></label></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right withBackground required">Contiene fosil:</div>
                                            <div class="col-4">
                                                <select name="Contiene_fosil" id="Contiene_fosil">
                                                    <option value="Si">Si</option>
                                                    <option value="No">No</option>
                                                </select>
                                            </div>
                                            <div class="col-4"><label for="Comp_octanaje"></label></div>
                                        </div>
                                        <div class="row no-padding OcultaCampo" id="Fosil">
                                            <div class="col-4 align-right withBackground required">Composicion fosil:</div>
                                            <div class="col-4"><input type="text" name="Composicion_fosil" id="Composicion_fosil" placeholder=""/></div>
                                            <div class="col-4"><label for="Composicion_fosil"></label></div>
                                        </div>
                                        <div class="row no-padding OcultaCampo" id="Propano">
                                            <div class="col-4 align-right withBackground required">Composicion propano:</div>
                                            <div class="col-4"><input type="text" name="Composicion_propano" id="Composicion_propano" placeholder=""/></div>
                                            <div class="col-4"><label for="Composicion_propano"></label></div>
                                        </div>
                                        <div class="row no-padding OcultaCampo" id="Butano">
                                            <div class="col-4 align-right withBackground required">Composicion butano:</div>
                                            <div class="col-4"><input type="text" name="Composicion_butano" id="Composicion_butano" placeholder=""/></div>
                                            <div class="col-4"><label for="Composicion_butano"></label></div>
                                        </div>
                                        <div class="row no-padding OcultaCampo" id="Octanaje">
                                            <div class="col-4 align-right withBackground required">Octanaje:</div>
                                            <div class="col-4"><input type="number" name="Comp_octanaje" id="Comp_octanaje" placeholder="" min="0" max="100"/></div>
                                            <div class="col-4"><label for="Comp_octanaje"></label></div>
                                        </div>
                                        <div class="row no-padding OcultaCampo" id="Etanol">
                                            <div class="col-4 align-right withBackground required">Etanol:</div>
                                            <div class="col-4"><input type="text" name="Comp_etanol" id="Comp_etanol" placeholder=""/></div>
                                            <div class="col-4"><label for="Comp_etanol"></label></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right"></div>
                                            <div class="col-4"><input type="submit" name="BotonD" id="Boton"/></div>
                                        </div>
                                        <input type="hidden" name="Cv_Producto" id="Cv_Producto"/>
                                        <input type="hidden" name="busca" id="busca"/>
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

        <script>
            $(document).ready(function () {
                $("#busca").val("<?= $busca ?>");
                $("#Id").val("<?= $busca ?>");
                $("#Tanque").val("<?= $objectVO->getTanque() ?>");
                $("#Clave_inst").val("<?= $objectVO->getClave_instalacion() ?>");
                $("#Comp_azufre").val("<?= $objectVO->getComp_azufre() ?>");
                $("#Fraccion_molar").val("<?= $objectVO->getFraccion_molar() ?>");
                $("#Poder_calorifico").val("<?= $objectVO->getPoder_calorifico() ?>");
                $("#Comp_octanaje").val("<?= number_format($objectVO->getComp_octanaje(), 0) ?>");
                $("#Comp_etanol").val("<?= $objectVO->getComp_etanol() ?>");
                $("#Cve_Producto_Sat").val("<?= $objectVO->getCve_producto_sat() ?>");
                $("#Cve_Producto_Sat").prop("disabled", true);
                $("#Cv_Producto").val("<?= $objectVO->getCve_producto_sat() ?>");
                $("#Gravedad_especifica").val("<?= $objectVO->getGravedad_especifica() ?>");
                $("#Clave_inst").val("<?= $objectVO->getClave_instalacion() ?>");
                $("#Clave_inst").prop("disabled", true);
                $("#Contiene_fosil").val("<?= $objectVO->getContiene_fosil() ?>");
                $("#Composicion_fosil").val("<?= $objectVO->getComp_fosil() ?>");
                $("#Composicion_propano").val("<?= $objectVO->getComp_propano() ?>");
                $("#Composicion_butano").val("<?= $objectVO->getComp_butano() ?>");
                $(".OcultaCampo").hide();
                if ($("#busca").val() !== "NUEVO") {
                    $("#Boton").val("Actualizar");
                } else {
                    $("#Boton").val("Agregar");
                }
                Muestra("<?= $objectVO->getCve_producto_sat() ?>", "<?= $objectVO->getClave_instalacion() ?>");
                $("#Cve_Producto_Sat").on("change", function () {
                    $(".OcultaCampo").hide();
                    Muestra($("#Cve_Producto_Sat").val(), "<?= $objectVO->getClave_instalacion() ?>");
                });

                $("#formulario1").submit(function (e) {
                    clicksForm = 0;
                    if (!validateFieldWithLabel("Composicion_fosil")) {
                        e.preventDefault();
                    }
                    if (!validateFieldWithLabel("Comp_octanaje")) {
                        e.preventDefault();
                    }
                    if (!validateFieldWithLabel("Fraccion_molar")) {
                        e.preventDefault();
                    }
                    if (!validateFieldWithLabel("Poder_calorifico")) {
                        e.preventDefault();
                    }
                    if (!validateFieldWithLabel("Comp_azufre")) {
                        e.preventDefault();
                    }
                    if (!validateFieldWithLabel("Comp_etanol")) {
                        e.preventDefault();
                    }
                    if (!validateFieldWithLabel("Composicion_butano")) {
                        e.preventDefault();
                    }
                    if (!validateFieldWithLabel("Composicion_propano")) {
                        e.preventDefault();
                    }

                });

            });

            function Muestra(dato, clave) {
                console.log(clave);
                if (clave === "EDS") {
                    if (dato === "PR07") {
                        $("#Octanaje").show();
                        $("#Fosil").show();
                    } else if (dato === "PR03") {
                        $("#Fosil").show();
                    } else if (dato === "PR08") {
                        $("#Gravedad").show();
                        $("#Azufre").show();
                    } else if (dato === "PR09") {
                        $("#Molar").show();
                        $("#Calorifico").show();
                    } else if (dato === "PR11") {
                        $("#Fosil").show();
                    } else if (dato === "PR12") {
                        $("#Propano").show();
                        $("#Butano").show();
                    }
                } else if (clave === "RCN" || clave === "TDP") {
                    if (dato === "PR08") {
                        $("#Gravedad").show();
                        $("#Azufre").show();
                    } else if (dato === "PR09") {
                        $("#Molar").show();
                        $("#Calorifico").show();
                    }
                }
            }
        </script>
    </body>
</html>