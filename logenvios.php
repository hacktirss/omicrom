<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$arrayFilter = array("Tipo" => $request->hasAttribute("Tipo") ? $request->getAttribute("Tipo") : 1,
    "FechaI" => date("Y-m-d"), "FechaF" => date("Y-m-d"), "Mes" => date("m"), "Anio" => date("Y"), "Periodo" => "D");
$nameSession = "bitacoraEnvios";
$session = new OmicromSession("logs.fecha_informacion", "logs.fecha_informacion", $nameSession, $arrayFilter, "Tipo");

foreach ($arrayFilter as $key => $value) {
    ${$key} = utils\HTTPUtils::getSessionBiValue($nameSession, $key);
}

$busca = $session->getSessionAttribute("criteria");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

require_once "./services/VolumetricosServices.php";

$Titulo = "Envio de archivos";
$Id = $Tipo == 1 ? 40 : 42;
$add = $Tipo == 1 ? "" : "acuse_sat";

$paginador = new Paginador($Id,
        $add,
        "",
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
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                var Tipo = "<?= $Tipo ?>";
                $(".FechaI").val("<?= $FechaI ?>");
                $(".FechaF").val("<?= $FechaF ?>");
                $("#Periodo").val("<?= $Periodo ?>");
                $("#Anio").val("<?= $Anio ?>");
                $("#Mes").val("<?= $Mes ?>");
                $(".Tipo").val(Tipo);

                const fnPerdiodo = (periodo = "D") => {
                    if (periodo === "D") {
                        $("#PeriodoM").hide();
                        $("#PeriodoD").show();
                    } else {
                        $("#PeriodoD").hide();
                        $("#PeriodoM").show();
                }
                };

                if (Tipo === "1") {
                    $("#form2").hide();
                } else {
                    $("#form1").hide();
                    fnPerdiodo($("#Periodo").val());
                }

                $("#Periodo").on("change", () => {
                    fnPerdiodo($("#Periodo").val());
                });

            });
        </script>
        <style type="text/css">
            #Selector{
                border: 0px solid #fff !important;
                padding-bottom: 10px !important;
            }
        </style>
        <?php $paginador->script(); ?>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <div id="Selector">
            <table aria-hidden="true" style="border: 1px solid #808B96;border-radius: 15px;">
                <tbody>
                    <tr>
                        <?php if ($Tipo == 2) { ?>
                            <td style="background-color: #CACACA;width: 33%;border-radius: 15px 0px 0px 15px;"><a href="logenvios.php?criteria=ini&Tipo=1&FechaI=<?= $FechaI ?>&FechaF=<?= $FechaF ?>">Envios a Pemex</a></td>
                            <td style="background-color: #FF6633;width: 33%;border-radius: 0px 15px 15px 0px;"">Envios al SAT</td>
                        <?php } else { ?>
                            <td style="background-color: #FF6633;width: 33%;border-radius: 15px 0px 0px 15px;">Envios a Pemex</td>
                            <td style="background-color: #CACACA;width: 33%;border-radius: 0px 15px 15px 0px;"><a href="logenvios.php?criteria=ini&Tipo=2&FechaI=<?= $FechaI ?>&FechaF=<?= $FechaF ?>"">Envios al SAT</a></td>
                        <?php } ?>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="TablaDatos">
            <table class="paginador" aria-hidden="true">
                <?php
                echo $paginador->headers(array("Editar", "Acuse", "Xml", $Tipo == 2 ? "Resumen" : ""), array($Tipo == 1 ? "Enviar" : ""));
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr>
                        <td class="alignCenter">
                            <a href="<?= $cLink ?>?busca=<?= $row["id"] ?>&Tipo=<?= $Tipo ?>"><i class="icon fa fa-lg fa-edit" aria-hidden="true"></i></a>
                        </td>
                        <td class="alignCenter">
                            <a href=javascript:wingral("pdflogenv.php?busca=<?= $row["id"] ?>&Tipo=<?= $Tipo ?>"); title="Mostrar acuse"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></a>
                        </td>
                        <td class="alignCenter">
                            <a href="<?= $self ?>?archivo=<?= $row["archivo"] ?>&Tipo=<?= $Tipo ?>" title="Descargar Archivo"><i class="icon fa fa-lg fa-download" aria-hidden="true"></i></a>
                        </td>
                        <td class="alignCenter">
                            <?php if ($Tipo == 2) { ?>
                                <a href="#" onclick="wingral('imprep_envios_sat.php?FechaI=<?= $row["fecha_informacion"] ?>&FechaF=<?= $row["fecha_informacion"] ?>')" title="Resumen"><i class="icon fa fa-lg fa-list-alt" aria-hidden="true"></i></a>
                            <?php } ?>
                        </td>
                        <?php echo $paginador->formatRow(); ?>
                        <td class="alignCenter">
                            <?php if ($Tipo == 1 && $row["codigodeenvio"] == 0 && $usuarioSesion->getLevel() == UsuarioDAO::LEVEL_MASTER) { ?>
                                <a href="<?= $self ?>?send=Si&cId=<?= $row["id"] ?>&Tipo=<?= $Tipo ?>" title="Enviar Archivo"><i class="icon fa fa-lg fa-send" aria-hidden="true"></i></a>
                                <?php
                            } else {
                                echo $row["acuse_sat"] === "" ? '<i style="color:green" class="fa fa-check" aria-hidden="true"></i>' : "<a href=javascript:winmin('SubeAcuse.php?busca=" . $row["id"] . "');><i class='fa fa-upload' aria-hidden='true'></i></a>";
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
        if ($Tipo == 1) {
            $nLinks["<i class='icon fa fa-lg fa-list-alt' aria-hidden=\"true\"></i> Concentrado por fecha"] = "javascript:wingral('exportacv.php?criteria=ini&op=Si');";
        } elseif ($Tipo == 2) {
            $nLinks["<i class='icon fa fa-lg fa-list-alt' aria-hidden=\"true\"></i> Reporte del SAT"] = "javascript:wingral('imprep_envios_sat.php?criteria=ini');";
        }
        echo $paginador->footer(false, $nLinks, false, true);
        echo $paginador->filter();
        ?>

        <div id="FormulariosBoots" style="margin-top: 10px;">
            <form name="form1" id="form1" method="post" action="">
                <div class="container no-margin" style="margin-bottom: 5px;">
                    <div class="row no-padding">
                        <div class="col-1 align-right withBackground" style="height: 30px;padding-top: 5px;">De:</div>
                        <div class="col-2 withBackground" style="height: 30px;padding-top: 5px;"><input type="date" name="FechaI" class="FechaI"></div>
                        <div class="col-1 align-right withBackground" style="height: 30px;padding-top: 5px;">A:</div>
                        <div class="col-2 withBackground" style="height: 30px;padding-top: 5px;"><input type="date" name="FechaF" class="FechaF"></div>
                        <div class="col-2 align-right withBackground" style="height: 30px;padding-top: 5px;"><input type="submit" name="Boton" class="Boton" value="Generar"></div>
                        <div class="col-4 withBackground" style="height: 30px;padding-top: 5px;"></div>
                    </div>
                </div>
                <input type="hidden" name="Tipo" class="Tipo">
            </form>
            <form name="form2" id="form2" method="post" action="">
                <div class="container no-margin" style="margin-bottom: 5px;">
                    <div class="row no-padding" style="height: 30px;padding-top: 5px;">
                        <div class="col-1 align-right withBackground" style="height: 30px;padding-top: 5px;">Periodo:</div>
                        <div class="col-2 withBackground" style="height: 30px;padding-top: 5px;">
                            <select name="Periodo" id="Periodo">
                                <option value="D">DIARIO</option>
                                <option value="M">MENSUAL</option>
                            </select>
                        </div>
                        <div class="col-1 align-right withBackground" style="height: 30px;padding-top: 5px;">Formato:</div>
                        <div class="col-1 withBackground" style="height: 30px;padding-top: 5px;">
                            <select name="Formato" id="Formato">
                                <option value="XML">XML</option>
                                <option value="JSON">JSON</option>
                            </select>
                        </div>
                        <div id="PeriodoD" class="col-6">
                            <div class="row no-padding">
                                <div class="col-2 align-right withBackground" style="height: 30px;padding-top: 5px;">De:</div>
                                <div class="col-4 withBackground" style="height: 30px;padding-top: 5px;"><input type="date" name="FechaI" class="FechaI"></div>
                                <div class="col-2 align-right withBackground" style="height: 30px;padding-top: 5px;">A:</div>
                                <div class="col-4 withBackground" style="height: 30px;padding-top: 5px;"><input type="date" name="FechaF" class="FechaF"></div>
                            </div>
                        </div>
                        <div id="PeriodoM" class="col-6">
                            <div class="row no-padding">
                                <div class="col-4 align-right withBackground" style="height: 30px;padding-top: 5px;">De:</div>
                                <div class="col-4 withBackground" style="height: 30px;padding-top: 5px;">
                                    <select name="Mes" id="Mes">
                                        <?php
                                        foreach (getMonts() as $key => $value) {
                                            echo "<option value='$key'>$value</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-4 withBackground" style="height: 30px;padding-top: 5px;">
                                    <select name="Anio" id="Anio">
                                        <?php
                                        foreach (getYears() as $key => $value) {
                                            echo "<option value='$key'>$value</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-1 align-left withBackground" style="height: 30px;padding-top: 5px;"><input type="submit" name="Boton" class="Boton" value="Generar"></div>
                    </div>
                </div>
                <input type="hidden" name="Tipo" class="Tipo">
            </form>
        </div>

        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>
    </body>
</html>