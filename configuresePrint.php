<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("importeletras.php");
include_once ("data/TanqueDAO.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$usuarioSesion = getSessionUsuario();

$busca = $request->getAttribute("busca");
$ciaDAO = new CiaDAO();
$ciaVO = $ciaDAO->retrieve(1);

$sql = "SELECT * FROM authuser WHERE id = $busca";
$Cpo = $mysqli->query($sql)->fetch_array();

$months = array();
setlocale(LC_TIME, 'es_MX.UTF-8');
for ($m = 1; $m <= 12; $m++) {
    $months[cZeros($m, 2, "LEFT")] = strftime("%B", mktime(0, 0, 0, $m, 12));
}
$cFecha = $ciaVO->getColonia() . " " . $ciaVO->getCiudad() . " a " . date("d") . " de " . $months[date("m")] . " de " . date("Y");

$Titulo = "Control de acceso a usuarios";
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports_print.php'; ?> 
        <title><?= $Gcia ?></title>
        <style>
            @page {
                size: A4 /*landscape*/;
            }
        </style>
        <script type="text/javascript">
            function Export2Doc(element, filename = '') {

                var preHtml = "<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'><head><meta charset='utf-8'><title>Export HTML To Doc</title></head><body>";
                var postHtml = "</body></html>";

                var html = preHtml + document.getElementById(element).innerHTML + postHtml;

                var blob = new Blob(['\ufeff', html], {
                    type: 'application/msword'
                });

                var url = 'data:application/vnd.ms-word;charset=utf-8,' + encodeURIComponent(html);


                filename = filename ? filename + '.doc' : 'document.doc';


                var downloadLink = document.createElement("a");

                document.body.appendChild(downloadLink);

                if (navigator.msSaveOrOpenBlob) {
                    navigator.msSaveOrOpenBlob(blob, filename);
                } else {

                    downloadLink.href = url;
                    downloadLink.download = filename;
                    downloadLink.click();
                }

                document.body.removeChild(downloadLink);
            }
        </script>

    </head>

    <!-- Set "A5", "A4" or "A3" for class name -->
    <!-- Set also "landscape" if you need -->
    <body class="A5">
        <div class="iconos">
            <table aria-hidden="true">
                <tr>
                    <td style="text-align: left"><?= $Titulo ?></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="text-align: center">
                        <em onclick="Export2Doc('exportContent', 'avisoProfeco');" class='icon fa fa-lg fa-file-word-o' title="Descargar tipo Word" style="margin-right: 20px;"></em>
                        <em onclick="print();" class='icon fa fa-lg fa-print' aria-hidden="true" title="Descargar tipo PDF"></em>
                    </td>
                </tr>
            </table>
        </div>
        <!-- Each sheet element should have the class "sheet" -->
        <!-- "padding-**mm" is optional: you can set 10, 15, 20 or 25 -->
        <div class="sheet padding-10mm">

            <?php nuevoEncabezadoPrint(null) ?>
            <div id="exportContent">
                <table style="width: 100%;" aria-hidden="true">
                    <tr>
                        <td style="font-weight: bold;text-align: right;"><em><?= date("Y-m-d") ?>; Altas, Bajas y Cambios de Accesos</em></td>
                    </tr>
                    <tr>
                        <td>
                            <table style="width: 100%;" title="Descripcion del usuario" summary="Descripcion del usuario">
                                <tr>
                                    <th>
                                        <strong style="font-size: 16px;">Nombre :</strong><br>
                                        <?= $Cpo["name"] ?>
                                    </th>
                                    <th>
                                        <strong style="font-size: 16px;">Perfil :</strong><br>
                                        <?= $Cpo["team"] ?>
                                    </th>
                                    <th>
                                        <strong style="font-size: 16px;">Estado :</strong><br>
                                        <?= $Cpo["status"] ?>
                                    </th>
                                    <th>
                                        <strong style="font-size: 16px;">Ultimo acceso :</strong><br>
                                        <?= $Cpo["lastactivity"] ?>
                                    </th>
                                    <th>
                                        <strong style="font-size: 16px;">Ultima modificación :</strong><br>
                                        <?= $Cpo["fecha_modificacion"] ?>
                                    </th>
                                </tr>
                                <tr>
                                    <td style="height: 60px;" colspan="5"></td>
                                </tr>
                                <tr><td colspan="5" style="text-align: center">___________________________________________</td></tr>
                                <tr><td colspan="5" style="text-align: center;font-weight: bold;">Nombre y Firma</td></tr>
                            </table>
                        </td>
                    </tr>
                </table>

            </div>
        </div>
    </body>
    <style>
        .StyleTable{
            width: 100%;
            border: 1px solid #606c84;
            border-radius: 0px 0px 20px 20px;
        }
        .StyleTable tr:nth-child(2){
            background-color: #CCD1D1;
        }
        .StyleTable tr:nth-child(1){
            background-color: #CCD1D1;
        }

    </style>
</html>     

