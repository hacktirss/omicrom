<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();

require './services/TerminalesService.php';

$Titulo = "Detalle de terminal";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

$terminalPosVO = new TerminalPosVO();
$terminalPosVO->setModel("NEW8210");
$terminalPosVO->setDispositivo("T");
$terminalPosVO->setStatus(StatusTerminal::ACTIVO);
if (is_numeric($busca)) {
    $terminalPosVO = $terminalPosDAO->retrieve($busca, "pos_id");
}
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php include './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#busca").val("<?= $busca ?>");
                $("#Status").val("<?= $terminalPosVO->getStatus() ?>");
                $("#Modelo").val("<?= $terminalPosVO->getModel() ?>");
                $("#Serial").val("<?= $terminalPosVO->getSerial() ?>");
                $("#Dispositivo").val("<?= $terminalPosVO->getDispositivo() ?>");
                $("#PrintedSN").val("<?= $terminalPosVO->getPrinted_serial() ?>");               
                $("#Ip").val("<?= $terminalPosVO->getIp() ?>");
            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;width: 120px;" class="nombre_cliente">
                    <a href="terminales.php"><div class="RegresarCss " alt="Flecha regresar" style="">Regresar</div></a>
                </td>
                <td style="vertical-align: top;">
                    <div id="FormulariosBoots">
                        <div class="container no-margin">
                            <div class="row no-padding">
                                <div class="col-8 background no-margin">
                                    <form name="formulario1" id="formulario1" method="post" action="">
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Serie: </div>
                                            <div class="col-4">
                                                <input type="text" style="width: 300px;" name="Serial" id="Serial" maxlength="40" class="clase-<?= $clase2 ?>"/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Modelo: </div>
                                            <div class="col-4">
                                                <!--<select name='Modelo' class='texto_tablas' id="Modelo">
                                                    <option value='NEW8210'>NEW8210</option>
                                                    <option value='NEW8110'>NEW8110</option>
                                                    <option value='NEW7110'>NEW7110</option>
                                                    <option value='UBX'>UBX</option>
                                                    <option value='NEW9210'>NEW9210</option>
                                                    <option value='NEW9220'>NEW9220</option>
                                                </select>-->
                                                <input type="text" style="width: 300px;" name="Modelo" id="Modelo" maxlength="40" class="clase-<?= $clase2 ?>"/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required"> N/S Impreso: </div>
                                            <div class="col-4">
                                                <input type="text" style="width: 300px;" name="PrintedSN" id="PrintedSN" maxlength="40" class="clase-<?= $clase2 ?>"/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Ip: </div>
                                            <div class="col-4">
                                                <input type="text" style="width: 300px;" name="Ip" id="Ip" maxlength="40" class="clase-<?= $clase2 ?>"/>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Status: </div>
                                            <div class="col-4">
                                                <select name='Status' class='texto_tablas' id="Status">
                                                    <option value='A'>Activo</option>
                                                    <option value='I'>Inactivo</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Dispositivo: </div>
                                            <div class="col-4">
                                                <select name='Dispositivo' class='texto_tablas' id="Dispositivo">
                                                    <option value='T'>Terminal POS</option>
                                                    <option value='C'>Equipo de Computo</option>
                                                    <option value='I'>Interfaz de Comunicación</option>
                                                    <option value='S'>Servidor</option>
                                                    <option value='D'>Dispensario</option>
                                                    <option value='V'>Sensor de Tanques</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-12 align-center">
                                                <input type='hidden' name='busca' id="busca">
                                                <?php
                                                $boton = (is_numeric($busca)) ? 'Actualizar' : 'Agregar';
                                                ?>
                                                <input type='submit' name='Boton' value='<?= $boton ?>' class='nombre_cliente'>
                                            </div>
                                        </div>
                                    </form>
                            </div>
                        </div>
                    </div>
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
