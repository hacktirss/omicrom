<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$nameVariableSession = "Dictamenes";
$session = new OmicromSession("dic.id", "dic.id", $nameVariableSession);

if ($request->hasAttribute("tipo")) {
    utils\HTTPUtils::setSessionObject("Tipo", $request->getAttribute("tipo"));
} else if (utils\HTTPUtils::getSessionObject("Tipo") == null || utils\HTTPUtils::getSessionObject("Tipo") === "") {
    utils\HTTPUtils::setSessionObject("Tipo", 1);
}
$tipo = utils\HTTPUtils::getSessionObject("Tipo");

require_once './services/DictamenService.php';

$busca = $session->getSessionAttribute("criteria");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Id = 78;
$Titulo = "Registro de dictamenes de tanques";
$AddSql = $tipo == 1 ? " prv.proveedorde != 'CV' " : " prv.proveedorde = 'CV' ";
$paginador = new Paginador($Id,
        "",
        "",
        "",
        $AddSql,
        $session->getSessionAttribute("sortField"),
        $session->getSessionAttribute("criteriaField"),
        utils\Utils::split($session->getSessionAttribute("criteria"), "|"),
        strtoupper($session->getSessionAttribute("sortType")),
        $session->getSessionAttribute("page"),
        "REGEXP",
        "");
//echo var_dump($paginador);

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
            });
        </script>
        <?php $paginador->script(); ?>
    </head>

    <body>

        <?php BordeSuperior(); ?>
        <div id="Selector">
            <table aria-hidden="true" style="border: 1px solid #808B96;border-radius: 15px;">
                <tbody>
                    <tr>
                        <?php if ($tipo == 1) { ?>
                            <td style="background-color: #FF6633;width: 49%;border-radius: 15px 0px 0px 15px;">Tanques</td>
                            <td style="background-color: #CACACA;width: 49%;border-radius: 0px 15px 15px 0px;"><a href="dictamenes.php?tipo=2">Control Volumetrico</a></td>
                        <?php } else { ?>
                            <td style="background-color: #CACACA;width: 49%;border-radius: 15px 0px 0px 15px;"><a href="dictamenes.php?tipo=1">Tanques</a></td>
                            <td style="background-color: #FF6633;width: 49%;border-radius: 0px 15px 15px 0px;">Control Volumetrico</td>
                        <?php } ?>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="TablaDatos">
            <table class="paginador" aria-hidden="true">
                <?php
                if ($tipo == 1) {
                    echo $paginador->headers(array("Editar", "Detalle"), array());
                } else {
                    echo $paginador->headers(array("Editar"), array());
                }
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr>
                        <td style="text-align: center;"><a href="<?= $cLink ?>?busca=<?= $row["id"] ?>"><i class="icon fa fa-lg fa-edit" aria-hidden="true"></i></a></td>
                        <?php
                        if ($tipo == 1) {
                            ?>
                            <td style="text-align: center;"><a href="<?= $cLinkd ?>?criteria=ini&cVarVal=<?= $row["id"] ?>"><i class="icon fa fa-lg fa-file-text" aria-hidden="true"></i></a></td>
                                    <?php
                                }
                                echo $paginador->formatRow();
                                ?>
                    </tr>
                    <?php
                }
                ?> 
            </table>
        </div>
        <?php
        $nlink = Array("<i class=\"icon fa fa-lg fa-plus-circle\" aria-hidden=\"true\"></i> ExportarXFecha" => "javascript:wingral('pidedatos.php?criteria=ini&busca=9')");
        echo $paginador->footer($usuarioSesion->getLevel() >= 7,$nlink,false,true);
        echo $paginador->filter();

        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>