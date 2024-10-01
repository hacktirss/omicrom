<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$nameVariableSession = "DictamenesDetalle";
$session = new OmicromSession("dicd.idnvo", "dicd.idnvo", $nameVariableSession);

require_once './services/DictamenService.php';

$busca = $session->getSessionAttribute("criteria");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Id = 79;
$Titulo = "Registro de dictamenes de tanques";

$paginador = new Paginador($Id,
        "dicd.contiene_fosil,dicd.comp_fosil,dicd.comp_octanaje,tanques.clave_producto",
        "",
        "",
        "dicd.id = '$cVarVal'",
        $session->getSessionAttribute("sortField"),
        $session->getSessionAttribute("criteriaField"),
        utils\Utils::split($session->getSessionAttribute("criteria"), "|"),
        strtoupper($session->getSessionAttribute("sortType")),
        $session->getSessionAttribute("page"),
        "REGEXP",
        "dictamenes.php");



$self = utils\HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");
$cLink = substr($self, 0, strrpos($self, ".")) . 'e.php';
$cLinkd = substr($self, 0, strrpos($self, ".")) . 'd.php';

$selectDictamen = "SELECT dictamen.*, prv.nombre FROM dictamen LEFT JOIN prv ON dictamen.proveedor = prv.id 
        WHERE dictamen.id = $cVarVal;";
$He = utils\IConnection::execSql($selectDictamen);
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
        
        <div style="width: 98%;margin-left: auto;margin-right: auto;border: 2px solid gray;margin-bottom: 10px;padding: 3px 1px;">
            <table style="width: 98%;margin-left: auto;margin-right: auto;" class="texto_tablas" aria-hidden="true">
                <tr style="background-color: #E1E1E1;height: 20px;">
                    <td><strong>Dictamen:</strong> <?= $He["id"] ?></td>
                    <td><strong>Proveedor:</strong> <?= $He["proveedor"] . " | " . $He["nombre"] ?></td>
                    <td><strong>Folio:</strong> <?= $He["numeroFolio"] ?></td>
                    <td><strong>Emisión:</strong> <?= $He["fechaEmision"] ?> </td>
                </tr>
            </table>
        </div>
        

        <div id="TablaDatos">
            <table class="paginador" aria-hidden="true">
                <?php
                $estado = 0;
                
                echo $paginador->headers(array("Editar"), array("Contiene Fosil","Composicion Fosil","Octanaje"));
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr>
                        <td style="text-align: center;">
                            <?php if (empty($row["estado"])) { ?>
                                <a href="<?= $cLink ?>?busca=<?= $row["idnvo"] ?>"><i class="icon fa fa-lg fa-edit" aria-hidden="true"></i></a>
                                <?php } ?>
                        </td>
                        <?php echo $paginador->formatRow(); ?>
                        <td style="text-align: center;" ><?php echo $row["contiene_fosil"]; ?></td>
                        <td style="text-align: center;" ><?php echo $row["comp_fosil"]; ?></td>
                        <?php if ( $row["clave_producto"] != "34006") { ?>
                        <td style="text-align: center;" ><?php echo $row["comp_octanaje"]; ?></td>
                        <?php }else { ?>
                            <td style="text-align: center;" >--</td>
                            <?php } ?>
                    </tr>
                    <?php
                    $estado = $row["estado"];
                }
                ?> 
            </table>
        </div>
        <?php
        $nLink = null;
        if (empty($estado)) {
            $nLink["<i class='icon fa fa-flag' aria-hidden=\"true\"></i> Click aqui para finalizar la operación <i class='icon fa fa-flag' aria-hidden=\"true\"></i>"] = "dictamenesd.php?op=Cerrar";
        }
        echo $paginador->footer(false, $nLink, false);
        echo $paginador->filter();

        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>