<?php
#Librerias
session_start();

include_once("check.php");
include_once("libnvo/lib.php");
include_once('./comboBoxes.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();

require_once './services/PeriodoPromocioneService.php';

$Titulo = "Detalle de promición";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);
$PeriodoPuntosDAO = new PeriodoPuntosDAO();
$PeriodoPuntosVO = new PeriodoPuntosVO();
$PeriodoPuntosVO = $PeriodoPuntosDAO->retrieve($busca);

$Return = "periodopromocion.php";
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">

    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script type="text/javascript">
            $(document).ready(function () {
                $("#Descripcion").val("<?= $PeriodoPuntosVO->getDescripcion() ?>");
                $("#FechaInicial").val("<?= $PeriodoPuntosVO->getFecha_inicial() ?>");
                $("#FechaCulmina").val("<?= $PeriodoPuntosVO->getFecha_culmina() ?>");
                $("#FechaFinal").val("<?= $PeriodoPuntosVO->getFecha_final() ?>");
                $("#Status").val("<?= $PeriodoPuntosVO->getActivo() ?>");
                $("#TipoV").val("<?= $PeriodoPuntosVO->getTipo_concentrado() ?>");
                $("#Monto").val("<?= $PeriodoPuntosVO->getMonto_promocion() ?>");
                $("#LimiteInferior").val("<?= $PeriodoPuntosVO->getLimite_inferior() ?>");
                $("#LimiteSuperior").val("<?= $PeriodoPuntosVO->getLimite_superior() ?>");
                $("#TipoPromo").val("<?= $PeriodoPuntosVO->getTipo_periodo() ?>");
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
                                            <div class="col-12 align-right required"><sub>Campos requeridos para tipo Acumulativo y por Consumo</sub></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-2 align-right">Descripcion : </div>
                                            <div class="col-10 align-right"><input type="text" name="Descripcion" id="Descripcion"></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-2 align-right">Fecha Inicial  : </div>
                                            <div class="col-2 align-left">
                                                <input type="text" name="FechaInicial" id="FechaInicial" style="width: 100px;">
                                                <img id="cFechaA" src="libnvo/calendar.png" alt="Calendario" height="15">
                                            </div>
                                            <div class="col-2 align-right">Fecha fin promoción : </div>
                                            <div class="col-2 align-left">
                                                <input type="text" name="FechaCulmina" id="FechaCulmina" style="width: 100px;">
                                                <img id="cFechaB" src="libnvo/calendar.png" alt="Calendario" height="15">
                                            </div>
                                            <div class="col-2 align-right">Fecha recompensas : </div>
                                            <div class="col-2 align-left">
                                                <input type="text" name="FechaFinal" id="FechaFinal" style="width: 100px;">
                                                <img id="cFechaC" src="libnvo/calendar.png" alt="Calendario" height="15">
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-2 align-right required">Status :</div>
                                            <div class="col-2 align-right">
                                                <select name="Status" id="Status">
                                                    <option value="0">Inactivo</option>
                                                    <option value="1">Activo</option>
                                                </select>
                                            </div>
                                            <div class="col-2 align-right required">Tipo:</div>
                                            <div class="col-2 align-right">
                                                <select name="TipoV" id="TipoV">
                                                    <option value="V">Volumen</option>
                                                    <option value="I">Importe</option>
                                                </select>
                                            </div>
                                            <div class="col-2 align-right required">Monto:</div>
                                            <div class="col-2 align-right"><input type="text" name="Monto" id="Monto"></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-2 align-right">Limite inferior:</div>
                                            <div class="col-2 align-right"><input type="text" name="LimiteInferior" id="LimiteInferior"></div>
                                            <div class="col-2 align-right">Limite superior:</div>
                                            <div class="col-2 align-right"><input type="text" name="LimiteSuperior" id="LimiteSuperior"></div>
                                            <div class="col-2 align-right required">Tipo de promoción:</div>
                                            <div class="col-2 align-right">
                                                <select name="TipoPromo" id="TipoPromo">
                                                    <!--<option value="P"><?= TiposDeBeneficio::P ?></option>-->
                                                    <option value="C"><?= TiposDeBeneficio::C ?></option>                                                    
                                                    <option value="A"><?= TiposDeBeneficio::A ?></option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <?php
                                            $VeneXProducto = explode(",", $PeriodoPuntosVO->getProducto_promocion());
                                            $MontoPromo = explode(",", $PeriodoPuntosVO->getFactores_producto());
                                            $ComDAO = new CombustiblesDAO();
                                            $ComVO = new CombustiblesVO();
                                            $e = 0;
                                            foreach ($VeneXProducto as $vxp) {
                                                if ($e <= 2) {
                                                    $ComVO = $ComDAO->retrieve($vxp, "clavei");
                                                    ?>
                                                    <div class="col-2 align-right required"><?= $ComVO->getDescripcion() ?></div>
                                                    <div class="col-2 align-right">
                                                        <input type="text" name="<?= $ComVO->getClavei() ?>" id="<?= $ComVO->getClavei() ?>" value="<?= $MontoPromo[$e] ?>" class="tiposImportes">
                                                    </div>
                                                    <?php
                                                }
                                                $e++;
                                            }
                                            $e = 0;
                                            foreach ($VeneXProducto as $vxp) {
                                                if ($e <= 2) {
                                                    ?>
                                                    <div class="col-2"></div>
                                                    <div class="col-2 align-center" style="font-weight: bold;font-size: 14px;height: 25px;padding-top: 7px;">
                                                        <?= $PeriodoPuntosVO->getMonto_promocion() ?> <?= $PeriodoPuntosVO->getTipo_concentrado() === "V" ? "Litro(s)" : "Peso(s)" ?> <br>Genera <?= $MontoPromo[$e] ?>
                                                    </div>
                                                    <?php
                                                }
                                                $e++;
                                            }
                                            ?>
                                            <input type="hidden" name="TotalImport" id="TotalImport">
                                        </div>
                                        <div class="row no-padding" style="margin-top: 15px;">
                                            <?php
                                            $VeneXProducto = explode(",", $PeriodoPuntosVO->getProducto_promocion());
                                            $MontoPromo = explode(",", $PeriodoPuntosVO->getLimites_inferiores());
                                            $e = 0;
                                            foreach ($VeneXProducto as $vxp) {
                                                if ($e <= 2) {
                                                    $ComVO = $ComDAO->retrieve($vxp, "clavei");
                                                    ?>
                                                    <div class="col-2 align-right required">Limite Inferior<br> <?= $ComVO->getDescripcion() ?></div>
                                                    <div class="col-2 align-right">
                                                        <input type="text" name="<?= $ComVO->getClavei() ?>_Limit_Inf" id="<?= $ComVO->getClavei() ?>_Limit_Inf" value="<?= $MontoPromo[$e] ?>" class="tiposImportes">
                                                    </div>
                                                    <?php
                                                }
                                                $e++;
                                            }
                                            $e = 0;
                                            foreach ($VeneXProducto as $vxp) {
                                                if ($e <= 2) {
                                                    ?>
                                                    <div class="col-2"></div>
                                                    <div class="col-2 align-center" style="font-weight: bold;font-size: 14px;height: 25px;padding-top: 7px;">
                                                        <?= $PeriodoPuntosVO->getTipo_concentrado() === "V" ? "Litro(s)" : "Peso(s)" ?> 
                                                    </div>
                                                    <?php
                                                }
                                                $e++;
                                            }
                                            ?>
                                        </div>
                                        <div class="row no-padding">
                                            <?php $Boton = $busca > 0 ? "Actualizar" : "Agregar"; ?>
                                            <div class="col-12 align-center" style="height: 40px;"><input type="submit" name="Boton" value="<?= $Boton ?>"></div>
                                        </div>
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
                displayCalendar($("#FechaCulmina")[0], "yyyy-mm-dd", $(this)[0]);
            });
            $("#cFechaC").css("cursor", "hand").click(function () {
                displayCalendar($("#FechaFinal")[0], "yyyy-mm-dd", $(this)[0]);
            });
            $(document).ready(function () {
                $(".tiposImportes").click(function () {

                });
            });
        </script>
        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>
    </body>

</html>