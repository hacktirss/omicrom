<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$mysqli = iconnect();

require './services/ComisionesService.php';

$Titulo = "Detalle de tanques";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

$objectVO = new ComisionesVO();
if (is_numeric($busca)) {
    $objectVO = $ComisionesDAO->retrieve($busca);
    $BotonName = "Actualizar";
} else {
    $BotonName = "Agregar";
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
                $("#Fecha").val("<?= $objectVO->getVigencia() ?>");
                $("#Proveedor").val("<?= $objectVO->getId_prv() ?>");
                $("#Combustible").val("<?= $objectVO->getId_com() ?>");
                $("#Monto").val("<?= $objectVO->getMonto() ?>");
                if ($("#busca").val() !== "NUEVO") {
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
                    <a href="comisiones.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">
                    <div id="FormulariosBoots">
                        <div class="container">
                            <div class="row background">                                
                                <div class="col-12 no-margin">
                                    <form name="formulario1" id="formulario1" method="post" action="comisionese.php">
                                        <div class="row no-padding">
                                            <div class="col-12 align-left subtitle">COMISIONES</div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right"><label class="label">Proveedor: </label></div>
                                            <div class="col-4"><?php ListasCatalogo::getClientesConsignacion("Proveedor") ?></div>
                                            <div class="col-1"><i class="fa fa-lg fa-question-circle" aria-hidden="true" data-toggle="modal" data-target="#modal-tanques-listas" data-identificador="CLAVES_TANQUES" data-operacion="11"></i></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right no-margin"><label class="label">Combustible: </label></div>
                                            <div class="col-4"><?php ListasCatalogo::getCombustiblesId("Combustible", "Todos") ?></div>
                                            <div class="col-1"><i class="fa fa-lg fa-question-circle" aria-hidden="true" data-toggle="modal" data-target="#modal-tanques-listas" data-identificador="CLAVES_SISTEMAS_MEDICION" data-operacion="11"></i></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right "><label class="label">Fecha de Inicio: <sup class="sup">3</sup>:</label></div>
                                            <div class="col-4"><input type="date" name="Fecha" id="Fecha"/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right "><label class="label">Monto: <sup class="sup">4</sup>:</label></div>
                                            <div class="col-2"><input type="text" name="Monto" id="Monto" placeholder="0.00"/></div>
                                            <div class="col-4"></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right"></div>
                                            <div class="col-2"><button type="submit" class="btn-boots"  name="Boton" value="<?= $BotonName ?>"><?= $BotonName ?></button></div>
                                        </div>
                                        <input type="hidden" name="busca" class="busca"/>
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