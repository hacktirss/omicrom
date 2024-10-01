<?php
#Librerias
session_start();

include_once ("check.php");
include_once ('comboBoxes.php');
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$RetSelec = "menu.php";
$Titulo = "Parametros del sistema";
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$ciaDAO = new CiaDAO();
$objectVO = $ciaDAO->retrieve(1);
//echo print_r($objectVO, true);
require_once './services/ParametrosService.php';

$latitud = $objectVO->getLatitud();
$longitud = $objectVO->getLongitud();
$link = sprintf("https://maps.google.com/maps?q=loc:%f,%f", $latitud, $longitud);
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
<?php
if (strpos($objectVO->getCia(), '"') == 0) {
    ?>
                    $("#Cia").val('<?= $objectVO->getCia() ?>');
    <?php
} else {
    ?>
                    $("#Cia").val("<?= $objectVO->getCia() ?>");
    <?php
}
?>
                $("#Rfc").val("<?= $objectVO->getRfc() ?>");
                $("#Representante_legal").val("<?= $objectVO->getRepresentante_legal() ?>");
                $("#Rfc_representante_legal").val("<?= $objectVO->getRfc_representante_legal() ?>");
                $("#Direccion").val("<?= $objectVO->getDireccion() ?>");
                $("#Numeroext").val("<?= $objectVO->getNumeroext() ?>");
                $("#Numeroint").val("<?= $objectVO->getNumeroint() ?>");
                $("#Colonia").val("<?= $objectVO->getColonia() ?>");
                $("#Ciudad").val("<?= $objectVO->getCiudad() ?>");
                $("#Estado").val("<?= $objectVO->getEstado() ?>");
                $("#Telefono").val("<?= $objectVO->getTelefono() ?>");
                $("#Codigo").val("<?= $objectVO->getCodigo() ?>");
                $("#Descripcion").val("<?= $objectVO->getDescripcion() ?>");
                $("#Direccionexp").val("<?= $objectVO->getDireccionexp() ?>");
                $("#Numeroextexp").val("<?= $objectVO->getNumeroextexp() ?>");
                $("#Numerointexp").val("<?= $objectVO->getNumerointexp() ?>");
                $("#Coloniaexp").val("<?= $objectVO->getColoniaexp() ?>");
                $("#Ciudadexp").val("<?= $objectVO->getCiudadexp() ?>");
                $("#Estadoexp").val("<?= $objectVO->getEstadoexp() ?>");
                $("#Codigoexp").val("<?= $objectVO->getCodigoexp() ?>");
                $("#Master").val("<?= $objectVO->getMaster() ?>");

                $("#LatitudGPS").val("<?= $objectVO->getLatitud() ?>");
                $("#LongitudGPS").val("<?= $objectVO->getLongitud() ?>");

                $("#Caracter_sat").val("<?= $objectVO->getCaracter_sat() ?>");
                $("#Clave_instalacion").val("<?= $objectVO->getClave_instalacion() ?>");
                $("#Modalidad_permiso").val("<?= $objectVO->getModalidad_permiso() ?>");

                $("#Profeco").click(function () {
                    Swal.fire({
                        title: "Favor de verificar y terminar de llenar los datos que se encuentren incompletos",
                        background: "#E9E9E9",
                        showConfirmButton: true,
                        confirmButtonText: "Enterado",
                        inputPlaceholder: 'Ejemplo: 1'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.open("pdfDatosDeInstalacion.php", "Omicrom Aviso PROFECO", "width=950px,height=550px,top=250px,left=80px,Menubar=No,Resizable=NO,Location=NO,Scrollbars=yes,Status=no,Toolbar=no");
                        }
                    });
                });
                $("#SAT").click(function () {
                    Swal.fire({
                        title: "Favor de verificar y terminar de llenar los datos que se encuentren incompletos",
                        background: "#E9E9E9",
                        showConfirmButton: true,
                        confirmButtonText: "Enterado",
                        inputPlaceholder: 'Ejemplo: 1'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.open("pdfAvisoSat.php", "Omicrom Aviso SAT", "width=950px,height=550px,top=250px,left=80px,Menubar=No,Resizable=NO,Location=NO,Scrollbars=yes,Status=no,Toolbar=no");
                        }
                    });
                });

                $("#copiar").click(function () {
                    $("#Direccionexp").val($("#Direccion").val());
                    $("#Numeroextexp").val($("#Numeroext").val());
                    $("#Numerointexp").val($("#Numeroint").val());
                    $("#Coloniaexp").val($("#Colonia").val());
                    $("#Ciudadexp").val($("#Ciudad").val());
                    $("#Estadoexp").val($("#Estado").val());
                    $("#Codigoexp").val($("#Codigo").val());
                });
                function wingral(url) {
                    windowGral = window.open(url, "wingeneral", "status=no,tollbar=yes,scrollbars=yes,menubar=no,width=1000,height=600,left=100,top=50");
                }
            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center;" class="nombre_cliente">
                    <a href="menu.php"><div class="RegresarCss " alt="Flecha regresar" style="">Regresar</div></a>
                </td>
                <td style="vertical-align: top;">
                    <div id="FormulariosBoots">

                        <div class="container">
                            <div class="row background">
                                <div class="col-6 no-margin">
                                    <form name="formulario1" id="formulario1" method="post" action="">
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Compañia:</div>
                                            <div class="col-8"><input type="text" name="Cia" id="Cia" placeholder="" required=""/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Rfc:</div>
                                            <div class="col-8"><input type="text" name="Rfc" id="Rfc" placeholder="" required=""/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Representante Legal:</div>
                                            <div class="col-8"><input type="text" name="RepLegal" id="Representante_legal" placeholder="" required=""/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Rfc Representante:</div>
                                            <div class="col-8"><input type="text" name="RfcRepLegal" id="Rfc_representante_legal" placeholder="" required=""/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Direccion:</div>
                                            <div class="col-8"><input type="text" name="Direccion" id="Direccion" placeholder="" required=""/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required"># Exterior:</div>
                                            <div class="col-4"><input type="text" name="Numeroext" id="Numeroext" placeholder="" required=""/></div>
                                            <div class="col-4"><input type="text" name="Numeroint" id="Numeroint" placeholder="# Interior"/></div>  
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Colonia:</div>
                                            <div class="col-8"><input type="text" name="Colonia" id="Colonia" placeholder="" required=""/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Ciudad:</div>
                                            <div class="col-8"><input type="text" name="Ciudad" id="Ciudad" placeholder="" required=""/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Estado:</div>
                                            <div class="col-8"><input type="text" name="Estado" id="Estado" placeholder="" required=""/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Telefono:</div>
                                            <div class="col-4"><input type="text" name="Telefono" id="Telefono" placeholder="" required=""/></div>
                                            <div class="col-2 align-right  required">C.P.:</div>
                                            <div class="col-2"><input type="text" name="Codigo" id="Codigo" placeholder="" required=""/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Descripción:</div>
                                            <div class="col-8"><input type="text" name="Descripcion" id="Descripcion" /></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Clave master:</div>
                                            <div class="col-8"><input type="text" name="Master" id="Master" placeholder=" * * * * * * " required=""/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">RFC Proveedor</div>
                                            <div class="col-8"><input type="text" value="<?= $objectVO->getRfc_proveedor_sw() ?>" disabled></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Permiso CRE:</div>
                                            <?php
                                            $SqlCre = "SELECT permiso FROM omicrom.permisos_cre WHERE llave = 'PERMISO_CRE';";
                                            $CRE = utils\IConnection::execSql($SqlCre);
                                            ?>
                                            <div class="col-8"><input type="text" value="<?= $CRE["permiso"] ?>" disabled></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-12 align-center subtitulos">Datos de expedición <span id="copiar" class="pointer" title="Clic aqui para copiar datos anteriores"><i class="icon fa fa-lg fa-copy" aria-hidden="true" ></i> Copiar datos primarios</span></div>
                                        </div>

                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Direccion:</div>
                                            <div class="col-8"><input type="text" name="Direccionexp" id="Direccionexp" placeholder=""/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right"># Exterior:</div>
                                            <div class="col-4"><input type="text" name="Numeroextexp" id="Numeroextexp" placeholder=""/></div>
                                            <div class="col-4"><input type="text" name="Numerointexp" id="Numerointexp" placeholder=""/></div>  
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Colonia:</div>
                                            <div class="col-8"><input type="text" name="Coloniaexp" id="Coloniaexp" placeholder=""/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Ciudad:</div>
                                            <div class="col-8"><input type="text" name="Ciudadexp" id="Ciudadexp" placeholder=""/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Estado:</div>
                                            <div class="col-8"><input type="text" name="Estadoexp" id="Estadoexp" placeholder=""/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">C.P.:</div>
                                            <div class="col-2"><input type="text" name="Codigoexp" id="Codigoexp" placeholder=""/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right"></div>
                                            <div class="col-4"><button type="submit" name="Boton" value="Actualizar">Actualizar</button></div>
                                        </div>
                                        <input type="hidden" name="busca" class="busca"/>
                                    </form>
                                </div>

                                <div class="col-6">
                                    <form name="formulario2" id="formulario2" method="post" action="">
                                        <div class="row no-padding">
                                            <div class="col-12 align-center subtitulos">Datos de localización <i class="icon fa fa-lg fa-map-location-dot" aria-hidden="true" ></i></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Latitud:</div>
                                            <div class="col-4"><input type="text" name="Latitud" id="LatitudGPS" placeholder="" required=""/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right required">Longitud:</div>
                                            <div class="col-4"><input type="text" name="Longitud" id="LongitudGPS" placeholder="" required=""/></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right"></div>
                                            <div class="col-4"><button type="submit" name="Boton" value="Actualizar Localizacion">Actualizar</button></div>
                                            <div class="col-4 align-right"><button type="button" onclick=openInNewTab("<?= $link ?>"); title="Click para ir Google Maps">Ir <i class="icon fa fa-lg fa-map-marker-alt" aria-hidden="true" ></i></button></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-12 align-center subtitulos">Parametros del SAT</div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Caracter Sat <sup>1</sup>:</div>
                                            <div class="col-7"><?php ListasCatalogo::getDataFromCatalogoSatCv("Caracter_sat", "CLAVES_CARACTER") ?></div>
                                            <div class="col-1"><i class="fa fa-lg fa-question-circle" style="cursor:pointer; cursor: hand" aria-hidden="true" data-toggle="modal" data-target="#modal-parametros-listas" data-identificador="CLAVES_CARACTER" data-operacion="11"></i></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Clave Instalacion <sup>2</sup>:</div>
                                            <div class="col-7"><?php ListasCatalogo::getDataFromCatalogoSatCv("Clave_instalacion", "CLAVES_INSTALACION") ?></div>
                                            <div class="col-1"><i class="fa fa-lg fa-question-circle" style="cursor:pointer; cursor: hand" aria-hidden="true" data-toggle="modal" data-target="#modal-parametros-listas" data-identificador="CLAVES_INSTALACION" data-operacion="11"></i></div>
                                        </div>
                                        <div class="row no-padding">
                                            <div class="col-4 align-right">Modalidad de Permiso <sup>3</sup>:</div>
                                            <div class="col-7"><?php ListasCatalogo::getDataFromCatalogoSatCv("Modalidad_permiso", "CLAVES_PERMISO") ?></div>
                                            <div class="col-1"><i class="fa fa-lg fa-question-circle" style="cursor:pointer; cursor: hand" aria-hidden="true" data-toggle="modal" data-target="#modal-parametros-listas" data-identificador="CLAVES_PERMISO" data-operacion="11"></i></div>
                                        </div>

                                        <div class="row no-padding">
                                            <div class="col-4 align-right"></div>
                                            <div class="col-4"><button type="submit" name="Boton" value="Actualizar SAT">Actualizar</button></div>
                                        </div>
                                        <input type="hidden" name="busca" class="busca"/>
                                    </form>
                                    <div class="row no-padding">
                                        <div class="col-12">
                                            <div style="display: inline-block;margin-right: 15px;cursor:pointer; cursor: hand" title="PDF para PROFECO" ><i class="fa fa-file-pdf-o fa-lg" id="Profeco" aria-hidden="true"> PROFECO</i></div>
                                            <div style="display: inline-block;cursor:pointer; cursor: hand" title="PDF para SAT"><i class="fa fa-file-pdf-o fa-lg" id="SAT" aria-hidden="true"> SAT</i></div>
                                        </div>
                                    </div>
                                    <div class="row no-padding">
                                        <div class="col-12">
                                            <span style="color:black;"><strong>Nota: </strong> campos necesarios para la generación de archivos del SAT.</span>
                                        </div>
                                    </div>
                                    <div class="row no-padding">
                                        <div class="col-12">
                                            <span style="font-size: 9px; color:#55514e;"><strong>1. Caracter Sat: </strong> requerido para expresar el carácter con el que actúa para efectos regulatorios.</span>
                                        </div>
                                    </div>
                                    <div class="row no-padding">
                                        <div class="col-12">
                                            <span style="font-size: 9px; color:#55514e;"><strong>2. Clave Instalación: </strong> requerido para expresar la clave de identificación de la instalación o proceso donde deban instalarse sistemas de medición.</span>
                                        </div>
                                    </div>
                                    <div class="row no-padding">
                                        <div class="col-12">
                                            <span style="font-size: 9px; color:#55514e;"><strong>3. Modalidad de Permiso: </strong> tipo de permiso conforme a lo señalado en la sección clave instalación.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style='text-align: left;'>(&nbsp;<span style='color: red;'><strong>*&nbsp;</strong></span>) Campos necesarios para captura de pipas y envio de volumetricos</div>
                </td>
            </tr>
        </table>	

        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

        <link rel="stylesheet" href="bootstrap/bootstrap-4.0.0/dist/css/bootstrap-modal.css" type="text/css">

        <?php include_once ("./bootstrap/modals/modal_parametros.html"); ?>

        <script src="./bootstrap/controller/utils.js"></script>
        <script src="./bootstrap/controller/parametros.js"></script>

    </body>
</html>
