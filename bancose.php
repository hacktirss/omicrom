<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require_once "./services/BancosService.php";

$request = utils\HTTPUtils::getRequest();

$Titulo = "Bancos detalle";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

$objectVO = new BancosVO();
$objectVO->setTipo_moneda(1);
if (is_numeric($busca)) {
    $objectVO = $objectDAO->retrieve($busca);
}
$arrayRadios = array("MXN" => 1, "USD" => 2);
$arrayActivo = array(1 => "Activo", 0 => "Inactivo");
$arrayRubros = array(1 => "Vendedores", 0 => "Bancos", 2 => "Otros");
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
                let rubro = "<?= $objectVO->getRubro() ?>";

                if (busca === "NUEVO") {
                    $("#Boton").val("Agregar");
                } else {
                    $("#Boton").val("Actualizar");
                }

                $("#busca").val(busca);
                $("#Banco").val("<?= $objectVO->getBanco() ?>").toUpperCase();
                $("#Rubro").val("<?= $objectVO->getRubro() ?>");
                $("input[name=Cuenta]").toUpperCase();
                $("#Concepto").val("<?= $objectVO->getConcepto() ?>").toUpperCase();
                $("#Ncc").val("<?= $objectVO->getNcc() ?>").toUpperCase();
                $("#Tipo_cambio").val("<?= $objectVO->getTipo_cambio() ?>");
                $("input[name=Tipo_moneda][value=<?= $objectVO->getTipo_moneda() ?>]").prop("checked", true);
                $("#Activo").val("<?= $objectVO->getActivo() ?>");
                $("#Cuenta").val("<?= $objectVO->getCuenta() ?>");
               
                $("#Cuenta").focus();
              
            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="bancos.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">
                    <form name="formulario1" id="formulario1" method="post" action="">
                        <div id="FormulariosBoots">
                            <div class="container no-margin">
                                <div class="row no-padding">
                                    <div class="col-3 align-right withBackground required">No.cuenta:</div>
                                    <div class="col-4"><input type="text" name="Cuenta" id="Cuenta" maxlength="20" class="clase-1" placeholder=""/></div>
                                </div>
                                <div class="row no-padding">
                                    <div class="col-3 align-right withBackground required">Banco:</div>
                                    <div class="col-4"><input type="text" name="Banco" id="Banco" maxlength="20" class="clase-1" placeholder="" required=""/></div>
                                </div>
                                <div class="row no-padding">
                                    <div class="col-3 align-right withBackground required">Concepto:</div>
                                    <div class="col-4"><input type="text" name="Concepto" id="Concepto" maxlength="40" class="clase-1" placeholder="" required=""/></div>
                                </div>
                                <div class="row no-padding">
                                    <div class="col-3 align-right withBackground">Cuenta contable:</div>
                                    <div class="col-4"><input type="text" name="Ncc" id="Ncc" maxlength="20" class="clase-1" placeholder=""/></div>
                                </div>
                                <div class="row no-padding">
                                    <div class="col-3 align-right withBackground required">Tipo de cambio:</div>
                                    <div class="col-1"><input type="text" name="Tipo_cambio" id="Tipo_cambio" maxlength="10" class="clase-1" placeholder="" required=""/></div>
                                    <div class="col-8 align-left">en caso de ser dolares poner el valor, de lo contrario poner el valor de: 1</div>
                                </div>
                                <div class="row no-padding">
                                    <div class="col-3 align-right withBackground required">Tipo de moneda:</div>
                                    <div class="col-4">
                                        <?php
                                        if (is_array($arrayRadios) && count($arrayRadios) > 0) {
                                            foreach ($arrayRadios as $key => $value) {
                                                ?>
                                                <input type="radio" name="Tipo_moneda" value="<?= $value ?>"/><?= $key ?>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="row no-padding">
                                    <div class="col-3 align-right withBackground required">Estado:</div>
                                    <div class="col-4">
                                        <select name="Activo" id="Activo">
                                            <?php
                                            if (is_array($arrayActivo) && count($arrayActivo) > 0) {
                                                foreach ($arrayActivo as $key => $value) {
                                                    ?>
                                                    <option value="<?= $key ?>" label="<?= $value ?>">
                                                        <?php
                                                    }
                                                }
                                                ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row no-padding">
                                    <div class="col-3 align-right"></div>
                                    <div class="col-4"><input type="submit" name="Boton" id="Boton"/></div>
                                </div>
                            </div>
                            <input type="hidden" name="Rubro" id="Rubro"/>
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