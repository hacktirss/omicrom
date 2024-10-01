<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require './services/DispensariosService.php';

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();

$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);
$Titulo = "Detalle de posicion $busca";

$objectVO = new ManVO();

$objectVO->setLado("A");
$objectVO->setPosicion(1);
if (is_numeric($busca)) {
    $objectVO = $manDAO->retrieve($busca, "id", false);
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
                $("#Dispensario").val("<?= $objectVO->getDispensario() ?>");
                $("#Posicion").val("<?= $objectVO->getPosicion() ?>");
                $("#Productos").val("<?= $objectVO->getProductos() ?>");
                $("#Isla").val("<?= $objectVO->getIsla() ?>");
                $("#Lado").val("<?= $objectVO->getLado() ?>");
                $("#Inventario").val("<?= $objectVO->getInventario() ?>");
                $("#spanPosicion").html("<?= $objectVO->getPosicion() ?>");
                $("#Calibracion").val("<?= date("Y-m-d", strtotime(date("Y-m-d") . "+ 6 month")) ?>");
                $("#Dispensario").focus();
                $("#Boton").val("Actualizar");
            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="dispensarios.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">
                    <div id="FormulariosBoots">
                        <div class="container no-margin">
                            <div class="row no-padding">
                                <div class="col-6 no-margin">
                                    <form name="formulario1" id="formulario1" method="post" action="">
                                        <div class="row no-padding">
                                            <div class="col-4 align-right withBackground required">Dispensario:</div>
                                            <div class="col-4"><input type="number" name="Dispensario" id="Dispensario" placeholder="" required="" min="1" max="32"/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right withBackground required">Posicion:</div>
                                            <div class="col-4"><input type="number" name="Posicion" id="Posicion" placeholder="" required="" min="1" max="64"/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right withBackground required">Productos por posicion:</div>
                                            <div class="col-4"><input type="number" name="Productos" id="Productos" placeholder="" required="" min="1" max="3"/></div>
                                        </div>

                                        <div class="row no-padding">
                                            <div class="col-4 align-right withBackground required">Maneja aceites:</div>
                                            <div class="col-4">
                                                <select name="Inventario" id="Inventario">
                                                    <option value="Si">Si</option>
                                                    <option value="No">No</option>
                                                </select>
                                            </div>
                                            <div class="col-4">Tiene asignado venta de aceites y lubricantes</div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right withBackground required">Clave Administrador <sup>1</sup>:</div>
                                            <div class="col-4"><input type="password" name="Clave_Admin" id="Clave_Admin" placeholder=" **** " required/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right"></div>
                                            <div class="col-4"><input type="submit" name="Boton" id="Boton"/></div>
                                        </div>
                                        <input type="hidden" name="busca" class="busca"/>
                                    </form>
                                    <div class="row no-padding">
                                        <div class="col-12">
                                            <span style="color:red;"><strong>Nota: (*)</strong> campos criticos para el correcto funcionamiento del Control Volumetrico.</span>
                                        </div>
                                    </div>
                                </div> 
                                <div class="col-6 no-margin">
                                    <form name="formulario2" id="formulario2" method="post" action="">
                                        <div class="row no-padding">
                                            <div class="col-12 align-center subtitulos">Ajustar fecha de próxima calibración</div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right withBackground">Fecha <sup>2</sup>:</div>
                                            <div class="col-4"><input type="date" name="Calibracion" id="Calibracion"/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right"></div>
                                            <div class="col-4"><input type="submit" name="Boton" value="Ajustar"/></div>
                                        </div>
                                        <input type="hidden" name="busca" class="busca"/>
                                    </form>
                                    <div class="row no-padding">
                                        <div class="col-12">
                                            <span style="color:black;"><strong>Nota:</strong> Esta fecha se aplicara para todas las mangueras de la posición <span id="spanPosicion"></span>.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 no-margin" style="font-size: 9px; color:#55514e;">
                                    <div class="row no-padding">
                                        <div class="col-12"><strong>1. Clave Administrador : </strong> Clave que tiene el administrador para poder hacer modificaciónes.</div>
                                    </div>
                                    <div class="row no-padding">
                                        <div class="col-12"><strong>2. Fecha : </strong> Fecha de la proxima calibración de los dispensarios.</div>
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


    </body>
</html>
