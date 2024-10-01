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

<link rel="stylesheet" href="libnvo/estilos_omicrom.css?sum=<?= md5_file("libnvo/estilos_omicrom.css") ?>" type="text/css">
<link rel="stylesheet" href="libnvo/estilos_login.css?sum=<?= md5_file("libnvo/estilos_login.css") ?>" type="text/css">
<link rel="stylesheet" href="fonts-awesome/css/font-awesome.css?n=2" type="text/css">
<!--<link rel="stylesheet" href="libnvo/css/bootstrap.min.css?n=2" type="text/css">-->
<script type="text/javascript" src="sweetalert2/sweetalert2.all.min.js"></script>
<script type="text/javascript" src="js/jquery-1.10.2.js"></script>
<script type="text/javascript" src="js/jquery.mockjax.js"></script>
<script type="text/javascript" src="js/js-usuarios.js"></script>
<script type="text/javascript" src="libnvo/js/bootstrap.min.js"></script>

<script type="text/javascript">
    var url = window.location;
    window.history.replaceState("object or string", "Title", url.pathname);
</script>