<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();

require './services/UsoGeneralService.php';

$Titulo = "Detalle de permiso de la cre";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

$objectDAO = new PermisoCreDAO();

$objectFather = $objectDAO->retrieve($cVarVal);
$objectVO = new PermisoCreVO();
if (is_numeric($busca)) {
    $objectVO = $objectDAO->retrieve($busca);
}
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title> 
        <script src="./bootstrap/controller/utils.js"></script>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="uso_generald.php"><div class="RegresarCss " alt="Flecha regresar" style="">Regresar</div></a>
                </td>
                <td style="vertical-align: top;">
                    <div id="FormulariosBoots">

                        <div class="container">

                            <div class="row background">
                                <div class="col-12 align-left title">Catalogo: <span id="Catalogo_"></span></div>
                                <div class="col-12 align-left">Descripcion: <span id="Descripcion_"></span></div>
                            </div>

                            <div class="row background">                                
                                <div class="col-12 no-margin">
                                    <form name="formulario1" id="formulario1" method="post" action="">
                                        <div class="row no-padding">
                                            <div class="col-12 align-left subtitle">Parámetros del SAT</div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right required"><label class="label">Descripcion <sup class="sup">1</sup>: </label></div>
                                            <div class="col-5"><input type="text" name="Descripcion" id="Descripcion" placeholder="" required="" onkeyup="transformarMayusculas(this)"/></div>
                                            <div class="col-4"><label for="Descripcion"></label></div>
                                        </div>
                                        <?php
                                        $TiInp = $objectFather->getCatalogo() !== "TERMINALES_ALMACENAMIENTO" ? "type='text'" : "type='number' min='0' max='999' ";
                                        $TiTitle = $objectFather->getCatalogo() !== "TERMINALES_ALMACENAMIENTO" ? "Clave" : "No. Terminal ";
                                        ?>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right required"><label class="label"><?= $TiTitle ?> <sup class="sup">2</sup>: </label></div>
                                            <div class="col-5"><input <?= $TiInp ?> name="Llave" id="Llave" placeholder="" required="" onkeyup="transformarMayusculas(this)"/></div>
                                            <div class="col-4"><label for="Llave"></label></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right required"><label class="label">Permiso <sup class="sup">3</sup>: </label></div>
                                            <div class="col-5"><input type="text" name="Permiso" id="Permiso" placeholder="" required="" onkeyup="transformarMayusculas(this)"/></div>
                                            <div class="col-4"><label for="Permiso"></label></div>
                                        </div>

                                        <div class="row no-padding">
                                            <div class="col-3 align-right required"><label class="label">Activo: </label></div>
                                            <div class="col-1">
                                                <div class="image-radio" data-valor="1" id="Radio-Si">
                                                </div>
                                            </div>
                                            <div class="col-1">
                                                <div class="image-radio" data-valor="0" id="Radio-No">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-3 align-right"></div>
                                            <div class="col-2"><button type="submit" class="btn-boots" name="Boton" value="Agregar">Agregar</button></div>
                                        </div>
                                        <input type="hidden" name="busca" class="busca"/>
                                        <input type="hidden" name="Estado" id="Estado"/>
                                    </form>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 no-margin" style="font-size: 10px; color:#55514e;">

                                    <div class="row no-padding">
                                        <div class="col-12">
                                            <strong class="sup">1. Descripciòn : </strong>
                                            Breve decripción de lo que se refiere la clave.
                                        </div>
                                    </div>
                                    <div class="row no-padding">
                                        <div class="col-12">
                                            <strong  class="sup">2. Clave : </strong>
                                            Este es una palabra clave que se utilizará para referenciar al pemiso, este puede ser un número (620,621,622,etc.)
                                            para el caso de las terminales de almacenamiento, alguna palabra para el caso de los proveedores de transporte, etc.
                                        </div>
                                    </div>
                                    <div class="row no-padding">
                                        <div class="col-12">
                                            <strong class="sup">3. Permiso : </strong>
                                            Permiso otorgado por la Comisión Reguladora de Energía (CRE). Los formatos validos son los siguientes:<br>
                                            <ul>
                                                <li>Permiso de la cre: <strong>PL/<span class="sup">XXXXX</span>/EXP/ES/<span class="sup">AAAA</span></strong></li>
                                                <li>Terminal de almacenamiento: <strong>PL/<span class="sup">XXXXX</span>/ALM/<span class="sup">AAAA</span></strong></li>
                                                <li>Proveedor de transporte: <strong>PL/<span class="sup">XXXXX</span>/TRA/OM/<span class="sup">AAAA</span></strong></li>
                                            </ul>
                                        </div>
                                    </div>                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

        <script>
            $(document).ready(function () {
                var busca = "<?= $busca ?>";
                var catalogo = "<?= $objectFather->getCatalogo() ?>";

                $(".busca").val(busca);

                if (busca === "NUEVO") {
                    $(".btn-boots").val("Agregar");
                    $(".btn-boots").html("Agregar");
                } else {
                    $(".btn-boots").val("Actualizar");
                    $(".btn-boots").html("Actualizar");
                }

                $("#Catalogo_").html(catalogo);
                $("#Descripcion_").html("<?= $objectFather->getDescripcion() ?>");

                $("#Llave").val("<?= $objectVO->getLlave() ?>");
                $("#Permiso").val("<?= $objectVO->getPermiso() ?>");
                $("#Descripcion").val("<?= $objectVO->getDescripcion() ?>");
                $("#Estado").val("<?= $objectVO->getEstado() ?>");
                getEstado("<?= $objectVO->getEstado() ?>");
                $(".image-radio").on("click", function () {
                    var subControl = $(this).data("valor");
                    console.log(subControl);
                    getEstado(subControl);
                    if (subControl.toString() === "1") {
                        $("#Estado").val(1);
                    } else {
                        $("#Estado").val(0);
                    }
                });

                function getEstado(optionSelected) {
                    console.log(optionSelected);
                    if (optionSelected.toString() === "0") {
                        $("#Radio-Si").html('<i class="icon fa fa-lg fa-circle-o"></i> Si');
                        $("#Radio-No").html('<i class="icon fa fa-lg fa-check-circle-o"></i> No');
                    } else {
                        $("#Radio-Si").html('<i class="icon fa fa-lg fa-check-circle-o"></i> Si');
                        $("#Radio-No").html('<i class="icon fa fa-lg fa-circle-o"></i> No');
                    }
                }

                $("#formulario1").submit(function (e) {
                    clicksForm = 0;
                    if (!validaInputByCatatalogo($("#Permiso"), catalogo)) {
                        setHtmlByForLabel("Permiso", "Permiso incorrecto, favor de verificarlo");
                        e.preventDefault();
                        return false;
                    }
                    return true;
                });

            });
        </script>                

    </body>
</html>