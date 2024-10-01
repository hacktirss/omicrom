<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

$Titulo = "Reporte de usuarios";
$Sql = "SELECT id,name,uname,team,status,feclave,fecha_creacion,fecha_modificacion FROM authuser WHERE groupwork = 0 ORDER BY name;";
$registros = utils\IConnection::getRowsFromQuery($Sql);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports.php'; ?> 
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css" type="text/css">
        <script type="text/javascript" src="https://unpkg.com/xlsx@0.15.1/dist/xlsx.full.min.js"></script>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $('table.display').DataTable({
                    dom: 'Bfrtip',
                    paging: false,
                    ordering: false,
                    buttons: [
                        'excelHtml5',
                        'pdfHtml5'
                    ],
                    columnDefs: [{targets: 3, className: 'dt-body-right'}]
                });
                $(".fa-solid").click(function () {
                    console.log(this.dataset.usr);
                    var idUsr = this.dataset.usr;
                });
            });
            function ExportToExcel(type, fn, dl) {
                var elt = document.getElementById('tbl_exporttable_to_xls');
                var wb = XLSX.utils.table_to_book(elt, {sheet: "sheet1"});
                return dl ?
                        XLSX.write(wb, {bookType: type, bookSST: true, type: 'base64'}) :
                        XLSX.writeFile(wb, fn || ('ReporteGerencia.' + (type || 'xlsx')));
            }
        </script>
    </head>

    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo); ?>

            <div id="tbl_exporttable_to_xls">
                <table style="width: 100%;" summary="Mostramos titulo del reporte"><tr><th><h3><?= $Titulo ?></h3></th></tr></table>
                <div id="Reportes">
                    <table id="RpAceites" aria-hidden="true" class="display" style="width: 100%;">
                        <thead>
                            <tr style="font-weight: bold;background-color: #ABEBC6">
                                <th style="text-align: center">Nombre</th>
                                <th style="text-align: center">Usuario</th>
                                <th style="text-align: center">Perfil</th>
                                <th style="text-align: center">Status</th>
                                <th style="text-align: center">Creación</th>
                                <th style="text-align: center">Modificacion</th>
                                <th style="text-align: center">Expiración <br>Clave</th>
                                <th style="text-align: center">Cambios</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                            $nCnt = $nImp = $nCos = 0;
                            foreach ($registros as $rg) {
                                ?>
                                <tr>
                                    <td style="text-align: left"><?= $rg["name"] ?></td>
                                    <td style="text-align: left"><?= $rg["uname"] ?></td>
                                    <td style="text-align: left"><?= $rg["team"] ?></td>
                                    <td style="text-align: left"><?= $rg["status"] ?></td>
                                    <td class="numero"><?= $rg["fecha_creacion"] ?></td>
                                    <td class="numero"><?= $rg["fecha_modificacion"] ?></td>
                                    <td class="numero"><?= $rg["feclave"] ?></td>
                                    <td><em class="fa-solid fa-file-lines" data-usr="<?= $rg["uname"] ?>"></em></td>
                                </tr>
                                <?php
                                $nCnt += $rg["cantidad"];
                                $nImp += $rg["importe"];
                                $nCos += $rg["costo"];
                                $nRng++;
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="footer">
                <?php topePagina(); ?>
            </div>
        </div>
    </body>
</html>