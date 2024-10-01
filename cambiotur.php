<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require_once './services/CambioTurnoService.php';

$request = utils\HTTPUtils::getRequest();
$arrayFilter = array("tipo" => $request->getAttribute("tipo") ? $request->getAttribute("tipo") : 1);
$session = new OmicromSession("ct.id", "ct.id");
$usuarioSesion = getSessionUsuario();
if ($request->getAttribute("criteria") === "ini") {
    utils\HTTPUtils::setSessionValue("BusquedaStatus", 0);
}
if ($request->hasAttribute("BusquedaStatus")) {
    if ((int) $request->getAttribute("BusquedaStatus") >= 0) {
        utils\HTTPUtils::setSessionValue("BusquedaStatus", (int) $request->getAttribute("BusquedaStatus"));
    }
}
foreach ($arrayFilter as $key => $value) {
    ${$key} = utils\HTTPUtils::getSessionBiValue($nameSession, $key);
}
$tipo = utils\HTTPUtils::getSessionValue("BusquedaStatus");
switch ($tipo) {
    case 0:
        $Add = "";
        break;
    case 1:
        $Add = " statusctv = 'Cerrado' ";
        break;
    case 2:
        $Add = " statusctv = 'Abierto' ";
        break;
}
$busca = $session->getSessionAttribute("criteria");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Id = 27;
$Titulo = "Cambio de turno";

$paginador = new Paginador($Id,
        "ct.status,ct.usr,ct.statusctv ",
        "",
        "",
        "$Add",
        $session->getSessionAttribute("sortField"),
        $session->getSessionAttribute("criteriaField"),
        utils\Utils::split($session->getSessionAttribute("criteria"), "|"),
        strtoupper($session->getSessionAttribute("sortType")),
        $session->getSessionAttribute("page"),
        "REGEXP",
        "");
$self = utils\HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");
$cLink = substr($self, 0, strrpos($self, ".")) . 'e.php';
$cLinkd = substr($self, 0, strrpos($self, ".")) . 'd.php';

$DispensarioSql = "SELECT Dispensarios FROM variables";
$DispensarioFetch = $mysqli->query($DispensarioSql)->fetch_array();
$Dispensario = $DispensarioFetch["Dispensarios"];

$islaDAO = new IslaDAO();
$islaVO = $islaDAO->retrieve(1, "isla");

$ct = utils\IConnection::execSql("SELECT id,status FROM ct WHERE TRUE ORDER BY id DESC LIMIT 1;");
$MuestraVisor = "SELECT valor FROM variables_corporativo WHERE llave = 'RolesSinVisor';";
$VlMv = utils\IConnection::execSql($MuestraVisor);
$VvlTeam = strpos($VlMv["valor"], $usuarioSesion->getTeam()) !== false ? 0 : 1;
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>

            function cerrarYabrir(corte, url) {
                var mensaje = "Va a cerrar y abrir un nuevo corte, está seguro?";
                if (confirm(mensaje)) {
                    document.location.href = url + "?cPrc=3&cId=" + corte;
                }
            }

            function cerrarYabrirLC(corte, url) {
                var mensaje = "Va a cerrar y abrir un nuevo corte, está seguro?";
                if (confirm(mensaje)) {
                    document.location.href = url + "?op=Crear&Corte=" + corte;
                }
            }

            function cerrarTurno(corte, url) {
                var mensaje = "Va a cerrar el corte, los dispensarios se mantendrán bloqueados, está seguro?";
                if (confirm(mensaje)) {
                    document.location.href = url + "?cPrc=2&cId=" + corte;
                }
            }

            $(document).ready(function () {
                if (<?= $VvlTeam ?> == 0) {
                    $("#TablaDatos").hide();
                    $(".pagerfooter").hide();
                    $("#autoForm").hide();
                }
                $("#autocomplete").focus();
                $("#BusquedaStatus").val("<?= utils\HTTPUtils::getSessionValue("BusquedaStatus") ?>");
                $("#AbrirCorteId").click(function () {
                    alertTextValidation("¿Que corte desea habilitar?", "number", "Aceptar", "", true, "question", 20000, true, "Cancelar", 0, 1000, "", "#F1948A");
                });
                $(".openThis").click(function () {
                    if (("<?= $usuarioSesion->getTeam() ?>" === "Administrador" || "<?= $usuarioSesion->getTeam() ?>" === "Supervisor") && "<?= utils\IConnection::execSql("SELECT valor FROM variables_corporativo WHERE llave='habilita_cortes'")["valor"] ?>" === "1") {
                        var Corte = this.dataset.corte;
                        jQuery.ajax({
                            type: "POST",
                            url: "getByAjax.php",
                            dataType: "json",
                            cache: false,
                            data: {"Op": "BuscaEnvios", "idCorte": Corte},
                            success: function (data) {
                                console.log(data);
                                if (data.idEnvio == null) {
                                    Swal.fire({
                                        title: "Seguro de abir el corte no." + Corte,
                                        icon: "question",
                                        iconColor: "#EC7063"}).then((result) => {
                                        if (result.isConfirmed) {
                                            jQuery.ajax({
                                                type: "POST",
                                                url: "getByAjax.php",
                                                dataType: "json",
                                                cache: false,
                                                data: {"Origen": "AbirTurno", "idCorte": Corte, "User": "<?= $usuarioSesion->getUsername() ?>"},
                                                beforeSend: function (xhr) {
                                                    Swal.fire({
                                                        title: 'Cargando',
                                                        showConfirmButton: false,
                                                        background: "rgba(213, 216, 220 , 0.9)",
                                                        backdrop: "rgba(5, 5, 25, 0.5)",
                                                        allowOutsideClick: false,
                                                        closeOnConfirm: true
                                                    });
                                                    Swal.showLoading();
                                                },
                                                success: function (data) {
                                                    alertTextValidation(data.Msj, "", "", "", false, data.Icon, data.Timer, false);
                                                    if (data.Success) {
                                                        window.setTimeout(function () {
                                                            location.reload();
                                                        }, data.Timer);
                                                    }
                                                }
                                            });
                                        }
                                    });
                                } else {
                                    Swal.fire({
                                        title: "Se tienen los cortes enviados a banco. Registros no. " + data.idEnvio,
                                        icon: "error",
                                        iconColor: "#EC7063"})
                                }
                            }
                        });
                    }
                });
            });
            function getResultado(val_Json) {
                if (val_Json.Sucess) {
                    jQuery.ajax({
                        type: "POST",
                        url: "getByAjax.php",
                        dataType: "json",
                        cache: false,
                        data: {"Origen": "AbirTurno", "idCorte": val_Json.Value, "User": "<?= $usuarioSesion->getUsername() ?>"},
                        beforeSend: function (xhr) {
                            Swal.fire({
                                title: 'Cargando',
                                showConfirmButton: false,
                                background: "rgba(213, 216, 220 , 0.9)",
                                backdrop: "rgba(5, 5, 25, 0.5)",
                                allowOutsideClick: false,
                                closeOnConfirm: true
                            });
                            Swal.showLoading();
                        },
                        success: function (data) {
                            alertTextValidation(data.Msj, "", "", "", false, data.Icon, data.Timer, false);
                            if (data.Success) {
                                window.setTimeout(function () {
                                    location.reload();
                                }, data.Timer);
                            }
                        }
                    });
                }
            }
        </script>
        <?php $paginador->script(); ?>
    </head>

    <body>
        <?php BordeSuperior(); ?>
        <form name="form1" method="post" action="">
            <div style="font-family: sans-serif ;display: inline-block;width: 100%;font-size: 13px;margin-bottom: 4px;color: #2C3E50">
                <div style="display: inline-block; width: 97%;text-align: right;">
                    Status : 
                    <div class="content-select">
                        <select  style="font-size: 12px;font-family: sans-serif;color: #2C3E50;" name="BusquedaStatus" id="BusquedaStatus" onchange="form1.submit();">
                            <option value="2">Abierto</option>
                            <option value="1">Cerrado</option>
                            <option value="0">Todos</option>
                        </select>
                        <em></em>
                    </div>
                </div>
                <!--                <div style="display: inline-block; width: 50%;padding-left: 90%;">
                <?php // echo (utils\IConnection::execSql("SELECT valor FROM variables_corporativo WHERE llave='habilita_cortes'")["valor"] == 1 && ($usuarioSesion->getTeam() === "Administrador" || $usuarioSesion->getTeam() === "Supervisor")) ? '<div id="AbrirCorteId">Abrir Corte <i class="fa-solid fa-lock-open"></i></div>' : ''; ?>
                                </div>-->
            </div>
        </form>
        <div id="TablaDatos">
            <table class="paginador" aria-hidden="true">
                <?php
                if ($usuarioSesion->getLevel() > 5) {
                    echo $paginador->headers(array("1", "2", "3", "", ""), array("", "", "", "",""));
                } else {
                    echo $paginador->headers(array(), array(""));
                }
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();

                    echo "<tr>";

                    if ($row["status"] === StatusCorte::CERRADO) {
                        if ($usuarioSesion->getLevel() > 5) {
                            if ($row["statusctv"] === StatusCorte::CERRADO) {
                                menuV2(3, $row["id"], 1);
                            } else {
                                menuV2(3, $row["id"], 0);
                            }
                        }
                    } else {
                        if ($usuarioSesion->getLevel() > 5) {
                            echo "<td align='center'>-</td>";
                            echo "<td align='center'>-</td>";
                            echo "<td align='center'><a href=javascript:wingral('impcorteace.php?criteria=ini&Corte=" . $row["id"] . "')><i class=\"icon fa fa-lg fa-print\" aria-hidden=\"true\"></i></a></td>";
                        }
                    }

                    if ($usuarioSesion->getLevel() > 5) {
                        echo "<td align='center'><a href='movvtascre.php?criteria=ini&Corte=" . $row["id"] . "' class='textosCualli' title='Movimientos del corte'>Movimientos</a></td>";
                        echo "<td align='center'><a class='textosCualli' title='Recaudacion de efectivo x vendedor p/este turno' href='mdepositos.php?criteria=ini&Corte=" . $row["id"] . "'>Colectas</a></td>";
                    }
                    echo $paginador->formatRow();

                    if ($row["status"] === StatusCorte::ABIERTO) {
                        if ($Dispensario !== "LC") {
                            echo "<td align='center'><a class='textosCualli' title='Cierra y abre un nuevo turno' href=javascript:cerrarYabrir(" . $row["id"] . ",'servicio.php');>Cerrar y abrir</a></td>";
                        } else {
                            echo "<td align='center'><a class='textosCualli' title='Cierra y abre un nuevo turno' href=javascript:cerrarYabrirLC(" . $row["id"] . ",'$self');>Cerrar y abrir</a></td>";
                        }
                        echo "<td align='center'><a title='Bloquea todos los dispensario' class='textosCualli' href=javascript:winuni('lockUnlock.php');>Bloquear</a></td>";
                        echo "<td align='center'><a class='textosCualli' title='Cierra turno y deja todos los dispensario bloqueados' href=javascript:cerrarTurno(" . $row["id"] . ",'servicio.php');>Cerrar turno</a></td>";
                        echo "<td><div class='IconAmarillo' title='Corte en curso'></div></td>";
                    } else {
                        echo "<td></td>";
                        echo "<td></td>";
                        echo "<td></td>";
                        echo "<td>";
                        if ($row["statusctv"] === StatusCorte::CERRADO) {
                            $Sql = "SELECT eed.id FROM env_efectivod eed left join env_efectivo ee ON ee.id=eed.id_ee WHERE id_corte=" . $row["id"] . " AND ee.status='Cerrado'";
                            $rsenv = utils\IConnection::execSql($Sql);
                            if ($rsenv["id"] > 0) {
                                echo "<div class='IconAzulFull openThis' title='Corte cuadrado y enviado a banco' data-corte='" . $row["id"] . "'></div>";
                            } else {
                                echo "<div class='IconVerde openThis' title='Corte cuadrado' data-corte='" . $row["id"] . "'></div>";
                                echo "<td><div><a><i class='fa-solid fa-envelope'></i></a></div>";
                            }
                        } else {
                            echo "<div class='IconAmarillo' title='Corte aun sin cuadrar bancos' data-corte='" . $row["id"] . "'></div>";
                        }
                        echo "</td>";
                        
                    }

                    echo "</tr>";
                }
                ?> 
            </table>
        </div>
        <?php
        $nLink = array();
        $nLink["<i class='icon fa fa-plus-circle' aria-hidden=\"true\"></i> Envio de efectivo"] = "envioEfectivo.php?criteria=ini";
        if ($islaVO->getStatus() === StatusIsla::CERRADO || $ct["status"] === StatusCorte::CERRADO) {
            $nLink["<i class='icon fa fa-plus-circle' aria-hidden=\"true\"></i> Abrir turno"] = "cambiotur.php?Boton=Abrir turno";
        }
        echo $paginador->footer(false, $nLink, false);
        echo $paginador->filter();
        echo "<div class='mensajes'>$Msj</div>";
        BordeSuperiorCerrar();
        PieDePagina();
        ?>
        <style type="text/css">
            img {
                border-color: white;
                border:0;
                height: 1px;
                width: 1px;
            }
        </style>
    </body>
</html>