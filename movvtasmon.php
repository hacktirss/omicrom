<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();

require_once './services/ReportesVentasService.php';
require_once './services/CambioTurnoService.php';

$Titulo = "Venta con Monederos del corte $Corte ";
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$ctVO = new CtVO();
if ($Corte > 0) {
    $ctVO = $ctDAO->retrieve($Corte);
}

$orden = "cliente";
if ($request->hasAttribute("orden")) {
    $orden = $request->getAttribute("orden");
}

$Ticket = 0;
$rmVO = new RmVO();
if ($request->hasAttribute("Ticket")) {
    $Ticket = $request->getAttribute("Ticket");
    $rmVO = $rmDAO->retrieve($Ticket);
}

$selectVentasMonedero = "SELECT man.isla_pos,
    rm.id,
    DATE_FORMAT(rm.fin_venta, '%d-%m-%Y %T' ) fin_venta,
    rm.cliente,
    cli.nombre as nombrec,
    cli.tipodepago,
    LOWER(com.descripcion) producto,
    rm.posicion,
    rm.volumen,
    ROUND(rm.pesos, 2) pesos,
    ROUND(rm.pagoreal, 2) pagoreal,
    rm.uuid,
    IFNULL(t.id, 0) trans,
    rm.vdm
FROM com,
    cli,
    man,
    rm
    LEFT JOIN transacciones t ON rm.id = t.ticket
WHERE 1 = 1
    AND com.clavei = rm.producto
    AND rm.cliente = cli.id
    AND rm.posicion = man.posicion
    AND man.activo = 'Si'
    AND rm.corte = '$Corte'
    AND cli.tipodepago IN ('Monedero','Reembolso') ";

if (is_numeric($IslaPosicion)) {
    $selectVentasMonedero .= " AND man.isla_pos = $IslaPosicion";
}
if ($Status !== "*") {
    if ($Status === "0") {
        $selectVentasMonedero .= " AND ROUND(rm.pesos,2) = ROUND(rm.pagoreal,2)";
    } elseif ($Status === "1") {
        $selectVentasMonedero .= " AND ROUND(rm.pesos,2) <> ROUND(rm.pagoreal,2)";
    }
}
$selectVentasMonedero .= "                
                   ORDER BY rm.$orden,rm.id";

$selectAceites = "  SELECT man.isla_pos,vt.id,vt.clave,inv.descripcion,vt.cantidad,
                    vt.unitario,vt.total,vt.posicion,cli.nombre, vt.referencia
                    FROM cli, man, vtaditivos vt
                    LEFT JOIN inv ON vt.clave = inv.id 
                    WHERE 1 =1 AND vt.cliente = cli.id AND vt.posicion = man.posicion AND man.activo = 'Si'
                    AND vt.tm = 'C' AND vt.corte = '$Corte'
                    AND cli.tipodepago IN ('Monedero')";
if (is_numeric($IslaPosicion)) {
    $selectAceites .= " AND man.isla_pos = $IslaPosicion";
}
$selectAceites .= "                      
                    ORDER BY vt.clave";

$selectClientes = " SELECT cli.id, cli.nombre FROM cli 
                    WHERE cli.tipodepago IN ('Monedero') AND cli.activo = 'Si' AND cli.observaciones NOT LIKE 'Autorizador' ORDER BY cli.nombre;";

$selectVendedor = " SELECT rm.vendedor,ven.alias FROM ven,rm 
                    WHERE rm.corte='$Corte' AND rm.vendedor = ven.id 
                    GROUP BY rm.vendedor;";

$registros = utils\IConnection::getRowsFromQuery($selectVentasMonedero);

$registrosACE = utils\IConnection::getRowsFromQuery($selectAceites);

$Clientes = utils\IConnection::getRowsFromQuery($selectClientes);

$Despachadores = utils\IConnection::getRowsFromQuery($selectVendedor);

$MonederosSeguro = utils\IConnection::execSql("SELECT valor FROM variables_corporativo WHERE llave = 'Monederos_Seguro';");
$Mseg = explode(",", $MonederosSeguro["valor"]);

$self = utils\HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");
$returnLink = "movvtasmon.php";
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php include './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script type="text/javascript">
            $(document).ready(function () {
                var orden = "<?= $orden ?>";

                $('input[name="orden"]').filter("[value='" + orden + "']").attr('checked', true);

                $("#autocomplete")
                        .addClass("texto_tablas")
                        .activeComboBox(
                                $("[name=\"form1\"]"),
                                "SELECT id as data, CONCAT(id, ' | ' , mid(nombre,1,50)) value FROM cli " +
                                "WHERE TRUE AND cli.tipodepago in ('Monedero') AND cli.activo = 'Si' ",
                                "nombre");

                $("#Corte").val("<?= $Corte ?>");
                $("#IslaPosicion").val("<?= $IslaPosicion ?>");
                $("#Status").val("<?= $Status ?>");
                $("#returnLink").val("<?= $returnLink ?>");
                $("#autocomplete").val("<?= html_entity_decode($SCliente) ?>");
                $("#InicialB").hide();

                $("#TicketValue").focus();

                $("#autocomplete").val("<?= html_entity_decode($Cliente) ?>");
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
                                </select>
                                <em></em>
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

                    <form name="form2" id="form2" method="post" action="">
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
                            <div class="col-1"><input type="text" name="Placas" placeholder="Placas" maxlength="20"></div>
                            <div class="col-1"><input type="text" name="Vdm" placeholder="Tirilla" maxlength="10"></div>
                            <div class="col-3">
                                <div class="content-select">
                                    <select name="Cliente" id="autocomplete" required="required">
                                        <option value="" selected="selected" disabled="">Seleccionar Monedero</option>
                                        <?php
                                        foreach ($Clientes as $Cli) {
                                            echo "<option value='" . $Cli["id"] . "'>" . ucwords(strtolower($Cli["nombre"])) . "</option>";
                                        }
                                        ?>
                                    </select>
                                    <em></em>
                                </div>
                            </div>
                            <div class="col-1 align-right"><span id="PagorealSpan">$ 0.00</span></div>
                            <div class="col-1 align-right">Pago/real:</div>
                            <div class="col-1"><input type="text" name="Pagoreal" id="Pagoreal"></div>
                            <div class="col-1"><input type="submit" name="Boton" value="Agregar" id="BotonAgregar"></div>
                            <div class="col-1 warning"><a href="movvtasmon.php" id="Cancelar" title="Cancelar operación"><i class="icon fa fa-lg fa-ban" aria-hidden="true" ></i></a></div>
                            <input type="hidden" name="Ticket" id="Ticket">
                        </div>
                        <input type="hidden" name="CorteValue" id="Corte">
                        <input type="hidden" name="returnLink" id="returnLink">
                        <input type="hidden" name="Tarjetas" value="1">
                        <input type="hidden" name="Monederos" value="1">
                    </form>
                    <?php
                }
                ?>
            </div>            
        </div>

        <div id="TablaDatos" style="min-height: 50px;">
            <table aria-hidden="true">
                <tr>
                    <td class="fondoNaranja">Tira</td>
                    <td class="fondoNaranja">Ticket</td>
                    <td class="fondoNaranja">Fecha</td>
                    <td class="fondoNaranja">Fac</td>
                    <td class="fondoNaranja">Cta</td>
                    <td class="fondoNaranja">Tipo</td>
                    <td class="fondoNaranja">Banco</td>
                    <td class="fondoNaranja">Producto</td>
                    <td class="fondoNaranja">Pos</td>
                    <td class="fondoNaranja">Litros</td>
                    <td class="fondoNaranja">Vta.real</td>
                    <td class="fondoNaranja">Pago C/tarjeta</td>
                    <td class="fondoNaranja">Borrar</td>
                </tr>

                <?php
                $des = $cli = "";
                $subI = $subV = $cont = 0;
                $nRng = 0;
                foreach ($registros as $rg) {
                    $style = "";

                    if ($rg["pesos"] <> $rg["pagoreal"] && abs($rg["pesos"] - $rg["pagoreal"]) > 0.01) {
                        $style = "background-color: #F7FF7C";
                    }
                    ?>
                    <tr style="<?= $style ?>">
                        <td align="right"><?= $rg["vdm"] ?></td>
                        <td align="right"><?= $rg["id"] ?></td>
                        <td align="center"><?= $rg["fin_venta"] ?></td>
                        <td style="text-align: center;">
                            <?php if (!empty($rg["uuid"]) && $rg["uuid"] !== FcDAO::SIN_TIMBRAR) { ?>
                                <a style="color: red;" href=javascript:winuni("enviafile.php?id=<?= $rg["uuid"] ?>&type=pdf&formato=0")><i class="icon fa fa-lg fa-file-pdf-o" aria-hidden="true"></i></a>
                            <?php } ?>
                        </td>
                        <td align="right"><?= $rg["cliente"] ?></td>
                        <td><?= $rg["tipodepago"] ?></td>
                        <td><?= substr(ucwords(strtolower($rg["nombrec"])), 0, 50) ?></td>
                        <td><?= ucwords(strtolower($rg["producto"])) ?></td>
                        <td align="right"><?= $rg["posicion"] ?></td>
                        <td align="right"><?= number_format($rg["volumen"], 2) ?></td>
                        <td align="right"><?= number_format($rg["pesos"], 2) ?></td>
                        <td align="right"><?= number_format($rg["pagoreal"], 2) ?></td>
                        <td style="text-align: center;">
                            <?php if ($ctVO->getStatusctv() === StatusCorte::ABIERTO && !(in_array($rg["cliente"], $Mseg))) { ?>
                                <?php if ($rg['trans'] == 0) { ?>
                                    <a class="textosCualli_i_n" href=javascript:borrarRegistro("<?= $self ?>","<?= $rg["id"] ?>","tipo=C&Tarjetas=1&returnLink=<?= $returnLink ?>&cId");><i class="icon fa fa-lg fa-trash" aria-hidden="true"></i></a>
                                <?php } else { ?>
                                    <?= $rg["trans"] ?>
                                <?php } ?>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php
                    $subI += $rg["pagoreal"];
                    $subV += $rg["volumen"];
                    $cont++;

                    if ($registros[$nRng + 1][$orden] !== $rg[$orden]) {
                        echo "<tr><td class='upTitles' colspan='1'>Transacciones</td></td><td class='upTitles' colspan='1'>" . number_format($cont, 0) . "</td><td class='upTitles' colspan='9'>Subtotal</td><td class='upTitles' colspan='1'>" . number_format($subV, 2) . "</td><td class='upTitles' colspan='2'>" . number_format($subI, 2) . "</td><td class='upTitles'></td></tr>";
                        $subI = $subV = $cont =0;
                    }
                    $nImpTar += $rg["pagoreal"];
                    $nLit += $rg["volumen"];
                    $nPes += $rg["pesos"];
                    $nRng++;
                }
                ?>


                <tr>
                    <td class="upTitlesSin" colspan="6"></td>
                    <td class="upTitlesSin">Diferencia: $ <?= number_format($nPes - $nImpTar, 2) ?></td>
                    <td class="upTitlesSin"></td>
                    <td class="upTitlesSin">Total</td>
                    <td class="upTitlesSin"><?= number_format($nLit, "2") ?></td>
                    <td class="upTitlesSin">$ <?= number_format($nPes, "2") ?></td>
                    <td class="upTitlesSin" style="color: #FF6600">$ <?= number_format($nImpTar, 2) ?></td>
                    <td class="upTitlesSin"></td>
                </tr>
            </table>
        </div>

        <div class="texto_tablas"  align="center">Venta de aceites con monederos</div>

        <div id="TablaDatos" style="min-height: 50px;">
            <table aria-hidden="true">
                <tr>
                    <td class="fondoNaranja">Posición</td>
                    <td class="fondoNaranja">Consumo</td>
                    <td class="fondoNaranja">Cliente</td>
                    <td class="fondoNaranja">Clave</td>
                    <td class="fondoNaranja">Descripcion</td>
                    <td class="fondoNaranja">Cnt</td>
                    <td class="fondoNaranja">Precio</td>
                    <td class="fondoNaranja">Importe</td>
                </tr>

                <?php
                foreach ($registrosACE as $rg) {

                    echo "<tr>";
                    echo "<td align='right'>" . $rg["posicion"] . "</td>";
                    echo "<td align='right'>" . $rg["referencia"] . "</td>";
                    echo "<td align='left'>" . substr(ucwords(strtolower($rg["nombre"])), 0, 30) . "</td>";
                    echo "<td align='right'>" . $rg["clave"] . "</td>";
                    echo "<td align='left'>" . substr(ucwords(strtolower($rg["descripcion"])), 0, 30) . "</td>";
                    echo "<td align='right'>" . number_format($rg["cantidad"], "0") . "</td>";
                    echo "<td align='right'>" . number_format($rg["unitario"], "2") . "</td>";
                    echo "<td align='right'>" . number_format($rg["total"], "2") . "</td>";
                    echo "</tr>";
                    $nImpAce += $rg["cantidad"] * $rg["unitario"];
                }
                ?>
                <tr>
                    <td class="upTitles" colspan="8"> <?= number_format($nImpAce, "2") ?></td>
                </tr>
            </table>
        </div>

        <div class="texto_tablas"  align="center" style="font-weight: bold;color: #FF6600">
            Total venta con monederos: $ <?= number_format($nImpTar + $nImpAce, "2") ?>
        </div>

        <?php echo $paginador->footer(false, null, false, false, 0, false); ?>

        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

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
                                } else if (response.cliente > 0 && response.tipo !== "Puntos") {
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
                                    pagoReal.val(response.importe);
                                    boton.show();
                                    $("#autocomplete").focus();
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
