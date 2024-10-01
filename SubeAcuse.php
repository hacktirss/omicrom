<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

require "./services/ReportesVentasService.php";
require "./services/ComandosService.php";

$request = utils\HTTPUtils::getRequest();
$usuarioSesion = getSessionUsuario();

if ($request->hasAttribute("busca")) {
    utils\HTTPUtils::setSessionValue("busca", $request->getAttribute("busca"));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue("busca", $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue("busca");
$op = $request->getAttribute("op");

$select = "SELECT * FROM omicrom.log_envios_sat WHERE id = $busca";

$Cpo = utils\IConnection::execSql($select);
$Fecha = date("Y-m-d H:i:s");
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require_once './config_omicrom_reports.php'; ?>
        <link rel="stylesheet" type="text/css" href="dropzone/min/dropzone.min.css">
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {

            });
        </script>
        <style>
            html,body{
                min-width: 350px;
            }
        </style>
    </head>
    <body>
        <div id="Controles">
            <div class="Titulo" style="height: 35px;">Subida de archivos de aceptacion</div>
            <div class="Subtitulo">
                <strong>Id:</strong> <?= $busca ?> 
                <strong style="margin-left: 10px;">Fecha de informacion:</strong> <?= $Cpo["fecha_informacion"] ?>
                <strong style="margin-left: 10px;">Periodo:</strong> <?= $Cpo["periodicidad"] ?>
            </div>
            <table aria-hidden="true" style="margin-top: 30px;">
                <tbody>
                    <tr>
                        <td>
                            <form class="dropzone" id="myDrop" enctype="multipart/form-data">
                                <div class="fallback">
                                    <input type="file" name="file" id="myId" multiple>
                                </div>
                            </form>
                        </td>
                    </tr>
                </tbody>
            </table>
            <script src="dropzone/min/dropzone.min.js"></script>
            <?php
            $Mes = explode("-", $Cpo["fecha_informacion"]);
            ?>
            <script type="text/javascript">
            $(document).ready(function () {
                Swal.fire({
                    title: "¿Seguro de subir el archivo de aceptacion?",
                    background: "#E9E9E9",
                    showConfirmButton: true,
                    showCancelButton: true,
                    backdrop: 'swal2-backdrop-show'
                }).then((result) => {
                    if (!result.isConfirmed) {
                        var ventana = window.self;
                        ventana.opener = window.self;
                        ventana.close();
                    }
                    //                    
                });


                Dropzone.prototype.defaultOptions.dictDefaultMessage = "Arrastrar o dar click para subir archivo XML";
                Dropzone.options.myDrop = {
                    url: "uploadAcuse.php?busca=<?= $busca ?>&Mes=<?= $Mes[1] ?>",
                    uploadMultiple: true,
                    maxFileSize: 3,
                    acceptedFiles: ".pdf",
                    success: function (file, response) {
                        console.log(response);
                        if (response == 1) {
                            Swal.fire({
                                title: "Archivo guardado con exito!",
                                background: "#E9E9E9",
                                showConfirmButton: true,
                                backdrop: 'swal2-backdrop-show'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    var ventana = window.self;
                                    ventana.opener = window.self;
                                    ventana.close();
                                }
                                //                    
                            });
                        } else if (response == 2) {
                            Swal.fire({
                                title: "Archivo contiene otro mes al seleccionado, favor de verificar!",
                                background: "#F1948A",
                                icon: "warning",
                                showConfirmButton: true,
                                backdrop: 'swal2-backdrop-show'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    var ventana = window.self;
                                    ventana.opener = window.self;
                                    ventana.close();
                                }
                                //                    
                            });
                        } else {
                            Swal.fire({
                                title: "Archivo contiene un rechazo favor de verificar!",
                                background: "#F1948A",
                                icon: "warning",
                                showConfirmButton: true,
                                backdrop: 'swal2-backdrop-show'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    var ventana = window.self;
                                    ventana.opener = window.self;
                                    ventana.close();
                                }
                                //                    
                            });
                        }
                    }
                }
            });
            </script>
        </div>
    </body>
</html>


