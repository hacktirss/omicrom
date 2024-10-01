<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();

if ($request->hasAttribute("busca")) {
    utils\HTTPUtils::setSessionValue("busca", $request->getAttribute("busca"));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue("busca", $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue("busca");
$Titulo = "Configuracion de permisos para usuario";

require_once './services/UsuariosService.php';

$selectConfiguracion = "SELECT * FROM authuser_cnf WHERE id_user = $busca";
$rows = utils\IConnection::getRowsFromQuery($selectConfiguracion);
$confArray = array();
foreach ($rows as $value) {
    $confArray[$value["id_menu"]]["editable"] = $value["editable"];
    $confArray[$value["id_menu"]]["permisos"] = $value["permisos"];
}
//error_log(print_r($confArray, TRUE));

$queryMenus = "SELECT * FROM menus WHERE nombre != 'ONOFF' ORDER BY orden";
$rgMenus = utils\IConnection::getRowsFromQuery($queryMenus);
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                var busca = "<?= $busca ?>";
                if (busca === "NUEVO") {
                    $("#Boton").val("Agregar");
                } else {
                    $("#Boton").val("Actualizar");
                    $("#busca").val(busca);
                }

                $("#Todo").click(function () {
                    var checkboxes = $(this).closest('form').find(':checkbox');
                    checkboxes.prop('checked', true);
                    $("#Limpiar").prop('checked', false);
                });

                $("#Limpiar").click(function () {
                    var checkboxes = $(this).closest('form').find(':checkbox');
                    checkboxes.prop('checked', false);
                });

                $("#select_all").change(function () {
                    var checkboxes = $(this).closest("form").find(":checkbox:enabled");
                    checkboxes.prop("checked", $(this).is(":checked"));
                });
            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <div id="FormularioChecks">
            <form id="form1" name="form1" method="post" action="">
                <table aria-describedby="mydesc" aria-hidden="true">
                    <tbody>

                        <?php
                        $auxLine = 0;
                        foreach ($rgMenus as $menu) {
                            if ($auxLine == 0) {
                                echo "<tr valign='top'>";
                            }

                            echo "<td width='20%'>";
                            echo "<strong>" . ucwords(strtolower($menu["nombre"])) . "</strong>";
                            echo "<hr>";

                            $i = 0;

                            $selectSubmenu = "  
                            SELECT menus.nombre,submenus.submenu,submenus.id,submenus.permisos
                            FROM submenus 
                            LEFT JOIN menus ON menus.id = submenus.menu 
                            WHERE menus.id = " . $menu["id"] . " -- AND submenus.permisos > 0
                            ORDER BY submenus.posicion";

                            $rows = utils\IConnection::getRowsFromQuery($selectSubmenu);

                            foreach ($rows as $rg) {

                                if ($rg["permisos"] > 0) {
                                    $submenu = str_replace(" ", "_", $rg["submenu"]) . $rg["id"];
                                    $visible = $confArray[$menu["id"]]["permisos"];
                                    $valor = $confArray[$menu["id"]]["editable"];
                                    $disable = $visible[$i] == 0 ? " disabled='disabled' " : "";

                                    echo "<span>";

                                    if ($visible[$i] == 1) {
                                        echo "<input class='micheck' type='checkbox' name='sub" . $rg["id"] . "' value='1' checked disabled='disabled'> ";
                                    } else {
                                        echo "<input class='micheck' type='checkbox' name='sub" . $rg["id"] . "' disabled='disabled'> ";
                                    }

                                    if ($valor[$i] == "1") {
                                        echo "<input class='micheck' type='checkbox' name='$submenu' value='1' checked $disable>";
                                    } else {
                                        echo "<input class='micheck' type='checkbox' name='$submenu' $disable>";
                                    }
                                    echo " " . $rg["submenu"] . "</span></br>";
                                }

                                $i++;
                            }
                            echo "</td>";

                            $auxLine++;
                            if ($auxLine > 4) {
                                echo "</tr>";
                                $auxLine = 0;
                            }
                        }

                        if ($auxLine > 0) {
                            echo "</tr>";
                        }
                        ?>
                        <tr><td colspan="100%"><hr></td></tr>

                        <tr>
                            <td style="text-align: left">
                                <a href="configusers.php" class="textosCualli"><i class="icon fa fa-lg fa-arrow-circle-left" aria-hidden="true"></i> Regresar</a>
                            </td>
                            <td></td>
                            <td style="text-align: center">
                                <input type="submit" name="BotonD" id="Boton">
                            </td>
                            <td></td>
                            <td>
                                <input type="checkbox" id="select_all"> Seleccionar todo 
                            </td>
                        </tr>
                    </tbody>
                </table>
                <input type="hidden" name="busca" id="busca">
            </form>

        </div>

        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>
