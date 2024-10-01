<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();

require "./services/ReportesVentasService.php";

$mysqli = iconnect();
$Titulo = "Control de despachos del $FechaI al $FechaF";
$cSql = $selectDespachos; // Esto es el query
$registro = $mysqli->query($cSql);

if ($FechaF == date('Y-m-d') || $FechaI == date('Y-m-d')) {
    $Msj = "La fecha seleccionada debe de menor a la fecha actual!";
    header("Location: pidedatos.php?busca=criteria=ini&busca=6&Msj=$Msj");
}

$registros = array();
while ($rows = mysqli_fetch_assoc($registro)) {
    $registros[] = $rows;
}

$registro2 = $mysqli->query($selectDespachos2);
error_log($mysqli->error);
while ($rows2 = mysqli_fetch_assoc($registro2)) {
    $registros2[] = $rows2;
}
$data = array("Nombre" => $Titulo, "Reporte" => $Id, "FechaI" => $FechaI, "FechaF" => $FechaF);

if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
}

if ($request->hasAttribute("export_data")) {
    if (!empty($registros)) {
        $filename = "Control de despachos del $FechaI al $FechaF.xls";
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=" . $filename);

        $mostrar_columnas = false;

        foreach ($registros as $reg) {
            if (!$mostrar_columnas) {
                echo implode("\t", array_keys($reg)) . "\n";
                $mostrar_columnas = true;
            }
            echo implode("\t", array_values($reg)) . "\n";
        }
// Agregar una línea en blanco entre las dos tablas
        echo "\n";

// Reiniciar la bandera para mostrar columnas para la segunda tabla ($Ccc)
        $mostrar_columnas = false;
        $NameUsr = "";
        $e = 0;
        $Cnt = 0;
        $SubT = 0;
        $Iva = 0;
        $Ieps = 0;
        $Imp = 0;
        $Desc = 0;
        $Tot = 0;
        foreach ($registros2 as $reg) {
            if ($NameUsr !== $reg["Cliente"] && $e > 0) {
                echo "TOTAL:\t \t" . $Cnt . "\t" . $SubT . "\t" . $Iva . "\t" . $Ieps . "\t" . $Imp . "\t " . $Desc . "\t" . $Tot . "\t\n";
                $Cnt = 0;
                $SubT = 0;
                $Iva = 0;
                $Ieps = 0;
                $Imp = 0;
                $Desc = 0;
                $Tot = 0;
            }
            $NameUsr = $reg["Cliente"];
            if ($e == 0) {
                $NameUsr = $reg["Cliente"];
            }
            if (!$mostrar_columnas) {
                echo implode("\t", array_keys($reg)) . "\n";
                $mostrar_columnas = true;
            }
            echo implode("\t", array_values($reg)) . "\n";
            error_log(" =====  " . implode("\t", array_values($reg)));
            $e++;
            $Cnt += $reg["Cantidad"];
            $SubT += $reg["Subtotal"];
            $Iva += $reg["Iva"];
            $Ieps += $reg["Ieps"];
            $Imp += $reg["Importe"];
            $Desc += $reg["Descuento"];
            $Tot += $reg["Total"];
        }
        echo "TOTAL:\t \t$Cnt\t$SubT\t$Iva\t$Ieps\t$Imp\t-\n";
    } else {
        echo 'No hay datos a exportar';
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports.php'; ?> 
        <title><?= $Gcia ?></title>
        <style>
            #Concentrado{
                width: 100%;
                border-collapse: separate;
                font-family: Arial, Helvetica, sans-serif;
                font-size: 12px;
                color: #55514e;
            }

            #Concentrado > thead > tr.titulo > td{
                background-color: var(--GrisClaro);
                border-bottom: solid 2px white;
            }

            #Concentrado > tbody > tr.titulo > td{
                height: 25px;
                background-color: var(--GrisClaro);
                font-weight: bold;
                text-align: right;
                text-align: center;
            }

        </style>
        <script>
            $(document).ready(function () {
            }]
            });
            });
        </script>
    </head>

    <body>
        <div id="container">
            <?php
            nuevoEncabezado($Titulo);
            $Num = 1;
            ?>
            <div id="Reportes" style="min-height: 20px;"> 
                <table id="Concentrado"  summary="Descarga control de despachos">
                    <tr><th></th></tr>
                </table>
            </div>

            <div class="container">
                <h2 style="text-align: center;" >Click en el bot&oacute;n para exportar datos a un archivo Excel</h2>
                <div class="well-sm col-sm-12">
                    <div style="text-align: center;">
                        <form  method="post">
                            <button type="submit" id="export_data" name='export_data'
                                    value="Export to excel" class="btn btn-info">Exportar a Excel</button>
                        </form>
                    </div>
                </div>
                <div style="height:30px;"></div>

            </div>
    </body>
</html>
