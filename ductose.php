<?php
#Librerias
session_start();

include_once ("check.php");
include_once ('comboBoxes.php');
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();

require './services/DuctosService.php';

$Titulo = "Detalle medios de transporte";
$nameVarBusca = "busca";
$nameSession = "Catalogo_Ductos";

if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}

$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);
$TipoDucto = utils\HTTPUtils::getSessionBiValue($nameSession, "Tipo_ducto");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$objectVO = new DuctosVO();
$objectDAO = new DuctosDAO();
if (is_numeric($busca)) {
    $objectVO = $objectDAO->retrieve($busca);
}

$TipoDeDucto = $objectVO->getTipo_ducto() == 0 ? "Ductos" : "Otros";
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
                    <a href="ductos.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">

                    <div id="FormulariosBoots">

                        <div class="container">

                            <div class="row background">
                                <div class="col-12 align-left title"> Transporte: <span id="Ducto"></span></div>
                                <div class="col-12 align-left" id="identificacion">Clave de Identificación: <span id="Clave"></span></div>
                                <div class="col-12 align-left" id="Tipo_Ducto">Tipo de ducto: <span id="TipoDucto"></span></div>
                            </div>

                            <div class="row background">                                
                                <div class="col-12 no-margin">
                                    <form name="formulario0" id="formulario0" method="post" action="">
                                        <div class="row no-padding">
                                            <div class="col-12 align-left subtitle">PARÁMETROS DEL SAT</div>
                                        </div>
                                        <div class="row no-padding" id="Descripcion_ducto">
                                            <div class="col-3 align-right"><label class="label">Medio de transporte <sup class="sup">1</sup>:</label></div>
                                            <div class="col-4"><input type="text" name="Descripcion_tipo_ducto" id="Descripcion_tipo_ducto" placeholder=""/></div>
                                        </div>
                                        <div class="row no-padding" id="Quita_ductos">
                                            <div class="col-3 align-right"><label class="label">Clave Instalacion :</label></div>
                                            <div class="col-4"><?php ListasCatalogo::getDataFromCatalogoSatCv("Clave_ductos00", "CLAVES_DUCTOS") ?></div>
                                            <div class="col-1"><i class="fa fa-lg fa-question-circle" aria-hidden="true" data-toggle="modal" data-target="#modal-parametros-listas" data-identificador="CLAVES_DUCTOS" data-operacion="11"></i></div>
                                        </div>
                                        <div class="row no-padding" >
                                            <div class="col-3 align-right"><label class="label">Sistema Medicion :</label></div>
                                            <div class="col-4"><?php ListasCatalogo::getDataFromCatalogoSatCv("Sistema_medicion", "CLAVES_SISTEMAS_MEDICION") ?></div>
                                            <div class="col-1"><i class="fa fa-lg fa-question-circle" aria-hidden="true" data-toggle="modal" data-target="#modal-parametros-listas" data-identificador="CLAVES_MEDIOS_TRANSPORTE" data-operacion="11"></i></div>
                                        </div>
                                        <div class="row no-padding" >
                                            <div class="col-3 align-right"><label class="label">Medidor :</label></div>
                                            <div class="col-4"><input type="text" name="Medidor" id="Medidor"></div>
                                            <div class="col-1"><i class="fa fa-lg fa-question-circle" aria-hidden="true" data-toggle="modal" data-target="#modal-parametros-listas" data-identificador="CLAVES_MEDIOS_TRANSPORTE" data-operacion="11"></i></div>
                                        </div>
                                        <div class="row no-padding" id="Quita_transportes">
                                            <div class="col-3 align-right"><label class="label">Clave Instalacion :</label></div>
                                            <div class="col-4"><?php ListasCatalogo::getDataFromCatalogoSatCv("Clave_ductos01", "CLAVES_MEDIOS_TRANSPORTE") ?></div>
                                            <div class="col-1"><i class="fa fa-lg fa-question-circle" aria-hidden="true" data-toggle="modal" data-target="#modal-parametros-listas" data-identificador="CLAVES_MEDIOS_TRANSPORTE" data-operacion="11"></i></div>
                                        </div>
                                        <div class="row no-padding" id="Quita_servicio">
                                            <div class="col-3 align-right"><label class="label">Clave Servicio :</label></div>
                                            <div class="col-4"><?php ListasCatalogo::getDataFromCatalogoSatCv("Clave_producto", "CLAVES_PRODUCTO") ?></div>
                                            <div class="col-1"><i class="fa fa-lg fa-question-circle" aria-hidden="true" data-toggle="modal" data-target="#modal-parametros-listas" data-identificador="CLAVES_PRODUCTO" data-operacion="11"></i></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right"><label class="label">Descripción : </label></div>
                                            <div class="col-4"><textarea type="text" name="Descripcion" id="Descripcion" placeholder="" rows="5" required="required"></textarea></div>
                                        </div>
                                        <div class="row no-padding" id="Quita_diametro">
                                            <div class="col-3 align-right"><label class="label">Diámetro <sub> (Pulgadas) </sub> : </label></div>
                                            <div class="col-2"><input type="text" name="Diametro" id="Diametro" placeholder="" required="required"/></div>
                                            <div class="col-5"> <label for="Diametro"></label></div>
                                        </div>
                                        <div class="row no-padding" id="Quita_almacentamiento">
                                            <div class="col-3 align-right"><label class="label">Almacenamiento : </label></div>
                                            <div class="col-2"><input type="text" name="Almacenamiento" id="Almacenamiento" placeholder="" required="required"/></div>
                                            <div class="col-5"> <label for="Almacenamiento"></label></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right"></div>
                                            <div class="col-4"><button type="submit" class="btn-boots" id="Boton" name="Boton" value="Agregar">Agregar</button></div>
                                        </div>
                                        <input type="hidden" name="busca" class="busca"/>
                                        <input type="hidden" name="Tpducto" id="Tpducto"/>
                                    </form>
                                </div>
                            </div>

                            <div class="row background" id="Quita_fecha">                                
                                <div class="col-12 no-margin">
                                    <form name="formulario0" id="formulario0" method="post" action="">
                                        <div class="row no-padding">
                                            <div class="col-12 align-left subtitle">AJUSTAR FECHA DE PRÓXIMA CALIBRACIÓN</div>
                                        </div>
                                        <div class="row no-padding" id="Descripcion_ducto">
                                            <div class="col-3 align-right"><label class="label">Fecha </div>
                                            <div class="col-3"><input type="date" name="Fecha_calibracion" id="Fecha_calibracion" placeholder="" required="required"/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right"></div>
                                            <div class="col-4"><button type="submit" class="btn-boots" id="Boton" name="Boton" value="ActualizarFecha">Actualizar Fecha</button></div>
                                        </div>
                                        <input type="hidden" name="busca" class="busca"/>
                                        <input type="hidden" name="Tpducto" id="Tpducto"/>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 no-margin" style="font-size: 9px; color:#55514e;">                                    
                                <div class="row no-padding">
                                    <div class="col-12" id="Quita_tipo_medidor"><strong class="sup">1. Tipo de medidor : </strong>
                                        Trasladar el producto de las instalaciones de distribución a las instalaciones de los usuarios finales o a 
                                        las instalaciones de expendio de petrolíferos, utilizando el transporte de su propiedad y que formaría parte
                                        del permiso de distribución, o utilizando transporte contratado a un permisionario de transporte por medios 
                                        distintos a ducto, en donde la custodia y responsabilidad del petrolífero la tendrá el distribuidor hasta su 
                                        entrega al usuario final o al permisionario de expendio al público. </div>
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

        <?php include_once ("./bootstrap/modals/modal_parametros.html"); ?>

        <script src="./bootstrap/controller/utils.js"></script>
        <script src="./bootstrap/controller/parametros.js"></script>

        <script type="text/javascript">
            $(document).ready(function () {
                $(".busca").val("<?= $busca ?>");
                $("#Tpducto").val("<?= $TipoDucto ?>");
                $("#Ducto").html("<?= $objectVO->getId_ducto() ?>");
                $("#TipoDucto").html("<?= $TipoDeDucto ?>");
                $("#Clave").html("<?= $objectVO->getClave_identificacion_ducto() ?>");
                $("#Descripcion").val("<?= $objectVO->getDescripcion_ducto() ?>");
                $("#Diametro").val("<?= $objectVO->getDiametro_ducto() ?>");
                $("#Fecha_calibracion").val("<?= $objectVO->getVigencia_calibracion_ducto() ?>");
                $("#Descripcion_tipo_ducto").val("<?= $objectVO->getDescripcion_tipo_ducto() ?>");
                $("#Almacenamiento").val("<?= $objectVO->getAlmacenamiento_ducto() ?>");
                $("#Clave_producto").val("<?= $objectVO->getCve_producto_sat_ducto() ?>");
                $("#Sistema_medicion").val("<?= $objectVO->getSistema_medicion() ?>");
                $("#Medidor").val("<?= $objectVO->getMedidor() ?>");
                if ($(".busca").val() !== "NUEVO") {
                    $("#Boton").val("Actualizar");
                    $("#Boton").html("Actualizar");
                } else {
                    $("#identificacion").hide();
                    $("#Tipo_Ducto").hide();
                    $("#Quita_fecha").hide();
                    $("#Ducto").html("NUEVO");
                }

                if (<?= $TipoDucto ?> == 0) {
                    //$("#Quita_almacentamiento").hide();
                    $("#Descripcion_ducto").hide();
                    $("#Quita_transportes").hide();
                    $("#Quita_tipo_medidor").hide();
                    $("#Quita_servicio").hide();

                    $("#Clave_ductos00").val("<?= $objectVO->getClave_identificacion_ducto() ?>");
                } else if (<?= $TipoDucto ?> == 1) {
                    document.getElementById("Descripcion_tipo_ducto").required = true;
                    $("#Quita_ductos").hide();
                    $("#Quita_diametro").hide();
                    $("#Clave_ductos01").val("<?= $objectVO->getClave_identificacion_ducto() ?>");
                }

                $("#formulario0").submit(function (e) {
                    clicksForm = 0;
                    if (!validateFieldWithLabel("Diametro")) {
                        e.preventDefault();
                    }
                    if (<?= $TipoDucto ?> > 0) {
                        if (!validateFieldWithLabel("Almacenamiento")) {
                            e.preventDefault();
                        }
                    }
                });
            });
        </script>
    </body>
</html> 
