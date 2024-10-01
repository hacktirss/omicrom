<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("importeletras.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$usuarioSesion = getSessionUsuario();

if ($request->hasAttribute("busca")) {
    utils\HTTPUtils::setSessionValue("busca", $request->getAttribute("busca"));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue("busca", $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue("busca");
$Factor = 1000000;

if ($request->hasAttribute("Boton")) {
    if ($request->getAttribute("Boton") === "Cambiar") {
        $val1 = $request->getAttribute("Ieps1");
        $val2 = $request->getAttribute("Ieps2") / $Factor;
        $ieps = $val1 + $val2;
        $updateIeps = "UPDATE com SET ieps='$ieps' WHERE id = $busca";
        if (!($mysqli->query($updateIeps))) {
            error_log($mysqli->error);
        }
        BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", "ACTUALIZACION DE IEPS[id=$busca]: $ieps");
        header("Location: modifica_ieps.php?busca=$busca");
    }
}

$ieps = $mysqli->query("SELECT * FROM com WHERE id=$busca")->fetch_array();

$placeholder = "Ieps actual: " . $ieps["ieps"];

$val1 = intval($ieps["ieps"]);
$mod = $ieps["ieps"];
if ($mod > 1) {
    $mod = $mod - $val1;
}

$val2 = $mod * $Factor;
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports.php'; ?> 
        <title><?= $Gcia ?></title>        
        <script>
            $(document).ready(function () {
                $("#busca").val("<?= $busca ?>");
            });
        </script>
        <style>
            html,body{
                background-color: white;
                width: 100%;
                min-width: 150px;
                min-height: 100px;
            }            
        </style>
    </head>

    <body>
        <div id="container">
            <?php nuevoEncabezadoMini($Titulo) ?>
            <form name="form1" action="" method="post">

                <table style="width: 95%;text-align: center;" aria-hidden="true" class="texto_tablas">
                    <tr>
                        <td colspan="3" align="center"><strong>Ieps de <?= $ieps["descripcion"] ?></strong>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                    </tr>
                    <tr>
                        <td>
                            Pesos: <input name="Ieps1" type="number" min="0" max="99" placeholder="00" value="<?= $val1 ?>" class="texto_tablas">
                        </td>
                        <td>
                            Cent: <input name="Ieps2" type="number" min="0" max="999999" placeholder="000000" value="<?= $val2 ?>" class="texto_tablas">                                  
                        </td>
                        <td>
                            &nbsp;&nbsp;<input type="submit" name="Boton" value="Cambiar" class="nombre_cliente">
                        </td>
                    </tr>
                </table>
                <input type="hidden" name="busca" id="busca">
            </form>      
        </div>
    </body>
</html>

