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
<script type="text/javascript" src="js/jquery-1.8.2.min.js"></script>
<script>
    function wingral(url) {
        window.open(url, 'wingeneral', 'status=no,tollbar=yes,scrollbars=yes,menubar=no,width=1000,height=600,left=100,top=50');
    }
    function winuni(url) {
        window.open(url, 'filtros', 'status=no,tollbar=yes,scrollbars=yes,menubar=no,width=790,height=550,left=250,top=80');
    }
    function winmin(url) {
        window.open(url, 'miniwin', 'width=400,height=500,left=200,top=120,location=no');
    }
    function winieps(url) {
        window.open(url, 'miniwin', 'width=400,height=200,left=200,top=120,location=no');
    }
    function confirmar(mensaje, url) {
        if (confirm(mensaje)) {
            document.location.href = url;
        }
    }
    function soporte(url) {
        window.open(url, 'Soporte Tecnico Detisa', 'width=750px,height=550px,top=250px,left=80px,Menubar=No,Resizable=NO,Location=NO,Scrollbars=yes,Status=no,Toolbar=no');
    }
    function mayus(e) {
        e.value = e.value.toUpperCase();
    }
    var fecha = new Date("<?= date("d M Y G:i:s") ?>");
    function hora() {
        //alert("hora activa");
        var hora = fecha.getHours();
        var minutos = fecha.getMinutes();
        var segundos = fecha.getSeconds();
        if (hora < 10) {
            hora = '0' + hora;
        }
        if (minutos < 10) {
            minutos = '0' + minutos;
        }
        if (segundos < 10) {
            segundos = '0' + segundos;
        }
        fech = hora + ":" + minutos + ":" + segundos;
        document.getElementById('hora').innerHTML = fech;
        fecha.setSeconds(fecha.getSeconds() + 1);
    }
    $(document).ready(function () {
        window.opener.clicksHref = 0;
        //Inicia la inactividad con eventos de mouse.
        $(this).mousemove(function (e) {
            //console.log("Ratón " + window.opener.inactividad);
            window.opener.inactividad = 1;
        });
        $(this).keypress(function (e) {
            //console.log("Teclado " + window.opener.inactividad);
            window.opener.inactividad = 1;
        });
    });
</script>    

<style>
    #hora{
        font-family: Arial, Helvetica, sans-serif;
        padding-right: 30px;
        font-weight: bold;
        font-size: 20px;
        color: #066;
    }
</style>

<link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>
<link href='libnvo/menu.css' rel='stylesheet' type='text/css'>
<link href='libnvo/tablas_css.css' rel='stylesheet' type='text/css'> 