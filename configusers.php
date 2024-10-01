<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$nameSession = "catalogoUsuarios";
$arrayFilter = array("locked" => 0);
$session = new OmicromSession("us.id", "us.id", $nameSession, $arrayFilter, "locked");

foreach ($arrayFilter as $key => $value) {
    ${$key} = utils\HTTPUtils::getSessionBiValue($nameSession, $key);
}

$busca = $session->getSessionAttribute("criteria");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Id = 84;
$Titulo = "Catálogo de usuarios";

$conditions = "";
if ($locked == 1) {
    $conditions = " AND (us.locked >= 5 OR us.alive = 1)";
    $Titulo = "Usuarios bloqueados y en sesión";
}

$paginador = new Paginador($Id,
        "us.alive,us.locked",
        "",
        "",
        "us.level <= 8  AND groupwork = 0  " . $conditions,
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

require_once './services/UsuariosService.php';
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

        <div id="TablaDatos">
            <table class="paginador" aria-hidden="true">
                <?php
                echo $paginador->headers(array("Editar", "Permisos"), array("En linea", $locked == 0 ? "" : "Desbloquear", $locked == 0 ? "" : "Liberar"));
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr>
                        <td style="text-align: center;"><a href="<?= $cLink ?>?busca=<?= $row["id"] ?>"><i class="icon fa fa-lg fa-edit" aria-hidden="true"></i></a></td>                        
                        <td style="text-align: center;">
                            <?php if ($usuarioSesion->getLevel() == UsuarioDAO::LEVEL_MASTER): ?>
                                <a href="<?= $cLinkd ?>?busca=<?= $row["id"] ?>"><i class="icon fa fa-lg fa-list-alt" aria-hidden="true"></i></a>
                            <?php endif; ?>
                        </td>
                        <?php echo $paginador->formatRow(); ?>
                        <td style="text-align: center;"><?= $row["alive"] == 1 ? "Si" : "No" ?></td>
                        <td style="text-align: center;">
                            <?php
                            if ($locked == 0) {
                                
                            } else {
                                if ($row["locked"] >= 5) {
                                    ?>
                                    <a href="<?= $self ?>?cId=<?= $row["id"] ?>&op=unlock"><i class="icon fa fa-lg fa-unlock" aria-hidden="true"></i></a>
                                    <?php
                                }
                            }
                            ?>
                        </td>
                        <td style="text-align: center;">
                            <?php if ($locked == 1 && $row["alive"] == 1) { ?>
                                <a href="<?= $self ?>?cId=<?= $row["id"] ?>&op=unlock"><i class="icon fa fa-lg fa-hourglass-end" aria-hidden="true"></i></a>
                                <?php } ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>
        <?php
        if ($locked == 0) {
            $nLink = array("<i class=\"icon fa fa-lg fa-unlock\" aria-hidden=\"true\"></i> Desbloquear Usuarios" => $self . "?locked=1", "<i class='fa-regular fa-file'></i> Reporte " => "javascript:winuni('reporteUsuarios.php')");
            $reload = true;
        } else {
            $nLink = array("<i class=\"icon fa fa-lg fa-arrow-circle-left\" aria-hidden=\"true\"></i> Regresar" => $self . "?criteria=ini", "<i class='fa-regular fa-file'></i> Reporte " => "javascript:winuni('reporteUsuarios.php');");
            $reload = false;
        }
        echo $paginador->footer($usuarioSesion->getTeam() === UsuarioPerfilDAO::PERFIL_ADMIN && $locked == 0, $nLink, false, $reload);
        echo $paginador->filter();
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>
