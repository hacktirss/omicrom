<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$mysqli = iconnect();

require_once './services/VolumetricosServices.php';

$Titulo = "Envio de archivos";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
    utils\HTTPUtils::setSessionValue("Tipo", $request->getAttribute("Tipo"));
} 
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);
$tipo = utils\HTTPUtils::getSessionValue("Tipo");

$select = "SELECT fecha_informacion informacion, generacion, envio, codigodeenvio codigo, checksum, resp_pemex, nombrearchivo archivo FROM logenvios20 WHERE id = '$busca'";
if($tipo == 2){
    $select = "SELECT fecha_informacion informacion, fecha_generacion generacion, fecha_envio envio, codigo_envio codigo, '' checksum, '' resp_pemex, nombre_archivo archivo FROM log_envios_sat WHERE id = '$busca'";
}

$CpoA = $mysqli->query($select);
$Cpo = $CpoA->fetch_array();

?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#busca").val("<?= $busca ?>");
            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;width: 90px;" class="nombre_cliente">
                    <a href="logenvios.php"><div class="RegresarCss " alt="Flecha regresar" style="">Regresar</div></a>
                </td>
                <td style="vertical-align: top;">
                    <form name="form1" id="form1" method="post" action="">

                        <?php
                        cTable("99%", "0");

                        cInput("Fecha informacion: ", "Text", "8", "Fechaz", "right", $Cpo["informacion"], "10", true, true, "");
                        cInput("Fecha generacion: ", "Text", "8", "Fechaz", "right", $Cpo["generacion"], "10", true, true, "");
                        cInput("Fecha de envio: ", "Text", "15", "EnvioX", "right", $Cpo["envio"], "20", true, true, '');
                        cInput("Codigo de envio: ", "Text", "15", "ConfirmadoX", "right", $Cpo["codigo"], "20", true, true, '');
                        cInput("Checksum: ", "Text", "25", "ChecksumX", "right", $Cpo["checksum"], "20", true, true, '');
                        cInput("Respuesta de pemex: ", "Text", "10", "Resp_pemexC", "right", $Cpo["resp_pemex"], "10", true, true, '');
                        cInput("Nombre del archivo: ", "Text", "10", "NombreX", "right", $Cpo["archivo"], "10", true, true, '');
                        cTableCie();
                        ?>
                        <input type="hidden" name="busca" id="busca">
                    </form>

                </td>
            <tr>            
        </table>

        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>
    </body>
</html>