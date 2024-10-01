<?php
#Librerias
session_start();
include_once("./check_report.php");
include_once("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";
$Titulo = "Reporte para gerencia del $Fecha";
$registros1 = utils\IConnection::getRowsFromQuery($selectGerencia1);
$registros = array();
foreach ($registros1 as $rg) {
    //error_log(print_r($rg, TRUE));
    $registros[$rg["turno"]][$rg["clavei"]]["importe"] = $rg["importe"];
    $registros[$rg["turno"]][$rg["clavei"]]["volumen"] = $rg["volumen"];
    $registros[$rg["turno"]][$rg["clavei"]]["importeS"] = $rg["importeS"];
    $registros[$rg["turno"]][$rg["clavei"]]["iva"] = $rg["iva"];
    $registros[$rg["turno"]][$rg["clavei"]]["ieps"] = $rg["ieps"];
    $registros[$rg["turno"]]["total"] = empty($registros[$rg["turno"]]["total"]) ? $rg["importe"] : $registros[$rg["turno"]]["total"] + $rg["importe"];
}
$registros2 = utils\IConnection::getRowsFromQuery($selectGerencia2);
$lecturas = array();
foreach ($registros2 as $rg) {
    //error_log(print_r($rg, TRUE));
    $lecturas[$rg["clavei"]]["producto"] = $rg["descripcion"];
    $lecturas[$rg["clavei"]]["inicial"] = $rg["inicial"];
    $lecturas[$rg["clavei"]][$rg["turno"]]["volumen"] = $rg["volumen"];
    $lecturas[$rg["clavei"]]["compras"] = $rg["compras"];
    $lecturas[$rg["clavei"]]["pemex"] = $rg["pemex"];
    $lecturas[$rg["clavei"]]["vol"] = empty($lecturas[$rg["clavei"]]["vol"]) ? $rg["vol"] : $lecturas[$rg["clavei"]]["vol"] + $rg["vol"];
    $lecturas[$rg["clavei"]]["volp"] = empty($lecturas[$rg["clavei"]]["volp"]) ? $rg["volp"] : $lecturas[$rg["clavei"]]["volp"] + $rg["volp"];
    $lecturas[$rg["clavei"]]["merma"] = empty($lecturas[$rg["clavei"]]["merma"]) ? $rg["merma"] : $lecturas[$rg["clavei"]]["merma"] + $rg["merma"];
}
$registros3 = utils\IConnection::getRowsFromQuery($selectGerencia3);
$registros4 = utils\IConnection::execSql($selectGerencia4);
$registros5 = utils\IConnection::execSql($selectGerencia5);
$registros6 = utils\IConnection::getRowsFromQuery($selectGerencia6);
$registros7 = utils\IConnection::getRowsFromQuery($selectGerencia7);
$registros8 = utils\IConnection::getRowsFromQuery($selectGerencia8);
$registros9 = utils\IConnection::getRowsFromQuery($selectGerencia9);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">

<head>
    <?php require "./config_omicrom_reports.php"; ?>
    <script type="text/javascript" src="https://unpkg.com/xlsx@0.15.1/dist/xlsx.full.min.js"></script>
    <title><?= $Gcia ?></title>
    <script>
        $(document).ready(function() {
            $("#Fecha").val("<?= $Fecha ?>").attr("size", "10");
            $("#cFecha").css("cursor", "hand").click(function() {
                displayCalendar($("#Fecha")[0], "yyyy-mm-dd", $(this)[0]);
            });
        });
        function ExportToExcel(type, fn, dl) {
       var elt = document.getElementById('tbl_exporttable_to_xls');
       var wb = XLSX.utils.table_to_book(elt, { sheet: "sheet1" });
       return dl ?
         XLSX.write(wb, { bookType: type, bookSST: true, type: 'base64' }):
         XLSX.writeFile(wb, fn || ('ReporteGerencia.' + (type || 'xlsx')));
    };
    </script>
</head>

<body>
    <div id="container">
        <div id="tbl_exporttable_to_xls">
        <?php nuevoEncabezado($Titulo); ?>
        <div id="Reportes">
            <table aria-hidden="true" class="display" style="width: 100%;">
                <thead>
                    <tr>
                        <td>Producto</td>
                        <?php
                        foreach ($Turnos as $key => $value) :
                            echo "<td> Turno " . $value . "</td>";
                        endforeach;
                        ?>
                        <td>Litros totales</td>
                        <td>Precio</td>
                        <td>subtotal</td>
                        <td>iva</td>
                        <td>ieps</td>
                        <td>Venta total</td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $cLt = $cTot = $csubT = $civa = $cieps = 0;
                    foreach ($Combustibles as $com) {
                        $subLt = $subTot = $subT = $iva = $ieps = 0;
                    ?>
                        <tr>
                            <td><?= $com["descripcion"] ?></td>
                            <?php
                            foreach ($Turnos as $key => $value) :
                                $subLt += $registros[$value][$com["clavei"]]["volumen"];
                                $subTot += $registros[$value][$com["clavei"]]["importe"];
                                $subT += $registros[$value][$com["clavei"]]["importeS"];
                                $iva += $registros[$value][$com["clavei"]]["iva"];
                                $ieps += $registros[$value][$com["clavei"]]["ieps"];
                            ?>
                                <td class="numero"><?= $registros[$value][$com["clavei"]]["importe"] ?></td>
                            <?php endforeach; ?>
                            <td class="numero"><?= $subLt ?></td>
                            <td class="numero"><?= number_format($subTot / $subLt, 2) ?></td>
                            <td class="numero"><?= $subT ?></td>
                            <td class="numero"><?= $iva ?></td>
                            <td class="numero"><?= $ieps ?></td>
                            <td class="numero"><?= $subTot ?></td>
                        </tr>
                    <?php
                        $cLt += $subLt;
                        $cTot += $subTot;
                        $csubT += $subT;
                        $civa += $iva;
                        $cieps += $ieps;
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td>Totales</td>
                        <?php
                        foreach ($Turnos as $key => $value) :
                            echo "<td>" . $registros[$value]["total"] . "</td>";
                        endforeach;
                        ?>
                        <td><?= number_format($cLt, 3) ?></td>
                        <td></td>
                        <td><?= number_format($csubT, 3) ?></td>
                        <td><?= number_format($civa, 3) ?></td>
                        <td><?= number_format($cieps, 3) ?></td>
                        <td><?= number_format($cTot, 2) ?></td>
                    </tr>
                </tfoot>
            </table>
            <table id="tbl_exporttable_to_xls" aria-hidden="true">
                <thead>
                    <tr>
                        <td>Almacen</td>
                        <td>I.Inic.00:00</td>
                        <?php
                        foreach ($Turnos as $key => $value) :
                            echo "<td>Lec.tno " . $value . "</td>";
                        endforeach;
                        ?>
                        <td>Compras</td>
                        <td>Uso Pemex</td>
                        <td>Vta.B</td>
                        <td>Merma</td>
                        <td>% Merma</td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($Combustibles as $com) {
                    ?>
                        <tr>
                            <td><?= $com["descripcion"] ?></td>
                            <td class="numero"><?= $lecturas[$com["clavei"]]["inicial"] ?></td>
                            <?php foreach ($Turnos as $key => $value) :
                            ?>
                                <td class="numero"><?= $lecturas[$com["clavei"]][$value]["volumen"] ?></td>
                            <?php endforeach; ?>
                            <td class="numero"><?= $lecturas[$com["clavei"]]["compras"] ?></td>
                            <td class="numero"><?= $lecturas[$com["clavei"]]["pemex"] ?></td>
                            <td class="numero"><?= $lecturas[$com["clavei"]]["vol"] ?></td>
                            <td class="numero"><?= $lecturas[$com["clavei"]]["merma"] ?></td>
                            <td class="numero"><?= number_format((($lecturas[$com["clavei"]]["vol"] - $lecturas[$com["clavei"]]["volp"]) / $lecturas[$com["clavei"]]["vol"]) * 100, 3) ?></td>
                        </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
            <table aria-hidden="true" >
                <thead>
                    <tr>
                        <td>Turno</td>
                        <td>Clientes crédito</td>
                        <td>Clientes prépago</td>
                        <td>Aditivos</td>
                        <td>Jarreos</td>
                        <td>Internos</td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $cCre = $nPre = $nLub = $nJar = $nInt = 0;
                    foreach ($registros3 as $rg) {
                    ?>
                        <tr>
                            <td><?= turnoLetra($rg["turno"]) ?></td>
                            <td class="numero"><?= $rg["credito"] ?></td>
                            <td class="numero"><?= $rg["prepago"] ?></td>
                            <td class="numero"><?= number_format($rg["lubricantes"], 2) ?></td>
                            <td class="numero"><?= $rg["jarreos"] ?></td>
                            <td class="numero"><?= $rg["internos"] ?></td>
                        </tr>
                    <?php
                        $nCre += $rg["credito"];
                        $nPre += $rg["prepago"];
                        $nLub += $rg["lubricantes"];
                        $nJar += $rg["jarreos"];
                        $nInt += $rg["internos"];
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td>Totales</td>
                        <td><?= number_format($nCre, 2) ?></td>
                        <td><?= number_format($nPre, 2) ?></td>
                        <td><?= number_format($nLub, 2) ?></td>
                        <td><?= number_format($nJar, 2) ?></td>
                        <td><?= number_format($nInt, 2) ?></td>
                    </tr>
                    <tr>
                        <td>Ieps</td>
                        <td><?= $registros4["producto1"] ?></td>
                        <td><?= $registros4["producto2"] ?></td>
                        <td><?= $registros4["producto3"] ?></td>
                        <td><?= $registros4["total"] ?></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Compras</td>
                        <td><?= $registros5["aumento"] ?></td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            <div style="width: 100%">
                <div id="Reportes" style="width: 45%;min-height: 100px;display: inline-table;position: relative">
                    <table aria-hidden="true">
                        <thead>
                            <tr>
                                <td>Depositos del día</td>
                                <td>Importe</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $dTot = 0;
                            foreach ($registros6 as $rg) {
                            ?>
                                <tr>
                                    <td><?= $rg["concepto"] ?></td>
                                    <td class="numero"><?= $rg["importe"] ?></td>
                                </tr>
                            <?php
                                $dTot += $rg["importe"];
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>Depositos totales</td>
                                <td><?= number_format($dTot, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div id="Reportes" style="width: 45%;min-height: 100px;display: inline-table;position: relative">
                    <table aria-hidden="true">
                        <thead>
                            <tr>
                                <td>Ingresos del día</td>
                                <td>Importe</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sTot = 0;
                            foreach ($registros7 as $rg) {
                            ?>
                                <tr>
                                    <td><?= $rg["concepto"] ?></td>
                                    <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                                </tr>
                            <?php
                                $sTot += $rg["importe"];
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>Saldo efectivo</td>
                                <td><?= number_format($sTot, 2) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <div id="Reportes" style="width: 70%;min-height: 150px;">
            <table aria-hidden="true">
                <thead>
                    <tr>
                        <td>Nombre del Producto</td>
                        <td>Unidades vendidas</td>
                        <td>Costo sin iva</td>
                        <td>Venta con iva</td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $aCnt = $aCosto = $aVta = 0;
                    foreach ($registros8 as $rg) {
                    ?>
                        <tr>
                            <td><?= $rg["aditivo"] ?></td>
                            <td class="numero"><?= $rg["cantidad"] ?></td>
                            <td class="numero"><?= $rg["costo"] ?></td>
                            <td class="numero"><?= $rg["total"] ?></td>
                        </tr>
                    <?php
                        $aCnt += $rg["cantidad"];
                        $aCosto += $rg["costo"];
                        $aVta += $rg["total"];
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td><?= number_format($aCnt) ?></td>
                        <td><?= number_format($aCosto, 2) ?></td>
                        <td><?= number_format($aVta, 2) ?></td>
                    </tr>
                </tfoot>
            </table>
            <table aria-hidden="true">
                <thead>
                    <tr>
                        <td>Clave</td>
                        <td>Pago con tarjeta bancaria</td>
                        <td>Importe</td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $dImp = 0;
                    foreach ($registros9 as $rg) {
                    ?>
                        <tr>
                            <td><?= $rg["clave"] ?></td>
                            <td><?= $rg["banco"] ?></td>
                            <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                        </tr>
                    <?php
                        $dImp += $rg["importe"];
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td></td>
                        <td>Total</td>
                        <td><?= number_format($dImp, 2) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
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
                                    <td>Fecha:</td>
                                    <td><input type="text" id="Fecha" name="Fecha"></td>
                                    <td class="calendario"><i id="cFecha" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
                                </tr>
                            </table>
                        </td>
                        <td>
                            <span><input type="submit" name="Boton" value="Enviar"></span>
                            <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                            <span><button onclick="ExportToExcel('xlsx')"><i class="icon fa fa-lg fa-bold fa-file-excel-o" aria-hidden="true"></i></button></span>
                        </td>
                    </tr>
                </table>
            </div>
        </form>
        <?php topePagina(); ?>
    </div>
</body>

</html>