<?php
$alignRight = "right";
$alignCenter = "center";
$alingLeft = "left";
$siMayusculas = true;
$siRequerido = "required";

$clase0 = "0";
$clase1 = "1";
$clase2 = "2";
$clase3 = "3";
$clase4 = "4";
$clase5 = "5";

$tipoText = 1;
$tipoSelect = 2;
$tipoNumber = 3;

/**
 * Generar nuevo bloque para formulario
 * @param string $nombre Nombre del formulario
 * @param string $accion Destino de los datos
 */
function abrirFormulario($nombre, $accion = "") {
    ?>
    <form name="<?= $nombre ?>" id="<?= $nombre ?>" method="post" action="<?= $accion ?>" onsubmit="validarFormulario(this);">
        <?php
    }

    /**
     * Cerrar bloque de formulario
     */
    function cerrarFormulario() {
        ?>
        <div id="Response" style="text-align: center; color: red; font-weight: bold;"></div>
        <div style="text-align: left;">(<sup><i style="color: red;font-size: 8px;" class="fa fa-lg fa-asterisk" aria-hidden="true"></i></sup>) Campos necesarios para control de venta</div>
    </form>
    <?php
}

/**
 * 
 * @param string $etiqueta
 * @param string $nombreInput
 * @param int $tamaño
 * @param string $mayusculas
 * @param string $requerido
 * @param string $placeholder
 * @param string $clase
 * @param string $comentarios
 */
function crearInputText($etiqueta, $nombreInput, $tamaño = 10, $mayusculas = false, $requerido = "", $placeholder = "", $clase = "1", $comentarios = "") {
    $toUpper = $mayusculas ? "onkeyup='transformarMayusculas(this);'" : "";
    $color = !empty($requerido) ? "red" : "transparent";
    ?>
    <div class="grupo1">
        <div>
            <sup><i style="color: <?= $color ?>;font-size: 7px;" class="fa fa-lg fa-asterisk" aria-hidden="true"></i></sup>
            <?= $etiqueta ?>:
        </div>
        <div>
            <?php if (!empty($nombreInput)) { ?>
                <input type="text" name="<?= $nombreInput ?>" id="<?= $nombreInput ?>" maxlength="<?= $tamaño ?>" class="clase-<?= $clase ?>" <?= $toUpper ?> placeholder="<?= $placeholder ?>" <?= $requerido ?>/>
            <?php } ?>
            <?= $comentarios ?>
        </div>
    </div>
    <?php
}

function crearInputPasswd($etiqueta, $nombreInput, $tamaño = 10, $requerido = "", $placeholder = "", $clase = "1", $comentarios = "") {
    $color = !empty($requerido) ? "red" : "transparent";
    ?>
    <div class="grupo1">
        <div>
            <sup><i style="color: <?= $color ?>;font-size: 7px;" class="fa fa-lg fa-asterisk" aria-hidden="true"></i></sup>
            <?= $etiqueta ?>:
        </div>
        <div>
            <?php if (!empty($nombreInput)) { ?>
                <input type="password" name="<?= $nombreInput ?>" id="<?= $nombreInput ?>" maxlength="<?= $tamaño ?>" class="clase-<?= $clase ?>" placeholder="<?= $placeholder ?>" <?= $requerido ?> autocomplete="new-password"/>
            <?php } ?>
            <?= $comentarios ?>
        </div>
    </div>
    <?php
}

/**
 * 
 * @global string $clase5
 * @param string $etiquetaPrincipal
 * @param string $etiquetaSecundaria
 * @param string $nombreInput1
 * @param string $nombreInput2
 * @param int $tamaño1
 * @param int $tamaño2
 * @param string $placeholder1
 * @param string $placeholder2
 * @param string $mayusculas
 * @param string $requerido
 * @param string $clase
 * @param string $tipo1
 * @param string $tipo2
 * @param string $arrayDato1
 * @param string $arrayDato2
 * @param string $comentarios
 */
function crearInputTextBy2($etiquetaPrincipal, $etiquetaSecundaria, $nombreInput1, $nombreInput2, $tamaño1 = 10, $tamaño2 = 10, $placeholder1 = "", $placeholder2 = "", $mayusculas = false, $requerido = "", $clase = "1", $tipo1 = 1, $tipo2 = 1, $arrayDato1 = null, $arrayDato2 = null, $comentarios = "", $minimo = 0, $maximo = 0) {
    global $clase5;
    $toUpper = $mayusculas ? "onkeyup='transformarMayusculas(this);'" : "";
    $color = !empty($requerido) ? "red" : "transparent";
    ?>
    <div class="grupo1">
        <div>
            <sup><i style="color: <?= $color ?>;font-size: 7px;" class="fa fa-lg fa-asterisk" aria-hidden="true"></i></sup>
            <?= $etiquetaPrincipal ?>
        </div>
        <div>
            <div class="clase-<?= $clase ?>">
                <div>
                    <?php if ($tipo1 == 1) { ?>
                        <input type="text" name="<?= $nombreInput1 ?>" id="<?= $nombreInput1 ?>" maxlength="<?= $tamaño1 ?>" class="clase-<?= $clase5 ?>" <?= $toUpper ?> placeholder="<?= $placeholder1 ?>" <?= $requerido ?>/>
                    <?php } elseif ($tipo1 == 2) { ?>
                        <select name="<?= $nombreInput1 ?>" id="<?= $nombreInput1 ?>" class="clase-<?= $clase5 ?>" <?= $requerido ?>>
                            <?php
                            if (is_array($arrayDato1) && count($arrayDato1) > 0) {
                                foreach ($arrayDato1 as $key => $value) {
                                    ?>
                                    <option value="<?= $key ?>"/><?= $value ?></option>
                                    <?php
                                }
                            }
                            ?>
                        </select>
                    <?php } elseif ($tipo1 == 3) { ?>
                        <input type="number" name="<?= $nombreInput1 ?>" id="<?= $nombreInput1 ?>" min="<?= $minimo ?>" max="<?= $maximo ?>" class="clase-<?= $clase5 ?>" placeholder="<?= $placeholder1 ?>" <?= $requerido ?>/>
                    <?php } ?>
                </div>
                <div>
                    <?= $etiquetaSecundaria ?>:
                </div>
                <div>
                    <?php if ($tipo2 == 1) { ?>
                        <input type="text" name="<?= $nombreInput2 ?>" id="<?= $nombreInput2 ?>" maxlength="<?= $tamaño2 ?>" class="clase-<?= $clase5 ?>" <?= $toUpper ?> placeholder="<?= $placeholder2 ?>" <?= $requerido ?>/>
                    <?php } elseif ($tipo2 == 2) { ?>
                        <select name="<?= $nombreInput2 ?>" id="<?= $nombreInput2 ?>" class="clase-<?= $clase5 ?>" <?= $requerido ?>>
                            <?php
                            if (is_array($arrayDato2) && count($arrayDato2) > 0) {
                                foreach ($arrayDato2 as $key => $value) {
                                    ?>
                                    <option value="<?= $key ?>"/><?= $value ?></option>
                                    <?php
                                }
                            }
                            ?>
                        </select>
                    <?php } elseif ($tipo2 == 3) { ?>
                        <input type="number" name="<?= $nombreInput2 ?>" id="<?= $nombreInput2 ?>" min="<?= $minimo ?>" max="<?= $maximo ?>" class="clase-<?= $clase5 ?>" placeholder="<?= $placeholder2 ?>" <?= $requerido ?>/>
                    <?php } ?>
                </div>
            </div>
            <div style="margin-left: 5px;"><?= $comentarios ?></div>
        </div>
    </div>
    <?php
}

function crearInputSelect($etiqueta, $nombreInput, $arrayDatos, $requerido = "", $clase = "1", $comentarios = "") {
    $color = !empty($requerido) ? "red" : "transparent";
    ?>
    <div class="grupo1">
        <div>
            <sup><i style="color: <?= $color ?>;font-size: 7px;" class="fa fa-lg fa-asterisk" aria-hidden="true"></i></sup>
            <?= $etiqueta ?>:
        </div>
        <div>
            <?php
            if (!empty($nombreInput)) {
                ?>
                <select name="<?= $nombreInput ?>" id="<?= $nombreInput ?>" class="clase-<?= $clase ?>" <?= $requerido ?>>
                    <?php
                    if (is_array($arrayDatos) && count($arrayDatos) > 0) {
                        foreach ($arrayDatos as $key => $value) {
                            ?>
                            <option value="<?= $key ?>"/><?= $value ?></option>
                            <?php
                        }
                    }
                    ?>
                </select>
            <?php } ?>
            <?= $comentarios ?>
        </div>
    </div>
    <?php
}

/**
 * 
 * @param string $etiqueta
 * @param string $nombreInput
 * @param int $minimo
 * @param int $maximo
 * @param string $requerido
 * @param string $placeholder
 * @param string $clase
 * @param string $comentarios
 */
function crearInputNumber($etiqueta, $nombreInput, $minimo = 0, $maximo = 0, $requerido = "", $placeholder = "", $clase = "1", $comentarios = "") {
    $color = !empty($requerido) ? "red" : "transparent";
    ?>
    <div class="grupo1">
        <div>
            <sup><i style="color: <?= $color ?>;font-size: 7px;" class="fa fa-lg fa-asterisk" aria-hidden="true"></i></sup>
            <?= $etiqueta ?>:
        </div>
        <div>
            <?php if (!empty($nombreInput)) { ?>
                <input type="number" name="<?= $nombreInput ?>" id="<?= $nombreInput ?>" min="<?= $minimo ?>" max="<?= $maximo ?>" class="clase-<?= $clase ?>" placeholder="<?= $placeholder ?>" <?= $requerido ?>/>
            <?php } ?>
            <?= $comentarios ?>
        </div>
    </div>
    <?php
}

function crearInputRadio($etiqueta, $nombreInput, $arrayRadios, $requerido = "", $comentarios = "") {
    $color = !empty($requerido) ? "red" : "transparent";
    ?>
    <div class="grupo1">
        <div>
            <sup><i style="color: <?= $color ?>;font-size: 7px;" class="fa fa-lg fa-asterisk" aria-hidden="true"></i></sup>
            <?= $etiqueta ?>:
        </div>
        <div>
            <?php
            if (is_array($arrayRadios) && count($arrayRadios) > 0) {
                foreach ($arrayRadios as $key => $value) {
                    ?>
                    <input type="radio" name="<?= $nombreInput ?>" value="<?= $value ?>"/><?= $key ?>
                    <?php
                }
            }
            ?>
            <?= $comentarios ?>
        </div>
    </div>
    <?php
}

function crearInputCheckbox($etiqueta, $nombreInput, $valor, $comentarios = "") {
    ?>
    <div class="grupo1">
        <div>
            <?= $etiqueta ?>:
        </div>
        <div>
            <?php if (!empty($nombreInput)) { ?>
                <input type="checkbox" name="<?= $nombreInput ?>" id="<?= $nombreInput ?>" value="<?= $valor ?>"/>
            <?php } ?>
            <?= $comentarios ?>
        </div>
    </div>
    <?php
}

function crearInputCheckbox2($etiqueta, $nombreInput, $nombreInput2, $valor, $valor2, $comentarios = "", $comentarios2 = "", $clase = 1) {
    ?>
    <div class="grupo1">
        <div>
            <?= $etiqueta ?>:
        </div>
        <div>
            <div class="clase-<?= $clase ?>">
                <div style="width: 5%;float: none;">
                    <input type="checkbox" name="<?= $nombreInput ?>" id="<?= $nombreInput ?>" value="<?= $valor ?>"/>
                </div>
                <div style="text-align: left;width: 45%"><?= $comentarios ?></div>
                <div style="width: 5%;float: none;">
                    <input type="checkbox" name="<?= $nombreInput2 ?>" id="<?= $nombreInput2 ?>" value="<?= $valor2 ?>"/>
                </div>
                <div style="text-align: left;width: 40%"><?= $comentarios2 ?></div>
            </div>
        </div>
    </div>
    <?php
}

function crearInputCheckboxArray($etiqueta, $arrayChecks) {
    ?>
    <div class="grupo1">
        <div>
            <?= $etiqueta ?>:
        </div>
        <div>
            <?php
            if (is_array($arrayChecks) && count($arrayChecks) > 0) {
                foreach ($arrayChecks as $key => $value) {
                    ?>
                    <input type="checkbox" name="<?= $key ?>" id="<?= $key ?>"/><?= $value ?>
                    <?php
                }
            }
            ?>
        </div>
    </div>
    <?php
}

function crearInputHidden($nombreInput) {
    ?>
    <input type="hidden" name="<?= $nombreInput ?>" id="<?= $nombreInput ?>"/>
    <?php
}

function crearBoton($nombreInput, $valorInput = "Enviar", $valoInput2 = "") {
    ?>
    <div class="grupo1">
        <div style="background-color: white;">
        </div>
        <div>
            <input type="submit" name="<?= $nombreInput ?>" id="<?= $nombreInput ?>" value="<?= $valorInput ?>"/>
            <?php if (!empty($valoInput2)) { ?>
                <input type="submit" name="<?= $nombreInput ?>" id="<?= $nombreInput ?>" value="<?= $valoInput2 ?>" style="margin-left: 20px;"/>
            <?php } ?>
        </div>
    </div>
    <?php
}
