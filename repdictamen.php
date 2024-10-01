<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

$Titulo = "Dictamen Realizados del $FechaI al $FechaF";

$selectNotas = "
select dic.id,me.foliofac,Round((me.volumenfac * 1000 ),0) volumenfac,me.fecha fechafac, prv.nombre,prv.rfc,
	   dic.numeroFolio,dic.fechaEmision,
       c.producto,dic.resultado,dicd.comp_octanaje,dicd.contiene_fosil,dicd.comp_fosil
 from dictamen dic inner join prv on dic.proveedor = prv.id
	 inner join cargas c on dic.noCarga = c.id
     inner join me on c.id = me.carga 
     inner join dictamend dicd on dic.id = dicd.id and c.tanque = dicd.tanque
where date(dic.fechaEmision) between date('$FechaI') and date('$FechaF') and me.documento != 'Jarreo';      
    ";

$registros = utils\IConnection::getRowsFromQuery($selectNotas);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css" type="text/css">
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css" type="text/css">
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#FechaI").val("<?= $FechaI ?>").attr("size", "10");
                $("#FechaF").val("<?= $FechaF ?>").attr("size", "10");
                $("#cFechaI").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaI")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#cFechaF").css("cursor", "hand").click(function () {
                    displayCalendar($("#FechaF")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $('#RpAceites').DataTable({
                    dom: 'Bfrtip',
                    paging: false,
                    buttons: [
                        'copyHtml5',
                        'excelHtml5',
                        'csvHtml5',
                        'pdfHtml5'
                    ]
                });
            });
        </script>
    </head>

    <body>

        <div id="container">
            <?php nuevoEncabezado($Titulo); ?>

            <div id="Reportes">
                    <table id="RpAceites" aria-hidden="true" class="display" style="width: 100%;">
                        <thead>
                            <tr class = "titulo">
                                <td colspan="13" style="text-align: center;">Notas de Credito</td>
                            </tr>
                            <tr>
                                <td>ID</td>
                                <td>Folio Factura</td>
                                <td>Fecha Factura</td>
                                <td>Volumen Facturado</td>
                                <td>Nombre</td>
                                <td>RFC</td>
                                <td>No.Folio</td>
                                <td>Fecha Emision </td>
                                <td>Producto </td>
                                <td>Resultado</td>
                                <td>Octanaje</td>
                                <td>Contiene_fosil</td>
                                <td>Composicion Fosil</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($registros as $rg) {
                                echo "<tr>";
                                echo "<td>" . $rg["id"] . "</td>";
                                echo "<td>" . $rg["foliofac"] . "</td>";
                                echo "<td>" . $rg["fechafac"] . "</td>";
                                echo "<td>" . $rg["volumenfac"] . "</td>";
                                echo "<td>" . $rg["nombre"] . "</td>";
                                echo "<td>" . $rg["rfc"] . "</td>";
                                echo "<td>" . $rg["numeroFolio"] . "</td>";
                                echo "<td>" . $rg["fechaEmision"] . "</td>";
                                echo "<td>" . $rg["producto"] . "</td>";
                                echo "<td>" . $rg["resultado"] . "</td>";
                                echo "<td>" . $rg["comp_octanaje"] . "</td>";
                                echo "<td>" . $rg["contiene_fosil"] . "</td>";
                                echo "<td>" . $rg["comp_fosil"] . "</td>";
                            }

                            ?>
                        </tbody>
                    </table>
            </div> 
        </div>
    </body>
</html>

