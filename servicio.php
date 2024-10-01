<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

include_once ('data/MensajesDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\Request::instance();

$usuarioSesion = getSessionUsuario();

$cId = utils\HTTPUtils::getRequest()->getAttribute("cId");
$cPrc = utils\HTTPUtils::getRequest()->getAttribute("cPrc");
$isla = utils\HTTPUtils::getRequest()->getAttribute("isla");
$numRegistros = 0;
$pop = 0;
if ($request->has("pop")) {
    $pop = 1;
    $sql = "SELECT * FROM msj WHERE tipo = '" . TipoMensaje::SIN_LEER . "' AND DATE_ADD(fecha,INTERVAL (vigencia - bd) DAY) >= CURRENT_DATE()";
    $registros = utils\IConnection::getRowsFromQuery($sql);
    $numRegistros = count($registros);
}

$Id = 8;
$Band = "SELECT tipo_permiso FROM cia;";
$stB = utils\IConnection::execSql($Band);
if ($stB["tipo_permiso"] === "TRA") {
    if (!is_string(utils\HTTPUtils::getSessionValue("FechaIni"))) {
        utils\HTTPUtils::setSessionValue("FechaIni", date("Y-m-d", strtotime('-1 day', strtotime(date('Y-m-d')))));
        utils\HTTPUtils::setSessionValue("FechaFin", date('Y-m-d'));
    } else if ($request->getAttributes("FechaIni")["FechaIni"] <> "") {
        utils\HTTPUtils::setSessionValue("FechaIni", $request->getAttributes("FechaIni")["FechaIni"]);
        utils\HTTPUtils::setSessionValue("FechaFin", $request->getAttributes("FechaFin")["FechaFin"]);
    }
    $FechaIni = utils\HTTPUtils::getSessionValue("FechaIni");
    $FechaFin = utils\HTTPUtils::getSessionValue("FechaFin");
}
$MuestraVisor = "SELECT valor FROM variables_corporativo WHERE llave = 'RolesSinVisor';";
$VlMv = utils\IConnection::execSql($MuestraVisor);
$VvlTeam = strpos($VlMv["valor"], $usuarioSesion->getTeam()) !== false ? 0 : 1;
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script type="text/javascript" src="js/showModalDialog.js"></script>
        <script type="text/javascript" src="js/visorPedidos.js"></script>
        <script>
            var timeOutRef;
            var count = 0;
            var popupWindow = null;
            var popVar = "<?= $pop ?>";

            function loadImg() {
                count++;
                var stringImg = "<div align='center'>";
                for (var i = 1; i <= count; i++) {
                    stringImg = stringImg + "<img src='libnvo/img1.gif'>";
                }
                stringImg = stringImg + "</div>";

                if (count === 5) {
                    count = 1;
                }
                $('#msj').html(stringImg);
            }

            function callLoad(milis) {
                window.setInterval(function () {
                    loadImg();
                }, milis);
            }
<?php
if ($stB["tipo_permiso"] === "TRA") {
    ?>
                function callVisorPedidos() {
                    $('#contenedorTra').load('visorPedidos.php?FechaFinalV=<?= $FechaFin ?>&FechaInicialV=<?= $FechaIni ?>', function (response, status, xhr) {
                        console.log(status);
                        if (status === "error") {
                            var msg = "Sorry but there was an error: ";
                            console.log(msg + xhr.status + " " + xhr.statusText);
                        }
                    });
                }
    <?php
}
?>
            function callVisor() {
                window.setInterval(function () {
                    $('#contenedor').load('phpsqlajax_genxml.php', function (response, status, xhr) {
                        if (status === "error") {
                            var msg = "Sorry but there was an error: ";
                            console.log(msg + xhr.status + " " + xhr.statusText);
                            //window.location = "500.html";
                        }
                        $(".flash").css("background", "#F5B7B1");
                        window.setTimeout(function () {
                            $(".flash").css("background", "");
                        }, 1000);
                    });
                }, 2000);
            }

            $(document).ready(function () {
                if (<?= $VvlTeam ?> == 0) {
                    $("#contenedor").hide();
                }

                $("#cFecha").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaIni")[0], "yyyy-mm-dd", $(this)[0]);
                    $("#Corte").val("");
                });
                $("#cFecha2").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaFin")[0], "yyyy-mm-dd", $(this)[0]);
                    $("#Corte").val("");
                });
                callLoad(1000);
                callVisor();
<?php
if ($stB["tipo_permiso"] === "TRA") {
    ?>
                    callVisorPedidos();

    <?php
}
?>
                generaCorte("<?= $cId ?>", "<?= $cPrc ?>", "<?= $isla ?>");
                pop();
            });

            pop = function () {
                var registros = "<?= $numRegistros ?>";
                console.log("registros: " + registros);
                if (popVar === "1" && registros > 0) {
                    console.log("Verificamos mensajes");
                    popupWindow = window.showModalDialog("vermensajes.php?Close=1&showheader=0", "Mensajes", "width:900px;height:450px;border: solid 2px #066;opacity: 0.97;");
                    console.log(popupWindow);
                }
            };
        </script>
    </head>

    <body>
        <?php BordeSuperior() ?>  

        <!--<div id='msj'></div>-->
        <?PHP
        $Band = "SELECT tipo_permiso FROM cia;";
        $stB = utils\IConnection::execSql($Band);
        if ($stB["tipo_permiso"] === "TRA") {
            ?>
            <a href=javascript:winuni("calendarRm.php?busca=ini");><i class="fa fa-calendar-plus-o fa-lg" aria-hidden="true" style="color:#009080;"> Registro de pedidos</i></a>
            <table style="width: 100%;text-align: center;" aria-hidden="true">
                <tr>
                    <td valign="top" style="min-height: 250px">
                        <div align='center' id='contenedorTra'></div>
                    </td>
                </tr>
            </table>
            <form name="form1" id="form1" method="post" action="">
                <table class="quicksearch" style="width: 100%;border-collapse: collapse; border: 1px solid #066;margin-top: 5px;" aria-hidden="true">
                    <tr>
                        <td style="text-align: right;width: 60%;"> &nbsp;
                            Fecha Inicial : 
                            <input type="text" id="FechaIni" name="FechaIni" style="width: 100px;" value="<?= $FechaIni ?>"> 
                            <img id="cFecha" src="libnvo/calendar.png" alt="Calendario" style="margin-right: 4%;">
                            Fecha Final :
                            <input type="text" id="FechaFin" name="FechaFin" style="width: 100px;" value="<?= $FechaFin ?>"> 
                            <img id="cFecha2" src="libnvo/calendar.png" alt="Calendario" style="margin-right: 2%;">
                            <input type="submit" id="BuscaFecha" name="BuscaFecha" value="Buscar" style="margin-right: 2%;font-family: sans-serif;color: #434343;">
                        </td>
                    </tr>
                </table>
            </form>
        <?php } else { ?>
            <table style="width: 100%;text-align: center;" aria-hidden="true">
                <tr>
                    <td valign="top" style="min-height: 250px">
                        <div align='center' id='contenedor'></div>
                    </td>
                </tr>
            </table>
            <?php
        }
        BordeSuperiorCerrar();
        PieDePagina();
        ?>
    </body>
    <script>

        function generaCorte(cId, cPrc, isla) {

            if (cPrc !== "") {
                console.log("iniciando proceso de corte");
                $.ajaxPrefilter(function (options, original_Options, jqXHR) {
                    options.async = true;
                });
                var callbacks = $.Callbacks();
                jQuery.ajax({
                    type: 'GET',
                    url: 'cerraryabrir.php',
                    dataType: 'json',
                    cache: false,
                    data: {"cId": cId, "cPrc": cPrc, "isla": isla},
                    beforeSend: function (xhr) {
                        //callVisor();
                    },
                    success: function (data) {
                        console.log(data);
                        window.location = "cambiotur.php?Msj=" + data.message;
                    },
                    error: function (jqXHR, textStatus) {
                        console.log(jqXHR);
                    }
                });
            } else {
                console.log("Parametro vacio: (" + cId + ")");
                //callVisor();
            }
        }
    </script>
</html>
