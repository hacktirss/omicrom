<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();

require './services/DispensariosService.php';

$Titulo = "Detalle de dispensario por manguera";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

$query = "SELECT * FROM man WHERE man.id = $cVarVal;";
$He = $mysqli->query($query)->fetch_array();

$objectVO = new ManProVO();
if (is_numeric($busca)) {
    $objectVO = $manProDAO->retrieve($busca);
}
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php include './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $(".busca").val("<?= $busca ?>");

                $("#Manguera").html("<?= $objectVO->getManguera() ?>");
                $("#Producto").html("<?= $objectVO->getDescripcion() ?>");

                $("#Calibracion").val("<?= $objectVO->getVigencia_calibracion() ?>");
                $("#Num_medidor").val("<?= $objectVO->getNum_medidor() ?>");
                $("#Tipo_medidor").val("<?= $objectVO->getTipo_medidor() ?>");
                $("#Modelo_medidor").val("<?= $objectVO->getModelo_medidor() ?>");
                Incertidumbre = <?= $objectVO->getIncertidumbre() ?> * 100;
                $("#Incertidumbre").val(Incertidumbre);
            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>       

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="dispensariosd.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">
                    <div id="FormulariosBoots">
                        <div class="container no-margin">
                            <div class="row background">
                                <div class="col-12 align-left title">Posición: <span><?= $He["posicion"] ?></span>, Manguera: <span id="Manguera"></span></div>
                                <div class="col-12 align-left">Dispensario : <span><?= $He["dispensario"] ?></span></div>
                                <div class="col-12 align-left">Producto: <span id="Producto"></span></div>                                
                            </div>

                            <div class="row background">
                                <div class="col-12 no-margin">
                                    <form name="formulario1" id="formulario1" method="post" action="">   
                                        <div class="row no-padding">
                                            <div class="col-12 align-left subtitle">PARÁMETROS DEL SAT</div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right"><label class="label">Tipo de medidor <sup class="sup">1</sup>:</label></div>
                                            <div class="col-5"><?php ListasCatalogo::getDataFromCatalogoSatCv("Tipo_medidor", "CLAVES_SISTEMAS_MEDICION") ?></div>
                                            <div class="col-1"><i class="fa fa-lg fa-question-circle" aria-hidden="true" data-toggle="modal" data-target="#modal-mangueras-listas" data-identificador="CLAVES_SISTEMAS_MEDICION" data-operacion="11"></i></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right"><label class="label">Modelo del medidor <sup class="sup">2</sup>:</label></div>
                                            <div class="col-5"><input type="text" name="Modelo_medidor" id="Modelo_medidor" placeholder="" required=""/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right"><label class="label">Numero de medidor <sup class="sup">3</sup>:</label></div>
                                            <div class="col-3"><input type="number" name="Num_medidor" id="Num_medidor" placeholder="" required="" min="1" max="6"/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right"><label class="label">Incertidumbre <sup class="sup">4</sup>:</label></div>
                                            <div class="col-3"><input type="text" name="Incertidumbre" id="Incertidumbre" placeholder="" required=""/></div>
                                            <div class="col-4"><label for="Incertidumbre"></label></div>
                                        </div>

                                        <div class="row no-padding">
                                            <div class="col-3 align-right"></div>
                                            <div class="col-3"><button type="submit" class="btn-boots" name="BotonD" value="Actualizar SAT">Actualizar</button></div>
                                        </div>
                                        <input type="hidden" name="busca" class="busca"/>
                                    </form>
                                </div>
                            </div>

                            <div class="row background">                                
                                <div class="col-12 no-margin">
                                    <form name="formulario2" id="formulario2" method="post" action="">
                                        <div class="row no-padding">
                                            <div class="col-12 align-left subtitle">AJUSTAR FECHA DE PRÓXIMA CALIBRACIÓN</div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right"><label class="label">Fecha <sup class="sup">5</sup>:</label></div>
                                            <div class="col-3"><input type="date" name="Calibracion" id="Calibracion"/></div>
                                            <div class="col-1"></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right"></div>
                                            <div class="col-3"><button type="submit" class="btn-boots"  name="BotonD" value="Ajustar">Actualizar</button></div>
                                        </div>
                                        <input type="hidden" name="busca" class="busca"/>
                                    </form>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 no-margin" style="font-size: 9px; color:#55514e;">                                    
                                    <div class="row no-padding">
                                        <div class="col-12"><strong class="sup">1. Tipo de medidor : </strong> Selección del tipo de medido que contiene el dispensario (manguera).</div>
                                    </div>
                                    <div class="row no-padding">
                                        <div class="col-12"><strong class="sup">2. Modelo de medidor : </strong> Modelo del medidor que se tiene en el dispensario.</div>
                                    </div>
                                    <div class="row no-padding">
                                        <div class="col-12"><strong class="sup">3. Numero de medidor : </strong> Numero de medidor fisico dependiedo de la marca del dispensario, valores entre 1 - 6.</div>
                                    </div>
                                    <div class="row no-padding">
                                        <div class="col-12"><strong class="sup">4. Incertidumbre : </strong> Margen de error dependiendo del modelo del medidor y especificaciónes del fabricante.</div>
                                    </div>
                                    <div class="row no-padding">
                                        <div class="col-12"><strong class="sup">5. Fecha : </strong> Fecha de la proxima calibración de los dispensarios.</div>
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
        PieDePagina()
        ?>

        <link rel="stylesheet" href="bootstrap/bootstrap-4.0.0/dist/css/bootstrap-modal.css" type="text/css">

        <?php include_once ("./bootstrap/modals/modal_mangueras.html"); ?>

        <script src="./bootstrap/controller/utils.js"></script>
        <script src="./bootstrap/controller/mangueras.js"></script>
        <script type="text/javascript">
            $(document).ready(function () {

                var fechaActual = new Date().toISOString().split('T')[0];
                // Establecer el atributo min del campo de fecha como la fecha actual
                $('#Calibracion').attr('min', fechaActual);

                // Validar la fecha seleccionada al enviar el formulario
                $('#formulario2').submit(function (event) {
                    var seleccion = $('#Calibracion').val();
                    if (seleccion < fechaActual) {
                        // Si la fecha seleccionada es anterior a la fecha actual, evitar enviar el formulario
                        event.preventDefault();
                        alert('La fecha de calibración no puede ser anterior a la fecha actual.');
                    }
                });

                $("#formulario1").submit(function (e) {
                    clicksForm = 0;
                    if (!validateFieldWithLabel("Num_medidor")) {
                        e.preventDefault();
                    }
                    if (!validateFieldWithLabel("Incertidumbre")) {
                        e.preventDefault();
                    }
                });
            });
        </script>
    </body>
</html>
