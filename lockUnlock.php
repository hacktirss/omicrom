<?php
session_start();
define("IDTAREA", -300);

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ('data/MensajesDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$usuarioSesion = getSessionUsuario();
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

if ($request->hasAttribute("Boton")) {
    $Return = "lockUnlock.php?";
    try {

        $deleteComandos = "DELETE FROM comandos WHERE idtarea = '" . IDTAREA . "' AND ejecucion = 0";
        if ($request->getAttribute("Boton") === "Bloquear") {
            $deleteComandos .= " AND comando LIKE 'B%';";
            $Fecha = $request->getAttribute("FechaB");
            $Opcion = "BLOQUEO";
            $img = "imgrj.png";
        } else {
            $deleteComandos .= " AND comando LIKE 'D%';";
            $Fecha = $request->getAttribute("FechaD");
            $Opcion = "DESBLOQUEO";
            $img = "imgna.png";
        }
        $Titulo = "PROGRAMACION DE  $Opcion";
        $Mensaje = '<div style="text-align: center;"><div style="font-size: 20pt;color: #FF6633;"><strong>¡ATENCIÓN!</strong></div>
                    <div style="font-size: 15pt;">Se crea programación de bombas para <strong>' . $Opcion . '</strong></div>
                    <div style="font-size: 12pt;">Fecha de aplicación: <strong>' . str_replace("T", " ", $Fecha) . '</strong></div>
                    <div><img src="libnvo/' . $img . '"></div></div>';

        if (!($mysqli->query($deleteComandos))) {
            error_log($mysqli->error);
        }

        $query = "SELECT posicion, CONCAT('p',posicion) p, "
                . "CONCAT('B',LPAD(posicion,2,0)) bloquear,  CONCAT('D',LPAD(posicion,2,0)) desbloquear FROM man_pro  "
                . "WHERE activo = 'Si' GROUP BY posicion ORDER BY posicion";
        $val = false;

        if (($result = $mysqli->query($query))) {
            while ($Pos = $result->fetch_array()) {
                if ($request->hasAttribute($Pos["p"])) {
                    if ($request->getAttribute("Boton") == "Bloquear") {
                        $comando = $Pos["bloquear"];
                    } else {
                        $comando = $Pos["desbloquear"];
                    }

                    $insertComando = "INSERT INTO comandos (posicion,manguera,comando,fecha_insercion,fecha_programada,idtarea) 
                                      VALUES ('" . $Pos["posicion"] . "',1,'$comando',NOW(),'$Fecha','" . IDTAREA . "');";

                    if (!($mysqli->query($insertComando))) {
                        error_log($mysqli->error);
                    } else {
                        $val = true;
                    }
                }
            }
        } else {
            error_log($mysqli->error);
        }

        if ($val) {
            $Msj = utils\Messages::MESSAGE_DEFAULT;
            BitacoraDAO::getInstance()->saveLog($usuarioSesion->getNombre(), "ADM", $Titulo);
            MensajesDAO::getInstance()->createMsj($usuarioSesion->getNombre(), $Titulo, $Mensaje, 1, 1);
        } else {
            $Msj = utils\Messages::MESSAGE_NO_OPERATION;
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log($ex);
    } finally {
        header("Location: $Return");
    }
}

$Fecha = date("Y-m-d") . "T" . date("H:00");

$selectEstados = "SELECT ep.posicion, ep.estado
                FROM estado_posiciones ep,man_pro mp
                WHERE ep.posicion = mp.posicion
                AND mp.activo = 'Si' AND mp.posicion < 97
                GROUP BY mp.posicion";
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports.php'; ?>   
        <title><?= $Gcia ?></title>
        <script>

            function load() {
                window.setInterval(function () {
                    $('#contenedorB').load("lockUnlockAjax.php?op=Com");
                    $('#contenedorD').load("lockUnlockAjax.php?op=Com");
                }, 1000);
            }
            $(document).ready(function () {
                load();
                $("#FechaB").val("<?= $Fecha ?>");
                $("#FechaD").val("<?= $Fecha ?>");
                //Seleccionar todos
                $("#checkTodosB").change(function () {
                    $(".miCheckBoxB").prop('checked', $(this).prop("checked"));
                });
                $("#checkTodosD").change(function () {
                    $(".miCheckBoxD").prop('checked', $(this).prop("checked"));
                });
            });
        </script>
    </head>

    <body>

        <div id='container'>
            <?php nuevoEncabezado($Titulo); ?>

            <table style="width: 100%" aria-hidden="true">
                <tr>
                    <td align='center' valign='top' class='texto_tablas'>
                        <div align='center'><strong>Bloqueo</strong></div>
                        <form name='form1' method='get' action=''>
                            <table style="width: 100%" aria-hidden="true">
                                <tr align='center'>
                                    <td width='70%'>
                                        <div id='contenedorB'></div></td>
                                    <td>
                                        <table aria-hidden="true">
                                            <tr class='texto_tablas'><td>Enviar</td></tr>
                                            <?php
                                            $posicioneB = $mysqli->query($selectEstados);
                                            while ($rg = $posicioneB->fetch_array()) {
                                                echo "<tr align='center' class='texto_tablas'>";
                                                if ($rg["estado"] <> '-') {
                                                    echo "<td><input type='checkbox' name='p" . $rg["posicion"] . "' class='miCheckBoxB'></td>";
                                                } else {
                                                    echo "<td><input type='checkbox' name='p" . $rg["posicion"] . "' disabled></td>";
                                                }
                                                echo "</tr>";
                                            }
                                            ?>
                                        </table>
                                    </td>
                                </tr>

                                <tr align='center' class='texto_tablas'>
                                    <td align="right">Seleccionar todas las posiciones: </td>
                                    <td align='center'>
                                        <input name="checkB" type='checkbox' id='checkTodosB'>
                                    </td>
                                </tr>

                                <tr align='center' valign='buttom' class="texto_tablas">
                                    <td align="right">
                                        Fecha: <input type="datetime-local" name="FechaB" id="FechaB" step="600" class="texto_tablas">
                                    </td>
                                    <td heigth='40'>
                                        <input class="nombre_cliente" type="submit" name="Boton" id="Boton"  value="Bloquear"/>
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: center; color: red;"><?= $Msj ?><hr  width="90%"></td>
                </tr>

                <tr>
                    <td align='center' valign='top' class='texto_tablas'>
                        <div align='center'><strong>Desbloqueo</strong></div>
                        <form name='form2' method='get' action=''>
                            <table style="width: 100%" aria-hidden="true">

                                <tr align='center'>
                                    <td width='70%'>
                                        <div id='contenedorD'></div></td>
                                    <td>
                                        <table aria-hidden="true">
                                            <tr class='texto_tablas'><td>Enviar</td></tr>
                                            <?php
                                            $posicioneD = $mysqli->query($selectEstados);
                                            while ($rg = $posicioneD->fetch_array()) {
                                                echo "<tr align='center' class='texto_tablas'>";
                                                if ($rg["estado"] <> '-') {
                                                    echo "<td><input type='checkbox' name='p" . $rg["posicion"] . "' class='miCheckBoxB'></td>";
                                                } else {
                                                    echo "<td><input type='checkbox' name='p" . $rg["posicion"] . "' disabled></td>";
                                                }
                                                echo "</tr>";
                                            }
                                            ?>
                                        </table>
                                    </td>
                                </tr>

                                <tr align='center' class='texto_tablas'>
                                    <td align="right">Seleccionar todas las posiciones: </td>
                                    <td align='center'>
                                        <input name="checkB" type='checkbox' id='checkTodosD'>
                                    </td>
                                </tr>

                                <tr align='center' valign='buttom' class="texto_tablas">
                                    <td align="right">
                                        Fecha: <input type="datetime-local" name="FechaD" id="FechaD" step="600" class="texto_tablas">
                                    </td>
                                    <td heigth='40'>
                                        <input class="nombre_cliente" type="submit" id="BotonD" name="Boton" value="Desbloquear"/>
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </td>
                </tr>

            </table>

        </div>
    </body>
</html>
