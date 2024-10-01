<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$mysqli = iconnect();

require_once './services/CambioTurnoService.php';

$ctVO = new CtVO();
if ($Corte > 0) {
    $ctVO = $ctDAO->retrieve($Corte);
}

$Titulo = "Corte: $Corte turno: " . $ctVO->getTurno() . " " . $ctVO->getFecha() . " ";

$selectVentasByDespachador = "
    SELECT 
    GROUP_CONCAT(DISTINCT SUB.posicion) posicion,
    SUB.despachador, SUB.alias, 
    SUM(SUB.ventas) ventas, 
    SUM(SUB.ventaCombustible) ventaCombustible, 
    SUM(SUB.ventaAceites) ventaAceites
    ,IFNULL(ctdep.importe, 0) depositos
    ,IFNULL(SUM(cttarjetas.importe), 0) depositosTar
    ,IFNULL(SUM(SUB.importeC),0) contado
    ,IFNULL(SUM(SUB.importeT),0) tarjeta
    ,IFNULL(SUM(SUB.importeP),0) impCli
    FROM(	
        SELECT man.posicion, 
            IFNULL(rm.vendedor, man.posicion) despachador,
            IFNULL(rm.alias, CONCAT('Posicion ', man.posicion)) alias,
            IFNULL(SUM(rm.ventas) ,0 ) ventas,
            IFNULL(SUM(rm.importe) ,0) ventaCombustible,
            IFNULL(vt.total ,0 ) ventaAceites,
            IFNULL(SUM(rmC.importeC) ,0 ) importeC,
            IFNULL(SUM(rmT.importeT),0) importeT,
            IFNULL(SUM(rmP.importeP),0) importeP
            FROM man
            LEFT JOIN (
                    SELECT rm.posicion, rm.vendedor, IFNULL(ven.alias, CONCAT('Posicion ', rm.posicion)) alias, COUNT(rm.id) ventas,
                    SUM(IF(
                            rm.cliente = 0 OR rm.pesos <> rm.pagoreal,
                            IF(rm.pesos - rm.pagoreal > 1 AND rm.inicio_venta <> rm.fin_venta, rm.pesos - rm.pagoreal, rm.pesos),
                            0
                    )) importea, SUM(rm.pesos) importe
                    FROM rm 
                    LEFT JOIN ven ON rm.vendedor = ven.id
                    WHERE 1 = 1 
                    AND rm.corte = $Corte AND rm.tipo_venta = 'D'
                    GROUP BY rm.posicion,rm.vendedor
            ) rm ON rm.posicion = man.posicion
            LEFT JOIN (
                    SELECT rm.posicion, rm.vendedor, 
                    SUM(rm.pesos) importeC
                    FROM rm 
                    LEFT JOIN ven ON rm.vendedor = ven.id
                    WHERE 1 = 1 AND tipodepago='Contado'
                    AND rm.corte = $Corte AND rm.tipo_venta = 'D'
                    GROUP BY rm.posicion, rm.vendedor
            ) rmC ON rmC.posicion = man.posicion
            LEFT JOIN (
                    SELECT rm.posicion, rm.vendedor, IFNULL(ven.alias, CONCAT('Posicion ', rm.posicion)) alias, COUNT(rm.id) ventas,
                    SUM(IF(
                            rm.cliente = 0 OR rm.pesos <> rm.pagoreal,
                            IF(rm.pesos - rm.pagoreal > 1 AND rm.inicio_venta <> rm.fin_venta, rm.pesos - rm.pagoreal, rm.pesos),
                            0
                    )) pb, sum(rm.pesos)  importeT
                    FROM rm 
                    LEFT JOIN ven ON rm.vendedor = ven.id
                    WHERE 1 = 1 AND tipodepago='Tarjeta'
                    AND rm.corte = $Corte AND rm.tipo_venta = 'D'
                    GROUP BY rm.posicion,rm.vendedor
            ) rmT ON rmT.posicion = man.posicion
            LEFT JOIN (
                    SELECT rm.posicion, rm.vendedor, COUNT(rm.id) ventas,
                    SUM(IF(
                            rm.pesos <> rm.pagoreal,
                            IF(rm.pesos - rm.pagoreal > 1 AND rm.inicio_venta <> rm.fin_venta, rm.pesos - rm.pagoreal, rm.pesos),
                            0
                    )) impt, SUM(rm.pesos) importeP
                    FROM rm
                    WHERE 1 = 1 AND tipodepago NOT IN ('Tarjeta','Contado')
                    AND rm.corte = $Corte AND rm.tipo_venta = 'D'
                    GROUP BY rm.posicion,rm.vendedor
            ) rmP ON rmP.posicion = man.posicion
            LEFT JOIN (
                    SELECT vt.posicion, SUM(vt.total) total 
                    FROM vtaditivos vt, cli WHERE TRUE 
                    AND vt.cliente = cli.id AND vt.corte = $Corte AND vt.cliente = 0
                    AND vt.tm = 'C' AND vt.cantidad > 0 AND vt.total > 0
                    GROUP BY vt.posicion
            ) vt ON man.posicion = vt.posicion
            WHERE 1 = 1 
            AND man.activo = 'Si'
            GROUP BY man.posicion
            ORDER BY man.posicion
    ) SUB
    LEFT JOIN (
            SELECT ctdep.despachador, SUM(ctdep.total) importe FROM ctdep 
        WHERE ctdep.corte = $Corte GROUP BY ctdep.despachador
    )
    ctdep ON SUB.despachador = ctdep.despachador 
    LEFT JOIN (
            SELECT cttarjetas.vendedor, SUM(cttarjetas.importe) importe FROM cttarjetas 
        WHERE cttarjetas.id = $Corte GROUP BY cttarjetas.vendedor
    )cttarjetas ON  SUB.despachador = cttarjetas.vendedor
    GROUP BY SUB.despachador
    ORDER BY SUB.posicion;
    ";

$registros = utils\IConnection::getRowsFromQuery($selectVentasByDespachador);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#autocomplete").focus();
            });
            function redirigir(variable) {
                window.location.href = variable;
            }
        </script>
    </head>

    <body>
        <?php BordeSuperior(); ?>
        <?php TotalizaDepositos(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr style="background-color: #E1E1E1;font-weight: bold;text-align: center;height: 25px;">
                <td style="width: 25%;" onclick="redirigir('mdepositos.php')">Depositos</td>
                <td style="width: 25%;" onclick="redirigir('mdepositosd.php')">Desglose monetario</td>
                <td style="width: 25%;background-color: #F63;color: white;">Saldos x despachador</td>
                <td style="width: 25%;" onclick="redirigir('mvendedores.php')">Vendedores x posicion</td>
            </tr> 
        </table>
        <div id="TablaDatos">
            <table aria-hidden="true"> 

                <tr>
                    <td class="fondoNaranja">Ventas</td>
                    <td class="fondoNaranja">Posicion</td>
                    <td class="fondoNaranja">Despachador</td>
                    <td class="fondoNaranja">No.ventas</td>
                    <td class="fondoNaranja">Combustible</td>
                    <td class="fondoNaranja">Aceites</td>
                    <td class="fondoNaranja">Total</td>
                    <td class="fondoNaranja">Vta. Clientes</td>
                    <td class="fondoNaranja">Contado</td>
                    <td class="fondoNaranja">Tarjeta</td>
                    <td class="fondoNaranja">Depositos</td>
                    <td class="fondoNaranja">Saldo</td>
                </tr>

                <?php
                $nPos = $nVen = "";
                foreach ($registros as $rg) {

                    echo "<tr>";
                    echo "<td align='center'><a class='textosCualli_i_n' href=javascript:winuni('rmvendedor.php?Corte=$Corte&busca=" . $rg["despachador"] . "')><i class=\"icon fa fa-lg fa-list-alt\" aria-hidden=\"true\"></i></a></td>";
                    echo "<td>&nbsp;" . $rg["posicion"] . "</td>";
                    echo "<td>&nbsp;" . ucwords(strtolower($rg["alias"])) . "</td>";
                    echo "<td align='right'>" . number_format($rg["ventas"], 0) . "</td>";
                    echo "<td align='right'>" . number_format($rg["ventaCombustible"], 2) . "</td>";
                    echo "<td align='right'>" . number_format($rg["ventaAceites"], 2) . "</td>";
                    echo "<td align='right'>" . number_format($rg["ventaCombustible"] - $rg["depositosTar"] + $rg["ventaAceites"], 2) . "</td>";
                    echo "<td align='right'>" . number_format($rg["impCli"], 2) . "</td>";
                    echo "<td align='right'>" . number_format($rg["contado"], 2) . "</td>";
                    echo "<td align='right'>" . number_format($rg["tarjeta"], 2) . "</td>";
                    echo "<td align='right'>" . number_format($rg["depositos"], 2) . "</td>";
                    echo "<td align='right'>" . number_format($rg["contado"] + $rg["ventaAceites"] - $rg["depositos"], 2) . "</td>";
                    echo "</tr>";

                    $nVentas += $rg["ventas"];
                    $nImporte += $rg["ventaCombustible"] - $rg["depositosTar"];
                    $nAceites += $rg["ventaAceites"];
                    $nDepositos += $rg["depositos"];
                    $nCli += $rg["impCli"];
                    $nContado += $rg["contado"];
                    $nTarjeta += $rg["tarjeta"];
                }

                echo "<tr>";
                echo "<td class='upTitles'></td>";
                echo "<td class='upTitles'></td>";
                echo "<td class='upTitles'>Total --></td>";
                echo "<td class='upTitles'>" . number_format($nVentas, 0) . "</td>";
                echo "<td class='upTitles'>" . number_format($nImporte, 2) . "</td>";
                echo "<td class='upTitles'>" . number_format($nAceites, 2) . "</td>";
                echo "<td class='upTitles'>" . number_format($nImporte + $nAceites, 2) . "</td>";
                echo "<td class='upTitles'>" . number_format($nCli, 2) . "</td>";
                echo "<td class='upTitles'>" . number_format($nContado, 2) . "</td>";
                echo "<td class='upTitles'>" . number_format($nTarjeta, 2) . "</td>";
                echo "<td class='upTitles'>" . number_format($nDepositos, 2) . "</td>";
                echo "<td class='upTitles'>" . number_format($nContado - $nDepositos + $nAceites, 2) . "</td>";
                echo "</tr>";
                ?>
            </table>
        </div>

        <?php echo $paginador->footer(false, null, false, false, 0, false); ?>

        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>

