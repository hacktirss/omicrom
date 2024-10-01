<?php
session_start();
set_time_limit(720);

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("comboBoxes.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$self = utils\HTTPUtils::self();

$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);
$tipo = utils\HTTPUtils::getSessionObject("Tipo");
require_once './services/TrasladosService.php';
$ciaDAO = new CiaDAO();
$ciaVO = $ciaDAO->retrieve(true);
$Cia = $ciaDAO->retrieve(1);
$clientesDAO = new ClientesDAO();
$clientesVO = new ClientesVO();

if ($tipo != 2) {
    $Titulo = "Detalle de traslado";
    $ObjectVO = new TrasladosVO();
    $ObjectDAO = new TrasladosDAO();
} else if ($tipo == 2) {
    $Titulo = "Detalle de ingreso";
    $ObjectVO = new IngresosVO();
    $ObjectDAO = new IngresosDAO();
}
if (is_numeric($busca)) {
    $ObjectVO = $ObjectDAO->retrieve($busca);
}
$clientesVO = $clientesDAO->retrieve($ObjectVO->getId_cli());
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#Id").val("<?= $busca ?>");
                $("#Folio").val("<?= $ObjectVO->getFolio() ?>");
                $("#Cliente").val("<?= $ciaVO->getEstacion() ?>");
                $("#Fecha").val("<?= $ObjectVO->getFecha() ?>");
                $("#Realizada").val("<?= $ObjectVO->getUsr() ?>");
                $("#Cantidad").val("<?= $ObjectVO->getCantidad() ?>");
                $("#Observaciones").val("<?= $ObjectVO->getObservaciones() ?>");
                $("#Uuid").val("<?= $ObjectVO->getUuid() ?>");
                $("#Metododepago").val("<?= $ObjectVO->getMetodoPago() ?>");
<?php
if ($tipo == 1) {
    ?>
                    $("#Formadepago").val("<?= $ObjectVO->getFormaPago() ?>");
                    $("#cuso").val("<?= $ObjectVO->getUsoCfdi() ?>");
    <?php
} else {
    ?>
                    $("#Formadepago").val("<?= $ObjectVO->getFormadepago() ?>");
                    $("#cuso").val("<?= $ObjectVO->getUsocfdi() ?>");
    <?php
}
?>
            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="traslados.php"><div class="RegresarCss " alt="Flecha regresar" style="">Regresar</div></a>
                </td>
                <td style="vertical-align: top;">
                    <form name="form1" id="form1" method="post" action="">
                        <div id="FormulariosBoots">
                            <div class="container no-margin">
                                <div class="row no-padding">
                                    <div class="col-10 background no-margin">
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Id: </div>
                                            <div class="col-2"><input type="text" name="Id" id="Id" class="clase-1" placeholder="" disabled/></div>
                                            <div class="col-1 align-right">Folio: </div>
                                            <div class="col-2"><input type="text" name="Folio" id="Folio" class="clase-1" placeholder="" disabled/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Cliente: </div>
                                            <div class="col-5"><input type="text" name="Cliente" id="Cliente" class="clase-1" placeholder="" disabled/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Fecha:</div>
                                            <div class="col-3"><input type="text" name="Fecha" id="Fecha" class="clase-1" placeholder="" disabled/></div>
                                        </div>  
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Realizada por: </div>
                                            <div class="col-3"><input type="text" name="Realizada" id="Realizada" class="clase-1" placeholder="" disabled/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Cantidad: </div>
                                            <div class="col-3"><input type="text" name="Cantidad" id="Cantidad" class="clase-1" placeholder="" disabled/></div>
                                        </div>
                                        <?php
                                        if ($tipo == 2) {
                                            ?>
                                            <div class="row no-padding">
                                                <div class="col-4 align-right">Metodo de pago: </div>
                                                <div class="col-6"><?php ComboboxMetodoDePago::generate("Metododepago", "250px"); ?></div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-4 align-right">Forma de pago: </div>
                                                <div class="col-6"><?php ComboboxFormaDePago::generate("Formadepago", "250px"); ?></div>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Uso CFDI: </div>
                                            <?php
                                            if ($tipo == 1) {
                                                ?>
                                                <div class="col-3"><?= ComboboxUsoCFDI::generateByTypeCli("cuso", strlen($ciaVO->getRfc())); ?></div>
                                                <?php
                                            } else {
                                                ?>
                                                <div class="col-3"><?= ComboboxUsoCFDI::generateByTypeCli("cuso", strlen($clientesVO->getRfc())); ?></div>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Observaciones: </div>
                                            <div class="col-6"><input type="text" name="Observaciones" id="Observaciones"></div>
                                        </div>
                                        <?php if (!empty($ObjectVO->getUuid()) && $ObjectVO->getUuid() !== "-----") { ?>
                                            <div class="row no-padding">
                                                <div class="col-4 align-right">Para su verificacion fiscal: </div>

                                                <div class="col-4 nombre_cliente align-right" onclick=openInNewTab('https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?id=<?= $ObjectVO->getUuid() ?>&re=<?= $ciaVO->getRfc() ?>&rr=<?= $ciaVO->getRfc() ?>&tt=<?= $ObjectVO->getTotal() ?>&fe=<?= substr($ObjectVO->getSello(), strlen($ObjectVO->getSello()) - 8, 8) ?>'); >
                                                    https://verificacfdi.facturaelectronica.sat.gob.mx
                                                </div>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Folio fiscal: </div>
                                            <div class="col-5"><input type="text" name="Uuid" id="Uuid" class="clase-1" placeholder="" disabled/></div>
                                        </div>
                                        <?php
                                        if ($ObjectVO->getUuid() === "-----") {
                                            ?>
                                            <div class="row no-padding">
                                                <div class="col-5"></div>
                                                <div class="col-7"><input type="submit" name="BotonA" value="Actualizar"> </div>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                        <input type='hidden' name='busca' id='busca' value='<?= $busca ?>'>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>                  
                    <div style="width: 100%;padding-left: 70%;">
                        <a href="trasladosd.php?criteria=ini&cVarVal=<?= $busca ?>"><div class="Continua " alt="Flecha de continuar" style="">Continuar</div></a>
                    </div>
                </td>
            </tr>
        </table>
        <?php
        BordeSuperiorCerrar();

        PieDePagina();
        ?>

    </body>

</html>
