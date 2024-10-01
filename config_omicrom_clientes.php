<?php
// PARAMENTROS DEL SISTEMA EN COLOR Y FONDOS
global $Gcia, $Gestacion, $Gdir, $Gfdogrid, $Gbarra, $InputCol;

$Gcia = "Sistema de control volumetrico ::: OMICROM";
$Gestacion = "Estacion Omicrom";
$Gdir = "Calle Normal de Maestros No 10 Col. Tulantongo Texcoco Edo Mex C.P 62743";

$Gfdogrid = "#E1E1E1";       // Fondo del Grid
$Gbarra = "#ACECAA";       // Color de la barra de movto dentro del grid   /7AAODD
$InputCol = "#006666";   //Input color;
?>

<meta http-equiv="expires" content="Sun, 01 Jan 2014 00:00:00 GMT"/>
<meta http-equiv="pragma" content="no-cache" />

<meta http-equiv="Content-Type" content="text/html; charset=utf-8">

<link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>

<link rel="stylesheet" href="libnvo/estilos_omicrom.css?n=10" type="text/css">
<link rel="stylesheet" href="libnvo/menu.css?n=3" type="text/css">
<link rel="stylesheet" href="libnvo/tablas_css.css?n=2" type="text/css">
<link rel="stylesheet" href="paginador/paginador.css?n=3" type="text/css">
<link rel="stylesheet" href="paginador/predictive_styles.css?n=2" type="text/css">
<link rel="stylesheet" href="fonts-awesome/css/font-awesome.css?n=2" type="text/css">
<link rel="stylesheet" type="text/css"  href="fonts-awesome/css/font-awesome.css">
<link rel="stylesheet" type="text/css"  href="fonts/css/all.css">
<link rel="stylesheet" type="text/css" href="libnvo/dhtmlgoodies_calendar.css?random=90051112" media="screen"/>
<script type="text/javascript" src="js/jquery-1.10.2.js"></script>
<script type="text/javascript" src="js/jquery.mockjax.js"></script>
<script type="text/javascript" src="js/jquery.autocomplete.js"></script>
<script type="text/javascript" src="paginador/predictive_search.js"></script>
<script type="text/javascript" src="libnvo/dhtmlgoodies_calendar.js?random=90090518"></script>
<script type="text/javascript" src="libnvo/js/bootstrap.min.js"></script>

<link type="text/css" rel="stylesheet"  href="bootstrap/bootstrap-4.0.0/dist/css/bootstrap-grid.css"/>
<link type="text/css" rel="stylesheet"  href="bootstrap/toast/css/estilosToast.css"/>

<script type="text/javascript">

    var fecha = new Date("<?= date("d M Y G:i:s") ?>");
    var clicksForm = 0;
    var clicksHref = 0;
    var url = window.location;
    window.history.replaceState("object or string", "Title", url.pathname);

    function wingral(url) {
        window.open(url, "wingeneral", "status=no,tollbar=yes,scrollbars=yes,menubar=no,width=1000,height=600,left=100,top=50");
    }

    function winuni(url) {
        window.open(url, "filtros", "status=no,tollbar=yes,scrollbars=yes,menubar=no,width=790,height=550,left=250,top=80");
    }

    function winmin(url) {
        window.open(url, "miniwin", "width=460,height=500,left=200,top=120,location=no");
    }

    function mayus(e) {
        e.value = e.value.toUpperCase();
    }

    function Direccion(url) {
        document.location.href = url;
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
        $("form").submit(function (e) {
            if (clicksForm > 0) {
                e.preventDefault();
                alert("Tu petición ha sido enviada,por favor esperé...");
                return false;
            }
            clicksForm = 1;
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
        $("a").on("click", function () {
            window.location.href = $(this).data("href");
        });
    });
</script>    
