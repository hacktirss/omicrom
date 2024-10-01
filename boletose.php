<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$mysqli = iconnect();

$Titulo = "Detalle de vales";
$nameVarBusca = "busca";
if ($request->hasAttribute("cId")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("cId"));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

require './services/GeneraValesService.php';

$selectVale = "SELECT genbol.fecha as creacion,cli.nombre,boletos.codigo,boletos.secuencia,boletos.importe,boletos.ticket,boletos.ticket2,boletos.importe1,boletos.importe2,
              boletos.importecargado,boletos.vigente 
              FROM boletos,genbol 
              LEFT JOIN cli ON genbol.cliente=cli.id 
              WHERE genbol.id=boletos.id AND boletos.idnvo='$busca'";
$CpoA = $mysqli->query($selectVale);
$Cpo = $CpoA->fetch_array();
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#busca").val("<?= $busca ?>");
                $("#cId").val("<?= $busca ?>");
                $("#T1").focus();
            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="boletos.php"><div class="RegresarCss " alt="Flecha regresar">Regresar</div></a>
                </td>
                <td style="vertical-align: top;">
                    <form name="form1" id="form1" method="post" action="">

                        <table style="width: 100%;" aria-hidden="true">
                            <tr>
                                <td>
                                    <div style="width: 90%;border-collapse: collapse; border: 1px solid #066;margin-bottom: 10px;">
                                        <table class="texto_tablas" aria-hidden="true" style="width: 99%;">
                                            <tr height="21" class="texto_tablas"><td style="background: #e1e1e1;text-align: right;" class="nombre_cliente">Cliente: &nbsp;</td><td colspan="5"><?= $Cpo["nombre"] ?></td></tr>
                                            <tr height="21" class="texto_tablas"><td style="background: #e1e1e1;text-align: right;"  class="nombre_cliente">Fecha de creaci&oacute;n: &nbsp;</td><td> <?= $Cpo["creacion"] ?></td></tr>
                                        </table>
                                    </div>

                                    <div style="width: 90%;border-collapse: collapse; border: 1px solid #066;margin-bottom: 10px;">
                                        <table class="texto_tablas" aria-hidden="true" style="width: 100%;">
                                            <tr height="21" class="texto_tablas">
                                                <td  style="background: #e1e1e1;text-align: right;" class="nombre_cliente">Codigo: &nbsp;</td><td> <?= $Cpo["codigo"] ?></td>
                                            </tr>
                                            <tr height="21" class="texto_tablas">
                                                <td style="background: #e1e1e1;text-align: right;" class="nombre_cliente">Secuencia: &nbsp;</td><td> <?= $Cpo["secuencia"] ?></td>
                                            </tr>
                                            <tr height="21" class="texto_tablas">
                                            <div style="visibility: hidden"><input type="number" name="impBoleto" id="impBoleto" value="<?= $Cpo["importe"] ?>" ></div>
                                            <td style="background: #e1e1e1;text-align: right;" class="nombre_cliente" >Ticket 1: &nbsp;</td>
                                            <td style="width: 100px;"><input type="number" name="T1" id="T1" value="<?= $Cpo["ticket"] ?>" class="texto_tablas" min="1" max="10000000" required="required" ></td>
                                            <td style="background: #e1e1e1;text-align: right;" class="nombre_cliente" >Ticket 2: &nbsp;</td><td width="100"><input type="number" name="T2" id="T2" value="<?= $Cpo["ticket2"] ?>" class="texto_tablas"  max="10000000" >
                                                </tr>
                                            <tr height="21" class="texto_tablas">
                                                <td  style="background: #e1e1e1;text-align: right;" class="nombre_cliente">Importe del Boleto: &nbsp;</td><td><?= $Cpo["importe"] ?></td>
                                            </tr>
                                            <tr height="21" class="texto_tablas"><td  style="background: #e1e1e1;text-align: right;" class="nombre_cliente">Importe cargado: &nbsp;</td><td><?= $Cpo["importecargado"] ?></td>
                                                <td  style="background: #e1e1e1;text-align: right;" class="nombre_cliente">Saldo : &nbsp;</td><td><?= number_format($Cpo["importe"] - $Cpo["importecargado"], 2) ?></td>
                                            </tr>
                                        </table>
                                    </div>

                                    <?php
                                    if ($Cpo["ticket"] <> 0) {

                                        $selectRm = "SELECT rm.id,rm.fin_venta,rm.posicion,ROUND(rm.pesos,2) pesos,ROUND(rm.volumen,3) volumen,rm.corte,com.descripcion 
                                                     FROM rm LEFT JOIN com ON com.clavei = rm.producto 
                                                     WHERE rm.id = '" . $Cpo["ticket"] . "'";
                                        if ($result = $mysqli->query($selectRm)) {
                                            $rg = $result->fetch_array();
                                            ?>
                                            <div style="width: 90%;border-collapse: collapse; border: 1px solid #066;margin-bottom: 10px;">
                                                <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
                                                    <tr><td colspan="4" class="nombre_cliente" align="center">Transacci&oacute;n 1</td></tr>
                                                    <tr height="21" class="texto_tablas"><td style="background: #e1e1e1;text-align: right;" class="nombre_cliente">No. Carga: &nbsp;</td><td><?= $Cpo["ticket"] ?></td></tr>
                                                    <tr height="21" class="texto_tablas"><td style="background: #e1e1e1;text-align: right;" class="nombre_cliente" >Fecha de carga: &nbsp;</td><td><?= $rg["fin_venta"] ?></td></tr>
                                                    <tr height="21" class="texto_tablas"><td  style="background: #e1e1e1;text-align: right;" class="nombre_cliente" >Posicion: &nbsp;</td><td><?= $rg["posicion"] ?></td>
                                                        <td  style="background: #e1e1e1;text-align: right;" class="nombre_cliente">Producto: &nbsp;</td><td><?= $rg["descripcion"] ?></td></tr>
                                                    <tr height="21" class="texto_tablas"><td  style="background: #e1e1e1;text-align: right;" class="nombre_cliente">Importe: &nbsp;</td><td><?= $rg["pesos"] ?></td>
                                                        <td style="background: #e1e1e1;text-align: right;" class="nombre_cliente">Litros: &nbsp;</td><td><?= $rg["volumen"] ?></td></tr>
                                                    <tr height="21" class="texto_tablas"><td style="background: #e1e1e1;text-align: right;" class="nombre_cliente">Corte: &nbsp;</td><td><?= $rg["corte"] ?></td>
                                                        <td  style="background: #e1e1e1;text-align: right;" class="nombre_cliente">Utilizado:</td><td><?= $Cpo["importe1"] ?></td></tr>
                                                </table>
                                            </div>

                                            <?php
                                        }
                                    }
                                    if ($Cpo["ticket2"] <> 0) {
                                        $selectRm = "SELECT rm.id,rm.fin_venta,rm.posicion,ROUND(rm.pesos,2) pesos,ROUND(rm.volumen,3) volumen,rm.corte,com.descripcion 
                                                     FROM rm LEFT JOIN com ON com.clavei = rm.producto 
                                                     WHERE rm.id = '$Cpo[ticket2]'";
                                        //error_log($selectRm);
                                        if (($result = $mysqli->query($selectRm))) {
                                            $rg = $result->fetch_array();
                                            error_log(print_r($rg, TRUE));
                                            ?>
                                            <div style="width: 90%;border-collapse: collapse; border: 1px solid #066;margin-bottom: 10px;">
                                                <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
                                                    <tr><td colspan="4" class="nombre_cliente" align="center">Transacci&oacute;n 1</td></tr>
                                                    <tr height="21" class="texto_tablas"><td class="nombre_cliente" style="background: #e1e1e1;text-align: right;">No. Carga: &nbsp;</td><td><?= $Cpo["ticket2"] ?></td></tr>
                                                    <tr height="21" class="texto_tablas"><td class="nombre_cliente" style="background: #e1e1e1;text-align: right;">Fecha de carga: &nbsp;</td><td><?= $rg["fin_venta"] ?></td></tr>
                                                    <tr height="21" class="texto_tablas"><td class="nombre_cliente" style="background: #e1e1e1;text-align: right;">Posicion: &nbsp;</td><td><?= $rg["posicion"] ?></td>
                                                        <td class="nombre_cliente" style="background: #e1e1e1;text-align: right;">Producto: &nbsp;</td><td><?= $rg["descripcion"] ?></td></tr>
                                                    <tr height="21" class="texto_tablas"><td class="nombre_cliente" style="background: #e1e1e1;text-align: right;">Importe: &nbsp;</td><td><?= $rg["pesos"] ?></td>
                                                        <td class="nombre_cliente" style="background: #e1e1e1;text-align: right;">Litros: &nbsp;</td><td><?= $rg["volumen"] ?></td></tr>
                                                    <tr height="21" class="texto_tablas"><td class="nombre_cliente" style="background: #e1e1e1;text-align: right;">Corte: &nbsp;</td><td><?= $rg["corte"] ?></td>
                                                        <td class="nombre_cliente" style="background: #e1e1e1;text-align: right;">Utilizado:</td><td><?= $Cpo["importe2"] ?></td></tr>
                                                </table>
                                            </div>
                                            <?php
                                        }
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php
                            if ($Cpo["vigente"] == "Si") {
                                echo "<tr><td colspan='2' align='center'><input type='submit' name='Boton' value='Liberar' class='nombre_cliente'></td></tr>";
                            }
                            ?>
                        </table>
                        <input type="hidden" name="busca" id="busca">
                        <input type="hidden" name="cId" id="cId">
                    </form>

                </td>
            </tr>
        </table>

        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>