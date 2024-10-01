<?php

include_once ('data/IslaDAO.php');

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$usuarioSesion = getSessionUsuario();
$Return = "logenvios.php?";

$islaDAO = new IslaDAO();
$ciaDAO = new CiaDAO();

if ($request->hasAttribute("Boton") && $request->getAttribute("Boton") !== utils\Messages::OP_NO_OPERATION_VALID) {
    $Msj = utils\Messages::MESSAGE_NO_OPERATION;

    //error_log(print_r($bancosVO, TRUE));
    try {
        if ($request->getAttribute("Boton") === utils\Messages::OP_UPDATE) {
            $Status = $request->getAttribute("Status");
            $busca = $request->getAttribute("busca");
            $updateLogs = "UPDATE logenvios20 SET  codigodeenvio = '$Status' WHERE id = '$busca' LIMIT 1";

            if ($mysqli->query($updateLogs)) {
                $Msj = utils\Messages::RESPONSE_VALID_UPDATE;
            } else {
                $Msj = utils\Messages::RESPONSE_ERROR;
            }
        } elseif ($request->getAttribute("Boton") === "Generar") {
            if ($request->getAttribute("Tipo") === "2") {
                $year = $sanitize->sanitizeString("Anio");
                $month = $sanitize->sanitizeString("Mes");
                if($sanitize->sanitizeString("Periodo") === "M"){
                    $FechaI = $year . "-" . $month . "-" . lastDayPerMonth($year, $month);
                    $FechaF = $year . "-" . $month . "-" . lastDayPerMonth($year, $month);
                } else {
                    $FechaI = $sanitize->sanitizeString("FechaI");
                    $FechaF = $sanitize->sanitizeString("FechaF");
                }
                VolumetricosServices::generaArchivosSat($FechaI, $FechaF, $sanitize->sanitizeString("Periodo"), $sanitize->sanitizeString("Formato"));
            } else {
                VolumetricosServices::generaPeriodo($sanitize->sanitizeString("FechaI"), $sanitize->sanitizeString("FechaF"));
            }
            $Msj = utils\Messages::MESSAGE_DEFAULT;
        }

        $Return .= "&Msj=" . urlencode($Msj);
    } catch (Exception $ex) {
        error_log("Error en logenvios: " . $ex);
    } finally {
        header("Location: $Return");
    }
}


if ($request->hasAttribute("archivo")) {
    $archivo = $request->getAttribute("archivo");

    $file = "/controlvolumetrico/$archivo";
    if ($request->getAttribute("Tipo") === "2") {
        $file = "/controlvolumetrico/sat/$archivo";
    }
    error_log($file);
    if (!empty($archivo) && file_exists($file)) {
        header("Content-Description: File Transfer");
        header("Content-Type: application/zip");
        header("Content-Disposition: attachment; filename=$archivo");
        header("Expires: 0");
        header("Cache-Control: must-revalidate");
        header("Pragma: public");
        readfile($file);
        exit;
    } else {
        header("Location: logenvios.php?Msj=El archivo [$archivo] no fue encontrado!");
    }
}

if ($request->hasAttribute("send")) {
    $cId = $sanitize->sanitizeInt("cId");
    if ($request->getAttribute("send") === "Si") {
        VolumetricosServices::sendFile($cId);
        header("Location: logenvios.php?Msj=Archivo Enviado");
    }
}

/**
 * Description of VolumetricosServices
 *
 * @author lino
 */
class VolumetricosServices {

    public static function sendFile($idArchivo) {
        $output = "/tmp/respuesta_cv.out";
        error_log("Enviando Archivo de Control Volumetrico con ID: " . $idArchivo);
        $command = "sudo java -cp /home/omicrom/cv/GeneradorArchivosPEMEX/GeneraArchivosCV12.jar com.mx.detisa.swap.PemexEnviaArchivos $idArchivo > $output 2>&1 &";
        error_log($command);
        exec($command);
    }

    public static function generaPeriodo($fechaInicial, $fechaFinal) {
        $output = "/tmp/respuesta_cv.out";
        error_log("Enviando comando para la generacion de los archivos de Control Volumetrico (" . $fechaInicial . " , " . $fechaFinal . ")");
        $command = "sudo sh -x /home/omicrom/cv/GeneradorArchivosPEMEX/cv_genera_periodo.sh  $fechaInicial $fechaFinal > $output 2>&1 &";
        error_log($command);
        exec($command);
    }

    public static function generaArchivosSat($fechaInicial, $fechaFinal, $periodicidad = "M", $formato = "XML") {
        $output = "/tmp/respuesta_cv.out";
        error_log("Enviando comando para la generacion de los archivos de Control Volumetrico ($periodicidad, $formato, $fechaInicial, $fechaFinal)");
        $command = "sudo sh -x /home/omicrom/cv/GeneradorArchivosPEMEX/genera_reporte_cv.sh $periodicidad $formato $fechaInicial $fechaFinal > $output 2>&1 &";
        error_log($command);
        exec($command);
    }

}

function leer_archivo($archivo) {
    $Archivo = "/controlvolumetrico/$archivo.zip";

    exec("sudo rm  /tmp/*");

    $zip = new ZipArchive;
    $res = $zip->open($Archivo);
    if ($res === TRUE) {
        $zip->extractTo('/tmp/');
        $zip->close();
    }

    $ArchivoTmp = "/tmp/$archivo.XML";
    if ($fp = fopen($ArchivoTmp, "r")) {
        $data = fread($fp, filesize($ArchivoTmp));
    } else {
        $data = "Existe un error en el archivo, no lo puedo leer o no existe";
    }

    return $data;
}
