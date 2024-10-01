<?php

#Librerias
session_start();

include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$cv = $request->getAttribute("cv");
$FechaI = $request->getAttribute("FechaI");
$FechaF = $request->getAttribute("FechaF");

try {

    if ($cv == 2) {
        $dir = "/controlvolumetrico/";

        $selectLogs = "
        SELECT * FROM logenvios20 
        WHERE fecha_informacion BETWEEN DATE('$FechaI') AND DATE('$FechaF') 
        ORDER BY fecha_informacion,generacion";

// echo $selectLogs;

        $registros = utils\IConnection::getRowsFromQuery($selectLogs);

        $Cpo = leerConvol($request->getAttribute("cv"));
        if (file_exists($dir)) {

            if (count($Cpo) <= 0) {
                $Msj = 'No tienes archivos en este directorio: ' . count($Cpo) . ' elementos. <br>Verifica tu version.';
                header("Location: exportacv.php?Msj=$Msj");
            }

            $file = tempnam("tmp", "zip");
            $zip = new \ZipArchive();
            if ($zip->open($file, \ZipArchive::OVERWRITE)) {


//echo "File: ".$file."---\n<br/>";

                foreach ($registros as $rg) {
                    //echo "Fecha: ".$rg[fecha_informacion].", Nombre: ".$rg[nombrearchivo];
                    $vFileName = $dir . $rg[nombrearchivo] . ".zip";
                    //echo $vFileName;
                    $vFname = $rg[fecha_informacion];

                    if (file_exists($vFileName)) {
                        //echo "Adding File Entry: ".$vFname;
                        $zip->addFile($vFileName, $vFname . ".zip");
                    }
                }
//echo "numfiles: " . $zip->numFiles . "\n";
//echo "status:" . $zip->status . "\n";
                $zip->close();

                if (file_exists($file)) {
//echo "Archivo Temporal Existe: ".$file;
                }


                header('Content-Description: File Transfer');
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename=cv_v2.zip');
                header('Expires: 0');
                header("Content-Length: " . filesize($file));
                error_log("FileSize: " . filesize($file));
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                readfile($file);

                unlink($file);
            }
            exit;

            /*
              if ($res === TRUE) {

              $pos = 0;
              while ($pos < count($Cpo)) {
              if (obtenerFechaCV2($Cpo[$pos]) >= $FechaI and obtenerFechaCV2($Cpo[$pos]) <= $FechaF) {
              $temp[$pos] = $Cpo[$pos];
              $vFname = $Cpo[$pos];
              $vFileName = $dir . $Cpo[$pos];
              $fp = fopen($vFileName, 'w');
              fwrite($fp, $Cpo[$pos]);
              fclose($fp);
              $zip->addFile($vFileName, $vFname);
              }
              $pos++;
              }

              $zip->close();
              if (count($temp) <= 0) {
              $Msj = 'No tienes registros en este rango de fechas: ' . count($temp) . ' elementos';
              header("Location: exportacv.php?Msj=$Msj");
              }
              $pos = 0;
              while ($pos < count($temp)) {
              $vFileName = "/tmp/" . $temp[$pos];
              unlink($vFileName);
              $pos++;
              }

              } else {
              $Msj = 'No se pudo realizar la descarga';
              header("Location: exportacv.php?Msj=$Msj");
              }
             */
        } else {
            $Msj = 'No existe el directorio y no cuenta con la version seleccionada';
            header("Location: exportacv.php?Msj=$Msj");
        }
    } elseif ($cv == 1) {
        $dir = "/home/omicrom/cv/convol/";

        $Cpo = leerConvol($cv);
        if (file_exists($dir)) {
            if (count($Cpo) <= 0) {
                $Msj = 'No tienes archivos en este directorio: ' . count($Cpo) . 'elementos. <br>Verifica tu version.';
                header("Location: exportacv.php?Msj=$Msj");
            }

            $zip = new ZipArchive;
            $path = '/tmp/';
            $varX = rand(1, 1000);
            $file = $path . "cv_v1" . $varX . ".zip";
            $res = $zip->open($file, ZipArchive::OVERWRITE);

            if ($res === TRUE) {

                $pos = 0;
                while ($pos < count($Cpo)) {
                    if (obtenerFechaCV1($Cpo[$pos]) >= $FechaI and obtenerFechaCV1($Cpo[$pos]) <= $FechaF) {
                        $temp[$pos] = $Cpo[$pos];
                        $vFname = $Cpo[$pos];
                        $vFileName = $dir . $Cpo[$pos];
                        $fp = fopen($vFileName, 'w');
                        fwrite($fp, $Cpo[$pos]);
                        fclose($fp);
                        $zip->addFile($vFileName, $vFname);
                    }
                    $pos++;
                }

                $zip->close();
                if (count($temp) <= 0) {
                    $Msj = 'No tienes registros en este rango de fechas: ' . count($temp) . ' elementos';
                    header("Location: exportacv.php?Msj=$Msj");
                }

                $pos = 0;
                while ($pos < count($temp)) {
                    $vFileName = "/tmp/" . $temp[$pos];
                    unlink($vFileName);
                    $pos++;
                }
            } else {
                $Msj = 'No se pudo realizar la descarga';
                header("Location: exportacv.php?Msj=$Msj");
            }

            if (file_exists($file)) {
                header('Content-Description: File Transfer');
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename=cv_v1.zip');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                readfile($file);
            }

            exit;
        } else {
            $Msj = 'No cuenta con la version seleccionada y no existe el directorio';
            header("Location: exportacv.php?Msj=$Msj");
        }
    }
} catch (Exception $ex) {
    error_log($ex);
}

function leerConvol($version, $file = null) {
    $i = 0;
    if ($version == 1) {
        $directorio = opendir("/home/omicrom/cv/convol");
    } else {
        $directorio = opendir("/controlvolumetrico");
    }
    if (!is_null(file)) {
        while ($archivo = readdir($directorio)) {
            if (!is_dir($archivo)) {
                $lista[$i] = $archivo;
                $i++;
            }
        }
    } else {
        $archivo = $directorio . "/" . $file;
        $lista[0] = $archivo;
    }
    return $lista;
}

function obtenerFechaCV1($nombre) {
    //ejemplo. 0000113131E09011ADI||20120722.200000 despues de las dos barra es posicion -15
    $year = substr($nombre, -15, 4);
    $mouth = substr($nombre, -11, 2);
    $day = substr($nombre, -9, 2);
    $fechaObtenida = $year . '-' . $mouth . '-' . $day;
    //echo $fechaObtenida.'<br>';
    return $fechaObtenida;
}

function obtenerFechaCV2($nombre) {
    //ejemplo. 0000113131E09011||20150116.233950ICH0504114C3.zip despues de las dos barra es posicion -31
    $year = substr($nombre, -31, 4);
    $mouth = substr($nombre, -27, 2);
    $day = substr($nombre, -25, 2);
    $fechaObtenida = $year . '-' . $mouth . '-' . $day;
    //echo $fechaObtenida.'<br>';
    return $fechaObtenida;
}
