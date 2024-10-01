<?php

session_start();
include_once ("libnvo/lib.php");
include_once ("data/IslaDAO.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$islaDAO = new IslaDAO();
$usuarioSesion = getSessionUsuario();

$Detallado = $request->getAttribute("Detallado");
$OrdenP = $request->getAttribute("Orden");
$islaVO = $islaDAO->retrieve(1, "isla");
$Corte = $islaVO->getCorte();

if ($request->getAttribute("op") === "Com") {

    if ($OrdenP === "P") {
        $selectPosiciones = "SELECT man.posicion,
                            CASE ep.estado 
                            WHEN 'e' THEN 'En espera...' WHEN 'd' THEN 'Despachando...' WHEN 'b' THEN 'Bloqueado...'
                            ELSE 'Fuera de linea...'
                            END estado,
                            CASE ep.estado 
                            WHEN 'e' THEN 'imgna.png' WHEN 'd' THEN 'imgvd.png' WHEN 'b' THEN 'imgrj.png'
                            ELSE 'imgng.png'
                            END imagen,
                            CASE ep.estado 
                            WHEN 'e' THEN 'orange' WHEN 'd' THEN 'green' WHEN 'b' THEN 'red'
                            ELSE 'gray'
                            END color
                            FROM
                            estado_posiciones ep,man
                            WHERE
                            ep.posicion = man.posicion
                            AND man.activo = 'Si'
                            GROUP BY man.posicion";

        $rows = utils\IConnection::getRowsFromQuery($selectPosiciones);

        echo "<table>";

        echo "<thead>";
        echo "<tr>";
        echo "<th>Posicion</th>";
        echo "<th>Estado</th>";
        echo "</tr>";
        echo "</thead>";

        echo "<tbody>";
        foreach ($rows as $rg) {
            echo "<tr>";
            echo "<td>" . $rg["posicion"] . "</td>";
            echo "<td style='color: " . $rg["color"] . "'><img src='libnvo/" . $rg["imagen"] . "'> " . $rg["estado"] . "</td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
    } elseif ($OrdenP === "I") {
        $selectIslas = "SELECT man.isla_pos, CONCAT('Isla ', man.isla_pos) descripcion 
                        FROM man
                        WHERE man.activo = 'Si' 
                        GROUP BY man.isla_pos
                        ORDER BY man.isla_pos;";

        $rows = utils\IConnection::getRowsFromQuery($selectIslas);

        echo "<table>";

        echo "<thead>";
        echo "<tr><th></th>";
        echo "<th>Islas - Dispensario</th>";
        echo "</tr>";
        echo "</thead>";

        echo "<tbody>";
        foreach ($rows as $rg) {
            echo "<tr><td></td>";
            echo "<td width='80%'>" . $rg["descripcion"] . "</td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
    } else {
        $selectVendedores = "SELECT ven.id despachador,GROUP_CONCAT(man.posicion) posiciones,ven.alias 
                            FROM man,ven 
                            WHERE ven.id = man.despachador 
                            AND man.activo = 'Si' 
                            GROUP BY ven.id
                            ORDER BY man.posicion;";

        $rows = utils\IConnection::getRowsFromQuery($selectVendedores);

        echo "<table>";

        echo "<thead>";
        echo "<tr><th></th>";
        echo "<th>Despachador</th>";
        echo "</tr>";
        echo "</thead>";

        echo "<tbody>";
        foreach ($rows as $rg) {
            echo "<tr><td></td>";
            echo "<td width='80%'>" . ucwords(strtoupper($rg["alias"])) . " (Pos: " . $rg["posiciones"] . ")</td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
    }
} elseif ($request->getAttribute("op") === "Desp") {

    echo "<div id='TablaDatosReporte' style='min-height: 150px;width: 70%;'>";
    if ($Detallado == "No") {

        echo " <table aria-hidden=\"true\">";

        echo "<tr><td class='tdEncabezado' colspan='5'>&#8226; Efectivo por vendedor</td><tr>";

        echo "<tr>";
        echo "<td class='downTitles'>Vendedor</td>";
        echo "<td class='downTitles'>Efectivo</td>";
        echo "<td class='downTitles'>Aditivos</td>";
        echo "<td class='downTitles'>Depositado</td>";
        echo "<td class='downTitles'>Circulante</td>";
        echo "</tr>";

        $Ven = "SELECT A.*,IFNULL(B.depositos,0) depositos
                FROM
                (
                    SELECT ven.id,ven.alias despachador,IFNULL(SUM(c.pesos),0) importe,c.posicion
                    FROM  $ct_parcial c,ven 
                    WHERE
                    c.vendedor=ven.id
                    GROUP BY c.vendedor
                ) AS A
                LEFT JOIN
                (
                    SELECT c.posicion,ven.id,ven.alias despachador,IFNULL(SUM(c.total),0) depositos
                    FROM ctdep c,ven 
                    WHEREc.despachador=ven.id
                    AND c.corte = '$Corte'
                    GROUP BY c.despachador
                ) AS B
                ON
                A.id = B.id;";

        $VtsA = $mysqli->query($Ven);
        $ImpT = $DepT = 0;
        while ($rg = $VtsA->fetch_array()) {
            /**
             * Se deben de considerar los aditivos.
             */
            $Sql_adi = "SELECT
                        IFNULL(ROUND(SUM(total),2),0) importe
                        FROM vtaditivos
                        WHERE corte = (SELECT corte FROM islas WHERE activo='Si')
                        AND tm = 'C'
                        AND vendedor = '" . $rg["id"] . "'";

            $Ace = $mysqli->query($Sql_adi)->fetch_array();

            /**
             * Demas ventas diferente de contado y D
             */
            $Sql_cre = "SELECT SUM(importe) importe 
                    FROM (
                    SELECT IFNULL(ROUND(SUM(pesos),2),0) importe
                    FROM rm WHERE cliente <> 0 AND vendedor = " . $rg["id"] . " 
                    AND tipo_venta = 'D'
                    AND corte = (SELECT corte FROM islas WHERE activo='Si')
                    UNION
                    SELECT IFNULL(ROUND(SUM(pesos),2),0) importe FROM rm 
                    WHERE tipo_venta = 'J' AND vendedor = " . $rg["id"] . "
                    AND corte = (SELECT corte FROM islas WHERE activo='Si')
                    ) AS sub ";
            $Cre = $mysqli->query($Sql_cre)->fetch_array();

            echo "<tr>";

            echo "<td>" . ucwords(strtoupper($rg["despachador"])) . "</td>";
            echo "<td align='right'>" . number_format($rg["importe"] - $Cre["importe"], 2) . "</td>";
            echo "<td align='right'>" . number_format($Ace["importe"], 2) . "</td>";
            echo "<td align='right'>" . number_format($rg["depositos"], 2) . "</td>";
            echo "<td align='right'>" . number_format($rg["importe"] - $rg["depositos"] - $Cre["importe"] + $Ace[0], 2) . "</td>";
            echo "</tr>";
            $ImpA += $Ace["importe"];
            $ImpT += $rg["importe"] - $Cre["importe"];
            $DepT += $rg["depositos"];
        }

        echo "<tr>";
        echo "<td class='upTitles'>Total</td>";
        echo "<td class='upTitles'>" . number_format($ImpT, 2) . "</td>";
        echo "<td class='upTitles'>" . number_format($ImpA, 2) . "</td>";
        echo "<td class='upTitles'>" . number_format($DepT, 2) . "</td>";
        echo "<td class='upTitles'>" . number_format($ImpT + $ImpA - $DepT, 2) . "</td>";
        echo "</tr>";
        echo "</table>";
    }

    echo "</div>";
} elseif ($request->getAttribute("op") === "Lec") {

    $BuscaVariable = "SELECT valor FROM variables_corporativo WHERE llave = 'return_importe'";
    $bV = utils\IConnection::execSql($BuscaVariable);
    $Concat = "";
    if ($bV["valor"] === "No") {
        $Concat = "IFNULL(ROUND(SUM(rm.pesos),2),0) tm1,
        IFNULL(ROUND(SUM(rm.pesos),2),0) tm2,
        IFNULL(ROUND(SUM(rm.pesos),2),0) tm3,
        CASE WHEN ROUND((IFNULL(ROUND(SUM(rm.pesos),2),0) - ctd.imonto1),2) < 0 THEN
        10000000 + ROUND((IFNULL(ROUND(SUM(rm.pesos),2),0) - ctd.imonto1),2)
        ELSE ROUND((IFNULL(ROUND(SUM(rm.pesos),2),0) - ctd.imonto1),2) END dm1,
        case when ROUND((IFNULL(ROUND(SUM(rm.pesos),2),0) - ctd.imonto2),2) < 0 THEN
        10000000 + ROUND((IFNULL(ROUND(SUM(rm.pesos),2),0) - ctd.imonto2),2) 
        ELSE ROUND((IFNULL(ROUND(SUM(rm.pesos),2),0) - ctd.imonto2),2) END dm2,
        case WHEN ROUND(( (IFNULL(ROUND(SUM(rm.pesos),2),0) - ctd.imonto3)),2)<0 then
        10000000 + ROUND(( (IFNULL(ROUND(SUM(rm.pesos),2),0) - ctd.imonto3)),2)
        ELSE ROUND(( (IFNULL(ROUND(SUM(rm.pesos),2),0) - ctd.imonto3)),2) END dm3,";
    } else {
        $Concat = "ROUND((tot.monto1),2) tm1,
        ROUND((tot.monto2),2) tm2,
        ROUND((tot.monto3),2) tm3,
        CASE WHEN ROUND((tot.monto1 - ctd.imonto1),2) < 0 THEN
        10000000 + ROUND((tot.monto1 - ctd.imonto1),2)
        ELSE ROUND((tot.monto1 - ctd.imonto1),2) END dm1,
        case when ROUND((tot.monto2 - ctd.imonto2),2) < 0 THEN
        10000000 + ROUND((tot.monto2 - ctd.imonto2),2) 
        ELSE ROUND((tot.monto2 - ctd.imonto2),2) END dm2,
        case WHEN ROUND(( (tot.monto3 - ctd.imonto3)),2)<0 then
        10000000 + ROUND(( (tot.monto3 - ctd.imonto3)),2)
        ELSE ROUND(( (tot.monto3 - ctd.imonto3)),2) END dm3,";
    }

    $selectComandos = "SELECT posicion,ejecucion FROM comandos WHERE idtarea=-100 AND ejecucion = 1 ORDER BY posicion";

    if (($result = $mysqli->query($selectComandos)) && (utils\HTTPUtils::getSessionValue("Folio")["Folio"] === null || utils\HTTPUtils::getSessionValue("GuardarCorte"))) {
        while ($Pos = $result->fetch_array()) {
            if ($Pos["ejecucion"] > 0) {
                $insert = "INSERT INTO ct_parcial (posicion, manguera, producto, v1, v2, v3, tv1, tv2, tv3, m1, m2, m3, tm1, tm2, tm3, dm1, dm2, dm3, dv1, dv2, dv3, ventas, vendedor, pesos, volumen)
                       SELECT 
                        ctd.posicion,
                        m.manguera,
                        com.descripcion producto,
                        ROUND(ctd.ivolumen1,2) v1,
                        ROUND(ctd.ivolumen2,2) v2,
                        ROUND(ctd.ivolumen3,2) v3,
                        ROUND((tot.volumen1),2) tv1,
                        ROUND((tot.volumen2),2) tv2,
                        ROUND((tot.volumen3),2) tv3,
                        ROUND(ctd.imonto1,2) m1,
                        ROUND(ctd.imonto2,2) m2,
                        ROUND(ctd.imonto3,2) m3,
                        " . $Concat . "
                        ROUND((tot.volumen1 - ctd.ivolumen1),2) dv1,
                        ROUND((tot.volumen2 - ctd.ivolumen2),2) dv2,
                        ROUND((tot.volumen3 - ctd.ivolumen3),2) dv3,
                        COUNT(rm.id) ventas,
                        IFNULL(rm.vendedor,0) vendedor,
                        IFNULL(ROUND(SUM(rm.pesos),2),0) pesos,
                        IFNULL(ROUND(SUM(rm.volumen),2),0) volumen
                        FROM ctd,totalizadores tot,com,man_pro m 
                        LEFT JOIN rm ON m.posicion = rm.posicion AND m.manguera = rm.manguera AND rm.corte = (SELECT corte FROM islas WHERE activo='Si')
                        WHERE
                        ctd.id = (SELECT corte FROM islas WHERE activo='Si')
                        AND ctd.posicion = tot.posicion
                        AND ctd.posicion = m.posicion
                        AND m.producto = com.clavei
                        AND m.activo = 'Si'
                        AND tot.idtarea = -100
                        AND ctd.posicion = " . $Pos["posicion"] . " 
                        GROUP BY rm.posicion,rm.manguera";

                $sqlCParcial = "SELECT IFNULL(posicion,0) p FROM ct_parcial WHERE posicion = '" . $Pos["posicion"] . "';";
                $showPos = $mysqli->query($sqlCParcial)->fetch_array();

                if ($showPos["p"] == 0) {
                    if (utils\HTTPUtils::getSessionValue("GuardarCorte")) {
                        $insert2 = "INSERT INTO ct_parcial_fecha (usr,serie,posicion, manguera, producto, v1, v2, v3, tv1, tv2, tv3, m1, m2, m3, tm1, tm2, tm3, dm1, dm2, dm3, dv1, dv2, dv3, ventas, vendedor, pesos, volumen)
                       SELECT 
                       " . $usuarioSesion->getId() . " usr,
                         " . utils\HTTPUtils::getSessionValue("SerieCp") . " serie,
                        ctd.posicion,
                        m.manguera,
                        com.descripcion producto,
                        ROUND(ctd.ivolumen1,2) v1,
                        ROUND(ctd.ivolumen2,2) v2,
                        ROUND(ctd.ivolumen3,2) v3,
                        ROUND((tot.volumen1),2) tv1,
                        ROUND((tot.volumen2),2) tv2,
                        ROUND((tot.volumen3),2) tv3,
                        ROUND(ctd.imonto1,2) m1,
                        ROUND(ctd.imonto2,2) m2,
                        ROUND(ctd.imonto3,2) m3,
                        " . $Concat . "
                        ROUND((tot.volumen1 - ctd.ivolumen1),2) dv1,
                        ROUND((tot.volumen2 - ctd.ivolumen2),2) dv2,
                        ROUND((tot.volumen3 - ctd.ivolumen3),2) dv3,
                        COUNT(rm.id) ventas,
                        IFNULL(rm.vendedor,0) vendedor,
                        IFNULL(ROUND(SUM(rm.pesos),2),0) pesos,
                        IFNULL(ROUND(SUM(rm.volumen),2),0) volumen
                        FROM ctd,totalizadores tot,com,man_pro m 
                        LEFT JOIN rm ON m.posicion = rm.posicion AND m.manguera = rm.manguera AND rm.corte = (SELECT corte FROM islas WHERE activo='Si')
                        WHERE
                        ctd.id = (SELECT corte FROM islas WHERE activo='Si')
                        AND ctd.posicion = tot.posicion
                        AND ctd.posicion = m.posicion
                        AND m.producto = com.clavei
                        AND m.activo = 'Si'
                        AND tot.idtarea = -100
                        AND ctd.posicion = " . $Pos["posicion"] . " 
                        GROUP BY rm.posicion,rm.manguera";
                        if ($mysqli->query($insert2)) {
                            $Msj .= "Registro agregado con exito!";
                        }
                    }
                    if (!($mysqli->query($insert))) {
                        error_log($mysqli->error);
                    }
                }
            }
        }
    }
//echo "F".utils\HTTPUtils::getSessionValue("Folio")["Folio"] ;
    if (utils\HTTPUtils::getSessionValue("Folio")["Folio"] !== null) {
        $ct_parcial = "ct_parcial_fecha";
        $WHERE = " WHERE ";
        $Serie = " serie = " . utils\HTTPUtils::getSessionValue("Folio")["Folio"];
        
        $Sqrs= "SELECT ctp.fecha,a.name FROM omicrom.ct_parcial_fecha ctp "
                        . "LEFT JOIN authuser a ON a.id=ctp.usr"
                        . " WHERE serie= " . utils\HTTPUtils::getSessionValue("Folio")["Folio"] . " limit 1;";
        //echo $Sqrs;
        $RsF = utils\IConnection::execSql($Sqrs);
        $Stit = "Reimpresión de folio " . utils\HTTPUtils::getSessionValue("Folio")["Folio"] . " Fecha de generacion " . $RsF["fecha"] . " Creado por " . $RsF["name"];
    } else {
        $ct_parcial = "ct_parcial";
        $Serie = "";
        $cSql = "";
    }
    /*     * *****************Bloque de codigo de programa************************** */

    echo "<div id='TablaDatosReporte' style='min-height: 150px;'>";
    echo $Stit;
    if ($Detallado == "Si") {

        echo " <table aria-hidden=\"true\">";

        echo "<tr>";
        echo "<td class='tdEncabezado' colspan='3' ></td>";
        echo "<td class='tdEncabezado' colspan='4' bgcolor='#e1e1e1' > Lecturas de dispensario</td>";
        echo "<td class='tdEncabezado' colspan='2' bgcolor='#e1e1e1'>Ventas registradas</td>";
        echo "<td class='tdEncabezado' colspan='2' bgcolor='#e1e1e1'>Diferencia</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td class='downTitles'>Posicion</td>";
        echo "<td class='downTitles'>Producto</td>";
        echo "<td class='downTitles'>No.ventas</td>";
        echo "<td class='downTitles'>Inicial</td>";
        echo "<td class='downTitles'>Actual</td>";
        /**
         * Dispensarios
         */
        echo "<td class='downTitles'>Litros</td>";
        echo "<td class='downTitles'>Importe</td>";

        /**
         * Ventas de rm
         */
        echo "<td class='downTitles'>Litros</td>";
        echo "<td class='downTitles'>Importe</td>";

        /**
         * Diferencias
         */
        echo "<td class='downTitles'>Litros</td>";
        echo "<td class='downTitles'>Importe</td>";

        echo "</tr>";
    } else {

        echo " <table aria-hidden=\"true\">";

        echo "<tr>";
        echo "<td class='tdEncabezado' colspan='3'></td>";
        echo "<td class='tdEncabezado' colspan='2'>Lecturas de dispensario</td>";
        echo "<td class='tdEncabezado' colspan='2'>Ventas registradas</td>";
        echo "<td class='tdEncabezado' colspan='2'>Diferencia</td>";

        echo "</tr>";

        echo "<tr>";
        echo "<td class='downTitles'>Posicion</td>";
        echo "<td class='downTitles'>Despachador</td>";
        echo "<td class='downTitles'>No.ventas</td>";

        echo "<td class='downTitles'>Litros</td>";
        echo "<td class='downTitles'>Importe</td>";

        echo "<td class='downTitles'>Litros</td>";
        echo "<td class='downTitles'>Importe</td>";

        echo "<td class='downTitles'>Litros</td>";
        echo "<td class='downTitles'>Importe</td>";

        echo "</tr>";
    }


    if ($Detallado == "Si") {
        $cSql = "SELECT
            posicion,
            manguera,
            producto combustible,
            v1,v2,v3,
            tv1,tv2,tv3,
            dm1,dm2,dm3,
            dv1,dv2,dv3,
            ventas,
            vendedor,
            pesos importe,
            volumen litros
            FROM $ct_parcial  $WHERE $Serie
            ORDER BY posicion,manguera ASC;";
    } else {
        $cSql = "SELECT 
                $ct_parcial.posicion,
                $ct_parcial.manguera,
                $ct_parcial.producto combustible,
                IF(     ROUND($ct_parcial.dm1+$ct_parcial.dm2+$ct_parcial.dm3,2) < 0,
                        ROUND($ct_parcial.dm1+$ct_parcial.dm2+$ct_parcial.dm3,2) + 1000000,
                        ROUND($ct_parcial.dm1+$ct_parcial.dm2+$ct_parcial.dm3,2)
                    ) dm1,
                ROUND($ct_parcial.dv1+$ct_parcial.dv2+$ct_parcial.dv3,2) dv1,
                SUM($ct_parcial.ventas) ventas,
                ven.alias despachador,
                SUM($ct_parcial.pesos) importe,
                SUM($ct_parcial.volumen) litros
                FROM $ct_parcial LEFT JOIN ven ON ven.id=$ct_parcial.vendedor $WHERE $Serie
                GROUP BY $ct_parcial.posicion
                ORDER BY $ct_parcial.posicion ASC;";
    }
//echo $cSql;

    if (($VtsA = $mysqli->query($cSql))) {

        while ($rg = $VtsA->fetch_array()) {

            echo "<tr>";

            if ($Detallado == "Si") {
                $SqlJar = "SELECT
                rm.posicion, 
                IFNULL(ROUND(SUM(rm.pesos),2),0) importej,
                IFNULL(ROUND(SUM(rm.volumen),2),0) litrosj
                FROM rm
                WHERE rm.corte = (SELECT corte FROM islas WHERE activo='Si')
                AND rm.tipo_venta='J'
                AND rm.posicion=" . $rg["posicion"] . " 
                AND rm.manguera=" . $rg["manguera"] . "";

                echo "<td align='center'>" . $rg["posicion"] . "</td>";
                echo "<td align='left'>&nbsp " . ucwords(strtolower($rg["combustible"])) . "</td>";
                echo "<td align='right'>" . $rg["ventas"] . "</td>";

                $v = "v" . $rg["manguera"];
                echo "<td align='right'>$rg[$v]</td>";
                $t = "tv" . $rg["manguera"];
                echo "<td align='right'>$rg[$t]</td>";

                $dv = "dv" . $rg["manguera"];
                $dm = "dm" . $rg["manguera"];

                /**
                 * Operacion de dispensarios 
                 */
                echo "<td align='right'>" . number_format($rg[$dv], 2) . "</td>";
                echo "<td align='right'>" . number_format($rg[$dm], 2) . "</td>";

                /**
                 * Venta de rm
                 */
                echo "<td align='right'>" . number_format($rg["litros"], 2) . "</td>";
                echo "<td align='right'>" . number_format($rg["importe"], 2) . "</td>";

                echo "<td align='right'> " . number_format($rg[$dv] - $rg["litros"], 2) . "</td>";
                echo "<td align='right'> " . number_format($rg[$dm] - $rg["importe"], 2) . "</td>";

                $Vts += $rg["ventas"];

                $LtsP += $rg[$dv];
                $ImpP += $rg[$dm];

                $Lts += $rg["litros"];
                $Imp += $rg["importe"];

                $Dimp += ($rg[$dm] - $rg["importe"]);
                $Dlit += ($rg[$dv] - $rg["litros"]);
            } else {

                $SqlJar = "SELECT
                rm.posicion, 
                IFNULL(ROUND(SUM(rm.pesos),2),0) importej,
                IFNULL(ROUND(SUM(rm.volumen),2),0) litrosj
                FROM rm
                WHERE rm.corte = (SELECT corte FROM islas WHERE activo='Si')
                AND rm.tipo_venta='J'
                AND rm.posicion=" . $rg["posicion"] . "";

                echo "<td align='center'>" . $rg["posicion"] . "</td>";
                echo "<td>" . ucwords(strtolower($rg["despachador"])) . "</td>";
                echo "<td align='right'>" . $rg["ventas"] . "</td>";
                echo "<td align='right'>" . number_format($rg["dv1"], 2) . "</td>";
                echo "<td align='right'>" . number_format($rg["dm1"], 2) . "</td>";

                echo "<td align='right'>" . number_format($rg["litros"], 2) . "</td>";
                echo "<td align='right'>" . number_format($rg["importe"], 2) . "</td>";

                echo "<td align='right'>" . number_format($rg["dv1"] - $rg["litros"], 2) . "</td>";
                echo "<td align='right'>" . number_format($rg["dm1"] - $rg["importe"], 2) . "</td>";

                $Vts += $rg["ventas"];

                $LtsP += $rg["dv1"];
                $ImpP += $rg["dm1"];

                $Lts += $rg["litros"];
                $Imp += $rg["importe"];

                $Dimp += ($rg["dm1"] - $rg["importe"]);
                $Dlit += ($rg["dv1"] - $rg["litros"]);
            }
            echo "</tr>";
        }


        echo "<tr>";

        if ($Detallado === "Si") {
            echo "<td class='upTitles'></td>";
            echo "<td class='upTitles'>Total</td>";
            echo "<td class='upTitles'>" . number_format($Vts, 0) . "</td>";
            echo "<td class='upTitles'></td>";
            echo "<td class='upTitles'></td>";

            echo "<td class='upTitles'>" . number_format($LtsP, 2) . "</td>";
            echo "<td class='upTitles'>" . number_format($ImpP, 2) . "</td>";

            echo "<td class='upTitles'>" . number_format($Lts, 2) . "</td>";
            echo "<td class='upTitles'>" . number_format($Imp, 2) . "</td>";

            echo "<td class='upTitles'>" . number_format($Dlit, 2) . "</td>";
            echo "<td class='upTitles'>" . number_format($Dimp, 2) . "</td>";
        } else {
            echo "<td class='upTitles'></td>";
            echo "<td class='upTitles'>Total</td>";
            echo "<td class='upTitles'>" . number_format($Vts, 2) . "</td>";
            echo "<td class='upTitles'>" . number_format($Lts, 2) . "</td>";

            echo "<td class='upTitles'>" . number_format($Imp, 2) . "</td>";
            echo "<td class='upTitles'>" . number_format($Lts, 2) . "</td>";
            echo "<td class='upTitles'>" . number_format($Imp, 2) . "</td>";

            echo "<td class='upTitles'>" . number_format($Dlit, 2) . "</td>";
            echo "<td class='upTitles'>" . number_format($Dimp, 2) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";

    echo "</div>";

    /*     * ********************************Bloque intermedio********************** */

    echo "<div id='TablaDatosReporte' style='min-height: 150px;'>";

    echo " <table aria-hidden=\"true\">";
    echo "<tr>";
    echo "<td class='tdEncabezado'>&#8226; Venta por producto</td>";
    echo "<td class='tdEncabezado'>&#8226; Desgloce Monetario</td>";
    echo "</tr>";

    echo "<tr>";

    echo "<td align='center'>";

    echo " <table aria-hidden=\"true\">";

    echo "<tr>";
    echo "<td class='downTitles'>Producto</td>";
    echo "<td class='downTitles'>No.Vtas</td>";
    echo "<td class='downTitles'>Litros</td>";
    echo "<td class='downTitles'>Importe</td>";
    echo "</tr>";

    $selectTotales = "SELECT IFNULL(SUM( ventas ),0) despachos,
                        IFNULL(SUM( pesos ),0) importe, IFNULL(SUM( volumen ),0) volumen,
                        producto 
                        FROM $ct_parcial $WHERE $Serie
                        GROUP BY producto DESC";

    if (($Vta = $mysqli->query($selectTotales))) {

        while ($rg = $Vta->fetch_array()) {

            echo "<tr>";
            echo "<td>" . ucwords(strtolower($rg["producto"])) . "</td>";
            echo "<td align='right'>" . number_format($rg["despachos"], 0) . "</td>";
            echo "<td align='right'>" . number_format($rg["volumen"], 2) . "</td>";
            echo "<td align='right'>" . number_format($rg["importe"], 2) . "</td>";
            echo "</tr>";

            $ImpT += $rg["importe"];
            $LtsT += $rg["volumen"];
            $VtsT += $rg["despachos"];
        }

        echo "<tr>";
        echo "<td class='upTitles'></td>";
        echo "<td class='upTitles'>" . number_format($VtsT, 0) . "</td>";
        echo "<td class='upTitles'>" . number_format($LtsT, 2) . "</td>";
        echo "<td class='upTitles'>" . number_format($ImpT, 2) . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "</td>";

    echo "<td align='center'>";

    $sqlDep = "SELECT dep.corte, depd.cuenta, sum( depd.importe ) AS importe, cli.alias, count(*) as movtos
                FROM cli, dep, depd
                WHERE 
                dep.corte = (SELECT corte FROM islas WHERE activo='Si') 
                AND dep.id = depd.id 
                AND depd.cuenta = cli.id
                GROUP BY depd.cuenta";
    if (($DepA = $mysqli->query($sqlDep))) {

        echo " <table aria-hidden=\"true\">";

        echo "<tr>";
        echo "<td class='downTitles'>Descripcion</td>";
        echo "<td class='downTitles'>No.Movtos</td>";
        echo "<td class='downTitles'>Importe</td>";
        echo "</tr>";
        $ImpT = $MovT = 0;
        while ($rg = $DepA->fetch_array()) {

            echo "<tr>";
            echo "<td>" . ucwords(strtolower($rg["alias"])) . "</td>";
            echo "<td align='right'>" . number_format($rg["movtos"], 0) . "</td>";
            echo "<td align='right'>" . number_format($rg["importe"], 2) . "</td>";
            echo "</tr>";
            $ImpT += $rg["importe"];
            $MovT += $rg["movtos"];
        }
        echo "<tr>";
        echo "<td class='upTitles'>Total</td>";
        echo "<td class='upTitles'>" . number_format($MovT, 0) . "</td>";
        echo "<td class='upTitles'>" . number_format($ImpT, 2) . "</td>";
        echo "</tr>";
        echo "</table>";
    } else {
        error_log($mysqli->error);
    }

    echo "</td>";
    echo "</tr>";
    echo "</table>";
    echo "</div>";
    /*     * *********************************************************************** */
}
mysqli_close($mysqli);
