<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require './services/ClientesService.php';
$usuarioSesion = getSessionUsuario();
$request = utils\HTTPUtils::getRequest();
$arrayFilter = array("Facturar" => $request->getAttribute("Facturar"));
$nameSession = "catalogoClientes";
$session = new OmicromSession("cli.nombre", "cli.nombre", $nameSession, $arrayFilter, "Filtros");

foreach ($arrayFilter as $key => $value) {
    ${$key} = utils\HTTPUtils::getSessionBiValue($nameSession, $key);
}
if ($request->getAttribute("NvaSerie") <> "") {
    utils\HTTPUtils::setSessionValue("NvaSerieP", $request->getAttribute("NvaSerie"));
}
$busca = $session->getSessionAttribute("criteria");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Id = 32;
$Titulo = "Catalogo de clientes";

$conditions = "";
if ($Facturar == 2) {
    $conditions = " AND cli.tipodepago NOT IN ('" . TiposCliente::PUNTOS . "') ";
}


$paginador = new Paginador($Id,
        "direccion, colonia, numeroext, numeroint, codigo, municipio, estado, telefono, correo",
        "",
        "",
        "cli.id >= 10 " . $conditions,
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
if (utils\HTTPUtils::getSessionObject("Tipo") == 1 && $session->getSessionAttribute("returnLink") === "trasladose.php?Boton=Agregar") {
    $Rdir = $rLink . "&Cliente=0&Op=1";
    header("location: $Rdir");
}
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#autocomplete").focus();
                if ("<?= utils\HTTPUtils::getSessionValue("NvaSerieP") ?>" !== "") {
                    $("#IniSerie").val("<?= utils\HTTPUtils::getSessionValue("NvaSerieP") ?>");
                }
                $("#IniSerie").change(function () {
                    if ($("#IniSerie").val() !== "") {
                        Swal.fire({
                            icon: "question",
                            title: "Seguro de inicializar la factura con serie " + $("#IniSerie").val(),
                            background: "#E9E9E9",
                            showConfirmButton: true,
                            confirmButtonText: "Cambiar",
                            backdrop: 'swal2-backdrop-show'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = "clientes.php?NvaSerie=" + $("#IniSerie").val();
                            }
                        });
                    }
                });
            });
        </script>
        <?php $paginador->script(); ?>
    </head>

    <body>

        <?php
        BordeSuperior();
        ?>
        <div id="TablaDatos">
            <?php
            if (!empty($session->getSessionAttribute("returnLink"))) {
                $BuscaVarablesExtras = "SELECT valor FROM variables_corporativo WHERE llave LIKE '%factura_extra%'";
                $RsSeriesEx = utils\IConnection::getRowsFromQuery($BuscaVarablesExtras);
                ?>
                <div style="width: 100%; text-align: right;padding-right: 25px;">
                    Inicializar serie
                    <select name="IniSerie" id="IniSerie">
                        <option value="">Valor Default</option>
                        <?php
                        foreach ($RsSeriesEx as $Rex) {
                            ?>
                            <option value="<?= $Rex['valor'] ?>"><?= $Rex['valor'] ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
                <?php
            }
            ?>
            <table class="paginador" aria-hidden="true">
                <?php
                $Visualiza = "SELECT valor FROM variables_corporativo WHERE llave ='AddTicketsCliente'";
                $VsV = utils\IConnection::execSql($Visualiza);
                $ArrayLeft = ($usuarioSesion->getTeam() === "Administrador" || $usuarioSesion->getTeam() === "Supervisor") && $VsV["valor"] == 1 ? array("Editar", "Codigos", "Relacionar") : array("Editar", "Codigos");
                if (empty($session->getSessionAttribute("returnLink"))) {
                    echo $paginador->headers($ArrayLeft, array("Borrar"));
                    while ($paginador->next()) {
                        $row = $paginador->getDataRow();
                        ?>
                        <tr>
                            <td style="text-align: center;"><a href="<?= $cLink ?>?busca=<?= $row['id'] ?>"><i class="icon fa fa-lg fa-edit" aria-hidden="true"></i></a></td>
                            <td style="text-align: center;"><a href="<?= $cLinkd ?>?criteria=ini&cVarVal=<?= $row["id"] ?>"><i class="icon fa fa-lg fa-barcode" aria-hidden="true"></i></a></td>
                            <?php
                            if (($usuarioSesion->getTeam() === "Administrador" || $usuarioSesion->getTeam() === "Supervisor") && $VsV["valor"] == 1) {
                                ?>
                                <td style="text-align: center;"><a href="clientesAgregaVentas.php?criteria=ini&busca=<?= $row["id"] ?>"><i class="fa-solid fa-folder-plus"></i></a></td>
                                <?php
                            }
                            ?>
                            <?php echo $paginador->formatRow(); ?>
                            <td style="text-align: center;"><a href=javascript:borrarRegistro("<?= $self ?>",<?= $row["id"] ?>,"cId");><i class="icon fa fa-lg fa-trash" aria-hidden="true"></i></a></td>
                        </tr>
                        <?php
                    }
                } else {
                    $AddEnvia = "";
                    if ($request->getAttribute("NvaSerie") !== "") {
                        $AddEnvia = "&NvaSerie=" . $request->getAttribute("NvaSerie");
                    }
                    if (utils\HTTPUtils::getSessionObject("Tipo") > 0) {
                        $AddEnvia .= "&Op=" . utils\HTTPUtils::getSessionObject("Tipo");
                    }
                    echo $paginador->headers(array(" ",), array());
                    while ($paginador->next()) {
                        $row = $paginador->getDataRow();
                        ?>
                        <tr>
                            <td style="text-align: center;"><a href="<?= $rLink ?>&Cliente=<?= $row["id"] . $AddEnvia ?>">seleccionar</a></td>
                            <?php echo $paginador->formatRow(); ?>
                        </tr>
                        <?php
                    }
                }
                ?>
            </table>
        </div>
        <?php
        $nLink = array();
        if (!empty($session->getSessionAttribute("backLink"))) {
            if ($Facturar > 0) {
                $nLink["<i class=\"icon fa fa-lg fa-plus-circle\" aria-hidden=\"true\"></i> Agregar"] = "clientese.php?Facturar=1";
            }
            $nLink["<i class=\"icon fa fa-lg fa-arrow-circle-left\" aria-hidden=\"true\"></i> Regresar"] = $session->getSessionAttribute("backLink");
        }
        echo $paginador->footer($usuarioSesion->getLevel() >= 6 && empty($session->getSessionAttribute("returnLink")), $nLink, true, true);
        echo $paginador->filter();
        echo "<div class='mensajes'>$Msj</div>";
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>