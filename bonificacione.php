<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("comboBoxes.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();

require './services/BonificacionService.php';

$Titulo = "Bonificacion a clientes";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);
$Fecha = date("Y-m-d");
$Cliente = $request->getAttribute("Cliente");
$busca = $busca == "" ? "NUEVO" : $busca;
$selectCliente = "
            SELECT id,nombre
            FROM cli
            WHERE id = $Cliente";
$Cli = utils\IConnection::execSql($selectCliente);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom.php"; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#busca").val("<?= $busca ?>");
                $("#Cliente").val("<?= $Cliente ?>");
            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;width:90px;" class="nombre_cliente">
                    <a href="bonificacion.php"><div class="RegresarCss " alt="Flecha regresar" style="">Regresar</div></a>
                </td>
                <td style="vertical-align: top;">
                    <div id="FormulariosBoots">
                        <div class="container no-margin">
                            <div class="row no-padding">
                                <div class="col-12 background container no-margin">
                                    <form name="form1" id="form1" method="post" action="">
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Id : </div>
                                            <div class="col-2 align-left"><?= $busca ?></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Cuenta : </div>
                                            <div class="col-5 align-left"><?= $Cliente . " | " . $Cli["nombre"] ?></div>
                                        </div>
                                        <div class="row no-padding" style="height: 50px;">
                                            <div class="col-4 align-right">Puntos disponibles : </div>
                                            <div class="col-5 align-left"><div id='Puntoshtml'></div></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Cantidad : </div>
                                            <div class="col-1 align-left"><input type="number" name="Cantidad" id="Cantidad" value="1" min="1" max="1000"></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Producto : </div>
                                            <div class="col-5 align-left"><?php ComboboxInventario::generate("Producto", "'Puntos'", "350px", "required='required'"); ?></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Promoción : </div>
                                            <div class="col-5 align-left">
                                                <?php
                                                $Promos = "SELECT * FROM periodo_puntos WHERE  tipo_periodo='P' AND activo = 1";
                                                $Prm = utils\IConnection::getRowsFromQuery($Promos);
                                                ?>
                                                <select name="Promo" id="Promo" class='texto_tablas'>
                                                    <?php
                                                    foreach ($Prm as $P) {
                                                        $Sts = $P["fecha_culmina"] <= date("Y-m-d") ? "Finalizada " . $P["fecha_culmina"] : "En proceso " . $P["fecha_culmina"];
                                                        ?>
                                                        <option value="<?= $P["id"] ?>"><?= $P["descripcion"] ?> - <?= $Sts ?></option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row no-padding"  style="height: 40px;">
                                            <div class="col-4 align-right"></div>
                                            <div class="col-2 align-left">
                                                <input type='submit' class='nombre_cliente' name='Boton' id='Boton' value='Agregar' style="margin-top: 10px">
                                                <input type="hidden" name="busca" id="busca">
                                                <input type="hidden" name="Cliente" id="Cliente">
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
        <script type="text/javascript">
            $(document).ready(function () {
                AjaxGetPuntos($("#Promo").val());
                $("#Promo").change(function () {
                    AjaxGetPuntos($("#Promo").val());
                });
                $("#Producto").change(function () {
                    AjaxGetPuntos($("#Promo").val());
                });
                $("#Cantidad").change(function () {
                    AjaxGetPuntos($("#Promo").val());
                });

                function AjaxGetPuntos(dt) {
                    jQuery.ajax({
                        type: 'GET',
                        url: 'getPuntos.php',
                        dataType: 'json',
                        cache: false,
                        data: {"Var": dt, "Origen": "GetPuntos", "Cliente": "<?= $Cliente ?>", "Producto": $("#Producto").val(), "Op": "ObtenPuntos"},
                        success: function (data) {
                            var restantes = data.Puntos - data.puntosConsumidos;
                            var Cnt = $("#Cantidad").val();
                            var Res = restantes - (data.InvPuntos * Cnt);
                            var Cav = "<table style='width:80%;border:1px solid #717171;border-radius:5px;'><tr style='font-weight: bold;background-color:#D5D8DC'><td style='width:30%;text-align:center;'>Acumulado</td>" +
                                    "<td style='width:30%;text-align:center;'>Costo</td><td style='width:30%;text-align:center;'>Total</td><td style='width:10%;'></td></tr>";
                            if (Res < 0 || data.Puntos === 0) {
                                $("#Puntoshtml").html(Cav +
                                        "<tr style='height:30px;'><td>" +
                                        restantes + "</td><td>" +
                                        data.InvPuntos * Cnt + "</td><td>" +
                                        Res + " </td><td style='text-align:center;'><i class='fa fa-times-circle fa-2x' aria-hidden='true' style='color:red'></i></td></tr>" +
                                        "</table>");
                                $("#Boton").hide();
                            } else {
                                $("#Puntoshtml").html(Cav +
                                        "<tr style='height:30px;'><td>" +
                                        restantes + "</td><td>" +
                                        data.InvPuntos * Cnt + "</td><td>" +
                                        Res + "</td><td style='text-align:center;'><i class='fa fa-check-circle fa-2x' aria-hidden='true' style='color:green'></i></td></tr>" +
                                        "</table>");
                                $("#Boton").show();
                            }
                        },
                        error: function (jqXHR) {
                            console.log(jqXHR);
                        }
                    });
                }
            });
        </script>
    </body>
</html>