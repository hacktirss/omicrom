<?php
#Librerias
session_start();
set_time_limit(720);

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("comboBoxes.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();

$nameVariableSession = "Bonificación de monederos";

//$Gfmt = utils\HTTPUtils::getSessionBiValue("catalogoFacturas", "fmt");          //Formato
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));
$Titulo = "Modulo detalle de bonificaciones";
$nameVarBusca = "busca";
if ($request->hasAttribute("criteria")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("busca"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

require './services/envioEfectivoService.php';
$ciaDAO = new CiaDAO();
$ciaVO = $ciaDAO->retrieve(1);

$version_cfdi = $ciaVO->getVersion_cfdi();

$pacA = $mysqli->query("SELECT * FROM proveedor_pac WHERE activo = 1");
$pac = $pacA->fetch_array();

if (is_numeric($busca)) {
//    $EnvEfectivoVO = $EnvEfectivoDAO->retrieve($busca);
}
$registrosfc = 0;

$SqlIniPuntos = "select COUNT(1) n from beneficios WHERE id_unidad = " . $Rst["id"];
$RsCnt = utils\IConnection::execSql($SqlIniPuntos);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom.php"; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#BotonNvo").click(function () {
                    console.log("Carga");
                });
                $("#BotonAceptar").hide();
                $("#ScrollAdd").scroll();
                $("#Ticket").focus();
                variable = parseFloat($("#ValAcumulado").val());
                $("#ImpTotal").html(parseFloat(variable).toFixed(2));
                i = 0

            });
        </script>
    </head>
    <input type="hidden" name="IdUnidad" id="IdUnidad" value="<?= $Rst["id"] ?>">
    <body>

        <?php BordeSuperior(); ?>
        <div id="DatosEncabezado" style="border: 1px solid #808B96;">
            <table aria-hidden="true">
                <tr>
                    <td><label>Id: </label><?= $Rst["id"] ?></td>
                    <td><label>Cliente: </label><?= $Rst["nombre"] ?></td>
                    <td>
                        <label>Puntos: </label><?= $Rst["puntos"] ?>
                        <?= $RsCnt["n"] == 0 ? "<div style='display:inline-block;font-size:15px;margin-left:40px;' id='IniPuntos'>Inicializa puntos</div>" : ""; ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="3"><label>Descripcion: </label><?= $Rst["descripcion"] ?></td>
                </tr>
            </table>
        </div>
        <input type="hidden" name="PuntosCliente" id="PuntosCliente" value="<?= $Rst["puntos"] ?>">
        <table style="width: 100%" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="width: 40px;">
                    <a href="bonificacionMonederos.php"><div class="RegresarCss " alt="Flecha regresar" style="">Regresar</div></a>
                </td>
                <td style="height : 280px !important; text-align : center !important; vertical-align: top !important;">
                    <div>
                        <input type="hidden" name="FechaAct" id="FechaAct" value="<?= $He["fecha"] ?>"> 
                        <div style="display: inline-block; width: 45%;height: 295px;" class="StatusCerrado">
                            <table style="background-color: #D5D8DC;width: 100%;border-radius: 5px;"> 
                                <caption>Bonifica combustible</caption>
                                <tr style="height: 40px;">
                                    <th style="text-align: right;font-weight: bold;" scope="col">No. Ticket :</th>
                                    <th style="text-align: left;" scope="col"><input type="number" name="IdTicket" id="IdTicket"></th>
                                </tr>
                                <tr style="height: 40px;">
                                    <td style="text-align: right;font-weight: bold;">Puntos : </td>
                                    <td style="text-align: left;"><input type="number" name="ImporteDisponible" id="ImporteDisponible" max="<?= $Rst["puntos"] ?>" min="0"></td>
                                </tr>
                                <tr style="height: 40px;">
                                    <td style="text-align: center;" colspan="2">
                                        <input type="button" name="Boton"  id="BotonBuscar" value="Buscar" style="width: 150px;">
                                        <input type="button" name="Boton"  id="BotonAceptar" value="Agregar Beneficios" style="width: 150px;">
                                    </td>
                                </tr>
                            </table> 
                            <div id="Respuesta_Ticket"></div>
                            <table style="background-color: #D5D8DC;width: 100%;border-radius: 5px;"> 
                                <caption>Bonifica Productos</caption>
                                <tr style="height: 40px;">
                                    <th style="text-align: right;font-weight: bold;" scope="col">Producto:</th>
                                    <th style="text-align: left;" scope="col">
                                        <?php ComboboxInventario::generatePuntos("ProductosValue", "'puntos'"); ?>
                                    </th>
                                </tr>
                                <tr style="height: 40px;">
                                    <td style="text-align: center;" colspan="2">
                                        <input type="button" name="Boton" value="Recompenza" id="Recompenza">
                                    </td>
                                </tr>
                            </table> 
                            <div id="Respuesta_Ticket"></div>
                        </div>
                        <div style="display: inline-block; width: 45%;" id="ScrollAdd" class="StatusCerrado">
                            <table class="paginador">
                                <caption>Tickets con beneficios</caption>
                                <tr><th>Ticket</th><th>Fecha</th><th>Puntos</th><th>Importe</th><th>Descuento</th></tr>
                                <?php
                                foreach ($rsEf as $ef) {
                                    ?>
                                    <tr title="<?= $ef["cnc"] ?>">
                                        <td style="text-align: center;padding-right: 10px;">
                                            <?php $ref = $ef["tm"] === "C" ? 'impticketdetick.php?busca=' . $ef["id"] . '&op=1' : 'pdfbonificacionMonedero.php?busca=' . $ef["id"] . '&TipoProducto=' . $ef["tm"] . '&IdCb=' . $ef["idCb"] . '&IdCli=' . $Rst["idCli"] . '&IdInv=' . $ef["itb"] ?>
                                            <?php $Type = $ef["tm"] === "C" ? "Combustible" : "Recompensa" ?>
                                            <a href=javascript:winuni("<?= $ref ?>")><?= $Type ?></a>
                                        </td>
                                        <td><?= $ef["fecha"] ?></td>
                                        <td style="text-align: right;padding-right: 10px;"><?= $ef["puntos"] ?></td>
                                        <td style="text-align: right;padding-right: 5px;">$ <?= number_format($ef["importe"], 2, ".", "") ?></td>
                                        <td style="text-align: right;padding-right: 5px;">$ <?= number_format($ef["descuento"], 2, ".", "") ?></td>
                                    </tr>
                                    <?php
                                    $TtImp += $ef["importe"];
                                    $Spp += $ef["puntos"];
                                    $TDesc += $ef["descuento"];
                                    $e++;
                                }
                                ?>
                                <tr style="font-weight: bold;">
                                    <td colspan="2" style="text-align: right">Total:</td>
                                    <td style="text-align: right;padding-right: 10px;"><?= $Spp ?></td>
                                    <td style="text-align: right;padding-right: 10px;">$ <?= number_format($TtImp, 2) ?></td>
                                    <td style="text-align: right;padding-right: 10px;">$ <?= number_format($TDesc, 2) ?></td>
                                </tr>
                            </table>
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
    <style>
        #ScrollAdd {
            width: 100%;
            padding: 5px;
            height: 300px;
            overflow-y: scroll;
            overflow-x: hidden;
        }
    </style>
</html>
<script type="text/javascript" src="js/bonificacion.js"></script>
