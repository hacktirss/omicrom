<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$usuarioSesion = getSessionUsuario();
$Cliente = utils\HTTPUtils::getSessionValue("Cuenta");

if ($request->hasAttribute("busca")) {
    utils\HTTPUtils::setSessionValue("busca", $request->getAttribute("busca"));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue("busca", $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue("busca");

$Titulo = "Unidades por cliente";
$Permiso = "SELECT receive_msg FROM omicrom.authuser WHERE id = " . $usuarioSesion->getId();
$RMsj = utils\IConnection::execSql($Permiso);
if ($request->hasAttribute("Boton")) {

    $ComA = $mysqli->query("SELECT id,descripcion FROM com WHERE activo='Si' ORDER BY id");
    while ($rg = $ComA->fetch_array()) {
        $Comb = 'c' . $rg['id'];
        if ($request->hasAttribute($Comb)) {
            $cNumero = $cNumero . $rg['id'];
        }
    }
    $SqlBusca = "SELECT periodo FROM unidades WHERE id = $busca";
    $UnSql = utils\IConnection::execSql($SqlBusca);
    if ($UnSql["periodo"] !== "B" && $request->getAttribute("Periodo") !== "B") {
        $updateUnidades = "UPDATE unidades SET "
                . "domi = '" . $request->getAttribute("DomI") . "', domf = '" . $request->getAttribute('DomF') . "', "
                . "luni = '" . $request->getAttribute("LunI") . "', lunf = '" . $request->getAttribute("LunF") . "', "
                . "mari = '" . $request->getAttribute("MarI") . "', marf = '" . $request->getAttribute("MarF") . "', "
                . "miei = '" . $request->getAttribute("MieI") . "', mief = '" . $request->getAttribute("MieF") . "', "
                . "juei = '" . $request->getAttribute("JueI") . "', juef = '" . $request->getAttribute("JueF") . "', "
                . "viei = '" . $request->getAttribute("VieI") . "', vief = '" . $request->getAttribute("VieF") . "', "
                . "sabi = '" . $request->getAttribute("SabI") . "', sabf = '" . $request->getAttribute("SabF") . "', "
                . "descripcion = '" . $request->getAttribute("Descripcion") . "', placas = '" . $request->getAttribute("Placas") . "', "
                . "combustible = '" . $cNumero . "', litros = '" . $request->getAttribute("Litros") . "', "
                . "importe = '" . $request->getAttribute("Importe") . "', estado = '" . $request->getAttribute("Estado") . "', "
                . "nip = '" . $request->getAttribute("Nip") . "', periodo = '" . $request->getAttribute("Periodo") . "', "
                . "simultaneo = '" . $request->getAttribute("Simultaneo") . "' "
                . "WHERE id = '" . $busca . "'";
    } else {
        $updateUnidades = "UPDATE unidades SET "
                . "domi = '" . $request->getAttribute("DomI") . "', domf = '" . $request->getAttribute('DomF') . "', "
                . "luni = '" . $request->getAttribute("LunI") . "', lunf = '" . $request->getAttribute("LunF") . "', "
                . "mari = '" . $request->getAttribute("MarI") . "', marf = '" . $request->getAttribute("MarF") . "', "
                . "miei = '" . $request->getAttribute("MieI") . "', mief = '" . $request->getAttribute("MieF") . "', "
                . "juei = '" . $request->getAttribute("JueI") . "', juef = '" . $request->getAttribute("JueF") . "', "
                . "viei = '" . $request->getAttribute("VieI") . "', vief = '" . $request->getAttribute("VieF") . "', "
                . "sabi = '" . $request->getAttribute("SabI") . "', sabf = '" . $request->getAttribute("SabF") . "', "
                . "descripcion = '" . $request->getAttribute("Descripcion") . "', placas = '" . $request->getAttribute("Placas") . "', "
                . "combustible = '" . $cNumero . "', litros = '" . $request->getAttribute("Litros") . "', "
                . "estado = '" . $request->getAttribute("Estado") . "', nip = '" . $request->getAttribute("Nip") . "', "
                . "simultaneo = '" . $request->getAttribute("Simultaneo") . "' "
                . "WHERE id = '" . $busca . "'";
    }
    if ($mysqli->query($updateUnidades)) {
        $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
    } else {
        error_log(print_r($mysqli->error, true));
        $Msj = utils\Messages::RESPONSE_ERROR;
    }
    header("Location: cli_tarjetas.php?Msj=$Msj");
}


$selectHe = "SELECT cli.nombre,cli.alias,cli.contacto FROM cli WHERE cli.id = '" . $Cliente . "'";
$He = utils\IConnection::execSql($selectHe);

$selectUnidad = "SELECT * FROM unidades WHERE id = '$busca'";
$Cpo = utils\IConnection::execSql($selectUnidad);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php include './config_omicrom_clientes.php'; ?>  
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#busca").val("<?= $busca ?>");

                $("#Placas").val("<?= $Cpo["placas"] ?>");
                $("#Descripcion").val("<?= $Cpo["descripcion"] ?>");
                $("#Litros").val("<?= $Cpo["litros"] ?>");
                $("#Importe").val("<?= $Cpo["importe"] ?>");
                $("#Periodo").val("<?= $Cpo["periodo"] ?>");
                $("#Simultaneo").val("<?= $Cpo["simultaneo"] ?>");
                $("#Estado").val("<?= $Cpo["estado"] ?>");
                $("#Nip").val("<?= $Cpo["nip"] ?>");

                $("#DomI").val("<?= $Cpo["domi"] ?>");
                $("#DomF").val("<?= $Cpo["domf"] ?>");
                $("#LunI").val("<?= $Cpo["luni"] ?>");
                $("#LunF").val("<?= $Cpo["lunf"] ?>");
                $("#MarI").val("<?= $Cpo["mari"] ?>");
                $("#MarF").val("<?= $Cpo["marf"] ?>");
                $("#MieI").val("<?= $Cpo["miei"] ?>");
                $("#MieF").val("<?= $Cpo["mief"] ?>");
                $("#JueI").val("<?= $Cpo["juei"] ?>");
                $("#JueF").val("<?= $Cpo["juef"] ?>");
                $("#VieI").val("<?= $Cpo["viei"] ?>");
                $("#VieF").val("<?= $Cpo["vief"] ?>");
                $("#SabI").val("<?= $Cpo["sabi"] ?>");
                $("#SabF").val("<?= $Cpo["sabf"] ?>");

                if ("<?= $Cpo["periodo"] ?>" === "B") {
                    $("#Importe").prop("disabled", true);
                } else {
                    $("#Importe").prop("disabled", false);
                }
            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(TRUE); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="cli_tarjetas.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">
                    <div style="width: 98%;margin-left: auto;margin-right: auto;border: 2px solid gray;margin-bottom: 10px;padding: 3px 1px;">
                        <table style="width: 98%;margin-left: auto;margin-right: auto;" class="texto_tablas" aria-hidden="true">
                            <tr style="background-color: #E1E1E1;height: 20px;">
                                <td><strong>Id:</strong> <?= $busca ?></td>
                                <td><strong>Nombre:</strong> <?= ucwords(strtolower($He["nombre"])) ?></td>
                                <td><strong>Alias:</strong> <?= ucwords(strtolower($He["alias"])) ?></td>
                            </tr>
                        </table>
                    </div>

                    <form name="form1" method="get" action="">
                        <table style="width: 98%;margin-left: auto;margin-right: auto;" class="texto_tablas" aria-hidden="true">
                            <tr>
                                <td align="right" bgcolor="#e1e1e1" class="nombre_cliente"><span style="color: red;"><strong>*&nbsp;</strong></span>Placas del vehiculo: &nbsp;</td>
                                <td><input type="text" name="Placas" id="Placas" value="" size="20"  placeholder="25H-58-52"></td>
                            </tr>

                            <tr>
                                <td align="right" bgcolor="#e1e1e1" class="nombre_cliente">Descripcion: &nbsp;</td>
                                <td><input type="text" name="Descripcion" id="Descripcion" size="20" ></td>
                            </tr>

                            <tr>
                                <td align="right" bgcolor="#e1e1e1" class="nombre_cliente"><span style="color: red;"><strong>*&nbsp;</strong></span>Litros: &nbsp;</td>
                                <td><input type="text" name="Litros" id="Litros" size="20"></td>
                            </tr>

                            <tr id="ImporteHidden">
                                <td align="right" bgcolor="#e1e1e1" class="nombre_cliente"><span style="color: red;"><strong>*&nbsp;</strong></span><strong>o&acute;</strong> Importe: &nbsp;</td>
                                <td><input type="text" name="Importe" id="Importe" size="20"></td>
                            </tr>

                            <tr>
                                <td align="right" bgcolor="#e1e1e1" class="nombre_cliente"><span style="color: red;"><strong>*&nbsp;</strong></span>Periodo: &nbsp;</td>
                                <td>
                                    <select name="Periodo" id="Periodo" class="texto_tablas">
                                        <option value="D">Diario</option>
                                        <option value="S">Semanal</option>
                                        <option value="Q">Quincenal</option>
                                        <option value="M">Mensual</option>
                                        <option value="B">Saldos</option>
                                    </select>
                                </td>
                            </tr>

                            <tr><td align="right" bgcolor="#e1e1e1" class="nombre_cliente"><span style="color: red;"><strong>*&nbsp;</strong></span>Combustibles: &nbsp;</td><td>
                                    <?php
                                    $Combustibles = " " . $Cpo["combustible"];
                                    $ComA = $mysqli->query("SELECT id, descripcion FROM com WHERE activo = 'Si' ORDER BY id");
                                    $nCnt = 1;
                                    while ($rg = $ComA->fetch_array()) {
                                        $Comb = 'c' . $rg['id'];
                                        $nValor = $rg['id'];
                                        if (strrpos($Combustibles, $nValor)) {
                                            echo "<input type='checkbox' name='$Comb' checked > ";
                                        } else {
                                            echo " <input type='checkbox' name='$Comb'> ";
                                        }
                                        echo ucwords(strtolower($rg['descripcion'])) . "&nbsp; ";
                                    }
                                    ?>
                                </td>
                            </tr>


                            <tr>
                                <td align="right" bgcolor="#e1e1e1"  class="nombre_cliente">Cargas Simultaneas: &nbsp;</td>
                                <td>
                                    <select name="Simultaneo" id="Simultaneo" class="texto_tablas">
                                        <option value="1">Permitir</option>
                                        <option value="0">Bloquear</option>
                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <td align="right" bgcolor="#e1e1e1" class="nombre_cliente"><span style="color: red;"><strong>*&nbsp;</strong></span>Status: &nbsp;</td>
                                <td>
                                    <select name="Estado" id="Estado" class="texto_tablas">
                                        <option value="a">Activo</option>
                                        <option value="d">Inactivo</option>

                                    </select>
                                </td>
                            </tr>

                            <tr>
                                <td align="right" bgcolor="#e1e1e1" class="nombre_cliente">Nip: &nbsp;</td>
                                <td><input type="text" name="Nip" id="Nip" size="20"></td>
                            </tr>

                        </table>


                        <p align="center" class="texto_tablas"><strong>Horario de carga por dia</strong></p>
                        <table style="width: 98%;margin-left: auto;margin-right: auto;" class="texto_tablas" aria-hidden="true">
                            <tr>
                                <td align="center">
                                    <?php generaTiraHoras() ?>
                                </td>
                            </tr>

                            <?php
                            if ($RMsj["receive_msg"] == 1) {
                                echo "<tr><td colspan='2' align='center'>";
                                if (is_numeric($busca)) {
                                    echo "<input type='submit' class='nombre_cliente' name='Boton' value='Actualizar'>";
                                } else {
                                    echo "<input type='submit' class='nombre_cliente' name='Boton' value='Agregar'>";
                                }
                                echo "</td><tr>";
                            }
                            ?>
                        </table>

                        <input type="hidden" name="busca" id="busca" >
                    </form>

                    <div style="text-align: left;" class="texto_tablas">(&nbsp;<span style="color: red;"><strong>*&nbsp;</strong></span>) Campos necesarios para control de venta</div>
                </td>
            </tr>
        </table>

        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>