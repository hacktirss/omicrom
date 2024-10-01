<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require_once "./services/DiagnosticoService.php";
shell_exec("free -h > /home/omicrom/xml/Memoria.txt");
$mysqli = iconnect();
$request = utils\Request::instance();
$Titulo = "Diagnostico de componentes";

$selectPosiciones = "
            SELECT man.posicion,e.estado,e.venta,e.volumen,LOWER(com.descripcion) producto,e.folio,com.color,
            CASE 
            WHEN e.estado = 'd' THEN 'despachando'
            WHEN e.estado = 'e' THEN 'en espera'
            WHEN e.estado = 'b' THEN 'bloqueado'
            WHEN e.estado = 'i' THEN 'inhabilitado'
            ELSE 'desconctda'
            END accion,
            CASE 
            WHEN e.estado = 'd' THEN 'imgvd.png'
            WHEN e.estado = 'e' THEN 'imgna.png'
            WHEN e.estado = 'b' THEN 'imgrj.png'
            WHEN e.estado = 'i' THEN 'imgrj.png'
            ELSE 'imgng.png'
            END imagen
            FROM man 
            LEFT JOIN estado_posiciones e ON man.posicion = e.posicion
            LEFT JOIN com ON e.producto = com.clavei
            WHERE TRUE AND man.activo = 'Si' 
            ORDER BY man.posicion";

$checkPosiciones = "
            SELECT COUNT(man.id) registros, GROUP_CONCAT(e.posicion) posiciones
            FROM man 
            LEFT JOIN estado_posiciones e ON man.posicion = e.posicion
            WHERE TRUE AND man.activo = 'Si' AND e.estado NOT IN('e','b','d')
            ";

$selectTanques = "
            SELECT tanques.tanque, com.descripcion, tanques.clave_producto,
            tanques.volumen_actual, tanques.fecha_hora_s,agua,
            tanques.capacidad_total, tanques.temperatura, tanques.agua, tanques.fecha_hora_s,
            tanques.volumen_operativo, tanques.altura, com.color
            FROM com, tanques 
            WHERE tanques.clave_producto = com.clave AND com.activo = 'Si' AND tanques.estado = 1
            ORDER BY tanque";

$checkTanques = "
            SELECT COUNT(tanques.id) registros, GROUP_CONCAT(com.clavei) productos
            FROM com, tanques 
            WHERE TRUE AND tanques.clave_producto = com.clave 
            AND com.activo = 'Si' AND tanques.estado = 1
            AND tanques.fecha_hora_s > DATE_SUB(NOW(), INTERVAL 10 MINUTE)";

$posiciones = utils\IConnection::getRowsFromQuery($selectPosiciones);
$tanques = utils\IConnection::getRowsFromQuery($selectTanques);

$siEstado = utils\IConnection::execSql($checkPosiciones);
$siTanques = utils\IConnection::execSql($checkTanques);

$domain_address = "www.google.com";
$response = ping($domain_address);
$connection = (new CheckDevice())->ping($domain_address);

$Id = 8;
$archivo = fopen("/home/omicrom/xml/Memoria.txt", "r");
$HtmlT = "<table border='1'>";
$i = 0;
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom.php"; ?>
        <title><?= $Gcia ?></title>
        <script type="text/javascript" src="bootstrap/controller/utils.js"></script>
        <script>
            $(document).ready(function () {

            });
        </script>
    </head>

    <body>
        <?php BordeSuperior() ?>

        <div id="FormulariosBoots">
            <div class="container no-margin">
                <div class="row">
                    <div class="col-12 align-left">Fecha de diagnostico: <strong><?= date("Y-m-d h:i:s"); ?></strong></div>

                    <div class="col-12 no-margin align-center"><h4>Visor de posiciones: <?= $siEstado["registros"] > 0 ? "<span style='color:red;'>Una o mas posiciones estan desconectadas!</span>" : "Todas las posiciones en linea" ?></h4></div>
                    <div class="col-12 background" style="border: solid 2px #666;padding-top: 10px;border-radius: 8px;">
                        <div class="row">
                            <?php foreach ($posiciones as $row) { ?>
                                <div class="col-3 no-margin" style="vertical-align: middle">
                                    <div class="row">
                                        <div class="col-12 align-left"><strong><?= $row["posicion"] ?></strong> <?= $row["accion"] ?></div>
                                        <div class="col-4 align-center">
                                            <?php
                                            switch ($row["imagen"]) {
                                                case "imgng.png":
                                                    $Color = "#ABB2B9";
                                                    break;
                                                case "imgvd.png":
                                                    $Color = "#7DCEA0";
                                                    break;
                                                case "imgna.png":
                                                    $Color = "#E59866";
                                                    break;
                                                case "imgrj.png":
                                                    $Color = "#D98880";
                                                    break;
                                            }
                                            ?>
                                            <em class="fa-solid fa-gas-pump fa-5x" style="color: <?= $Color ?>;margin-top: 5px;"></em>                                 
                                                   <!--<img src="libnvo/<?= $row["imagen"] ?>" alt=""/>-->                                            
                                        </div>
                                        <div class="col-8 align-center">
                                            <div class="row">
                                                <div class="col-6 align-right">Monto:</div>
                                                <div class="col-6" style="color: #F63;"><?= number_format($row["venta"], 2) ?></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-6 align-right">Litros:</div>
                                                <div class="col-6" style="color: #F63;"><?= number_format($row["volumen"], 3) ?></div>
                                            </div>
                                            <div class="row">
                                                <div class="col-6 align-right">Producto:</div>
                                                <div class="col-6" style="color: #F63;"><?= $row["producto"] ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>                        
                    </div>

                    <div class="col-12 align-center"><h4>Tanques de combustibles: <?= $siTanques["registros"] > 0 ? "Todos los tanques en linea" : "<span style='color:red;'>Uno o mas tanques no estan en linea, no han reportado sus lecturas!</span>" ?></h4></div>
                    <div class="col-12 background" style="border: solid 2px #666;padding-top: 15px;border-radius: 8px;">
                        <div class="row" style="padding: 10px;">
                            <?php foreach ($tanques as $row) { ?>
                                <div class="col-3 no-margin " style="margin-bottom: 10px;">
                                    <div class="row">
                                        <div class="col-8 align-right required"><strong>Tanque:</strong></div>
                                        <div class="col-4"><?= $row["tanque"] ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-8 align-right"><strong>Existencia:</strong></div>
                                        <div class="col-4"><?= $row["volumen_actual"] ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-8 align-right"><strong>Por llenar:</strong></div>
                                        <div class="col-4"><?= $row["volumen_operativo"] ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-8 align-right"><strong>Volumen operativo:</strong></div>
                                        <div class="col-4"><?= $row["volumen_operativo"] ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-8 align-right"><strong>Altura:</strong></div>
                                        <div class="col-4"><?= $row["altura"] ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-8 align-right"><strong>Agua:</strong></div>
                                        <div class="col-4"><?= $row["agua"] ?></div>
                                    </div>
                                </div>
                                <div class="col-3 no-margin" style="vertical-align: middle">
                                    <div class="row">
                                        <div class="col-6 align-left"><strong>* <?= $row["descripcion"] ?></strong></div>
                                    </div>
                                    <div class="row">  
                                        <div class="col-6 align-center">
                                            <?php
                                            $Porcentaje = number_format(($row["volumen_actual"] / $row["volumen_operativo"]) * 100, 0, ".", "");
                                            $Rest = 100 - $Porcentaje;
                                            $Prs = $Porcentaje . "%";
                                            $Rst = $Rest . "%";
                                            ?>
                                            <div class="Tanques" style="background: linear-gradient(#E5E8E8 <?= $Rst ?>, <?= $row["color"] ?> <?= $Rst ?>);">
                                                <table>
                                                    <caption>-</caption>
                                                    <tr>
                                                        <th style="width: 40%;"></th><th style="width: 20%;"></th><th style="width: 40%;"></th>
                                                    </tr>
                                                    <tr><td></td><td></td><td></td></tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-12 align-left"><strong>Lectura:</strong> <?= $row["fecha_hora_s"] ?></div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="col-12 align-center"><h4>Conectividad con Internet: <?= $connection ? "Servicio a Internet disposible!!" : "<span style='color: red'>No hay conectividad!</span>" ?></h4></div>
                    <div class="col-12 background" style="border: solid 2px #666;padding-top: 10px;border-radius: 8px;">
                        <div class="row">
                            <div class="col-12 align-center">           
                                <?php print_r($response[0] . "<br/>") ?>
                                <?php print_r($response[1] . "<br/>") ?>
                                <?php print_r($response[2] . "<br/>") ?>
                                <?php print_r($response[3] . "<br/>") ?>
                                <?php print_r($response[4] . "<br/>") ?>
                                <?php print_r($response[5] . "<br/>") ?>
                            </div>
                        </div>
                    </div>

                    <table title="Diagnostico de UCC (Información del servidor)" summary="Diagnostico de UCC (Información del servidor)"><tr><th class="subtitulos">Diagnostico UCC</th></tr></table>
                    <div class="col-12 background" style="border: solid 2px #666;padding-top: 10px;margin-top: 10px;border-radius: 8px;">
                        <div class="row">
                            <div class="col-12 align-center">
                                <?php
                                $i = 0;
                                while (!feof($archivo)) {
                                    $Filas = nl2br(fgets($archivo));
                                    if ($i == 0) {
                                        $traer = explode("    ", $Filas);
                                        echo"<div style='width:40%;margin-left:29%;font-weight: bold;'>";
                                        foreach ($traer as $T) {
                                            echo $T . " &nbsp;&nbsp;&nbsp;";
                                        }
                                        echo "</div>";
                                        $i++;
                                    } else {
                                        $traer = explode("    ", $Filas);
                                        echo "<div style='width:40%;margin-left:30%;text-align:left;'>";
                                        foreach ($traer as $T) {
                                            echo $T . " &nbsp;&nbsp;&nbsp;";
                                        }
                                        echo "</div>";
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col-12 align-center">           
                                <form name="formDiagnostico" method="post" action="">
                                    <button name="Boton" value="Diagnostico"> Realizar diagnostico</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php BordeSuperiorCerrar() ?>
        <?php PieDePagina() ?>

    </body>
</html>
