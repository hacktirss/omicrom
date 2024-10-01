<?php
session_start();

include_once ("./libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$connection = iconnect();

$ciaDAO = new CiaDAO();
$ciaVO = $ciaDAO->retrieve(1);

$clavePemex = $ciaVO->getClavepemex() !== "" ? "Clave Pemex: " . $ciaVO->getClavepemex() : "";
$permisoCre = $ciaVO->getPermisocre() !== "" ? "Permiso CRE: " . $ciaVO->getPermisocre() : "";

if ($request->hasAttribute("user")) {
    utils\HTTPUtils::setSessionValue("user", $request->getAttribute("user"));
}
$user = utils\HTTPUtils::getSessionValue("user");
//error_log(print_r($request, true));
if ($request->hasAttribute("Boton")) {

    $usuarios = new Usuarios();
    $usuarioVO = $usuarios->getUser($user);
    $usuarioVO->setPassword($request->getAttribute("nuevo"));

    $response = $usuarios->changePasswordUser($usuarioVO, 1);

    if ($response === Usuarios::RESPONSE_VALID) {
        BitacoraDAO::getInstance()->saveLog(utils\HTTPUtils::getCookieValue("USERNAME"), "ADM", "CAMBIO DE CONTRASEñA");
        header("Location: index.php?Msj=4");
    } else {
        $Msj = $response;
    }
}
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php include "./config_omicrom_login.php"; ?>    
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#user").val("<?= $user ?>");
                $("#mensaje").html("<?= $Msj ?>");

                $("#change_pass").submit(function (event) {

                    var password = $("#nuevo").val();
                    var password2 = $("#confirmar").val();

                    if (password !== password2) {
                        event.preventDefault();
                        $("#mensaje").html("Las contraseñas no coinciden.");
                        $("#nuevo").focus();
                        return false;
                    }

                    if (!validaPassword($("#nuevo"))) {
                        event.preventDefault();
                    }
                });

                $("#PasswordEye").mousedown(function () {
                    $(".toggle-password").toggleClass("fa-eye fa-eye-slash");
                    $("#nuevo").attr("type", "text");
                    $("#confirmar").attr("type", "text");
                }).mouseup(function () {
                    $(".toggle-password").toggleClass("fa-eye fa-eye-slash");
                    $("#nuevo").attr("type", "password");
                    $("#confirmar").attr("type", "password");
                });

                $("#nuevo").focus();
            });
        </script>
    </head>

    <body>
        <div id="inicio">
            <table id="firstTable" aria-hidden="true">
                <tr>
                    <td height="83" width="173" valign="center">    
                        <table width="100%" aria-hidden="true">
                            <tr>
                                <td>
                                    <a target="_blank" href="http://omicrom.com.mx" rel="noopener noreferrer">
                                        <img src="img/logo.png" alt="Logo omicrom" style="width: 180px; height: 100px; padding: 5px;">
                                    </a>
                                </td>
                                <td class="texto_bienvenida_usuario" align="left"><?= $ciaVO->getCia() ?> <br/> <?= $permisoCre ?></td>
                                <td style="text-align: right;padding-right: 25px;color: #F63" class="texto_bienvenida_usuario" valign="bottom">
                                    Estacion: <?= $ciaVO->getNumestacion() ?> Sucursal: <?= ucwords(strtolower($ciaVO->getEstacion())) ?> Fae: <?= $ciaVO->getIdfae() ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div style="width: 45%;display: inline-table" valign="top">
                            <form id="change_pass" method="post" action="" autocomplete="off">

                                <div  class="texto_bienvenida_usuario" style="text-align: center;padding-bottom: 15px;">Cambio de contraseña</div>

                                <div id="boxTable" class="texto_tablas">
                                    <div style="text-align: center">Ingrese sus claves</div>
                                    <div class="input-icons">
                                        Contraseña:
                                        <i class="icon fa fa-lg fa-key" aria-hidden="true"></i>
                                        <input type="password" name="nuevo" id="nuevo" class="input-field" autocomplete="new-password" required/>
                                    </div>
                                    <div class="input-icons">
                                        &nbsp;&nbsp;Confirmar:
                                        <i class="icon fa fa-lg fa-key" aria-hidden="true"></i>
                                        <input type="password" name="confirmar" id="confirmar" class="input-field" autocomplete="new-password" required/>
                                        <span id="PasswordEye" toggle="#password-field" class="fa fa-fw fa-eye-slash field_icon toggle-password"></span>
                                    </div>
                                    <span style="margin-left: auto; margin-right: auto;">
                                        <button><i class="icon fa fa-lg fa-key" aria-hidden="true"></i> Continuar</button>
                                    </span>
                                </div>  
                                <input type="hidden" name="user" id="user">
                                <input type="hidden" name="Boton" id="Cambiar">
                            </form>
                        </div>
                        <div style="width: 45%;display: inline-table" class="texto_tablas" valign="top">
                            <div class="texto_bienvenida_usuario" style="padding-top: 30px;padding-bottom: 30px;color: #F63;">
                                Debe cambiar su contraseña ya que ha expirado o esta entrando por primera vez.
                            </div>
                            <?= Usuarios::lineamientosPassword(); ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div id="mensaje" style="color: #F32F2F;font-weight: bold;text-align: center;"></div>
                        <div class="iconos">
                            <a target="_blank" href="https://fb.me/omicrom.oficial" title="Siguenos en Facebook" rel="noopener noreferrer">
                                <i class="icon fa fa-lg fa-facebook-square" aria-hidden="true"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <?php include "./modal_window_ajax.php"; ?>
    </body>
</html>
