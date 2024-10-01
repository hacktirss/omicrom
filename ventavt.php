<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("data/CtDAO.php");
include_once ("data/VariablesDAO.php");

use com\softcoatl\utils as utils;

require './services/ReportesVentasService.php';

$mysqli = iconnect();

$ctDAO = new CtDAO();
$ctVO = $ctDAO->retrieve($Corte);
$llave = VariablesDAO::getVariable("llave");

$Titulo = " Resumen de ventas corte No. " . $ctVO->getId() . "  / " . $ctVO->getFecha() . " turno: " . $ctVO->getTurno();
$selectProductoByCorteN = "
            SELECT ROUND(SUM( rm.pesosp) - IFNULL(Tn.imp,0),2) importe, ROUND(SUM( rm.volumen ),3) volumen , com.descripcion,
            COUNT( * ) despachos 
            FROM man, rm 
            LEFT JOIN com ON rm.producto = com.clavei
            LEFT JOIN 
	(SELECT sum(volumen) - SUM(volumenp) vol,SUM(importe) -  SUM(pesosp) imp,producto
                FROM rm WHERE corte = $Corte AND tipo_venta='N' GROUP BY producto) Tn
            ON Tn.producto=rm.producto
            WHERE 1 = 1 
            AND man.posicion = rm.posicion AND man.activo = 'Si'
            AND rm.corte = $Corte AND rm.tipo_venta in ('N')
            GROUP BY com.descripcion DESC";
$Precios = utils\IConnection::getRowsFromQuery($selectPreciosByCorte);

$registros = utils\IConnection::getRowsFromQuery($selectVentaByCorteCerrado);

$registrosA = utils\IConnection::getRowsFromQuery($selectAditivosByCorteCerrado);

$registrosG = utils\IConnection::getRowsFromQuery($selectGastosByCorteCerrado);

$registrosP = utils\IConnection::getRowsFromQuery($selectProductoByCorteCerrado);
$registrosN = utils\IConnection::getRowsFromQuery($selectProductoByCorteN);

$horario = "";
if ($llave) {
    $selectFechas = "
            SELECT 1 ini,  IFNULL( fecha_hora_s,  '0000-00-00 00:00:00' ) fecha 
            FROM(
                SELECT fecha_hora_s
                FROM tanques_h 
                WHERE fecha_hora_s BETWEEN '" . $ctVO->getFecha() . "' AND '" . $ctVO->getFechaf() . "' 
                ORDER BY fecha_hora_s ASC LIMIT 1
            ) inicio
            UNION
            SELECT 2 ini, IFNULL( fecha_hora_s,  '0000-00-00 00:00:00' ) fecha 
            FROM(
                SELECT fecha_hora_s
                FROM tanques_h 
                WHERE fecha_hora_s BETWEEN '" . $ctVO->getFecha() . "' AND '" . $ctVO->getFechaf() . "' 
                ORDER BY fecha_hora_s DESC LIMIT 1
            ) fin";
    //error_log($selectFechas);
    $result = $mysqli->query($selectFechas);
    while ($rg = $result->fetch_array()) {
        if ($rg["ini"] == 1) {
            $horario .= "DE " . $rg["fecha"];
        } else {
            $horario .= " A " . $rg["fecha"];
        }
    }
}
?> 

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#Detallado").val("<?= $Detallado ?>");
                $("#Corte").val("<?= $Corte ?>");
            });
        </script>
    </head>

    <body>

        <div id="container">
            <?php nuevoEncabezado($Titulo); ?>

            <table style="width: 100%;" aria-hidden="true">
                <tr>
                    <td width="30%" valign="top">
                        <div class="texto_tablas">Hora inicial: <?= $ctVO->getHora() ?></div>
                        <div class="texto_tablas">Hora final:   <?= date("H:i:s", strtotime($ctVO->getFechaf())) ?></div>
                    </td>
                    <td width="40%">
                        &nbsp;
                    </td>
                    <td width="30%" align="right">
                        <table style="width: 100%;" aria-hidden="true">
                            <?php
                            foreach ($Precios as $rg) {
                                ?>
                                <tr class="texto_tablas">
                                    <td align="right"><?= ucwords(strtolower($rg["descripcion"])) . ": " ?></td>
                                    <td align="right" style="background-color: #CACACA"><?= "$ " . $rg["precio"] ?></td>
                                </tr>
                                <?php
                            }
                            ?>
                        </table>
                    </td>
                </tr>
            </table>

            <div id="Reportes" style="min-height: 200px;">
                <table aria-hidden="true">
                    <thead>
                        <?php
                        $Vts = 0;
                        $LtsJ = 0;
                        $ImpJ = 0;
                        $Lts = 0;
                        $Imp = 0;

                        $colspan = 2;

                        if ($Detallado === "Si") {
                            $colspan = 3;
                            ?>

                            <tr class="titulo">
                                <td colspan="4"></td>
                                <td colspan="2">Vendido</td>
                                <td>Consignaciones</td>
                                <td colspan="2">Jarreos</td>
                                <td colspan="2">Importe</td>
                            </tr>
                            <tr>
                                <td>Posicion</td>
                                <td>Manguera</td>
                                <td>Producto</td>
                                <td>No.ventas</td>
                                <td>Litros</td>
                                <td>Importe</td>
                                <td>Litros</td>
                                <td>Importe</td>
                                <td>Litros</td>
                                <td>Importe</td>
                            </tr>

                            <?php
                        } else {
                            ?>

                            <tr class="titulo">
                                <td colspan="3"></td>
                                <td colspan="2">Vendido</td>
                                <td>Consignaciones</td>
                                <td colspan="2">Jarreos</td>
                                <td colspan="2">Importe</td>
                            </tr>
                            <tr>
                                <td>Posicion</td>
                                <td>Despachador</td>
                                <td>No.ventas</td>
                                <td>Litros</td>
                                <td>Importe</td>
                                <td>Litros</td>
                                <td>Importe</td>
                                <td>Litros</td>
                                <td>Importe</td>
                            </tr>

                            <?php
                        }
                        ?>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($registros as $rg) {
                            ?>
                            <tr>

                                <?php if ($Detallado === "Si") { ?>

                                    <td><?= $rg["posicion"] ?></td>
                                    <td><?= $rg["manguera"] ?></td>
                                    <td><?= $rg["combustible"] ?></td>
                                    <td class="numero"><?= $rg["ventas"] ?></td>
                                    <td class="numero"><?= number_format($rg["v_total"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["p_total"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["N_vtotal"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["importej"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["litrosj"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                                <?php } else { ?>

                                    <td><?= $rg["posicion"] ?></td>
                                    <td><?= $rg["despachador"] ?></td>
                                    <td class="numero"><?= $rg["ventas"] ?></td>
                                    <td class="numero"><?= number_format($rg["v_total"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["p_total"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["N_vtotal"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["importej"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["litrosj"], 2) ?></td>
                                    <td class="numero"><?= number_format($rg["p_total"], 2) ?></td>
                                <?php } ?>

                            </tr>
                            <?php
                            $Vts += $rg["ventas"];
                            $LtsJ += $rg["litrosj"];
                            $ImpJ += $rg["importej"];
                            $LtsN += $rg["N_vtotal"];
                            $ImpN += $rg["N_ptotal"];
                            $Lts += $rg["v_total"];
                            $Imp += str_replace(",", "", number_format($rg["p_total"], 2));
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="<?= $colspan ?>"></td>
                            <td><?= number_format($Vts, 0) ?></td>
                            <td><?= number_format($Lts, 2) ?></td>
                            <td><?= number_format($Imp, 2) ?></td>
                            <td><?= number_format($LtsN, 2) ?></td>
                            <td><?= number_format($ImpJ, 2) ?></td>
                            <td><?= number_format($LtsJ, 2) ?></td>
                            <td><?= number_format($Imp, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div id="Reportes" style="width: 60%">
                <table aria-hidden="true">
                    <thead>
                        <tr class="titulo">
                            <td colspan="5">Venta de aceites</td>
                        </tr>
                        <tr>
                            <td>Tpo.pago</td>
                            <td>Clave</td>
                            <td>Producto</td>
                            <td>Cnt</td>
                            <td>Importe</td>
                        </tr>
                    </thead>
                    <tbody>

                        <?php
                        $nSubImp = $nCnt = 0;
                        foreach ($registrosA as $rg) {
                            ?>
                            <tr>
                                <td><?= $rg["tipodepago"] ?></td>
                                <td><?= $rg["clave"] ?></td>
                                <td class="numero"><?= ucwords(strtolower($rg["descripcion"])) ?></td>
                                <td class="numero"><?= number_format($rg["cantidad"], 0) ?></td>
                                <td class="numero"><?= number_format($rg["total"], 2) ?></td>
                            </tr>
                            <?php
                            $nSubImp += $rg["total"];
                            $nCnt += $rg["cantidad"];
                            $GranT += $rg["total"];
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td></td>
                            <td>Total</td>
                            <td><?= number_format($nCnt, 0) ?></td>                               
                            <td><?= number_format($nSubImp, 2) ?></td>
                        </tr>
                    <tfoot>
                </table>

                <table aria-hidden="true">
                    <thead>
                        <tr class="titulo">
                            <td colspan="4">Gastos</td>
                        </tr>
                        <tr>
                            <td>Cuenta</td>
                            <td>Cliente</td>
                            <td>Concepto</td>
                            <td>Importe</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $GasImp = 0;
                        foreach ($registrosG as $rg) {
                            ?>
                            <tr>
                                <td class="numero"><?= substr(ucwords(strtolower($rg["cliente"])), 0, 40) ?></td>
                                <td class="numero"><?= substr(ucwords(strtolower($rg["alias"])), 0, 40) ?></td>
                                <td class="numero"><?= substr(ucwords(strtolower($rg["concepto"])), 0, 60) ?></td>
                                <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                            </tr>
                            <?php
                            $GasImp += $rg["importe"];
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td></td>
                            <td></td>
                            <td>Total</td>                            
                            <td><?= number_format($GasImp, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>


                <table aria-hidden="true">
                    <thead>
                        <tr class="titulo">
                            <td colspan="4">Venta por producto</td>
                        </tr>
                        <tr>
                            <td>Producto</td>
                            <td>No.Vtas</td>
                            <td>Litros</td>
                            <td>Importe</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $ImpT = $LtsT = $VtsT = 0;
                        foreach ($registrosP as $rg) {
                            ?>
                            <tr>
                                <td><?= $rg["descripcion"] ?></td>
                                <td class="numero"><?= number_format($rg["despachos"], 0) ?></td>
                                <td class="numero"><?= number_format($rg["volumen"], 3) ?></td>
                                <td class="numero"><?= number_format($rg["importe"], 2) ?></td>
                            </tr>
                            <?php
                            $ImpT += $rg["importe"];
                            $LtsT += $rg["volumen"];
                            $VtsT += $rg["despachos"];
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>Total</td>
                            <td><?= number_format($VtsT, 0) ?></td>
                            <td><?= number_format($LtsT, 3) ?></td>
                            <td><?= number_format($ImpT, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
                
                <table aria-hidden="true">
                    <thead>
                        <tr class="titulo">
                            <td colspan="4">Venta por producto (Consignaciones)</td>
                        </tr>
                        <tr>
                            <td>Producto</td>
                            <td>No.Vtas</td>
                            <td>Litros</td>
                            <td>Importe</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $ImpT = $LtsT = $VtsT = 0;
                        foreach ($registrosN as $rg) {
                            ?>
                            <tr>
                                <td><?= $rg["descripcion"] ?></td>
                                <td class="numero"><?= number_format($rg["despachos"], 0) ?></td>
                                <td class="numero"><?= number_format($rg["volumen"], 3) ?></td>
                                <td class="numero"><?= number_format(0.00, 2) ?></td>
                            </tr>
                            <?php
                            $LtsT += $rg["volumen"];
                            $VtsT += $rg["despachos"];
                        }
                        ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>Total</td>
                            <td><?= number_format($VtsT, 0) ?></td>
                            <td><?= number_format($LtsT, 3) ?></td>
                            <td><?= number_format(0, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>

                <?php if ($llave[0] == 1) { ?>
                    <table aria-hidden="true">
                        <thead>
                            <tr class="titulo">
                                <td colspan="8">INVENTARIO  <br/><span style="font-size: 9px;letter-spacing: normal"><?= $horario ?></span></td>
                            </tr>
                            <tr>
                                <td>Producto</td>
                                <td>Inv.inicial</td>
                                <td>Cargas</td>
                                <td>V.Factura</td>
                                <td>Venta</td>
                                <td>Inv.logico</td>
                                <td>Inv.real</td>
                                <td>Diferencia</td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($registrosP as $rg) {

                                $selectInventario = "
                                        SELECT volumen_actual,id,fecha_hora_s 
                                        FROM tanques_h 
                                        WHERE fecha_hora_s BETWEEN '" . $ctVO->getFecha() . "' AND '" .
                                        $ctVO->getFechaf() . "' AND producto='" . $rg["descripcion"] . "'";

                                $selectInicio = $selectInventario . " ORDER BY fecha_hora_s ASC LIMIT 1";
                                $InvInicial = utils\IConnection::execSql($selectInicio);

                                $selectFin = $selectInventario . " ORDER BY fecha_hora_s DESC LIMIT 1";
                                $InvFinal = utils\IConnection::execSql($selectFin);

                                $selectCargas = "
                                        SELECT SUM(aumento) aumento,SUM(vf.volFac*1000) volFac FROM cargas
                                        LEFT JOIN ( SELECT SUM(volumenfac) volFac FROM cargas LEFT JOIN me ON cargas.id=me.carga
                                        WHERE cargas.producto = '" . $rg["descripcion"] . "' AND cargas.tipo = 0
                                        AND fecha_fin BETWEEN '" . $ctVO->getFecha() . "' AND '" . $ctVO->getFechaf() . "') vf ON TRUE
                                        WHERE producto = '" . $rg["descripcion"] . "' AND tipo = 0
                                        AND fecha_fin BETWEEN '" . $ctVO->getFecha() . "' AND '" . $ctVO->getFechaf() . "' ";
                                $Cargas = utils\IConnection::execSql($selectCargas);
                                ?>
                                <tr>
                                    <td><?= $rg["descripcion"] ?></td>
                                    <td class="numero"><?= number_format($InvInicial[volumen_actual], 0) ?></td>
                                    <td class="numero"><?= number_format($Cargas["aumento"], 0) ?></td>
                                    <td class="numero"><?= number_format($Cargas["volFac"], 0) ?></td>
                                    <td class="numero"><?= number_format($rg["volumen"], 0) ?></td>
                                    <td class="numero"><?= number_format($InvInicial[volumen_actual] + $Cargas["aumento"] - $rg["volumen"], 0) ?></td>
                                    <td class="numero"><?= number_format($InvFinal[volumen_actual], 0) ?></td>
                                    <td class="numero"><?= number_format($InvFinal[volumen_actual] - ($InvInicial[volumen_actual] + $Cargas["aumento"] - $rg["volumen"]), 0) ?></td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>

                <?php } ?>

                <table aria-hidden="true">
                    <tfoot>
                        <tr>
                            <td></td>
                            <td></td>
                            <td>Combustibles:</td>                                
                            <td><?= number_format($ImpT, 2) ?></td>
                        </tr>

                        <tr>
                            <td></td>
                            <td></td>
                            <td>Aceites:</td>                                
                            <td><?= number_format($nSubImp, 2) ?></td>
                        </tr>

                        <tr>
                            <td></td>
                            <td></td>
                            <td>Gastos:</td>
                            <td><?= number_format($GasImp, 2) ?></td>
                        </tr>

                        <tr>
                            <td></td>
                            <td></td>
                            <td>GRAN TOTAL</td>                                
                            <td><?= number_format($ImpT + $nSubImp - $GasImp, 2) ?></td>
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
                            <td>
                                <table style="width: 100%" aria-hidden="true">
                                    <tr class="texto_tablas">
                                        <td style="text-align: right;padding-right: 5px">Detallado:</td>
                                        <td style="text-align: left;padding-left: 5px">
                                            <select id="Detallado" name="Detallado">
                                                <option value="Si">Si</option>
                                                <option value="No">No</option>
                                            </select>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td>
                                <span><input type="submit" name="Boton" value="Enviar"></span>
                                <span><button onclick="print()" title="Imprimir reporte"><i class="icon fa fa-lg fa-print" aria-hidden="true"></i></button></span>
                            </td>
                        </tr>
                    </table>
                </div>
                <input type="hidden" name="Corte" id="Corte">
            </form>
            <?php topePagina(); ?>
        </div>
    </body>
</html>
