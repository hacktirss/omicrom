<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("comboBoxes.php");

use com\softcoatl\utils as utils;

$session = new OmicromSession("etd.idnvo", "etd.idnvo");

$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$self = utils\HTTPUtils::self();

require './services/ComprasdService.php';

$Titulo = "Detalle de compra";
$Id = 51;

$paginador = new Paginador($Id,
        "etd.idnvo,ROUND((etd.costo*(1-etd.descuento)),3) costod,ROUND(etd.descuento,4) dcto,
        ROUND(etd.cantidad * etd.costo, 2) importesin, 
        ROUND(etd.cantidad * (etd.costo * (1 - etd.descuento) ), 2) importe, 
        ROUND(etd.cantidad * (etd.costo * (1 - etd.descuento) ) * (1 - etd.descuento)*(1 - etd.adicional), 2) importe_real,
        ROUND(etd.cantidad * (etd.costo * (1 - etd.descuento) ) * ( 1 - ( 1 - etd.descuento ) * ( 1 - etd.adicional ) ) , 2 ) descuento",
        "LEFT JOIN inv ON etd.producto = inv.id",
        "",
        "etd.id = '$cVarVal' AND etd.cantidad > 0",
        $session->getSessionAttribute("sortField"),
        $session->getSessionAttribute("criteriaField"),
        utils\Utils::split($session->getSessionAttribute("criteria"), "|"),
        strtoupper($session->getSessionAttribute("sortType")),
        $session->getSessionAttribute("page"),
        "REGEXP",
        "compras.php?criteria=ini");

$comprasVO = new ComprasVO();
if (is_numeric($cVarVal)) {
    $comprasVO = $comprasDAO->retrieve($cVarVal);
}


$selectSumEtd = "SELECT 
                ROUND(SUM(etd.cantidad * etd.costo), 2) importesin, 
                ROUND(SUM(etd.cantidad * (etd.costo * (1 - etd.descuento) * (1 - etd.adicional)) ), 2) importe, 
                ROUND(SUM(etd.cantidad * etd.costo), 2) importe_real,
                ROUND(SUM(etd.cantidad * etd.costo * ( 1 - ( 1 - etd.descuento ) * ( 1 - etd.adicional ) )) , 4 ) descuento 
                FROM et,etd WHERE et.id = etd.id AND et.id = '$cVarVal'";

$number = $mysqli->query($selectSumEtd)->fetch_array();
$iva = ($number["importe_real"] - $number["descuento"]) * 0.16;

$selectValida = "SELECT 
        SUM(importe) importe, 
        SUM(importe_real) importe_real,
        SUM(descuento) descuento,
        (SUM(importe_real) + SUM(descuento)) importeG
        FROM (
            SELECT etd.producto,
            ROUND(SUM(etd.cantidad * (etd.costo * (1 - etd.descuento) * (1 - etd.adicional))), 2) importe, 
            ROUND(SUM(etd.cantidad * etd.costo), 2) importe_real,
            ROUND(SUM(etd.cantidad * etd.costo * ( 1 - ( 1 - etd.descuento ) * ( 1 - etd.adicional ) )) , 2 ) descuento 
            FROM etd WHERE etd.id ='$cVarVal'
        ) a;";

$valida = $mysqli->query($selectValida)->fetch_array();
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#Cantidad").focus();
            });

            function reload() {
                window.location = "comprasd.php";
            }
        </script>
        <?php $paginador->script(); ?>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <div style="width: 98%;margin-left: auto;margin-right: auto;border: 2px solid gray;margin-bottom: 10px;padding: 3px 1px;">
            <table style="width: 98%;margin-left: auto;margin-right: auto;" class="texto_tablas" aria-hidden="true">
                <tr style="background-color: #E1E1E1;height: 20px;">
                    <td> &nbsp; <strong>No.entrada: </strong><?= $cVarVal ?> </td>
                    <td> &nbsp; <strong>Fecha:</strong> <?= $comprasVO->getFecha() ?></td>
                    <td> &nbsp; <strong>Cantidad: </strong> <?= $comprasVO->getCantidad() . " pzs" ?></td>
                    <td> &nbsp; <strong>Importe: </strong> <?= number_format($comprasVO->getImportesin() + $comprasVO->getIva(), 2) ?> </td>
                </tr>
                <tr style="background-color: #E1E1E1;height: 20px;">
                    <td colspan="2"> &nbsp; <strong>Proveedor: </strong><?= $comprasVO->getProveedor() . " | " . $comprasVO->getAlias() ?>&nbsp; </td>
                    <td>&nbsp; <strong>Concepto:</strong> <?= $comprasVO->getConcepto() ?></td>
                    <td> &nbsp; <strong>Docto: </strong> <?= $comprasVO->getDocumento() ?></td>
                </tr>
            </table>
        </div>

        <div id="TablaDatos">
            <table class="paginador" aria-hidden="true">
                <?php
                echo $paginador->headers(array(), array("Borrar"));
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr>

                        <td><?= $row["producto"] ?></td>
                        <td><?= $row["descripcion"] ?></td>
                        <td style="text-align: right;"><?= $row["cantidad"] ?></td>
                        <td style="text-align: right;"><?= $row["costo"] ?></td>
                        <td style="text-align: right;"><?= $row["dcto"] ?>%</td>
                        <td style="text-align: right;"><?= $row["costod"] ?></td>
                        <td style="text-align: right;"><?= $row["importe"] ?></td>

                        <td style="text-align: center;">
                            <?php if ($comprasVO->getStatus() === StatusCompra::ABIERTO) { ?>
                                <a href=javascript:borrarRegistro("<?= $self ?>",<?= $row["idnvo"] ?>,"cId");><i class="icon fa fa-lg fa-trash" aria-hidden="true"></i></a>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>

        <?php
        $nLink = array();
        $flag = false;
        if ((abs($comprasVO->getImportesin() - $valida["importe"]) < 3)) {
            if ((abs($comprasVO->getIva() - $iva) < 3 || $comprasVO->getIva() == 0)) {
                if ($comprasVO->getStatus() === StatusCompra::ABIERTO) {
                    $nLink["<span class='textosCualli'><i class='icon fa fa-flag' aria-hidden=\"true\"></i> Documento cuadrado, da click aqui para cerrarla <i class='icon fa fa-flag' aria-hidden=\"true\"></i><span>"] = "comprasd.php?op=cr";
                    $flag = true;
                }
            }
        }
        echo $paginador->footer(false, $nLink, false, false);

        $iva = $comprasVO->getIva()==0 ? $comprasVO->getIva() : $iva;
        ?>
        <table style="width: 100%;" aria-hidden="true">
            <tr class='texto_tablas'>
                <td>Importe: <strong><?= number_format($number["importe_real"], 2) ?></strong></td>
                <td>Descuento: <strong><?= number_format($number["descuento"], 4) ?></strong></td>
                <td>Importe c/desc: <strong><?= number_format($number["importe_real"] - $number["descuento"], 2) ?></strong></td>
                <td>Iva: <strong><?= number_format($iva, 2) ?></strong></td>
                <td>Total: <strong>$ <?= number_format($number["importe_real"] - $number["descuento"] + $iva, 2) ?></strong></td>
            </tr>
        </table>

        <form name='form1' method='get' action=''>

            <?php
            if ($comprasVO->getStatus() === StatusCompra::ABIERTO && !$flag) {
                echo "<table align='center' width='100%' border='0' cellpadding='1' cellspacing='1'>";

                echo "<tr bgcolor='#cacaca' class='nombre_cliente' height='25'>";
                if ($request->hasAttribute("Producto")) {
                    $Producto = $request->getAttribute("Producto");
                    echo "<td align='left'>";
                    $Inv = $mysqli->query("SELECT id,descripcion,umedida,ROUND(costo,2) costo FROM inv WHERE id='$Producto'")->fetch_array();
                    echo "<b onclick='reload();' title='Click aqui para cancelar'>" . ucwords(strtolower($Inv["id"] . " | " . $Inv["descripcion"])) . " &nbsp; <i class='icon fa fa-undo' aria-hidden=\"true\"></i></strong>";
                    echo "</td>";
                    echo "<td>";
                    cInputDat("&nbsp; Cnt:", "Number", "5", "Cantidad", "-", "", "5", true, false, " min='1' max='6999'");
                    echo "</td>";
                    echo "<td>&nbsp; Costo por: ";
                    echo "| Pieza&nbsp;<input type='radio' name='Tipo' value='1' checked>";
                    echo "Paquete&nbsp;<input type='radio' name='Tipo' value='2'> |";
                    cInputDat("", "Text", "5", "Costo", "-", $Inv["costo"], "10", true, false);
                    echo "</td>";
                    echo "<td>";
                    cInputDat("&nbsp; Desc:", "text", "4", "Desc", "left", "0", "4", true, false, "");
                    echo "%</td>";
                    echo "<td width='10%'>";
                    echo "<input type='hidden' name='Producto' value='$Producto'>";
                    echo " &nbsp &nbsp <input class='nombre_cliente' type='submit' name='Boton' value='Agregar'>";
                    echo "</td>";
                } else {
                    echo "<td align='left'> &nbsp; ";
                    ComboboxInventario::generate("Producto", "'" . $comprasVO->getProveedorde() . "'", "350px", "onChange=submit();");
                    echo "</td><td width='17%'>&nbsp;</td><td width='20%'>&nbsp;</td><td width='10%'>&nbsp;</td>";
                }
                echo "</tr>";

                echo "</table>";

                echo "<table width='100%' align='center' border=0 cellspacing='1' cellpadding='1'>";

                echo "<tr class='texto_tablas' height='30px'><td width='20%'>Descuento global:</td>";
                echo "<td>%<input type='text' name='Descuento' class='texto_tablas' size='7' placeholder='Porcentaje'> <strong>&oacute;</strong> <input type='text' name='DescuentoI' class='texto_tablas' size='10' placeholder='Importe'></td>";
                echo "<td><input type='submit' name='Boton' value='Aplicar' class='nombre_cliente'> *Se aplicará a todos los productos ingresados.</td></tr>";

                echo "<tr class='texto_tablas' height='30px'><td>Descuento adicional:</td>";
                echo "<td>%<input type='text' name='Adicional' class='texto_tablas' size='7' placeholder='Porcentaje'>%</td>";
                echo "<td><input type='submit' name='Boton' value='Adicionar' class='nombre_cliente'> *Se aplicará al importe generado.</td></tr>";
                echo "</table>";
            }
            ?>

        </form>

        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

</html>
