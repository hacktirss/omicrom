<?php
#Librerias
session_start();

include_once ("./check_report.php");
include_once ("libnvo/lib.php");
include_once ("importeletras.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();

$Titulo = "Corte Parcial";

$selectByDespachador = "
            SELECT ven.id despachador,man.posicion,ven.alias 
            FROM man,ven 
            WHERE ven.id = man.despachador AND man.activo = 'Si' 
            GROUP BY ven.id
            ORDER BY man.posicion;";

$selectByPosicion = "
            SELECT ep.posicion, ep.estado
            FROM estado_posiciones ep,man 
            WHERE ep.posicion = man.posicion
            AND man.activo = 'Si'";

$selectByIsla = "
            SELECT man.isla_pos, GROUP_CONCAT(man.posicion) posiciones
            FROM man 
            WHERE TRUE AND man.activo = 'Si' 
            GROUP BY man.isla_pos
            ORDER BY man.isla_pos";

$CtsG = "SELECT serie,authuser.name,cpf.fecha FROM omicrom.ct_parcial_fecha cpf LEFT JOIN authuser ON authuser.id=cpf.usr group by serie order by serie desc limit 10;";

$rowsByDespachador = utils\IConnection::getRowsFromQuery($selectByDespachador);
$rowsByPosicion = utils\IConnection::getRowsFromQuery($selectByPosicion);
$rowsByIsla = utils\IConnection::getRowsFromQuery($selectByIsla);
$rowsCtGuardado = utils\IConnection::getRowsFromQuery($CtsG);
utils\HTTPUtils::setSessionValue("GuardarCorte", false);
?>
<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require "./config_omicrom_reports.php"; ?> 
        <title><?= $Gcia ?></title>
        <script>
            var pagina = "ventad.php";

            function callVisor(orden) {
                //window.setInterval(function () {
                $("#contenedor").load("ventaPos.php?op=Com&Orden=" + orden);
                //}, 1000);
            }
            function redireccionar() {
                location.href = pagina;
            }
        </script>

        <script>
            $(document).ready(function () {
                let varOrden = "P";
                callVisor(varOrden);
                hideOptions(varOrden);

                $("#checkTodos").change(function () {
                    $("input:checkbox").prop("checked", $(this).prop("checked"));
                });

                $("#Consultar").click(function (e) {
                    e.preventDefault();
                    jQuery.ajax({
                        type: "POST",
                        url: "ventaAjax.php",
                        dataType: "json",
                        cache: false,
                        data: $("#FormData").serialize(),
                        beforeSend: function (xhr) {
                            console.log(xhr);
                            $("#Consultar").hide();
                            $("#response-container").html("<p><br>Procesando, espere por favor...</p><p><i class=\"fa fa-spinner fa-pulse fa-4x\" aria-hidden=\"true\"></i><span class=\"sr-only\">Loading...</span></p>");
                        },
                        success: function (response) {
                            console.log(response);
                            if (response.success) {
                                var output = "<p><strong>" + response.message + "</b</p>";
                                $("#response-container").html(output);
                                if (varOrden === "I") {
                                    pagina = "ventai.php";
                                }
                                setTimeout(redireccionar(), 3000);
                            } else {
                                $("#Consultar").show();
                                $("#response-container").html("No ha habido suerte: " + response.message);
                            }
                        },
                        error: function (jqXHR, textStatus) {
                            console.log(jqXHR);
                            $("#Consultar").show();
                            $("#response-container").html("<p><br><strong>" + textStatus + ": intenta nuevamente</strong></p>");
                        }
                    });
                });

                $("input[name='Orden']").change(function () {
                    varOrden = $(this).val();
                    $("#contenedor").empty();
                    callVisor(varOrden);
                    hideOptions(varOrden);
                });

                function hideOptions(value) {
                    console.log(value);
                    switch (value) {
                        case "P":
                            $("#Despachadores").hide();
                            $("#Islas").hide();
                            $("#CGuardados").hide();
                            $("#Posiciones").show();
                            $("#contenedor").show();
                            break;
                        case "D":
                            $("#Posiciones").hide();
                            $("#CGuardados").hide();
                            $("#Islas").hide();
                            $("#Despachadores").show();
                            $("#contenedor").show();
                            break;
                        case "I":
                            $("#Despachadores").hide();
                            $("#Posiciones").hide();
                            $("#CGuardados").hide();
                            $("#Islas").show();
                            $("#contenedor").show();
                            break;
                        case "PG":
                            $("#Despachadores").hide();
                            $("#Posiciones").hide();
                            $("#Islas").hide();
                            $("#CGuardados").show();
                            $("#contenedor").hide();
                            break;
                    }
                }
            });
        </script>

    </head>

    <body>

        <div id="container">
            <?php nuevoEncabezado($Titulo); ?>

            <form name="form1" id="FormData" method="post" action="">

                <div id="CorteParcial">
                    <div class="header_corte">
                        <strong>Totalizadores por: </strong>                                
                        <input type="radio" name="Orden" value="D" class="botonAnimatedMin"> Despachador 
                        <input type="radio" name="Orden" value="P" checked="" class="botonAnimatedMin" style="margin-left: 7px;"> Posicion
                        <input type="radio" name="Orden" value="I" class="botonAnimatedMin" style="margin-left: 7px;"> Isla
                    </div>
                    <div class="header_corte" style="margin-top: 10px;">
                        <strong>Cortes parciales guardados: </strong><input type="radio" name="Orden" value="PG" class="botonAnimatedMin" style="margin-left: 7px;"> 
                    </div>
                    <div class="content_corte">
                        <div style="float: left" id="contenedor"></div>
                        <div style="float: right">
                            <div style="height: 30px;text-align: left;font-weight: bold;padding-top: 5px;"><input type="checkbox" id="checkTodos"> Seleccionar todo</div>
                            <div id="Despachadores">
                                <?php foreach ($rowsByDespachador as $value) : ?>
                                    <div><?= $value["despachador"] . ": " ?><input type="checkbox" name="registros[]" value="<?= $value["despachador"] ?>" class="miCheckBox"/></div>
                                <?php endforeach; ?>
                            </div>
                            <div id="Posiciones">
                                <?php foreach ($rowsByPosicion as $value) : ?>
                                    <div><?= $value["posicion"] . ": " ?><input type="checkbox" name="registros[]" value="<?= $value["posicion"] ?>" class="miCheckBox"/></div>
                                <?php endforeach; ?>
                            </div>
                            <div id="Islas">
                                <?php foreach ($rowsByIsla as $value) : ?>
                                    <div><?= $value["isla_pos"] . ": " ?><input type="checkbox" name="registros[]" value="<?= $value["isla_pos"] ?>" class="miCheckBox"/></div>
                                <?php endforeach; ?>
                            </div>
                            <div id="CGuardados">
                                <?php foreach ($rowsCtGuardado as $value) : ?>
                                    <div><?= $value["authuser"] . " " . $value["fecha"] . " FOLIO : " . $value["serie"] ?> <a href="ventad.php?Folio=<?= $value["serie"] ?>"><i class="fa fa-list" aria-hidden="true"></i></a></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div style="text-align: right;width: 100%;">
                            Guardar Corte <input type="checkbox" name="GCorte" id="GCorte" value="Guardar" class="botonAnimatedMin">
                        </div>
                    </div>
                    <div>
                        <input type="submit" id="Consultar" value="Enviar" />
                    </div>
                    <div id="response-container"> </div>                    
                </div> 
            </form>
        </div>

        <div id="footer">
            <?php topePagina() ?>
        </div>
    </body>
</html>
