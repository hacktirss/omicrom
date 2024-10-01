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

require_once './services/RemisionesService.php';

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
                    <a href="remisiones.php"><div class="RegresarCss " alt="Flecha regresar" style="">Regresar</div></a>
                </td>
                <td style="vertical-align: top;">
                    <form name="form1" id="form1" method="post" action="">

                        <?php if (is_numeric($busca)) { ?>

                            <?php
                            if (($rmVO->getProcesado() > 0 || $rmVO->getUuid() !== "-----") && date("Y-m-d", strtotime($rmVO->getFin_venta())) < date("Y-m-d", strtotime(date("Y-m-d") . " -1 day"))) {
                                $Nota = "Venta trasmitida al portal de PEMEX<br>ó ya ha sido facturado el ticket";
                            } elseif ($rmVO->getCliente() > 0) {
                                $Nota = "";
                            } else {
                                if ($usuarioSesion->getTeam() === "Administrador" || $usuarioSesion->getTeam() === "Supervisor") {
                                    $Nota = "<input class='nombre_cliente' type='submit' name='Boton' value='Cambiar tipo de despacho'>";
                                }
                            }

                            cTable('95%', '0');
                            cInput("<strong>Id:", "Text", "5", "Id", "right", $busca, "40", false, true, " &nbsp; &nbsp; No.corte: <strong>" . $rmVO->getCorte() . "</strong>  &nbsp; &nbsp; Turno: <strong>" . $rmVO->getTurno() . "</strong>");
                            cInput("Posicion:", "Text", "20", "Posicion", "right", $rmVO->getPosicion(), "5", true, true, ' &nbsp Manguera: ' . $rmVO->getManguera());
                            cInput("Producto:", "Text", "40", "Producto", "right", $rmVO->getProducto(), "5", true, true, $comVO->getDescripcion() . " [" . $comVO->getClave() . "]");
                            cInput("Cliente:", "Text", "40", "Cliente", "right", "<strong>" . $clienteVO->getId() . "</strong> | <strong>" . $clienteVO->getTipodepago() . "</strong> | " . $clienteVO->getNombre(), "5", true, true, "");
                            cInput("Precio:", "Text", "40", "Precio", "right", $rmVO->getPrecio(), "5", true, true, '');
                            cInput("Fecha de venta:", "Text", "40", "Fin_venta", "right", $rmVO->getFin_venta(), "5", true, true, '');
                            cInput("Importe $:", "Text", "40", "Importe", "right", number_format($rmVO->getPesos(), 2), "5", true, true, '');
                            cInput("Volumen:", "Text", "40", "Volumen", "right", number_format($rmVO->getVolumen(), 3), "5", true, true, 'Lts');
                            cInput("Vendedor:", "Text", "30", "Vendedor", "right", $rmVO->getVendedor(), "30", true, true, "");
                            cInput("UUID:", "Text", "30", "Turno", "right", $rmVO->getUuid(), "5", true, true, '');
                            cSelect("Tipo de venta:", "right", $matriz0, "Tipo_venta", " width: 100px;", "", $Nota);
                            cInput("No.placas:", "Text", "30", "", "right", "" . $rmVO->getPlacas() . " &nbsp &nbsp &nbsp ** No.codigo: " . $rmVO->getCodigo(), "5", true, true, '');
                            if (round($rmVO->getImporte(), 0) == round($rmVO->getPesos(), 0)) {
                                if ($CtRs["statusctv"] === "Abierto") {
                                    if ($rmVO->getTipo_venta() === TipoVenta::NORMAL) {
                                        //error_log(print_r($SCliente, TRUE));
                                        if (($SCliente->getTipodepago() === TiposCliente::CONTADO || $SCliente->getTipodepago() === TiposCliente::PUNTOS) && !$request->hasAttribute("Cliente")) {
                                            $div = "<div style='position: relative;'>";
                                            $div .= "<input type='text' size='40' class='texto_tablas' name='Cliente' id='autocomplete' placeholder='Ingrese nombre o número de cliente' >";
                                            $div .= "&nbsp;&nbsp;<input class='nombre_cliente' type='submit' name='Boton' value='Enviar'>";
                                            $div .= "</div><div id='autocomplete-suggestions'></div>";

                                            cInput("Cliente nuevo: ", "Text", 20, "", "right", "", 5, true, true, $div);
                                        } else {
                                            cInput("Cliente nuevo: ", "Text", 20, "", "right", $SCliente->getNombre() . " | " . $SCliente->getTipodepago(), 5, true, true, "<input type='hidden' name='Cliente' id='Cliente' value='$Cliente'");
                                            $div = "<div style='position: relative;'>";
                                            $div .= "<input type='text' size='40' class='texto_tablas' name='Codigo' list='Codigos' placeholder='Buscar código'  autocomplete=\"off\">";
                                            $div .= "<datalist id='Codigos'>";
                                            foreach ($Codigos as $codigo) {
                                                $div .= "<option value='" . $codigo["descripcion"] . "'>";
                                            }
                                            $div .= "</datalist>";
                                            cInput("Seleccionar código: ", "Text", 20, "", "right", "", 5, true, true, $div);

                                            $div2 = "<div style='position: relative;'>";
                                            $div2 .= "<input class='texto_tablas' type='text' name='Placas' onkeyup='mayus(this);' placeholder='Num.Placas'>";
                                            $div2 .= " &nbsp; Km.: <input class='texto_tablas' type='number' name='Kilometraje' max='10000000'>";
                                            $div2 .= "&nbsp;&nbsp;<input class='nombre_cliente' type='submit' name='Boton' value='Guardar'>";
                                            $div2 .= "</div>";

                                            cInput("Placas: ", "Text", 20, "", "right", "", 5, true, true, $div2);
                                        }
                                    }
                                } else {
                                    cInput("Cliente nuevo: ", "Text", 20, "", "right", $SCliente->getNombre() . " | " . $SCliente->getTipodepago(), 5, true, true, "<input type='hidden' name='Cliente' id='Cliente' value='$Cliente'");
                                }
                            }
                            cTableCie();
                            ?>

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
        </script>
    </body>

</html>