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

if ($Corte == $islaVO->getCorte()) {
    $sql = "SELECT man.isla_pos, GROUP_CONCAT(DISTINCT man.posicion ORDER BY man.posicion ASC) posicion , 
            GROUP_CONCAT(DISTINCT man.despachador ORDER BY man.despachador ASC) despachador,
            GROUP_CONCAT(DISTINCT ven.alias ORDER BY ven.alias ASC) alias
            FROM man, ven 
            WHERE man.despachador = ven.id AND man.activo = 'Si' AND ven.activo = 'Si'
            GROUP BY man.isla_pos;";
} else {
    $sql = "SELECT man.isla_pos, 
            IFNULL(GROUP_CONCAT(DISTINCT rm.posicion ORDER BY rm.posicion ASC), GROUP_CONCAT(DISTINCT man.posicion ORDER BY man.posicion ASC) )posicion , 
            IFNULL(GROUP_CONCAT(DISTINCT rm.vendedor ORDER BY rm.vendedor ASC), GROUP_CONCAT(DISTINCT man.despachador ORDER BY man.despachador ASC)) despachador,
            IFNULL(GROUP_CONCAT(DISTINCT ven.alias), 'NO DISPONIBLE') alias
            FROM man
            LEFT JOIN rm ON man.posicion = rm.posicion AND rm.corte = $Corte
            LEFT JOIN ven ON rm.vendedor = ven.id
            WHERE man.activo = 'Si'
            GROUP BY man.isla_pos;";
}

$result = $mysqli->query($sql);

$vendedores = array();
$selectVendedor = "SELECT ven.id,CONCAT(LPAD(ven.id,2,0), ' | ', ven.alias) alias FROM ven WHERE ven.activo = 'Si' AND ven.id >=50 ORDER BY ven.alias";
$DesA = $mysqli->query($selectVendedor);
while ($Des = $DesA->fetch_array()) {
    $vendedores[$Des["id"]] = $Des["alias"];
}
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
                <td style="width: 25%;" onclick="redirigir('mdepositost.php')">Saldos x despachador</td>
                <td style="width: 25%;background-color: #F63;color: white;">Vendedores x posicion</td>
            </tr> 
        </table>



        <div align="center" class="texto_tablas"><strong>Despachadores asignados a islas para el corte: <?= $Corte ?></strong></div>

        <div id="TablaDatos">
            <table aria-hidden="true">

                <tr>
                    <td class="fondoNaranja" align="center">Isla</td>
                    <td class="fondoNaranja" align="center">Despachador asignado</td>
                    <td class="fondoNaranja" align="center">Cambiar despachador a</td>
                    <td class="fondoNaranja" align="center">Regresar valores a la isla por default</td>
                </tr>

                <?php
                while ($rg = $result->fetch_array()) {
                    $Vendedor = "Vendedor" . $rg["posicion"];
                    ?>
                    <form name="<?= $Vendedor ?>" method="post" action="">
                        <tr>
                            <td align="center"><?= $rg[isla_pos] ?></td>
                            <td><?= $rg["alias"] ?></td>
                            <td align="center">
                                <select class="texto_tablas" name="Despachador">
                                    <option value="" selected="selected" disabled="">---SELECCIONAR DESPACHADOR---</option>
                                    <?php
                                    foreach ($vendedores as $key => $value) {
                                        echo "<option value='$key'>$value</option>";
                                    }
                                    ?>
                                </select>
                                <button class="nombre_cliente" name="BotonColectas" value="Reasignar" >Enviar</button>
                            </td>
                            <td align="center"><button class="nombre_cliente" name="BotonColectas" value="Revertir" >Aceptar</button></td>
                        </tr>
                        <input type="hidden" name="Isla" value="<?= $rg[isla_pos] ?>">
                    </form>
                    <?php
                }
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