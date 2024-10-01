<?php
set_time_limit(300);
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();

require_once './services/NotasCreditoService.php';

$Titulo = "Detalle de notas de credito";
$nameVarBusca = "busca";
if($request->hasAttribute($nameVarBusca)){
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif($request->hasAttribute("id")){
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);


$ciaVO = $ciaDAO->retrieve(1);

$ncVO = new NcVO();
$clienteVO = new ClientesVO();
if (is_numeric($busca)) {
    $ncVO = $ncDAO->retrieve($busca);
    $clienteVO = $clientesDAO->retrieve($ncVO->getCliente());
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
            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>
        
        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center; width: 100px;" class="nombre_cliente">
                    <a href="notascre.php"><div class="RegresarCss " alt="Flecha regresar" style="">Regresar</div></a>
                </td>
                <td style="vertical-align: top;">
                    <form name="form1" id="form1" method="post" action="">

                        <?php
                        cTable("99%", "0");

                        cInput("Id :", "Text", "5", "Id", "right", $busca, "40", false, true, " <span style='background: #DADADA;padding-left: 5px' class='nombre_cliente'> Factura: </span> &nbsp;" . $ncVO->getFactura());
                        cInput("Fecha : ", "Text", "10", "Fecha", "right", $ncVO->getFecha(), "10", true, true, "");
                        cInput("Cliente :", "Text", "5", "Cliente", "right", $ncVO->getCliente() . " | " . $clienteVO->getNombre(), "5", true, true, "");
                        cInput("Cantidad :", "Text", "5", "Cantidad", "right", $ncVO->getCantidad(), "5", true, true, "");
                        cInput("Iva :", "Text", "5", "Iva", "right", $ncVO->getIva(), "5", true, true, "");
                        cInput("Ieps :", "Text", "5", "Ieps", "right", $ncVO->getIeps(), "5", true, true, "");
                        cInput("Importe :", "Text", "5", "Importe", "right", $ncVO->getImporte(), "5", true, true, "");
                        cInput("Total :", "Text", "5", "Total", "right", $ncVO->getTotal(), "5", true, true, "");
                        cInput("Folio fiscal:", "Text", "40", "Uuid", "right", $ncVO->getUuid(), "40", true, true, "");

                        if ($ncVO->getUuid() !== NcDAO::SIN_TIMBRAR) {

                            cInput("Para su verificacion fiscal:", "Text", "0", "Mensaje", "right", "<a class='textosCualli' target='_BLANK' href='https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx"
                                    . "?id=" . $ncVO->getUuid()
                                    . "&re=" . $ciaVO->getRfc()
                                    . "&rr=" . $clienteVO->getRfc()
                                    . "&tt=" . $ncVO->getTotal()
                                    . "&fe=" . substr($ncVO->getSello(), strlen($ncVO->getSello()) - 8, 8)
                                    . "'>https://verificacfdi.facturaelectronica.sat.gob.mx</a>", "0", true, true, "");

                            cInput("Enviar por correo: ", "Text", "40", "Correo", "right", $clienteVO->getCorreo(), "40", false, false, "<input class='nombre_cliente' type='submit' name='Boton' value='Enviar correo' class='texto_tablas'>");
                            if (!empty($clienteVO->getCorreo2())) {
                                cInput("Correo CC.:", "Text", "5", "Correo2", "right", $clienteVO->getCorreo2(), "5", true, true, "");
                            }
                        }

                        cTableCie();
                        ?>
                        <input type='hidden' name='busca' id='busca'>
                    </form>
                </td>
            </tr>

        </table>

        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>

</html>
