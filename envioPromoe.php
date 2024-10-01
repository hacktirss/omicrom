<?php
#Librerias
session_start();

include_once("check.php");
include_once("libnvo/lib.php");
include_once('./comboBoxes.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();

require_once './services/EnvioPromoService.php';

$Titulo = "Detalle de promición";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);
$EnvioPromoDAO = new EnvioPromoDAO();
$EnvioPromoVO = new EnvioPromoVO();
$EnvioPromoVO = $EnvioPromoDAO->retrieve($busca);

$Return = "envioPromo.php";
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">

    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script type="text/javascript">
            $(document).ready(function () {
                if ("<?= $busca ?>" === "NUEVO") {
                    $("#FechaInicial").val("<?= date("Y-m-d H:i:s") ?>");
                    $("#FechaCreacion").val("<?= date("Y-m-d H:i:s") ?>");
                    $("#FechaFinal").val("<?= date("Y-m-d H:i:s") ?>");
                } else {
                    $("#FechaInicial").val("<?= $EnvioPromoVO->getFecha_inicio() ?>");
                    $("#FechaCreacion").val("<?= $EnvioPromoVO->getFecha_creacion() ?>");
                    $("#FechaFinal").val("<?= $EnvioPromoVO->getFecha_final() ?>");
                    $("#Producto").val("<?= $EnvioPromoVO->getId_producto() ?>");
                    $("#Descuento").val("<?= $EnvioPromoVO->getDescuento() ?>");
                    $("#Consumo_Min").val("<?= $EnvioPromoVO->getConsumo_min() ?>");
                    $("#Status").val("<?= $EnvioPromoVO->getStatus() ?>");
<?php
if ($EnvioPromoVO->getStatus() === "Abierto") {
    
}
?>

                }
            });
        </script>
    </head>
    <body>
        <?php BordeSuperior(); ?>
        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="<?= $Return ?>"><div class="RegresarCss " alt="Flecha regresar" style="">Regresar</div></a>
                </td>
                <td style="vertical-align: top;">
                    <div id="FormulariosBoots">
                        <div class="container no-margin">
                            <div class="row no-padding">
                                <div class="col-12 background container no-margin">
                                    <form name="formulario1" id="formulario1" method="post" action="">
                                        <div class="row no-padding">
                                            <div class="col-2 align-right"></div>
                                            <div class="col-3 align-right">
                                                <table summary="Id">
                                                    <tr><th class="subtitulos">Id : <?= $busca ?></th></tr>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Descripcion : </div>
                                            <div class="col-7 align-right">
                                                <textarea name="Descripcion" id="Descripcion" rows="4"><?= $EnvioPromoVO->getDescripcion() ?></textarea>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Creación: </div>
                                            <div class="col-4 align-left">
                                                <input type="text" name="FechaCreacion" id="FechaCreacion" style="width: 150px;" disabled>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Fecha inicio : </div>
                                            <div class="col-4 align-left">
                                                <input type="date" name="FechaInicial" id="FechaInicial" style="width: 150px;">
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Fecha fin : </div>
                                            <div class="col-4 align-left">
                                                <input type="date" name="FechaFinal" id="FechaFinal" style="width: 150px;">
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Producto :</div>
                                            <div class="col-2 align-right">
                                                <?php ComboboxInventario::generate("Producto", "'Combustible'", "350px", "", "Todos los combustibles"); ?>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Descuento :</div>
                                            <div class="col-2 align-right"><input type="text" name="Descuento" id="Descuento"></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Consumo Minimo :</div>
                                            <div class="col-2 align-right"><input type="text" name="Consumo_Min" id="Consumo_Min"></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Status :</div>
                                            <div class="col-2 align-right">
                                                <select name="Status" id="Status">
                                                    <option value="Abierto">Abierto</option>
                                                    <option value="Cerrada">Cerrada</option>
                                                    <option value="Cancelada">Cancelada</option>
                                                </select>
                                            </div>
                                        </div>
                                        <?php if ($EnvioPromoVO->getStatus() === "Abierto" || $busca === "NUEVO") { ?>
                                            <div class="row no-padding">
                                                <?php $Boton = $busca > 0 ? "Actualizar" : "Agregar"; ?>
                                                <div class="col-12 align-center" style="height: 40px;"><input type="submit" name="Boton" value="<?= $Boton ?>"></div>
                                            </div>
                                        <?php } ?>
                                        <input type="hidden" name="busca" value="<?= $busca ?>">
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <script type="text/javascript">
            $("#cFechaA").css("cursor", "hand").click(function () {
                displayCalendar($("#FechaInicial")[0], "yyyy-mm-dd", $(this)[0]);
            });
            $("#cFechaB").css("cursor", "hand").click(function () {
                displayCalendar($("#FechaFinal")[0], "yyyy-mm-dd", $(this)[0]);
            });
        </script>
        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>
    </body>

</html>