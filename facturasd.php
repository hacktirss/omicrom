<?php
#Librerias
session_start();
set_time_limit(720);

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("comboBoxes.php");
include_once ("data/UsuarioDAO.php");
include_once ("data/Uso_webDAO.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();

require './services/FacturasdService.php';

$session = new OmicromSession("fcd.ticket", "fcd.ticket", $nameVariableSession);

$Gfmt = utils\HTTPUtils::getSessionBiValue("catalogoFacturas", "fmt");          //Formato
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));
if ($Gfmt == 1) {
    $Titulo = "Modulo de facturación de tickets detalle";
} else {
    $Titulo = "Modulo de facturación detalle";
}

utils\HTTPUtils::setSessionValue("cGenericPerso", 0);
$usuarioSesion = getSessionUsuario();
$Uso_webVO = new Uso_webVO();
$Uso_webDAO = new Uso_webDAO();
$Uso_webVO->setId($cVarVal);
$Uso_webVO->setOrigen("fcd");
$Uso_webVO->setFecha(date("Y-m-d H:i:s"));
$Uso_webVO->setId_authuser($usuarioSesion->getId());
$Msj = $Uso_webDAO->ValidaExistencia($Uso_webVO, "fcd");
if ($Msj == utils\Messages::RESPONSE_USER_LIVE) {
    header("location: facturas.php?Msj=" . $Msj . " Esperar " . utils\HTTPUtils::getSessionValue("MinutosRes") . " Minutos");
}
$Msj = $request->getAttribute("Msj");
$ciaDAO = new CiaDAO();
$ciaVO = $ciaDAO->retrieve(1);

$version_cfdi = $ciaVO->getVersion_cfdi();

$pacA = $mysqli->query("SELECT * FROM proveedor_pac WHERE activo = 1");
$pac = $pacA->fetch_array();

$cSQL = "SELECT CONCAT(fc.serie, ' ', fc.folio) folio, fc.status, fc.uuid, fc.fecha, fc.cliente,fc.observaciones,fc.periodo,fc.meses, 
        ROUND(fc.cantidad, 2) cantidad,ROUND(fc.importe + fc.iva + fc.ieps, 2 ) subtotal,fc.descuento, fc.ano,
        fc.importe, fc.iva, fc.ieps, fc.total,IFNULL(pagos.importe, 0) pagoImporte, COUNT(fcd.id) registrosDetalle
        FROM fc 
        LEFT JOIN fcd ON fc.id = fcd.id
        LEFT JOIN pagos ON fc.relacioncfdi = pagos.id
        WHERE fc.id = " . $cVarVal;
$HeA = $mysqli->query($cSQL);
$He = $HeA->fetch_array();

$SqlCom = "SELECT * FROM com WHERE activo = 'Si'";
$Cm = utils\IConnection::getRowsFromQuery($SqlCom);
$HtmlAdd = "<select name='" . $coms["descripcion"] . "' value='" . $coms["descripcion"] . "'  id='Seleccteds'>";
$HtmlAdd .= "<option value=''>SELECCIONE ALGUN PRODUCTO</option>";
foreach ($Cm as $coms) {
//    $HtmlAdd .= "<input type='checkbox' class='checkscom' name='" . $coms["descripcion"] . "' value='" . $coms["descripcion"] . "'  id='" . $coms["descripcion"] . "'> " . $coms["descripcion"];
    $HtmlAdd .= "<option value='" . $coms["descripcion"] . "'>" . $coms["descripcion"] . "</option>";
}
$HtmlAdd .= "</select>";

$clienteVO = new ClientesVO();
if (is_numeric($cVarVal)) {
    $fcVO = $fcDAO->retrieve($cVarVal);
    $clienteVO = $clientesDAO->retrieve($fcVO->getCliente());
}

$vCorporativoDAO = new V_CorporativoDAO();
$vCorporativoVO = $vCorporativoDAO->retrieve(ListaLlaves::FACTURACION_ABIERTA, "llave");

$Id = 54;

$paginador = new Paginador($Id,
        "fcd.idnvo, IF(inv.rubro = 'Combustible',rm.fin_venta, vt.fecha) fecha_venta,fcd.descuento,"
        . "IF(rm.descuento > rm.importe,'Diferencia','Normal') sts ",
        "LEFT JOIN inv ON fcd.producto = inv.id
         LEFT JOIN rm ON rm.id = fcd.ticket AND inv.rubro = 'Combustible'
         LEFT JOIN vtaditivos vt ON vt.id = fcd.ticket AND inv.rubro = 'Aceites'",
        "",
        "fcd.id = '$cVarVal'",
        $session->getSessionAttribute("sortField"),
        $session->getSessionAttribute("criteriaField"),
        utils\Utils::split($session->getSessionAttribute("criteria"), "|"),
        strtoupper($session->getSessionAttribute("sortType")),
        $session->getSessionAttribute("page"),
        "=",
        "facturas.php");
$tableContents = $paginador->getTableContents();

/**
 * 0.- Sin registros.
 * 1.- Tickets
 * 2.- Abiertas
 */
$registrosfc = 0;
if (!empty($tableContents['dataCount'])) {

    $sql = "SELECT * FROM fcd WHERE fcd.id = " . $cVarVal . " AND ticket > 0";
    $tickes = $mysqli->query($sql)->fetch_array();
    if (!empty($tickes)) {
        $registrosfc = 1;
    } else {
        $registrosfc = 2;
    }
}
$isGlobal = $registrosfc > 0 && $clienteVO->getRfc() === FcDAO::RFC_GENERIC && $He['status'] == StatusFactura::CERRADO;

$Fecha_3 = date("Y-m-d", strtotime("-3 DAY"));
$FechaI = date("Y-m-") . "01";
$FechaF = date("Y-m-d");
$FechaII = date("Y-m-") . "01";
$FechaFF = date("Y-m-d");
$sql = "SELECT id FROM inv WHERE umedida = 'E48'";
$disp = $mysqli->query($sql)->fetch_array();
$Dt = is_numeric($disp["id"]) ? $disp["id"] : 0;
$self = utils\HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom.php"; ?>
        <script type="text/javascript" src="js/pagosdifd.js"></script>
        <title><?= $Gcia ?></title>
        <style>
            #ScrollM {
                width: 100%;
                padding: 5px;
                height: 380px;
                overflow-y: scroll;
                overflow-x: hidden;
            }
        </style>
        <script>
            $(document).ready(function () {
                $("#FechaI").val("<?= $FechaI ?>");
                $("#FechaF").val("<?= $FechaF ?>");
                $("#FechaICn").val("<?= $FechaI ?>");
                $("#FechaFCn").val("<?= $FechaF ?>");
                $("#FechaIM").val("<?= $FechaI ?>");
                $("#FechaFM").val("<?= $FechaF ?>");
                $("#FechaII").val("<?= $FechaII ?>");
                $("#FechaFF").val("<?= $FechaFF ?>");
                $("#Fecha").val("<?= $Fecha_3 ?>");
                $("#Producto").change(function () {
                    var val = $("#Producto").val();
                    if (val == 1 || val == 2 || val == 3 || val == 4 || val == 5) {
                        $("#Importe").show().prop('disabled', false);
                        $("#Cantidad").show().prop('disabled', false);
                        if (val == <?= $Dt ?>) {
                            $("#Cantidad").prop('disabled', true);
                        }
                    } else {
                        $("#Importe").prop('disabled', true);
                        $("#Cantidad").prop('disabled', false);
                    }
                });
                $("#Ticket").focus();
                $(".LiberacionWarning").click(function () {
                    Swal.fire({
                        title: "¿Seguro de liberar los ticket?",
                        icon: 'warning',
                        html: '¡La factura aun no esta cancelada por el SAT!',
                        iconColor: '#C0392B',
                        background: "#E9E9E9",
                        cancelButtonColor: '#E74C3C',
                        showConfirmButton: true,
                        showCancelButton: true,
                        confirmButtonText: "Aceptar"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            jQuery.ajax({
                                type: "POST",
                                url: "getByAjax.php",
                                dataType: "json",
                                cache: false,
                                data: {"Origen": "LiberaTickets", "IdFc": "<?= $cVarVal ?>", "Usr": "<?= $usuarioSesion->getUsername() ?>"},
                                beforeSend: function () {
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
                                    Swal.fire({
                                        title: '<?= utils\Messages::MESSAGE_DEFAULT ?>',
                                        icon: 'success',
                                        iconColor: '#58D68D',
                                        showConfirmButton: false
                                    });
                                    setTimeout(function () {
                                        location.reload();
                                    }, 1300);
                                }
                            });
                        }
                    });
                });
            }
            );
        </script>
        <?php $paginador->script(); ?>
    </head>

    <body>
        <input type="hidden" name="IdHd" id="IdHd" value="<?= $fcVO->getId() ?>">
        <input type="hidden" name="ImporteHd" id="ImporteHd" value="<?= $fcVO->getTotal() ?>">
        <input type="hidden" name="CliHd" id="CliHd" value="<?= $fcVO->getCliente() ?>">
        <input type="hidden" name="UuidHd" id="UuidHd" value="<?= $fcVO->getUuid() ?>">        
        <?php BordeSuperior(); ?>

        <?php if ($pac["pruebas"] == "1") { ?>
            <div style="background-color: red; color: white; text-align:center;border-radius: 5px;margin-bottom: 5px ;font-family: Helvetica, Arial, Verdana, Tahoma, sans-serif; font-size:14px; font-weight:bold;">
                ALERTA FACTURANDO EN MODO DE DEMOSTRACIÓN
            </div>
        <?php } ?>

        <div id="DatosEncabezado" style="border: 1px solid #808B96;">
            <?php
            if ($fcVO->getStatus() == 2 && ($usuarioSesion->getTeam() === "Administrador" || $usuarioSesion->getTeam() === "Supervisor")) {
                ?>
                <div class="LiberacionWarning">
                    <div>
                        <em class="fa-solid fa-circle-exclamation fa-lg" ></em> Liberación de tickets
                    </div>
                </div>
                <?php
            }
            ?>
            <table aria-hidden="true">
                <tr>
                    <td><label>Id: </label><span><?= $cVarVal ?></span> <label>Folio: </label><span class="naranja"><?= $He["folio"] ?></span></td>
                    <td colspan="3"><label>Cliente: </label><span><?= $clienteVO->__toDescription() ?></span></td>
                    <td colspan="2"><span><?= $He["fecha"] ?></span></td>
                    <td><label>Pago: </label><span class="naranja"><?= number_format($He["pagoImporte"], 2) ?></span></td>
                </tr>
                <tr>
                    <td><label>RFC: </label><span><?= $clienteVO->getRfc() ?></span></td>
                    <td><label>Cantidad: </label><span class="naranja"><?= number_format($He["cantidad"], 2) ?></span></td>
                    <td><label>Importe: </label><span><?= number_format($He["importe"], 2) ?></span></td>
                    <td><label>Ieps: </label><span><?= number_format($He["ieps"], 2) ?></span></td>
                    <td><label>Iva: </label><span><?= number_format($He["iva"], 2) ?></span></td>
                    <td><label>Desc.: </label><span><?= number_format($He["descuento"], 2) ?></span></td>
                    <td><label>Total: </label><span class="naranja"><?= number_format(round($He["importe"] + $He["ieps"] + $He["iva"], 2), 2) ?></span></td>
                </tr>
                <tr>
                    <td colspan="7"><label>Observaciones: </label><span><?= $He["observaciones"] ?></span></td>
                </tr>
            </table>
        </div>


        <table style="width: 100%" class="texto_tablas" aria-hidden="true">
            <tr height=25>
                <td>
                    <?php
                    if ($ciaVO->getFacturacion() === "Si") {
                        if ($He["status"] == StatusFactura::ABIERTO) {
                            if ($He["total"] > 0) {
                                if ($clienteVO->getTipodepago() !== TiposCliente::MONEDERO || ($clienteVO->getTipodepago() === TiposCliente::MONEDERO && $He["total"] == $He["pagoImporte"])) {
                                    echo "<a class='enlace_timbre2' href='genfactura331.php'>&nbsp;GENERAR FACTURA&nbsp;</a>";
                                }
                            }
                        } else {
                            if ($He['status'] == StatusFactura::CERRADO) {
                                echo "<font color='#990000'> Factura cerrada y timbrada";
                            } elseif ($He['status'] == StatusFactura::CANCELADO) {
                                echo "<font color='#990000'> Factura cancelada y timbrada";
                            } elseif ($He['status'] == StatusFactura::CANCELADO_ST) {
                                echo "<font color='#990000'> Factura cancelada sin timbrar";
                            }
                        }
                        echo "</td><td align='right' class='subtitulos'>";
                    } else {
                        echo "OCURRIO UN ERROR, FAVOR DE COMUNICARSE A SOPORTE";
                    }
                    ?>
                </td>
                <?php if ($clienteVO->getTipodepago() === TiposCliente::PREPAGO) { ?>
                    <td style="text-align: right">Anticipos <input type="checkbox" name="PD" id="PD" class="botonAnimatedMin"></td>
                <?php } ?>
            </tr>
        </table>
        <div style="width: 100%; height: 400px;" id="Desap"> <div id="Contenido"></div></div>

        <table style="width: 100%" class="texto_tablas" aria-hidden="true" id="ContenidoFacturas">
            <tr>
                <td style="height : 280px !important; text-align : center !important; vertical-align: top !important;">
                    <div id="TablaDatos">
                        <input type="hidden" name="FechaAct" id="FechaAct" value="<?= $He["fecha"] ?>"> 
                        <table class="paginador" id='Tabla_Fac' aria-hidden="true">
                            <?php
                            echo $paginador->headers(array(" "), array("Desc", $isGlobal ? "Devolución" : "Borrar"));
                            $arrayComentarios = array();
                            ?>
                            <tbody>
                                <?php
                                while ($paginador->next()) {
                                    $row = $paginador->getDataRow();
                                    $colorDesc = "";
                                    $TitleDesc = "";
                                    if ($row["sts"] === "Diferencia") {
                                        $colorDesc = "#EDBB99";
                                        $TitleDesc = "title='El importe es menor al descuento proporcionado'";
                                    }
                                    $row["corte"] == 0 ? $Color = "#F5B7B1" : $Color = "";
                                    ?>
                                    <tr style="background-color: <?= $Color ?>;" <?= $TitleDesc ?>>
                                        <td title="Fecha de la venta: <?= $row["fecha_venta"] ?>"></td>
                                        <?php echo $paginador->formatRow(); ?>
                                        <td style="text-align: right;background-color: <?= $colorDesc ?>"><?= number_format($row["descuento"], 2) ?></td>
                                        <td style="text-align: center;">
                                            <?php if ($He["status"] == StatusFactura::CERRADO && $isGlobal && $row["ticket"] > 0 && $He["registrosDetalle"] > 1) { ?>
                                                <a href=javascript:generaDevolucion("<?= $self ?>","<?= $row["ticket"] ?>","<?= $row["idnvo"] ?>","cId");><i class="icon fa fa-lg fa-copy" aria-hidden="true"></i></a>
                                            <?php } else if ($He["status"] == StatusFactura::ABIERTO) { ?>
                                                <a href=javascript:borrarRegistro("<?= $self ?>","<?= $row["idnvo"] ?>","cId");><i class="icon fa fa-lg fa-trash" aria-hidden="true"></i></a>
                                            <?php } ?>
                                        </td>

                                    </tr>
                                    <?php
                                    $ImpTt += $row["importe"];
                                }
                                ?>
                            </tbody>
                        </table>
                        <?php
                        if ($clienteVO->getTipodepago() === "Monedero" && $ImpTt > 0) {
                            echo ((($He["importe"] + $He["ieps"] + $He["iva"]) + 5) > $ImpTt && (($He["importe"] + $He["ieps"] + $He["iva"]) - 5) < $ImpTt) ?
                                    "" :
                                    "<a style='font-size: 19px; color :#F53535;' href='#' id='RestarVenta' data-importe='" . number_format($ImpTt - ($He["importe"] + $He["ieps"] + $He["iva"]), 2, ".", "") . "'>Tu factura de monedero tiene error en el detalle " . number_format($ImpTt - ($He["importe"] + $He["ieps"] + $He["iva"]), 2) . ".<br>Click para ajustar algún producto <i class='fa-solid fa-hand-sparkles'></i></a>";
                        }
                        ?>
                    </div>
                    <?php
                    $nLink = [];
                    if ($He["status"] == StatusFactura::ABIERTO) {
                        $nLink = Array("<i class=\"icon fa fa-lg fa-eraser\" aria-hidden=\"true\"></i> Limpiar" => "facturasd.php?op=Limpiar",
                            "<i class=\"fa-solid fa-calculator\"></i> Suma" => "facturasd.php?Calcula=Si&busca=$cVarVal");
                    }
                    echo $paginador->footer(false, $nLink, true, true);
                    echo $paginador->filter();
                    ?>
                </td>
            </tr>
        </table>

        <?php if ($He["status"] == StatusFactura::ABIERTO) { ?>

            <div id="FormulariosBoots">

                <div class="container no-margin">

                    <?php if ($clienteVO->getRfc() !== FcDAO::RFC_GENERIC && $clienteVO->getTipodepago() !== TiposCliente::MONEDERO) : ?>
                        <form name="formFacturacion" id="formFacturacion" method="post" action="facturasd.php">
                            <div id="FormulariosBoots">
                                <div class="container no-margin">
                                    <div class="row no-padding">
                                        <div class="col-12 background container no-margin">
                                            <?php
                                            $sql = "SELECT valor FROM variables_corporativo WHERE llave = 'FacGasyServ';";
                                            $var = $mysqli->query($sql);
                                            $valor = $var->fetch_array();
                                            $valor["valor"] === "Si" ? $primerIf = true : $primerIf = $registrosfc == 0 || $registrosfc == 1;
                                            $valor["valor"] === "Si" ? $segundoIf = true : $segundoIf = $Gfmt == 0;
                                            $valor["valor"] === "Si" ? $tercerIf = true : $tercerIf = $vCorporativoVO->getValor() == 1 && ($registrosfc == 0 || $registrosfc == 2);
                                            ?>
                                            <?php if ($primerIf) : ?>
                                                <div class="row no-padding">
                                                    <div class="col-1">Ticket:</div>
                                                    <div class="col-2"><input type="number" id="Ticket" name="Ticket" min="1" placeholder="# Numero de ticket" autofocus="true"></div>
                                                    <div class="col-2"><input type="radio" class="botonAnimatedMin" name="Tipo" value="C" checked> Combustible</div>
                                                    <div class="col-2"><input type="radio" class="botonAnimatedMin" name="Tipo" value="A"> Aditivo</div>
                                                    <div class="col-5 "><input type="submit" name="Boton" value="Agregar ticket" id="PorTicket"></div>
                                                    <input type="hidden" id="BotonTicket" value="Agregar ticket">
                                                    <input type="hidden" id="EdoCuentaTicket" name="EdoCuentaTicket">
                                                </div>

                                            <?php endif; ?>
                                            <?php if ($segundoIf) : ?>
                                                <?php if ($tercerIf && ($usuarioSesion->getTeam() === typeTeam::ADMINISTRADOR || $usuarioSesion->getTeam() === typeTeam::SUPERVISOR)) : ?>
                                                    <div class="row no-padding">
                                                        <div class="col-1">Producto:</div>
                                                        <?php
                                                        $Tipo = "SELECT valor FROM variables_corporativo WHERE llave = 'FacturaConsignacion'";
                                                        $TipoRs = utils\IConnection::execSql($Tipo);
                                                        $TRs = $TipoRs["valor"] === "Si" ? "'Otros'" : "'Aceites','Combustible','Otros'";
                                                        ?>
                                                        <div class="col-5"><?php ComboboxInventario::generate("Producto", $TRs, "") ?></div>
                                                        <div class="col-1">Cnt:</div>
                                                        <div class="col-1"><input type="text" id="Cantidad" name="Cantidad" placeholder="Cantidad"></div>
                                                        <div class="col-1">Importe:</div>
                                                        <div class="col-1"><input type="text" id="Importe" name="Importe" placeholder="Importe"></div>
                                                        <div class="col-1">
                                                            <input type="button" value="Agregar" id="ProductoLibre">
                                                            <input type="hidden" id="BotonHd" value="Agregar">
                                                            <input type="hidden" id="EdoCuenta" name="EdoCuenta">
                                                        </div>
                                                        <div title="Activar para asginar cantidad con los tickets disponibles" class="col-1">
                                                            <input type="checkbox" class="botonAnimatedMin" name="AddTickets" id="AddTickets" value="BuscaTicket"/>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                                <?php if ($primerIf) : ?>
                                                    <div class="row no-padding">
                                                        <div class="col-1">F.Inicial:</div>
                                                        <div class="col-2"><input type="date" name="FechaI" id="FechaI"></div>
                                                        <div class="col-1">F.Final:</div>
                                                        <div class="col-2"><input type="date" name="FechaF" id="FechaF"></div>
                                                        <div class="col-1">Productos:</div>
                                                        <div class="col-2"><?php ComboboxCombustibles::generate("Combustible", "", "", "* | TODOS"); ?></div>
                                                        <div class="col-3"><input type="submit" name="Boton" value="Agregar vtas"></div>
                                                    </div>
                                                <?php endif; ?>

                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div> 
                            </div>
                        </form>
                    <?php elseif ($clienteVO->getTipodepago() === TiposCliente::MONEDERO) : ?>
                        <form name="formFacturacion" id="formFacturacion" method="post" action="facturasd.php">
                            <div class="row no-padding">
                                <div class="col-12 withBackground align-center"><strong>Ventas con Monederos</strong></div>
                            </div>
                            <div class="row no-padding">
                                <div class="col-1 withBackground">F.Inicial:</div>
                                <div class="col-2 withBackground"><input type="date" name="FechaI" id="FechaI"></div>
                                <div class="col-1 withBackground">F.Final:</div>
                                <div class="col-2 withBackground"><input type="date" name="FechaF" id="FechaF"></div>
                                <div class="col-6 withBackground"><input type="submit" name="BotonFecha" value="Agregar"></div>
                            </div>
                            <div class="row no-padding">
                                <div class="col-1 withBackground">Pago:</div>
                                <div class="col-2 withBackground"><input type="number" id="Pago" name="Pago" min="1" placeholder="# Numero de pago"></div>
                                <div class="col-1 withBackground"><strong>&oacute;</strong> Tirilla:</div>
                                <div class="col-2 withBackground"><input type="text" id="Tirilla" name="Tirilla" placeholder="# Numero de tirilla" title="Esta es capturada desde el cambio de turno en cada venta"></div>
                                <div class="col-6 withBackground"><input type="submit" name="BotonPago" value="Agregar"></div>
                            </div>
                            <div class="row no-padding">
                                <div class="col-1 withBackground">Ticket:</div>
                                <div class="col-2 withBackground"><input type="number" id="Ticket" name="Ticket" min="1" placeholder="# Numero de ticket" autofocus="true"></div>
                                <div class="col-2 withBackground"><input type="radio" name="Tipo" value="C" checked> Combustible</div>
                                <div class="col-2 withBackground"><input type="radio" name="Tipo" value="A"> Aditivo</div>
                                <div class="col-5 withBackground"><input type="submit" name="Boton" value="Agregar ticket" id="PorTicket"></div>
                                <input type="hidden" id="BotonTicket" value="Agregar ticket">
                            </div>
                            <input type="hidden" name="General" value="1">
                            <input type="hidden" name="Monedero" value="1">
                            <input type="hidden" name="Cliente" value="<?= $clienteVO->getId() ?>">

                        </form>
                        <div class="row">
                            <div class="col-12 withBackground" style="height: 30px;padding-top: 5px;">
                                <form name="formFacturacion5" method="post" action="facturasd.php">
                                    <div class="row">
                                        <div class="col-2 align-right">Año: <input style="width: 60px;margin-left: 35px;" type="text" name="AnoPeriodo" id="AnoPeriodo" value="<?= date("Y") ?>"></div>
                                        <div class="col-2 align-right">Periodo:</div>
                                        <div class="col-2"><?php ListasCatalogo::getDataPeriodicidad("Periodo_sat") ?></div>
                                        <div class="col-2 align-right">Meses:</div>
                                        <div class="col-2"><?php ListasCatalogo::getDataMeses("Meses") ?></div>
                                        <div class="col-2"><input type="submit" name="Boton" value="Agregar Periodo" id="Periodo"></div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="row no-padding">
                            <div class="col-3 withBackground align-center"><strong>Ventas de Contado</strong></div>
                            <div class="col-3 withBackground align-center"><strong>Ventas de Tarjeta</strong></div>
                            <div class="col-3 withBackground align-center"><strong>Ventas de Monederos</strong></div>
                            <div class="col-3 withBackground align-center"><strong>Ventas de Aceites</strong></div>
                        </div>
                        <div class="row">
                            <div class="col-3 withBackground" style="height: auto;">
                                <form name="formFacturacion1" method="post" action="facturasd.php">
                                    <div class="row">
                                        <div class="col-4">Ticket:</div>
                                        <div class="col-8"><input type="number" name="Ticket" id="Ticket" placeholder="# de ticket" min="1"></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-4">ó Fecha Ini:</div>
                                        <div class="col-8"><input type="date" name="FechaICn" id="FechaICn"></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-4">ó Fecha Fin:</div>
                                        <div class="col-8"><input type="date" name="FechaFCn" id="FechaFCn"></div>
                                    </div>
                                    <div class="row no-padding">
                                        <div class="col-8">&nbsp;</div>
                                        <div class="col-4"><input type="submit" name="Contado" value="Agregar" id="AgregaContado"></div>
                                    </div>
                                    <input type="hidden" name="General" value="1">
                                </form>
                            </div>
                            <div class="col-3 withBackground" style="height: auto;">
                                <form name="formFacturacion2" method="post" action="facturasd.php">
                                    <div class="row">
                                        <div class="col-4">Cliente:</div>
                                        <div class="col-8"><?php ListasCatalogo::getClientesByRubro("Cliente", ["'Tarjeta'"], ["*" => "Todos"]) ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-4">F.Inicial:</div>
                                        <div class="col-8"><input type="date" name="FechaI" id="FechaI"></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-4">F.Final:</div>
                                        <div class="col-8"><input type="date" name="FechaF" id="FechaF"></div>
                                    </div>
                                    <div class="row no-padding">
                                        <div class="col-8">&nbsp;</div>
                                        <div class="col-4"><input type="submit" name="Tarjeta" value="Agregar" id="AgregarTarjeta"></div>
                                    </div>
                                    <input type="hidden" name="General" value="1">
                                </form>
                            </div>
                            <div class="col-3 withBackground" style="height: auto;">
                                <form name="formFacturacion3" method="post" action="facturasd.php">
                                    <div class="row">
                                        <div class="col-4">Tirilla:</div>
                                        <div class="col-8"><input type="text" name="Tirilla" placeholder="# de Tirilla"></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-4">F.Inicial:</div>
                                        <div class="col-8"><input type="date" name="FechaI" id="FechaIM"></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-4">F.Final:</div>
                                        <div class="col-8"><input type="date" name="FechaF" id="FechaFM"></div>
                                    </div>
                                    <div class="row no-padding">
                                        <div class="col-8">&nbsp;</div>
                                        <div class="col-4"><input type="submit" name="Monedero" value="Agregar" id="AgregarMonedero"></div>
                                    </div>
                                    <input type="hidden" name="General" value="1">
                                </form>
                            </div>
                            <div class="col-3 withBackground" style="height: auto;">
                                <form name="formFacturacion4" method="post" action="facturasd.php">
                                    <div class="row">
                                        <div class="col-4">
                                            Rubro: 
                                        </div>
                                        <div class="col-8">
                                            <select style="width: 100%;" name="RubroAditivo" id="RubroAditivo">
                                                <option value="Aceites">Aceites</option>
                                                <option value="Seguro">Seguro</option>
                                                <option value="Servicio">Servicio</option>
                                                <option value="Otros">Otros</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-4">F.Inicial:</div>
                                        <div class="col-8"><input type="date" name="FechaII" id="FechaII"></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-4">F.Final:</div>
                                        <div class="col-8"><input type="date" name="FechaFF" id="FechaFF"></div>
                                    </div>
                                    <div class="row no-padding">
                                        <div class="col-8">&nbsp;</div>
                                        <div class="col-4"><input type="submit" name="Aceites" value="Agregar" id="AgregarAceites"></div>
                                    </div>
                                    <input type="hidden" name="General" value="1">
                                </form>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 withBackground" style="height: 30px;padding-top: 5px;">
                                <form name="formFacturacion5" method="post" action="facturasd.php">
                                    <div class="row">
                                        <div class="col-2 align-right">Año: 
                                            <select  style="width: 60px;margin-left: 35px;"  name="AnoPeriodo" id="AnoPeriodo">
                                                <?php
                                                for ($i = 2022; $i <= date("Y"); $i++) {
                                                    ?>
                                                    <option value="<?= $i ?>"><?= $i ?></option>
                                                    <?php
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-2 align-right">Periodo:</div>
                                        <div class="col-2"><?php ListasCatalogo::getDataPeriodicidad("Periodo_sat") ?></div>
                                        <div class="col-2 align-right">Meses:</div>
                                        <div class="col-2"><?php ListasCatalogo::getDataMeses("Meses") ?></div>
                                        <div class="col-2"><input type="submit" name="Boton" value="Agregar Periodo" id="Periodo"></div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>            
            </div>            
            <?php
        }
        ?>
        <?php BordeSuperiorCerrar() ?>
        <?php
        PieDePagina();
        $Periodo = $He["periodo"];
        $Meses = $He["meses"];
        ?>
        <script type="text/javascript">
            $(document).ready(function () {
                $("#ProductoLibre").click(function () {
                    $("#BotonHd").attr("name", "Boton");
                    confirmSwal();
                });

                $("#PorTicket").click(function () {
                    $("#BotonTicket").attr("name", "Boton");
                    // confirmPorTicket();
                });
                $("#Periodo_sat").val("<?= $Periodo ?>");
                $("#Meses").val("<?= $Meses ?>");
                $("#AnoPeriodo").val("<?= $He["ano"] ?>");
                $(".checkscom").click(function () {
                    $(".checkscom").prop('checked', false);
                    $(this).prop('checked', true);
                });
                $("#RestarVenta").click(function () {
                    var Importe = this.dataset.importe;
                    Swal.fire({
                        title: "¿Seguro de reducir $" + Importe + "?",
                        icon: "info",
                        background: "#E9E9E9",
                        showConfirmButton: true,
                        confirmButtonText: "Si",
                        showCancelButton: true,
                        html: "<?= $HtmlAdd ?>",
                        cancelButtonText: "No",
                        cancelButtonColor: '#d33'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = "facturasd.php?op=ReduceProducto&Producto=" + $("#Seleccteds").val() + "&Importe=" + Importe;
                        }
                    });
                });
            });
            function confirmSwal() {
                Swal.fire({
                    title: "¿Deseas agregar los Conceptos Manuales al estado de cuenta?",
                    icon: "info",
                    background: "#E9E9E9",
                    toast: true,
                    position: "top-right",
                    showConfirmButton: true,
                    confirmButtonText: "Si",
                    showCancelButton: true,
                    cancelButtonText: "No",
                    cancelButtonColor: '#d33'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $("#EdoCuenta").val("Si");
                        $('#formFacturacion').submit();
                    } else {
                        $("#EdoCuenta").val("No");
                        $('#formFacturacion').submit();
                    }
                });


            }
            function confirmPorTicket() {
                Swal.fire({
                    title: "¿Deseas agregar ticket al estado de cuenta?",
                    icon: "info",
                    background: "#E9E9E9",
                    toast: true,
                    position: "top-right",
                    showConfirmButton: true,
                    confirmButtonText: "Si",
                    showCancelButton: true,
                    cancelButtonText: "No",
                    cancelButtonColor: '#d33'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $("#EdoCuentaTicket").val("Si");
                        $('#formFacturacion').submit();
                    } else {
                        $("#EdoCuentaTicket").val("No");
                        $('#formFacturacion').submit();
                    }
                });


            }
            function sleep(ms) {
                return new Promise(resolve => setTimeout(resolve, ms));
            }
        </script>
        <script src="./js/pages/facturasd.js"></script>
    </body>
</html>
