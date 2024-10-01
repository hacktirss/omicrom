<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("data/ClientesDAO.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$self = utils\HTTPUtils::self();

$Titulo = "Detalle de usuario";

if ($request->hasAttribute("busca")) {
    utils\HTTPUtils::setSessionValue("busca", $request->getAttribute("busca"));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue("busca", $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue("busca");

require_once './services/UsuariosService.php';

$usuarios = new Usuarios;
$usuarioVO = new UsuarioVO;
$usuarioVO->setStatus(StatusUsuario::ACTIVO);
$usuarioVO->setTeam(UsuarioPerfilDAO::PERFIL_DEFAULT);
if (is_numeric($busca)) {
    $usuarioVO = $usuarios->getUser($busca);
}
$name = "";
if ($usuarioVO->getTeam() === "Cliente") {
    $CliVO = new ClientesVO();
    $CliDAO = new ClientesDAO();
    $VarDesc = explode(".", $usuarioVO->getNombre());
    $CliVO = $CliDAO->retrieve($VarDesc[0]);
    $SCliente = $name = $CliVO->getId() . ".- " . $CliVO->getNombre();
}
$btnGenerar = " <span class='generar texto_tablas' style='background-color:black;' id='Generar'>Generar contraseña</span>";
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script type="text/javascript">
            $(document).ready(function () {
                var consultaClientes = "SELECT data, value FROM (SELECT id as data, CONCAT(id, '.- ', alias, ' | ', nombre,  ' | ', rfc) value FROM cli WHERE TRUE AND activo = 'Si' " +
                        " UNION SELECT 0 as data, CONCAT(0, ' | ', 'Sin definir') value) AS sub WHERE TRUE";
                var valueClientes = "value";
                $("#autocomplete").prop("placeholder", " Cliente a buscar");
                $("#autocomplete").focus();
                $("#autocomplete").activeComboBox($("[name='formulario1']"), consultaClientes, valueClientes);
                $("#autocomplete_tr").hide();
                if ("<?= $usuarioVO->getTeam() ?>" === "Cliente") {
                    $("#autocomplete_tr").show();
                    $("#autocomplete").val("<?= $name ?>");
                }
                $("#Rol").change(function () {
                    if ($("#Rol").val() == 8) {
                        $("#autocomplete_tr").prop('required', true);
                        $("#autocomplete_tr").show();
                    } else {
                        $("#autocomplete_tr").prop('required', false);
                        $("#autocomplete_tr").hide();
                    }
                });
                $("#Boton").click(function () {
                    if ($("#Rol").val() == 8) {
                        if ($("#autocomplete").val() != "") {
                            return true;
                        } else {
                            alert("Error falta relacionar con un cliente");
                            return false;
                        }
                    } else {
                        return true;
                    }
                });
            });
        </script>
        <script type="text/javascript" src="js/js-usuarios.js"></script>
    </head>
    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="configusers.php"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">
                    <div id="FormulariosBoots">
                        <div class="container no-margin">
                            <div class="row no-padding">
                                <div class="col-12 background container no-margin">
                                    <form name="formulario1" id="formulario1" method="post" action="">
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Nombre: </div>
                                            <div class="col-3">
                                                <input type="text" name="Name" id="Name" class="clase-<?= $clase1 ?>" placeholder='Nombre completo' required/>
                                            </div>
                                            <div class="col-5"></div>
                                            <div class="col-1">
                                                <a href=javascript:winuni("configuresePrint.php?busca=<?= $busca ?>");>
                                                    <em class="fa-solid fa-print fa-2x" ></em>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Correo: </div>
                                            <div class="col-3">
                                                <input type="text" name="Mail" id="Mail" class="clase-<?= $clase1 ?>"  placeholder='usuario@dominio.com'/>
                                            </div>
                                        </div>
                                        <div class="row no-padding" id="UsrCl">
                                            <div class="col-3 align-right">Usuario: </div>
                                            <div class="col-3">
                                                <input type="text" name="Uname" id="Uname" class="clase-<?= $clase1 ?>"/>
                                            </div>
                                        </div>
                                        <?php
                                        if (!is_numeric($busca)) {
                                            ?>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right">Contraseña: </div>
                                                <div class="col-3">
                                                    <input type="text" name="Passwd" id="Passwd" class="clase-<?= $clase1 ?>"/>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Perfil: </div>
                                            <div class="col-3">
                                                <?php
                                                $rq = "required='required'";
                                                $opciones = "$rq  title='Estos perfiles fueron definidos por el SAT'";
                                                ListasCatalogo::getRolesUsuarios("Rol", "", $opciones);
                                                ?>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right">Activo: </div>
                                            <div class="col-3">
                                                <select name='Status' id='Status' class='texto_tablas'>
                                                    <option value='active'>Si</option>
                                                    <option value='inactive'>No</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row no-padding" id="autocomplete_tr">
                                            <div class="col-3 align-right">Cliente</div>
                                            <div class="col-3 align-right">
                                                <div style="position: relative;">
                                                    <input type="search" name="SCliente" id="autocomplete" onClick="this.select();" value="<?= $SCliente ?>">                                                                
                                                </div>
                                                <div id="autocomplete-suggestions"></div>
                                            </div>
                                        </div>
                                        <?php
                                        if ($usuarioVO->getTeam() != "Cliente") {
                                            ?>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right"> </div>
                                                <div class="col-6">
                                                    <input type='submit' class='nombre_cliente' name='Boton' id='Boton'>
                                                </div>
                                                <div class="col-3 align-right"></div>
                                            </div>
                                            <input type="hidden" name="busca" id="busca1"/>
                                            <?php
                                        } else {
                                            ?><div class="row no-padding">
                                                <div class="col-3 align-right"> </div>
                                                <div class="col-6">
                                                    <input type='submit' class='nombre_cliente' name='BotonCli' id='BotonCli' value="Actualiza Status">
                                                </div>
                                                <div class="col-3 align-right"></div>
                                            </div>
                                            <input type="hidden" name="busca" id="busca1"/>
                                            <?php
                                        }
                                        ?>

                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                    if (is_numeric($busca)) {
                        ?>
                        <div id="FormulariosBoots">
                            <div class="container no-margin">
                                <div class="row no-padding">
                                    <div class="col-12 background container no-margin">
                                        <form name="formulario1" id="formulario1" method="post" action="">
                                            <div class="row no-padding">
                                                <div class="col-3 align-right">Nuevo Password: </div>
                                                <div class="col-3">
                                                    <input type="text" name="Passwd" id="Passwd" class="clase-<?= $clase1 ?>" required/>
                                                </div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right">Confirmar: </div>
                                                <div class="col-3">
                                                    <input type="text" name="PasswdC" id="PasswdC" class="clase-<?= $clase1 ?>" required/>
                                                </div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right"></div>
                                                <div class="col-3">
                                                    <input type='submit' class='nombre_cliente' name='Boton' value='Cambiar contraseña' id='Cambiar'>
                                                    <input type="hidden" name="busca" id="busca2"/>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                    <?php Usuarios::lineamientosPassword(); ?>
                    <div id="Response" style="color: #F32F2F;font-weight: bold;text-align: center;"></div>
                    <div id="MensajeError" style="color: #F32F2F;font-weight: bold;text-align: center;"></div>
                </td>
                </td>
            </tr>
        </table>
        <input type="hidden" id="JsBusca" value="<?= $busca ?>">
        <input type="hidden" id="JsNombre" value="<?= $usuarioVO->getNombre() ?>">
        <input type="hidden" id="JsUsername" value="<?= $usuarioVO->getUsername() ?>">
        <input type="hidden" id="JsMail" value="<?= $usuarioVO->getMail() ?>">
        <input type="hidden" id="JsStatus" value="<?= $usuarioVO->getStatus() ?>">
        <input type="hidden" id="JsRol" value="<?= $usuarioVO->getRol() ?>">
        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>
    </body>
    <script src="./js/pages/configuserse.js"></script>
</html> 
