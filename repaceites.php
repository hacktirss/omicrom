<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";

$Titulo = "Inventario de aceites del $FechaI al $FechaF";

$registros = utils\IConnection::getRowsFromQuery($selectInventario);

$selectInvd = "
                SELECT invd.id producto, invd.isla_pos, invd.existencia ,inv.codigo
                FROM inv,invd
                WHERE 1 = 1 
                AND inv.id = invd.id
                AND inv.rubro = 'Aceites' AND inv.activo = 'Si'
                ORDER BY inv.id, invd.isla_pos";

$registrosIsla = utils\IConnection::getRowsFromQuery($selectInvd);

foreach ($registrosIsla as $value) {
    $registrosArray[$value["producto"]][$value["isla_pos"]] = $value["existencia"];
}
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
                $("#Detallado").val("<?= $Detallado ?>");
                $('#RpAceites').DataTable({
                    language: {
                        "decimal": "",
                        "emptyTable": "No hay información",
                        "info": "_START_ a _END_ registros",
                        "infoEmpty": "Mostrando 0 to 0 of 0 Entradas",
                        "infoFiltered": "(Filtrado de _MAX_ total entradas)",
                        "infoPostFix": "",
                        "thousands": ",",
                        "lengthMenu": "Mostrar _MENU_ Entradas",
                        "loadingRecords": "Cargando...",
                        "processing": "Procesando...",
                        "search": "Buscar:",
                        "zeroRecords": "Sin resultados encontrados",
                        "paginate": {
                            "first": "Primero",
                            "last": "Ultimo",
                            "next": "Siguiente",
                            "previous": "Anterior"
                        }
                    },
                    dom: 'Bfrtip',
                    paging: false,
                    buttons: [
                        {extend: 'copyHtml5', footer: true},
                        {extend: 'excelHtml5', footer: true},
                        {extend: 'csvHtml5', footer: true},
                        {extend: 'pdfHtml5', footer: true}
                    ]
                });
            });
        </script>
    </head>
    <style>
        label,.dataTables_info{
            color:#585858;
            font-size: 13px;
        }
        .btn{
            font-size: 9px;
        }
        tbody tr td:nth-child(2){
            text-align: left;
        }
        tbody tr td:nth-child(1){
            text-align: right;
            padding-right: 5px;
        }
        tbody tr td:nth-child(+n+3){
            text-align: right;
        }
    </style>
    <body>

        <div id="container">
            <?php nuevoEncabezado($Titulo); ?>

            <div id="Reportes">

                <?php
                if ($Detallado === "No") {
                    ?>

                    <table id="RpAceites" aria-hidden="true" class="display dataTables" style="width: 100%;font-size: 11px;">
                        <thead>
                            <tr>
                                <td colspan="5"></td>
                                <td colspan="<?= count($IslasPosicionInventario) ?>">Islas o Dispensarios</td>
                                <td colspan="2" style="text-align: center;"> Totales</td>
                            </tr>
                            <tr style="height: 11px;">
                                <td style="width: 60px;">Producto </td>
                                <td style="width: 500px;">Descripcion</td>
                                <!--<td>Codigo</td>-->
                                <td>Costo </td>
                                <td>Precio </td>
                                <td>Almacen </td>
                                <?php foreach ($IslasPosicionInventario as $value) { ?>
                                    <td><?= $value ?></td>
                                <?php } ?>
                                <td>Piezas </td>
                                <td>Importe </td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $Gtotal = $Almacen = $Total = 0;
                            $arrayP = null;
                            foreach ($registros as $rg) {
                                echo "<tr>";

                                echo "<td>" . $rg["clave_producto"] . "</td>";
                                echo "<td>" . $rg["descripcion"] . "</td>";
//                                echo "<td>" . $rg["codigo"] . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg["costo"], 2) . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg["precio"], 2) . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg["existencia"], 0) . "</td>";

                                $SubTotal = $rg["existencia"];
                                foreach ($IslasPosicionInventario as $value) {
                                    $vas = $registrosArray[$rg["id"]][$value] === null ? 0 : $registrosArray[$rg["id"]][$value];
                                    echo '<td class="numero">' . $vas . '</td>';
                                    $SubTotal += $registrosArray[$rg["id"]][$value];
                                    $arrayP[$value] += $registrosArray[$rg["id"]][$value];
                                }


                                echo "<td class=\"numero\">" . number_format($SubTotal, 0) . "</td>";
                                echo "<td class=\"numero\">" . number_format(($SubTotal * $rg["costo"]), 2) . "</td>";
                                echo "</tr>";

                                $Gtotal += $SubTotal;
                                $Almacen += $rg["existencia"];
                                $Total += ($SubTotal * $rg["costo"]);
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td style="text-align: right"></td>
                                <td style="text-align: right"></td>
                                <td style="text-align: right"></td>
                                <td style="text-align: right"></td>
                                <td style="text-align: right"><?= number_format($Almacen, 0) ?></td>
                                <?php
                                foreach ($arrayP as $rg) {
                                    echo "<td style='text-align: right'>" . number_format($rg, 0) . "</td>";
                                }
                                ?>
                                <td style="text-align: right"><?= number_format($Gtotal, 0) ?></td>
                                <td style="text-align: right"><?= number_format($Total, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>

                    <?php
                } else {
                    ?>
                    <table aria-hidden="true">
                        <thead>
                            <tr>
                                <td>Producto </td>
                                <td>Descripcion</td>
                                <td>Inv.inicial </td>
                                <td>Entradas</td>
                                <td>Ventas</td>
                                <td>Existencia</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $Almacen = $Compras = $Ventas = $Existencia = 0;
                            foreach ($registros as $rg) {

                                echo "<tr>";

                                echo "<td>" . $rg["clave"] . "</td>";
                                echo "<td>" . $rg["descripcion"] . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg["inicio"], 0) . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg["compras"], 0) . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg["ventas"], 0) . "</td>";
                                echo "<td class=\"numero\">" . number_format($rg["exi"], 0) . "</td>";
                                echo "</tr>";

                                $Almacen += $rg["inicio"];
                                $Compras += $rg["compras"];
                                $Ventas += $rg["ventas"];
                                $Existencia += $rg["exi"];
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td></td>
                                <td></td>
                                <td><?= number_format($Almacen, 0) ?></td>
                                <td><?= number_format($Compras, 0) ?></td>
                                <td><?= number_format($Ventas, 0) ?></td>
                                <td><?= number_format($Existencia, 0) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                    <?php
                }
                ?>
            </div> 
        </div>

        <div id="footer">
            <form name="formActions" method="post" action="" id="form" class="oculto">
                <div id="Controles">
                    <table aria-hidden="true">
                        <tr style="height: 40px;">
                            <td style="width: 30%;">
                                <table aria-hidden="true">
                                    <tr>
                                        <td>F.inicial:</td>
                                        <td><input type="text" id="FechaI" name="FechaI"></td>
                                        <td class="calendario"><i id="cFechaI" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
                                    </tr>
                                    <tr>
                                        <td>F.final:</td>
                                        <td><input type="text" id="FechaF" name="FechaF"></td>
                                        <td class="calendario"><i id="cFechaF" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <table style="width: 100%" aria-hidden="true">
                                    <tr>
                                        <td>&nbsp;Vista:</td>
                                        <td style="text-align: left;padding-left: 5px;">
                                            <select id="Detallado" name="Detallado">
                                                <option value="Si">Estado de cuenta</option>
                                                <option value="No">Inventario</option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <span><input type="submit" name="Boton" value="Enviar"></span>
                                <?php
                                if ($usuarioSesion->getTeam() !== "Operador") {
                                    ?>
                                    <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>                                
                                            <?php
                                        }
                                        ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
            <?php topePagina(); ?>
        </div>
    </body>
</html>

