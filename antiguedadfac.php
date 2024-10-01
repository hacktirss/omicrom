<?php
#Librerias
session_start();

include_once ("auth.php");
include_once ("authconfig.php");
include_once ("check.php");

use com\softcoatl\utils as utils;

require "./services/ReportesClientesService.php";
$Titulo = "Antiguedad de saldos al $Fecha";

if ($Detallado === "Si") {
    $selectAntiguedad .= "AND sub.importe !=0 ";
}
else{
    $selectAntiguedad .= "";
}

$selectAntiguedad .= "ORDER BY cli.tipodepago,cli.id";

$registros = utils\IConnection::getRowsFromQuery($selectAntiguedad);
error_log($selectAntiguedad);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports.php'; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                var cliente = "<?= html_entity_decode($SCliente) ?>";
                $("#autocomplete").val(cliente.replace("Array", ""))
                        .attr("placeholder", "* Favor de buscar al cliente *")
                        .click(function () {
                            this.select();
                        }).focus()
                        .activeComboBox(
                                $("[name=\"form1\"]"),
                                "SELECT id as data, CONCAT(id, \' | \', mid(nombre,1,50)) value FROM cli WHERE id>=10",
                                "nombre");
                $("#Fecha").val("<?= $Fecha ?>").attr("size", "10");
                $("#cFecha").css("cursor", "hand").click(function () {
                    displayCalendar($("#Fecha")[0], "yyyy-mm-dd", $(this)[0]);
                });
                $("#Detallado").val("<?= $Detallado ?>");
            });
        </script>

    </head>

    <body>
        <div id="container">

            <?php nuevoEncabezado($Titulo); ?>

            <div id="Reportes">
                <table aria-hidden="true">
                    <thead>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td colspan="4">Días</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Cuenta</td>
                            <td>Cliente</td>
                            <td>T. Pago</td>
                            <td>Por facturar</td>
                            <td>1 - 7</td>
                            <td>8 - 14</td>
                            <td>15 - 21</td>
                            <td>22 ó más</td>
                            <td>Total</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $Tipo = "";
                        foreach ($registros as $rg) {

                            if (!empty($Tipo) && $Tipo != $rg["tipodepago"]) {
                                ?>
                                <tr class="subtotal">
                                    <td colspan="8">Subtotal</td>
                                    <td class="moneda"><?= number_format($Total, 2) ?></td>
                                </tr>
                                <?php
                                $Total = 0;
                            }
                            ?>
                            <tr>
                                <td><?= $rg["cliente"] ?></td>
                                <td class="overflow"><?= $rg["nombre"] ?></td>
                                <td><?= $rg["tipodepago"] ?></td>
                                <td class="numero"><a href="antiguedadfacd.php?criteria=ini&ClienteS=<?= $rg["cliente"] ?>"><?= number_format($rg["activo"], 2) ?></a></td>
                                <td class="numero"><?= number_format($rg["a"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["b"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["c"], 2) ?></td>
                                <td class="numero"><?= number_format($rg["d"], 2) ?></td>                                
                                <td class="numero"><?= number_format($rg["total"], 2) ?></td>
                            </tr>
                            <?php
                            $Tipo = $rg["tipodepago"];
                            $Total += $rg["total"];
                            $activo += $rg["activo"];
                            $aImp += $rg["a"];
                            $bImp += $rg["b"];
                            $cImp += $rg["c"];
                            $dImp += $rg["d"];
                            $impTotal += $rg["total"];
                            $nRng++;
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan='8'>Subtotal</td>
                            <td class="moneda"><?= number_format($Total, 2) ?></td>
                        </tr>
                        <tr>
                            <td colspan="3">Gran total</td>
                            <td class="numero"><?= number_format($activo, 2) ?></td>
                            <td class="moneda"><?= number_format($aImp, 2) ?></td>
                            <td class="moneda"><?= number_format($bImp, 2) ?></td>
                            <td class="moneda"><?= number_format($cImp, 2) ?></td>
                            <td class="moneda"><?= number_format($dImp, 2) ?></td>
                            <td class="moneda"><?= number_format($impTotal, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div id="footer">
            <form name="formActions" method="post" action="" id="form" class="oculto">
                <div id="Controles">
                    <table aria-hidden="true">
                        <tr style="height: 40px;">
                            <td style="width: 30%;">
                                <table aria-hidden="true">
                                    <tr class="texto_tablas">
                                        <td>Fecha:</td>
                                        <td><input type="text" id="Fecha" name="Fecha"></td>
                                        <td class="calendario"><i id="cFecha" class="fa fa-2x fa-calendar" aria-hidden="true"></i></td>
                                    </tr>                                
                                </table>
                            </td>
                            <td>excluir clientes con saldo en ceros: 
                                    <select name="Detallado" id="Detallado">
                                        <option value="No">No</option>
                                        <option value="Si">Si</option>
                                    </select>
                                </td>
                            <td>
                                <span><input type="submit" name="Boton" value="Enviar"></span>
                                <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
            <?php topePagina(); ?>
        </div>
    </body>
</html>