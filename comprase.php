<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("comboBoxes.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$sanitize = SanitizeUtil::getInstance();
$self = utils\HTTPUtils::self();

$Titulo = "Detalle de compra";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

require_once './services/ComprasService.php';

$proveedorDAO = new ProveedorDAO();

$comprasVO = new ComprasVO();
if (is_numeric($busca)) {
    $comprasVO = $comprasDAO->retrieve($busca);
} else {
    $comprasVO->setFecha(date("Y-m-d"));

    if ($request->hasAttribute("Proveedor")) {
        $comprasVO->setProveedor($request->getAttribute("Proveedor"));
    } else {
        $comprasVO->setProveedor(1);
    }
}
$proveedorVO = $proveedorDAO->retrieve($comprasVO->getProveedor());
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <style>
            #myDrop {
                min-height: 80px;
                padding: 5px;
            }
            .dz-button {
                font-size: 22px !important;
                font-weight: bold;
                color: #4F4F4F;
            }
            .dropzone{
                border-radius: 10px;
                border-color: #099;
                background: #EDFAF3;
            }
            .dropzone:hover{
                border:2px solid #EC7063;
                border-color: #EC7063 !important;
                background-color: #FAD7A0 !important;
                font-weight: bold;
                color: #273746;
            }
        </style>
        <script>
            $(document).ready(function () {
                $("#busca").val("<?= $busca ?>");
                $("#Proveedor").val("<?= $comprasVO->getProveedor() ?>");
                $("#Concepto").val("<?= $comprasVO->getConcepto() ?>");
                $("#Documento").val("<?= $comprasVO->getDocumento() ?>");
                $("#Importesin").val("<?= $comprasVO->getImportesin() ?>");
                $("#Uuid").val("<?= $comprasVO->getUuid() ?>");
                $("#Iva").val("<?= $comprasVO->getIva() ?>");
                $("#Observaciones").val("<?= $comprasVO->getObservaciones() ?>");
                $('#Fecha').val('<?= $comprasVO->getFecha() ?>').attr('size', '18').addClass('texto_tablas');
                $('#cFecha').css('cursor', 'hand').click(function () {
                    displayCalendar($('#Fecha')[0], 'yyyy-mm-dd', $(this)[0]);
                });

                $("#Concepto").focus();
                Dropzone.prototype.defaultOptions.dictDefaultMessage = "<i class='fa-regular fa-hard-drive'></i> Importar Datos XML <i class='fa-regular fa-file-code'></i>";
                Dropzone.options.myDrop = {
                    url: "uploadAditivos.php?busca=<?= $carga ?>&Proveedor=<?= $request->getAttribute("Proveedor") ?>",
                    uploadMultiple: true,
                    maxFileSize: 3,
                    acceptedFiles: ".xml",
                    height: "10px",
                    init: function init() {
                        this.on("addedfile", function () {
                            setTimeout(function () {
                                window.location.href = "compras.php?criteria=ini";
                            }, 800);
                        });
                    }
                }
            });

        </script>
        <script src="dropzone/min/dropzone.min.js"></script>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center; width: 95px;" class="nombre_cliente">
                    <a href="compras.php"><div class="RegresarCss " alt="Flecha regresar" style="">Regresar</div></a>
                </td>
                <td style="vertical-align: top;">
                    <form name="form1" id="form1" method="post" action="">
                        <table style="width: 95%;margin-left: auto;margin-right: auto;" class='texto_tablas' aria-hidden="true">
                            <?php
                            cInput("Id:", "Text", "5", "Id", "right", $busca, "40", false, true, "");

                            echo "<tr><td align='right' class='nombre_cliente' bgcolor='#e1e1e1'> Proveedor: &nbsp;</td>";
                            echo "<td>";
                            ComboboxProveedor::generate("Proveedor", "'Aceites'", "325px", " required='required'");
                            echo "</td>";
                            echo "</tr>";

                            cInput("Concepto:", "Text", "50", "Concepto", "right", "", "50", true, false, "", " placeholder='Breve descripcion de la compra'  required='required'");

                            cInput("Observaciones:", "Text", "50", "Observaciones", "right", "", "50", true, false, "", " placeholder='Breve observacion de la compra'  required='required'");

                            cInput("Fecha de la compra: ", "Text", "18", "Fecha", "right", "", "18", true, false, '<img id="cFecha" src="libnvo/calendar.png" alt="Calendario">');

                            cInput("Folio ó No.de factura:", "Text", "18", "Documento", "right", "", "20", true, false, '', " required='required'");

                            cInput("Uuid:", "Text", "38", "Uuid", "right", "", "20", true, false, '', " required='required'");

                            cInput("Importe sin iva:", "Text", "18", "Importesin", "right", "", "20", true, false, ' (* Subtotal de la factura con descuento.)', " required='required'");

                            cInput("Iva:", "Text", "18", "Iva", "right", "", "20", true, false, '');

                            cInput("Tipo de pago:", "Text", "18", "Tipodepago", "right", $proveedorVO->getTipodepago(), "20", true, true, '');

                            cInput("No.de dias de credito:", "Text", "10", "Dias_credito", "right", $proveedorVO->getDias_credito(), "10", true, true);

                            echo "<tr><td colspan='2' align='center'>";
                            if (is_numeric($busca)) {
                                if ($comprasVO->getStatus() === StatusCompra::ABIERTO) {
                                    echo "<input type='submit' class='nombre_cliente' name='Boton' value='Actualizar'>";
                                }
                            } else {
                                echo "<input type='submit' class='nombre_cliente' name='Boton' value='Agregar'>";
                            }
                            echo "</td><tr>";
                            ?>

                        </table>
                        <input type='hidden' name='busca' id="busca">

                    </form>
                </td>
            </tr>
            <?php
            if ($busca === "NUEVO") {
                ?>
                <tr >
                    <td colspan="2" style="padding-top: 40px;">
                        <div class="container show-dropzone">
                            <div class="row no-padding">
                                <div class="col-lg-3"></div>
                                <div class="col-lg-4" title="Recuerda que para que el uso sea correcto, se necesita tener los productos con el mismo nombre a como los tiene el proveedor registrado en su factura o xml">
                                    <div class="btn-group w-100">
                                        <form class="dropzone" id="myDrop" enctype="multipart/form-data">
                                            <div class="fallback">
                                                <input type="file" name="file" id="myId" multiple>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php
            }
            ?>
        </table>

        <?php
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>
