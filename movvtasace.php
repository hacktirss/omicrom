<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("comboBoxes.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();

require_once "./services/ReportesVentasService.php";
require_once './services/CambioTurnoService.php';

$Titulo = "Venta de Aceites del corte $Corte ";
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$ctVO = new CtVO();
if ($Corte > 0) {
    $ctVO = $ctDAO->retrieve($Corte);
}


$cSql_1 = "
        SELECT man.isla_pos, vt.id,cli.id cliente,cli.nombre,cli.tipodepago,vt.clave,vt.descripcion,
        vt.posicion,vt.cantidad,vt.unitario costo,vt.total,TRIM(vt.uuid) uuid, vt.referencia,
        IFNULL(c.nombre,'') cliente_rm
        FROM 
        cli, man, vtaditivos vt
        LEFT JOIN rm ON vt.referencia = rm.id LEFT JOIN cli c ON rm.cliente = c.id 
        WHERE TRUE
        AND man.posicion = vt.posicion AND man.activo = 'Si'
        AND vt.corte = '$Corte' AND vt.cliente = cli.id  AND vt.tm = 'C' 
        AND cli.tipodepago IN ('Contado','Puntos') AND vt.cantidad > 0
        ";
if (is_numeric($IslaPosicion)) {
    $cSql_1 .= " AND man.isla_pos = $IslaPosicion";
}
$cSql_1 .= " ORDER BY vt.id ASC";

$cSql_2 = "
        SELECT man.isla_pos, vt.id,cli.id cliente,cli.nombre,cli.tipodepago,vt.clave,vt.descripcion,vt.uuid,
        vt.posicion,vt.cantidad,vt.unitario costo,vt.total,IFNULL(fc.id,0) folio, vt.referencia            
        FROM 
        cli, man, vtaditivos vt
        LEFT JOIN fc ON vt.uuid = fc.uuid AND fc.uuid <> '-----' AND fc.status = " . StatusFactura::CERRADO . "
        WHERE TRUE
        AND man.posicion = vt.posicion AND man.activo = 'Si'
        AND vt.corte = '$Corte' AND vt.cliente = cli.id AND vt.tm = 'C' AND cli.tipodepago NOT REGEXP 'Contado|Puntos'
        ";
if (is_numeric($IslaPosicion)) {
    $cSql_2 .= " AND man.isla_pos = $IslaPosicion";
}
$cSql_2 .= " ORDER BY vt.id ASC";

$cSql_3 = "
        SELECT cli.tipodepago tipo,SUM(vt.total) importe            
        FROM 
        cli, man, vtaditivos vt
        WHERE TRUE
        AND man.posicion = vt.posicion AND man.activo = 'Si'
        AND vt.corte = '$Corte' AND vt.cliente = cli.id AND vt.tm = 'C'                                                        
        ";
if (is_numeric($IslaPosicion)) {
    $cSql_3 .= " AND man.isla_pos = $IslaPosicion";
}
$cSql_3 .= " GROUP BY cli.tipodepago";

$self = utils\HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");
$returnLink = "movvtasace.php";
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php include './config_omicrom.php'; ?>    
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {

                var CerrarOrden = 0;
                $("form").submit(function (e) {
                    if (CerrarOrden > 0) {
                        e.preventDefault();
                        window.alert("Tu petición ha sido enviada, por favor esperé ... ");
                        return false;
                    }
                    CerrarOrden = 1;
                    return true;
                });

                var ultimoCliente = "";

                $("#returnLink").val("<?= $returnLink ?>");
                $("#IslaPosicion").val("<?= $IslaPosicion ?>");

                $("#Asignar").click(function () {
                    var pattern = /^\d+$/;
                    var value = $("#Ticket").val();
                    if (value === "") {
                        $("#MensajeAce").text("Favor de llenar el campo con un ticket de contado.");
                        $("#Ticket").focus();
                        return false;
                    } else if (!pattern.test(value)) {
                        $("#MensajeAce").text("Favor de ingresar un ticket valido.");
                        $("#Ticket").focus();
                        return false;
                    }
                    return true;
                });


                $("#Codigo").click(function () {
                    var cliente = $("#autocomplete").val();
                    if (cliente.length > 0 && (ultimoCliente === "" || cliente !== ultimoCliente)) {
                        ultimoCliente = cliente;
                        $.ajax({
                            url: "getTicket.php",
                            type: "post",
                            data: {Codigos: 1, Cliente: ultimoCliente},
                            dataType: "json",
                            beforeSend: function (xhr, opts) {
                                console.log("Limpiando lista para cliente: " + ultimoCliente);
                                $("#Codigos").empty();
                            },
                            success: function (response) {
                                var len = response.length;
                                for (var i = 0; i < len; i++) {
                                    var id = response[i]["descripcion"];
                                    $("#Codigos").append("<option value='" + id + "'></option>");
                                }
                            },
                            error: function (jqXHR, ex) {
                                console.log("Status: " + jqXHR.status);
                                console.log("Uncaught Error.\n" + jqXHR.responseText);
                                console.log(ex);
                            }
                        });
                    }
                });

                $("#autocomplete").activeComboBox(
                        $("[name=\"form1\"]"),
                        "SELECT id as data, CONCAT(id, ' | ' , tipodepago, ' | ' , nombre) value FROM cli " +
                        "WHERE TRUE AND cli.tipodepago NOT REGEXP 'Contado|Puntos'",
                        "nombre");
            });
        </script>

        <style>
            .form {
                width: 100%;
                margin-top: 0px;
                margin-bottom: 0px;
            }
            .form table{
                width: 100%;
                margin-top: 0px;
                margin-bottom: 0px;
            }
            .form td {
                background-color: #E1E1E1;
                height: 30px;
            }
        </style>
    </head>

    <body>

        <?php BordeSuperior() ?>
        <?php TotalizaCorte() ?>

        <div id="Controles">
            <form name="form1" id="form1" method="post" action="">
                <table aria-hidden="true">
                    <tbody>
                        <tr>
                            <td class="alignRight" style="width: 15%;">Isla o Dispensario</td>
                            <td>
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
                            </td>
                            <td></td>
                            <td></td>
                        </tr>                        
                    </tbody>
                </table>
            </form>
        </div>

        <div style="text-align: center" class="texto_tablas"><strong>Ventas de contado</strong></div>

        <div id="TablaDatos" style="min-height: 100px;">
            <table aria-hidden="true">
                <tr>
                    <td class="fondoNaranja">Ticket</td>
                    <td class="fondoNaranja">Consumo</td>
                    <td class="fondoNaranja">Fac</td>
                    <td class="fondoNaranja">Cta</td>
                    <td class="fondoNaranja">Cliente</td>
                    <td class="fondoNaranja">Clave</td>
                    <td class="fondoNaranja">Descripcion</td>
                    <td class="fondoNaranja">Isla</td>
                    <td class="fondoNaranja">Cnt</td>
                    <td class="fondoNaranja">Precio</td>
                    <td class="fondoNaranja">Importe</td>
                    <td class="fondoNaranja">Borrar</td>
                </tr>

                <?php
                $res1 = $mysqli->query($cSql_1);

                $nTarCredito = $nTarTarjeta = $nTarEfectivo = 0;
                while ($rg = $res1->fetch_array()) {
                    ?>
                    <tr>
                        <td align="right"><?= $rg["id"] ?></td>
                        <td align="right"><?= $rg["referencia"] ?></td>
                        <td style="text-align: center;">
                            <?php if ($rg["uuid"] !== "-----") { ?>
                                <a style="color: red;" href=javascript:winuni("enviafile.php?id=<?= $rg["uuid"] ?>&type=pdf&formato=0")><i class="icon fa fa-lg fa-file-pdf-o" aria-hidden="true"></i></a>
                            <?php } ?>
                        </td>
                        <td align="right"><?= $rg["cliente"] ?></td>
                        <td><?= substr(ucwords(strtolower($rg["nombre"])), 0, 30) ?></td>
                        <td align="right"><?= $rg["clave"] ?></td>
                        <td><?= substr(ucwords(strtolower($rg["descripcion"])), 0, 30) ?></td>
                        <td align="right"><?= number_format($rg["isla_pos"], 0) ?></td>
                        <td align="right"><?= number_format($rg["cantidad"], 0) ?></td>
                        <td align="right"><?= number_format($rg["costo"], 2) ?></td>
                        <td align="right"><?= number_format($rg["total"], 2) ?></td>

                        <td style="text-align: center;">
                            <?php if ($ctVO->getStatusctv() === StatusCorte::ABIERTO) { ?>
                                <a class="textosCualli_i_n" href=javascript:borrarRegistro("<?= $self ?>","<?= $rg["id"] ?>","tipo=A&returnLink=<?= $returnLink ?>&cId");><i class="icon fa fa-lg fa-trash" aria-hidden="true"></i></a>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>
        <br/>

        <div style="text-align: center;color: #990000;"><?= $Msj ?></div>

        <div style="text-align: center" class="texto_tablas"><strong>Ventas a crédito</strong></div>

        <div id="TablaDatos" style="min-height: 50px;">
            <table aria-hidden="true">
                <tr>
                    <td class="fondoNaranja">Ticket</td>
                    <td class="fondoNaranja">Consumo</td>
                    <td class="fondoNaranja">Fac</td>
                    <td class="fondoNaranja">Cta</td>
                    <td class="fondoNaranja">Cliente</td>
                    <td class="fondoNaranja">Clave</td>
                    <td class="fondoNaranja">Descripcion</td>
                    <td class="fondoNaranja">Isla</td>
                    <td class="fondoNaranja">Cnt</td>
                    <td class="fondoNaranja">Precio</td>
                    <td class="fondoNaranja">Importe</td>
                    <td class="fondoNaranja">Liberar</td>
                </tr>

                <?php
                $res2 = $mysqli->query($cSql_2);
                while ($rg = $res2->fetch_array()) {
                    ?>
                    <tr>
                        <td align="right"><?= $rg["id"] ?></td>
                        <td align="right"><?= $rg["referencia"] ?></td>
                        <td style="text-align: center;">
                            <?php if ($rg["folio"] !== "0") { ?>
                                <a style="color: red;" href=javascript:winuni("enviafile.php?id=<?= $rg["uuid"] ?>&type=pdf&formato=0")><i class="icon fa fa-lg fa-file-pdf-o" aria-hidden="true"></i></a>
                            <?php } ?>
                        </td>
                        <td align="right"><?= $rg["cliente"] ?></td>
                        <td><?= substr(ucwords(strtolower($rg["nombre"])), 0, 30) ?></td>
                        <td align="right"><?= $rg["clave"] ?></td>
                        <td><?= substr(ucwords(strtolower($rg["descripcion"])), 0, 30) ?></td>
                        <td align="right"><?= number_format($rg["isla_pos"], 0) ?></td>
                        <td align="right"><?= number_format($rg["cantidad"], 0) ?></td>
                        <td align="right"><?= number_format($rg["costo"], 2) ?></td>
                        <td align="right"><?= number_format($rg["total"], 2) ?></td>

                        <td style="text-align: center;">
                            <?php if ($ctVO->getStatusctv() === StatusCorte::ABIERTO) { ?>
                                <a class="textosCualli_i_n" href="<?= $self ?>?op=Liberar&cId=<?= $rg["id"] ?>&returnLink=<?= $returnLink ?>"><i class="icon fa fa-lg fa-trash" aria-hidden="true"></i></a>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>

        <br/>
        <table style="width: 100%;text-align: center;" class="texto_tablas" aria-hidden="true">
            <tr>
                <?php
                $result3 = $mysqli->query($cSql_3);
                while ($rg = $result3->fetch_array()) {
                    ?>
                    <td> <?= $rg["tipo"] ?>: $<?= number_format($rg["importe"], 2) ?></td>
                <?php } ?>
            </tr>
        </table>

        <?php echo $paginador->footer(false, null, false, false, 0, false); ?>

        <?php if ($ctVO->getStatusctv() === StatusCorte::ABIERTO) { ?>

            <div id="FormulariosBoots">
                <form name="form1" method="post" action="">
                    <div class="container no-margin">
                        <div class="row no-padding">
                            <div class="col-6 withBackground"><?= ListasCatalogo::getProductosByInventario("Clave", "'Aceites','Seguro','Servicio','Otros'", "required='required'", array("" => "<--------- Favor de seleccionar el producto -------->")); ?></div>
                            <div class="col-1 align-right withBackground">Isla o Disp.:</div>
                            <div class="col-1 withBackground"><?= ListasCatalogo::getIslaPosicion("Isla", "", "required='required'") ?></div>
                            <div class="col-1 align-right withBackground">Cnt.:</div>
                            <div class="col-1 withBackground"><input type="number" name="Cantidad" id="Cantidad"  min="1" value="1" required></div>
                            <div class="col-2 align-right withBackground"><input type="submit" name="Boton" value="Agregar como contado" id="Agregar"></div>
                        </div>
                    </div>
                    <input type="hidden" name="returnLink" id="returnLink">
                </form>
                <form name="form2" method="post" action="">
                    <div class="container no-margin">
                        <div class="row no-padding">
                            <div class="col-6 withBackground"><input type="search" placeholder="Buscar cliente" name="Cliente" id="autocomplete" required="required"></div>
                            <div class="col-2 withBackground"><input type="number" name="Ticket" id="Ticket" min="1" placeholder="#Ticket" required="required"></div>
                            <div class="col-2 withBackground">
                                <input type="text" id="Codigo" name="Codigo" list="Codigos" placeholder="Buscar código"  autocomplete="off">
                                <datalist id="Codigos"></datalist>
                            </div>
                            <div class="col-2 align-right withBackground"><input type="submit" name="Boton" value="Reasignar a cliente" id="Asignar"></div>
                        </div>
                    </div>
                    <input type="hidden" name="returnLink" id="returnLink">
                </form>
                <div class="container no-margin">
                    <div class="row no-padding">
                        <div class="col-12 warning" id="MensajeAce">Para poder reasignar una venta a un cliente, es necesario agregarlo primero como contado para obtener un número de ticket.</div>
                    </div>
                </div>
            </div>

        <?php } ?>

        <?php BordeSuperiorCerrar() ?>
        <?php PieDePagina() ?>

    </body>
</html>