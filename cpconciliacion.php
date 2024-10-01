<?php
#Librerias
session_start();

include_once("./check_report.php");
include_once("libnvo/lib.php");
include_once("importeletras.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$usuarioSesion = getSessionUsuario();

$cId = $request->getAttribute("busca");

$query = "SELECT cargas.id,
    cargas.clave_producto,
    cargas.producto,
    com.clavei,
    cargas.inicia_carga,
    cargas.finaliza_carga,
    cargas.t_inicial,
    cargas.t_final,
    cargas.vol_inicial,
    cargas.vol_final,
    cargas.aumento,
    IFNULL(c.id, 0) nextId
FROM me,
    com,
    cargas
    LEFT JOIN cargas c ON c.id = cargas.id + 1
WHERE me.carga = cargas.id
    AND com.clave = me.producto
    AND me.id = '$cId'
    AND cargas.tipo = '0';";

$Inicio_sql = $mysqli->query($query);
$Inicio = $Inicio_sql->fetch_array();

error_log($Inicio["id"] );

if ($Inicio["id"] > 0 && $Inicio["nextId"] > 0) {
    $query = "SELECT cargas.inicia_carga,cargas.finaliza_carga,cargas.vol_inicial,cargas.vol_final,cargas.aumento 
            FROM cargas,me 
            WHERE me.id = '" . $Inicio["nextId"] . "' AND me.carga = cargas.id";

    $Fin = $mysqli->query($query)->fetch_array();

    $selectVentas = "SELECT SUM(volumenp) total 
            FROM rm 
            WHERE fin_venta BETWEEN '$Inicio[finaliza_carga]' AND '$Fin[inicia_carga]' 
            AND producto = '" . $Inicio["clavei"] . "' AND tipo_venta = 'D'";

    $Lt = $mysqli->query($selectVentas)->fetch_array();

    $dif = $Inicio["vol_final"] - $Fin["vol_inicial"];


   

}
$Titulo = "Conciliacion por combustible";
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">

<head>
    <?php require './config_omicrom_reports.php'; ?>
    <title><?= $Gcia ?></title>
    <style type="text/css">
        html,
        body {
            min-width: 250px;
        }
    </style>
</head>

<body>

    <div id='container'>
        <?php nuevoEncabezadoMini($Titulo) ?>

        <?php
        if ($Inicio["id"] > 0) { ?>

            <table style="width: 100%" aria-hidden="true">

                    <tr class='texto_tablas'>
                        <td>No. de Entrada: </td>
                        <td><strong><?= $cId ?></strong>&nbsp;</td>
                    </tr>
                    <tr class='texto_tablas'>
                        <td>Producto: </td>
                        <td><strong><?= $Inicio["producto"] ?></td>
                    </tr>

                    <tr class='texto_tablas'>
                        <td colspan='2'>
                            <hr>
                        </td>
                    </tr>

                    <tr class='texto_tablas'>
                        <td>Entrada de la pipa: </td>
                        <td><strong><?= $Inicio["inicia_carga"] ?></td>
                    </tr>
                    <tr class='texto_tablas'>
                        <td>Temperatura Inicial: </td>
                        <td><strong><?= $Inicio["t_inicial"] ?> &#176;C</td>
                    </tr>
                    <tr class='texto_tablas'>
                        <td>Temperatura Final: </td>
                        <td><strong><?= $Inicio["t_final"] ?> &#176;C</td>
                    </tr>
                    <tr class='texto_tablas'>
                        <td>Inventario Inicial: </td>
                        <td><strong><?= number_format($Inicio["vol_inicial"], 3) ?></td>
                    </tr>
                    <tr class='texto_tablas'>
                        <td><strong>Inventario Final: </td>
                        <td><strong><?= number_format($Inicio["vol_final"], 3) ?></td>
                    </tr>
                    <tr class='texto_tablas'>
                        <td>Aumento: </td>
                        <td><strong><?= number_format($Inicio["aumento"], 3) ?></td>
                    </tr>

                    <tr class='texto_tablas'>
                        <td colspan='2'>
                            <hr>
                        </td>
                    </tr>


                </table>

           <?php if ($Inicio["nextId"] > 0) {
        ?>
                <table style="width: 100%" aria-hidden="true">

                    <tr class='texto_tablas'>
                        <td>Entrada de la siguiente pipa: </td>
                        <td><strong><?= $Fin["inicia_carga"] ?></td>
                    </tr>
                    <tr class='texto_tablas'>
                        <td><strong>Inventario Inicial: </td>
                        <td><strong><?= number_format($Fin["vol_inicial"], 3) ?></td>
                    </tr>
                    <tr class='texto_tablas'>
                        <td>Inventario Final: </td>
                        <td><strong><?= number_format($Fin["vol_final"], 3) ?></td>
                    </tr>
                    <tr class='texto_tablas'>
                        <td>Aumento: </td>
                        <td><strong><?= number_format($Fin["aumento"], 3) ?></td>
                    </tr>

                    <tr class='texto_tablas'>
                        <td colspan='2'>
                            <hr>
                        </td>
                    </tr>

                    <tr class='texto_tablas'>
                        <td>Diferencia: </td>
                        <td><strong><?= number_format(($dif), 3) ?></td>
                    </tr>
                    <tr class='texto_tablas'>
                        <td>Litros Vendidos: </td>
                        <td><strong><?= number_format($Lt["total"], 3) ?></strong></td>
                    </tr>
                    <tr class='texto_tablas'>
                        <td>Varianza: </td>
                        <td><strong><?= number_format($Lt["total"] - $dif, 3) ?></strong></td>
                    </tr>

                </table>
        <?php
            } else {
                echo "<div align='center'><strong>No hay informaci&oacute;n aun de la siguiente carga!!</strong></div>";
                error_log("entra en 2 else");
            }
        } else {
            echo "<div align='center'><strong>No hay informaci&oacute;n aun de esta carga ya que fue capturada como Jarreo!!</strong></div>";
        }
        ?>
        <br />
    </div>
    <div id='footer'>
        <?php topePagina(); ?>
    </div>
</body>

</html>