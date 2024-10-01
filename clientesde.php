<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

require "./services/ClientesService.php";

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$self = utils\HTTPUtils::self();

$Titulo = "Detalle de unidad";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

$clienteVO = new ClientesVO();
if (is_numeric($busca)) {
    $clienteVO = $clienteDAO->retrieve($cVarVal);
}

$tarjetaVO = new TarjetaVO();
if (is_numeric($busca)) {
    $tarjetaVO = $tarjetaDAO->retrieve($busca);
}

$arrayPeriodo = array("D" => "Diario", "S" => "Semanal", "Q" => "Quincenal", "M" => "Mensual", "B" => "Saldos", "A" => "Monedero Acumulativo", "C" => "Monedero x Consumos");
$arraySimultaneo = array(0 => "Bloquear", 1 => "Permitir");
$arrayEstado = array("a" => "Activo", "d" => "Inactivo");
$arrayCombustibles = array();

$result = $mysqli->query("SELECT id, descripcion FROM com WHERE activo = 'Si' ORDER BY id");
$rows = $result->num_rows;
while ($rg = $result->fetch_array()) {
    $arrayCombustibles["c" . $rg["id"]] = ucwords(strtolower($rg['descripcion']));
}
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>

            $(document).ready(function () {
                var combustible = "<?= $tarjetaVO->getCombustible() ?>";
                var count = Number.parseInt("<?= $rows ?>");

                $("#busca").val("<?= $busca ?>");

                $("#Placas").val("<?= $tarjetaVO->getPlacas() ?>");
                $("#Descripcion").val("<?= $tarjetaVO->getDescripcion() ?>");
                $("#Litros").val("<?= $tarjetaVO->getLitros() ?>");
                $("#Importe").val("<?= $tarjetaVO->getImporte() ?>");
                $("#Periodo").val("<?= $tarjetaVO->getPeriodo() ?>");
                $("#Simultaneo").val("<?= $tarjetaVO->getSimultaneo() ?>");
                $("#Estado").val("<?= $tarjetaVO->getEstado() ?>");
                $("#Nip").val("<?= $tarjetaVO->getNip() ?>");
                if ("<?= $tarjetaVO->getPeriodo() ?>" === "B") {
                    $("#Importe").prop("disabled", true);
                }
                for (i = 1; i <= count; i++) {
                    if (combustible.includes(i)) {
                        $("#c" + i).prop("checked", "checked");
                    }
                }

                $("#DomI").val("<?= $tarjetaVO->getDomi() ?>");
                $("#DomF").val("<?= $tarjetaVO->getDomf() ?>");
                $("#LunI").val("<?= $tarjetaVO->getLuni() ?>");
                $("#LunF").val("<?= $tarjetaVO->getLunf() ?>");
                $("#MarI").val("<?= $tarjetaVO->getMari() ?>");
                $("#MarF").val("<?= $tarjetaVO->getMarf() ?>");
                $("#MieI").val("<?= $tarjetaVO->getMiei() ?>");
                $("#MieF").val("<?= $tarjetaVO->getMief() ?>");
                $("#JueI").val("<?= $tarjetaVO->getJuei() ?>");
                $("#JueF").val("<?= $tarjetaVO->getJuef() ?>");
                $("#VieI").val("<?= $tarjetaVO->getViei() ?>");
                $("#VieF").val("<?= $tarjetaVO->getVief() ?>");
                $("#SabI").val("<?= $tarjetaVO->getSabi() ?>");
                $("#SabF").val("<?= $tarjetaVO->getSabf() ?>");
                $("#Nomeco").val("<?= $tarjetaVO->getNumeco() ?>");

            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <div style="width: 98%;margin-left: auto;margin-right: auto;border: 2px solid gray;margin-bottom: 10px;padding: 3px 1px;">
            <table style="width: 98%;margin-left: auto;margin-right: auto;" class="texto_tablas" aria-hidden="true">
                <tr style="background-color: #E1E1E1;height: 30px;">
                    <td> &nbsp; <strong>Cuenta:</strong> <?= $cVarVal ?></td><td> &nbsp; <strong>Nombre:</strong> <?= $clienteVO->getNombre() ?></td>
                    <td> &nbsp; <strong>Tipo de Cliente:</strong> <?= $clienteVO->getTipodepago() ?> </td>
                </tr>
            </table>
        </div>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="clientesd.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">
                    <div id="Formularios">
                        <?php
                        abrirFormulario("formulario1", "clientesd.php");
                        crearInputText("Placas", "Placas", 20, $siMayusculas, $siRequerido, "25H-58-52", $clase1);
                        crearInputText("Descripción", "Descripcion", 30, $siMayusculas, $siRequerido, "", $clase1);
                        crearInputNumber("Litros", "Litros", 0, 1000000, $siRequerido, "", $clase1);
                        crearInputNumber("<strong>o&acute;</strong> Importe", "Importe", 0, 1000000, $siRequerido, "", $clase1);
                        crearInputSelect("Periodo", "Periodo", $arrayPeriodo, $siRequerido, $clase0);
                        crearInputCheckboxArray("Combustibles", $arrayCombustibles);
                        crearInputSelect("Cargas Simultaneas", "Simultaneo", $arraySimultaneo, $siRequerido, $clase0);
                        crearInputText("Numero Economico", "Nomeco", 30, $siMayusculas, $siRequerido, "", $clase1);
                        crearInputSelect("Estado", "Estado", $arrayEstado, $siRequerido, $clase0);
                        crearInputText("Nip", "Nip", 10, $siMayusculas, $siRequerido, "", $clase0);
                        generaTiraHoras();
                        crearBoton("BotonD", "Actualizar");
                        crearInputHidden("busca");
                        cerrarFormulario();
                        ?>
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