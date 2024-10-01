<?php
#Librerias
session_start();
set_time_limit(720);

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("comboBoxes.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();

require './services/envioEfectivoService.php';
$nameVariableSession = "EnvioEfectivoDetalle";

//$Gfmt = utils\HTTPUtils::getSessionBiValue("catalogoFacturas", "fmt");          //Formato
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));
$EnvEfectivoVO = new Env_efectivoVO();
$EnvEfectivoDAO = new Env_efectivoDAO();
$Titulo = "Modulo detalle de envios de efectivo";
$nameVarBusca = "busca";
$session = new OmicromSession("eed.id_corte", "eed.id_corte");
$busca = $session->getSessionAttribute("criteria");
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

$ciaDAO = new CiaDAO();
$ciaVO = $ciaDAO->retrieve(1);

$version_cfdi = $ciaVO->getVersion_cfdi();

$pacA = $mysqli->query("SELECT * FROM proveedor_pac WHERE activo = 1");
$pac = $pacA->fetch_array();

if (is_numeric($busca)) {
    $EnvEfectivoVO = $EnvEfectivoDAO->retrieve($busca);
}
$sql = "SELECT ee.id,b.concepto FROM env_efectivo ee LEFT JOIN bancos b ON ee.id_banco=b.id";
$Rst = utils\IConnection::execSql($sql);
$Id = 155;
$paginador = new Paginador($Id,
        "id",
        "",
        "",
        "id_ee = '$busca'",
        $session->getSessionAttribute("sortField"),
        $session->getSessionAttribute("criteriaField"),
        utils\Utils::split($session->getSessionAttribute("criteria"), "|"),
        strtoupper($session->getSessionAttribute("sortType")),
        $session->getSessionAttribute("page"),
        "REGEXP",
        "envioEfectivo.php?criteria=ini");
$tableContents = $paginador->getTableContents();

/**
 * 0.- Sin registros.
 * 1.- Tickets
 * 2.- Abiertas
 */
$registrosfc = 0;
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom.php"; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#BotonNvo").click(function () {
                    console.log("Carga");
                });
                $("#ScrollAdd").scroll();
                if ("<?= $EnvEfectivoVO->getStatus() ?>" !== "Abierto") {
                    $(".StatusCerrado").hide();
                }
                $("#Ticket").focus();
                variable = parseFloat($("#ValAcumulado").val());
                $("#ImpTotal").html(parseFloat(variable).toFixed(2));
                i = 0
                $(".IdCorteD").click(function () {
                    console.log(this.dataset.id);
                    console.log(this);
                    jQuery.ajax({
                        type: 'GET',
                        url: 'getByAjax.php',
                        dataType: 'json',
                        cache: false,
                        data: {"Origen": "EditaEnvio", "IdIdentifica": this.dataset.id},
                        success: function (data) {
                            $("#EditaImporte").html(data.Rs)
                        },
                        error: function (jqXHR) {
                            console.log(jqXHR);
                        }
                    });
                });

                $("#Guardar").click(function () {
                    if (i == 0) {
                        $('input[type=checkbox]:checked').each(function () {
                            jQuery.ajax({
                                type: 'GET',
                                url: 'getByAjax.php',
                                dataType: 'json',
                                cache: false,
                                data: {"Origen": "InsertEnvios", "Id_ee": "<?= $busca ?>", "Id_corte": $(this).prop("name")},
                                beforeSend: function (xhr) {
                                    $('#RegimenFiscal').empty();
                                },
                                success: function (data) {
                                    console.log(data);
                                    console.log(data.Rs);
                                },
                                error: function (jqXHR) {
                                    console.log(jqXHR);
                                }
                            });
                        });
                        $(location).attr('href', "envioEfectivod.php");
                    }
                    i++;
                });
                $(".botonAnimatedMin").click(function () {
                    id = parseFloat($(this).closest('tr').find('td:eq(2)').text());
                    if ($(this).is(':checked')) {
                        variable = variable + id;
                    } else {
                        variable = variable - id;
                    }
                    console.log(variable);
                    $("#ImporteTotal").val();
                    Txt = "";
                    if (variable > parseFloat($("#ImporteTotal").val())) {
                        Txt = "El importe de la transferencia es menor al total de cortes. El total del envio se vera afectado en caso de continuar";
                    }
                    $("#MsjTotal").html(Txt);
                    $("#ImpTotal").html(parseFloat(variable).toFixed(2));
                });
            });
        </script>
        <?php $paginador->script(); ?>
    </head>

    <body>

        <?php BordeSuperior(); ?>
        <div id="DatosEncabezado" style="border: 1px solid #808B96;">
            <input type="hidden" name="ImporteTotal" id="ImporteTotal" value="<?= $EnvEfectivoVO->getImporte() ?>">
            <table aria-hidden="true">
                <tr>
                    <td><label>Id: </label><span><?= $EnvEfectivoVO->getId() ?></span></td>
                    <td><label>Banco: </label><?= $Rst["concepto"] ?></td>
                    <td><label>Importe: </label><?= number_format($EnvEfectivoVO->getImporte(), 2) ?></td>
                </tr>
                <tr>
                    <td><label>Fecha envio: </label><?= $EnvEfectivoVO->getFecha_envio() ?></td>
                    <td><label>Fecha registro: </label><?= $EnvEfectivoVO->getFecha_creacion() ?></td>
                    <td><label>Status: </label><?= $EnvEfectivoVO->getStatus() ?></td>
                </tr>
                <tr>
                    <td colspan="7"><label>Observaciones: </label><span><?= $EnvEfectivoVO->getDescripcion() ?></span></td>
                </tr>
            </table>
        </div>
        <table style="width: 100%" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="height : 280px !important; text-align : center !important; vertical-align: top !important;">
                    <div id="TablaDatos">
                        <input type="hidden" name="FechaAct" id="FechaAct" value="<?= $He["fecha"] ?>"> 
                        <?php
                        if ($EnvEfectivoVO->getStatus() !== "Abierto") {
                            $Porc = "100%";
                        } else {
                            $Porc = "40%";
                        }
                        ?>
                        <div style="display: inline-block; width: <?= $Porc ?>;height: 295px;">
                            <table class="paginador" id='Tabla_Fac' aria-hidden="true">

                                <caption>Cortes para envio</caption>
                                <?php
                                echo $paginador->headers(array(""), array(""));
                                $arrayComentarios = array();
                                ?>
                                <tbody>
                                    <?php
                                    $sumImp = 0;
                                    while ($paginador->next()) {
                                        $row = $paginador->getDataRow();
                                        ?>
                                        <tr style="background-color: <?= $Color ?>;" <?= $TitleDesc ?>>
                                            <td style="text-align: center; color: #006633;">
                                                <i class="fa fa-pencil-square-o fa-lg IdCorteD StatusCerrado" aria-hidden="true" data-Id='<?= $row["id"] ?>' ></i>
                                            </td>
                                            <?php echo $paginador->formatRow(); ?>
                                            <td style="text-align: center; color: #E74C3C;">
                                                <a class="StatusCerrado" href=javascript:confirmar("Seguro_de_eliminar_este_registro?","envioEfectivod.php?IdD=<?= $row["id"] ?>&Op=Delete&IdR=<?= $busca ?>")><i class="fa fa-trash-o fa-lg" aria-hidden="true"></i></a>
                                            </td>
                                        </tr>
                                        <?php
                                        $sumImp += $row["monto"];
                                    }
                                    ?>
                                    <tr style="font-weight: bold;">
                                        <td colspan="2" style="text-align: right">Total : <?= number_format($sumImp, 2) ?></td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tbody>
                                <input type="hidden" id="ValAcumulado" name="ValAcumulado" value="<?= $sumImp ?>">
                            </table>
                            <div id="EditaImporte"></div>
                        </div>
                        <div style="display: inline-block; width: 35%;" id="ScrollAdd" class="StatusCerrado">
                            <table class="paginador">
                                <caption>Cortes sin enviar</caption>
                                <tr><th>Corte</th><th>Fecha</th><th>Importe</th><th></th></tr>
                                <?php
                                $Sql = "SELECT ct.id,ct.fecha,egr.imp - IFNULL(eev.monto,0) imp,cnc FROM ct "
                                        . "LEFT JOIN (SELECT SUM(importe) imp,corte,GROUP_CONCAT(CONCAT(b.banco,':', importe)) cnc FROM egr "
                                        . "LEFT JOIN bancos b ON egr.clave=b.id "
                                        . "WHERE tm='C' AND corte > 0 GROUP BY corte) egr ON "
                                        . "egr.corte=ct.id "
                                        . "LEFT JOIN (SELECT SUM(monto) monto,id_corte FROM env_efectivod group by id_corte)  eev ON eev.id_corte=egr.corte "
                                        . "WHERE id NOT IN "
                                        . "(SELECT  if((select sum(egr.importe) FROM egr WHERE egr.corte=eed.id_corte) = "
                                        . "SUM(eed.monto),0,eed.id_corte) id_corte FROM env_efectivod eed "
                                        . "LEFT JOIN egr ON eed.id_corte=egr.corte  WHERE ROUND(eed.monto,1) = ROUND(egr.importe,1)) AND statusctv='Cerrado' "
                                        . "AND (egr.imp - IFNULL(eev.monto,0)) > 1";
//                                echo $Sql;
                                $rsEf = utils\IConnection::getRowsFromQuery($Sql);
                                foreach ($rsEf as $ef) {
                                    ?>
                                    <tr title="<?= $ef["cnc"] ?>">
                                        <td style="text-align: right;padding-right: 10px;"><?= $ef["id"] ?></td>
                                        <td><?= $ef["fecha"] ?></td>
                                        <td style="text-align: right;padding-right: 5px;"><?= number_format($ef["imp"], 2, ".", "") ?></td>
                                        <td><input type="checkbox" name="<?= $ef["id"] ?>" class="botonAnimatedMin"></td>
                                    </tr>
                                    <?php
                                    $TtImp += $ef["imp"];
                                }
                                ?>
                                <tr style="font-weight: bold;">
                                    <td></td>
                                    <td style="text-align: right;">Total:</td>
                                    <td><?= number_format($TtImp, 2) ?></td>
                                    <td></td>
                                </tr>
                            </table>
                        </div>
                        <div style="display: inline-block; width: 23%;height: 295px;" class="StatusCerrado">
                            <table>
                                <caption>Totales y cierre de envio</caption>
                                <tr>
                                    <th>
                                        <strong>Importe: <div id="ImpTotal"></div></strong>
                                        <strong style="color:red"><div id="MsjTotal"></div></strong>
                                    </th>
                                </tr>
                                <tr style="height: 80px;padding-top: 40px;">
                                    <td style="text-align: center;"><input type="submit" name="Guardar" id="Guardar" value="Enviar" style="height: 40px;width: 100px;font-size: 17px;"></td>
                                </tr>
                                <tr><td></td></tr>
                                <tr>
                                    <td style="height: 38px;padding-top:2px; ">
                                        <?php
                                        $max = $EnvEfectivoVO->getImporte() + 1;
                                        $min = $EnvEfectivoVO->getImporte() - 1;
                                        if ($EnvEfectivoVO->getImporte() + 1 > $sumImp && $EnvEfectivoVO->getImporte() - 1 < $sumImp && $EnvEfectivoVO->getImporte() > 0) {
                                            ?>
                                            <div style="width: 100%;height: 30px;background-color: #117A65;padding: 8px;border-radius: 5px;color: white;font-weight: bold;font-size: 13px;">
                                                <a href="envioEfectivod.php?Op=Cerrar&IdOp=<?= $busca ?>">Registro listo para ser cerrado</a>
                                            </div>
                                            <?php
                                        }
                                        ?> 
                                    </td>
                                </tr>
                            </table> 
                        </div>
                    </div>
                    <?php
                    echo $paginador->footer(false, $nLink, true, true);
//                    echo $paginador->filter();
                    ?>
                </td>
            </tr>
        </table>

        <?php BordeSuperiorCerrar() ?>
        <?php
        PieDePagina();
        $Periodo = $He["periodo"];
        $Meses = $He["meses"];
        ?>
    </body>
    <style>
        #ScrollAdd {
            width: 100%;
            padding: 5px;
            height: 300px;
            overflow-y: scroll;
            overflow-x: hidden;
        }
    </style>
</html>
