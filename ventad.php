<?php
#Librerias
session_start();

include_once ("auth.php");
include_once ("authconfig.php");
include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\Request::instance();
$request->getAttributes("Folio") >= 0 ? utils\HTTPUtils::setSessionValue("Folio", $request->getAttributes("Folio")) : utils\HTTPUtils::setSessionValue("Folio", "");
if ($request->has("Boton")) {
    if ($request->get("Boton") === "Ajustar") {

        $corteSql = $mysqli->query("(SELECT corte FROM islas WHERE activo = 'Si')")->fetch_array();
        $corte = $corteSql["corte"];

        $sql = "SELECT posicion,
                ROUND((tv1 - vol1),3) totV1,ROUND((tm1 - imp1),2) totI1,
                ROUND((tv2 - vol2),3) totV2,ROUND((tm2 - imp2),2) totI2,
                ROUND((tv3 - vol3),3) totV3,ROUND((tm3 - imp3),2) totI3
                FROM(
                        SELECT subA.posicion,MAX(subA.vol1) vol1,
                        MAX(subA.imp1) imp1,MAX(subA.vol2) vol2,MAX(subA.imp2) imp2,MAX(subA.vol3) vol3,MAX(subA.imp3) imp3,
                        IFNULL(subB.tv1,0) tv1,IFNULL(subB.tm1,0) tm1,IFNULL(subB.tv2,0) tv2,IFNULL(subB.tm2,0) tm2,IFNULL(subB.tv3,0) tv3,IFNULL(subB.tm3,0) tm3 
                        FROM(
                                SELECT m.posicion,m.manguera,
                                ROUND(IFNULL(SUM(rm.volumen),0),3) vol1, ROUND(IFNULL(SUM(rm.pesos),0),2) imp1,
                                ROUND(0,3) vol2,ROUND(0,2) imp2,ROUND(0,3) vol3,ROUND(0,2) imp3
                                FROM man,man_pro m
                                LEFT JOIN rm ON m.posicion = rm.posicion AND m.manguera = rm.manguera AND rm.corte = (SELECT corte FROM islas WHERE activo = 'Si')
                                WHERE man.posicion = m.posicion AND man.activo = 'Si' AND m.manguera = 1
                                GROUP BY m.posicion,m.manguera
                                UNION 
                                SELECT m.posicion,m.manguera,ROUND(0,3) vol1,ROUND(0,2) imp1,
                                ROUND(IFNULL(SUM(rm.volumen),0),3) vol2, ROUND(IFNULL(SUM(rm.pesos),0),2) imp2,
                                ROUND(0,3) vol3,ROUND(0,2) imp3
                                FROM man,man_pro m
                                LEFT JOIN rm ON m.posicion = rm.posicion AND m.manguera = rm.manguera AND rm.corte = (SELECT corte FROM islas WHERE activo = 'Si')
                                WHERE man.posicion = m.posicion AND man.activo = 'Si' AND m.manguera = 2
                                GROUP BY m.posicion,m.manguera
                                UNION 
                                SELECT m.posicion,m.manguera,ROUND(0,3) vol1,ROUND(0,2) imp1,ROUND(0,3) vol2,ROUND(0,2) imp2,
                                ROUND(IFNULL(SUM(rm.volumen),0),3) vol3, ROUND(IFNULL(SUM(rm.pesos),0),2) imp3
                                FROM man,man_pro m
                                LEFT JOIN rm ON m.posicion = rm.posicion AND m.manguera = rm.manguera AND rm.corte = (SELECT corte FROM islas WHERE activo = 'Si')
                                WHERE man.posicion = m.posicion AND man.activo = 'Si' AND m.manguera = 3
                                GROUP BY m.posicion,m.manguera) subA 
                        LEFT JOIN 
                        (
                                SELECT m.posicion,IFNULL(t.volumen1,0) tv1,IFNULL(t.volumen2,0) tv2,IFNULL(t.volumen3,0) tv3,
                                IFNULL(t.monto1,0) tm1,IFNULL(t.monto2,0) tm2,IFNULL(t.monto3,0) tm3 
                                FROM man m LEFT JOIN totalizadores t ON m.posicion =  t.posicion AND t.idtarea = -100
                                WHERE m.activo = 'Si'
                        ) subB ON subA.posicion = subB.posicion
                GROUP BY subA.posicion) subQ; ";

        $result = $mysqli->query($sql);

        while ($rows = $result->fetch_array()) {
            $sqlCtd = "UPDATE ctd SET ivolumen1 = " . $rows["totV1"] . ",imonto1 = " . $rows["totI1"] . ",
                       ivolumen2 = " . $rows["totV2"] . ",imonto2 = " . $rows["totI2"] . ",
                       ivolumen3 = " . $rows["totV1"] . ",imonto3 = " . $rows["totI3"] . " 
                       WHERE id = $corte AND posicion =" . $rows["posicion"] . ";";
            $mysqli->query($sqlCtd);
        }
    }
}

if ($request->has("Corte")) {
    $CtA = $mysqli->query("SELECT id, fecha, hora, turno, isla FROM ct WHERE id='$Corte'");
} else {
    $CtA = $mysqli->query("SELECT id, fecha, hora, turno, isla FROM ct WHERE status='Abierto' ORDER BY id DESC LIMIT 1");
}


$Ct = $CtA->fetch_array();
$Corte = $Ct["id"];
$Isla = $Ct["isla"];

$Titulo = "No.corte:" . $Ct["id"] . " isla:" . $Ct["isla"] . " turno:" . $Ct["turno"] . " Fecha y hora de inicio: " . $Ct["fecha"];

$Detallado = "Si";
if ($request->has("Detallado")) {
    $Detallado = $request->get("Detallado");
}

$IslaA = $mysqli->query("SELECT id, isla FROM ct WHERE status = 'Abierto' ORDER BY id");
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <title><?= $Gcia ?></title>
        <script>
            function callVisor1() {
                window.setInterval(function () {
                    $("#contenedor1").load("ventaPos.php?op=Lec&Detallado=<?= $Detallado
        ?>", function (response, status, xhr) {
                        if (status === "error") {
//window.location = "500.html";
                        }
                    });
                }, 1000);
            }

            function callVisor2() {
                window.setInterval(function () {
                    $("#contenedor2").load("ventaPos.php?op=Desp&Corte=<?= $Corte ?>&Detallado=<?= $Detallado ?>", function (response, status, xhr) {
                        if (status === "error") {
//window.location = "500.html";
                        }
                    });
                }, 1000);
            }

            $(document).ready(function () {
                callVisor1();
                callVisor2();
                $("#Detallado").val("<?= $Detallado ?>");
                $("#Islas").val("<?= $Ct["id"] ?>");
            });

        </script>
    </head>

    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo) ?>
            <div id="contenedor1"></div>
            <div id="contenedor2"></div>
        </div>

        <div id="footer">
            <form name="formActions" method="post" action="" id="form" class="oculto">
                <div id="Controles">
                    <table aria-hidden="true">
                        <tr style="height: 40px;">
                            <td>Islas abiertas: 
                                <select name="Islas" id="Islas" class="texto_tablas">
                                    <?php
                                    while ($Isla = $IslaA->fetch_array()) {
                                        echo "<option value='" . $Isla["id"] . "'>" . $Isla["isla"] . "</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td>Detallado: 
                                <select name="Detallado" class="texto_tablas" id="Detallado">
                                    <option value="Si">Si</option>
                                    <option value="No">No</option>
                                </select> 
                            </td>
                            <td>
                                <span><input type="submit" style="width:80px" name="Boton" value="Enviar"></span>
                            </td>
                            <td>
                                <span><input type="submit" style="width:80px" name="Boton" value="Ajustar"></span>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4">
                                <span><input type="submit" style="width:80px" name="Boton" value="Imprimir" onclick="print();"></span>
                            </td>
                        </tr>
                    </table>
                </div>
            </form>
            <?php topePagina() ?>
        </div>

    </body>

</html>
