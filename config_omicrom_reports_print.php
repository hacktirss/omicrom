<?php
// PARAMENTROS DEL SISTEMA EN COLOR Y FONDOS
global $Gcia, $Gestacion, $Gdir, $Gfdogrid, $Gbarra, $InputCol;

$Gcia = "Reportes ::: OMICROM";
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

<link rel="stylesheet" href="libnvo/css/estilos_omicrom_reportes_print.css?n=<?= md5_file("libnvo/css/estilos_omicrom_reportes_print.css")?>"  type="text/css">
<link rel="stylesheet" href="fonts-awesome/css/font-awesome.css?n=2" type="text/css">
<link rel="stylesheet" type="text/css" href="libnvo/dhtmlgoodies_calendar.css?random=90051112" media="screen"/>
<!-- Normalize or reset CSS with your favorite library -->
<link rel="stylesheet" href="libnvo/css/normalize.min.css">

<!-- Load paper.css for happy printing -->
<link rel="stylesheet" href="libnvo/css/paper.css?n=<?= md5_file("libnvo/css/paper.css")?>">

<link rel="stylesheet" href="js/excel/css/tableexport.css">

<script type="text/javascript" src="js/jquery-1.10.2.js"></script>
<script type="text/javascript" src="libnvo/dhtmlgoodies_calendar.js?random=90090518"></script>
<script type="text/javascript" src="libnvo/js/bootstrap.min.js"></script>
<script type="text/javascript" src="js/FileSaver.js"></script>
<script type="text/javascript" src="js/xlsx.core.min.js"></script>
<script type="text/javascript" src="js/excel/js/tableexport.js"></script>


<script type="text/javascript">

    var clicksForm = 0;
    var clicksHref = 0;


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

        $("a").click(function () {
            if (clicksHref > 0) {
                //alert("Tu petición ha sido enviada,por favor esperé...");
                return false;
            }
            clicksHref = 1;
            return true;
        });
    });

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
        
        $("a").each(function () {
            $(this).data("href", $(this).attr("href")).removeAttr("href");
            $(this).css("cursor", "pointer");
        });
        $("a").on("click", function () {
            window.location.href = $(this).data("href");
        });
    });
</script>    
