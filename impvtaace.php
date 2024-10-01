<?php
session_start();

include_once ("check_report.php");
include_once ("libnvo/lib.php");
include_once("importeletras.php");
include_once ("data/SysFilesDAO.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$mysqli = iconnect();

$busca = $request->getAttribute("busca");

$ciaDAO = new CiaDAO();
$sysFilesDAO = new SysFilesDAO();
$sysFilesVO = $sysFilesDAO->retrieve("fc_img");

$ciaVO = $ciaDAO->retrieve(1);
$logo = $sysFilesVO->getFile();

$Fecha = date('Y-m-d H:i');

$sql = "SELECT vt.corte,vt.posicion,vt.fecha,vt.clave,vt.descripcion,vt.cantidad,vt.unitario,"
        . "vt.total,vt.cliente,cli.alias, cli.tipodepago "
        . "FROM vtaditivos as vt LEFT JOIN cli ON vt.cliente=cli.id "
        . "WHERE vt.id ='$busca'";
$VtaA = $mysqli->query($sql);
$Vt = $VtaA->fetch_array();
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
                    <tr>
                        <td align="left">Original</td>
                    </tr>
                    <tr><td align="center"><img src="data:image/jpeg;base64,<?= base64_encode($logo) ?>" style="width: 200px; height: 90px;" alt=""></td></tr>
                    <tr><td align="center" class="TextosTitulos"><strong><?= $ciaVO->getCia() ?></strong></td></tr>                
                    <tr><td align="center"><?= $ciaVO->getDireccion() . " " . $ciaVO->getNumeroext() ?></td></tr>
                    <tr><td align="center"><?= $ciaVO->getCiudad() . " " . $ciaVO->getEstado() ?> Cp. <?= $ciaVO->getCodigo() ?></td></tr>
                    <tr><td align="center">Telefono: <?= $ciaVO->getTelefono() ?></td></tr>
                    <tr><td align="center">RFC: <?= $ciaVO->getRfc() ?></td></tr>
                    <tr><td align="center">Sucursal: <?= $ciaVO->getEstacion() ?></td></tr>
                    <tr><td align="center">Permiso: <?= $ciaVO->getPermisocre() ?></td></tr>
                    <tr><td align="center">No.estacion: <strong><?= $ciaVO->getNumestacion() ?></strong></td></tr>                                

                    <tr><td align="center"><br><?= "Folio: <strong>$busca</strong>" ?> </td></tr>

                    <tr><td align="center"><strong>Fecha venta <?= $Vt["fecha"] ?></strong></td></tr>
                    <tr><td align="center">Fecha impresion <?= $Fecha ?></strong></td></tr>
                    <tr><td align="center">Posicion: <?= $Vt["posicion"] ?> Manguera: <?= $Vt["manguera"] ?></td></tr>
                    <tr><td align="center"><strong>Tipo de pago: <?= $Vt["tipodepago"] ?></td></tr>
                    <tr><td align="center"><strong><?= "Cliente: " . $Vt["cliente"] . " | " . ucfirst(strtoupper(substr($Vt["alias"], 0, 45))) ?></strong></td></tr>

                </table><br/>

                <table style="text-align: center" class="text" aria-hidden="true">
                    <tr>
                        <td width="45%"><strong>Producto</td>
                        <td align="right" width="15%"><strong>Cnt</td>
                        <td align="right" width="20%"><strong>Precio</td>
                        <td align="right" width="20%"><strong>Importe</td>
                    </tr>

                    <tr>
                        <td><strong><?= $Vt["clave"] ?></strong> <?= $Vt["descripcion"] ?></td>
                        <td align="right"><?= number_format($Vt["cantidad"], "0") ?></td>
                        <td align="right"><?= number_format($Vt["unitario"], "2") ?></td>
                        <td align="right"><?= number_format($Vt["total"], "2") ?></td>
                    </tr>

                    <tr>
                        <td> &nbsp;</td>
                        <td> &nbsp;</td>
                        <td align="right"> Subtotal</td>
                        <td align="right"><?= number_format($Vt["cantidad"] * $Vt["unitario"], "2") ?></td>
                    </tr>
                    <tr>
                        <td> &nbsp;</td>
                        <td> &nbsp;</td>
                        <td align="right"> Iva</td>
                        <td align="right"><?= number_format($Iva, "2") ?></td>
                    </tr>
                    <tr>
                        <td> &nbsp;</td>
                        <td> &nbsp;</td>
                        <td align="right"> Total</td>
                        <td align="right"><?= number_format($Vt["total"], "2") ?></td>
                    </tr>

                </table>

                <br/><div align="center"> <?= impletras($Vt["total"], "pesos") ?></div>
            </div>
        </div>
    </body>
</html>
