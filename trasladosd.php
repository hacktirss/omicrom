<?php
#Librerias
session_start();
set_time_limit(720);

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("comboBoxes.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();

require './services/TrasladosdService.php';
$tipo = utils\HTTPUtils::getSessionObject("Tipo");

if ($tipo == 1) {
    $httpDir = "TrasladosCartaPorte.php?busca=$cVarVal";
    $nameVariableSession = "CatalogoTrasladosdetalle";
    $session = new OmicromSession("td.id", "td.id", $nameVariableSession, $arrayFilter);
} else {
    $httpDir = "IngresosCartaPorte.php?busca=$cVarVal";
    $nameVariableSession = "CatalogoIngresosdetalle";
    $session = new OmicromSession("id.id", "id.id", $nameVariableSession, $arrayFilter);
}
if ($request->getAttribute("tipo") > 0) {
    utils\HTTPUtils::setSessionObject("Tipo", $request->getAttribute("tipo"));
}
$Gfmt = utils\HTTPUtils::getSessionBiValue("catalogoFacturas", "fmt");          //Formato
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$ciaDAO = new CiaDAO();
$ciaVO = $ciaDAO->retrieve(1);

$version_cfdi = $ciaVO->getVersion_cfdi();

$pacA = $mysqli->query("SELECT * FROM proveedor_pac WHERE activo = 1");
$pac = $pacA->fetch_array();
if ($tipo == 1) {
    $Titulo = "Detalle de Traslado";
    $cSQL = "SELECT t.id,t.total,t.ieps,t.iva,t.importe,t.cantidad,t.fecha FROM traslados t 
        LEFT JOIN traslados_detalle ON t.id = traslados_detalle.id
        WHERE t.id =" . $cVarVal;
} else {
    $Titulo = "Detalle de Ingreso";
    $cSQL = "SELECT i.id,i.total,i.ieps,i.iva,i.importe,i.cantidad,i.fecha FROM ingresos i 
        LEFT JOIN ingresos_detalle ON i.id = ingresos_detalle.id
        WHERE i.id =" . $cVarVal;
}
error_log($cSQL);
$HeA = $mysqli->query($cSQL);
$He = $HeA->fetch_array();

$clienteVO = new ClientesVO();
if ($tipo == 1) {
    if (is_numeric($cVarVal)) {
        $TrasladosDAO = new TrasladosDAO();
        $objectDVO = new TrasladosDetalleVO();
        $objectDVO = $trasladosDetalleDAO->retrieve($cVarVal);
        $objectVO = $TrasladosDAO->retrieve($cVarVal);
    }
    $Id = 142;
} elseif ($tipo == 2) {
    if (is_numeric($cVarVal)) {
        $IngresosDAO = new IngresosDAO();
        $objectDVO = new Ingresos_detalleVO();
        $objectDVO = $IngresosDetalleDAO->retrieve($cVarVal);
        $objectVO = $IngresosDAO->retrieve($cVarVal);
    }
    $Id = 157;
}

$vCorporativoDAO = new V_CorporativoDAO();
$vCorporativoVO = $vCorporativoDAO->retrieve(ListaLlaves::FACTURACION_ABIERTA, "llave");
$dd = $tipo == 1 ? "td" : "id";
$paginador = new Paginador($Id,
        "idnvo,rm.idcxc,com.descripcion,id_rm",
        " LEFT JOIN rm ON rm.id=id.id_rm "
        . "LEFT JOIN com ON com.clavei=rm.producto ",
        "",
        "$dd.id = '$cVarVal' AND $dd.producto > 0 ",
        $session->getSessionAttribute("sortField"),
        $session->getSessionAttribute("criteriaField"),
        "",
        strtoupper($session->getSessionAttribute("sortType")),
        $session->getSessionAttribute("page"),
        "",
        "traslados.php");
$tableContents = $paginador->getTableContents();

/**
 * 0.- Sin registros.
 * 1.- Tickets
 * 2.- Abiertas
 */
$ClientesDAO = new ClientesDAO();
$clienteVO = $ClientesDAO->retrieve($objectVO->getId_cli());

$Fecha_3 = date("Y-m-d", strtotime("-3 DAY"));
$FechaI = date("Y-m-") . "01";
$FechaF = date("Y-m-d");
$sql = "SELECT id FROM inv WHERE umedida = 'E48'";
$disp = $mysqli->query($sql)->fetch_array();
$Dt = is_numeric($disp["id"]) ? $disp["id"] : 0;
$self = utils\HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom.php"; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                var i = 0;
                setInterval(function () {
                    if (i % 2 == 0) {
                        $(".Continua").css({"background": "#E74C3C", "color": "white"});
                    } else {
                        $(".Continua").css({"background": "#F8C471", "color": "#2C3E50"});
                    }
                    i++;
                }, 1000);

                $("#AgregaPL").click(function () {
                    if ($("#NoPedido").val() > 0 && $("#NoSalida").val() > 0) {
                        Swal.fire({
                            title: "Ingresar  pedido o salida.",
                            icon: "error",
                            toast: true,
                            position: 'top-end',
                            confirmButtonColor: "#006666",
                            timer: 4000
                        });
                    } else {
                        var txt = $("#NoPedido").val() > 0 ? "l pedido" : " la venta";
                        var ValorInput = $("#NoPedido").val() > 0 ? $("#NoPedido").val() : $("#NoSalida").val();
                        var OrigenInput = $("#NoPedido").val() > 0 ? "P" : "V";
                        Swal.fire({
                            title: "Ingresar  el importe de" + txt + ", precio por litro",
                            icon: "question",
                            confirmButtonColor: "#006666",
                            input: "text",
                            showCancelButton: true
                        }).then((result) => {
                            console.log(result.value);
                            $.ajax({
                                type: "POST",
                                url: "getByAjax.php",
                                dataType: 'json',
                                data: {
                                    "Op": "CalculaRm",
                                    "Value": result.value,
                                    "id_Mov": ValorInput,
                                    "Origen": OrigenInput
                                },
                                success: function (data) {
                                    var datos = data;
                                    if (data.success) {
                                        Swal.fire({
                                            title: data.message,
                                            icon: "success",
                                            toast: true,
                                            position: 'top-end',
                                            confirmButtonColor: "#006666",
                                            timer: 4000
                                        });
                                        $("#formFacturacion2").submit();
                                    } else {
                                        Swal.fire({
                                            title: data.message,
                                            icon: "error",
                                            toast: true,
                                            position: 'top-end',
                                            confirmButtonColor: "#006666",
                                            timer: 4000
                                        });
                                    }
                                },
                                error: function (jqXHR, ex) {
                                    console.log("Status: " + jqXHR.status);
                                    console.log("Uncaught Error.\n" + jqXHR.responseText);
                                    console.log(ex);
                                }
                            });
                        });
                    }
                });
            });
        </script>
        <?php $paginador->script(); ?>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <?php if ($pac["pruebas"] == "1") { ?>
            <div   class="Factura_Modo_Demo">
                ALERTA MODO DE DEMOSTRACIÓN
            </div>
        <?php } ?>

        <div id="DatosEncabezado">
            <table aria-hidden="true">
                <tr>
                    <td><label>Id: </label><span style="margin-right: 15px;"><?= $cVarVal ?></span></td>
                    <td><label>Folio: </label><span class="naranja"><?= $objectVO->getFolio() ?></span></td>
                    <td><label>Nombre: </label><?= $tipo == 2 ? $clienteVO->getNombre() : $ciaVO->getCia() ?></td>
                    <td><label>RFC: </label><span><?= $tipo == 2 ? $clienteVO->getRfc() : $ciaVO->getRfc(); ?></span></td>
                    <td><label>Cantidad: </label><span class="naranja"><?= number_format($objectVO->getCantidad(), 2) ?></span></td>
                </tr>
                <tr>
                    <td colspan="2"><label>Fecha: </label><span><?= $He["fecha"] ?></span></td>
                    <td colspan="3"><label>Observaciones: </label><span><?= $objectVO->getObservaciones() ?></span></td>
                </tr>
            </table>
        </div>
        <table style="width: 100%" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="height : 280px !important; text-align : center !important; vertical-align: top !important;">
                    <div id="TablaDatos">
                        <table>
                            <tr style="background: white;">
                                <td>
                                    <table class="paginador" id='Tabla_Fac' aria-hidden="true" style="width: 100%;">
                                        <?php
                                        echo $paginador->headers(array(), array("Borrar"));
                                        $arrayComentarios = array();
                                        ?>
                                        <tbody>
                                            <?php
                                            while ($paginador->next()) {
                                                $row = $paginador->getDataRow();
                                                ?>
                                                <tr> 
                                                    <?php echo $paginador->formatRow(); ?>
                                                    <td style="text-align: center;">
                                                        <?php if ($objectVO->getUuid() === "-----") { ?>
                                                            <a href=javascript:borrarRegistro("<?= $self ?>","<?= $row["idnvo"] ?>","cId");><i class="icon fa fa-lg fa-trash" aria-hidden="true"></i></a>
                                                        <?php } ?>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </td>
                                <?php
                                if ($objectVO->getCantidad() > 0) {
                                    ?>
                                    <td style="width: 130px;">
                                        <a href="<?= $httpDir ?>"><div class="Continua " alt="Flecha de continuar" style="">Continuar</div></a>
                                    </td>
                                    <?php
                                }
                                ?>
                            </tr>
                        </table>
                    </div>
                    <?php
                    echo $paginador->footer(false, $nLink, true, true);
                    echo $paginador->filter();
                    ?>
                </td>
            </tr>
        </table>
        <?php if ($He["status"] == StatusFactura::ABIERTO && $objectVO->getUuid() === "-----") { ?>
            <div id="FormulariosBoots">
                <div class="container no-margin">
                    <form name="formFacturacion" id="formFacturacion" method="post" action="trasladosd.php">
                        <div class="row no-padding">
                            <div class="col-12 withBackground align-center" style="font-weight: bold;">Registrar venta</div>
                        </div>
                        <div class="row no-padding">
                            <div class="col-1 withBackground align-right">Producto:</div>
                            <div class="col-4 withBackground"><?php ComboboxInventario::generate("Producto", "'Aceites','Combustible','Otros'", "") ?></div>
                            <div class="col-1 withBackground align-right">Cnt:</div>
                            <div class="col-1 withBackground"><input type="text" id="Cantidad" name="Cantidad" placeholder="Cantidad" required></div>
                            <div class="col-1 withBackground align-right"></div>
                            <div class="col-1 withBackground align-right">Costo por litro:</div>
                            <div class="col-1 withBackground"><input type="text" id="PrecioxLitro" name="PrecioxLitro" placeholder="Costo" required></div>
                            <div class="col-2 withBackground">
                                <input type="submit" value="Agregar" id="ProductoLibre">
                                <input type="hidden" id="BotonHd" value="Agregar">
                                <input type="hidden" id="EdoCuenta" name="EdoCuenta">
                            </div>
                        </div>
                    </form>
                    <form name="formFacturacion2" id="formFacturacion2" method="post" action="trasladosd.php">
                        <div class="row no-padding">
                            <div class="col-12 withBackground align-center" style="font-weight: bold;">Seleccionar Venta</div>
                            <div class="col-6 withBackground align-right">Pedido:</div>
                            <div class="col-1 withBackground"><input type="number" name="NoPedido" id="NoPedido"></div>
                            <div class="col-1 withBackground align-right">Ó</div>
                            <div class="col-1 withBackground align-right">Ticket:</div>
                            <div class="col-1 withBackground"><input type="number" name="NoSalida" id="NoSalida"></div>
                            <div class="col-2 withBackground">
                                <input type="hidden" value="Agregar" name="Agregar Registro">
                                <input type="button" value="Agregal" id="AgregaPL" name="BotonSelecc">
                            </div>
                        </div>
                    </form>
                </div>            
            </div>            
            <?php
        }
        ?>
        <?php BordeSuperiorCerrar() ?>
        <?php PieDePagina() ?>
    </body>
</html>