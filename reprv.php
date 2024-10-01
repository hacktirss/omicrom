<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

$Titulo = "Pagos Realizados del $FechaI al $FechaF";

$selectNotas = "
SELECT 
    pp.id,pr.nombre,pp.fecha,pp.concepto,pp.importe,pp.status
FROM
    pagosprv pp left join prv pr on pp.proveedor = pr.id 
WHERE
    DATE(fecha) BETWEEN DATE('$FechaI') AND DATE('$FechaF');    
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
                                <td colspan="13" style="text-align: center;"></td>
                            </tr>
                            <tr>
                                <td>No.Pago </td>
                                <td>Nombre </td>
                                <td>Fecha </td>
                                <td>Concepto </td>
                                <td>Importe </td>
                                <td>Status </td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($registros as $rg) {
                                echo "<tr>";
                                echo "<td>" . $rg["id"] . "</td>";
                                echo "<td>" . $rg["nombre"] . "</td>";
                                echo "<td>" . $rg["fecha"] . "</td>";
                                echo "<td>" . $rg["concepto"] . "</td>";
                                echo "<td>" . $rg["importe"] . "</td>";
                                echo "<td>" . $rg["status"] . "</td>";
                            }
                            ?>
                        </tbody>
                    </table>
            </div> 
        </div>
    </body>
</html>

