<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$self = utils\HTTPUtils::self();

$Titulo = "Detalle de dictamen";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

require_once "./services/DictamenService.php";

$objectVO = new DictamenVO();
if (is_numeric($busca)) {
    $objectVO = $objectDAO->retrieve($busca);
}
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>        
    </head>

    <body>

        <?php
        BordeSuperior();
        ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <?php $rtn = $request->hasAttribute("return") ? $request->getAttribute("return") : "dictamenes.php"; ?>
                    <a href="<?= $rtn ?>"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">
                    <div id="FormulariosBoots">
                        <div class="container no-margin">
                            <div class="row no-padding">
                                <div class="col-9 background no-margin">
                                    <form name="formulario1" id="formulario1" method="post" action="">
                                        <?php
                                        if ($request->hasAttribute("return")) {
                                            ?>
                                            <input type="hidden" name="return" value="<?= $request->getAttribute("return") ?>">
                                            <?php
                                        }
                                        if (utils\HTTPUtils::getSessionObject("Tipo") == 1) {
                                            ?>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right required">Id:</div>
                                                <div class="col-4"><input type="text" name="Id" id="Id" placeholder="" disabled=""/></div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right required">Nombre:</div>
                                                <div class="col-4"><?php ListasCatalogo::getProveedores("Proveedor", "'Dictamenes'", "", "required='required'") ?></div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right required">Carga:</div>
                                                <div class="col-4"><input type="text" name="Carga" id="Carga" placeholder="" required="" onkeyup="mayus(this);"/></div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right required">Lote:</div>
                                                <div class="col-4"><input type="text" name="Lote" id="Lote" placeholder="" required="" onkeyup="mayus(this);"/></div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right required">Folio:</div>
                                                <div class="col-4"><input type="text" name="NumeroFolio" id="NumeroFolio" placeholder="" required="" onkeyup="mayus(this);"/></div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right required">Emisión:</div>
                                                <div class="col-4"><input type="date" name="FechaEmision" id="FechaEmision" placeholder="" onkeyup="mayus(this);"/></div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right required">Resultado:</div>
                                                <div class="col-8"><textarea name="Resultado" id="Resultado" placeholder="" onkeyup="mayus(this);" rows="10"/><?= $objectVO->getResultado() ?></textarea></div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right"></div>
                                                <div class="col-4">
                                                    <input type="submit" name="Boton" id="Boton"/>
                                                    <input type="submit" name="Boton2" id="Boton2" value="Cerrar Dictamen"/>
                                                </div>
                                                <div class="col-3 align-center" onclick="winuni('visualizaPdf.php?Direccion=Dictamen_<?= $busca ?>.pdf')" style="font-weight: bold;background-color: #D5D8DC;border-radius: 10px;padding-top: 2px;">
                                                    Visualiza archivo <em class="fa-regular fa-eye"></em>
                                                </div>
                                            </div>
                                            <?php
                                        } else {
                                            ?>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right required">Id:</div>
                                                <div class="col-4"><input type="text" name="Id" id="Id" placeholder="" disabled=""/></div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right required">Nombre Verificador:</div>
                                                <div class="col-4"><?php ListasCatalogo::getProveedores("Proveedor", "'CV'", "", "required='required'") ?></div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right required">Folio:</div>
                                                <div class="col-4"><input type="text" name="NumeroFolio" id="NumeroFolio" placeholder="" required="" onkeyup="mayus(this);"/></div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right required">Emisión:</div>
                                                <div class="col-4"><input type="date" name="FechaEmision" id="FechaEmision" placeholder="" onkeyup="mayus(this);"/></div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right required">Resultado:</div>
                                                <div class="col-8"><textarea name="Resultado" id="Resultado" placeholder="" onkeyup="mayus(this);" rows="10"/><?= $objectVO->getResultado() ?></textarea></div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right"></div>
                                                <div class="col-4"><input type="submit" name="Boton" id="Boton"/></div>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                        <input type="hidden" name="busca" id="busca"/>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <?php
        echo $ExistFile = is_file("/home/omicrom/xml/Dictamen_$busca.pdf") ? "<div style='width:20%;margin-left:40%; border:1px solid black;text-align:center;border-radius:5px;background-color:#099;color:white;' id='DescargaArchivo'>Descarga</div>" : "";
        if ($busca > 0) {
            ?>
            <table style="width: 70%;margin-left: 15%;" id="DownloadDictamen" title="Bajar dictamen" summary="Bajar dictamen">
                <tr>
                    <th style="text-align: center;">
                        <div class="row no-padding" style="width: 400px;height: 150px;">
                            <div class="col-12">
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
                            </div>
                        </div>
                    </th>
                </tr>
            </table>
            <?php
        } else {
            ?>

            <table style="width: 70%;margin-left: 15%;" id="DownloadDictamen" title="Bajar dictamen">
                <tr>
                    <th style="text-align: center;">
                        <div class="row no-padding" style="width: 400px;height: 150px;">
                            <div class="col-12">
                                <div class="container show-dropzone">
                                    <div class="row no-padding">
                                        <div class="col-lg-12">
                                            <div class="btn-group w-100">
                                                <form class="dropzone" id="myDropCreate" enctype="multipart/form-data">
                                                    <div class="fallback">
                                                        <input type="file" name="file" id="myId" multiple>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </th>
                </tr>
            </table>
            <?php
        }
        ?>
        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        $ExistFile = is_file("/home/omicrom/xml/Dictamen_1.pdf") ? "fa-download" : "fa-upload";
        ?>
        <script src="dropzone/min/dropzone.min.js"></script>
        <script type="text/javascript">
                                                    $(document).ready(function () {
                                                        $(".<?= $ExistFile ?>").addClass("fa-fade");
                                                        $("#busca").val("<?= $busca ?>");
                                                        $("#Id").val("<?= $busca ?>");
                                                        $("#Proveedor").val("<?= $objectVO->getProveedor() ?>");
                                                        $("#Lote").val("<?= $objectVO->getLote() ?>");
                                                        $("#NumeroFolio").val("<?= $objectVO->getNumerofolio() ?>");
                                                        $("#FechaEmision").val("<?= $objectVO->getFechaemision() ?>");
                                                        if ("<?= $request->getAttribute("IdE") ?>" !== "") {
                                                            $("#Carga").val("<?= $request->getAttribute("IdE") ?>");
                                                        } else {
                                                            $("#Carga").val("<?= $objectVO->getNoCarga() ?>");
                                                        }
                                                        if ($("#busca").val() !== "NUEVO") {
                                                            $("#Boton").val("Actualizar");
                                                            if ("<?= $objectVO->getEstado() ?>" === "1") {
                                                                $("#Boton").hide();
                                                                $("#Boton2").hide();
                                                                $("#DownloadDictamen").hide();
                                                            }
                                                        } else {
                                                            $("#Boton2").hide();
                                                            $("#Boton").val("Agregar");
                                                        }

                                                        $("#DescargaArchivo").click(function () {
                                                            window.location.href = "dictamenese.php?Name=Dictamen_<?= $busca ?>.pdf&op=Download"
                                                        });
                                                        $("#Proveedor").focus();
                                                        Dropzone.prototype.defaultOptions.dictDefaultMessage = "Dar click para subir archivo PDF <i class='fa-solid fa-upload fa-2x'></i>";
                                                        Dropzone.options.myDrop = {
                                                            url: "uploadDictamen.php?IdDictamen=<?= $busca ?>",
                                                            uploadMultiple: true,
                                                            maxFileSize: 3,
                                                            acceptedFiles: ".pdf",
                                                            init: function init() {
                                                                this.on("addedfile", function () {
                                                                    setTimeout(function () {
                                                                        window.location.href = "dictamenese.php?return=entradas.php";
                                                                    }, 800);
                                                                });
                                                            }
                                                        }

                                                        Dropzone.options.myDropCreate = {
                                                            url: "uploadDictamen.php?IdDictamen=<?= $request->getAttribute("IdE") ?>",
                                                            uploadMultiple: true,
                                                            maxFileSize: 3,
                                                            acceptedFiles: ".xml, .json",
                                                            init: function init() {
                                                                this.on("addedfile", function (variable) {
                                                                    console.log(variable);
                                                                    setTimeout(function () {
                                                                        jQuery.ajax({
                                                                            type: "POST",
                                                                            url: "getByAjax.php",
                                                                            dataType: "json",
                                                                            cache: false,
                                                                            data: {"Op": "UtimoDictamen"},
                                                                            beforeSend: function (xhr) {
                                                                                Swal.fire({
                                                                                    title: 'Cargando',
                                                                                    showConfirmButton: false,
                                                                                    background: "rgba(213, 216, 220 , 0.9)",
                                                                                    backdrop: "rgba(5, 5, 25, 0.5)",
                                                                                    allowOutsideClick: false,
                                                                                    closeOnConfirm: true
                                                                                });
                                                                                Swal.showLoading();
                                                                            },
                                                                            success: function (data) {
                                                                                console.log(data);
                                                                                window.location.href = "dictamenese.php?return=<?= $rtn ?>&busca=" + data.respuesta;
                                                                            }
                                                                        });

                                                                    }, 800);
                                                                });
                                                            }
                                                        }

                                                        $("#Boton").click(function () {
                                                            console.log($("#Resultado").val().length);
                                                            if ($("#Resultado").val().length >= 10 && $("#Resultado").val().length <= 300) {
                                                                return true;
                                                            } else {
                                                                alert("El resultado tiene que tener un minimo de 10 palabras");
                                                            }
                                                            return false;
                                                        });
                                                    });
        </script>
    </body>

</html>