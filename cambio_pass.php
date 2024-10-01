<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

#Variables comunes;
$Titulo = "Cambio de contraseña";
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

require_once './services/UsuariosService.php';

$aMes = array("-", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
$FechaCreacion = $usuarioSesion->getCreation();
$MesCreacion = substr($FechaCreacion, 5, 2) * 1;
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script type="text/javascript" src="js/js-usuarios.js"></script>

        <script>
            $(document).ready(function () {
                $("#actual").focus();

                $("#mensaje").html("<?= $Msj ?>");
                
                $("#Generar").click(function (event) {
                    event.preventDefault();
                    var pass = generatePassword();
                    $("#nuevo").val(pass);
                    $("#confirmar").val(pass);
                });
                
                $("#miUsuario").submit(function (event) {
                    
                    var password = $('#nuevo').val();
                    var password2 = $('#confirmar').val();

                    if (password !== password2) {
                        event.preventDefault();
                        $("#mensaje").html("Las contraseñas no coinciden.");
                        $('#nuevo').focus();
                        clicksForm = 0;
                        return false;
                    }
                    
                    var response = validaPassword2($("#nuevo"));
                    console.log(response);
                    if (response !== "OK") {
                        $("#Response").html(response);
                        clicksForm = 0;
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
                
            });
           
        </script>
        
        <style>
            .generar{
                background-color: #E1E1E1;
                margin-left: 5px;
                padding: 5px 10px 5px 10px;
                border-radius: 3px;
                box-shadow: 3px 3px 0px 0px #BABABA;
                color: #888888;
                cursor: pointer;
            }
            .toggle-password{
                font-size: 14px;
            }
            
        </style>
    </head>

    <body>
        
        <?php BordeSuperior(); ?>
        
        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="menu.php"><div class="RegresarCss " alt="Flecha regresar" style="">Regresar</div></a>
                </td>
                <td style="vertical-align: top;">
                    <form name="change_pass" id="miUsuario" method="post" action="">

                        <?php
                        cTable("99%", "0");
                        
                        echo "<tr><td align='left' colspan='2' class='texto_tablas'>Su contraseña expira el: <strong>" . substr($FechaCreacion, 8, 2) . " de " . $aMes[$MesCreacion] . " del " . substr($FechaCreacion, 0, 4) . "</strong>";
                        echo "<br/><br/></td><tr>";
                        
                        $btnGenerar = " <span class='generar texto_tablas' id='Generar'>Generar contraseña</span>";
                        $btnMostrar = " <span id='PasswordEye' toggle=\"#password-field\" class=\"fa fa-fw fa-eye-slash field_icon toggle-password\" style=\"cursor:pointer; cursor: hand;margin-left:10px;margin-top:4px;\"></span>";
                        
                        cInput("Ingrese contrase actual:", "password", "30", "actual", "right", '', "20", false, false, '', " placeholder='**********' required autocomplete='new-password'");
                        cInput("Nueva contraseña:", "password", "30", "nuevo", "right", '', "20", false, false, $btnGenerar, " placeholder='**********'required autocomplete='new-password'");
                        cInput("Confirmar contraseña:", "password", "30", "confirmar", "right", '', "20", false, false, $btnMostrar, " placeholder='**********' required autocomplete='new-password'");
                        
                        echo "<tr><td></td><td align='left'>&nbsp;";
                        echo "<input type='submit' class='nombre_cliente' name='Cambiar' value='Cambiar contraseña' id='Cambiar' style=\"cursor:pointer; cursor: hand;\">";
                        echo "</td><tr>";

                        echo "<tr><td colspan='2' align='center'><br/><br/></td><tr>";

                        echo "<tr><td colspan='2' class='texto_tablas'>";
                        Usuarios::lineamientosPassword();
                        echo "</td><tr>";
                        cTableCie();
                        ?>

                    </form>

                    <div id="Response" style="color: #F32F2F;font-weight: bold;text-align: center;"></div>
                    <div class='mensajes'><?= $Msj?></div>
                </td>
            </tr>
        </table>

        <?php BordeSuperiorCerrar() ?>

        <?php PieDePagina(); ?>

    </body>
</html>
