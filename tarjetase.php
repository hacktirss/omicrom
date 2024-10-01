<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$self = utils\HTTPUtils::self();

$Titulo = "Detalle de unidad";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

require_once "./services/TarjetasService.php";

$tarjetaDAO = new TarjetaDAO();
$tarjetaVO = new TarjetaVO();

if (is_numeric($busca)) {
    $tarjetaVO = $tarjetaDAO->retrieve($busca);
}
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                let busca = "<?= $busca ?>";
                $("#Codigo").val("<?= $tarjetaVO->getCodigo() ?>");
                $("#Impreso").val("<?= $tarjetaVO->getImpreso() ?>");
                $("#Cliente").val("<?= $tarjetaVO->getCliente() ?>").prop("disabled", true);
                $("#Descripcion").val("<?= $tarjetaVO->getDescripcion() ?>").prop("disabled", true);
                $("#Litros").val("<?= $tarjetaVO->getLitros() ?>").prop("disabled", true);
                $("#Importe").val("<?= $tarjetaVO->getImporte() ?>").prop("disabled", true);

                $("#busca").val(busca);
                $("#Codigo").focus();
            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="tarjetas.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">
                    <div id="FormulariosBoots">
                        <div class="container no-margin">
                            <div class="row no-padding">
                                <div class="col-8 background no-margin">
                                    <form name="formulario1" id="formulario1" method="post" action="">
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Codigo de barra: </div>
                                            <div class="col-4">
                                                <input type="text" style="width: 300px;" name="Codigo" id="Codigo" maxlength="30" class="clase-<?= $clase2 ?>"/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Codigo impreso: </div>
                                            <div class="col-4">
                                                <input type="text" style="width: 300px;" name="Impreso" id="Impreso" maxlength="30" class="clase-<?= $clase2 ?>" placeholder="Puede ser el mismo que el codigo de barras"/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Cliente: </div>
                                            <div class="col-4">
                                                <input type="text" style="width: 300px;" name="Cliente" id="Cliente" maxlength="30" class="clase-<?= $clase2 ?>"/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Descripción de la unidad: </div>
                                            <div class="col-4">
                                                <input type="text" style="width: 300px;" name="Descripcion" id="Descripcion" maxlength="30" class="clase-<?= $clase2 ?>"/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Litros permitidos: </div>
                                            <div class="col-4">
                                                <input type="text" style="width: 300px;" name="Litros" id="Litros" maxlength="30" class="clase-<?= $clase2 ?>"/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Importe permitido $: </div>
                                            <div class="col-4">
                                                <input type="text" style="width: 300px;" name="Importe" id="Importe" maxlength="30" class="clase-<?= $clase2 ?>"/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-8 align-center">
                                                <?php
                                                crearBoton("Boton", is_numeric($busca) ? "Actualizar" : "Agregar");
                                                crearInputHidden("busca");
                                                ?>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-11 align-right mensajeInput">
                                                (<sup><i style="color: red;font-size: 8px;" class="fa fa-lg fa-asterisk" aria-hidden="true"></i></sup>) 
                                                Campos necesarios para control de venta
                                            </div>
                                        </div>
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