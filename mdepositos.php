<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require_once './services/CambioTurnoService.php';

$request = utils\HTTPUtils::getRequest();
$mysqli = iconnect();

$session = new OmicromSession("ven.alias", "ven.alias", $nameVariableSession);
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));
$Id = 69;

$paginador = new Paginador($Id,
        "ctdep.id,ctdep.despachador,IF(tipo_cambio <> 1,'USD','MXN') cambio",
        "LEFT JOIN ven ON ctdep.despachador = ven.id",
        "GROUP BY despachador",
        "ctdep.corte = '$Corte' ",
        $session->getSessionAttribute("sortField"),
        $session->getSessionAttribute("criteriaField"),
        utils\Utils::split($session->getSessionAttribute("criteria"), "|"),
        strtoupper($session->getSessionAttribute("sortType")),
        $session->getSessionAttribute("page"),
        "REGEXP",
        "cambiotur.php",
        "(SELECT tipo_cambio,fecha,despachador,SUM(total) total,id,corte FROM ctdep WHERE ctdep.corte = '$Corte' GROUP BY despachador)
    ctdep");

$ctVO = new CtVO();
if ($Corte > 0) {
    $ctVO = $ctDAO->retrieve($Corte);
}

if ($ctVO->getStatus() === StatusCorte::ABIERTO) {
    $cSqlH = "
            SELECT ven.id ven,CONCAT(LPAD(ven.id,2,0),' | ',ven.alias) alias 
            FROM ven 
            WHERE ven.activo = 'Si' AND ven.id >= 50
            ORDER BY ven.alias ASC";
} else {
    $cSqlH = "
            SELECT rm.vendedor ven,CONCAT(LPAD(rm.vendedor,2,0),' | ',ven.alias) alias 
            FROM rm 
            LEFT JOIN ven ON rm.vendedor = ven.id 
            LEFT JOIN man ON rm.posicion = man.posicion
            WHERE rm.corte = $Corte  AND man.activo = 'Si'
            GROUP BY vendedor 
            ORDER BY ven.alias ASC";
}
$Vendedores = array();
$Ven = $mysqli->query($cSqlH);
while ($rg = $Ven->fetch_array()) {
    $Vendedores[$rg["ven"]] = $rg["alias"];
}
if ($ctVO->getStatus() === "Abierto") {
    $cSqlBn = "SELECT ven.id, ven.alias FROM man LEFT JOIN ven ON man.despachador = ven.id WHERE man.activo ='Si' ORDER BY id DESC;";
    $VendedoresAct = array();
    $VenAct = $mysqli->query($cSqlBn);
    $result = 1;
    while ($rga = $VenAct->fetch_array()) {
        $VendedoresAct[$rga["id"]] = $rga["alias"];
        $result == 2 ? 1 : $result = $rga["id"] >= 50 ? 1 : 2;
    }
}
$Titulo = "Corte: $Corte turno: " . $ctVO->getTurno() . " " . $ctVO->getFecha() . " ";

$selecttCtdep = "SELECT SUM(total) total FROM ctdep WHERE corte = '$Corte'";
$Total = $mysqli->query($selecttCtdep)->fetch_array();

$self = utils\HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#autocomplete").focus();
                $("#CleanDetail").click(function () {
                    $(".DetallePago").hide();
                });
                $(".infoDetalle").click(function () {
                    var thisb = this;
                    var despachador, corte;
                    despachador = this.dataset.despachador;
                    corte = this.dataset.corte;
                    $(".DetallePago").hide();
                    jQuery.ajax({
                        type: 'GET',
                        url: 'getByAjax.php',
                        dataType: 'json',
                        cache: false,
                        data: {"Op": "DetalleDeDepositos", "Despachador": despachador, "Corte": corte},
                        success: function (data) {
                            $(thisb).parent().parent().after(data.Html);
                            console.log(data.Html);
                        },
                        error: function (jqXHR) {
                            console.log(jqXHR);
                        }
                    });
                });
            });
            function redirigir(variable) {
                window.location.href = variable;
            }
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>
        <?php TotalizaDepositos(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr style="background-color: #E1E1E1;font-weight: bold;text-align: center;height: 25px;">
                <td style="width: 25%;background-color: #F63;color: white;">Depositos</td>
                <td style="width: 25%;" onclick="redirigir('mdepositosd.php')">Desglose monetario</td>
                <td style="width: 25%;" onclick="redirigir('mdepositost.php')">Saldos x despachador</td>
                <td style="width: 25%;" onclick="redirigir('mvendedores.php')">Vendedores x posicion</td>
            </tr> 
            <tr>
                <td colspan='4' align='left'>
                    <div style="text-align: center;color: #990000;"><?= $Msj ?></div>
                    <?php
                    if ($ctVO->getStatusctv() === StatusCorte::ABIERTO) {
                        ?>
                        <form name='form1' method='post' action=''>
                            <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
                                <tr bgcolor ='#E1E1E1' class='texto_tablas'>
                                    <td style="text-align: right">
                                        <?php
                                        if ($result == 1) {
                                            ?>
                                            <select name='DespachadorAct' class='texto_tablas DespachadorAct' required="required">
                                                <option value=''>Seleccionar despachador</option>
                                                <?php
                                                foreach ($VendedoresAct as $key => $value) {
                                                    echo "<option value='$key'>$value</option>";
                                                }
                                                ?>
                                            </select>
                                            <?php
                                        } else {
                                            ?>
                                            <select name='Despachador' class='texto_tablas Despachador' required="required">
                                                <option value=''>Seleccionar despachador</option>
                                                <?php
                                                foreach ($Vendedores as $key => $value) {
                                                    echo "<option value='$key'>$value</option>";
                                                }
                                                ?>
                                            </select>
                                            <?php
                                        }
                                        ?>
                                    </td>
                                    <td style="text-align: right">$0.50:&nbsp;<input type='text' name='M0050c' size="3"></td>
                                    <td style="text-align: right">$1:   &nbsp;<input type='text' name='M0001p' size="3"></td>
                                    <td style="text-align: right">$2:   &nbsp;<input type='text' name='M0002p' size="3"></td>
                                    <td style="text-align: right">$5:   &nbsp;<input type='text' name='M0005p' size="3"></td>
                                    <td style="text-align: right">$10:  &nbsp;<input type='text' name='M0010p' size="3"></td>
                                    <td style="text-align: right">$20:<input type='text' name='M0020p' size='3'></td>
                                </tr>
                                <tr bgcolor ='#E1E1E1' class='texto_tablas'>
                                    <td style="text-align: right">
                                        <input type='radio' name='Tipo_moneda' value='1' checked > MXN
                                        <input type='radio' name='Tipo_moneda' value='2' > USD
                                    </td>
                                    <td style="text-align: right">$50:  &nbsp;<input type='text' name='M0050p' size='3'></td>
                                    <td style="text-align: right">$100: &nbsp;<input type='text' name='M0100p' size='3'></td>
                                    <td style="text-align: right">$200: &nbsp;<input type='text' name='M0200p' size='3'></td>
                                    <td style="text-align: right">$500: &nbsp;<input type='text' name='M0500p' size='3'></td>
                                    <td style="text-align: right">$1000:&nbsp;<input type='text' name='M1000p' size="3"></td>
                                    <td style="text-align: right"><input class='nombre_cliente' type='submit' name='BotonColectas' value='Agregar' class='texto_tablas'></td>
                                </tr>
                            </table>

                        </form>
                    <?php } ?>
                </td>
            </tr>
        </table>
        <table style="width: 100%;">
            <tr>
                <td style="text-align: right;"><i class="fa-solid fa-broom" id="CleanDetail" style="color: #27AE60;"></i></td>
            </tr>
        </table>
        <div id="TablaDatos" style="min-height: 180px;">
            <table class="paginador2" aria-hidden="true">
                <?php
                echo $paginador->headers(array("Imprimir", "Tipo Cambio"), array());
                $des = "";
                $subI = 0;
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();

                    if ($des == "") {
                        $des = $row["alias"];
                    } else {
                        if ($des !== $row["alias"]) {
                            echo "<tr><td colspan='5' class='upTitles'>Sub-total:</td><td class='upTitles'>" . number_format($subI, 2) . "</strong></td><td class='upTitles'></td></tr>";
                            $subI = 0;
                            $des = $row["alias"];
                        }
                    }
                    ?>
                    <tr>
                        <td align="center">
                            <i class="fa-solid fa-circle-info infoDetalle" style="color: rgb(255, 102, 51);margin-right: 20px;"data-despachador="<?= $row["despachador"] ?>" data-corte="<?= $Corte ?>"></i>
                            <a class="textosCualli_i_n" href=javascript:winmin("mdepositos_imp.php?busca=<?= $row["id"] ?>&Op=1");><i class="icon fa fa-lg- fa-print" aria-hidden="true"></i></a>
                        </td>                   
                        <td><?= $row["cambio"] ?></td>
                        <td><?= $row["id"] ?></td>
                        <td><?= $row["fecha"] ?></td>
                        <td><?= $row["despachador"] ?></td>
                        <td><?= substr(ucwords(strtolower($row["alias"])), 0, 50) ?></td>
                        <td align="right">$ <?= number_format($row["total"], 2) ?></td>
                    </tr>
                    <?php
                    if ($row["total"] > 0) {
                        $subI += $row["total"];
                        $nImp += $row["pesos"];
                        $nImpTot += $row["total"];
                    }
                }
                echo "<tr><td colspan='5' class='upTitles'>Sub-total:</td><td class='upTitles'>" . number_format($subI, 2) . "</strong></td><td class='upTitles'></td></tr>";
                ?>
            </table>
        </div>

        <?php
        $Tot = number_format($Total["total"], 2);
        $nLinks["<i class='icon fa fa-lg fa-print' aria-hidden=\"true\"></i> Reporte"] = "javascript:winuni('repdepositos.php?Corte=$Corte');";
        $nLinks["<div style='width: 70%;display: inline-table;text-align: center;'><i class='icon fa fa-lg fa-money' aria-hidden=\"true\"></i> Total <strong>$Tot</strong></div>"] = "#";
        echo $paginador->footer(false, $nLinks, false, false);
        echo $paginador->filter();

        BordeSuperiorCerrar();
        PieDePagina();
        ?>


    </body>
</html>