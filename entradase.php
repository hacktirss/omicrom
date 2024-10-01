<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ('./comboBoxes.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();

require_once './services/CapturaPipasService.php';

$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Titulo = "Captura de pipas";
$MeOK = true;
$Id = 41;

$objectVO = new MeVO();

$CarA = $mysqli->query("SELECT cargas.tanque,cargas.clave_producto as producto,cargas.aumento as cantidad,
                    cargas.fecha_insercion as fechae,com.descripcion,vol_inicial,vol_final,cargas.id 
                    FROM com,cargas WHERE cargas.clave_producto=com.clave AND cargas.id = '$carga'");
$Car = $CarA->fetch_array();

$HeA = $mysqli->query("SELECT me_tmp.*, folioenvios conversion FROM me_tmp WHERE carga = '$carga' LIMIT 1");
$He = $HeA->fetch_array();

$self = utils\HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title> 
        <script type="text/javascript">
            $(document).ready(function () {
                $("#Proveedor").val("<?= $objectVO->getProveedor() ?>").prop("required", true);
                $("#Punto_exportacion").val("<?= $objectVO->getPunto_exportacion() ?>");
                $("#Pais_destino").val(<?= $objectVO->getPais_destino() ?>);
                $("#Pais_origen").val("<?= $objectVO->getPais_origen() ?>");
                $("#Punto_internacion").val("<?= $objectVO->getPunto_internacion() ?>");
                $("#Medio_entrada").val("<?= $objectVO->getMedio_transporte_entrada() ?>");
                $("#Medio_salida").val("<?= $objectVO->getMedio_transporte_salida() ?>");
                $("#Incoterms").val("<?= $objectVO->getIncoterms() ?>");
                $("#Transporte").val("<?= $objectVO->getProveedorTransporte() ?>").prop("required", true);
                $("#Terminal").val("<?= $objectVO->getTerminal() ?>").prop("required", true);

                $("#Documento").val("<?= $objectVO->getDocumento() ?>").prop("required", true);
                $("#FechaFac").val("<?= $objectVO->getFechafac() ?>").prop("required", true);
                $("#Clavevehiculo").val("<?= $objectVO->getClavevehiculo() ?>").prop("required", true);
                $("#Facturas").val("<?= $objectVO->getFacturas() ?>").prop("required", true);

                $("#Proveedor").on("change", function () {
                    obtenProveedor();
                });
                $(".rm-extranjero").hide();
                $("#TanqueProducto").html("<?= $Car["tanque"] . " | " . $Car["descripcion"] ?>");
                $("#Fecha_tanque").html("<?= $Car["fechae"] ?>");
                $("#Cantidad").html("<?= number_format($Car["cantidad"], 2) ?>");
                obtenProveedor();

            });
            function obtenProveedor() {
                var provee = document.getElementById("Proveedor");
                var opcionSelect = provee.options[provee.selectedIndex].text;
                resultado = opcionSelect.split("|", 3);
                console.log(resultado[2]);
                if (resultado[2] === " Extranjero") {
                    $("#rm-exportacion").show();
                    $("#rm-internacion").show();
                    $("#rm-destino").show();
                    $("#rm-origen").show();
                    $("#rm-entrada").show();
                    $("#rm-salida").show();
                    $("#rm-incoterms").show();
                    document.getElementById("Punto_exportacion").required = true;
                    document.getElementById("Punto_internacion").required = true;
                    document.getElementById("Pais_destino").required = true;
                    document.getElementById("Pais_origen").required = true;
                    document.getElementById("Medio_entrada").required = true;
                    document.getElementById("Medio_salida").required = true;
                    document.getElementById("Incoterms").required = true;
                } else {
                    $(".rm-extranjero").hide();
                    document.getElementById("Punto_exportacion").required = false;
                    document.getElementById("Punto_internacion").required = false;
                    document.getElementById("Pais_destino").required = false;
                    document.getElementById("Pais_origen").required = false;
                    document.getElementById("Medio_entrada").required = false;
                    document.getElementById("Medio_salida").required = false;
                    document.getElementById("Incoterms").required = false;
                    $("#Punto_exportacion").val("");
                    $("#Pais_destino").val("");
                    $("#Pais_origen").val("");
                    $("#Punto_internacion").val("");
                    $("#Medio_entrada").val("");
                    $("#Medio_salida").val("");
                    $("#Incoterms").val("");

                }
            }
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <div class="mensajes"><?= $Msj ?></div>

        <form name="form1" method="post" action="">
            <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
                <tr>
                    <td style="text-align: center;" class="nombre_cliente">
                        <a href="pipaspendientes.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                    </td>
                    <td style="vertical-align: top;">
                        <div id="FormulariosBoots">
                            <div class="container">
                                <div class="row background">
                                    <div class="col-12 align-left title">Tanque: <span id="TanqueProducto"></span></div>
                                    <div class="col-12 align-left">Fecha: <span id="Fecha_tanque"></span></div>
                                    <div class="col-12 align-left">Cantidad : <span id="Cantidad"></span></div>
                                </div>
                                <div class="row background">                                
                                    <div class="col-12 no-margin">
                                        <div class="row no-padding"><div class="col-12 align-left subtitle">PARÁMETROS DEL SAT</div></div>
                                        <div class="row no-padding">
                                            <div class="col-5 align-right"><label class="label">Proveedor de combustible : </label></div>
                                            <div class="col-5"><?php ComboboxProveedor::generate("Proveedor", "'Combustibles'", ""); ?></div>
                                        </div>
                                        <div class="row no-padding rm-extranjero" id="rm-exportacion">
                                            <div class="col-5 align-right"><label class="label">Punto de exportacion : </label></div>
                                            <div class="col-4"><input type="text" name="Punto_exportacion" id="Punto_exportacion"></div>
                                        </div>
                                        <div class="row no-padding rm-extranjero" id="rm-internacion">
                                            <div class="col-5 align-right"><label class="label">Punto de internacion : </label></div>
                                            <div class="col-4"><input type="text" name="Punto_internacion" id="Punto_internacion"></div>
                                        </div>
                                        <div class="row no-padding rm-extranjero" id="rm-destino">
                                            <div class="col-5 align-right" ><label class="label">Pais de destino : </label></div>
                                            <div class="col-4"><input type="text" name="Pais_destino" id="Pais_destino"></div>
                                        </div>
                                        <div class="row no-padding rm-extranjero" id="rm-origen">
                                            <div class="col-5 align-right"><label class="label">Pais de origen : </label></div>
                                            <div class="col-4"><input type="text" name="Pais_origen" id="Pais_origen"></div>
                                        </div>
                                        <div class="row no-padding rm-extranjero" id="rm-entrada">
                                            <div class="col-5 align-right"><label class="label">Medio de transporte de entrada : </label></div>
                                            <div class="col-4"><input type="text" name="Medio_entrada" id="Medio_entrada"></div>
                                        </div>
                                        <div class="row no-padding rm-extranjero" id="rm-salida">
                                            <div class="col-5 align-right"><label class="label">Medio de transporte de salida : </label></div>
                                            <div class="col-4"><input type="text" name="Medio_salida" id="Medio_salida"></div>
                                        </div>
                                        <div class="row no-padding rm-extranjero" id="rm-incoterms">
                                            <div class="col-5 align-right"><label class="label">Incoterms : </label></div>
                                            <div class="col-4"><input type="text" name="Incoterms" id="Incoterms"></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-5 align-right"><label class="label">Proveedor de transporte : </label></div>
                                            <div class="col-5"><?php ComboboxCatalogoUniversal::generate("Transporte", "PROVEEDORES_TRANSPORTE", "", "", "SELECCIONE UN PROVEEDOR DE TRANSPORTE"); ?></div>
                                            <div class="col-1"><i class="fa fa-lg fa-question-circle" aria-hidden="true" data-toggle="modal" data-target="#modal-entradas-listas" data-identificador="PROVEEDORES_TRANSPORTE" data-operacion="11"></i></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-5 align-right"><label class="label">Terminal de almacenamiento : </label></div>
                                            <div class="col-5"><?php ComboboxCatalogoUniversal::generate("Terminal", "TERMINALES_ALMACENAMIENTO", "", "", "SELECCIONE UNA TERMINAL DE ALMACENAMIENTO"); ?></div>
                                            <div class="col-1"><i class="fa fa-lg fa-question-circle" aria-hidden="true" data-toggle="modal" data-target="#modal-entradas-listas" data-identificador="TERMINALES_ALMACENAMIENTO" data-operacion="11"></i></div>
                                        </div>                                       
                                        <div class="row no-padding">
                                            <div class="col-5 align-right"><label class="label">Tipo de documento : </label></div>
                                            <div class="col-3">
                                                <select name="Documento" id="Documento">
                                                    <option value="RP">RP</option>
                                                    <option value="CP">CP</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-5 align-right"><label class="label">Tipo de Carga : </label></div>
                                            <div class="col-3">
                                                <select name="TipoCarga" id="TipoCarga">
                                                    <option value="Normal">Normal</option>
                                                    <option value="Consignacion">Consignacion</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-5 align-right"><label class="label">Fecha de factura(s) : </label></div>
                                            <div class="col-3">
                                                <input type="date" id="FechaFac" name="FechaFac" required="required">
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-5 align-right"><label class="label">Clave del vehículo : </label></div>
                                            <div class="col-2"><input type="text" id="Clavevehiculo" name="Clavevehiculo" onkeyup="transformarMayusculas(this);" minlength="6" maxlength="12"></div>
                                            <div class="col-4">solo letras y numeros Ej.PMX99999</div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-5 align-right"><label class="label">Número de facturas que amparan la carga : </label></div>
                                            <div class="col-1">
                                                <select name="Facturas" id="Facturas">
                                                    <option value="1">1</option>
                                                    <option value="2">2</option>
                                                    <option value="3">3</option>
                                                    <option value="4">4</option>
                                                    <option value="5">5</option>
                                                    <option value="6">6</option>
                                                </select>
                                            </div>
                                            <div class="col-4">en caso de tener varias facturas</div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right"></div>
                                            <div class="col-2"><button type="submit" class="btn-boots"  name="Boton" value="Enviar">Enviar</button></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-12 align-center warning" id="Messages"></div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </td>
                </tr>
            </table>
            <input type="hidden" name="op" id="op" value="2">
            <input type="hidden" name="busca" id="busca">
            <input type="hidden" name="IdCarga" value="<?= $carga ?>"/>
        </form>

        <?php
        echo $paginador->footer(false, null, false, false, 0, false);
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

        <link rel="stylesheet" href="bootstrap/bootstrap-4.0.0/dist/css/bootstrap-modal.css" type="text/css">

        <?php include_once ("./bootstrap/modals/modal_entradas.html"); ?>

        <script>
            $(document).ready(function () {
                $(".btn-boots").on("click", function (e) {
                    if ($("#Clavevehiculo").val().length < 6) {
                        e.preventDefault();
                        $("#Clavevehiculo").focus();
                        $("#Messages").html("La clave del vehículo debe contener de 6 a 12 caracteres.");
                    }
                });

                $("#Clavevehiculo").on("keyup", function () {
                    $("#Messages").html("");
                });
            });
        </script>

        <script src="./bootstrap/controller/utils.js"></script>
        <script src="./bootstrap/controller/entradas.js"></script>

    </body>
</html>


