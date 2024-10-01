<?php
#Librerias
include ('libnvo/lib.php');

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$connection = iconnect();

$ciaDAO = new CiaDAO();
$ciaVO = $ciaDAO->retrieve(1);

$clavePemex = $ciaVO->getClavepemex() !== "" ? "Clave Pemex: " . $ciaVO->getClavepemex() : "";
$permisoCre = $ciaVO->getPermisocre() !== "" ? "Permiso CRE: " . $ciaVO->getPermisocre() : "";

$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php include './config_omicrom_login.php'; ?>   
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#Usuario").focus();
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
                    <td style="padding-top: 5%;">

                        <div style="width: 100%;text-align: center"> 
                            <br/>
                            <div class='texto_tablas' style='color: red;font-size: 12px;font-weight: bold;'><?= $Msj ?></div>
                            <br/>
                            <div class='texto_tablas' style='color: red;font-size: 13px;font-weight: bold;'>FAVOR DE CONTACTAR A SU ADMINISTRADOR DE LA ESTACION PARA LIBERAR SU CUENTA NUEVAMENTE</div>
                        </div>

                        <div  class="texto_tablas" style="text-align: center;padding-top: 15px;padding-bottom: 10px;font-weight: bold">
                            Para el correcto uso del sistema es necesario utilizar el navegador
                            <a target="_blank" href="https://www.google.com/intl/es-419_ALL/chrome/" style="text-decoration: none;color: #007fff;font-size: 12px;" rel="noopener noreferrer">
                                <img src="libnvo/google-logo.gif" style="width: 45px" alt="Logo google">&nbsp;Chrome
                            </a>
                        </div>
                        <div style="width: 100%;text-align: center"><a style="text-decoration: none;" href="javascript:history.back()"> Volver </a></div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="iconos">
                            <a target="_blank" href="https://fb.me/omicrom.oficial" title="Siguenos en Facebook" rel="noopener noreferrer">
                                <i class="icon fa fa-lg fa-facebook-square" aria-hidden="true"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </body>
</html>
