<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("data/MensajesDAO.php");
include_once ("comboBoxes.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$usuarioSesion = getSessionUsuario();

if ($request->hasAttribute("criteria")) {
    utils\HTTPUtils::setSessionValue("Ventas", true);
    utils\HTTPUtils::setSessionValue("Promocion", false);
    utils\HTTPUtils::setSessionValue("MasVentas", true);
    utils\HTTPUtils::setSessionValue("MenosVentas", false);
    utils\HTTPUtils::setSessionValue("FechaExpira", date("Y-m-d"));
    utils\HTTPUtils::setSessionValue("MinimoConsumo", 1);
    unset($_SESSION["CliSelecc"]);
    unset($_SESSION["TipoGeneracion"]);
    unset($_SESSION["Importe"]);
}
if ($request->hasAttribute("PorVentas") || $request->hasAttribute("PorPromo")) {
    utils\HTTPUtils::setSessionValue("Ventas", $request->getAttribute("PorVentas") === "on" ? true : false);
    utils\HTTPUtils::setSessionValue("Promocion", $request->getAttribute("PorPromo") === "on" ? true : false);
}

if ($request->hasAttribute("MasVentas") || $request->hasAttribute("MenosVentas")) {
    utils\HTTPUtils::setSessionValue("MasVentas", $request->getAttribute("MasVentas") === "on" ? true : false);
    utils\HTTPUtils::setSessionValue("MenosVentas", $request->getAttribute("MenosVentas") === "on" ? true : false);
}

$PorVentas = utils\HTTPUtils::getSessionValue("Ventas");
$Promocion = utils\HTTPUtils::getSessionValue("Promocion");
$MasVentas = utils\HTTPUtils::getSessionValue("MasVentas");
$MenosVenta = utils\HTTPUtils::getSessionValue("MenosVentas");

require './services/lanzaPromoService.php';
$Rvvl = "";
$v = 1;
foreach ($_SESSION["CliSelecc"] as $vvl) {
    if (count($_SESSION["CliSelecc"]) > $v) {
        $Rvvl .= $vvl . ",";
    } else {
        $Rvvl .= $vvl;
    }
    $v++;
}
require './services/ReportesVentasService.php';
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));
$Titulo = "Lanzamiento de promociónes via whatsapp";
$Id = 5;
$rsValue = utils\IConnection::getRowsFromQuery($SqlPnts);
$rsValueIn = utils\IConnection::getRowsFromQuery($SqlPntsIn);
$VTxt = $_SESSION["TipoGeneracion"] === "Gen1" ? "Regalo de $" . $_SESSION["Importe"] : "Por cada litro $" . $_SESSION["Importe"];
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php include './config_omicrom.php'; ?> 
        <title><?= $Gcia ?></title>
        <script type="text/javascript">
            $(document).ready(function () {
                $("#Producto").val("<?= $Producto ?>");
                $("#FechaI").val("<?= $FechaI ?>");
                $("#FechaF").val("<?= $FechaF ?>");
                $("#cFechaI").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaI")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#cFechaExp").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaExpira")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#cFechaF").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaF")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $(".TipoBusqueda").click(function () {
                    $(".TipoBusqueda").prop('checked', false);
                    $(this).prop('checked', true);
                });

                $(".TipoBusquedaClientes").click(function () {
                    $(".TipoBusquedaClientes").prop('checked', false);
                    $(this).prop('checked', true);
                });
                $("#PorPromo").prop('checked',<?= $Promocion ?>);
                $("#PorVentas").prop('checked',<?= $PorVentas ?>);
                $("#MasVentas").prop('checked',<?= $MasVentas ?>);
                $("#MenosVentas").prop('checked',<?= $MenosVenta ?>);

                $(".LanzaPromo").click(function () {
                    Swal.fire({
                        title: "¿Seguro de lanzar la promoción?",
                        background: "#E9E9E9",
                        showConfirmButton: true,
                        confirmButtonText: "Lanzamiento",
                        html: 'Fecha de expiración : <?= utils\HTTPUtils::getSessionValue("FechaExpira") ?><br>Consumo minimo : <?= utils\HTTPUtils::getSessionValue("MinimoConsumo") ?> Litros<br><?= $VTxt ?><br>Importe total : ' + $("#TotalR").val()
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "lanzapromo.php?Boton=LanzaPromo";
                        }
                    });
                });
            });
        </script>
    </head>
    <body>
        <?php BordeSuperior(); ?>

        <table style="width: 100%;height: 550px;">
            <tr>
                <td valign="top" style="width: 50%;">
                    <div class="container">
                        <div class="content">
                            <div id="TablaDatos" style="min-width: 100px !important;">
                                <form id="Form1" name="Form1">
                                    <table style="width: 100%;height: 50px;">
                                        <tr>
                                            <td colspan="2">
                                                <strong>Fecha inicial</strong> <input type="text" name="FechaI" id="FechaI">
                                                <i id="cFechaI" class="fa fa-2x fa-calendar" aria-hidden="true"></i>
                                            </td>
                                            <td>
                                                <strong>Fecha final</strong> <input type="text" name="FechaF" id="FechaF">
                                                <i id="cFechaF" class="fa fa-2x fa-calendar" aria-hidden="true"></i>
                                            </td>
                                            <td>
                                                <strong>Producto:</strong>
                                                <?= ComboboxCombustibles::generateBusqueda("Producto", "140px", "", "SELECCIONE") ?>
                                            </td>
                                        </tr>
                                    </table>
                                    <table>
                                        <tr>
                                            <td style="width: 25%; text-align:right;">
                                                <strong>Tipo de busqueda :</strong>
                                            </td>
                                            <td style="width: 37%; text-align: right;padding-right: 10px;">
                                                <strong>Ventas</strong>
                                                <input type="radio" name="PorVentas" id="PorVentas" class="TipoBusquedaClientes botonAnimatedMin">
                                            </td>
                                            <td style="width: 38%; text-align: right;padding-right: 10px;">
                                                <strong>Promoción</strong>
                                                <input type="radio" name="PorPromo" id="PorPromo" class="TipoBusquedaClientes botonAnimatedMin">
                                            </td>
                                        </tr>
                                    </table>
                                    <table>
                                        <tr>
                                            <td style="width: 25%; text-align:right;">
                                                <strong>Orden :</strong>
                                            </td>
                                            <td style="width: 37%; text-align: right;padding-right: 10px;">
                                                <strong>Mas ventas</strong>
                                                <input type="radio" name="MasVentas" id="MasVentas" class="TipoBusqueda botonAnimatedMin">
                                            </td>
                                            <td style="width: 38%; text-align: right;padding-right: 10px;">
                                                <strong>Menos ventas</strong> 
                                                <input type="radio" name="MenosVentas" id="MenosVentas" class="TipoBusqueda botonAnimatedMin">
                                            </td>
                                        </tr>
                                    </table>
                                    <table>
                                        <tr>
                                            <td style="text-align: center">
                                                <input type="submit" name="BotonG" id="BotonG" value="Busca" style="width: 60%;">
                                            </td>
                                        </tr>
                                    </table>
                                </form>
                                <table class="paginador">
                                    <thead>
                                        <tr>
                                            <th>No.</th>
                                            <th>Id</th>
                                            <th>Producto</th>
                                            <th>Volumen</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $v = 0;
                                        foreach ($rsValue as $vdc) {
                                            if ($_SESSION['VentasBorra'][$vdc["id"]] == 0) {
                                                $v++;
                                                switch ($v) {
                                                    case 1:
                                                        $color = 'style="color:#D4AC0D"';
                                                        $tpCll = "fa-solid fa-trophy fa-2x";
                                                        break;
                                                    case 2:
                                                        $color = 'style="color:#808B96"';
                                                        $tpCll = "fa-solid fa-trophy fa-lg";
                                                        break;
                                                    case 3:
                                                        $color = 'style="color:#D35400"';
                                                        $tpCll = "fa-solid fa-trophy";
                                                        break;
                                                    case 4:
                                                        $color = 'style="color:#E74C3C"';
                                                        $tpCll = "fa-solid fa-medal";
                                                        break;
                                                    case 5:
                                                        $color = 'style="color:#C0392B"';
                                                        $tpCll = "fa-solid fa-medal";
                                                        break;
                                                }
                                                $vvl = '<i class="' . $tpCll . '" ' . $color . '></i>';
                                                ?>
                                                <tr>
                                                    <td style="text-align: center;">
                                                        <?php
                                                        if ($MenosVenta) {
                                                            echo $v;
                                                        } else {
                                                            ?>
                                                            <?= $v <= 5 ? $vvl : $v ?>  
                                                        <?php } ?>
                                                    </td>
                                                    <td><?= $vdc["id"] ?></td>
                                                    <td><?= $vdc["nombre"] ?></td>
                                                    <td style="text-align: right;"><?= number_format($vdc["volumen"], 2) ?></td>
                                                    <td style="text-align: center;"><a class="TransferenciaEfectiva" href="lanzapromo.php?Boton=AddTicket&idCliente=<?= $vdc["id"] ?>&LugarLista=<?= $v ?>"><i class="fa-solid fa-file-import fa-lg" style="color: #006666;"></i></a></td>
                                                </tr>
                                                <?php
                                                $vtt += $vdc["volumen"];
                                                $itt += $vdc["importe"];
                                            }
                                        }
                                        ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td style="text-align: right;font-weight: bold;" colspan="2">Total : </td>
                                            <td style="text-align: right;font-weight: bold;" colspan="2"><?= number_format($vtt, 2) ?></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </td>
                <td valign="top">
                    <div class="container">
                        <div class="content">
                            <div id="TablaDatos" style="min-width: 100px !important;">
                                <form id="Form4" name="Form4">
                                    <table style="width: 100%;height: 50px;">
                                        <tr>
                                            <td style="width: 20%;text-align: right;">
                                                <strong>Fecha expira</strong>  
                                            </td>
                                            <td style="width: 30%; text-align: center;">
                                                <input type="text" name="FechaExpira" id="FechaExpira" value="<?= utils\HTTPUtils::getSessionValue("FechaExpira") ?>" style="width: 100px;">
                                                <i id="cFechaExp" class="fa fa-2x fa-calendar" aria-hidden="true"></i>
                                            </td>
                                            <td style="width: 20%; text-align: center;">
                                                <strong>Consumo minimo (litros)</strong>
                                            </td>
                                            <td style="width: 15%; text-align: center;">
                                                <input type="text" name="ConsumoMin" id="ConsumoMin" value="<?= utils\HTTPUtils::getSessionValue("MinimoConsumo") ?>" style="width: 80px;">
                                            </td>
                                            <td style="text-align: center;">
                                                <input type="submit" name="Boton" id="ParametrosC" value="Actualiza Parametros">
                                            </td>
                                        </tr>
                                    </table>
                                </form>
                                <form id="Form2" name="Form2">
                                    <table style="width: 100%;height: 50px;">
                                        <tr>
                                            <td style="width: 20%;text-align: right;">
                                                <strong>A todos $</strong>  
                                            </td>
                                            <td style="width: 50%; text-align: center;">
                                                <input type="text" name="ImporteGeneral" id="ImporteGeneral">
                                            </td>
                                            <td style="text-align: center;">
                                                <input type="submit" name="Boton" value="Genera 1">
                                            </td>
                                        </tr>
                                    </table>
                                </form>
                                <form id="Form3" name="Form3">
                                    <table style="width: 100%;height: 50px;">
                                        <tr>
                                            <td style="width: 20%;text-align: right;">
                                                <strong>Por cada litro $  </strong>
                                            </td>
                                            <td style="width: 50%; text-align: center;">
                                                <input type="text" name="ImporteXLitro" id="ImporteXLitro">
                                            </td>
                                            <td style="text-align: center;">
                                                <input type="submit" name="Boton" id="Boton" value="Genera 2">
                                            </td>
                                        </tr>
                                    </table>
                                </form>
                                <table class="paginador">
                                    <thead>
                                        <tr>
                                            <th>Cliente</th>
                                            <th>Volumen</th>
                                            <th>Regalo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $v = 0;
                                        foreach ($rsValueIn as $vdc) {
                                            utils\HTTPUtils::setSessionValue("ImporteCli" . $vdc["id"], $_SESSION["TipoGeneracion"] === "Gen1" ? $_SESSION["Importe"] : $_SESSION["Importe"] * $vdc["volumen"]);
                                            $v++;
                                            ?>
                                            <tr>
                                                <td><?= $vdc["nombre"] ?></td>
                                                <td style="text-align: right;"><?= number_format($vdc["volumen"], 2) ?></td>
                                                <td style="text-align: right;"><?= number_format($_SESSION["TipoGeneracion"] === "Gen1" ? $_SESSION["Importe"] : $_SESSION["Importe"] * $vdc["volumen"], 2) ?></td>
                                            </tr>
                                            <?php
                                            $Gtt += $_SESSION["TipoGeneracion"] === "Gen1" ? $_SESSION["Importe"] : $_SESSION["Importe"] * $vdc["volumen"];
                                            $vtt += $vdc["volumen"];
                                            $itt += $vdc["importe"];
                                        }
                                        ?>
                                    </tbody>
                                    <thead>
                                    <td></td>
                                    <td></td>
                                    <td style="text-align: right;"><input type="text" name="TotalR" id="TotalR" value="<?= number_format($Gtt, 2) ?>" style="width: 80px;" disabled></td>
                                    </thead>
                                </table>
                                <table style="width: 100%;">
                                    <tr>
                                        <td style="background-color: white;text-align: center;">
                                            <div class="LanzaPromo">Lanzar promoción</div>
                                        </td>
                                    </tr>
                                </table>
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
<style>

    td .container {
        width: 100%;
        height: 550px;
        overflow: auto; /* Agregamos un overflow */
        border: 1px solid #ccc;
    }
    #divCierraTickets{
        width: 40%;
        margin-left: 30%;
        border:1px solid #006666;
        height: 30px;
        border-radius: 10px;
        padding-top: 5px;
        background-color: #ff6633;
        color: white;
    }
    #divCierraTickets:hover{
        font-weight: bold;
        background-color: #FFA04C;
    }

    .LanzaPromo{
        height: 29px;
        width: 50%;
        background-color: #066;
        color: white;
        border-radius: 10px;
        margin-left: 25%;
        padding-top: 5px;
        margin-top: 15px;
        font-size: 20px;
    }
    .LanzaPromo:hover{
        background-color: #ABEBC6;
        color: #566573;
    }
</style>