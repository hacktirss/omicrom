<?php

session_start();
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();

$selectPosiciones = "SELECT ep.posicion,ep.estado
                FROM estado_posiciones ep,man_pro mp
                WHERE ep.posicion = mp.posicion
                AND mp.activo='Si' AND mp.posicion < 97
                GROUP BY mp.posicion";

if ($request->getAttribute("op") === "Com") {
    $cSql = $mysqli->query($selectPosiciones);

    echo "<table border='0' width='100%'>";
    echo "<tr class='texto_tablas'>";
    echo "<th width='70%'>Posicion</th>";
    echo "<th width='30%'>Estado</th>";
    echo "</tr>";

    while ($rg = $cSql->fetch_array()) {
        echo "<tr align='center' class='texto_tablas'>";
        echo "<td>" . $rg["posicion"] . "</td>";
        if ($rg["estado"] == 'e') {
            echo "<td align='left'><img src='libnvo/imgna.png' height='15'><font color='orange'> En espera...</td>";
        } elseif ($rg["estado"] == 'd') {
            echo "<td align='left'><img src='libnvo/imgvd.png' height='15'><font color='green'> Despachando...</td>";
        } elseif ($rg["estado"] == 'b') {
            echo "<td align='left'><img src='libnvo/imgrj.png' height='15'><font color='red'> Bloqueado...</td>";
        } else {
            echo "<td align='left'><img src='libnvo/imgng.png' height='15'><font color='gray'> Fuera de linea...</td>";
        }
        echo "</tr>";
    }

    echo "</table>";
}
$mysqli->close();
