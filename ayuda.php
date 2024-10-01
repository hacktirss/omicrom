<?php
#Librerias
session_start();
header("X-Frame-Options: SAMEORIGIN");

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();

if ($request->hasAttribute("Id")) {
    utils\HTTPUtils::setSessionValue("Id", $request->getAttribute("Id"));
}
$Id = utils\HTTPUtils::getSessionValue("Id");

$selectQty = "SELECT ayuda FROM qrys WHERE id = '$Id'";
$Qry = utils\IConnection::execSql($selectQty);
$page = $Qry["ayuda"];
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {

                $(window).resize(function () {
                    var height = $(window).height();
                    //console.log('Screen height is currently: ' + height + 'px.');
                    $("#FrameManual").height(height - 120);
                });
            });
        </script>
        <style>
            .manual{
                width: 100%;
                margin-top: 50px;
                margin-left: auto;
                margin-right: auto;
                text-align: center;
            }
            .manual a{
                font-size: 50px;
                color: #006633;
            }
            .manual a.hover{
                color: #FF6633;
            }
        </style>
    </head>

    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo) ?>
            <object id="FrameManual" data="manual/Manual_Usuario.pdf#page=<?= $page ?>" type="application/pdf" style="width:100%; height: 100%; min-height:500px;">
                <div class="manual">
                    <p><a href="manual/Manual_Usuario.pdf#page=<?= $page ?>"> Manual de Usuario <i class="icon fa fa-lg fa-file-pdf-o" style="color: red;" aria-hidden="true"></i></a></p>
                    <p>Da click en el titulo para visuaizarlo <i class="icon fa fa-lg fa-hand-o-up" aria-hidden="true" ></i></p>
                </div>
            </object>
        </div>
    </body>
</html>
