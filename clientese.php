<?php
session_start();

include_once ("check.php");
include_once ('comboBoxes.php');
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ClientesService.php";

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$self = utils\HTTPUtils::self();

$Titulo = "Detalle de cliente";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

$clienteDAO = new ClientesDAO();
$ciaDAO = new CiaDAO();
$ciaVO = $ciaDAO->retrieve(1);

$clienteVO = new ClientesVO();
$clienteVO->setMunicipio(html_entity_decode($ciaVO->getCiudad()));
$clienteVO->setEstado(html_entity_decode($ciaVO->getEstado()));
$clienteVO->setEnviarcorreo("Si");
$clienteVO->setTipodepago("Contado");
$clienteVO->setFormadepago("01");
$clienteVO->setCodigo($ciaVO->getCodigo());

if (is_numeric($busca)) {
    $clienteVO = $clienteDAO->retrieve($busca);
} else {
    if ($request->hasAttribute("Rfc")) {
        $clienteVO->setRfc($request->getAttribute("Rfc"));
        $clienteByRfc = $clienteDAO->retrieve($request->getAttribute("Rfc"), "rfc", " AND id > 20 ");
        if ($clienteByRfc->getId() > 0) {
            $clienteVO = $clienteByRfc;
            utils\HTTPUtils::setSessionValue($nameVarBusca, $clienteByRfc->getId());
            $busca = utils\HTTPUtils::getSessionValue($nameVarBusca);
        }
    }
}
$nombreJSdoble = strpos($clienteVO->getNombre(), '"');

$VvlTeam = strpos($clienteVO->getNombre(), "'") !== false ? true : false;
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>

            $(document).ready(function () {
                const  pattern = /^[A-ZÑ&]{3,4}[\d]{6}[A-ZÑ\d]{3}$/;
                $("#busca").val("<?= $busca ?>");
                $("#Facturar").val("<?= $request->getAttribute("Facturar") ?>");
                var desgloseIeps = "<?= $clienteVO->getDesgloseIEPS() ?>";
                var nombreFactura = "<?= $clienteVO->getNombreFactura() ?>";
                var AutCorporativo = "<?= $clienteVO->getAutorizaCorporativo() ?>";
                $("#Rfc").val("<?= html_entity_decode($clienteVO->getRfc()) ?>");
                $("#Alias").val("<?= html_entity_decode($clienteVO->getAlias()) ?>");
                $("#Calle").val("<?= $clienteVO->getDireccion() ?>");
                $("#Numeroext").val("<?= $clienteVO->getNumeroext() ?>");
                $("#Numeroint").val("<?= $clienteVO->getNumeroint() ?>");
                $("#Colonia").val("<?= $clienteVO->getColonia() ?>");
                $("#Municipio").val("<?= $clienteVO->getMunicipio() ?>");
                $("#Estado").val("<?= $clienteVO->getEstado() ?>");
                $("#RegimenFiscal").val("<?= $clienteVO->getRegimenFiscal() ?>");
                $("#Codigo").val("<?= $clienteVO->getCodigo() ?>");
                $("#Telefono").val("<?= $clienteVO->getTelefono() ?>");
                $("#Formadepago").val("<?= $clienteVO->getFormadepago() ?>");
                $("#Cuentaban").val("<?= $clienteVO->getCuentaban() ?>");
                $("#Correo").val("<?= $clienteVO->getCorreo() ?>");
                $("#ccCorreo").val("<?= $clienteVO->getCorreo2() ?>");
                $("#Enviarcorreo").val("<?= $clienteVO->getEnviarcorreo() ?>");
                $("#Tipodepago").val("<?= $clienteVO->getTipodepago() ?>");
                $("#Limite").val("<?= $clienteVO->getLimite() ?>");
                $("#Contacto").val("<?= html_entity_decode($clienteVO->getContacto()) ?>");
                $("#Ncc").val("<?= $clienteVO->getNcc() ?>");
                $("#Puntos").val("<?= $clienteVO->getPuntos() ?>");
                $("#Activo").val("<?= $clienteVO->getActivo() ?>");
                $("#Facturacion").val("<?= $clienteVO->getFacturacion() ?>");
                $("#DiasCredito").val("<?= $clienteVO->getDiasCredito() ?>");
                $("#PuntosPor").val("<?= $clienteVO->getPuntos() ?>");
                $("#TipoFactura").val("<?= $clienteVO->getTipoMonedero() ?>");
                if ("<?= $clienteVO->getTipodepago() ?>" === "Credito") {
                    $("#ClientesCredito").show();
                } else {
                    $("#ClientesCredito").hide();
                }
                if ($("#Tipodepago").val() === "Monedero") {
                    $("#showTipoFacturaMonedero").show();
                } else {
                    $("#showTipoFacturaMonedero").hide();
                }
                $("#Tipodepago").change(function () {
                    if ($("#Tipodepago").val() === "Credito") {
                        $("#ClientesCredito").show();
                    } else {
                        $("#ClientesCredito").hide();
                    }
                    if ($("#Tipodepago").val() === "Monedero") {
                        $("#showTipoFacturaMonedero").show();
                    } else {
                        $("#showTipoFacturaMonedero").hide();
                    }
                });

                $("#FCuenta").val(1);
                $("#FAlias").val(1);
                if (AutCorporativo == 0) {
                    $("#autorizaCorporativo").prop("checked", false).val(AutCorporativo);
                } else {
                    $("#autorizaCorporativo").prop("checked", true).val(AutCorporativo);
                }
                if (desgloseIeps === "S") {
                    $("#DesgloseIEPS").prop("checked", true);
                }
                if (nombreFactura === "C" || nombreFactura === "F") {
                    $("#FCuenta").prop("checked", true);
                }
                if (nombreFactura === "A" || nombreFactura === "F") {
                    $("#FAlias").prop("checked", true);
                }

                $("#BotonRfc").click(function (e) {
                    let rfc = $("#Rfc").val().trim();
                    if (!pattern.test(rfc)) {
                        e.preventDefault();
                        $("#Rfc").focus();
                        alert("El RFC [" + rfc + "] ingresado es invalido, favor de verificarlo.\nEstructura valida {ABCD}{YYMMDD}{123}");
                    }
                });
                $("#Boton").click(function (e) {
                    let rfc = $("#Rfc").val().trim();
                    if (!pattern.test(rfc)) {
                        e.preventDefault();
                        $("#Rfc").focus();
                        alert("El RFC [" + rfc + "] ingresado es invalido, favor de verificarlo.\nEstructura valida {ABCD}{YYMMDD}{123}");
                    }
                });
                if ("<?= $busca ?>" === "NUEVO" && $("#Rfc").val().length === 13) {
                    $("#RegimenFiscal").val("612");
                } else if ("<?= $busca ?>" === "NUEVO" && $("#Rfc").val().length != 13) {
                    $("#RegimenFiscal").val("601");
                }
                $("#Rfc").focus();
                AjaxRegimenFiscal($("#Rfc").val());
                $("#Rfc").change(function () {
                    AjaxRegimenFiscal($("#Rfc").val());
                });
                $("#Nombre").change(function () {
                    var regex = /\|/;
                    if (regex.test($("#Nombre").val())) {
                        alertTextValidation("Error el nombre no puede contener '|'", "", "", "", false, "error", 1000);
                        $("#Nombre").val("");
                    }
                });
            });
            function AjaxRegimenFiscal(data) {
                jQuery.ajax({
                    type: 'GET',
                    url: 'getByAjax.php',
                    dataType: 'json',
                    cache: false,
                    data: {"Var": data, "Origen": "GetRegimenFiscales"},
                    beforeSend: function (xhr) {
                        $('#RegimenFiscal').empty();
                    },
                    success: function (data) {
                        for (var dt of data)
                        {
                            $('#RegimenFiscal').append($('<option>', {
                                value: dt["clave"],
                                text: dt["clave"] + ".- " + dt["descripcion"]
                            }));
                            $('#RegimenFiscal').val("<?= $clienteVO->getRegimenFiscal() ?>");
                        }
                    },
                    error: function (jqXHR) {
                        console.log(jqXHR);
                    }
                });
            }
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?> 

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="clientes.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">
                    <?php
                    if (!is_numeric($busca) && empty($clienteVO->getRfc())) {
                        ?>
                        <div id="FormulariosBoots">
                            <div class="container no-margin">
                                <div class="row no-padding">
                                    <div class="col-10 background container no-margin">
                                        <form name="formulario1" id="formulario1" method="post" action="">
                                            <div class="row no-padding">
                                                <div class="col-11 align-right mensajeInput">
                                                    (<sup><i style="color: red;font-size: 8px;" class="fa fa-lg fa-asterisk" aria-hidden="true"></i></sup>) 
                                                    <strong> Campos necesarios para la facturación 4.0</strong>
                                                </div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right required">R.F.C: </div>
                                                <div class="col-3">
                                                    <input type="text" name="Rfc" id="Rfc" maxlength="15" class="clase-<?= $clase1 ?>" required/>
                                                </div>
                                                <div class="col-4 mensajeInput">Sin guiones ni espacios en blanco</div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-5 align-right mensajeInput">
                                                    <?php crearBoton("BotonRfc"); ?>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    } else {
                        ?>
                        <div id="FormulariosBoots">
                            <div class="container no-margin">
                                <div class="row no-padding">
                                    <div class="col-12 background container no-margin">
                                        <form name="formulario2" id="formulario2" method="post" action="">
                                            <div class="row no-padding">
                                                <div class="col-11 align-right mensajeInput">
                                                    (<sup><i style="color: red;font-size: 8px;" class="fa fa-lg fa-asterisk" aria-hidden="true"></i></sup>) 
                                                    <strong> Campos necesarios para la facturación 4.0</strong>
                                                </div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right required">R.F.C: </div>
                                                <div class="col-3">
                                                    <input type="text" name="Rfc" id="Rfc" maxlength="40" class="clase-<?= $clase2 ?>" required/>
                                                </div>
                                                <div class="col-4 mensajeInput">Sin guiones ni espacios en blanco</div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right required">Nombre: </div>
                                                <div class="col-5">
                                                    <?php
                                                    if ($VvlTeam) {
                                                        ?>
                                                        <input type="text" name="Nombre" id="Nombre" maxlength="300" class="clase-<?= $clase2 ?>" value="<?= $clienteVO->getNombre() ?>" required/>
                                                        <?php
                                                    } else {
                                                        ?>
                                                        <input type='text' name='Nombre' id='Nombre' maxlength='300' class='clase-<?= $clase2 ?>' value='<?= $clienteVO->getNombre() ?>' required/>
                                                        <?php
                                                    }
                                                    ?>
                                                </div>
                                                <div class="col-4 mensajeInput">Num. Cliente: <strong><?= $busca ?></strong></div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right">Alias: </div>
                                                <div class="col-5">
                                                    <input type="text" name="Alias" id="Alias" maxlength="20" class="clase-<?= $clase2 ?>"/>
                                                </div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right">Calle: </div>
                                                <div class="col-5">
                                                    <input type="text" name="Calle" id="Calle" maxlength="40" class="clase-<?= $clase2 ?>"/>
                                                </div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right">No. exterior: </div>
                                                <div class="col-1"><input type="text" name="Numeroext" id="Numeroext" class="clase-<?= $clase2 ?>" /></div>
                                                <div class="col-3 align-right">No. interior: </div>
                                                <div class="col-1"><input type="text" name="Numeroint" id="Numeroint" class="clase-<?= $clase2 ?>"/></div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right">Colonia: </div>
                                                <div class="col-5">
                                                    <input type="text" name="Colonia" id="Colonia" maxlength="40" class="clase-<?= $clase2 ?>"/>
                                                </div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right">Municipio: </div>
                                                <div class="col-5">
                                                    <input type="text" name="Municipio" id="Municipio" maxlength="40" class="clase-<?= $clase2 ?>"/>
                                                </div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right">Estado: </div>
                                                <div class="col-5">
                                                    <input type="text" name="Estado" id="Estado" maxlength="40" class="clase-<?= $clase2 ?>"/>
                                                </div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right">Telefono: </div>
                                                <div class="col-3"><input type="text" name="Telefono" id="Telefono" placeholder="(01) 555-5555-555" class="clase-<?= $clase2 ?>" /></div>
                                                <div class="col-1 align-right required">C.P: </div>
                                                <div class="col-1"><input type="text" name="Codigo"  id="Codigo" class="clase-<?= $clase2 ?>" required="required"/></div>
                                                <div class="col-2" id="TextoErrorCP"></div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right required">Regimen Fiscal: </div>
                                                <div class="col-5">
                                                    <select name="RegimenFiscal" id="RegimenFiscal" class="clase-<?= $clase ?>" required>
                                                        <option value=""/>Selecciona Regimen Fiscal</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right required">Forma de pago: </div>
                                                <div class="col-5">
                                                    <select name="Formadepago" id="Formadepago" class="clase-<?= $clase ?>">
                                                        <?php
                                                        $arrayDatos = CatalogosSelectores::getFormasDePago();
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
                                                <div class="col-3 align-right">4 ultimos digitos de la cuenta: </div>
                                                <div class="col-2">
                                                    <input type="text" name="Cuentaban" id="Cuentaban" maxlength="40" class="clase-<?= $clase2 ?>" placeholder="****1234"/>
                                                </div>
                                                <div class="col-4 mensajeInput"> Solo aplica para pagos con transferencia</div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right">Correo electrónico: </div>
                                                <div class="col-3"><input type="text" name="Correo" id="Correo" class="clase-<?= $clase2 ?>"/></div>
                                                <div class="col-1 align-right">Enviar: </div>
                                                <div class="col-1">
                                                    <select id="Enviarcorreo" name="Enviarcorreo">
                                                        <option value="Si">Si</option>
                                                        <option value="No">No</option>
                                                    </select>
                                                </div>
                                                <div class="col-4 mensajeInput">Enviar automáticamente</div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right">Correo CC: </div>
                                                <div class="col-5">
                                                    <input type="text" name="ccCorreo" id="ccCorreo" maxlength="160" class="clase-<?= $clase2 ?>" placeholder="Correos separados con [;] Maximo 3"/>
                                                </div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right">Desglose IEPS: </div>
                                                <div class="col-3">
                                                    <input type="checkbox" name="DesgloseIEPS" class="botonAnimatedMin" id="DesgloseIEPS" value="S"/>
                                                    Desglose de IEPS en facturación</div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right">Datos del Receptor en Facturas: </div>
                                                <div class="col-3 "><input type="checkbox" name="FCuenta" id="FCuenta" value="1" class="botonAnimatedMin"/> Incluir número de cuenta</div>
                                                <div class="col-3 "><input type="checkbox" name="FAlias" id="FAlias" value="1" class="botonAnimatedMin"/> Incluir Alias</div>
                                            </div>
                                            <?php
                                            if ($usuarioSesion->getTeam() !== PerfilesUsuarios::FACTURACION) {
                                                ?>
                                                <div class="row no-padding">
                                                    <div  class='col-3'></div>
                                                    <div  class='col-6 align-center subtitulos' style="height: 25px;background-color: #CCD1D1;line-height: 25px;border-radius: 15px;">
                                                        DATOS AJENOS A LA FACTURACION
                                                    </div>
                                                    <div  class='col-3'></div>
                                                </div>
                                                <div class="row no-padding">
                                                    <div class="col-3 align-right required">Tipo de cliente: </div>
                                                    <div class="col-5">
                                                        <select name="Tipodepago" id="Tipodepago" class="clase-<?= $clase ?>">
                                                            <?php
                                                            $arrayDatos = CatalogosSelectores::getTipos_Cliente();
                                                            foreach ($arrayDatos as $key => $value) {
                                                                ?>
                                                                <option value="<?= $key ?>"/><?= $value ?></option>
                                                                <?php
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-2 mensajeInput">Puntos: <?= $clienteVO->getPuntos(); ?></div>
                                                </div>
                                                <div class="row no-padding" id="ClientesCredito">
                                                    <div class="col-3 align-right">Días de credito: </div>
                                                    <div class="col-1">
                                                        <input type="number" name="DiasCredito" id="DiasCredito">
                                                    </div>
                                                </div>
                                                <div class="row no-padding">
                                                    <div class="col-3 align-right">Limite de Credito: </div>
                                                    <div class="col-5">
                                                        <input type="text" name="Limite" id="Limite" maxlength="40" class="clase-<?= $clase2 ?>" />
                                                    </div>
                                                </div>
                                                <div class="row no-padding">
                                                    <div class="col-3 align-right">Contacto: </div>
                                                    <div class="col-5">
                                                        <input type="text" name="Contacto" id="Contacto" maxlength="40" class="clase-<?= $clase2 ?>" />
                                                    </div>
                                                </div>
                                                <div class="row no-padding">
                                                    <div class="col-3 align-right">No. Cuenta Contable: </div>
                                                    <div class="col-5">
                                                        <input type="text" name="Ncc" id="Ncc" maxlength="40" class="clase-<?= $clase2 ?>" />
                                                    </div>
                                                </div>
                                                <div class="row no-padding" id="showTipoFacturaMonedero">
                                                    <div class="col-3 align-right">Tipo de Factura XML: </div>
                                                    <div class="col-2">
                                                        <select id="TipoFactura" name="TipoFactura">
                                                            <option value="<?= tipoFacturaMonedero::NORMAL ?>">Normal</option>
                                                            <option value="<?= tipoFacturaMonedero::MONTONETO ?>">Monto Neto</option>
                                                            <option value="<?= tipoFacturaMonedero::EFECTIVALE ?>">Efectivale</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row no-padding">
                                                    <div class="col-3 align-right">Autorización de corporativo: </div>
                                                    <div class="col-1">
                                                        <input type="text" name="autorizaCorporativo" id="autorizaCorporativo" maxlength="40" class="clase-<?= $clase2 ?>" />
                                                    </div>
                                                    <div class="col-8 mensajeInput">Bandera que indica si la validación del saldo es local o en corporativo</div>
                                                </div>
                                                <div class="row no-padding">
                                                    <div class="col-3 align-right required">Status del cliente: </div>
                                                    <div class="col-1">
                                                        <select id="Activo" name="Activo">
                                                            <option value="Si">Si</option>
                                                            <option value="No">No</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-2 mensajeInput" >Activar en reportes</div>
                                                </div>
                                                <div class="row no-padding">
                                                    <div class="col-3 align-right required">Facturación: </div>
                                                    <div class="col-1">
                                                        <?php
                                                        if ($usuarioSesion->getTeam() === PerfilesUsuarios::ADMINISTRADOR) {
                                                            ?>
                                                            <select name="Facturacion" id="Facturacion" class="clase-<?= $clase ?>">
                                                                <?php
                                                                $arrayDatos = ListasCatalogo::getArrayList("Activo");
                                                                foreach ($arrayDatos as $key => $value) {
                                                                    ?>
                                                                    <option value="<?= $key ?>"/><?= $value ?></option>
                                                                    <?php
                                                                }
                                                                ?>
                                                            </select>
                                                            <?php
                                                        } else {
                                                            crearInputHidden("Facturacion");
                                                        }
                                                        ?>
                                                    </div>
                                                    <div class="col-6 mensajeInput" >Indica si se le pueden realizar facturas</div>
                                                </div>
                                                <div class="row no-padding">
                                                    <div class="col-3 align-right">Multiplica puntos por: </div>
                                                    <div class="col-1"> <input type="number" name="PuntosPor" id="PuntosPor"></div>
                                                </div>
                                                <div class="row no-padding">
                                                    <div class="col-3 align-right"> Ultima Modificacion:</div>
                                                    <div class="col-3"><?= $clienteVO->getUlitmaModificacion() ?></div>
                                                </div>
                                                <div class="row no-padding">
                                                    <div  class='col-3'></div>
                                                    <div  class='col-6 align-center subtitulos' style="height: 25px;background-color: #CCD1D1;line-height: 25px;border-radius: 15px;">
                                                        DATOS DE ACCESO
                                                    </div>
                                                    <div  class='col-3'></div>
                                                </div>
                                                <?php
                                                $ExistAuth = "SELECT id,uname,lastactivity FROM authuser WHERE uname = " . $clienteVO->getId() . " AND team ='Cliente'";
                                                $rsEx = utils\IConnection::execSql($ExistAuth);
                                                if ($usuarioSesion->getLevel() >= 7 && is_numeric($busca)) {
                                                    if ($rsEx["id"] > 0) {
                                                        ?>

                                                        <div class="row no-padding" title="En caso de querer cambiar la contraseña favor de contactase con soporte">
                                                            <div class="col-3 align-right">Usr :</div>
                                                            <div class="col-1"><?= $rsEx["uname"] ?></div>
                                                            <div class="col-2 align-right">Ultimo acceso</div>
                                                            <div class="col-3"><?= $rsEx["lastactivity"] ?></div>
                                                        </div>
                                                        <?php
                                                    }
                                                    if (($clienteVO->getTipodepago() === TiposCliente::CREDITO || $clienteVO->getTipodepago() === TiposCliente::PREPAGO) && !($rsEx["id"] > 0)) {
                                                        ?>
                                                        <div class="row no-padding">
                                                            <div class="col-3 align-right">Dar acceso a Sistema: </div>
                                                            <div class="col-3">
                                                                <input type="text" name="Password" id="Password" placeholder="******" class="clase-<?= $clase2 ?>" />
                                                            </div>
                                                            <div class="col-1 mensajeInput">
                                                                <input type='submit' name='Boton' value='Dar acceso al sistema'><br/>
                                                            </div>
                                                        </div>
                                                        <div class="row no-padding mensajeInput">
                                                            <div class="col-12 align-center">En caso de requerir  que este cliente entre al sistema</div>
                                                        </div>
                                                        <?php
                                                    }
                                                }
                                            } else {
                                                crearInputHidden("Tipodepago");
                                                crearInputHidden("Limite");
                                                crearInputHidden("Contacto");
                                                crearInputHidden("Ncc");
                                                crearInputHidden("Activo");
                                                crearInputHidden("Facturacion");
                                            }
                                            ?>
                                            <div class="row no-padding mensajeInput" id="OcultaInput">
                                                <div class="col-10 align-center">
                                                    <?php
                                                    crearInputHidden("Puntos");
                                                    crearBoton("Boton", is_numeric($busca) ? $request->hasAttribute("Facturar") ? "Facturar" : "Actualizar" : "Agregar", is_numeric($busca) ? "Agregar como nuevo cliente" : "");
                                                    crearInputHidden("Facturar");
                                                    crearInputHidden("busca");
                                                    ?>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <?php if ($usuarioSesion->getTeam() === "Administrador") { ?>
                                        <div class="col-12 background container no-margin" style="margin-top: 15px;">
                                            <div style="width: 100%; text-align: center;"><h3>USUARIOS CON ACCESO A LA INFORMACIÓN</h3></div>
                                            <form name="formulario2" id="formulario2" method="post" action="">
                                                <div class="row no-padding">
                                                    <div class="col-12">
                                                        <table style="width: 80%;margin-left: 10%;border-radius: 10px;">
                                                            <tr style="background-color: rgb(0, 153, 153);color:white;font-size: 14px;font-weight: bold;">
                                                                <td style="padding-left: 5px;">Id</td>
                                                                <td style="padding-left: 5px;">Nombre</td>
                                                                <td style="padding-left: 5px;">Usuario</td>
                                                                <td style="padding-left: 5px;">Ultimo acceso</td>
                                                                <td style="padding-left: 5px;"></td>
                                                                <td style="padding-left: 5px;">Status</td>
                                                                <td style="padding-left: 5px;">Edita</td>
                                                            </tr>
                                                            <?php
                                                            $SqlClis = "SELECT * FROM authuser WHERE name LIKE '%" . $busca . "%' UNION ALL "
                                                                    . "SELECT * FROM authuser WHERE uname = '$busca'";
                                                            $Clis = utils\IConnection::getRowsFromQuery($SqlClis);
                                                            $e = 0;
                                                            foreach ($Clis as $cli) {
                                                                $HtmlColor = $e % 2 == 0 ? "#F2FFFC" : "#A2D9CE";
                                                                $TCheck = $cli["status"] === "active" ? "checked" : "";
                                                                $TCheck1 = $cli["receive_msg"] == 1 ? "checked" : "";
                                                                ?>
                                                                <tr style="background-color: <?= $HtmlColor ?>">
                                                                    <td style="padding-left: 5px;"><?= $cli["id"] ?></td>
                                                                    <td style="padding-left: 5px;"><?= $cli["name"] ?></td>
                                                                    <td style="padding-left: 5px;"><?= $cli["uname"] ?></td>
                                                                    <td style="padding-left: 5px;"><?= $cli["lastactivity"] ?></td>
                                                                    <td style="padding-left: 5px;"><?= $cli["status"] ?></td>
                                                                    <td style="padding-left: 5px;"><input type="checkbox" class="botonAnimatedGreen" <?= $TCheck ?> data-idauth='<?= $cli["id"] ?>' data-status='<?= $cli["status"] ?>' data-origen="status"></td>
                                                                    <td style="padding-left: 5px;"><input type="checkbox" class="botonAnimatedGreen" <?= $TCheck1 ?> data-idauth='<?= $cli["id"] ?>' data-status='<?= $cli["receive_msg"] ?>' data-origen="modifica"></td>
                                                                </tr>
                                                                <?php
                                                                $e++;
                                                            }
                                                            ?>
                                                        </table>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <?php
                            $ciaVO = $ciaDAO->retrieve("true");
                            if ($ciaVO->getClave_instalacion() === "TRA") {
                                $objectVO = new DireccionVO();
                                $direccionDAO = new DireccionDAO();
                                if (is_numeric($busca)) {
                                    $objectVO = $direccionDAO->retrieve($busca, "id_origen", " AND tabla_origen = 'C'");
                                }
                                ?>
                                <script>
                                    $(document).ready(function () {
                                        $("#EstadoCP").val("<?= $objectVO->getEstado() ?>");
                                        $("#MunicipioCP").val("<?= $objectVO->getMunicipio() ?>");
                                        $("#LocalidadCP").val("<?= $objectVO->getLocalidad() ?>");
                                        $("#ColoniaCP").val("<?= $objectVO->getColonia() ?>");
                                        AjaxEstado("<?= $objectVO->getEstado() ?>", "<?= $objectVO->getMunicipio() ?>", "<?= $objectVO->getLocalidad() ?>");
                                        AjaxCodigoPostal("<?= $objectVO->getCodigo_postal() ?>", "<?= $objectVO->getColonia() ?>");
                                    });
                                </script>
                                <div class="container no-margin" style="margin-top: 15px;">
                                    <div class="row no-padding">
                                        <div class="col-12 background container no-margin">
                                            <form name="formulario3" id="formulario3" method="post" action="">
                                                <input type="hidden"  name="busca" value="<?= $busca ?>">
                                                <div class="row no-padding">
                                                    <div class="col-11 align-right mensajeInput">
                                                        (<sup><i style="color: red;font-size: 8px;" class="fa fa-lg fa-asterisk" aria-hidden="true"></i></sup>) 
                                                        <strong> Campos necesarios para la Carta Porte 2.0</strong>
                                                    </div>
                                                </div>
                                                <div class="row no-padding">
                                                    <div class="col-3 align-right required">Descripcion:</div>
                                                    <div class="col-6"><input type="text" name="DescripcionCP" id="DescripcionCP" placeholder="" value="<?= $objectVO->getDescripcion() ?>" ></div>
                                                </div>
                                                <div class="row no-padding">
                                                    <div class="col-3 align-right required">Calle:</div>
                                                    <div class="col-6"><input type="text" name="CalleCP" id="CalleCP" placeholder="" required="" value="<?= $objectVO->getCalle() ?>" onkeyup="mayus(this);"/></div>
                                                </div>
                                                <div class="row no-padding">
                                                    <div class="col-3 align-right required">Numero Ext.:</div>
                                                    <div class="col-6"><input type="text" name="ExtCP" id="ExtCP" placeholder="" required="" value="<?= $objectVO->getNum_exterior() ?>"/></div>

                                                </div>
                                                <div class="row no-padding">
                                                    <div class="col-3 align-right">Numero Int:</div>
                                                    <div class="col-6"><input type="text" name="IntCP" id="IntCP" placeholder="" value="<?= $objectVO->getNum_interior() ?>"/></div>
                                                </div> 
                                                <div class="row no-padding">
                                                    <div class="col-3 align-right required">Estado:</div>
                                                    <div class="col-6">
                                                        <select name="EstadoCP" id="EstadoCP" required >
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
                                                    <div class="col-3 align-right required" > Municipio:</div>
                                                    <div class="col-6">
                                                        <select name="MunicipioCP" id="MunicipioCP" required></select>                                              
                                                    </div>
                                                </div>                                       
                                                <div class="row no-padding">
                                                    <div class="col-3 align-right required">Localidad:</div>
                                                    <div class="col-6">
                                                        <select name="LocalidadCP" id="LocalidadCP" required ></select>                                                           
                                                    </div>
                                                </div>
                                                <div class="row no-padding">
                                                    <div class="col-3 align-right required">Codigo Postal:</div>
                                                    <div class="col-6">
                                                        <input type="text" name="CodigoPostalCP" id="CodigoPostalCP" placeholder="" required="" value="<?= $objectVO->getCodigo_postal() ?>"/>
                                                    </div>
                                                </div>                                       
                                                <div class="row no-padding">
                                                    <div class="col-3 align-right required">Colonia:</div>
                                                    <div class="col-6">
                                                        <select name="ColoniaCP" id="ColoniaCP" required ></select> 
                                                    </div>
                                                </div>                             
                                                <div class="row no-padding">
                                                    <div class="col-3 align-right "></div>
                                                    <div class="col-3">
                                                        <input type="submit" name="Boton2" value="Actualizar">
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                        <?php
                    }
                    ?>
                </td>
            </tr>
        </table>

        <?php BordeSuperiorCerrar(); ?>
        <?php PieDePagina(); ?>
        <script type="text/javascript">
            $(document).ready(function () {
                var expreg = /^([0-9]{5})$/;
                $("#TextoErrorCP").html("Formato C.P. (5 digitos)").css("color", "red");
                $("#Codigo").change(function () {
                    if (!expreg.test($("#Codigo").val())) {
                        $("#Codigo").css("background-color", '#F5B7B1');
                        $("#TextoErrorCP").show();
                        $("#OcultaInput").hide();
                    } else {
                        $("#Codigo").css("background-color", '#FDFEFE');
                        $("#TextoErrorCP").hide();
                        $("#OcultaInput").show();
                    }
                });
                if (!expreg.test($("#Codigo").val())) {
                    $("#Codigo").css("background-color", '#F5B7B1');
                } else {
                    $("#TextoErrorCP").hide();
                }
                if ("<?= $busca ?>" == "NUEVO") {
                    $("#Codigo").val("");
                }

                $("#EstadoCP").change(function () {
                    AjaxEstado($("#EstadoCP").val(), "EstadoCP");
                });
                $("#CodigoPostalCP").change(function () {
                    if ($("#CodigoPostalCP").val().length == 5) {
                        AjaxCodigoPostal($("#CodigoPostalCP").val());
                    }

                });

                $(".botonAnimatedGreen").click(function () {
                    var idUser = this.dataset.idauth;
                    var status = this.dataset.status;
                    var origen = this.dataset.origen;
                    Getorigen = origen === "status" ? "ActualizaStatusAuthCli" : "GeneraMovimiento";
                    jQuery.ajax({
                        type: 'GET',
                        url: 'getByAjax.php',
                        dataType: 'json',
                        cache: false,
                        data: {"Op": Getorigen, "idAuth": idUser, "Status": status},
                        success: function (data) {
                            location.reload();
                        },
                        error: function (jqXHR) {
                            console.log(jqXHR);
                        }
                    });
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
                        $('#LocalidadCP').empty();
                        $('#MunicipioCP').empty();
                    },
                    success: function (data) {
                        for (var dt of data)
                        {
                            for (var d of dt) {
                                if (typeof d["localidad"] != "string") {

                                    $('#MunicipioCP').append($('<option>', {
                                        value: d["clave"],
                                        text: d["clave"] + ".- " + d["descripcion"]
                                    }));
                                } else {
                                    $('#LocalidadCP').append($('<option>', {
                                        value: d["id"],
                                        text: d["localidad"] + ".- " + d["descripcion"]
                                    }));
                                }
                            }
                            $("#MunicipioCP").val(val1);
                            $("#LocalidadCP").val(val2);
                        }
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
                            $('#ColoniaCP').append($('<option>', {
                                value: dt["colonia"],
                                text: dt["codigo_postal"] + ".- " + dt["nombre"]
                            }));
                        }
                        $("#ColoniaCP").val(val1);
                    },
                    error: function (jqXHR) {
                        console.log(jqXHR);
                    }
                });
            }
        </script>
    </body>
</html>

