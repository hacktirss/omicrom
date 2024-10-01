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

<link rel="stylesheet" href="libnvo/estilos_omicrom_reportes.css?n=<?= md5_file("libnvo/estilos_omicrom_reportes.css") ?>"  type="text/css">
<link rel="stylesheet" href="libnvo/tablas_css.css?n=2" type="text/css">
<link rel="stylesheet" href="paginador/paginador.css?n=3" type="text/css">
<link rel="stylesheet" href="paginador/predictive_styles.css?n=2" type="text/css">
<link rel="stylesheet" href="fonts-awesome/css/font-awesome.css?n=2" type="text/css">
<link rel="stylesheet" type="text/css"  href="fonts/css/all.css">
<link rel="stylesheet" type="text/css" href="libnvo/dhtmlgoodies_calendar.css?random=90051112" media="screen"/>

<link rel="stylesheet" href="js/excel/css/tableexport.css">

<script type="text/javascript" src="js/jquery-1.10.2.js"></script>
<script type="text/javascript" src="js/jquery.mockjax.js"></script>
<script type="text/javascript" src="js/jquery.autocomplete.js"></script>
<script type="text/javascript" src="paginador/predictive_search.js"></script>
<script type="text/javascript" src="libnvo/dhtmlgoodies_calendar.js?random=90090518"></script>
<script type="text/javascript" src="libnvo/js/bootstrap.min.js"></script>
<script type="text/javascript" src="graficos/js/highcharts.js"></script>
<script type="text/javascript" src="graficos/js/modules/exporting.js"></script>

<script type="text/javascript" src="js/FileSaver.js"></script>
<script type="text/javascript" src="js/xlsx.core.min.js"></script>
<script type="text/javascript" src="js/excel/js/tableexport.js"></script>

<link rel="stylesheet" type="text/css" href="datatables/Buttons-2.2.2/css/buttons.bootstrap4.css"/>

<script type="text/javascript" src="datatables/DataTables-1.11.5/js/jquery.dataTables.js"></script>
<script type="text/javascript" src="datatables/DataTables-1.11.5/js/dataTables.bootstrap4.js"></script>
<script type="text/javascript" src="datatables/Buttons-2.2.2/js/dataTables.buttons.js"></script>
<script type="text/javascript" src="datatables/Buttons-2.2.2/js/buttons.bootstrap4.js"></script>
<script type="text/javascript" src="datatables/Buttons-2.2.2/js/buttons.html5.js"></script>
<script type="text/javascript" src="datatables/JSZip-2.5.0/jszip.js"></script>
<script type="text/javascript" src="datatables/pdfmake-0.1.36/pdfmake.js"></script>
<script type="text/javascript" src="datatables/pdfmake-0.1.36/vfs_fonts.js"></script>

<script type="text/javascript" src="sweetalert2/sweetalert2.all.min.js"></script>
<script type="text/javascript">

    var clicksForm = 0;
    var clicksHref = 0;
    var url = window.location;
    window.history.replaceState("object or string", "Title", url.pathname);

    $(document).ready(function () {
        try {
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
        } catch (err) {
            console.log(err);
        }

        $("a").each(function () {
            $(this).data("href", $(this).attr("href")).removeAttr("href");
            $(this).css("cursor", "pointer");
        });
        $("a").on("click", function () {
            window.location.href = $(this).data("href");
        });

        $("form").submit(function (e) {
            if (clicksForm > 0) {
                e.preventDefault();
                console.log("Tu petición ha sido enviada,por favor esperé...");
                return false;
            }
            clicksForm = 1;
            console.log("_____________________________" + $(".OpX").val());
            if ($(".OpX").val() != 1) {
                Swal.fire({
                    title: 'Cargando',
                    showConfirmButton: false,
                    background: "rgba(213, 216, 220 , 0.9)",
                    backdrop: "rgba(5, 5, 25, 0.5)",
                    allowOutsideClick: false,
                    closeOnConfirm: true
                });
                Swal.showLoading();
            }
//            return true;
        });
    });


    function wingral(url) {
        window.open(url, 'wingeneral', 'status=no,tollbar=yes,scrollbars=yes,menubar=no,width=1000,height=600,left=100,top=50');
    }
</script>    
