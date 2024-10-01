<?php

/*
 * CFDIComboBoxes
 * omicrom®
 * © 2017, Detisa 
 * http://www.detisa.com.mx
 * @author Rolando Esquivel Villafaña, Softcoatl
 * @version 1.0
 * @since jun 2017
 */
CONST SELECT = "</select>";
CONST OPTION = "<option value='";
CONST CLOPTION = "</option>";

class ComboboxINEAmbito {

    static function generate($comboID) {

        $mysqli = iconnect();

        $qry = $mysqli->query("SELECT clave, descripcion FROM cfdi33_ine_ambito WHERE status = 1");

        $html = "<select style='font-size: 10px;' name='$comboID' id='$comboID'>";
        $html .= "<option value=''>Ámbito del proceso</option>";
        while ($rs = $qry->fetch_array()) {
            $html .= OPTION . $rs["descripcion"] . "'>" . $rs["clave"] . " | " . $rs["descripcion"] . CLOPTION;
        }

        echo $html . SELECT;
    }

}

class ComboboxINEEntidad {

    static function generate($comboID) {
        $mysqli = iconnect();
        $qry = $mysqli->query("SELECT clave, descripcion FROM cfdi33_ine_entidad WHERE status = 1");

        $html = "<select style='font-size: 10px;' name='$comboID' id='$comboID'><option value=''>Seleccione la entidad</option>";
        while ($rs = $qry->fetch_array()) {
            $html .= OPTION . $rs["clave"] . "'>" . $rs["clave"] . " | " . $rs["descripcion"] . CLOPTION;
        }

        echo $html . SELECT;
    }

}

class ComboboxINEComite {

    static function generate($comboID) {
        $mysqli = iconnect();
        $qry = $mysqli->query("SELECT clave, descripcion FROM cfdi33_ine_tcomite WHERE status = 1");

        $html = "<select  class='texto_tablas' name='$comboID' id='$comboID'><option value=''>Seleccione el tipo de comité</option>";
        while ($rs = $qry->fetch_array()) {
            $html .= OPTION . $rs["descripcion"] . "'>" . $rs["clave"] . " | " . $rs["descripcion"] . CLOPTION;
        }

        echo $html . SELECT;
    }

}

class ComboboxINEProceso {

    static function generate($comboID) {
        $mysqli = iconnect();
        $qry = $mysqli->query("SELECT clave, descripcion FROM cfdi33_ine_tproceso WHERE status = 1");

        $html = "<select  class='texto_tablas' name='$comboID' id='$comboID'><option value=''>Seleccione el tipo de proceso</option>";
        while ($rs = $qry->fetch_array()) {
            $html .= OPTION . $rs["descripcion"] . "'>" . $rs["clave"] . " | " . $rs["descripcion"] . CLOPTION;
        }
        echo $html . SELECT;
    }

}

class ComboboxComplementos {

    static function generate($comboID) {
        $mysqli = iconnect();
        $qry = $mysqli->query("SELECT id clave, nombre descripcion FROM complementos WHERE status = 'A'");

        $html = "<select  class='texto_tablas' name='$comboID' id='$comboID'><option value=''>ASIGNAR COMPLEMENTO</option>";
        while ($rs = $qry->fetch_array()) {
            $html .= OPTION . $rs["clave"] . "'>" . $rs["clave"] | $rs["descripcion"] . CLOPTION;
        }

        echo $html . SELECT;
    }

}

class ComboboxFormaDePago {

    static function generate($comboID, $width = "150px") {
        $mysqli = iconnect();
        $qry = $mysqli->query("SELECT clave, descripcion FROM cfdi33_c_fpago WHERE status = 1");

        $html = "<select  class='texto_tablas' name='$comboID' id='$comboID' style='width: $width'><option value=''>SELECCIONE FORMA DE PAGO</option>";
        while ($rs = $qry->fetch_array()) {
            $html .= OPTION . $rs["clave"] . "'>" . $rs["clave"] | $rs["descripcion"] . CLOPTION;
        }
        $html .= "<option value='98'>NA | No Aplica</option>";

        echo $html . SELECT;
    }

}

class ComboboxMetodoDePago {

    static function generate($comboID, $width = "150px") {
        $mysqli = iconnect();
        $qry = $mysqli->query("SELECT clave, descripcion FROM cfdi33_c_mpago WHERE status = 1");

        $html = "<select  class='texto_tablas' name='$comboID' id='$comboID' style='width: $width'>";
        while ($rs = $qry->fetch_array()) {
            $html .= OPTION . $rs["clave"] . "'>" . $rs["clave"] | $rs["descripcion"] . CLOPTION;
        }

        echo $html . SELECT;
    }

}

class ComboboxUnidades {

    static function generate($comboID) {
        $mysqli = iconnect();
        $qry = $mysqli->query("SELECT clave, nombre FROM cfdi33_c_unidades WHERE status = 1");

        $html = "<select  style='width: 90%'  class='texto_tablas' name='$comboID' id='$comboID'><option value=''>SELECCIONE UNIDAD</option>";
        while ($rs = $qry->fetch_array()) {
            $html .= OPTION . $rs["clave"] . "'>" . $rs["clave"] | $rs["nombre"] . CLOPTION;
        }

        echo $html . SELECT;
    }

}

class ComboboxDivison {

    static function generate($comboID, $tipo, $options = "") {
        $mysqli = iconnect();
        $query = "SELECT clave, descripcion FROM cfdi33_c_categorias WHERE clave_padre = '0' " . (empty($tipo) ? "" : " AND tipo = '$tipo'");
        $qry = $mysqli->query($query);

        $html = "<select class = 'texto_tablas' name = '$comboID' id = '$comboID' $options><option value = ''>SELECCIONE DIVISIÓN</option>";
        while ($rs = $qry->fetch_array()) {
            $html .= OPTION . $rs["clave"] . "'>" . $rs["descripcion"] . CLOPTION;
        }

        echo $html . SELECT;
    }

}

class ComboboxGrupo {

    static function generate($comboID, $division, $options = "") {
        $mysqli = iconnect();
        $qry = $mysqli->query("SELECT clave, descripcion FROM cfdi33_c_categorias WHERE clave_padre = '$division'");

        $html = "<select class = 'texto_tablas' name = '$comboID' id = '$comboID' $options><option value = ''>SELECCIONE GRUPO</option>";
        while ($rs = $qry->fetch_array()) {
            $html .= OPTION . $rs["clave"] . "'>" . $rs["descripcion"] . CLOPTION;
        }

        echo $html . SELECT;
    }

}

class ComboboxClase {

    static function generate($comboID, $grupo, $options = "") {
        $mysqli = iconnect();
        $qry = $mysqli->query("SELECT clave, descripcion FROM cfdi33_c_categorias WHERE clave_padre = '$grupo'");

        $html = "<select class = 'texto_tablas' name = '$comboID' id = '$comboID' $options><option value = ''>SELECCIONE CLASE</option>";
        while ($rs = $qry->fetch_array()) {
            $html .= OPTION . $rs["clave"] . "'>" . $rs["descripcion"] . CLOPTION;
        }

        echo $html . SELECT;
    }

}

class ComboboxProductoServicio {

    static function generate($comboID, $clase, $options = "") {
        $mysqli = iconnect();
        $html = "<select class = 'texto_tablas' name = '$comboID' id = '$comboID' $options><option value = ''>SELECCIONE CONCEPTO</option>";
        $html .= "<option value = '01010101'>No existe en el catálogo</option>";
        if ($clase != '') {
            $qry = $mysqli->query("SELECT clave, nombre FROM cfdi33_c_conceptos WHERE clave LIKE 'substr($clase, 0, 6)%'");

            while ($rs = $qry->fetch_array()) {
                $html .= OPTION . $rs["clave"] . "'>" . $rs["clave"] | $rs["nombre"] . CLOPTION;
            }
        }

        echo $html . SELECT;
    }

}

class ComboboxCommonProductoServicio {

    static function generate($comboID, $options = "") {
        $mysqli = iconnect();
        $html = "<select class = 'texto_tablas' name = '$comboID' id = '$comboID' $options><option value = ''>SELECCIONE CONCEPTO</option>";
        $html .= "<option value = '01010101'>01010101 | No existe en el catálogo</option>";
        $qry = $mysqli->query("SELECT clave, nombre FROM cfdi33_c_conceptos WHERE status = '1'");

        while ($rs = $qry->fetch_array()) {
            $html .= OPTION . $rs["clave"] . "'>" . $rs["clave"] | $rs["nombre"] . CLOPTION;
        }

        echo $html . SELECT;
    }

}

class ComboboxTipoRelacion {

    static function generate($comboID, $width = "500px") {
        global $SELECT;
        $mysqli = iconnect();
        $qry = $mysqli->query("SELECT clave, descripcion FROM cfdi33_c_trelacion WHERE status = 1");

        $html = "<select style = 'font-size: 10px;width: $width' name = '$comboID' id = '$comboID'>";
        $html .= "<option value = ''>SELECCIONE EL TIPO DE RELACI & Oacute;
        N</option>";
        while ($rs = $qry->fetch_array()) {
            $html .= OPTION . $rs["clave"] . "'>" . $rs["clave"] | $rs["descripcion"] . CLOPTION;
        }

        echo $html . SELECT;
    }

}

//ComboboxTipoRelacion

class ComboboxUsoCFDI {

    static function generate($comboID, $width = "500px") {
        $mysqli = iconnect();
        $qry = $mysqli->query("SELECT clave, descripcion FROM cfdi33_c_uso WHERE status = 1");

        $html = "<select class = 'texto_tablas' name = '$comboID' id = '$comboID' style = 'width: $width'><option value = ''>SELECCIONE USO CFDI</option>";
        while ($rs = $qry->fetch_array()) {
            $html .= OPTION . $rs["clave"] . "'>" . str_replace(' ', '&nbsp;', $rs["clave"]) . " | " . $rs["descripcion"] . CLOPTION;
        }

        echo $html . SELECT;
    }

}

class ComboboxRegimenes {

    static function generate($comboID) {
        $mysqli = iconnect();
        $qry = $mysqli->query("SELECT clave, descripcion FROM cfdi33_c_regimenes WHERE status = 1");

        $html = "<select style='font-size: 10px;' name='$comboID' id='$comboID'><option value=''>SELECCIONE RÉGIMEN FISCAL</option>";
        while ($rs = $qry->fetch_array()) {
            $html .= OPTION . "" . $rs["clave"] . "'>" . $rs["clave"] | $rs["descripcion"] . CLOPTION;
        }

        echo $html . SELECT;
    }

}
