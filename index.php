<?php
#Librerias
include ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$connection = iconnect();

$ciaDAO = new CiaDAO();
$ciaVO = $ciaDAO->retrieve(1);

$clavePemex = $ciaVO->getClavepemex() !== "" ? "Clave Pemex: " . $ciaVO->getClavepemex() : "";
$permisoCre = $ciaVO->getPermisocre() !== "" ? "Permiso CRE: " . $ciaVO->getPermisocre() : "";

$Response = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));
$htmlResponse = "";
if ($Response == 2) {
    $htmlResponse = "<div class='texto_tablas' style='color:red'>Tu clave ha sido cambiada con <font size='+1'>exito!!!</font></div>";
    $htmlResponse .= "<div class='texto_tablas'><font color='red'>FAVOR DE INGRESAR AHORA CON TU NUEVA CLAVE</div>";
} elseif ($Response == 3) {
    $htmlResponse = "<div class='texto_tablas' style='color:red'><strong>La sesión ha sido cerrada por inactividad o ha expirado</strong></div>";
    $htmlResponse .= "<div class='texto_tablas'><strong>VUELVE A INGRESAR TUS DATOS</strong></div>";
} elseif ($Response == 4) {
    $htmlResponse = "<div class='texto_tablas' style='color:red'>Clave actualizada con <font size='+1'>exito!!!</font></div>";
    $htmlResponse .= "<div class='texto_tablas'><strong>VUELVE A INGRESAR TUS DATOS</strong></div>";
} elseif ($Response == 5) {
    $htmlResponse = "<div class='texto_tablas' style='color:red'>La sesión ha sido cerrada con <font size='+1'>exito!!!</font></div>";
    $htmlResponse .= "<div class='texto_tablas'>AHORA PUEDES CERRAR EL NAVEGADOR</div>";
} else {
    $htmlResponse = "<div class='texto_tablas'><strong>$Response</strong></div>";
}
error_log("Begin session from: " . getBrowser());
if (strpos(strtoupper(getBrowser()), "HANDHELD") !== false) {
    
}
foreach ($_COOKIE as $Cok => $val) {
    $Select = "SELECT * FROM authuser WHERE name = '$Cok' AND passwd = md5('$val');";
    $Rst = utils\IConnection::execSql($Select);
    if (is_numeric($Rst["id"])) {
        $Usr = $Cok;
        $Psw = $val;
        $sts = "Ok";
    }
}
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
        <?php include './config_omicrom_login.php'; ?>    
        <title><?= $Gcia ?></title>
    </head>
    <script type="text/javascript">
        $(document).ready(function () {
            if ("<?= $sts ?>" == "Ok") {
                $("#Usuario").val("<?= $Usr ?>");
                $("#Contrasenia").val("<?= $Psw ?>");
                $("#Contrasenia").attr('type', 'password');
                $("#Recordarme").val("Ok");
                $("#Recordarme").prop("checked", true);
            }
            if ("<?= $request->getAttribute("Op") ?>" === "AccessByCorporativo") {
                $("#Usuario").val("<?= $request->getAttribute("Usr") ?>");
                $("#Contrasenia").val("<?= $request->getAttribute("Passw") ?>").attr('type', 'password');
                event.preventDefault();
                jQuery.ajax({
                    type: "POST",
                    url: "auth_ajax.php",
                    dataType: "json",
                    cache: false,
                    data: {"username": $("#Usuario").val(), "password": $("#Contrasenia").val(), "recordar": $("#Recordarme").val()},
                    beforeSend: function (xhr) {
                        $("#Msj").hide();
                        $("#Fail").hide();
                        $("#myLoader").modal("toggle");
                    },
                    success: function (data) {
                        //console.log(data);
                        console.log("Success");
                        if (data.success) {
                            window.location = data.redirect;
                        } else {
                            var count = 0;
                            if (data.count !== null && data.count !== "") {
                                count = parseInt(data.count) + 1;
                            }
                            if (data.count < 5) {
                                $("#Msj").html("<strong>" + data.message + "</strong>");
                                if (count > 0) {
                                    $("#Fail").html("Intento fallido " + count);
                                }
                                $("#Contrasenia").val("");
                                $("#myLoader").modal("toggle");
                                $("#Msj").show();
                                $("#Fail").show();
                                $("#Usuario").focus();
                            } else {
                                window.location = "locked.php?Msj=" + data.message;
                            }
                        }
                    },
                    error: function (jqXHR, textStatus) {
                        console.log(jqXHR);
                        console.log("error");
                        window.location = "index.php?Msj=Error";
                        $("#Msj").html(textStatus);
                    }
                });
            }
        });
    </script>
    <body>
        <div id="inicio">
            <table id="firstTable" aria-hidden="true">
                <tr>
                    <td>
                        <table id="ContenedorLogin" style="width: 100%;height: 100%;" summary="Mostramos imagenes de omicrom y su index">
                            <tr>
                                <th colspan="2"></th>    
                            </tr>
                            <tr>
                                <td id="Img">
                                    <div class="BotonImg" id="Img1"></div>
                                    <div class="BotonImg" id="Img2"></div>
                                    <div class="BotonImg" id="Img3"></div>
                                </td>
                                <td>
                                    <section>
                                        <form id="Login" method="post" action="" autocomplete="off">
                                            <div class="texto_bienvenida_usuario" style="text-align: center;padding-bottom: 15px;">
                                                <img src="img/logo.png" alt="Logo omicrom" style="width: 190px; height: 110px; padding: 5px;">
                                            </div>
                                            <div class="subTitles" style="text-align: center;padding-bottom: 3px;">
                                                ¡ Bienvenido al sistema de control volumétrico !
                                            </div>
                                            <div class="subTitles" style="text-align: center;padding-bottom: 15px;">
                                                Ingrese sus datos
                                            </div>
                                            <div id="boxTable" class="texto_tablas">
                                                <div class="input-icons">
                                                    <i class="icon fa fa-lg fa-user" aria-hidden="true"></i>
                                                    <input type="text" name="username" id="Usuario" class="input-field" placeholder="Usuario" autocomplete="none" required/>
                                                </div>
                                                <div class="input-icons">

                                                    <i class="icon fa fa-lg fa-key" aria-hidden="true"></i>
                                                    <input type="text" name="text" id="Contrasenia" class="input-field"  placeholder="Contraseña" autocomplete="new-password" required/>
                                                    <span id="PasswordEye" style="color: #566573" toggle="#password-field" class="fa fa-fw fa-eye-slash field_icon toggle-password"></span>
                                                </div>
                                                <!--                                                <div style="width:100%;">
                                                                                                    <div style="display: inline-block;width: 50%;">
                                                                                                        <input type="checkbox" name="Recordarme" id="Recordarme"> Recordarme
                                                                                                    </div>
                                                                                                </div>-->
                                                <span style="margin-left: auto; margin-right: auto;">
                                                    <button id="IdEntrar"><i class="icon fa fa-lg fa-sign-in" aria-hidden="true"></i> Entrar</button>
                                                </span>
                                            </div>
                                            <div style="width: 100%;text-align: center;font-weight: bold;font-size: 11px;color: #566573;">
                                                <?= $ciaVO->getCia() ?> 
                                                <br>No.estacion: <?= $ciaVO->getNumestacion() ?> Sucursal: <?= $ciaVO->getEstacion() ?>
                                                <br>Permiso CRE: <?= $ciaVO->getPermisocre() ?>
                                                <?php
                                                $Md5 = "SELECT md5 FROM servicios WHERE nombre = 'Omicrom';";
                                                $Ms5Omicrom = utils\IConnection::execSql($Md5);
                                                ?>
                                                <br>sha256 : <?= $Ms5Omicrom["md5"] ?>
                                            </div>
                                            <div class="mensajes" align="center" id="MensajeError" style="text-align: center;padding-top: 15px;padding-bottom: 10px;font-weight: bold"></div>
                                            <div style="width: 100%;text-align: center;color: red;" id="Msj" class="texto_tablas"></div>
                                            <div style="width: 100%;text-align: center;color: red;" id="Fail" class="texto_tablas"></div>
                                        </form>
                                    </section>

                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
        <?php include "./modal_window_ajax.php"; ?>
        <input type="hidden" id="htmlResponse" value="<?= $htmlResponse ?>">
        <script src="./js/pages/index.js?ver=<?= md5_file("js/pages/index.js") ?>"></script>
    </body>
</html>
