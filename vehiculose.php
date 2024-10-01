<?php
#Librerias
session_start();

include_once ("check.php");
include_once ('comboBoxes.php');
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$self = utils\HTTPUtils::self();

$Titulo = "Detalle de Vehiculo";
$nameVarBusca = "buscaV";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

require_once "./services/VehiculosService.php";

$objectVO = new VehiculoVO();
$vehiculoDAO = new VehiculoDAO();
if (is_numeric($busca)) {
    error_log("El valor de busca : " . $busca);
    $objectVO = $vehiculoDAO->retrieve($busca);
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

                    <a href="<?= $request->hasAttribute("ReturnD") ? $request->getAttribute("ReturnD") : "vehiculos.php"; ?>"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">

                    <div id="FormulariosBoots">

                        <div class="container no-margin">
                            <div class="row no-padding">
                                <div class="col-12 background no-margin">
                                    <form name="formulario1" id="formulario1" method="post" action="">
                                        <?php
                                        if ($request->hasAttribute("ReturnD")) {
                                            ?>
                                            <input type="hidden" name="ReturnD" value="<?= $request->getAttribute("ReturnD") ?>">
                                            <?php
                                        }
                                        ?>          
                                        <div class="row no-padding">
                                            <div class="col-6 align-right"></div>
                                            <div class="col-4"><sub style="font-size: 10px; color: #EC7063;font-weight: bold;">Datos requeridos para generar una Carta Porte *</sub></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Id:</div>
                                            <div class="col-2"><input type="text" name="Id" id="Id" placeholder="" disabled=""/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Descripcion: </div>
                                            <div class="col-8">
                                                <input type="text" name="Descripcion" id="Descripcion">
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Conf Vehicular: </div>
                                            <div class="col-8">
                                                <select name="Conf" id="Conf" required class="clase-<?= $clase ?>">
                                                    <?php
                                                    $arrayDatos = CatalogosSelectores::getConfiguracionVehicular();
                                                    foreach ($arrayDatos as $key => $value) {
                                                        ?>
                                                        <option value="<?= $key ?>"/><?= $value ?></option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Permiso SCT: </div>
                                            <div class="col-8">
                                                <select name="Permiso" id="Permiso" required class="clase-<?= $clase ?>">
                                                    <?php
                                                    $arrayDatos = CatalogosSelectores::getTipoPermiso();
                                                    foreach ($arrayDatos as $key => $value) {
                                                        ?>
                                                        <option value="<?= $key ?>"/><?= $value ?></option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Numero SCT:</div>
                                            <div class="col-2"><input type="text" name="NumeroSCT" id="NumeroSCT" required="" placeholder="" onkeyup="mayus(this);"/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right ">Placa:</div>
                                            <div class="col-2"><input type="text" name="Placa" id="Placa" placeholder="" required="" onkeyup="mayus(this);"/></div>

                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Año modelo:</div>
                                            <div class="col-2"><input type="text" name="Anio" id="Anio" placeholder="" maxlength="4" required="" onkeyup="mayus(this);"/></div>

                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Aseguradora:</div>
                                            <div class="col-4"><input type="text" name="Aseguradora" id="Aseguradora" placeholder="" required="" onkeyup="mayus(this);"/></div>

                                        </div>                                                                              
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Numero Seguro:</div>
                                            <div class="col-2"><input type="text" name="Seguro" id="Seguro" placeholder="" required="" onkeyup="mayus(this);"/></div>
                                        </div>                                                                                                                                                 
                                        <div class="row no-padding">
                                            <div class="col-3 align-right"></div>
                                            <div class="col-4"><input type="submit" name="Boton" id="Boton"/></div>
                                        </div>                                       
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
                $("#Descripcion").val("<?= $objectVO->getDescripcion() ?>");
                $("#Conf").val("<?= $objectVO->getConf_vehicular() ?>");
                $("#Placa").val("<?= $objectVO->getPlaca() ?>");
                $("#Anio").val("<?= $objectVO->getAnio_modelo() ?>");
                $("#Remolque").val("<?= $objectVO->getSubtipo_remolque() ?>");
                $("#PRemolque").val("<?= $objectVO->getPlaca_remolque() ?>");
                $("#Permiso").val("<?= $objectVO->getPermiso_sct() ?>");
                $("#NumeroSCT").val("<?= $objectVO->getNumero_sct() ?>");
                $("#Aseguradora").val("<?= $objectVO->getNombre_aseguradora() ?>");
                $("#Seguro").val("<?= $objectVO->getNumero_seguro() ?>");
                $("#Figura").val("<?= $objectVO->getTipo_figura() ?>");

                if ($("#busca").val() !== "NUEVO") {
                    $("#Boton").val("Actualizar");
                } else {
                    $("#Boton").val("Agregar");
                }

                $("#Placa").focus();

                $("#Boton").on("click", function (e) {
                });
            });
        </script>
    </body>
</html>