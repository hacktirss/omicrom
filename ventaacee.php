<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require_once "./services/VentaAditivosService.php";

$request = utils\HTTPUtils::getRequest();

$Titulo = "Aditivos detalle";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

$objectVO = new VentaAditivosVO();
$clienteVO = new ClientesVO();
if (is_numeric($busca)) {
    $objectVO = $objectDAO->retrieve($busca);
    $clienteVO = $clientesDAO->retrieve($objectVO->getCliente());
}

$SCliente = $clienteVO;
if ($request->hasAttribute("Cliente")) {
    $SeachCliente = $request->getAttribute("Cliente");
    $Cliente = strpos($SeachCliente, "|") > 0 ? trim(substr($SeachCliente, 0, strpos($SeachCliente, "|"))) : trim($SeachCliente);
    $SCliente = $clientesDAO->retrieve($Cliente);

    $selectCodigos = "SELECT id, CONCAT(codigo, ' | ', TRIM(impreso), ' | ', TRIM(descripcion) , ' | ', TRIM(placas)) descripcion
                    FROM unidades WHERE cliente = '$Cliente' AND LOWER(estado) = 'a'
                    ORDER BY impreso";
    $Codigos = utils\IConnection::getRowsFromQuery($selectCodigos);
} elseif ($clienteVO->getId() > 0) {
    $selectCodigos = "SELECT id, CONCAT(codigo, ' | ', TRIM(impreso), ' | ', TRIM(descripcion) , ' | ', TRIM(placas)) descripcion
                    FROM unidades WHERE cliente = '" . $clienteVO->getId() . "' AND LOWER(estado) = 'a'
                    ORDER BY impreso";
    $Codigos = utils\IConnection::getRowsFromQuery($selectCodigos);
}
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>

        <script>
            $(document).ready(function () {
                let busca = "<?= $busca ?>";

                if (busca === "NUEVO") {
                    $("#Boton").val("Agregar");
                } else {
                    $("#Boton").val("Guardar");
                }

                $("#busca").val(busca);
                $("#Id").val("<?= $busca ?>");
                $("#Corte").val("<?= $objectVO->getCorte() ?>");
                $("#Posicion").val("<?= $objectVO->getPosicion() ?>");
                $("#Fecha").val("<?= $objectVO->getFecha() ?>");
                $("#Producto").val("<?= $objectVO->getProducto() ?>");
                $("#UUID").val("<?= $objectVO->getUuid() ?>");
                $("#Unitario").val("<?= number_format($objectVO->getUnitario(), 2) ?>");
                $("#Cantidad").val("<?= $objectVO->getCantidad() ?>");
                $("#Total").val("<?= $objectVO->getTotal() ?>");
                $("#Referencia").val("<?= $objectVO->getReferencia() ?>");


                $("#Cliente").val("<?= $objectVO->getCliente() ?>");
                $("#Codigo").val("<?= $objectVO->getDatalist() ?>");

            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="ventaace.php"><div class="RegresarCss " alt="Flecha regresar" style="">Regresar</div></a>
                </td>
                <td style="vertical-align: top;">
                    <form name="formulario1" id="formulario1" method="post" action="">
                        <div id="FormulariosBoots">
                            <div class="container no-margin">
                                <div class="row no-padding">
                                    <div class="col-3 align-right withBackground required">Id:</div>
                                    <div class="col-4"><input type="text" name="Id" id="Id" disabled=""></div>
                                </div>
                                <div class="row no-padding">
                                    <div class="col-3 align-right withBackground required">Corte:</div>
                                    <div class="col-4"><input type="text" name="Corte" id="Corte" disabled=""></div>
                                </div>
                                <div class="row no-padding">
                                    <div class="col-3 align-right withBackground required"># Posicion:</div>
                                    <div class="col-4"><?= ListasCatalogo::getPosiciones("Posicion", "", " disabled"); ?></div>
                                </div>
                                <div class="row no-padding">
                                    <div class="col-3 align-right withBackground required">Fecha de operación:</div>
                                    <div class="col-4"><input type="text" name="Fecha" id="Fecha" disabled=""></div>
                                </div>
                                <div class="row no-padding">
                                    <div class="col-3 align-right withBackground required">Producto:</div>
                                    <div class="col-4"><?= ListasCatalogo::getProductosByInventario("Producto", "'Aceites'", " disabled"); ?></div>
                                </div>
                                <div class="row no-padding">
                                    <div class="col-3 align-right withBackground required">UUID:</div>
                                    <div class="col-4"><input type="text" name="UUID" id="UUID" disabled=""></div>
                                </div>
                                <div class="row no-padding">
                                    <div class="col-3 align-right withBackground required">Precio Unitario:</div>
                                    <div class="col-2"><input type="text" name="Unitario" id="Unitario" disabled=""></div>
                                </div>
                                <div class="row no-padding">
                                    <div class="col-3 align-right withBackground required">Cantidad:</div>
                                    <div class="col-2"><input type="text" name="Cantidad" id="Cantidad" disabled=""></div>
                                </div>
                                <div class="row no-padding">
                                    <div class="col-3 align-right withBackground required">Total:</div>
                                    <div class="col-2"><input type="text" name="Total" id="Total" disabled=""></div>
                                </div>
                                <div class="row no-padding">
                                    <div class="col-3 align-right withBackground required">Referencia:</div>
                                    <div class="col-2"><input type="text" name="Referencia" id="Referencia" disabled=""></div>
                                </div>
                                <div class="row no-padding">
                                    <div class="col-3 align-right withBackground required">Cliente:</div>
                                    <div class="col-4"><?= ListasCatalogo::getClientes("Cliente", " disabled"); ?></div>
                                </div>
                                <?php if ($objectVO->getTotal() == 0 || $objectVO->getCantidad() == 0 || $objectVO->getTm() === "H") { ?>

                                <?php } else { ?>
                                    <div class="row no-padding">
                                        <div class="col-3 align-right withBackground required">Seleccionar codigo:</div>
                                        <div class="col-4">
                                            <input type="text" id="Codigo" name="Codigo" list="Codigos" placeholder="Buscar código"  autocomplete="off">
                                            <datalist id="Codigos">
                                                <?php
                                                foreach ($Codigos as $codigo) {
                                                    echo "<option value='" . $codigo["descripcion"] . "'>";
                                                }
                                                ?>
                                            </datalist>
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