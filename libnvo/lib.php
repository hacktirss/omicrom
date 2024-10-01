<?php
ini_set("error_log", "/var/log/apache2/error_omicrom.log");
define("VERSION", "1.9.3.42");
define("BROWSER", "CHROME");
define("HIDEURI", "HIDEURI");
define("DELIMITER", "-");
define("FACTENDPOINT", "http://0.0.0.0:9190/GeneradorCFDIsWEB/Facturador?wsdl");
setlocale(LC_ALL, 'es_MX.UTF-8');
set_time_limit(300);

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once('softcoatl/SoftcoatlHTTP.php');

include_once('data/AlarmasDAO.php');
include_once('data/BitacoraDAO.php');
include_once('data/UsuarioDAO.php');
include_once('data/CiaDAO.php');
include_once('data/CtDAO.php');

include_once('nusoap/nusoap.php');
include_once('phpmailer/PHPMailerUtil.php');

include_once("Usuarios.php");
include_once("ListasCatalogo.php");
include_once("paginador/Paginador.php");
include_once("paginador/OmicromSession.php");
include_once("Utilerias.php");
include_once("FuncionesFormularios.php");

use com\softcoatl\utils as utils;
//Import the PHPMailer class into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * 
 * @return mysqli
 */
function iconnect() {
    $conn = utils\IConnection::getConnection();
    $conn->query("SET GLOBAL log_bin_trust_function_creators = 1;");
    $conn->query("UPDATE servicios SET version = '" . VERSION . "' WHERE nombre = 'Omicrom' LIMIT 1;");
    $tzs = $conn->query("SELECT zonahoraria tz FROM cia");
    $tz = $tzs->fetch_array();
    date_default_timezone_set($tz['tz']);
    return $conn;
}

/**
 * 
 * @return PDO
 */
function pdoconnect() {
    $conn = utils\PDOConnection::getConnection();
    $conn->query("UPDATE servicios SET version = '" . VERSION . "' WHERE nombre = 'Omicrom' LIMIT 1;");
    $tzs = $conn->query("SELECT zonahoraria tz FROM cia");
    $tz = $tzs->fetch();
    date_default_timezone_set($tz['tz']);
    return $conn;
}

/**
 * Valida el tiempo transcurrido en la session y determina si esta finaliza
 * o permanece dentro del sistema.
 */
function validaSessionActiva() {
    ?>
    <script>
        var inactividad = 1;
        var today = new Date();
        $(document).ready(function () {
            //Incrementa el contador de inactividad cada minuto.
            console.log("Time init: " + today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds());
            inactividad = setInterval(function () {
                incrementarInactividad();
            }, 60000); // 1 minuto

            //Inicia la inactividad con eventos de mouse.
            $(this).mousemove(function (e) {
                inactividad = 1;
            });
            $(this).keypress(function (e) {
                inactividad = 1;
            });
        });
        function incrementarInactividad() {
            console.log("idle: " + inactividad);
            if (inactividad === 10) { // 10 minutes
                closeOpener();
                window.location = "logout.php?timeout=1";
            }
            inactividad++;
        }
    </script>
    <?php
}

function validaReferencia() {
    $referer = utils\HTTPUtils::getEnvironment()->getAttribute("HTTP_REFERER");
    $uri = utils\HTTPUtils::getEnvironment()->getAttribute("REQUEST_URI");
    $query = utils\HTTPUtils::getEnvironment()->getAttribute("QUERY_STRING");
    //error_log("referer: " . $referer);
    if (empty($referer)) {
        error_log("referer: " . $referer);
        error_log("uri: " . $uri);
        error_log("query: " . $query);
        ?>
        <script type="text/javascript">
            window.location = "403.html";
        </script>
        <?php
    }
}

function BordeSuperior($clientes = FALSE) {
    global $Titulo, $Id, $usuarioSesion;

    validaSessionActiva();
    validaReferencia();

    $connection = iconnect();
    $usuarioSesion = getSessionUsuario();
    //error_log(print_r($usuarioSesion,true));
    $ciaDAO = new CiaDAO();
    $ciaVO = $ciaDAO->retrieve(1);
    $lBd = false;

    $Profile = $usuarioSesion->getTeam();
    $Sql = "SELECT cre.permiso permisocre FROM permisos_cre cre WHERE 1=1
            AND cre.catalogo='VARIABLES_EMPRESA'
            AND cre.llave = 'PERMISO_CRE';";
    $CreA = $connection->query($Sql);
    $Cre = $CreA->fetch_array();

    $nMes = (int) date("m");

    $aMes = array("-", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
    $cFecha = date("d") . " de " . $aMes[$nMes] . " del " . date("Y");

    $ManA = $connection->query("SELECT posicion FROM man_pro WHERE enable <> 0 AND activo='Si'");

    if ($ManA->num_rows > 0) {
        $lBd = true;
    }
    ?>

    <div id="header">
        <table style="width: 100%;border-collapse: collapse; border: 0px solid white;" aria-hidden="true">
            <tr style="background-image: url('libnvo/fondo_top_verde.png');height: 120px;">
                <td style="min-width: 180px; text-align: center;">
                    <img src='img/logo.png' style="width: 180px; height: 100px; padding: 5px;" alt="Logo omicrom" onclick="location.reload();">
                </td>
                <td valign='bottom' style="min-width: 655px;">
                    <div style="text-align: center;font-size: 18px;" class='texto_bienvenida_usuario'><?= $ciaVO->getCia() ?></div>
                    <div style="text-align: center;color: #686868" class='texto_tablas'><?= $ciaVO->getDireccion() ?> No. <?= $ciaVO->getNumeroext() ?>&nbsp;Col. <?= $ciaVO->getColonia() ?>&nbsp;<?= $ciaVO->getCiudad() ?>&nbsp;<?= $ciaVO->getEstado() ?></div>
                    <div style="text-align: center" class='texto_tablas'>No.estacion: <strong><?= $ciaVO->getNumestacion() ?> </strong> Clave Pemex: <strong><?= $ciaVO->getClavepemex() ?></strong> Sucursal: <strong><?= $ciaVO->getEstacion() ?></strong> RFC: <strong><?= $ciaVO->getRfc() ?></strong></div>
                    <div style="text-align: center" class='texto_tablas'>Permiso CRE: <strong><?= $Cre['permisocre'] ?></strong> Clave de Instalacion: <strong><?= $ciaVO->getClave_instalacion() . "-" . sprintf("%04d", $ciaVO->getIdfae()) ?></strong></div><br>
                </td>
                <td style="min-width: 200px;vertical-align: middle">
                    <?php if ($lBd) { ?>
                        <p style="text-align: right" class='nombre_cliente'><br><br><?= $cFecha ?> &nbsp; &nbsp;</p>
                    <?php } ?>
                    <div id='hora' style="text-align: right"></div>
                </td>
            </tr>
        </table>

        <table style="width: 100%;height: 33px;border-collapse: collapse; border-bottom: 1px solid gray;background-color: #DADADA;z-index: 1;" aria-hidden="true">
            <tr>
                <td style="min-width: 180px;text-align: center;">
                    <strong class="Acceso">
                        <?php if ($usuarioSesion->getLevel() >= 5) { ?>
                            <a href="menu.php"> Inicio </a>
                        <?php } else { ?>
                            <a href="cli_menu.php"> Inicio </a>
                        <?php } ?>
                        <a href=javascript:wingral("ayuda.php?Id=<?= $Id ?>"); title="Módulo <?= $Id ?>">Ayuda</a>
                        <a href="logout.php">Salir</a>
                    </strong>
                </td>
                <td style="min-width: 855px;vertical-align: middle">
                    <?php menuV2(1); ?>
                </td>
            </tr>
        </table>
    </div>
    <div id="container">
        <table class="table" aria-hidden="true" aria-hidden="true">
            <tr>
                <td class="tdMenus" valign='top'>
                    <table style="border-bottom: 1px solid gray;" aria-hidden="true">
                        <?php
                        $cVar = $connection->query("SELECT isla,turno,status,corte FROM islas WHERE activo='Si' ORDER BY isla");
                        while ($cIsla = $cVar->fetch_array()) {
                            echo "<tr class='texto_tablas' style='background-color: #ff6633;font-weight: bold;color: white;'>";
                            echo "<td style='border-radius: 5px;' align='center' width='50%'>Corte: " . $cIsla['corte'] . "</td>";
                            echo "<td style='border-radius: 5px;' align='center' width='50%'>Turno: " . $cIsla['turno'] . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </table>

                    <table style="padding-bottom: 25px;" aria-hidden="true">
                        <?php menuV2(2, 0, 0, $clientes); //muestra los menus de la barra lateral 
                        ?>
                    </table>
                    <?php
                    if ($usuarioSesion->getLevel() > 6) {
                        $sqlLink = $connection->query("SELECT CONCAT(v.valor,cia.numestacion,'&Id=',cia.idfae) link 
                                                FROM variables_corporativo v 
                                                LEFT JOIN cia ON TRUE WHERE v.llave = 'link_soporte'");
                        $fetchLink = $sqlLink->fetch_array();
                        $link = $fetchLink[0];
                        //$link = "http://localhost/soporte/servicio.php?Id=261";
                        $Nom = explode(" ", $usuarioSesion->getNombre());
                        $NvoNom = "";
                        for ($i = 0; $i < count($Nom); $i++) {
                            $NvoNom .= $Nom[$i] . "%20";
                        }
                        // echo '<div align="center" class="texto_tablas click"><br/><iframe id="WhatsApp" width="120" height="30" style="border: 0" src="https://cdn.smooch.io/message-us/index.html?channel=whatsapp&color=teal&size=compact&radius=4px&label=WhatsApp&number=525544437143"></iframe><br/></div>';

                        $link = $link . "&Usuario=" . $NvoNom . "&CorreoE=" . $usuarioSesion->getMail();
                        ?>
                        <style type="text/css">
                            #ContenedorIcon {
                                width: 100%;
                                align-content: center;
                                text-align: center
                            }

                            #IconReporte {
                                text-align: center;
                                border: 1px solid #006666;
                                height: 40px;
                                width: 120px;
                                border-radius: 5px;
                                margin: 0px auto;
                                background-color: #065e55;
                            }

                            #IconReporte:hover {
                                border: 2px solid #D35400 !important;
                                border-radius: 10px;
                                border-collapse: separate;
                                border-spacing: 8px;
                            }

                            #WhatsApp:hover {
                                border: 2px solid #D35400 !important;
                                border-radius: 8px !important;
                            }
                        </style>
                        <div id="ContenedorIcon">
                            <div align='center' id="IconReporte" class="click">
                                <span class='seleccionar' onclick=javascript:soporte('<?= $link ?>'); style="font-size: 12px;color: #fafafa;">
                                    <div style="display: inline-block;width: 13%;text-align: left;padding: 0px 0px 0px 13px;position: relative;">
                                        <i class="fa fa-envelope-o fa-2x" aria-hidden="true" title='Presione para mandar un mensaje a Soporte Tecnico'></i>
                                    </div>
                                    <div style="display: inline-block;width: 77%;text-align: center;padding-top: 5px;position: relative;">Ingresar <br>reporte</div>
                                </span>
                            </div>
                            <br/>
                            <?php
                        }
                        if ($usuarioSesion->getLevel() > 5) {
                            ?>
                            <div align='center' id="IconReporte" class="click" data-toggle="modal" data-target="#modal-acerca-de" >
                                <span class='seleccionar' style="font-size: 12px;color: #fafafa;" title="Informacion general sistema Omicrom" >
                                    <div style="display: inline-block;width: 13%;text-align: left;padding: 0px 0px 0px 13px;position: relative;">
                                        <em class="fa-sharp fa-light fa-exclamation fa-2x" style="margin-top: 6px;"></em>   
                                    </div>
                                    <div style="display: inline-block;width: 77%;text-align: center;padding-top: 5px;position: relative;">Acerca de</div>
                                </span>
                            </div>
                        </div> 
                        <?php
                    }
                    ?>

                </td>

                <td class="tdContenido" valign="top">

                    <table style="width: 100%;border-collapse: collapse;" aria-hidden="true">
                        <tr>
                            <td align='left' class='subtitulos' width='40%'><?= $Titulo ?></td>
                            <?php if ($usuarioSesion->getLevel() > 5) { ?>
                                <td style="text-align: center" class='texto_tablas'>
                                    <ul class="ttw-notification-menu">
                                        <li id="alerts" class="notification-menu-item last-item"><a href="alarmas.php?criteria=ini" title="Alarmas del sistema"><img id="img_alerts" class="click" src="libnvo/alerta.png" alt="Icono alerta"></a></li>
                                        <li id="pipes" class="notification-menu-item"><a href="pipaspendientes.php?criteria=ini" title="Pipas pendientes de captura"><img id="img_pipes" class="click" src="libnvo/pipa.png" alt="Icono pipa"></a></li>
                                        <li id="messages" class="notification-menu-item"><a href="mensajes.php?criteria=ini" title="Mensajes y notificaciones"><img id="img_messages" class="click" src="libnvo/mensaje.png" alt="Icono mensaje"></a></li>
                                        <li class="notification-menu-item last-item" style="vertical-align: middle;">
                                            <a href="cambio_pass.php" title="Clic aqui para cambiar su contraseña"><img src="libnvo/usuario.png" alt="Icono usuario">
                                                <span class="username"><?= ucwords(strtolower($usuarioSesion->getNombre())); ?></span>
                                                <span class="profile"><?= "(" . $Profile . ")" ?></span>
                                            </a>
                                        </li>
                                    </ul>
                                </td>
                                <?php
                            } else {
                                echo "<td style='width: 50%;text-align:right;font-weight: bold;' class='texto_tablas'><img src='libnvo/usuario.png' style='width: 25px;height: 25px;'>&nbsp;";
                                echo $usuarioSesion->getUsername() . " | " . $usuarioSesion->getNombre();
                                echo "</td>";
                            }
                            ?>
                        </tr>
                    </table>
                    <?php
                    if ($connection != null) {
                        $connection->close();
                    }
                }

                function BordeSuperiorCerrar() {
                    ?>
                </td>
            </tr>
        </table>
    </div>

    <div role="alert" id="mitoast" aria-live="assertive" aria-atomic="true" class="toast">

        <div id="colorC" class=" container" style="padding-top: 3px;padding-bottom: 3px;">
            <div class="row" style="width: 340px;margin-bottom: 0;padding-bottom: 0;">
                <div class="col-1"><i class="icon fa fa-lg fa-bell" aria-hidden="true"></i></div>
                <div class="col-9"><strong>Mensaje de sistema</strong></div>
                <div class="col-1" style="text-align: right;"><i class="icon fa fa-lg fa-close" aria-hidden="true" data-dismiss="toast" aria-label="Cerrar" onclick="cerrarToast()"></i></div>
            </div>
        </div>
        <div class="toast-body"></div>

    </div>

    <?php
}

function PieDePagina() {
    global $mysqli;
    $connection = iconnect();

    $Sql = "SELECT version,modificacion FROM servicios WHERE nombre = 'Omicrom' LIMIT 1;";
    $result = $connection->query($Sql);
    $Ver = $result->fetch_array();
    ?>
    <div id="footer">
        <table aria-hidden="true">
            <tr>
                <td>
                    DETISA ::: Normal de Maestros No.10 Col. Tulantongo Texcoco Estado de Mexico Cp. 56217 Tel. 595 13 40 003 www.detisa.com.mx
                </td>
                <td>Versión: <span><?= $Ver["version"] ?></span> Ultima modificación: <span><?= $Ver["modificacion"] ?></span></td>
            </tr>
        </table>
    </div>

    <script>
        $(document).ready(function () {
            window.setInterval(function () {
                hora();
            }, 1000);
        });
    </script>

    <script type="text/javascript" src="notification/js/jquery-ui-1.8.14.custom.min.js"></script>
    <script type="text/javascript" src="notification/js/ttw-notification-menu.js"></script>
    <script type="text/javascript" src="notification/js/jquery.tools.js"></script>
    <script type="text/javascript" src="notification/js/jquery.uniform.min.js"></script>
    <!-- Zendesk -->
    <script id="ze-snippet" src="https://static.zdassets.com/ekr/snippet.js?key=b70831f4-bccd-497c-9671-3050a360a6cc"></script>

    <script type="text/javascript" src="libnvo/zendesk.js"></script>

    <?php
    if ($connection != null) {
        $connection->close();
    }
    if ($mysqli != null) {
        $mysqli->close();
    }
}

/**
 * 
 * @param int $opcion Establece en que apartado se mostraran los menus.
 * @param int $registro Id del registro para el caso del cambio de turno.
 * @param int $estado Indica si el corte esta cerrado o abierto 0|1
 * @param boolean $esCliente Indica si es para mostrar informacion de usuarios o clientes.
 */
function menuV2($opcion, $registro = 0, $estado = 0, $esCliente = false) {

    $usuarioSesion = getSessionUsuario();

    if (!$esCliente) {
        if ($opcion == 1) {
            generaMenuSuperior($usuarioSesion->getId(), $opcion);
        } elseif ($opcion == 2) {
            generaMenuLateral($usuarioSesion->getId(), $opcion);
        } elseif ($opcion == 3) {
            getReportesCortes($usuarioSesion->getId(), $opcion, $registro, $estado);
        }
    } else {
        generaMenusCliente();
    }
}

/**
 * 
 * @param int $usuario Indentificador del usuario
 * @param int $opcion Establece en que apartado se mostraran los menus.
 * @return string
 */
function getConsultaPermisos($usuario, $opcion) {
    $selectPermisos = "
                    SELECT menus.id menu, menus.nombre, menus.orden, cnf.permisos, cnf.editable 
                    FROM authuser_cnf cnf, menus WHERE TRUE
                    AND menus.id = cnf.id_menu AND cnf.id_user = $usuario ";
    if ($opcion == 1) {
        $selectPermisos .= " AND menus.tipo = 1 ";
    } elseif ($opcion == 2) {
        $selectPermisos .= " AND menus.tipo = 0 AND UPPER(menus.nombre) LIKE '%LATERAL%' ";
    } elseif ($opcion == 3) {
        $selectPermisos .= " AND menus.tipo = 0 AND UPPER(menus.nombre) LIKE '%CAMBIO%' ";
    }
    $selectPermisos .= "ORDER BY menus.orden; ";

    return $selectPermisos;
}

/**
 * 
 * @param int $usuario Indentificador del usuario
 * @param int $opcion Establece en que apartado se mostraran los menus.
 */
function generaMenuSuperior($usuario, $opcion) {
    $permisos = utils\IConnection::getRowsFromQuery(getConsultaPermisos($usuario, $opcion));
    $submenus = utils\HTTPUtils::getSessionValue("S_USER");

    print "<div id='menu'>";
    foreach ($permisos as $permiso) :
        $opciones = $submenus[$permiso["menu"]];
        if ($permiso["permisos"] > 0) :
            print "<ul><li><a href=#>" . $permiso["nombre"] . "</a><span> &nbsp;| </span><div><ul>";
            $i = 0;
            foreach ($opciones as $key => $value) :
                if ($permiso["permisos"][$i++] == 1 || $value["permiso"] == TipoPermisos::FREE) :
                    print "<li><a href='" . $value["direccion"] . "'>" . $value["nombre"] . "</a></li>";
                endif;
            endforeach;
            print "</ul></div></li></ul>";
        endif;
    endforeach;
    print "</div>";
}

/**
 * 
 * @param int $usuario Indentificador del usuario
 * @param int $opcion Establece en que apartado se mostraran los menus.
 */
function generaMenuLateral($usuario, $opcion) {
    $permisos = utils\IConnection::getRowsFromQuery(getConsultaPermisos($usuario, $opcion));
    $submenus = utils\HTTPUtils::getSessionValue("S_USER");

    foreach ($permisos as $permiso) :
        $opciones = $submenus[$permiso["menu"]];
        if ($permiso["permisos"] > 0) :
            $i = 0;
            foreach ($opciones as $key => $value) :
                if ($permiso["permisos"][$i++] == 1 || $value["permiso"] == TipoPermisos::FREE) :
                    print "<tr onclick=javascript:Direccion('" . $value["direccion"] . "'); ><td class='lateral'><a href='#'>&#8226; " . $value["nombre"] . "</a></td></tr>";
                endif;
            endforeach;
        endif;
    endforeach;
}

/**
 * 
 * @param int $usuario Indentificador del usuario
 * @param int $opcion Establece en que apartado se mostraran los menus.
 * @param int $registro Id del registro para el caso del cambio de turno.
 * @param int $estado Indica si el corte esta cerrado o abierto 0|1
 */
function getReportesCortes($usuario, $opcion, $registro, $estado = 0) {
    $permisos = utils\IConnection::getRowsFromQuery(getConsultaPermisos($usuario, $opcion));
    $submenus = utils\HTTPUtils::getSessionValue("S_USER");

    foreach ($permisos as $permiso) :
        $opciones = $submenus[$permiso["menu"]];
        if ($permiso["permisos"] > 0) :
            $i = 0;
            foreach ($opciones as $key => $value) :
                if ($permiso["permisos"][$i] == 1 || $value["permiso"] == TipoPermisos::FREE) :
                    print "<td align='center'><a href=javascript:wingral('" . ($estado == 1 && $i == 0 ? $opciones[$i + 3]["direccion"] : $value["direccion"]) . $registro . "')><i class=\"icon fa fa-lg fa-print\" title='" . $value["nombre"] . "' aria-hidden=\"true\"></i></a></td>";
                else :
                    print $i != 2 ? "<td align='center'></td>" : "";
                endif;
                $i++;
            endforeach;
        endif;
    endforeach;
}

function generaMenusCliente() {
    $Cliente = utils\HTTPUtils::getSessionValue("Cuenta");
    echo "<tr><td class='lateral' onclick=javascript:Direccion('cli_facturas.php?criteria=ini');  style='font-size: 13px;font-family: sans-serif;color:#4B4B4B'>&#8226; Facturación</td></tr>";
    echo "<tr><td class='lateral' onclick=javascript:Direccion('cli_tarjetas.php?criteria=ini'); style='font-size: 13px;font-family: sans-serif;color:#4B4B4B'>&#8226; Tarjetas</td></tr>";
    echo "<tr><td class='lateral' onclick=javascript:wingral('cli_repvtasunidad.php?criteria=ini&ClienteS=$Cliente&Desglose=Dia'); style='font-size: 13px;font-family: sans-serif;color:#4B4B4B'>&#8226; Consumos</td></tr>";
    echo "<tr><td class='lateral' onclick=javascript:Direccion('cli_saldos.php?criteria=ini'); style='font-size: 13px;font-family: sans-serif;color:#4B4B4B'>&#8226; Saldos</td></tr>";
    echo "<tr><td class='lateral' onclick=javascript:Direccion('cli_edipagos.php?criteria=ini'); style='font-size: 13px;font-family: sans-serif;color:#4B4B4B'>&#8226; Pagos</td></tr>";
    echo "<tr><td class='lateral' onclick=javascript:Direccion('cli_cxc.php?criteria=ini&ClienteS=$Cliente'); style='font-size: 13px;font-family: sans-serif;color:#4B4B4B'>&#8226; Estado de cuenta</td></tr>";
    echo "<tr><td class='lateral' onclick=javascript:wingral('cli_repvtasdiacli.php??criteria=ini&ClienteS=$Cliente&Desglose=Dia'); style='font-size: 13px;font-family: sans-serif;color:#4B4B4B'>&#8226; Consumos por dia</td></tr>";
}

function menu($opcion, $registro, $clientes = FALSE) {

    $connection = iconnect();
    $usuarioSesion = getSessionUsuario();
    $Id_Usuario = $usuarioSesion->getId();

    $sql = "SELECT estacion,cxc,cxp,catalogos,reportes,graficas,polizas,menuLateral,cambioTurno ,configuracion
            FROM conf_users WHERE id_user='$Id_Usuario'";

    $Configuracion_sql = $connection->query($sql);
    $Configuracion = $Configuracion_sql->fetch_array();

    $Tabla = utils\HTTPUtils::getSessionValue("SUBMENUS");
    $menus = utils\HTTPUtils::getSessionValue("MENUS");

    $cont = count($menus);

    if ($opcion == 1) {
        echo "<div id='menu'>";
        $m = $i = 0;

        while ($m < $cont) {
            $num = number_format($Configuracion[$m]);
            $cadena = $Configuracion[$m];
            $aux = $Tabla;
            $auxMenus = $menus;

            if ($num > 0 && $auxMenus[$m][2] === "1") {
                echo "<ul><li><a href=#>" . $auxMenus[$m][0] . "</a><span> &nbsp;| </span>";
                echo '<div><ul>';

                $j = 0;

                while ($aux[$i][0] === $auxMenus[$m][3]) {
                    if ((substr($cadena, $j, 1) == 1 || $aux[$i][3] == 2) && ($aux[$i][3] > 0)) {
                        echo "<li><a href='" . $aux[$i][2] . "'>" . $aux[$i][1] . "</a></li>";
                    }
                    $j++;
                    $i++;
                }
                echo '</ul></div>';
                echo '</li></ul>';
            } else {
                while ($aux[$i][0] === $auxMenus[$m][3]) {
                    $i++;
                }
            }
            $m++;
        }
        echo "</div>";
    } elseif ($opcion == 2) {

        if ($clientes) {
            $Cliente = utils\HTTPUtils::getSessionValue("Cuenta");
            echo "<tr><td class='lateral'><a href='cli_facturas.php?criteria=ini'>&#8226; Facturación</a></td></tr>";
            echo "<tr><td class='lateral'><a href='cli_tarjetas.php?criteria=ini'>&#8226; Tarjetas</a></td></tr>";
            echo "<tr><td class='lateral'><a href=javascript:wingral('cli_repvtasunidad.php?criteria=ini&ClienteS=$Cliente&Desglose=Dia');>&#8226; Consumos</a></td></tr>";
            echo "<tr><td class='lateral'><a href='cli_saldos.php?criteria=ini'>&#8226; Saldos</a></td></tr>";
            echo "<tr><td class='lateral'><a href='cli_edipagos.php?criteria=ini'>&#8226; Pagos</a></td></tr>";
            echo "<tr><td class='lateral'><a href='cli_cxc.php??criteria=ini&ClienteS=$Cliente'>&#8226; Estado de cuenta</a></td></tr>";
            echo "<tr><td class='lateral'><a href=javascript:wingral('cli_repvtasdiacli.php??criteria=ini&ClienteS=$Cliente&Desglose=Dia');>&#8226; Consumos por dia</a></td></tr>";
        } else {
            $m = $j = $i = 0;

            $orden = $connection->query("SELECT orden FROM menus WHERE id=5")->fetch_array();

            $aux = $Tabla;
            $auxMenus = $menus;
            $cadena = $Configuracion['menuLateral'];
            $num = number_format($cadena);

            while ($m < $cont && $num > 0) { //Bucle necesario en caso de que lleguen a crecer los menus
                while ($aux[$i][0] === $auxMenus[$m][3]) {

                    if ($orden[0] == $aux[$i][4]) {
                        if ($cadena[$j] == 1 || $aux[$i][3] == 2) {
                            echo "<tr><td class='lateral'><a href='" . $aux[$i][2] . "'>&#8226; " . $aux[$i][1] . "</a></td></tr>";
                        }
                        $j++;
                    }
                    $i++;
                }
                $m++;
            }
        }
    } elseif ($opcion == 3) {

        $m = $j = $i = 0;

        $orden = $connection->query("SELECT orden FROM menus WHERE id=6")->fetch_array();

        $sub = $Tabla;
        $auxMenus = $menus;
        $cadena = $Configuracion['cambioTurno'];
        $num = number_format($cadena);

        while ($m < $cont && $num > 0) {
            while ($sub[$i][0] == $auxMenus[$m][3]) {
                if ($orden[0] == $sub[$i][4] && $sub[$i][3] > 0) {
                    if ($cadena[$j] == 1 || $sub[$i][3] == 2) {
                        if ($j == 0) {
                            echo "<td align='center'><a href=javascript:wingral('" . $sub[$i + 2][2] . $registro . "')><i class=\"icon fa fa-lg fa-print\" title='" . $sub[$i + 2][1] . "' aria-hidden=\"true\"></i></a></td>";
                        } else {
                            echo "<td align='center'><a href=javascript:wingral('" . $sub[$i][2] . $registro . "')><i class=\"icon fa fa-lg fa-print\" title='" . $sub[$i][1] . "' aria-hidden=\"true\"></i></a></td>";
                        }
                    } else {
                        echo "<td align='center'></td>";
                    }
                    $j++;
                }
                $i++;
            }
            $m++;
        }
    } elseif ($opcion == 4) {
        $m = $j = $i = 0;

        $orden = $connection->query("SELECT orden FROM menus WHERE id=6")->fetch_array();

        $sub = $Tabla;
        $auxMenus = $menus;
        $cadena = $Configuracion['cambioTurno'];
        $num = number_format($cadena);

        while ($m < $cont && $num > 0) {
            while ($sub[$i][0] == $auxMenus[$m][3]) {
                if ($orden[0] == $sub[$i][4] && $sub[$i][3] > 0) {
                    if ($cadena[$j] == 1 || $sub[$i][3] == 2) {
                        echo "<td align='center'><a href=javascript:wingral('" . $sub[$i][2] . $registro . "')><i class=\"icon fa fa-lg fa-print\" title='" . $sub[$i][1] . "' aria-hidden=\"true\"></i></a></td>";
                    } else {
                        echo "<td align='center'></td>";
                    }
                    $j++;
                }
                $i++;
            }
            $m++;
        }
    }
    if ($connection != null) {
        $connection->close();
    }
}

/**
 * 
 * @param string $Vlr Valor a modificar
 * @param int $nLen Longitud final
 * @param string $Position LEFT | RIGHT
 * @return string
 */
function cZeros($Vlr, $nLen, $Position = "") {
    $Position = strtoupper($Position);
    if ($Position == "" || $Position == "LEFT") {
        for ($i = strlen($Vlr); $i < $nLen; $i = $i + 1) {
            $Vlr = "0" . $Vlr;
        }
    } elseif ($Position == "RIGHT") {
        for ($i = strlen($Vlr); $i < $nLen; $i = $i + 1) {
            $Vlr .= "0";
        }
    }
    return $Vlr;
}

function cTable($Tam, $Borde) {    //Abre tabla
    echo "<table width='$Tam' border='$Borde' cellpadding='1' cellspacing='2'>";
}

function cTableCie() {      //Cierra tabla
    echo "</table>";
}

function cInputDat($Titulo, $Tipo, $LonCampo, $Campo, $Alin, $Valor, $MaxLon, $Mayuscula, $Ed = NULL, $Requerimientos = NULL) {

    $Mayus = "";
    if ($Mayuscula) {
        $Mayus = " onkeyup='mayus(this);'";
    }

    echo "$Titulo <input type='$Tipo' id='$Campo' class='texto_tablas'  name='$Campo' size='$LonCampo' value='$Valor' maxlenght='$MaxLon' $Requerimientos $Mayus>";
}

/**
 * 
 * @param string $Titulo
 * @param string $TipoInput
 * @param int $LongitudCampo
 * @param string $NombreCampo
 * @param string $AlineacionTitulo
 * @param string $Valor
 * @param int $MaxLongitud
 * @param boolean $TransformaMayuscula
 * @param boolean $SoloNotas
 * @param string $NotasAdicionales
 * @param string $ParametrosAdicionales
 * @param string $Required
 */
function cInput($Titulo, $TipoInput, $LongitudCampo, $NombreCampo, $AlineacionTitulo, $Valor, $MaxLongitud, $TransformaMayuscula = FALSE, $SoloNotas = FALSE, $NotasAdicionales = "", $ParametrosAdicionales = "", $Required = "") {

    echo "<tr height='21' class='texto_tablas'>";
    echo "<td align='$AlineacionTitulo'  bgcolor='#e1e1e1' class='nombre_cliente'>$Titulo &nbsp; </td>";
    echo "<td>&nbsp;";
    if ($SoloNotas) {
        echo "$Valor &nbsp; $NotasAdicionales";
    } else {
        $Mayus = "";
        if ($TransformaMayuscula) {
            $Mayus = " onkeyup='mayus(this);'";
        }
        echo "<input type='$TipoInput' id='$NombreCampo' class='texto_tablas'  name='$NombreCampo' size='$LongitudCampo' value='$Valor' maxlenght='$MaxLongitud' $ParametrosAdicionales $Mayus $Required/> $NotasAdicionales";
    }
    echo "</td>";
    echo "</tr>";
}

function cInputcc($Titulo, $TipoInput, $LongitudCampo, $NombreCampo, $AlineacionTitulo, $Valor, $MaxLongitud, $TransformaMayuscula = FALSE, $SoloNotas = FALSE, $NotasAdicionales = "", $ParametrosAdicionales = "", $Required = "") {

    echo "<tr height='21' class='texto_tablas'>";
    echo "<td align='$AlineacionTitulo'  bgcolor='#e1e1e1' class='nombre_cliente'>$Titulo &nbsp; </td>";
    echo "<td>&nbsp;";
    if ($SoloNotas) {
        echo "$Valor &nbsp; $NotasAdicionales";
    } else {
        $Mayus = "";
        if ($TransformaMayuscula) {
            $Mayus = " onkeyup='mayus(this);'";
        }
        echo'<input type="' . $TipoInput . '" id="' . $NombreCampo . '" class="texto_tablas"  name="' . $NombreCampo . '" size="' . $LongitudCampo . '" value="' . $Valor . '" maxlenght="' . $MaxLongitud . '" ' . $ParametrosAdicionales . $Mayus . $Required . '/> ' . $NotasAdicionales . '';
    }
    echo "</td>";
    echo "</tr>";
}

/**
 * 
 * @param string $etiqueta
 * @param string $nombreInput
 * @param array $arrayDatos
 * @param string $requerido
 * @param string $clase
 * @param string $comentarios
 */
function cInputSelect($etiqueta, $nombreInput, $arrayDatos, $requerido = "", $clase = "texto_tablas", $comentarios = "", $Tamaño = "") {
    ?>
    <tr class='texto_tablas'>
        <td bgcolor='#e1e1e1' align="right" class="nombre_cliente">
            <?= $etiqueta ?>:
        </td>
        <td>
            <?php
            if (!empty($nombreInput)) {
                ?>
                <select style="width:<?= $Tamaño ?>;" name="<?= $nombreInput ?>" id="<?= $nombreInput ?>" class="texto_tablas" <?= $requerido ?>>
                    <?php
                    foreach ($arrayDatos as $key => $value) {
                        ?>
                        <option value="<?= $key ?>" /><?= $value ?></option>
                    <?php
                }
                ?>
            </select>
        <?php } ?>
        <?= $comentarios ?>
    </td>
    </tr>
    <?php
}

/**
 * 
 * @param string $Titulo
 * @param int $Ancho
 * @param int $Alto
 * @param string $NombreCampo
 * @param string $Alineacion
 * @param string $Valor
 * @param int $MaxLongitud
 * @param boolean $SoloNotas
 * @param string $NotasAdicionales
 * @param string $ParametrosAdicionales
 */
function cInputArea($Titulo, $Ancho, $Alto, $NombreCampo, $Alineacion, $Valor, $MaxLongitud, $SoloNotas = FALSE, $NotasAdicionales = NULL, $ParametrosAdicionales = NULL) {

    echo "<tr height='21' class='texto_tablas'>";
    echo "<td align='$Alineacion'  bgcolor='#e1e1e1' class='nombre_cliente'>$Titulo &nbsp; </td>";

    if ($SoloNotas) {
        echo "<td style='max-width: 400px;'>&nbsp;$Valor &nbsp; $NotasAdicionales</td></tr>";
    } else {
        echo "<td>&nbsp;";
        echo "<textarea class='texto_tablas' id='$NombreCampo'  name='$NombreCampo' rows='$Ancho' cols='$Alto' maxlenght='$MaxLongitud' $ParametrosAdicionales/>$Valor</textarea>";
        echo "</td>";
    }
    echo "</tr>";
}

/**
 * 
 * @param string $Titulo
 * @param string $TituloAlineacion
 * @param array() $matriz
 * @param string $NombreCampo
 * @param string $style
 * @param string $Adicionales
 * @param string $Nota
 */
function cSelect($Titulo, $TituloAlineacion, $matriz, $NombreCampo, $style = "", $Adicionales = "", $Nota = "") {
    echo "<tr height='30' class='texto_tablas'><td bgcolor='#e1e1e1' align='$TituloAlineacion' class='nombre_cliente'>$Titulo &nbsp; </td>";
    echo "<td>&nbsp;<select name='$NombreCampo' id='$NombreCampo' class='texto_tablas' style='$style' $Adicionales>";
    foreach ($matriz as $key => $value) {
        echo "<option value='$key'>$value</option>";
    }
    echo "</select> $Nota</td>";
    echo "</tr>";
}

function IdTarea() {

    $connection = iconnect();
    $connection->query("UPDATE variables SET idtarea = LAST_INSERT_ID(idtarea)+1");
    $Tarea = $connection->insert_id;
    return $Tarea;
}

function TotalizaCorte() {

    global $Corte, $nAceCre, $nAceConsig, $nAceTar, $nAceMon, $nAceEfe, $nCredito, $nConsignacion, $nTarjeta, $nEfectivo, $nMonedero, $nBancos, $ConcentrarVtasTarjeta, $IslaPosicion, $nEfectivoDisplay;
    $usuarioSesion = getSessionUsuario();
    $self = utils\HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");
    $ct = utils\IConnection::execSql("SELECT * FROM ct WHERE id = '$Corte';");
    $VC1 = utils\IConnection::execSql("SELECT valor FROM variables_corporativo WHERE llave = 'importeBruto';");
    $MuestrB = utils\IConnection::execSql("SELECT valor FROM variables_corporativo WHERE llave = 'Muestra_banco'");
    $TotalVtaP = 0;
    $banderaBanc = false;
    if ($MuestrB["valor"] == "Si" AND $usuarioSesion->getTeam() == 'Operador') {
        $banderaBanc = TRUE;
    }

    $connection = iconnect();
    $sqlAdd = $ct["status"] === "Cerrado" && $ct["statusctv"] === "Abierto" ? "" : "clave > 0 AND";
    $Egr = $connection->query("SELECT SUM( egr.importe ) total FROM egr WHERE $sqlAdd egr.corte = " . $Corte)->fetch_array();
    $nBancos = $Egr["total"];

    $GastosA = $connection->query("SELECT IFNULL( SUM( importe ), 0 ) importe FROM ctpagos WHERE corte = " . $Corte);
    $Gastos = $GastosA->fetch_array();
    if ($_REQUEST["IslaPosicion"] > 0) {
        $nGastos = 0;
    } else {
        $nGastos = $Gastos["importe"];
    }
    $PrmA = $connection->query("SELECT ventastarxticket FROM cia");
    $Prm = $PrmA->fetch_array();
    $ConcentrarVtasTarjeta = $Prm["ventastarxticket"];

    $cttarjetas = $connection->query("SELECT COUNT( * ) items FROM cttarjetas WHERE id = " . $Corte)->fetch_array();
    if ($cttarjetas["items"] > 0) {
        $ConcentrarVtasTarjeta = "S";
    }

    $selectVtaAce = "
            SELECT cli.tipodepago, SUM( vt.total) importe
            FROM man,vtaditivos vt
            JOIN cli ON vt.cliente = cli.id
            WHERE 1 = 1 
            AND man.posicion = vt.posicion AND man.activo = 'Si'
            AND vt.corte = $Corte AND vt.tm in ('C','N') AND vt.cantidad > 0
            ";
    if (is_numeric($IslaPosicion)) {
        $selectVtaAce .= " AND man.isla_pos = $IslaPosicion";
    }
    $selectVtaAce .= " GROUP BY cli.tipodepago";

    $AceA = $connection->query($selectVtaAce) or error_log($connection->error);
    $nAceConsig = $nAceTotal = $nAceEfe = $nAceCre = $nAceTar = $nAceMon = 0;
    while ($rg = $AceA->fetch_array()) {
        if ($rg["tipodepago"] === TiposCliente::CREDITO || $rg["tipodepago"] === TiposCliente::PREPAGO | $rg["tipodepago"] === TiposCliente::AUTOCONSUMO | $rg["tipodepago"] === TiposCliente::CORTESIA) {
            $nAceCre += $rg["importe"];
        } elseif ($rg["tipodepago"] === TiposCliente::TARJETA || $rg["tipodepago"] === TiposCliente::VALES) {
            $nAceTar += $rg["importe"];
        } elseif ($rg["tipodepago"] === TiposCliente::MONEDERO || $rg["tipodepago"] === TiposCliente::REEMBOLSO) {
            $nAceMon += $rg["importe"];
        } else if ($rg["tipodepago"] === TiposCliente::CONSIGNACION) {
            $nAceConsig += $rg["importe"];
        } else {
            $nAceEfe += $rg["importe"];
        }
        $nAceTotal += $rg["importe"];
    }

    $selectVtaRm = "
            SELECT ct.statusctv, cli.tipodepago, SUM(rm.pesos) pesos, SUM(rm.pesosp) pesosp,rm.factor, 
            SUM(rm.pagoreal) pagoreal, SUM(rm.pesos - rm.pagoreal) diferencia,SUM(rm.descuento) descuento
            FROM man, ct, rm
            JOIN cli ON rm.cliente = cli.id
            WHERE 1 = 1 
            AND man.posicion = rm.posicion AND man.activo = 'Si' 
            AND rm.corte = ct.id
            AND rm.corte = $Corte AND rm.tipo_venta in ('D','N')
            ";
    if (is_numeric($IslaPosicion)) {
        $selectVtaRm .= " AND man.isla_pos = $IslaPosicion";
    }
    $selectVtaRm .= " GROUP BY cli.tipodepago";
    error_log($selectVtaRm);
    $TotA = $connection->query($selectVtaRm) or error_log($connection->error);

    $nConsignacion = $nCredito = $nTarjeta = $nMonedero = $TotalVtaDisplay = $TotalVtaP = 0;
    while ($rg = $TotA->fetch_array()) {
        //        if ($rg["tipodepago"] === TiposCliente::CREDITO || $rg["tipodepago"] === TiposCliente::PREPAGO || $rg["tipodepago"] === TiposCliente::CONSIGNACION) {        
        if ($rg["tipodepago"] === TiposCliente::CREDITO || $rg["tipodepago"] === TiposCliente::PREPAGO || $rg["tipodepago"] === TiposCliente::AUTOCONSUMO || $rg["tipodepago"] === TiposCliente::CORTESIA) {
            $nCredito += $rg["pagoreal"];
        } elseif ($rg["tipodepago"] === TiposCliente::TARJETA || $rg["tipodepago"] === TiposCliente::VALES) {
            $nTarjeta += $rg["pagoreal"];
        } elseif ($rg["tipodepago"] === TiposCliente::MONEDERO) {
            $nMonedero += $rg["pagoreal"];
        } elseif ($rg["tipodepago"] === TiposCliente::CONSIGNACION) {
            $nConsignacion += $rg["pagoreal"];
        }
        if ($ct["statusctv"] === StatusCorte::ABIERTO && $VC1["valor"] === "1") {
            $TotalVtaDisplay += $rg["pesos"] - $rg["descuento"];
        } else {
            $TotalVtaDisplay += $rg["pesosp"] - $rg["descuento"];
        }
        $TotalVtaP += $rg["pesosp"] - ($rg["descuento"] / (1 + ($rs["factor"] / 100)));
    }

    if ($ConcentrarVtasTarjeta == "S") {
        $TarA = $connection->query("SELECT SUM( importe ) importe FROM cttarjetas WHERE id = " . $Corte);
        $Tar = $TarA->fetch_array();
        $nTarjeta += $Tar["importe"];
    }

    if ($ct["statusctv"] === StatusCorte::ABIERTO && $VC1["valor"] === "1") {
        error_log("Efectivo = " . $TotalVtaDisplay . " + " . $nAceEfe . " - (" . $nTarjeta . " + " . $nCredito . " + " . $nGastos . " + " . $nMonedero . ")");
        $nEfectivoDisplay = $TotalVtaDisplay + $nAceEfe - ($nTarjeta + $nCredito + $nGastos + $nMonedero + $nConsignacion);
    } else {
        $nEfectivoDisplay = $TotalVtaP + $nAceEfe - ($nTarjeta + $nCredito + $nGastos + $nMonedero + $nConsignacion);
    }

    $nEfectivo = $TotalVtaP + $nAceEfe - ($nTarjeta + $nCredito + $nGastos + $nMonedero + $nConsignacion);

    $DolA = "
            SELECT IFNULL( ROUND( SUM( f.detalle * f.monto ) ,2 ), 0 ) dolares, IFNULL( ROUND( SUM( f.monto ) , 2 ), 0 ) monto
            FROM formas_de_pago f, rm , man 
            WHERE 1 = 1 
            AND man.posicion = rm.posicion AND man.activo = 'Si' 
            AND rm.id = f.id  AND rm.corte = " . $Corte;
    $DolA = $connection->query($DolA);
    $Dol = $DolA->fetch_array();

    //if ($VC1["valor"] === "1") {
    echo "<div class='subtitulos' style='text-align: left;'>Turno: " . $ct["turno"] . " Fecha: " . $ct["fecha"] . " al " . $ct["fechaf"] . "</div>";
    //}

    echo "<div id='TablaDatos' style='min-height: 40px;'>";

    echo "<table style='text-align: center;'>";
    echo "<tr>";
    if (strpos($self, 'movvtascre.php') !== false) {
        $titleBG1 = " style='background-color: #FF6633;color: white;'";
        $titleColor1 = "style='background-color: #FF6633;color: white;'";
    }
    echo "<td class='fondoGris' $titleBG1><a $titleColor1 href='movvtascre.php?Limpia=1'>Vtas.credito</a></td>";
    if (strpos($self, 'movvtastar.php') !== false) {
        $titleBG2 = " style='background-color: #FF6633;color: white;'";
        $titleColor2 = "style='background-color: #FF6633;color: white;'";
    }
    echo "<td $titleBG2 class='fondoGris'><a $titleColor2 href='movvtastar.php?Limpia=1'>Vta.tarj.bancaria</a></td>";
    if (strpos($self, 'movvtasconsig.php') !== false) {
        $titleConsig = " style='background-color: #FF6633;color: white;'";
        $titleConsig3 = "style='background-color: #FF6633;color: white;'";
    }
    echo "<td $titleConsig class='fondoGris'><a $titleConsig3 href='movvtasconsig.php'>Consignacion</a></td>";
    if (strpos($self, 'movvtasmon.php') !== false) {
        $titleBG2_1 = " style='background-color: #FF6633;color: white;'";
        $titleColor2_1 = "style='background-color: #FF6633;color: white;'";
    }
    echo "<td $titleBG2_1 class='fondoGris'><a $titleColor2_1 href='movvtasmon.php?Limpia=1'>Monederos</a></td>";
    if (strpos($self, 'movvtasace.php') !== false) {
        $titleBG3 = " style='background-color: #FF6633;color: white;'";
        $titleColor3 = "style='background-color: #FF6633;color: white;'";
    }
    echo "<td $titleBG3 class='fondoGris'><a $titleColor3 href='movvtasace.php'>* Aceites</a></td>";
    if (strpos($self, 'movdolares.php') !== false) {
        $titleBG45 = " style='background-color: #FF6633;color: white;'";
        $titleColor45 = "style='background-color: #FF6633;color: white;'";
    }
    echo "<td $titleBG45 class='fondoGris'><a $titleColor45 href='movdolares.php'>Dolares</a></td>";
    if (strpos($self, 'movpagos.php') !== false) {
        $titleBG4 = " style='background-color: #FF6633;color: white;'";
        $titleColor4 = "style='background-color: #FF6633;color: white;'";
    }
    echo "<td $titleBG4 class='fondoGris'><a $titleColor4 href='movpagos.php'>Gastos</a></td>";
    if (strpos($self, 'movvtasefe.php') !== false) {
        $titleBG5 = " style='background-color: #FF6633;color: white;'";
        $titleColor5 = "style='background-color: #FF6633;color: white;'";
    }
    echo "<td $titleBG5 class='fondoGris'><a $titleColor5 href='movvtasefe.php?criteria=ini&Corte=$Corte'> Efectivo</a></td>";
    echo "<td class='fondoGris'>Total vta</td>";
    $sql = "SELECT statusctv FROM ct WHERE id = '$Corte'";
    $Ct = $connection->query($sql)->fetch_array();
    if (abs($nEfectivo - $nBancos) <= 1 || $Ct[0] === "Cerrado") {
        echo "<td class='fondoGris'><img src='libnvo/verde.png' height=18 title='Tu corte se encuentra cuadrado'></td>";
        $cFondo = "#f1f1f1";
    } else {
        echo "<td class='fondoGris'><img src='libnvo/amarillo.png' height=18 title='Tu corte aun no se encuentra cuadrado, el efectivo debe ser igual a lo depositado en bancos'></td>";
        $cFondo = "#fdef2b";
    }
    if (strpos($self, 'movgastos.php') !== false) {
        $titleBG6 = " style='background-color: #FF6633;color: white;'";
        $titleColor6 = "style='background-color: #FF6633;color: white;'";
    }
    if ($usuarioSesion->getTeam() == 'Administrador' OR $usuarioSesion->getTeam() == 'Supervisor'OR $banderaBanc == TRUE) {
        echo "<td $titleBG6 class='fondoGris'><a $titleColor6 href='movgastos.php'> Bancos</a></td>";
    }
    echo "</tr>";

    echo "<tr>";
    echo "<td align='right' bgcolor='#f1f1f1'>$ " . number_format($nCredito + $nAceCre, 2) . "</td>";
    echo "<td align='right' bgcolor='#f1f1f1'>" . number_format($nTarjeta + $nAceTar, 2) . "</td>";
    echo "<td align='right' bgcolor='#f1f1f1'>" . number_format($nConsignacion + $nAceConsig, 2) . "</td>";
    echo "<td align='right' bgcolor='#f1f1f1'>" . number_format($nMonedero + $nAceMon, 2) . "</td>";
    echo "<td align='right' bgcolor='#f1f1f1' title='La venta de contado de aceites ya esta incluido en el efectivo total.'><mark>" . number_format($nAceTotal, 2) . "<mark></td>";
    echo "<td align='right' bgcolor='#f1f1f1'>" . number_format($Dol[1], 2) . "</td>";
    echo "<td align='right' bgcolor='#f1f1f1'>" . number_format($nGastos, 2) . "</td>";
    echo "<td align='right' bgcolor='#f1f1f1'>" . number_format($nEfectivoDisplay, 2) . "</td>";
    echo "<td align='right' bgcolor='#f1f1f1'><strong>" . number_format($TotalVtaDisplay + $nAceTotal, 2) . "</strong></td>";
    echo "<td bgcolor='#f1f1f1'>vs</td>";
    if ($usuarioSesion->getTeam() == 'Administrador' OR $usuarioSesion->getTeam() == 'Supervisor' OR $banderaBanc == TRUE) {
        echo "<td align='right' bgcolor='#f1f1f1'>" . number_format($nBancos, 2) . "</td>";
    }
    echo "</tr></table></div>";

    if ($connection != null) {
        $connection->close();
    }
}

function TotalizaDepositos() {

    global $Corte, $ImpDepositos;

    $connection = iconnect();
    $DepA = $connection->query("SELECT SUM(total) FROM ctdep WHERE corte = " . $Corte);
    $Dep = $DepA->fetch_array();
    $ImpDepositos = $Dep[0];
    if ($connection != null) {
        $connection->close();
    }
}

/**
 * Establece el pie de página de los reportes del sistema.
 * @global type $connection
 */
function topePagina() {

    global $connection, $mysqli;

    $FechaPie = date("Y-m-d H:i:s");

    // Closes both mysqli and mysql connnections
    if ($connection != null) {
        $connection->close();
    }
    if ($mysqli != null) {
        $mysqli->close();
    }
    ?>
    <table style="width: 100%" class="texto_tablas_mini" aria-hidden="true">
        <tr>
            <td align='left'>Desarrollo y transferencia de informatica s.a. de c.v.&nbsp;&nbsp; www.detisa.com.mx</td>
            <td align='right'>Fecha de impresion: <?= $FechaPie ?></td>
        </tr>
    </table>
    <?php
}

/**
 * Define el encabezado de los reportes del sistema.
 * @param type $Titulo
 */
function nuevoEncabezado($Titulo, $Close = true, $ShowHeader = true) {
    validaReferencia();
    $ciaDAO = new CiaDAO();
    $ciaVO = $ciaDAO->retrieve(1);
    if ($ShowHeader) {
        ?>
        <div>
            <table style="width: 100%" aria-hidden="true">
                <tr>
                    <td height='10%' width='15%'><img src='img/logo.png' onclick="location.reload();" style="cursor: pointer;width: 160px; height: 80px;" title="Recargar página." alt="Logo omicrom"></td>
                    <td height='10%' width='70%' style="text-align: center">
                        <div class='texto_tablas'><strong><?= $ciaVO->getCia() . " " . $ciaVO->getClavepemex() ?></strong></div>
                        <div class='texto_tablas_mini'>Estacion: <?= $ciaVO->getNumestacion() ?> Sucursal: <?= $ciaVO->getEstacion() ?> RFC: <?= $ciaVO->getRfc() ?></strong></div>
                        <div class='texto_tablas_mini'><?= $ciaVO->getDireccion() ?> No <?= $ciaVO->getNumeroext() ?> <?= $ciaVO->getColonia() ?> <?= $ciaVO->getCiudad() ?> </div>
                        <div class='texto_tablas_mini'><br /><strong><?= $Titulo ?></strong></div>
                    </td>
                    <?php if ($Close) { ?>
                        <td height='10%' width='15%' style="text-align: center;color: #099;font-size: 12px;font-weight: bold;cursor: pointer;" onclick="window.close();" class="oculto">Cerrar</td>
                    <?php } else { ?>
                        <td></td>
                    <?php } ?>
                </tr>
                <tr>
                    <td colspan="3">
                        <hr>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }
}

/**
 * Define el encabezado de los reportes del sistema.
 * @param type $Titulo
 */
function nuevoEncabezadoPrint($Titulo) {
    $ciaDAO = new CiaDAO();
    $ciaVO = $ciaDAO->retrieve(1);
    ?>
    <div id="header_report_print">
        <table style="width: 100%" aria-hidden="true">
            <tr class="tableexport-ignore">
                <td height='10%' width='15%'><img src='img/logo.png' onclick="location.reload();" style="cursor: pointer;width: 160px; height: 80px" title="Recargar página." alt="Logo omicrom"></td>
                <td height='10%' width='70%' style="text-align: center">
                    <div class="title"><strong><?= $ciaVO->getCia() . " " . $ciaVO->getClavepemex() ?></strong></div>
                    <div class="sub-title">Estacion: <?= $ciaVO->getNumestacion() ?> Sucursal: <?= $ciaVO->getEstacion() ?></strong></div>
                    <?php if (!empty($Titulo)) { ?>
                        <div class="sub-title"><strong><?= $Titulo ?></strong></div>
                    <?php } ?>
                </td>
                <td height='10%' width='15%' class="enlace" onclick="window.close();">Cerrar</td>
            </tr>
            <tr class="tableexport-ignore">
                <td colspan="3">
                    <hr>
                </td>
            </tr>
        </table>
    </div>
    <?php
}

function nuevoEncabezadoMini($Titulo) {
    $ciaDAO = new CiaDAO();
    $ciaVO = $ciaDAO->retrieve(1);
    ?>
    <div>
        <table style="width: 100%" aria-hidden="true">
            <tr>
                <td style="text-align: center;"><img src='img/logo.png' onclick="location.reload();" style="cursor: pointer;width: 180px; height: 100px; padding: 5px;" title="Recargar página." alt="Logo omicrom"></td>
            </tr>
            <tr>
                <td height='10%' width='70%' style="text-align: center">
                    <div class="texto_tablas"><strong><?= $ciaVO->getCia() ?></strong></div>
                    <div class="texto_tablas_mini">Estacion: <?= $ciaVO->getNumestacion() ?> Sucursal: <?= $ciaVO->getEstacion() ?></strong></div>
                    <div class="texto_tablas_mini"><br /><strong><?= $Titulo ?></strong></div>
                </td>
            </tr>
        </table>
        <hr>
    </div>
    <?php
}

function generaTiraHoras() {
    $arrayDias = array();
    $arrayDias[0][0] = "Domingo";
    $arrayDias[0][1] = "DomI";
    $arrayDias[0][2] = "DomF";
    $arrayDias[1][0] = "Lunes";
    $arrayDias[1][1] = "LunI";
    $arrayDias[1][2] = "LunF";
    $arrayDias[2][0] = "Martes";
    $arrayDias[2][1] = "MarI";
    $arrayDias[2][2] = "MarF";
    $arrayDias[3][0] = "Miercoles";
    $arrayDias[3][1] = "MieI";
    $arrayDias[3][2] = "MieF";
    $arrayDias[4][0] = "Jueves";
    $arrayDias[4][1] = "JueI";
    $arrayDias[4][2] = "JueF";
    $arrayDias[5][0] = "Viernes";
    $arrayDias[5][1] = "VieI";
    $arrayDias[5][2] = "VieF";
    $arrayDias[6][0] = "Sabado";
    $arrayDias[6][1] = "SabI";
    $arrayDias[6][2] = "SabF";

    foreach ($arrayDias as $key => $value) {
        echo "<div class='horario texto_tablas'>";
        echo "<div style='text-align: center;background: #006666;color: white;font-weight: bold;'>$value[0]</div>";
        echo "<div>";
        echo "<select class='texto_tablas' name='$value[1]' id='$value[1]'>";
        for ($i = 0; $i <= 23; $i = $i + 1) {
            $HraI = cZeros($i, 2) . ":00:00";
            echo "<option value='$HraI'>$HraI</option>";
        }
        echo "</select>";
        echo "</div>";
        echo "<div style='text-align: center'>a:</div>";
        echo "<div>";
        echo "<select class='texto_tablas' name='$value[2]' id='$value[2]'>";
        for ($i = 0; $i <= 22; $i = $i + 1) {
            $HraI = cZeros($i, 2) . ":00:00";
            echo "<option value='$HraI'>$HraI</option>";
        }
        echo "<option value='23:59:59'>23:59:59</option>";
        echo "</select>";
        echo "</div>";
        echo "</div>";
    }
}

/**
 * 
 * @param string $uuid Indentificador de la factura a enviar
 * @param string $mainMail Correo principal para el envio de los archivos
 * @param string $ccMail Correos secundarios para el envio de los archivos
 * @return string Mensaje de respuesta del envio
 */
function enviarCorreo($uuid, $mainMail, $ccMail = null) {
    $connection = iconnect();
    $SmtpA = $connection->query("SELECT * FROM smtp WHERE smtpvalido = 1 ORDER BY id");
    $Smtp = $SmtpA->fetch_array();

    $CiaA = $connection->query("SELECT * FROM cia");
    $Cia = $CiaA->fetch_array();

    if (substr(php_uname(), 0, 7) == "Windows") {
        $directorio = "/xampp/htdocs/omicrom/fae/archivos/";
    } else {
        $directorio = "/var/www/html/omicrom/fae/archivos/";
        $command = "sudo chmod 0777 " . $directorio;
        exec($command);
    }

    $Msj = "";
    try {

        $mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        $mail->Debugoutput = 'error_log';
        $mail->Host = $Smtp['smtpname'];
        $mail->Port = $Smtp['smtpport'];
        $mail->isHTML(true);
        $mail->SMTPAuth = true;

        if ($Smtp['smtpport'] === "true") {
            $mail->SMTPSecure = 'tls'; //Conexion cifrada TLS
        } else {
            $mail->SMTPSecure = 'false';
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
        }

        $mail->Username = $Smtp['smtpuser'];
        $mail->Password = $Smtp['smtploginpass'];

        $receptor = "";
        $sql = "SELECT tabla, version, pdf_format, cfdi_xml, 
            IF(name32 IS NULL OR name32 = '', name33, name32) name,
            IF(rfc32 IS NULL OR rfc32 = '', rfc33, rfc32) rfc,
            uuid FROM (
               SELECT 
                  tabla,
                  pdf_format,
                  cfdi_xml, 
                  version,
                  ExtractValue(cfdi_xml, '/cfdi:Comprobante/cfdi:Receptor/@nombre') name32, 
                  ExtractValue(cfdi_xml, '/cfdi:Comprobante/cfdi:Receptor/@Nombre') name33, 
                  ExtractValue(cfdi_xml, '/cfdi:Comprobante/cfdi:Receptor/@rfc') rfc32, 
                  ExtractValue(cfdi_xml, '/cfdi:Comprobante/cfdi:Receptor/@Rfc') rfc33, 
                  uuid FROM facturas WHERE uuid =  '" . $uuid . "') SUBQ";

        $result = $connection->query($sql);
        if ($myrowsel = $result->fetch_array()) {
            $receptor = $myrowsel['name'] . " (" . $myrowsel['rfc'] . ")";

            // Read attachments
            $wsdl = FACTENDPOINT;
            $client = new nusoap_client($wsdl, true);
            $client->timeout = 720;
            $client->response_timeout = 720;
            $client->soap_defencoding = 'UTF-8';
            $client->namespaces = array("SOAP-ENV" => "http://schemas.xmlsoap.org/soap/envelope/");

            $formato = $formato == 1 ? "TC" : "A1";
            switch ($myrowsel['tabla']) {
                case "4":
                    $docto = "AN";
                    break;
                case "3":
                    $docto = "RP";
                    break;
                case "2":
                    $docto = "CR";
                    break;
                case "1":
                    $docto = $myrowsel['rfc'] == "XAXX010101000" ? "FG" : "FA";
                    break;
                default:
                    $docto = "FA";
                    break;
            }

            $params = array(
                "uuid" => $uuid,
                "formato" => $formato,
                "send" => true,
                "correo" => $mainMail
            );

            $message = "generaPDFFile";

            try {
                $result = $client->call($message, $params);
                error_log("****************** Se ha enviado el PDF con el nuevo método*****************");
            } catch (Exception $e) {
                $Msj = "Error : " . $e->getMessage();
            }

            $VarCorreos = explode(";", $ccMail);
            foreach ($VarCorreos as $vrc) {
                $params = array(
                    "uuid" => $uuid,
                    "formato" => $formato,
                    "send" => true,
                    "correo" => $vrc
                );

                $message = "generaPDFFile";

                try {
                    $result = $client->call($message, $params);
                    error_log("****************** Se ha enviado el PDF con el nuevo método*****************");
                } catch (Exception $e) {
                    $Msj = "Error : " . $e->getMessage();
                }
            }
            $Msj = "Sus archivos Xml y Pdf han sido enviados con exito";

            /*
              $mail->AddStringAttachment($pdfFile, $uuid . ".pdf", "base64", "application/pdf");
              $mail->AddStringAttachment($myrowsel['cfdi_xml'], $uuid . ".xml", "base64", "application/xml");

              //$mail->ContentType = 'multipart/mixed';
              //Set the subject line
              $mail->Subject = "Envio de Factura Electronica Folio " . ( empty($comprobante->getSerie()) ? "" : $comprobante->getSerie() . "-" ) . $comprobante->getFolio();

              $mail->Body = "Estimado <strong>" . $receptor . "</strong>:"
              . "<br /><br />Le estamos enviando por este medio el <strong>CFDI Comprobante Fiscal Digital (Factura Electr&oacute;nica) Folio " . ( empty($comprobante->getSerie()) ? "" : $comprobante->getSerie() . "-" ) . $comprobante->getFolio() . "</strong> "
              . "correspondiente a su consumo en <strong>" . $Cia['cia'] . "</strong>"
              . "<br/>Nos ponemos a sus &oacute;rdenes para cualquier aclaraci&oacute;n al respecto al Tel&eacute;fono " . $Cia['telefono']
              . "<br/>----------------------------------------------------------------------------------------------------------------------------------<br/>"
              . "Sistema de Facturaci&oacute;n Electr&oacute;nica / <strong>Detisa</strong> Desarrollo y Transferencia de Inform&aacute;tica S.A. de C.V. / detisa.com.mx";

              //Set who the message is to be sent from
              $mail->SetFrom($Smtp['smtpsender'], 'Factura Omicrom');
              error_log("Address : " . $mainMail);
              $mail->AddAddress($mainMail, $receptor);

              $mail->ConfirmReadingTo = $mainMail;

              if (!is_null($ccMail) && !empty($ccMail)) {
              $arrayMail = explode(";", $ccMail);
              foreach ($arrayMail as $value) {
              if (!empty($value))
              $mail->addCC($value, $value);
              }
              }

              if (!($mail->Send())) {
              error_log($mail->ErrorInfo);
              $Msj = "Mailer Error: " . $mail->ErrorInfo;
              } else {
              $Msj = "Sus archivos Xml y Pdf han sido enviados con exito";
              }
             */
        } else {
            $Msj = "No se encontró el documento. Favor de informar a soporte";
        }
    } catch (phpmailerException $e) {
        error_log($e->errorMessage());
        $Msj = $e->errorMessage();
    } catch (Exception $e) {
        error_log($e->getMessage());
        $Msj = $e->getMessage();
    }
    if ($connection != null) {
        $connection->close();
    }
    return $Msj;
}

function crearCorte($Corte) {

    $Msj = "Corte creado con exito.";
    $connection = iconnect();
    if (!$connection->query("CALL insertVentasAutoconsumo(" . $Corte . ")")) {
        error_log($connection->error);
    }

    /**
     * Revisar los totalizadores
     */
    $sql_totalizadores = "
            INSERT INTO totalizadores
            SELECT null id,m.posicion,islas.turno,
            IFNULL(rm1.volumen,0) volumen1,IFNULL(rm1.pesos,0) importe1,
            IFNULL(rm2.volumen,0) volumen2,IFNULL(rm2.pesos,0) importe2,
            IFNULL(rm3.volumen,0) volumen3,IFNULL(rm3.pesos,0) importe3,
            now() fecha,islas.corte idtarea,0 folio
            FROM islas,man_pro m 
            LEFT JOIN 
            (
            SELECT islas.corte,islas.turno,rm.posicion,rm.manguera,rm.producto,
            IFNULL(ROUND(SUM(rm.volumen),2),0) volumen,
            IFNULL(ROUND(SUM(rm.pesos),2),0) pesos 
            FROM rm,islas
            WHERE rm.corte = islas.corte AND rm.manguera = 1
            GROUP BY rm.posicion,rm.manguera
            ) rm1 ON  rm1.posicion = m.posicion
            LEFT JOIN 
            (
            SELECT islas.corte,islas.turno,rm.posicion,rm.manguera,rm.producto,
            IFNULL(ROUND(SUM(rm.volumen),2),0) volumen,
            IFNULL(ROUND(SUM(rm.pesos),2),0) pesos 
            FROM rm,islas
            WHERE rm.corte = islas.corte AND rm.manguera = 2
            GROUP BY rm.posicion,rm.manguera
            ) rm2 ON  rm2.posicion = m.posicion
            LEFT JOIN 
            (
            SELECT islas.corte,islas.turno,rm.posicion,rm.manguera,rm.producto,
            IFNULL(ROUND(SUM(rm.volumen),2),0) volumen,
            IFNULL(ROUND(SUM(rm.pesos),2),0) pesos 
            FROM rm,islas
            WHERE rm.corte = islas.corte AND rm.manguera = 3
            GROUP BY rm.posicion,rm.manguera
            ) rm3 ON  rm3.posicion = m.posicion
            WHERE 
            m.activo = 'Si' AND islas.activo = 'Si'
            GROUP BY m.posicion;";
    if (!$connection->query($sql_totalizadores)) {
        error_log($connection->error);
    }

    /**
     * Actualizar el detalle de los cortes
     */
    $sql_ctd = "
                    UPDATE ctd c 
                    LEFT JOIN 
                    (
                        SELECT null id,m.posicion,islas.turno,
                        IFNULL(rm1.volumen,0) volumen1,IFNULL(rm1.pesos,0) importe1,
                        IFNULL(rm2.volumen,0) volumen2,IFNULL(rm2.pesos,0) importe2,
                        IFNULL(rm3.volumen,0) volumen3,IFNULL(rm3.pesos,0) importe3,
                        now() fecha,islas.corte idtarea,0 folio
                        FROM islas,man_pro m 
                        LEFT JOIN 
                        (
                            SELECT islas.corte,islas.turno,rm.posicion,rm.manguera,rm.producto,
                            IFNULL(ROUND(SUM(rm.volumen),2),0) volumen,
                            IFNULL(ROUND(SUM(rm.pesos),2),0) pesos 
                            FROM rm,islas
                            WHERE rm.corte = islas.corte AND rm.manguera = 1
                            GROUP BY rm.posicion,rm.manguera
                            ) rm1 ON  rm1.posicion = m.posicion
                        LEFT JOIN 
                        (
                            SELECT islas.corte,islas.turno,rm.posicion,rm.manguera,rm.producto,
                            IFNULL(ROUND(SUM(rm.volumen),2),0) volumen,
                            IFNULL(ROUND(SUM(rm.pesos),2),0) pesos 
                            FROM rm,islas
                            WHERE rm.corte = islas.corte AND rm.manguera = 2
                            GROUP BY rm.posicion,rm.manguera
                            ) rm2 ON  rm2.posicion = m.posicion
                        LEFT JOIN 
                        (
                            SELECT islas.corte,islas.turno,rm.posicion,rm.manguera,rm.producto,
                            IFNULL(ROUND(SUM(rm.volumen),2),0) volumen,
                            IFNULL(ROUND(SUM(rm.pesos),2),0) pesos 
                            FROM rm,islas
                            WHERE rm.corte = islas.corte AND rm.manguera = 3
                            GROUP BY rm.posicion,rm.manguera
                        ) rm3 ON  rm3.posicion = m.posicion
                        WHERE 
                        m.activo = 'Si' AND islas.activo = 'Si'
                        GROUP BY m.posicion) t
                    ON t.posicion = c.posicion AND t.idtarea = c.id
                    SET
                    c.fmonto1 = c.imonto1 + t.importe1,
                    c.fmonto2 = c.imonto2 + t.importe2,
                    c.fmonto3 = c.imonto3 + t.importe3,
                    c.fvolumen1 = c.ivolumen1 + t.volumen1,
                    c.fvolumen2 = c.ivolumen2 + t.volumen2,
                    c.fvolumen3 = c.ivolumen3 + t.volumen3
                    WHERE c.id = " . $Corte;

    if (!$connection->query($sql_ctd)) {
        error_log($connection->error);
    }

    /**
     * Acualiza ct
     */
    $now = date("Y-m-d H:i:s");
    $sql_ct = "UPDATE ct SET status = 'Cerrado',fechaf = NOW(), concepto = CONCAT( DATE_FORMAT( NOW(), '%Y-%m-%d %H:%i:%s'), ' (OMI)' ), usr = 'OMI' WHERE id = " . $Corte;
    if (!$connection->query($sql_ct)) {
        error_log($connection->error);
    }

    /**
     * Agregar nuevo registro en ct con el turno en curso
     */
    $sql_tur = "SELECT IFNULL(tur.isla,1) isla,IFNULL(tur.turno,1) turno 
                FROM cia LEFT JOIN tur ON TRUE AND TIME(tur.horaf) < CURRENT_TIME()  
                AND tur.activo = 'Si'  
                ORDER BY tur.turno DESC LIMIT 1;";
    error_log($sql_tur);
    $tur = $connection->query($sql_tur)->fetch_array();

    $sql_insert_ct = "INSERT INTO ct( fecha, hora, fechaf, concepto, isla, turno, 
                        bloqueada, idtareai, idtareaf, usr, status, statusctv) 
                     VALUES( NOW(), CURRENT_TIME(), NOW(), '<strong>Turno abierto </strong>', '" . $tur['isla'] . "', '" . $tur['turno'] . "',
                        0, 0, 0, 'OMI', 'Abierto', 'Abierto')";

    if (!$connection->query($sql_insert_ct)) {
        error_log($connection->error);
    }
    $id = $connection->insert_id;

    /**
     * Insertar el detalle del corte
     */
    $sql_man = "SELECT * FROM man WHERE activo = 'Si'";

    $man = $connection->query($sql_man);
    while ($rg = $man->fetch_array()) {
        $sql_insert_ctd = "INSERT INTO ctd( id, posicion, imonto1, imonto2, imonto3, ivolumen1, ivolumen2, ivolumen3, 
                                    fmonto1, fmonto2, fmonto3, fvolumen1, fvolumen2, fvolumen3 ) 
                          VALUES(" . $id . ", " . $rg['posicion'] . ", 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0 )";
        if (!$connection->query($sql_insert_ctd)) {
            error_log($connection->error);
        }
    }

    /**
     * Actualiza islas
     */
    $sql_islas = "UPDATE islas SET corte = '" . $id . "', turno = '" . $tur['turno'] . "', status = 'Abierta' WHERE isla = '" . $tur['isla'] . "' AND activo = 'Si'";
    if (!$connection->query($sql_islas)) {
        error_log($connection->error);
    }

    if ($connection != null) {
        $connection->close();
    }
    return $Msj;
}

function truncateFloat($number, $digitos) {
    $raiz = 10;
    $multiplicador = pow($raiz, $digitos);
    $resultado = ((int) ($number * $multiplicador)) / $multiplicador;
    return number_format($resultado, $digitos);
}

function encodeFolio($input) {
    $base = "KLMNOPQRSTUVWXYZ";
    $encoded = strtoupper($input);
    $return = "";
    for ($i = 0; $i < strlen($encoded); $i++) {
        $idx = hexdec(substr($encoded, $i, 1));
        $return .= substr($base, $idx, 1);
    }
    return $return;
}

function tipoVenta($char) {
    $Dsp = "";
    switch ($char) {
        case "J":
            $Dsp = "Jarreo";
            break;
        case "A":
            $Dsp = "Auto jarreo";
            break;
        case "N":
            $Dsp = "Consignacion";
            break;
        case "D":
            $Dsp = "Normal";
            break;
    }
    return $Dsp;
}

function turnoLetra($turno) {
    $var = "";
    switch ($turno) {
        case 1:
            $var = "PRIMERO";
            break;
        case 2:
            $var = "SEGUNDO";
            break;
        case 3:
            $var = "TERCERO";
            break;
        case 4:
            $var = "CUARTO";
            break;
    }
    return $var;
}

/**
 * 
 * @param int $status
 * @return string
 */
function statusLetra($status) {
    $var = "";
    switch ($status) {
        case 0:
            $var = "Abierto";
            break;
        case 1:
            $var = "Cerrado";
            break;
        case 2:
            $var = "Cancelado";
    }
    return $var;
}

/**
 * 
 * @param int $status
 * @return string
 */
function statusCFDI($status) {
    $var = "";
    switch ($status) {
        case 0:
            $var = "Abierto";
            break;
        case 1:
            $var = "Timbrado";
            break;
        case 2:
            $var = "Cancelado";
            break;
        case 3:
            $var = "Cancelado S/T";
            break;
    }
    return $var;
}

/**
 * 
 * @param int $origen
 * @return string
 */
function origenCFDI($origen) {
    $var = "";
    switch ($origen) {
        case 1:
            $var = "Omicrom";
            break;
        case 2:
            $var = "Terminal";
            break;
        case 3:
            $var = "En linea";
            break;
        default:
            $var = "Otro";
            break;
    }
    return $var;
}

function getDayDate($Fecha) {
    setlocale(LC_ALL, "es_ES");
    $day = date("d", strtotime($Fecha));
    $num = date("w", strtotime($Fecha));
    switch ($num) {
        case 0:
            $String = "Domingo";
            break;
        case 1:
            $String = "Lunes";
            break;
        case 2:
            $String = "Martes";
            break;
        case 3:
            $String = "Miercoles";
            break;
        case 4:
            $String = "Jueves";
            break;
        case 5:
            $String = "Viernes";
            break;
        case 6:
            $String = "Sabado";
            break;
    }
    return $String . " - " . $day;
}

/**
 * 
 * @param string $archivo
 * @param string $producto
 * @param string $subproducto
 * @return string
 */
function leer_archivo_zip_to_xml($archivo, $producto, $subproducto) {
    error_log("parameters{name => $archivo, product: $producto, sub-product: $subproducto}");
    $data = array();
    $f_zip = "/controlvolumetrico/$archivo.zip";
    $f_xml = "/controlvolumetrico/$archivo.XML";
    $exists = false;

    exec("chmod 0777 /controlvolumetrico");

    if (!file_exists($f_zip)) {
        error_log("El archivo $f_zip no existe!");
    }
    if (!file_exists($f_xml)) {
        error_log("El archivo $f_xml no existe!");
        try {
            $zip = new ZipArchive;
            $res = $zip->open($f_zip);
            if ($res === TRUE) {
                $zip->extractTo('/controlvolumetrico/');
                $zip->close();
                $exists = true;
            }
        } catch (Exception $ex) {
            error_log($ex);
        }
    } else {
        $exists = true;
    }
    if ($exists) {

        $xml = file_get_contents($f_xml);
        $sxml = new SimpleXMLElement($xml);
        $sxml->registerXPathNamespace('controlesvolumetricos', 'https://www.sat.gob.mx/esquemas/controlesvolumetricos');

        $exi = $sxml->xpath('//controlesvolumetricos:EXI');

        foreach ($exi as $value) {
            if ($value["claveProducto"] == $producto && $value["claveSubProducto"] == $subproducto) {
                //error_log(print_r($value, TRUE));
                $data["compras"] += $value["volumenRecepcion"];
                $data["extraccion"] += $value["volumenExtraccion"];
                $data["disponible"] += $value["volumenDisponible"];
            }
        }


        $data["venta"] = 0;

        $vtaCabecera = $sxml->xpath('//controlesvolumetricos:VTA/controlesvolumetricos:VTACabecera');
        foreach ($vtaCabecera as $value) {
            //error_log(print_r($vtaCabecera, TRUE));
            if ($value["claveProducto"] == $producto && $value["claveSubProducto"] == $subproducto) {
                $data["venta"] += $value["sumatoriaVolumenDespachado"];
            }
        }

        //error_log(print_r($data, TRUE));        
    } else {
        error_log("Existe un error, no se puedo leer o no existe en el archivo $f_xml");
        $data = "Existe un error, no se puedo leer o no existe en el archivo $f_xml";
    }

    return $data;
}

/**
 * 
 * @param array $parameters
 * @return string
 */
function generaPDF($parameters) {
    try {
        error_log("Transformando versión 3.3. Genera PDF");

        $wsdl = FACTENDPOINT;
        $client = new nusoap_client($wsdl, true);
        $client->timeout = 720;
        $client->response_timeout = 720;
        $client->soap_defencoding = 'UTF-8';
        $client->namespaces = array("SOAP-ENV" => "http://schemas.xmlsoap.org/soap/envelope/");

        $formato = $parameters[formato] == 1 ? "TC" : "A1";
        switch ($parameters[tabla]) {
            case "4":
                $docto = "AN";
                break;
            case "3":
                $docto = "RP";
                break;
            case "2":
                $docto = "CR";
                break;
            case "1":
                $docto = $parameters['rfc'] === "XAXX010101000" ? "FG" : "FA";
                break;
            default:
                $docto = "FA";
                break;
        }

        $params = array(
            "uuid" => $parameters[uuid],
            "formato" => $formato,
            "tipo" => $docto
        );

        $message = "generaPDFFile";

        $response = $client->call($message, $params);
        $pdfFile = base64_decode($response['return']);
        error_log("****************** Se ha generado el PDF con el nuevo método*****************");
        return $pdfFile;
    } catch (Exception $e) {
        error_log("Error : " . $e->getMessage());
        return null;
    }
}

/**
 * 
 * @param array $parameters
 * @param string $NombreZip
 */
function generaDescarga($parameters, $NombreZip) {
    try {
        error_log("Transformando versión 3.3. Genera PDF");
        error_log(print_r($parameters, TRUE));

        $wsdl = FACTENDPOINT;
        $client = new nusoap_client($wsdl, true);
        $client->timeout = 720;
        $client->response_timeout = 720;
        $client->soap_defencoding = 'UTF-8';
        $client->namespaces = array("SOAP-ENV" => "http://schemas.xmlsoap.org/soap/envelope/");

        $response = $client->call("generaDescarga", $parameters);
        $zipFile = base64_decode($response['return']);
        error_log("****** Se ha generado el ZIP con el nuevo método*******");
    } catch (Exception $e) {
        error_log("Error : " . $e->getMessage());
    } finally {
        header('Content-Description: File Transfer');
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename=' . $NombreZip);
        header("Content-Length: " . strlen(bin2hex($zipFile)) / 2);
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        ob_clean();
        flush();

        echo $zipFile;
        exit;
    }
}

/**
 * 
 * @param array() $parameters
 * @return string
 */
function generaDescargaXmls($parameters, $NombreZip) {
    try {
        error_log(print_r($parameters, TRUE));
        //array_map('unlink', array_filter((array) glob("/tmp/*")));
        if (!empty($parameters["formaPago"]) && $parameters["formaPago"] !== "*") :
            $selectFacturas = "
                SELECT fc.formadepago,facturas.uuid, facturas.cfdi_xml
                FROM facturas, fc WHERE 1 
                AND facturas.id_fc_fk = fc.id 
                AND fc.formadepago = '" . $parameters["formaPago"] . "'
                AND DATE(fc.fecha) BETWEEN DATE('" . $parameters["inicio"] . "') AND DATE('" . $parameters["fin"] . "')
                ";
        elseif (!empty($parameters["tipoCliente"]) && $parameters["tipoCliente"] !== "*") :
            $selectFacturas = "
                SELECT cli.tipodepago,fc.formadepago,facturas.uuid, facturas.cfdi_xml
                FROM facturas, fc, cli WHERE 1 
                AND facturas.id_fc_fk = fc.id AND fc.cliente = cli.id
                AND cli.tipodepago = '" . $parameters["tipoCliente"] . "'
                AND DATE(fc.fecha) BETWEEN DATE('" . $parameters["inicio"] . "') AND DATE('" . $parameters["fin"] . "')
                ";
        else :
            $selectFacturas = "
                SELECT facturas.uuid, facturas.cfdi_xml FROM facturas, fc WHERE 1
                AND facturas.id_fc_fk = fc.id 
                AND DATE(fc.fecha) BETWEEN DATE('" . $parameters["inicio"] . "') AND DATE('" . $parameters["fin"] . "')
                AND facturas.receptor LIKE '" . FcDAO::RFC_GENERIC . "'";
        endif;

        error_log($selectFacturas);

        $zipFile = new ZipArchive();
        $filename = "/tmp/" . date("Ymd_His") . ".zip";
        $config = array(
            'indent' => true,
            'clean' => true,
            'input-xml' => true,
            'output-xml' => true,
            'wrap' => false
        );
        if ($zipFile->open($filename, ZipArchive::CREATE)) :
            $tidy = new tidy();
            $rows = utils\IConnection::getRowsFromQuery($selectFacturas);
            error_log("Find rows: " . count($rows));
            foreach ($rows as $rg) :
                $xml = $tidy->repairstring($rg["cfdi_xml"], $config);
                $zipFile->addFromString($rg["uuid"] . ".xml", $xml);
            endforeach;
            $zipFile->close();
        endif;
    } catch (Exception $e) {
        error_log("Error : " . $e->getMessage());
    } finally {
        header('Content-type: application/zip');
        header('Content-Disposition: attachment; filename="' . $NombreZip . '"');
        header("Content-length: " . filesize($filename));
        header("Pragma: no-cache");
        header("Expires: 0");
        ob_clean();
        flush();
        readfile($filename);
        unlink($filename);
        exit;
    }
}

/**
 * 
 * @param array() $parameters
 * @return string
 */
function generaDescargaXmlsByVenta($parameters, $NombreZip) {
    try {
        error_log(print_r($parameters, TRUE));
        $fechaVenta = str_replace("-", "", $parameters["inicio"]);
        $fechaFVenta = str_replace("-", "", $parameters["fin"]);
        $fecha = $parameters["inicio"];
        $fechaF = $parameters["fin"];

        $selectFacturas = "
                SELECT sub.*, IFNULL(facturas.cfdi_xml, '') cfdi_xml
                FROM (
                        SELECT fc.id factura, fc.folio, fc.serie, fc.origen, fc.fecha fecha_generacion, fc.formadepago forma_pago, cli.tipodepago tipo_cliente, cli.id cliente,
                        fcd.ticket, rm.fin_venta fecha_venta, inv.descripcion producto,
                        fc.uuid, rm.precio, ROUND(rm.volumen, 3) volumen, ROUND(rm.pesos, 2) importe
                        FROM cli, rm, fcd, inv, fc
                        WHERE TRUE
                    AND cli.id = rm.cliente
                        AND rm.id = fcd.ticket AND fcd.producto < 10
                        AND fcd.producto = inv.id
                        AND fcd.id = fc.id AND fc.status = 1
                        AND rm.fecha_venta BETWEEN $fechaVenta AND  $fechaFVenta
                        UNION
                        SELECT fc.id factura, fc.folio, fc.serie, 1 origen, fc.fecha fecha_generacion, fc.formadepago forma_pago, cli.tipodepago tipo_cliente, cli.id cliente,
                        fcd.ticket, vt.fecha fecha_venta, vt.descripcion producto, 
                        fc.uuid, vt.unitario, vt.cantidad volumen, vt.total importe
                        FROM cli, vtaditivos vt, fcd, fc
                        WHERE TRUE
                    AND cli.id = vt.cliente
                        AND vt.id = fcd.ticket AND fcd.producto >= 10 AND vt.tm = 'C'
                        AND fcd.id = fc.id AND fc.status = 1
                        AND DATE(vt.fecha) BETWEEN DATE('$fecha') AND DATE('$fechaF')
                ) sub
                LEFT JOIN facturas ON sub.uuid = facturas.uuid
                WHERE TRUE           
                ";

        if (!empty($parameters["formaPago"]) && $parameters["formaPago"] !== "*") :
            $selectFacturas .= " AND sub.forma_pago = '" . $parameters["formaPago"] . "'";
        endif;
        if (!empty($parameters["tipoCliente"]) && $parameters["tipoCliente"] !== "*") :
            $selectFacturas .= " AND sub.tipo_cliente = '" . $parameters["tipoCliente"] . "'";
        endif;

        $selectFacturas .= " 
                GROUP BY sub.uuid 
                ORDER BY sub.fecha_venta ASC";

        error_log($selectFacturas);

        $zipFile = new ZipArchive();
        $filename = "/tmp/" . date("Ymd_His") . ".zip";
        $config = array(
            'indent' => true,
            'clean' => true,
            'input-xml' => true,
            'output-xml' => true,
            'wrap' => false
        );
        if ($zipFile->open($filename, ZipArchive::CREATE)) :
            $tidy = new tidy();
            $rows = utils\IConnection::getRowsFromQuery($selectFacturas);
            error_log("Find rows: " . count($rows));
            foreach ($rows as $rg) :
                $name_xml = $rg["uuid"] . "_" . $rg["tipo_cliente"] . "_" . $rg["cliente"] . "_" . $rg["forma_pago"] . ".xml";
                $xml = $tidy->repairstring($rg["cfdi_xml"], $config);
                $zipFile->addFromString($name_xml, $xml);
            endforeach;
            $zipFile->close();
        endif;
    } catch (Exception $e) {
        error_log("Error : " . $e->getMessage());
    } finally {
        header('Content-type: application/zip');
        header('Content-Disposition: attachment; filename="' . $NombreZip . '"');
        header("Content-length: " . filesize($filename));
        header("Pragma: no-cache");
        header("Expires: 0");
        ob_clean();
        flush();
        readfile($filename);
        unlink($filename);
        exit;
    }
}

/**
 * Devuleve un arreglo llave-valor con los meses a dos digitos 
 * y su etiqueta con el nombre del mes
 * @return []
 */
function getMonts() {
    $months = array();
    setlocale(LC_TIME, "es_MX.UTF-8");
    for ($m = 1; $m <= 12; $m++) {
        $months[str_pad($m, 2, "0", STR_PAD_LEFT)] = strtoupper(strftime("%B", mktime(0, 0, 0, $m, 12)));
    }

    return $months;
}

/**
 * Devuelve los años en los que hubo venta de la BD omicrom
 * @return []
 */
function getYears() {
    $mysqli = iconnect();
    $years = array();
    $selectYears = "SELECT YEAR(fin_venta) year FROM rm GROUP BY YEAR(fin_venta);";
    $yearResult = $mysqli->query($selectYears);
    while ($rg = $yearResult->fetch_array()) {
        $years[$rg["year"]] = $rg["year"];
    }
    return $years;
}

/**
 * Devuelve el último día del mes correspondiente a un año en especifico.
 * @param integer $year
 * @param string $month
 * @return integer
 */
function lastDayPerMonth($year, $month) {
    $calendar = CAL_GREGORIAN;
    return cal_days_in_month($calendar, $month, $year);
}

/**
 * Actualizamos valores para mensajes externos al flujo
 * @param string $Msj
 * @return integer
 */
function SetExternalMessage($Msj) {
    $QueryS = "SELECT llave FROM variables_corporativo WHERE llave = 'ErrorExterno';";
    $RsQry = utils\IConnection::execSql($QueryS);
    if ($RsQry["llave"] == "") {
        $QueryI = "INSERT INTO variables_corporativo (llave,valor,descripcion) "
                . "VALUES ('ErrorExterno','$Msj','Error obtenido externamente y no lo puedo obtener')";
        utils\IConnection::execSql($QueryI);
        return true;
    } else {
        $QueryU = "UPDATE variables_corporativo SET valor = '$Msj' WHERE llave = 'ErrorExterno'";
        utils\IConnection::execSql($QueryU);
        return true;
    }
}

/**
 * Obtenemos valores para mensajes externos al flujo
 * @return string
 */
function getExternalMessage() {
    $BuscaError = "SELECT valor FROM variables_corporativo WHERE llave = 'ErrorExterno'";
    $valor = utils\IConnection::execSql($BuscaError);
    $Msj = $valor["valor"];
    if ($valor["valor"] !== "") {
        $QueryU = "UPDATE variables_corporativo SET valor = '' WHERE llave = 'ErrorExterno'";
        utils\IConnection::execSql($QueryU);
    }
    return $Msj;
}
