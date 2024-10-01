<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$arrayFilter = array("Rubro" => 1);
$nameSession = "catalogoProductos";
$session = new OmicromSession("inv.clave_producto", "inv.clave_producto", $nameSession, $arrayFilter, "Rubro");

foreach ($arrayFilter as $key => $value) {
    ${$key} = utils\HTTPUtils::getSessionBiValue($nameSession, $key);
}

$busca = $session->getSessionAttribute("criteria");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Id = 13;
$Titulo = "Catalogo de productos";

if ($Rubro == 1) {
    $cRubro = " inv.rubro='Aceites' ";
} else {
    $cRubro = " inv.rubro <> 'Aceites' ";
}

$paginador = new Paginador($Id,
        "inv.id",
        "LEFT JOIN cfdi33_c_unidades c ON inv.inv_cunidad = c.clave",
        "",
        "inv.id > 10 AND " . $cRubro,
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

require_once './services/ProductosService.php';
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
            <table aria-hidden="true">
                <tbody>
                    <tr>
                        <?php if ($Rubro == 1) { ?>
                            <td style="background-color: #FF6633">Aditivos & Aceites</td>
                            <td style="background-color: #CACACA"><a href="?Rubro=2">Servicios y otros</a></td>
                        <?php } else { ?>
                            <td style="background-color: #CACACA"><a href="?Rubro=1">Aditivos & Aceites</a></td>
                            <td style="background-color: #FF6633">Servicios y otros</td>
                        <?php } ?>
                    </tr>
            </table>
        </div>

        <div id="TablaDatos">
            <table class="paginador" aria-hidden="true">
                <?php
                $BuscaPermiso = "SELECT valor FROM variables_corporativo WHERE llave = 'FacturacionFechas'";
                $rsBsq = utils\IConnection::execSql($BuscaPermiso);
                if ($usuarioSesion->getTeam() !== PerfilesUsuarios::FACTURACION || $rsBsq["valor"] == 1) {
                    echo $paginador->headers(array("Editar"), array("Borrar"));
                } else {
                    echo $paginador->headers();
                }
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr>
                        <?php
                        if ($usuarioSesion->getTeam() !== PerfilesUsuarios::FACTURACION || $rsBsq["valor"] == 1) {
                            ?>
                            <td style="text-align: center;"><a href="<?= $cLink ?>?busca=<?= $row["id"] ?>"><i class="icon fa fa-lg fa-edit" aria-hidden="true"></i></a></td>
                            <?php echo $paginador->formatRow(); ?>
                            <td style="text-align: center;"><a href=javascript:borrarRegistro("<?= $self ?>",<?= $row["id"] ?>,"cId");><i class="icon fa fa-lg fa-trash" aria-hidden="true"></i></a></td>
                                    <?php
                                } else {
                                    ?>
                                    <?php echo $paginador->formatRow(); ?>
                                    <?php
                                }
                                ?>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>
        <?php
        $nLinks["<i class='icon fa fa-lg fa-list-alt' aria-hidden=\"true\"></i> Lista de precios"] = "javascript:winuni('imppreciosace.php');";
        echo $paginador->footer($usuarioSesion->getLevel() >= 7, $nLinks, false, true);
        echo $paginador->filter();
        echo "<div class='mensajes'>$Msj</div>";
        BordeSuperiorCerrar();
        PieDePagina();
        ?>
    </table>

</body>


