<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("comboBoxes.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();

require_once './services/ReportesVentasService.php';
require_once './services/CambioTurnoService.php';

$Titulo = "Venta en Dolares del corte $Corte ";
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$ctVO = new CtVO();
if ($Corte > 0) {
    $ctVO = $ctDAO->retrieve($Corte);
}

$Ticket = 0;
$rmVO = new RmVO();
if ($request->hasAttribute("Ticket")) {
    $Ticket = $request->getAttribute("Ticket");
    $rmVO = $rmDAO->retrieve($Ticket);
}

$selectVentasDolares = "
        SELECT 
        rm.id,rm.cliente, LOWER(com.descripcion) producto,rm.posicion,rm.volumen,rm.pesos,
        f.detalle tipoCambio,f.monto,rm.pagoreal,IFNULL(fc.id,0) folio
        FROM man, com, formas_de_pago f, rm
        LEFT JOIN fc ON rm.uuid = fc.uuid AND fc.uuid <> '-----' AND fc.status = '1'
        WHERE TRUE
        AND man.posicion = rm.posicion AND man.activo = 'Si'
        AND com.clavei = rm.producto 
        AND rm.id = f.id 
        AND rm.corte = '$Corte' 
        ";

if (is_numeric($IslaPosicion)) {
    $selectVentasDolares .= " AND man.isla_pos = $IslaPosicion";
}

$selectVentasDolares .= " 
        ORDER BY rm.id ";

$registros = utils\IConnection::getRowsFromQuery($selectVentasDolares);

$result = $mysqli->query($cSql);

$self = utils\HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");
$returnLink = "movdolares.php";
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php include './config_omicrom.php'; ?>   
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                var orden = "<?= $orden ?>";

                $("input[name='orden']").filter("[value='" + orden + "']").attr("checked", true);

                $("#Corte").val("<?= $Corte ?>");
                $("#IslaPosicion").val("<?= $IslaPosicion ?>");
                $("#Status").val("<?= $Status ?>");
                $("#returnLink").val("<?= $returnLink ?>");
                $("#autocomplete").val("<?= html_entity_decode($SCliente) ?>");
                $("#InicialB").hide();

                $("#TicketValue").focus();

            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>
        <?php TotalizaCorte(); ?>

        <div id="FormulariosBoots">
            <div class="container no-margin">
                <form name="form1" id="form1" method="post" action="">
                    <div class="row no-padding">
                        <div class="col-2 align-right">Isla o Dispensario:</div>
                        <div class="col-2">
                            <div class="content-select">
                                <select id="IslaPosicion" name="IslaPosicion" style="min-width: 94px;" onchange="submit();">
                                    <?php
                                    foreach ($IslasPosicion as $key => $value) {
                                        echo "<option value='$key'>$value</option>";
                                    }
                                    ?>
                                </select><em></em>
                            </div>
                        </div>
                        <div class="col-2"></div>
                        <div class="col-2">Orden de busqueda:</div>
                        <div class="col-1 align-right">Cliente</div>
                        <div class="col-1"><input type="radio" class="botonAnimatedGreen" name="orden" value="cliente" onchange="submit();"></div>
                        <div class="col-1 align-right">Posición</div>
                        <div class="col-1"><input type="radio" class="botonAnimatedGreen" name="orden" value="posicion" onchange="submit();"></div>
                    </div>
                </form>

                <?php
                if ($ctVO->getStatusctv() === StatusCorte::ABIERTO) {
                    ?>
                    <form name="form2" id="form3" method="post" action="">
                        <div class="row no-padding" id="InicialA">
                            <div class="col-2 align-right">No.ticket:</div>
                            <div class="col-2"><input type="number" name="TicketValue" id="TicketValue" min="0" max="10000000"></div>
                            <div class="col-1"><input type="submit" name="BotonEnviar" value="Buscar" id="BotonEnviar"></div>
                            <div class="col-5"></div>
                            <div class="col-2">
                                <div class="content-select">
                                    <select name="Status" id="Status" onchange="submit();"><option value="*">Todos</option><option value="0">Completos</option><option value="1">Modificados</option></select>
                                    <em></em>
                                </div>
                            </div>
                        </div>
                    </form>
                    <form name="form3" id="form3" method="post" action="">
                        <div class="row no-padding" id="InicialB">
                            <div class="col-2 align-right">Ticket: <span id="TicketSpan">0</span></div>
                            <div class="col-1 align-right"><span id="PagorealSpan">$ 0.00</span></div>
                            <div class="col-1 align-right">Pago/Dolares:</div>
                            <div class="col-1"><input type="text" name="Dolares" id="Pagoreal"></div>
                            <div class="col-1"><input type="submit" name="Boton" value="Agregar" id="BotonAgregar"></div>
                            <div class="col-1 warning"><a href="movvtasmon.php" id="Cancelar" title="Cancelar operación"><i class="icon fa fa-lg fa-ban" aria-hidden="true" ></i></a></div>
                            <input type="hidden" name="Ticket" id="Ticket">
                        </div>
                        <input type="hidden" name="CorteValue" id="Corte">
                        <input type="hidden" name="returnLink" id="returnLink">
                        <input type="hidden" name="Tarjetas" value="0">
                    </form>
                    <?php
                }
                ?>
            </div>
        </div>

        <div id="TablaDatos">
            <table aria-hidden="true">
                <tr>
                    <td class="fondoNaranja">Ticket</td>
                    <td class="fondoNaranja">Fac</td>
                    <td class="fondoNaranja">Producto</td>
                    <td class="fondoNaranja">Pos</td>
                    <td class="fondoNaranja">Litros</td>
                    <td class="fondoNaranja">Importe MX</td>
                    <td class="fondoNaranja">Pago/Dolares</td>                                                    
                    <td class="fondoNaranja">Tpo/Cambio</td>
                    <td class="fondoNaranja">Importe USD</td>
                    <td class="fondoNaranja">-----</td>
                </tr>

                <?php
                foreach ($registros as $rg) {
                    ?>
                    <tr>
                        <td align="right"><?= $rg["id"] ?></td>
                        <td style="text-align: center;">
                            <?php if (!empty($rg["uuid"]) && $rg["uuid"] !== FcDAO::SIN_TIMBRAR) { ?>
                                <a href=javascript:winuni("enviafile.php?id=<?= $rg["uuid"] ?>&type=pdf&formato=0")><i class="icon fa fa-lg fa-file-pdf-o" aria-hidden="true"></i></a>
                            <?php } ?>
                        </td>

                        <td style="text-align: center;"><?= ucwords(strtolower($rg["producto"])) ?></td>
                        <td align="right"><?= number_format($rg["posicion"], 0) ?></td>
                        <td align="right"><?= number_format($rg["volumen"], 2) ?></td>
                        <td align="right"><strong><?= number_format($rg["pesos"], 2) ?></strong></td>
                        <td align="right"><?= number_format($rg["monto"], 2) ?></td>
                        <td align="right"><?= number_format($rg["tipoCambio"], 2) ?></td>
                        <td align="right"><?= number_format($rg["monto"] * $rg["tipoCambio"], 2) ?></td>
                        <td style="text-align: center;">
                            <?php if ($ctVO->getStatusctv() === StatusCorte::ABIERTO) { ?>
                                <a class="textosCualli" href=javascript:confirmar("Deseas&nbsp;eliminar&nbsp;el&nbsp;registro?","<?= $self ?>?cId=<?= $rg["id"] ?>&op=Dolares&returnLink=<?= $returnLink ?>");><i class="icon fa fa-lg fa-trash" aria-hidden="true"></i></a>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php
                    $nImp += $rg["monto"];
                    $nLit += $rg["volumen"];
                    $nPes += $rg["pesos"];
                    $nDolares += $rg["monto"] * $rg["tipoCambio"];
                }
                ?>

                <tr>
                    <td class="upTitles" colspan="4">Total:</td>
                    <td class="upTitles"><?= number_format($nLit, 2) ?></td>
                    <td class="upTitles"><?= number_format($nPes, 2) ?></td>                                                    
                    <td class="upTitles" style="color: #F63"><?= number_format($nImp, 2) ?></td>
                    <td class="upTitles"></td>
                    <td class="upTitles"><?= number_format($nDolares, 2) ?></td>
                    <td class="upTitles"></td>
                </tr>
            </table>
        </div>

        <?php echo $paginador->footer(false, null, false, false, 0, false); ?>

        <?php BordeSuperiorCerrar() ?>
        <?php PieDePagina(); ?>

        <script>
            $(document).ready(function () {
                $("#Cancelar").click(function () {
                    $("#BotonEnviar").val("Buscar");
                    $("#BotonEnviar").show();
                    $("#BotonAgregar").hide();
                    $("#Pagoreal").val("");
                    $("#PagorealSpan").html("$ 0.00");
                    $("#InicialB").hide();
                    $("#InicialA").show();
                    $("#Ticket").val("");
                    return false;
                });

                $("#BotonEnviar").click(function (e) {
                    e.preventDefault();
                    seekTicket();
                });
                $("#TicketValue").bind("keypress keydown keyup", function (e) {
                    if (e.keyCode === 13) {
                        e.preventDefault();
                        seekTicket();
                    }
                });

                function seekTicket() {
                    let ticket = $("#TicketValue").val();
                    let isla_pos = $("#IslaPosicion").val();
                    let corte = $("#Corte").val();

                    let pagoReal = $("#Pagoreal");
                    let boton = $("#BotonAgregar");
                    let botonE = $("#BotonEnviar");

                    if (ticket > 0) {
                        $.ajax({
                            url: "getTicket.php",
                            type: "post",
                            data: {"Ticket": ticket},
                            dataType: "json",
                            success: function (response) {
                                console.log(response);
                                console.log("Isla-Posicion: " + isla_pos);
                                console.log("Corte: " + corte);

                                if (response.corte !== corte) {
                                    alert("Error! el ticket: [" + ticket + "] corresponde a otro corte [" + response.corte + "]");
                                } else if (response.cliente > 0) {
                                    alert("El ticket ya ha sido asignado al cliente " + response.nombre + "[" + response.tipo + "]");
                                } else if (isla_pos > 0 && response.isla_pos !== isla_pos) {
                                    alert("El ticket no pertenece a la isla " + isla_pos + ". Isla: [" + response.isla_pos + "]");
                                } else if (response.tipo_venta !== "D") {
                                    alert("No se pueden agregar tickets marcados como jarreo, autojarreo o consignacion");
                                } else {
                                    $("#InicialB").show();
                                    $("#InicialA").hide();
                                    botonE.val("");
                                    botonE.hide();
                                    $("#TicketSpan").html(ticket);
                                    $("#Ticket").val(ticket);
                                    $("#PagorealSpan").html("$ " + response.importe);
                                    //pagoReal.val(response.importe);
                                    boton.show();
                                    pagoReal.focus();
                                }
                            },
                            error: function (jqXHR, ex) {
                                console.log("Status: " + jqXHR.status);
                                console.log("Uncaught Error.\n" + jqXHR.responseText);
                                console.log(ex);
                            }
                        });
                    } else {
                        alert("Ticket invalido");
                    }
                }

            });
        </script>

    </body>

</html>
