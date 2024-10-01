<?php
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("comboBoxes.php");
include_once ('data/FcDAO.php');

use com\softcoatl\utils as utils;

$Titulo = "Favor de confirmar sus datos";
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

if (utils\HTTPUtils::getRequest()->getAttribute("FechaG") !== "") {
    $fcVO = new FcVO();
    $fcDAO = new FcDAO();
    $fcVO = $fcDAO->retrieve(utils\HTTPUtils::getSessionBiValue("CatalogoFacturasDetalle", "cVarVal"));
    $fcVO->setFecha(utils\HTTPUtils::getRequest()->getAttribute("FechaG"));
    $fcDAO->update($fcVO);
}

require './services/FacturasdService.php';

$pacDAO = new ProveedorPACDAO();
$ppac = $pacDAO->getActive();

$fcVO = new FcVO();
$clienteVO = new ClientesVO();
if (is_numeric($cVarVal)) {
    $fcVO = $fcDAO->retrieve($cVarVal);
    $clienteVO = $clientesDAO->retrieve($fcVO->getCliente());
    if ($clienteVO->getTipodepago() === TiposCliente::MONEDERO) {
        
    }
}
$Cia = "SELECT codigo FROM cia;";
$Cp = utils\IConnection::execSql($Cia);

$VvlTeam = strpos($clienteVO->getNombre(), "'") !== false ? true : false;
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom.php"; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("input[name=Boton2]").click(function () {
                    var generico = 0;
                    if ($("#Metododepago").val() !== '' && $("#Formadepago").val() !== '' && $("#RegimenFiscal").val() !== '' && $("#CodigoPostal").val() !== '' && $("#cuso").val() !== "") {
                        if ($("#rfcGenerico").is(':checked')) {
                            generico = 1;
                        }
                        var locationDir = "genfactura331.php?Boton=" + $(this).val() + "&rfcGenerico=" + generico + "&CambioCantidad=" + $("#CambioCantidad").val();
                        console.log(locationDir);
                        jQuery.ajax({
                            type: 'GET',
                            url: 'getByAjax.php',
                            dataType: 'json',
                            cache: false,
                            data: {"Id": <?= $fcVO->getId() ?>, "Op": "ValidaFactura"},
                            success: function (data) {
                                if (data.Pass == 1) {
                                    $(location).attr('href', locationDir);
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        iconColor: '#F1948A',
                                        title: 'La factura ya fue timbrada'
                                    }).then((result) => {
                                        $(location).attr('href', "facturas.php");
                                    });
                                }
                            },
                            error: function (jqXHR) {
                                console.log(jqXHR);
                            }
                        });
                    } else {
                        alert("Faltan ingresar datos, favor de verificar");
                    }
                });
                $("body").on("shown.bs.modal", "#modal-de-carga", function (e) { });
                $("#ViewGenerico").hide();
                if ('<?= utils\HTTPUtils::getSessionValue("cGeneric") ?>' == '1') {
                    $("#rfcGenerico").attr("checked", true);
                    $("#ViewGenerico").show();
                }
                if ("<?= utils\HTTPUtils::getSessionValue("cGenericPerso") ?>" == "1") {
                    $("#rfcGenericoPersonal").attr("checked", true);
                }
                $("#rfcGenerico").click(function () {
                    if (!$(this).prop("checked")) {
                        $("#ViewGenerico").hide();
                    } else {
                        $("#ViewGenerico").show();
                    }
                });
                $("#rfcGenericoPersonal").click(function () {
                    if ($(this).prop("checked")) {
                        Swal.fire({
                            icon: 'question',
                            iconColor: '#EC7063',
                            title: 'La factura sera emitida con estos conceptos.',
                            showCancelButton: true,
                            cancelButtonColor: "#d33",
                            confirmButtonText: "Aceptar",
                            html: "Razon social : " + $("#Nombre").val() + "<br>RFC : XAXX010101000<br> Uso CFDI: S01 - Sin efectos fiscales<br> Regimen F.: 616.- Sin obligaciones fiscales <br> CP: <?= $Cp[codigo] ?>",
                            background: "#D6EAF8"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $("#rfcGenericoPersonal").prop("checked", true);
                            } else {
                                $("#rfcGenericoPersonal").prop("checked", false);
                            }
                        });
                    }
                });
                $("#Formadepago").attr("name", "Formadepago").attr("required", "true").val("<?= $fcVO->getFormadepago() ?>");
                $("#Metododepago").attr("name", "Metododepago").attr("required", "true").val("<?= $fcVO->getMetododepago() ?>");
                $("#Relacioncfdi").attr("name", "Relacioncfdi").val("<?= $fcVO->getRelacioncfdi() ?>");
                $("#FolioRelacionado").attr("name", "FolioRelacionado").val("<?= $fcVO->getRelacionfolio() ?>");
                $("#tiporelacion").attr("name", "tiporelacion").val("<?= $fcVO->getTiporelacion() ?>");
                $("#TipoRelacion").val("<?= $fcVO->getDocumentoRelacion() ?>");
                $("#FechaG").val("<?= $fcVO->getFecha() ?>");
                $("#CodigoPostal").val("<?= $clienteVO->getCodigo() ?>");
                $("#Meses").val("<?= $fcVO->getMeses() ?>");
                $("#Periodo_sat").val("<?= $fcVO->getPeriodo() ?>");
                $("#Anio").val("<?= $fcVO->getAno() ?>");
                var nivel = "<?= $usuarioSesion->getLevel() ?>";
                if (nivel >= 7) {
                    $("#cuso").val("<?= $fcVO->getUsocfdi() ?>").attr("required", "true");
                }
                $("#Relacioncfdi").change(function () {
                    if ($(this).val === "") {
                        $("#tiporelacion").attr("required", false);
                    } else {
                        $("#tiporelacion").attr("required", true);
                    }
                });
                $("#CambioCantidad").val("EnFalse");
                AjaxRegimenFiscal("<?= $clienteVO->getRfc() ?>");
                $("#TipoCantidad").click(function () {
                    var tipo = false;
                    if ($("#TipoCantidad").prop("checked")) {
                        $("#CambioCantidad").val("EnTrue");
                    } else {
                        $("#CambioCantidad").val("EnFalse");
                    }
                });

                $(".TipoRelacion").change(function () {
                    var valor = $(this).val();
                    var idRegistro = this.dataset.idrg;
                    Swal.fire({
                        icon: 'question',
                        iconColor: '#EC7063',
                        title: 'Seguro de modificar el tipo de relación de la factura.',
                        showCancelButton: true,
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Aceptar",
                        background: "#D6EAF8"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            jQuery.ajax({
                                type: 'GET',
                                url: 'getByAjax.php',
                                dataType: 'json',
                                cache: false,
                                data: {"Op": "ActualizaRelacionMultiple", "IdRegistro": idRegistro, "TipoRelacion": valor},
                                success: function (data) {
                                    location.reload();
                                },
                                error: function (jqXHR) {
                                    console.log(jqXHR);
                                }
                            });
                        }
                    });
                });

                setInterval(function () {
                    jQuery.ajax({
                        type: 'GET',
                        url: 'getByAjax.php',
                        dataType: 'json',
                        cache: false,
                        data: {"Op": "RevisaIngresoRegistro", "IdFc": "<?= $fcVO->getId() ?>", "Cnt": $("#TipoRelacionCnt").val()},
                        success: function (data) {
                            if (data.Value) {
                                location.reload();
                            }
                        },
                        error: function (jqXHR) {
                            console.log(jqXHR);
                        }
                    });
                }, 2000)

            });

            function openRelationshipWindow() {
                window.open("catfc1.php?criteria=ini&Cliente=<?= $fcVO->getCliente() ?>", "_blank", "width=1070,height=420,resizable=no,scrollbars=no");
            }

            function openComplementosWindow() {
                var comp = $("#complemento").val();
                window.open("complementos.php?id=" +<?= $cVarVal ?> + "&complemento=" + comp, "_blank", "width=1070,height=420,resizable=no,scrollbars=no");
            }

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

        <?php
        BordeSuperior();
        ?>

        <?php if ($ppac->getPruebas() === "1") { ?>
            <div style="background-color: red; color: white; text-align:center; font-family: Helvetica, Arial, Verdana, Tahoma, sans-serif; font-size:14px; font-weight:bold;">
                ALERTA FACTURANDO EN MODO DE DEMOSTRACIÓN
            </div>
        <?php } ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="facturasd.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">
                    <form name="form1" id="form1" method="post" action="">
                        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
                            <input type="hidden" name="Relacioncfdi" id="Relacioncfdi"/>

                            <?php
                            cInput("(&nbsp;<span style='color: red;'><strong>*&nbsp;</strong></span>) ", "Text", "10", "", "right", "<strong> Campos obligatorios para timbrar su factura</strong>", "15", false, true, "");

                            cInput("Folio :", "Text", "5", "Id", "right", $fcVO->getFolio(), "40", false, true, "");

                            cInput("<span style='color: red;'><strong>*&nbsp;</strong></span>R.f.c.:", "Text", "15", "Rfc", "right", $clienteVO->getRfc(), "15", true, false, '', " readonly='readonly'  disabled");
                            if ($VvlTeam) {
                                cInputcc("<span style='color: red;'><strong>*&nbsp;</strong></span>Nombre: ", "Text", "50", "Nombre", "right", $clienteVO->getNombre(), "200", true, false, " <span  class='nombre_cliente'> &nbsp;&nbsp; Num. Cliente: </span><strong>" . $clienteVO->getId() . "</strong>", " readonly='readonly'");
                            } else {
                                cInput("<span style='color: red;'><strong>*&nbsp;</strong></span>Nombre: ", "Text", "50", "Nombre", "right", $clienteVO->getNombre(), "200", true, false, " <span  class='nombre_cliente'> &nbsp;&nbsp; Num. Cliente: </span><strong>" . $clienteVO->getId() . "</strong>", " readonly='readonly'");
                            }
                            ?>

                            <?php if ($usuarioSesion->getLevel() >= 7) { ?>
                                <tr>
                                    <td align="right" bgcolor="#e1e1e1" class="nombre_cliente">Facturar al RFC Genérico PG: &nbsp;</td>
                                    <td align="left">&nbsp;<input type="checkbox" id="rfcGenerico" name="rfcGenerico" value="1" <?= $request->getAttribute("rfcGenerico") == "1" ? "checked" : "" ?>><small> Generar el CFDI para un éste cliente usando el RFC genérico. Sólo en caso de ser necesario</small></td>
                                </tr>
                                <tr>
                                    <td align="right" bgcolor="#e1e1e1" class="nombre_cliente">Facturar al RFC Genérico: &nbsp;<?= $request->getAttribute("rfcGenericoPersonal") ?></td>
                                    <td align="left">&nbsp;<input type="checkbox" id="rfcGenericoPersonal" name="rfcGenericoPersonal" value="1" <?= $request->getAttribute("rfcGenericoPersonal") == "1" ? "checked" : "" ?>>
                                        <small>Generar el CFD con puro RFC generico</small>
                                    </td>
                                </tr>
                                <tr id="ViewGenerico">
                                    <td></td>
                                    <td>
                                        Año: <input style="width: 60px;margin-left: 5px;" type="text" name="Anio" value="<?= date("Y") ?>" class="texto_tablas">
                                        Mes: <?= ListasCatalogo::getDataMeses("Meses", "Seleccionar mes", "class='texto_tablas'") ?>
                                        Periodo: <?= ListasCatalogo::getDataPeriodicidad("Periodo_sat", "Selecciona periodo", "class='texto_tablas'") ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" bgcolor="#e1e1e1" class="nombre_cliente">Uso de CFDI: &nbsp;</td>
                                    <td align="left">&nbsp;<?= ComboboxUsoCFDI::generateByTypeCli("cuso", strlen($clienteVO->getRfc())); ?></td>
                                </tr>
                            <?php } ?>
                            <tr>
                                <td align="right" bgcolor="#e1e1e1" class="nombre_cliente">Fecha : &nbsp;</td>
                                <td align="left">
                                    &nbsp;<input type="text" class="texto_tablas" id="FechaG" name="FechaG"> 
                                    C.P. <input type="number" class="texto_tablas" min="1000" max="99999" id="CodigoPostal" name="CodigoPostal">
                                </td>
                            </tr>
                            <tr>
                                <td align="right" bgcolor="#e1e1e1" class="nombre_cliente" >Regimen F.</td>
                                <td align="left">
                                    &nbsp;<select name="RegimenFiscal"  class="texto_tablas" id="RegimenFiscal">
                                        <option value=""/>Selecciona Regimen Fiscal</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td align="right" bgcolor="#e1e1e1" class="nombre_cliente">CFDI relacionado: &nbsp;</td>
                                <td align="left">&nbsp;<input type="text" id="FolioRelacionado" class="texto_tablas" size="10" disabled="disabled"  style="display: inline"/> &nbsp;<div id="TipoR"  style="display: inline"></div>
                                    <input type="hidden" name="TipoRelacion" id="TipoRelacion" style="display: inline">
                                    &nbsp;<a class="textosCualli" href="javascript:openRelationshipWindow()"><i class="icon fa fa-lg fa-search-plus" aria-hidden="true"></i></a><small>en caso de ser necesario</small>
                                    &nbsp; <?php ComboboxTipoRelacion::generate('tiporelacion', "260px"); ?>
                                </td>
                            </tr>
                            <tr>
                                <td align="right" bgcolor="#e1e1e1" class="nombre_cliente">Relación multiple : </td>
                                <td>
                                    <table style="margin-left: 40px;width: 80%;border: 1px solid black;border-radius: 10px;">
                                        <thead>
                                            <tr style="font-weight: bold;height: 20px;">
                                                <td><a href="javascript:winuni('catfcmultiple.php?Cliente=<?= $fcVO->getCliente() ?>&criteria=ini&FcOrigen=<?= $fcVO->getId() ?>');">
                                                        <i class="fa-solid fa-plus">Agregar</i>
                                                    </a>
                                                </td>
                                                <td>Serie - Folio</td>
                                                <td>UUID</td>
                                                <td>T.R.</td>
                                                <td></td>
                                                <td>Eliminar</td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $relacionUuid = "SELECT CONCAT(serie, ' - ' ,folio_factura ) folio,uuid_relacionado,tipo_relacion tr,id FROM relacion_cfdi WHERE id_fc = " . $fcVO->getId();
                                            $Uuids = utils\IConnection::getRowsFromQuery($relacionUuid);
                                            $e = 0;
                                            foreach ($Uuids as $uds) {
                                                $StyleColor = $e % 2 == 0 ? " style='background-color:#EAEDED;' " : "";
                                                $e++;
                                                ?>
                                                <tr <?= $StyleColor ?>>
                                                    <td style='text-align:right;padding-right: 10px;'><?= $e ?></td>
                                                    <td style='padding-left: 10px;'><?= $uds["folio"] ?></td>
                                                    <td style='padding-left: 10px;'><?= $uds["uuid_relacionado"] ?></td>
                                                    <td><?= $uds["tr"] ?></td>
                                                    <td>
                                                        <?php
                                                        if ($e == 1) {
                                                            $Sql = "SELECT clave, descripcion FROM cfdi33_c_trelacion WHERE status = 1";
                                                            $qry = utils\IConnection::getRowsFromQuery($Sql);
                                                            ?>
                                                            <select style='width: 150px;' class='texto_tablas TipoRelacion' name='TRelacion' data-idrg="<?= $fcVO->getId() ?>">
                                                                <option value = ''>SELECCIONE EL TIPO DE RELACI&Oacute;N</option>
                                                                <?php
                                                                $Rgst = 0;
                                                                foreach ($qry as $rs) {
                                                                    ?>
                                                                    <option value = '<?= $rs["clave"] ?>'><?= $rs["clave"] ?> | <?= $rs["descripcion"] ?></option>
                                                                    <?php
                                                                    $Rgst++;
                                                                }
                                                                ?>
                                                            </select>
                                                        <?php }
                                                        ?>
                                                    </td>
                                                    <td style="text-align: center;">
                                                        <a href="genfactura331.php?Boton=DeleteRelacion&idDt=<?= $uds["id"] ?>"><i class="fa-solid fa-trash-can" style="color: #ff6633;"></i></a>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                            ?>
                                        </tbody>
                                        <input type="hidden"  name="TipoRelacionCnt" id="TipoRelacionCnt" value="<?= $e ?>">
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td align="right" bgcolor="#e1e1e1" class="nombre_cliente">Complementos: &nbsp;</td>
                                <td align="left">
                                    &nbsp; <?php ComboboxComplementos::generate('complemento'); ?>
                                    &nbsp;<a class="textosCualli" href="javascript:openComplementosWindow()"><i class="icon fa fa-lg fa-search-plus" aria-hidden="true"></i></a><small>en caso de ser requerido</small>
                                </td>
                            </tr>
                            <tr style="height: 25px;">
                                <td align="right" bgcolor="#e1e1e1" class="nombre_cliente">Forma de pago: &nbsp;</td>
                                <td align="left">&nbsp;
                                    <?php $Vc = utils\IConnection::execSql("SELECT valor FROM variables_corporativo WHERE llave = 'CreditoRestringido'") ?>
                                    <?php ComboboxFormaDePago::generate("Formadepago", "250px", "", $clienteVO->getTipodepago(), $Vc["valor"]); ?>
                                </td>
                            </tr>
                            <tr style="height: 25px;">
                                <td align="right" bgcolor="#e1e1e1" class="nombre_cliente">Método de pago: &nbsp;</td>
                                <td align="left">&nbsp;
                                    <?php ComboboxMetodoDePago::generate("Metododepago", "250px", "", $clienteVO->getTipodepago(), $Vc["valor"]); ?>
                                </td>
                            </tr>
                            <tr style="height: 25px;">
                                <td align="right" bgcolor="#e1e1e1" class="nombre_cliente">Correo electronico: &nbsp;</td>
                                <td align="left">&nbsp;&nbsp;<input type="text" name="Correo" value="<?= $clienteVO->getCorreo() ?>" class="texto_tablas" size="20" style="width: 250px;"> &nbsp; enviar correo
                                    <?php
                                    if ($clienteVO->getEnviarcorreo() == 'Si') {
                                        echo "<input type='checkbox' class='botonAnimatedMin' name='Enviarcorreo' value='Si' checked>";
                                    } else {
                                        echo "<input type='checkbox' class='botonAnimatedMin' name='Enviarcorreo' value='Si'>";
                                    }
                                    ?>
                                </td>
                            </tr>

                            <?php cInput("<span style='color: red;'><strong>*&nbsp;</strong></span>Observaciones ", "Text", "60", "Observaciones", "right", $fcVO->getObservaciones(), "100", false, false, "", " placeholder=' Redactar una breve observacion o comentario'"); ?>

                            <tr style="height: 25px;">
                                <td align="right" bgcolor="#e1e1e1" class="nombre_cliente">Desglose IEPS: &nbsp;</td>
                                <td align="left">
                                    <?php
                                    if ($clienteVO->getDesgloseIEPS() == 'S') {
                                        echo "<input type='checkbox' class='botonAnimatedMin' name='DesgloseIEPS' value='S' checked>";
                                    } else {
                                        echo "<input type='checkbox' class='botonAnimatedMin' name='DesgloseIEPS' value='S'>";
                                    }
                                    ?>
                                    &nbsp;desglosa IEPS
                                </td>
                            </tr>
                            <tr style="height: 25px;">
                                <td align="right" bgcolor="#e1e1e1" class="nombre_cliente">Datos del Receptor: &nbsp;</td>
                                <td align="left">
                                    <?php
                                    if ($clienteVO->getNombreFactura() == 'C' || $clienteVO->getNombreFactura() == 'F') {
                                        echo "<input type='checkbox' class='botonAnimatedMin' name='FCuenta' value='1' checked>";
                                    } else {
                                        echo "<input type='checkbox' class='botonAnimatedMin' name='FCuenta' value='1'>";
                                    }
                                    ?>
                                    &nbsp;Incluir Número de Cuenta
                                    <?php
                                    if ($clienteVO->getNombreFactura() == 'A' || $clienteVO->getNombreFactura() == 'F') {
                                        echo "<input type='checkbox' class='botonAnimatedMin' name='FAlias' value='1' checked>";
                                    } else {
                                        echo "<input type='checkbox' class='botonAnimatedMin' name='FAlias' value='1'>";
                                    }
                                    ?>
                                    &nbsp;Incluir Alias
                                </td>
                            </tr>
                            <tr style="height: 25px;">
                                <td></td>
                                <td align="left"><input type="submit" class="nombre_cliente" name="Boton" value="Guardar estos cambios" title="Guarda los datos del formulario"></td>
                            </tr>

                            <tr>
                                <td colspan="2">
                                    <?php
                                    $ComplementosINE = "SELECT * FROM complemento_val WHERE id_fc_fk = " . $fcVO->getId();
                                    $vvl = utils\IConnection::getRowsFromQuery($ComplementosINE);
                                    if (count($vvl) > 1) {
                                        ?>
                                        <table style="width: 70%;margin-left: 10%;border: 1px solid #066;border-radius: 8px;">
                                            <thead style="background-color: #066;color: white;font-weight: bold;">
                                                <tr><td colspan="2" style="text-align: center;">COMPLEMENTO INE </td></tr>
                                                <tr >
                                                    <td style="padding-left: 10px;">Atributo</td>
                                                    <td style="padding-left: 10px;">Valor</td>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $Valores = "SELECT complemento, atributo, IFNULL( valor, defecto ) valor FROM ( 
                                                SELECT A.id_complemento, A.id id_atributo, A.nombre atributo, C.nombre complemento, A.defecto FROM complementos C 
                                                JOIN complemento_attr A ON C.id = A.id_complemento WHERE C.id = 1 ) complemento 
                                                LEFT JOIN ( SELECT * FROM complemento_val WHERE id_fc_fk = " . $fcVO->getId() . ") valores USING(id_complemento, id_atributo)";
                                                $Vals = utils\IConnection::getRowsFromQuery($Valores);
                                                $e = 0;
                                                foreach ($Vals as $rsg) {
                                                    $VlColor = $e % 2 == 0 ? "#ABEBC6" : "";
                                                    ?>
                                                    <tr style="background-color: <?= $VlColor ?>;">
                                                        <td><?= $rsg["atributo"] ?></td>
                                                        <td><?= $rsg["valor"] ?></td>
                                                    </tr>
                                                    <?php
                                                    $e++;
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                        <?php
                                    }
                                    ?>
                                </td>
                            </tr>
                        </table>
                        <div align="center" style="width: 100%;" class="texto_tablas">
                            <br/>
                            <div id="DatosEncabezado">
                                <table aria-hidden="true">
                                    <tr bgcolor ="#E1E1E1" align="center">
                                        <td style="width: 45%"><input type="button" aria-hidden="true" data-toggle="modal"  data-target="#modal-de-carga" name="Boton2" value="Genera factura formato carta" class="ValidaBotones"/></td>
                                        <?php if ($clienteVO->getRfc() !== ClientesDAO::GENERIC_RFC && $clienteVO->getTipodepago() !== TiposCliente::MONEDERO) { ?>
                                            <td style="width: 45%"><input type="button" aria-hidden="true" data-toggle="modal"  data-target="#modal-de-carga" name="Boton2" value="Genera factura formato ticket" class="ValidaBotones"/></td>
                                        <?php } ?>
                                        <td>
                                            <input type='checkbox' class='botonAnimatedMin' name='TipoCantidad' id="TipoCantidad">
                                            <input type="hidden" name="CambioCantidad" id="CambioCantidad">
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="mensajes" style="padding-top: 5px;"><?= $Msj ?></div>
                    </form>
                </td>
            </tr>
        </table>

        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>
    </body>
    <style>
        .fa-plus:hover {
            color: #ff6633;
        }
        .fa-plus {
            color: #066;
        }
    </style>
</html> 
<link rel="stylesheet" href="bootstrap/bootstrap-4.0.0/dist/css/bootstrap-modal.css" type="text/css">
<?php include_once ("bootstrap/modals/modal_carcss.html"); ?>
