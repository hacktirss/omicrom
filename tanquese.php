<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$mysqli = iconnect();

require './services/TanquesService.php';

$Titulo = "Detalle de tanques";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

$objectVO = new TanqueVO();
$objectVO->setTanque(0);
$objectVO->setEstado(1);
if (is_numeric($busca)) {
    $objectVO = $tanqueDAO->retrieve($busca);
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

                $("#TanqueProducto").html("<?= $objectVO->getTanque() . " " . ListasCatalogo::getCombustiblesStr($objectVO->getClave_producto()) ?>");
                if ("<?= $objectVO->getEstado() ?>" == 1) {
                    estado = "Activo";
                } else {
                    estado = "Desactivado";
                }
                $("#Activo").html(estado);
                $("#Capacidad").html("<?= $objectVO->getCapacidad_total() ?>");
                $("#Tanque").val("<?= $objectVO->getTanque() ?>");
                $("#Producto").val("<?= $objectVO->getClave_producto() ?>");
                $("#Estado").val("<?= $objectVO->getEstado() ?>");
                $("#CapacidadTotal").val("<?= $objectVO->getCapacidad_total() ?>");
                $("#Volumen_fondaje").val("<?= $objectVO->getVolumen_fondaje() ?>");
                $("#Volumen_minimo").val("<?= $objectVO->getVolumen_minimo() ?>");
                $("#Volumen_operativo").val("<?= $objectVO->getVolumen_operativo() ?>");
                $("#Prefijo_sat").val("<?= $objectVO->getPrefijo_sat() ?>");
                $("#Sistema_medicion").val("<?= $objectVO->getSistema_medicion() ?>");
                $("#Sensor").val("<?= $objectVO->getSensor() ?>");
                $("#Descripcion").val("<?= $objectVO->getDescripcion() ?>");
                $("#Proveedor").val("<?= $objectVO->getIdProveedor() ?>");
                $("#ProveedorSensor").val("<?= $objectVO->getIdProveedorSensor() ?>");
                Incertidumbre = <?= $objectVO->getIncertidumbre_sensor() ?> * 100;
                $("#Incertidumbre_sensor").val(Incertidumbre);

                if ($("#busca").val() !== "NUEVO") {
                    $("#Calibracion").val("<?= $objectVO->getVigencia_calibracion() ?>");
                    $("#Boton").val("Actualizar");
                } else {
                    $("#Boton").val("Agregar");
                }
            });
        </script>

    </head>

    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="tanques.php"><div class="RegresarCss " alt="Flecha regresar" style="">Regresar</div></a>
                </td>
                <td style="vertical-align: top;">
                    <div id="FormulariosBoots">
                        <div class="container">
                            <div class="row background">
                                <div class="col-12 align-left title">Tanque o medios de almacenamiento: <span id="TanqueProducto"></span></div>
                                <div class="col-12 align-left">Estado: <span id="Activo"></span></div>
                                <div class="col-12 align-left">Capacidad : <span id="Capacidad"></span></div>
                            </div>
                            <div class="row background">                                
                                <div class="col-12 no-margin">
                                    <form name="formulario1" id="formulario1" method="post" action="">
                                        <div class="row no-padding">
                                            <div class="col-12 align-left subtitle">PARÁMETROS DEL SAT</div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right"><label class="label">Prefijo <sup class="sup">1</sup>: </label></div>
                                            <div class="col-4"><?php ListasCatalogo::getDataFromCatalogoSatCv("Prefijo_sat", "CLAVES_TANQUES") ?></div>
                                            <div class="col-1"><i class="fa fa-lg fa-question-circle" aria-hidden="true" data-toggle="modal" data-target="#modal-tanques-listas" data-identificador="CLAVES_TANQUES" data-operacion="11"></i></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right no-margin"><label class="label">Sistema medición <sup class="sup">2</sup>: </label></div>
                                            <div class="col-4"><?php ListasCatalogo::getDataFromCatalogoSatCv("Sistema_medicion", "CLAVES_SISTEMAS_MEDICION") ?></div>
                                            <div class="col-1"><i class="fa fa-lg fa-question-circle" aria-hidden="true" data-toggle="modal" data-target="#modal-tanques-listas" data-identificador="CLAVES_SISTEMAS_MEDICION" data-operacion="11"></i></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right "><label class="label">Localización y/o Descripción :</label></div>
                                            <div class="col-4"><input type="text" name="Descripcion" id="Descripcion"/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right "><label class="label">Proveedor :</label></div>
                                            <div class="col-4">
                                                <select name="Proveedor" id="Proveedor">
                                                    <?php
                                                    $PrvT = "SELECT * FROM prv  WHERE proveedorde = 'Equipo'";
                                                    $RPrvT = utils\IConnection::getRowsFromQuery($PrvT);
                                                    foreach ($RPrvT as $rpv) {
                                                        ?>
                                                        <option value="<?= $rpv["id"] ?>"><?= $rpv["nombre"] ?></option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right "><label class="label">Proveedor Sensor:</label></div>
                                            <div class="col-4">
                                                <select name="ProveedorSensor" id="ProveedorSensor">
                                                    <?php
                                                    $PrvT = "SELECT * FROM prv  WHERE proveedorde = 'Equipo'";
                                                    $RPrvT = utils\IConnection::getRowsFromQuery($PrvT);
                                                    foreach ($RPrvT as $rpv) {
                                                        ?>
                                                        <option value="<?= $rpv["id"] ?>"><?= $rpv["nombre"] ?></option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right "><label class="label">Tipo de sensor <sup class="sup">3</sup>:</label></div>
                                            <div class="col-4"><input type="text" name="Sensor" id="Sensor" placeholder="Ej. Veeder Root 350, 450, 450-PLUS"/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right "><label class="label">Capacidad Total :</label></div>
                                            <div class="col-2"><input type="text" name="CapacidadTotal" id="CapacidadTotal" placeholder="0.00"/></div>
                                            <div class="col-4"><label for="Incertidumbre_sensor"></label></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right "><label class="label">Incertidumbre <sup class="sup">4</sup>:</label></div>
                                            <div class="col-2"><input type="text" name="Incertidumbre_sensor" id="Incertidumbre_sensor" placeholder="0.00"/></div>
                                            <div class="col-4"><label for="Incertidumbre_sensor"></label></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right"></div>
                                            <div class="col-2"><button type="submit" class="btn-boots"  name="Boton" value="ActualizarSAT">Actualizar</button></div>
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
                                            <div class="col-3 align-right"><label class="label">Fecha <sup class="sup">5</sup>: </label></div>
                                            <div class="col-2"><input type="date" name="Calibracion" id="Calibracion"/></div>
                                            <div class="col-1"></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right"></div>
                                            <div class="col-2"><button type="submit" class="btn-boots"  name="Boton" value="Ajustar">Actualizar</button></div>
                                        </div>
                                        <input type="hidden" name="busca" class="busca"/>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 no-margin" style="font-size: 10px; color:#55514e;">
                                <div class="row no-padding">
                                    <div class="col-12"><strong class="sup">1. Prefijo : </strong> Es el tipo de almacenamiento se tiene en uso.</div>
                                </div>
                                <div class="row no-padding">
                                    <div class="col-12"><strong class="sup">2. Sistema de Medición : </strong> 
                                        Requerido para expresar la clave de identificación del sistema de medición instalado en cada tanque</div>
                                </div>
                                <div class="row no-padding">
                                    <div class="col-12"><strong class="sup">3. Tipo de sensor : </strong> Es el modelo de sesor que se esta usando para la lectura de tanques.</div>
                                </div>
                                <div class="row no-padding">
                                    <div class="col-12">
                                        <strong class="sup">4. Incertidumbre : </strong>  
                                        Requerido para expresar la incertidumbre de la medición. Por ejemplo, para una incertidumbre del 
                                        1% se deberá ingresar 0.010. Este campo deberá poder repetirse por cada medidor instalado en el tanque.
                                    </div>
                                </div>
                                <div class="row no-padding">
                                    <div class="col-12"><strong class="sup">5. Fecha : </strong> Fecha de la proxima calibración de los tanques.</div>
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

        <?php include_once ("./bootstrap/modals/modal_tanques.html"); ?>

        <script src="./bootstrap/controller/utils.js"></script>
        <script src="./bootstrap/controller/tanques.js"></script>
        <script type="text/javascript">
            $(document).ready(function () {
                $("#formulario1").submit(function (e) {
                    clicksForm = 0;
                    if (!validateFieldWithLabel("Incertidumbre_sensor")) {
                        e.preventDefault();
                    }
                });
            });
        </script>
    </body>
</html>