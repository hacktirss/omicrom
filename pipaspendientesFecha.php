<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("data/CargasDAO.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$mysqli = iconnect();
$session = new OmicromSession("", "");

$busca = $session->getSessionAttribute("criteria");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$CargasDAO = new CargasDAO();
$CargaVO = $CargasDAO->retrieve($request->getAttribute("busca"));
if ($request->getAttribute("Boton") === "Actualizar") {
    $CargaVO->setVol_doc($request->getAttribute("VolumenDocumentado"));
    if ($CargasDAO->update($CargaVO)) {
        $Msj = "Registro actualizado con exito!";
        header("Location: pipaspendientesFecha.php?busca=" . $CargaVO->getId() . "&Msj=$Msj");
    }
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
        </script>
    </head>
    <body>
        <form name="form0" id="form0" method="post" action="">
            <div class='texto_tablas' style="padding: 10px;margin: 15px;border: 2px solid #5DADE2;border-radius: 25px;">
                <div>Carga id. <?= $CargaVO->getId() ?></div>
                Volumen Documentado :
                <input type="text" class='texto_tablas' name="VolumenDocumentado" value="<?= $CargaVO->getVol_doc() ?>" >
                <input type="hidden" name="busca" value="<?= $CargaVO->getId() ?>">
                <div style="margin-left: 45%;margin-top: 20px;"><input class='texto_tablas' type="submit" name="Boton" value="Actualizar"></div>
            </div>
        </form>
    </body>
</html>
