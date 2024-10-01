<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);
$fecha = date("Y-m-d", strtotime(date("Y-m-d") . "-1 day"));

if ($busca == 1) {
    $Titulo = "Generación de Póliza de Ingresos <br/><strong>ContPAQi</strong>";
} elseif ($busca == 2) {
    $Titulo = "Generación de Póliza de Ingresos <br/><strong>COI</strong>";
} elseif ($busca == 3) {
    $Titulo = "Generación de Póliza de Egresos <br/><strong>ContPAQi</strong>";
} else {
    $Titulo = "";
}
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <title><?= $Gcia ?></title>
        <script>
            var FechaPoliza = "";
            $(document).ready(function () {
                var busca = "<?= $busca ?>";
                $("#fecha").val("<?= $fecha ?>").attr("size", "10").addClass("texto_tablas");
                $("#cFecha").css("cursor", "hand").click(function () {
                    displayCalendar($("#fecha")[0], "yyyy-mm-dd", $(this)[0]);
                });

                if (busca === "1") {
                    $("#genCSV").click(function () {
                        validate(generaCSV, $("#fecha").val());
                    });
                    $("#genPDF").click(function () {
                        validate(generaPDF, $("#fecha").val());
                    });
                } else if (busca === "2") {
                    $("#genXLS").click(function () {
                        validate(generaCOIXLS, $("#fecha").val());
                    });
                    $("#genPDF").click(function () {
                        validate(generaCOIPDF, $("#fecha").val());
                    });
                    $("#genXML").click(function () {
                        validate(generaCOIXML, $("#fecha").val());
                    });
                } else if (busca === "3") {
                    $("#genCSV").click(function () {
                        generaECSV();
                    });
                    $("#genPDF").click(function () {
                        generaEPDF();
                    });
                }
            });

            validate = function (evento, fecha) {
                var htmlContent = "";
                var showButtons = true;
                $.ajaxPrefilter(function (options, original_Options, jqXHR) {
                    options.async = true;
                });
                var callbacks = $.Callbacks();
                jQuery.ajax({
                    type: "GET",
                    url: "ajax_poliza_checker.php",
                    dataType: "json",
                    cache: false,
                    data: {"sDate": fecha},
                    beforeSend: function (xhr) {
                        //console.log(xhr);
                        //console.log(evento);
                        $("#dateStatus").empty();
                    },
                    success: function (response) {
                        console.log(response);
                        //var rs = jQuery.parseJSON(response);
                        var rs = response;
                        if (rs.pagos.length === 0 && rs.cortes.length === 0) {
                            htmlContent += "<font color=\"red\">No es posible generar la Póliza del día " + fecha + ":<br/>";
                            htmlContent += "No hay movimientos para la fecha seleccionada.</font>";
                            $("#dateStatus").html(htmlContent); 
                            return false;
                        }
                        $.each(rs.cortes, function (index, value) {
                            if (rs.cortes[index].status === "Abierto") {
                                htmlContent += "<font color=\"red\">No es posible generar la Póliza del día " + fecha + ":<br/>";
                                htmlContent += "Corte abierto: " + rs.cortes[index].id + " " + rs.cortes[index].concepto + " " + rs.cortes[index].descripcion + ".</font><br/>";
                                $("#dateStatus").html(htmlContent); 
                                return false;
                            }
                            if (rs.cortes[index].statusctv === "Abierto") {
                                htmlContent += "<font color=\"blue\">El corte " + rs.cortes[index].id + " " + rs.cortes[index].concepto + " " + rs.cortes[index].descripcion + " no ha sido cuadrado. Se generará una póliza temporal.</font></br>";
                            }
                        });
                        if (rs.pagos.length === 0) {
                            htmlContent += "<font color=\"blue\">No ha capturado los depósitos para la fecha seleccionada. Se generará una póliza temporal.</font><br/>";
                        }
                        $.each(rs.pagos, function (index, value) {
                            if (rs.pagos[index].status === "Abierta") {
                                htmlContent += "<font color=\"blue\">Pago abierto: " + rs.pagos[index].fecha + " " + rs.pagos[index].concepto + " " + rs.pagos[index].nombre + ". Se generará una póliza temporal.</font></br>";
                            }
                        });
                        $("#dateStatus").html(htmlContent); 
                        FechaPoliza = fecha;
                        evento();
                    },
                    error: function (jqXHR, textStatus) {
                        console.log(jqXHR);
                    }
                });
            };

            generaEPDF = function () {
                window.location = "generadorPolizasEContpaqPDF.php?poliza=EGRESOS&sistema=ContPAQi&formato=PDF&fecha=" + FechaPoliza;
            };
            generaECSV = function () {
                window.location = "generadorPolizasEContpaqCSV.php?poliza=EGRESOS&sistema=ContPAQi&formato=TXT&fecha=" + FechaPoliza;
            };
            generaPDF = function () {
                window.location = "generadorPolizasContpaqPDF.php?poliza=INGRESOS&sistema=ContPAQi&formato=PDF&fecha=" + FechaPoliza;
            };
            generaCSV = function () {
                window.location = "generadorPolizasContpaqCSV.php?poliza=INGRESOS&sistema=ContPAQi&formato=TXT&fecha=" + FechaPoliza;
            };
            generaCOIXLS = function () {
                window.location = "generadorPolizasCOI.php?poliza=INGRESOS&sistema=COI&formato=XLS&fecha=" + FechaPoliza;
            };
            generaCOIPDF = function () {
                window.location = "generadorPolizasCOIPDF.php?poliza=INGRESOS&sistema=COI&formato=PDF&fecha=" + FechaPoliza;
            };
            generaCOIXML = function () {
                window.location = "generadorPolizasCOIXML.php?poliza=INGRESOS&sistema=COI&formato=XML&fecha=" + FechaPoliza;
            };
        </script>

    <body>

        <div id="container">
            <?php nuevoEncabezado($Titulo); ?>

            <table style="width: 100%;height: 70%;text-align: center;" aria-hidden="true">
                <tr>
                    <td>
                        <div style="text-align: center;font-size: 16px;"><?= $Titulo ?></div>
                        <div>
                            <table style="margin-left: auto;margin-right: auto;" aria-hidden="true">
                                <tr class="texto_tablas">
                                    <td style="padding: 1px;">&nbsp;Fecha:</td>
                                    <td style="padding: 1px;">
                                        <input type="text" id="fecha" name="fecha">
                                    </td>
                                    <td style="padding: 1px;color: lightslategrey;font-size: 9px;"><i id="cFecha" class="fa fa-2x fa-calendar" aria-hidden="true" aria-hidden="true"></i></td>
                                </tr>
                            </table>
                        </div>
                        <?php
                        if ($busca == 1) {
                            ?>
                            <div>
                                <table style="margin-left: auto;margin-right: auto;" aria-hidden="true">
                                    <tr class="texto_tablas">
                                        <td style="cursor:pointer; padding: 10px; display: inline-block; border-radius: 5px; color: white; border: 2px solid #666666;">
                                            <div id="genCSV" style="color: lightslategrey;"><i class="fa fa-4x fa-file-text-o" title="Descargar Archivo Plano" aria-hidden="true" aria-hidden="true"></i></div>
                                        </td>
                                        <td>&nbsp;</td>
                                        <td style="cursor:pointer; padding: 10px; display: inline-block; border-radius: 5px; color: white; border: 2px solid #666666;">
                                            <div id="genPDF" style="color: red;"><i class="fa fa-4x fa-file-pdf-o" title="Visualizar PDF" aria-hidden="true" aria-hidden="true"></i></div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <?php
                        } else if ($busca == 2) {
                            ?>
                            <div style="cursor: pointer; padding: 10px; display: inline-block; border-radius: 5px; color: white; border: 2px solid #666666;">                            
                                <table style="margin-left: auto;margin-right: auto;" aria-hidden="true">
                                    <tr class="texto_tablas">
                                        <td style="cursor:pointer; padding: 10px; display: inline-block; border-radius: 5px; color: white; border: 2px solid #666666;">
                                            <div id="genXLS"><i style="color: navy" class="fa fa-4x fa-file-excel-o" title="Descargar Archivo XSL" aria-hidden="true" aria-hidden="true"></i></div>
                                        </td>
                                        <td>&nbsp;</td>
                                        <td style="cursor:pointer; padding: 10px; display: inline-block; border-radius: 5px; color: white; border: 2px solid #666666;">
                                            <div id="genPDF" style="color: red;"><i class="fa fa-4x fa-file-pdf-o" title="Visualizar PDF" aria-hidden="true" aria-hidden="true"></i></div>
                                        </td>
                                        <td>&nbsp;</td>
                                        <td style="cursor:pointer; padding: 10px; display: inline-block; border-radius: 5px; color: white; border: 2px solid #666666;">
                                            <div id="genXML" style="color: lightblue;"><i class="fa fa-4x fa-file-code-o" title="Visualizar XML" aria-hidden="true" aria-hidden="true"></i></div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        <?php } else if ($busca == 3) {
                            ?>
                            <div>
                                <table style="margin-left: auto;margin-right: auto;" aria-hidden="true">
                                    <tr class="texto_tablas">
                                        <td style="cursor:pointer; padding: 10px; display: inline-block; border-radius: 5px; color: white; border: 2px solid #666666;">
                                            <div id="genCSV" style="color: lightslategrey;"><i class="fa fa-4x fa-file-text-o" title="Descargar Archivo Plano" aria-hidden="true" aria-hidden="true"></i></div>
                                        </td>
                                        <td>&nbsp;</td>
                                        <td style="cursor:pointer; padding: 10px; display: inline-block; border-radius: 5px; color: white; border: 2px solid #666666;">
                                            <div id="genPDF" style="color: red;"><i class="fa fa-4x fa-file-pdf-o" title="Visualizar PDF" aria-hidden="true" aria-hidden="true"></i></div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        <?php } ?>
                    </td>
                </tr>
            </table>
            <div id="dateStatus" style="text-align: center;"></div>
        </div>
    </body>
</html>
