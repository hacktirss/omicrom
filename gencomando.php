<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";
require "./services/ComandosService.php";

$request = utils\HTTPUtils::getRequest();
$usuarioSesion = getSessionUsuario();

if ($request->hasAttribute("busca")) {
    utils\HTTPUtils::setSessionValue("busca", $request->getAttribute("busca"));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue("busca", $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue("busca");
$op = $request->getAttribute("op");

$selectMan = "
        SELECT man.lado,man.productos,man.isla,estado_posiciones.estado,islas.corte,islas.status, 
        man.marca, listas_valor.dispensario, variables_corporativo.valor longitud
        FROM islas,man
        LEFT JOIN estado_posiciones ON man.posicion = estado_posiciones.posicion
        LEFT JOIN (
            SELECT llave_lista_valor, UPPER(valor_lista_valor) dispensario
            FROM listas, listas_valor
            WHERE listas.id_lista = listas_valor.id_lista_lista_valor
            AND listas.nombre_lista = 'MARCA DISPENSARIOS'
        ) listas_valor ON listas_valor.llave_lista_valor = man.marca
        LEFT JOIN variables_corporativo ON TRUE AND llave = 'preset_length'
        WHERE man.posicion = '$busca' AND man.isla = islas.isla";

$Cpo = utils\IConnection::execSql($selectMan);

$selectProductosByPosicion = "
        SELECT man_pro.producto,com.descripcion
        FROM man_pro 
        LEFT JOIN com ON man_pro.producto = com.clavei
        WHERE man_pro.posicion = '$busca' AND man_pro.activo='Si'";

$Combustibles = utils\IConnection::getRowsFromQuery($selectProductosByPosicion);

$Man = utils\IConnection::execSql("SELECT numventas,conteoventas FROM man WHERE id = '$busca'");

$Variables = utils\IConnection::execSql("SELECT UPPER(v.Dispensarios) dispensario, ROUND(MAX(com.precio),2) precio FROM variables v LEFT JOIN com ON TRUE; ");

if ($request->getAttribute("opc") == 1) {
    $Precio = $request->getAttribute("Pesos") . "." . $request->getAttribute("Centavos");
    $selectCombustible = "SELECT descripcion FROM com WHERE clavei='" . $request->getAttribute("Producto") . "'";
    $Com = utils\IConnection::execSql($selectCombustible);
}

$self = utils\HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");
$Fecha = date("Y-m-d H:i:s");
$Dispensario = $Cpo["dispensario"];

$LimiteImporte = 9990;
$LimiteVolumen = (int) ($LimiteImporte / $Variables["precio"]);

if ($Dispensario === "GILBARCO" || $Dispensario === "TEAM"  && $Cpo["longitud"] == 7) {
    $LimiteImporte = 99990;
    $LimiteVolumen = (int) ($LimiteImporte / $Variables["precio"]);
}
error_log($Dispensario);
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require_once './config_omicrom_reports.php'; ?>
        <title><?= $Gcia ?></title>
        <?php
        if (!empty($op)) {
            echo "<meta http-equiv='refresh' content='1;url=gencomando.php?op=9' />";
        }
        ?>
        <script>
            $(document).ready(function () {
                $("#Fecha").val("<?= $Fecha ?>").attr("size", "18");

                var opc = "<?= $request->getAttribute("opc") ?>";

                if (opc === "1") {
                    $("#Pesos").val("<?= $request->getAttribute("Pesos") ?>");
                    $("#Centavos").val("<?= $request->getAttribute("Centavos") ?>");
                    $("#Fecha").val("<?= $request->getAttribute("Fecha") ?>");
                    $("#Producto").val("<?= $request->getAttribute("Producto") ?>");
                }
            });
        </script>
        <style>
            html,body{
                min-width: 350px;
            }
        </style>
    </head>

    <body>

        <?php if (!empty($op)) { ?>
            <p style="text-align: center;padding-top: 30px;"><?= $Msj ?></p>
            <div style="text-align: center;padding-top: 30px;">favor de esperar... <br/>
                <i class="fa fa-spinner fa-pulse fa-4x" aria-hidden="true"></i>
                <span class="sr-only">Loading...</span>
            </div>
        <?php } elseif ($request->getAttribute("opc") == 1) {
            ?>
            <form name="form1" method="get" action="" style="width: 100%">
                <div id="Controles">                
                    <table aria-hidden="true">
                        <tbody>
                            <tr height="40">
                                <td colspan="2" style="text-align: center;font-size: 18px">Atencion!!!</td>
                            </tr>
                            <tr height="32">
                                <td colspan="2" style="text-align: center">Se va a realizar el siguiente cambio de precio</td>
                            </tr>
                            <tr height="32">
                                <td style="text-align: right;">Posicion:</td><td style="text-align: left;"><strong><?= $busca ?></strong></td>
                            </tr>
                            <tr height="32">
                                <td style="text-align: right;">Producto:</td><td style="text-align: left;"><strong><?= $request->getAttribute("Producto") . " | " . $Com["descripcion"] ?> </strong></td>
                            </tr>
                            <tr height="32">
                                <td style="text-align: right;">Fecha de aplicacion:</td><td style="text-align: left;"><strong><?= $request->getAttribute("Fecha") ?></strong></td>
                            </tr>
                            <tr height="32">
                                <td style="text-align: right;">Nuevo precio: </td><td style="text-align: left;"><strong>$ <?= $Precio ?></strong></td>
                            </tr>
                            <tr height="130">
                                <td colspan="2" style="text-align: center">
                                    <input type="submit" name="Boton" value="Hacer cambio">
                                </td>
                            </tr>
                            <tr height="130">
                                <td colspan="2" style="text-align: center">
                                    <a  href="gencomando.php?busca=<?= $busca ?>">Cancelar movimiento</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <input type="hidden" name="Pesos" id="Pesos">
                    <input type="hidden" name="Centavos" id="Centavos">
                    <input type="hidden" name="Fecha" id="Fecha">
                    <input type="hidden" name="Producto" id="Producto">
                    <input type="hidden" name="op" value="3">
                    <input type="hidden" name="busca" value="<?= $busca ?>">                
                </div>
            </form>
            <?php
        } else {
            ?>

            <div id="Controles">
                <div class="Titulo">Envio de comandos</div>
                <div class="Subtitulo">Posicion: <?= $busca ?> &nbsp;  No.manguera(s): <?= $Cpo["productos"] ?></div>
                <table aria-hidden="true">
                    <tbody>
                        <tr>
                            <td>
                                <?php
                                if ($Cpo["estado"] === "e") {
                                    echo "<a href='$self?busca=$busca&op=1'>Bloquear posicion</a>";
                                } else {
                                    echo "Bloquear posicion";
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if ($Cpo["estado"] === "b") {
                                    if ($Cpo["corte"] == 0 || $Cpo["status"] == "Cerrada") {
                                        echo "Desbloquear posicion";
                                    } else {
                                        echo "<a href='$self?busca=$busca&op=2'>Desbloquear posicion</a>";
                                    }
                                } else {
                                    echo "Desbloquear posicion";
                                }
                                ?>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <table aria-hidden="true">
                    <tbody>
                        <?php if ($usuarioSesion->getLevel() >= 8) { ?>
                            <tr>
                                <td>
                                    <form name="form1" method="post" action="gencomando.php" class="Formulario">
                                        <div>Cambio de precio</div>
                                        <table aria-hidden="true">
                                            <tbody>
                                                <tr>
                                                    <td style="text-align: right;">Producto:</td>
                                                    <td>
                                                        <select name="Producto" id="Producto">
                                                            <?php
                                                            foreach ($selectProductosActivos as $rg) {
                                                                echo "<option value='" . $rg["clavei"] . "'>" . $rg["descripcion"] . "</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td colspan="2">Dispesarios: <strong><?= $Dispensario ?></strong></td>
                                                </tr>

                                                <tr>
                                                    <td style="text-align: right;">Pesos: (&dollar;)</td>
                                                    <td>
                                                        <select name="Pesos" id="Pesos">
                                                            <?php
                                                            for ($i = 1; $i <= 99; $i++) {
                                                                $Pesos = cZeros($i, 2);
                                                                echo "<option value='$Pesos'>$Pesos</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>Centavos: (&cent;)</td>
                                                    <td>
                                                        <select name="Centavos" id="Centavos">
                                                            <?php
                                                            for ($i = 0; $i <= 99; $i++) {
                                                                $Valores = cZeros($i, 2);
                                                                echo "<option value='$Valores'>$Valores</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align: right;">Aplicar:</td>
                                                    <td><input type="text" name="Fecha" id="Fecha"></td>
                                                    <td></td>
                                                    <td><input type="submit" name="Boton" value="Enviar"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <input type="hidden" name="opc" value="1">
                                        <input type="hidden" name="busca" value="<?= $busca ?>">
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>

                        <tr>
                            <td>
                                <form name="form2" method="get" action="gencomando.php" class="Formulario">
                                    <div>Prefijado</div>
                                    <table aria-hidden="true">
                                        <tbody>
                                            <tr>
                                                <td style="text-align: right;">Producto:</td>
                                                <td colspan="4">
                                                    <select name="Producto">
                                                        <?php
                                                        foreach ($Combustibles as $rg) {
                                                            echo "<option value='" . $rg["producto"] . "'>" . $rg["descripcion"] . "</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: right;">Importe:</td>
                                                <td><input type="number" name="Pesos" value="1" min="1" max="<?= $LimiteImporte ?>" class="Retro"></td>
                                                <td><input type="number" name="Centavos" value="0" min="0" max="99" class="Retro"></td>
                                                <td><input type="submit" name="Boton" value="Enviar Importe"></td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: right;">Litros:</td>
                                                <td><input type="number" name="Litros" value="1" min="1" max="<?= $LimiteVolumen ?>" class="Retro"></td>
                                                <td><input type="number" name="Mililitros" value="0" min="0" max="99" class="Retro"></td>
                                                <td><input type="submit" name="Boton" value="Enviar Volumen"></td>
                                            </tr>
                                            <tr>
                                                <td style="text-align: right;">Tanque lleno:</td>
                                                <td><input type="checkbox" name="Tanque"></td>
                                                <td></td>
                                                <td><input type="submit" name="Boton" value="Tanque Lleno"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <input type="hidden" name="op" value="4"> 
                                    <input type="hidden" name="busca" value="<?= $busca ?>">
                                </form>
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <form name="form3" method="post" action="gencomando.php" class="Formulario">
                                    <div>Prefija numero de ventas permitidas p/bloquear dispensario</div>
                                    <table aria-hidden="true">
                                        <tbody>
                                            <tr>
                                                <td>No.de ventas:</td>
                                                <td><input type="number" name="Numventas" value="<?= $Man["numventas"] ?>" min="0" max="999" class="Retro"></td>
                                                <td><input type="submit" name="Boton" value="Enviar"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <input type="hidden" name="op" value="7"> 
                                    <input type="hidden" name="busca" value="<?= $busca ?>">
                                </form>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <?php
                                if ($Cpo["estado"] === 'i') {
                                    echo "<a href='$self?busca=$busca&op=6'>Habilitar posicion</a>";
                                } else {
                                    if (($Dispensario === "GILBARCO" || $Dispensario === "WAYNE") && $usuarioSesion->getLevel() == 9) {
                                        echo "<a href='$self?busca=$busca&op=5'>Cambiar a modo programacion</a>";
                                    }
                                }
                                ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <?php
        }
        ?>
    </body>
</html>


