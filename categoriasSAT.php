<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("CFDIComboBoxes.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$mysqli = iconnect();

$tipo = $request->getAttribute("tipo");
$division = $request->getAttribute("division");
$grupo = $request->getAttribute("grupo");
$clase = $request->getAttribute("clase");
$cbConcepto = $request->getAttribute("quicksearch") != '' ? trim(substr($request->getAttribute("quicksearch"), 0, strpos($request->getAttribute("quicksearch"), "|"))) : $request->getAttribute("claveps");


$busca = $request->getAttribute("busca");

$Return = "productose.php?busca=$busca";

$Titulo = "Catálogo de claves de Producto/Servicio SAT CFDI 3.3";

$sqlCat = "
    SELECT 
            CT.clave ccategoria,
            CT.clave_PADRE,
            C.nombre, 
            C.clave,
            CT.descripcion, 
            CT.tipo,
            CASE
                WHEN CT.clave = CONCAT(SUBSTR(C.clave, 1, 2), '000000') THEN 'division'
                WHEN CT.clave = CONCAT(SUBSTR(C.clave, 1, 4), '0000') THEN 'grupo'
                WHEN CT.clave = CONCAT(SUBSTR(C.clave, 1, 6), '00') THEN 'clase'
                WHEN CT.clave = C.clave  THEN 'concepto'
            END categoria
        FROM cfdi33_c_conceptos C
        JOIN cfdi33_c_categorias CT 
            ON CT.clave = CONCAT(SUBSTR(C.clave, 1, 2), '000000') 
            OR CT.clave = CONCAT(SUBSTR(C.clave, 1, 4), '0000')
            OR CT.clave = CONCAT(SUBSTR(C.clave, 1, 6), '00')
        WHERE C.clave = '" . $cbConcepto . "'
        ORDER BY CT.clave
";

$ct_ps_q =$mysqli->query($sqlCat);

$ctipo = "";
$cdivision = "";
$cgrupo = "";
$cclase = "";

while (($ct_rs_rs = $ct_ps_q->fetch_array())) {

    if ($ct_rs_rs['categoria'] == 'division') {
        $cdivision = $ct_rs_rs['ccategoria'];
    }
    if ($ct_rs_rs['categoria'] == 'grupo') {
        $cgrupo = $ct_rs_rs['ccategoria'];
    }
    if ($ct_rs_rs['categoria'] == 'clase') {
        $cclase = $ct_rs_rs['ccategoria'];
    }

    $ctipo = $ct_rs_rs['tipo'];
}

if (substr($request->getAttribute("Boton"), 0, 7) == "Agregar") {
    $cSql = "UPDATE cfdi33_c_conceptos SET status = 1 WHERE clave = '" . $cbConcepto . "'";
    if (!$mysqli->query($cSql)) {
        error_log($mysqli->error);
    }

    $Msj = "Registro dado de alta";

    header("Location: $Return?Msj=$Msj&busca=$busca&common_claveps=$cbConcepto");
}
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom.php"; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $('#tipo').val('<?= $ctipo != '' ? $ctipo : $tipo ?>');
                $('#division').val('<?= $cdivision != '' ? $cdivision : $division ?>');
                $('#grupo').val('<?= $cgrupo != '' ? $cgrupo : $grupo ?>');
                $('#clase').val('<?= $cclase != '' ? $cclase : $clase ?>');
                $('#claveps').val('<?= $cbConcepto != '' ? $cbConcepto : $claveps ?>');

                $('#tipo').change(function () {
                    $('#division').val('');
                    $('#grupo').val('');
                    $('#clase').val('');
                    $('#claveps').val('');
                    document.form1.submit();
                });
                $('#division').change(function () {
                    $('#grupo').val('');
                    $('#clase').val('');
                    $('#claveps').val('');
                    document.form1.submit();
                });
                $('#grupo').change(function () {
                    $('#clase').val('');
                    $('#claveps').val('');
                    document.form1.submit();
                });
                $('#clase').change(function () {
                    $('#claveps').val('');
                    document.form1.submit();
                });
                $('#claveps').change(function () {
                    $('#Boton').val('Agregar Clave ' + $('#claveps').val());
                });

                $('#autocomplete').val('<?= $SCliente ?>').addClass('texto_tablas')
                        .attr('placeholder', 'Favor de seleccionar el concepto')
                        .click(function () {
                            this.select();
                        })
                        .activeComboBox(
                                $('[name=\'form1\']'),
                                'SELECT clave as data, CONCAT(clave, \' | \', nombre) value FROM cfdi33_c_conceptos WHERE 1=1',
                                'nombre');
                $('#autocomplete').focus();
            });
           
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>
        
        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="productose.php?busca=<?= $busca?>"><img src="libnvo/regresa.jpg" alt="Flecha regresar"></a><br/>regresar
                </td>
                <td style="vertical-align: top;">
                    <form name="form1" id="form1" method="post" action="">

                        <input type="hidden" name="busca" id="busca" value="<?= $busca ?>"/> 

                        <table style="width: 90%; text-align: center; border: 0px; margin: 5px;" aria-hidden="true">                                            

                            <caption class="nombre_cliente">Herramienta de búsqueda por Categorías de Clave de Producto/Servicio definidas por el SAT para el CFDI versión 3.3</caption>

                            <tr class="nombre_cliente" style="padding: 5px;">
                                <td class="nombre_cliente" style="background-color:#DEDEF1;"> 
                                    <strong>Búsqueda rápida.</strong> <br/>
                                    <small>Escriba el texto a buscar y seleccione el más apropiado.</small>
                                </td>
                                <td class="nombre_cliente"  style="text-align: left;"> 
                                    <div style="position: relative;">
                                        <input style="font-size: 12px;width: 450px;" type="search" name="quicksearch" id="autocomplete"/>
                                    </div>
                                    <div id="autocomplete-suggestions"></div>
                                </td>
                            </tr>

                            <tr class="nombre_cliente" >
                                <td class="nombre_cliente" style="background-color:#DEDEF1;">
                                    <strong>Producto/Servicio</strong>
                                </td>
                                <td class="nombre_cliente"  style="text-align: left;"> 
                                    <select  class='texto_tablas' name="tipo" id="tipo" style="width: 450px;">
                                        <option value="">SELECCIONE TIPO</option>
                                        <option value="Producto">Producto</option>
                                        <option value="Servicio">Servicio</option>
                                    </select>
                                </td>
                            </tr>

                            <tr class="nombre_cliente">
                                <td class="nombre_cliente" style="background-color:#DEDEF1;">
                                    <strong>División</strong>
                                </td>
                                <td class="nombre_cliente"  style="text-align: left;"> 
                                    <?= ComboboxDivison::generate("division", $ctipo != '' ? $ctipo : $tipo, " style='width: 450px;'"); ?>
                                </td>
                            </tr>

                            <tr class="nombre_cliente" >
                                <td class="nombre_cliente" style="background-color:#DEDEF1;">
                                    <strong>Grupo</strong>
                                </td>
                                <td class="nombre_cliente"  style="text-align: left;"> 
                                    <?= ComboboxGrupo::generate("grupo", $cdivision != '' ? $cdivision : $division, " style='width: 450px;'"); ?>
                                </td>
                            </tr>

                            <tr class="nombre_cliente">
                                <td class="nombre_cliente" style="background-color:#DEDEF1;">
                                    <strong>Clase</strong>
                                </td>
                                <td class="nombre_cliente"  style="text-align: left;"> 
                                    <?= ComboboxClase::generate("clase", $cgrupo != '' ? $cgrupo : $grupo, " style='width: 450px;'"); ?>
                                </td>
                            </tr>

                            <tr class="nombre_cliente">
                                <td class="nombre_cliente" style="background-color:#DEDEF1;">
                                    <strong>Clave de Producto/Servicio</strong> <br/>
                                    <small>Clave de producto requerida por el SAT.</small>
                                </td>
                                <td class="nombre_cliente"  style="text-align: left;"> 
                                    <?= ComboboxProductoServicio::generate("claveps", $cclase != '' ? $cclase : $clase, " style='width: 450px;'"); ?>
                                </td>
                            </tr>

                            <tr>
                                <td colspan="2">
                                    <input class="nombre_cliente" type="submit" id="Boton" name="Boton" value="Agregar Clave <?= $cbConcepto ?>">
                                </td>
                            </tr>
                        </table>
                    </form>
                </td>
            </tr>
        </table>

        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>

</html>

