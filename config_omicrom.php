<?php
// PARAMENTROS DEL SISTEMA EN COLOR Y FONDOS
global $Gcia, $Gestacion, $Gdir, $Gfdogrid, $Gbarra, $InputCol, $Msj;

$Gcia = "Sistema de control volumetrico ::: OMICROM";
$Gestacion = "Estacion Omicrom";
$Gdir = "Calle Normal de Maestros No 10 Col. Tulantongo Texcoco Edo Mex C.P 62743";

$Gfdogrid = "#E1E1E1";       // Fondo del Grid
$Gbarra = "#ACECAA";       // Color de la barra de movto dentro del grid   /7AAODD
$InputCol = "#006666";   //Input color;

/* Parametros para formularios */
$required = "required";
$alignRight = "right";
/* date_default_timezone_set('America/Mexico_City'); */
?>
<!--<script src="https://kit.fontawesome.com/4a752e22b0.js" crossorigin="anonymous"></script>-->
<meta http-equiv="expires" content="Sun, 01 Jan 2014 00:00:00 GMT"/>
<meta http-equiv="pragma" content="no-cache" />

<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta charset="utf-8">
<meta http-equiv="refresh" content="800;url=servicio.php" /> 

<link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>

<link rel="stylesheet" type="text/css"  href="libnvo/estilos_omicrom.css?var=<?= md5_file("libnvo/estilos_omicrom.css") ?>">
<link rel="stylesheet" type="text/css"  href="libnvo/menu.css">
<link rel="stylesheet" type="text/css"  href="libnvo/tablas_css.css">
<link rel="stylesheet" type="text/css"  href="paginador/paginador.css">
<link rel="stylesheet" type="text/css"  href="paginador/predictive_styles.css">
<link rel="stylesheet" type="text/css"  href="fonts-awesome/css/font-awesome.css">

<link rel="stylesheet" type="text/css"  href="fonts/css/all.css">
<link rel="stylesheet" type="text/css" href="libnvo/dhtmlgoodies_calendar.css">
<link rel="stylesheet" type="text/css"  href="libnvo/css/estilosFormularios.css">

<link rel="stylesheet" type="text/css" href="notification/css/style.css">
<link rel="stylesheet" type="text/css" href="notification/css/uniform.css">
<link rel="stylesheet" type="text/css" href="notification/css/style_light.css">

<link type="text/css" rel="stylesheet"  href="bootstrap/bootstrap-4.0.0/dist/css/bootstrap-grid.css"/>
<link type="text/css" rel="stylesheet"  href="bootstrap/toast/css/estilosToast.css"/>

<!-- Zendesk -->
<link type="text/css" rel="stylesheet"  href="libnvo/zendesk.css"/>

<!-- dropzonejs -->
<link rel="stylesheet" type="text/css" href="dropzone/min/dropzone.min.css">
<!-- Sweetalert2@10 -->
<!--<script src="//cdn.jsdelivr.net/npm/sweetalert2@10"></script>-->
<script type="text/javascript" src="sweetalert2/sweetalert2.all.min.js"></script>
<script type="text/javascript" src="js/jquery.mockjax.js"></script>
<script type="text/javascript" src="js/jquery-1.10.2.js"></script>
<script type="text/javascript" src="js/jquery.mockjax.js"></script>
<script type="text/javascript" src="js/jquery.autocomplete.js"></script>
<script type="text/javascript" src="js/funcionesFormulario.js"></script>
<script type="text/javascript" src="js/useAlert.js"></script>
<script type="text/javascript" src="libnvo/dhtmlgoodies_calendar.js"></script>
<script type="text/javascript" src="notification/omicrom.js"></script>
<script type="text/javascript" src="paginador/predictive_search.js"></script>

<script type="text/javascript" src="bootstrap/bootstrap-4.2.1/js/bootstrap.min.js"></script>
<script type="text/javascript" src="bootstrap/toast/js/functionsToast.js?var=1.1"></script>


<script type="text/javascript">

    var fecha = new Date("<?= date("d M Y G:i:s") ?>");
    var clicksForm = 0;
    var clicksHref = 0;
    var url = window.location;
    var windowGral = null;
    var windowUni = null;
    var windowMin = null;
    var windowIeps = null;
    var windowSoporte = null;

    window.history.replaceState("object or string", "Title", url.pathname);

    function wingral(url) {
        windowGral = window.open(url, "wingeneral", "status=no,tollbar=yes,scrollbars=yes,menubar=no,width=1000,height=600,left=100,top=50");
    }

    function winuni(url) {
        windowUni = window.open(url, "filtros", "status=no,tollbar=yes,scrollbars=yes,menubar=no,width=790,height=550,left=250,top=80");
    }

    function winmin(url) {
        windowMin = window.open(url, "miniwin", "width=460,height=500,left=200,top=120,location=no");
    }

    function winieps(url) {
        windowIeps = window.open(url, "miniwin", "width=400,height=200,left=200,top=120,location=no");
    }

    function soporte(url) {
        windowSoporte = window.open(url, "Soporte Tecnico Detisa", "width=750px,height=550px,top=250px,left=80px,Menubar=No,Resizable=NO,Location=NO,Scrollbars=yes,Status=no,Toolbar=no");
    }

    function closeOpener() {
        if (windowGral !== null) {
            windowGral.close();
        }
        if (windowUni !== null) {
            windowUni.close();
        }
        if (windowMin !== null) {
            windowMin.close();
        }
        if (windowIeps !== null) {
            windowIeps.close();
        }
        if (windowSoporte !== null) {
            windowSoporte.close();
        }
    }

    function confirmar(mensaje, url) {
        if (confirm(mensaje)) {
            document.location.href = url;
        }
    }

    function confirmarOperacion(url) {
        var mensaje = "Esta seguro que quiere realizar esta operación?";
        if (confirm(mensaje)) {
            document.location.href = url;
        }
    }
    function Direccion(url) {
        document.location.href = url;
    }

    function borrarRegistro(direccion, identificador, variable) {
        var mensaje = "Esta seguro que quiere borrar el registro " + identificador + "?";
        if (confirm(mensaje)) {
            var url = direccion + "?op=Si&" + variable + "=" + identificador;
            document.location.href = url;
        }
    }

    function generaDevolucion(direccion, ticket, identificador, variable) {
        var mensaje = "Esta seguro que quiere generar una devolución para facturar el consumo " + ticket + " a un cliente?";
        if (confirm(mensaje)) {
            var url = direccion + "?op=Si&action=devolucion&" + variable + "=" + identificador;
            document.location.href = url;
        }
    }

    function openInNewTab(url) {
        var win = window.open(url, '_blank');
        win.focus();
    }

    function mayus(e) {
        e.value = e.value.toUpperCase();
    }

    function hora() {
        //alert("hora activa");
        var hora = fecha.getHours();
        var minutos = fecha.getMinutes();
        var segundos = fecha.getSeconds();
        if (hora < 10) {
            hora = "0" + hora;
        }
        if (minutos < 10) {
            minutos = "0" + minutos;
        }
        if (segundos < 10) {
            segundos = "0" + segundos;
        }
        fech = hora + ":" + minutos + ":" + segundos;
        document.getElementById("hora").innerHTML = fech;
        fecha.setSeconds(fecha.getSeconds() + 1);
    }

    $(document).ready(function () {
        $(".click").hover(function () {
            $(this).addClass("fa-bounce");
        }, function () {
            $(this).removeClass("fa-bounce");
        });
        var Msj = "<?= $Msj ?>";
        if (Msj !== "" && Msj.length > 0) {
            console.log(Msj);
            error = Msj.indexOf("error");
            error2 = Msj.indexOf("Error");
            if (error >= 0 || error2 >= 0) {
                mostrarToast(Msj, false);
            } else {
                mostrarToast(Msj, true);
            }
        }


        $("form").submit(function (e) {
            if (clicksForm > 0) {
                e.preventDefault();
                console.log("Tu petición ha sido enviada,por favor esperé...");
                return false;
            }
            clicksForm = 1;
            Swal.fire({
                title: 'Cargando',
                showConfirmButton: false,
                background: "rgba(213, 216, 220 , 0.9)",
                backdrop: "rgba(5, 5, 25, 0.5)",
                allowOutsideClick: false,
                closeOnConfirm: true
            });
            Swal.showLoading();
            return true;
        });
        $(".textosCualli").click(function () {
            if (clicksHref > 0) {
                console.log("Tu petición ha sido enviada,por favor esperé...");
                return false;
            }
            clicksHref = 1;
            return true;
        });
        $("a").each(function () {
            $(this).data("href", $(this).attr("href")).removeAttr("href");
            $(this).css("cursor", "pointer");
        });
        $("a").on("click", function (e) {
            e.preventDefault();
            window.location.href = $(this).data("href");
            /*var url = $(this).data("href").split('?')[0];*/
        });
        $(".fa-print").hover(function () {
            $(this).attr("title", "Imprimir registro");
        });
        $(".fa-file-text").hover(function () {
            $(this).attr("title", "Detalle del registro");
        });
        $(".fa-edit").hover(function () {
            $(this).attr("title", "Editar registro");
        });
        $(".fa-trash").hover(function () {
            $(this).attr("title", "Borrar registro");
        });
        $(".fa-barcode").hover(function () {
            $(this).attr("title", "Codigos del cliente");
        });
        $(".fa-file-pdf-o").hover(function () {
            $(this).attr("title", "Obtener factura en PDF");
        });
        $(".fa-file-code-o").hover(function () {
            $(this).attr("title", "Obtener archivo XML");
        });
        $("body").on("shown.bs.modal", "#modal-acerca-de", function (e) {
            var event = $(e.relatedTarget);
            var Identificador = event.data("identificador");
            var modalTitle = "Acerca de Omicrom ";
            var modal = $(this);

            modal.find(".modal-title").html(modalTitle);

            $.ajax({
                type: "POST",
                url: "getByAjax.php",
                data: {"Origen": "ObtenAcercaDe"},
                beforeSend: function (xhr, opts) {

                },
                success: function (data) {
                    console.log(data);
                    var array = JSON.parse(data);
                    console.log(array);
                    var lista = array.rows;
                    modal.find("table tbody").empty();
                    var e = 0;
                    jQuery.each(lista, function (name, value) {
                        var back = "";
                        if (e % 2 == 0) {
                            console.log(value.clave);
                            back = "style='background: #EAECEE'";
                        }
                        modal.find("table tbody").append("<tr " + back + "><td >" + value.nombre + "</td><td>" + value.version + "</td><td>" + value.md5 + "</td></tr>");
                        e++;
                    });

                },
                error: function (jqXHR, ex) {
                    console.log("Status: " + jqXHR.status);
                    console.log("Uncaught Error.\n" + jqXHR.responseText);
                    console.log(ex);
                }
            });

        });
        $("#NumColumns").val(<?= $_SESSION["NewRow"] ?>);
        $("#NumColumns").change(function () {
            if ($("#NumColumns").val() > 4 && $("#NumColumns").val() <= 200) {
                var dir = window.location + "?criteria=ini&rowCnt=" + $("#NumColumns").val();
                window.location.href = dir;
            } else if ($("#NumColumns").val() <= 4) {
                alertTextValidation("El minimo a valor es 5, Favor de verificar", "", "", "", false, "error", 3500, false);
            } else if ($("#NumColumns").val() >= 201) {
                alertTextValidation("El maximo valor es 200, Favor de verificar", "", "", "", false, "error", 3500, false);
            }
        });
    });
    (function ($) {
        $.fn.toUpperCase = function () {
            this.keyup(function () {
                $(this).val($(this).val().toUpperCase());
            });
        };
    })(jQuery);

    function imprimirSeleccion(nombre) {
        var ficha = document.getElementById(nombre);
        var ventimp = window.open(' ', 'popimpr');
        ventimp.document.write(ficha.innerHTML);
        ventimp.document.close();
        ventimp.print( );
        ventimp.close();
    }
</script>    

<link rel="stylesheet" href="bootstrap/bootstrap-4.0.0/dist/css/bootstrap-modal.css" type="text/css">
<div class="modal fade" id="modal-acerca-de">
    <div class="modal-dialog modal-lg">
        <form name="formModal1" id="formModal1" method="post" action="">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Acerca de Omicrom</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">                                    
                    <div class="form-group row">
                        <div class="col-12">
                            <div id="div_print">
                                <table aria-hidden="true" style="width: 100%;">
                                    <thead>
                                        <tr style="background-color: #52BE80;color:white">
                                            <th scope = "col" style="width: 15%;">Equipo</th>
                                            <th scope = "col">Version</th>
                                            <th scope = "col">Md5</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                            <div style="width: 100%;text-align: right;"><a style="font-family: sans-serif;font-size: 13px;font-weight: bold;" href="javascript:imprimirSeleccion('div_print')" >Imprimir <em class="fa-solid fa-print"></em></a></div>
                        </div>                        
                    </div>
                </div>                
            </div>
            <!-- /.modal-content -->
            <input type="hidden" name="Identificador" class="Identificador">
            <input type="hidden" name="ParamValidator" class="ParamValidator">
        </form>
    </div>
</div>
