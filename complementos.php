<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("CFDIComboBoxes.php");
include_once ("data/ComplementoDAO.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$mysqli = iconnect();

$id = $request->getAttribute("id");
$complemento = $request->getAttribute("complemento");

if ($complemento == 1) {
    $tproceso = $request->getAttribute("tproceso");
    $ambito = $request->getAttribute("ambito");
    $tcomite = $request->getAttribute("tcomite");
    $idContabilidad = $request->getAttribute("idContabilidad");
    $entidad = $request->getAttribute("entidad");


    $dao = new ComplementoDAO();

    if ($request->getAttribute("Boton") === "Guardar Datos") {
        $dao->setAtributo(1, 2, $id, $tproceso);
        $dao->setAtributo(1, 6, $id, $ambito);
        $dao->setAtributo(1, 3, $id, $tcomite);
        $dao->setAtributo(1, 4, $id, $idContabilidad);
        $dao->setAtributo(1, 5, $id, $entidad);
    }

    $complementoValores = $dao->getComplemento(1, $id);
}
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom_reports.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                var complemento = "<?= $complemento ?>";
                var boton = "<?= $request->getAttribute("Boton") ?>";
                $('input').each(function () {
                    $(this).attr('name', $(this).attr('id'));
                });
                $('select').each(function () {
                    $(this).attr('name', $(this).attr('id'));
                });
                if (complemento === "1") {
                    $('#tproceso').val('<?= $complementoValores['TipoProceso'] ?>');
                    $('#ambito').val('<?= $complementoValores['Ambito'] ?>');
                    $('#tcomite').val('<?= $complementoValores['TipoComite'] ?>');
                    $('#idContabilidad').val('<?= $complementoValores['IdContabilidad'] ?>');
                    $('#entidad').val('<?= $complementoValores['ClaveEntidad'] ?>');

                    if (boton === "Guardar Datos") {
                        window.close();
                    }
                }
            });
        </script>
    </head>
    <body>

        <div id="container">
            <?php nuevoEncabezado($Titulo) ?>
            <?php if ($complemento == 1) { ?>
                <form name="formINE">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <input type="hidden" name="complemento" value="<?= $complemento ?>">
                    <table style="width: 60%; height: 60%; margin-top: auto; margin-bottom: auto; margin-left: auto; margin-right: auto; text-align: center; padding: 0px; border-collapse: collapse; border: 1px solid #066; background-color: #e1e1e1;" aria-hidden="true">
                        <caption><h2>Complemento INE Factura <?= $id ?></h2></caption>
                        <tr style="height: 25px;">
                            <td align="right">* Tipo de Proceso:&nbsp;</td>
                            <td align="left">&nbsp;<?php ComboboxINEProceso::generate("tproceso") ?></td>
                        </tr>
                        <tr style="height: 25px;">
                            <td align="right">* Ambito:&nbsp;</td>
                            <td align="left">&nbsp;<?php ComboboxINEAmbito::generate("ambito") ?></td>
                        </tr>
                        <tr style="height: 25px;">
                            <td align="right">* Tipo de Comité:&nbsp;</td>
                            <td align="left">&nbsp;<?php ComboboxINEComite::generate("tcomite") ?></td>
                        </tr>
                        <tr style="height: 25px;">
                            <td align="right">* Clave de Contabilidad:&nbsp;</td>
                            <td align="left">&nbsp;<input type="text" id="idContabilidad" value="" class="texto_tablas" size="55" onBLur="Mayusculas('Nombre')"></td>
                        </tr>
                        <tr style="height: 25px;">
                            <td align="right">* Clave de Entidad:&nbsp;</td>
                            <td align="left">&nbsp;<?php ComboboxINEEntidad::generate("entidad") ?></td>
                        </tr>
                        <tr style="height: 25px;">
                            <td align="center" colspan="2">
                                <button type="submit" name="Boton" value="Guardar Datos">Guardar</button>
                                <button type="button" value="Cancelar" onclick="window.close()">Cancelar</button>
                            </td>
                        </tr>
                    </table>
                </form>
            <?php }
            ?>
        </div>
    </body>
</html>
