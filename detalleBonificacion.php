<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("data/MensajesDAO.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$usuarioSesion = getSessionUsuario();

$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));
$Titulo = "Menu principal";
$Id = 5;

if ($request->hasAttribute("op")) {
    if ($request->getAttribute("op") === "st") {
        $sql = "SELECT * FROM msj WHERE tipo = '" . TipoMensaje::SIN_LEER . "' AND DATE_ADD(fecha,INTERVAL vigencia DAY) >= CURRENT_DATE()";
        $registros = utils\IConnection::getRowsFromQuery($sql);
        $numRegistros = count($registros);
        if ($numRegistros == 0) {
            header("Location: servicio.php");
        } else {
            header("Location: servicio.php?pop=1");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php include './config_omicrom.php'; ?> 
        <title><?= $Gcia ?></title>
        <script type="text/javascript">

        </script>
    </head>

    <body>
        <?php
        $sql = "select descripcion,producto_promocion,factores_producto,tipo_concepto,monto_promocion from periodo_puntos where tipo_periodo='A';";
        $RsSql = utils\IConnection::execSql($sql);
        $Productos = explode(",", $RsSql["producto_promocion"]);
        $Promo = explode(",", $RsSql["factores_producto"]);
        ?>
        <div style="width: 100%;height: 400px;padding-top: 25px;">
            <table style="width: 80%; margin-left: 10%;background-color: #cbcbcb" title="Detalle de los puntos que se tienen por bonificación" summary="Detalle de los puntos de bonificación">
                <tr style="background-color: #066;color: #cccccc">
                    <th colspan="2"><?= $RsSql["descripcion"] ?></th>
                </tr>
                <tr style="background-color: #066;color: #cccccc">
                    <td colspan="2"><?= ROUND($RsSql["monto_promocion"], 0) ?> Puntos = $1 Peso</td>
                </tr>
                <tr style="background-color: #066;color: #cccccc">
                    <td colspan="2">Promoción basada en <?= $RsSql["tipo_concepto"] === "I" ? "Importe" : "Volumen" ?></td>
                </tr>
                <tr style="background-color: #066;color: #cccccc">
                    <td>Producto</td>
                    <td>Bonificacion</td>
                </tr>
                <?php
                for ($i = 0; $i <= 3; $i++) {
                    ?>
                    <tr>
                        <td><?= $Productos[$i] ?></td>
                        <td><?= $Promo[$i] ?></td>
                    </tr>
                    <?php
                }
                ?>   
                <tr style="background-color: #066;color: #cccccc">
                    <td colspan="2">
                        Esto quiere decir que del producto <?= $Productos[0] ?> por cada <?= $RsSql["tipo_concepto"] === "I" ? "peso" : "litro" ?> acumulara <?= $Promo[0] ?> </td>
                </tr>
            </table>
        </div>

        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>
    </body>
</html>
