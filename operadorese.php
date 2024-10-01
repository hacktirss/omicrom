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

$Titulo = "Detalle de Operador";
$nameVarBusca = "buscaO";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

require_once "./services/OperadoresService.php";

$objectVO = new OperadorVO();
$operadorDAO = new OperadorDAO();
if (is_numeric($busca)) {
    error_log("El valor de busca : " . $busca);
    $objectVO = $operadorDAO->retrieve($busca);
}

//echo print_r($objectVO, true);
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
                    <a href="<?= $request->hasAttribute("ReturnD") ? $request->getAttribute("ReturnD") : "operadores.php"; ?>"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">

                    <div id="FormulariosBoots">

                        <div class="container no-margin">
                            <div class="row no-padding">
                                <div class="col-10 background no-margin">
                                    <form name="formulario1" id="formulario1" method="post" action="">
                                        <?php
                                        if ($request->hasAttribute("ReturnD")) {
                                            ?>
                                            <input type="hidden" name="ReturnD" value="<?= $request->getAttribute("ReturnD") ?>">
                                            <?php
                                        }
                                        ?>                                        
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Id:</div>
                                            <div class="col-2"><input type="text" name="Id" id="Id" placeholder="" required="" disabled=""/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Nombre:</div>
                                            <div class="col-5"><input type="text" name="Nombre" id="Nombre" placeholder="" required="" onkeyup="mayus(this);"/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">RFC:</div>
                                            <div class="col-3"><input type="text" name="RFC" id="RFC" placeholder="" required="" onkeyup="mayus(this);"/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Licencia:</div>
                                            <div class="col-3"><input type="text" name="Licencia" id="Licencia" placeholder="" required="" onkeyup="mayus(this);"/></div>
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
                        <h3 style="color: #2C3E50">Direccion <sub style="font-size: 8px;color: #DC7633">Necesario para generar la Carta Porte</sub></h3>
                        <div class="container no-margin">
                            <div class="row no-padding">
                                <div class="col-10 background no-margin">
                                    <form name="formulario1" id="formulario1" method="post" action="">  
                                        <?php
                                        if ($request->hasAttribute("ReturnD")) {
                                            ?>
                                            <input type="hidden" name="ReturnD" value="<?= $request->getAttribute("ReturnD") ?>">
                                            <?php
                                        }
                                        ?>     
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Calle:</div>
                                            <div class="col-7"><input type="text" name="Calle" id="Calle" placeholder="" required="" onkeyup="mayus(this);"/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Numero Ext.:</div>
                                            <div class="col-2"><input type="text" name="Ext" id="Ext" placeholder="" required="" onkeyup="mayus(this);"/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Numero Int:</div>
                                            <div class="col-2"><input type="text" name="Int" id="Int" placeholder="" onkeyup="mayus(this);"/></div>
                                        </div> 
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Estado:</div>
                                            <div class="col-8">
                                                <select style="width: 400px;" name="Estado" id="Estado" required >
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
                                                <select  style="width: 400px;" name="Municipio" id="Municipio" required ></select>                                              
                                            </div>
                                        </div>                                       
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Localidad:</div>
                                            <div class="col-8">
                                                <select style="width: 400px;" name="Localidad" id="Localidad" required ></select>                                                           
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Codigo Postal:</div>
                                            <div class="col-2">
                                                <input type="number" name="CodigoPostal" id="CodigoPostal" placeholder="" required="" max="99999" onkeyup="mayus(this);"/>
                                            </div>
                                        </div>                                       
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Colonia:</div>
                                            <div class="col-8">
                                                <select name="Colonia" id="Colonia" style="width: 400px;" required ></select> 
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right"></div>
                                            <div class="col-4"><input type="submit" name="Boton2" id="Boton2" value="Actualizar"></div>
                                        </div>                                       
                                        <input type="hidden" name="busca2" id="busca2">
                                        <input type="hidden" name="IdDireccion" id="IdDireccion">
                                        <input type="hidden" name="idCliente" id="idCliente">
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
        <?php
        $objectVO1 = new DireccionVO();
        $direccionDAO = new DireccionDAO();
        $objectVO1 = $direccionDAO->retrieve($objectVO->getId(), "id_origen", " AND tabla_origen = 'O' ");
        ?>
        <script type="text/javascript">
            $(document).ready(function () {
                $("#busca").val("<?= $busca ?>");
                $("#busca2").val("<?= $busca ?>");
                $("#Id").val("<?= $busca ?>");
                $("#idCliente").val("<?= $busca ?>");
                $("#RFC").val("<?= $objectVO->getRfc_operador() ?>");
                $("#Nombre").val("<?= $objectVO->getNombre() ?>");
                $("#Licencia").val("<?= $objectVO->getNum_licencia() ?>");
                if ($("#busca").val() !== "NUEVO") {
                    $("#Boton").val("Actualizar");
                } else {
                    $("#Boton").val("Agregar");
                }
                $("#Boton").on("click", function (e) {
                    const  pattern = /^[A-ZÑ&]{3,4}[\d]{6}[A-ZÑ\d]{3}$/;
                    let rfc = $("#RFC").val().trim();
                    if (!pattern.test(rfc)) {
                        e.preventDefault();
                        $("#RFC").focus();
                        alert("El RFC [" + rfc + "] ingresado es invalido, favor de verificarlo.\nEstructura valida {ABCD}{YYMMDD}{123}");
                    }
                });
                $("#IdDireccion").val("<?= $objectVO1->getId() ?>");
                $("#Calle").val("<?= $objectVO1->getCalle() ?>");
                $("#Ext").val("<?= $objectVO1->getNum_exterior() ?>");
                $("#Int").val("<?= $objectVO1->getNum_interior() ?>");
                $("#Estado").val("<?= $objectVO1->getEstado() ?>");
                AjaxEstado("<?= $objectVO1->getEstado() ?>", "<?= $objectVO1->getMunicipio() ?>", "<?= $objectVO1->getLocalidad() ?>");
                $("#CodigoPostal").val("<?= $objectVO1->getCodigo_postal() ?>");
                AjaxCodigoPostal("<?= $objectVO1->getCodigo_postal() ?>", "<?= $objectVO1->getColonia() ?>");
                $("#Colonia").val("<?= $objectVO1->getColonia() ?>");
                $("#Calle").focus();
                if ($("#busca").val() !== "NUEVO") {
                    $("#Boton").val("Actualizar");
                } else {
                    $("#Boton").val("Agregar");
                }
                if ("<?= $objectVO1->getId() ?>" === "") {
                    $("#Boton2").val("Agregar Direccion");
                } else {
                    $("#Boton2").val("Actualizar Direccion");
                }
                $("#Estado").change(function () {
                    AjaxEstado($("#Estado").val(), "Estado", "<?= $objectVO1->getLocalidad() ?>");
                });
                $("#CodigoPostal").change(function () {
                    if ($("#CodigoPostal").val().length == 5) {
                        AjaxCodigoPostal($("#CodigoPostal").val());
                    }

                });
                $("#Municipio").val("<?= $objectVO1->getMunicipio() ?>");
                $("#Localidad").val("<?= $objectVO1->getLocalidad() ?>");
            });
            function AjaxEstado(dt, valM, valL) {
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
                        }
                        console.log("C  : " + valM + " Y " + valL);
                        $("#Municipio").val(valM);
                        $("#Localidad").val(valL);
                    },
                    error: function (jqXHR) {
                        console.log(jqXHR);
                    }
                });
            }

            function AjaxCodigoPostal(dt, val) {
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
                        $("#Colonia").val(val);
                    },
                    error: function (jqXHR) {
                        console.log(jqXHR);
                    }
                });
            }

        </script>
    </body>
</html>