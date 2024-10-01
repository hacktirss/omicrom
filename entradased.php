<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ('./comboBoxes.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$usuarioSesion = getSessionUsuario();

require_once './services/CapturaPipasService.php';

$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Titulo = "Captura de pipas";
$MeOK = true;
$Id = 41;

$CarA = $mysqli->query("SELECT cargas.tanque,cargas.clave_producto as producto,cargas.aumento as cantidad,
                    cargas.fecha_insercion as fechae,com.descripcion,vol_inicial,vol_final,cargas.id 
                    FROM com,cargas WHERE cargas.clave_producto=com.clave AND cargas.id = '$carga'");
$Car = $CarA->fetch_array();

$selectHeader = "SELECT me_tmp.*, folioenvios conversion FROM me_tmp WHERE carga = '$carga' AND usuario = " . $usuarioSesion->getId();
$He = utils\IConnection::execSql($selectHeader);

$selectDetalle = "
        SELECT id,foliofac,fechafac,importefac,volumenfac,preciou,cuadrada,tipo, IF(uuid = '-----', '', uuid) uuid, volumen_devolucion,tipo,tipocomprobante 
        FROM me_tmp 
        WHERE carga = '$carga' AND usuario = " . $usuarioSesion->getId();
$Me_tmpA = utils\IConnection::getRowsFromQuery($selectDetalle);

$options = array(TipoCarga::NORMAL, TipoCarga::CONSIGNACION);

$self = utils\HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#busca").val("<?= $busca ?>");

                $("input[name=Foliofac]").on("keyup", function () {
                    this.value = this.value.replace(/[^0-9]/g, "");
                });
            });
        </script>
    </head>

    <body>

        <?php
        BordeSuperior();

        $Vlr = 1;
        $sum_square = 0;
        foreach ($Me_tmpA as $Me_tmp) {
            $sum_square += $Me_tmp["cuadrada"];
            ?>

            <div id="FormulariosBoots">
                <div class="container">
                    <div class="row background" style="padding-bottom: 5px;padding-top: 5px; font-weight: bold;">
                        <div class="col-12 align-center">Datos de la factura <?= $Vlr ?></div>
                    </div>
                    <div class="row no-margin">
                        <div class="col-5 background">
                            <?php
                            $output = "";
                            for ($i = 0; $i < count($options); $i++) {
                                $output .= '<option ' . ( $Me_tmp["tipo"] === $options[$i] ? 'selected="selected"' : '' ) .
                                        ' value="' . $options[$i] . '">' . $options[$i] . '</option>';
                            }
                            ?>
                            <form class="formsEntriesSave" method="post" action="">
                                <div class="container">
                                    <div class="row no-padding">
                                        <div class="col-5 align-right required">Folio fac:</div>
                                        <div class="col-7">
                                            <input type="text" class="form-control" name="Foliofac" value="<?= $Me_tmp["foliofac"] ?>" onkeyup="mayus(this);" placeholder="Solo números" required="required"/>
                                        </div>
                                    </div>
                                    <div class="row no-padding">
                                        <div class="col-5 align-right required">UUID:</div>
                                        <div class="col-7">
                                            <input type="text" class="form-control form-uuid" name="UUID" value="<?= $Me_tmp["uuid"] ?>" onkeyup="mayus(this);" placeholder="Ej: DFA7E8F2-DF91-408F-A328-2921AF47C94A"/>
                                        </div>
                                    </div>
                                    <div class="row no-padding">
                                        <div class="col-12 align-right"><label for="UUID" class="form-uuid-label"></label></div>
                                    </div>
                                    <div class="row no-padding">
                                        <div class="col-5 align-right required">Tipo de carga:</div>
                                        <div class="col-7">
                                            <select name="TipoCarga" class="form-control" required="required">
                                                <?= $output ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row no-padding">
                                        <div class="col-5 align-right required">Volumen:</div>
                                        <div class="col-3 align-right"><span>m<sup>3</sup></span><input type="radio" name="Unidad" value="1" class="Unidad" checked="checked"></div>
                                        <div class="col-3 align-right"><span>Lts.</span><input type="radio"  name="Unidad" value="2" class="Unidad"></div>                                            
                                    </div>
                                    <div class="row no-padding">
                                        <div class="col-5 align-right"></div>
                                        <div class="col-7">
                                            <input type="text" class="form-control" name="Volumenfac" value="<?= !empty($Me_tmp["volumenfac"]) ? $Me_tmp["volumenfac"] : "" ?>" required="required" placeholder="Volumen"/>
                                        </div>
                                    </div>
                                    <div class="row no-padding">
                                        <div class="col-5 align-right">Precio unitario:</div>
                                        <div class="col-3 align-right"><input type="radio" name="Conversion" value="1" class="Conversion" checked="checked"></div>
                                        <div class="col-3 align-right">Importe <input type="radio"  name="Conversion" value="2" class="Conversion"></div>
                                    </div>
                                    <div class="row no-padding">
                                        <div class="col-5 align-right"></div>
                                        <div class="col-7">
                                            <input type="text" name="Preciou" class="form-control" value="<?= !empty($Me_tmp["preciou"]) ? number_format($Me_tmp["preciou"], 6, ".", "") : "" ?>" required="required" placeholder="Precio unitario"/>
                                        </div>
                                    </div>
                                    <div class="row no-padding">
                                        <div class="col-5 align-right required">Importe total:</div>
                                        <div class="col-7">
                                            <input type="text" name="Importefac" class="form-control" value="<?= !empty($Me_tmp["importefac"]) ? $Me_tmp["importefac"] : "" ?>" required="required" placeholder="Fac. C/Impuestos">
                                        </div>
                                    </div>
                                    <div class="row no-padding">
                                        <div class="col-5 align-right">Devolucion en litros:</div>
                                        <div class="col-6">
                                            <input type="text" name="Volumen_devolucion" class="form-control" value="<?= empty($Me_tmp["volumen_devolucion"]) ? "0.00" : $Me_tmp["volumen_devolucion"]; ?>" title="Programa flotillas">
                                        </div>
                                        <div class="col-1 align-center">
                                            <i class="fa fa-question-circle fa-lg" aria-hidden="true" title="Campo para capturar el volumen de litros que devolvio PEMEX como parte del programa de flotillas"></i>
                                        </div>
                                    </div>
                                    <div class="row no-padding">
                                        <div class="col-5 align-right">Tipo de comprobante:</div>
                                        <div class="col-6">
                                            <select name="TipoComprobante">
                                                <option value='<?= $Me_tmp["tipocomprobante"] ?>' selected><?= $Me_tmp["tipocomprobante"] == "I" ? "Ingreso" : "Egreso" ?></option>
                                                <option value="I">Ingreso</option>
                                                <option value="E">Egreso</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row no-padding">
                                        <div class="col-5" title="En caso de ser factura con servicio de flete" style="font-size: 10px;">
                                            <input type="checkbox" name="CheckLocation" id="CheckLocation" value="<?= $_REQUEST["BotonCheck"] ?>" class="botonAnimatedMin"/>
                                            Factura por flete 
                                        </div>
                                        <div class="col-3 align-right"><?= $Me_tmp["cuadrada"] === "1" ? "<img src='libnvo/verde.png'>" : "<img src='libnvo/amarillo.png'>" ?></div>
                                        <div class="col-4"><input type="submit" class="form-control" name="Boton" value="Guardar"></div>                                        
                                    </div>
                                </div>
                                <input type="hidden" name="IdCarga" value="<?= $carga ?>"/>
                                <input type='hidden' name='busca' value='<?= $Me_tmp["id"] ?>'>
                                <input type='hidden' name='NoFactura' value='<?= $Vlr ?>'>
                            </form>
                            <form method="post" action="" id="ButtonCheckbox">
                                <input type="hidden" name="BotonCheck" value="Si">
                            </form>
                            <?php
                            if (!is_numeric($Me_tmp["foliofac"])) {
                                ?>
                                <div class="container show-dropzone">
                                    <div class="row no-padding">
                                        <div class="col-lg-12">
                                            <div class="btn-group w-100">
                                                <form class="dropzone" id="myDrop" enctype="multipart/form-data">
                                                    <div class="fallback">
                                                        <input type="file" name="file" id="myId" multiple>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                        </div>

                        <div class="col-7" valign='top'>
                            <?php
                            $nImp = 0;
                            ?>
                            <form class="formsEntriesConcepts" method='post' action="" style="padding-left: 5px;">
                                <div id="TablaDatos" class="row " style="min-height: 150px;min-width: 400px;">
                                    <table aria-hidden="true">
                                        <tr>
                                            <td class="fondoVerde">Producto</td>
                                            <td class="fondoVerde">Cnt</td>
                                            <td class="fondoVerde">Precio</td>
                                            <td class="fondoVerde">Importe</td>
                                            <td class="fondoVerde">Borrar</td>
                                        </tr>

                                        <?php
                                        $selectMed = "
                                                    SELECT med_tmp.clave, inv.descripcion, med_tmp.cantidad, ROUND(med_tmp.precio,6) precio, med_tmp.idnvo 
                                                    FROM med_tmp
                                                    LEFT JOIN inv ON med_tmp.clave=inv.id WHERE med_tmp.id='" . $Me_tmp["id"] . "'";
                                        $Med_tmpA = $mysqli->query($selectMed);
                                        $tieneIva = false;
                                        while ($Med_tmp = $Med_tmpA->fetch_array()) {
                                            echo "<tr class='textosItalicos'>";
                                            echo "<td>" . $Med_tmp["descripcion"] . "</td>";
                                            echo "<td align='right'>" . $Med_tmp["cantidad"] . "</td>";
                                            echo "<td align='right'>" . $Med_tmp["precio"] . "</td>";
                                            echo "<td align='right'>" . round($Med_tmp["cantidad"] * $Med_tmp["precio"], 2) . "</td>";
                                            echo "<td align='center'><a class='textosCualli_i' href='$self?busca=" . $Me_tmp["id"] . "&cId=" . $Med_tmp["idnvo"] . "&op=Si'><i class=\"icon fa fa-lg fa-trash\" aria-hidden=\"true\"></i></a></td>";
                                            echo "</tr>";
                                            $nImp += ($Med_tmp["cantidad"] * $Med_tmp["precio"]);
                                            if ($Med_tmp["clave"] === "6") {
                                                $tieneIva = true;
                                            }
                                        }
                                        ?>
                                        <tr>
                                            <td class="upTitles">Importe</td>
                                            <td class="upTitles"></td>
                                            <td class="upTitles"></td>
                                            <td class="upTitles"><?= truncateFloat(number_format($nImp, 3, ".", ""), 2); ?></td>
                                            <td class="upTitles align-center">
                                                <?php if (!empty($Me_tmp["cuadrada"]) && $nImp > 0 && !$tieneIva) { ?>
                                                    <a href="entradased.php?Producto=6&Tipo=2&Importe=0&Boton=Agregar&busca=<?= $Me_tmp["id"] ?>"><i class="fa fa-lg fa-money" aria-hidden="true" title="Agregar iva"></i></a>
                                                <?php } ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <?= $Me_tmp["tipo"] ?>
                                <?php if (empty($Me_tmp["cuadrada"]) && $nImp > 0) { ?>
                                    <div class="row"  class="Conceptos">
                                        <div class="col-4"><?php ComboboxInventario::generate("Producto", "'Ent-pipas'", "", "required='required'", "SELECCIONE UN CONCEPTO"); ?></div>
                                        <div class="col-4">
                                            Precio: <input type='radio' name='Tipo' value="1" checked="checked">
                                            Importe: <input type='radio' name='Tipo' value="2">
                                        </div>
                                        <div class="col-2"><input type='text' name='Importe' size='8' class="texto_tablas" placeholder="0.00" required="required"></div>
                                        <div class="col-2"><input type='submit' name='Boton' value='Agregar' class="nombre_cliente texto_tablas" id='Agregar'></div>
                                        <input type='hidden' name='busca' value='<?= $Me_tmp["id"] ?>'>
                                    </div>
                                    <div style="font-size: 11px;">
                                        &nbsp;Si selecciona la opción <strong>Precio</strong> se multiplicará por la cantidad  <strong><?= $Me_tmp["volumenfac"] ?></strong> m<sup>3</sup>
                                        <br/>&nbsp;Si selecciona la opción <strong>Importe</strong> se dividirá por la cantidad  <strong><?= $Me_tmp["volumenfac"] ?></strong> m<sup>3</sup>
                                    </div>
                                <?php } ?>
                            </form>
                            <div id="butn"></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            $Vlr++;
        }

        if (count($Me_tmpA) == $sum_square || $Me_tmp["tipo"] === "Consignacion") {
            ?>
            <form name="formComplete" action="" method="post">
                <p align='center' class="texto_tablas" style="color: #FF6633;font-weight: bold;">La entrada de combustible se encuetra cuadrada con su(s) factura(s) </p>
                <p align='center'><input type='submit' name='Boton' value='Consolidar entrada' class="nombre_cliente"></p>
            </form>
            <?php
        }
        echo $paginador->footer(false, null, false, false, 0, false);
        BordeSuperiorCerrar();
        PieDePagina();
        $Msjx = getExternalMessage();
        if ($Msjx !== "") {
            $Clr = strstr($Msjx, "ERROR") ? "#F5B7B1" : "#ABEBC6";
            $Icon = strstr($Msjx, "ERROR") ? "error" : "success";
            $Time = strstr($Msjx, "ERROR") ? 100000 : 2000;
            $Error = false;
            $Error = strstr($Msjx, "1001") ? "1" : "0";
            ?>
            <script type="text/javascript">
                $(document).ready(function () {
                    Swal.fire({
                        title: "<?= $Msjx ?>",
                        background: "<?= $Clr ?>",
                        icon: "<?= $Icon ?>",
                        timer: <?= $Time ?>
                    });
                    if ("<?= $Error ?>" == "1") {
                        setTimeout(function () {
                            window.location.href = "pipaspendientes.php";
                        }, 5000);
                    }
                });
            </script>
            <?php
        }
        ?>
        <script src="dropzone/min/dropzone.min.js"></script>
        <script type="text/javascript">
            $(document).ready(function () {
                $(".botonAnimatedMin").change(function () {
                    $("#ButtonCheckbox").submit();
                });
                if ("<?= $_REQUEST["BotonCheck"] ?>" == "Si") {
                    $(".botonAnimatedMin").prop('checked', true);
                }
                $(".form-uuid").on("keyup", function () {
                    var text = $(this).val();
                    console.log(text);
                    $(this).val(text.replaceAll("\u2010", "-"));
                });
                $(".formsEntriesSave").submit(function (e) {
                    clicksForm = 0;
                    var tipoCarga = $(this).find("select[name=TipoCarga]").val();
                    console.log(tipoCarga);
                    if (tipoCarga == "Normal") {
                        if (!validateFieldUuid($(this))) {
                            e.preventDefault();
                            setInterval(function () {
                                location.reload();
                            }, 3000);
                        }
                    }
                    return true;
                });
                Dropzone.prototype.defaultOptions.dictDefaultMessage = "Arrastrar o dar click para subir archivo XML";
                Dropzone.options.myDrop = {
                    url: "upload.php?busca=<?= $carga ?>&Cliente=<?= $usuarioSesion->getId() ?>&Location=" + $("#CheckLocation").val(),
                    uploadMultiple: true,
                    maxFileSize: 3,
                    acceptedFiles: ".xml",
                    init: function init() {
                        this.on("addedfile", function () {
                            setTimeout(function () {
                                location.reload(true);
                            }, 800);
                        });
                    }
                }
                $('select[name="TipoComprobante"]').change(function () {
                    if ($(this).val() === "E") {
                        Swal.fire({
                            icon: 'info',
                            iconColor: '#F1948A',
                            title: 'Al ingresar la pipa como egreso, se verá reducida el volumen en tus archivos volumetricos.'
                        });
                    }
                });
            });
        </script>
    </body>
</html>


