<?php
session_start();

include_once ("auth.php");
include_once ("authconfig.php");
include_once ("check.php");
include_once ("libnvo/lib.php");

include_once ("data/MensajesDAO.php");

use com\softcoatl\utils as utils;

$connection = iconnect();

$request = utils\HTTPUtils::getRequest();
error_log(print_r($request, TRUE));
$busca = 0;
$Close = true;
$ShowHeader = true;

if ($request->hasAttribute("busca")) {
    $busca = $request->getAttribute("busca");
}
if ($request->hasAttribute("Close")) {
    $Close = false;
}
if ($request->hasAttribute("showheader")) {
    $ShowHeader = $request->getAttribute("showheader");
}

$sql = "SELECT * FROM msj WHERE tipo = '" . TipoMensaje::SIN_LEER . "' AND DATE_ADD(fecha,INTERVAL vigencia DAY) >= CURRENT_DATE()";
if ($busca > 0) {
    $sql = "SELECT * FROM msj WHERE id = '$busca';";
}

$mensajes = $connection->query($sql);

$count = $mensajes->num_rows;
if ($request->hasAttribute("op")) {
    if ($request->getAttribute("op") === "marcar") {
        $updateMensaje = "UPDATE msj SET tipo='" . TipoMensaje::LEIDO . "' WHERE id='" . $request->getAttribute("id") . "'";
        if (!($connection->query($updateMensaje))) {
            error_log($connection->error);
        }
        header("Location: vermensajes.php?Close=1");
    }
}
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                var count = "<?= $count ?>";
                if(window.opener != null && !window.opener.closed){
                    console.log("Parent open");
                   console.log(window.opener.document.popupWindow);
                }
                
                if (count === "0") {
                    console.log("count: " + count);
                    console.log(opener.popupWindow);
                    //opener.document.body.removeChild();
                    window.close();
                }
            });
        </script>

    </head>

    <body>
        <div id="container">
            <?php nuevoEncabezado($Titulo, $Close, $ShowHeader) ?>

            <?php
            while ($row = $mensajes->fetch_array()) {
                $busca = $row["id"];
                ?>
                <table style="width: 100%;" aria-hidden="true">
                    <?php if ($busca != 0) { ?> 
                        <tr class="texto_tablas">
                            <td colspan="2" style="text-align: left;">
                                <a class="seleccionar" href="vermensajes.php?op=marcar&id=<?= $row["id"] ?>" title="">
                                    <i class="icon fa fa-envelope-open" aria-hidden="true"></i> Marcar como leido
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr class="texto_tablas">
                        <td rowspan="3" style="width: 50px;font-size: 30px;"><i class="icon fa fa-users" aria-hidden="true"></i></td>
                        <td><?= $row["fecha"] . " " . $row["hora"] ?></td>
                    </tr>
                    <tr class="texto_tablas">
                        <td><strong>Asunto: </strong> <?= $row["titulo"] ?></td>
                    </tr>
                    <tr class="texto_tablas">
                        <td><strong>De: </strong><?= $row["de"] === "array" ? "Soporte Omicrom" : $row["de"] ?></td>
                    </tr>
                    <tr class="texto_tablas">
                        <td colspan="2"><strong>Para: </strong>Estacion de servicio</td>
                    </tr>
                    <tr class="texto_tablas">
                        <td colspan="2" style="padding-top: 20px;border: 1px solid #DADADA">
                            <div style="width: 100%">
                                <?= html_entity_decode($row["nota"]) ?>
                            </div>
                        </td>
                    </tr>
                </table>

                <?php
            }
            ?>
        </div>

        <div id="footer">
            <?php topePagina(); ?>
        </div>

    </body>

</html>
