<?php
#Librerias
session_start();

include_once ("auth.php");
include_once ("authconfig.php");
include_once ("check.php");
include_once ("comboBoxes.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$self = utils\HTTPUtils::self();

$Titulo = "Pagos";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$usuarioSesion = getSessionUsuario();
if ($request->getAttribute("Creacion") == 1) {
    $sqlId = "SELECT id FROM omicrom.pagos order by id desc limit 1;";
    $CpoId = $mysqli->query($sqlId);
    $Cpo = $CpoId->fetch_array();
    $sqlFc = "SELECT id FROM fc order by id desc limit 1;";
    $CpoFc = $mysqli->query($sqlFc);
    $CpoFc = $CpoFc->fetch_array();
    header("Location: pagose33.php?busca=" . $Cpo["id"] . "&IdFc=" . $CpoFc["id"]);
} else if ($request->getAttribute("Creacion") == 2) {
    $Msj = "Error : UUID usado con anterioridad";
    header("Location: pagos.php?Msj=$Msj");
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

$nameVarCliente = "Cliente";
if ($request->hasAttribute($nameVarCliente)) {
    utils\HTTPUtils::setSessionValue($nameVarCliente, $request->getAttribute($nameVarCliente));
}
$Cliente = utils\HTTPUtils::getSessionValue($nameVarCliente);

require_once 'services/PagosService.php';

$pagoVO = new PagoVO();
$clienteVO = new ClientesVO();

if (is_numeric($busca)):
    $pagoVO = $pagoDAO->retrieve($busca);
    $clienteVO = $clientesDAO->retrieve($pagoVO->getCliente());
    if ($pagoVO->getUuid() !== "-----"):
        $sql = "SELECT pagos.uuid, cia.rfc rfc_emisor,cli.rfc rfc_receptor,
                pagos.importe,ExtractValue(facturas.cfdi_xml, '/cfdi:Comprobante/@Sello') sello
                FROM pagos 
                LEFT JOIN cli ON pagos.cliente = cli.id
                LEFT JOIN cia ON TRUE
                LEFT JOIN facturas ON facturas.uuid = pagos.uuid
                WHERE pagos.id = '" . $busca . "'";
        $CpoA = $mysqli->query($sql);
        $Cpo = $CpoA->fetch_array();
    endif;
else:
    $clienteVO = $clientesDAO->retrieve($Cliente);
    if ($clienteVO->getFacturacion() === "1") :
        $pagoVO->setCliente($Cliente);
        $pagoVO->setFecha(date("Y-m-d H:i:s"));
        $pagoVO->setFechaD(date("Y-m-d"));
        $pagoVO->setHoraD("12:00:00");
        $pagoVO->setStatus(StatusPago::ABIERTO);
        $pagoVO->setFormapago($clienteVO->getFormadepago());
    else:
        $Msj = "Error: el cliente [" . $clienteVO->getNombre() . "] no tiene permisos para facturar.";
        header("Location: pagos.php?Msj=$Msj");
    endif;
endif;
$pp = "SELECT aplicado FROM pagos WHERE id = " . $pagoVO->getId();
$Apli = utils\IConnection::execSql($pp);
$currentDate = date("Y-m-d");
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom.php"; ?>
        <title><?= $Gcia ?></title>
        <script>

            $(document).ready(function () {
                $("#Fecha_Ini").val("<?= $pagoVO->getFecha_ini() ?>");
                $("#Fecha_Fin").val("<?= $pagoVO->getFecha_fin() ?>");
<?php
if ($busca === "NUEVO") {
    ?>
                    $("#Fecha_Ini").val("<?= date("Y-m-d") ?>");
                    $("#Fecha_Fin").val("<?= date("Y-m-d") ?>");
    <?php
}
?>

                $("#Formapago").val("<?= $pagoVO->getFormapago() ?>");
                $("#Banco").val("<?= $pagoVO->getBanco() ?>");
                $("#Relacioncfdi").val("<?= $pagoVO->getRelacioncfdi() ?>");
                $("#tiporelacion").val("<?= $pagoVO->getTiporelacion() ?>");
                $("#Montonoreconocido").val("<?= $pagoVO->getMontonoreconocido() ?>");
                $("#UsoCfdi").val("<?= $pagoVO->getUsocfdi() ?>");
                $("#FechaDeposito").val("<?= $pagoVO->getFechaD() ?>").attr("size", "12").addClass("texto_tablas");
                $("#cFecha").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaDeposito")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#cFecha_ini").css("cursor", "hand").click(function () {
                    displayCalendar($("#Fecha_Ini")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#cFecha_fin").css("cursor", "hand").click(function () {
                    displayCalendar($("#Fecha_Fin")[0], "yyyy-mm-dd", $(this)[0]);
                });
                if ("<?= $pagoVO->getImporte() ?>" == "<?= $Apli["aplicado"] ?>") {
//                    $(".MuestraActualizar").hide();
                }

                $("#form1").submit(function (e) {
                    let startDate = $("#FechaDeposito").val();
                    let maxDate = "<?= $currentDate ?>";
                    if ((new Date(startDate).getTime() > new Date(maxDate).getTime())) {
                        $("#FechaDeposito").val("<?= $pagoVO->getFechaD() ?>");
                        $("#FechaDeposito").focus();
                        clicksForm = 0;
                        alert("La fecha del deposito no puede ser mayor a la fecha actual.");
                        e.preventDefault();
                    }
                });

                $("#Concepto").focus();

                $("#Password").click(function () {
                    $(this).prop("type", "password");
                });
            });

            function openRelationshipWindow() {
                window.open("catpagos.php?orden=pagos.id&cliente=<?= $pagoVO->getCliente() ?>", "_blank", "width=800,height=420,resizable=no,scrollbars=no");
            }
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="pagos.php"><div class="RegresarCss " alt="Flecha regresar" style="">Regresar</div></a>
                </td>
                <td style="vertical-align: top;">
                    <form name = "form1" id = "form1" method = "post" action = "">
                        <input type = "hidden" name = "busca" value = "<?= $pagoVO->getId() ?>">
                        <input type = "hidden" name = "Cliente" value = "<?= $pagoVO->getCliente() ?>">
                        <table style = "width: 95%; border: 0px; padding: 0px;" class = "texto_tablas" aria-hidden = "true">
                            <?php
                            cInput("Id:", "Text", "5", "Id", "right", $busca . " | Status: <strong>" . $pagoVO->getStatus() . "</strong>", "40", false, true, "", "");
                            cInput("Realizado por:", "Text", "5", "Usuario", "right", $pagoVO->getUsr(), "40", false, true, "", "");
                            cInput("Cliente: ", "Text", "50", "Nombre", "right", $clienteVO->__toDescription(), "80", true, true, "", "");
                            cInput("Fecha de captura: ", "Text", "40", "", "right", $pagoVO->getFecha(), "80", true, true, "", "");
                            cInput("<span style='color: red;'><strong>*&nbsp;</strong></span>Fecha del Deposito: ", "Text", "10", "FechaDeposito", "right", "", "10", true, false, "&nbsp <img src='libnvo/calendar.png' id='cFecha'> movimiento registrado en el banco", " required='required'");
                            if ($clienteVO->getTipodepago() === "Monedero" || $clienteVO->getTipodepago() === "Tarjeta") {
                                ?>
                                <tr><td colspan="2" style="font-size: 16px; background-color: rgb(225, 225, 225);font-weight: bold;text-align: center;">Periodo de fechas que se estan pagando</td></tr>
                                <?php
                                cInput("Fecha Inicio: ", "Text", "10", "Fecha_Ini", "right", "", "10", true, false, "&nbsp <img src='libnvo/calendar.png' id='cFecha_ini'>", " required='required'");
                                cInput("Fecha Fin: ", "Text", "10", "Fecha_Fin", "right", "", "10", true, false, "&nbsp <img src='libnvo/calendar.png' id='cFecha_fin'>", " required='required'");
                                cInput("Monto no reconocido: ", "Text", "10", "Montonoreconocido", "right", "", "10", true, false, "", " required='required'");
                                ?>
                                <tr><td colspan="2" style="font-size: 16px; background-color: rgb(225, 225, 225);font-weight: bold;text-align: center;height: 10px;"></td></tr>
                                <?php
                            }
                            cInput("<span style='color: red;'><strong>*&nbsp;</strong></span>Hora del Deposito: ", "Time", "10", "HoraDeposito", "right", $pagoVO->getHoraD(), "40", true, false, "", " required='required'");
                            ?>

                            <tr class="texto_tablas">
                                <td align="right" bgcolor="#e1e1e1" class="nombre_cliente">Recibo de pago relacionado: &nbsp;</td>
                                <td align="left">&nbsp;<input type="text" name="Relacioncfdi" id="Relacioncfdi" class="texto_tablas" size="10"/>
                                    &nbsp;<a class="textosCualli" href="javascript:openRelationshipWindow()"><i class="icon fa fa-lg fa-search-plus" aria-hidden="true"></i></a><small>en caso de ser necesario</small>
                                    &nbsp;<?php ComboboxTipoRelacion::generate("tiporelacion", "230px"); ?>
                                </td>
                            </tr>

                            <?php
                            cInput("<span style='color: red;'><strong>*&nbsp;</strong></span>Concepto: ", "Text", "50", "Concepto", "right", $pagoVO->getConcepto(), "80", true, false, "", "required='required' ");
                            if ($clienteVO->getTipodepago() === TiposCliente::MONEDERO) {
                                cInput("<span style='color: red;'><strong>*&nbsp;</strong></span>UUID del pago: ", "Text", "50", "UUID", "right", $pagoVO->getUuid(), "80", true, false, "", "required='required' ");
                            }
                            cInput("Número de Operación: ", "Text", "50", "NumOper", "right", $pagoVO->getNumoperacion(), "80", true, false, "", " placeholder='Numero de transaccion bancaria.'");

                            if ($clienteVO->getTipodepago() === "Prepago") {
                                ?>
                                <tr>
                                    <td align="right" bgcolor="#e1e1e1" class="nombre_cliente">
                                        Uso de CFDI :
                                    <td>
                                        &nbsp; <?= ComboboxUsoCFDI::generateByTypeCli("UsoCfdi", strlen($clienteVO->getRfc()), "required"); ?>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>

                            <tr>
                                <td style="background-color: #e1e1e1; text-align: right;" class="nombre_cliente" >
                                    <span style="color: red;"><strong>*&nbsp;</strong></span>Forma de Pago: &nbsp;</td>
                                <td>
                                    <?php ComboboxFormaDePago::generate("Formapago", "330px", " required='required'") ?>
                                </td>
                            </tr>

                            <tr class="texto_tablas">
                                <td align="right" class="nombre_cliente" bgcolor="#e1e1e1">
                                    <span style="color: red;"><strong>*&nbsp;</strong></span>Banco: &nbsp;</td>
                                <td>
                                    <?php ComboboxBancos::generate("Banco", "330px", " required='required'") ?>
                                </td>
                            </tr>
                            <?php
                            cInput("<span style='color: red;'><strong>*&nbsp;</strong></span>Importe: ", "Text", "10", "Importe", "right", $pagoVO->getImporte(), "40", true, false, "", " required='required'");

                            if (is_numeric($busca)) {
                                $Tipocli = "SELECT tipodepago FROM cli WHERE id = " . $pagoVO->getCliente();
                                $Tc = utils\IConnection::execSql($Tipocli);
                                if ((($pagoVO->getUuid() === "-----" && $pagoVO->getStatusCFDI() == StatusPagoCFDI::ABIERTO) || $pagoVO->getStatus() === StatusPago::ABIERTO ) || ($pagoVO->getStatus() === StatusPago::CERRADO && $Tc["tipodepago"] === "Tarjeta")) {
                                    echo "<tr><td colspan='2' align='center'><input type='submit' name='Boton' value='Actualizar' class='nombre_cliente MuestraActualizar'></td></tr>";
                                }
                            } else {
                                echo "<tr><td colspan='2' align='center'><input type='submit' name='Boton' value='Agregar' class='nombre_cliente'></td></tr>";
                            }

                            if ($busca > 0 && $pagoVO->getStatus() !== StatusPago::CANCELADO) {
                                if ($pagoVO->getUuid() !== "-----" && $pagoVO->getStatusCFDI() > StatusPagoCFDI::ABIERTO) {
                                    if (!empty($Cpo["uuid"])) {
                                        ?>

                                        <?php
                                        cInput("Para su verificacion fiscal:", "Text", "0", "Mensaje", "right", "<a class='textosCualli' target='_BLANK' href='https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx"
                                                . "?id=" . $Cpo["uuid"]
                                                . "&re=" . $Cpo["rfc_emisor"]
                                                . "&rr=" . $Cpo["rfc_receptor"]
                                                . "&tt=" . $Cpo["importe"]
                                                . "&fe=" . substr($Cpo["sello"], strlen($Cpo["sello"]) - 8, 8)
                                                . "'>https://verificacfdi.facturaelectronica.sat.gob.mx</a>", "0", true, true, "");
                                    }

                                    cInput("Folio fiscal:", "Text", "40", "Uuid", "right", $Cpo['uuid'], "40", true, true, "");
                                    cInput("Enviar correo:", "Text", "40", "Correo", "right", $clienteVO->getCorreo(), "40", false, false, "<input class='nombre_cliente' type='submit' name='Boton' value='Enviar correo'>");
                                    if (!empty($clienteVO->getCorreo2())) {
                                        cInput("Correo CC.:", "Text", "5", "Correo2", "right", $clienteVO->getCorreo2(), "5", true, true, "");
                                    }
                                }
                                ?>

                                <?php
                                if (($pagoVO->getUuid() === PagoDAO::SIN_TIMBRAR || ($pagoVO->getStatusCFDI() != StatusPagoCFDI::CERRADO && $pagoVO->getStCancelacion() == StatusCancelacionFactura::CANCELADA_CONFIRMADA )) || $clienteVO->getTipodepago() === "Monedero") {
                                    cInput("Cancelar pago:", "text", "40", "Password", "right", "", "40", false, false, "<input class='nombre_cliente' type='submit' name='Boton' value='Cancelar'>", " placeholder='Ingresar clave de cancelacion'");
                                } else {
                                    if ($clienteVO->getTipodepago() === TiposCliente::CREDITO || $clienteVO->getTipodepago() === TiposCliente::PREPAGO) {
                                        $relacionados = "<div style='text-align: center;'><span style='color: red;'><strong>*&nbsp;</strong></span><a class=\"textosCualli\" href=\"cantimbrepago.php?busca=" . $busca . "\">Cancelar Pago</a> <span style='color: red;'><strong>*&nbsp;</strong></span></div>";
                                        cInput("Cancelar pago:", "text", "40", "Password", "right", "", "40", false, true, "<span style='color: red;'><strong>*&nbsp;</strong></span> Deberá cancelar el CFDI de anticipo o complemento antes de poder cancelar el pago <span style='color: red;'><strong>*&nbsp;</strong></span>$relacionados", "");
                                    }
                                }
                            }
                            ?>
                            <input type="hidden" name="IdFc" value="<?= $request->getAttribute("IdFc") ?>">
                        </table>
                    </form>
                    <?php
                    $SqlBusca = "SELECT id FROM fc WHERE relacioncfdi = '$busca'";
                    $CpoBs = $mysqli->query($SqlBusca);
                    $CpoBc = $CpoBs->fetch_array();
                    if ($CpoBc["id"] > 0 && $busca <> "NUEVO") {
                        ?>
                        <div style="width: 100%;">
                            <div style="margin-left: 70%;" title="Link de redireccionamiento a la factura asociada">
                                <a class="textosCualli" href="facturasd.php?cVarVal=<?= $CpoBc["id"] ?>"><em class="fa-solid fa-circle-info fa-lg"></em></i> Ir al detalle de la factura</a>
                            </div>
                        </div>
                        <?php
                    } else if ($clienteVO->getTipodepago() === TiposCliente::MONEDERO && $pagoVO->getStatus() <> "Cerrada") {
                        ?>
                        <div style="height: 25px;padding-left: 40%;padding-top: 10px;" title="Sin check pasa EDENRED MEXICO">
                            <form name="form2" id="form2" method="post" action="">
                                Monto Neto : <input type="checkbox" name="MontoNeto" class="botonAnimatedMin" value="1" id="MontoNeto"/>
                                Efectivale : <input type="checkbox" name="Efectivale" class="botonAnimatedMin" value="1" id="Efectivale"/>
                                <input type="hidden" name="DireccionUpload" id="DireccionUpload">
                                <input type="hidden" name="MN" value="1" id="MN"/>
                            </form>
                        </div>
                        <div style="margin-top: 25px;" class="container show-dropzone">
                            <div class="row no-padding">
                                <div class="col-lg-3"></div>
                                <div class="col-lg-5">
                                    <div class="btn-group w-100">
                                        <form class="dropzone" id="myDrop" enctype="multipart/form-data">
                                            <div class="fallback">
                                                <input type="file" name="file" id="myId" multiple>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="col-lg-3"></div>
                            </div>
                        </div>
                        <?php
                    }
                    ?>

                    <div style='text-align: left;' class="texto_tablas">(&nbsp;<span style='color: red;'><strong>*&nbsp;</strong></span>) Campos necesarios para control y registro de pagos</div>
                </td>
            </tr>
        </table>
        <?php
        $CliIni = $request->getAttribute("Cliente") > 0 ? $request->getAttribute("Cliente") : 0;
        ?>
        <script src="dropzone/min/dropzone.min.js"></script>
        <script type="text/javascript">
            $(document).ready(function () {
                var MontoNeto = "";
                var i = 0;
                $("#MontoNeto").change(function () {
                    $("#MontoNeto").val(1);
                    $('#form2').submit();
                });

                $("#Efectivale").change(function () {
                    $("#DireccionUpload").val(1);
                    $('#form2').submit();
                });
                var direccion;
                if ("<?= $request->getAttribute("DireccionUpload") ?>" == 1) {
                    $("#Efectivale").prop('checked', true).val(1);
                    direccion = "uploadPagosEfectivale";
                } else {
                    $("#Efectivale").prop('checked', false).val(0);
                    direccion = "uploadPagos";
                }
                if ("<?= $request->getAttribute("MontoNeto") ?>" == 1) {
                    $("#MontoNeto").prop('checked', true).val(1);
                } else {
                    $("#MontoNeto").prop('checked', false).val(0);
                }
                if ("<?= $clienteVO->getTipoMonedero() ?>" === "<?= tipoFacturaMonedero::MONTONETO ?>" && <?= $CliIni ?> > 0) {
                    $("#MontoNeto").prop('checked', true).val(1);
                    $("#MontoNeto").val(1);
                    $('#form2').submit();
                } else if ("<?= $clienteVO->getTipoMonedero() ?>" === "<?= tipoFacturaMonedero::EFECTIVALE ?>" && <?= $CliIni ?> > 0) {
                    $("#Efectivale").prop('checked', true).val(1);
                    $("#DireccionUpload").val(1);
                    $('#form2').submit();
                }
                Dropzone.prototype.defaultOptions.dictDefaultMessage = "Arrastrar o dar click para subir archivo XML";
                Dropzone.options.myDrop = {
                    url: direccion + ".php?busca=<?= $carga ?>&Cliente=<?= $pagoVO->getCliente() ?>&Usr=<?= $usuarioSesion->getUsername() ?>&MontoNeto=" + $("#MontoNeto").val(),
                    uploadMultiple: true,
                    maxFileSize: 3,
                    acceptedFiles: ".xml",
                    success: function (file, response) {
                        if (response == "Error") {
                            setTimeout(function () {
                                $(location).attr('href', 'pagose33.php?Creacion=2');
                            }, 700);
                        } else {
                            setTimeout(function () {
                                $(location).attr('href', 'pagose33.php?Creacion=1');
                            }, 700);
                        }
                    }
                }
            });
        </script>
        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>