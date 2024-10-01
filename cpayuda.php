<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("data/SysFilesDAO.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$usuarioSesion = getSessionUsuario();

$ciaDAO = new CiaDAO();
$sysFilesDAO = new SysFilesDAO();
$sysFilesVO = $sysFilesDAO->retrieve("fc_img");

$ciaVO = $ciaDAO->retrieve(1);
$logo = $sysFilesVO->getFile();

if ($request->hasAttribute("busca")) {
    utils\HTTPUtils::setSessionValue("busca", $request->getAttribute("busca"));
}
$cId = utils\HTTPUtils::getSessionValue("busca");
$select2 = "
            SELECT GROUP_CONCAT(DISTINCT me.id ORDER BY me.id ASC) capturas
            FROM cargas 
            LEFT JOIN com ON cargas.clave_producto = com.clave
            LEFT JOIN rm ON fin_venta BETWEEN cargas.fecha_inicio AND cargas.fecha_fin AND rm.producto = com.clavei
            LEFT JOIN me ON me.carga = cargas.id
            WHERE TRUE AND cargas.id = $cId
            GROUP BY cargas.id;";

$row2 = utils\IConnection::execSql($select2);

$select = "
            SELECT cargas.fecha_inicio, cargas.fecha_fin, cargas.producto,cargas.t_inicial,
            cargas.t_final,cargas.vol_inicial,cargas.vol_final,cargas.aumento,cargas.tcAumento,
            COUNT(rm.id) ventas, ROUND(IFNULL(SUM(rm.volumenp),0),2) volumen_despachadoo 
            FROM cargas 
            LEFT JOIN com ON cargas.clave_producto = com.clave
            LEFT JOIN rm ON fin_venta BETWEEN cargas.fecha_inicio AND cargas.fecha_fin AND rm.producto = com.clavei
            WHERE TRUE AND cargas.id = $cId
            GROUP BY cargas.id;";

$row = utils\IConnection::execSql($select);

$Titulo = "Transacciones al momento de la carga";
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports_print.php'; ?> 
        <title><?= $Gcia ?></title>
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

    <body class="A4-Ticket">

        <div class="sheet padding-10mm">
            <form name="form1" method="get" action="" class="noPrint">
                <div style="text-align: center;position: relative;">
                    <input type="submit" name="Boton" value="Imprimir" onclick="print()">
                </div>
            </form>
            <div id="container">
                <div>
                    <table style="text-align: center" class="text" aria-hidden="true">
                        <tr><td align="center"><img src="data:image/jpeg;base64,<?= base64_encode($logo) ?>" style="width: 200px; height: 90px;" alt=""></td></tr>
                        <tr><td align="center" class="TextosTitulos"><strong><?= $ciaVO->getCia() ?></strong></td></tr>          
                        <tr><td align="center">RFC: <?= $ciaVO->getRfc() ?></td></tr>                             
                        <tr><td align="center">Sucursal: <?= $ciaVO->getEstacion() ?></td></tr>
                        <tr><td align="center">Permiso: <?= $ciaVO->getPermisocre() ?></td></tr>
                    </table>
                </div>
                <?php //nuevoEncabezadoMini($Titulo)  ?>

                <div style="min-height: 150px;padding-top: 15px;">

                    <table aria-hidden="true">
                        <tr><td style="text-align: right;">No. de Carga: </td><td><strong> <?= $cId ?></strong></td></tr>
                        <tr><td style="text-align: right;">Producto: </td><td><strong> <?= $row["producto"] ?></strong></td></tr>
                        <tr><td style="text-align: right;">No. de Captura: </td><td><strong> <?= $row2["capturas"] ?></strong></td></tr>
                        <tr><td style="text-align: right;">Inicio de Carga: </td><td><strong> <?= $row["fecha_inicio"] ?></td></tr>
                        <tr><td style="text-align: right;">Fin de Carga: </td><td><strong> <?= $row["fecha_fin"] ?></td></tr>
                        <tr><td style="text-align: right;">Temperatura incial: </td><td><strong> <?= $row["t_inicial"] ?></td></tr>
                        <tr><td style="text-align: right;">Temperatura final: </td><td><strong> <?= $row["t_final"] ?></td></tr>
                        <tr><td style="text-align: right;">Volumen incial: </td><td><strong> <?= $row["vol_inicial"] ?></td></tr>
                        <tr><td style="text-align: right;">Volumen final: </td><td><strong> <?= $row["vol_final"] ?></td></tr>
                        <tr><td style="text-align: right;">Aumento Bruto: </td><td><strong> <?= $row["aumento"] ?></td></tr>
                        <tr><td style="text-align: right;">Aumento Neto: </td><td><strong> <?= $row["tcAumento"] ?></td></tr>
                        <tr><td style="text-align: right;">No. de Despachos: </td><td><strong> <?= $row["ventas"] ?></strong></td></tr>
                        <tr><td style="text-align: right;">Litros: </td><td><strong> <?= $row["volumen_despachadoo"] ?></strong></td></tr>
                    </table>                       
                </div>
            </div>
            <div id="footer">
                <span>Fecha de impresion: <?= date("Y-m-d H:i:s") ?></span>
            </div>
        </div>
    </body>
</html>

