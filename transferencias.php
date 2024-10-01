<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("comboBoxes.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$session = new OmicromSession("transf.id", "transf.id");

$busca = $session->getSessionAttribute("criteria");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Id = 58;
$Titulo = "Transferencia de productos a piso";

$paginador = new Paginador($Id,
        "",
        "LEFT JOIN inv ON transf.producto = inv.id",
        "",
        "",
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

require_once './services/TransferenciasService.php';

$Almacen = 0;
$Isla = 1;
$Producto = "";
if ($request->hasAttribute("Producto")) {
    $Producto = $request->getAttribute("Producto");

    $Alm = $mysqli->query("SELECT existencia FROM inv WHERE id = '$Producto'")->fetch_array();
    $Almacen = $Alm["existencia"];
}

if ($request->hasAttribute("Posicion")) {
    $Posicion = $request->getAttribute("Posicion");
}
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                let producto = "<?= $Producto ?>";
                
                $("#Isla").val("<?= $Isla ?>");
                $("#Producto").val(producto);
                $("#Producto").focus();
                
                if(producto === ""){
                    $("#Boton").prop("disabled",true);
                } else{
                    $("#Cantidad").focus();
                }
            });
        </script>
        <?php $paginador->script(); ?>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <div id="TablaDatos">
            <table class="paginador" aria-hidden="true">
                <?php
                echo $paginador->headers(array(), array("Cancelar"));
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr>                        
                        <?php echo $paginador->formatRow(); ?>
                        <td style="text-align: center;">
                            <?php if ($row [cantidad] > 0) { ?>
                                <a href="<?= $cLink ?>?busca=<?= $row['id'] ?>"><i class="icon fa fa-lg fa-trash" aria-hidden="true"></i></a>
                            <?php } ?>
                        </td>
                    </tr>
                <?php }
                ?>
            </table>
        </div>
        <?php
        $nLinks = array();
        $nLinks["<i class=\"fa fa-lg fa-sign-out\"></i> Resurtido Formato 1"] = "javascript:winuni('transferenciasd1.php?op=ini')";
        $nLinks["<i class=\"fa fa-lg fa-sign-out\"></i> Resurtido Formato 2"] = "javascript:winuni('transferenciasd2.php?op=ini')";
        echo $paginador->footer(false, $nLinks, true, true);
        echo $paginador->filter();
        echo "<div class='mensajes'>$Msj</div>";
        ?>

        <form name="form1" method="post" action="">

            <table style="width: 100%;border-collapse: collapse; border: 1px solid #066;background-color: #e1e1e1" aria-hidden="true">
                <tr height="40" valign="center"  class="texto_tablas">
                    <td>
                        Producto:  <?php ComboboxInventario::generate("Producto", "'Aceites'", "300px", "onchange='submit();'"); ?>
                    <td>
                        Almacen: <strong><?= $Almacen ?></strong>
                    </td>
                    <td>
                        Cantidad: <input type="number" name="Cantidad" id="Cantidad" min="0" max="5099" class="texto_tablas">
                    </td>
                    <td>
                        Isla o Disp.:
                        <select name="Isla" id="Isla" class="texto_tablas">
                            <?php
                            $ManA = $mysqli->query("SELECT isla_pos FROM man WHERE inventario = 'Si' AND activo = 'Si' GROUP BY isla_pos");
                            while ($rg = $ManA->fetch_array()) {
                                echo "<option value='$rg[isla_pos]'>$rg[isla_pos]</option>";
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                        <input class="nombre_cliente" type="submit" name="Boton" id="Boton" value="Agregar">
                    </td>
                </tr>
            </table>
            <br/>
        </form>
        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>