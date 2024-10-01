<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("comboBoxes.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\Request::instance();
$connection = utils\IConnection::getConnection();
$usuarioSesion = getSessionUsuario();
if ($request->getAttributes("busca") === "ini") {
    $ts = "timeGridWeek";
}

if ($usuarioSesion->getTeam() === "Cliente") {
    $AddSql = "AND p.id_user =" . $usuarioSesion->getId();
}

$Rm = "SELECT p.fecha,com.color,com.descripcion,p.volumen,
    DATE_FORMAT(STR_TO_DATE(p.fecha, '%Y-%m-%d %H:%i:%s'),'%Y-%m-%dT%H:%i:%s') fechaT,
    DATE_FORMAT(STR_TO_DATE(fechafin, '%Y-%m-%d %H:%i:%s'),'%Y-%m-%dT%H:%i:%s') fechaF,
    CONCAT(p.id,'.-',com.descripcion,' ',ROUND(p.volumen,2),' Lts.') producto FROM pedidos p 
    LEFT JOIN com ON p.producto= com.clavei WHERE TRUE AND status < 5  $AddSql  ORDER BY p.id DESC;";
$Rms = utils\IConnection::getRowsFromQuery($Rm);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>.:: Agenda de Salidas ::.</title>
        <link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>
        <link rel="stylesheet" type="text/css"  href="libnvo/estilos_omicrom.css?var=<?= md5_file("libnvo/estilos_omicrom.css") ?>"/>
        <link rel="stylesheet" type="text/css"  href="libnvo/menu.css"/>
        <link rel="stylesheet" type="text/css"  href="libnvo/tablas_css.css"/>
        <link rel="stylesheet" type="text/css"  href="paginador/paginador.css"/>
        <link rel="stylesheet" type="text/css"  href="paginador/predictive_styles.css"/>
        <link rel="stylesheet" type="text/css"  href="fonts-awesome/css/font-awesome.css"/>
        <link rel="stylesheet" type="text/css" href="libnvo/dhtmlgoodies_calendar.css"/>
        <link rel="stylesheet" type="text/css"  href="libnvo/css/estilosFormularios.css"/>

        <link rel="stylesheet" type="text/css" href="notification/css/style.css"/>
        <link rel="stylesheet" type="text/css" href="notification/css/uniform.css"/>
        <link rel="stylesheet" type="text/css" href="notification/css/style_light.css"/>

        <!-- Sweetalert2@10 -->
        <script type="text/javascript" src="sweetalert2/sweetalert2.all.min.js"></script>
        <script type="text/javascript" src="js/jquery-3.1.1.js"></script>
        <script type="text/javascript" src="js/jquery.mockjax.js"></script>
        <script type="text/javascript" src="js/jquery.autocomplete1.js"></script>
        <script type="text/javascript" src="js/funcionesFormulario.js"></script>

        <script type="text/javascript" src="libnvo/dhtmlgoodies_calendar.js"></script>
        <script type="text/javascript" src="paginador/predictive_search.js"></script>
        <link type="text/css" rel="stylesheet"  href="bootstrap/bootstrap-4.0.0/dist/css/bootstrap-grid.css"/>
        <link type="text/css" rel="stylesheet"  href="bootstrap/toast/css/estilosToast.css"/>
        <link type="text/css" rel="stylesheet"  href="calendarRm.css"/>
        <script type="text/javascript" src="bootstrap/bootstrap-4.2.1/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="bootstrap/toast/js/functionsToast.js?var=1.1"></script>
        <link rel="stylesheet" href="bootstrap/bootstrap-4.0.0/dist/css/bootstrap-modal.css" type="text/css">
        <!--<script src="https://cdn.jsdelivr.net/gh/xcash/bootstrap-autocomplete@v2.3.7/dist/latest/bootstrap-autocomplete.min.js"></script>-->
        <link href='fullcalendar-5.4.0/lib/main.css' rel='stylesheet' />
        <script src='js/calendario.js'></script> 
        <script src='fullcalendar-5.4.0/lib/main.js'></script> 
        <script type="text/javascript" src="js/typeahead.bundle.js"></script>
        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function () {
<?php
if ($_REQUEST["busca"] === "ini") {
    ?>
                    if (sessionStorage.getItem('View') == null) {
                        sessionStorage.setItem('View', "timeGridWeek");
                        var href = "calendarRm.php";
                        $(location).attr('href', href);
                    }
    <?php
} else {
    ?>
                    sessionStorage.setItem('View', sessionStorage.getItem('View'));
    <?php
}
?>
                var sss = sessionStorage.getItem('View');
                console.log(sss);
                var calendarEl = document.getElementById('calendar');
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    height: '80%',
                    expandRows: true,
                    editable: true,
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                    },
                    selectable: true,
                    weekNumbers: true,
                    expandRows: true,
                    themeSystem: 'standard',
                    dayMaxEventRows: true, // for all non-TimeGrid views  
                    views: {
                        timeGrid: {
                            dayMaxEventRows: 5, // adjust to 6 only for timeGridWeek/timeGridDay
                            dayMaxEvents: 5,
                        }
                    },
                    select: function (info) {
                        var date = new Date();
                        var date2 = new Date(info.startStr);
                        if (date.toISOString() <= date2.toISOString()) {
                            /*Agregamos un nuevo registro a pedidos*/
                            $("#id").html("Agregar nueva salida");
                            $("#Cliente").val("");
                            $("#Cantidad").val("").prop("disabled", false);
                            $("#Fecha").val("").prop("disabled", false);
                            $("#Combustible").val("").prop("disabled", false);
                            $("#Terminal").val("").prop("disabled", false);
                            $("#IdTicket").html("0");
                            $("#IdPedidoHtml").html("Nuevo");
                            $("#ClienteShow").hide();
                            if ($("#TeamUsr").val() === "Cliente") {
                                $("#ClienteAdd").hide();
                                $("#ClienteShow").show();
                                $("#NombreCli").html("<?= $usuarioSesion->getNombre() ?>");
                            } else {
                                $("#ClienteAdd").show();
                            }
                            var fecha = info.startStr.split("T");
                            if (!(fecha[1] == null)) {
                                var fechaS = fecha[1];
                                var fecha2 = fechaS.split("-");
                                $("#Fecha").val(fecha[0] + " " + fecha2[0]);
                            } else {
                                $("#Fecha").val(fecha[0] + " 00:00:00");
                            }
                            $("#Actualizar").hide();
                            $("#Agregar").show();
                            $("#Nvo").hide();
                            $('#exampleModal').modal('toggle');
                        }
                    },
                    eventClick: function (info) {
                        /*Buscamos el detalle de nuestro pedido al cliente*/
                        $("#id").html("Detalle de registro");
                        $("#Nvo").show();
                        var txt = info.event._def.title;
                        var IdRm = txt.split(".");
                        console.log("HOLA " + IdRm[0]);
                        $.ajax({
                            type: "GET",
                            url: "ListBootstrap.php?Op=TraeTicket&IdNvo=" + IdRm[0],
                            success: function (data) {
                                var JsonData = JSON.parse(data);
                                console.log(JsonData);
                                $("#NombreCli").html(JsonData["Cliente"]);
                                $("#IdTicket").html(JsonData["IdRm"]);
                                $("#IdPedidoHtml").html(IdRm[0]);
                                if (JsonData["Status"] == 1) {
                                    $("#Cantidad").val(JsonData["Volumen"]).prop("disabled", false);
                                    $("#Nvo").show();
                                    $("#Actualizar").show();
                                } else {
                                    $("#Cantidad").val(JsonData["Volumen"]).prop("disabled", true);
                                    $("#Nvo").hide();
                                    $("#Actualizar").hide();
                                }
                                $("#Fecha").val(JsonData["Inicia"]).prop("disabled", true);
                                $("#Combustible").val(JsonData["Producto"]).prop("disabled", true);
                                if (typeof JsonData["Terminal"] !== "string") {
                                    $("#Terminal").val(JsonData["Terminal"]).prop("disabled", false);
                                } else {
                                    $("#Terminal").val(JsonData["Terminal"]).prop("disabled", true);
                                }

                                if (JsonData["Alerta"] == 1) {
                                    Swal.fire({
                                        title: "La cantidad a enviar fue modificada por el cliente. Favor de verificar",
                                        position: "center",
                                        icon: "info",
                                        showConfirmButton: true,
                                        confirmButtonText: "Aceptar",
                                        showCancelButton: true,
                                        cancelButtonColor: '#d33'
                                    }).then((result) => {
                                        if (result.isConfirmed) {

                                        }
                                    });
                                }
                            },
                            error: function (jqXHR, ex) {
                                console.log("Status: " + jqXHR.status);
                                console.log("Uncaught Error.\n" + jqXHR.responseText);
                                console.log(ex);
                            }
                        });
                        $("#bt").val(IdRm[0]);
                        $("#ClienteShow").show()
                        $("#NombreCli").html();
                        $("#ClienteAdd").hide();
                        $("#Agregar").hide();
                        $("#exampleModal").modal("toggle");
                    },
                    viewClassNames: function (info) {
                        sessionStorage.setItem('View', info.view.type);
                    },
                    eventDrop: function (info) {
                        var txt, IdRm, fecha, obtenemosHora, descomponemos, FechaIngresada, obtenemosHoraf;
                        txt = info.event._def.title;
                        IdRm = txt.split(".");
                        fecha = info.event.start.toISOString().split("T");
                        obtenemosHora = info.event.start + " ";
                        obtenemosHoraf = info.event.end + "as ";
                        descomponemos = obtenemosHora.split(" ");
                        descomponemosf = obtenemosHoraf.split(" ");
                        if (!(fecha[1] == null)) {
                            FechaIngresada = fecha[0] + " " + descomponemos[4];
                            FechaIngresadaF = fecha[0] + " " + descomponemosf[4];
                        } else {
                            FechaIngresada = fecha[0] + " 00:00:00";
                            FechaIngresadaF = fecha[0] + " 00:30:00";
                        }
                        console.log(descomponemosf[4]);
                        if (typeof descomponemosf[4] === 'undefined') {
                            console.log("Entramos");
                            FechaIngresadaF = FechaIngresada;
                        }
                        Swal.fire({
                            title: "¿ Seguro de cambiar Id " + info.event.title + " al dia " + FechaIngresada + " ?",
                            position: "center",
                            icon: "info",
                            showConfirmButton: true,
                            confirmButtonText: "Aceptar",
                            showCancelButton: true,
                            cancelButtonColor: '#d33'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    type: "GET",
                                    url: "ListBootstrap.php?Op=ActualizaHora&FechaF=" + FechaIngresadaF + "&Fecha=" + FechaIngresada + "&IdNvo=" + IdRm[0],
                                    success: function (data) {
                                        var Error = JSON.parse(data);
                                        if (Error.sts == "Error") {
                                            Swal.fire({
                                                title: "La fecha ingreada es menor a la fecha actual!",
                                                position: "center",
                                                icon: "error",
                                                showConfirmButton: true,
                                                confirmButtonText: "Aceptar",
                                                showCancelButton: true,
                                                cancelButtonColor: '#d33'
                                            }).then((result) => {
                                                var href = "calendarRm.php";
                                                $(location).attr('href', href);
                                                return true;
                                            });
                                        } else if (Error.sts == "Error2") {
                                            Swal.fire({
                                                title: "Pedido finalizado",
                                                position: "center",
                                                icon: "error",
                                                showConfirmButton: true,
                                                confirmButtonText: "Aceptar",
                                                showCancelButton: true,
                                                cancelButtonColor: '#d33'
                                            }).then((result) => {
                                                var href = "calendarRm.php";
                                                $(location).attr('href', href);
                                                return true;
                                            });
                                        }
                                    },
                                    error: function (jqXHR, ex) {
                                        console.log("Status: " + jqXHR.status);
                                        console.log("Uncaught Error.\n" + jqXHR.responseText);
                                        console.log(ex);
                                    }
                                });
                            } else {
                                info.revert();
                            }
                        })
                    },
                    eventResize: function (info) {
                        var obtenemosHora, descomponemos, fecha, IdRm, txt;
                        txt = info.event._def.title;
                        IdRm = txt.split(".");
                        fecha = info.event.start.toISOString().split("T");
                        obtenemosHora = info.event.end + " ";
                        descomponemos = obtenemosHora.split(" ");
                        if (!(fecha[1] == null)) {
                            FechaIngresada = fecha[0] + " " + descomponemos[4];
                        } else {
                            FechaIngresada = fecha[0] + " 00:00:00";
                        }
                        Swal.fire({
                            title: "¿ Seguro de cambiar fecha final de Id " + info.event.title + " al dia " + FechaIngresada + " ?",
                            position: "center",
                            icon: "info",
                            showConfirmButton: true,
                            confirmButtonText: "Aceptar",
                            showCancelButton: true,
                            cancelButtonColor: '#d33'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    type: "GET",
                                    url: "ListBootstrap.php?Op=AumentaHora&Fecha=" + FechaIngresada + "&IdNvo=" + IdRm[0],
                                    success: function (data) {

                                    },
                                    error: function (jqXHR, ex) {
                                        console.log("Status: " + jqXHR.status);
                                        console.log("Uncaught Error.\n" + jqXHR.responseText);
                                        console.log(ex);
                                    }
                                });
                            } else {
                                info.revert();
                            }
                        });
                        console.log(FechaIngresada);
                    },
                    initialView: sss,
                    navLinks: true, // can click day/week names to navigate views
                    nowIndicator: true,
                    dayMaxEvents: true, // allow "more" link when too many events
                    events: [
<?php
foreach ($Rms as $rs) {
    ?>
                            {
                                title: '<?= $rs["producto"] ?>',
                                start: '<?= $rs["fechaT"] ?>',
                                end: '<?= $rs["fechaF"] ?>',
                                color: '<?= $rs["color"] ?>'
                            },
    <?php
}
?>
                    ]
                }
                );
                calendar.render();
                /*AQUI INICIAN LOS BOTONES AZULES*/
                $("#Agregar").click(function () {
                    var IdCli = $("#basicAuto").val();
                    var cnt = IdCli.split(".");
                    if ($("#Combustible").val().length > 0) {
                        if ($("#basicAuto").val().length > 0) {
                            if ($("#Terminal").val().length > 0) {
                                if ($("#Cantidad").val() > 0) {
                                    $.ajax({
                                        type: "GET",
                                        url: "ListBootstrap.php?Op=AgregaRm&Id_Cia=" + $("#Id_Cia").val() + "&IdFault=<?= $usuarioSesion->getId() ?>&TerminalA=" + $("#Terminal").val() + "&Fecha=" + $("#Fecha").val() + "&Cliente=" + cnt[0] + "&Cantidad=" + $("#Cantidad").val() + "&Combustible=" + $("#Combustible").val(),
                                        success: function (data) {
                                            var Error = JSON.parse(data);
                                            if (Error.sts == "Error") {
                                                Swal.fire({
                                                    title: "La fecha ingreada es menor a la fecha actual!",
                                                    position: "center",
                                                    icon: "error",
                                                    showConfirmButton: true,
                                                    confirmButtonText: "Aceptar",
                                                    showCancelButton: true,
                                                    cancelButtonColor: '#d33'
                                                })
                                            } else {
                                                var href = "calendarRm.php";
                                                $(location).attr('href', href);
                                                return true;
                                            }
                                        },
                                        error: function (jqXHR, ex) {
                                            console.log("Status: " + jqXHR.status);
                                            console.log("Uncaught Error.\n" + jqXHR.responseText);
                                            console.log(ex);
                                        }
                                    });
                                } else {
                                    alert("Favor de ingresar algúna cantidad");
                                }
                            } else {
                                alert("Favor de ingresar algúna terminal");
                            }
                        } else {
                            alert("Favor de ingresar algún cliente");
                        }
                    } else {
                        alert("Favor de ingresar algún producto");
                    }
                });
                /*ACTUALIZAMOS*/
                $("#Actualizar").click(function () {
                    $.ajax({
                        type: "GET",
                        url: "ListBootstrap.php?Op=ActualizaCantidadPedido&IdFault=<?= $usuarioSesion->getId() ?>&Cantidad=" + $("#Cantidad").val() + "&IdPedido=" + $("#bt").val() + "&Terminal=" + $("#Terminal").val()
                    });
                    calendar.render();
                });

                /*AQUI TERMINAMOS LOS BOTONES*/
            });
        </script>
    </head>
    <body>

        <div id='calendar-container'>
            <div id='calendar'></div>
        </div>
    </body>
</html>
<input type="hidden" name="TeamUsr" id="TeamUsr" value="<?= $usuarioSesion->getTeam() ?>">
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="color: #FFFFFF; background-color: #F6F6F6;">
            <form name='form1' method='get' action="<?= $_SERVER['PHP_SELF'] ?>" onSubmit='return ValidaCampos();'>
                <div class="modal-header" style = "color: #FFFFFF; background-color: #255B83">
                    <h3 class="modal-title" id="exampleModalLabel">
                        <a style="align-content: center;"><div id="id"></div></a>
                    </h3>
                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table border='0' style="width: 100%;" summary="Formulario de pedidos">
                        <tr><th><div class="id"></div></th></tr>
                        <tr>
                            <td valign="top">
                                <table border='0' style="width: 95%;color: #566573;font-size: 18px;" summary="Datos del formaulario">
                                    <tr><th colspan="2"></th></tr>
                                    <tr style="height: 30px">
                                        <td style="text-align: right;height: 35px;">Id Pedido :</td>
                                        <td><div id="IdPedidoHtml"></div></td>
                                    </tr> 
                                    <tr style="height: 30px">
                                        <td style="text-align: right;height: 35px;">Fecha/Hora :</td>
                                        <td><input style="width: 90%" type='text' class='inputs' id="Fecha" name='Fecha' required></input></td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: right;height: 35px;">Combustible :</td>
                                        <td><?php
                                            $qry = utils\IConnection::getRowsFromQuery("SELECT com.clavei, com.descripcion FROM com WHERE com.activo = 'Si' ORDER BY com.clavei;");
                                            $html = "<select style='width: 90%' class = 'inputs' name = 'Combustible' id = 'Combustible' ><option value = ''>SELECCIONE UN PRODUCTO</option>";
                                            foreach ($qry as $rs) {
                                                $html .= "<option value = '" . $rs["clavei"] . "'>" . str_replace(' ', '&nbsp;', $rs["clavei"]) . " | " . $rs["descripcion"] . "</option>";
                                            }
                                            echo $html . "</select>";
                                            ?></td>
                                    </tr>
                                    <tr id="ClienteAdd">
                                        <td style="text-align: right;height: 35px;">Cliente : </td>
                                        <td>
                                            <input id="basicAuto" id="Cliente" name="Cliente" type="text" class='inputs' placeholder="Busca cliente"  style="width: 90%;">
                                        </td>
                                    </tr>
                                    <tr id="ClienteShow">
                                        <td style="text-align: right;height: 35px;">Cliente : </td>
                                        <td>
                                            <div id="NombreCli"></div>
                                        </td>
                                    </tr> 
                                    <tr>
                                        <td style="text-align: right;height: 35px;">Terminal : </td>
                                        <td>
                                            <input id="Terminal" name="Terminal" type="text" class='inputs' placeholder="Busca terminal de almacenamiento"  style="width: 90%;">
                                        </td>
                                    </tr>
                                    <tr id="ClienteShow">
                                        <td style="text-align: right;height: 35px;">Id Ticket : </td>
                                        <td>
                                            <div id="IdTicket"></div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="text-align: right;height: 35px;">Cantidad :</td>
                                        <td>
                                            <input style="width: 90%" type='text' class='inputs' id="Cantidad" name='Cantidad' required></input>
                                        </td>
                                    </tr>
                                    <?php
                                    if ($usuarioSesion->getTeam() === "Administrador") {
                                        ?>
                                        <tr id="Nvo">
                                            <td colspan="2">
                                                <input class="botones"  style="margin-right: 1%;margin-left: 25%;"  type="submit" value="Aceptar Venta" id="AceptarVenta" name='boton'>
                                                <input class="botones" type="submit" value="Cancelar Venta" id="CancelarVenta" name='boton'>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <input class="botones" type="button" value='Agregar' id="Agregar" name='boton'>
                    <input class="botones" type="submit" value="Actualizar" id="Actualizar" name='boton'>
                    <input type="hidden" name="bt" id="bt">
                    <input type="hidden" name="busca" id="busca">
                </div>
            </form>
        </div>
    </div>
</div>
<style>
    .fc .fc-timegrid-now-indicator-line{
        border: 5px solid BLUE;
    }
    .fc-scroller-harness{
        height: 126% !important;
    }
    .fc-scrollgrid-section{
        height: 0% !important;
    }
</style>