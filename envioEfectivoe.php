<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("./comboBoxes.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$self = utils\HTTPUtils::self();

$Titulo = "Detalle de envio";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

require_once "./services/envioEfectivoService.php";
//
$objectVO = new Env_efectivoVO();
$objectDAO = new Env_efectivoDAO();
if (is_numeric($busca)) {
    $objectVO = $objectDAO->retrieve($busca);
    $dsb = $objectVO->getStatus() == "Cerrado" || $objectVO->getStatus() == "Cancelado" ? "disabled" : "";
}
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>        
    </head>   
    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="envioEfectivo.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">

                    <div id="FormulariosBoots">

                        <div class="container no-margin">
                            <div class="row no-padding">
                                <div class="col-10 background no-margin">
                                    <form name="formulario1" id="formulario1" method="post" action="">
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Id:</div>
                                            <div class="col-2"><input type="text" name="Id" id="Id" placeholder="" required="" disabled=""/></div>
                                        </div>
                                        <?php
                                        $selectBancos = "SELECT id,banco,cuenta,concepto FROM bancos WHERE activo = 1 AND rubro = '0' ORDER BY id";
                                        $registrosBancos = utils\IConnection::getRowsFromQuery($selectBancos);
                                        ?>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Banco:</div>
                                            <div class="col-5">
                                                <select name="Banco" id="Banco" class="texto_tablas" <?= $dsb ?>>
                                                    <?php foreach ($registrosBancos as $rg) { ?>
                                                        <option value="<?= $rg["id"] ?>"><?= ucwords(strtolower($rg["banco"] . " | " . $rg["cuenta"] . " | " . $rg["concepto"])) ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Importe:</div>
                                            <div class="col-3"><input type="text" name="Importe" id="Importe" required=""  <?= $dsb ?> /></div>
                                        </div> 

                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Status:</div>
                                            <div class="col-3">
                                                <select name="Status" id="Status" disabled>
                                                    <option value="Abierto">Abierto</option>
                                                    <option value="Cerrado">Cerrado</option>
                                                    <option value="Cancelado">Cancelado</option>
                                                </select>
                                            </div>
                                        </div> 
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Fecha Envio:</div>
                                            <div class="col-3"><input type="text" name="FechaEnvio" id="FechaEnvio" required=""   <?= $dsb ?>/></div>
                                        </div> 
                                        <div class="row no-padding">
                                            <div class="col-4 align-right ">Fecha Creacion:</div>
                                            <div class="col-3"><input type="text" name="FechaCreacion" id="FechaCreacion" disabled="" /></div>
                                        </div> 
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Descripcion:</div>
                                            <div class="col-5">
                                                <textarea name="Descripcion" id="Descripcion" rows="5" cols="250"  <?= $dsb ?>></textarea>
                                            </div>
                                        </div>
                                        <?php
                                        if ($objectVO->getStatus() !== "Cerrado") {
                                            ?>
                                            <div class="row no-padding">
                                                <div class="col-4 align-right"></div>
                                                <?php $Btn = $busca > 0 ? "Actualizar" : "Agregar" ?>
                                                <div class="col-4"><input type="submit" name="Boton" id="Boton" value="<?= $Btn ?>"/></div>
                                            </div>                                       
                                            <input type="hidden" name="busca" id="busca"/>
                                            <?php
                                        }
                                        ?>
                                    </form>
                                </div>
                                <?php
                                if ($objectVO->getStatus() === "Cerrado") {
                                    ?>
                                    <div class="col-10 background no-margin" style="margin-top: 15px;">
                                        <form name="formulario2" id="formulario2" method="post" action="">
                                            <div class="row no-padding">
                                                <div class="col-4 align-right">Cancelar movimiento:</div>
                                                <div class="col-4 align-right"><input type="password" name="CancelMov" id="CancelMov"></div>
                                                <div class="col-1 align-right"><input type="submit" name="Boton" id="Boton" value="Cancelar"></div>
                                            </div>
                                        </form>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        $Value = is_numeric($objectVO->getId()) ? $objectVO->getId() : "NUEVO";
        ?>
        <script type="text/javascript">
            $(document).ready(function () {
                $("#busca").val("<?= $objectVO->getId() ?>");
                $("#Id").val("<?= $Value ?>");
                $("#Banco").val("<?= $objectVO->getId_banco() ?>");
                $("#Descripcion").val("<?= $objectVO->getDescripcion() ?>");
                $("#Importe").val("<?= $objectVO->getImporte() ?>");
                if ("<?= $busca ?>" > 0) {
                    $("#FechaEnvio").val("<?= $objectVO->getFecha_envio() ?>");
                    $("#Status").val("<?= $objectVO->getStatus() ?>");
                    $("#FechaCreacion").val("<?= $objectVO->getFecha_creacion() ?>");
                } else {
                    $("#FechaEnvio").val("<?= date("Y-m-d H:i:s") ?>");
                    $("#Status").val("Abierto");
                    $("#FechaCreacion").val("<?= date("Y-m-d H:i:s") ?>");
                }
            });
        </script>
    </body>
</html>