<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();

require './services/TurnosService.php';

$Titulo = "Detalle de turnos";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

$turnoVO = new TurnoVO();
if (is_numeric($busca)) {
    $turnoVO = $turnoDAO->retrieve($busca);
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
                $("#Turno").val("<?= $turnoVO->getTurno() ?>");
                $("#Isla").val("<?= $turnoVO->getIsla() ?>");
                $("#Activo").val("<?= $turnoVO->getActivo() ?>");
                $("#Descripcion").val("<?= $turnoVO->getDescripcion() ?>");
                $("#Horai").val("<?= $turnoVO->getHorai() ?>");
                $("#Horaf").val("<?= $turnoVO->getHoraf() ?>");
                $("#CorteA").val("<?= $turnoVO->getCortea() ?>");
            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="turnos.php"><div class="RegresarCss " alt="Flecha regresar" style="">Regresar</div></a>
                </td>
                <td style="vertical-align: top;">
                    <div id="FormulariosBoots">
                        <div class="container no-margin">
                            <div class="row no-padding">
                                <div class="col-8 background no-margin">
                                    <form name="formulario1" id="formulario1" method="post" action="">
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Isla: </div>
                                            <div class="col-4">
                                                <select name='Isla' class='texto_tablas' id="Isla">
                                                    <?php
                                                    $islas = Array(1, 2, 3, 4, 5, 6, 7, 8, 9);
                                                    foreach ($islas as $row) {
                                                        echo "<option value='$row'>$row</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Turno: </div>
                                            <div class="col-4">
                                                <select name='Turno' class='texto_tablas' id="Turno">
                                                    <?php
                                                    $turnos = Array(0, 1, 2, 3, 4);
                                                    foreach ($turnos as $row) {
                                                        echo "<option value='$row'>$row</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Descripcion: </div>
                                            <div class="col-4">
                                                <input type='text' name='Descripcion' size='20' maxlenght='30' id='Descripcion' onBLur=mayus('Descripcion') class='texto_tablas'>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Hora Inicial: </div>
                                            <div class="col-4">
                                                <input type='text' name='Horai' size='8' maxlenght='8' id='Horai' class='texto_tablas'>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Hora Final: </div>
                                            <div class="col-4">
                                                <input type='text' name='Horaf' size='8' maxlenght='8' id='Horaf' class='texto_tablas'>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Activo: </div>
                                            <div class="col-4">
                                                <select name='Activo' class='texto_tablas' id="Activo">
                                                    <option value='Si'>Si</option>
                                                    <option value='No'>No</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Corte automatico: </div>
                                            <div class="col-4">
                                                <select name='CorteA' class='texto_tablas' id="CorteA">
                                                    <option value='1'>Si</option>
                                                    <option value='0'>No</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-8 align-center">
                                                <?php
                                                if (is_numeric($busca)) {
                                                    echo "<input type='submit' name='Boton' value='Actualizar' class='nombre_cliente'>";
                                                } else {
                                                    echo "<input type='submit' name='Boton' value='Agregar' class='nombre_cliente'>";
                                                }
                                                ?>
                                                <input type='hidden' name='busca' id='busca'>
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