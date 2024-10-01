<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$request = utils\HTTPUtils::getRequest();
$session = new OmicromSession("id", "id");

$busca = $session->getSessionAttribute("criteria");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Titulo = "Promociónes enviadas via Whatsapp <i class='fa-brands fa-whatsapp'></i>";
$Id = 162;

$paginador = new Paginador($Id,
        "id",
        "",
        "",
        "",
        $session->getSessionAttribute("sortField"),
        $session->getSessionAttribute("criteriaField"),
        utils\Utils::split($session->getSessionAttribute("criteria"), "|"),
        strtoupper($session->getSessionAttribute("sortType")),
        $session->getSessionAttribute("page"),
        "REGEXP",
        "");

$self = utils\HTTPUtils::getEnvironment()->getAttribute("PHP_SELF");
$cLink = substr($self, 0, strrpos($self, ".")) . 'e.php';
$cLinkd = substr($self, 0, strrpos($self, ".")) . 'd.php';
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
        <?php $paginador->script(); ?>
    </head>
    <body>
        <?php BordeSuperior(); ?>
        <div id="TablaDatos">
            <table class="paginador" aria-hidden="true">
                <?php
                echo $paginador->headers(array("Editar","Detalle"), array());
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();
                    ?>
                    <tr>
                        <td style="text-align: center;"><a href="<?= $cLink ?>?busca=<?= $row["id"] ?>"><i class="icon fa fa-lg fa-edit" aria-hidden="true"></i></a></td>
                        <td style="text-align: center;"><a href="<?= $cLinkd ?>?busca=<?= $row["id"] ?>"><i class="icon fa fa-lg fa-list" aria-hidden="true"></i></a></td>
                        <?php echo $paginador->formatRow(); ?>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>
        <?php
        echo $paginador->footer($usuarioSesion->getLevel() >= 7, array());
        echo $paginador->filter();
        echo "<div class='mensajes'>$Msj</div>";
        BordeSuperiorCerrar();
        PieDePagina();
        $fecha = utils\IConnection::execSql("SELECT valor FROM variables_corporativo WHERE (`llave` = 'Inicio_Puntos'); ");
        $actual = date("Y-m-d", strtotime(date("Y-m-d") . " + 1 day"));
        ?>
        <script>
            $(document).ready(function () {
                $("#FechaPuntos").click(function () {
                    Swal.fire({
                        title: "Actualizar fecha",
                        text: "Fecha Actual : <?= $fecha["valor"] ?> ",
                        background: "#E9E9E9",
                        showConfirmButton: true,
                        confirmButtonText: "Cambiar",
                        input: 'text',
                        inputValue: "<?= $actual ?>",
                        inputLabel: '* Ingresar una fecha posterior a la actual',
                        inputPlaceholder: 'Ejemplo: <?= $actual ?>',
                        footer: '<p style="color:red;">Al actualizar una fecha los puntos y las bonificaciones se iran a 0.00</p>'

                    }).then((result) => {
                        if (result.isConfirmed) {
                            jQuery.ajax({
                                type: "POST",
                                url: "bootstrap/ajax/updateBonificacion.php",
                                dataType: "json",
                                cache: false,
                                data: {"op": 1, "FechaActual": result.value},
                                beforeSend: function (xhr) {
                                    $("#Msj").hide();
                                    $("#Fail").hide();
                                    $("#myLoader").modal("toggle");
                                },
                                success: function (data) {
                                    console.log(data);
                                    Swal.fire({
                                        icon: 'success',
                                        iconColor: 'green',
                                        title: data,
                                        background: "#ABEBC6"
                                    }).then((result) => {
                                        setTimeout(GoToPipas(), 2500);
                                    });
                                },
                                error: function (jqXHR, textStatus) {
                                    console.log(jqXHR);
                                    Swal.fire({
                                        icon: 'warning',
                                        iconColor: 'red',
                                        title: jqXHR.responseText,
                                        background: "#F5B7B1"
                                    })
                                }
                            });
                            //setInterval(GoToPipas(), 3500);
                        }
                        //                    
                    });
                });
            });
            function GoToPipas() {
                window.location.href = 'bonificacion.php?criteria=ini';
            }
        </script>
    </body>
</html>