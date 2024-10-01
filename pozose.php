<?php
#Librerias
session_start();

include_once ("check.php");
include_once ('comboBoxes.php');
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();

require './services/PozosService.php';

$Titulo = "Detalle pozo";
$nameVarBusca = "busca";
$nameSession = "Catalogo_Pozos";

if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameSession, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameSession, $request->getAttribute("id"));
}

$busca = utils\HTTPUtils::getSessionValue($nameSession);
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$objectVO = new PozosVO();
$objectDAO = new PozosDAO();
if (is_numeric($busca)) {
    $objectVO = $objectDAO->retrieve($busca);
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
                    <a href="pozos.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">

                    <div id="FormulariosBoots">

                        <div class="container">

                            <div class="row background">
                                <div class="col-12 align-left title"> Pozos: <span id="Pozo"></span></div>
                            </div>

                            <div class="row background">                                
                                <div class="col-12 no-margin">
                                    <form name="formulario0" id="formulario0" method="post" action="">

                                        <div class="row no-padding">
                                            <div class="col-3 align-right"><label class="label">Descripción del pozo: </label></div>
                                            <div class="col-4"><textarea type="text" name="Descripcion" id="Descripcion" placeholder="" rows="3" required="required"></textarea></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-12 align-left subtitle">Sistema de medición</div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right"><label class="label">Clave : </label></div>
                                            <div class="col-4"> <?php ListasCatalogo::getDataFromCatalogoSatCv("Clave_sistema_medicion", "CLAVES_SISTEMAS_MEDICION") ?></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right"><label class="label">Descripción : </label></div>
                                            <div class="col-4"><textarea type="text" name="Descripcion_sistema_medicion" id="Descripcion_sistema_medicion" placeholder="" rows="3" required="required"></textarea></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right"><label class="label">Vigencia : </label></div>
                                            <div class="col-2"><input type="date" name="Vigencia_sistema_medicion" id="Vigencia_sistema_medicion" required="required"/></div>
                                            <div class="col-5"> <label for="Diametro"></label></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right"><label class="label">Incertidumbre : </label></div>
                                            <div class="col-2"><input type="text" name="Incertidumbre_sistema_medicion" id="Incertidumbre_sistema_medicion" required="required"/></div>
                                            <div class="col-5"> <label for="Diametro"></label></div>
                                        </div>

                                        <div class="row no-padding">
                                            <div class="col-3 align-right"></div>
                                            <div class="col-4"><button type="submit" class="btn-boots" id="Boton" name="Boton">Agregar</button></div>
                                        </div>
                                        <input type="hidden" name="busca" class="busca"/>
                                        <input type="hidden" name="Tpducto" id="Tpducto"/>
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
        <link rel="stylesheet" href="bootstrap/bootstrap-4.0.0/dist/css/bootstrap-modal.css" type="text/css">

        <?php include_once ("./bootstrap/modals/modal_parametros.html"); ?>

        <script src="./bootstrap/controller/utils.js"></script>
        <script src="./bootstrap/controller/parametros.js"></script>

        <script type="text/javascript">
            $(document).ready(function () {
                $(".busca").val("<?= $busca ?>");
                console.log("<?= $request->getAttribute("id") ?>");
                if ("<?= $request->getAttribute("id") ?>" == "NUEVO") {
                    $("#Boton").val("Agregar");
                    $("#Vigencia_sistema_medicion").val(<?= date("Y-m-d") ?>)
                    $("#Pozo").html("NUEVO");
                    console.log("Agregar");
                } else {
                    $("#Descripcion").val("<?= $objectVO->getDescripcion() ?>");
                    $("#Pozo").html("<?= $busca ?>");
                    $("#Descripcion_sistema_medicion").val("<?= $objectVO->getDescripcion_sistema_medicion() ?>");
                    $("#Vigencia_sistema_medicion").val("<?= $objectVO->getVigencia_sistema_medicion() ?>");
                    $("#Incertidumbre_sistema_medicion").val("<?= $objectVO->getIncertidumbre_sistema_medicion() ?>");
                    $("#Clave_sistema_medicion").val("<?= $objectVO->getClave_sistema_medicion() ?>");
                    $("#Boton").val("Actualizar");
                    console.log("Actualiza");
                }


                $("#formulario0").submit(function (e) {
                    clicksForm = 0;

                });
            });
        </script>
    </body>
</html> 
