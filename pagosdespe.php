<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("comboBoxes.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$self = utils\HTTPUtils::self();

$Titulo = "Pagos a despachadores";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

include_once "./services/PagosDespachadorService.php";

$objectVO = new PagosDespVO();
$objectVO->setDeposito(date("Y-m-d"));
$objectVO->setConcepto("ABONO A CUENTA");
if (is_numeric($busca)) {
    $objectVO = $pagosDespDAO->retrieve($busca);
}

$selectVendedores = "SELECT ven.id, ven.nombre FROM ven WHERE ven.id >= 50 AND ven.activo = 'Si' ORDER BY ven.nombre;";
$arrayVendedores = utils\IConnection::getRowsFromQuery($selectVendedores)
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>        
        <script>
            $(document).ready(function () {
                let busca = "<?= $busca ?>";
                let status = "<?= $objectVO->getStatus() ?>";

                if (busca === "NUEVO") {
                    $("#Boton").val("Agregar");
                } else if (status === "<?= StatusPagoDespachador::ABIERTO ?>") {
                    $("#Boton").val("Actualizar");
                } else if (status === "<?= StatusPagoDespachador::CERRADO ?>") {
                    $("#Boton").val("Cancelar");
                    $("#Password").focus();
                } else {
                    $("#Boton").hide();
                }

                $("#busca").val(busca);
                $("#Id").val(busca).prop("disabled", true);
                $("#Vendedor").val("<?= $objectVO->getVendedor() ?>");
                $("#Fecha").val("<?= $objectVO->getDeposito() ?>");
                $("#cFecha").css("cursor", "hand").click(function () {
                    displayCalendar($("#Fecha")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#Concepto").val("<?= $objectVO->getConcepto() ?>").toUpperCase();
                $("#Importe").val("<?= $objectVO->getImporte() ?>");
                $("#Status").val("<?= statusLetra($objectVO->getStatus()) ?>").prop("disabled", true);
            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="pagosdesp.php"><div class="RegresarCss " alt="Flecha regresar" style="">Regresar</div></a>
                </td>
                <td style="vertical-align: top;">
                    <form name="formulario1" id="formulario1" method="post" action="" autocomplete="off">
                        <div id="FormulariosBoots">
                            <div class="container no-margin">
                                <div class="row no-padding">
                                    <div class="col-3 align-right withBackground required">Id:</div>
                                    <div class="col-1"><input type="text" name="Id" id="Id" class="clase-1"/></div>
                                    <div class="col-1 align-right withBackground">Status:</div>
                                    <div class="col-2"><input type="text" name="Status" id="Status" class="clase-1"/></div>
                                </div>
                                <div class="row no-padding">
                                    <div class="col-3 align-right withBackground required">No.cuenta:</div>
                                    <div class="col-4">
                                        <select name="Vendedor" id="Vendedor" required="">
                                            <option selected="selected" disabled="" value="">SELECCIONAR VENDEDOR</option>
                                            <?php
                                            if (is_array($arrayVendedores) && count($arrayVendedores) > 0) {
                                                foreach ($arrayVendedores as $ven) {
                                                    ?>
                                                    <option value="<?= $ven["id"] ?>" label="<?= $ven["id"] . " | " . $ven["nombre"] ?>">
                                                        <?php
                                                    }
                                                }
                                                ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row no-padding">
                                    <div class="col-3 align-right withBackground required">Fecha de pago:</div>
                                    <div class="col-2"><input type="text" name="Fecha" id="Fecha" maxlength="10" class="clase-1" placeholder="" required=""/></div>
                                    <div class="col-1"><i class="icon fa fa-lg fa-calendar" id="cFecha" aria-hidden="true"></i></div>
                                </div>
                                <div class="row no-padding">
                                    <div class="col-3 align-right withBackground required">Concepto:</div>
                                    <div class="col-4"><input type="text" name="Concepto" id="Concepto" maxlength="64" class="clase-1" placeholder="" required=""/></div>
                                </div>
                                <div class="row no-padding">
                                    <div class="col-3 align-right withBackground required">Importe:</div>
                                    <div class="col-4"><input type="text" name="Importe" id="Importe" class="clase-1" placeholder="" required=""/></div>
                                </div>
                                <?php if ($objectVO->getStatus() == StatusPagoDespachador::CERRADO) { ?>
                                    <div class="row no-padding">
                                        <div class="col-3 align-right withBackground required">Cancelar pago:</div>
                                        <div class="col-4">
                                            <input type="password" name="Password" id="Password" class="clase-1" placeholder="Ingresar clave de cancelacion" required="" autocomplete="off"/>
                                        </div>
                                    </div>
                                <?php } ?>
                                <div class="row no-padding">
                                    <div class="col-3 align-right"></div>
                                    <div class="col-4"><input type="submit" name="Boton" id="Boton"/></div>
                                </div>
                            </div>
                            <input type="hidden" name="busca" id="busca"/>
                        </div>
                        <div style="text-align: left;">(<sup><i style="color: red;font-size: 8px;" class="fa fa-lg fa-asterisk" aria-hidden="true"></i></sup>) Campos necesarios para control de venta</div>
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

