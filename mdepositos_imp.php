<?php
session_start();

include_once ("check_report.php");
include_once ("libnvo/lib.php");
include_once ("importeletras.php");
include_once ("phpqrcode/qrlib.php");
include_once ("data/SysFilesDAO.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$mysqli = iconnect();

$busca = $request->getAttribute("busca");

$ciaDAO = new CiaDAO();
$ciaVO = $ciaDAO->retrieve(1);

$Fecha = date('Y-m-d H:i');

$Sql1 = "SELECT corte,despachador FROM ctdep WHERE id = $busca;";
$rs1 = utils\IConnection::execSql($Sql1);

if ($request->getAttribute("Op") == 1) {
    $sql = "SELECT LPAD(ctdep.id, 8, 0) folio,ven.nombre,ctdep.fecha,ctdep.corte,ct.turno,SUM(ctdep.total) total
            FROM  ct,ctdep,ven
            WHERE  ct.id = ctdep.corte AND ctdep.despachador = ven.id AND ctdep.corte = " . $rs1["corte"] . " AND ctdep.despachador=" . $rs1["despachador"] . ";";
} else {
    $sql = "SELECT LPAD(ctdep.id,8,0) folio,ven.nombre,ctdep.fecha, ctdep.corte, ct.turno, ctdep.total
        FROM ct, ctdep, ven
        WHERE ct.id = ctdep.corte AND ctdep.despachador = ven.id AND ctdep.id = $busca";
}
$Vt = utils\IConnection::getRowsFromQuery($sql);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports_print.php'; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#busca").val("<?= $busca ?>");
            });

            window.onbeforeprint = function (e) {
                e.preventDefault();
                console.log('This will be called before the user prints.');
            };
            window.onafterprint = function () {
                window.location.reload();
            };
        </script>
        <style>
            @page {
                size: A4-Ticket;
            }
            @media print {
                .noPrint {
                    display:none;
                }
            }
        </style>
    </head>

    <!-- Set "A5", "A4" or "A3" for class name -->
    <!-- Set also "landscape" if you need -->
    <body class="A4-Ticket">

        <!-- Each sheet element should have the class "sheet" -->
        <!-- "padding-**mm" is optional: you can set 10, 15, 20 or 25 -->

        <div class="sheet padding-10mm">
            <form name="form1" method="get" action="" class="noPrint">
                <div style="text-align: center;position: relative;">
                    <input type="submit" name="Boton" class="nombre_cliente" value="Imprimir" onclick="print()">
                    <input type="hidden" name="busca" id="busca">
                </div>
            </form>

            <div align="center" class="text" style="align-items: flex-start">
                <table style="text-align: center" class="text" aria-hidden="true">

                    <tr><td align="center" class="TextosTitulos"><strong><?= $ciaVO->getCia() ?></strong></td></tr>                
                    <tr><td align="center"><?= $ciaVO->getDireccion() . " " . $ciaVO->getNumeroext() ?></td></tr>
                    <tr><td align="center"><?= $ciaVO->getCiudad() . " " . $ciaVO->getEstado() ?> Cp. <?= $ciaVO->getCodigo() ?></td></tr>
                    <tr><td align="center">Sucursal: <?= $ciaVO->getEstacion() ?></td></tr>          

                    <tr><td align="left"><br/>Folio: <strong><?= $Vt[0][folio] ?></strong></td></tr>
                    <tr><td align="left">Fecha impresion: <strong><?= $Fecha ?></strong></td></tr>
                    <tr><td align="left"><?= $request->getAttribute("Op") == 1 ? "General" : "Por deposito" ?></td></tr>
                    <tr><td align="left"><br/>Corte: <strong><?= $Vt[0][corte] ?></strong></td></tr>
                    <tr><td align="left">Turno: <strong><?= $Vt[0][turno] ?></strong></td></tr>
                    <tr><td align="left">Despachador: <strong><?= $Vt[0][nombre] ?></strong></td></tr>
                    <tr><td align="left">Fecha del deposito: <strong>
                                <?php
                                if ($request->getAttribute("Op") == 1) {
                                    $palabras = explode(" ", $Vt[0][fecha]);
                                    echo $palabras[0];
                                } else {
                                    echo $Vt[0][fecha];
                                }
                                ?>
                            </strong></td></tr>
                    <tr><td align="left">Monto depositado: $<strong><?= number_format($Vt[0][total], 2, ".", ",") ?></strong></td></tr>

                    <tr><td><br/><br/><hr></td></tr>
                    <tr><td align="center">Firma</td></tr>
                </table>
            </div>
        </div>
    </body>
</html>