<?php
#Librerias
session_start();
set_time_limit(300);

include_once ("auth.php");
include_once ("authconfig.php");
include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$mysqli = iconnect();

$arrayFilter = array("Fecha" => date("Y-m-d"), "Disponible" => "N", "Corte" => "",
    "Turno" => "*", "Posicion" => "*", "Producto" => "*");
$nameSession = "catalogoRemisiones";
$session = new OmicromSession("rm.id", "rm.id", $nameSession, $arrayFilter, "Filtros");
$usuarioSesion = getSessionUsuario();

/**
 * Valida las busquedas desde el visor de posiciones    
 */
if ($request->hasAttribute("Servicio")) {
    utils\HTTPUtils::setSessionBiValue($nameSession, "Posicion", $request->getAttribute("Posicion"));
}

foreach ($arrayFilter as $key => $value) {
    ${$key} = utils\HTTPUtils::getSessionBiValue($nameSession, $key);
}

$busca = $session->getSessionAttribute("criteria");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Id = 14;
$Titulo = "Despachos";

$conditions = "";
if (strpos($session->getSessionAttribute("criteriaField"), "rm.id") === false || empty($busca)) {
    if (!empty($Fecha)) {
        $conditions .= "rm.fecha_venta = '" . str_replace("-", "", $Fecha) . "'";
    } elseif (!empty($Corte) && $Corte > 0) {
        $conditions .= "rm.corte = $Corte";
        $Turno = "*";
    }

    if ($Posicion !== '*' && trim($Posicion) !== "") {
        $conditions .= " AND rm.posicion = '$Posicion'";
    }
    if ($Producto !== "*" && trim($Producto) !== "") {
        $conditions .= " AND rm.producto='$Producto' ";
    }
    if ($Turno !== '*' && trim($Turno) !== "") {
        $conditions .= " AND rm.turno = '$Turno'";
    }
    if ($Disponible === "S") {
        $conditions .= " AND rm.cliente = 0 and rm.uuid = '-----' AND rm.pesos > 0";
    }
}

$conditionsFrom = $conditions;
if ($usuarioSesion->getTeam() === PerfilesUsuarios::FACTURACION) {
    $VC1 = utils\IConnection::execSql("SELECT valor FROM variables_corporativo WHERE llave = 'limite_rm';");
    if (!empty($VC1["valor"]) && is_numeric($VC1["valor"])) {
        $conditionsFrom .= " ORDER BY rm.id DESC LIMIT " . $VC1["valor"];
    }
}

$paginador = new Paginador($Id,
        "rm.tipo_venta, rm.cliente ,rm.descuento ,rm.tipodepago, rm.fin_venta fecha, rm.codigo,
        rm.cliente, rm.corte, rm.comprobante, rm.uuid, ct.statusctv status,
        SUBSTR( UPPER( SHA1( CONCAT( '|', LPAD( rm.id, 7, '0' ), '|', LPAD( rm.posicion, 2, '0' ), '|', LPAD( rm.manguera, 2, '0' ), '|', LPAD( cia.idfae, 5, '0' ), '|', DATE_FORMAT( rm.fin_venta, '%Y-%m-%dT%H:%i:%s' ), '|', CAST( ROUND( rm.volumenv, 4 ) AS DECIMAL( 10, 4 ) ), '|', CAST( ROUND( rm.pesosv, 2 ) AS DECIMAL( 10, 2 ) ), '|' ) ) ), 1, 23 ) FOLIO_FAE",
        "
        LEFT JOIN cli ON rm.cliente = cli.id
        JOIN ct ON rm.corte = ct.id
        JOIN com ON rm.producto = com.clavei AND com.activo = 'Si'  
        LEFT JOIN cia ON TRUE",
        "",
        $conditions,
        $session->getSessionAttribute("sortField"),
        $session->getSessionAttribute("criteriaField"),
        utils\Utils::split($session->getSessionAttribute("criteria"), "|"),
        strtoupper($session->getSessionAttribute("sortType")),
        $session->getSessionAttribute("page"),
        "REGEXP",
        "",
        "(
        SELECT 
        aux.*, IF( DATE( aux.fin_venta ) = CURDATE() OR aux.importe = aux.pesosv OR (aux.uuid IS NOT NULL AND aux.uuid != '-----'), aux.pesosv, aux.importe) pesos, 
        IF( DATE( aux.fin_venta ) = CURDATE() OR aux.importe = aux.pesosv OR (aux.uuid IS NOT NULL AND aux.uuid != '-----'), 
        IF( ABS( aux.diferencia ) > 0, ROUND( ( aux.importec + IF( aux.desgloseieps = 'S', 0.00, aux.importeieps ) + aux.diferencia )/( aux.preciouu + IF( aux.desgloseieps = 'S', 0.0000, aux.ieps ) ), 4 ), aux.volumenv ),
        ROUND( aux.importe/aux.precio, 4 ) ) volumen
        FROM (
            SELECT
                IFNULL( cli.desgloseieps, 'N' ) desgloseieps, cli.tipodepago, rm.id, rm.tipo_venta,
                rm.posicion, rm.manguera, rm.fin_venta, rm.fecha_venta, rm.precio,rm.descuento,
                ROUND( rm.volumen, 4 ) volumenv, ROUND( rm.pesos, 2 ) pesosv, ROUND( rm.importe, 2 ) importe,
                rm.producto, rm.iva,  rm.ieps, rm.comprobante, rm.cliente, rm.placas, rm.codigo, rm.turno, rm.corte,
                rm.uuid, rm.kilometraje,
                ROUND((rm.precio-rm.ieps)/(1+rm.iva), 4) preciouu, ROUND(rm.volumen * ROUND((rm.precio-rm.ieps)/(1+rm.iva), 4 ), 2) importec, 
                ROUND(rm.volumen * ROUND((rm.precio-rm.ieps)/(1+rm.iva), 4 ) * rm.iva, 2 ) importeiva,
                ROUND(rm.volumen * rm.ieps, 2 ) importeieps,
                ROUND(rm.pesos, 2 ) - ROUND(rm.volumen * ROUND((rm.precio-rm.ieps)/(1+rm.iva), 4), 2) - ROUND(rm.volumen * ROUND((rm.precio-rm.ieps)/(1+rm.iva), 4 ) * rm.iva, 2) -ROUND(rm.volumen * rm.ieps, 2) diferencia
                FROM rm
                LEFT JOIN cli ON rm.cliente = cli.id
                WHERE TRUE 
                " . (!empty($conditionsFrom) ? "AND $conditionsFrom" : "") . "
            ) aux
        ) rm");

$self = utils\HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");
$cLink = substr($self, 0, strrpos($self, ".")) . 'e.php';
$cLinkd = substr($self, 0, strrpos($self, ".")) . 'd.php';

$VC1 = utils\IConnection::execSql("SELECT valor FROM variables_corporativo WHERE llave = 'url_fact_online';");
$VC2 = utils\IConnection::execSql("SELECT valor FROM variables_corporativo WHERE llave = 'fact_online_omicrom';");
$VC3 = utils\IConnection::execSql("SELECT valor FROM variables_corporativo WHERE llave = 'uso_corporativo';");

include_once './services/RemisionesService.php';
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {

                $("#Fecha").val("<?= $Fecha ?>").attr("size", "8").addClass("texto_tablas");
                $("#cFecha").css("cursor", "hand").click(function () {
                    displayCalendar($("#Fecha")[0], "yyyy-mm-dd", $(this)[0]);
                    $("#Corte").val("");
                });

                $("#Posicion").val("<?= $Posicion ?>").addClass("texto_tablas");
                $("#Producto").val("<?= $Producto ?>").addClass("texto_tablas");
                $("#Turno").val("<?= $Turno ?>").addClass("texto_tablas");
                $("#Corte").val("<?= $Corte ?>").addClass("texto_tablas");

                var Disponible = "<?= $Disponible ?>";
                if (Disponible === "S") {
                    $("#DisponibleS").prop("checked", true);
                    $("#DisponibleN").prop("checked", false);
                } else {
                    $("#DisponibleN").prop("checked", true);
                    $("#DisponibleS").prop("checked", false);
                }

                $("#Fecha").focus(function () {
                    $("#Corte").val("");
                });

                $("#Corte").focus(function () {
                    $("#Fecha").val("");
                });

                $("#form1").submit(function (event) {
                    if ($("#Fecha").val() !== "" || $("#Corte").val() !== "") {
                        //$("#message").text("Validated...").show();
                        return;
                    }

                    if ($("#Fecha").val() === "" && $("#Corte").val() === "") {
                        $("#message").text("Seleccione un corte o asigne una fecha!").show().fadeOut(3000);
                    }

                    event.preventDefault();
                });
            });
            function winminls(url) {
                windowMin = window.open(url, "miniwin", "width=460,height=500,left=200,top=120,location=no");
            }
            function winunils(url) {
                windowUni = window.open(url, "filtros", "status=no,tollbar=yes,scrollbars=yes,menubar=no,width=790,height=550,left=250,top=80");
            }
        </script>
        <?php $paginador->script(); ?>
    </head>

    <body>

        <?php BordeSuperior(); ?>
        <input  type="hidden" name="NameUser" id="NameUser" value="<?= $usuarioSesion->getNombre() ?>">
        <div id="TablaDatos">
            <table class="paginador" aria-hidden="true">
                <?php
                echo $paginador->headers(array("", "", "", "Corte"), array("dto", "Tipo", " "));
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    $Bonificado = $row["codigo"] <> 0 && $row["cliente"] == 0 ? "<strong style='color:#27AE60;' title='Codigo : " . $row["codigo"] . "'>B.</strong>" : false;
                    ?>
                    <tr>
                        <td style="text-align: center;"><a href=javascript:winminls("impticketdetick.php?busca=<?= $row["id"] ?>&op=1")><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></a></td>
                        <td style="text-align: center;">
                            <?php if ($row["tipo_venta"] === TipoVenta::NORMAL) { ?>
                                <a href="remisionese.php?busca=<?= $row["id"] ?>" title="Cambiar a pago con tarjeta o para un cliente">seleccionar</a>
                            <?php } ?>
                        </td>
                        <td style="text-align: center;">
                            <?php if (($row["tipodepago"] === TiposCliente::TARJETA || $row["tipodepago"] === TiposCliente::CONTADO || $row["tipodepago"] === TiposCliente::VALES) && $row["uuid"] === FcDAO::SIN_TIMBRAR && $row["tipo_venta"] === TipoVenta::NORMAL && ($row["comprobante"] > 0 || $VC3["valor"] == 1)) {
                                ?>
                                <?php
                                $Ticket = encodeFolio($row["FOLIO_FAE"]);
                                $Ticket2 = chunk_split($Ticket, 6, "-");
                                $Ticket3 = substr($Ticket2, 0, strlen($Ticket2) - 1);
                                $Url = $VC2["valor"] == 1 ? $VC1["valor"] . $Ticket3 : $VC1["valor"];
                                ?>
                                <a href=javascript:winunils("<?= $Url ?>");>facturar</a>
                            <?php } elseif ($row["uuid"] !== "-----" && $row["tipo_venta"] === TipoVenta::NORMAL) { ?>
                                <a style="color: red" href=javascript:winunils("enviafile.php?file=fc&id=<?= $row["uuid"] ?>&type=pdf");><i class="icon fa fa-lg fa-file-pdf-o" aria-hidden="true"></i></a>
                            <?php } ?>
                        </td>
                        <td style="text-align: center;"><?= $Bonificado ?> <?= $row["corte"] ?></td>
                        <?php echo $paginador->formatRow(); ?>
                        <?php $Disp = tipoVenta($row["tipo_venta"]); ?>
                        <td><?= $row["descuento"] ?></td>
                        <td style="text-align: center;">
                            <?php if ($usuarioSesion->getLevel() >= 8 && $row["cliente"] == 0) { ?>
                                <a href="remisionese.php?busca=<?= $row["id"] ?>" title="Tipo de despacho, Jarreo, Normal..."><?= $Disp ?></a>
                            <?php } else { ?>
                                <?= $Disp; ?>
                            <?php } ?>
                        </td>
                        <td><?= $row["comprobante"] ?></td>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>

        <?php
        $data = array("Nombre" => $Titulo, "Reporte" => 2,
            "Fecha" => $Fecha, "Corte" => $Corte,
            "Posicion" => $Posicion, "Producto" => $Producto,
            "Turno" => $Turno, "Disponible" => $Disponible,
            "busca" => $busca, "Criterio" => $session->getSessionAttribute("criteriaField"));
        $nLink = array("<i class=\"icon fa fa-lg fa-download\" aria-hidden=\"true\"></i> Exportar" => "report_excel.php?" . http_build_query($data));
        $GroupWork = utils\IConnection::execSql("SELECT groupwork FROM authuser WHERE id = " . $usuarioSesion->getIdUsuario());
        echo $paginador->footer($GroupWork["groupwork"] >= 1, $nLink, false, true);
        $BuscaPermiso = "SELECT valor FROM variables_corporativo WHERE llave = 'FacturacionFechas'";
        $rsBsq = utils\IConnection::execSql($BuscaPermiso);
        ?> 

        <?php if ($usuarioSesion->getTeam() !== PerfilesUsuarios::FACTURACION || $rsBsq["valor"] == 1) { ?>

            <?php
            echo $paginador->filter();
            echo "<div class='mensajes'>$Msj</div>";
            ?>
            <form name="form1" id="form1" method="post" action="">
                <table class="quicksearch" style="width: 100%;border-collapse: collapse; border: 1px solid #066;margin-top: 5px;" aria-hidden="true">
                    <tr>
                        <td style="text-align: left;"> &nbsp;
                            Fecha: 
                            <input type="text" id="Fecha" name="Fecha"> 
                            <img id="cFecha" src="libnvo/calendar.png" alt="Calendario">
                        </td>
                        <td>
                            &nbsp; &nbsp; Turno: 
                            <select name="Turno" class="nombre_cliente" id="Turno">

                                <?php
                                $sql = "SELECT '*' turno
                                    UNION
                                    SELECT turno FROM tur 
                                    WHERE activo='Si'";
                                $TurA = $mysqli->query($sql);
                                while ($Tur = $TurA->fetch_array()) {
                                    echo "<option value='" . $Tur["turno"] . "'>" . $Tur["turno"] . "</option>";
                                }
                                ?>
                            </select> 
                        </td>
                        <td>
                            &nbsp;&nbsp; Posicion: 
                            <select name="Posicion" class="nombre_cliente" id="Posicion">
                                <?php
                                $sql2 = "SELECT '*' posicion
                                    UNION
                                    SELECT posicion FROM man 
                                    WHERE activo='Si' ORDER BY posicion";
                                $ManA = $mysqli->query($sql2);
                                while ($Man = $ManA->fetch_array()) {
                                    echo "<option value='$Man[0]'>$Man[0]</option>";
                                }
                                ?>
                            </select>  
                        </td>
                        <td>
                            &nbsp;&nbsp; Prod: 
                            <select name="Producto" class="nombre_cliente" id="Producto">
                                <?php
                                $ComSql = "SELECT '*' producto 
                                        UNION 
                                        SELECT clavei producto FROM com 
                                        WHERE activo = 'Si'";
                                $ComA = $mysqli->query($ComSql);
                                while ($Com = $ComA->fetch_array()) {
                                    echo "<option value='$Com[0]'>$Com[0]</option>";
                                }
                                ?>
                            </select>
                        </td>
                        <td>
                            Corte: 
                            <input type="number" name="Corte"  min="1" max="10000" id="Corte" style="width: 50px;"> 
                        </td>
                        <td style="text-align: right;">
                            Disponibles:
                        </td>
                        <td>
                            Si<input type="radio" class="botonAnimatedMin" name="Disponible" id="DisponibleS" value="S" title="Muestra las ventas sin cliente y sin timbrar">
                            No<input type="radio" class="botonAnimatedMin" name="Disponible" id="DisponibleN" value="N" title="Muestra las ventas sin cliente y sin timbrar">
                        </td>
                        <td>
                            <input class="nombre_cliente" type="submit" name="Filtros" id="Filtros" value="Buscar">
                        </td>
                        <td><a href=javascript:winunils("impTicketEditado.php");><i class="fa fa-ticket" aria-hidden="true"></i></a></td>
                        <td>
                            <div style="display: inline-block; width: 100%;">
                                <?php
                                $SqlVr = "SELECT valor FROM variables_corporativo WHERE llave = 'cambia_importes'";
                                $SqlVrD = utils\IConnection::execSql($SqlVr);
                                $SqlPrm = "SELECT valor FROM variables_corporativo WHERE llave = 'UsuariosPermiso'";
                                $SqlPrmD = utils\IConnection::execSql($SqlPrm);
                                $UsrP = explode(",", $SqlPrmD["valor"]);
                                $ValP = false;
                                foreach ($UsrP as $Up) {
                                    if (!$ValP) {
                                        $ValP = $Up == $usuarioSesion->getId() ? true : false;
                                    }
                                }
                                if ($SqlVrD["valor"] === "Si" && $ValP) {
                                    ?>
                                    <i id="CambiodeImporte" class="fa fa-exchange" aria-hidden="true"></i>
                                    <?php
                                }
                                ?> 
                            </div>

                        </td>
                    </tr>
                </table>
                <span id="message" style="text-align: center;color: red;font-weight: bold"></span>
                <input type="hidden" name="pagina" value="1">

            </form>
        <?php } ?>
        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>
        <link rel="stylesheet" href="bootstrap/bootstrap-4.0.0/dist/css/bootstrap-modal.css" type="text/css">

        <?php include_once ("./bootstrap/modals/AjusteTicket.html"); ?>

        <script src="./bootstrap/controller/utils.js"></script>
        <script src="./bootstrap/controller/ajuste.js?var=<?= md5("bootstrap/controller/ajuste.js") ?>"></script>
    </body>
</html>
