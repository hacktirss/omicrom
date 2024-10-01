<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$arrayFilter = array("Tipo_ducto" => 0);
$nameSession = "Catalogo_Ductos";
$session = new OmicromSession("id_ducto", "id_ducto",$nameSession,$arrayFilter,"Tipo_ducto");

foreach ($arrayFilter as $key => $value) {
    ${$key} = utils\HTTPUtils::getSessionBiValue($nameSession, $key);
}

$busca = $session->getSessionAttribute("criteria");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Id = 136;
$Titulo = "Medios de transporte y distribución";

$paginador = new Paginador($Id,
        "id_ducto,id_sat",
        "",
        "",
        "tipo_ducto = '$Tipo_ducto'",
        $session->getSessionAttribute("sortField"),
        $session->getSessionAttribute("criteriaField"),
        utils\Utils::split($session->getSessionAttribute("criteria"), "|"),
        strtoupper($session->getSessionAttribute("sortType")),
        $session->getSessionAttribute("page"),
        "REGEXP",
        "");

$self = utils\HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");
$cLink = substr($self, 0, strrpos($self, ".")) . 'e.php';
$cLinkd = substr($self, 0, strrpos($self, ".")) . 'd.php';
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#autocomplete").focus();
                 var Tipo = "<?= $Tipo ?>";
                $(".Tipo").val(Tipo);
            });
        </script>
        <?php $paginador->script(); ?>
    </head>

    <body>

        <?php BordeSuperior(); ?>
        <div id="Selector">
            <table aria-hidden="true">
                <tbody>
                    <tr>
                        <?php if ($Tipo_ducto == 0) { ?>
                            <td style="background-color: #FF6633;width: 33%;">Ductos</td>
                            <td style="background-color: #CACACA;width: 33%;"><a href="ductos.php?Tipo_ducto=1">Otros</a></td>
                        <?php } else { ?>
                            <td style="background-color: #CACACA;width: 33%;"><a href="ductos.php?Tipo_ducto=0">Ductos</a></td>
                            <td style="background-color: #FF6633;width: 33%;">Otros</td>
                        <?php } ?>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="TablaDatos">
             <table class="paginador" aria-hidden="true">
                <?php
                echo $paginador->headers(array("Editar"), array());
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr>
                        <td style="text-align: center;"><a href="<?= $cLink ?>?busca=<?= $row['id_ducto'] ?>"><i class="icon fa fa-lg fa-edit" aria-hidden="true"></i></a></td>
                        <td style="text-align: center;"><?= "".$row['id_sat']."-".sprintf("%04d", $row['id_ducto']) ?></td> 
                        <td style="text-align: center;"><?= $row['clave_identificacion_ducto'] ?></td>
                        <td style="text-align: center;"><?= $row['descripcion_ducto'] ?></td>
                        <td style="text-align: center;"><?= "".$row['diametro_ducto'].'"' ?></td>
                        <td style="text-align: center;"><?= $row['sistema_medicion'] ?></td>
                        <td style="text-align: center;"><?= $row['medidor'] ?></td>
                        <td style="text-align: center;"><?= $row['almacenamiento_ducto'] ?></td>
                    </tr>
                    <?php
                }
                ?> 
            </table>
        </div>
        <?php
        echo $paginador->footer(true);
        echo $paginador->filter();
        echo "<div class='mensajes'>$Msj</div>";
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>