<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("./comboBoxes.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$self = utils\HTTPUtils::self();

$Titulo = "Detalle de Direcciones";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

require_once "./services/DireccionesService.php";

$objectVO = new DireccionVO();
$direccionDAO = new DireccionDAO();
if (is_numeric($busca)) {
    $objectVO = $direccionDAO->retrieve($busca);
    $a = $objectVO->getColonia();
}
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>        
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="direcciones.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">

                    <div id="FormulariosBoots">

                        <div class="container no-margin">
                            <div class="row no-padding">
                                <div class="col-9 background no-margin">
                                    <form name="formulario1" id="formulario1" method="post" action="">
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Id:</div>
                                            <div class="col-4"><input type="text" name="Id" id="Id" placeholder="" disabled=""/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Descripcion:</div>
                                            <div class="col-8"><input type="text" name="Descripcion" id="Descripcion" placeholder="" ></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Calle:</div>
                                            <div class="col-8"><input type="text" name="Calle" id="Calle" placeholder="" required="" onkeyup="mayus(this);"/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Numero Ext.:</div>
                                            <div class="col-8"><input type="text" name="Ext" id="Ext" placeholder="" required="" onkeyup="mayus(this);"/></div>

                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Numero Int:</div>
                                            <div class="col-8"><input type="text" name="Int" id="Int" placeholder="" onkeyup="mayus(this);"/></div>

                                        </div> 
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Estado:</div>
                                            <div class="col-8">
                                                <select name="Estado" id="Estado" required >
                                                    <?php
                                                    $arrayDatos = CatalogosSelectores::getEstado();
                                                    foreach ($arrayDatos as $key => $value) {
                                                        ?>
                                                        <option value="<?= $key ?>"/><?= $value ?></option>
                                                        <?php
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required" > Municipio:</div>
                                            <div class="col-8">
                                                <select name="Municipio" id="Municipio" required ></select>                                              
                                            </div>
                                        </div>                                       
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Localidad:</div>
                                            <div class="col-8">
                                                <select name="Localidad" id="Localidad" required ></select>                                                           
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Codigo Postal:</div>
                                            <div class="col-8">
                                                <input type="text" name="CodigoPostal" id="CodigoPostal" placeholder="" required="" onkeyup="mayus(this);"/>
                                            </div>
                                        </div>                                       
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Colonia:</div>
                                            <div class="col-8">
                                                <select name="Colonia" id="Colonia" required ></select> 
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right"></div>
                                            <div class="col-4"><input type="submit" name="Boton" id="Boton"/></div>
                                        </div>                                       
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
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

        <script>
            $(document).ready(function () {
                $("#busca").val("<?= $busca ?>");

                $("#Id").val("<?= $busca ?>");
                $("#Calle").val("<?= $objectVO->getCalle() ?>");
                $("#Ext").val("<?= $objectVO->getNum_exterior() ?>");
                $("#Int").val("<?= $objectVO->getNum_interior() ?>");
                $("#Estado").val("<?= $objectVO->getEstado() ?>");
                $("#Descripcion").val("<?= $objectVO->getDescripcion() ?>");
                AjaxEstado("<?= $objectVO->getEstado() ?>", "<?= $objectVO->getMunicipio() ?>", "<?= $objectVO->getLocalidad() ?>");
                $("#Municipio").val("<?= $objectVO->getMunicipio() ?>");
                $("#Localidad").val("<?= $objectVO->getLocalidad() ?>");
                $("#CodigoPostal").val("<?= $objectVO->getCodigo_postal() ?>");
                AjaxCodigoPostal("<?= $objectVO->getCodigo_postal() ?>", "<?= $objectVO->getColonia() ?>");
                $("#Colonia").val("<?= $objectVO->getColonia() ?>");
                $("#TOrigen").val("<?= $objectVO->getTabla_origen() ?>");
                AjaxTablaOrigen("<?= $objectVO->getTabla_origen() ?>", "<?= $objectVO->getId_origen() ?>");
                $("#IdOrigen").val("<?= $objectVO->getId_origen() ?>");
                $("#Calle").focus();

                if ($("#busca").val() !== "NUEVO") {
                    $("#Boton").val("Actualizar");
//                    $("#TOrigen").prop('disabled', true);
//                    $("#IdOrigen").prop('disabled', true);
                } else {
                    $("#Boton").val("Agregar");
                }
                $("#Boton").on("click", function (e) {
                });
                $("#Estado").change(function () {
                    AjaxEstado($("#Estado").val(), "Estado");
                });
                $("#TOrigen").change(function () {
                    AjaxTablaOrigen($("#TOrigen").val());
                });
                $("#CodigoPostal").change(function () {
                    if ($("#CodigoPostal").val().length == 5) {
                        AjaxCodigoPostal($("#CodigoPostal").val());
                    }

                });

            });

            function AjaxEstado(dt, val1, val2) {
                jQuery.ajax({
                    type: 'GET',
                    url: 'getDirecciones.php',
                    dataType: 'json',
                    cache: false,
                    data: {"Var": dt, "Origen": "Estado"},
                    beforeSend: function (xhr) {
                        $('#Localidad').empty();
                        $('#Municipio').empty();
                    },
                    success: function (data) {
                        for (var dt of data)
                        {
                            for (var d of dt) {
                                if (typeof d["localidad"] != "string") {

                                    $('#Municipio').append($('<option>', {
                                        value: d["clave"],
                                        text: d["clave"] + ".- " + d["descripcion"]
                                    }));
                                } else {
                                    $('#Localidad').append($('<option>', {
                                        value: d["id"],
                                        text: d["localidad"] + ".- " + d["descripcion"]
                                    }));
                                }
                            }
                            $("#Municipio").val(val1);
                            $("#Localidad").val(val2);
                        }
                    },
                    error: function (jqXHR) {
                        console.log(jqXHR);
                    }
                });
            }

            function AjaxTablaOrigen(dt, var1) {
                console.log(dt);
                jQuery.ajax({
                    type: 'GET',
                    url: 'getDirecciones.php',
                    dataType: 'json',
                    cache: false,
                    data: {"Var": dt, "Origen": "IdOrigen"},
                    beforeSend: function (xhr) {
                        $('#IdOrigen').empty();
                    },
                    success: function (data) {
                        for (var dt of data)
                        {
                            $('#IdOrigen').append($('<option>', {
                                value: dt["id"],
                                text: dt["id"] + ".- " + dt["nombre"]
                            }));
                        }
                        $("#IdOrigen").val(var1);
                    },
                    error: function (jqXHR) {
                        console.log(jqXHR);
                    }
                });
            }

            function AjaxCodigoPostal(dt, val1) {
                jQuery.ajax({
                    type: 'GET',
                    url: 'getDirecciones.php',
                    dataType: 'json',
                    cache: false,
                    data: {"Var": dt, "Origen": "CodigoPostal"},
                    beforeSend: function (xhr) {
                        $('#Colonia').empty();
                    },
                    success: function (data) {
                        for (var dt of data)
                        {
                            $('#Colonia').append($('<option>', {
                                value: dt["colonia"],
                                text: dt["codigo_postal"] + ".- " + dt["nombre"]
                            }));
                        }
                        $("#Colonia").val(val1);
                    },
                    error: function (jqXHR) {
                        console.log(jqXHR);
                    }
                });
            }
        </script>

    </body>

</html>