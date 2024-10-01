<?php
#Librerias
session_start();
set_time_limit(300);

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("data/FcDAO.php");

use com\softcoatl\utils as utils;

$request = utils\Request::instance();
$mysqli = iconnect();
$pop = 0;
$arrayFilter = array("fmt" => $request->has("fmt") ? $request->get("fmt") : 0,
    "tipo" => $request->has("tipo") ? $request->get("tipo") : 1);
$nameSession = "catalogoTraslados";
foreach ($arrayFilter as $key => $value) {
    ${$key} = utils\HTTPUtils::getSessionBiValue($nameSession, $key);
}
utils\HTTPUtils::setSessionObject("Tipo", $tipo);
$session = new OmicromSession($tipo != 2 ? "t.id" : "ingresos.id", $tipo != 2 ? "t.id" : "ingresos.id", $nameSession, $arrayFilter, "tipo");

$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$conditions = "";

$Titulo = "Modulo de Carta Porte";
if ($tipo != 2) {
    $Id = 141;
    $AddSql = "t.uuid";
} else if ($tipo == 2) {
    $Id = 156;
    $AddSql = "ingresos.uuid,ingresos.id";
}

$paginador = new Paginador($Id,
        "$AddSql,status",
        "LEFT JOIN cli ON id_cli = cli.id",
        "",
        $conditions,
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

if (!empty($session->getSessionAttribute("returnLink"))) {
    $rLink = $session->getSessionAttribute("returnLink");
}
utils\HTTPUtils::setSessionObject("Tipo", $tipo);
if (($request->getAttributes("Org")["Org"] === "Si" && $request->getAttributes("Rep")["Rep"] !== "Si") || $request->getAttributes("Iniciamos")["Iniciamos"] === "Si") {
    $Dir = "location: traslados.php?tipo=" . $request->getAttributes("tipo")["tipo"] . "&criteria=ini&Rep=Si";
    header($Dir);
}
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom.php"; ?>
        <title><?= $Gcia ?></title>
        <script>
            var popVar = "<?= $pop ?>";
            pop = function () {
                if (popVar === "1") {
                    wingral('enviafile.php?file=fc&id=<?= $request->get("idp") ?>&type=pdf&formato=<?= $request->get("fmp") ?>');
                }
            };
            $(document).ready(function () {
                $("#autocomplete").focus();
            });
        </script>
        <?php $paginador->script(); ?>
    </head>

    <body onload="pop();">

        <?php BordeSuperior(); ?>
        <div id="Selector">
            <table aria-hidden="true" style="border: 1px solid #808B96;border-radius: 15px;">
                <tbody>
                    <tr>
                        <?php if ($tipo != 2) { ?>
                            <td style="background-color: #FF6633;width: 33%;border-radius: 15px 0px 0px 15px;">Transporte Propio</td>
                            <td style="background-color: #CACACA;width: 33%;border-radius: 0px 15px 15px 0px;color: #2C3E50;"><a href="traslados.php?tipo=2&criteria=ini&Org=Si">Trasnporte convencional</a></td>
                        <?php } elseif ($tipo == 2) { ?>
                            <td style="background-color: #CACACA;width: 33%;border-radius: 15px 0px 0px 15px;"><a href="traslados.php?tipo=1&criteria=ini&Org=Si">Transporte Propio</a></td>
                            <td style="background-color: #FF6633;width: 33%;border-radius: 0px 15px 15px 0px;">Trasnporte convencional</td>
                        <?php } ?>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="TablaDatos">
            <table class="paginador" aria-hidden="true">
                <?php
                echo $paginador->headers(array("Edita", "Detalle", "Pdf", "Xml"), array(""));
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr title="<?= $title ?>">
                        <td style="text-align: center;"><a href="<?= $cLink ?>?busca=<?= $row['id'] ?>&cVarVal=<?= $row['id'] ?>&tipo=<?= $tipo ?>"><i class="icon fa fa-lg fa-edit" aria-hidden="true"></i></a></td>
                        <td style="text-align: center;">
                            <?php
                            if ($row["status"] == 0 || $row["status"] == 1) {
                                ?>
                                <a href="<?= $cLinkd ?>?busca=<?= $row['id'] ?>&cVarVal=<?= $row['id'] ?>&criteria=ini&tipo=<?= $tipo ?>"><i class="fa fa-file-text" aria-hidden="true"></i></a>
                                <?php
                            }
                            ?>
                        </td>
                        <?php
                        if ($row["uuid"] !== "-----" && $row["status"] == 1) {
                            ?>
                            <td style="text-align: center;"><a style="color: red;" href="javascript:winuni('enviafile.php?id=<?= $row['uuid'] ?>&type=pdf&formato=0')"><i class="icon icon fa fa-lg fa-file-pdf-o" style="color:#E74C3C;" aria-hidden="true"></i></a></td>
                            <td style="text-align: center;"><a style="color: graytext;" href="javascript:winuni('enviafile.php?id=<?= $row['uuid'] ?>&type=xml')"><i class="icon fa fa-lg fa-file-code-o" style="color:#2E86C1;" aria-hidden="true"></i></a></td>
                            <?php
                        } elseif ($row["uuid"] !== "-----" && ($row["status"] == 2 || $row["status"] == 3)) {
                            ?>
                            <td style="text-align: center;"><i class="fa-solid fa-ban" style="color: #F1948A"></i></td>
                            <td style="text-align: center;"><i class="fa-solid fa-ban" style="color: #F1948A"></i></td>
                            <?php
                        } else {
                            ?>
                            <td style="text-align: center;"></td>
                            <td style="text-align: center;"></td>
                            <?php
                        }
                        echo $paginador->formatRow();
                        ?>
                        <td style="text-align: center;">
                            <?php
                            if ($row["status"] == 3) {
                                ?>
                                <a href="">Cancelado</a>
                                <?php
                            } else {
                                ?>
                                <a href="cancartaporte.php?busca=<?= $row['id'] ?>">Cancelar</a>
                                <?php
                            }
                            ?>
                        </td>
                    </tr>
                    <?php
                }
                ?> 
            </table>
        </div>

        <?php
        $nLink = array();
        if (!empty($session->getSessionAttribute("backLink"))) {
            $nLink["<i class=\"icon fa fa-lg fa-arrow-circle-left\" aria-hidden=\"true\"></i> Regresar"] = "trasladosd.php?busca=ini";
        }
        echo $paginador->footer(true, $nLink, true, true);
        echo $paginador->filter();
        echo "<div class='mensajes'>$Msj</div>";
        BordeSuperiorCerrar();
        PieDePagina();
        ?>
    </body>
</html>
<?php
