<?php
#Librerias
session_start();

include_once ("check.php");
include_once ("libnvo/lib.php");

use com\softcoatl\utils as utils;

$mysqli = iconnect();
$request = utils\HTTPUtils::getRequest();
$self = utils\HTTPUtils::self();
$Return = "remisiones.php";

$Titulo = "Detalle de venta";
$nameVarBusca = "busca";
if ($request->hasAttribute($nameVarBusca)) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute($nameVarBusca));
} elseif ($request->hasAttribute("id")) {
    utils\HTTPUtils::setSessionValue($nameVarBusca, $request->getAttribute("id"));
}
$busca = utils\HTTPUtils::getSessionValue($nameVarBusca);

require_once './services/RemisioneseService.php';

$rmVO = new RmVO();
$clienteVO = new ClientesVO();
$comVO = new CombustiblesVO();
if (is_numeric($busca)) {
    $rmVO = $rmDAO->retrieve($busca);
    $clienteVO = $clientesDAO->retrieve($rmVO->getCliente());
    $comVO = $comDAO->retrieve($rmVO->getProducto(), "clavei");
    $Cliente = $rmVO->getCliente();
} else {
    $PosA = $mysqli->query("SELECT posicion FROM man WHERE activo='Si' ORDER BY posicion");
    $matrizPosicion = array("" => "SELECCIONAR");

    if ($request->hasAttribute("Posicion")) {
        $matrizPosicion = array();
        $matrizProducto = array();
        $Com = $mysqli->query("SELECT m.producto,c.descripcion FROM man_pro m,com c "
                . "WHERE m.producto = c.clavei AND m.activo='Si' AND m.posicion = '" . $request->getAttribute("Posicion") . "'");
        while ($rg = $Com->fetch_array()) {
            $matrizProducto[$rg["producto"]] = $rg["descripcion"];
        }
    }

    while ($rg = $PosA->fetch_array()) {
        $matrizPosicion[$rg["posicion"]] = $rg["posicion"];
    }
    $Titulo = "Agregar venta";
}

$SCliente = $clienteVO;
if ($request->hasAttribute("Cliente")) {
    $SeachCliente = $request->getAttribute("Cliente");
    $Cliente = strpos($SeachCliente, "|") > 0 ? trim(substr($SeachCliente, 0, strpos($SeachCliente, "|"))) : trim($SeachCliente);
    $SCliente = $clientesDAO->retrieve($Cliente);

    $selectCodigos = "SELECT id, CONCAT(id, ' | ', TRIM(impreso), ' | ', TRIM(descripcion) , ' | ', TRIM(placas),IF(periodo = 'B',CONCAT(' | Saldo Disponible $', importe),'')) descripcion
                    FROM unidades WHERE cliente = '$Cliente' AND LOWER(estado) = 'a'
                    ORDER BY impreso";
    $Codigos = utils\IConnection::getRowsFromQuery($selectCodigos);
} elseif ($clienteVO->getId() > 0) {
    $selectCodigos = "SELECT id, CONCAT(id, ' | ', TRIM(impreso), ' | ', TRIM(descripcion) , ' | ', TRIM(placas)) descripcion
                    FROM unidades WHERE cliente = '" . $clienteVO->getId() . "' AND LOWER(estado) = 'a'
                    ORDER BY impreso";
    $Codigos = utils\IConnection::getRowsFromQuery($selectCodigos);
}
$SlCt = "SELECT status,statusctv FROM ct WHERE id = " . $rmVO->getCorte();
$CtRs = utils\IConnection::execSql($SlCt);

$matriz0 = array("D" => "Normal", "J" => "Jarreo", "A" => "Uvas/Pemex", "N" => "Consignacion");
?>

<!DOCTYPE html>
<html lang="es" xml:lang="es">
    <head>
        <?php require './config_omicrom.php'; ?>
        <title><?= $Gcia ?></title>
        <script>
            $(document).ready(function () {
                $("#busca").val("<?= $busca ?>");

                if ($("#busca").val() > 0) {
                    $("#Tipo_venta").val("<?= $rmVO->getTipo_venta() ?>");

                    if ($("#Tipo_venta").val() === "D") {
                        $("#autocomplete").focus();
                        if ($("#Cliente").val() > 0) {
                            $("#autocompleteCodigo").focus();
                        }
                    }
                } else {
                    $("#Posicion").focus();
                    if ($("#Posicion").val() > 0) {
                        $("#Posicion").val("<?= $request->getAttribute("Posicion") ?>");
                        $("#Importe").focus();
                    }
                }
            });
        </script>
    </head>

    <body>

        <?php BordeSuperior(); ?>

        <table style="width: 100%;" class="texto_tablas" aria-hidden="true">
            <tr>
                <td style="text-align: center; width: 90px;" class="nombre_cliente" >
                    <a href="remisionesCP.php"><div class="RegresarCss " alt="Flecha regresar" style="">Regresar</div></a>
                </td>
                <td style="vertical-align: top;">
                    <form name="form1" id="form1" method="post" action="">

                        <?php if (is_numeric($busca)) { ?>

                            <?php
                            if (($rmVO->getProcesado() > 0 || $rmVO->getUuid() !== "-----") && date("Y-m-d", strtotime($rmVO->getFin_venta())) < date("Y-m-d", strtotime(date("Y-m-d") . " -1 day"))) {
                                $Nota = "Venta trasmitida al portal de PEMEX<br>ó ya ha sido facturado el ticket";
                            } elseif ($rmVO->getCliente() > 0) {
                                $Nota = "";
                                $InputVolumen = "<input type='button' onclick='validarNumero()' class='nombre_cliente' name='Boton1' value='Modificar volumen'>";
                            } else {
                                if ($usuarioSesion->getTeam() === "Administrador" || $usuarioSesion->getTeam() === "Supervisor") {
                                    $Nota = "<input class='nombre_cliente' type='submit' name='Boton' value='Cambiar tipo de despacho'>";
                                    $InputVolumen = "<input type='button' onclick='validarNumero()' class='nombre_cliente' name='Boton1' value='Modificar volumen'>";
                                }
                            }
                            ?>

                            <div id="FormulariosBoots">
                                <div class="container no-margin">
                                    <div class="row no-padding">
                                        <div class="col-10 background container no-margin">

                                            <div class="row no-padding">
                                                <div class="col-11 align-right mensajeInput">
                                                    <sup><i style="color: red;font-size: 8px;" class="fa fa-lg fa-asterisk" aria-hidden="true"></i></sup>
                                                    <strong>Ingresar volumen registrado por el veeder de la gasolinera</strong>
                                                </div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right required">Id:</div>
                                                <div class="col-2">
                                                    <input type="text" name="IdBusca" id="IdBusca" class="clase-<?= $clase2 ?>" value="<?= $busca ?>" disabled>
                                                </div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right required">Producto:</div>
                                                <div class="col-2">
                                                    <input type="text" name="Producto" id="Producto" class="clase-<?= $clase2 ?>" value="<?= $comVO->getDescripcion() . " [" . $comVO->getClave() . "]" ?>" disabled/>
                                                </div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right required">Fecha:</div>
                                                <div class="col-2">
                                                    <input type="text" name="Fecha" id="Fecha" class="clase-<?= $clase2 ?>" value="<?= $rmVO->getFin_venta() ?>" disabled/>
                                                </div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right required">Cliente:</div>
                                                <div class="col-4">
                                                    <input type="text" name="Cliente" id="Cliente" class="clase-<?= $clase2 ?>" value="<?= $clienteVO->getTipodepago() . " | " . $clienteVO->getNombre() ?>" disabled/>
                                                </div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right required">Importe:</div>
                                                <div class="col-3">
                                                    <input type="text" name="Importe" id="Importe" value="<?= number_format($rmVO->getPesos(), 2) ?>" class="clase-<?= $clase2 ?>" disabled/>
                                                </div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right required">Volumen:</div>
                                                <div class="col-3">
                                                    <input type="text" name="Importe" id="Importe" value="<?= number_format($rmVO->getVolumen(), 2) ?>" class="clase-<?= $clase2 ?>" disabled/>
                                                </div>
                                            </div>
                                            <div class="row no-padding">
                                                <div class="col-3 align-right required">UUID:</div>
                                                <div class="col-3">
                                                    <input type="text" name="Uuid" id="Uuid" value="<?= $rmVO->getUuid() ?>" class="clase-<?= $clase2 ?>" disabled/>
                                                </div>
                                            </div>
                                            <form name="formulario2" id="formulario2" method="post" action="">
                                                <input type="hidden" name="busca" value="<?= $busca ?>">
                                                <div class="row no-padding">
                                                    <div class="col-3 align-right required">Tipo de venta:</div>
                                                    <div class="col-3">
                                                        <select name="Tipo_venta" id="Tipo_venta" class="texto_tablas">
                                                            <option value="D">Normal</option>
                                                            <option value="J">Jarreo</option>
                                                            <option value="A">Uvas/Pemex</option>
                                                            <option value="N">Consignacion</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-4 mensajeInput"><?= $Nota ?></div>
                                                </div>
                                            </form>
                                            <?php
                                            if ($rmVO->getTotalizadorVF() == 0) {
                                                ?>
                                                <form name="formulario3" id="formulario3" method="post" action="">
                                                    <div class="row no-padding">
                                                        <div class="col-3 align-right required">Volumen:</div>
                                                        <div class="col-3">
                                                            <input type="text" name="VolumenV" id="VolumenV" class="clase-<?= $clase2 ?>" placeholder="<?= $rmVO->getVolumen() * 0.98 ?>"  required/>
                                                        </div>
                                                        <div class="col-4 mensajeInput">
                                                            <?= $InputVolumen ?>
                                                            <input type='hidden' onclick="validarNumero()" class='nombre_cliente' name='Boton' value='Modificar volumen'>
                                                        </div>
                                                    </div>
                                                </form>
                                                <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <?php
                        } else {
                            cTable('75%', '0');
                            cInput("<strong>Id:<strong>", "Text", "5", "Id", "right", $busca, "40", false, true, "");
                            cSelect("Posicion:", "right", $matrizPosicion, "Posicion", " width: 100px;", " onChange='submit()' required='required'", "");
                            if ($request->hasAttribute("Posicion")) {
                                cSelect("Producto:", "right", $matrizProducto, "Producto", " width: 100px;", " required='required'", "");
                                cInput("Importe:", "Text", 12, "Importe", "right", 0, "10", true, false, "");
                                cInput("<strong>&Oacute;</o> ", "Text", 20, "", "right", "", 5, true, true, "");
                                cInput("Litros:", "Text", 12, "Volumen", "right", 0, "10", true, false, "");

                                echo "</td><tr>";
                                echo "<tr><td colspan='2' align='center'>";
                                echo "&nbsp;<input type='submit' class='nombre_cliente' name='Boton' value='Agregar'>";
                                echo "</td><tr>";
                            }

                            cTableCie();
                        }
                        ?>

                        <input type='hidden' name='busca' id='busca'>
                    </form>
                </td>
            </tr>
        </table>

        <?php BordeSuperiorCerrar() ?>
        <?php PieDePagina() ?>

        <script>
            $(document).ready(function () {

                $("#autocomplete").activeComboBox($(""),
                        "SELECT id as data, CONCAT(id, ' | ', nombre, ' | ', tipodepago) value FROM cli WHERE TRUE AND tipodepago NOT REGEXP 'Contado' AND activo = 'Si'",
                        "nombre");

                var cliente = "<?= $Cliente ?>";

                $("#autocompleteCodigo").activeComboBox($(""),
                        "SELECT id as data,CONCAT(id, ' | ',impreso, ' | ', descripcion) value FROM unidades WHERE cliente = '" + cliente + "'",
                        "impreso");
            });

            function validarNumero() {
                var ImporteM =<?= $rmVO->getVolumen() ?>;
                var valor = $("#VolumenV").val();
                var regex = /^-?\d*\.?\d+$/; // Expresión regular para números enteros o decimales
                var ImporteMin = ImporteM * 0.8;
                var ImporteMax = ImporteM * 1.1;
                if (regex.test(valor) && valor > ImporteMin && valor < ImporteMax) {
                    Swal.fire({
                        title: "¿Seguro que desea modificar la cantidad a " + valor + " ?",
                        icon: "info",
                        background: "#E9E9E9",
                        showConfirmButton: true,
                        confirmButtonText: "Si",
                        showCancelButton: true,
                        cancelButtonText: "No",
                        cancelButtonColor: '#d33'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $("#formulario3")[0].submit();
                        }
                    });
                } else {
                    alert("Valor minimo : " + ImporteMin + " ValorMax : " + ImporteMax);
                }
            }
        </script>
    </body>

</html>