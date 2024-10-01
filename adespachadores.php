<?php
#Librerias
session_start();

include_once ("auth.php");
include_once ("authconfig.php");
include_once ("check.php");
include_once ("libnvo/lib.php");
include_once ("data/CiaDAO.php");

use com\softcoatl\utils as utils;

$usuarioSesion = getSessionUsuario();

$request = utils\HTTPUtils::getRequest();
$session = new OmicromSession("man.id", "man.id");

$busca = $session->getSessionAttribute("criteria");
$Msj = urldecode(utils\HTTPUtils::getRequest()->getAttribute("Msj"));

$Titulo = "Despachadores x turno";
$Id = 68;

$paginador = new Paginador($Id,
        "man.id,man.inventario , man.lado,man.isla_pos,GROUP_CONCAT(DISTINCT man.posicion ORDER BY man.posicion ASC) posicion , 
        GROUP_CONCAT(DISTINCT man.despachador ORDER BY man.despachador ASC) despachador,
        GROUP_CONCAT(DISTINCT ven.alias ORDER BY ven.alias ASC) alias,
        GROUP_CONCAT(DISTINCT ven_s.alias ORDER BY ven_s.alias ASC) alias_s,
        GROUP_CONCAT(DISTINCT ven_s.id ORDER BY ven_s.alias ASC) alias_sId",
        "LEFT JOIN ven ON man.despachador = ven.id 
        LEFT JOIN ven ven_s ON man.despachadorsig = ven_s.id",
        "GROUP BY man.isla_pos,man.lado",
        "man.activo='Si'",
        $session->getSessionAttribute("sortField"),
        $session->getSessionAttribute("criteriaField"),
        utils\Utils::split($session->getSessionAttribute("criteria"), "|"),
        strtoupper($session->getSessionAttribute("sortType")),
        $session->getSessionAttribute("page"),
        "REGEXP",
        "");
$tableContents = $paginador->getTableContents();
$tableContents['headers'] = null;
$paginador->setTableContents($tableContents);

require_once './services/VendedoresService.php';

$Vendedores = Array();
$selectVendedor = "SELECT ven.id,CONCAT(LPAD(ven.id,2,0), ' | ', ven.alias) alias FROM ven WHERE ven.activo = 'Si' AND ven.id >=50 ORDER BY ven.alias";
$VenA = $mysqli->query($selectVendedor);
while ($row = $VenA->fetch_array()) {
    $Vendedores[$row["id"]] = $row["alias"];
}
$CiaDAO = new CiaDAO();
$CiaVO = new CiaVO();
$CiaVO = $CiaDAO->retrieve("true", "master");
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
                echo $paginador->headers(array("Isla", "Posición", "Despachador asignado", "Cambiar despachador a", "Revertir despachador", "Despachador Sig.", "Reasignar Desp. Sig.", "Inv"), array());
                while ($paginador->next()) {
                    $row = $paginador->getDataRow();

                    $VenPos = "Despachador" . $row["isla_pos"];
                    $VenSig = "DespachadorSig" . $row["isla_pos"];
                    ?>
                    <tr class="cks">
                        <td style="text-align: center;"><?= $row["isla_pos"] ?></td>
                        <td style="text-align: center;"><?= $row["posicion"] ?></td>
                        <td><?= $row["alias"] ?></td>
                        <td style="text-align: center;">
                            <select class="nombre_cliente" name="<?= $VenPos ?>" data-islapos="<?= $row[isla_pos] ?>" data-lado="<?= $row[lado] ?>" data-op="Asigna">
                                <option value="" selected="selected" disabled="">---SELECCIONAR---</option>
                                <option value="<?= $row["posicion"] ?>" >Sin despachador</option>
                                <?php
                                foreach ($Vendedores as $key => $value) {
                                    echo "<option value='$key'>$value</option>";
                                }
                                ?>
                            </select> 
                        </td>
                        <td style="text-align: center;"><button class="nombre_cliente btns" data-islapos="<?= $row[isla_pos] ?>" data-lado="<?= $row[lado] ?>" data-op="Asigna"  name="BotonRevertir" value="<?= $row[alias_sId] ?>">Revertir isla: <?= $row[isla_pos] ?> | <?= $row[lado] ?></button></td>
                        <td><?= $row[alias_s] ?></td>
                        <td style="text-align: center;">
                            <select class="nombre_cliente" name="DespachadorSig[]" data-op="DespachadorSig" data-islapos="<?= $row[isla_pos] ?>" data-lado="<?= $row[lado] ?>" >
                                <option value="" selected="selected" disabled="">---SELECCIONAR---</option>
                                <option value='<?= $row["posicion"] ?>'>Posicion <?= $row["posicion"] ?></option>
                                <?php
                                foreach ($Vendedores as $key => $value) {
                                    echo "<option value='$key'>$value</option>";
                                }
                                ?>
                            </select> 
                            <input type="hidden" name="Islas[]" value="<?= $row[isla_pos] ?>">
                        </td>
                        <td>
                            <?php $ValCheck = $row["inventario"] === "Si" ? "checked='true'" : ""; ?>
                            <input type="checkbox" class="botonAnimatedGreen" <?= $ValCheck ?>>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>

        <input type="hidden" name="returnLink" value="adespachadores.php">
        <script type="text/javascript">
            $(document).ready(function () {
                $(".botonAnimatedGreen").click(function () {
                    Swal.fire({
                        title: "Configuración necesaria con Soporte",
                        background: "#E9E9E9",
                        showConfirmButton: false
                    }).then((result) => {
                        if (result.value === "<?= $CiaVO->getMaster() ?>") {
                            var trSelectIsla = $(this).parent().parent().find('td:eq(0)').text();
                            var trSelectPos = $(this).parent().parent().find('td:eq(1)').text();
                            var valCheck = $(this).prop('checked');
                            console.log(trSelectIsla + " Y " + trSelectPos + " Check " + valCheck);
                            jQuery.ajax({
                                type: "POST",
                                url: "bootstrap/ajax/getDespachadores.php",
                                dataType: "json",
                                cache: false,
                                data: {"Op": "ActualizaInv00000", "IslaPos": trSelectIsla, "posicion": trSelectPos, "Check": valCheck},
                                success: function (data) {
                                    console.log(data);
                                    Swal.fire({
                                        icon: 'success',
                                        position: 'top-end',
                                        iconColor: 'green',
                                        title: data.val,
                                        background: "#ABEBC6",
                                        toast: true,
                                        timer: 3000,
                                        showConfirmButton: false
                                    });
                                }
                            });
                        } else {
                            var valCheck = $(this).prop('checked');
                            console.log("CEHCK " + valCheck);
                            if (valCheck === true) {
                                $(this).prop('checked', false);
                            } else {
                                $(this).prop('checked', true);
                            }
                        }
                    });
                });
                $(".nombre_cliente").click(function () {
                    var tdlg = $(this);
                    var opcion = this.dataset.op;
                    var trd = tdlg.parent().parent();
                    var Usrm = "<?= $usuarioSesion->getNombre() ?>";
                    jQuery.ajax({
                        type: "POST",
                        url: "bootstrap/ajax/getDespachadores.php",
                        dataType: "json",
                        cache: false,
                        data: {"Op": this.dataset.op, "IslaPos": this.dataset.islapos, "lado": this.dataset.lado, "Despachador": $(this).val(), "Modifica": Usrm},
                        beforeSend: function () {
                            Swal.fire({
                                title: "Procesando, espere por favor...",
                                background: "#ABEBC6",
                                showConfirmButton: false,
                            });
                        },
                        success: function (data) {
                            if (opcion === "DespachadorSig") {
                                trd.find('td:eq(5)').html(data.val);
                                trd.find('td:eq(4)').html("");
                                btn = true;
                            } else {
                                trd.find('td:eq(2)').html(data.val);
                                btn = false;
                            }
                            Swal.fire({
                                icon: 'success',
                                position: 'top-end',
                                iconColor: 'green',
                                title: "Registros modificados con exito del vendedor " + data.val + "",
                                background: "#ABEBC6",
                                toast: true,
                                timer: 3000,
                                showConfirmButton: btn,
                                confirmButtonText: "Recargar <i class='fa fa-refresh' aria-hidden='true'></i>"
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    location.reload();
                                }
                            });
                        }
                    });
                });
            });
        </script>
        <?php
        echo "<div class='mensajes'>$Msj</div>";
        BordeSuperiorCerrar();
        PieDePagina();
        ?>

    </body>
</html>