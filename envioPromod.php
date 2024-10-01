<?php
#Librerias
session_start();

include_once("check.php");
include_once("libnvo/lib.php");
include_once('./comboBoxes.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();

require_once './services/EnvioPromoService.php';

$Titulo = "Detalle del envio de la promoción";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}

if ($request->hasAttribute("Criterios")) {
    utils\HTTPUtils::setSessionValue("Criterios", $request->getAttribute("Criterios"));
}
$CriteriosG = utils\HTTPUtils::getSessionValue("Criterios");
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);
$EnvioPromoDAO = new EnvioPromoDAO();
$EnvioPromoVO = new EnvioPromoVO();
$EnvioPromoVO = $EnvioPromoDAO->retrieve($busca);

$Return = "envioPromo.php";
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">

    <head>
        <?php require './config_omicrom.php'; ?>
        <script type="text/javascript" src="js/envioPromo.js"></script>
        <title><?= $Gcia ?></title>
        <script type="text/javascript">
            $(document).ready(function () {
                if ("<?= $busca ?>" === "NUEVO") {
                    $("#FechaInicial").val("<?= date("Y-m-d H:i:s") ?>");
                    $("#FechaCreacion").val("<?= date("Y-m-d H:i:s") ?>");
                    $("#FechaFinal").val("<?= date("Y-m-d H:i:s") ?>");
                } else {
                    $("#FechaInicial").val("<?= $EnvioPromoVO->getFecha_inicio() ?>");
                    $("#FechaCreacion").val("<?= $EnvioPromoVO->getFecha_creacion() ?>");
                    $("#FechaFinal").val("<?= $EnvioPromoVO->getFecha_final() ?>");
                    $("#Producto").val("<?= $EnvioPromoVO->getId_producto() ?>");
                    $("#Descuento").val("<?= $EnvioPromoVO->getDescuento() ?>");
                    $("#Consumo_Min").val("<?= $EnvioPromoVO->getConsumo_min() ?>");
                }

                if ("<?= $EnvioPromoVO->getStatus() ?>" !== "Abierto") {
                    $(".StatusCancelacion").hide();
                }
            });
        </script>
    </head>
    <body>
        <?php BordeSuperior(); ?>
        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;width: 120px;" class="nombre_cliente">
                    <a href="<?= $Return ?>"><div class="RegresarCss " alt="Flecha regresar" style="">Regresar</div></a>
                </td>
                <td style="vertical-align: top;">
                    <div id="FormulariosBoots">
                        <div class="container no-margin">
                            <div class="row no-padding">
                                <div class="col-12 background container no-margin">
                                    <form name="formulario1" id="formulario1" method="post" action="">
                                        <div class="row no-padding">
                                            <div class="col-12">
                                                <table style="height: 350px; width: 100%;" summary="Envia promo detalle">
                                                    <tr><th colspan="2" id="Header_Envio_Promo"></th></tr>
                                                    <tr>
                                                        <td style="height: 30px;padding-left: 60px;" class="subtitulos">Id : <?= $busca ?></td>
                                                        <td style="text-align: right;padding-right: 60px;font-size: 17px;color: #606060;" title="Los consumos se calculan desde 30 días atrás hasta el día actual"  class="StatusCancelacion">
                                                            Organizar clientes por:
                                                            <?php
                                                            if ($CriteriosG === "Todos") {
                                                                ?><a class="tablelink" href="#" style="margin-left: 20px;color:#EC7063 "><em class="fa-solid fa-globe"></em> Todos</a><?php
                                                            } else {
                                                                ?><a class="tablelink" href="envioPromod.php?Criterios=Todos" style="margin-left: 20px;"><em class="fa-solid fa-globe"></em> Todos</a><?php
                                                            }
                                                            if ($CriteriosG === "Mas") {
                                                                ?><a class="tablelink" href="#" style="margin-left: 20px;color:#EC7063 "><em class="fa-solid fa-plus"></em> Mas consumos</a><?php
                                                            } else {
                                                                ?><a class="tablelink" href="envioPromod.php?Criterios=Mas" style="margin-left: 20px;"><em class="fa-solid fa-plus"></em> Mas consumos</a><?php
                                                            }
                                                            if ($CriteriosG === "Menos") {
                                                                ?><a class="tablelink" href="#" style="margin-left: 20px;color:#EC7063 "><em class="fa-solid fa-minus"></em> Menos consumos</a><?php
                                                            } else {
                                                                ?><a class="tablelink" href="envioPromod.php?Criterios=Menos" style="margin-left: 20px;"><em class="fa-solid fa-minus"></em> Menos consumos</a><?php
                                                            }
                                                            ?>

                                                        </td>
                                                    </tr>
                                                    <tr class="StatusCancelacion">
                                                        <td style="height: 20px;padding-left: 55px;">
                                                            Cliente :
                                                        </td>
                                                        <td>
                                                            <?php
                                                            if ($CriteriosG === "Mas") {
                                                                $Order = "DESC;";
                                                                $Add = "AND rm.fecha_venta >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 30 DAY), '%Y%m%d')";
                                                            } else if ($CriteriosG === "Menos") {
                                                                $Order = "ASC;";
                                                                $Add = "AND rm.fecha_venta >= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 30 DAY), '%Y%m%d')";
                                                            }
                                                            $Cli = "SELECT id, CONCAT(id,'.- ',nombre,'. No. Ventas :',cnt) nombre FROM 
                                                                    (SELECT cli.id,cli.nombre,cli.telefono,COUNT(1)  cnt FROM 
                                                                    cli LEFT JOIN rm ON cli.id=rm.cliente WHERE cli.activo = 'Si'  $Add
                                                                    AND cli.id > 10 AND cli.telefono <> '' GROUP BY cli.id) 
                                                                    cli ORDER BY cnt ";
                                                            $Cli = $Cli . $Order;
                                                            $CliSql = utils\IConnection::getRowsFromQuery($Cli);
                                                            ?>
                                                            <select name="Cliente" id="Cliente">
                                                                <option value="*">Todos los clientes que contengan telefono</option>
                                                                <?php
                                                                foreach ($CliSql as $rsC) {
                                                                    ?>
                                                                    <option value="<?= $rsC["id"] ?>"><?= $rsC["nombre"] ?></option>
                                                                    <?php
                                                                }
                                                                ?>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="submit" name="BotonD" value="Agregar">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td colspan="3" valign='top' style="padding-top: 15px;">
                                                            <table style="width: 90%;margin-left: 5%;background-color: white;border: 1px solid black;" summary="Detalle del envio de promo">
                                                                <thead>
                                                                    <tr style="background-color: #006666;color:white;"><th>Id</th><th>Cliente</th><th>No.Telefono</th><th class="StatusCancelacion">Eliminar</th></tr>
                                                                </thead>
                                                                <tbody style="height:250px;">
                                                                    <?php
                                                                    $SqlD = "SELECT envioPromod.idnvo idEnv,cli.id,cli.nombre,cli.telefono FROM envioPromod "
                                                                            . "LEFT JOIN cli ON cli.id=envioPromod.id_authuser WHERE envioPromod.id = " . $busca;
                                                                    $Rsd = utils\IConnection::getRowsFromQuery($SqlD);
                                                                    $e = 0;
                                                                    foreach ($Rsd as $rs) {
                                                                        $HtmlColor = $e % 2 == 0 ? "#D4D4D4" : "";
                                                                        ?>
                                                                        <tr style="height: 20px;background-color: <?= $HtmlColor ?>;">
                                                                            <td><?= $rs["id"] ?></td>
                                                                            <td><?= $rs["nombre"] ?></td>
                                                                            <td><?= $rs["telefono"] ?></td>
                                                                            <td style="text-align: center;" class="StatusCancelacion"><em data-idpd="<?= $rs["idEnv"] ?>" class="fa-solid fa-trash Delete" ></em></td>
                                                                        </tr>
                                                                        <?php
                                                                        $e++;
                                                                    }
                                                                    ?>
                                                                    <tr><td colspan="4"></td></tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    <tr class="StatusCancelacion">
                                                        <td colspan="3" style="text-align: center"> 
                                                            <input type="button" name="Enviar_Promo" id="Enviar_Promo" value="Lanza promoción" class="botonPromocion">
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                        <input type="hidden" name="busca" id="busca" value="<?= $busca ?>">
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>
    </body>
    <script type="text/javascript">
        $(document).ready(function () {
            $("#Enviar_Promo").click(function () {
                Swal.fire({
                    icon: '<?= utils\Messages::QUESTIONICON ?>',
                    iconColor: '#3498DB',
                    title: "¿Estas seguro de enviar promoción a los clientes?",
                    background: "#ABEBC6",
                    timer: 10000,
                    showConfirmButton: true,
                    showCancelButton: true,
                    confirmButtonText: "Aceptar",
                    cancelButtonText: "Cancelar",
                    cancelButtonColor: "red"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "envioPromod.php?op=LanzarPromo";
                    }
                });
            });
        });
    </script>
</html>