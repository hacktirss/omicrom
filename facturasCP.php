<?php
session_start();
set_time_limit(720);

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$self = utils\HTTPUtils::self();

$Titulo = "Detalle por factura";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);


require_once './services/FacturasService.php';

$ciaDAO = new CiaDAO();

$Cia = $ciaDAO->retrieve(1);
$fcVO = new FcVO();
$clienteVO = new ClientesVO();
if (is_numeric($busca)) {
    $fcVO = $fcDAO->retrieve($busca);
    $clienteVO = $clientesDAO->retrieve($fcVO->getCliente());
}
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            function confirmarMov(url) {
                var msj = "En caso de se una factura abierta(no se procesaron tickets), se puede ver afectado su estado de cuenta\nDeseas continuar?";
                if (confirm(msj)) {
                    document.location.href = url;
                }
            }
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="facturas.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">
                    <form name="form1" id="form1" method="post" action="">

                        <?php
                        cTable("99%", "0");

                        cInput("Id :", "Text", "5", "Id", "right", $busca, "40", false, true, " <span style='background: #DADADA;padding-left: 5px' class='nombre_cliente'> Folio: </span> &nbsp;" . $fcVO->getFolio());
                        cInput("Fecha : ", "Text", "10", "Fecha", "right", $fcVO->getFecha(), "10", true, true, "");
                        cInput("Cliente :", "Text", "5", "Cliente", "right", $fcVO->getCliente() . " | " . $clienteVO->getNombre(), "5", true, true, "");
                        cInput("Realizada por :", "Text", "5", "Usr", "right", strtoupper($fcVO->getUsr()), "5", true, true, "");
                        cInput("Cantidad :", "Text", "5", "Cantidad", "right", $fcVO->getCantidad(), "5", true, true, "");
                        cInput("Iva :", "Text", "5", "Iva", "right", $fcVO->getIva(), "5", true, true, "");
                        cInput("Ieps :", "Text", "5", "Ieps", "right", $fcVO->getIeps(), "5", true, true, "");
                        cInput("Importe :", "Text", "5", "Importe", "right", $fcVO->getImporte(), "5", true, true, "");
                        cInput("Total :", "Text", "5", "Total", "right", $fcVO->getTotal(), "5", true, true, "");

                        if (!empty($fcVO->getUuid()) && $fcVO->getUuid() !== "-----") {

                            cInput("Para su verificacion fiscal:", "Text", "0", "Mensaje", "right", "<div class='nombre_cliente' style='cursor: pointer;' onclick=openInNewTab('https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx"
                                    . "?id=" . $fcVO->getUuid()
                                    . "&re=" . $Cia->getRfc()
                                    . "&rr=" . $clienteVO->getRfc()
                                    . "&tt=" . $fcVO->getTotal()
                                    . "&fe=" . substr($fcVO->getSello(), strlen($fcVO->getSello()) - 8, 8)
                                    . "')>https://verificacfdi.facturaelectronica.sat.gob.mx</div>", "0", true, true, "");
                        }

                        cInput("Folio fiscal:", "Text", "40", "Uuid", "right", $fcVO->getUuid(), "40", true, true, "");

                        if (!empty($fcVO->getUuid()) && $fcVO->getUuid() !== "-----") {
                            cInput("Enviar por correo: ", "Text", "40", "Correo", "right", $clienteVO->getCorreo(), "40", false, false, "<input class='nombre_cliente' type='submit' name='Boton' value='Enviar correo' class='texto_tablas'>");
                            if (!empty($clienteVO->getCorreo2())) {
                                cInput("Correo CC.:", "Text", "5", "Correo2", "right", $clienteVO->getCorreo2(), "5", true, true, "");
                            }
                        }
                        cTableCie();

                        echo "<input type='hidden' name='busca' id='busca' value='$busca'>";
                        ?>
                    </form>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <table style="width: 100%;padding-right: 15px;padding-left: 15px;" aria-hidden="true">
                        <?php
                        if (($clienteVO->getTipodepago() === TiposCliente::CREDITO || $clienteVO->getTipodepago() === TiposCliente::PREPAGO) && $fcVO->getUuid() !== "-----" && $fcVO->getStatus() == StatusFactura::CERRADO) {

                            $sql1 = "SELECT IFNULL(factura,0) var FROM cxch 
                                    WHERE factura = '" . $fcVO->getId() . "' AND cliente = '" . $fcVO->getCliente() . "' AND tm = 'C'";
                            $Cxch = utils\IConnection::execSql($sql1);

                            $sql2 = "SELECT IFNULL(factura,0) var FROM cxc 
                                    WHERE factura = '" . $fcVO->getId() . "' AND cliente = '" . $fcVO->getCliente() . "' AND tm = 'C'";
                            $Cxc = utils\IConnection::execSql($sql2);

                            $sql3 = "SELECT IFNULL(SUM(fcd.importe),0) suma FROM fcd WHERE fcd.id = '" . $fcVO->getId() . "' AND fcd.ticket > 0;";
                            $ticketsCxc = utils\IConnection::execSql($sql3);

                            $sql4 = "SELECT IFNULL(SUM(fcd.importe),0) suma FROM fcd WHERE fcd.id = '" . $fcVO->getId() . "' AND fcd.ticket = 0;";
                            $ticketsManuales = utils\IConnection::execSql($sql4);
                            //error_log(print_r($ticketsManuales, true));

                            if ($Cxch["var"] == $fcVO->getId()) {
                                echo "<tr class='texto_tablas'><td align='center'>Esta factura se encuentra saldada y en el Edo.de cuenta del historico &nbsp;</td></tr>";
                            } elseif ($Cxc["var"] == $fcVO->getId() && $ticketsCxc["suma"] == $fcVO->getTotal()) {
                                echo "<tr class='texto_tablas'><td align='center'>La factura ya se encuentra en Edo. de cuenta por el monto de " . $ticketsCxc["suma"] . "</td></tr>";
                            } else {

                                if ($ticketsManuales["suma"] > 0) {
                                    echo "<tr class='texto_tablas'><td colspan='3'>* La factura tiene registros manuales.</td></tr>";
                                }
                                if ($ticketsCxc["suma"] > 0 && $Cxc["var"] < $fcVO->getTotal()) {
                                    echo "<tr class='texto_tablas'><td colspan='3'>* Faltan tickets en el estado de cuenta.</td></tr>";
                                }
                                echo "<tr>";

                                if ($ticketsManuales["suma"] > 0) {
                                    echo "<td>";
                                    echo "<a href=javascript:confirmarMov('$self?op=Acentar0&busca=$busca') class='seleccionar' style='font-size:12px;'><strong>Asentar tickets manuales</strong></a>";
                                    echo "</td>";
                                }

                                echo "<td align='center'>";
                                echo "<a href=javascript:confirmarMov('$self?op=AcentarC&busca=$busca') class='seleccionar' style='font-size:12px;'><strong>Asentar tickets de contado</strong></a>";
                                echo "</td>";

                                echo "<td align='right'>";
                                echo "<a href=javascript:confirmarMov('$self?op=Acentar&busca=$busca') class='seleccionar' style='font-size:12px;'><strong>Asentar factura</strong></a>";
                                echo "<td>";

                                echo "</tr>";
                            }
                        }
                        ?>
                    </table>
                </td>
            </tr>
        </table>
        <?php
        BordeSuperiorCerrar();

        PieDePagina();
        ?>

    </body>

</html>
