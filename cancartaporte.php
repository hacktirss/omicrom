<?php
#Librerias
session_start();
set_time_limit(720);
include_once ('comboBoxes.php');
include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("ConsultaCFDIClient.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$self = utils\HTTPUtils::self();

require './services/CartaPorteCanService.php';

if ($request->hasAttribute("busca")) {
    utils\HTTPUtils::setSessionValue("busca", $request->getAttribute("busca"));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue("busca", $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue("busca");
$Titulo = "Cancelacion de factura Carta Porte";

$pacA = $mysqli->query("SELECT * FROM proveedor_pac WHERE activo = 1");
$pac = $pacA->fetch_array();

$CiaDAO = new CiaDAO();
$Cia = $CiaDAO->retrieve(1);

$sql = "SELECT i.fecha, i.id_cli, i.status, 1 items, i.id,
        IF( facturas.version IS NOT NULL AND facturas.version != '3.2', ExtractValue(facturas.cfdi_xml, '/cfdi:Comprobante/cfdi:Impuestos/@TotalImpuestosTrasladados'), ExtractValue(facturas.cfdi_xml, '/cfdi:Comprobante/cfdi:Impuestos/@totalImpuestosTrasladados') ) cfditraslados,
        IF( facturas.version IS NOT NULL AND facturas.version != '3.2', ExtractValue(facturas.cfdi_xml, '/cfdi:Comprobante/@SubTotal'), ExtractValue(facturas.cfdi_xml, '/cfdi:Comprobante/@subTotal') ) cfdisubtotal,
        IF( facturas.version IS NOT NULL AND facturas.version != '3.2', ExtractValue(facturas.cfdi_xml, '/cfdi:Comprobante/@Total'), ExtractValue(facturas.cfdi_xml, '/cfdi:Comprobante/@total') ) cfditotal,
        IF( facturas.version IS NOT NULL AND facturas.version != '3.2', ExtractValue(facturas.cfdi_xml, '/cfdi:Comprobante/@Sello'), ExtractValue(facturas.cfdi_xml, '/cfdi:Comprobante/@sello')) sello,
        IF( facturas.version IS NOT NULL AND facturas.version != '3.2', ExtractValue(facturas.cfdi_xml, '/cfdi:Comprobante/cfdi:Emisor/@Rfc'), ExtractValue(facturas.cfdi_xml, '/cfdi:Comprobante/cfdi:Emisor/@rfc')) rfcEmisor,
        IF( facturas.version IS NOT NULL AND facturas.version != '3.2', ExtractValue(facturas.cfdi_xml, '/cfdi:Comprobante/cfdi:Receptor/@Rfc'), ExtractValue(facturas.cfdi_xml, '/cfdi:Comprobante/cfdi:Receptor/@rfc')) rfcReceptor,
        cia.rfc emisor, cli.nombre, cli.tipodepago, i.uuid, IFNULL( i.usr, 'Unknown' ) usr, i.serie, i.folio
  	FROM ingresos i
        JOIN cia ON TRUE
        LEFT JOIN cli ON cli.id = i.id_cli 
        LEFT JOIN facturas ON facturas.id_fc_fk = i.id AND facturas.uuid = i.uuid 
        WHERE i.id = " . $busca . " GROUP BY i.id";

$CpoA = $mysqli->query($sql);
if ($mysqli->error) {
    error_log($mysqli->error);
}
$Cpo = $CpoA->fetch_array();

$statusCFDI = array();
$ValCan = $Cpo['rfcReceptor'] === "XAXX010101000" ? "04" : "02";
$Cancelar = false;
if ($Cpo['status'] == 2 && (empty($Cpo['uuid']) || $Cpo['uuid'] === "-----")) {
    $Mensaje = "Factura cancelada sin timbrar";
} else if (!empty($Cpo['rfcEmisor']) && $Cpo['emisor'] != $Cpo['rfcEmisor']) {
    $Mensaje = "La factura no puede ser cancelada, el emisor del CFDI es distinto al de la Estación";
} else if ($Cpo['items'] > 1 && ($Cpo['rfcReceptor'] === "XAXX010101000" && $Cpo['tipodepago'] === "Contado")) {
    $Cancelar = true;
} else if (!empty($statusCFDI['EsCancelable']) && trim(strtoupper($statusCFDI['EsCancelable'])) == strtoupper('No Cancelable')) {
    $Mensaje = "El SAT indica que la factura no puede ser cancelada ya que tiene folios relacionados que deben ser cancelados previamente " . $relacionados;
} else if (!empty($statusCFDI['Estado']) && $statusCFDI['Estado'] === "Cancelado") {
    $Mensaje = "La factura ya ha sido cancelada";
} else {
    $Cancelar = true;
    if (!empty($statusCFDI['EsCancelable']) && contains($statusCFDI['EsCancelable'], "Cancelable con")) {
        $Mensaje = "<strong>Requiere autorización del receptor. Se enviará la solicitud de cancelación.</strong>";
    }

    if ($Cpo['tipodepago'] === "Prepago") {
        $pagoDAO = new PagoDAO();
        $pagoVO = $pagoDAO->retrieve($Cpo['relacioncfdi']);
        if ($pagoVO->getId() > 0) {
            if ($pagoVO->getStatus_pago() == 5) {
                $selectNc = "SELECT * FROM nc WHERE relacioncfdi = '$busca' AND status = '" . 1 . "'";
                if (($resultNc = $mysqli->query($selectNc)) && ($rg = $resultNc->fetch_assoc())) {
                    $Cancelar = false;
                    $Mensaje = "La factura no puede ser cancelada ya que ha sido asociada la nota de credito " . $rg["id"] . "";
                }
            }
        }
    } elseif ($Cpo['tipodepago'] === "Credito") {
        $sqlP = "SELECT IFNULL(GROUP_CONCAT(pagos.id),0) pagos 
                FROM pagos,pagose 
                WHERE pagos.id = pagose.id AND pagose.factura = '$busca'";
        $pagos = $mysqli->query($sqlP)->fetch_array();

        if (!empty($pagos["pagos"]) && $pagos["pagos"] != 0) {
            $Cancelar = false;
            $Mensaje = "La factura no puede ser cancelada ya que ha sido asociada a los pagos $pagos[0] y puede verse afectado el estado de cuenta";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#busca").val("<?= $busca ?>");
                $("#TipoCancelacion").val("<?= $ValCan ?>");
            });
        </script>
    </head>

    <body>
        <?php
        BordeSuperior();
        if ($pac['pruebas'] == '1') {
            ?>
            <div style="background-color: red; color: white; text-align:center; font-family: Helvetica, Arial, Verdana, Tahoma, sans-serif; font-size:14px; font-weight:bold;">
                ALERTA FACTURANDO EN MODO DE DEMOSTRACIÓN
            </div>
            <?php
        }
        ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="traslados.php"><div class="RegresarCss " alt="Flecha regresar" style="">Regresar</div></a>
                </td>
                <td style="vertical-align: top;">
                    <form name="form1" id="form1" method="post" action="">

                        <?php
                        cTable("99%", "0");
                        cInput("Id :", "Text", "5", "Id", "right", $busca, "40", false, true, "Folio de factura: <font color='red'><strong>" . (empty($Cpo['serie']) ? '' : $Cpo['serie'] . ' - ') . $Cpo['folio'] . "</strong></font>");
                        cInput("Fecha : ", "Text", "10", "Fecha", "right", $Cpo['fecha'], "10", true, true, "");
                        cInput("Cliente :", "Text", "05", "Cliente", "right", $Cpo['cliente'] . " - " . $Cpo['nombre'] . " [" . $Cpo["tipodepago"] . "]", "55", true, true, "");
                        cInput("Realizada por :", "Text", "5", "Usr", "right", strtoupper($Cpo['usr']), "5", true, true, "");
                        cInput("Importe :", "Text", "5", "Importe", "right", $Cpo['cfdisubtotal'], "5", true, true, "");
                        cInput("Total Traslados :", "Text", "5", "Iva", "right", $Cpo['cfditraslados'], "5", true, true, "");
                        cInput("Total :", "Text", "5", "Total", "right", $Cpo['cfditotal'], "5", true, true, "");
                        cInput("RFC emisor :", "Text", "15", "Rfc", "right", $Cpo['rfcEmisor'], "15", true, true, "");
                        cInput("RFC receptor:", "Text", "15", "Rfc", "right", $Cpo['rfcReceptor'], "15", true, true, "");
                        cInput("Folio Fiscal:", "Text", "40", "Uuid", "right", $Cpo['uuid'], "40", true, true, "");

                        if (!empty($Cpo['uuid']) && $Cpo['uuid'] !== "-----") {

                            cInput("Para su verificación fiscal:", "Text", "0", "Mensaje", "right", "<div class='nombre_cliente' style='cursor: pointer;' onclick=openInNewTab('https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx"
                                    . "?id=" . $Cpo['uuid']
                                    . "&re=" . $Cpo['rfcEmisor']
                                    . "&rr=" . $Cpo['rfcReceptor']
                                    . "&tt=" . $Cpo['cfditotal']
                                    . "&fe=" . substr($Cpo['sello'], strlen($Cpo['sello']) - 8, 8)
                                    . "')>https://verificacfdi.facturaelectronica.sat.gob.mx</a>", "0", true, true, "");

                            if (empty($statusCFDI['Error'])) {
                                if (!empty($statusCFDI['CodigoEstatus'])) {
                                    cInput("Código <strong>(SAT)</strong> :", "Text", "5", "Total", "right", $statusCFDI['CodigoEstatus'], "5", true, true, "");
                                }
                                if (!empty($statusCFDI['Estado'])) {
                                    cInput("Estatus del CFDI <strong>(SAT)</strong> :", "Text", "5", "Total", "right", $statusCFDI['Estado'] == "No Encontrado" ? $statusCFDI['Estado'] . " ( * ) " : $statusCFDI['Estado'], "5", true, true, "");
                                }
                                if (!empty($statusCFDI['EsCancelable'])) {
                                    cInput("Es Cancelable <strong>(SAT)</strong> :", "Text", "5", "Total", "right", $statusCFDI['EsCancelable'], "5", true, true, "");
                                }
                                if (!empty($statusCFDI['EstatusCancelacion'])) {
                                    cInput("Estatus Cancelación <strong>(SAT)</strong> :", "Text", "5", "Total", "right", $statusCFDI['EstatusCancelacion'], "5", true, true, "");
                                }
                            } else {
                                cInput("Estatus del CFDI <strong>(SAT)</strong> :", "Text", "5", "Total", "right", $statusCFDI['Error'], "5", true, true, "");
                            }
                        }

                        if ($Cancelar) {
                            ?>
                            <tr height='21' class='texto_tablas'>
                                <td align='right' bgcolor='#e1e1e1' class='nombre_cliente'>Tipo de cancelacion : &nbsp;</td>
                                <td>
                                    &nbsp;<select name="TipoCancelacion" id="TipoCancelacion" class="texto_tablas">
                                        <?php
                                        $arrayDatos = CatalogosSelectores::getMotivos_Cancelacion();
                                        foreach ($arrayDatos as $key => $value) {
                                            ?>
                                            <option value="<?= $key ?>"/><?= $value ?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>

                            <?php
                            cInput("Clave de cancelacion: ", "Password", "20", "Password", "right", '', "40", false, false, "<input type='submit' class='nombre_cliente' name='Boton' value='Cancelar'><br>$Mensaje", " placeholder='********'");
                        } else {
                            echo "<tr><td class='texto_tablas' colspan='2' style='text-align: center;color: red;'>$Mensaje</td></tr>";
                        }

                        cTableCie();
                        ?>
                        <input type='hidden' name='busca' id='busca'>
                        <div class="texto_tablas" style="text-align: left; width: 95%; padding: 3px;color: red">
                            <?php if (!empty($Cpo['uuid']) && $Cpo['uuid'] !== "-----" && contains($statusCFDI['Estado'], "No Encontrado")) { ?>
                                ( * )  El CFDI aún no se encuentra en los registros del SAT, esto puede tomar hasta 72 horas después de timbrado.<br/>Este tiempo de respuesta depende totalmente del SAT.
                            <?php } ?>
                        </div>
                    </form>
                </td>
            </tr>
        </table>
        <?php BordeSuperiorCerrar(); ?>
        <?php PieDePagina(); ?>

    </body>
</html>
<?php

function contains($original, $busqueda) {
    return strpos($original, $busqueda) !== FALSE;
}
